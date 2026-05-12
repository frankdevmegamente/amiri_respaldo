<?php
/**
 * Investigar faltante de $-177,767.46 MXN
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

echo "=== INVESTIGACIÓN: $-177,767.46 MXN ===\n\n";

// 1. Buscar en cuentas (valor absoluto)
echo "1. CUENTAS CON SALDO CERCANO A -177,767.46:\n";
$sql = "SELECT id, nombre, saldo_actual FROM cuentas 
        WHERE ABS(saldo_actual - (-177767.46)) < 1000 AND activo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($results) > 0) {
    foreach ($results as $r) {
        echo "   #{$r['id']} {$r['nombre']}: {$r['saldo_actual']}\n";
    }
} else {
    echo "   No se encontró ese saldo exacto en cuentas\n";
}
echo "\n";

// 2. Buscar en movimientos
echo "2. MOVIMIENTOS CON MONTO CERCANO A 177,767.46:\n";
$sql = "SELECT id, tipo, monto, descripcion FROM movimientos 
        WHERE ABS(monto - 177767.46) < 1000 AND activo = 1 LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($results) > 0) {
    foreach ($results as $r) {
        echo "   #{$r['id']} {$r['tipo']} \${$r['monto']} - " . substr($r['descripcion'], 0, 40) . "\n";
    }
} else {
    echo "   No se encontró ese monto en movimientos\n";
}
echo "\n";

// 3. Suma de saldos negativos
echo "3. SUM A DE SALDOS NEGATIVOS (MXN):\n";
$sql = "SELECT SUM(saldo_actual) as total_negativo FROM cuentas 
        WHERE activo = 1 AND saldo_actual < 0 AND tipo_moneda = 'MXN'";
$total_neg = $pdo->query($sql)->fetchColumn();
echo "   Total saldos negativos: " . number_format($total_neg, 2) . "\n";
echo "   Diferencia con -177,767.46: " . number_format(abs($total_neg - (-177767.46)), 2) . "\n\n";

// 4. Verificar si es la suma de 2 cuentas específicas
echo "4. ANÁLISIS DE CUENTAS NEGATIVAS:\n";
$sql = "SELECT id, nombre, saldo_actual FROM cuentas 
        WHERE activo = 1 AND saldo_actual < 0 ORDER BY saldo_actual ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cuentas_neg = $stmt->fetchAll(PDO::FETCH_ASSOC);

$suma_neg = 0;
foreach ($cuentas_neg as $c) {
    echo "   #{$c['id']} {$c['nombre']}: " . number_format($c['saldo_actual'], 2) . "\n";
    $suma_neg += $c['saldo_actual'];
}
echo "   SUM A: " . number_format($suma_neg, 2) . "\n\n";

// 5. Revisar si el número viene de "Utilidades AMIRI" + "Makalan"
echo "5. ANÁLISIS: Utilidades AMIRI + Makalan:\n";
$sql = "SELECT saldo_actual FROM cuentas WHERE id IN (1, 32)";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$utilidades = $cuentas[0]['saldo_actual'];
$makalan = $cuentas[1]['saldo_actual'];
$suma = $utilidades + $makalan;
echo "   Utilidades AMIRI (#1): " . number_format($utilidades, 2) . "\n";
echo "   Makalan Sa De Cv (#32): " . number_format($makalan, 2) . "\n";
echo "   Suma: " . number_format($suma, 2) . "\n";
echo "   Diferencia con -177,767.46: " . number_format(abs($suma - (-177767.46)), 2) . "\n\n";

// 6. Preguntar qué reporte muestra ese número
echo "6. ¿DE DÓNDE SALE ESE NÚMERO?\n";
echo "   Por favor indica en qué pantalla o reporte ves el faltante de $-177,767.46\n";
echo "   Algunas posibilidades:\n";
echo "   - Página de inicio (dashboard)\n";
echo "   - Reporte de saldos\n";
echo "   - Cálculo de ganancias\n";
echo "   - Vista de cuentas\n\n";

// 7. Buscar en código PHP cualquier cálculo con ese número
echo "7. BUSCANDO EN ARCHIVOS PHP...\n";
$archivos = glob(__DIR__ . "/*.php");
foreach ($archivos as $archivo) {
    $contenido = file_get_contents($archivo);
    if (strpos($contenido, '177767') !== false || strpos($contenido, '177,767') !== false) {
        echo "   Encontrado en: " . basename($archivo) . "\n";
    }
}

echo "\n=== FIN DE INVESTIGACIÓN ===\n";
