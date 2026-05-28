<?php
/**
 * REPORTE FINANCIERO COMPLETO - SISTEMA AMIRI
 * 
 * Genera reporte mensual de ingresos, egresos, comisiones, ganancias FIFO, gastos
 * 
 * USO: php scripts/reporte_financiero.php
 */

require_once __DIR__ . '/../src/config/db.php';

$pdo = getPDO();
$db = $pdo->query('SELECT DATABASE()')->fetchColumn();
$fecha_gen = date('Y-m-d H:i:s');

// ============================================================
// RECOLECTAR DATOS
// ============================================================

// 1. Ganancias FIFO por mes (desde consumos_inventario)
$fifo = $pdo->query("
    SELECT DATE_FORMAT(ci.created_at, '%Y-%m') as mes,
           ROUND(SUM(ci.ganancia), 2) as ganancia_fifo,
           ROUND(SUM(CASE WHEN ci.ganancia > 0 THEN ci.ganancia ELSE 0 END), 2) as ganancias_positivas,
           ROUND(SUM(CASE WHEN ci.ganancia < 0 THEN ci.ganancia ELSE 0 END), 2) as perdidas,
           COUNT(*) as operaciones
    FROM consumos_inventario ci
    JOIN movimientos m ON ci.movimiento_id = m.id
    WHERE m.estado = 'activo' AND m.activo = 1
    GROUP BY mes ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// 2. Flujo de caja mensual (movimientos activos)
$flujo = $pdo->query("
    SELECT DATE_FORMAT(m.fecha, '%Y-%m') as mes,
           m.moneda,
           ROUND(SUM(CASE WHEN m.tipo = 'entrada' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as entradas,
           ROUND(SUM(CASE WHEN m.tipo = 'salida' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as salidas,
           ROUND(SUM(CASE WHEN m.tipo = 'traspaso' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as traspasos,
           ROUND(SUM(CASE WHEN m.tipo = 'comision' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as comisiones_mov
    FROM movimientos m
    GROUP BY mes, m.moneda ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// 3. Comisiones Amiri devengadas por mes (de deudas_clientes)
$com_amiri = $pdo->query("
    SELECT DATE_FORMAT(dc.fecha, '%Y-%m') as mes,
           ROUND(SUM(dc.comision_amiri), 2) as comision_amiri,
           ROUND(SUM(dc.comision_promotor), 2) as comision_promotor_dev,
           COUNT(*) as operaciones_com
    FROM deudas_clientes dc
    WHERE dc.activo = 1
    GROUP BY mes ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// 4. Gastos por mes
$gastos = $pdo->query("
    SELECT DATE_FORMAT(g.fecha, '%Y-%m') as mes,
           COALESCE(cg.nombre, 'TOTAL') as categoria,
           ROUND(SUM(g.monto), 2) as total,
           COUNT(*) as cantidad
    FROM gastos g
    JOIN categorias_gasto cg ON g.categoria_id = cg.id
    WHERE g.activo = 1
    GROUP BY mes, cg.nombre
    UNION ALL
    SELECT DATE_FORMAT(g.fecha, '%Y-%m') as mes,
           'TOTAL' as categoria,
           ROUND(SUM(g.monto), 2) as total,
           COUNT(*) as cantidad
    FROM gastos g
    WHERE g.activo = 1
    GROUP BY mes
    ORDER BY mes, FIELD(categoria, 'TOTAL', ''), total DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 5. Gastos totales por mes
$gastos_totales = $pdo->query("
    SELECT DATE_FORMAT(g.fecha, '%Y-%m') as mes,
           ROUND(SUM(g.monto), 2) as total_gastos,
           COUNT(*) as cantidad
    FROM gastos g
    WHERE g.activo = 1
    GROUP BY mes ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// 6. Pagos a promotores por mes
$pagos_prom = $pdo->query("
    SELECT DATE_FORMAT(pp.fecha_pago, '%Y-%m') as mes,
           p.nombre as promotor,
           ROUND(SUM(pp.monto_pagado), 2) as pagado
    FROM pagos_promotores pp
    JOIN promotores p ON pp.promotor_id = p.id
    WHERE pp.activo = 1
    GROUP BY mes, p.nombre ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// 7. Saldos actuales por tipo de cuenta
$saldos = $pdo->query("
    SELECT tc.descripcion as tipo,
           c.tipo_moneda,
           ROUND(SUM(c.saldo_actual), 2) as total,
           COUNT(*) as cuentas
    FROM cuentas c
    JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
    WHERE c.activo = 1
    GROUP BY tc.descripcion, c.tipo_moneda ORDER BY tc.id
")->fetchAll(PDO::FETCH_ASSOC);

// 8. Deudas activas (empresa debe)
$deudas_emp = $pdo->query("
    SELECT p.nombre as prestamista,
           ROUND(SUM(de.monto - de.monto_pagado), 2) as saldo_pendiente,
           ROUND(SUM(de.monto), 2) as total_prestado,
           ROUND(SUM(de.monto_pagado), 2) as total_pagado
    FROM deudas_empresa de
    JOIN prestamistas p ON de.prestamista_id = p.id
    WHERE de.activo = 1 AND de.estado IN ('pendiente', 'parcial')
    GROUP BY p.nombre ORDER BY saldo_pendiente DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 9. Deudas clientes activas (le deben a empresa)
$deudas_cli = $pdo->query("
    SELECT c.nombre as cliente,
           dc.moneda,
           ROUND(SUM(dc.monto_total - dc.monto_pagado), 2) as saldo_pendiente,
           ROUND(SUM(dc.monto_total), 2) as total_venta,
           ROUND(SUM(dc.monto_pagado), 2) as total_pagado
    FROM deudas_clientes dc
    JOIN clientes c ON dc.cliente_id = c.id
    WHERE dc.activo = 1 AND dc.estado IN ('pendiente', 'parcial')
    GROUP BY c.nombre, dc.moneda ORDER BY saldo_pendiente DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 10. Préstamos otorgados activos
$prest_otorg = $pdo->query("
    SELECT persona_nombre,
           ROUND(SUM(monto_total - monto_pagado), 2) as saldo_pendiente,
           ROUND(SUM(monto_total), 2) as total_prestado
    FROM prestamos_otorgados
    WHERE activo = 1 AND estado IN ('pendiente', 'parcial')
    GROUP BY persona_nombre ORDER BY saldo_pendiente DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 11. Comisiones por promotor (lo que se les debe)
$total_promotor_dev = $pdo->query("SELECT ROUND(SUM(comision_promotor),2) as total FROM deudas_clientes WHERE activo = 1")->fetchColumn();
$com_prom_devengadas = $pdo->query("
    SELECT p.nombre as promotor,
           ROUND(COALESCE((SELECT SUM(pp.monto_pagado) FROM pagos_promotores pp WHERE pp.promotor_id = p.id AND pp.activo = 1), 0), 2) as pagado
    FROM promotores p
    WHERE p.activo = 1
    GROUP BY p.nombre ORDER BY pagado DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 12. Resumen por servicio
$servicios = $pdo->query("
    SELECT s.nombre as servicio,
           m.moneda,
           ROUND(SUM(CASE WHEN m.tipo = 'entrada' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as entradas,
           ROUND(SUM(CASE WHEN m.tipo = 'salida' AND m.estado = 'activo' AND m.activo = 1 THEN m.monto ELSE 0 END), 2) as salidas,
           COUNT(*) as movimientos
    FROM movimientos m
    JOIN servicios s ON m.servicio_id = s.id
    WHERE m.estado = 'activo' AND m.activo = 1
    GROUP BY s.nombre, m.moneda ORDER BY entradas DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 13. Ganancias FIFO por venta (registros individuales con pérdidas)
$fifo_detalle = $pdo->query("
    SELECT ci.id, ci.cantidad_consumida, ci.paridad_compra, ci.paridad_venta, ci.ganancia,
           DATE_FORMAT(ci.created_at, '%Y-%m-%d') as fecha,
           m.descripcion
    FROM consumos_inventario ci
    JOIN movimientos m ON ci.movimiento_id = m.id
    WHERE m.estado = 'activo' AND m.activo = 1 AND ci.ganancia < 0
    ORDER BY ci.ganancia ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// ARMAR MESES DISPONIBLES
// ============================================================
$meses = [];
foreach ($flujo as $f) { $meses[$f['mes']] = true; }
foreach ($gastos_totales as $g) { $meses[$g['mes']] = true; }
foreach ($fifo as $f) { $meses[$f['mes']] = true; }
$meses = array_keys($meses);
sort($meses);

// ============================================================
// GENERAR ARCHIVO CSV
// ============================================================
$csv_dir = __DIR__ . '/../reportes';
if (!is_dir($csv_dir)) mkdir($csv_dir, 0777, true);
$csv_file = $csv_dir . '/reporte_financiero_' . date('Y-m-d_H-i-s') . '.csv';
$csv = fopen($csv_file, 'w');
fwrite($csv, "\xEF\xBB\xBF"); // BOM UTF-8

fwrite($csv, "REPORTE FINANCIERO AMIRI\n");
fwrite($csv, "Generado: $fecha_gen | BD: $db\n\n");

// ---- Sección CSV: Resumen Mensual ----
fwrite($csv, "=== RESUMEN MENSUAL (MXN) ===\n");
fwrite($csv, "Mes;Ingresos MXN;Egresos MXN;Traspasos MXN;Ganancia FIFO;Comision Amiri;Comision Promotor;Gastos;Saldo Neto\n");
foreach ($meses as $mes) {
    $ing = 0; $egr = 0; $tras = 0; $fif = 0; $cam = 0; $cpr = 0; $gas = 0;
    foreach ($flujo as $f) { if ($f['mes'] == $mes && $f['moneda'] == 'MXN') { $ing += $f['entradas']; $egr += $f['salidas']; $tras += $f['traspasos']; } }
    foreach ($fifo as $f) { if ($f['mes'] == $mes) $fif += $f['ganancia_fifo']; }
    foreach ($com_amiri as $c) { if ($c['mes'] == $mes) { $cam += $c['comision_amiri']; $cpr += $c['comision_promotor_dev']; } }
    foreach ($gastos_totales as $g) { if ($g['mes'] == $mes) $gas += $g['total_gastos']; }
    $neto = $ing - $egr + $fif + $cam - $gas;
    fwrite($csv, "$mes;$ing;$egr;$tras;$fif;$cam;$cpr;$gas;$neto\n");
}

fwrite($csv, "\n=== FLUJO POR MES Y MONEDA ===\n");
fwrite($csv, "Mes;Moneda;Entradas;Salidas;Traspasos\n");
foreach ($flujo as $f) fwrite($csv, "{$f['mes']};{$f['moneda']};{$f['entradas']};{$f['salidas']};{$f['traspasos']}\n");

fwrite($csv, "\n=== GANANCIAS FIFO POR MES ===\n");
fwrite($csv, "Mes;Ganancia Total;Ganancias Positivas;Perdidas;Operaciones\n");
foreach ($fifo as $f) fwrite($csv, "{$f['mes']};{$f['ganancia_fifo']};{$f['ganancias_positivas']};{$f['perdidas']};{$f['operaciones']}\n");

fwrite($csv, "\n=== VENTAS CON PERDIDA FIFO ===\n");
fwrite($csv, "ID;Cantidad;Paridad Compra;Paridad Venta;Perdida;Fecha;Descripcion\n");
foreach ($fifo_detalle as $f) fwrite($csv, "{$f['id']};{$f['cantidad_consumida']};{$f['paridad_compra']};{$f['paridad_venta']};{$f['ganancia']};{$f['fecha']};\"{$f['descripcion']}\"\n");

fwrite($csv, "\n=== COMISIONES AMIRI Y PROMOTOR DEVENGADAS ===\n");
fwrite($csv, "Mes;Comision Amiri;Comision Promotor;Operaciones\n");
foreach ($com_amiri as $c) fwrite($csv, "{$c['mes']};{$c['comision_amiri']};{$c['comision_promotor_dev']};{$c['operaciones_com']}\n");

fwrite($csv, "\n=== GASTOS POR MES ===\n");
fwrite($csv, "Mes;Categoria;Total;Cantidad\n");
foreach ($gastos as $g) {
    fwrite($csv, "{$g['mes']};{$g['categoria']};{$g['total']};{$g['cantidad']}\n");
}

fwrite($csv, "\n=== PAGOS A PROMOTORES ===\n");
fwrite($csv, "Mes;Promotor;Pagado\n");
foreach ($pagos_prom as $p) fwrite($csv, "{$p['mes']};{$p['promotor']};{$p['pagado']}\n");

fwrite($csv, "\n=== SALDOS ACTUALES POR TIPO ===\n");
fwrite($csv, "Tipo;Moneda;Total;Cuentas\n");
foreach ($saldos as $s) fwrite($csv, "{$s['tipo']};{$s['tipo_moneda']};{$s['total']};{$s['cuentas']}\n");

fwrite($csv, "\n=== DEUDAS ACTIVAS (EMPRESA DEBE) ===\n");
fwrite($csv, "Prestamista;Saldo Pendiente;Total Prestado;Total Pagado\n");
foreach ($deudas_emp as $d) fwrite($csv, "{$d['prestamista']};{$d['saldo_pendiente']};{$d['total_prestado']};{$d['total_pagado']}\n");

fwrite($csv, "\n=== DEUDAS ACTIVAS (CLIENTES DEBEN) ===\n");
fwrite($csv, "Cliente;Moneda;Saldo Pendiente;Total Venta;Total Pagado\n");
foreach ($deudas_cli as $d) fwrite($csv, "{$d['cliente']};{$d['moneda']};{$d['saldo_pendiente']};{$d['total_venta']};{$d['total_pagado']}\n");

fwrite($csv, "\n=== PRESTAMOS OTORGADOS ===\n");
fwrite($csv, "Persona;Saldo Pendiente;Total Prestado\n");
foreach ($prest_otorg as $p) fwrite($csv, "{$p['persona_nombre']};{$p['saldo_pendiente']};{$p['total_prestado']}\n");

fwrite($csv, "\n=== COMISIONES POR PROMOTOR ===\n");
fwrite($csv, "Promotor;Pagado\n");
foreach ($com_prom_devengadas as $c) {
    fwrite($csv, "{$c['promotor']};{$c['pagado']}\n");
}
fwrite($csv, "DEVENGADO TOTAL (deudas_clientes);$total_promotor_dev\n");

fwrite($csv, "\n=== MOVIMIENTOS POR SERVICIO ===\n");
fwrite($csv, "Servicio;Moneda;Entradas;Salidas;Movimientos\n");
foreach ($servicios as $s) fwrite($csv, "{$s['servicio']};{$s['moneda']};{$s['entradas']};{$s['salidas']};{$s['movimientos']}\n");

fclose($csv);
$csv_size = filesize($csv_file);

// ============================================================
// MOSTRAR EN CONSOLA
// ============================================================
echo str_repeat("=", 90) . PHP_EOL;
echo "  REPORTE FINANCIERO AMIRI" . PHP_EOL;
echo "  Generado: $fecha_gen  |  BD: $db" . PHP_EOL;
echo str_repeat("=", 90) . PHP_EOL . PHP_EOL;

// --- Resumen Mensual ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  1. RESUMEN MENSUAL (MXN)" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo str_pad("Mes", 8) . " | " . str_pad("Ingresos", 14) . " | " . str_pad("Egresos", 14) . " | " . str_pad("FIFO", 12) . " | " . str_pad("Com.Amiri", 10) . " | " . str_pad("Gastos", 10) . " | " . str_pad("Neto", 14) . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
foreach ($meses as $mes) {
    $ing = 0; $egr = 0; $fif = 0; $cam = 0; $gas = 0;
    foreach ($flujo as $f) { if ($f['mes'] == $mes && $f['moneda'] == 'MXN') { $ing += $f['entradas']; $egr += $f['salidas']; } }
    foreach ($fifo as $f) { if ($f['mes'] == $mes) $fif += $f['ganancia_fifo']; }
    foreach ($com_amiri as $c) { if ($c['mes'] == $mes) $cam += $c['comision_amiri']; }
    foreach ($gastos_totales as $g) { if ($g['mes'] == $mes) $gas += $g['total_gastos']; }
    $neto = $ing - $egr + $fif + $cam - $gas;
    echo str_pad($mes, 8) . " | $" . str_pad(number_format($ing, 2), 12) . " | $" . str_pad(number_format($egr, 2), 12) . " | $" . str_pad(number_format($fif, 2), 10) . " | $" . str_pad(number_format($cam, 2), 8) . " | $" . str_pad(number_format($gas, 2), 8) . " | $" . str_pad(number_format($neto, 2), 12) . PHP_EOL;
}
echo PHP_EOL;

// --- Ganancias FIFO ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  2. GANANCIAS FIFO (USDT)" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo str_pad("Mes", 8) . " | " . str_pad("Ganancia Total", 14) . " | " . str_pad("Ganancias (+)", 14) . " | " . str_pad("Perdidas (-)", 14) . " | " . str_pad("Operaciones", 12) . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
$tot_fifo = ['ganancia_fifo' => 0, 'ganancias_positivas' => 0, 'perdidas' => 0, 'operaciones' => 0];
foreach ($fifo as $f) {
    echo str_pad($f['mes'], 8) . " | $" . str_pad(number_format($f['ganancia_fifo'], 2), 12) . " | $" . str_pad(number_format($f['ganancias_positivas'], 2), 12) . " | $" . str_pad(number_format($f['perdidas'], 2), 12) . " | " . str_pad($f['operaciones'], 10) . PHP_EOL;
    foreach ($tot_fifo as $k => &$v) $v += $f[$k];
}
echo str_pad("TOTAL", 8) . " | $" . str_pad(number_format($tot_fifo['ganancia_fifo'], 2), 12) . " | $" . str_pad(number_format($tot_fifo['ganancias_positivas'], 2), 12) . " | $" . str_pad(number_format($tot_fifo['perdidas'], 2), 12) . " | " . str_pad($tot_fifo['operaciones'], 10) . PHP_EOL;
echo PHP_EOL;

// --- Pérdidas FIFO ---
if (count($fifo_detalle) > 0) {
    echo str_repeat("-", 90) . PHP_EOL;
    echo "  3. VENTAS CON PERDIDA FIFO" . PHP_EOL;
    echo str_repeat("-", 90) . PHP_EOL;
    $total_perdidas = 0;
    foreach ($fifo_detalle as $f) {
        echo "  #{$f['id']}: {$f['cantidad_consumida']} USDT | Compra @{$f['paridad_compra']} | Venta @{$f['paridad_venta']} | Perdida: \${$f['ganancia']} | {$f['fecha']}" . PHP_EOL;
        $total_perdidas += $f['ganancia'];
    }
    echo "  TOTAL PERDIDAS: \$" . number_format($total_perdidas, 2) . PHP_EOL . PHP_EOL;
}

// --- Comisiones ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  4. COMISIONES AMIRI Y PROMOTOR (devengadas)" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo str_pad("Mes", 8) . " | " . str_pad("Comision Amiri", 14) . " | " . str_pad("Comision Promotor", 18) . " | " . str_pad("Operaciones", 12) . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
$tot_com = ['comision_amiri' => 0, 'comision_promotor_dev' => 0, 'operaciones_com' => 0];
foreach ($com_amiri as $c) {
    echo str_pad($c['mes'], 8) . " | $" . str_pad(number_format($c['comision_amiri'], 2), 12) . " | $" . str_pad(number_format($c['comision_promotor_dev'], 2), 14) . " | " . str_pad($c['operaciones_com'], 10) . PHP_EOL;
    foreach ($tot_com as $k => &$v) $v += $c[$k];
}
echo str_pad("TOTAL", 8) . " | $" . str_pad(number_format($tot_com['comision_amiri'], 2), 12) . " | $" . str_pad(number_format($tot_com['comision_promotor_dev'], 2), 14) . " | " . str_pad($tot_com['operaciones_com'], 10) . PHP_EOL;
echo PHP_EOL;

// --- Gastos ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  5. GASTOS POR MES" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo str_pad("Mes", 8) . " | " . str_pad("Total Gastos", 14) . " | " . str_pad("Cantidad", 10) . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
$tot_gas = ['total_gastos' => 0, 'cantidad' => 0];
foreach ($gastos_totales as $g) {
    echo str_pad($g['mes'], 8) . " | $" . str_pad(number_format($g['total_gastos'], 2), 12) . " | " . str_pad($g['cantidad'], 8) . PHP_EOL;
    foreach ($tot_gas as $k => &$v) $v += $g[$k];
}
echo str_pad("TOTAL", 8) . " | $" . str_pad(number_format($tot_gas['total_gastos'], 2), 12) . " | " . str_pad($tot_gas['cantidad'], 8) . PHP_EOL;
echo PHP_EOL;

// --- Pagos Promotores ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  6. PAGOS A PROMOTORES" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
$prom_agrup = [];
foreach ($pagos_prom as $p) {
    if (!isset($prom_agrup[$p['promotor']])) $prom_agrup[$p['promotor']] = 0;
    $prom_agrup[$p['promotor']] += $p['pagado'];
}
foreach ($prom_agrup as $nom => $tot) {
    echo "  $nom: $" . number_format($tot, 2) . PHP_EOL;
}
echo PHP_EOL;

// --- Saldos ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  7. SALDOS ACTUALES POR TIPO" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
foreach ($saldos as $s) {
    echo "  {$s['tipo']} ({$s['tipo_moneda']}): $" . number_format($s['total'], 2) . " ({$s['cuentas']} cuentas)" . PHP_EOL;
}
echo PHP_EOL;

// --- Deudas ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  8. DEUDAS ACTIVAS" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo "  LO QUE LA EMPRESA DEBE:" . PHP_EOL;
$total_debe = 0;
foreach ($deudas_emp as $d) {
    echo "    {$d['prestamista']}: \$" . number_format($d['saldo_pendiente'], 2) . " pendiente (de \$" . number_format($d['total_prestado'], 2) . ")" . PHP_EOL;
    $total_debe += $d['saldo_pendiente'];
}
echo "    TOTAL: \$" . number_format($total_debe, 2) . PHP_EOL . PHP_EOL;

echo "  LO QUE LE DEBEN A LA EMPRESA:" . PHP_EOL;
$total_deben = 0;
foreach ($deudas_cli as $d) {
    echo "    {$d['cliente']}: \$" . number_format($d['saldo_pendiente'], 2) . " {$d['moneda']}" . PHP_EOL;
    $total_deben += $d['saldo_pendiente'];
}
foreach ($prest_otorg as $p) {
    echo "    {$p['persona_nombre']} (prestamo): \$" . number_format($p['saldo_pendiente'], 2) . PHP_EOL;
    $total_deben += $p['saldo_pendiente'];
}
echo "    TOTAL: \$" . number_format($total_deben, 2) . PHP_EOL . PHP_EOL;

// --- Comisiones por Promotor ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  9. COMISIONES POR PROMOTOR" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
echo str_pad("Promotor", 25) . " | " . str_pad("Pagado", 14) . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
foreach ($com_prom_devengadas as $c) {
    echo str_pad($c['promotor'], 25) . " | $" . str_pad(number_format($c['pagado'], 2), 12) . PHP_EOL;
}
echo "  DEVENGADO TOTAL (deudas_clientes): \$" . number_format($total_promotor_dev, 2) . PHP_EOL;
echo PHP_EOL;

// --- Por servicio ---
echo str_repeat("-", 90) . PHP_EOL;
echo "  10. MOVIMIENTOS POR SERVICIO" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;
foreach ($servicios as $s) {
    echo "  {$s['servicio']} ({$s['moneda']}): +\$" . number_format($s['entradas'], 2) . " / -\$" . number_format($s['salidas'], 2) . " ({$s['movimientos']} mov)" . PHP_EOL;
}
echo PHP_EOL;

// ============================================================
// RESUMEN EJECUTIVO
// ============================================================
echo str_repeat("=", 90) . PHP_EOL;
echo "  RESUMEN EJECUTIVO" . PHP_EOL;
echo str_repeat("=", 90) . PHP_EOL;

$total_ingresos = 0; $total_egresos = 0;
foreach ($flujo as $f) { if ($f['moneda'] == 'MXN') { $total_ingresos += $f['entradas']; $total_egresos += $f['salidas']; } }
echo "  Total Ingresos (MXN): \$" . number_format($total_ingresos, 2) . PHP_EOL;
echo "  Total Egresos  (MXN): \$" . number_format($total_egresos, 2) . PHP_EOL;
echo "  Flujo Neto     (MXN): \$" . number_format($total_ingresos - $total_egresos, 2) . PHP_EOL;
echo "  Ganancia FIFO  (USDT): \$" . number_format($tot_fifo['ganancia_fifo'], 2) . PHP_EOL;
echo "  Comisiones Amiri     : \$" . number_format($tot_com['comision_amiri'], 2) . PHP_EOL;
echo "  Total Gastos         : \$" . number_format($tot_gas['total_gastos'], 2) . PHP_EOL;
echo "  Deuda Empresa        : \$" . number_format($total_debe, 2) . PHP_EOL;
echo "  Clientes Deben       : \$" . number_format($total_deben, 2) . PHP_EOL;
echo PHP_EOL;
echo "  CSV generado: " . basename($csv_file) . " (" . number_format($csv_size / 1024, 1) . " KB)" . PHP_EOL;
echo str_repeat("=", 90) . PHP_EOL;
