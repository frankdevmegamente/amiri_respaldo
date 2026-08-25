<?php
date_default_timezone_set('America/Mexico_City');
header('Content-Type: application/json');

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/configuracion.php';

$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Sistema';

$tipo = $_GET['tipo'] ?? '';

// Resolve date range parameters
$anioParam = $_GET['anio'] ?? null;
$fechaInicio = $_GET['fecha_desde'] ?? $_GET['fecha_inicio'] ?? null;
$fechaFin = $_GET['fecha_hasta'] ?? $_GET['fecha_fin'] ?? null;
$mesParam = $_GET['mes'] ?? null;

// For resumen: determine fInicio/fFin based on params
$fInicio = null;
$fFin = null;

if ($fechaInicio && $fechaFin) {
    // Range filter
    $fInicio = $fechaInicio;
    $fFin = $fechaFin;
    $mesActual = substr($fechaInicio, 0, 7);
} elseif ($anioParam && !$mesParam) {
    // Year filter (no specific month)
    $fInicio = $anioParam . '-01-01';
    $fFin = $anioParam . '-12-31';
    $mesActual = null;
} else {
    // Single month filter (mes=YYYY-MM or mes=MM + anio=YYYY)
    if ($anioParam && $mesParam) {
        $mesActual = $anioParam . '-' . str_pad($mesParam, 2, '0', STR_PAD_LEFT);
    } elseif ($mesParam && preg_match('/^\d{4}-\d{2}$/', $mesParam)) {
        $mesActual = $mesParam;
    } else {
        $mesActual = date('Y-m');
    }
    $fInicio = $mesActual . '-01';
    $fFin = date('Y-m-t', strtotime($fInicio));
}

try {
    $pdo = getPDO();

    // ==================== MESES DISPONIBLES PARA CERRAR ====================
    if ($tipo === 'meses_disponibles') {
        $stmt = $pdo->query("
            SELECT DISTINCT m FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') as m FROM deudas_clientes WHERE activo = 1
                UNION
                SELECT DATE_FORMAT(fecha, '%Y-%m') FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND activo = 1 AND estado = 'activo'
                UNION
                SELECT DATE_FORMAT(fecha, '%Y-%m') FROM gastos WHERE activo = 1
            ) meses ORDER BY m ASC
        ");
        $todos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cerrados = $pdo->query("SELECT mes FROM cierres_mensuales")->fetchAll(PDO::FETCH_COLUMN);
        $disponibles = array_values(array_diff($todos, $cerrados));
        echo json_encode(['meses' => $disponibles, 'cerrados' => $cerrados]);
        exit;
    }

    // ==================== RESUMEN ====================
    if ($tipo === 'resumen') {
        $fechaIni = getFechaInicializacion($pdo);

        $mesCerrado = false;
        $corteDelMes = null;

        if ($mesActual) {
            // Single month: check if it's closed
            $stmtCorte = $pdo->prepare("SELECT * FROM cierres_mensuales WHERE mes = ?");
            $stmtCorte->execute([$mesActual]);
            $corteDelMes = $stmtCorte->fetch(PDO::FETCH_ASSOC);
            $mesCerrado = (bool)$corteDelMes;
        }

        if ($mesCerrado && $corteDelMes) {
            // Use closed data
            $comisiones = (float)$corteDelMes['comisiones_amiri'];
            $divisas = (float)$corteDelMes['ganancia_divisas'];
            $gastos = [
                'total' => (float)$corteDelMes['gastos_total'],
                'fijos' => (float)$corteDelMes['gastos_fijos'],
                'variables' => (float)$corteDelMes['gastos_variables'],
            ];
            $utilidadMes = (float)$corteDelMes['utilidad_neta'];
        } else {
            // Calculate live using fInicio/fFin

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fInicio, $fFin]);
            $comisiones = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
            $stmt->execute([$fInicio, $fFin]);
            $divisas = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fInicio, $fFin]);
            $gastos = $stmt->fetch(PDO::FETCH_ASSOC);

            $utilidadMes = $comisiones + $divisas - (float)$gastos['total'];
        }

        // Último corte (para capital acumulado)
        $stmt = $pdo->query("SELECT * FROM cierres_mensuales ORDER BY mes DESC LIMIT 1");
        $ultimoCorte = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM retiros_capital");
        $totalRetiros = (float)$stmt->fetchColumn();

        $capitalCerrado = $ultimoCorte ? (float)$ultimoCorte['capital_acumulado'] : 0;

        $stmtUtil = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1");
        $utilidadesAcumuladas = (float)$stmtUtil->fetchColumn();

        $stmtUtil3 = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1");
        $utilidadesAmiriSaldo = (float)$stmtUtil3->fetchColumn();

        $stmtCapAmiri = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1");
        $capAmiriRow = $stmtCapAmiri->fetch(PDO::FETCH_ASSOC);
        $capitalAmiriId = $capAmiriRow ? (int)$capAmiriRow['id'] : null;
        $capitalAmiriSaldo = $capAmiriRow ? (float)$capAmiriRow['saldo_actual'] : 0;

        $capitalActual = $capitalAmiriSaldo;

        // Capital anterior = capital at end of period before this one
        if ($mesActual) {
            $mesAnterior = date('Y-m', strtotime($mesActual . '-01 -1 month'));
            $stmtAnt = $pdo->prepare("SELECT capital_acumulado FROM cierres_mensuales WHERE mes = ?");
            $stmtAnt->execute([$mesAnterior]);
            $capAnterior = $stmtAnt->fetchColumn();
            if ($capAnterior === false) {
                if ($corteDelMes) {
                    $capAnterior = (float)$corteDelMes['capital_acumulado'] - (float)$corteDelMes['utilidad_neta'];
                } else {
                    $capAnterior = 0;
                }
            } else {
                $capAnterior = (float)$capAnterior;
            }
        } else {
            // Year or range: find last closed corte before fInicio
            $stmtAnt = $pdo->prepare("SELECT capital_acumulado FROM cierres_mensuales WHERE mes < ? ORDER BY mes DESC LIMIT 1");
            $stmtAnt->execute([$fInicio]);
            $capAnterior = $stmtAnt->fetchColumn();
            $capAnterior = $capAnterior !== false ? (float)$capAnterior : 0;
        }

        // Detectar si falta corte inicial o está vacío
        $needsCorteUpdate = false;
        if ($capitalAmiriSaldo > 0.01 && $mesActual) {
            if ($ultimoCorte && $ultimoCorte['mes'] === $mesActual) {
                $comisionesCorte = (float)$ultimoCorte['comisiones_amiri'];
                $divisasCorte = (float)$ultimoCorte['ganancia_divisas'];
                if ($comisionesCorte < 0.01 && $divisasCorte < 0.01) {
                    $needsCorteUpdate = true;
                }
            } elseif (!$ultimoCorte) {
                $needsCorteUpdate = true;
            }
        }

        echo json_encode([
            'utilidad_mes' => $utilidadMes,
            'comisiones_amiri' => $comisiones,
            'ganancia_divisas' => $divisas,
            'gastos_fijos' => (float)$gastos['fijos'],
            'gastos_variables' => (float)$gastos['variables'],
            'gastos_total' => (float)$gastos['total'],
            'total_retiros' => $totalRetiros,
            'mes_actual' => $mesActual,
            'mes_cerrado' => $mesCerrado,
            'ultimo_corte' => $ultimoCorte ? $ultimoCorte['mes'] : null,
            'datos_corte' => $corteDelMes,
            'capital_actual' => $capitalActual,
            'capital_cerrado' => $capitalCerrado,
            'capital_anterior' => $capAnterior,
            'utilidades_acumuladas' => $utilidadesAcumuladas,
            'utilidades_amiri_saldo' => $utilidadesAmiriSaldo,
            'puede_inicializar' => ($capitalAmiriSaldo < 0.01) || $needsCorteUpdate,
            'needs_corte_update' => $needsCorteUpdate,
            'needs_corte_crear' => $needsCorteUpdate && !$ultimoCorte,
            'fecha_inicializacion' => $fechaIni,
        ]);
        exit;
    }

    // ==================== CIERRES ====================
    if ($tipo === 'cierres') {
        $stmt = $pdo->query("SELECT * FROM cierres_mensuales ORDER BY mes DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ==================== RETIROS ====================
    if ($tipo === 'retiros') {
        $stmt = $pdo->query("SELECT r.*, c.nombre AS cuenta_nombre FROM retiros_capital r LEFT JOIN cuentas c ON r.cuenta_id = c.id ORDER BY r.fecha DESC, r.id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ==================== HISTORIAL CAPITAL ====================
    if ($tipo === 'historial') {
        $stmt = $pdo->query("SELECT * FROM historial_saldos hs JOIN movimientos m ON hs.movimiento_id = m.id WHERE hs.cuenta_id = (SELECT id FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1) ORDER BY m.fecha DESC LIMIT 50");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ==================== GASTOS DEL MES ====================
    if ($tipo === 'gastos_mes') {
        $mes = $_GET['mes'] ?? date('Y-m');
        $fInicio = $mes . '-01';
        $fFin = date('Y-m-t', strtotime($fInicio));
        $stmt = $pdo->prepare("
            SELECT g.*, c.nombre as categoria_nombre
            FROM gastos g
            LEFT JOIN categorias_gasto c ON g.categoria_id = c.id
            WHERE g.fecha BETWEEN ? AND ? AND g.activo = 1
            ORDER BY g.fecha DESC, g.id DESC
        ");
        $stmt->execute([$fInicio, $fFin]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // ==================== DETALLE DEL MES (para modal explicativo) ====================
    if ($tipo === 'detalle_mes') {
        $mes = $_GET['mes'] ?? date('Y-m');
        $fInicio = $mes . '-01';
        $fFin = date('Y-m-t', strtotime($fInicio));

        $stmt = $pdo->prepare("SELECT dc.id, dc.fecha, cl.nombre as cliente, dc.monto_neto_cliente as total, dc.comision_amiri, dc.moneda, dc.estado FROM deudas_clientes dc LEFT JOIN clientes cl ON dc.cliente_id=cl.id WHERE dc.fecha BETWEEN ? AND ? AND dc.activo=1 AND dc.comision_amiri > 0 ORDER BY dc.fecha, dc.id");
        $stmt->execute([$fInicio, $fFin]);
        $comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT m.id, m.fecha, m.monto, m.descripcion FROM movimientos m WHERE m.servicio_id IN (2,3) AND m.tipo = 'comision' AND m.cuenta_destino_id = 1 AND m.fecha BETWEEN ? AND ? AND m.activo = 1 AND m.estado = 'activo' ORDER BY m.fecha");
        $stmt->execute([$fInicio, $fFin]);
        $divisas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['comisiones' => $comisiones, 'divisas' => $divisas, 'mes' => $mes]);
        exit;
    }

    // ==================== CERRAR MES ====================
    if ($tipo === 'cerrar_mes') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $mes = $_POST['mes'] ?? date('Y-m', strtotime('-1 month'));
        list($anio, $mesNum) = explode('-', $mes);
        $fInicio = "$anio-$mesNum-01";
        $fFin = date('Y-m-t', strtotime($fInicio));

        // Verificar que no esté ya cerrado
        $stmt = $pdo->prepare("SELECT id FROM cierres_mensuales WHERE mes = ?");
        $stmt->execute([$mes]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => "El mes $mes ya está cerrado"]);
            exit;
        }

        // Calcular métricas del mes
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fInicio, $fFin]);
        $comisiones = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
        $stmt->execute([$fInicio, $fFin]);
        $divisas = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fInicio, $fFin]);
        $gastos = $stmt->fetch(PDO::FETCH_ASSOC);

        $utilidad = $comisiones + $divisas - (float)$gastos['total'];

        // Capital acumulado = suma de utilidades de meses anteriores cronológicamente + esta utilidad
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(utilidad_neta), 0) FROM cierres_mensuales WHERE mes < ?");
        $stmt->execute([$mes]);
        $capitalPrev = (float)$stmt->fetchColumn();
        // Si no hay cortes previos pero Capital Amiri ya tiene saldo (inicialización), usarlo como base
        if ($capitalPrev < 0.01) {
            $stmtCapitalBase = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1");
            $capitalPrev = (float)$stmtCapitalBase->fetchColumn();
        }
        $capitalAcumulado = $capitalPrev + $utilidad;

        // Insertar cierre
        $stmt = $pdo->prepare("INSERT INTO cierres_mensuales (mes, fecha_cierre, comisiones_amiri, ganancia_divisas, gastos_fijos, gastos_variables, gastos_total, utilidad_neta, capital_acumulado) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$mes, $comisiones, $divisas, (float)$gastos['fijos'], (float)$gastos['variables'], (float)$gastos['total'], $utilidad, $capitalAcumulado]);

        $idCierre = $pdo->lastInsertId();

        // Recalcular capital_acumulado de todos los cierres en orden cronológico
        $stmtAll = $pdo->query("SELECT id, utilidad_neta FROM cierres_mensuales ORDER BY mes ASC");
        $running = 0;
        $stmtUpd = $pdo->prepare("UPDATE cierres_mensuales SET capital_acumulado = ? WHERE id = ?");
        while ($row = $stmtAll->fetch()) {
            $running += (float)$row['utilidad_neta'];
            $stmtUpd->execute([$running, $row['id']]);
        }
        $capitalAcumulado = $running;
        if (function_exists('registrarAuditoria')) {
            registrarAuditoria($pdo, $usuario_id, 'capital_cerrar_mes', "Cierre del mes $mes", json_encode(['id' => $idCierre, 'utilidad' => $utilidad, 'capital' => $capitalAcumulado]));
        }

        // Restar retiros de capital_acumulado para evitar double-counting en la diferencia
        $stmtTotalRetiros = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM retiros_capital");
        $totalRetiros = (float)$stmtTotalRetiros->fetchColumn();
        $capitalEsperado = $capitalAcumulado - $totalRetiros;

        // Sincronizar cuenta de Capital Amiri con capital_acumulado (descontando retiros)
        $stmtCap = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1");
        $capitalRow = $stmtCap->fetch(PDO::FETCH_ASSOC);
        if ($capitalRow) {
            $capitalId = (int)$capitalRow['id'];
            $saldoCapAnt = (float)$capitalRow['saldo_actual'];
            $diferencia = round($capitalEsperado - $saldoCapAnt, 2);
            if (abs($diferencia) > 0.01) {
                $grupoId = round(microtime(true) * 1000);
                $tipoMov = $diferencia > 0 ? 'entrada' : 'salida';
                $desc = "Ajuste por cierre de mes $mes";
                $montoAbs = abs($diferencia);
                $origenId = $tipoMov === 'entrada' ? null : $capitalId;
                $destinoId = $tipoMov === 'entrada' ? $capitalId : null;
                $stmtMov = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), ?, ?, 'MXN', ?, ?, ?, ?, 19, 1)");
                $stmtMov->execute([$grupoId, $tipoMov, $montoAbs, $desc, $origenId, $destinoId, $usuario_id]);
                $movId = $pdo->lastInsertId();
                $pdo->exec("SET @hyperion_saldo_auth = 1");
                $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$capitalEsperado, $capitalId]);
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, ?, ?, ?, ?)")->execute([$capitalId, $movId, $tipoMov, $montoAbs, $saldoCapAnt, $capitalEsperado]);

                // También sincronizar Utilidades (tipo_cuenta = 3)
                $stmtUtil = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");
                $utilRow = $stmtUtil->fetch(PDO::FETCH_ASSOC);
                if ($utilRow) {
                    $utilId = (int)$utilRow['id'];
                    $saldoUtilAnt = (float)$utilRow['saldo_actual'];
                    $movTipoUtil = $tipoMov === 'entrada' ? 'salida' : 'entrada';
                    $utilOrigen = $movTipoUtil === 'entrada' ? null : $utilId;
                    $utilDestino = $movTipoUtil === 'entrada' ? $utilId : null;
                    $stmtMovUtil = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), ?, ?, 'MXN', ?, ?, ?, ?, 19, 1)");
                    $stmtMovUtil->execute([$grupoId, $movTipoUtil, $montoAbs, $desc, $utilOrigen, $utilDestino, $usuario_id]);
                    $movUtilId = $pdo->lastInsertId();
                    $saldoUtilNuevo = $movTipoUtil === 'entrada' ? $saldoUtilAnt + $montoAbs : $saldoUtilAnt - $montoAbs;
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldoUtilNuevo, $utilId]);
                    $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, ?, ?, ?, ?)")->execute([$utilId, $movUtilId, $movTipoUtil, $montoAbs, $saldoUtilAnt, $saldoUtilNuevo]);
                }
            }
        }

        // Absorber centavos residuales (< $1) en tipo_cuenta=3
        $stmtRes = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1");
        $residual = round((float)$stmtRes->fetchColumn(), 2);
        if ($residual > 0.001 && $residual < 1.00) {
            // Obtener IDs de cuentas
            $stmtU = $pdo->query("SELECT id FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");
            $utilRowRes = $stmtU->fetch(PDO::FETCH_ASSOC);
            $stmtC = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1");
            $capRowRes = $stmtC->fetch(PDO::FETCH_ASSOC);
            if ($utilRowRes && $capRowRes) {
                $utilIdRes = (int)$utilRowRes['id'];
                $capIdRes = (int)$capRowRes['id'];
                $capSaldoAntRes = (float)$capRowRes['saldo_actual'];
                $capSaldoNuevoRes = round($capSaldoAntRes + $residual, 2);

                // Actualizar el cierre recién creado
                $pdo->prepare("UPDATE cierres_mensuales SET utilidad_neta = utilidad_neta + ?, capital_acumulado = capital_acumulado + ? WHERE id = ?")
                    ->execute([$residual, $residual, $idCierre]);
                $utilidad += $residual;
                $capitalAcumulado += $residual;

                $grupoRes = round(microtime(true) * 1000);
                $descRes = "Ajuste por absorción de centavos residuales en cierre de mes $mes";

                // Salida de tipo_cuenta=3
                $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), 'salida', ?, 'MXN', ?, ?, NULL, ?, 19, 1)")
                    ->execute([$grupoRes, $residual, $descRes, $utilIdRes, $usuario_id]);
                $movRes1Id = $pdo->lastInsertId();

                // Entrada a Capital Amiri
                $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), 'entrada', ?, 'MXN', ?, NULL, ?, ?, 19, 1)")
                    ->execute([$grupoRes, $residual, $descRes, $capIdRes, $usuario_id]);
                $movRes2Id = $pdo->lastInsertId();

                // Actualizar saldos
                $pdo->exec("SET @hyperion_saldo_auth = 1");
                $pdo->prepare("UPDATE cuentas SET saldo_actual = 0 WHERE id = ?")->execute([$utilIdRes]);
                $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$capSaldoNuevoRes, $capIdRes]);

                // historial_saldos
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")
                    ->execute([$utilIdRes, $movRes1Id, $residual, $residual, 0]);
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'entrada', ?, ?, ?)")
                    ->execute([$capIdRes, $movRes2Id, $residual, $capSaldoAntRes, $capSaldoNuevoRes]);
            }
            // Si no se encontraron cuentas, no hacer nada (caso improbable)
        }

        echo json_encode(['success' => true, 'id' => $idCierre, 'mes' => $mes, 'utilidad_neta' => $utilidad, 'capital_acumulado' => $capitalAcumulado]);
        exit;
    }

    // ==================== RETIRO ====================
    if ($tipo === 'retiro') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $monto = (float)($_POST['monto'] ?? 0);
        $concepto = trim($_POST['concepto'] ?? '');
        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        validarMesNoCerrado($pdo, $fecha);

        if ($monto <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El monto debe ser mayor a 0']);
            exit;
        }
        if (empty($concepto)) {
            http_response_code(400);
            echo json_encode(['error' => 'El concepto es obligatorio']);
            exit;
        }

        // Insertar retiro
        $cuentaIdPost = (int)($_POST['cuenta_id'] ?? 0);
        $stmt = $pdo->prepare("INSERT INTO retiros_capital (monto, concepto, fecha, usuario_id, usuario_nombre, cuenta_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$monto, $concepto, $fecha, $usuario_id, $usuario_nombre, $cuentaIdPost > 0 ? $cuentaIdPost : null]);
        $retiroId = $pdo->lastInsertId();

        // Descontar de Capital Amiri (igual que gastos.php)
        $stmtCap = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1");
        $capitalRow = $stmtCap->fetch(PDO::FETCH_ASSOC);
        if ($capitalRow) {
            $capitalId = (int)$capitalRow['id'];
            $saldoAnt = (float)$capitalRow['saldo_actual'];
            $nuevoSaldo = round($saldoAnt - $monto, 2);
            $grupoId = round(microtime(true) * 1000);
            $desc = "Retiro de capital: $concepto";
            $stmtMov = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, referencia_id, referencia_tipo, activo) VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, 18, ?, 'retiro_capital', 1)");
            $stmtMov->execute([$grupoId, $fecha, $monto, $desc, $capitalId, $usuario_id, $retiroId]);
            $movId = $pdo->lastInsertId();
            $pdo->exec("SET @hyperion_saldo_auth = 1");
            $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$nuevoSaldo, $capitalId]);
            $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")->execute([$capitalId, $movId, $monto, $saldoAnt, $nuevoSaldo]);
        }

        // Descontar también de la cuenta seleccionada (si es diferente a Capital Amiri)
        $cuentaIdPost = (int)($_POST['cuenta_id'] ?? 0);
        if ($cuentaIdPost && $capitalRow && $cuentaIdPost !== (int)$capitalRow['id']) {
            $stmtCuenta = $pdo->prepare("SELECT id, saldo_actual FROM cuentas WHERE id = ? AND activo = 1");
            $stmtCuenta->execute([$cuentaIdPost]);
            $cuentaRow = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
            if ($cuentaRow) {
                $saldoCuentaAnt = (float)$cuentaRow['saldo_actual'];
                $saldoCuentaNuevo = round($saldoCuentaAnt - $monto, 2);
                $grupoIdCuenta = round(microtime(true) * 1000);
                $descCuenta = "Retiro de capital: $concepto";
                $stmtMovCuenta = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, referencia_id, referencia_tipo, activo) VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, 18, ?, 'retiro_capital', 1)");
                $stmtMovCuenta->execute([$grupoIdCuenta, $fecha, $monto, $descCuenta, $cuentaIdPost, $usuario_id, $retiroId]);
                $movCuentaId = $pdo->lastInsertId();
                $pdo->exec("SET @hyperion_saldo_auth = 1");
                $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldoCuentaNuevo, $cuentaIdPost]);
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")->execute([$cuentaIdPost, $movCuentaId, $monto, $saldoCuentaAnt, $saldoCuentaNuevo]);
            }
        }

        echo json_encode(['success' => true, 'id' => $retiroId, 'monto' => $monto]);
        exit;
    }

    // ==================== INICIALIZAR CAPITAL ====================
    if ($tipo === 'inicializar') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            // Obtener cuentas
            $stmtCap = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1 LIMIT 1");
            $capRow = $stmtCap->fetch(PDO::FETCH_ASSOC);
            if (!$capRow) {
                throw new Exception('No se encontró la cuenta Capital Amiri');
            }
            $capitalId = (int)$capRow['id'];
            $saldoCapital = (float)$capRow['saldo_actual'];

            $stmtUtil = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");
            $utilRow = $stmtUtil->fetch(PDO::FETCH_ASSOC);
            if (!$utilRow) {
                throw new Exception('No se encontró la cuenta Utilidades AMIRI');
            }
            $utilId = (int)$utilRow['id'];
            $montoTransferir = round((float)$utilRow['saldo_actual'], 2);

            $mesInicio = date('Y-m');

            // Verificar si ya existe un corte para este mes
            $stmtExist = $pdo->prepare("SELECT id FROM cierres_mensuales WHERE mes = ?");
            $stmtExist->execute([$mesInicio]);
            $corteExistente = $stmtExist->fetch();

            // Si Capital Amiri ya tiene saldo y existe el corte, solo recalcular (ya se inicializó antes)
            if ($saldoCapital > 0.01 && $corteExistente) {
                $saltoTransferencia = true;
            } else {
                $saltoTransferencia = false;
            }

            // Cuando se salta la transferencia, el capital_acumulado del corte debe ser el saldo actual de Capital Amiri
            $capitalAcumuladoCorte = $saltoTransferencia ? $saldoCapital : $montoTransferir;

            if (!$saltoTransferencia) {
                if ($montoTransferir < 0.01) {
                    throw new Exception('Utilidades AMIRI no tiene saldo disponible para transferir');
                }

                // Crear movimiento (traspaso)
                $grupoId = round(microtime(true) * 1000);
                $fecha = date('Y-m-d');
                $utilSaldoAnt = (float)$utilRow['saldo_actual'];
                $utilSaldoNuevo = 0;
                $descripcion = "Inicialización de Capital Amiri (transferencia de utilidades históricas)";
                $stmtMov = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, ?, 'traspaso', ?, 'MXN', ?, ?, ?, ?, 19, 1)");
                $stmtMov->execute([$grupoId, $fecha, $montoTransferir, $descripcion, $utilId, $capitalId, $usuario_id]);
                $movId = $pdo->lastInsertId();

                // Actualizar Utilidades AMIRI (salida)
                $pdo->exec("SET @hyperion_saldo_auth = 1");
                $pdo->prepare("UPDATE cuentas SET saldo_actual = 0 WHERE id = ?")->execute([$utilId]);
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")->execute([$utilId, $movId, $montoTransferir, $utilSaldoAnt, $utilSaldoNuevo]);

                // Actualizar Capital Amiri (entrada)
                $pdo->exec("SET @hyperion_saldo_auth = 1");
                $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$montoTransferir, $capitalId]);
                $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'entrada', ?, 0, ?)")->execute([$capitalId, $movId, $montoTransferir, $montoTransferir]);

                // Guardar fecha_inicializacion
                $ahora = date('Y-m-d H:i:s');
                setFechaInicializacion($pdo, $ahora);
            }

            // Obtener todos los meses con actividad histórica (orden ascendente)
            $stmtMeses = $pdo->query("
                SELECT DISTINCT m FROM (
                    SELECT DATE_FORMAT(fecha, '%Y-%m') as m FROM deudas_clientes WHERE activo = 1
                    UNION
                    SELECT DATE_FORMAT(fecha, '%Y-%m') FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND activo = 1 AND estado = 'activo'
                    UNION
                    SELECT DATE_FORMAT(fecha, '%Y-%m') FROM gastos WHERE activo = 1
                ) meses ORDER BY m ASC
            ");
            $meses = $stmtMeses->fetchAll(PDO::FETCH_COLUMN);

            if (empty($meses)) {
                throw new Exception('No hay datos históricos para inicializar');
            }

            $capitalAnterior = 0;

            // Batch queries: 3 queries total en vez de 3N
            $comisionesPorMes = [];
            $stmtComBatch = $pdo->query("SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE activo = 1 GROUP BY DATE_FORMAT(fecha, '%Y-%m')");
            while ($row = $stmtComBatch->fetch(PDO::FETCH_ASSOC)) {
                $comisionesPorMes[$row['mes']] = (float)$row['total'];
            }

            $divisasPorMes = [];
            $stmtDivBatch = $pdo->query("SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND activo = 1 AND estado = 'activo' GROUP BY DATE_FORMAT(fecha, '%Y-%m')");
            while ($row = $stmtDivBatch->fetch(PDO::FETCH_ASSOC)) {
                $divisasPorMes[$row['mes']] = (float)$row['total'];
            }

            $gastosPorMes = [];
            $stmtGasBatch = $pdo->query("SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE activo = 1 GROUP BY DATE_FORMAT(fecha, '%Y-%m')");
            while ($row = $stmtGasBatch->fetch(PDO::FETCH_ASSOC)) {
                $gastosPorMes[$row['mes']] = $row;
            }

            $stmtIns = $pdo->prepare("INSERT INTO cierres_mensuales (mes, fecha_cierre, comisiones_amiri, ganancia_divisas, gastos_fijos, gastos_variables, gastos_total, utilidad_neta, capital_acumulado) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE fecha_cierre = NOW(), comisiones_amiri = VALUES(comisiones_amiri), ganancia_divisas = VALUES(ganancia_divisas), gastos_fijos = VALUES(gastos_fijos), gastos_variables = VALUES(gastos_variables), gastos_total = VALUES(gastos_total), utilidad_neta = VALUES(utilidad_neta), capital_acumulado = VALUES(capital_acumulado)");

            foreach ($meses as $mes) {
                $com = $comisionesPorMes[$mes] ?? 0;
                $div = $divisasPorMes[$mes] ?? 0;
                $gasRow = $gastosPorMes[$mes] ?? ['total' => 0, 'fijos' => 0, 'variables' => 0];
                $gasTotal = (float)$gasRow['total'];
                $gasFijos = (float)$gasRow['fijos'];
                $gasVars = (float)$gasRow['variables'];

                $util = $com + $div - $gasTotal;
                $capitalAnterior += $util;

                $stmtIns->execute([$mes, $com, $div, $gasFijos, $gasVars, $gasTotal, $util, $capitalAnterior]);
            }

            // Ajustar el último mes para que capital_acumulado coincida exactamente con el saldo real
            $capitalFinal = $saltoTransferencia ? $saldoCapital : $montoTransferir;
            $diferencia = round($capitalFinal - $capitalAnterior, 2);
            if (abs($diferencia) > 0.001) {
                $ultimoMes = end($meses);
                $pdo->prepare("UPDATE cierres_mensuales SET utilidad_neta = utilidad_neta + ?, capital_acumulado = capital_acumulado + ? WHERE mes = ?")->execute([$diferencia, $diferencia, $ultimoMes]);
                $capitalAnterior = $capitalFinal;
            }
            $capitalAcumuladoCorte = $capitalFinal;

            if (function_exists('registrarAuditoria')) {
                $msgAuditoria = $saltoTransferencia
                    ? "Cortes históricos actualizados (" . count($meses) . " meses). Capital Amiri: \$$capitalAcumuladoCorte"
                    : "Inicialización de Capital Amiri: \$$montoTransferir transferidos desde Utilidades AMIRI, " . count($meses) . " cortes creados";
                registrarAuditoria($pdo, $usuario_id, 'capital_inicializar', $msgAuditoria, json_encode(['monto' => $capitalAcumuladoCorte, 'capital_id' => $capitalId, 'util_id' => $utilId, 'meses' => count($meses)]));
            }

            $pdo->commit();

            $fechaMostrar = $saltoTransferencia ? getFechaInicializacion($pdo) : $ahora;

            echo json_encode([
                'success' => true,
                'monto_transferido' => $capitalAcumuladoCorte,
                'fecha_inicializacion' => $fechaMostrar,
                'total_meses' => count($meses),
                'mensaje' => $saltoTransferencia
                    ? "Cortes históricos actualizados (" . count($meses) . " meses)"
                    : "Capital inicializado con \$$montoTransferir desde Utilidades AMIRI (" . count($meses) . " cortes creados)",
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    if ($tipo === 'meses_cerrados') {
        $stmt = $pdo->query("SELECT mes, fecha_cierre, utilidad_neta, capital_acumulado FROM cierres_mensuales ORDER BY mes DESC");
        $meses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($meses);
        exit;
    }

    if ($tipo === 'abrir_mes') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $mes = $_POST['mes'] ?? '';
        if (!$mes || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Mes inválido. Formato: YYYY-MM']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, comisiones_amiri, ganancia_divisas, gastos_total, utilidad_neta, capital_acumulado FROM cierres_mensuales WHERE mes = ?");
        $stmt->execute([$mes]);
        $cierre = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cierre) {
            http_response_code(404);
            echo json_encode(['error' => "El mes $mes no está cerrado"]);
            exit;
        }

        $hayPosteriores = $pdo->prepare("SELECT COUNT(*) FROM cierres_mensuales WHERE mes > ?");
        $hayPosteriores->execute([$mes]);
        if ((int)$hayPosteriores->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => "No puedes abrir el mes $mes porque hay cierres posteriores. Abre primero los meses más recientes."]);
            exit;
        }

        $pdo->beginTransaction();
        try {
            $grupoTs = round(microtime(true) * 1000);

            $stmtMovs = $pdo->prepare("SELECT id, grupo_id, tipo, monto, cuenta_origen_id, cuenta_destino_id, descripcion FROM movimientos WHERE servicio_id = 19 AND activo = 1 AND (descripcion LIKE ? OR descripcion LIKE ?)");
            $likeCierre = "Ajuste por cierre de mes $mes%";
            $likeResidual = "Ajuste por absorción de centavos%mes $mes%";
            $stmtMovs->execute([$likeCierre, $likeResidual]);
            $movsARevertir = $stmtMovs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($movsARevertir as $mov) {
                $pdo->prepare("UPDATE movimientos SET activo = 0 WHERE id = ?")->execute([$mov['id']]);

                if ($mov['cuenta_origen_id']) {
                    $pdo->exec("SET @hyperion_saldo_auth = 1");
                    $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                    $stmtSaldo->execute([$mov['cuenta_origen_id']]);
                    $saldoAnt = (float)$stmtSaldo->fetchColumn();
                    $saldoNuevo = round($saldoAnt + (float)$mov['monto'], 2);
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldoNuevo, $mov['cuenta_origen_id']]);
                    $movReversa = round(microtime(true) * 1000);
                    $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), ?, ?, 'MXN', ?, ?, ?, 19, 1)")->execute([$movReversa, $mov['tipo'] === 'entrada' ? 'salida' : 'entrada', $mov['monto'], "Reversión por apertura de mes $mes", $mov['cuenta_origen_id'], $usuario_id]);
                    $movReversaId = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, ?, ?, ?, ?)")->execute([$mov['cuenta_origen_id'], $movReversaId, $mov['tipo'] === 'entrada' ? 'salida' : 'entrada', $mov['monto'], $saldoAnt, $saldoNuevo]);
                }

                if ($mov['cuenta_destino_id']) {
                    $pdo->exec("SET @hyperion_saldo_auth = 1");
                    $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                    $stmtSaldo->execute([$mov['cuenta_destino_id']]);
                    $saldoAnt = (float)$stmtSaldo->fetchColumn();
                    $saldoNuevo = round($saldoAnt - (float)$mov['monto'], 2);
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldoNuevo, $mov['cuenta_destino_id']]);
                    $movReversa = round(microtime(true) * 1000);
                    $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, usuario_id, servicio_id, activo) VALUES (?, NOW(), ?, ?, 'MXN', ?, ?, ?, 19, 1)")->execute([$movReversa, $mov['tipo'] === 'entrada' ? 'salida' : 'entrada', $mov['monto'], "Reversión por apertura de mes $mes", $mov['cuenta_destino_id'], $usuario_id]);
                    $movReversaId = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, ?, ?, ?, ?)")->execute([$mov['cuenta_destino_id'], $movReversaId, $mov['tipo'] === 'entrada' ? 'salida' : 'entrada', $mov['monto'], $saldoAnt, $saldoNuevo]);
                }
            }

            $stmtCierre = $pdo->prepare("DELETE FROM cierres_mensuales WHERE id = ?");
            $stmtCierre->execute([$cierre['id']]);

            if (function_exists('registrarAuditoria')) {
                registrarAuditoria($pdo, $usuario_id, 'capital_abrir_mes', "Apertura del mes $mes", json_encode(['cierre_anterior' => $cierre]));
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'mes' => $mes, 'mensaje' => "Mes $mes abierto correctamente. Los movimientos de ese mes ya no están bloqueados."]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Tipo de consulta no válido']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
