<?php
$dbName = $argv[1] ?? 'amiri_reparada';
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=$dbName;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$originales = [
    1 => 6281.34,     2 => 2260357.40,  24 => 0.00,       25 => 0.00,
    26 => 0.00,       32 => 113801.36,  34 => 648369.72,  36 => 325132.61,
    39 => 0.00,       51 => 121640.17,  52 => 30.00,      180 => 0.00,
    181 => 158400.00, 182 => 318.84,    183 => 0.00,      184 => 0.00,
    185 => 0.00,      186 => 0.00,
];

echo "Revirtiendo saldo_actual a valores originales del dump...\n";
$revertidos = 0;
foreach ($originales as $id => $valor) {
    $stmt = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id=?");
    $stmt->execute([$id]);
    $actual = (float)$stmt->fetchColumn();
    if (abs($actual - $valor) > 0.01) {
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $upd = $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?");
        $upd->execute([$valor, $id]);
        echo "  #$id: " . number_format($actual, 2) . " → " . number_format($valor, 2) . "\n";
        $revertidos++;
    }
}
echo "Total: $revertidos cuenta(s) revertidas.\n";
