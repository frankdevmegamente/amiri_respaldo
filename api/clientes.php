<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/services/ClienteService.php';

require_once __DIR__ . '/../src/config/logger.php';



initApi();

requireAdmin();

$pdo = getPDO();

$clienteService = new ClienteService($pdo);



/* ===== GET ===== */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {



    if (isset($_GET['id'])) {

        try {

            $cliente = $clienteService->obtenerCliente($_GET['id']);

            if (!$cliente) {

                http_response_code(404);

                jsonResponse(['error' => 'Cliente no encontrado']);

                exit;

            }

            jsonResponse($cliente);

            exit;

        } catch (Exception $e) {

            Logger::error('Error al obtener cliente', ['message' => $e->getMessage()]);

            http_response_code(500);

            jsonResponse(['error' => 'Error al obtener cliente']);

            exit;

        }

    }

    

    // Filtrar por moneda si se especifica

    $moneda = $_GET['moneda'] ?? null;

    if ($moneda) {

        // Validar moneda y mapear a columna

        $monedasValidas = [

            'MXN' => 'opera_mxn',

            'USD' => 'opera_usd', 

            'USDT' => 'opera_usdt'

        ];

        

        $monedaUpper = strtoupper($moneda);

        if (!isset($monedasValidas[$monedaUpper])) {

            http_response_code(400);

            jsonResponse(['error' => 'Moneda no válida']);

            exit;

        }

        

        $columna = $monedasValidas[$monedaUpper];

        

        $stmt = $pdo->prepare("

            SELECT c.*, p.nombre AS promotor

            FROM clientes c

            LEFT JOIN promotores p ON c.promotor_id = p.id

            WHERE c.{$columna} = 1 AND c.activo = 1

            ORDER BY c.nombre

        ");

        $stmt->execute();

        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));

        exit;

    }



    $clientes = $clienteService->listarClientes(true);

    jsonResponse($clientes);

    exit;

}



/* ===== POST (CREATE / UPDATE) ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    $id = $_POST['id'] ?? null;

    

    // Checkboxes de monedas (si no está en POST, es 0)

    $opera_mxn = isset($_POST['opera_mxn']) ? 1 : 0;

    $opera_usd = isset($_POST['opera_usd']) ? 1 : 0;

    $opera_usdt = isset($_POST['opera_usdt']) ? 1 : 0;

    

    // Si opera MXN, promotor es requerido; si no, es null

    $promotor_id = $opera_mxn ? (int)($_POST['promotor_id'] ?? 0) : null;

    

    $data = [

        'nombre' => trim($_POST['nombre'] ?? ''),

        'contacto' => trim($_POST['contacto'] ?? ''),

        'promotor_id' => $promotor_id,

        'retencion_total' => $opera_mxn ? (float)($_POST['retencion_total'] ?? 0) : 0,

        'porcentaje_amiri' => $opera_mxn ? (float)($_POST['porcentaje_amiri'] ?? 0) : 0,

        'porcentaje_promotor' => $opera_mxn ? (float)($_POST['porcentaje_promotor'] ?? 0) : 0,

        'opera_mxn' => $opera_mxn,

        'opera_usd' => $opera_usd,

        'opera_usdt' => $opera_usdt

    ];



    try {

        if ($id) {

            // Actualizar cliente existente

            $datos_anteriores = obtenerDatosAnteriores($pdo, 'clientes', $id);

            

            $clienteService->actualizarCliente($id, $data);

            

            registrarAuditoria($pdo, [

                'accion' => 'UPDATE',

                'modulo' => 'clientes',

                'tabla' => 'clientes',

                'registro_id' => $id,

                'descripcion' => "Actualizó cliente: {$data['nombre']}",

                'datos_anteriores' => $datos_anteriores,

                'datos_nuevos' => $data

            ]);

            

            jsonResponse(['success' => true, 'message' => 'Cliente actualizado correctamente']);

            

        } else {

            // Crear nuevo cliente

            $result = $clienteService->crearCliente($data);

            

            registrarAuditoria($pdo, [

                'accion' => 'INSERT',

                'modulo' => 'clientes',

                'tabla' => 'clientes',

                'registro_id' => $result['cliente_id'],

                'descripcion' => "Creó cliente: {$data['nombre']}",

                'datos_nuevos' => array_merge($data, ['id' => $result['cliente_id']])

            ]);

            

            registrarAuditoria($pdo, [

                'accion' => 'INSERT',

                'modulo' => 'clientes',

                'tabla' => 'cuentas',

                'registro_id' => $result['cuenta_id'],

                'descripcion' => "Creó cuenta automática para cliente: Caja - {$data['nombre']}",

                'datos_nuevos' => [

                    'id' => $result['cuenta_id'],

                    'nombre' => "Caja - {$data['nombre']}",

                    'tipo_moneda' => $data['tipo_moneda']

                ]

            ]);

            

            jsonResponse($result);

        }

        

    } catch (ValidationException $e) {

        Logger::warning('Validación fallida en clientes', [

            'errors' => $e->getErrors(),

            'data' => $data

        ]);

        jsonError($e->getMessage(), 400, $e->getErrors());

    } catch (Exception $e) {

        Logger::error('Error al procesar cliente', [

            'message' => $e->getMessage(),

            'trace' => $e->getTraceAsString()

        ]);

        jsonError('Error al procesar cliente: ' . $e->getMessage(), 500);

    }

}



/* ===== DELETE ===== */

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    parse_str(file_get_contents('php://input'), $input);

    $id = filter_var($_GET['id'] ?? $input['id'] ?? null, FILTER_VALIDATE_INT);



    if (!$id) {

        Logger::warning('Intento de eliminar cliente sin ID válido', ['input' => $input]);

        jsonError('ID de cliente no válido', 400);

    }



    try {

        // Obtener datos antes de eliminar

        $cliente = $clienteService->obtenerCliente($id);

        

        if (!$cliente) {

            Logger::warning('Intento de eliminar cliente inexistente', ['id' => $id]);

            jsonError('Cliente no encontrado', 404);

        }

        

        $cuenta_id = $cliente['cuenta_id'] ?? null;

        

        // Obtener datos de la cuenta si existe

        $datos_cuenta = null;

        if ($cuenta_id) {

            $stmt = $pdo->prepare("SELECT * FROM cuentas WHERE id = ?");

            $stmt->execute([$cuenta_id]);

            $datos_cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

        }

        

        $pdo->beginTransaction();

        

        // Soft-delete: desactivar cliente (NUNCA borrar físicamente)

        $stmt = $pdo->prepare("UPDATE clientes SET activo = 0, eliminado_en = NOW(), eliminado_por = ? WHERE id = ?");

        $stmt->execute([$_SESSION['usuario_id'] ?? null, $id]);

        

        // Desactivar la cuenta asociada si existe (soft-delete)

        if ($cuenta_id) {

            $stmt = $pdo->prepare("UPDATE cuentas SET activo = 0 WHERE id = ?");

            $stmt->execute([$cuenta_id]);

        }

        

        // Registrar auditorías

        registrarAuditoria($pdo, [

            'accion' => 'SOFT_DELETE',

            'modulo' => 'clientes',

            'tabla' => 'clientes',

            'registro_id' => $id,

            'descripcion' => "Desactivó cliente: {$cliente['nombre']}",

            'datos_anteriores' => $cliente

        ]);

        

        if ($cuenta_id && $datos_cuenta) {

            registrarAuditoria($pdo, [

                'accion' => 'SOFT_DELETE',

                'modulo' => 'clientes',

                'tabla' => 'cuentas',

                'registro_id' => $cuenta_id,

                'descripcion' => "Desactivó cuenta asociada a cliente: {$datos_cuenta['nombre']}",

                'datos_anteriores' => $datos_cuenta

            ]);

        }

        

        $pdo->commit();

        

        Logger::info('Cliente desactivado exitosamente', ['cliente_id' => $id, 'nombre' => $cliente['nombre']]);

        jsonResponse(['success' => true, 'message' => 'Cliente eliminado correctamente']);

        

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        Logger::error('Error al eliminar cliente', [

            'message' => $e->getMessage(),

            'trace' => $e->getTraceAsString(),

            'cliente_id' => $id

        ]);

        jsonError('Error al eliminar cliente: ' . $e->getMessage(), 500);

    }

}

