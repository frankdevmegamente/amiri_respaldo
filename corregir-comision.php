<?php
$host = 'localhost';
$db   = 'guiaspar_amiri_db_sandbox';
$user = 'guiaspar_root';
$pass = '@m!r!App.001';
$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
echo "<pre>";
$mov416 = $pdo->query("SELECT * FROM movimientos WHERE id=416")->fetch();
$mov415 = $pdo->query("SELECT * FROM movimientos WHERE id=415")->fetch();
$dc26   = $pdo->query("SELECT * FROM deudas_clientes WHERE id=26")->fetch();
$cta1   = $pdo->query("SELECT * FROM cuentas WHERE id=1")->fetch();
$cta181 = $pdo->query("SELECT * FROM cuentas WHERE id=181")->fetch();
echo "Antes:\n";
echo "  movimientos ID=416 (Amiri): {$mov416['monto']}\n";
echo "  movimientos ID=415 (neto):  {$mov415['monto']}\n";
echo "  deudas_cli ID=26 (amiri):   {$dc26['comision_amiri']}\n";
echo "  deudas_cli ID=26 (neto):    {$dc26['monto_neto_cliente']}\n";
echo "  cuentas ID=1 (Amiri):       {$cta1['saldo_actual']}\n";
echo "  cuentas ID=181 (cliente):   {$cta181['saldo_actual']}\n\n";
if (!isset($_GET['confirmar'])) {
    echo "Agrega ?confirmar=1 a la URL para ejecutar.\n";
    exit;
}
$pdo->beginTransaction();
try {
    $pdo->exec("UPDATE movimientos SET monto=4000.000000 WHERE id=416");
    $pdo->exec("UPDATE movimientos SET monto=495000.000000 WHERE id=415");
    $pdo->exec("UPDATE deudas_clientes SET comision_amiri=4000.00, monto_neto_cliente=495000.00 WHERE id=26");
    $pdo->exec("UPDATE cuentas SET saldo_actual = saldo_actual - 1000 WHERE id=1");
    $pdo->exec("UPDATE cuentas SET saldo_actual = saldo_actual + 1000 WHERE id=181");
    $pdo->commit();
    echo "CORRECCIÓN APLICADA.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";