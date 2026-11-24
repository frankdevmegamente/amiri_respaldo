<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Utilidades (v2) - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        .chart-container { position: relative; height: 300px; }
        .nav-pills .nav-link { border-radius: 8px; padding: 10px 20px; }
        .nav-pills .nav-link.active { background: var(--nx-accent-gradient); }
        .table-reportes th { background-color: var(--nx-surface-2); font-weight: 600; }
        [data-bs-theme="dark"] .table-reportes th { background-color: var(--nx-surface-2); }
        .metric-card-util { transition: all 0.25s ease; position: relative; overflow: hidden; }
        .metric-card-util:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08) !important; }
        [data-bs-theme="dark"] .metric-card-util:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.25) !important; }
        .metric-card-util .top-strip { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 0.75rem 0.75rem 0 0; }
        .util-icon { width: 42px; height: 42px; }
        .util-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--nx-text-muted); }
        [data-bs-theme="dark"] .util-label { color: var(--nx-text-muted); }
        .util-value { font-size: 1.5rem; font-weight: 800; color: var(--nx-text); line-height: 1.2; letter-spacing: -0.3px; }
        [data-bs-theme="dark"] .util-value { color: #ffffff; }
        .util-breakdown { padding-top: 10px; margin-top: 6px; border-top: 1px solid var(--nx-border); }
        [data-bs-theme="dark"] .util-breakdown { border-top-color: #1a181c; }
        .util-breakdown .br-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 0.78rem; color: var(--nx-text-muted); }
        [data-bs-theme="dark"] .util-breakdown .br-row { color: var(--nx-text-muted); }
        .util-breakdown .br-row .br-label { min-width: 0; }
        .util-breakdown .br-row .br-value { font-weight: 700; white-space: nowrap; margin-left: 12px; }
        .util-breakdown .br-divider { height: 1px; background: var(--nx-border); margin: 4px 0; }
        [data-bs-theme="dark"] .util-breakdown .br-divider { background: #1a181c; }
        .util-breakdown .br-total .br-value { font-size: 0.85rem; }
        [data-bs-theme="dark"] .metric-card-util { background-color: var(--nx-surface) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .metric-card-util .util-icon { background-color: rgba(255,255,255,0.06) !important; }
        [data-bs-theme="dark"] .metric-card-util .util-value { color: #ffffff !important; }
        [data-bs-theme="dark"] .quick-stat-box { background-color: rgba(255,255,255,0.04) !important; }
        [data-bs-theme="dark"] .util-breakdown .border-top { border-top-color: #1a181c !important; }
        .trend-badge { font-size: 0.65rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px; }
        .trend-badge.up { background: rgba(197,143,184,0.14); color: #d9a3cc; }
        .trend-badge.down { background: rgba(255,122,122,0.14); color: #ff7a7a; }
        .trend-badge.neutral { background: rgba(255,255,255,0.10); color: var(--nx-text-muted); }
        .drill-link { transition: all 0.15s; display:inline-flex; align-items:center; gap:4px; }
        .drill-link .ti-search { font-size:14px; opacity:0.6; transition:all 0.15s; }
        .drill-link:hover { color: #c58fb8 !important; }
        .drill-link:hover .ti-search { opacity: 1; font-size:16px; }
        [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro { display: block !important; }
        @keyframes skeleton-pulse { 0%{opacity:0.6} 50%{opacity:1} 100%{opacity:0.6} }
        .skeleton { animation:skeleton-pulse 1.4s ease-in-out infinite; background:linear-gradient(90deg,var(--nx-surface-2) 25%,var(--nx-surface-3) 50%,var(--nx-surface-2) 75%); background-size:200% 100%; border-radius:6px; }
        [data-bs-theme="dark"] .skeleton { background:linear-gradient(90deg,#1a181c 25%,#2a2427 50%,#1a181c 75%); background-size:200% 100%; }
        .skeleton-text { height:14px; width:60%; margin-bottom:6px; }
        .skeleton-value { height:28px; width:45%; margin-bottom:8px; }
        .skeleton-badge { height:18px; width:80px; border-radius:20px; }
        .skeleton-chart { height:260px; width:100%; border-radius:8px; }
        .empty-state { text-align:center; padding:24px 16px; }
        .empty-state .empty-icon { font-size:2.5rem; opacity:0.25; margin-bottom:8px; display:block; }
        .empty-state .empty-title { font-size:0.95rem; font-weight:600; color: var(--nx-text-muted); margin-bottom:4px; }
        .empty-state .empty-sub { font-size:0.78rem; color:var(--nx-text-muted); }
        [data-bs-theme="dark"] .empty-state .empty-title { color:var(--nx-text-muted); }
        [data-bs-theme="dark"] .empty-state .empty-sub { color:#9a9096; }
        .top-cliente-row { display:flex; align-items:center; gap:8px; padding:5px 0; border-bottom:1px solid var(--nx-border); }
        [data-bs-theme="dark"] .top-cliente-row { border-bottom-color:#1a181c; }
        .top-cliente-row:last-child { border-bottom:none; }
        .top-cliente-rank { width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.6rem; font-weight:700; color:#fff; flex-shrink:0; }
        .top-cliente-name { flex:1; font-size:0.78rem; font-weight:600; color: var(--nx-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        [data-bs-theme="dark"] .top-cliente-name { color:#ffffff; }
        .top-cliente-bar-wrap { flex:0 0 80px; height:6px; background:var(--nx-border); border-radius:4px; overflow:hidden; }
        [data-bs-theme="dark"] .top-cliente-bar-wrap { background:#1a181c; }
        .top-cliente-bar { height:100%; border-radius:4px; transition:width 0.6s ease; }
        .top-cliente-value { font-size:0.72rem; font-weight:700; color: var(--nx-text-muted); min-width:55px; text-align:right; }
        [data-bs-theme="dark"] .top-cliente-value { color:var(--nx-text-muted); }
        .sparkline-wrap { width:100%; height:36px; margin-top:6px; }
        .meta-bar-wrap { margin-top:8px; padding-top:6px; border-top:1px solid var(--nx-border); }
        [data-bs-theme="dark"] .meta-bar-wrap { border-top-color:#1a181c; }
        .meta-bar-label { display:flex; justify-content:space-between; font-size:0.68rem; color: var(--nx-text-muted); margin-bottom:3px; }
        .meta-bar-track { height:5px; background:var(--nx-border); border-radius:4px; overflow:hidden; }
        [data-bs-theme="dark"] .meta-bar-track { background:#1a181c; }
        .meta-bar-fill { height:100%; border-radius:4px; transition:width 0.8s ease; }
        .meta-btn { font-size:0.65rem; padding:0 4px; color: var(--nx-text-muted); cursor:pointer; }
        .meta-btn:hover { color:#c58fb8; }
        .br-section-label { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--nx-text-muted); margin-bottom:4px; }
        [data-bs-theme="dark"] .br-section-label { color:#9a9096; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
    data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php include 'src/assets/partials/header.php'; ?>
    <?php include 'src/assets/partials/sidebar.php'; ?>
    <div class="body-wrapper">
        <div class="body-wrapper-inner">
            <div class="container-fluid px-4" style="max-width: 100%;">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body px-4 py-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">
                                                <i class="ti ti-coin fs-6"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-semibold mb-0" style="font-size:1rem;">Reporte de Utilidades (v2)</h5>
                                                <p class="text-muted mb-0" style="font-size:0.75rem;">Utilidades desglosadas por servicio, cliente y promotor</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                                            <div style="min-width:130px;">
                                                <select class="form-select form-select-sm" id="selectPeriodo">
                                                    <option value="mes" selected>Este Mes</option>
                                                    <option value="mes_pasado">Mes Pasado</option>
                                                    <option value="anio">Este AÃ±o</option>
                                                    <option value="rango">Fecha Personalizada</option>
                                                </select>
                                            </div>
                                            <div id="fechaInicioGroup" style="display:none;min-width:120px;">
                                                <input type="date" class="form-control form-control-sm" id="fechaInicio">
                                            </div>
                                            <div id="fechaFinGroup" style="display:none;min-width:120px;">
                                                <input type="date" class="form-control form-control-sm" id="fechaFin">
                                            </div>
                                            <div>
                                                <button class="btn btn-primary btn-sm" id="btnFiltrar">
                                                    <i class="ti ti-filter"></i> Aplicar
                                                </button>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border-0 py-1 px-2" id="rangoFechas"><i class="ti ti-calendar me-1"></i>Este Mes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="top-strip" style="background:linear-gradient(90deg,#c58fb8,#fbbf24,#d9a3cc);height:3px;border-radius:0.75rem 0.75rem 0 0;"></div>
                            <div class="card-body p-4">
                                <div class="skeleton-wrap" id="skeletonResumen" style="display:none;">
                                    <div class="row g-3">
                                        <div class="col-xl-4"><div class="skeleton skeleton-chart"></div></div>
                                        <div class="col-xl-4"><div class="skeleton skeleton-chart"></div></div>
                                        <div class="col-xl-4"><div class="skeleton skeleton-chart"></div></div>
                                    </div>
                                </div>
                                <div id="contentResumen">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px;height:30px;">
                                            <i class="ti ti-report-money fs-6"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-semibold mb-0" style="font-size:0.95rem;">Estado de Resultados</h5>
                                            <span class="text-muted" style="font-size:0.7rem;">Utilidades desglosadas por ingreso, gasto y resultado final</span>
                                        </div>
                                        <span class="badge bg-purple bg-opacity-10 text-purple border-0 ms-auto" id="rangoResultados">PerÃ­odo actual</span>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border-0" style="font-size:0.6rem;cursor:help;" title="Los datos se actualizan automÃ¡ticamente al cambiar el perÃ­odo o promotor. Fuentes: deudas_clientes, movimientos, gastos.">fuentes</span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-xl-4 col-md-6 mb-3">
                                            <div class="card card-stat border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="stat-icon bg-purple bg-opacity-10 text-purple me-3">
                                                            <i class="ti ti-trending-up"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1 small">
                                                                Utilidad Bruta
                                                                <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="utilidad_bruta" style="cursor:help;font-size:14px;"></i>
                                                            </p>
                                                            <h4 class="mb-0 fw-bold" id="statUtilidadBruta">$0.00</h4>
                                                        </div>
                                                    </div>
                                                    <div class="bg-light rounded p-2 small mt-2 border">
                                                        <div class="d-flex justify-content-between text-muted mb-1">
                                                            <span>(+) Utilidad AMIRI:</span> <span id="lblComisionesAmiri" class="fw-bold text-dark">$0.00</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-muted mb-1" id="brutaDivisasToggle" style="cursor:pointer;">
                                                            <span><i class="ti ti-chevron-down" id="brutaDivisasChevron"></i> (+) Ganancia Divisas:</span>
                                                            <span id="lblGananciaDivisas" class="fw-bold text-dark">$0.00</span>
                                                        </div>
                                                        <div id="brutaDivisasDetalle" style="display:none;">
                                                            <div class="d-flex justify-content-between text-muted ps-4 mb-1">
                                                                <span>USD:</span> <span id="lblBrutaDivisasUsd" class="fw-bold text-dark">$0.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-muted ps-4 mb-1">
                                                                <span>USDT:</span> <span id="lblBrutaDivisasUsdt" class="fw-bold text-dark">$0.00</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1 fw-bold border-top pt-1" style="color:#d9a3cc;">
                                                            <span>(=) UTILIDAD BRUTA:</span> <span id="lblUtilidadBruta">$0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 mb-3">
                                            <div class="card card-stat border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                                            <i class="ti ti-trending-up"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1 small">
                                                                Utilidad Neta
                                                                <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="resultado_operativo_real" style="cursor:help;font-size:14px;"></i>
                                                            </p>
                                                            <h4 class="mb-0 fw-bold" id="statUtilidadNeta">$0.00</h4>
                                                        </div>
                                                    </div>
                                                    <div class="bg-light rounded p-2 small mt-2 border">
                                                        <div class="d-flex justify-content-between text-muted mb-1">
                                                            <span>(+) Bruta:</span> <span id="lblNetaBruta" class="fw-bold text-dark">$0.00</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-danger mb-1" id="netaGastosToggle" style="cursor:pointer;">
                                                            <span><i class="ti ti-chevron-down" id="netaGastosChevron"></i> (-) Gastos:</span>
                                                            <span id="lblNetaGastos" class="fw-bold">$0.00</span>
                                                        </div>
                                                        <div id="netaGastosDetalle" style="display:none;">
                                                            <div class="d-flex justify-content-between text-danger ps-3 mb-1">
                                                                <span>Gastos Fijos:</span> <span id="lblNetaGastosFijos">$0.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-danger ps-3 mb-1">
                                                                <span>Gastos Variables:</span> <span id="lblNetaGastosVariables">$0.00</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1 fw-bold border-top pt-1" style="color:#d9a3cc;">
                                                            <span>(=) NETO:</span> <span id="lblNetFinal">$0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 mb-3">
                                            <div class="card card-stat border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                                            <i class="ti ti-percentage"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1 small">
                                                                Rentabilidad Neta
                                                            </p>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <h4 class="mb-0 fw-bold" id="statRentabilidadNeta">0%</h4>
                                                                <span id="trendRentabilidad"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bg-light rounded p-2 small mt-2 border">
                                                        <div class="d-flex justify-content-between text-muted mb-1" id="rentNetaToggle" style="cursor:pointer;">
                                                            <span><i class="ti ti-chevron-down" id="rentNetaChevron"></i> (=) Utilidad Neta:</span>
                                                            <span id="lblRentNeta" class="fw-bold text-dark">$0.00</span>
                                                        </div>
                                                        <div id="rentNetaDetalle" style="display:none;">
                                                            <div class="d-flex justify-content-between text-muted ps-3 mb-1">
                                                                <span>(+) Bruta:</span> <span id="lblRentNetaBruta" class="fw-bold text-dark">$0.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-danger ps-3 mb-1">
                                                                <span>(-) Gastos:</span> <span id="lblRentNetaGastos" class="fw-bold">$0.00</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-muted mb-1" id="rentBrutaToggle" style="cursor:pointer;">
                                                            <span><i class="ti ti-chevron-down" id="rentBrutaChevron"></i> (Ã·) Utilidad Bruta:</span>
                                                            <span id="lblRentBruta" class="fw-bold text-dark">$0.00</span>
                                                        </div>
                                                        <div id="rentBrutaDetalle" style="display:none;">
                                                            <div class="d-flex justify-content-between text-muted ps-3 mb-1">
                                                                <span>(+) Utilidad AMIRI:</span> <span id="lblRentBrutaAmiri" class="fw-bold text-dark">$0.00</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between text-muted ps-3 mb-1" id="rentDivisasToggle" style="cursor:pointer;">
                                                                <span><i class="ti ti-chevron-down" id="rentDivisasChevron"></i> (+) Ganancia Divisas:</span>
                                                                <span id="lblRentBrutaDivisas" class="fw-bold text-dark">$0.00</span>
                                                            </div>
                                                            <div id="rentDivisasDetalle" style="display:none;">
                                                                <div class="d-flex justify-content-between text-muted ps-4 mb-1">
                                                                    <span>USD:</span> <span id="lblRentDivisasUsd" class="fw-bold text-dark">$0.00</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between text-muted ps-4 mb-1">
                                                                    <span>USDT:</span> <span id="lblRentDivisasUsdt" class="fw-bold text-dark">$0.00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1 fw-bold border-top pt-1" style="color:#d9a3cc;">
                                                            <span>Rent. Neta:</span> <span id="lblRentResultado">0%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent d-flex align-items-center flex-wrap gap-2">
                                <h6 class="fw-semibold mb-0"><i class="ti ti-dashboard me-2" style="color:#c58fb8;"></i>Dashboard de Utilidades</h6>
                                <span class="badge bg-purple bg-opacity-10 text-purple border-0 ms-1" id="rangoDashboard">PerÃ­odo actual</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-lg-4 mb-3 mb-lg-0">
                                        <h6 class="fw-semibold mb-3 small text-muted text-uppercase" style="letter-spacing:0.4px;"><i class="ti ti-chart-bar me-1" style="color:#c58fb8;"></i> Utilidad por Servicio</h6>
                                        <div class="chart-container">
                                            <canvas id="chartServicio"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mb-3 mb-lg-0">
                                        <h6 class="fw-semibold mb-3 small text-muted text-uppercase" style="letter-spacing:0.4px;"><i class="ti ti-chart-pie me-1" style="color:#d9a3cc;"></i> Utilidad por Cliente (Top 5)</h6>
                                        <div class="chart-container">
                                            <canvas id="chartCliente"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <h6 class="fw-semibold mb-3 small text-muted text-uppercase" style="letter-spacing:0.4px;"><i class="ti ti-chart-donut me-1" style="color:#fbbf24;"></i> Utilidad por Promotor</h6>
                                        <div class="chart-container">
                                            <canvas id="chartPromotor"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-3 pt-3 border-top">
                                    <div class="col-md-3 col-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(197,143,184,0.1);color:#c58fb8;">
                                                <i class="ti ti-clipboard-list" style="font-size:14px;"></i>
                                            </div>
                                            <div style="line-height:1.2;">
                                                <p class="text-muted mb-0" style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.3px;">Servicios</p>
                                                <strong class="fs-6" id="totalUtilidadServicio" style="color: var(--nx-text);">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(197,143,184,0.12);color:var(--nx-primary);">
                                                <i class="ti ti-users" style="font-size:14px;"></i>
                                            </div>
                                            <div style="line-height:1.2;">
                                                <p class="text-muted mb-0" style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.3px;">Clientes</p>
                                                <strong class="fs-6" id="totalUtilidadCliente" style="color: var(--nx-text);">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(197,143,184,0.12);color:#d9a3cc;">
                                                <i class="ti ti-user-plus" style="font-size:14px;"></i>
                                            </div>
                                            <div style="line-height:1.2;">
                                                <p class="text-muted mb-0" style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.3px;">Promotores</p>
                                                <strong class="fs-6" id="totalUtilidadPromotor" style="color: var(--nx-text);">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(251,191,36,0.14);color:#fbbf24;">
                                                <i class="ti ti-percentage" style="font-size:14px;"></i>
                                            </div>
                                            <div style="line-height:1.2;">
                                                <p class="text-muted mb-0" style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.3px;">Margen Prom.</p>
                                                <strong class="fs-6" id="statMargenPromedio" style="color: var(--nx-text);">0%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2 pt-3 border-top">
                                    <div class="col-lg-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="ti ti-crown" style="color:#fbbf24;font-size:14px;"></i>
                                            <h6 class="fw-semibold mb-0" style="font-size:0.82rem;">Top 10 Clientes</h6>
                                        </div>
                                        <div id="topClientesList"><div class="empty-state"><span class="empty-icon"><i class="ti ti-users"></i></span><div class="empty-title">Sin datos</div><div class="empty-sub">No hay clientes en el perÃ­odo</div></div></div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="ti ti-table" style="color:#c58fb8;font-size:14px;"></i>
                                            <h6 class="fw-semibold mb-0" style="font-size:0.82rem;">Explorador de Datos</h6>
                                            <div class="btn-group btn-group-sm ms-3" role="group" id="tablaToggle2">
                                                <button type="button" class="btn btn-primary active" data-table="servicio">Por Servicio</button>
                                                <button type="button" class="btn btn-outline-primary" data-table="promotor">Por Promotor</button>
                                                <button type="button" class="btn btn-outline-primary" data-table="cliente">Por Cliente</button>
                                            </div>
                                        </div>
                                        <div class="table-section" id="tablaSectionServicio2">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-reportes" id="tablaServicio">
                                                    <thead>
                                                        <tr>
                                                            <th>Servicio</th>
                                                            <th class="text-end">Operaciones</th>
                                                            <th class="text-end">Utilidad AMIRI</th>
                                                            <th class="text-end">Ganancia Divisas</th>
                                                            <th class="text-end">Total</th>
                                                            <th class="text-end">% ContribuciÃ³n</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyServicio"></tbody>
                                                    <tfoot class="table-light fw-bold">
                                                        <tr>
                                                            <td>TOTALES</td>
                                                            <td class="text-end" id="totalServOps">0</td>
                                                            <td class="text-end" id="totalServAmiri">$0.00</td>
                                                            <td class="text-end" id="totalServDivisas">$0.00</td>
                                                            <td class="text-end" id="totalServTotal">$0.00</td>
                                                            <td class="text-end">100%</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="table-section d-none" id="tablaSectionPromotor2">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-reportes" id="tablaPromotor">
                                                    <thead>
                                                        <tr>
                                                            <th>Promotor</th>
                                                            <th class="text-end">Clientes</th>
                                                            <th class="text-end">Operaciones</th>
                                                            <th class="text-end">Utilidad AMIRI</th>
                                                            <th class="text-end">Utilidad Promotor</th>
                                                            <th class="text-end">% ContribuciÃ³n</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyPromotor"></tbody>
                                                    <tfoot class="table-light fw-bold">
                                                        <tr>
                                                            <td>TOTALES</td>
                                                            <td class="text-end" id="totalPromClientes">0</td>
                                                            <td class="text-end" id="totalPromOps">0</td>
                                                            <td class="text-end" id="totalPromAmiri">$0.00</td>
                                                            <td class="text-end" id="totalPromPromotor">$0.00</td>
                                                            <td class="text-end">100%</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="table-section d-none" id="tablaSectionCliente2">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-reportes" id="tablaCliente">
                                                    <thead>
                                                        <tr>
                                                            <th>Cliente</th>
                                                            <th>Promotor</th>
                                                            <th class="text-end">Operaciones</th>
                                                            <th class="text-end">Monto Total</th>
                                                            <th class="text-end">Utilidad AMIRI</th>
                                                            <th class="text-end">% ContribuciÃ³n</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyCliente"></tbody>
                                                    <tfoot class="table-light fw-bold">
                                                        <tr>
                                                            <td>TOTALES</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-end" id="totalCliMonto">$0.00</td>
                                                            <td class="text-end" id="totalCliComision">$0.00</td>
                                                            <td class="text-end">100%</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-bar me-2" style="color:#c58fb8;"></i>Utilidad por DÃ­a</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted" id="totalPeriodoGrafica"></small>
                                    <select class="form-select form-select-sm w-auto" id="selectGrafica">
                                        <option value="utilidad_diaria" selected>Diaria (por dÃ­a)</option>
                                        <option value="utilidad_bruta">Mensual (tendencia)</option>
                                        <option value="por_servicio">Por Servicio</option>
                                        <option value="por_promotor">Por Promotor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height:350px;">
                                    <canvas id="chartComparativa"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-premium" id="modalDetalleDia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-list-details me-2"></i>Operaciones del DÃ­a <span id="modalDiaTitulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Servicio</th>
                                <th>Cliente</th>
                                <th>Promotor</th>
                                <th class="text-end">Monto Total</th>
                                <th class="text-end">Com. AMIRI</th>
                                <th class="text-end">Com. Promotor</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleDia"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4">TOTALES</td>
                                <td class="text-end" id="totalDetalleMonto">$0.00</td>
                                <td class="text-end" id="totalDetalleAmiri">$0.00</td>
                                <td class="text-end" id="totalDetallePromotor">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="src/assets/js/sidebarmenu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>if (window.nxChartTheme) window.nxChartTheme();</script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="src/assets/js/utils.js"></script>
<script src="src/assets/js/pantalla-completa.js"></script>
<script>
function renderTopClientes(datos) {
    var top = datos.slice(0, 10);
    if (!top.length) {
        $('#topClientesList').html('<div class="empty-state"><span class="empty-icon"><i class="ti ti-users"></i></span><div class="empty-title">Sin datos</div><div class="empty-sub">No hay clientes en el perÃ­odo</div></div>');
        return;
    }
    var maxVal = parseFloat(top[0].comision_amiri) || 1;
    var colores = ['#c58fb8','var(--nx-primary)','var(--nx-success)','var(--nx-warning)','#ff7a7a','#ff7a7a','#b585a8','#3ddc84','#c58fb8','#d9a3cc'];
    var html = '';
    top.forEach(function(r, i) {
        var v = parseFloat(r.comision_amiri) || 0;
        var pct = (v / maxVal) * 100;
        html += '<div class="top-cliente-row">' +
            '<div class="top-cliente-rank" style="background:' + colores[i] + ';">' + (i + 1) + '</div>' +
            '<span class="top-cliente-name">' + (r.cliente || 'â€”') + '</span>' +
            '<div class="top-cliente-bar-wrap"><div class="top-cliente-bar" style="width:' + pct + '%;background:' + colores[i] + ';"></div></div>' +
            '<span class="top-cliente-value">$' + formatoNumero(v) + '</span>' +
        '</div>';
    });
    $('#topClientesList').html(html);
}

let chartServicio, chartCliente, chartPromotor, chartComparativa;
let dtServicio, dtCliente, dtPromotor;
const COLORES = ['#c58fb8','var(--nx-warning)','var(--nx-success)','var(--nx-primary)','var(--nx-warning)','var(--nx-success)','var(--nx-primary)','var(--nx-warning)','#d9a3cc','#d9a3cc'];

function formatoNumero(num) {
    num = parseFloat(num || 0);
    if (isNaN(num)) num = 0;
    var parts = num.toFixed(2).split('.');
    var intPart = parts[0], decPart = parts[1];
    var result = '', count = 0;
    for (var i = intPart.length - 1; i >= 0; i--) {
        if (count > 0 && count % 3 === 0) result = ',' + result;
        result = intPart[i] + result;
        count++;
    }
    return result + '.' + decPart;
}

function formatoMoneda(num, moneda) {
    var s = '$';
    if (moneda === 'USD') s = 'US$';
    else if (moneda === 'EUR') s = '\u20AC';
    return s + formatoNumero(num);
}

function getParams() {
    let periodo = $('#selectPeriodo').val();
    var params = '';
    if (periodo === 'mes_pasado') {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        const anio = d.getFullYear();
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const dias = new Date(anio, d.getMonth() + 1, 0).getDate();
        params = `periodo=rango&fecha_inicio=${anio}-${mes}-01&fecha_fin=${anio}-${mes}-${dias}`;
    } else if (periodo === 'rango') {
        params = `periodo=rango&fecha_inicio=${$('#fechaInicio').val()}&fecha_fin=${$('#fechaFin').val()}`;
    } else {
        params = `periodo=${periodo}`;
    }
    return params;
}

function mostrarCarga(mostrar) {
    $('#loadingOverlay').toggle(mostrar);
}

function mostrarError(msg) {
    const toast = `<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index:9999;" role="alert">
        <i class="ti ti-alert-circle me-1"></i> ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    $('body').append(toast);
    setTimeout(function() { $('.alert-danger').alert('close'); }, 5000);
}

function cargarResumen() {
    mostrarSkeleton(true);
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=resumen&${getParams()}`)
        .done(function(data) {
            const bruta = parseFloat(data.utilidad_bruta) || 0;
            const neta = parseFloat(data.utilidad_neta) || 0;
            const gastos = parseFloat(data.gastos_total) || 0;
            var margen = bruta > 0 ? (neta / bruta) * 100 : 0;
            $('#statUtilidadNeta').text('$' + formatoNumero(neta));
            $('#statUtilidadBruta').text('$' + formatoNumero(bruta));
            $('#statRentabilidadNeta').text(margen.toFixed(1) + '%');
            $('#lblComisionesAmiri').text('$' + formatoNumero(data.comisiones_amiri));
            $('#lblGananciaDivisas').text('$' + formatoNumero(data.ganancia_divisas));
            $('#lblUtilidadBruta').text('$' + formatoNumero(bruta));
            $('#lblNetaBruta').text('$' + formatoNumero(bruta));
            $('#lblNetaGastos').text('$' + formatoNumero(gastos));
            $('#lblNetaGastosFijos').text('$' + formatoNumero(data.gastos_fijos));
            $('#lblNetaGastosVariables').text('$' + formatoNumero(data.gastos_variables));
            $('#lblNetFinal').text('$' + formatoNumero(neta));
            $('#lblBrutaDivisasUsd').text('$' + formatoNumero(data.ganancia_usd));
            $('#lblBrutaDivisasUsdt').text('$' + formatoNumero(data.ganancia_usdt));
            $('#lblRentNeta').text('$' + formatoNumero(neta));
            $('#lblRentBruta').text('$' + formatoNumero(bruta));
            $('#lblRentNetaBruta').text('$' + formatoNumero(bruta));
            $('#lblRentNetaGastos').text('$' + formatoNumero(gastos));
            $('#lblRentBrutaAmiri').text('$' + formatoNumero(data.comisiones_amiri));
            $('#lblRentBrutaDivisas').text('$' + formatoNumero(data.ganancia_divisas));
            $('#lblRentDivisasUsd').text('$' + formatoNumero(data.ganancia_usd));
            $('#lblRentDivisasUsdt').text('$' + formatoNumero(data.ganancia_usdt));
            $('#lblRentResultado').text(margen.toFixed(1) + '%').css('color', margen >= 0 ? 'var(--nx-success)' : '#ff7a7a');
            if (neta < 0) {
                $('#statUtilidadNeta, #lblNetFinal').removeClass('text-primary').addClass('text-danger').css('color', '#ff7a7a');
            } else {
                $('#statUtilidadNeta, #lblNetFinal').removeClass('text-danger').addClass('text-primary').css('color', 'var(--nx-success)');
            }

            var margenColor = margen >= 0 ? 'var(--nx-success)' : '#ff7a7a';
            $('#statRentabilidadNeta').css('color', margenColor);
            $('#statMargenPromedio').text(margen.toFixed(1) + '%').css('color', margenColor);

            $.getJSON(`api/reportes-utilidades-v2.php?tipo=comparativa&${getParams()}`)
                .done(function(prev) {
                    var prevBruta = parseFloat(prev.utilidad_bruta) || 0;
                    var prevNeta = parseFloat(prev.utilidad_neta) || 0;
                    var prevGastos = parseFloat(prev.gastos_total) || 0;

                    function badgeTendencia(actual, anterior) {
                        if (anterior === 0) return '<span class="trend-badge neutral" title="Periodo actual: $' + formatoNumero(actual) + ' | Periodo anterior: $0"><i class="ti ti-minus"></i> â€”</span>';
                        var diff = ((actual - anterior) / anterior) * 100;
                        var cls = diff >= 0 ? 'up' : 'down';
                        var icon = diff >= 0 ? 'ti ti-trending-up' : 'ti ti-trending-down';
                        return '<span class="trend-badge ' + cls + '" title="Actual: $' + formatoNumero(actual) + ' | Anterior: $' + formatoNumero(anterior) + '"><i class="' + icon + '" style="font-size:10px;"></i> ' + (diff >= 0 ? '+' : '') + diff.toFixed(1) + '%</span>';
                    }
                    $('#trendNeta').html(badgeTendencia(neta, prevNeta));

                    var prevMargen = prevBruta > 0 ? (prevNeta / prevBruta) * 100 : 0;
                    var ppDiff = margen - prevMargen;
                    var ppCls = ppDiff >= 0 ? 'up' : 'down';
                    var ppIcon = ppDiff >= 0 ? 'ti ti-trending-up' : 'ti ti-trending-down';
                    $('#trendRentabilidad').html('<span class="trend-badge ' + ppCls + '" title="Actual: ' + margen.toFixed(1) + '% | Anterior: ' + prevMargen.toFixed(1) + '%"><i class="' + ppIcon + '" style="font-size:10px;"></i> ' + (ppDiff >= 0 ? '+' : '') + ppDiff.toFixed(1) + ' p.p.</span>');
                })
                .fail(function() {
                    mostrarError('Error al cargar comparativa');
                });

            if (data._explicacion) {
                $('.tooltip-icon').each(function() {
                    const key = $(this).data('explica');
                    const texto = data._explicacion[key];
                    if (texto) $(this).attr('title', texto);
                });
                $('.tooltip-icon').tooltip('dispose');
                $('[title]').tooltip({ placement: 'top', trigger: 'hover' });
            }

        })
        .fail(function() { mostrarError('Error al cargar resumen de utilidades'); })
        .always(function() { mostrarSkeleton(false); });
}

function cargarPorServicio() {
    mostrarCarga(true);
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=por_servicio&${getParams()}`)
        .done(function(data) {
            const datos = data.datos || [];
            const totalUtilidad = parseFloat(data.total_utilidad) || 0;
            $('#totalUtilidadServicio').text('$' + formatoNumero(totalUtilidad));
            let html = '';
            let totalOps = 0, totalAmiri = 0, totalDivisas = 0, total = 0;
            datos.forEach(function(r) {
                const amiri = parseFloat(r.comisiones_amiri) || 0;
                const divisas = parseFloat(r.ganancia_divisas) || 0;
                const t = parseFloat(r.total) || 0;
                totalOps += parseInt(r.operaciones) || 0;
                totalAmiri += amiri;
                totalDivisas += divisas;
                total += t;
                html += `<tr>
                    <td><a href="#" class="drill-link text-decoration-none fw-medium" data-servicio="${r.servicio || 'â€”'}"><i class="ti ti-search"></i>${r.servicio || 'â€”'}</a></td>
                    <td class="text-end">${r.operaciones || 0}</td>
                    <td class="text-end">$${formatoNumero(amiri)}</td>
                    <td class="text-end">$${formatoNumero(divisas)}</td>
                    <td class="text-end fw-bold">$${formatoNumero(t)}</td>
                    <td class="text-end"><span class="badge bg-primary bg-opacity-10 text-primary border-0">${r.porcentaje || 0}%</span></td>
                </tr>`;
            });
            if (dtServicio) { dtServicio.destroy(); dtServicio = null; }
            $('#bodyServicio').html(html || '<tr><td colspan="6" class="text-center">No hay datos</td></tr>');
            dtServicio = $('#tablaServicio').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                pageLength: 10, order: [[4, 'desc']], searching: false, info: false,
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: [0, 4] },
                    { responsivePriority: 2, targets: [2, 5] },
                    { responsivePriority: 3, targets: [1, 3] }
                ]
            });
            $('#totalServOps').text(totalOps);
            $('#totalServAmiri').text('$' + formatoNumero(totalAmiri));
            $('#totalServDivisas').text('$' + formatoNumero(totalDivisas));
            $('#totalServTotal').text('$' + formatoNumero(total));
            const ctx = document.getElementById('chartServicio').getContext('2d');
            if (chartServicio) chartServicio.destroy();
            chartServicio = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datos.map(d => d.servicio),
                    datasets: [{ label: 'Total', data: datos.map(d => parseFloat(d.total) || 0), backgroundColor: COLORES.slice(0, datos.length), borderRadius: 6, borderSkipped: false }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 }, callbacks: { label: function(c) { return '$' + formatoNumero(c.parsed.y); } } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toFixed(0); } } } }
                }
            });
        })
        .fail(function() { mostrarError('Error al cargar utilidades por servicio'); })
        .always(function() { mostrarCarga(false); });
}

function initClienteTable() {
    dtCliente = $('#tablaCliente').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10, order: [[4, 'desc']],
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: [0, 4] },
            { responsivePriority: 2, targets: [3, 5] },
            { responsivePriority: 3, targets: [1, 2] }
        ]
    });
}

function cargarPorCliente() {
    mostrarCarga(true);
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=por_cliente&${getParams()}`)
        .done(function(data) {
            const datos = data.datos || [];
            const totalUtilidad = parseFloat(data.total_utilidad) || 0;
            $('#totalUtilidadCliente').text('$' + formatoNumero(totalUtilidad));
            let html = '';
            let totalMonto = 0, totalComision = 0;
            datos.forEach(function(r) {
                const comision = parseFloat(r.comision_amiri) || 0;
                const monto = parseFloat(r.monto_total) || 0;
                totalMonto += monto;
                totalComision += comision;
                html += `<tr>
                    <td>
                        <a href="detalle-cliente.php?id=${r.cliente_id}" class="me-1">${r.cliente}</a>
                        <a href="#" class="drill-link text-muted" data-cliente-id="${r.cliente_id}" title="Ver operaciones"><i class="ti ti-search"></i></a>
                    </td>
                    <td>${r.promotor || '-'}</td>
                    <td class="text-end">${r.operaciones || 0}</td>
                    <td class="text-end">$${formatoNumero(monto)}</td>
                    <td class="text-end text-primary fw-bold">$${formatoNumero(comision)}</td>
                    <td class="text-end"><span class="badge bg-success bg-opacity-10 text-success border-0">${r.porcentaje || 0}%</span></td>
                </tr>`;
            });
            if (dtCliente) { dtCliente.destroy(); dtCliente = null; }
            $('#bodyCliente').html(html || '<tr><td colspan="6" class="text-center">No hay datos</td></tr>');
            if (!$('#tablaSectionCliente2').hasClass('d-none')) {
                initClienteTable();
            }
            $('#totalCliMonto').text('$' + formatoNumero(totalMonto));
            $('#totalCliComision').text('$' + formatoNumero(totalComision));
            const ctx = document.getElementById('chartCliente').getContext('2d');
            if (chartCliente) chartCliente.destroy();
            chartCliente = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datos.slice(0, 5).map(d => d.cliente),
                    datasets: [{ data: datos.slice(0, 5).map(d => parseFloat(d.comision_amiri) || 0), backgroundColor: COLORES, borderWidth: 0, hoverOffset: 6 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12 } }, tooltip: { backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 }, callbacks: { label: function(c) { return ' $' + formatoNumero(c.parsed || 0); } } } } }
            });
            renderTopClientes(datos);
        })
        .fail(function() { mostrarError('Error al cargar utilidades por cliente'); })
        .always(function() { mostrarCarga(false); });
}

function initPromotorTable() {
    dtPromotor = $('#tablaPromotor').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10, order: [[3, 'desc']],
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: [0, 3] },
            { responsivePriority: 2, targets: [4, 5] },
            { responsivePriority: 3, targets: [1, 2] }
        ]
    });
}

function cargarPorPromotor() {
    mostrarCarga(true);
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=por_promotor&${getParams()}`)
        .done(function(data) {
            const datos = data.datos || [];
            const totalUtilidad = parseFloat(data.total_utilidad) || 0;
            $('#totalUtilidadPromotor').text('$' + formatoNumero(totalUtilidad));
            let html = '';
            let totalCli = 0, totalOps = 0, totalAmiri = 0, totalProm = 0;
            datos.forEach(function(r) {
                const amiri = parseFloat(r.comision_amiri) || 0;
                const prom = parseFloat(r.comision_promotor) || 0;
                totalCli += parseInt(r.clientes) || 0;
                totalOps += parseInt(r.operaciones) || 0;
                totalAmiri += amiri;
                totalProm += prom;
                html += `<tr>
                    <td><a href="#" class="drill-link text-decoration-none fw-medium" data-promotor-id="${r.promotor_id}"><i class="ti ti-search"></i>${r.promotor}</a></td>
                    <td class="text-end">${r.clientes || 0}</td>
                    <td class="text-end">${r.operaciones || 0}</td>
                    <td class="text-end text-primary fw-bold">$${formatoNumero(amiri)}</td>
                    <td class="text-end text-success">$${formatoNumero(prom)}</td>
                    <td class="text-end"><span class="badge bg-warning bg-opacity-10 text-warning border-0">${r.porcentaje || 0}%</span></td>
                </tr>`;
            });
            if (dtPromotor) { dtPromotor.destroy(); dtPromotor = null; }
            $('#bodyPromotor').html(html || '<tr><td colspan="6" class="text-center">No hay datos</td></tr>');
            if (!$('#tablaSectionPromotor2').hasClass('d-none')) {
                initPromotorTable();
            }
            $('#totalPromClientes').text(totalCli);
            $('#totalPromOps').text(totalOps);
            $('#totalPromAmiri').text('$' + formatoNumero(totalAmiri));
            $('#totalPromPromotor').text('$' + formatoNumero(totalProm));
            var chartPromCanvas = document.getElementById('chartPromotor');
            if (chartPromCanvas) {
                var ctx2 = chartPromCanvas.getContext('2d');
                if (chartPromotor) chartPromotor.destroy();
                chartPromotor = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: datos.map(d => d.promotor),
                        datasets: [{ data: datos.map(d => parseFloat(d.comision_amiri) || 0), backgroundColor: COLORES, borderWidth: 0, hoverOffset: 6 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12 } }, tooltip: { backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 }, callbacks: { label: function(c) { return ' $' + formatoNumero(c.parsed || 0); } } } } }
                });
            }
        })
        .fail(function() { mostrarError('Error al cargar utilidades por promotor'); })
        .always(function() { mostrarCarga(false); });
}

function formatearFechaCorta(f) {
    if (!f) return '';
    var p = f.split('-');
    return p[2] + '/' + p[1];
}

function cargarGraficaComparativa() {
    const desglose = $('#selectGrafica').val();
    mostrarCarga(true);
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=grafica&desglose=${desglose}&${getParams()}`)
        .done(function(data) {
            const ctx = document.getElementById('chartComparativa').getContext('2d');
            if (chartComparativa) chartComparativa.destroy();
            $('#totalPeriodoGrafica').text('');
            if (desglose === 'utilidad_diaria') {
                var dias = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
                var comisiones = dias.map(function(d) {
                    var item = data.find(function(r) { return parseInt(r.dia.toString().split('-')[2]) === d; });
                    return item ? parseFloat(item.comisiones) || 0 : 0;
                });
                var comisionesProm = dias.map(function(d) {
                    var item = data.find(function(r) { return parseInt(r.dia.toString().split('-')[2]) === d; });
                    return item ? parseFloat(item.comisiones_promotor) || 0 : 0;
                });
                var montos = dias.map(function(d) {
                    var item = data.find(function(r) { return parseInt(r.dia.toString().split('-')[2]) === d; });
                    return item ? parseFloat(item.monto_total) || 0 : 0;
                });
                var operaciones = dias.map(function(d) {
                    var item = data.find(function(r) { return parseInt(r.dia.toString().split('-')[2]) === d; });
                    return item ? parseInt(item.operaciones) || 0 : 0;
                });
                var totalPeriodo = 0;
                data.forEach(function(d) { totalPeriodo += parseFloat(d.comisiones) || 0; });
                $('#totalPeriodoGrafica').text('Total: $' + formatoNumero(totalPeriodo));
                chartComparativa = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: dias.map(function(d) { return 'DÃ­a ' + d; }),
                        datasets: [
                            { label: 'Utilidad AMIRI', data: comisiones, backgroundColor: '#c58fb8', borderRadius: 4, borderSkipped: false },
                            { label: 'Utilidad Promotor', data: comisionesProm, backgroundColor: 'var(--nx-primary)', borderRadius: 4, borderSkipped: false }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        onClick: function(e, elements) {
                            if (elements.length > 0) {
                                var idx = elements[0].datasetIndex === 0 ? elements[0].index : elements[0].index;
                                var monto = montos[idx];
                                if (monto > 0) {
                                    var diaNum = idx + 1;
                                    var dateItem = data.find(function(r) { return parseInt(r.dia.toString().split('-')[2]) === diaNum; });
                                    if (dateItem) abrirDetalleDia(dateItem.dia);
                                }
                            }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, usePointStyle: true } },
                            tooltip: {
                                backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 },
                                callbacks: {
                                    label: function(c) { return c.dataset.label + ': $' + formatoNumero(c.parsed.y); },
                                    afterBody: function(items) {
                                        var ops = operaciones[items[0].dataIndex] || 0;
                                        var tot = montos[items[0].dataIndex] || 0;
                                        return 'Operaciones: ' + ops + '\nTotal: $' + formatoNumero(tot);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toFixed(0); } } }
                        }
                    }
                });
            } else if (desglose === 'utilidad_bruta') {
                var totalPeriodo = 0;
                data.forEach(function(d) { totalPeriodo += parseFloat(d.comisiones) || 0; });
                $('#totalPeriodoGrafica').text('Total: $' + formatoNumero(totalPeriodo) + ' (' + data.length + ' meses)');
                chartComparativa = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(function(d) { return d.mes; }),
                        datasets: [
                            { label: 'Utilidad AMIRI', data: data.map(function(d) { return parseFloat(d.comisiones) || 0; }), backgroundColor: '#c58fb8', borderRadius: 4, borderSkipped: false },
                            { label: 'Utilidad Promotor', data: data.map(function(d) { return parseFloat(d.comisiones_promotor) || 0; }), backgroundColor: 'var(--nx-primary)', borderRadius: 4, borderSkipped: false }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, usePointStyle: true } },
                            tooltip: {
                                backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 },
                                callbacks: {
                                    label: function(c) { return c.dataset.label + ': $' + formatoNumero(c.parsed.y); }
                                }
                            }
                        },
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toFixed(0); } } }
                        }
                    }
                });
            } else {
                const grupos = [...new Set(data.map(d => desglose === 'por_servicio' ? d.servicio : d.promotor))].filter(Boolean);
                const meses = [...new Set(data.map(d => d.mes))].sort();
                const key = desglose === 'por_servicio' ? 'servicio' : 'promotor';
                if (grupos.length === 0 || meses.length === 0) {
                    $('#totalPeriodoGrafica').text('Sin datos para este perÃ­odo');
                    chartComparativa = null;
                } else {
                    const datasets = grupos.map((g, i) => ({
                        label: g,
                        data: meses.map(m => { const item = data.find(d => d.mes === m && d[key] === g); return item ? parseFloat(item.total) || 0 : 0; }),
                        backgroundColor: COLORES[i % COLORES.length], borderWidth: 1
                    }));
                    chartComparativa = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: meses, datasets: datasets.map(ds => ({ ...ds, borderRadius: 4, borderSkipped: false })) },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 14, padding: 14 } }, tooltip: { backgroundColor: 'var(--nx-surface-2)', titleColor: '#fff', bodyColor: 'var(--nx-text)', cornerRadius: 8, padding: { x: 12, y: 8 } } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toFixed(0); } } } } }
                    });
                }
            }
        })
        .fail(function() { mostrarError('Error al cargar la grÃ¡fica'); })
        .always(function() { mostrarCarga(false); });
}

function abrirDetalleDia(dia) {
    var p = dia.split('-');
    var label = p[2] + '/' + p[1] + '/' + p[0];
    $('#modalDetalleDia .modal-title').html('<i class="ti ti-list-details me-2"></i>Operaciones del D\u00eda <strong>' + label + '</strong>');
    $.getJSON(`api/reportes-utilidades-v2.php?tipo=detalle_dia&dia=${dia}&${getParams()}`)
        .done(function(data) {
            var html = '', totalMonto = 0, totalAmiri = 0, totalProm = 0;
            data.forEach(function(r) {
                var monto = parseFloat(r.monto) || 0;
                var amiri = parseFloat(r.comision_amiri) || 0;
                var prom = parseFloat(r.comision_promotor) || 0;
                totalMonto += monto; totalAmiri += amiri; totalProm += prom;
                var fechaHora = r.creado_en || '-';
                html += '<tr>' +
                    '<td style="white-space:nowrap;">' + fechaHora + '</td>' +
                    '<td><span class="badge bg-primary bg-opacity-10 text-primary border-0">' + (r.servicio || 'Venta de Efectivo') + '</span></td>' +
                    '<td>' + (r.cliente || '-') + '</td>' +
                    '<td>' + (r.promotor || '-') + '</td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(monto) + '</td>' +
                    '<td class="text-end" style="color:#c58fb8;">$' + formatoNumero(amiri) + '</td>' +
                    '<td class="text-end" style="color:#d9a3cc;">$' + formatoNumero(prom) + '</td>' +
                    '</tr>';
            });
            $('#bodyDetalleDia').html(html || '<tr><td colspan="7" class="text-center">No hay operaciones</td></tr>');
            $('#totalDetalleMonto').text('$' + formatoNumero(totalMonto));
            $('#totalDetalleAmiri').text('$' + formatoNumero(totalAmiri));
            $('#totalDetallePromotor').text('$' + formatoNumero(totalProm));
            $('#modalDetalleDia').modal('show');
        })
        .fail(function() { mostrarError('Error al cargar detalle del dÃ­a'); });
}

$(document).on('click', '.drill-link', function(e) {
    e.preventDefault();
    var $this = $(this);
    var clienteId = $this.data('cliente-id');
    var promotorId = $this.data('promotor-id');
    var servicio = $this.data('servicio');
    var url = 'api/reportes-utilidades-v2.php?tipo=detalle_operaciones&' + getParams();
    var titulo = '';
    if (clienteId) { url += '&cliente_id=' + clienteId; titulo = $this.closest('tr').find('a:first').text().trim(); }
    else if (promotorId !== undefined) { url += '&promotor_id=' + promotorId; titulo = $this.text().trim(); }
    else if (servicio) { url += '&servicio=' + encodeURIComponent(servicio); titulo = servicio; }
    else return;
    mostrarCarga(true);
    $.getJSON(url)
        .done(function(data) {
            var html = '', totalMonto = 0, totalAmiri = 0, totalProm = 0;
            data.forEach(function(r) {
                var monto = parseFloat(r.monto) || 0;
                var amiri = parseFloat(r.comision_amiri) || 0;
                var prom = parseFloat(r.comision_promotor) || 0;
                totalMonto += monto; totalAmiri += amiri; totalProm += prom;
                var fecha = r.creado_en || r.fecha || '-';
                html += '<tr>' +
                    '<td style="white-space:nowrap;">' + fecha + '</td>' +
                    '<td><span class="badge bg-primary bg-opacity-10 text-primary border-0">' + (r.servicio || 'Venta de Efectivo') + '</span></td>' +
                    '<td>' + (r.cliente || '-') + '</td>' +
                    '<td>' + (r.promotor || '-') + '</td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(monto) + '</td>' +
                    '<td class="text-end" style="color:#c58fb8;">$' + formatoNumero(amiri) + '</td>' +
                    '<td class="text-end" style="color:#d9a3cc;">$' + formatoNumero(prom) + '</td>' +
                    '</tr>';
            });
            $('#bodyDetalleDia').html(html || '<tr><td colspan="7" class="text-center">No hay operaciones</td></tr>');
            $('#totalDetalleMonto').text('$' + formatoNumero(totalMonto));
            $('#totalDetalleAmiri').text('$' + formatoNumero(totalAmiri));
            $('#totalDetallePromotor').text('$' + formatoNumero(totalProm));
            $('#modalDetalleDia .modal-title').html('<i class="ti ti-list-details me-2"></i>Operaciones: <strong>' + titulo + '</strong>');
            $('#modalDetalleDia').modal('show');
        })
        .fail(function() { mostrarError('Error al cargar operaciones'); })
        .always(function() { mostrarCarga(false); });
});

function exportarReporte(formato) {
    const url = `api/exportar-reporte.php?reporte=utilidades&formato=${formato}&${getParams()}`;
    window.location.href = url;
}

function actualizarRangoDashboard() {
    var sel = $('#selectPeriodo').val();
    var label = '';
    if (sel === 'rango') {
        var fi = $('#fechaInicio').val() || '?', ff = $('#fechaFin').val() || '?';
        label = fi + ' â†’ ' + ff;
    } else if (sel === 'mes_pasado') {
        label = 'Mes Pasado';
    } else if (sel === 'anio') {
        label = 'Este AÃ±o';
    } else {
        label = 'Este Mes';
    }
    $('#rangoDashboard').text(label);
    $('#rangoFechas').html('<i class="ti ti-calendar"></i> ' + label);
    $('#rangoResultados').text(label);
}

function recargarTodo() {
    actualizarRangoDashboard();
    cargarResumen(); cargarPorServicio(); cargarPorCliente(); cargarPorPromotor(); cargarGraficaComparativa();
}

function mostrarSkeleton(show) {
    $('#skeletonResumen').toggle(Boolean(show));
    $('#contentResumen').toggle(!Boolean(show));
}

function dibujarSparkline(id, datos) {
    var canvas = document.getElementById(id);
    if (!canvas || !datos || datos.length < 2) return;
    var ctx = canvas.getContext('2d');
    var w = canvas.parentElement.clientWidth || 80;
    var h = 36;
    var dpr = window.devicePixelRatio || 1;
    canvas.width = w * dpr; canvas.height = h * dpr;
    canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
    ctx.scale(dpr, dpr);
    var vals = datos.map(function(v) { return parseFloat(v) || 0; });
    var max = Math.max.apply(null, vals) || 1;
    var min = Math.min.apply(null, vals) || 0;
    var range = (max - min) || 1;
    var stepX = w / (vals.length - 1);
    ctx.clearRect(0, 0, w, h);
    var grad = ctx.createLinearGradient(0, 0, 0, h);
    grad.addColorStop(0, 'rgba(197,143,184,0.25)');
    grad.addColorStop(1, 'rgba(197,143,184,0.02)');
    ctx.beginPath();
    vals.forEach(function(v, i) {
        var x = i * stepX, y = h - ((v - min) / range) * (h - 6) - 3;
        if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.lineTo((vals.length - 1) * stepX, h);
    ctx.lineTo(0, h);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();
    ctx.beginPath();
    vals.forEach(function(v, i) {
        var x = i * stepX, y = h - ((v - min) / range) * (h - 6) - 3;
        if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.strokeStyle = '#c58fb8';
    ctx.lineWidth = 1.5;
    ctx.stroke();
    var lastX = (vals.length - 1) * stepX;
    var lastY = h - ((vals[vals.length - 1] - min) / range) * (h - 6) - 3;
    ctx.beginPath();
    ctx.arc(lastX, lastY, 2.5, 0, Math.PI * 2);
    ctx.fillStyle = '#c58fb8';
    ctx.fill();
}

function toggleTabla(tipo) {
    $('#tablaSectionServicio2, #tablaSectionPromotor2, #tablaSectionCliente2').addClass('d-none');
    $('#tablaSection' + tipo.charAt(0).toUpperCase() + tipo.slice(1) + '2').removeClass('d-none');
    $('#tablaToggle2 .btn').removeClass('active btn-primary').addClass('btn-outline-primary');
    $('#tablaToggle2 .btn[data-table="' + tipo + '"]').addClass('active btn-primary').removeClass('btn-outline-primary');
    if (tipo === 'servicio' && dtServicio) { try { dtServicio.responsive.recalc(); } catch(e) {} }
    if (tipo === 'cliente') { if (dtCliente) { try { dtCliente.responsive.recalc(); } catch(e) {} } else { initClienteTable(); } }
    if (tipo === 'promotor') { if (dtPromotor) { try { dtPromotor.responsive.recalc(); } catch(e) {} } else { initPromotorTable(); } }
}

$('#tablaToggle2').on('click', '.btn', function() {
    toggleTabla($(this).data('table'));
});

$('#selectPeriodo').on('change', function() {
    if ($(this).val() === 'rango') { $('#fechaInicioGroup, #fechaFinGroup').show(); recargarTodo(); }
    else { $('#fechaInicioGroup, #fechaFinGroup').hide(); recargarTodo(); }
});

$('#fechaInicio, #fechaFin').on('change', function() {
    if ($('#selectPeriodo').val() === 'rango') recargarTodo();
});

$('#selectGrafica').on('change', function() { cargarGraficaComparativa(); });

$('#btnFiltrar').on('click', recargarTodo);

$(document).on('click', '#netaGastosToggle', function() {
    $('#netaGastosDetalle').slideToggle(200);
    $('#netaGastosChevron').toggleClass('ti-chevron-down ti-chevron-up');
});

$(document).on('click', '#rentNetaToggle', function() {
    $('#rentNetaDetalle').slideToggle(200);
    $('#rentNetaChevron').toggleClass('ti-chevron-down ti-chevron-up');
});

$(document).on('click', '#rentBrutaToggle', function() {
    $('#rentBrutaDetalle').slideToggle(200);
    $('#rentBrutaChevron').toggleClass('ti-chevron-down ti-chevron-up');
});

$(document).on('click', '#rentDivisasToggle', function() {
    $('#rentDivisasDetalle').slideToggle(200);
    $('#rentDivisasChevron').toggleClass('ti-chevron-down ti-chevron-up');
});

$(document).on('click', '#brutaDivisasToggle', function() {
    $('#brutaDivisasDetalle').slideToggle(200);
    $('#brutaDivisasChevron').toggleClass('ti-chevron-down ti-chevron-up');
});

$(document).ajaxError(function(event, jqxhr) {
    if (jqxhr.status === 401) {
        window.location.href = 'login.php';
    }
});

$(document).ready(function() {
    $('#fechaInicio').val(new Date().toISOString().slice(0, 8) + '01');
    $('#fechaFin').val(new Date().toISOString().slice(0, 10));
    $('body').append('<div id="loadingOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);z-index:9998;"><div class="position-absolute top-50 start-50 translate-middle"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Cargando...</span></div></div></div>');
    actualizarRangoDashboard();
    cargarResumen(); cargarPorServicio(); cargarPorCliente(); cargarPorPromotor(); cargarGraficaComparativa();
});
</script>
</body>
</html>
