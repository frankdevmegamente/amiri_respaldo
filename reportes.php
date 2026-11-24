<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8">

    <title>Reportes - Amiri</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">

    <link rel="stylesheet" href="src/assets/css/styles.min.css">

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="src/assets/css/layout-custom.css">

    <style>
                .body-wrapper {
            /* Aumentamos esto para librar completamente la altura del header azul */
            padding-top: 100px !important; 
        }
        
        .body-wrapper-inner, 
        .body-wrapper .container-fluid {
            /* Le damos un pequeño respiro visual para que el título no quede pegando con el header */
            padding-top: 15px !important;
            margin-top: 0 !important;
        }

        .card-stat {

            transition: transform 0.2s, box-shadow 0.2s;

        }

        .tooltip-icon {
            opacity: 0.5;
            transition: opacity 0.15s;
            vertical-align: middle;
            margin-left: 2px;
        }

        .tooltip-icon:hover {
            opacity: 1;
        }



        .card-stat:hover {

            transform: translateY(-2px);

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);

        }



        .stat-icon {

            width: 60px;

            height: 60px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            font-size: 1.5rem;

        }



        .nav-pills .nav-link {

            border-radius: 8px;

            padding: 10px 20px;

        }



        .nav-pills .nav-link.active {

            background: var(--nx-accent-gradient);

        }



        .chart-container {

            position: relative;

            height: 300px;

        }



        .reportes-header {
            position: relative;
            overflow: hidden;
        }

        .reportes-header .bg-pattern {
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            pointer-events: none;
        }

        .reportes-header .bg-pattern-2 {
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            pointer-events: none;
        }

        .filter-card {

            background: var(--nx-surface-2);

            border: none;

        }



        [data-bs-theme="dark"] .filter-card {

            background: var(--nx-surface-2);

        }



        .badge-usd {

            background: var(--nx-accent-gradient);

            color: white;

        }



        .badge-usdt {

            background: linear-gradient(135deg, #3ddc84, var(--nx-success));

            color: white;

        }



        .table-reportes th {

            background-color: var(--nx-surface-2);

            font-weight: 600;

        }



        [data-bs-theme="dark"] .table-reportes th {

            background-color: var(--nx-surface-2);

        }
 [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }

        /* Dark mode styles for stat cards info boxes */
        [data-bs-theme="dark"] .card-stat .bg-light {
            background-color: var(--nx-surface-2) !important;
            color: var(--nx-text) !important;
            border-color: var(--nx-border) !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-muted {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-dark {
            color: var(--nx-text) !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-danger {
            color: #f87171 !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-primary {
            color: #60a5fa !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-info {
            color: #22d3ee !important;
        }

        [data-bs-theme="dark"] .card-stat .bg-light .text-warning {
            color: #fbbf24 !important;
        }
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .page-breadcrumb,
        [data-bs-theme="dark"] .page-breadcrumb h3,
        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-body,
        [data-bs-theme="dark"] .modal-content,
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            background-color: var(--nx-surface) !important;
            border-color: var(--nx-border) !important;
            color: var(--nx-text) !important;
        }

        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(197,143,184,0.12) !important; }
[data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(61,220,132,0.12) !important; }
[data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(251,191,36,0.12) !important; }
[data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(197,143,184,0.10) !important; }
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



                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card reportes-header dashboard-header shadow-sm border-0 rounded-4">
                                <div class="bg-pattern"></div>
                                <div class="bg-pattern-2"></div>
                                <div class="card-body p-4 position-relative">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md">
                                            <div class="d-flex align-items-center gap-3 mb-1">
                                                <div class="d-flex align-items-center justify-content-center rounded-3"
                                                     style="width:48px;height:48px;background:var(--nx-primary-soft);">
                                                    <i class="ti ti-chart-bar" style="font-size:1.6rem;color:var(--nx-primary);"></i>
                                                </div>
                                                <div>
                                                    <h2 class="mb-0">Reportes y Estadísticas</h2>
                                                    <p class="text-muted mb-0 mt-1 small">Visualiza métricas y estadísticas del sistema en tiempo real</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Filtros -->

                    <div class="row mb-4">

                        <div class="col-12">

                            <div class="card filter-card shadow-sm" style="overflow:visible !important; position:relative; z-index:10;">

                                <div class="card-body py-3">

                                    <div class="row align-items-end g-3">

                                        <div class="col-md-2">

                                            <label class="form-label fw-semibold">Período</label>

                                            <select class="form-select" id="selectPeriodo">

                                                <option value="dia">Hoy</option>

                                                <option value="semana">Esta Semana</option>

                                                <option value="mes" selected>Este Mes</option>

                                                <option value="anio">Este Año</option>

                                                <option value="rango">Rango Personalizado</option>

                                            </select>

                                        </div>

                                        <div class="col-md-2" id="fechaInicioGroup" style="display:none;">

                                            <label class="form-label fw-semibold">Desde</label>

                                            <input type="date" class="form-control" id="fechaInicio">

                                        </div>

                                        <div class="col-md-2" id="fechaFinGroup" style="display:none;">

                                            <label class="form-label fw-semibold">Hasta</label>

                                            <input type="date" class="form-control" id="fechaFin">

                                        </div>

                                        <div class="col-md-2">

                                            <button class="btn btn-primary w-100" id="btnFiltrar">

                                                <i class="ti ti-filter"></i> Aplicar

                                            </button>

                                        </div>

                                        <div class="col-md-4 text-end">

                                            <div class="btn-group me-2">

                                                <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" data-bs-container="body">

                                                    <i class="ti ti-download"></i> Exportar

                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">

                                                    <li><h6 class="dropdown-header">Resumen General</h6></li>

                                                    <li><a class="dropdown-item" href="#" onclick="exportar('resumen', 'excel')"><i class="ti ti-file-spreadsheet text-success"></i> Excel (.xlsx)</a></li>

                                                    <li><a class="dropdown-item" href="#" onclick="exportar('resumen', 'csv')"><i class="ti ti-file-text text-info"></i> CSV</a></li>

                                                    <li><a class="dropdown-item" href="#" onclick="exportar('resumen', 'pdf')"><i class="ti ti-file-text text-danger"></i> PDF</a></li>

                                                </ul>

                                            </div>

                                            <span class="text-muted" id="rangoFechas">Cargando...</span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- Tarjetas de Resumen -->

                    <div class="row mb-4" id="tarjetasResumen">
                        
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card card-stat metric-card border-0 shadow-sm h-100" id="cardNeta"><div class="metric-accent bg-success" id="accentNeta"></div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="metric-icon bg-success bg-opacity-10 text-success me-3" id="iconNetaBg">
                                            <i class="ti ti-trending-up" id="iconNeta"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1 small">
                                                Ganancia Neta
                                                <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="resultado_operativo_real" style="cursor:help;font-size:14px;"></i>
                                            </p>
                                            <h4 class="mb-0 fw-bold" id="statGananciaTotal">$0.00</h4>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-2 small mt-2 border">
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span>(+) Bruta:</span> <span id="lblFormulaBruta" class="fw-bold text-dark">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-danger mb-1" id="gastosToggle" style="cursor:pointer;">
                                            <span><i class="ti ti-chevron-down" id="gastosChevron"></i> (-) Gastos:</span>
                                            <span id="lblTotalGastos" class="fw-bold">$0.00</span>
                                        </div>
                                        <div id="gastosDetalle" style="display:none;">
                                            <div class="d-flex justify-content-between text-danger ps-3 mb-1">
                                                <span>Gastos Fijos:</span> <span id="lblGastosFijos">$0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between text-danger ps-3 mb-1">
                                                <span>Gastos Variables:</span> <span id="lblGastosVariables">$0.00</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 fw-bold border-top pt-1">
                                            <span>(=) NETO:</span> <span id="lblFormulaNeta">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Capital Amiri -->
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card card-stat metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-primary"></div><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="metric-icon bg-primary bg-opacity-10 text-primary me-3"><i class="ti ti-crown"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1 small">Capital Amiri</p>
                                            <h4 class="mb-0 fw-bold" id="capitalAmiriTotal">$0.00</h4>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-2 small mt-2 border">
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span>Último Corte:</span> <span id="capitalAmiriCorte" class="fw-bold text-dark">-</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span>Retiros:</span> <span id="capitalAmiriRetiros" class="fw-bold text-dark">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between fw-bold border-top pt-1">
                                            <span>Capital Actual:</span> <span id="capitalAmiriActual" class="text-primary fw-bold">$0.00</span>
                                        </div>
                                    </div>
                                    <a href="capital-amiri.php" class="stretched-link"></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card card-stat metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-info"></div><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="metric-icon bg-info bg-opacity-10 text-info me-3"><i class="ti ti-currency-dollar"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1 small">
                                                Ganancia Divisas
                                                <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="ganancia_divisas_total" style="cursor:help;font-size:14px;"></i>
                                            </p>
                                            <h4 class="mb-0 fw-bold" id="statGananciaDivisas">$0.00</h4>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-2 small mt-2 border">
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span>(+) Utilidad USDT: <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="ganancia_usdt" style="cursor:help;font-size:12px;"></i></span> <span id="lblUtilidadUSDT" class="fw-bold text-dark">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted border-bottom pb-1">
                                            <span>(+) Utilidad USD: <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="ganancia_usd" style="cursor:help;font-size:12px;"></i></span> <span id="lblUtilidadUSD" class="fw-bold text-dark">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 fw-bold text-info">
                                            <span>(=) TOTAL CRIPTO:</span> <span id="lblUtilidadTotal">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card card-stat metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-warning"></div><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="metric-icon bg-warning bg-opacity-10 text-warning me-3"><i class="ti ti-cash"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-1 small">
                                                Ventas de Efectivo
                                                <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="ventas_efectivo_total" style="cursor:help;font-size:14px;"></i>
                                            </p>
                                            <h4 class="mb-0 fw-bold" id="statVentasEfectivo">$0.00</h4>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-2 small mt-2 border">
                                        <div class="d-flex justify-content-between text-muted mb-1">
                                            <span><i class="ti ti-receipt text-warning"></i> Operaciones: <i class="ti ti-info-circle text-muted tooltip-icon" data-explica="ventas_efectivo_cantidad" style="cursor:help;font-size:12px;"></i></span> <span id="lblVentasOperaciones" class="fw-bold text-dark">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted border-bottom pb-1">
                                            <span><i class="ti ti-chart-dots text-warning"></i> Ticket Promedio:</span> <span id="lblVentasPromedio" class="fw-bold text-dark">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1 fw-bold text-warning">
                                            <span><i class="ti ti-percentage"></i> Margen Promedio:</span> <span id="lblVentasMargen">0.00%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    



                    <!-- Tabs de Reportes -->

                    <div class="row">

                        <div class="col-12">

                            <div class="card shadow-sm">

                                <div class="card-header bg-transparent">

                                    <ul class="nav nav-pills" id="reportesTabs" role="tablist">

                                        <li class="nav-item" role="presentation">

                                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabGraficas" type="button">

                                                <i class="ti ti-chart-line me-1"></i> Gráficas

                                            </button>

                                        </li>

                                        <li class="nav-item" role="presentation">

                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabComisiones" type="button">

                                                <i class="ti ti-percentage me-1"></i> Comisiones

                                            </button>

                                        </li>

                                        <li class="nav-item" role="presentation">

                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabDivisas" type="button">

                                                <i class="ti ti-currency-dollar me-1"></i> Divisas

                                            </button>

                                        </li>

                                        <li class="nav-item" role="presentation">

                                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabVentas" type="button">

                                                <i class="ti ti-cash me-1"></i> Ventas Efectivo

                                            </button>

                                        </li>

                                    </ul>

                                </div>

                                <div class="card-body p-4">

                                    <div class="tab-content" id="reportesTabsContent">



                                        <!-- Tab Gráficas -->

                                        <div class="tab-pane fade show active" id="tabGraficas" role="tabpanel">

                                            <div class="row">

                                                <div class="col-md-6 mb-4">

                                                    <h6 class="fw-semibold mb-3">

                                                        <i class="ti ti-chart-area text-primary"></i> Comisiones Diarias

                                                    </h6>

                                                    <div class="chart-container">

                                                        <canvas id="chartComisionesDiarias"></canvas>

                                                    </div>

                                                </div>

                                                <div class="col-md-6 mb-4">

                                                    <h6 class="fw-semibold mb-3">

                                                        <i class="ti ti-chart-pie text-success"></i> Comisiones por Promotor

                                                    </h6>

                                                    <div class="chart-container">

                                                        <canvas id="chartComisionesPromotor"></canvas>

                                                    </div>

                                                </div>

                                                <div class="col-md-6 mb-4">

                                                    <h6 class="fw-semibold mb-3">

                                                        <i class="ti ti-chart-bar text-warning"></i> Ventas de Efectivo Diarias

                                                    </h6>

                                                    <div class="chart-container">

                                                        <canvas id="chartVentasDiarias"></canvas>

                                                    </div>

                                                </div>

                                                <div class="col-md-6 mb-4">

                                                    <h6 class="fw-semibold mb-3">

                                                        <i class="ti ti-chart-dots text-info"></i> Operaciones de Divisas

                                                    </h6>

                                                    <div class="chart-container">

                                                        <canvas id="chartDivisasDiarias"></canvas>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>



                                        <!-- Tab Comisiones -->

                                        <div class="tab-pane fade" id="tabComisiones" role="tabpanel">

                                            <div class="row mb-3">

                                                <div class="col-md-4">

                                                    <select class="form-select" id="selectTipoComision">

                                                        <option value="todas">Todas las Comisiones</option>

                                                        <option value="amiri">Solo AMIRI</option>

                                                        <option value="promotores">Por Promotor</option>

                                                    </select>

                                                </div>

                                                <div class="col-md-4" id="selectPromotorGroup" style="display:none;">

                                                    <select class="form-select" id="selectPromotor">

                                                        <option value="">Todos los promotores</option>

                                                    </select>

                                                </div>

                                                <div class="col-md-4 text-end">

                                                    <div class="btn-group">

                                                        <button class="btn btn-sm btn-outline-success" onclick="exportar('comisiones', 'excel')" title="Excel">

                                                            <i class="ti ti-file-spreadsheet"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-info" onclick="exportar('comisiones', 'csv')" title="CSV">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-danger" onclick="exportar('comisiones', 'pdf')" title="PDF">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="table-responsive">

                                                <table class="table table-hover table-reportes" id="tablaComisiones">

                                                    <thead>

                                                        <tr>

                                                            <th>Fecha</th>

                                                            <th>Operaciones</th>

                                                            <th class="text-end">Comisión AMIRI</th>

                                                            <th class="text-end">Comisión Promotor</th>

                                                            <th class="text-end">Total</th>

                                                        </tr>

                                                    </thead>

                                                    <tbody id="bodyComisiones">

                                                    </tbody>

                                                    <tfoot class="table-light fw-bold">

                                                        <tr>

                                                            <td colspan="2">TOTALES</td>

                                                            <td class="text-end" id="totalComisionAmiri">$0.00</td>

                                                            <td class="text-end" id="totalComisionPromotor">$0.00</td>

                                                            <td class="text-end" id="totalComisiones">$0.00</td>

                                                        </tr>

                                                    </tfoot>

                                                </table>

                                            </div>

                                        </div>



                                        <!-- Tab Divisas -->

                                        <div class="tab-pane fade" id="tabDivisas" role="tabpanel">

                                            <div class="row mb-3">

                                                <div class="col-md-3">

                                                    <select class="form-select" id="selectMonedaDivisa">

                                                        <option value="USD">USD (Dólares)</option>

                                                        <option value="USDT">USDT (Tether)</option>

                                                    </select>

                                                </div>

                                                <div class="col-md-9 text-end">

                                                    <div class="btn-group">

                                                        <button class="btn btn-sm btn-outline-success" onclick="exportar('divisas', 'excel')" title="Excel">

                                                            <i class="ti ti-file-spreadsheet"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-info" onclick="exportar('divisas', 'csv')" title="CSV">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-danger" onclick="exportar('divisas', 'pdf')" title="PDF">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>



                                            <!-- Resumen de Divisas -->

                                            <div class="row mb-4">

                                                <div class="col-md-3">

                                                    <div class="card bg-primary bg-opacity-10 border-0">

                                                        <div class="card-body text-center">

                                                            <h6 class="text-muted mb-1">Total Comprado</h6>

                                                            <h4 class="text-primary mb-0" id="divisaComprado">0</h4>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-md-3">

                                                    <div class="card bg-success bg-opacity-10 border-0">

                                                        <div class="card-body text-center">

                                                            <h6 class="text-muted mb-1">Total Vendido</h6>

                                                            <h4 class="text-success mb-0" id="divisaVendido">0</h4>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-md-3">

                                                    <div class="card bg-warning bg-opacity-10 border-0">

                                                        <div class="card-body text-center">

                                                            <h6 class="text-muted mb-1">MXN Gastado</h6>

                                                            <h4 class="text-warning mb-0" id="divisaMxnGastado">$0</h4>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-md-3">

                                                    <div class="card bg-info bg-opacity-10 border-0">

                                                        <div class="card-body text-center">

                                                            <h6 class="text-muted mb-1">Ganancia MXN</h6>

                                                            <h4 class="text-info mb-0" id="divisaGanancia">$0</h4>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>



                                            <div class="table-responsive">

                                                <table class="table table-hover table-reportes" id="tablaDivisas">

                                                    <thead>

                                                        <tr>

                                                            <th>Fecha</th>

                                                            <th>Servicio</th>

                                                            <th class="text-end">Cantidad</th>

                                                            <th class="text-end">TC</th>

                                                            <th class="text-end">Monto MXN</th>

                                                            <th>Cliente/Proveedor</th>

                                                        </tr>

                                                    </thead>

                                                    <tbody id="bodyDivisas">

                                                    </tbody>

                                                </table>

                                            </div>

                                        </div>



                                        <!-- Tab Ventas Efectivo -->

                                        <div class="tab-pane fade" id="tabVentas" role="tabpanel">

                                            <div class="row mb-3">

                                                <div class="col-md-12 text-end">

                                                    <div class="btn-group">

                                                        <button class="btn btn-sm btn-outline-success" onclick="exportar('ventas_efectivo', 'excel')" title="Excel">

                                                            <i class="ti ti-file-spreadsheet"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-info" onclick="exportar('ventas_efectivo', 'csv')" title="CSV">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                        <button class="btn btn-sm btn-outline-danger" onclick="exportar('ventas_efectivo', 'pdf')" title="PDF">

                                                            <i class="ti ti-file-text"></i>

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="table-responsive">

                                                <table class="table table-hover table-reportes" id="tablaVentas">

                                                    <thead>

                                                        <tr>

                                                            <th>Fecha</th>

                                                            <th>Cliente</th>

                                                            <th>Promotor</th>

                                                            <th class="text-end">Monto</th>

                                                            <th class="text-end">Com. AMIRI</th>

                                                            <th class="text-end">Com. Promotor</th>

                                                            <th>Estado</th>

                                                            <th>Moneda</th>

                                                        </tr>

                                                    </thead>

                                                    <tbody id="bodyVentas">

                                                    </tbody>

                                                </table>

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


<script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>if (window.nxChartTheme) window.nxChartTheme();</script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script src="src/assets/js/pantalla-completa.js"></script>



    <script>

        // Variables globales

        let chartComisionesDiarias, chartComisionesPromotor, chartVentasDiarias, chartDivisasDiarias;



        // Formatear número

        function formatoNumero(num) {

            return parseFloat(num || 0).toLocaleString('es-MX', {

                minimumFractionDigits: 2,

                maximumFractionDigits: 2

            });

        }



        // Obtener parámetros de filtro

        function getParams() {

            const periodo = $('#selectPeriodo').val();

            let params = `periodo=${periodo}`;



            if (periodo === 'rango') {

                params += `&fecha_inicio=${$('#fechaInicio').val()}&fecha_fin=${$('#fechaFin').val()}`;

            }



            return params;

        }



        // Función para exportar reportes

        function exportar(reporte, formato) {

            let url = `api/exportar-reporte.php?reporte=${reporte}&formato=${formato}&${getParams()}`;

            

            // Agregar moneda si es reporte de divisas

            if (reporte === 'divisas') {

                url += `&moneda=${$('#selectMonedaDivisa').val()}`;

            }

            

            // Abrir en nueva ventana para descargar

            window.location.href = url;

        }



        // Mostrar/ocultar campos de rango

        $('#selectPeriodo').on('change', function() {

            if ($(this).val() === 'rango') {

                $('#fechaInicioGroup, #fechaFinGroup').show();

            } else {

                $('#fechaInicioGroup, #fechaFinGroup').hide();

            }

        });



        // Cargar resumen con desglose detallado
        function _renderResumen(data) {
            const neta = parseFloat(data.ganancia_neta) || 0;
            const gastosFijos = parseFloat(data.gastos_fijos) || 0;
            const gastosVariables = parseFloat(data.gastos_variables) || 0;
            const totalGastos = gastosFijos + gastosVariables;
            $('#statGananciaTotal').text('$' + formatoNumero(neta));
            $('#lblFormulaBruta').text('$' + formatoNumero(data.ganancia_bruta));
            $('#lblTotalGastos').text('-$' + formatoNumero(totalGastos));
            $('#lblGastosFijos').text('-$' + formatoNumero(gastosFijos));
            $('#lblGastosVariables').text('-$' + formatoNumero(gastosVariables));
            $('#lblFormulaNeta').text('$' + formatoNumero(neta));

            if(neta < 0) {
                $('#statGananciaTotal, #lblFormulaNeta').removeClass('text-success').addClass('text-danger');
                $('#iconNetaBg').removeClass('bg-success text-success').addClass('bg-danger text-danger');
                $('#iconNeta').removeClass('ti-trending-up').addClass('ti-trending-down');
            } else {
                $('#statGananciaTotal, #lblFormulaNeta').removeClass('text-danger').addClass('text-success');
                $('#iconNetaBg').removeClass('bg-danger text-danger').addClass('bg-success text-success');
                $('#iconNeta').removeClass('ti-trending-down').addClass('ti-trending-up');
            }

            let totalRetencion = parseFloat(data.comisiones_amiri) + parseFloat(data.comisiones_promotores);
            let usd = parseFloat(data.ganancia_usd) || 0;
            let usdt = parseFloat(data.ganancia_usdt) || 0;
            
            $('#statGananciaDivisas, #lblUtilidadTotal').text('$' + formatoNumero(data.ganancia_divisas_total));
            $('#lblUtilidadUSDT').text('$' + formatoNumero(usdt));
            $('#lblUtilidadUSD').text('$' + formatoNumero(usd));

            let ventasTotales = parseFloat(data.ventas_efectivo_total) || 0;
            let operaciones = parseInt(data.ventas_efectivo_cantidad) || 0;
            let ticketPromedio = operaciones > 0 ? (ventasTotales / operaciones) : 0;
            let margen = ventasTotales > 0 ? ((totalRetencion / ventasTotales) * 100) : 0;

            $('#statVentasEfectivo').text('$' + formatoNumero(ventasTotales));
            $('#lblVentasOperaciones').text(operaciones);
            $('#lblVentasPromedio').text('$' + formatoNumero(ticketPromedio));
            $('#lblVentasMargen').text(margen.toFixed(2) + '%');

            const periodoTexto = {
                'dia': 'Hoy', 'semana': 'Esta Semana', 'mes': 'Este Mes',
                'anio': 'Este Año', 'rango': 'Rango Personalizado'
            };
            $('#rangoFechas').text(periodoTexto[$('#selectPeriodo').val()]);

            if (data._explicacion) {
                $('.tooltip-icon').each(function() {
                    const key = $(this).data('explica');
                    const texto = data._explicacion[key];
                    if (texto) $(this).attr('title', texto);
                });
                $('.tooltip-icon').tooltip('dispose');
                $('[title]').tooltip({ placement: 'top', trigger: 'hover' });
            }
        }

        function cargarResumen(data) {
            if (data) { _renderResumen(data); return; }
            $.getJSON(`api/reportes.php?tipo=resumen&${getParams()}`, _renderResumen);
        }

        function cargarCapitalAmiri(data) {
            if (data) {
                $('#capitalAmiriTotal').text('$' + formatoNumero(data.utilidades_acumuladas));
                $('#capitalAmiriCorte').text(data.ultimo_corte || 'Sin cortes');
                $('#capitalAmiriRetiros').text('$' + formatoNumero(data.total_retiros));
                $('#capitalAmiriActual').text('$' + formatoNumero(data.capital_actual));
                return;
            }
            $.getJSON('api/capital.php?tipo=resumen', function(cap) {
                $('#capitalAmiriTotal').text('$' + formatoNumero(cap.utilidades_acumuladas));
                $('#capitalAmiriCorte').text(cap.ultimo_corte || 'Sin cortes');
                $('#capitalAmiriRetiros').text('$' + formatoNumero(cap.total_retiros));
                $('#capitalAmiriActual').text('$' + formatoNumero(cap.capital_actual));
            }).fail(function() {
                $('#capitalAmiriTotal').text('$0.00');
            });
        }



        // Cargar gráfica de comisiones diarias

        function _renderGraficaComisionesDiarias(data) {
            const ctx = document.getElementById('chartComisionesDiarias').getContext('2d');
            if (chartComisionesDiarias) chartComisionesDiarias.destroy();
            chartComisionesDiarias = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.fecha),
                    datasets: [{
                        label: 'AMIRI',
                        data: data.map(d => parseFloat(d.amiri || 0)),
                        borderColor: 'var(--nx-primary)',
                        backgroundColor: 'rgba(197,143,184,0.12)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Promotores',
                        data: data.map(d => parseFloat(d.promotores || 0)),
                        borderColor: 'var(--nx-success)',
                        backgroundColor: 'rgba(61, 220, 132, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        function cargarGraficaComisionesDiarias(data) {
            if (data) { _renderGraficaComisionesDiarias(data); return; }
            $.getJSON(`api/reportes.php?tipo=graficas&grafica=comisiones_diarias&${getParams()}`, _renderGraficaComisionesDiarias);
        }



        // Cargar gráfica de comisiones por promotor

        function _renderGraficaComisionesPromotor(data) {
            const ctx = document.getElementById('chartComisionesPromotor').getContext('2d');
            if (chartComisionesPromotor) chartComisionesPromotor.destroy();
            const colores = ['var(--nx-primary)', 'var(--nx-success)', 'var(--nx-warning)', 'var(--nx-danger)', '#539BFF', '#d9a3cc', '#FF6692', '#36A2EB', '#FFCE56', '#4BC0C0'];
            chartComisionesPromotor = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.promotor),
                    datasets: [{ data: data.map(d => parseFloat(d.comision || 0)), backgroundColor: colores.slice(0, data.length), borderWidth: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
            });
        }

        function cargarGraficaComisionesPromotor(data) {
            if (data) { _renderGraficaComisionesPromotor(data); return; }
            $.getJSON(`api/reportes.php?tipo=graficas&grafica=comisiones_por_promotor&${getParams()}`, _renderGraficaComisionesPromotor);
        }



        // Cargar gráfica de ventas diarias

        function _renderGraficaVentasDiarias(data) {
            const ctx = document.getElementById('chartVentasDiarias').getContext('2d');
            if (chartVentasDiarias) chartVentasDiarias.destroy();
            chartVentasDiarias = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.fecha),
                    datasets: [{
                        label: 'Monto Total',
                        data: data.map(d => parseFloat(d.total || 0)),
                        backgroundColor: 'rgba(255, 174, 31, 0.7)',
                        borderColor: 'var(--nx-warning)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        function cargarGraficaVentasDiarias(data) {
            if (data) { _renderGraficaVentasDiarias(data); return; }
            $.getJSON(`api/reportes.php?tipo=graficas&grafica=ventas_diarias&${getParams()}`, _renderGraficaVentasDiarias);
        }



        // Cargar gráfica de divisas

        function _renderGraficaDivisas(data) {
            const ctx = document.getElementById('chartDivisasDiarias').getContext('2d');
            if (chartDivisasDiarias) chartDivisasDiarias.destroy();
            const fechas = [...new Set(data.map(d => d.fecha))];
            const usdCompras = fechas.map(f => { const item = data.find(d => d.fecha === f && d.moneda === 'USD'); return item ? parseFloat(item.compras || 0) : 0; });
            const usdVentas = fechas.map(f => { const item = data.find(d => d.fecha === f && d.moneda === 'USD'); return item ? parseFloat(item.ventas || 0) : 0; });
            chartDivisasDiarias = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Compras USD',
                        data: usdCompras,
                        backgroundColor: 'rgba(197,143,184,0.7)'
                    }, {
                        label: 'Ventas USD',
                        data: usdVentas,
                        backgroundColor: 'rgba(61, 220, 132, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        function cargarGraficaDivisas(data) {
            if (data) { _renderGraficaDivisas(data); return; }
            $.getJSON(`api/reportes.php?tipo=graficas&grafica=divisas_diarias&${getParams()}`, _renderGraficaDivisas);
        }



        // Cargar tabla de comisiones

        function cargarTablaComisiones() {

            const tipo = $('#selectTipoComision').val();

            const promotorId = $('#selectPromotor').val();



            let url = `api/reportes.php?tipo=comisiones&subtipo=${tipo}&${getParams()}`;

            if (promotorId) url += `&promotor_id=${promotorId}`;



            $.getJSON(url, function(data) {

                let html = '';

                let totalAmiri = 0,

                    totalPromotor = 0;



                if (tipo === 'promotores' && !promotorId) {

                    // Vista por promotor

                    $('#tablaComisiones thead tr').html(`

                        <th>Promotor</th>

                        <th class="text-end">Operaciones</th>

                        <th class="text-end">Total Ventas</th>

                        <th class="text-end">Comisión</th>

                    `);



                    data.forEach(row => {

                        const comision = parseFloat(row.comision || 0);

                        totalPromotor += comision;

                        html += `<tr>

                            <td><a href="detalle-promotor.php?id=${row.promotor_id}">${row.promotor}</a></td>

                            <td class="text-end">${row.operaciones}</td>

                            <td class="text-end">$${formatoNumero(row.total_ventas)}</td>

                            <td class="text-end text-success fw-bold">$${formatoNumero(comision)}</td>

                        </tr>`;

                    });



                    $('#tablaComisiones tfoot tr').html(`

                        <td colspan="3" class="fw-bold">TOTAL</td>

                        <td class="text-end fw-bold">$${formatoNumero(totalPromotor)}</td>

                    `);

                } else {

                    // Vista por fecha

                    $('#tablaComisiones thead tr').html(`

                        <th>Fecha</th>

                        <th>Operaciones</th>

                        <th class="text-end">Comisión AMIRI</th>

                        <th class="text-end">Comisión Promotor</th>

                        <th class="text-end">Total</th>

                    `);



                    data.forEach(row => {

                        const amiri = parseFloat(row.comision_amiri || row.comision || 0);

                        const promotor = parseFloat(row.comision_promotor || 0);

                        totalAmiri += amiri;

                        totalPromotor += promotor;



                        html += `<tr>

                            <td>${formatearFechaHora(row.creado_en)}</td>

                            <td>${row.operaciones || '-'}</td>

                            <td class="text-end">$${formatoNumero(amiri)}</td>

                            <td class="text-end">$${formatoNumero(promotor)}</td>

                            <td class="text-end fw-bold">$${formatoNumero(amiri + promotor)}</td>

                        </tr>`;

                    });



                    $('#tablaComisiones tfoot tr').html(`

                        <td colspan="2" class="fw-bold">TOTALES</td>

                        <td class="text-end fw-bold">$${formatoNumero(totalAmiri)}</td>

                        <td class="text-end fw-bold">$${formatoNumero(totalPromotor)}</td>

                        <td class="text-end fw-bold">$${formatoNumero(totalAmiri + totalPromotor)}</td>

                    `);

                }



                $('#bodyComisiones').html(html || '<tr><td colspan="5" class="text-center">No hay datos</td></tr>');

            });

        }



        // Cargar lista de promotores

        function _renderListaPromotores(data) {
            let html = '<option value="">Todos los promotores</option>';
            data.forEach(p => { html += `<option value="${p.id}">${p.nombre}</option>`; });
            $('#selectPromotor').html(html);
        }

        function cargarListaPromotores(data) {
            if (data) { _renderListaPromotores(data); return; }
            $.getJSON('api/reportes.php?tipo=lista_promotores', _renderListaPromotores);
        }



        // Mostrar/ocultar selector de promotor

        $('#selectTipoComision').on('change', function() {

            if ($(this).val() === 'promotores') {

                $('#selectPromotorGroup').show();

            } else {

                $('#selectPromotorGroup').hide();

            }

            cargarTablaComisiones();

        });



        $('#selectPromotor').on('change', function() {

            cargarTablaComisiones();

        });



        // Cargar tabla de divisas

        function _renderTablaDivisas(data) {
            const moneda = $('#selectMonedaDivisa').val();
            const r = data.resumen;
            $('#divisaComprado').text(formatoNumero(r.total_comprado) + ' ' + moneda);
            $('#divisaVendido').text(formatoNumero(r.total_vendido) + ' ' + moneda);
            $('#divisaMxnGastado').text('$' + formatoNumero(r.mxn_gastado));
            $('#divisaGanancia').text('$' + formatoNumero(r.ganancia_mxn));
            let html = '';
            data.operaciones.forEach(op => {
                const esCompra = op.servicio.includes('Compra');
                const badgeClass = esCompra ? 'bg-primary' : 'bg-success';
                const persona = op.proveedor !== '-' ? op.proveedor : op.cliente;
                html += `<tr><td>${formatearFechaHora(op.creado_en)}</td><td><span class="badge ${badgeClass}">${op.servicio}</span></td><td class="text-end">${formatoNumero(op.cantidad)} ${moneda}</td><td class="text-end">$${formatoNumero(op.tipo_cambio)}</td><td class="text-end">$${formatoNumero(op.monto_mxn)}</td><td>${persona}</td></tr>`;
            });
            $('#bodyDivisas').html(html || '<tr><td colspan="6" class="text-center">No hay datos</td></tr>');
        }

        function cargarTablaDivisas(data) {
            if (data) { _renderTablaDivisas(data); return; }
            const moneda = $('#selectMonedaDivisa').val();
            $.getJSON(`api/reportes.php?tipo=divisas&moneda=${moneda}&${getParams()}`, _renderTablaDivisas);
        }



        $('#selectMonedaDivisa').on('change', function() {

            cargarTablaDivisas();

        });



        // Cargar tabla de ventas de efectivo

        function cargarTablaVentas() {

            $.getJSON(`api/reportes.php?tipo=ventas_efectivo&${getParams()}`, function(data) {

                let html = '';



                data.forEach(v => {

                    const estadoBadge = {

                        'pendiente': 'bg-warning',

                        'parcial': 'bg-info',

                        'pagado': 'bg-success'

                    };



                    html += `<tr>

                        <td>${formatearFechaHora(v.creado_en)}</td>

                        <td><a href="detalle-cliente.php?id=${v.id}">${v.cliente}</a></td>

                        <td>${v.promotor || '-'}</td>

                        <td class="text-end">$${formatoNumero(v.monto_total)}</td>

                        <td class="text-end text-primary">$${formatoNumero(v.comision_amiri)}</td>

                        <td class="text-end text-success">$${formatoNumero(v.comision_promotor)}</td>

                        <td><span class="badge ${estadoBadge[v.estado]}">${v.estado}</span></td>

                        <td><span class="badge ${v.moneda === 'MXN' ? 'bg-secondary' : (v.moneda === 'USD' ? 'badge-usd' : 'badge-usdt')}">${v.moneda}</span></td>

                    </tr>`;

                });



                $('#bodyVentas').html(html || '<tr><td colspan="8" class="text-center">No hay datos</td></tr>');

            });

        }



        // Cargar todo

        function cargarTodo() {
            $.getJSON(`api/reportes.php?tipo=full_dashboard&${getParams()}`, function(d) {
                cargarResumen(d.resumen);
                cargarGraficaComisionesDiarias(d.comisiones_diarias);
                cargarGraficaComisionesPromotor(d.comisiones_por_promotor);
                cargarGraficaVentasDiarias(d.ventas_diarias);
                cargarGraficaDivisas(d.divisas_diarias);
                cargarTablaComisiones();
                _renderTablaDivisas({operaciones: d.divisas_operaciones, resumen: d.divisas_resumen});
                cargarTablaVentas();
                cargarCapitalAmiri(d.capital_amiri);
                cargarListaPromotores(d.lista_promotores);
            });
        }



        // Toggle detalle de gastos

        $(document).on('click', '#gastosToggle', function() {
            $('#gastosDetalle').slideToggle(200);
            $('#gastosChevron').toggleClass('ti-chevron-down ti-chevron-up');
        });

        // Evento del botón filtrar

        $('#btnFiltrar').on('click', function() {

            cargarTodo();

        });



        // Inicializar

        $(document).ready(function() {

            // Fechas por defecto

            $('#fechaInicio').val(new Date().toISOString().slice(0, 8) + '01');

            $('#fechaFin').val(new Date().toISOString().slice(0, 10));



            cargarListaPromotores();

            cargarTodo();

        });

    </script>

</body>



</html>
