<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=amiri_reparada;charset=utf8mb4','root','',[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
]);

echo "=== INTEGRIDAD ===\n";
$r=$pdo->query("SELECT COUNT(*) FROM cuentas c LEFT JOIN historial_saldos h ON h.id=(SELECT MAX(h2.id) FROM historial_saldos h2 WHERE h2.cuenta_id=c.id) WHERE c.activo=1 AND ROUND(c.saldo_actual-COALESCE(h.saldo_nuevo,0),2)>0.01")->fetchColumn();
echo "Balance descuadres: $r (esperado 18)\n";

$cu=$pdo->query("SELECT id FROM cuentas WHERE activo=1")->fetchAll();
$e6=0;
foreach($cu as $x){
    $h=$pdo->prepare("SELECT id,tipo,monto,saldo_anterior,saldo_nuevo FROM historial_saldos WHERE cuenta_id=? ORDER BY created_at ASC,id ASC");
    $h->execute([$x['id']]);$rows=$h->fetchAll();if(!$rows)continue;
    $sa=(float)$rows[0]['saldo_anterior'];
    foreach($rows as $r2){$ex=round($sa+($r2['tipo']==='entrada'?(float)$r2['monto']:-(float)$r2['monto']),2);if(abs($ex-(float)$r2['saldo_nuevo'])>0.01)$e6++;$sa=$ex;}
}
echo "Historial errores: $e6 (esperado 0)\n";

$huerf=$pdo->query("SELECT COUNT(*) FROM historial_saldos h LEFT JOIN movimientos m ON h.movimiento_id=m.id WHERE m.id IS NULL")->fetchColumn();
echo "Historiales huerfanos: $huerf (esperado 0)\n";

echo "\n=== MÉTRICAS FINANCIERAS ===\n";
echo "Comisiones AMIRI total:     " . number_format((float)$pdo->query("SELECT COALESCE(SUM(comision_amiri),0) FROM deudas_clientes WHERE activo=1")->fetchColumn(), 2) . " MXN\n";
echo "  Pagadas:                  " . number_format((float)$pdo->query("SELECT COALESCE(SUM(comision_amiri),0) FROM deudas_clientes WHERE activo=1 AND estado='pagado'")->fetchColumn(), 2) . " MXN\n";
echo "  Pendientes/parcial:       " . number_format((float)$pdo->query("SELECT COALESCE(SUM(comision_amiri),0) FROM deudas_clientes WHERE activo=1 AND estado IN('pendiente','parcial')")->fetchColumn(), 2) . " MXN\n";
echo "Comisiones Promotor total:  " . number_format((float)$pdo->query("SELECT COALESCE(SUM(comision_promotor),0) FROM deudas_clientes WHERE activo=1")->fetchColumn(), 2) . " MXN\n";
echo "Ganancia FIFO:              " . number_format((float)$pdo->query("SELECT COALESCE(SUM(ganancia),0) FROM consumos_inventario")->fetchColumn(), 2) . " MXN\n";
echo "Gastos totales:             " . number_format((float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1")->fetchColumn(), 2) . " MXN\n";
echo "  Fijos:                    " . number_format((float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1 AND clasificacion='fijo'")->fetchColumn(), 2) . " MXN\n";
echo "  Variables:                " . number_format((float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM gastos WHERE activo=1 AND clasificacion='variable'")->fetchColumn(), 2) . " MXN\n";
echo "Deuda empresa pendiente:    " . number_format((float)$pdo->query("SELECT COALESCE(SUM(monto-monto_pagado),0) FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1")->fetchColumn(), 2) . " MXN\n";
echo "Deuda clientes pendiente:   " . number_format((float)$pdo->query("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1")->fetchColumn(), 2) . " MXN\n";
echo "Saldo Utilidades (#1):      " . number_format((float)$pdo->query("SELECT saldo_actual FROM cuentas WHERE id=1")->fetchColumn(), 2) . " MXN\n";
echo "Saldo Caja Efectivo (#2):   " . number_format((float)$pdo->query("SELECT saldo_actual FROM cuentas WHERE id=2")->fetchColumn(), 2) . " MXN\n";

echo "\n=== TOP 5 CLIENTES POR COMISIÓN AMIRI ===\n";
$top5=$pdo->query("SELECT cl.nombre,ROUND(SUM(d.comision_amiri),2) as t FROM deudas_clientes d JOIN clientes cl ON d.cliente_id=cl.id WHERE d.activo=1 AND d.comision_amiri>0 GROUP BY cl.id,cl.nombre ORDER BY t DESC LIMIT 5")->fetchAll();
foreach($top5 as $i=>$c) echo "  ".($i+1).". {$c['nombre']}: ".number_format($c['t'],2)." MXN\n";

echo "\n=== PROMOTORES CON COMISIONES ===\n";
$proms=$pdo->query("SELECT pr.nombre,ROUND(SUM(d.comision_promotor),2) as t FROM deudas_clientes d JOIN clientes cl ON d.cliente_id=cl.id JOIN promotores pr ON cl.promotor_id=pr.id WHERE d.activo=1 AND d.comision_promotor>0 GROUP BY pr.id,pr.nombre ORDER BY t DESC")->fetchAll();
foreach($proms as $c) echo "  {$c['nombre']}: ".number_format($c['t'],2)." MXN\n";

echo "\n=== DEUDAS CLIENTES ACTIVAS vs INACTIVAS ===\n";
$estados=$pdo->query("SELECT estado,COUNT(*) as c,ROUND(SUM(comision_amiri),2) as com FROM deudas_clientes WHERE activo=1 GROUP BY estado ORDER BY estado")->fetchAll();
foreach($estados as $e) echo "  {$e['estado']}: {$e['c']} deudas, comision AMIRI: ".number_format($e['com'],2)." MXN\n";

echo "\n=== ECUACION CONTABLE (valores originales) ===\n";
$act=$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2)")->fetchColumn();
$cap=$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta=3")->fetchColumn();
$pas=$pdo->query("SELECT COALESCE(SUM(saldo_actual),0) FROM cuentas WHERE activo=1 AND tipo_cuenta=4")->fetchColumn();
echo "Activos (tipo 1,2): ".number_format($act,2)." MXN\n";
echo "Capital (tipo 3):   ".number_format($cap,2)." MXN\n";
echo "Pasivos (tipo 4):   ".number_format($pas,2)." MXN\n";
$dif = $act - $cap - $pas;
echo "Diferencia:         ".number_format($dif,2)." MXN\n";
echo "Estado: " . (abs($dif)<0.01 ? "BALANCEADA" : "DESBALANCEADA por " . number_format($dif,2) . " MXN") . "\n";
