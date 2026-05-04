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
   PUT - Registrar pago (Simple o Múltiple en cascada)
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $idsRaw = $input['id'] ?? null;
    $pago = (float)($input['monto_a_pagar'] ?? 0);
    $cuenta_pago_id = $input['cuenta_pago_id'] ?? null;
    $fecha_pago_input = $input['fecha_pago'] ?? date('Y-m-d H:i:s');
    
    if (strlen($fecha_pago_input) === 10) {
        $fecha_pago = $fecha_pago_input . ' ' . date('H:i:s');
    } else {
        $fecha_pago = $fecha_pago_input;
    }

    if (!$idsRaw || !$cuenta_pago_id || $pago <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos para pago (ID, monto o cuenta)']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 1. Validar que la cuenta de pago (física) tenga saldo suficiente
        $stmtCuenta = $pdo->prepare("SELECT nombre, saldo_actual, tipo_moneda FROM cuentas WHERE id = ? FOR UPDATE");
        $stmtCuenta->execute([$cuenta_pago_id]);
        $cuentaCaja = $stmtCuenta->fetch(PDO::FETCH_ASSOC);

        if (!$cuentaCaja) {
            throw new Exception("La cuenta de pago seleccionada no existe.");
        }
        
        $moneda_pago = $cuentaCaja['tipo_moneda'] ?? 'MXN';

        if (floatval($cuentaCaja['saldo_actual']) < $pago) {
            throw new Exception("Saldo insuficiente en la cuenta seleccionada. Saldo actual: $" . number_format($cuentaCaja['saldo_actual'], 2));
        }

        // 2. Preparar los IDs de las ventas a pagar
        $ids = is_string($idsRaw) ? explode(',', $idsRaw) : [$idsRaw];
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($v) { return $v > 0; });

        if (empty($ids)) {
            throw new Exception("Ningún ID de venta válido proporcionado.");
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 3. Obtener las ventas pendientes ordenadas por fecha (FIFO)
        $stmtDeudas = $pdo->prepare("
            SELECT dc.*, cu.saldo_actual as saldo_cuenta_cliente, c.nombre as cliente_nombre
            FROM deudas_clientes dc
            LEFT JOIN cuentas cu ON dc.cuenta_cliente_id = cu.id
            LEFT JOIN clientes c ON dc.cliente_id = c.id
            WHERE dc.id IN ($placeholders) 
            AND dc.estado != 'pagado' 
            AND dc.activo = 1
            ORDER BY dc.fecha ASC, dc.id ASC
            FOR UPDATE
        ");
        $stmtDeudas->execute($ids);
        $ventas = $stmtDeudas->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ventas)) {
            throw new Exception('No hay ventas pendientes válidas en la selección.');
        }

        // 4. Validar moneda (Seguridad anti-choques cambiarios)
        foreach($ventas as $v) {
            if(($v['moneda'] ?? 'MXN') !== $moneda_pago) {
                 throw new Exception("Moneda no coincidente. La cuenta es {$moneda_pago} pero intentas pagar una deuda en {$v['moneda']}.");
            }
        }

        // 5. Ciclo de Pago en Cascada
        $monto_restante = $pago;
        $grupo_id = round(microtime(true) * 1000);
        
        // Obtener servicio 'Pago a Cliente' (id 13 esperado)
        $stmtServ = $pdo->prepare("SELECT id FROM servicios WHERE nombre = 'Pago a Cliente' LIMIT 1");
        $stmtServ->execute();
        $serv = $stmtServ->fetch(PDO::FETCH_ASSOC);
        $servicio_id = $serv ? (int)$serv['id'] : 13;

        foreach ($ventas as $venta) {
            if ($monto_restante <= 0.001) break; // Se acabó el dinero

            $saldo_pendiente_venta = floatval($venta['monto_neto_cliente']) - floatval($venta['monto_pagado']);
            
            // Cuánto se abonará a ESTA venta específica
            $abono_aplicado = min($monto_restante, $saldo_pendiente_venta);
            $nuevo_pagado = floatval($venta['monto_pagado']) + $abono_aplicado;
            $nuevo_estado = ($nuevo_pagado >= floatval($venta['monto_neto_cliente']) - 0.001) ? 'pagado' : 'parcial';

            // A) Actualizar la venta
            $pdo->prepare("
                UPDATE deudas_clientes 
                SET monto_pagado = ?, estado = ?, fecha_pago = ?, movimiento_pago_id = ?, actualizado_en = NOW()
                WHERE id = ?
            ")->execute([$nuevo_pagado, $nuevo_estado, explode(' ', $fecha_pago)[0], $grupo_id, $venta['id']]);

            // B) Restar del saldo virtual de la cuenta del cliente
            if ($venta['cuenta_cliente_id']) {
                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                    ->execute([$abono_aplicado, $venta['cuenta_cliente_id']]);
            }

            $monto_restante -= $abono_aplicado;
        }

        // 6. Descontar el dinero REAL de la caja física/banco seleccionado de un solo golpe
        $monto_total_aplicado = $pago - $monto_restante;
        
        $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
            ->execute([$monto_total_aplicado, $cuenta_pago_id]);

        // 7. Registrar UNA SOLA SALIDA en el historial (movimientos) para no saturar la tabla
        $es_multiple = count($ids) > 1 ? " (Pago Múltiple)" : "";
        $descripcion = "Pago de venta(s) a {$ventas[0]['cliente_nombre']}{$es_multiple}";

        $stmtIns = $pdo->prepare("
            INSERT INTO movimientos (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, paridad, descripcion, activo)
            VALUES (?, ?, ?, 'salida', ?, ?, ?, ?, ?, 1, ?, 1)
        ");
        $stmtIns->execute([
            $grupo_id, 
            explode(' ', $fecha_pago)[0], 
            $servicio_id, 
            $moneda_pago,
            $monto_total_aplicado, 
            $cuenta_pago_id, 
            $ventas[0]['cuenta_cliente_id'], 
            $ventas[0]['cliente_id'], 
            $descripcion
        ]);

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Pago registrado correctamente'
        ]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
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

