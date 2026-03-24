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

if (!function_exists('getPDO')) {
    function getPDO() {
        static $pdo = null;
        if ($pdo === null) {
            $requireEnv = __DIR__ . '/env.php';
            if (file_exists($requireEnv)) {
                require_once $requireEnv;
            }
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'guiaspar_amiri_db';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? 'TuContraseñaSegura';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }
        return $pdo;
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
    
    // Calcular nuevo saldo
    if ($tipo === 'entrada') {
        $saldo_nuevo = $saldo_anterior + abs($monto);
    } else {
        $saldo_nuevo = $saldo_anterior - abs($monto);
    }
    
    // Actualizar saldo en la cuenta
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