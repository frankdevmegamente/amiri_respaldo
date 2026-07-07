<?php
require_once __DIR__ . '/src/config/db.php';
$db = getPDO();

echo "=== SALDOS POR TIPO DE CUENTA ===\n";
$r = $db->query('SELECT c.tipo_cuenta, tc.descripcion, COUNT(*) as cnt, SUM(c.saldo_actual) as total
    FROM cuentas c LEFT JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
    WHERE c.activo = 1 GROUP BY c.tipo_cuenta ORDER BY c.tipo_cuenta');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo '  Tipo ' . $row['tipo_cuenta'] . ' ' . $row['descripcion'] . ': ' . $row['cnt'] . ' cuentas, $' . number_format($row['total'], 2) . "\n";
}

echo "\n=== TODAS LAS CUENTAS CON SALDO !== 0 ===\n";
$r = $db->query('SELECT c.id, c.nombre, c.tipo_cuenta, tc.descripcion as tipo, c.saldo_actual, c.tipo_moneda
    FROM cuentas c LEFT JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
    WHERE c.activo = 1 AND c.saldo_actual != 0
    ORDER BY c.tipo_cuenta, c.saldo_actual DESC');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo '  #' . str_pad($row['id'], 4) . ' [' . $row['tipo_cuenta'] . '] ' . str_pad($row['tipo'], 18) . ' ' . str_pad($row['nombre'], 35) . ' $' . number_format($row['saldo_actual'], 2) . ' ' . $row['tipo_moneda'] . "\n";
}

echo "\n=== ECUACION CONTABLE ===\n";
$activos = (float)$db->query('SELECT SUM(saldo_actual) FROM cuentas WHERE activo = 1 AND saldo_actual > 0')->fetchColumn();
$pasivos = (float)$db->query('SELECT SUM(ABS(saldo_actual)) FROM cuentas WHERE activo = 1 AND saldo_actual < 0')->fetchColumn();
$capital = (float)$db->query('SELECT SUM(saldo_actual) FROM cuentas WHERE activo = 1 AND tipo_cuenta IN (3, 7)')->fetchColumn();
$oblig = (float)$db->query("SELECT COALESCE(SUM(monto_total - COALESCE(monto_pagado,0)),0) FROM deudas_empresa WHERE activo = 1 AND estado IN ('pendiente','parcial')")->fetchColumn();
$por_cobrar = (float)$db->query("SELECT COALESCE(SUM(monto_total - COALESCE(monto_pagado,0)),0) FROM deudas_clientes WHERE activo = 1 AND estado IN ('pendiente','parcial')")->fetchColumn();

echo '  A) Activos (saldos > 0):       $' . number_format($activos, 2) . "\n";
echo '  B) Pasivos (saldos < 0):       $' . number_format($pasivos, 2) . "\n";
echo '  C) Capital (tipos 3+7):        $' . number_format($capital, 2) . "\n";
echo '  D) Obligaciones (deudas_emp):  $' . number_format($oblig, 2) . "\n";
echo '  E) Por cobrar (deudas_cli):    $' . number_format($por_cobrar, 2) . "\n\n";

$diff_act_cap_obl = $activos - $capital - $oblig;
$diff_full = $activos + $por_cobrar - $capital - $oblig;

echo '  Diferencia (A - C - D):        $' . number_format($diff_act_cap_obl, 2) . "\n";
echo '  Diferencia (A + E - C - D):    $' . number_format($diff_full, 2) . "\n";
echo '  A + E = Activos + Por cobrar:  $' . number_format($activos + $por_cobrar, 2) . "\n";
echo '  C + D = Capital + Oblig:       $' . number_format($capital + $oblig, 2) . "\n";

echo "\n=== PRESTAMOS OTORGADOS PENDIENTES ===\n";
$r = $db->query("SELECT id, persona_nombre, monto_total, COALESCE(monto_pagado,0) as monto_pagado, (monto_total - COALESCE(monto_pagado,0)) as saldo, estado FROM prestamos_otorgados WHERE activo = 1 AND estado != 'pagado' ORDER BY persona_nombre");
$totalP = 0;
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $saldo = (float)$row['saldo'];
    echo '  #' . $row['id'] . ' ' . $row['persona_nombre'] . ' | $' . number_format($row['monto_total'], 2) . ' - Pagado $' . number_format($row['monto_pagado'], 2) . ' = Saldo $' . number_format($saldo, 2) . ' (' . $row['estado'] . ")\n";
    $totalP += $saldo;
}
echo '  TOTAL PRESTAMOS OTORGADOS PENDIENTES: $' . number_format($totalP, 2) . "\n";

echo "\n=== DEUDAS CLIENTES PENDIENTES ===\n";
$r = $db->query("SELECT d.id, COALESCE(cl.nombre,'SIN CLIENTE') as cliente, d.monto_total, COALESCE(d.monto_pagado,0) as monto_pagado, (d.monto_total - COALESCE(d.monto_pagado,0)) as saldo, d.estado
    FROM deudas_clientes d LEFT JOIN clientes cl ON d.cliente_id = cl.id
    WHERE d.activo = 1 AND d.estado IN ('pendiente','parcial')
    ORDER BY cl.nombre");
$totalC = 0;
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $saldo = (float)$row['saldo'];
    echo '  #' . $row['id'] . ' ' . $row['cliente'] . ' | $' . number_format($row['monto_total'], 2) . ' - Pagado $' . number_format($row['monto_pagado'], 2) . ' = Saldo $' . number_format($saldo, 2) . ' (' . $row['estado'] . ")\n";
    $totalC += $saldo;
}
echo '  TOTAL DEUDAS CLIENTES: $' . number_format($totalC, 2) . "\n\n";

// === ANALISIS ===
echo "=== ANALISIS DE LA DIFERENCIA ===\n";
echo "  La alerta muestra: 'Diferencia neta: -$762,307.01'\n";
echo "  Eso significa (Saldos totales) - (Capital) - (Obligaciones) = -$" . number_format(abs($diff_act_cap_obl), 2) . "\n\n";

echo "  Posibles causas del faltante:\n";
echo "  1. Prestamos otorgados por \$$totalP no estan reflejados como activos\n";
echo "  2. Deudas de clientes por cobrar \$$totalC no estan en saldos de cuentas\n";
echo "  3. Puede haber cuentas de tipo incorrecto (clasificacion erronea)\n";
echo "  4. Pueden faltar movimientos que actualicen saldos\n\n";

echo "  Si sumamos prestamos + deudas clientes al activo:\n";
echo "  Activos ajustados: \$" . number_format($activos + $totalP + $totalC, 2) . "\n";
echo "  Capital+Oblig:     \$" . number_format($capital + $oblig, 2) . "\n";
echo "  Diferencia final:  \$" . number_format(($activos + $totalP + $totalC) - $capital - $oblig, 2) . "\n";
