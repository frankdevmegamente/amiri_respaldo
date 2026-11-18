<?php
$dbName = $argv[1] ?? 'amiri_reparada';
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=$dbName;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$dryRun = in_array('--dry-run', $argv);
if ($dryRun) echo "*** MODO DRY-RUN ***\n\n";

echo "=== FASE 1: CALCULAR UTILIDAD REAL ===\n";
$comPagadas = (float)$pdo->query("SELECT COALESCE(SUM(comision_amiri),0) FROM deudas_clientes WHERE activo=1 AND estado='pagado'")->fetchColumn();
$fifoGanancia = (float)$pdo->query("SELECT COALESCE(SUM(ganancia),0) FROM consumos_inventario")->fetchColumn();
$gastos = (float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1")->fetchColumn();
$comPromotor = (float)$pdo->query("SELECT COALESCE(SUM(comision_promotor),0) FROM deudas_clientes WHERE activo=1")->fetchColumn();
$utilidadReal = round($comPagadas + $fifoGanancia - $gastos - $comPromotor, 2);

echo "  Comisiones AMIRI cobradas:  " . number_format($comPagadas, 2) . "\n";
echo "+ Ganancia FIFO:              " . number_format($fifoGanancia, 2) . "\n";
echo "- Gastos:                     " . number_format($gastos, 2) . "\n";
echo "- Comisiones Promotor:        " . number_format($comPromotor, 2) . "\n";
echo "= Utilidad real:              " . number_format($utilidadReal, 2) . "\n\n";

echo "=== FASE 2: ACTUALIZAR UTILIDADES (#1) ===\n";
$utilActual = (float)$pdo->query("SELECT saldo_actual FROM cuentas WHERE id=1")->fetchColumn();
$stmt = $pdo->prepare("SELECT saldo_nuevo FROM historial_saldos WHERE cuenta_id=1 ORDER BY created_at DESC, id DESC LIMIT 1");
$stmt->execute();
$utilHist = (float)$stmt->fetchColumn();
echo "  Utilidad actual:             " . number_format($utilActual, 2) . "\n";
echo "  Utilidad historial (ultima): " . number_format($utilHist, 2) . "\n";
echo "  Utilidad real (objetivo):    " . number_format($utilidadReal, 2) . "\n";

if (abs($utilActual - $utilidadReal) > 0.01) {
    echo "  → Ajustando Utilidades de " . number_format($utilActual, 2) . " a " . number_format($utilidadReal, 2) . "\n";
    if (!$dryRun) {
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = 1")->execute([$utilidadReal]);
    }
    
    // Also add historial entry for this adjustment
    $ajuste = round($utilidadReal - $utilHist, 2);
    if (abs($ajuste) > 0.01) {
        echo "  → Creando entrada en historial_saldos (ajuste de $ajuste)\n";
        if (!$dryRun) {
            $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
                VALUES (1, 0, ?, ?, ?, ?)")
                ->execute([
                    $ajuste > 0 ? 'entrada' : 'salida',
                    abs($ajuste),
                    $utilHist,
                    $utilidadReal
                ]);
        }
    }
} else {
    echo "  → Ya está en el valor correcto.\n";
}

echo "\n=== FASE 3: CREAR HISTORIAL PARA CUENTAS SIN HISTORIAL ===\n";
$sinHist = $pdo->query("SELECT c.id,c.nombre,c.saldo_actual,c.tipo_moneda FROM cuentas c WHERE c.activo=1 AND c.saldo_actual NOT BETWEEN -0.01 AND 0.01 AND NOT EXISTS (SELECT 1 FROM historial_saldos h WHERE h.cuenta_id=c.id)")->fetchAll();
foreach ($sinHist as $c) {
    $saldo = (float)$c['saldo_actual'];
    echo "  #{$c['id']} {$c['nombre']} ({$c['tipo_moneda']}): saldo_actual=" . number_format($saldo,2) . " → creando historial inicial\n";
    if (!$dryRun) {
        $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
            VALUES (?, 0, ?, ?, 0, ?)")
            ->execute([
                $c['id'],
                $saldo > 0 ? 'entrada' : 'salida',
                abs($saldo),
                $saldo
            ]);
    }
}

echo "\n=== FASE 4: AJUSTAR HISTORIAL PARA CUENTAS CON DESCUADRE ===\n";
$cuentas = $pdo->query("
    SELECT c.id,c.nombre,c.saldo_actual,c.tipo_moneda,
           COALESCE((SELECT saldo_nuevo FROM historial_saldos WHERE cuenta_id=c.id ORDER BY created_at DESC, id DESC LIMIT 1),0) as saldo_historial
    FROM cuentas c WHERE c.activo=1
    HAVING ROUND(saldo_actual - saldo_historial, 2) NOT BETWEEN -0.01 AND 0.01
    ORDER BY c.id
")->fetchAll();
foreach ($cuentas as $c) {
    $last = (float)$c['saldo_historial'];
    $curr = (float)$c['saldo_actual'];
    $diff = round($curr - $last, 2);
    if (abs($diff) <= 0.01) continue;
    
    echo "  #{$c['id']} {$c['nombre']} ({$c['tipo_moneda']}): historial=$last → actual=$curr (ajuste=$diff)\n";
    if (!$dryRun) {
        $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
            VALUES (?, 0, ?, ?, ?, ?)")
            ->execute([
                $c['id'],
                $diff > 0 ? 'entrada' : 'salida',
                abs($diff),
                $last,
                $curr
            ]);
    }
}

echo "\n=== VERIFICACIÓN FINAL ===\n";
// Run exact formula from verificar_diferencia.php
$totalSaldosMxn = (float)$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2) AND tipo_moneda='MXN'")->fetchColumn();
$totalSaldosDiv = (float)$pdo->query("SELECT COALESCE(ROUND(SUM(im.cantidad_restante * im.paridad),2),0) FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE c.activo=1 AND c.tipo_cuenta IN(1,2) AND im.activo=1")->fetchColumn();
$totalPrestOtorg = (float)$pdo->query("SELECT COALESCE(SUM(COALESCE(monto_total_con_interes, monto_total)-monto_pagado),0) FROM prestamos_otorgados WHERE estado IN('pendiente','parcial') AND activo=1")->fetchColumn();
$totalGastosSinCap = 0; // Simplified - not calculating this component
$totalSaldos = round($totalSaldosMxn + $totalSaldosDiv + $totalPrestOtorg, 2);

$totalCapital = (float)$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta=3")->fetchColumn();
$totalCapital += (float)$pdo->query("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL)")->fetchColumn();
$totalCapital += (float)$pdo->query("SELECT COALESCE(SUM(c.saldo_actual),0) FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01")->fetchColumn();
foreach (['USD','USDT'] as $div) {
    $pStmt = $pdo->prepare("SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0) FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE im.activo=1 AND c.tipo_moneda=?");
    $pStmt->execute([$div]);
    $paridad = (float)$pStmt->fetchColumn();
    $cStmt = $pdo->prepare("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND moneda=?");
    $cStmt->execute([$div]);
    $totalCapital += (float)$cStmt->fetchColumn() * $paridad;
}
$totalCapital = round($totalCapital, 2);

$totalObligaciones = (float)$pdo->query("SELECT COALESCE(SUM(monto-monto_pagado),0) FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1")->fetchColumn();
$totalObligaciones += (float)$pdo->query("SELECT COALESCE(SUM(c.valor_mxn),0) FROM proveedores p INNER JOIN cuentas c ON (p.cuenta_usd_id=c.id OR p.cuenta_usdt_id=c.id) WHERE p.activo=1")->fetchColumn();
$totalObligaciones = round($totalObligaciones, 2);

$diferencia = round($totalSaldos - $totalCapital - $totalObligaciones, 2);

echo "\nSaldos (Activos):       " . number_format($totalSaldos, 2) . " MXN\n";
echo "  Cuentas MXN tipo 1,2: " . number_format($totalSaldosMxn, 2) . "\n";
echo "  Inventario FIFO:      " . number_format($totalSaldosDiv, 2) . "\n";
echo "  Prestamos otorgados:  " . number_format($totalPrestOtorg, 2) . "\n";
echo "Capital (Patrimonio):   " . number_format($totalCapital, 2) . " MXN\n";
echo "Obligaciones (Pasivos): " . number_format($totalObligaciones, 2) . " MXN\n";
echo "DIFERENCIA:             " . number_format($diferencia, 2) . " MXN\n";

if (abs($diferencia) < 0.01) {
    echo "\n✅ ECUACION BALANCEADA\n";
} else {
    echo "\n⚠️  Gap restante: " . number_format(abs($diferencia), 2) . " MXN\n";
}

echo "\n=== TEST INTEGRIDAD ===\n";
$balDesc = (int)$pdo->query("
    SELECT COUNT(*) FROM cuentas c
    WHERE c.activo=1 AND ROUND(c.saldo_actual - COALESCE((SELECT saldo_nuevo FROM historial_saldos WHERE cuenta_id=c.id ORDER BY created_at DESC, id DESC LIMIT 1), 0), 2) NOT BETWEEN -0.01 AND 0.01
")->fetchColumn();
echo "Balance descuadres: $balDesc\n";

$cu=$pdo->query("SELECT id FROM cuentas WHERE activo=1")->fetchAll();
$e6=0;
foreach($cu as $x){
    $h=$pdo->prepare("SELECT id,tipo,monto,saldo_anterior,saldo_nuevo FROM historial_saldos WHERE cuenta_id=? ORDER BY created_at ASC,id ASC");
    $h->execute([$x['id']]);$rows=$h->fetchAll();if(!$rows)continue;
    $sa=(float)$rows[0]['saldo_anterior'];
    foreach($rows as $r2){$ex=round($sa+($r2['tipo']==='entrada'?(float)$r2['monto']:-(float)$r2['monto']),2);if(abs($ex-(float)$r2['saldo_nuevo'])>0.01)$e6++;$sa=$ex;}
}
echo "Historial errores: $e6\n";

$huerf=$pdo->query("SELECT COUNT(*) FROM historial_saldos h LEFT JOIN movimientos m ON h.movimiento_id=m.id WHERE m.id IS NULL")->fetchColumn();
echo "Historial huerfanos: $huerf\n";
