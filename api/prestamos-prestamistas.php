<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/config/logger.php';



initApi();

requireAdmin();

$pdo = getPDO();



/* METHOD OVERRIDE */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {

    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);

}



/* GET */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {



    // Si viene ?id= retorna un préstamo específico

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {

        $stmt = $pdo->prepare("

            SELECT *

            FROM deudas_empresa

            WHERE id = ?

        ");

        $stmt->execute([$id]);

        $resultado = $stmt->fetch();

        if (!$resultado) {

            http_response_code(404);

            echo json_encode(['error' => 'Préstamo no encontrado']);

            exit;

        }

        echo json_encode($resultado);

        exit;

    }



    // Si viene ?prestamista_id= retorna todos los de ese prestamista

    $prestamistaId = filter_input(INPUT_GET, 'prestamista_id', FILTER_VALIDATE_INT);



    if ($prestamistaId) {

        $stmt = $pdo->prepare("

            SELECT *

            FROM deudas_empresa

            WHERE prestamista_id = ? AND activo = 1

            ORDER BY fecha DESC

        ");

        $stmt->execute([$prestamistaId]);

        echo json_encode($stmt->fetchAll());

        exit;

    }

}



/* POST (CREAR PRÉSTAMO) */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    try {

        $prestamista_id = $_POST['prestamista_id'] ?? null;

        $cuenta_id = $_POST['cuenta_id'] ?? null;

        $monto = (float)($_POST['monto'] ?? 0);

        $fecha = $_POST['fecha'] ?? null;



        if (!$prestamista_id || !$cuenta_id || $monto <= 0 || !$fecha) {

            Logger::warning('Datos inválidos para crear préstamo', $_POST);

            http_response_code(400);

            echo json_encode(['error' => 'Datos inválidos', 'datos' => $_POST]);

            exit;

        }



        $pdo->beginTransaction();

        

        $pdo->prepare("

            INSERT INTO deudas_empresa

            (prestamista_id, cuenta_id, monto, fecha, descripcion, estado, monto_pagado, activo)

            VALUES (?,?,?,?,?,'pendiente',0,1)

        ")->execute([

            $prestamista_id,

            $cuenta_id,

            $monto,

            $fecha,

            $_POST['descripcion'] ?? null

        ]);

        

        $prestamo_id = $pdo->lastInsertId();

        

        // Generar ID de grupo único para este préstamo

        $grupo_id = round(microtime(true) * 1000);

        

        // Obtener nombre del prestamista

        $stmt = $pdo->prepare("SELECT nombre FROM prestamistas WHERE id = ?");

        $stmt->execute([$prestamista_id]);

        $prestamista_nombre = $stmt->fetchColumn();

        

        // Registrar en movimientos con prestamista_id Y grupo_id

        // servicio_id = 10 (Préstamo de Prestamista)

        // moneda = MXN por defecto para préstamos

        $pdo->prepare("

            INSERT INTO movimientos

            (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, prestamista_id, usuario_id, servicio_id, activo)

            VALUES (?, ?, 'entrada', ?, 'MXN', ?, ?, ?, ?, 10, 1)

        ")->execute([

            $grupo_id,

            $fecha,

            $monto,

            'Préstamo de ' . $prestamista_nombre . ': ' . ($_POST['descripcion'] ?? 'Sin descripción'),

            $cuenta_id,

            $prestamista_id,

            $_SESSION['usuario_id']

        ]);



        $pdo->prepare("

            UPDATE cuentas SET saldo_actual = saldo_actual + ?

            WHERE id = ?

        ")->execute([$monto, $cuenta_id]);

        

        // Registrar auditoría

        registrarAuditoria($pdo, [

            'accion' => 'INSERT',

            'modulo' => 'prestamos',

            'tabla' => 'deudas_empresa',

            'registro_id' => $prestamo_id,

            'descripcion' => "Registró préstamo: $" . number_format($monto, 2) . " de $prestamista_nombre",

            'datos_nuevos' => [

                'id' => $prestamo_id,

                'prestamista_id' => $prestamista_id,

                'prestamista_nombre' => $prestamista_nombre,

                'cuenta_id' => $cuenta_id,

                'monto' => $monto,

                'fecha' => $fecha,

                'descripcion' => $_POST['descripcion'] ?? null

            ]

        ]);



        $pdo->commit();

        

        Logger::info('Préstamo creado exitosamente', ['prestamo_id' => $prestamo_id]);

        echo json_encode(['success' => true, 'prestamo_id' => $prestamo_id]);

        exit;

        

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        Logger::error('Error al crear préstamo', [

            'message' => $e->getMessage(),

            'trace' => $e->getTraceAsString()

        ]);

        http_response_code(500);

        echo json_encode(['error' => 'Error al crear préstamo: ' . $e->getMessage()]);

        exit;

    }

}



/* PUT (PAGO) */

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {



    // Leer datos de PUT como JSON

    $input = json_decode(file_get_contents('php://input'), true) ?? [];



    $id = $input['id'] ?? null;

    $pago = (float)($input['monto_a_pagar'] ?? 0);

    $cuenta = $input['cuenta_pago_id'] ?? null;

    // AQUÍ ATRAPAMOS LA FECHA DE PAGO MANUAL

    $fecha_pago = $input['fecha_pago'] ?? date('Y-m-d H:i:s');



    if (!$id || !$cuenta || $pago <= 0) {

        http_response_code(400);

        echo json_encode(['error' => 'Datos inválidos para pago']);

        exit;

    }



    $stmt = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id=?");

    $stmt->execute([$cuenta]);

    $saldo = $stmt->fetchColumn();

    if ($saldo < $pago) {

        http_response_code(400);

        echo json_encode(['error' => 'Saldo insuficiente']);

        exit;

    }



    $stmt = $pdo->prepare("SELECT monto, monto_pagado FROM deudas_empresa WHERE id=?");

    $stmt->execute([$id]);

    $prestamo = $stmt->fetch();



    if (!$prestamo) {

        http_response_code(404);

        echo json_encode(['error' => 'Préstamo no encontrado']);

        exit;

    }



    $nuevoPagado = $prestamo['monto_pagado'] + $pago;

    if ($nuevoPagado > $prestamo['monto']) {

        http_response_code(400);

        echo json_encode(['error' => 'Pago excede el monto']);

        exit;

    }



    $estado = $nuevoPagado >= $prestamo['monto'] ? 'pagado' : 'parcial';



    $pdo->beginTransaction();

    try {

        // Generar ID de grupo único para este pago

        $grupo_id = round(microtime(true) * 1000);

        

        // Obtener datos del préstamo y prestamista

        $stmt = $pdo->prepare("

            SELECT pp.*, p.nombre AS prestamista_nombre

            FROM deudas_empresa pp

            JOIN prestamistas p ON pp.prestamista_id = p.id

            WHERE pp.id = ?

        ");

        $stmt->execute([$id]);

        $datos_prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        

        // AQUÍ CAMBIAMOS EL NOW() POR NUESTRA VARIABLE $fecha_pago

        $pdo->prepare("

            UPDATE deudas_empresa

            SET monto_pagado=?, estado=?, fecha_pago=?

            WHERE id=?

        ")->execute([$nuevoPagado, $estado, $fecha_pago, $id]);



        $pdo->prepare("

            UPDATE cuentas SET saldo_actual = saldo_actual - ?

            WHERE id=?

        ")->execute([$pago, $cuenta]);

        

        // AQUÍ TAMBIÉN CAMBIAMOS EL NOW() POR NUESTRA VARIABLE $fecha_pago

        // Registrar en movimientos con servicio_id = 14 (Pago de Préstamo)

        $pdo->prepare("

            INSERT INTO movimientos

            (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, prestamista_id, usuario_id, servicio_id, activo)

            VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, ?, ?, 14, 1)

        ")->execute([

            $grupo_id,

            $fecha_pago,

            $pago,

            'Pago de préstamo a ' . $datos_prestamo['prestamista_nombre'],

            $cuenta,

            $datos_prestamo['prestamista_id'],

            $_SESSION['usuario_id']

        ]);

        

        // Registrar auditoría

        registrarAuditoria($pdo, [

            'accion' => 'UPDATE',

            'modulo' => 'prestamos',

            'tabla' => 'deudas_empresa',

            'registro_id' => $id,

            'descripcion' => "Registró pago de $" . number_format($pago, 2) . " al préstamo de {$datos_prestamo['prestamista_nombre']} (Estado: $estado)",

            'datos_anteriores' => [

                'monto_pagado' => $prestamo['monto_pagado'],

                'estado' => $datos_prestamo['estado']

            ],

            'datos_nuevos' => [

                'monto_pagado' => $nuevoPagado,

                'estado' => $estado,

                'pago_realizado' => $pago,

                'fecha_pago' => $fecha_pago

            ]

        ]);



        $pdo->commit();

        echo json_encode(['success' => true]);

        exit;

    } catch (Exception $e) {

        $pdo->rollBack();

        http_response_code(500);

        echo json_encode(['error' => 'Error al pagar préstamo: ' . $e->getMessage()]);

        exit;

    }

}



/* DELETE (BORRADO PROFUNDO Y AUTOMATIZADO) */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    // 1. Obtener datos del préstamo antes de desactivar
    $stmt = $pdo->prepare("
        SELECT pp.*, p.nombre AS prestamista_nombre
        FROM deudas_empresa pp
        JOIN prestamistas p ON pp.prestamista_id = p.id
        WHERE pp.id = ?
    ");
    $stmt->execute([$id]);
    $datos_anteriores = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos_anteriores) {
        http_response_code(404);
        echo json_encode(['error' => 'Préstamo no encontrado']);
        exit;
    }

    // 2. CANDADO DE SEGURIDAD: Evitar borrar préstamos que ya tienen pagos
    if ((float)$datos_anteriores['monto_pagado'] > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede eliminar un préstamo que ya tiene abonos. Por favor, revierte los pagos primero.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 3. Desactivar el préstamo de la tabla de deudas (Soft Delete)
        $pdo->prepare("
            UPDATE deudas_empresa SET activo = 0 WHERE id = ?
        ")->execute([$id]);

        // 4. RESTAR EL DINERO DE LA CAJA AUTOMÁTICAMENTE
        $pdo->prepare("
            UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?
        ")->execute([$datos_anteriores['monto'], $datos_anteriores['cuenta_id']]);

        // 5. BUSCAR Y ANULAR EL MOVIMIENTO DEL FLUJO
        // Buscamos el movimiento exacto que metió este dinero para anularlo
        $stmtMov = $pdo->prepare("
            SELECT grupo_id 
            FROM movimientos 
            WHERE prestamista_id = ? AND cuenta_destino_id = ? AND monto = ? AND servicio_id = 10 AND estado = 'activo'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtMov->execute([
            $datos_anteriores['prestamista_id'], 
            $datos_anteriores['cuenta_id'], 
            $datos_anteriores['monto']
        ]);
        $movimiento = $stmtMov->fetch(PDO::FETCH_ASSOC);

        if ($movimiento) {
            $pdo->prepare("
                UPDATE movimientos 
                SET estado = 'anulado', motivo_correccion = 'Préstamo eliminado manualmente desde el panel' 
                WHERE grupo_id = ?
            ")->execute([$movimiento['grupo_id']]);
        }
        
        // 6. Registrar en auditoría el proceso completo
        registrarAuditoria($pdo, [
            'accion' => 'DELETE',
            'modulo' => 'prestamos',
            'tabla' => 'deudas_empresa',
            'registro_id' => $id,
            'descripcion' => "Eliminó préstamo de {$datos_anteriores['prestamista_nombre']} por $" . number_format($datos_anteriores['monto'], 2) . " y restó el saldo de la caja automáticamente.",
            'datos_anteriores' => $datos_anteriores
        ]);

        $pdo->commit();
        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el préstamo y ajustar la caja: ' . $e->getMessage()]);
        exit;
    }
}



http_response_code(405);

echo json_encode(['error' => 'Método no permitido']);