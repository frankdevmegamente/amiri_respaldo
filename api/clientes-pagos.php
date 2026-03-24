<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { $input = $_POST; }

$cliente_id = isset($input['cliente_id']) ? (int)$input['cliente_id'] : null;
$cuenta_pago_id = isset($input['cuenta_pago_id']) ? (int)$input['cuenta_pago_id'] : null;
$monto = isset($input['monto']) ? floatval($input['monto']) : 0;
$fecha = $input['fecha'] ?? date('Y-m-d');
$descripcion = trim($input['descripcion'] ?? 'Pago a Cliente');
$paridad = isset($input['paridad']) ? floatval($input['paridad']) : null;

if (!$cliente_id || !$cuenta_pago_id || $monto <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

try {
    // Caja del cliente
    $stmtCliente = $pdo->prepare("SELECT cuenta_id FROM clientes WHERE id = ?");
    $stmtCliente->execute([$cliente_id]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
    if (!$cliente || !$cliente['cuenta_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'El cliente no tiene caja asociada']);
        exit;
    }
    $cuenta_cliente_id = (int)$cliente['cuenta_id'];

    // Cuenta origen
    $stmtOrigen = $pdo->prepare("SELECT id, tipo_moneda, saldo_actual FROM cuentas WHERE id = ? AND activo = 1");
    $stmtOrigen->execute([$cuenta_pago_id]);
    $origen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);
    if (!$origen) {
        http_response_code(404);
        echo json_encode(['error' => 'Cuenta de pago no encontrada o inactiva']);
        exit;
    }

    $moneda = $origen['tipo_moneda'];
    if ($moneda === 'MXN') { $paridad = 1.0; }
    if ($paridad === null || $paridad <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Paridad requerida para pagos en ' . $moneda]);
        exit;
    }

    $monto_mxn = $monto * $paridad;
    if (floatval($origen['saldo_actual']) < $monto_mxn) {
        http_response_code(400);
        echo json_encode(['error' => 'Saldo insuficiente en la cuenta de pago']);
        exit;
    }

    // Servicio Cambio de Flujo
    $stmtServ = $pdo->prepare("SELECT id FROM servicios WHERE nombre = 'Cambio de Flujo' LIMIT 1");
    $stmtServ->execute();
    $servicio = $stmtServ->fetch(PDO::FETCH_ASSOC);
    if (!$servicio) {
        http_response_code(500);
        echo json_encode(['error' => 'Servicio Cambio de Flujo no definido']);
        exit;
    }
    $servicio_id = (int)$servicio['id'];

    // Generar ID de grupo único para este pago
    $grupo_id = round(microtime(true) * 1000);
    
    $pdo->beginTransaction();

    // Registrar pago a cliente con servicio_id = 13 (Pago a Cliente)
    $stmtFlujo = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, paridad, descripcion, activo) VALUES (?,?,13,'salida',?,?,?,?,?,?,?,1)");
    $stmtFlujo->execute([$grupo_id, $fecha, $moneda, $monto, $cuenta_pago_id, $cuenta_cliente_id, $cliente_id, $paridad, $descripcion]);

    // Actualizar saldos
    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
        ->execute([$monto_mxn, $cuenta_pago_id]);

    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")
        ->execute([$monto_mxn, $cuenta_cliente_id]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar pago: ' . $e->getMessage()]);
}
