<?php

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido', 405);
}

$pdo = getPDO();

try {
    $pdo->beginTransaction();

    // Disable FK checks temporarily for bulk delete
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Zero out account balances
    $pdo->exec("UPDATE cuentas SET saldo_actual = 0, valor_mxn = 0");

    // 2. Delete financial transaction records
    $pdo->exec("DELETE FROM movimientos");
    $pdo->exec("DELETE FROM historial_saldos");
    $pdo->exec("DELETE FROM consumos_inventario");
    $pdo->exec("DELETE FROM inventario_monedas");

    // 3. Delete debts and loans
    $pdo->exec("DELETE FROM deudas_clientes");
    $pdo->exec("DELETE FROM deudas_empresa");
    $pdo->exec("DELETE FROM prestamos_otorgados");

    // 4. Delete expenses and payments
    $pdo->exec("DELETE FROM gastos");
    $pdo->exec("DELETE FROM pagos_promotores");

    // 5. Delete monthly closings and capital withdrawals
    $pdo->exec("DELETE FROM cierres_mensuales");
    $pdo->exec("DELETE FROM retiros_capital");

    // Re-enable FK checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 6. Log the reset action
    $stmt = $pdo->prepare("
        INSERT INTO auditoria (usuario_id, accion, modulo, descripcion, ip_address, created_at)
        VALUES (?, 'RESET_DATOS', 'SISTEMA', 'Reset completo de datos financieros: cuentas, movimientos, inventario, deudas, gastos, cierres', ?, NOW())
    ");
    $stmt->execute([$_SESSION['usuario_id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'message' => 'Datos financieros reseteados correctamente. Cuentas, usuarios y catálogos conservados.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    jsonError('Error al resetear datos: ' . $e->getMessage(), 500);
}
