<?php
/**
 * Verificar valores exactos de la diferencia
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

echo "=== VERIFICACIÓN DE DIFERENCIA ===\n\n";

// 1. SALDOS (cuentas tipo 1,2 MXN + inventario FIFO + préstamos otorgados)
echo "1. TOTAL SALDOS:\n";

$stmtMxn = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2) AND tipo_moneda='MXN'");
$totalSaldosMxn = $stmtMxn->fetchColumn();
echo "   Cuentas tipo 1,2 MXN: " . number_format($totalSaldosMxn, 2) . "\n";

$stmtDiv = $pdo->query("SELECT COALESCE(SUM(im.cantidad_restante * im.paridad),0) AS total FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE c.activo=1 AND c.tipo_cuenta IN(1,2) AND im.activo=1");
$totalSaldosDiv = $stmtDiv->fetchColumn();
echo "   Inventario FIFO (MXN): " . number_format($totalSaldosDiv, 2) . "\n";

$stmtPrestOtorgados = $pdo->query("SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS total FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1");
$totalPrestamosOtorgados = $stmtPrestOtorgados->fetchColumn();
echo "   Préstamos otorgados: " . number_format($totalPrestamosOtorgados, 2) . "\n";

$totalSaldos = $totalSaldosMxn + $totalSaldosDiv + $totalPrestamosOtorgados;
echo "   TOTAL SALDOS: " . number_format($totalSaldos, 2) . "\n\n";

// 2. CAPITAL (cuentas tipo 3 + deudas_clientes + promotores)
echo "2. TOTAL CAPITAL:\n";

$stmtCap = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta = 3");
$totalCapital = $stmtCap->fetchColumn();
echo "   Cuentas tipo 3 (Capital): " . number_format($totalCapital, 2) . "\n";

$stmtCliMxn = $pdo->query("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL)");
$totalCliMxn = $stmtCliMxn->fetchColumn();
echo "   Deudas clientes MXN: " . number_format($totalCliMxn, 2) . "\n";
$totalCapital += $totalCliMxn;

$stmtProm = $pdo->query("SELECT COALESCE(SUM(c.saldo_actual),0) AS total FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01");
$totalProm = $stmtProm->fetchColumn();
echo "   Promotores externos: " . number_format($totalProm, 2) . "\n";
$totalCapital += $totalProm;

// USD/USDT deudas clientes convertidas
foreach (['USD', 'USDT'] as $div) {
    $stmtP = $pdo->prepare("SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0) AS p FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE im.activo=1 AND c.tipo_moneda=:m");
    $stmtP->execute(['m' => $div]);
    $paridad = $stmtP->fetchColumn();
    
    $stmtCli = $pdo->prepare("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND moneda=:m");
    $stmtCli->execute(['m' => $div]);
    $totalCliDiv = $stmtCli->fetchColumn();
    
    echo "   Deudas clientes $div (convertido): " . number_format($totalCliDiv * $paridad, 2) . " (paridad: $paridad)\n";
    $totalCapital += ($totalCliDiv * $paridad);
}

echo "   TOTAL CAPITAL: " . number_format($totalCapital, 2) . "\n\n";

// 3. OBLIGACIONES (prestamistas + proveedores)
echo "3. TOTAL OBLIGACIONES:\n";

$stmtPrest = $pdo->query("SELECT COALESCE(SUM(monto-monto_pagado),0) AS total FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1");
$totalPrestamistas = $stmtPrest->fetchColumn();
echo "   Préstamos empresa: " . number_format($totalPrestamistas, 2) . "\n";

$stmtProv = $pdo->query("
    SELECT COALESCE(SUM(c.valor_mxn), 0) AS total 
    FROM proveedores p 
    INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id)
    WHERE p.activo = 1
");
$totalProveedores = $stmtProv->fetchColumn();
echo "   Proveedores (valor_mxn): " . number_format($totalProveedores, 2) . "\n";

$totalObligaciones = $totalPrestamistas + $totalProveedores;
echo "   TOTAL OBLIGACIONES: " . number_format($totalObligaciones, 2) . "\n\n";

// 4. DIFERENCIA
$diferencia = round($totalSaldos - $totalCapital - $totalObligaciones, 2);
echo "4. DIFERENCIA (Saldos - Capital - Obligaciones):\n";
echo "   $totalSaldos - $totalCapital - $totalObligaciones = $diferencia\n\n";

if (abs($diferencia) < 0.01) {
    echo "✅ CUADRA PERFECTO\n";
} else {
    echo "⚠️  FALTANTE: " . number_format(abs($diferencia), 2) . " MXN\n";
    echo "   Esto significa que tienes más deudas (Capital + Obligaciones) que saldos reales\n";
}

echo "\n=== FIN ===\n";
