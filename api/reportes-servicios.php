<?php

require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$pdo = getPDO();

$periodo = $_GET['periodo'] ?? 'mes';
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

switch ($periodo) {
    case 'dia':
        $fechaInicio = $fechaFin = date('Y-m-d');
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        $fechaFin = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        $fechaFin = date('Y-m-t');
        break;
    case 'anio':
        $fechaInicio = date('Y-01-01');
        $fechaFin = date('Y-12-31');
        break;
    case 'rango':
        break;
}

$tipo = $_GET['tipo'] ?? 'resumen';

$tiposValidos = ['resumen', 'retiros_diarios', 'detalle_dia', 'todos_movimientos', 'diario_servicio', 'lista_servicios', 'operaciones', 'detalle_operacion'];
if (!in_array($tipo, $tiposValidos)) {
    echo json_encode(['error' => 'Tipo de reporte no válido']);
    exit;
}

$periodosValidos = ['dia', 'semana', 'mes', 'anio', 'rango'];
if (!in_array($periodo, $periodosValidos)) {
    echo json_encode(['error' => 'Período no válido']);
    exit;
}

try {
    switch ($tipo) {

        case 'resumen':
            $resultado = [];

            // Obtener todos los movimientos de servicios != 1
            // Para cada operación (mismo grupo_id) nos quedamos con tipo='entrada' si existe
            $stmt = $pdo->prepare("
                SELECT m.servicio_id, s.nombre as servicio,
                       COALESCE(NULLIF(m.grupo_id, 0), -m.id) as operation_key,
                       m.tipo, m.monto, m.moneda
                FROM movimientos m
                INNER JOIN servicios s ON m.servicio_id = s.id
                WHERE m.servicio_id != 1
                  AND m.fecha BETWEEN ? AND ? AND m.activo = 1
                  AND m.tipo != 'ajuste'
                  AND (m.estado IS NULL OR m.estado = 'activo')
                ORDER BY m.servicio_id, operation_key,
                         FIELD(m.tipo, 'entrada', 'salida', 'traspaso', 'comision', 'ajuste')
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por servicio + operation_key
            // Para cada operación: preferir divisa (USD/USDT) sobre MXN;
            // si ambas son MXN o ambas divisa, priorizar 'entrada'
            $ops = [];
            $mxnPorOp = []; // operation_key -> suma de MXN para esa operación
            foreach ($rows as $r) {
                $key = $r['servicio_id'] . '_' . $r['operation_key'];
                // Acumular contraparte MXN para cada operación
                if ($r['moneda'] === 'MXN' && $r['tipo'] !== 'comision') {
                    if (!isset($mxnPorOp[$key])) {
                        $mxnPorOp[$key] = 0;
                    }
                    $mxnPorOp[$key] += abs((float)$r['monto']);
                }
                // Elegir movimiento representativo de la operación
                if (!isset($ops[$key])) {
                    $ops[$key] = $r;
                } else {
                    $cur = $r['moneda'];
                    $oldCur = $ops[$key]['moneda'];
                    $isDivisa = in_array($cur, ['USD', 'USDT']);
                    $oldIsDivisa = in_array($oldCur, ['USD', 'USDT']);
                    if ($isDivisa && !$oldIsDivisa) {
                        $ops[$key] = $r; // divisa vence a MXN
                    } elseif (!$isDivisa && $oldIsDivisa) {
                        // conservar divisa
                    } elseif ($r['tipo'] === 'entrada' && $ops[$key]['tipo'] !== 'entrada') {
                        $ops[$key] = $r; // misma moneda: preferir entrada
                    }
                }
            }

            // Sumar por servicio
            $serviciosMap = [];
            foreach ($ops as $key => $op) {
                $sid = $op['servicio_id'];
                if (!isset($serviciosMap[$sid])) {
                    $serviciosMap[$sid] = ['id' => $sid, 'servicio' => $op['servicio'], 'total' => 0, 'total_mxn' => 0, 'cantidad' => 0];
                }
                $serviciosMap[$sid]['total'] += (float)$op['monto'];
                $serviciosMap[$sid]['total_mxn'] += (float)($mxnPorOp[$key] ?? 0);
                $serviciosMap[$sid]['cantidad']++;
            }

            $servicios = array_values($serviciosMap);

            // Obtener ganancia de consumos_inventario (servicios 2 y 3: Venta de Dolares/USDT)
            $stmtGanancia = $pdo->prepare("
                SELECT mv.servicio_id, COALESCE(SUM(ci.ganancia), 0) as ganancia
                FROM consumos_inventario ci
                JOIN movimientos mv ON ci.movimiento_id = mv.id
                JOIN inventario_monedas im ON ci.inventario_id = im.id
                WHERE mv.fecha BETWEEN ? AND ? AND mv.activo = 1
                GROUP BY mv.servicio_id
            ");
            $stmtGanancia->execute([$fechaInicio, $fechaFin]);
            $gananciaPorServicio = [];
            foreach ($stmtGanancia->fetchAll(PDO::FETCH_ASSOC) as $g) {
                $gananciaPorServicio[$g['servicio_id']] = (float)$g['ganancia'];
            }
            foreach ($servicios as &$s) {
                $s['ganancia'] = $gananciaPorServicio[$s['id']] ?? 0;
            }
            unset($s);

            // Venta de Efectivo (servicio 1): usar deudas_clientes como fuente canónica
            $stmtDc = $pdo->prepare("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto_total), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmtDc->execute([$fechaInicio, $fechaFin]);
            $dc = $stmtDc->fetch(PDO::FETCH_ASSOC);
            if ($dc && $dc['cantidad'] > 0) {
                $totalMxn = (float)$dc['total'];
                $servicios[] = [
                    'id' => 1,
                    'servicio' => 'Venta de Efectivo',
                    'total' => $totalMxn,
                    'total_mxn' => $totalMxn,
                    'cantidad' => (int)$dc['cantidad'],
                    'ganancia' => 0
                ];
            }

            // Reordenar por nombre
            usort($servicios, function ($a, $b) { return strcmp($a['servicio'], $b['servicio']); });

            $resultado['servicios'] = $servicios;
            echo json_encode($resultado);
            break;

        case 'diario_servicio':
            $servicioId = (int)($_GET['servicio_id'] ?? 1);

            if ($servicioId === 1) {
                // Venta de Efectivo: usa deudas_clientes (1 operación = 1 registro)
                $sql = "SELECT DAY(fecha) as dia, COUNT(*) as cantidad,
                               COALESCE(SUM(monto_total), 0) as total
                        FROM deudas_clientes
                        WHERE fecha BETWEEN ? AND ? AND activo = 1
                        GROUP BY DAY(fecha) ORDER BY dia";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fechaInicio, $fechaFin]);
            } else {
                // Demás servicios: obtener movimientos y agrupar por operation_key en PHP
                $sql = "SELECT m.id, m.grupo_id, m.fecha, DAY(m.fecha) as dia,
                               m.monto, m.moneda, m.tipo
                        FROM movimientos m
                        WHERE m.servicio_id = ? AND m.fecha BETWEEN ? AND ?
                          AND m.activo = 1 AND m.tipo != 'ajuste'
                        ORDER BY m.fecha, COALESCE(NULLIF(m.grupo_id, 0), -m.id),
                                 FIELD(m.moneda, 'USDT', 'USD', 'MXN'),
                                 FIELD(m.tipo, 'entrada', 'salida', 'traspaso', 'comision', 'ajuste')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$servicioId, $fechaInicio, $fechaFin]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Agrupar por operation_key (mismo grupo_id = misma operación),
                // preferir divisa (USD/USDT) sobre MXN; misma moneda → preferir entrada
                $ops = [];
                foreach ($rows as $r) {
                    $key = $r['grupo_id'] ?? 's_' . $r['id'];
                    if (!isset($ops[$key])) {
                        $ops[$key] = $r;
                    } else {
                        $cur = $r['moneda'];
                        $oldCur = $ops[$key]['moneda'];
                        $isDivisa = in_array($cur, ['USD', 'USDT']);
                        $oldIsDivisa = in_array($oldCur, ['USD', 'USDT']);
                        if ($isDivisa && !$oldIsDivisa) {
                            $ops[$key] = $r;
                        } elseif (!$isDivisa && $oldIsDivisa) {
                            // conservar divisa
                        } elseif ($r['tipo'] === 'entrada' && $ops[$key]['tipo'] !== 'entrada') {
                            $ops[$key] = $r;
                        }
                    }
                }

                // Sumar por día
                $diario = [];
                foreach ($ops as $op) {
                    $d = (int)$op['dia'];
                    if (!isset($diario[$d])) {
                        $diario[$d] = ['dia' => $d, 'cantidad' => 0, 'total' => 0];
                    }
                    $diario[$d]['cantidad']++;
                    $diario[$d]['total'] += (float)$op['monto'];
                }
                echo json_encode(array_values($diario));
                break;
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'retiros_diarios':
            // Mantener compatibilidad: redirigir a diario_servicio con valores por defecto
            $_GET['servicio_id'] = 7;
            $_GET['tipo_mov'] = 'traspaso';
            // fall through

        case 'detalle_dia':
            $dia = $_GET['dia'] ?? date('d');
            $servicioId = (int)($_GET['servicio_id'] ?? 1);
            $tipoMov = $_GET['tipo_mov'] ?? '';

            $whereTipo = '';
            if ($tipoMov !== '') {
                $whereTipo = "AND m.tipo = " . $pdo->quote($tipoMov);
            }

            $sql = "SELECT m.id, m.creado_en, m.monto, m.descripcion, m.tipo, m.referencia_tipo, m.beneficiario, COALESCE(c.nombre, '-') as cliente, COALESCE(p.nombre, '-') as promotor, COALESCE(pr.nombre, '-') as proveedor, COALESCE(ps.nombre, '-') as prestamista FROM movimientos m LEFT JOIN clientes c ON m.cliente_id = c.id LEFT JOIN promotores p ON m.promotor_id = p.id LEFT JOIN proveedores pr ON m.proveedor_id = pr.id LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id WHERE m.servicio_id = ? AND m.fecha BETWEEN ? AND ? AND DAY(m.fecha) = ? AND m.activo = 1 $whereTipo ORDER BY m.creado_en DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$servicioId, $fechaInicio, $fechaFin, (int)$dia]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'todos_movimientos':
            $tipoOperacion = $_GET['tipo_operacion'] ?? 'todos';
            $servicioId = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : null;

            $whereExtra = '';
            if ($tipoOperacion === 'retiros') {
                $whereExtra = "AND m.servicio_id = 7 AND m.tipo = 'traspaso'";
            } elseif ($tipoOperacion === 'depositos') {
                $whereExtra = "AND m.servicio_id IN (1, 2, 3)";
            } elseif ($tipoOperacion === 'transferencias') {
                $whereExtra = "AND m.servicio_id IN (11, 13, 14, 15)";
            }
            if ($servicioId) {
                $whereExtra .= " AND m.servicio_id = $servicioId";
            }

            $sql = "SELECT m.creado_en, m.id, m.fecha, s.nombre as servicio, m.tipo, m.moneda, m.monto, m.paridad, m.descripcion, COALESCE(c.nombre, '-') as cliente, COALESCE(p.nombre, '-') as promotor, COALESCE(pr.nombre, '-') as proveedor, COALESCE(ps.nombre, '-') as prestamista FROM movimientos m JOIN servicios s ON m.servicio_id = s.id LEFT JOIN clientes c ON m.cliente_id = c.id LEFT JOIN promotores p ON m.promotor_id = p.id LEFT JOIN proveedores pr ON m.proveedor_id = pr.id LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id WHERE m.fecha BETWEEN ? AND ? AND m.activo = 1 AND (m.estado IS NULL OR m.estado = 'activo') $whereExtra ORDER BY m.fecha DESC, m.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'operaciones':
            $servicioId = (int)($_GET['servicio_id'] ?? 0);
            if (!$servicioId) {
                echo json_encode([]);
                break;
            }

            if ($servicioId === 1) {
                // Venta de Efectivo: 1 operación = 1 registro en deudas_clientes
                $sql = "SELECT dc.id, dc.fecha, dc.monto_total, dc.monto_neto_cliente,
                               dc.comision_amiri, dc.comision_promotor, dc.moneda,
                               COALESCE(c.nombre, '-') as cliente,
                               dc.estado, dc.monto_pagado
                        FROM deudas_clientes dc
                        LEFT JOIN clientes c ON dc.cliente_id = c.id
                        WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                        ORDER BY dc.fecha DESC, dc.id DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fechaInicio, $fechaFin]);
            } else {
                // Demás servicios: agrupar por grupo_id, mostrar la entrada si existe
                $sql = "SELECT m.id, m.grupo_id, m.fecha, m.monto, m.moneda, m.tipo, m.descripcion,
                               COALESCE(c.nombre, '-') as cliente,
                               COALESCE(p.nombre, '-') as promotor,
                               COALESCE(pr.nombre, '-') as proveedor,
                               COALESCE(co.nombre, '-') as cuenta_origen,
                               COALESCE(cd.nombre, '-') as cuenta_destino
                        FROM movimientos m
                        LEFT JOIN clientes c ON m.cliente_id = c.id
                        LEFT JOIN promotores p ON m.promotor_id = p.id
                        LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
                        LEFT JOIN cuentas co ON m.cuenta_origen_id = co.id
                        LEFT JOIN cuentas cd ON m.cuenta_destino_id = cd.id
                        WHERE m.servicio_id = ? AND m.fecha BETWEEN ? AND ?
                          AND m.activo = 1 AND m.tipo != 'ajuste'
                          AND (m.estado IS NULL OR m.estado = 'activo')
                        ORDER BY m.fecha DESC, m.id DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$servicioId, $fechaInicio, $fechaFin]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Agrupar por grupo_id: preferir divisa (USD/USDT) sobre MXN;
                // si ambas son MXN o ambas divisa, priorizar 'entrada'
                $groups = [];
                $mxnPorGrupo = [];
                foreach ($rows as $r) {
                    $key = $r['grupo_id'] ?? 'standalone_' . $r['id'];
                    // Acumular contraparte MXN
                    if ($r['moneda'] === 'MXN' && $r['tipo'] !== 'comision') {
                        if (!isset($mxnPorGrupo[$key])) {
                            $mxnPorGrupo[$key] = 0;
                        }
                        $mxnPorGrupo[$key] += abs((float)$r['monto']);
                    }
                    // Elegir movimiento representativo
                    if (!isset($groups[$key])) {
                        $groups[$key] = $r;
                    } else {
                        $cur = $r['moneda'];
                        $oldCur = $groups[$key]['moneda'];
                        $isDivisa = in_array($cur, ['USD', 'USDT']);
                        $oldIsDivisa = in_array($oldCur, ['USD', 'USDT']);
                        if ($isDivisa && !$oldIsDivisa) {
                            $groups[$key] = $r;
                        } elseif (!$isDivisa && $oldIsDivisa) {
                            // conservar divisa
                        } elseif ($r['tipo'] === 'entrada' && $groups[$key]['tipo'] !== 'entrada') {
                            $groups[$key] = $r;
                        }
                    }
                }
                // Agregar monto_mxn a cada grupo
                foreach ($groups as $key => &$g) {
                    $g['monto_mxn'] = $mxnPorGrupo[$key] ?? 0;
                }
                echo json_encode(array_values($groups));
                break;
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'detalle_operacion':
            $servicioId = (int)($_GET['servicio_id'] ?? 0);
            $operacionId = (int)($_GET['operacion_id'] ?? 0);
            if (!$servicioId || !$operacionId) {
                echo json_encode(['movimientos' => []]);
                break;
            }

            if ($servicioId === 1) {
                // Obtener datos de la operación desde deudas_clientes
                $stmtDeuda = $pdo->prepare("
                    SELECT dc.id, dc.movimiento_venta_id, dc.monto_total, dc.monto_neto_cliente,
                           dc.comision_amiri, dc.comision_promotor, dc.moneda,
                           COALESCE(c.nombre, '-') as cliente,
                           dc.estado, dc.monto_pagado, dc.fecha
                    FROM deudas_clientes dc
                    LEFT JOIN clientes c ON dc.cliente_id = c.id
                    WHERE dc.id = ? AND dc.activo = 1
                ");
                $stmtDeuda->execute([$operacionId]);
                $deuda = $stmtDeuda->fetch(PDO::FETCH_ASSOC);
                if (!$deuda || !$deuda['movimiento_venta_id']) {
                    echo json_encode(['operacion' => $deuda ?: null, 'movimientos' => []]);
                    break;
                }

                // Traer movimientos del grupo
                $stmt = $pdo->prepare("
                    SELECT m.id, m.creado_en, m.tipo, m.monto, m.moneda, m.descripcion,
                           COALESCE(c.nombre, '-') as cliente,
                           COALESCE(p.nombre, '-') as promotor,
                           COALESCE(pr.nombre, '-') as proveedor,
                           COALESCE(ps.nombre, '-') as prestamista
                    FROM movimientos m
                    LEFT JOIN clientes c ON m.cliente_id = c.id
                    LEFT JOIN promotores p ON m.promotor_id = p.id
                    LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
                    LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id
                    WHERE m.grupo_id = ? AND m.activo = 1
                    ORDER BY m.id
                ");
                $stmt->execute([$deuda['movimiento_venta_id']]);
                echo json_encode([
                    'operacion' => $deuda,
                    'movimientos' => $stmt->fetchAll(PDO::FETCH_ASSOC)
                ]);
            } else {
                // Otros servicios: traer movimientos del mismo grupo
                $stmt = $pdo->prepare("SELECT grupo_id FROM movimientos WHERE id = ? AND activo = 1");
                $stmt->execute([$operacionId]);
                $mov = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$mov || !$mov['grupo_id']) {
                    $stmt2 = $pdo->prepare("
                        SELECT id, creado_en, tipo, monto, moneda, descripcion,
                               COALESCE(c.nombre, '-') as cliente,
                               COALESCE(p.nombre, '-') as promotor,
                               COALESCE(pr.nombre, '-') as proveedor,
                               COALESCE(ps.nombre, '-') as prestamista
                        FROM movimientos m
                        LEFT JOIN clientes c ON m.cliente_id = c.id
                        LEFT JOIN promotores p ON m.promotor_id = p.id
                        LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
                        LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id
                        WHERE m.id = ?
                    ");
                    $stmt2->execute([$operacionId]);
                    $movs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                    $montoDivisa = 0; $montoMxn = 0; $ganancia = 0; $monedaDivisa = '';
                    foreach ($movs as $m) {
                        $mon = $m['moneda'] ?? ''; $mont = abs((float)$m['monto']);
                        if (in_array($mon, ['USD', 'USDT'])) { $montoDivisa += $mont; $monedaDivisa = $mon; }
                        elseif ($mon === 'MXN' && $m['tipo'] !== 'comision') $montoMxn += $mont;
                        if (in_array($servicioId, [2, 3]) && ($m['tipo'] ?? '') === 'comision' && $mont > 0) $ganancia += $mont;
                    }
                    echo json_encode([
                        'movimientos' => $movs,
                        'monto_divisa' => $montoDivisa,
                        'moneda_divisa' => $monedaDivisa,
                        'monto_mxn' => $montoMxn,
                        'tasa' => $montoDivisa > 0 ? round($montoMxn / $montoDivisa, 4) : 0,
                        'ganancia' => $ganancia ?: 0
                    ]);
                    break;
                }
                $sql = "SELECT m.id, m.creado_en, m.tipo, m.monto, m.moneda, m.descripcion,
                               COALESCE(c.nombre, '-') as cliente,
                               COALESCE(p.nombre, '-') as promotor,
                               COALESCE(pr.nombre, '-') as proveedor,
                               COALESCE(ps.nombre, '-') as prestamista
                        FROM movimientos m
                        LEFT JOIN clientes c ON m.cliente_id = c.id
                        LEFT JOIN promotores p ON m.promotor_id = p.id
                        LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
                        LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id
                        WHERE m.grupo_id = ? AND m.activo = 1
                        ORDER BY m.id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$mov['grupo_id']]);
                $movs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $montoDivisa = 0; $montoMxn = 0; $ganancia = 0; $monedaDivisa = '';
                foreach ($movs as $m) {
                    $mon = $m['moneda'] ?? ''; $mont = abs((float)$m['monto']);
                    if (in_array($mon, ['USD', 'USDT'])) { $montoDivisa += $mont; $monedaDivisa = $mon; }
                    elseif ($mon === 'MXN' && $m['tipo'] !== 'comision') $montoMxn += $mont;
                    if (in_array($servicioId, [2, 3]) && ($m['tipo'] ?? '') === 'comision' && $mont > 0) $ganancia += $mont;
                }
                echo json_encode([
                    'movimientos' => $movs,
                    'monto_divisa' => $montoDivisa,
                    'moneda_divisa' => $monedaDivisa,
                    'monto_mxn' => $montoMxn,
                    'tasa' => $montoDivisa > 0 ? round($montoMxn / $montoDivisa, 4) : 0,
                    'ganancia' => $ganancia ?: 0
                ]);
            }
            break;

        case 'lista_servicios':
            $stmt = $pdo->query("
                SELECT DISTINCT m.servicio_id as id, s.nombre as servicio
                FROM movimientos m
                JOIN servicios s ON m.servicio_id = s.id
                WHERE m.activo = 1 AND (m.estado IS NULL OR m.estado = 'activo')
                ORDER BY s.nombre
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        default:
            echo json_encode(['error' => 'Tipo de reporte no válido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
