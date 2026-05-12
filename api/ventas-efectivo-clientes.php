<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAdmin();

$pdo = getPDO();



/* METHOD OVERRIDE */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {

    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);

}



/* ============================================================

   GET - Obtener ventas de efectivo

============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {



    // Si viene ?id= retorna una venta específica

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {

        $stmt = $pdo->prepare("

            SELECT vec.*, c.nombre as cliente_nombre

            FROM deudas_clientes vec

            LEFT JOIN clientes c ON vec.cliente_id = c.id

            WHERE vec.id = ?

        ");

        $stmt->execute([$id]);

        $resultado = $stmt->fetch();

        if (!$resultado) {

            http_response_code(404);

            echo json_encode(['error' => 'Venta no encontrada']);

            exit;

        }

        echo json_encode($resultado);

        exit;

    }



    // Si viene ?cliente_id= retorna todas las ventas de ese cliente

    $clienteId = filter_input(INPUT_GET, 'cliente_id', FILTER_VALIDATE_INT);



    if ($clienteId) {

        $stmt = $pdo->prepare("

            SELECT vec.*, c.nombre as cliente_nombre, cu.nombre as cuenta_nombre

            FROM deudas_clientes vec

            LEFT JOIN clientes c ON vec.cliente_id = c.id

            LEFT JOIN cuentas cu ON vec.cuenta_cliente_id = cu.id

            WHERE vec.cliente_id = ? AND vec.activo = 1

            ORDER BY vec.fecha DESC, vec.estado ASC

        ");

        $stmt->execute([$clienteId]);

        echo json_encode($stmt->fetchAll());

        exit;

    }



    // Si no hay parámetros, retornar todas las ventas pendientes

    $stmt = $pdo->query("

        SELECT vec.*, c.nombre as cliente_nombre, cu.nombre as cuenta_nombre

        FROM deudas_clientes vec

        LEFT JOIN clientes c ON vec.cliente_id = c.id

        LEFT JOIN cuentas cu ON vec.cuenta_cliente_id = cu.id

        WHERE vec.activo = 1 AND vec.estado != 'pagado'

        ORDER BY vec.fecha DESC

    ");

    echo json_encode($stmt->fetchAll());

    exit;

}



/* ============================================================

   POST - Crear nueva venta de efectivo (NO SE USA AQUÍ)

   Las ventas se crean desde api/flujo.php automáticamente

============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    http_response_code(400);

    echo json_encode(['error' => 'Las ventas se crean desde el módulo de Flujo']);

    exit;

}



/* ============================================================
   PUT - Registrar pago (Simple, Múltiple o por array de pagos)
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_once __DIR__ . '/../src/config/helpers.php';

    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Nuevo formato: array de pagos individuales
    $pagos = $input['pagos'] ?? null;
    
    // Legacy: id + monto_a_pagar
    $idsRaw = $input['id'] ?? null;
    $pago_legacy = (float)($input['monto_a_pagar'] ?? 0);

    $cuenta_pago_id = $input['cuenta_pago_id'] ?? null;
    $fecha_pago_input = $input['fecha_pago'] ?? date('Y-m-d H:i:s');
    
    if (strlen($fecha_pago_input) === 10) {
        $fecha_pago = $fecha_pago_input . ' ' . date('H:i:s');
    } else {
        $fecha_pago = $fecha_pago_input;
    }

    $pdo->beginTransaction();
    try {
        // Obtener servicio 'Pago a Cliente' (id 13 esperado)
        $stmtServ = $pdo->prepare("SELECT id FROM servicios WHERE nombre = 'Pago a Cliente' LIMIT 1");
        $stmtServ->execute();
        $serv = $stmtServ->fetch(PDO::FETCH_ASSOC);
        $servicio_id = $serv ? (int)$serv['id'] : 13;

        // ─────────────────────────────────────────────────────────────────
        // MODO 1: Array de pagos individuales [{id, abono}]
        // ─────────────────────────────────────────────────────────────────
        if (!empty($pagos)) {
            $pagos_validos = [];
            $total_pago = 0;

            foreach ($pagos as $p) {
                $id = (int)($p['id'] ?? 0);
                $abono = (float)($p['abono'] ?? 0);
                if ($id <= 0 || $abono <= 0) continue;
                $pagos_validos[] = ['id' => $id, 'abono' => $abono];
                $total_pago += $abono;
            }

            if (empty($pagos_validos) || !$cuenta_pago_id) {
                throw new Exception("Datos inválidos para el pago");
            }

            // Validar saldo de cuenta física
            $stmtC = $pdo->prepare("SELECT saldo_actual, tipo_moneda FROM cuentas WHERE id = ? FOR UPDATE");
            $stmtC->execute([$cuenta_pago_id]);
            $cuentaCaja = $stmtC->fetch(PDO::FETCH_ASSOC);
            if (!$cuentaCaja) throw new Exception("Cuenta de pago no existe");
            if ((float)$cuentaCaja['saldo_actual'] < $total_pago) {
                throw new Exception("Saldo insuficiente. Necesitas $" . number_format($total_pago, 2));
            }

            $moneda_pago = $cuentaCaja['tipo_moneda'] ?? 'MXN';
            $grupo_id = round(microtime(true) * 1000);
            $cliente_nombre = '';

            foreach ($pagos_validos as $pv) {
                $stmtD = $pdo->prepare("
                    SELECT dc.*, c.nombre as cliente_nombre, cu.saldo_actual as saldo_cuenta_cliente
                    FROM deudas_clientes dc
                    LEFT JOIN clientes c ON dc.cliente_id = c.id
                    LEFT JOIN cuentas cu ON dc.cuenta_cliente_id = cu.id
                    WHERE dc.id = ? AND dc.estado != 'pagado' AND dc.activo = 1
                    FOR UPDATE
                ");
                $stmtD->execute([$pv['id']]);
                $venta = $stmtD->fetch(PDO::FETCH_ASSOC);
                if (!$venta) continue;

                if (($venta['moneda'] ?? 'MXN') !== $moneda_pago) {
                    throw new Exception("Moneda no coincidente en venta #{$pv['id']}");
                }

                $pendiente = (float)$venta['monto_neto_cliente'] - (float)$venta['monto_pagado'];
                $abono_real = min($pv['abono'], $pendiente);
                if ($abono_real <= 0) continue;

                $nuevo_pagado = (float)$venta['monto_pagado'] + $abono_real;
                $nuevo_estado = ($nuevo_pagado >= (float)$venta['monto_neto_cliente'] - 0.001) ? 'pagado' : 'parcial';

                $pdo->prepare("
                    UPDATE deudas_clientes 
                    SET monto_pagado = ?, estado = ?, fecha_pago = ?, movimiento_pago_id = ?, actualizado_en = NOW()
                    WHERE id = ?
                ")->execute([$nuevo_pagado, $nuevo_estado, explode(' ', $fecha_pago)[0], $grupo_id, $venta['id']]);

                if ($venta['cuenta_cliente_id']) {
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                        ->execute([$abono_real, $venta['cuenta_cliente_id']]);
                }

                // Crear movimiento individual por cada deuda con referencia_id
                $pdo->prepare("
                    INSERT INTO movimientos (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, referencia_id, paridad, descripcion, activo)
                    VALUES (?, ?, ?, 'salida', ?, ?, ?, ?, ?, ?, 1, ?, 1)
                ")->execute([
                    $grupo_id,
                    explode(' ', $fecha_pago)[0],
                    $servicio_id,
                    $moneda_pago,
                    $abono_real,
                    $cuenta_pago_id,
                    $venta['cuenta_cliente_id'],
                    $venta['cliente_id'],
                    $venta['id'],
                    "Pago a {$venta['cliente_nombre']} - Venta #{$venta['id']}"
                ]);

                $mov_id = $pdo->lastInsertId();
                actualizarSaldoCuenta($pdo, $cuenta_pago_id, $abono_real, 'salida', $mov_id, "Pago a {$venta['cliente_nombre']} - Venta #{$venta['id']}");

                $cliente_nombre = $venta['cliente_nombre'];
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Pago(s) registrado(s) correctamente']);
            exit;
        }

        // ─────────────────────────────────────────────────────────────────
        // MODO 2: Legacy — id + monto_a_pagar (conversión a pagos array)
        // ─────────────────────────────────────────────────────────────────
        if (!$idsRaw || !$cuenta_pago_id || $pago_legacy <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos para pago (ID, monto o cuenta)']);
            exit;
        }

        $total_pago_legacy = $pago_legacy;

        // Validar cuenta física
        $stmtCuenta = $pdo->prepare("SELECT nombre, saldo_actual, tipo_moneda FROM cuentas WHERE id = ? FOR UPDATE");
        $stmtCuenta->execute([$cuenta_pago_id]);
        $cuentaCaja = $stmtCuenta->fetch(PDO::FETCH_ASSOC);

        if (!$cuentaCaja) throw new Exception("La cuenta de pago seleccionada no existe.");
        $moneda_pago = $cuentaCaja['tipo_moneda'] ?? 'MXN';

        if ((float)$cuentaCaja['saldo_actual'] < $total_pago_legacy) {
            throw new Exception("Saldo insuficiente. Saldo actual: $" . number_format($cuentaCaja['saldo_actual'], 2));
        }

        $ids = is_string($idsRaw) ? explode(',', $idsRaw) : [$idsRaw];
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($v) => $v > 0);
        if (empty($ids)) throw new Exception("Ningún ID de venta válido.");

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmtDeudas = $pdo->prepare("
            SELECT dc.*, c.nombre as cliente_nombre
            FROM deudas_clientes dc
            LEFT JOIN clientes c ON dc.cliente_id = c.id
            WHERE dc.id IN ($placeholders) AND dc.estado != 'pagado' AND dc.activo = 1
            ORDER BY dc.fecha ASC, dc.id ASC
            FOR UPDATE
        ");
        $stmtDeudas->execute($ids);
        $ventas = $stmtDeudas->fetchAll(PDO::FETCH_ASSOC);
        if (empty($ventas)) throw new Exception('No hay ventas pendientes.');

        foreach ($ventas as $v) {
            if (($v['moneda'] ?? 'MXN') !== $moneda_pago) {
                throw new Exception("Moneda no coincidente.");
            }
        }

        $monto_restante = $total_pago_legacy;
        $grupo_id = round(microtime(true) * 1000);

        foreach ($ventas as $venta) {
            if ($monto_restante <= 0.001) break;

            $pendiente = (float)$venta['monto_neto_cliente'] - (float)$venta['monto_pagado'];
            $abono_aplicado = min($monto_restante, $pendiente);
            $nuevo_pagado = (float)$venta['monto_pagado'] + $abono_aplicado;
            $nuevo_estado = ($nuevo_pagado >= (float)$venta['monto_neto_cliente'] - 0.001) ? 'pagado' : 'parcial';

            $pdo->prepare("
                UPDATE deudas_clientes 
                SET monto_pagado = ?, estado = ?, fecha_pago = ?, movimiento_pago_id = ?, actualizado_en = NOW()
                WHERE id = ?
            ")->execute([$nuevo_pagado, $nuevo_estado, explode(' ', $fecha_pago)[0], $grupo_id, $venta['id']]);

            if ($venta['cuenta_cliente_id']) {
                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                    ->execute([$abono_aplicado, $venta['cuenta_cliente_id']]);
            }

            // Movimiento individual con referencia_id
            $pdo->prepare("
                INSERT INTO movimientos (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, referencia_id, paridad, descripcion, activo)
                VALUES (?, ?, ?, 'salida', ?, ?, ?, ?, ?, ?, 1, ?, 1)
            ")->execute([
                $grupo_id,
                explode(' ', $fecha_pago)[0],
                $servicio_id,
                $moneda_pago,
                $abono_aplicado,
                $cuenta_pago_id,
                $venta['cuenta_cliente_id'],
                $venta['cliente_id'],
                $venta['id'],
                "Pago a {$venta['cliente_nombre']} - Venta #{$venta['id']}"
            ]);

            $mov_id = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, $cuenta_pago_id, $abono_aplicado, 'salida', $mov_id, "Pago a {$venta['cliente_nombre']} - Venta #{$venta['id']}");

            $monto_restante -= $abono_aplicado;
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar pago: ' . $e->getMessage()]);
        exit;
    }
}



/* ============================================================

   DELETE - Eliminar venta (soft delete)

============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {



    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id) {

        http_response_code(400);

        echo json_encode(['error' => 'ID requerido']);

        exit;

    }



    $pdo->prepare("

        UPDATE deudas_clientes SET activo = 0 WHERE id = ?

    ")->execute([$id]);



    echo json_encode(['success' => true]);

    exit;

}



http_response_code(405);

echo json_encode(['error' => 'Método no permitido']);

