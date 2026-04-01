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

   PUT - Registrar pago de una venta

============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {



    // Leer datos de PUT como JSON

    $input = json_decode(file_get_contents('php://input'), true) ?? [];



    $id = $input['id'] ?? null;

    $pago = (float)($input['monto_a_pagar'] ?? 0);

    $cuenta = $input['cuenta_pago_id'] ?? null;

    $fecha_pago = $input['fecha_pago'] ?? date('Y-m-d');



    if (!$id || !$cuenta || $pago <= 0) {

        http_response_code(400);

        echo json_encode(['error' => 'Datos inválidos para pago']);

        exit;

    }



    // Validar que la venta existe y obtener datos

    $stmt = $pdo->prepare("

        SELECT vec.*, cu.saldo_actual as saldo_cuenta_cliente

        FROM deudas_clientes vec

        LEFT JOIN cuentas cu ON vec.cuenta_cliente_id = cu.id

        WHERE vec.id = ?

    ");

    $stmt->execute([$id]);

    $venta = $stmt->fetch();



    if (!$venta) {

        http_response_code(404);

        echo json_encode(['error' => 'Venta no encontrada']);

        exit;

    }



    // Calcular nuevo monto pagado

    $nuevoPagado = $venta['monto_pagado'] + $pago;

    

    // Validar que no exceda el monto neto

    if ($nuevoPagado > $venta['monto_neto_cliente']) {

        http_response_code(400);

        echo json_encode(['error' => 'El pago excede el monto adeudado']);

        exit;

    }



    // Determinar nuevo estado

    $estado = $nuevoPagado >= $venta['monto_neto_cliente'] ? 'pagado' : 'parcial';



    $pdo->beginTransaction();

    try {

        // Actualizar la venta

        $pdo->prepare("

            UPDATE deudas_clientes

            SET monto_pagado = ?, estado = ?, fecha_pago = ?, actualizado_en = NOW()

            WHERE id = ?

        ")->execute([$nuevoPagado, $estado, $fecha_pago, $id]);



            // Restar de la cuenta del cliente (saldo virtual)

        $pdo->prepare("

            UPDATE cuentas 

            SET saldo_actual = saldo_actual - ?

            WHERE id = ?

        ")->execute([$pago, $venta['cuenta_cliente_id']]);



            // Restar de la cuenta de pago seleccionada (nuestra caja/banco que entrega el efectivo)

        $pdo->prepare("

            UPDATE cuentas 

            SET saldo_actual = saldo_actual - ?

            WHERE id = ?

        ")->execute([$pago, $cuenta]);



            // Registrar en movimientos para trazabilidad y enlace con flujo.php

            $grupo_id = round(microtime(true) * 1000);



            // Obtener servicio 'Pago a Cliente' (id 13 esperado)

            $stmtServ = $pdo->prepare("SELECT id FROM servicios WHERE nombre = 'Pago a Cliente' LIMIT 1");

            $stmtServ->execute();

            $serv = $stmtServ->fetch(PDO::FETCH_ASSOC);

            $servicio_id = $serv ? (int)$serv['id'] : 13;



            // Insertar movimiento tipo 'salida' (Pago a Cliente)

            $stmtIns = $pdo->prepare("\n            INSERT INTO movimientos (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, paridad, descripcion, activo)\n            VALUES (?, NOW(), ?, 'salida', 'MXN', ?, ?, ?, ?, ?, ?, 1)\n        ");

            $descripcion = 'Pago de venta efectivo ID ' . $id;

            // paridad = 1 para MXN

            $stmtIns->execute([$grupo_id, $servicio_id, $pago, $cuenta, $venta['cuenta_cliente_id'], $venta['cliente_id'], 1, $descripcion]);



            // Guardar referencia al grupo de pago en la venta

            $pdo->prepare("UPDATE deudas_clientes SET movimiento_pago_id = ? WHERE id = ?")->execute([$grupo_id, $id]);



        $pdo->commit();

        echo json_encode([

            'success' => true,

            'nuevo_estado' => $estado,

            'monto_pagado' => $nuevoPagado,

            'saldo_pendiente' => $venta['monto_neto_cliente'] - $nuevoPagado

        ]);

        exit;

    } catch (Exception $e) {

        $pdo->rollBack();

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

