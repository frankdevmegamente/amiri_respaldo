<?php

/**

 * API: Gestión de correcciones de movimientos

 * 

 * Endpoints:

 * - GET ?action=historial&id={movimiento_id} - Obtener historial de correcciones

 * - POST action=anular - Anular un movimiento

 * - POST action=corregir - Corregir un movimiento

 */



require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/services/MovimientoCorreccionService.php';



header('Content-Type: application/json');



// Validar autenticación

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode(['error' => 'No autenticado']);

    exit;

}



// Solo admins pueden hacer correcciones

if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {

    http_response_code(403);

    echo json_encode(['error' => 'Solo administradores pueden hacer correcciones']);

    exit;

}



$usuario_id = $_SESSION['usuario_id'];



try {

    $pdo = getPDO();

    $service = new MovimientoCorreccionService($pdo);

    

    $method = $_SERVER['REQUEST_METHOD'];

    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    

    // =========================================================================

    // GET: Consultas

    // =========================================================================

    if ($method === 'GET') {

        switch ($action) {

            case 'historial':

                // Obtener historial de correcciones de un movimiento

                $movimiento_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                $historial = $service->obtenerHistorialCorrecciones($movimiento_id);

                echo json_encode([

                    'success' => true,

                    'historial' => $historial

                ]);

                break;

                

            case 'detalle':

                // Obtener detalle de un movimiento

                $movimiento_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                $movimiento = $service->obtenerMovimiento($movimiento_id);

                

                if (!$movimiento) {

                    http_response_code(404);

                    echo json_encode(['error' => 'Movimiento no encontrado']);

                    exit;

                }

                

                echo json_encode([

                    'success' => true,

                    'movimiento' => $movimiento

                ]);

                break;

                

            case 'ultimo':

                // Buscar el último grupo activo (por el ID más alto de cualquier movimiento del grupo)

                // Excluir grupos que ya tienen al menos un movimiento anulado/revertido

                $stmt = $pdo->prepare("

                    SELECT m.grupo_id, MAX(m.id) AS max_id

                    FROM movimientos m

                    WHERE m.estado = 'activo' AND m.activo = 1

                      AND (m.es_reverso = 0 OR m.es_reverso IS NULL)

                    GROUP BY m.grupo_id

                    ORDER BY max_id DESC

                    LIMIT 1

                ");

                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);



                if (!$row) {

                    echo json_encode(['success' => true, 'movimiento' => null]);

                    break;

                }



                // Obtener el movimiento "representativo" del grupo:

                // el primero (id ASC) que no sea historial (descripcion no empieza con 'Registro venta:')

                $stmtRep = $pdo->prepare("

                    SELECT m.*, s.nombre AS servicio_nombre,

                           cl.nombre AS cliente_nombre, pr.nombre AS proveedor_nombre,

                           cu_orig.nombre AS cuenta_origen_nombre,

                           cu_dest.nombre AS cuenta_destino_nombre

                    FROM movimientos m

                    LEFT JOIN servicios s ON m.servicio_id = s.id

                    LEFT JOIN clientes cl ON m.cliente_id = cl.id

                    LEFT JOIN proveedores pr ON m.proveedor_id = pr.id

                    LEFT JOIN cuentas cu_orig ON m.cuenta_origen_id = cu_orig.id

                    LEFT JOIN cuentas cu_dest ON m.cuenta_destino_id = cu_dest.id

                    WHERE m.grupo_id = ? AND m.estado = 'activo' AND m.activo = 1

                      AND (m.es_reverso = 0 OR m.es_reverso IS NULL)

                      AND (m.descripcion NOT LIKE 'Registro venta:%' OR m.descripcion IS NULL)

                    ORDER BY m.id ASC

                    LIMIT 1

                ");

                $stmtRep->execute([$row['grupo_id']]);

                $ultimo = $stmtRep->fetch(PDO::FETCH_ASSOC);



                if (!$ultimo) {

                    echo json_encode(['success' => true, 'movimiento' => null]);

                    break;

                }



                $stmtGrupo = $pdo->prepare("

                    SELECT m.*, s.nombre AS servicio_nombre,

                           cu_orig.nombre AS cuenta_origen_nombre,

                           cu_dest.nombre AS cuenta_destino_nombre

                    FROM movimientos m

                    LEFT JOIN servicios s ON m.servicio_id = s.id

                    LEFT JOIN cuentas cu_orig ON m.cuenta_origen_id = cu_orig.id

                    LEFT JOIN cuentas cu_dest ON m.cuenta_destino_id = cu_dest.id

                    WHERE m.grupo_id = ? AND m.estado = 'activo' AND m.activo = 1

                    ORDER BY m.id ASC

                ");

                $stmtGrupo->execute([$ultimo['grupo_id']]);

                $grupo = $stmtGrupo->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'movimiento' => $ultimo, 'grupo' => $grupo]);

                break;



            default:

                http_response_code(400);

                echo json_encode(['error' => 'Acción no especificada']);

        }

    }



    // =========================================================================

    // POST: Modificaciones

    // =========================================================================

    elseif ($method === 'POST') {

        switch ($action) {

            case 'anular':

                // Anular un movimiento o grupo de movimientos

                $movimiento_id = filter_var($_POST['movimiento_id'] ?? null, FILTER_VALIDATE_INT);

                $grupo_id = filter_var($_POST['grupo_id'] ?? null, FILTER_VALIDATE_INT);

                $motivo = trim($_POST['motivo'] ?? '');

                

                if (!$movimiento_id && !$grupo_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento o grupo requerido']);

                    exit;

                }

                

                if (empty($motivo)) {

                    http_response_code(400);

                    echo json_encode(['error' => 'Motivo de anulación requerido']);

                    exit;

                }

                

                // Si se envía grupo_id, anular todo el grupo

                if ($grupo_id) {

                    $resultado = $service->anularPorGrupo($grupo_id, $motivo, $usuario_id);

                } else {

                    $resultado = $service->anularMovimiento($movimiento_id, $motivo, $usuario_id);

                }

                

                if ($resultado['success']) {

                    echo json_encode($resultado);

                } else {

                    http_response_code(400);

                    echo json_encode($resultado);

                }

                break;

                

            case 'corregir':

                // Corregir un movimiento

                $movimiento_id = filter_var($_POST['movimiento_id'] ?? null, FILTER_VALIDATE_INT);

                $motivo = trim($_POST['motivo'] ?? '');

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                if (empty($motivo)) {

                    http_response_code(400);

                    echo json_encode(['error' => 'Motivo de corrección requerido']);

                    exit;

                }

                

                // Construir array de datos corregidos

                $datos_corregidos = [];

                

                // Solo incluir campos que fueron enviados/modificados

                if (isset($_POST['monto'])) {

                    $datos_corregidos['monto'] = floatval($_POST['monto']);

                }

                if (isset($_POST['fecha'])) {

                    $datos_corregidos['fecha'] = $_POST['fecha'];

                }

                if (isset($_POST['cliente_id'])) {

                    $datos_corregidos['cliente_id'] = $_POST['cliente_id'] ?: null;

                }

                if (isset($_POST['promotor_id'])) {

                    $datos_corregidos['promotor_id'] = $_POST['promotor_id'] ?: null;

                }

                if (isset($_POST['proveedor_id'])) {

                    $datos_corregidos['proveedor_id'] = $_POST['proveedor_id'] ?: null;

                }

                if (isset($_POST['cuenta_origen_id'])) {

                    $datos_corregidos['cuenta_origen_id'] = $_POST['cuenta_origen_id'] ?: null;

                }

                if (isset($_POST['cuenta_destino_id'])) {

                    $datos_corregidos['cuenta_destino_id'] = $_POST['cuenta_destino_id'] ?: null;

                }

                if (isset($_POST['paridad'])) {

                    $datos_corregidos['paridad'] = floatval($_POST['paridad']);

                }

                if (isset($_POST['descripcion'])) {

                    $datos_corregidos['descripcion'] = $_POST['descripcion'];

                }

                

                if (empty($datos_corregidos)) {

                    http_response_code(400);

                    echo json_encode(['error' => 'No se especificaron datos a corregir']);

                    exit;

                }

                

                $resultado = $service->corregirMovimiento($movimiento_id, $datos_corregidos, $motivo, $usuario_id);

                

                if ($resultado['success']) {

                    echo json_encode($resultado);

                } else {

                    http_response_code(400);

                    echo json_encode($resultado);

                }

                break;

                

            default:

                http_response_code(400);

                echo json_encode(['error' => 'Acción no especificada']);

        }

    }

    

    else {

        http_response_code(405);

        echo json_encode(['error' => 'Método no permitido']);

    }

    

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([

        'error' => 'Error interno del servidor',

        'message' => $e->getMessage()

    ]);

}

