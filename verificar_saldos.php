<?php
/**
 * Verificar saldos y encontrar el faltante de $-177,767.46
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

echo "=== VERIFICACIÓN DE SALDOS ===\n\n";

// 1. Suma de todos los saldos MXN activos
$sql_mxn = "SELECT SUM(saldo_actual) as total FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN'";
$total_mxn = $pdo->query($sql_mxn)->fetchColumn();
echo "1. SUMA DE SALDOS MXN (cuentas activas): " . number_format($total_mxn, 2) . "\n\n";

// 2. Desglose por cuenta MXN
$sql_cuentas = "SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN' ORDER BY saldo_actual DESC";
$stmt = $pdo->prepare($sql_cuentas);
$stmt->execute();
$cuentas_mxn = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "2. DESGLOSE DE CUENTAS MXN:\n";
foreach ($cuentas_mxn as $c) {
    $signo = $c['saldo_actual'] >= 0 ? '+' : '';
    echo "   #{$c['id']} {$c['nombre']}: {$signo}" . number_format($c['saldo_actual'], 2) . "\n";
}
echo "\n";

// 3. Suma teórica de movimientos MXN
$sql_mov = "
    SELECT 
        SUM(CASE WHEN tipo = 'entrada' THEN monto WHEN tipo = 'salida' THEN -monto ELSE 0 END) as balance_mxn
    FROM movimientos 
    WHERE activo = 1 AND estado = 'activo' AND moneda = 'MXN'
";
$balance_mxn = $pdo->query($sql_mov)->fetchColumn();
echo "3. BALANCE TEÓRICO MXN (entradas - salidas): " . number_format($balance_mxn, 2) . "\n\n";

// 4. Diferencia
$diferencia = $total_mxn - $balance_mxn;
echo "4. DIFERENCIA (Saldos - Teórico): " . number_format($diferencia, 2) . "\n";
if (abs($diferencia) < 0.01) {
    echo "   ✅ Los saldos coinciden con los movimientos\n";
} else {
    echo "   ⚠️ Hay una diferencia de " . number_format(abs($diferencia), 2) . "\n";
}
echo "\n";

// 5. Buscar el faltante de -177,767.46
echo "5. BUSCANDO FALTANTE DE $-177,767.46...\n";

// Verificar si hay alguna cuenta, movimiento o cálculo que dé ese número
$sql_buscar = "
    SELECT 
        'Movimiento' as tipo, id, monto, tipo as mov_tipo, descripcion
    FROM movimientos 
    WHERE ABS(monto - 177767.46) < 0.01 AND activo = 1 AND estado = 'activo'
    UNION ALL
    SELECT 
        'Cuenta' as tipo, id, saldo_actual, tipo_moneda, nombre
    FROM cuentas 
    WHERE ABS(saldo_actual - (-177767.46)) < 0.01 AND activo = 1
    UNION ALL
    SELECT 
        'Historial' as tipo, id, monto, tipo as mov_tipo, CONCAT('Cuenta: ', cuenta_id)
    FROM historial_saldos 
    WHERE ABS(monto - 177767.46) < 0.01
";

$stmt = $pdo->prepare($sql_buscar);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($resultados) > 0) {
    echo "   Encontrado:\n";
    foreach ($resultados as $r) {
        echo "   - {$r['tipo']} #{$r['id']}: " . number_format($r['monto'], 2) . " ({$r['mov_tipo']})\n";
    }
} else {
    echo "   No se encontró ese monto exacto\n";
    echo "   Buscando montos cercanos...\n";
    
    $sql_cercano = "
        SELECT id, monto, tipo, descripcion
        FROM movimientos 
        WHERE monto > 177000 AND monto < 178000 AND activo = 1
        LIMIT 10
    ";
    $stmt = $pdo->prepare($sql_cercano);
    $stmt->execute();
    $cercanos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($cercanos) > 0) {
        echo "   Montos cercanos encontrados:\n";
        foreach ($cercanos as $c) {
            echo "   - Movimiento #{$c['id']}: " . number_format($c['monto'], 2) . " ({$c['tipo']}) - {$c['descripcion']}\n";
        }
    }
}
echo "\n";

// 6. Verificar si el "faltante" viene de cuentas inactivas
$sql_inactivas = "SELECT SUM(saldo_actual) as total_inactivo FROM cuentas WHERE activo = 0 AND tipo_moneda = 'MXN'";
$total_inactivo = $pdo->query($sql_inactivas)->fetchColumn();
echo "6. SALDOS DE CUENTAS INACTIVAS MXN: " . number_format($total_inactivo, 2) . "\n";

// 7. Verificar suma total (activas + inactivas)
$sql_total = "SELECT SUM(saldo_actual) as gran_total FROM cuentas WHERE tipo_moneda = 'MXN'";
$gran_total = $pdo->query($sql_total)->fetchColumn();
echo "7. GRAN TOTAL MXN (todas las cuentas): " . number_format($gran_total, 2) . "\n\n";

// 8. Verificar si el faltante es por alguna resta específica
// A veces el "faltante" se refiere a que el balance debería ser X pero es Y
echo "8. ANÁLISIS DEL FALTANTE:\n";
echo "   Si alguien dice 'faltan $-177,767.46', normalmente significa:\n";
echo "   - El saldo debería ser: " . number_format($balance_mxn, 2) . " (según movimientos)\n";
echo "   - El saldo real es: " . number_format($total_mxn, 2) . " (suma de cuentas)\n";
echo "   - Diferencia: " . number_format($diferencia, 2) . "\n";

// 9. Verificar si hay alguna cuenta con saldo negativo grande
$sql_negativos = "SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN' AND saldo_actual < -100000 ORDER BY saldo_actual ASC";
$stmt = $pdo->prepare($sql_negativos);
$stmt->execute();
$negativos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($negativos) > 0) {
    echo "\n9. CUENTAS CON SALDO NEGATIVO GRANDE:\n";
    foreach ($negativos as $n) {
        echo "   #{$n['id']} {$n['nombre']}: " . number_format($n['saldo_actual'], 2) . "\n";
    }
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
