<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAuth(['admin', 'usuario']);

if (($_SESSION['usuario_rol'] ?? '') === 'usuario' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonError('Solo lectura', 403);
}

$pdo = getPDO();



try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = json_decode(file_get_contents('php://input'), true);

        

        $proveedor_id   = (int)($data['proveedor_id']   ?? 0);

        $cuenta_pago_id = (int)($data['cuenta_pago_id'] ?? 0);

        $monto_mxn      = (float)($data['monto']        ?? 0);  // siempre en MXN

        $paridad        = (float)($data['paridad']       ?? 0);  // TC compra promedio

        $moneda_pago    = strtoupper($data['moneda']     ?? 'USD'); // USD o USDT
        $fecha          = $data['fecha'] ?? date('Y-m-d');

        validarMesNoCerrado($pdo, $fecha);



        if (!$proveedor_id || !$cuenta_pago_id || !$monto_mxn) {

            http_response_code(400);

            echo json_encode(['error' => 'Datos incompletos']);

            exit;

        }



        // Obtener datos del proveedor — incluye las cuentas por moneda

        $stmtProveedor = $pdo->prepare("SELECT cuenta_id, cuenta_usd_id, cuenta_usdt_id, cuenta_mxn_id, tipo_moneda, nombre FROM proveedores WHERE id = ?");

        $stmtProveedor->execute([$proveedor_id]);

        $proveedor = $stmtProveedor->fetch(PDO::FETCH_ASSOC);



        // Determinar la cuenta del proveedor según la moneda a pagar

        if ($moneda_pago === 'MXN') {

            $cuentaProveedorId = $proveedor['cuenta_mxn_id'] ?? $proveedor['cuenta_id'];

        } else if ($moneda_pago === 'USD') {

            $cuentaProveedorId = $proveedor['cuenta_usd_id'] ?? $proveedor['cuenta_id'];

        } else {

            $cuentaProveedorId = $proveedor['cuenta_usdt_id'] ?? $proveedor['cuenta_id'];

        }



        if (!$proveedor || !$cuentaProveedorId) {

            http_response_code(404);

            echo json_encode(['error' => 'Proveedor no encontrado o sin cuenta asociada para ' . $moneda_pago]);

            exit;

        }



        // Validar que la cuenta de pago existe y es MXN

        $stmtCuentaPago = $pdo->prepare("SELECT tipo_moneda, saldo_actual, nombre FROM cuentas WHERE id = ?");

        $stmtCuentaPago->execute([$cuenta_pago_id]);

        $cuentaPago = $stmtCuentaPago->fetch(PDO::FETCH_ASSOC);



        if (!$cuentaPago) {

            http_response_code(404);

            echo json_encode(['error' => 'Cuenta de pago no encontrada']);

            exit;

        }



        if ($cuentaPago['saldo_actual'] < $monto_mxn) {

            http_response_code(400);

            echo json_encode(['error' => 'Saldo insuficiente en la cuenta de pago']);

            exit;

        }



        $monedaProveedor = $moneda_pago;

        // Equivalente en divisa del monto MXN pagado (para MXN es 1:1)

        $monto_divisa = ($moneda_pago === 'MXN') ? $monto_mxn : (($paridad > 0) ? round($monto_mxn / $paridad, 8) : 0);



        // Iniciar transacción

        $pdo->beginTransaction();



        try {

            $grupo_id = round(microtime(true) * 1000);



            // 1. Restar MXN de la cuenta de pago
            $pdo->exec("SET @hyperion_saldo_auth = 1");
            $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                ->execute([$monto_mxn, $cuenta_pago_id]);

            // 2. Reducir el adeudo del proveedor

            $pdo->exec("SET @hyperion_saldo_auth = 1");

            if ($moneda_pago === 'MXN') {

                // Para MXN: solo reducir saldo_actual (ya es MXN)

                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")

                    ->execute([$monto_mxn, $cuentaProveedorId]);

            } else {

                // Para USD/USDT: reducir saldo_actual (divisa) y valor_mxn

                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ?, valor_mxn = valor_mxn - ? WHERE id = ?")

                    ->execute([$monto_divisa, $monto_mxn, $cuentaProveedorId]);

            }



            // 3. Registrar movimiento en MXN

            $stmtFlujo = $pdo->prepare("

                INSERT INTO movimientos

                (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, proveedor_id, paridad, descripcion, usuario_id, activo)

                VALUES (?, ?, 11, 'salida', 'MXN', ?, ?, ?, ?, ?, ?, 1)

            ");

            $stmtFlujo->execute([

                $grupo_id,

                $fecha,

                $monto_mxn,

                $cuenta_pago_id,

                $proveedor_id,

                $paridad,

                "Pago a proveedor {$proveedor['nombre']} — {$monto_divisa} {$monedaProveedor}" . ($moneda_pago !== 'MXN' ? " (TC: {$paridad})" : ""),

                $_SESSION['usuario_id']

            ]);

            

            $pdo->commit();

            

            http_response_code(200);

            echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);

        } catch (Exception $e) {

            $pdo->rollBack();

            throw $e;

        }

    } else {

        http_response_code(405);

        echo json_encode(['error' => 'Método no permitido']);

    }

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);

}

?>

