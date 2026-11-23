<?php
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
$fi = $_GET['inicio'] ?? date('Y-m-01');
$ff = $_GET['fin'] ?? date('Y-m-t');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diagnostico - Utilidad AMIRI</title>
<link rel="stylesheet" href="src/assets/css/styles.min.css">
<style>
body{background:#0f172a;color:#e2e8f0;padding:20px;font-family:'Segoe UI',sans-serif}
h1{color:#a78bfa;border-bottom:2px solid #c58fb8;padding-bottom:10px}
h2{color:#c58fb8;margin-top:30px;font-size:1.1rem}
table{width:100%;border-collapse:collapse;margin:10px 0 20px 0;font-size:0.85rem}
th{background:#1e293b;color:#a78bfa;padding:8px 12px;text-align:left;border:1px solid #334155}
td{padding:6px 12px;border:1px solid #334155}
tr:nth-child(even){background:#1e293b}
tr:hover{background:#334155}
.neg{color:#f87171;font-weight:bold}.pos{color:#4ade80;font-weight:bold}
.small{font-size:0.75rem;color:#64748b}
.filters{background:#1e293b;padding:12px 16px;border-radius:8px;margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.filters label{color:#94a3b8;font-size:0.85rem}
.filters input{background:#0f172a;border:1px solid #334155;color:#e2e8f0;padding:4px 8px;border-radius:4px}
.filters button{background:#c58fb8;color:#fff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer}
.alert{padding:12px 16px;border-radius:8px;margin:10px 0;font-size:0.85rem}
.alert-danger{background:rgba(248,113,113,0.15);border:1px solid rgba(248,113,113,0.3);color:#fca5a5}
.alert-info{background:rgba(96,165,250,0.15);border:1px solid rgba(96,165,250,0.3);color:#93c5fd}
.alert-warning{background:rgba(251,191,36,0.15);border:1px solid rgba(251,191,36,0.3);color:#fde68a}
.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:16px 0}
.si{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:12px;text-align:center}
.si .lb{font-size:0.7rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px}
.si .vl{font-size:1.3rem;font-weight:800;margin-top:4px}
</style>
</head>
<body>
<h1>Diagnostico: Utilidad AMIRI</h1>
<div class="filters">
<label>Desde:</label><input type="date" id="fI" value="<?= htmlspecialchars($fi) ?>">
<label>Hasta:</label><input type="date" id="fF" value="<?= htmlspecialchars($ff) ?>">
<button onclick="location.href='?inicio='+document.getElementById('fI').value+'&fin='+document.getElementById('fF').value">Filtrar</button>
<button onclick="location.href='?inicio=2020-01-01&fin=2099-12-31'" style="background:#334155">Ver Todo Historico</button>
</div>
<?php

// 1. DEUDAS_CLIENTES
echo "<h2>1. COMISIONES AMIRI (deudas_clientes.comision_amiri)</h2>";
$stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri),0) as total,
    COALESCE(SUM(CASE WHEN comision_amiri>0 THEN comision_amiri ELSE 0 END),0) as positivos,
    COALESCE(SUM(CASE WHEN comision_amiri<0 THEN comision_amiri ELSE 0 END),0) as negativos,
    COUNT(*) as total_reg,
    SUM(CASE WHEN comision_amiri<0 THEN 1 ELSE 0 END) as neg_reg
FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo=1");
$stmt->execute([$fi,$ff]); $dc=$stmt->fetch();
echo '<div class="sg">';
echo '<div class="si"><div class="lb">Total Com. AMIRI</div><div class="vl '.((float)$dc['total']>=0?'pos':'neg').">$".number_format((float)$dc['total'],2).'</div></div>';
echo '<div class="si"><div class="lb">Positivos ('.$dc['total_reg'].')</div><div class="vl pos">$'.number_format((float)$dc['positivos'],2).'</div></div>';
echo '<div class="si"><div class="lb">Negativos ('.$dc['neg_reg'].')</div><div class="vl neg">$'.number_format((float)$dc['negativos'],2).'</div></div>';
echo '</div>';

$stmt = $pdo->prepare("SELECT dc.id, cl.nombre as cliente, dc.monto_total, dc.comision_amiri, dc.comision_promotor, dc.monto_neto_cliente, dc.fecha, dc.descripcion, dc.estado, dc.movimiento_venta_id, dc.moneda
FROM deudas_clientes dc LEFT JOIN clientes cl ON dc.cliente_id=cl.id
WHERE dc.fecha BETWEEN ? AND ? AND dc.activo=1 AND dc.comision_amiri<0 ORDER BY dc.comision_amiri ASC");
$stmt->execute([$fi,$ff]); $negDC=$stmt->fetchAll();
if($negDC){
    echo '<div class="alert alert-danger">Se encontraron <strong>'.count($negDC).' registros</strong> con comision_amiri negativa:</div>';
    echo '<table><thead><tr><th>ID</th><th>Cliente</th><th>Monto Total</th><th>Com.AMIRI</th><th>Com.Prom</th><th>Neto</th><th>Fecha</th><th>Estado</th><th>Grupo</th><th>Descripcion</th></tr></thead><tbody>';
    foreach($negDC as $r){
        echo '<tr><td>'.$r['id'].'</td><td>'.htmlspecialchars($r['cliente']??'?').'</td><td>$'.number_format((float)$r['monto_total'],2).'</td><td class="neg">$'.number_format((float)$r['comision_amiri'],2).'</td><td>$'.number_format((float)$r['comision_promotor'],2).'</td><td>$'.number_format((float)$r['monto_neto_cliente'],2).'</td><td>'.$r['fecha'].'</td><td>'.$r['estado'].'</td><td>'.$r['movimiento_venta_id'].'</td><td class="small">'.htmlspecialchars($r['descripcion']??'').'</td></tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">No hay comision_amiri negativa en deudas_clientes. El problema NO esta aqui.</div>';
}

// 2. MOVIMIENTOS DIVISAS
echo "<h2>2. GANANCIA DIVISAS (movimientos tipo=comision, servicio 2/3)</h2>";
$stmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) as total,
    COALESCE(SUM(CASE WHEN monto>0 THEN monto ELSE 0 END),0) as positivos,
    COALESCE(SUM(CASE WHEN monto<0 THEN monto ELSE 0 END),0) as negativos,
    COUNT(*) as total_reg,
    SUM(CASE WHEN monto<0 THEN 1 ELSE 0 END) as neg_reg
FROM movimientos WHERE servicio_id IN (2,3) AND tipo='comision' AND cuenta_destino_id=1 AND fecha BETWEEN ? AND ? AND activo=1");
$stmt->execute([$fi,$ff]); $mv=$stmt->fetch();
echo '<div class="sg">';
echo '<div class="si"><div class="lb">Total Ganancia Divisas</div><div class="vl '.((float)$mv['total']>=0?'pos':'neg').">$".number_format((float)$mv['total'],2).'</div></div>';
echo '<div class="si"><div class="lb">Positivos</div><div class="vl pos">$'.number_format((float)$mv['positivos'],2).'</div></div>';
echo '<div class="si"><div class="lb">Negativos ('.$mv['neg_reg'].')</div><div class="vl neg">$'.number_format((float)$mv['negativos'],2).'</div></div>';
echo '</div>';

$stmt = $pdo->prepare("SELECT id, grupo_id, monto, fecha, descripcion, estado, es_reverso, servicio_id, cliente_id
FROM movimientos WHERE servicio_id IN (2,3) AND tipo='comision' AND cuenta_destino_id=1 AND fecha BETWEEN ? AND ? AND activo=1 AND monto<0 ORDER BY monto ASC");
$stmt->execute([$fi,$ff]); $negMV=$stmt->fetchAll();
if($negMV){
    echo '<div class="alert alert-danger"><strong>'.count($negMV).' registros</strong> con monto negativo (perdidas FIFO):</div>';
    echo '<table><thead><tr><th>ID</th><th>Grupo</th><th>Svc</th><th>Monto</th><th>Fecha</th><th>Estado</th><th>Reverso</th><th>Descripcion</th></tr></thead><tbody>';
    foreach($negMV as $r){
        $svc=((int)$r['servicio_id']===2)?'USD':'USDT';
        echo '<tr><td>'.$r['id'].'</td><td>'.$r['grupo_id'].'</td><td>'.$svc.'</td><td class="neg">$'.number_format((float)$r['monto'],2).'</td><td>'.$r['fecha'].'</td><td>'.$r['estado'].'</td><td>'.($r['es_reverso']?'SI':'No').'</td><td class="small">'.htmlspecialchars($r['descripcion']??'').'</td></tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">No hay registros negativos de ganancia_divisas.</div>';
}

// 3. BUG: ANULADOS
echo "<h2>3. BUG: Movimientos anulados que se siguen contando</h2>";
$stmt = $pdo->prepare("SELECT id, grupo_id, monto, fecha, estado, es_reverso, descripcion, servicio_id
FROM movimientos WHERE servicio_id IN (2,3) AND tipo='comision' AND cuenta_destino_id=1 AND fecha BETWEEN ? AND ? AND activo=1 AND estado!='activo' ORDER BY fecha DESC");
$stmt->execute([$fi,$ff]); $bug=$stmt->fetchAll();
if($bug){
    $tBug=0; foreach($bug as $r) $tBug+=(float)$r['monto'];
    echo '<div class="alert alert-warning">BUG CONFIRMADO: <strong>'.count($bug).' movimientos anulados/corregidos</strong> que la query aun contando. Impacto: <strong>$'.number_format($tBug,2).'</strong></div>';
    echo '<table><thead><tr><th>ID</th><th>Grupo</th><th>Svc</th><th>Monto</th><th>Fecha</th><th>Estado</th><th>Reverso</th><th>Desc</th></tr></thead><tbody>';
    foreach($bug as $r){
        $svc=((int)$r['servicio_id']===2)?'USD':'USDT';
        echo '<tr><td>'.$r['id'].'</td><td>'.$r['grupo_id'].'</td><td>'.$svc.'</td><td class="'.((float)$r['monto']>=0?'pos':'neg').">$".number_format((float)$r['monto'],2).'</td><td>'.$r['fecha'].'</td><td>'.$r['estado'].'</td><td>'.($r['es_reverso']?'SI':'No').'</td><td class="small">'.htmlspecialchars($r['descripcion']??'').'</td></tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">No hay movimientos anulados contandose. Bug no detectado.</div>';
}

// 4. RESUMEN POR MES
echo "<h2>4. UTILIDAD AMIRI POR MES</h2>";
$stmt = $pdo->prepare("SELECT DATE_FORMAT(dc.fecha,'%Y-%m') as mes, COALESCE(SUM(dc.comision_amiri),0) as ca, COUNT(dc.id) as ops
FROM deudas_clientes dc WHERE dc.fecha BETWEEN ? AND ? AND dc.activo=1 GROUP BY DATE_FORMAT(dc.fecha,'%Y-%m') ORDER BY mes");
$stmt->execute([$fi,$ff]); $mdc=$stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DATE_FORMAT(m.fecha,'%Y-%m') as mes, COALESCE(SUM(m.monto),0) as gd, COUNT(m.id) as ops
FROM movimientos m WHERE m.servicio_id IN (2,3) AND m.tipo='comision' AND m.cuenta_destino_id=1 AND m.fecha BETWEEN ? AND ? AND m.activo=1 GROUP BY DATE_FORMAT(m.fecha,'%Y-%m') ORDER BY mes");
$stmt->execute([$fi,$ff]); $mmv=$stmt->fetchAll();

$am=[];
foreach($mdc as $r) $am[$r['mes']]=['mes'=>$r['mes'],'ca'=>(float)$r['ca'],'ops_dc'=>(int)$r['ops'],'gd'=>0,'ops_mv'=>0];
foreach($mmv as $r){
    $m=$r['mes'];
    if(!isset($am[$m])) $am[$m]=['mes'=>$m,'ca'=>0,'ops_dc'=>0,'gd'=>0,'ops_mv'=>0];
    $am[$m]['gd']=(float)$r['gd']; $am[$m]['ops_mv']=(int)$r['ops'];
}
ksort($am);

echo '<table><thead><tr><th>Mes</th><th>Com. AMIRI</th><th>Gan. Divisas</th><th>Utilidad Bruta</th><th>Ops DC</th><th>Ops MV</th></tr></thead><tbody>';
foreach($am as $m){
    $ub=$m['ca']+$m['gd'];
    $cls=$ub>=0?'pos':'neg';
    echo '<tr>';
    echo '<td><strong>'.$m['mes'].'</strong></td>';
    echo '<td class="'.($m['ca']>=0?'pos':'neg').'">$'.number_format($m['ca'],2).'</td>';
    echo '<td class="'.($m['gd']>=0?'pos':'neg').'">$'.number_format($m['gd'],2).'</td>';
    echo '<td class="'.$cls.'">$'.number_format($ub,2).'</td>';
    echo '<td>'.$m['ops_dc'].'</td>';
    echo '<td>'.$m['ops_mv'].'</td>';
    echo '</tr>';
}
echo '</tbody></table>';

// 5. CONTEXTO CIERRES
echo "<h2>5. MESES CERRADOS (cierres_mensuales)</h2>";
$stmt = $pdo->query("SELECT mes, comisiones_amiri, ganancia_divisas, gastos_total, utilidad_neta, capital_acumulado FROM cierres_mensuales ORDER BY mes");
$cierres=$stmt->fetchAll();
if($cierres){
    echo '<table><thead><tr><th>Mes</th><th>Com. AMIRI</th><th>Gan. Divisas</th><th>Gastos</th><th>Util. Neta</th><th>Capital Acum.</th></tr></thead><tbody>';
    foreach($cierres as $c){
        $cls=((float)$c['utilidad_neta']>=0?'pos':'neg');
        echo '<tr><td><strong>'.$c['mes'].'</strong></td><td>$'.number_format((float)$c['comisiones_amiri'],2).'</td><td>$'.number_format((float)$c['ganancia_divisas'],2).'</td><td>$'.number_format((float)$c['gastos_total'],2).'</td><td class="'.$cls.'">$'.number_format((float)$c['utilidad_neta'],2).'</td><td>$'.number_format((float)$c['capital_acumulado'],2).'</td></tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">No hay cierres mensuales registrados.</div>';
}

// 6. COMPARACION: lo que ve el reporte vs lo que dicen los cierres
echo "<h2>6. COMPARACION: Reporte (query live) vs Cierres</h2>";
echo '<div class="alert alert-info">El reporte de utilidades calcula en vivo desde deudas_clientes y movimientos, NO desde cierres_mensuales. ';
echo 'Si los meses estan cerrados pero los datos raw cambiaron despues del cierre, el reporte mostrara valores diferentes a los cierres.</div>';

echo "</body></html>";
?>
