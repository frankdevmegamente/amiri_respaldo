<?php

// api/flujo.php

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/validator.php';
require_once __DIR__ . '/../src/config/helpers.php';



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

    validarMesNoCerrado($pdo, $fecha);
    validarMesNoMayorQueSesion($fecha);

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

    // Usar la función centralizada de helpers.php que SÍ registra en historial_saldos
    // $actualizarSaldo = function($cuenta_id, $monto, $tipo_operacion) use ($pdo) { ... }  // ELIMINADO

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

        // Redondear estrictamente a 2 decimales para evitar fantasmas de centavos
        $comAmiri = round($monto * ($porcentaje_amiri / 100), 2);
        $comPromotor = round($monto * ($porcentaje_promotor / 100), 2);
        $montoAlCliente = round($monto - $comAmiri - $comPromotor, 2);

        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $montoAlCliente, 
            null, $cuenta_cliente_id, $cliente_id, null, null, null, null, 1, $servicio . ' - efectivo recibido neto'
        ]);
        $mov_id2 = $pdo->lastInsertId();
        actualizarSaldoCuenta($pdo, $cuenta_cliente_id, $montoAlCliente, 'entrada', $mov_id2, $servicio . ' - efectivo recibido neto');

        if ($comAmiri > 0) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comAmiri, 
                null, 1, null, null, null, null, null, 1, 'Comisión Amiri - ' . $servicio
            ]);
            $mov_id3 = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, 1, $comAmiri, 'entrada', $mov_id3, 'Comisión Amiri - ' . $servicio);
        }
        if ($comPromotor > 0 && $cuenta_promotor_id) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comPromotor, 
                null, $cuenta_promotor_id, null, $promotor_id, null, null, null, 1, 'Comisión Promotor - ' . $servicio
            ]);
            $mov_id4 = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, $cuenta_promotor_id, $comPromotor, 'entrada', $mov_id4, 'Comisión Promotor - ' . $servicio);
        }

        // Registrar el ingreso a la cuenta seleccionada (Caja, Banco, etc.)
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $monto,
            null, $cuenta_id, $cliente_id, null, null, null, null, 1, 'Ingreso por ' . $servicio
        ]);
        $mov_id_cuenta = $pdo->lastInsertId();
        actualizarSaldoCuenta($pdo, $cuenta_id, $monto, 'entrada', $mov_id_cuenta, 'Ingreso por ' . $servicio);

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

    } else if ($servicio === 'Compra de Efectivo') {

        if (!$cuenta_cliente_id) {
            throw new Exception('El cliente no tiene cuenta/caja asociada');
        }
        if (!$cuenta_id) {
            throw new Exception('Debe seleccionar una cuenta/caja para recibir el efectivo');
        }

        $porcentaje_amiri = floatval($data['porcentaje_amiri'] ?? 0);
        $porcentaje_promotor = floatval($data['porcentaje_promotor'] ?? 0);

        $comAmiri = round($monto * ($porcentaje_amiri / 100), 2);
        $comPromotor = round($monto * ($porcentaje_promotor / 100), 2);
        $montoAlCliente = round($monto - $comAmiri - $comPromotor, 2);

        if ($montoAlCliente <= 0) {
            throw new Exception('El monto al cliente debe ser mayor a $0. Las comisiones suman ' . ($porcentaje_amiri + $porcentaje_promotor) . '%');
        }

        $tipo_pago = $data['tipo_pago'] ?? 'credito';
        $cuenta_pago_id = $data['cuenta_pago_id'] ?? null;

        // 1) ENTRADA to the selected cuenta (caja/banco) — full monto recibido del cliente
        $stmtMovimiento->execute([
            $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $monto,
            null, $cuenta_id, $cliente_id, null, null, null, null, 1, 'Ingreso por ' . $servicio
        ]);
        $mov_id1 = $pdo->lastInsertId();
        actualizarSaldoCuenta($pdo, $cuenta_id, $monto, 'entrada', $mov_id1, 'Ingreso por ' . $servicio);

        // 2) COMISION from Utilidades (gasto)
        if ($comAmiri > 0) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comAmiri,
                1, null, null, null, null, null, null, 1, 'Comisión Amiri - ' . $servicio
            ]);
            $mov_id2 = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, 1, $comAmiri, 'salida', $mov_id2, 'Comisión Amiri - ' . $servicio);
        }

        // 3) COMISION from Promotor (gasto)
        if ($comPromotor > 0 && $cuenta_promotor_id) {
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comPromotor,
                $cuenta_promotor_id, null, null, $promotor_id, null, null, null, 1, 'Comisión Promotor - ' . $servicio
            ]);
            $mov_id3 = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, $cuenta_promotor_id, $comPromotor, 'salida', $mov_id3, 'Comisión Promotor - ' . $servicio);
        }

        // 4) SALIDA to cliente (contado) o deuda (credito)
        if ($tipo_pago === 'contado') {
            if (!$cuenta_pago_id) {
                throw new Exception('Debe seleccionar una cuenta para realizar el pago');
            }
            // Validar saldo
            $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
            $stmtSaldo->execute([$cuenta_pago_id]);
            $saldoPago = (float)$stmtSaldo->fetchColumn();
            if ($montoAlCliente > $saldoPago + 0.001) {
                throw new Exception('Saldo insuficiente en la cuenta de pago. Disponible: $' . number_format($saldoPago, 2));
            }
            $stmtMovimiento->execute([
                $grupo_id, $fecha, $servicio_id, 'salida', 'MXN', $montoAlCliente,
                $cuenta_pago_id, null, $cliente_id, null, null, null, null, 1, 'Pago por ' . $servicio
            ]);
            $mov_id4 = $pdo->lastInsertId();
            actualizarSaldoCuenta($pdo, $cuenta_pago_id, $montoAlCliente, 'salida', $mov_id4, 'Pago por ' . $servicio);
        } else {
            // Crédito: registrar deuda de AMIRI hacia el cliente
            $stmtDeuda = $pdo->prepare("INSERT INTO deudas_clientes (cliente_id, cuenta_cliente_id, movimiento_venta_id, monto_total, comision_amiri, comision_promotor, monto_neto_cliente, fecha, descripcion, estado, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 1)");
            $stmtDeuda->execute([
                $cliente_id,
                $cuenta_cliente_id,
                $grupo_id,
                $monto,
                $comAmiri,
                $comPromotor,
                $montoAlCliente,
                $fecha,
                'Compra de efectivo - Pendiente de pago al cliente'
            ]);
        }

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

        actualizarSaldoCuenta($pdo, $origen_id, $monto, 'salida');

        actualizarSaldoCuenta($pdo, $destino_id, $monto, 'entrada');

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

        // Validar saldo suficiente
        $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
        $stmtSaldo->execute([$cuenta_bancaria_id]);
        $saldoActual = (float)$stmtSaldo->fetchColumn();
        if ($monto > $saldoActual + 0.001) {
            throw new Exception('Saldo insuficiente en la cuenta bancaria. Disponible: $' . number_format($saldoActual, 2) . ' MXN');
        }

        $tipo_retiro_texto = $tipo_retiro == 'sin_tarjeta' ? 'Sin Tarjeta' : 'Cobro en Cheque';

        $descripcion = "Retiro de Efectivo - {$tipo_retiro_texto}";

        $stmtMovimiento->execute([

            $grupo_id, $fecha, $servicio_id, 'traspaso', 'MXN', $monto, $cuenta_bancaria_id, $caja_efectivo_id, null, null, null, null, $tipo_retiro, null, $descripcion

        ]);

        actualizarSaldoCuenta($pdo, $cuenta_bancaria_id, $monto, 'salida');

        actualizarSaldoCuenta($pdo, $caja_efectivo_id, $monto, 'entrada');

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
