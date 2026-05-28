<?php

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();

$pdo = getPDO();

function testBalanceCuentas($pdo) {
    $res = ['num' => 1, 'nombre' => 'Balance individual por cuenta', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $cuentas = $pdo->query("
        SELECT c.id, c.nombre, c.saldo_actual, c.tipo_moneda,
               COALESCE(h.saldo_nuevo, 0) AS saldo_historial
        FROM cuentas c
        LEFT JOIN historial_saldos h ON h.id = (
            SELECT MAX(h2.id) FROM historial_saldos h2 WHERE h2.cuenta_id = c.id
        )
        WHERE c.activo = 1
        ORDER BY c.id
    ")->fetchAll();
    foreach ($cuentas as $c) {
        $saldo_real = (float)$c['saldo_actual'];
        $saldo_hist = (float)$c['saldo_historial'];
        $diff = round($saldo_real - $saldo_hist, 2);
        if (abs($diff) > 0.01) {
            $res['detalles'][] = ['error' => "#{$c['id']} {$c['nombre']} ({$c['tipo_moneda']}): saldo_actual=" . number_format($saldo_real, 2) . " vs historial=" . number_format($saldo_hist, 2) . " → diferencia=" . number_format($diff, 2)];
        }
    }
    if (empty($res['detalles'])) {
        $res['msg'] = 'Todas las cuentas cuadran (' . count($cuentas) . ' cuentas verificadas)';
    } else {
        $res['status'] = 'error';
        $res['msg'] = count($res['detalles']) . ' cuenta(s) con descuadre';
    }
    return $res;
}

function testPartidaDoble($pdo) {
    $res = ['num' => 2, 'nombre' => 'Partida doble por grupo de movimientos', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $grupos = $pdo->query("
        SELECT grupo_id, moneda, COUNT(*) as total_movs,
               GROUP_CONCAT(DISTINCT tipo ORDER BY tipo SEPARATOR ',') as tipos,
               ROUND(SUM(CASE WHEN tipo IN ('entrada','comision') THEN monto WHEN tipo IN ('salida') THEN -monto WHEN tipo = 'traspaso' THEN 0 ELSE 0 END), 2) AS balance
        FROM movimientos
        WHERE activo = 1 AND estado = 'activo' AND grupo_id IS NOT NULL AND moneda IS NOT NULL
        GROUP BY grupo_id, moneda
        HAVING COUNT(*) > 0
        ORDER BY grupo_id, moneda
    ")->fetchAll();
    $desbalanceados = 0;
    $saltados = 0;
    $vistos = [];
    foreach ($grupos as $g) {
        $bal = (float)$g['balance'];
        $tiposArr = array_map('trim', explode(',', $g['tipos']));
        $hasEntrada = count(array_intersect($tiposArr, ['entrada','comision'])) > 0;
        $hasSalida = in_array('salida', $tiposArr);
        $hasTraspaso = in_array('traspaso', $tiposArr);
        $esUnilateral = (!$hasEntrada || !$hasSalida) && !$hasTraspaso;
        $vistos[$g['grupo_id']] = true;
        if ($hasTraspaso) {
            $saltados++;
        } elseif ($esUnilateral) {
            $saltados++;
        } elseif (abs($bal) > 0.01) {
            $desbalanceados++;
            if ($desbalanceados <= 30) {
                $res['detalles'][] = ['error' => "Grupo #{$g['grupo_id']} ({$g['moneda']}): balance=" . number_format($bal, 2) . " ({$g['total_movs']} movs, tipos: {$g['tipos']})"];
            }
        }
    }
    if ($desbalanceados > 30) {
        $res['detalles'][] = ['error' => "... y " . ($desbalanceados - 30) . " grupos más desbalanceados", 'tipo' => 'warning'];
    }
    $total = count($vistos);
    $msg = 'Todos los grupos cuadran (' . number_format($total) . ' grupos verificados)';
    if ($saltados > 0) $msg .= " — $saltados grupo(s) omitidos (traspaso o unilateral válido)";
    if (empty($res['detalles'])) {
        $res['msg'] = $msg;
    } else {
        $res['status'] = 'error';
        $res['msg'] = "$desbalanceados grupo(s) desbalanceado(s) de " . number_format($total) . " totales";
    }
    return $res;
}

function testDeudasClientes($pdo) {
    $res = ['num' => 3, 'nombre' => 'Consistencia de deudas clientes', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $deudas = $pdo->query("SELECT id, monto_total, monto_neto_cliente, monto_pagado, comision_amiri, comision_promotor, estado FROM deudas_clientes WHERE activo = 1 ORDER BY id")->fetchAll();
    $mal_estado = 0;
    foreach ($deudas as $d) {
        $id = $d['id'];
        if ((float)$d['monto_pagado'] > (float)$d['monto_neto_cliente'] + 0.01) {
            $res['detalles'][] = ['error' => "Deuda #$id: monto_pagado (" . number_format($d['monto_pagado'], 2) . ") > monto_neto_cliente (" . number_format($d['monto_neto_cliente'], 2) . ")"];
        }
        $pendiente = (float)$d['monto_neto_cliente'] - (float)$d['monto_pagado'];
        $estadoEsperado = ($pendiente <= 0.001) ? 'pagado' : (($d['monto_pagado'] > 0) ? 'parcial' : 'pendiente');
        if ($d['estado'] !== $estadoEsperado) {
            $mal_estado++;
            if ($mal_estado <= 5) {
                $res['detalles'][] = ['error' => "Deuda #$id: estado={$d['estado']} pero debería ser $estadoEsperado (pendiente=" . number_format($pendiente, 2) . ")", 'tipo' => 'warning'];
            }
        }
        $suma_comisiones = round((float)$d['comision_amiri'] + (float)$d['comision_promotor'], 2);
        if (abs($suma_comisiones + (float)$d['monto_neto_cliente'] - (float)$d['monto_total']) > 0.02) {
            $res['detalles'][] = ['error' => "Deuda #$id: monto_total (" . number_format($d['monto_total'], 2) . ") != neto (" . number_format($d['monto_neto_cliente'], 2) . ") + comisiones (" . number_format($suma_comisiones, 2) . ")", 'tipo' => 'warning'];
        }
    }
    if ($mal_estado > 5) {
        $res['detalles'][] = ['error' => "... y " . ($mal_estado - 5) . " deudas más con estado incorrecto", 'tipo' => 'warning'];
    }
    if (empty($res['detalles'])) {
        $res['msg'] = 'Deudas clientes OK (' . count($deudas) . ' registros)';
    } else {
        $res['status'] = 'warning';
        $res['msg'] = count($res['detalles']) . ' inconsistencia(s) detectada(s)';
    }
    return $res;
}

function testDeudasEmpresa($pdo) {
    $res = ['num' => 4, 'nombre' => 'Consistencia de deudas empresa', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $deudas = $pdo->query("SELECT id, monto, monto_pagado, estado FROM deudas_empresa WHERE activo = 1 ORDER BY id")->fetchAll();
    $mal_estado = 0;
    foreach ($deudas as $d) {
        if ((float)$d['monto_pagado'] > (float)$d['monto'] + 0.01) {
            $res['detalles'][] = ['error' => "Deuda empresa #{$d['id']}: pagado (" . number_format($d['monto_pagado'], 2) . ") > monto (" . number_format($d['monto'], 2) . ")"];
        }
        $pendiente = (float)$d['monto'] - (float)$d['monto_pagado'];
        $estadoEsperado = ($pendiente <= 0.01) ? 'pagado' : (($d['monto_pagado'] > 0) ? 'parcial' : 'pendiente');
        if ($d['estado'] !== $estadoEsperado) {
            $mal_estado++;
            if ($mal_estado <= 5) {
                $res['detalles'][] = ['error' => "Deuda empresa #{$d['id']}: estado={$d['estado']} debería ser $estadoEsperado", 'tipo' => 'warning'];
            }
        }
    }
    if ($mal_estado > 5) {
        $res['detalles'][] = ['error' => "... y " . ($mal_estado - 5) . " más con estado incorrecto", 'tipo' => 'warning'];
    }
    if (empty($res['detalles'])) {
        $res['msg'] = 'Deudas empresa OK (' . count($deudas) . ' registros)';
    } else {
        $res['status'] = 'warning';
        $res['msg'] = count($res['detalles']) . ' inconsistencia(s)';
    }
    return $res;
}

function testFIFO($pdo) {
    $res = ['num' => 5, 'nombre' => 'Inventario FIFO', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $lotes = $pdo->query("SELECT id, moneda, cantidad_original, cantidad_restante FROM inventario_monedas WHERE activo = 1 ORDER BY id")->fetchAll();
    $sobrevendidos = 0;
    foreach ($lotes as $l) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(cantidad_consumida), 0) FROM consumos_inventario WHERE inventario_id = :id");
        $stmt->execute(['id' => $l['id']]);
        $consumido = (float)$stmt->fetchColumn();
        $esperado = round((float)$l['cantidad_original'] - $consumido, 8);
        $real = (float)$l['cantidad_restante'];
        if ($esperado < 0 && $real == 0) {
            $sobrevendidos++;
            continue;
        }
        if (abs($esperado - $real) > 0.00000001) {
            $res['detalles'][] = ['error' => "Lote #{$l['id']} ({$l['moneda']}): restante debería ser " . rtrim(rtrim(number_format($esperado, 8), '0'), '.') . " pero es " . rtrim(rtrim(number_format($real, 8), '0'), '.')];
        }
    }
    $totalGanancia = (float)$pdo->query("SELECT COALESCE(SUM(ganancia), 0) FROM consumos_inventario")->fetchColumn();
    $msg = 'Inventario FIFO: ' . count($lotes) . ' lotes, ganancia: ' . number_format($totalGanancia, 2) . ' MXN';
    if ($sobrevendidos > 0) {
        $msg .= ", $sobrevendidos sobrevendido(s) (restante=0)";
    }
    if (empty($res['detalles'])) {
        $res['msg'] = $msg . ' — OK';
    } else {
        $res['status'] = 'error';
        $res['msg'] = count($res['detalles']) . ' lote(s) con inconsistencia';
    }
    return $res;
}

function testHistorialSaldos($pdo) {
    $res = ['num' => 6, 'nombre' => 'Secuencia historial de saldos', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $cuentas = $pdo->query("SELECT id, nombre FROM cuentas WHERE activo = 1 ORDER BY id")->fetchAll();
    $revisadas = 0;
    $fallos = 0;
    foreach ($cuentas as $c) {
        $historial = $pdo->prepare("
            SELECT id, tipo, monto, saldo_anterior, saldo_nuevo
            FROM historial_saldos
            WHERE cuenta_id = :id
            ORDER BY created_at ASC
        ");
        $historial->execute(['id' => $c['id']]);
        $rows = $historial->fetchAll();
        if (empty($rows)) continue;
        $revisadas++;
        $saldo_acum = (float)$rows[0]['saldo_anterior'];
        foreach ($rows as $r) {
            $saldo_esperado = $saldo_acum + ($r['tipo'] === 'entrada' ? (float)$r['monto'] : -(float)$r['monto']);
            $saldo_esperado = round($saldo_esperado, 2);
            if (abs($saldo_esperado - (float)$r['saldo_nuevo']) > 0.01) {
                $fallos++;
                if ($fallos <= 5) {
                    $res['detalles'][] = ['error' => "Cuenta #{$c['id']} ({$c['nombre']}) historial #{$r['id']}: saldo_nuevo=" . number_format($r['saldo_nuevo'], 2) . " pero debería ser " . number_format($saldo_esperado, 2)];
                }
            }
            $saldo_acum = (float)$r['saldo_nuevo'];
        }
        $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id=?");
        $stmtSaldo->execute([$c['id']]);
        $saldoActualCuenta = (float)$stmtSaldo->fetchColumn();
        $ultimo = end($rows);
        if (abs($ultimo['saldo_nuevo'] - $saldoActualCuenta) > 0.01) {
            $fallos++;
            if ($fallos <= 5) {
                $res['detalles'][] = ['error' => "Cuenta #{$c['id']} ({$c['nombre']}): último historial=" . number_format($ultimo['saldo_nuevo'], 2) . " vs saldo_actual=" . number_format($saldoActualCuenta, 2), 'tipo' => 'warning'];
            }
        }
    }
    if ($fallos > 5) $res['detalles'][] = ['error' => "... y " . ($fallos - 5) . " errores más en historial", 'tipo' => 'warning'];
    if (empty($res['detalles'])) {
        $res['msg'] = 'Historial de saldos OK (' . $revisadas . ' cuentas revisadas)';
    } else {
        $res['status'] = 'error';
        $res['msg'] = $fallos . ' error(es) en secuencia de historial';
    }
    return $res;
}

function testEcuacionContable($pdo) {
    $res = ['num' => 7, 'nombre' => 'Ecuación contable (Activos = Capital + Pasivos)', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $actMxn = (float)$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2) AND tipo_moneda='MXN'")->fetchColumn();
    $actDiv = (float)$pdo->query("SELECT COALESCE(ROUND(SUM(im.cantidad_restante * im.paridad), 2),0) FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE c.activo=1 AND c.tipo_cuenta IN(1,2) AND im.activo=1")->fetchColumn();
    $check = $pdo->query("SHOW TABLES LIKE 'prestamos_otorgados'")->fetchColumn();
    $actPrestOtor = 0;
    if ($check) {
        $actPrestOtor = (float)$pdo->query("SELECT COALESCE(SUM(monto_total - monto_pagado),0) FROM prestamos_otorgados WHERE estado IN ('pendiente','parcial') AND activo = 1")->fetchColumn();
    }
    $totalActivos = $actMxn + $actDiv + $actPrestOtor;
    $capCuentas = (float)$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta = 3")->fetchColumn();
    $capDeudasCliMxn = (float)$pdo->query("SELECT COALESCE(SUM(monto_neto_cliente - monto_pagado),0) FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL)")->fetchColumn();
    $capPromotores = (float)$pdo->query("SELECT COALESCE(SUM(c.saldo_actual),0) FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01")->fetchColumn();
    $totalCapital = $capCuentas + $capDeudasCliMxn + $capPromotores;
    foreach (['USD', 'USDT'] as $div) {
        $stmtPar = $pdo->prepare("
            SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0)
            FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id
            WHERE im.activo=1 AND c.tipo_moneda=?
        ");
        $stmtPar->execute([$div]);
        $paridad = (float)$stmtPar->fetchColumn();
        $stmtCliDiv = $pdo->prepare("
            SELECT COALESCE(SUM(monto_neto_cliente - monto_pagado),0)
            FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND moneda=?
        ");
        $stmtCliDiv->execute([$div]);
        $cliDiv = (float)$stmtCliDiv->fetchColumn();
        $totalCapital += $cliDiv * $paridad;
    }
    $pasPrestamistas = (float)$pdo->query("SELECT COALESCE(SUM(monto - monto_pagado),0) FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1")->fetchColumn();
    $pasProveedores = (float)$pdo->query("
        SELECT COALESCE(SUM(c.valor_mxn),0) FROM proveedores p
        INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id)
        WHERE p.activo = 1
    ")->fetchColumn();
    $totalPasivos = $pasPrestamistas + $pasProveedores;
    $diferencia = round($totalActivos - $totalCapital - $totalPasivos, 2);
    $res['detalles'][] = ['msg' => "Activos: " . number_format($totalActivos, 2) . " MXN | Capital: " . number_format($totalCapital, 2) . " MXN | Pasivos: " . number_format($totalPasivos, 2) . " MXN", 'tipo' => 'info'];
    if (abs($diferencia) < 0.01) {
        $res['msg'] = 'ECUACIÓN BALANCEADA — Activos = Capital + Pasivos';
    } else {
        $res['status'] = 'error';
        $res['msg'] = 'Descuadre de ' . number_format(abs($diferencia), 2) . ' MXN en la ecuación contable';
        $res['detalles'][] = ['error' => "Diferencia: " . number_format($diferencia, 2) . " MXN — Activos ($totalActivos) - Capital ($totalCapital) - Pasivos ($totalPasivos) = " . number_format($diferencia, 2)];
    }
    return $res;
}

function testIntegridadReferencial($pdo) {
    $res = ['num' => 8, 'nombre' => 'Integridad referencial (sin huérfanos)', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $checks = [
        'cuenta_origen_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN cuentas c ON m.cuenta_origen_id=c.id WHERE m.cuenta_origen_id IS NOT NULL AND c.id IS NULL AND m.activo=1",
        'cuenta_destino_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN cuentas c ON m.cuenta_destino_id=c.id WHERE m.cuenta_destino_id IS NOT NULL AND c.id IS NULL AND m.activo=1",
        'cliente_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN clientes c ON m.cliente_id=c.id WHERE m.cliente_id IS NOT NULL AND c.id IS NULL AND m.activo=1",
        'promotor_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN promotores p ON m.promotor_id=p.id WHERE m.promotor_id IS NOT NULL AND p.id IS NULL AND m.activo=1",
        'proveedor_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN proveedores p ON m.proveedor_id=p.id WHERE m.proveedor_id IS NOT NULL AND p.id IS NULL AND m.activo=1",
        'servicio_id en movimientos' => "SELECT COUNT(*) FROM movimientos m LEFT JOIN servicios s ON m.servicio_id=s.id WHERE s.id IS NULL AND m.activo=1",
        'cliente_id en deudas_clientes' => "SELECT COUNT(*) FROM deudas_clientes d LEFT JOIN clientes c ON d.cliente_id=c.id WHERE c.id IS NULL AND d.activo=1",
        'prestamista_id en deudas_empresa' => "SELECT COUNT(*) FROM deudas_empresa d LEFT JOIN prestamistas p ON d.prestamista_id=p.id WHERE p.id IS NULL AND d.activo=1",
        'cuenta_id en historial_saldos' => "SELECT COUNT(*) FROM historial_saldos h LEFT JOIN cuentas c ON h.cuenta_id=c.id WHERE c.id IS NULL",
        'movimiento_id en historial_saldos' => "SELECT COUNT(*) FROM historial_saldos h LEFT JOIN movimientos m ON h.movimiento_id=m.id WHERE m.id IS NULL",
    ];
    foreach ($checks as $nombre => $sql) {
        $count = (int)$pdo->query($sql)->fetchColumn();
        if ($count > 0) {
            $res['detalles'][] = ['error' => "$nombre: $count registro(s) huérfano(s)"];
        }
    }
    if (empty($res['detalles'])) {
        $res['msg'] = 'Todas las referencias son válidas (0 registros huérfanos)';
    } else {
        $res['status'] = 'error';
        $res['msg'] = count($res['detalles']) . ' tipo(s) de referencia con huérfanos';
    }
    return $res;
}

function testMovimientosAnomalos($pdo) {
    $res = ['num' => 9, 'nombre' => 'Movimientos anómalos', 'status' => 'ok', 'detalles' => [], 'msg' => ''];
    $sinGrupo = (int)$pdo->query("SELECT COUNT(*) FROM movimientos WHERE grupo_id IS NULL AND activo=1 AND estado='activo'")->fetchColumn();
    if ($sinGrupo > 0) {
        $res['detalles'][] = ['error' => "$sinGrupo movimiento(s) activo(s) sin grupo_id", 'tipo' => 'warning'];
    }
    $montoCero = (int)$pdo->query("SELECT COUNT(*) FROM movimientos WHERE monto = 0 AND activo=1 AND estado='activo'")->fetchColumn();
    if ($montoCero > 0) {
        $res['detalles'][] = ['error' => "$montoCero movimiento(s) activo(s) con monto = 0", 'tipo' => 'warning'];
    }
    $sinsentido = $pdo->query("
        SELECT m.id, m.tipo, m.monto, m.descripcion
        FROM movimientos m
        WHERE m.activo=1 AND m.estado='activo' AND m.monto < 0 AND m.tipo IN ('entrada','salida')
        ORDER BY m.id LIMIT 20
    ")->fetchAll();
    if (count($sinsentido) > 0) {
        $lista = [];
        foreach ($sinsentido as $s) {
            $lista[] = "#{$s['id']} {$s['tipo']} " . number_format($s['monto'], 2);
        }
        $res['detalles'][] = ['error' => count($sinsentido) . " movimiento(s) con monto negativo en entrada/salida: " . implode(', ', $lista), 'tipo' => 'warning'];
    }
    $serviciosActivos = $pdo->query("SELECT id FROM servicios WHERE activo=1")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($serviciosActivos)) {
        $servIdStr = implode(',', $serviciosActivos);
        $servInvalido = (int)$pdo->query("SELECT COUNT(*) FROM movimientos WHERE activo=1 AND servicio_id NOT IN ($servIdStr)")->fetchColumn();
        if ($servInvalido > 0) {
            $res['detalles'][] = ['error' => "$servInvalido movimiento(s) con servicio_id inválido", 'tipo' => 'warning'];
        }
    }
    $creditoProv = (int)$pdo->query("
        SELECT COUNT(*) FROM movimientos
        WHERE activo=1 AND estado='activo' AND tipo='salida' AND proveedor_id IS NOT NULL AND cuenta_origen_id IS NULL
    ")->fetchColumn();
    $sinOrigenDestino = (int)$pdo->query("
        SELECT COUNT(*) FROM movimientos
        WHERE activo=1 AND estado='activo' AND tipo IN ('entrada','salida')
        AND ((tipo='entrada' AND cuenta_destino_id IS NULL) OR (tipo='salida' AND cuenta_origen_id IS NULL))
        AND NOT (tipo = 'salida' AND proveedor_id IS NOT NULL AND cuenta_origen_id IS NULL)
    ")->fetchColumn();
    if ($creditoProv > 0) {
        $res['detalles'][] = ['msg' => "$creditoProv compra(s) a crédito proveedor (origen omitido intencionalmente)", 'tipo' => 'info'];
    }
    if ($sinOrigenDestino > 0) {
        $res['detalles'][] = ['error' => "$sinOrigenDestino movimiento(s) entrada/salida sin cuenta origen/destino", 'tipo' => 'warning'];
    }
    if (empty($res['detalles'])) {
        $res['msg'] = 'Sin movimientos anómalos detectados';
    } else {
        $res['status'] = 'warning';
        $res['msg'] = count($res['detalles']) . ' anomalía(s) detectada(s)';
    }
    return $res;
}

try {
    $tests = [
        testBalanceCuentas($pdo),
        testPartidaDoble($pdo),
        testDeudasClientes($pdo),
        testDeudasEmpresa($pdo),
        testFIFO($pdo),
        testHistorialSaldos($pdo),
        testEcuacionContable($pdo),
        testIntegridadReferencial($pdo),
        testMovimientosAnomalos($pdo),
    ];

    $errores = 0;
    foreach ($tests as $t) {
        if ($t['status'] === 'error') $errores++;
    }

    $financiero = [
        'comisiones_amiri' => (float)$pdo->query("SELECT COALESCE(SUM(comision_amiri),0) FROM deudas_clientes WHERE activo=1")->fetchColumn(),
        'comisiones_promotor' => (float)$pdo->query("SELECT COALESCE(SUM(comision_promotor),0) FROM deudas_clientes WHERE activo=1")->fetchColumn(),
        'gastos_totales' => (float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1")->fetchColumn(),
        'gastos_fijos' => (float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1 AND clasificacion='fijo'")->fetchColumn(),
        'gastos_variables' => (float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1 AND clasificacion='variable'")->fetchColumn(),
        'ganancias_fifo' => (float)$pdo->query("SELECT COALESCE(SUM(ganancia),0) FROM consumos_inventario")->fetchColumn(),
    ];

    echo json_encode([
        'tests' => $tests,
        'financiero' => $financiero,
        'resumen' => [
            'total' => count($tests),
            'exitosas' => count($tests) - $errores,
            'con_warning' => count(array_filter($tests, fn($t) => $t['status'] === 'warning')),
            'errores' => $errores,
            'estado' => $errores > 0 ? 'error' : 'ok'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar pruebas de integridad: ' . $e->getMessage()]);
}
