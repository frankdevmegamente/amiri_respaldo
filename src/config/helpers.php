<?php

if (!function_exists('requireAuth')) {

    function requireAuth($role = null) {

        session_start();

        if (!isset($_SESSION['usuario_id'])) {

            header('Location: login.php');

            exit;

        }

        if ($role && $_SESSION['usuario_rol'] !== $role) {

            http_response_code(403);

            echo json_encode(['error' => 'Acceso denegado']);

            exit;

        }

    }

}





/**

 * Actualiza el saldo de una cuenta y registra el histórico

 * 

 * @param PDO $pdo Conexión a la base de datos

 * @param int $cuenta_id ID de la cuenta a actualizar

 * @param float $monto Monto del movimiento (positivo o negativo)

 * @param string $tipo 'entrada' o 'salida'

 * @param int|null $movimiento_id ID del movimiento que causa el cambio

 * @param string|null $descripcion Descripción del movimiento

 * @return array ['saldo_anterior' => float, 'saldo_nuevo' => float]

 */

function actualizarSaldoCuenta($pdo, $cuenta_id, $monto, $tipo, $movimiento_id = null, $descripcion = null) {

    // Obtener saldo actual

    $stmt = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE");

    $stmt->execute([$cuenta_id]);

    $saldo_anterior = (float)$stmt->fetchColumn();

    // Redondear monto a 2 decimales para evitar acumulación de errores
    $monto = round(abs($monto), 2);

    // Calcular nuevo saldo redondeado

    if ($tipo === 'entrada') {

        $saldo_nuevo = round($saldo_anterior + $monto, 2);

    } else {

        $saldo_nuevo = round($saldo_anterior - $monto, 2);

    }

    

    // Actualizar saldo en la cuenta
    // La variable de sesión autoriza el trigger trg_cuentas_before_update
    $pdo->exec("SET @hyperion_saldo_auth = 1");

    $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?");

    $stmt->execute([$saldo_nuevo, $cuenta_id]);

    

    // Registrar en histórico de movimientos de cuenta (solo si hay movimiento_id válido)

    if ($movimiento_id) {

        $stmt = $pdo->prepare("

            INSERT INTO historial_saldos

            (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)

            VALUES (?, ?, ?, ?, ?, ?)

        ");

        $stmt->execute([

            $cuenta_id,

            $movimiento_id,

            $tipo,

            abs($monto),

            $saldo_anterior,

            $saldo_nuevo

        ]);

    }

    

    return [

        'saldo_anterior' => $saldo_anterior,

        'saldo_nuevo' => $saldo_nuevo

    ];

}



/**

 * Sumar a saldo de cuenta (wrapper para entradas)

 */

function sumarSaldoCuenta($pdo, $cuenta_id, $monto, $movimiento_id = null, $descripcion = null) {

    return actualizarSaldoCuenta($pdo, $cuenta_id, $monto, 'entrada', $movimiento_id, $descripcion);

}



/**

 * Restar de saldo de cuenta (wrapper para salidas)

 */

function restarSaldoCuenta($pdo, $cuenta_id, $monto, $movimiento_id = null, $descripcion = null) {

    return actualizarSaldoCuenta($pdo, $cuenta_id, $monto, 'salida', $movimiento_id, $descripcion);

}

if (!function_exists('validarMesNoCerrado')) {

    function validarMesNoCerrado($pdo, $fecha, $mensajeExtra = '') {
        $mes = date('Y-m', strtotime($fecha));
        $stmt = $pdo->prepare("SELECT id FROM cierres_mensuales WHERE mes = ?");
        $stmt->execute([$mes]);
        if ($stmt->fetch()) {
            $msg = "No puedes registrar operaciones en el mes $mes porque ya está cerrado (ya se generó el corte mensual). Elige una fecha en un mes que aún no haya sido cerrado.";
            if ($mensajeExtra) {
                $msg .= ' ' . $mensajeExtra;
            }
            throw new Exception($msg);
        }
    }

}

if (!function_exists('validarMesNoMayorQueSesion')) {

    function validarMesNoMayorQueSesion($fecha) {
        $mesFecha = date('Y-m', strtotime($fecha));
        $mesSesion = $_SESSION['mes_activo'] ?? date('Y-m');
        if ($mesFecha > $mesSesion) {
            throw new Exception("No puedes registrar operaciones en $mesFecha porque el sistema está en $mesSesion. Cambia al mes correspondiente en el encabezado.");
        }
    }

}
