<?php
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gastos - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script src="src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .gastos-header {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
        }
        .gastos-header .bg-pattern {
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(93, 135, 255, 0.08);
            pointer-events: none;
        }
        .gastos-header .bg-pattern-2 {
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(19, 222, 185, 0.06);
            pointer-events: none;
        }
        .gastos-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .gastos-header .mes-badge { font-size: 1rem; color: #8c9ea8; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card {
            position: relative;
            border-radius: 12px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
        }
        .metric-accent {
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            border-radius: 12px 12px 0 0;
        }
        .metric-label {
            font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.5px; color: #8c9ea8;
        }
        .metric-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
        }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #8c9ea8; }

        /* ─── Chart containers ────────────────────────── */
        /* ─── Desglose items ─────────────────────────── */
        .desglose-item { transition: background 0.15s ease; border-radius: 8px; padding: 0 8px; cursor: pointer; }
        .desglose-item:hover { background: rgba(0,0,0,0.02); }
        .desglose-item.active { background: rgba(93,135,255,0.06); box-shadow: inset 3px 0 0 #5D87FF; }
        [data-bs-theme="dark"] .desglose-item:hover { background: rgba(255,255,255,0.03); }
        [data-bs-theme="dark"] .desglose-item.active { background: rgba(93,135,255,0.12); }
        [data-bs-theme="dark"] .progress { background: #2c344c; }

        /* ─── Dark mode ───────────────────────────────── */
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] .page-breadcrumb h3 { color: #e9ecef !important; }
        [data-bs-theme="dark"] .card { background: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: #1a2234 !important; color: #e9ecef !important; border-bottom-color: #2c344c !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #e9ecef !important; border-color: #2c344c !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05); }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #252f43 !important; border-color: #2c344c !important; color: #e9ecef !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select,
        [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #252f43; border: 1px solid #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .modal-content { background-color: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: #2c344c; }
        [data-bs-theme="dark"] .modal-footer { border-top-color: #2c344c; }
        [data-bs-theme="dark"] .modal-title { color: #e9ecef; }
        [data-bs-theme="dark"] .form-label { color: #e9ecef; }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select { background-color: #252f43; border-color: #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #8c9ea8; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
         data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- ════════════════════════════════════
                         PAGE HEADER
                         ════════════════════════════════════ -->
                    <div class="card gastos-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(250,139,107,0.12);">
                                            <i class="ti ti-file-dollar text-danger" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0" id="mesLabel">Cargando...</h2>
                                            <span class="mes-badge"><i class="ti ti-circle"></i> Gastos</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select form-select-sm" id="selectPeriodo" style="min-width:130px;">
                                            <option value="mes" selected>Este Mes</option>
                                            <option value="mes_pasado">Mes Pasado</option>
                                            <option value="anio">Este Año</option>
                                            <option value="rango">Fecha Personalizada</option>
                                        </select>
                                        <div id="fechaInicioGroup" style="display:none;min-width:120px;">
                                            <input type="date" class="form-control form-control-sm" id="fechaInicio">
                                        </div>
                                        <div id="fechaFinGroup" style="display:none;min-width:120px;">
                                            <input type="date" class="form-control form-control-sm" id="fechaFin">
                                        </div>
                                        <button class="btn btn-primary btn-sm" id="btnFiltrar">
                                            <i class="ti ti-filter"></i> Aplicar
                                        </button>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border-0 py-1 px-2" id="rangoFechas"><i class="ti ti-calendar me-1"></i>Este Mes</span>
                                        <div class="vr mx-1"></div>
                                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalGasto">
                                            <i class="ti ti-plus"></i> Nuevo Gasto
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarTipo">
                                            <i class="ti ti-tag"></i> Tipo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         KPI METRIC CARDS
                         ════════════════════════════════════ -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-danger"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total del Mes</span>
                                        <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="ti ti-currency-dollar"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-danger" id="kpiTotal">$0.00</div>
                                    <div class="metric-footer" id="kpiTotalFooter">
                                        <i class="ti ti-calendar"></i> <span>Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Promedio x Gasto</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-calculator"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiPromedio">$0.00</div>
                                    <div class="metric-footer" id="kpiPromedioFooter">
                                        <i class="ti ti-calendar"></i> <span>Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-secondary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Cantidad de Gastos</span>
                                        <div class="metric-icon bg-secondary bg-opacity-10 text-secondary">
                                            <i class="ti ti-list"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-secondary" id="kpiCantidad">0</div>
                                    <div class="metric-footer" id="kpiCantidadFooter">
                                        <i class="ti ti-calendar"></i> <span>Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Gasto más Grande</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-arrow-up-right"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiMaximo">$0.00</div>
                                    <div class="metric-footer" id="kpiMaximoFooter">
                                        <i class="ti ti-tag"></i> <span>Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         DESGLOSE POR TIPO
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                            <h6 class="fw-semibold mb-0">
                                <i class="ti ti-list-details text-danger me-2"></i>Desglose por Tipo
                                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 ms-2" id="desgloseLegend"></span>
                            </h6>
                        </div>
                        <div class="card-body py-3" id="desgloseGastos">
                            <div class="text-muted text-center py-4"><i class="ti ti-circle-off" style="font-size:1.5rem"></i><br>Cargando...</div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         TABLE
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <div id="filterIndicator" class="d-none align-items-center gap-2 mb-3 small">
                                <span class="text-muted">Filtrando por:</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border-0 fw-normal px-3 py-1" id="filterLabel"></span>
                                <a href="#" id="clearFilter" class="text-decoration-none text-danger small"><i class="ti ti-x"></i> Quitar filtro</a>
                            </div>
                            <table id="tablaGastos" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cuenta</th>
                                        <th>Tipo de Gasto</th>
                                        <th>Clasificación</th>
                                        <th>Monto</th>
                                        <th>Concepto</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             MODAL: Nuevo Gasto
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalGasto" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo Gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formGasto">
                        <div class="modal-body">
                            <input type="hidden" id="id" name="id">
                            <div class="mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cuenta</label>
                                <select class="form-select" name="cuenta_id" required>
                                    <option value="">-- Selecciona una cuenta --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Clasificación (Fijo/Variable) *</label>
                                <select class="form-select" name="clasificacion" required>
                                    <option value="">-- Selecciona una clasificación --</option>
                                    <option value="fijo">Gasto Fijo</option>
                                    <option value="variable">Gasto Variable</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Gasto *</label>
                                <select class="form-select" name="categoria_id" required>
                                    <option value="">-- Selecciona un tipo --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="ti ti-coin"></i> Monto
                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" step="0.01" class="form-control" name="monto" id="inputMonto" required min="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Concepto</label>
                                <textarea class="form-control" name="concepto" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             MODAL: Registrar Tipo de Gasto
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalAgregarTipo" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-tag"></i> Nuevo Tipo de Gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formAgregarTipo">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="src/assets/js/dark-mode.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>

        <script>
            // ─── Helpers ────────────────────────────────
            function formatoMoneda(value, currency) {
                if (typeof value !== 'number') value = Number(value) || 0;
                return '$' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // ─── Globals ─────────────────────────────────
            let tabla = null;

            // ─── Period Filter ───────────────────────────
            function getParams() {
                const periodo = $('#selectPeriodo').val();
                if (periodo === 'mes_pasado') {
                    const d = new Date();
                    d.setMonth(d.getMonth() - 1);
                    const anio = d.getFullYear();
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const dias = new Date(anio, d.getMonth() + 1, 0).getDate();
                    return 'periodo=rango&fecha_inicio=' + anio + '-' + mes + '-01&fecha_fin=' + anio + '-' + mes + '-' + String(dias).padStart(2, '0');
                }
                if (periodo === 'rango') {
                    return 'periodo=rango&fecha_inicio=' + $('#fechaInicio').val() + '&fecha_fin=' + $('#fechaFin').val();
                }
                return 'periodo=' + periodo;
            }

            $(document).on('change', '#selectPeriodo', function() {
                if ($(this).val() === 'rango') {
                    $('#fechaInicioGroup, #fechaFinGroup').show();
                } else {
                    $('#fechaInicioGroup, #fechaFinGroup').hide();
                    actualizarDashboard();
                }
            });

            $(document).on('click', '#btnFiltrar', function() {
                actualizarDashboard();
            });

            // ─── Cargar Resumen (KPI Cards) ──────────────
            function cargarResumen() {
                var periodoTexto = { 'mes': 'Este Mes', 'mes_pasado': 'Mes Pasado', 'anio': 'Este Año', 'rango': 'Fecha Personalizada' };
                var label = periodoTexto[$('#selectPeriodo').val()] || 'Este Mes';
                $('#rangoFechas').html('<i class="ti ti-calendar me-1"></i>' + label);
                $.getJSON('api/gastos.php?resumen=1&' + getParams(), function(data) {
                    $('#kpiTotal').text(formatoMoneda(data.total_monto));
                    $('#kpiPromedio').text(formatoMoneda(data.promedio_monto));
                    $('#kpiCantidad').text(data.total_gastos);
                    $('#kpiMaximo').text(formatoMoneda(data.max_monto));

                    var subtxt = (data.total_gastos || 0) + ' gastos';
                    $('#kpiTotalFooter').html('<i class="ti ti-calendar"></i> ' + subtxt);
                    $('#kpiPromedioFooter').html('<i class="ti ti-calendar"></i> ' + subtxt);
                    $('#kpiCantidadFooter').html('<i class="ti ti-calendar"></i> ' + subtxt);
                    $('#kpiMaximoFooter').html('<i class="ti ti-tag"></i> ' + (data.max_concepto || '—'));
                }).fail(function() {
                    $('#kpiTotal').text('Error');
                });
            }

            // ─── Desglose por Tipo ──────────────────────
            const badgeClasif = {
                'fijo': '<span class="badge bg-primary bg-opacity-10 text-primary border-0" style="font-size:0.65rem">Fijo</span>',
                'variable': '<span class="badge bg-warning bg-opacity-10 text-warning border-0" style="font-size:0.65rem">Variable</span>',
                'mixto': '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0" style="font-size:0.65rem">Mixto</span>',
                'sin_definir': '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0" style="font-size:0.65rem">—</span>'
            };

            function renderDesglose(datos) {
                let container = document.getElementById('desgloseGastos');
                container.innerHTML = '';

                if (!datos || datos.length === 0 || datos.every(d => parseFloat(d.total) === 0)) {
                    container.innerHTML = '<div class="text-muted text-center py-4"><i class="ti ti-circle-off" style="font-size:1.5rem"></i><br>Sin gastos en este mes</div>';
                    $('#desgloseLegend').text('');
                    return;
                }

                let totalGeneral = datos.reduce((s, d) => s + parseFloat(d.total), 0);
                let fijos = datos.filter(d => d.clasificacion === 'fijo').length;
                let vars = datos.filter(d => d.clasificacion === 'variable').length;
                let partes = [];
                if (fijos) partes.push('<span class="text-primary">' + fijos + ' fijos</span>');
                if (vars) partes.push('<span class="text-warning">' + vars + ' variables</span>');
                $('#desgloseLegend').html('(' + partes.join(' · ') + ') · Total: ' + formatoMoneda(totalGeneral));

                let html = '';
                datos.forEach(function(d, i) {
                    let total = parseFloat(d.total) || 0;
                    let pct = totalGeneral > 0 ? (total / totalGeneral * 100) : 0;
                    let pctBar = Math.max(pct, 2); // mínimo 2% para barra visible
                    let color = d.clasificacion === 'fijo' ? '#5D87FF' :
                                d.clasificacion === 'variable' ? '#FFAE1F' : '#8c9ea8';

                    html += '<div class="desglose-item" data-tipo="' + d.tipo.replace(/"/g, '&quot;') + '" data-index="' + i + '">';
                    html += '  <div class="d-flex align-items-center gap-3 py-2">';
                    html += '    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-2" style="width:36px;height:36px;background:' + color + '18;">';
                    html += '      <i class="ti ti-receipt" style="color:' + color + ';font-size:1.1rem;"></i>';
                    html += '    </div>';
                    html += '    <div class="flex-grow-1" style="min-width:0;">';
                    html += '      <div class="d-flex justify-content-between align-items-center mb-1">';
                    html += '        <span class="fw-semibold small">' + d.tipo + '</span>';
                    html += '        <div class="d-flex align-items-center gap-2">';
                    html += '          ' + badgeClasif[d.clasificacion] || '';
                    html += '          <span class="fw-bold" style="color:' + color + ';font-size:0.95rem;">' + formatoMoneda(total) + '</span>';
                    html += '        </div>';
                    html += '      </div>';
                    html += '      <div class="progress" style="height:6px;background:#e9ecef;">';
                    html += '        <div class="progress-bar rounded-pill" role="progressbar" style="width:' + pctBar + '%;background:' + color + ';" aria-valuenow="' + pct + '" aria-valuemin="0" aria-valuemax="100"></div>';
                    html += '      </div>';
                    html += '      <div class="d-flex justify-content-end" style="font-size:0.7rem;color:#8c9ea8;margin-top:2px;">' + pct.toFixed(1) + '% del total</div>';
                    html += '    </div>';
                    html += '  </div>';
                    html += datos.length > 1 && i < datos.length - 1 ? '<hr class="my-0 opacity-25">' : '';
                    html += '</div>';
                });

                container.innerHTML = html;
            }

            function cargarDesglose() {
                $.getJSON('api/gastos.php?chart=1&' + getParams(), function(data) {
                    renderDesglose(data);
                }).fail(function() {
                    renderDesglose([]);
                });
            }

            // ─── Filtro por tipo (clic en desglose) ──────
            let filtroActivo = '';

            $('#desgloseGastos').on('click', '.desglose-item', function() {
                let tipo = $(this).data('tipo');
                if (!tipo) return;
                if ($(this).hasClass('active') && filtroActivo === tipo) {
                    limpiarFiltro();
                    return;
                }
                aplicarFiltro(tipo);
            });

            function aplicarFiltro(tipo) {
                filtroActivo = tipo;
                $('.desglose-item').removeClass('active');
                $('.desglose-item[data-tipo="' + tipo.replace(/"/g, '&quot;') + '"]').addClass('active');
                $('#filterLabel').text(tipo);
                $('#filterIndicator').removeClass('d-none').addClass('d-flex');

                if (tabla) {
                    let col = tabla.column(2);
                    col.search('^' + tipo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).draw();
                }
            }

            function limpiarFiltro() {
                filtroActivo = '';
                $('.desglose-item').removeClass('active');
                $('#filterIndicator').removeClass('d-flex').addClass('d-none');
                if (tabla) {
                    tabla.column(2).search('').draw();
                }
            }

            $('#clearFilter').on('click', function(e) {
                e.preventDefault();
                limpiarFiltro();
            });

            // ─── DataTable ───────────────────────────────
            function cargarTabla() {
                let url = 'api/gastos.php?' + getParams();

                if (tabla) {
                    tabla.ajax.url(url).load();
                } else {
                    tabla = $('#tablaGastos').DataTable({
                        ajax: { url: url, dataSrc: '' },
                        columns: [
                            {
                                data: 'fecha',
                                title: 'Fecha',
                                render: function(data, type, row) {
                                    if (!data) return 'N/A';
                                    if (type === 'sort' || type === 'type') return data;
                                    const soloFecha = data.split(' ')[0];
                                    const partes = soloFecha.split('-');
                                    const fechaOp = partes[2] + '/' + partes[1] + '/' + partes[0];
                                    let infoExtra = '';
                                    if (row.creado_en) {
                                        let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                                        infoExtra = '<br><small class="text-muted" style="font-size:0.7rem;"><i class="ti ti-device-floppy"></i> ' + captura + '</small>';
                                    }
                                    return '<span class="fw-bold">' + fechaOp + '</span>' + infoExtra;
                                }
                            },
                            { data: 'cuenta_nombre' },
                            { data: 'tipo_gasto_nombre' },
                            {
                                data: 'clasificacion',
                                render: function(data) {
                                    if (data === 'fijo') return '<span class="badge bg-primary bg-opacity-10 text-primary border-0">Fijo</span>';
                                    if (data === 'variable') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Variable</span>';
                                    return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Sin definir</span>';
                                }
                            },
                            {
                                data: 'monto',
                                render: function(data) {
                                    let monto = parseFloat(data) || 0;
                                    return '<strong class="text-danger">$' + monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong>';
                                }
                            },
                            { data: 'concepto' }
                        ],
                        scrollX: true,
                        order: [[0, 'desc']],
                        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
                    });
                }
            }

            // ─── Dashboard completo ──────────────────────
            function actualizarDashboard() {
                limpiarFiltro();
                cargarResumen();
                cargarDesglose();
                cargarTabla();
            }

            // ─── Modales ─────────────────────────────────
            function cargarTiposGasto() {
                $.get('api/tipos-gasto.php', function(tipos) {
                    const select = $('select[name="categoria_id"]');
                    select.empty().append('<option value="">-- Selecciona un tipo --</option>');
                    tipos.forEach(t => select.append('<option value="' + t.id + '">' + t.nombre + '</option>'));
                });
            }

            $('#modalGasto').on('show.bs.modal', function() {
                $.get('api/cuentas.php?solo_gastos=1&con_saldo=1', function(cuentas) {
                    const sel = $('select[name="cuenta_id"]');
                    sel.empty().append('<option value="">-- Selecciona una cuenta --</option>');
                    cuentas.forEach(c => {
                        const saldo = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(c.saldo_actual);
                        let nombreCuenta = c.nombre;
                        if (c.banco && c.banco.trim()) nombreCuenta += ' (' + c.banco + ')';
                        const label = c.numero_cuenta ? nombreCuenta + ' - Saldo: ' + saldo : nombreCuenta + ' - Saldo: ' + saldo;
                        sel.append('<option value="' + c.id + '" data-saldo="' + c.saldo_actual + '">' + label + '</option>');
                    });
                    const efectivo = cuentas.find(c => c.nombre.toLowerCase().includes('efectivo'));
                    if (efectivo) sel.val(efectivo.id);
                });
            });

            $('button[data-bs-target="#modalGasto"]').on('click', function() {
                $('#formGasto')[0].reset();
                $('#id').val('');
                $('#montoFormateado').text('');
                cargarTiposGasto();
            });

            $('#inputMonto').on('input', function() {
                const valor = parseFloat($(this).val());
                const spanFormato = $('#montoFormateado');
                if (isNaN(valor) || valor === 0) {
                    spanFormato.text('');
                } else {
                    const formato = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
                    spanFormato.text('= ' + formato);
                }
            });

            $('#formGasto').on('submit', function(e) {
                e.preventDefault();
                const cuentaId = $('select[name="cuenta_id"]').val();
                const saldo = parseFloat($('select[name="cuenta_id"] option:selected').data('saldo'));
                const monto = parseFloat($('input[name="monto"]').val());
                if (monto > saldo) {
                    showToast('El monto excede el saldo disponible (' + formatoMoneda(saldo) + ')', 'error');
                    return;
                }
                const fd = new FormData($('#formGasto')[0]);
                $.ajax({
                    url: 'api/gastos.php',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#modalGasto').modal('hide');
                        actualizarDashboard();
                        showToast('Gasto guardado', 'success');
                    },
                    error: function(xhr) {
                        let msg = 'Error al guardar';
                        try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
                        showToast(msg, 'error');
                    }
                });
            });

            $('#formAgregarTipo').on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData($('#formAgregarTipo')[0]);
                $.ajax({
                    url: 'api/tipos-gasto.php',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#modalAgregarTipo').modal('hide');
                        showToast('Tipo de gasto agregado', 'success');
                        cargarTiposGasto();
                    },
                    error: function(xhr) {
                        let msg = 'Error desconocido';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            msg = res.error || msg;
                        } catch(e) {
                            msg = xhr.responseText || 'Error ' + xhr.status + ': ' + xhr.statusText;
                        }
                        const modalBody = $('#modalAgregarTipo .modal-body');
                        modalBody.prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    }
                });
            });

            // ─── Init ────────────────────────────────────
            $(document).ready(function() {
                actualizarDashboard();
            });
        </script>
</body>
</html>
