<?php
// api/flujo.php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/validator.php';

initApi();
requireAdmin();
$pdo = getPDO();

$data = getJsonInput();

if (!$data) {
    jsonError('Datos inválidos', 400);
}

// Validar datos de entrada con Validator
try {
    $validator = new Validator();
    $validator->required($data, ['fecha', 'servicio', 'monto']);
    $validator->date($data['fecha'] ?? '');
    $validator->numeric($data['monto'] ?? 0, 0.01);
    $validator->numeric($data['paridad'] ?? 1, 0);
} catch (ValidationException $e) {
    jsonError($e->getMessage(), 400, $e->getErrors());
}

$fecha = $data['fecha'];
$servicio = $data['servicio'];
$cliente_id = $data['cliente_id'] ?? null;
$proveedor_id = $data['proveedor_id'] ?? null;
$cuenta_id = $data['cuenta_id'] ?? null;
$destino_id = $data['destino_id'] ?? null;
$monto = floatval($data['monto']);
$paridad = floatval($data['paridad'] ?? 1);
$total_mxn = $monto * $paridad;

// Obtener cuenta del cliente si existe
$cuenta_cliente_id = null;
$promotor_id = null;
$cuenta_promotor_id = null;
if ($cliente_id) {
    $stmtCliente = $pdo->prepare("SELECT cuenta_id, promotor_id FROM clientes WHERE id = ?");
    $stmtCliente->execute([$cliente_id]);
    $clienteData = $stmtCliente->fetch(PDO::FETCH_ASSOC);
    if ($clienteData) {
        $cuenta_cliente_id = $clienteData['cuenta_id'];
        $promotor_id = $clienteData['promotor_id'];

        if ($promotor_id) {
            $stmtPromotor = $pdo->prepare("SELECT cuenta_id FROM promotores WHERE id = ?");
            $stmtPromotor->execute([$promotor_id]);
            $promotorData = $stmtPromotor->fetch(PDO::FETCH_ASSOC);
            if ($promotorData) {
                $cuenta_promotor_id = $promotorData['cuenta_id'];
            }
        }
    }
}

try {
    $pdo->beginTransaction();

    // Generar un ID único para agrupar todos los movimientos de esta operación
    $grupo_id = round(microtime(true) * 1000); // timestamp en milisegundos

    // Obtener el ID del servicio por su nombre
    $servicioStmt = $pdo->prepare("SELECT id FROM servicios WHERE nombre = ? LIMIT 1");
    $servicioStmt->execute([$servicio]);
    $servicioResult = $servicioStmt->fetch(PDO::FETCH_ASSOC);
    if (!$servicioResult) {
        throw new Exception('Servicio no encontrado: ' . $servicio);
    }
    $servicio_id = $servicioResult['id'];

    // Helper para actualizar saldos (solo MXN)
    $actualizarSaldo = function($cuenta_id, $monto, $tipo_operacion) use ($pdo) {
        if (!$cuenta_id) return false;
        $operador = ($tipo_operacion === 'entrada') ? '+' : '-';
        $sql = "UPDATE cuentas SET saldo_actual = saldo_actual " . $operador . " ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error al preparar sentencia SQL: ' . $sql);
        }
        $result = $stmt->execute([$monto, $cuenta_id]);
        if (!$result) {
            throw new Exception('Error al actualizar saldo para cuenta ' . $cuenta_id . ': ' . json_encode($stmt->errorInfo()));
        }
        return true;
    };

    // Preparar statement para insertar en movimientos
    $stmtMovimiento = $pdo->prepare("
        INSERT INTO movimientos 
        (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id, proveedor_id, referencia_id, referencia_tipo, paridad, descripcion, activo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    if ($servicio === 'Venta de Efectivo') {
        if (!$cuenta_cliente_id) {
            throw new Exception('El cliente no tiene cuenta/caja asociada');
        }
        if (!$cuenta_id) {
            throw new Exception('Debe seleccionar una cuenta/caja para recibir el dinero');
        }
        $porcentaje_amiri = floatval($data['porcentaje_amiri'] ?? 0);
        $porcentaje_promotor = floatval($data['porcentaje_promotor'] ?? 0);
        $comAmiri = $monto * ($porcentaje_amiri / 100);
        $comPromotor = $monto * ($porcentaje_promotor / 100);
        $montoAlCliente = $monto - $comAmiri - $comPromotor;
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $monto, 
            null, $cuenta_id, $cliente_id, null, null, null, null, 1, $servicio . ' - dinero recibido del cliente'
        ]);
        $actualizarSaldo($cuenta_id, $monto, 'entrada');
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $montoAlCliente, 
            null, $cuenta_cliente_id, $cliente_id, null, null, null, null, 1, $servicio . ' - efectivo recibido neto'
        ]);
        $actualizarSaldo($cuenta_cliente_id, $montoAlCliente, 'entrada');
        if ($comAmiri > 0) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comAmiri, 
                null, 1, null, null, null, null, null, 1, 'Comisión Amiri - ' . $servicio
            ]);
            $actualizarSaldo(1, $comAmiri, 'entrada');
        }
        if ($comPromotor > 0 && $cuenta_promotor_id) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comPromotor, 
                null, $cuenta_promotor_id, null, $promotor_id, null, null, null, 1, 'Comisión Promotor - ' . $servicio
            ]);
            $actualizarSaldo($cuenta_promotor_id, $comPromotor, 'entrada');
        }
        $stmtVenta = $pdo->prepare("INSERT INTO deudas_clientes (cliente_id, cuenta_cliente_id, movimiento_venta_id, monto_total, comision_amiri, comision_promotor, monto_neto_cliente, fecha, descripcion, estado, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 1)");
        $stmtVenta->execute([
            $cliente_id,
            $cuenta_cliente_id,
            $grupo_id,
            $monto,
            $comAmiri,
            $comPromotor,
            $montoAlCliente,
            $fecha,
            'Venta de efectivo - Pendiente de pago'
        ]);
    } else if ($servicio === 'Cambio de Flujo') {
        $origen_id = intval($data['origen_id'] ?? 0);
        $destino_id = intval($data['destino_id'] ?? 0);
        if (!$origen_id || !$destino_id) {
            throw new Exception('Cuentas origen/destino requeridas');
        }
        $cuentaOrigenStmt = $pdo->prepare("SELECT tipo_moneda FROM cuentas WHERE id = ?");
        $cuentaOrigenStmt->execute([$origen_id]);
        $cuentaOrigen = $cuentaOrigenStmt->fetch(PDO::FETCH_ASSOC);
        $cuentaDestinoStmt = $pdo->prepare("SELECT tipo_moneda FROM cuentas WHERE id = ?");
        $cuentaDestinoStmt->execute([$destino_id]);
        $cuentaDestino = $cuentaDestinoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$cuentaOrigen || !$cuentaDestino) {
            throw new Exception('Cuentas no encontradas');
        }
        $monedaTransferencia = $cuentaOrigen['tipo_moneda'];
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'traspaso', $monedaTransferencia, $monto, $origen_id, $destino_id, null, null, null, null, null, null, 'Cambio de flujo - transferencia entre cuentas'
        ]);
        $actualizarSaldo($origen_id, $monto, 'salida');
        $actualizarSaldo($destino_id, $monto, 'entrada');
    } else if ($servicio === 'Retiro de Efectivo') {
        $cuenta_bancaria_id = intval($data['cuenta_bancaria_id'] ?? 0);
        $tipo_retiro = $data['tipo_retiro'] ?? '';
        $cajaStmt = $pdo->query("SELECT id FROM cuentas WHERE tipo_cuenta = 2 AND es_caja_empresa = 1 AND activo = 1 LIMIT 1");
        $cajaEfectivo = $cajaStmt->fetch(PDO::FETCH_ASSOC);
        if (!$cajaEfectivo) {
            throw new Exception('No se encontró la Caja de Efectivo del sistema');
        }
        $caja_efectivo_id = $cajaEfectivo['id'];
        if (!$cuenta_bancaria_id) {
            throw new Exception('Cuenta bancaria requerida');
        }
        if (!$tipo_retiro) {
            throw new Exception('Tipo de retiro requerido');
        }
        $cuentaBancariaStmt = $pdo->prepare("SELECT tipo_cuenta, tipo_moneda FROM cuentas WHERE id = ?");
        $cuentaBancariaStmt->execute([$cuenta_bancaria_id]);
        $cuentaBancaria = $cuentaBancariaStmt->fetch(PDO::FETCH_ASSOC);
        if (!$cuentaBancaria || $cuentaBancaria['tipo_cuenta'] != 1) {
            throw new Exception('Debe seleccionar una cuenta bancaria');
        }
        $tipo_retiro_texto = $tipo_retiro == 'sin_tarjeta' ? 'Sin Tarjeta' : 'Cobro en Cheque';
        $descripcion = "Retiro de Efectivo - {$tipo_retiro_texto}";
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'traspaso', 'MXN', $monto, $cuenta_bancaria_id, $caja_efectivo_id, null, null, null, null, $tipo_retiro, null, $descripcion
        ]);
        $actualizarSaldo($cuenta_bancaria_id, $monto, 'salida');
        $actualizarSaldo($caja_efectivo_id, $monto, 'entrada');
    } else {
        throw new Exception('Servicio no reconocido: ' . $servicio);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
