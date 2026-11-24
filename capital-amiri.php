<?php
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Capital Amiri - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.css" />
<style>
    [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
    [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }
    .body-wrapper { padding-top: 100px !important; }
    .body-wrapper-inner,
    .body-wrapper .container-fluid {
        padding-top: 15px !important;
        margin-top: 0 !important;
    }
    .page-header .card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--nx-primary), var(--nx-success));
        z-index: 1;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .capital-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--nx-text-muted);
    }
    [data-bs-theme="dark"] .capital-section-title { color: var(--nx-text-muted); }
    .table-reportes th { position: sticky; top: 0; background: var(--nx-surface); z-index: 2; }
    [data-bs-theme="dark"] .table-reportes th,
    .dark-mode .table-reportes th { background: var(--nx-surface-2); }
    [data-bs-theme="dark"] .bg-light { background-color: var(--nx-surface-2) !important; }
</style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php $pagina_actual = 'capital-amiri.php'; include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                <!-- Header -->
                <div class="page-header">
                    <div class="card shadow-sm border-0 rounded-4" style="position:relative;overflow:hidden;">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4" style="position:relative;z-index:1;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                        <i class="ti ti-crown fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-semibold mb-0">Capital Amiri</h5>
                                        <p class="text-muted mb-0" style="font-size:0.82rem;">Cortes mensuales, retiros y evolución del capital</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ti ti-calendar text-muted" style="font-size:0.85rem;"></i>
                                        <select class="form-select form-select-sm" id="mesFilter" style="width:auto;min-width:130px;">
                                            <option value="">— Mes actual —</option>
                                        </select>
                                    </div>
                                    <span class="text-muted" style="opacity:0.3;">|</span>
                                    <button class="btn btn-sm btn-primary" id="btnCerrarMes" title="Cerrar mes actual">
                                        <i class="ti ti-lock"></i> Cerrar Mes
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" id="btnRetiroCapital" title="Registrar retiro">
                                        <i class="ti ti-cash-off"></i> Retiro
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="exportarReporte()" title="Exportar">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Summary Metric Cards -->
                <div class="row g-3 mb-4" id="summaryCards">
                    <div class="col-xl col-md-6">
                        <div class="card card-stat metric-card border-0 shadow-sm h-100 animating">
                            <div class="metric-accent bg-primary"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Capital Actual <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Saldo total de Capital Amiri incluye utilidades acumuladas y aportaciones" style="cursor:help;font-size:0.75rem;"></i></span>
                                    <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="ti ti-building-bank"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="capCapitalActual">$0</div>
                                <div class="metric-footer">
                                    <i class="ti ti-coin"></i> <span>Saldo disponible</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6">
                        <div class="card card-stat metric-card border-0 shadow-sm h-100 animating">
                            <div class="metric-accent bg-success"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Último Corte <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Último mes con cierre contable registrado" style="cursor:help;font-size:0.75rem;"></i></span>
                                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                                        <i class="ti ti-calendar-stats"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="capUltimoCorte" style="font-size:1.2rem;">-</div>
                                <div class="metric-footer">
                                    <i class="ti ti-history"></i> <span>Mes cerrado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6">
                        <div class="card card-stat metric-card border-0 shadow-sm h-100 animating">
                            <div class="metric-accent bg-warning"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Utilidad del Mes <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Comisiones AMIRI + Ganancia Divisas - Gastos del período. Se acumula al cerrar el mes." style="cursor:help;font-size:0.75rem;"></i></span>
                                    <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="ti ti-trending-up"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="capUtilidadMes">$0</div>
                                <div class="metric-footer">
                                    <i class="ti ti-cash"></i> <span>Período actual</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6">
                        <div class="card card-stat metric-card border-0 shadow-sm h-100 animating">
                            <div class="metric-accent bg-danger"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Total Retiros <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Total acumulado de retiros de capital realizados históricamente" style="cursor:help;font-size:0.75rem;"></i></span>
                                    <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="ti ti-cash-off"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="capTotalRetiros">$0</div>
                                <div class="metric-footer">
                                    <i class="ti ti-receipt"></i> <span>Histórico</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6">
                        <div class="card card-stat metric-card border-0 shadow-sm h-100 animating">
                            <div class="metric-accent bg-secondary"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Gastos del Mes <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Gastos operativos del período (fijos + variables)" style="cursor:help;font-size:0.75rem;"></i></span>
                                    <div class="metric-icon bg-secondary bg-opacity-10 text-secondary">
                                        <i class="ti ti-shopping-cart"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="capGastosMes">$0</div>
                                <div class="metric-footer d-flex gap-3 small">
                                    <span><i class="ti ti-circle"></i> Fijos: <strong id="capGastosFijos">$0</strong></span>
                                    <span><i class="ti ti-circle"></i> Variables: <strong id="capGastosVariables">$0</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remanente Detallado -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0"><i class="ti ti-calculator text-primary me-2"></i>Remanente del Período</h6>
                        <div>
                            <button class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#modalExplicacionCalculo" title="Ver detalle de cómo se calcula">
                                <i class="ti ti-info-circle"></i> ¿Cómo se calcula?
                            </button>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 fs-6 fw-bold" id="remanenteExplicacion">$0</span>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:0.85rem;">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                        <i class="ti ti-percentage me-1"></i>Com. AMIRI: <span id="remComisiones">$0</span>
                                    </span>
                                    <span class="text-muted fw-bold">+</span>
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                        <i class="ti ti-currency-dollar me-1"></i>Gan. Divisas: <span id="remDivisas">$0</span>
                                    </span>
                                    <span class="text-muted fw-bold">-</span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                                        <i class="ti ti-shopping-cart me-1"></i>Gastos: <span id="remGastos">$0</span>
                                    </span>
                                    <span class="fw-bold mx-1 text-muted">=</span>
                                    <span class="badge bg-success text-white px-3 py-2 fs-6" id="remanenteExplicacion2">$0</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="bg-light rounded p-2 small border">
                                    <div class="row g-1">
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">Com. AMIRI</span>
                                                <span id="remComVal">$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">+ Gan. Divisas</span>
                                                <span id="remDivVal">$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">- Gastos Fijos</span>
                                                <span class="text-danger" id="remGastosFVal">-$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">- Gastos Variables</span>
                                                <span class="text-danger" id="remGastosVVal">-$0</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between px-1 fw-bold border-bottom pb-1">
                                                <span>Remanente</span>
                                                <span class="text-success" id="remTotalVal">$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">+ Capital anterior</span>
                                                <span class="text-muted" id="remSaldoAntVal">$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1">
                                                <span class="text-muted">- Retiros</span>
                                                <span class="text-danger" id="remRetirosVal">$0</span>
                                            </div>
                                            <div class="d-flex justify-content-between px-1 fw-bold pt-1" style="border-top:2px solid var(--nx-border);">
                                                <span>Capital Acumulado</span>
                                                <span class="text-success fs-6" id="remCapAcumVal">$0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="ti ti-info-circle me-1"></i>
                            Remanente = (Comisiones AMIRI + Ganancia Divisas) - Gastos Totales.
                            Se acumula mes a mes en los cortes. Retiros descuentan del capital disponible.
                        </small>
                    </div>
                </div>

                <!-- Gastos del Mes Detallado -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0"><i class="ti ti-shopping-cart text-secondary me-2"></i>Gastos del Mes</h6>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm" id="gastosMesSelect" style="width:auto;min-width:140px;">
                                <option value="">— Cargando —</option>
                            </select>
                            <small class="text-muted" id="gastosMesLabel"></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>Clasificación</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-secondary">Fijo</span></td>
                                        <td class="text-end" id="gastosFijosCant">0</td>
                                        <td class="text-end" id="gastosFijosTotal">$0</td>
                                        <td class="text-center"><button class="btn btn-sm btn-outline-secondary ver-gastos-btn" data-clasificacion="fijo"><i class="ti ti-eye"></i> Ver Gastos</button></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning text-dark">Variable</span></td>
                                        <td class="text-end" id="gastosVariablesCant">0</td>
                                        <td class="text-end" id="gastosVariablesTotal">$0</td>
                                        <td class="text-center"><button class="btn btn-sm btn-outline-secondary ver-gastos-btn" data-clasificacion="variable"><i class="ti ti-eye"></i> Ver Gastos</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal: Detalle Gastos -->
                <div class="modal fade" id="modalDetalleGastos" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="ti ti-shopping-cart text-secondary me-2"></i>Gastos <span id="modalGastosClasificacion">Fijos</span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-2">
                                <table class="table table-hover table-sm mb-0" id="tablaDetalleGastos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Categoría</th>
                                            <th>Concepto</th>
                                            <th class="text-end">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts: Historial de Cortes + Evolución -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0"><i class="ti ti-history text-primary me-2"></i>Historial de Cortes</h6>
                                <small class="text-muted">Utilidad vs Capital acumulado</small>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height:220px;">
                                    <canvas id="chartHistorialCortes"></canvas>
                                </div>
                                <div class="table-responsive mt-2" style="max-height:160px; overflow-y:auto;">
                                    <table class="table table-sm table-hover mb-0" id="tablaHistorialCortes" style="font-size:0.78rem;">
                                        <thead class="table-light" style="position:sticky;top:0;">
                                            <tr>
                                                <th>Mes</th>
                                                <th class="text-end">Utilidad</th>
                                                <th class="text-end">Capital</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-area text-primary me-2"></i>Evolución del Capital</h6>
                                <small class="text-muted">Tendencia</small>
                            </div>
                            <div class="card-body d-flex align-items-center">
                                <div class="chart-container w-100" style="height:220px;">
                                    <canvas id="chartEvolucionCapital"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cortes Mensuales -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0"><i class="ti ti-calendar-stats text-primary me-2"></i>Cortes Mensuales</h6>
                        <div>
                            <small class="text-muted me-2">Detalle completo mes a mes</small>
                            <button class="btn btn-sm btn-outline-warning" id="btnAbrirMes"><i class="ti ti-lock-open"></i> Abrir Mes</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaCierres" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Fecha Corte</th>
                                        <th class="text-end">Comisiones</th>
                                        <th class="text-end">Divisas</th>
                                        <th class="text-end">Gastos</th>
                                        <th class="text-end">Utilidad Neta</th>
                                        <th class="text-end">Capital Acumulado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Retiros -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent py-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0"><i class="ti ti-cash-off text-primary me-2"></i>Retiros</h6>
                        <span class="text-muted" style="font-size:0.85rem;">
                            <i class="ti ti-info-circle me-1"></i>Total retirado: <strong id="retirosTotalLabel">$0</strong>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaRetiros" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Concepto</th>
                                        <th>Cuenta</th>
                                        <th>Registrado por</th>
                                        <th>Registrado el</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Modal: Cerrar Mes -->
<div class="modal fade" id="modalCerrarMes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-lock me-2"></i>Cerrar Mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="cerrarMesSelect">Mes a cerrar</label>
                    <select class="form-select fs-5 fw-bold" id="cerrarMesSelect"></select>
                </div>
                <div class="bg-light rounded p-3 small" id="cerrarResumenContainer">
                    <div class="d-flex justify-content-between mb-1"><span>Comisiones AMIRI:</span><span id="cerrarComisiones">$0</span></div>
                    <div class="d-flex justify-content-between mb-1"><span>Ganancia Divisas:</span><span id="cerrarDivisas">$0</span></div>
                    <div class="d-flex justify-content-between mb-1"><span>Gastos Totales:</span><span id="cerrarGastos">$0</span></div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-1"><span>Utilidad Neta:</span><span id="cerrarUtilidad">$0</span></div>
                </div>
                <p class="text-muted small mt-2 mb-0">Esta acción cerrará el período y ajustará la cuenta de capital automáticamente.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnConfirmarCierre"><i class="ti ti-lock"></i> Confirmar Cierre</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Retiro -->
<div class="modal fade" id="modalAbrirMes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="ti ti-lock-open text-warning me-2"></i>Abrir Mes Cerrado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    <i class="ti ti-alert-triangle me-1"></i>
                    <strong>Precaución:</strong> Al abrir un mes se eliminará el corte mensual y se revertirán los ajustes de capital asociados. Podrás volver a cerrar después de verificar los datos.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="abrirMesSelect">Mes a abrir</label>
                    <select class="form-select fs-5 fw-bold" id="abrirMesSelect">
                        <option value="">Selecciona un mes...</option>
                    </select>
                </div>
                <div id="abrirMesInfo" class="bg-light rounded p-3 small" style="display:none;">
                    <div class="d-flex justify-content-between mb-1"><span>Utilidad Neta:</span><span id="abrirMesUtilidad">$0</span></div>
                    <div class="d-flex justify-content-between mb-1"><span>Capital Acumulado:</span><span id="abrirMesCapital">$0</span></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning" id="btnConfirmarAbrirMes" disabled><i class="ti ti-lock-open"></i> Abrir Mes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Retiro -->
<div class="modal fade" id="modalRetiroCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-cash-off me-2"></i>Registrar Retiro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="bg-light rounded p-3 small mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Capital Amiri (saldo actual):</span>
                        <span class="fw-bold text-success fs-6" id="retiroSaldoActual">$0</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cuenta origen</label>
                    <select class="form-select" id="retiroCuenta">
                        <option value="">-- Selecciona una cuenta --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="retiroMonto" placeholder="0.00" />
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="btnRetiroMontoTotal">
                        <i class="ti ti-arrow-down"></i> Monto Total ($<span id="retiroMontoTotalLabel">0.00</span>)
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Concepto</label>
                    <input type="text" class="form-control" id="retiroConcepto" placeholder="Ej: Retiro personal" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="retiroFecha" />
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning" id="btnConfirmarRetiro"><i class="ti ti-cash-off"></i> Registrar Retiro</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Explicación del Cálculo -->
<div class="modal fade" id="modalExplicacionCalculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-info-circle text-info me-2"></i>¿Cómo se calcula?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="ti ti-calculator me-1"></i>
                    <strong>Fórmula:</strong> Utilidad del Mes = Comisiones AMIRI + Ganancia Divisas − Gastos Totales
                </div>

                <h6 class="fw-semibold text-primary mb-2"><i class="ti ti-percentage me-1"></i>Comisiones AMIRI</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover mb-0" id="explicacionComisiones">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-end">Venta Total</th>
                                <th class="text-end">Comisión (~2%)</th>
                                <th>Moneda</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="text-muted text-center">Cargando...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold table-active">
                                <td colspan="3" class="text-end">Total Comisiones:</td>
                                <td class="text-end" id="explicacionTotalComisiones">$0</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h6 class="fw-semibold text-info mb-2"><i class="ti ti-currency-dollar me-1"></i>Ganancia Divisas</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover mb-0" id="explicacionDivisas">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="3" class="text-muted text-center">Cargando...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold table-active">
                                <td colspan="2" class="text-end">Total Divisas:</td>
                                <td class="text-end" id="explicacionTotalDivisas">$0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="bg-light rounded p-3 small">
                    <div class="row g-2">
                        <div class="col-md-4 text-center">
                            <div class="fw-bold text-primary fs-5" id="explicacionSubtotalComisiones">$0</div>
                            <span class="text-muted">Comisiones AMIRI</span>
                        </div>
                        <div class="col-md-1 text-center d-flex align-items-center justify-content-center fs-4 text-muted">+</div>
                        <div class="col-md-3 text-center">
                            <div class="fw-bold text-info fs-5" id="explicacionSubtotalDivisas">$0</div>
                            <span class="text-muted">Ganancia Divisas</span>
                        </div>
                        <div class="col-md-1 text-center d-flex align-items-center justify-content-center fs-4 text-muted">−</div>
                        <div class="col-md-3 text-center">
                            <div class="fw-bold text-danger fs-5" id="explicacionSubtotalGastos">$0</div>
                            <span class="text-muted">Gastos Totales</span>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="text-center">
                        <span class="fw-bold fs-4" id="explicacionResultado">$0</span>
                        <span class="text-muted ms-2">Utilidad del Mes</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="src/assets/js/sidebarmenu.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="src/assets/js/utils.js"></script>
<script src="src/assets/js/pantalla-completa.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function formatoNumero(n) {
        return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    let tablaCierres, tablaRetiros, chartEvolucion, chartHistorial;
    var paginaMesActual = '';

    function cargarResumen(mes) {
        var url = 'api/capital.php?tipo=resumen' + (mes ? '&mes=' + mes : '');
        $.getJSON(url, function(data) {
            $('#capCapitalActual').text('$' + formatoNumero(data.utilidades_acumuladas));
            $('#capUltimoCorte').text(data.ultimo_corte || 'Sin cortes');
            $('#capUtilidadMes').text('$' + formatoNumero(data.utilidad_mes));
            $('#capTotalRetiros').text('$' + formatoNumero(data.total_retiros));
            $('#capGastosMes').text('$' + formatoNumero(data.gastos_total));
            $('#capGastosFijos').text('$' + formatoNumero(data.gastos_fijos));
            $('#capGastosVariables').text('$' + formatoNumero(data.gastos_variables));
            $('#remanenteExplicacion').text('$' + formatoNumero(data.utilidad_mes));
            $('#remanenteExplicacion2').text('$' + formatoNumero(data.utilidad_mes));
            // Tabla detalle
            $('#remComisiones').text('$' + formatoNumero(data.comisiones_amiri));
            $('#remDivisas').text('$' + formatoNumero(data.ganancia_divisas));
            $('#remGastos').text('$' + formatoNumero(data.gastos_total));
            $('#remComVal').text('$' + formatoNumero(data.comisiones_amiri));
            $('#remDivVal').text('$' + formatoNumero(data.ganancia_divisas));
            $('#remGastosFVal').text('-$' + formatoNumero(data.gastos_fijos));
            $('#remGastosVVal').text('-$' + formatoNumero(data.gastos_variables));
            $('#remTotalVal').text('$' + formatoNumero(data.utilidad_mes));
            var saldoAnt = data.capital_anterior;
            $('#remSaldoAntVal').text('$' + formatoNumero(saldoAnt));
            $('#remRetirosVal').text('$' + formatoNumero(data.total_retiros));
            $('#remCapAcumVal').text('$' + formatoNumero(data.capital_actual));

            paginaMesActual = data.mes_actual;
            // Cargar tabla de gastos del mes actual
            cargarGastosMes(data.mes_actual);

            // Banner de inicializar oculto permanentemente
        });
    }

    var _cierresData = null;

    function _renderHistorialCortes(data) {
        var tbody = '';
        data.forEach(function(r) {
            tbody += '<tr><td>' + r.mes + '</td><td class="text-end text-success">$' + formatoNumero(r.utilidad_neta) + '</td><td class="text-end text-primary fw-bold">$' + formatoNumero(r.capital_acumulado) + '</td></tr>';
        });
        $('#tablaHistorialCortes tbody').html(tbody || '<tr><td colspan="3" class="text-muted text-center">Sin cortes registrados</td></tr>');
        if (data.length < 2) { if (chartHistorial) chartHistorial.destroy(); chartHistorial = null; return; }
        var labels = data.map(function(d) { return d.mes; });
        var utilidades = data.map(function(d) { return parseFloat(d.utilidad_neta); });
        var capitales = data.map(function(d) { return parseFloat(d.capital_acumulado); });
        if (chartHistorial) chartHistorial.destroy();
        chartHistorial = new Chart(document.getElementById('chartHistorialCortes'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Capital Acumulado', data: capitales, type: 'line', borderColor: 'var(--nx-primary)', backgroundColor: 'rgba(197,143,184,0.1)', fill: true, tension: 0.4, pointRadius: 3, yAxisID: 'y1' },
                    { label: 'Utilidad Neta', data: utilidades, backgroundColor: 'rgba(19,222,185,0.7)', borderColor: 'var(--nx-success)', borderWidth: 1, borderRadius: 4, yAxisID: 'y' },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } } },
                scales: {
                    y: { beginAtZero: true, position: 'left', grid: { display: false } },
                    y1: { beginAtZero: false, position: 'right', grid: { display: false } }
                }
            }
        });
    }

    function _renderGrafica(data) {
        if (!data.length) return;
        if (chartEvolucion) chartEvolucion.destroy();
        var reversed = data.slice().reverse();
        var ctx = document.getElementById('chartEvolucionCapital').getContext('2d');
        chartEvolucion = new Chart(ctx, {
            type: 'line',
            data: {
                labels: reversed.map(function(d) { return d.mes; }),
                datasets: [
                    { label: 'Capital Acumulado', data: reversed.map(function(d) { return parseFloat(d.capital_acumulado); }), borderColor: 'var(--nx-primary)', backgroundColor: 'rgba(197,143,184,0.1)', fill: true, tension: 0.4, pointRadius: 3 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: false, grid: { display: true, drawBorder: false } } }
            }
        });
    }

    function cargarCierres(data) {
        if (tablaCierres) { if (data) { tablaCierres.clear().rows.add(data).draw(); } else { tablaCierres.ajax.reload(); } return; }
        var opts = {
            columns: [
                { data: 'mes' },
                { data: 'fecha_cierre', render: function(d) { return d ? d.split(' ')[0] : '-'; } },
                { data: 'comisiones_amiri', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
                { data: 'ganancia_divisas', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
                { data: 'gastos_total', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
                { data: 'utilidad_neta', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
                { data: 'capital_acumulado', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
            ],
            order: [[0, 'desc']],
            scrollX: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        };
        if (data) { opts.data = data; } else { opts.ajax = { url: 'api/capital.php?tipo=cierres', dataSrc: '' }; }
        tablaCierres = $('#tablaCierres').DataTable(opts);
    }

    function cargarRetiros() {
        if (tablaRetiros) { tablaRetiros.ajax.reload(); return; }
        tablaRetiros = $('#tablaRetiros').DataTable({
            ajax: { url: 'api/capital.php?tipo=retiros', dataSrc: '' },
            columns: [
                { data: 'fecha' },
                { data: 'monto', className: 'text-end', render: function(d) { return '$' + formatoNumero(d); } },
                { data: 'concepto' },
                { data: 'cuenta_nombre', render: function(d) { return d || '-'; } },
                { data: 'usuario_nombre' },
                { data: 'created_at', render: function(d) { return d ? d.split(' ')[0] : '-'; } },
            ],
            order: [[0, 'desc']],
            scrollX: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });
    }

    var gastosMesActual = '';
    var gastosMesData = [];

    function cargarGastosMes(mes) {
        gastosMesActual = mes || gastosMesActual || '';

        // Poblar selector de meses si está vacío
        if ($('#gastosMesSelect option').length <= 1) {
            var opts = '<option value="">— Mes —</option>';
            for (var a = 2026; a <= 2027; a++) {
                for (var m = 1; m <= 12; m++) {
                    var mm = (m < 10 ? '0' : '') + m;
                    var label = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][m];
                    opts += '<option value="' + a + '-' + mm + '">' + label + ' ' + a + '</option>';
                }
            }
            $('#gastosMesSelect').html(opts);
        }

        // Seleccionar el mes actual en el dropdown
        if (gastosMesActual) {
            $('#gastosMesSelect').val(gastosMesActual);
            var partes = gastosMesActual.split('-');
            var meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            var label = (partes.length === 2) ? (meses[parseInt(partes[1])] + ' ' + partes[0]) : gastosMesActual;
            $('#gastosMesLabel').text(label || '');
        }

        var url = 'api/capital.php?tipo=gastos_mes';
        if (gastosMesActual) url += '&mes=' + gastosMesActual;

        $.getJSON(url, function(data) {
            gastosMesData = data || [];

            var fijos = gastosMesData.filter(function(g) { return g.clasificacion === 'fijo'; });
            var variables = gastosMesData.filter(function(g) { return g.clasificacion === 'variable'; });

            var fijosTotal = fijos.reduce(function(s, g) { return s + parseFloat(g.monto); }, 0);
            var variablesTotal = variables.reduce(function(s, g) { return s + parseFloat(g.monto); }, 0);

            $('#gastosFijosCant').text(fijos.length);
            $('#gastosFijosTotal').text('$' + formatoNumero(fijosTotal));
            $('#gastosVariablesCant').text(variables.length);
            $('#gastosVariablesTotal').text('$' + formatoNumero(variablesTotal));
        });
    }

    function cargarCierresYGraficas() {
        if (_cierresData) {
            cargarCierres(_cierresData);
            _renderHistorialCortes(_cierresData);
            _renderGrafica(_cierresData);
            return;
        }
        $.getJSON('api/capital.php?tipo=cierres', function(data) {
            _cierresData = data;
            cargarCierres(data);
            _renderHistorialCortes(data);
            _renderGrafica(data);
        });
    }

    // Abrir modal de cierre
    $('#btnCerrarMes').on('click', function() {
        var meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        var $select = $('#cerrarMesSelect');
        $select.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

        $.getJSON('api/capital.php?tipo=meses_disponibles', function(data) {
            $select.empty();
            if (!data.meses || !data.meses.length) {
                $select.append('<option value="">No hay meses disponibles</option>');
                return;
            }
            data.meses.forEach(function(m) {
                var pts = m.split('-');
                var label = meses[parseInt(pts[1])] + ' ' + pts[0];
                $select.append('<option value="' + m + '">' + label + '</option>');
            });
            // Pre-seleccionar el mes actual
            var def = paginaMesActual || data.meses[data.meses.length - 1];
            if ($select.find('option[value="' + def + '"]').length) {
                $select.val(def);
            }
            $select.prop('disabled', false);
            $select.trigger('change');
            $('#modalCerrarMes').modal('show');
        }).fail(function() {
            $select.empty().append('<option value="">Error al cargar</option>');
        });
    });

    // Cambio de mes en select de cierre
    $(document).on('change', '#cerrarMesSelect', function() {
        var mes = $(this).val();
        if (!mes) return;
        var meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        var pts = mes.split('-');

        // Cargar preview
        $('#cerrarComisiones').text('$0');
        $('#cerrarDivisas').text('$0');
        $('#cerrarGastos').text('$0');
        $('#cerrarUtilidad').text('$0');
        $.getJSON('api/capital.php?tipo=resumen&mes=' + mes, function(data) {
            if (data.mes_cerrado) {
                var nomMes = meses[parseInt(pts[1])] + ' ' + pts[0];
                if (typeof toastr !== 'undefined') {
                    toastr.warning('El mes de ' + nomMes + ' ya está cerrado');
                } else {
                    alert('El mes de ' + nomMes + ' ya está cerrado');
                }
                return;
            }
            $('#cerrarComisiones').text('$' + formatoNumero(data.comisiones_amiri));
            $('#cerrarDivisas').text('$' + formatoNumero(data.ganancia_divisas));
            $('#cerrarGastos').text('$' + formatoNumero(data.gastos_total));
            $('#cerrarUtilidad').text('$' + formatoNumero(data.utilidad_mes));
        });
    });

    // Cambio de mes en filtro principal
    $(document).on('change', '#mesFilter', function() {
        cargarResumen($(this).val() || '');
    });

    // Cambio de mes en select de gastos
    $(document).on('change', '#gastosMesSelect', function() {
        var mes = $(this).val();
        if (mes) cargarGastosMes(mes);
    });

    // Ver gastos por clasificación (modal)
    $(document).on('click', '.ver-gastos-btn', function() {
        var clasif = $(this).data('clasificacion');
        var label = clasif === 'fijo' ? 'Fijos' : 'Variables';
        $('#modalGastosClasificacion').text(label);

        var filtrados = gastosMesData.filter(function(g) { return g.clasificacion === clasif; });
        var tbody = '';
        filtrados.forEach(function(g) {
            var cat = g.categoria_nombre || '-';
            var con = g.concepto || '-';
            tbody += '<tr><td>' + g.fecha + '</td><td>' + cat + '</td><td>' + con + '</td><td class="text-end">$' + formatoNumero(g.monto) + '</td></tr>';
        });
        if (!tbody) {
            tbody = '<tr><td colspan="4" class="text-muted text-center">Sin gastos ' + label.toLowerCase() + '</td></tr>';
        }
        $('#tablaDetalleGastos tbody').html(tbody);
        $('#modalDetalleGastos').modal('show');
    });

    // Confirmar cierre
    $('#btnConfirmarCierre').on('click', function() {
        const mes = $('#cerrarMesSelect').val();
        if (!mes) { alert('Selecciona un mes'); return; }
        const btn = $(this).prop('disabled', true).html('<i class="ti ti-loader spinner"></i> Cerrando...');
        $.post('api/capital.php?tipo=cerrar_mes', { mes: mes }, function(res) {
            if (res.success) {
                $('#modalCerrarMes').modal('hide');
                cargarResumen();
                _cierresData = null;
                cargarCierresYGraficas();
                if (typeof toastr !== 'undefined') { toastr.success('Mes ' + mes + ' cerrado correctamente'); } else { alert('Mes cerrado'); }
            } else {
                alert(res.error || 'Error al cerrar mes');
            }
        }).fail(function(xhr) {
            var err = 'Error al cerrar mes';
            try { err = JSON.parse(xhr.responseText).error || err; } catch(e) {}
            alert(err);
        }).always(function() { btn.prop('disabled', false).html('<i class="ti ti-lock"></i> Confirmar Cierre'); });
    });

    // Abrir mes cerrado
    $('#btnAbrirMes').on('click', function() {
        $('#abrirMesSelect').val('');
        $('#abrirMesInfo').hide();
        $('#btnConfirmarAbrirMes').prop('disabled', true);
        $.getJSON('api/capital.php?tipo=meses_cerrados', function(data) {
            var sel = $('#abrirMesSelect').empty().append('<option value="">Selecciona un mes...</option>');
            data.forEach(function(m) {
                sel.append('<option value="' + m.mes + '" data-utilidad="' + m.utilidad_neta + '" data-capital="' + m.capital_acumulado + '">' + m.mes + '</option>');
            });
            if (data.length === 0) {
                sel.append('<option value="" disabled>No hay meses cerrados</option>');
            }
        });
        $('#modalAbrirMes').modal('show');
    });

    $('#abrirMesSelect').on('change', function() {
        var opt = $(this).find(':selected');
        var mes = $(this).val();
        if (mes) {
            $('#abrirMesUtilidad').text('$' + formatoNumero(opt.data('utilidad')));
            $('#abrirMesCapital').text('$' + formatoNumero(opt.data('capital')));
            $('#abrirMesInfo').show();
            $('#btnConfirmarAbrirMes').prop('disabled', false);
        } else {
            $('#abrirMesInfo').hide();
            $('#btnConfirmarAbrirMes').prop('disabled', true);
        }
    });

    $('#btnConfirmarAbrirMes').on('click', function() {
        var mes = $('#abrirMesSelect').val();
        if (!mes) { alert('Selecciona un mes'); return; }
        if (!confirm('¿Estás seguro de abrir el mes ' + mes + '? Se eliminará el corte y se revertirán los ajustes de capital.')) return;
        var btn = $(this).prop('disabled', true).html('<i class="ti ti-loader spinner"></i> Abriendo...');
        $.post('api/capital.php?tipo=abrir_mes', { mes: mes }, function(res) {
            if (res.success) {
                $('#modalAbrirMes').modal('hide');
                cargarResumen();
                _cierresData = null;
                cargarCierresYGraficas();
                if (typeof toastr !== 'undefined') { toastr.success(res.mensaje); } else { alert(res.mensaje); }
            } else {
                alert(res.error || 'Error al abrir mes');
            }
        }).fail(function(xhr) {
            var err = 'Error al abrir mes';
            try { err = JSON.parse(xhr.responseText).error || err; } catch(e) {}
            alert(err);
        }).always(function() { btn.prop('disabled', false).html('<i class="ti ti-lock-open"></i> Abrir Mes'); });
    });

    // Abrir modal de retiro
    $('#btnRetiroCapital').on('click', function() {
        $('#retiroMonto').val('');
        $('#retiroConcepto').val('');
        $('#retiroFecha').val(new Date().toISOString().split('T')[0]);
        $('#retiroSaldoActual').text('$0');
        $('#retiroCuenta').val('');
        $.getJSON('api/capital.php?tipo=resumen', function(data) {
            $('#retiroSaldoActual').text('$' + formatoNumero(data.utilidades_acumuladas));
            $('#retiroMontoTotalLabel').text(formatoNumero(data.utilidades_acumuladas));
        });
        $.get('api/cuentas.php?con_saldo=1&tipo_moneda=MXN', function(cuentas) {
            var sel = $('#retiroCuenta').empty().append('<option value="">-- Selecciona una cuenta --</option>');
            var filtradas = cuentas.filter(function(c) { return c.tipo_cuenta == 1 || c.tipo_cuenta == 2; });
            filtradas.forEach(function(c) {
                var saldo = parseFloat(c.saldo_actual || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                var nombreCuenta = c.nombre;
                if (c.banco && c.banco.trim()) nombreCuenta += ' (' + c.banco + ')';
                var label = c.numero_cuenta ? nombreCuenta + ' - Saldo: $' + saldo : nombreCuenta + ' - Saldo: $' + saldo;
                sel.append('<option value="' + c.id + '" data-saldo="' + c.saldo_actual + '">' + label + '</option>');
            });
        });
        $('#modalRetiroCapital').modal('show');
    });

    // Botón Monto Total
    $(document).on('click', '#btnRetiroMontoTotal', function() {
        var total = parseFloat($('#retiroMontoTotalLabel').text().replace(/,/g, '')) || 0;
        $('#retiroMonto').val(total);
    });

    // Confirmar retiro
    $('#btnConfirmarRetiro').on('click', function() {
        var monto = parseFloat($('#retiroMonto').val()) || 0;
        var concepto = $('#retiroConcepto').val().trim();
        var fecha = $('#retiroFecha').val();
        var cuentaId = $('#retiroCuenta').val();
        if (!cuentaId) { alert('Selecciona una cuenta origen'); return; }
        if (monto <= 0) { alert('Ingresa un monto válido'); return; }
        if (!concepto) { alert('Ingresa un concepto'); return; }
        var saldoDisponible = parseFloat($('#retiroSaldoActual').text().replace(/[$,]/g, '')) || 0;
        if (monto > saldoDisponible) {
            alert('El monto ($' + formatoNumero(monto) + ') excede el saldo disponible ($' + formatoNumero(saldoDisponible) + ')');
            return;
        }
        var cuentaSaldo = parseFloat($('#retiroCuenta option:selected').data('saldo')) || 0;
        if (monto > cuentaSaldo) {
            alert('El monto ($' + formatoNumero(monto) + ') excede el saldo de la cuenta seleccionada ($' + formatoNumero(cuentaSaldo) + ')');
            return;
        }
        const btn = $(this).prop('disabled', true).html('<i class="ti ti-loader spinner"></i> Registrando...');
        $.post('api/capital.php?tipo=retiro', { monto: monto, concepto: concepto, fecha: fecha, cuenta_id: cuentaId }, function(res) {
            if (res.success) {
                $('#modalRetiroCapital').modal('hide');
                cargarResumen();
                if (tablaRetiros) tablaRetiros.ajax.reload();
                if (typeof toastr !== 'undefined') { toastr.success('Retiro registrado'); } else { alert('Retiro registrado'); }
            } else {
                alert(res.error || 'Error al registrar retiro');
            }
        }).fail(function(xhr) {
            var err = 'Error al registrar retiro';
            try { err = JSON.parse(xhr.responseText).error || err; } catch(e) {}
            alert(err);
        }).always(function() { btn.prop('disabled', false).html('<i class="ti ti-cash-off"></i> Registrar Retiro'); });
    });

    // Exportar
    function exportarReporte() {
        window.location.href = 'api/exportar-reporte.php?reporte=capital_amiri&formato=pdf&periodo=mes&' + new URLSearchParams({ fecha_inicio: '', fecha_fin: '' }).toString();
    }

    function cargarExplicacion(mes) {
        var url = 'api/capital.php?tipo=detalle_mes' + (mes ? '&mes=' + mes : '');
        $.getJSON(url, function(data) {
            // Comisiones
            var tbody = '';
            var totalCom = 0;
            (data.comisiones || []).forEach(function(c) {
                totalCom += parseFloat(c.comision_amiri || 0);
                tbody += '<tr><td>' + c.fecha + '</td><td>' + c.cliente + '</td><td class="text-end">$' + formatoNumero(c.total) + '</td><td class="text-end text-primary fw-bold">$' + formatoNumero(c.comision_amiri) + '</td><td>' + c.moneda + '</td><td><span class="badge bg-' + (c.estado === 'pagado' ? 'success' : c.estado === 'parcial' ? 'warning' : 'secondary') + '-opacity-10 text-' + (c.estado === 'pagado' ? 'success' : c.estado === 'parcial' ? 'warning' : 'secondary') + '">' + (c.estado || '') + '</span></td></tr>';
            });
            if (!tbody) tbody = '<tr><td colspan="6" class="text-muted text-center">Sin comisiones este mes</td></tr>';
            $('#explicacionComisiones tbody').html(tbody);
            $('#explicacionTotalComisiones').text('$' + formatoNumero(totalCom));

            // Divisas
            var tbodyDiv = '';
            var totalDiv = 0;
            (data.divisas || []).forEach(function(d) {
                totalDiv += parseFloat(d.monto || 0);
                tbodyDiv += '<tr><td>' + d.fecha + '</td><td>' + d.descripcion + '</td><td class="text-end text-info fw-bold">$' + formatoNumero(d.monto) + '</td></tr>';
            });
            if (!tbodyDiv) tbodyDiv = '<tr><td colspan="3" class="text-muted text-center">Sin ganancia de divisas este mes</td></tr>';
            $('#explicacionDivisas tbody').html(tbodyDiv);
            $('#explicacionTotalDivisas').text('$' + formatoNumero(totalDiv));

            // Subtotal formula
            var totalGastos = parseFloat($('#capGastosMes').text().replace(/[$,]/g, '')) || 0;
            var utilidad = totalCom + totalDiv - totalGastos;
            $('#explicacionSubtotalComisiones').text('$' + formatoNumero(totalCom));
            $('#explicacionSubtotalDivisas').text('$' + formatoNumero(totalDiv));
            $('#explicacionSubtotalGastos').text('$' + formatoNumero(totalGastos));
            $('#explicacionResultado').text('$' + formatoNumero(utilidad))
                .removeClass('text-success text-danger')
                .addClass(utilidad >= 0 ? 'text-success' : 'text-danger');
        });
    }

    // Cargar explicación al abrir modal
    $('#modalExplicacionCalculo').on('show.bs.modal', function() {
        var mes = $('#mesFilter').val() || paginaMesActual || '';
        cargarExplicacion(mes);
    });

    function cargarFiltroMeses() {
        $.getJSON('api/capital.php?tipo=meses_disponibles', function(data) {
            var mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
            var $select = $('#mesFilter');
            var actual = $select.val();
            var todos = (data.meses || []).concat(data.cerrados || []);
            todos = [...new Set(todos)].sort();
            $select.empty().append('<option value="">— Mes actual —</option>');
            todos.forEach(function(m) {
                var pts = m.split('-');
                $select.append('<option value="' + m + '">' + (mesesNombres[parseInt(pts[1])] || pts[1]) + ' ' + pts[0] + '</option>');
            });
            if (actual) $select.val(actual);
        });
    }

    // Init
    $(document).ready(function() {
        cargarResumen();
        cargarRetiros();
        cargarCierresYGraficas();
        cargarFiltroMeses();
        // Inicializar tooltips de Bootstrap
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
</body>
</html>
