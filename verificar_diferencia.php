<?php
/**
 * Verificar valores exactos de la diferencia contable
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

// 1. SALDOS
$stmtMxn = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta IN(1,2) AND tipo_moneda='MXN'");
$totalSaldosMxn = $stmtMxn->fetchColumn();

$stmtDiv = $pdo->query("SELECT COALESCE(SUM(im.cantidad_restante * im.paridad),0) AS total FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE c.activo=1 AND c.tipo_cuenta IN(1,2) AND im.activo=1");
$totalSaldosDiv = $stmtDiv->fetchColumn();

$stmtPrestOtorgados = $pdo->query("SELECT COALESCE(SUM(COALESCE(monto_total_con_interes, monto_total) - monto_pagado),0) AS total FROM prestamos_otorgados WHERE estado IN ('pendiente', 'parcial') AND activo = 1");
$totalPrestamosOtorgados = $stmtPrestOtorgados->fetchColumn();

$totalSaldos = $totalSaldosMxn + $totalSaldosDiv + $totalPrestamosOtorgados;

// 2. CAPITAL
$stmtCap = $pdo->query("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta = 3");
$totalCapitalVal = (float)$stmtCap->fetchColumn();

$stmtCliMxn = $pdo->query("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL)");
$totalCliMxn = (float)$stmtCliMxn->fetchColumn();

$stmtProm = $pdo->query("SELECT COALESCE(SUM(c.saldo_actual),0) AS total FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01");
$totalProm = (float)$stmtProm->fetchColumn();

$totalCapitalVal += $totalCliMxn + $totalProm;

$USD = ['paridad' => 0, 'total' => 0, 'convertido' => 0];
$USDT = ['paridad' => 0, 'total' => 0, 'convertido' => 0];

foreach (['USD', 'USDT'] as $div) {
    $stmtP = $pdo->prepare("SELECT COALESCE(SUM(im.cantidad_restante*im.paridad)/NULLIF(SUM(im.cantidad_restante),0),0) AS p FROM inventario_monedas im INNER JOIN cuentas c ON im.cuenta_id=c.id WHERE im.activo=1 AND c.tipo_moneda=:m");
    $stmtP->execute(['m' => $div]);
    $paridad = $stmtP->fetchColumn();
    $stmtCli = $pdo->prepare("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND moneda=:m");
    $stmtCli->execute(['m' => $div]);
    $totalCliDiv = (float)$stmtCli->fetchColumn();
    $$div = ['paridad' => $paridad, 'total' => $totalCliDiv, 'convertido' => $totalCliDiv * $paridad];
    $totalCapitalVal += ($totalCliDiv * $paridad);
}

// 3. OBLIGACIONES
$stmtPrest = $pdo->query("SELECT COALESCE(SUM(monto-monto_pagado),0) AS total FROM deudas_empresa WHERE estado IN('pendiente','parcial') AND activo=1");
$totalPrestamistas = $stmtPrest->fetchColumn();

$stmtProv = $pdo->query("SELECT COALESCE(SUM(c.valor_mxn), 0) AS total FROM proveedores p INNER JOIN cuentas c ON (p.cuenta_usd_id = c.id OR p.cuenta_usdt_id = c.id) WHERE p.activo = 1");
$totalProveedores = $stmtProv->fetchColumn();

$totalObligaciones = $totalPrestamistas + $totalProveedores;

// 4. DIFERENCIA
$diferencia = round($totalSaldos - $totalCapitalVal - $totalObligaciones, 2);
$esBalanceado = abs($diferencia) < 0.10;

$nf = function($n) { return '$' . number_format((float)$n, 2); };
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Ecuaci�n Contable</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <style>
        :root { --card-shadow: 0 4px 20px rgba(0,0,0,0.08); --card-radius: 16px; --transition: all 0.3s ease; }
        body { background: #f4f6fb; font-family: system-ui, -apple-system, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .wrapper { width: 100%; max-width: 1100px; }
        .header-card { background: white; border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); padding: 28px 32px; margin-bottom: 24px; position: relative; overflow: hidden; }
        .header-card::before { content: ""; position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: var(--nx-primary); opacity: 0.06; border-radius: 50%; pointer-events: none; }
        .header-card::after { content: ""; position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: var(--nx-success); opacity: 0.06; border-radius: 50%; pointer-events: none; }
        .header-card h1 { font-size: 1.5rem; font-weight: 700; margin: 0; color: #1a1a2e; display: flex; align-items: center; gap: 12px; }
        .header-card h1 i { color: var(--nx-primary); font-size: 1.8rem; }
        .header-card p { color: #6c757d; margin: 6px 0 0 0; font-size: 0.9rem; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .card-stat { background: white; border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); padding: 24px; transition: var(--transition); }
        .card-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .card-stat .stat-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .card-stat .stat-header .icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .card-stat .stat-header h3 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; color: #6c757d; font-weight: 600; }
        .card-stat .total { font-size: 1.6rem; font-weight: 700; margin: 0 0 12px 0; line-height: 1.2; }
        .card-stat .breakdown { list-style: none; padding: 0; margin: 0; border-top: 1px solid #f5f2f4; padding-top: 10px; }
        .card-stat .breakdown li { display: flex; justify-content: space-between; font-size: 0.8rem; padding: 3px 0; color: #495057; }
        .card-stat .breakdown li .label { color: #6c757d; }
        .card-stat .breakdown li .value { font-weight: 600; }
        .status-card { background: white; border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); padding: 28px 32px; text-align: center; }
        .status-card .status-icon { font-size: 3rem; margin-bottom: 8px; }
        .status-card .status-text { font-size: 1.4rem; font-weight: 700; }
        .status-card .status-detail { font-size: 0.9rem; color: #6c757d; margin-top: 4px; }
        .progress-bar { height: 6px; border-radius: 3px; background: #f5f2f4; margin-top: 14px; overflow: hidden; }
        .progress-bar .fill { height: 100%; border-radius: 3px; }
        .table-detail { width: 100%; margin-top: 20px; background: white; border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); overflow: hidden; }
        .table-detail table { width: 100%; border-collapse: collapse; }
        .table-detail th { background: #f8f9fa; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; padding: 12px 20px; text-align: left; font-weight: 600; }
        .table-detail td { padding: 10px 20px; font-size: 0.9rem; border-top: 1px solid #f5f2f4; }
        .table-detail tr:last-child td { font-weight: 700; border-top: 2px solid #dee2e6; }
        .badge-green { color: var(--nx-success); } .badge-red { color: var(--nx-danger); }
        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 500px) { .grid { grid-template-columns: 1fr; } }
        [data-bs-theme="dark"] body { /* Helios */ }
        [data-bs-theme="dark"] .header-card, [data-bs-theme="dark"] .card-stat, [data-bs-theme="dark"] .status-card, [data-bs-theme="dark"] .table-detail { background: #1f2535; }
        [data-bs-theme="dark"] .header-card h1 { color: #e6ecff; }
        [data-bs-theme="dark"] .card-stat .stat-header h3 { color: #9aa4c8; }
        [data-bs-theme="dark"] .card-stat .total { color: #e6ecff; }
        [data-bs-theme="dark"] .card-stat .breakdown { border-top-color: #2b3350; }
        [data-bs-theme="dark"] .card-stat .breakdown li { color: #c8cee8; }
        [data-bs-theme="dark"] .card-stat .breakdown li .label { color: #9aa4c8; }
        [data-bs-theme="dark"] .table-detail th { background: #232b3c; color: #9aa4c8; }
        [data-bs-theme="dark"] .table-detail td { color: #c8cee8; border-top-color: #2b3350; }
        [data-bs-theme="dark"] .table-detail tr:last-child td { border-top-color: #3d4760; }
        [data-bs-theme="dark"] .status-card .status-detail { color: #9aa4c8; }
        [data-bs-theme="dark"] .progress-bar { background: #2b3350; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
<div class="wrapper">
    <div class="header-card">
        <h1><i class="ti ti-scale"></i> Verificaci�n de Ecuaci�n Contable</h1>
        <p>Diferencia = Saldos &minus; Capital &minus; Obligaciones &nbsp;&middot;&nbsp; El resultado debe ser $0.00</p>
    </div>
    <div class="grid">
        <div class="card-stat">
            <div class="stat-header">
                <div class="icon" style="background:rgba(93,135,255,0.1);color:var(--nx-primary);"><i class="ti ti-building-bank"></i></div>
                <h3>Saldos</h3>
            </div>
            <div class="total" style="color:var(--nx-primary);"><?= $nf($totalSaldos) ?></div>
            <ul class="breakdown">
                <li><span class="label">Cuentas tipo 1,2 MXN</span><span class="value"><?= $nf($totalSaldosMxn) ?></span></li>
                <li><span class="label">Inventario FIFO</span><span class="value"><?= $nf($totalSaldosDiv) ?></span></li>
                <li><span class="label">Pr�stamos otorgados</span><span class="value"><?= $nf($totalPrestamosOtorgados) ?></span></li>
            </ul>
        </div>
        <div class="card-stat">
            <div class="stat-header">
                <div class="icon" style="background:rgba(19,222,185,0.1);color:var(--nx-success);"><i class="ti ti-users"></i></div>
                <h3>Capital</h3>
            </div>
            <div class="total" style="color:var(--nx-success);"><?= $nf($totalCapitalVal) ?></div>
            <ul class="breakdown">
                <li><span class="label">Cuentas tipo 3</span><span class="value"><?= $nf($totalCapitalVal - $totalCliMxn - $totalProm - $USD['convertido'] - $USDT['convertido']) ?></span></li>
                <li><span class="label">Deudas clientes MXN</span><span class="value"><?= $nf($totalCliMxn) ?></span></li>
                <li><span class="label">Promotores externos</span><span class="value"><?= $nf($totalProm) ?></span></li>
                <?php if ((float)$USD['total'] > 0): ?><li><span class="label">Deudas USD</span><span class="value"><?= $nf($USD['convertido']) ?></span></li><?php endif; ?>
                <?php if ((float)$USDT['total'] > 0): ?><li><span class="label">Deudas USDT</span><span class="value"><?= $nf($USDT['convertido']) ?></span></li><?php endif; ?>
            </ul>
        </div>
        <div class="card-stat">
            <div class="stat-header">
                <div class="icon" style="background:rgba(250,137,107,0.1);color:var(--nx-danger);"><i class="ti ti-credit-card"></i></div>
                <h3>Obligaciones</h3>
            </div>
            <div class="total" style="color:var(--nx-danger);"><?= $nf($totalObligaciones) ?></div>
            <ul class="breakdown">
                <li><span class="label">Pr�stamos empresa</span><span class="value"><?= $nf($totalPrestamistas) ?></span></li>
                <li><span class="label">Proveedores</span><span class="value"><?= $nf($totalProveedores) ?></span></li>
            </ul>
        </div>
        <div class="card-stat">
            <div class="stat-header">
                <div class="icon" style="background:rgba(255,174,31,0.1);color:var(--nx-warning);"><i class="ti ti-math-symbols"></i></div>
                <h3>Diferencia</h3>
            </div>
            <div class="total" style="color:<?= $esBalanceado ? "var(--nx-success)" : "var(--nx-danger)" ?>;"><?= $nf($diferencia) ?></div>
            <ul class="breakdown">
                <li><span class="label">Saldos</span><span class="value"><?= number_format($totalSaldos,2) ?></span></li>
                <li><span class="label">&minus; Capital</span><span class="value"><?= number_format($totalCapitalVal,2) ?></span></li>
                <li><span class="label">&minus; Obligaciones</span><span class="value"><?= number_format($totalObligaciones,2) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="status-card">
        <div class="status-icon <?= $esBalanceado ? "badge-green" : "badge-red" ?>">
            <i class="ti <?= $esBalanceado ? "ti-circle-check" : "ti-alert-triangle" ?>"></i>
        </div>
        <div class="status-text" style="color:<?= $esBalanceado ? "var(--nx-success)" : "var(--nx-danger)" ?>;">
            <?= $esBalanceado ? "CUADRA PERFECTO" : "DESBALANCEADA" ?>
        </div>
        <div class="status-detail">
            <?php if ($esBalanceado): ?>
                Diferencia de $<?= number_format(abs($diferencia), 2) ?> &mdash; redondeo de punto flotante, contablemente $0.00
            <?php else: ?>
                Faltante: $<?= number_format(abs($diferencia), 2) ?> MXN &mdash; Revisa los movimientos
            <?php endif; ?>
        </div>
    </div>
    <div class="table-detail">
        <table>
            <thead><tr><th>Concepto</th><th>Categor�a</th><th style="text-align:right;">Monto MXN</th></tr></thead>
            <tbody>
                <tr><td>Cuentas tipo 1,2 (MXN)</td><td>SALDOS</td><td style="text-align:right;"><?= $nf($totalSaldosMxn) ?></td></tr>
                <tr><td>Inventario FIFO</td><td>SALDOS</td><td style="text-align:right;"><?= $nf($totalSaldosDiv) ?></td></tr>
                <tr><td>Pr�stamos otorgados pendientes</td><td>SALDOS</td><td style="text-align:right;"><?= $nf($totalPrestamosOtorgados) ?></td></tr>
                <tr><td>Subtotal SALDOS</td><td></td><td style="text-align:right;font-weight:700;"><?= $nf($totalSaldos) ?></td></tr>
                <tr><td>Cuentas tipo 3 (Capital)</td><td>CAPITAL</td><td style="text-align:right;"><?= $nf($totalCapitalVal - $totalCliMxn - $totalProm - $USD['convertido'] - $USDT['convertido']) ?></td></tr>
                <tr><td>Deudas clientes MXN pendientes</td><td>CAPITAL</td><td style="text-align:right;"><?= $nf($totalCliMxn) ?></td></tr>
                <tr><td>Promotores externos</td><td>CAPITAL</td><td style="text-align:right;"><?= $nf($totalProm) ?></td></tr>
                <?php if ((float)$USD['total'] > 0): ?><tr><td>Deudas clientes USD</td><td>CAPITAL</td><td style="text-align:right;"><?= $nf($USD['convertido']) ?></td></tr><?php endif; ?>
                <?php if ((float)$USDT['total'] > 0): ?><tr><td>Deudas clientes USDT</td><td>CAPITAL</td><td style="text-align:right;"><?= $nf($USDT['convertido']) ?></td></tr><?php endif; ?>
                <tr><td>Subtotal CAPITAL</td><td></td><td style="text-align:right;font-weight:700;"><?= $nf($totalCapitalVal) ?></td></tr>
                <tr><td>Pr�stamos empresa pendientes</td><td>OBLIGACIONES</td><td style="text-align:right;"><?= $nf($totalPrestamistas) ?></td></tr>
                <tr><td>Proveedores</td><td>OBLIGACIONES</td><td style="text-align:right;"><?= $nf($totalProveedores) ?></td></tr>
                <tr><td>Subtotal OBLIGACIONES</td><td></td><td style="text-align:right;font-weight:700;"><?= $nf($totalObligaciones) ?></td></tr>
                <tr style="background:#f8f9fa;">
                    <td><strong>DIFERENCIA (Saldos &minus; Capital &minus; Obligaciones)</strong></td>
                    <td></td>
                    <td style="text-align:right;font-weight:700;color:<?= $esBalanceado ? "var(--nx-success)" : "var(--nx-danger)" ?>;font-size:1.1rem;"><?= $nf($diferencia) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
