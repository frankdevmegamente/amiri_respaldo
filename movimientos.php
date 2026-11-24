<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Movimientos - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }

        [data-bs-theme="dark"] .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro { display: block !important; }

        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .card { /* Helios handles card bg */ }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: var(--nx-surface) !important; color: #f5f2f4 !important; border-bottom-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #f5f2f4 !important; border-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.05); }

        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select,
        [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #1a181c; border: 1px solid rgba(255,255,255,0.12); color: #f5f2f4; }

        [data-bs-theme="dark"] .badge.bg-success { background-color: rgba(19,222,185,0.15) !important; color: var(--nx-success) !important; }
        [data-bs-theme="dark"] .badge.bg-danger { background-color: rgba(250,137,107,0.15) !important; color: var(--nx-danger) !important; }
        [data-bs-theme="dark"] .badge.bg-info { background-color: rgba(197,143,184,0.15) !important; color: var(--nx-primary) !important; }
        [data-bs-theme="dark"] .badge.bg-secondary { background-color: rgba(140,158,168,0.15) !important; color: #9a9096 !important; }
        [data-bs-theme="dark"] .badge.bg-warning { background-color: rgba(255,174,31,0.15) !important; color: var(--nx-warning) !important; }

        .mov-header { position: relative; overflow: hidden; border-radius: 16px !important; }
        .mov-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(197,143,184,0.10); pointer-events: none; }
        .mov-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19,222,185,0.06); pointer-events: none; }
        .mov-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .mov-header .sub-badge { font-size: 1rem; color: #9a9096; }

        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }

        [data-bs-theme="dark"] .mov-header .sub-badge { color: #9a9096 !important; }
        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(197,143,184,0.12) !important; }
        [data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(19,222,185,0.12) !important; }
        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255,174,31,0.12) !important; }
        [data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(83,155,255,0.12) !important; }
        [data-bs-theme="dark"] .bg-danger.bg-opacity-10 { background-color: rgba(250,137,107,0.12) !important; }
        [data-bs-theme="dark"] .bg-light { background-color: var(--nx-surface-2) !important; }

        .filtros-bar { background: var(--nx-surface-2) !important; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; }
        [data-bs-theme="dark"] .filtros-bar { background: var(--nx-surface-2); }
        .filtros-bar .form-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; margin-bottom: 4px; }
        .filtros-bar .form-control, .filtros-bar .form-select { font-size: 0.85rem; }

        tr.fila-anulada { opacity: 0.55; }
        tr.fila-anulada td { text-decoration: line-through; }

        .tipo-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .tipo-pill { cursor: pointer; border: 1px solid var(--nx-border); background: var(--nx-surface-2); color: var(--nx-text-muted);
            border-radius: 999px; padding: 6px 14px; font-size: 0.82rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px; transition: all .15s ease; }
        .tipo-pill:hover { border-color: var(--nx-primary); color: var(--nx-primary); }
        .tipo-pill.active { background: var(--nx-primary); border-color: var(--nx-primary); color: #fff; }
        .tipo-pill .count { background: rgba(0,0,0,.08); border-radius: 999px; padding: 0 8px; font-size: 0.75rem; }
        .tipo-pill.active .count { background: rgba(255,255,255,.25); }
        [data-bs-theme="dark"] .tipo-pill { background: var(--nx-surface-2); border-color: var(--nx-border); color: var(--nx-text-muted); }
        [data-bs-theme="dark"] .tipo-pill:hover { border-color: var(--nx-primary); color: var(--nx-primary); }
        [data-bs-theme="dark"] .tipo-pill.active { background: var(--nx-primary); border-color: var(--nx-primary); color: #fff; }

        /* Estado de cuenta por cuenta (tipo banco) */
        #resumenCuenta { border-left: 4px solid var(--nx-primary); }
        #resumenCuenta .rc-monto { font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
        #resumenCuenta .rc-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .monto-entrada { color: #0ca678; font-weight: 700; }
        .monto-salida { color: #e03131; font-weight: 700; }
        .saldo-cta { font-weight: 800; }
        [data-bs-theme="dark"] .monto-entrada { color: var(--nx-success) !important; }
        [data-bs-theme="dark"] .monto-salida { color: var(--nx-danger) !important; }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- PAGE HEADER -->
                    <div class="card mov-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(197,143,184,0.14);">
                                            <i class="ti ti-arrows-exchange text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Movimientos</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Historial completo de todos los movimientos</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-outline-secondary" onclick="exportarCSV()">
                                        <i class="ti ti-download"></i> Exportar CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI CARDS -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="ti ti-list"></i></div>
                                    </div>
                                    <div class="metric-value text-primary" id="kpiTotal">0</div>
                                    <div class="metric-footer">Movimientos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Entradas</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success"><i class="ti ti-arrow-down-left"></i></div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiEntradas">0</div>
                                    <div class="metric-footer">Ingresos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-danger"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Salidas</span>
                                        <div class="metric-icon bg-danger bg-opacity-10 text-danger"><i class="ti ti-arrow-up-right"></i></div>
                                    </div>
                                    <div class="metric-value text-danger" id="kpiSalidas">0</div>
                                    <div class="metric-footer">Egresos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Traspasos</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info"><i class="ti ti-arrows-double-ne-sw"></i></div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiTraspasos">0</div>
                                    <div class="metric-footer">Entre cuentas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Comisiones</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="ti ti-percentage"></i></div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiComisiones">0</div>
                                    <div class="metric-footer">Tipo comisi&oacute;n</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-secondary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Movimientos revertidos</span>
                                        <div class="metric-icon bg-secondary bg-opacity-10 text-secondary"><i class="ti ti-arrow-back-up"></i></div>
                                    </div>
                                    <div class="metric-value text-secondary" id="kpiRevertidos">0</div>
                                    <div class="metric-footer">Tipo ajuste</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN ESTADO DE CUENTA (visible al elegir una cuenta) -->
                    <div class="card shadow-sm border-0 mb-3" id="resumenCuenta" style="display:none;">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:42px;height:42px;background:rgba(197,143,184,0.14);">
                                            <i class="ti ti-building-bank text-primary" style="font-size:1.4rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" id="rcNombre">Cuenta</div>
                                            <small class="text-muted" id="rcDetalle">-</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row text-center g-2">
                                        <div class="col-6 col-lg-3">
                                            <div class="rc-label">Saldo inicial periodo</div>
                                            <div class="rc-monto" id="rcSaldoInicial">-</div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="rc-label">Entradas</div>
                                            <div class="rc-monto monto-entrada" id="rcEntradas">-</div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="rc-label">Salidas</div>
                                            <div class="rc-monto monto-salida" id="rcSalidas">-</div>
                                        </div>
                                        <div class="col-6 col-lg-3">
                                            <div class="rc-label">Saldo actual total</div>
                                            <div class="rc-monto text-primary" id="rcSaldoActual">-</div>
                                            <small class="text-muted" id="rcNumMovs" style="font-size:0.72rem;"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted" style="font-size:0.72rem;">Listado ordenado de nuevo a viejo (lo m&aacute;s reciente primero). El saldo remanente de cada fila es el saldo de la cuenta justo despu&eacute;s de ese movimiento. Si filtras por fechas, el saldo inicial corresponde al inicio del periodo filtrado.</small>
                        </div>
                    </div>

                    <!-- FILTROS RÁPIDOS POR TIPO -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body py-3">
                            <div class="tipo-pills" id="tipoPills">
                                <button type="button" class="tipo-pill active" data-tipo=""><i class="ti ti-list"></i> Todos <span class="count" id="countTodos">0</span></button>
                                <button type="button" class="tipo-pill" data-tipo="entrada"><i class="ti ti-arrow-down-left"></i> Entradas <span class="count" id="countEntradas">0</span></button>
                                <button type="button" class="tipo-pill" data-tipo="salida"><i class="ti ti-arrow-up-right"></i> Salidas <span class="count" id="countSalidas">0</span></button>
                                <button type="button" class="tipo-pill" data-tipo="traspaso"><i class="ti ti-arrows-double-ne-sw"></i> Traspasos <span class="count" id="countTraspasos">0</span></button>
                                <button type="button" class="tipo-pill" data-tipo="comision"><i class="ti ti-percentage"></i> Comisiones <span class="count" id="countComisiones">0</span></button>
                                <button type="button" class="tipo-pill" data-tipo="ajuste"><i class="ti ti-arrow-back-up"></i> Revertidos <span class="count" id="countAjustes">0</span></button>
                            </div>
                        </div>
                    </div>

                    <!-- FILTROS -->
                    <div class="filtros-bar">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="filtroFechaInicio">
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="filtroFechaFin">
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="salida">Salida</option>
                                    <option value="traspaso">Traspaso</option>
                                    <option value="comision">Comisi&oacute;n</option>
                                    <option value="ajuste">Ajuste</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Servicio</label>
                                <select class="form-select" id="filtroServicio">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Moneda</label>
                                <select class="form-select" id="filtroMoneda">
                                    <option value="">Todas</option>
                                    <option value="MXN">MXN</option>
                                    <option value="USD">USD</option>
                                    <option value="USDT">USDT</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6 d-flex align-items-end gap-2 flex-wrap">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="filtroAnulados" checked>
                                    <label class="form-check-label" for="filtroAnulados" style="font-size:0.8rem; white-space:nowrap;" title="Sin marcar: solo vigentes. Marcado: incluye anulados/corregidos/inactivos.">Incluir anulados</label>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="aplicarFiltros()">
                                    <i class="ti ti-search"></i> Buscar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                            <div class="col-xl-4 col-md-8">
                                <label class="form-label">B&uacute;squeda libre</label>
                                <input type="text" class="form-control" id="filtroTexto" placeholder="Descripci&oacute;n, folio, cuenta, cliente... (Enter para buscar)">
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label" title="Al elegir una cuenta se muestra la columna Saldo remanente y se ordena de viejo a nuevo, como estado de cuenta">Cuenta</label>
                                <select class="form-select" id="filtroCuenta">
                                    <option value="">Todas</option>
                                </select>
                                <small class="text-muted" style="font-size:0.72rem;">Elige una cuenta para ver saldo remanente</small>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" id="filtroCliente">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Proveedor</label>
                                <select class="form-select" id="filtroProveedor">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Prestamista</label>
                                <select class="form-select" id="filtroPrestamista">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label" title="Incluye movimientos directos del promotor + movimientos de sus clientes asociados">Promotor (+ clientes)</label>
                                <select class="form-select" id="filtroPromotor">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaMovimientos" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Servicio</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                                <th>Entrada</th>
                                                <th>Salida</th>
                                                <th>Moneda</th>
                                                <th>Saldo remanente</th>
                                                <th>Cta. Origen</th>
                                                <th>Cta. Destino</th>
                                                <th>Cliente</th>
                                                <th>Proveedor</th>
                                                <th>Prestamista</th>
                                                <th>Promotor</th>
                                                <th>Descripci&oacute;n</th>
                                                <th>Estado</th>
                                                <th>Registr&oacute;</th>
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
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/utils.js"></script>

    <script>
        let tabla;
        let datosCompletos = [];

        function construirURL() {
            let url = 'api/movimientos-todos.php?';
            const params = [];

            const fi = $('#filtroFechaInicio').val();
            const ff = $('#filtroFechaFin').val();
            const tipo = $('#filtroTipo').val();
            const servicio = $('#filtroServicio').val();
            const moneda = $('#filtroMoneda').val();
            const anulados = $('#filtroAnulados').is(':checked');
            const cuenta = $('#filtroCuenta').val();
            const cliente = $('#filtroCliente').val();
            const proveedor = $('#filtroProveedor').val();
            const prestamista = $('#filtroPrestamista').val();
            const promotor = $('#filtroPromotor').val();
            const texto = $('#filtroTexto').val().trim();

            if (fi) params.push('fecha_inicio=' + encodeURIComponent(fi));
            if (ff) params.push('fecha_fin=' + encodeURIComponent(ff));
            if (tipo) params.push('tipo=' + encodeURIComponent(tipo));
            if (servicio) params.push('servicio_id=' + encodeURIComponent(servicio));
            if (moneda) params.push('moneda=' + encodeURIComponent(moneda));
            if (cuenta) params.push('cuenta_id=' + encodeURIComponent(cuenta));
            if (cliente) params.push('cliente_id=' + encodeURIComponent(cliente));
            if (proveedor) params.push('proveedor_id=' + encodeURIComponent(proveedor));
            if (prestamista) params.push('prestamista_id=' + encodeURIComponent(prestamista));
            if (promotor) params.push('promotor_id=' + encodeURIComponent(promotor));
            if (texto) params.push('q=' + encodeURIComponent(texto));
            if (anulados) params.push('incluir_anulados=1');
            // Siempre lo más reciente primero
            params.push('orden=desc');

            return url + params.join('&');
        }

        function formatMonto(d, moneda) {
            if (!d) return moneda === 'USDT' ? '0.00 USDT' : '$0.00';
            if (moneda === 'USDT') {
                return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(d) + ' USDT';
            }
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: moneda || 'MXN' }).format(d);
        }

        function badgeTipo(d) {
            if (!d) return '';
            const t = d.toLowerCase();
            if (t === 'entrada') return '<span class="badge bg-success bg-opacity-10 text-success border-0">Entrada</span>';
            if (t === 'salida') return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Salida</span>';
            if (t === 'traspaso') return '<span class="badge bg-info bg-opacity-10 text-info border-0">Traspaso</span>';
            if (t === 'comision' || t === 'comisión') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Comisi&oacute;n</span>';
            if (t === 'ajuste') return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Ajuste</span>';
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0 text-capitalize">' + d + '</span>';
        }

        function badgeEstado(d) {
            if (!d) return '';
            const e = d.toLowerCase();
            if (e === 'activo') return '<span class="text-success fw-bold">Activo</span>';
            if (e === 'anulado') return '<span class="text-danger fw-bold">Anulado</span>';
            if (e === 'corregido') return '<span class="text-warning fw-bold">Corregido</span>';
            return '<span class="fw-bold text-capitalize">' + d + '</span>';
        }

        function formatFecha(data, type) {
            if (!data) return type === 'sort' ? '' : 'N/A';
            const iso = data.split(' ')[0];
            if (type === 'sort' || type === 'type') return iso;
            const partes = iso.split('-');
            if (partes.length !== 3) return data;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        function textCell(val) {
            return val || '<span class="text-muted">-</span>';
        }

        // ===== Lógica tipo banco por cuenta =====
        let cuentaInfo = null;          // {saldo_actual, nombre, banco, ...} de la cuenta filtrada
        let cuentaLista = false;        // true cuando ya llegó api/cuentas.php (evita flash con bal=0)
        let saldosCorridos = {};        // movimiento_id -> saldo después del movimiento
        let resumenCuenta = { entradas: 0, salidas: 0, inicial: null, actual: null, n: 0 };

        function cuentaFiltroId() {
            return String($('#filtroCuenta').val() || '');
        }
        function hayFiltroCuenta() {
            return !!cuentaFiltroId();
        }
        function ladoCuenta(m) {
            // Devuelve 'entrada' si la cuenta filtrada recibe, 'salida' si entrega.
            const f = cuentaFiltroId();
            if (!f) return '';
            const origenId = (m.cuenta_origen_id !== null && m.cuenta_origen_id !== undefined) ? String(m.cuenta_origen_id) : '';
            const destinoId = (m.cuenta_destino_id !== null && m.cuenta_destino_id !== undefined) ? String(m.cuenta_destino_id) : '';
            if (destinoId === f && origenId === f) {
                // Mismo origen y destino (raro): decide por tipo
                const t = (m.tipo || '').toLowerCase();
                return (t === 'salida') ? 'salida' : 'entrada';
            }
            if (destinoId === f) return 'entrada';
            if (origenId === f) return 'salida';
            return '';
        }
        function esEntradaCuenta(m) { return ladoCuenta(m) === 'entrada'; }
        function esSalidaCuenta(m) { return ladoCuenta(m) === 'salida'; }

        function esVigente(m) {
            // Solo los vigentes alimentan neto y corrido (anulado/corregido/inactivo quedan fuera).
            if (!m) return false;
            if (m.activo !== undefined && m.activo !== null && String(m.activo) !== '1') return false;
            const e = (m.estado || '').toLowerCase();
            return e === 'activo';
        }
        function montoAbs(m) { return Math.abs(parseFloat(m.monto) || 0); }

        // Saldo remanente: 1) historial_saldos del lado de la cuenta filtrada (si es sano),
        // 2) saldo corrido calculado solo con vigentes, 3) mejor dato disponible.
        function histLado(m) {
            const f = cuentaFiltroId();
            const origenId = (m.cuenta_origen_id !== null && m.cuenta_origen_id !== undefined) ? String(m.cuenta_origen_id) : '';
            const destinoId = (m.cuenta_destino_id !== null && m.cuenta_destino_id !== undefined) ? String(m.cuenta_destino_id) : '';
            if (f && destinoId === f && m.saldo_destino_nuevo !== null && m.saldo_destino_nuevo !== undefined && m.saldo_destino_nuevo !== '') {
                return { nuevo: parseFloat(m.saldo_destino_nuevo), anterior: parseFloat(m.saldo_destino_anterior) };
            }
            if (f && origenId === f && m.saldo_origen_nuevo !== null && m.saldo_origen_nuevo !== undefined && m.saldo_origen_nuevo !== '') {
                return { nuevo: parseFloat(m.saldo_origen_nuevo), anterior: parseFloat(m.saldo_origen_anterior) };
            }
            return null;
        }
        function histSano(h) {
            if (!h || h.nuevo === null || isNaN(h.nuevo)) return false;
            // Un saldo bancario negativo es imposible: historial corrupto (reversos con signo).
            if (h.nuevo < 0) return false;
            if (h.anterior !== null && !isNaN(h.anterior) && h.anterior < 0) return false;
            return true;
        }
        function getSaldoRemanente(m) {
            if (!m) return { nuevo: null, anterior: null };
            const h = histLado(m);
            if (histSano(h)) return h;
            if (m.id !== undefined && m.id !== null && saldosCorridos[m.id] !== undefined) {
                const v = saldosCorridos[m.id];
                return { nuevo: v.nuevo, anterior: v.anterior };
            }
            if (m.saldo_destino_nuevo !== null && m.saldo_destino_nuevo !== undefined) {
                const c = { nuevo: parseFloat(m.saldo_destino_nuevo), anterior: parseFloat(m.saldo_destino_anterior) };
                if (histSano(c)) return c;
            }
            if (m.saldo_origen_nuevo !== null && m.saldo_origen_nuevo !== undefined) {
                const c = { nuevo: parseFloat(m.saldo_origen_nuevo), anterior: parseFloat(m.saldo_origen_anterior) };
                if (histSano(c)) return c;
            }
            return { nuevo: null, anterior: null };
        }

        function renderSaldoRemanente(m) {
            const s = getSaldoRemanente(m);
            if (s.nuevo === null || isNaN(s.nuevo)) {
                return '<span class="text-muted" title="Sin historial de saldo para este movimiento">-</span>';
            }
            let flecha = '';
            if (s.anterior !== null && !isNaN(s.anterior)) {
                if (s.nuevo > s.anterior) flecha = ' <i class="ti ti-arrow-up-right text-success"></i>';
                else if (s.nuevo < s.anterior) flecha = ' <i class="ti ti-arrow-down-right text-danger"></i>';
            }
            const titulo = (s.anterior !== null && !isNaN(s.anterior))
                ? ('Saldo anterior: ' + formatMonto(s.anterior, m.moneda))
                : 'Saldo después del movimiento';
            return '<span class="saldo-cta" title="' + titulo + '">' + formatMonto(s.nuevo, m.moneda) + '</span>' + flecha;
        }

        function renderEntrada(m) {
            if (!esEntradaCuenta(m)) return '<span class="text-muted">-</span>';
            return '<span class="monto-entrada">+' + formatMonto(montoAbs(m), m.moneda) + '</span>';
        }
        function renderSalida(m) {
            if (!esSalidaCuenta(m)) return '<span class="text-muted">-</span>';
            return '<span class="monto-salida">-' + formatMonto(montoAbs(m), m.moneda) + '</span>';
        }

        function sincronizarColumnaSaldo() {
            if (!tabla) return;
            const esCuenta = hayFiltroCuenta();
            // Índices: 0 Fecha, 1 Servicio, 2 Tipo, 3 Monto, 4 Entrada, 5 Salida, 6 Moneda, 7 Saldo
            tabla.column(3).visible(!esCuenta, false);
            tabla.column(4).visible(esCuenta, false);
            tabla.column(5).visible(esCuenta, false);
            tabla.column(7).visible(esCuenta, false);
            tabla.columns.adjust();
            $('#resumenCuenta').toggle(esCuenta);
        }

        // Calcula totales y saldo corrido fila por fila (viejo -> nuevo), SOLO con vigentes.
        // El corrido solo se pinta cuando cuentaInfo ya llegó (bandera cuentaLista);
        // antes de eso se deja N/D para no mostrar el flash neto-desde-cero (-$10k).
        // saldoInicial = saldoActual - (entradas - salidas) del listado actual.
        function calcularResumenCuenta(rows) {
            saldosCorridos = {};
            resumenCuenta = { entradas: 0, salidas: 0, inicial: null, actual: null, n: 0 };
            if (!hayFiltroCuenta() || !Array.isArray(rows)) { pintarResumenCuenta(); return; }
            const vigentes = rows.filter(esVigente);
            let entradas = 0, salidas = 0;
            vigentes.forEach(function(m) {
                const v = montoAbs(m);
                if (esEntradaCuenta(m)) entradas += v;
                else if (esSalidaCuenta(m)) salidas += v;
                else {
                    // Sin lado claro (p.ej. traspaso donde la cuenta no coincide): usa tipo
                    const t = (m.tipo || '').toLowerCase();
                    if (t === 'entrada') entradas += v;
                    else if (t === 'salida' || t === 'comision' || t === 'comisión') salidas += v;
                }
            });
            const neto = entradas - salidas;
            const actual = cuentaInfo ? parseFloat(cuentaInfo.saldo_actual) : null;
            const inicial = (actual !== null && !isNaN(actual)) ? (actual - neto) : null;
            if (!cuentaLista || inicial === null) {
                // Cuenta aún cargando o sin saldo: no inventar corrido desde 0.
                resumenCuenta = { entradas: entradas, salidas: salidas, inicial: inicial, actual: actual, n: rows.length };
                pintarResumenCuenta();
                return;
            }
            // Recorre en orden cronológico (viejo -> nuevo) para el cálculo, aunque se muestre lo reciente primero
            const orden = vigentes.slice().sort(function(a, b) {
                if (a.fecha < b.fecha) return -1;
                if (a.fecha > b.fecha) return 1;
                return (a.id || 0) - (b.id || 0);
            });
            let bal = inicial;
            orden.forEach(function(m) {
                const v = montoAbs(m);
                const ant = bal;
                if (esEntradaCuenta(m)) bal += v;
                else if (esSalidaCuenta(m)) bal -= v;
                else {
                    const t = (m.tipo || '').toLowerCase();
                    if (t === 'entrada') bal += v;
                    else if (t === 'salida' || t === 'comision' || t === 'comisión') bal -= v;
                }
                saldosCorridos[m.id] = { nuevo: bal, anterior: ant };
            });
            resumenCuenta = { entradas: entradas, salidas: salidas, inicial: inicial, actual: actual, n: rows.length };
            pintarResumenCuenta();
        }

        function pintarResumenCuenta() {
            if (!hayFiltroCuenta()) return;
            const moneda = (cuentaInfo && cuentaInfo.tipo_moneda) ? cuentaInfo.tipo_moneda : ($('#filtroMoneda').val() || 'MXN');
            const nombre = cuentaInfo ? cuentaInfo.nombre : ($('#filtroCuenta option:selected').text() || 'Cuenta');
            const det = cuentaInfo
                ? ((cuentaInfo.banco ? cuentaInfo.banco + ' · ' : '') + (cuentaInfo.numero_cuenta || '') + ' · ' + (cuentaInfo.tipo_moneda || '')).trim()
                : '';
            $('#rcNombre').text(nombre);
            $('#rcDetalle').text(det || '-');
            $('#rcEntradas').text(formatMonto(resumenCuenta.entradas, moneda));
            $('#rcSalidas').text(formatMonto(resumenCuenta.salidas, moneda));
            $('#rcSaldoInicial').text(resumenCuenta.inicial === null ? 'N/D' : formatMonto(resumenCuenta.inicial, moneda));
            $('#rcSaldoActual').text(resumenCuenta.actual === null ? 'N/D' : formatMonto(resumenCuenta.actual, moneda));
            $('#rcNumMovs').text(resumenCuenta.n + ' movimientos en el filtro');
        }

        function cargarResumenCuenta() {
            const id = cuentaFiltroId();
            if (!id) { cuentaInfo = null; cuentaLista = true; calcularResumenCuenta(datosCompletos); sincronizarColumnaSaldo(); return; }
            cuentaLista = false;
            // Limpia el corrido previo para no pintar valores rancios mientras llega la cuenta
            saldosCorridos = {};
            $.getJSON('api/cuentas.php?id=' + encodeURIComponent(id))
                .done(function(c) { cuentaInfo = c || null; })
                .fail(function() { cuentaInfo = null; })
                .always(function() {
                    cuentaLista = true;
                    calcularResumenCuenta(datosCompletos);
                    sincronizarColumnaSaldo();
                    // Re-pinta la tabla con el saldo corrido ya calculado (cuadra con saldo actual)
                    if (tabla) tabla.draw(false);
                });
        }

        function calcularKPIs(data) {
            let entradas = 0, salidas = 0, traspasos = 0, comisiones = 0, revertidos = 0;

            (Array.isArray(data) ? data : []).forEach(function(m) {
                const t = (m.tipo || '').toLowerCase();
                if (t === 'entrada') entradas++;
                else if (t === 'salida') salidas++;
                else if (t === 'traspaso') traspasos++;
                else if (t === 'comision' || t === 'comisión') comisiones++;
                else if (t === 'ajuste') revertidos++;
            });

            // Tarjetas = registros actualmente en pantalla (respetan todos los filtros)
            $('#kpiTotal').text((Array.isArray(data) ? data.length : 0).toLocaleString('es-MX'));
            $('#kpiEntradas').text(entradas.toLocaleString('es-MX'));
            $('#kpiSalidas').text(salidas.toLocaleString('es-MX'));
            $('#kpiTraspasos').text(traspasos.toLocaleString('es-MX'));
            $('#kpiComisiones').text(comisiones.toLocaleString('es-MX'));
            $('#kpiRevertidos').text(revertidos.toLocaleString('es-MX'));
        }

        // Pills: desglose real bajo los demás filtros, IGNORANDO el tipo seleccionado.
        // Usa petición aparte sin `tipo` para que los números nunca se pongan en 0.
        let pillsSeq = 0;
        function construirURLSinTipo() {
            const url = construirURL().replace(/([?&])tipo=[^&]*&?/, function(m, sep) { return sep === '?' ? '?' : ''; });
            return url.replace(/[?&]$/, '');
        }
        function actualizarPillsConteo() {
            const seq = ++pillsSeq;
            const url = construirURLSinTipo();
            $('.tipo-pill .count').text('…');
            $.getJSON(url)
                .done(function(data) {
                    if (seq !== pillsSeq || !Array.isArray(data)) return;
                    let entradas = 0, salidas = 0, traspasos = 0, comisiones = 0, ajustes = 0;
                    data.forEach(function(m) {
                        const t = (m.tipo || '').toLowerCase();
                        if (t === 'entrada') entradas++;
                        else if (t === 'salida') salidas++;
                        else if (t === 'traspaso') traspasos++;
                        else if (t === 'comision' || t === 'comisión') comisiones++;
                        else if (t === 'ajuste') ajustes++;
                    });
                    const fmt = function(n) { return n.toLocaleString('es-MX'); };
                    $('#countTodos').text(fmt(data.length));
                    $('#countEntradas').text(fmt(entradas));
                    $('#countSalidas').text(fmt(salidas));
                    $('#countTraspasos').text(fmt(traspasos));
                    $('#countComisiones').text(fmt(comisiones));
                    $('#countAjustes').text(fmt(ajustes));
                })
                .fail(function() {
                    if (seq !== pillsSeq) return;
                    $('.tipo-pill .count').text('0');
                });
        }

        function setTipoFiltro(tipo) {
            $('#filtroTipo').val(tipo);
            marcarPillActivo(tipo);
            aplicarFiltros();
        }

        function marcarPillActivo(tipo) {
            $('#tipoPills .tipo-pill').each(function() {
                $(this).toggleClass('active', ($(this).data('tipo') || '') === (tipo || ''));
            });
        }

        function cargarServicios() {
            $.get('api/servicios.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(s) {
                        $('#filtroServicio').append('<option value="' + s.id + '">' + s.nombre + '</option>');
                    });
                }
            });
        }

        function aplicarFiltros() {
            if (!tabla) return;
            // Siempre lo más reciente primero
            tabla.order([[0, 'desc']]);
            tabla.ajax.url(construirURL()).load(function() {
                cargarResumenCuenta();
            });
            actualizarPillsConteo();
        }

        function limpiarFiltros() {
            $('#filtroFechaInicio').val('');
            $('#filtroFechaFin').val('');
            $('#filtroTipo').val('');
            $('#filtroServicio').val('');
            $('#filtroMoneda').val('');
            $('#filtroAnulados').prop('checked', false);
            $('#filtroCuenta').val('');
            $('#filtroCliente').val('');
            $('#filtroProveedor').val('');
            $('#filtroPrestamista').val('');
            $('#filtroPromotor').val('');
            $('#filtroTexto').val('');
            marcarPillActivo('');
            aplicarFiltros();
        }

        function cargarEntidades() {
            const params = new URLSearchParams(window.location.search);
            const cuentaParam = params.get('cuenta_id') || '';
            $.getJSON('api/cuentas.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(c) {
                        const label = c.nombre + (c.banco ? ' (' + c.banco + ')' : '') + ' [' + c.tipo_moneda + ']';
                        $('#filtroCuenta').append('<option value="' + c.id + '">' + label + '</option>');
                    });
                    // Preselección desde cuentas.php? "Ver movimientos"
                    if (cuentaParam && $('#filtroCuenta option[value="' + cuentaParam + '"]').length) {
                        $('#filtroCuenta').val(cuentaParam);
                        aplicarFiltros();
                    } else {
                        sincronizarColumnaSaldo();
                    }
                }
            });
            $.getJSON('api/clientes.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(c) {
                        $('#filtroCliente').append('<option value="' + c.id + '">' + c.nombre + '</option>');
                    });
                }
            });
            $.getJSON('api/proveedores.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(p) {
                        $('#filtroProveedor').append('<option value="' + p.id + '">' + p.nombre + '</option>');
                    });
                }
            });
            $.getJSON('api/prestamistas.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(p) {
                        $('#filtroPrestamista').append('<option value="' + p.id + '">' + p.nombre + '</option>');
                    });
                }
            });
            $.getJSON('api/promotores.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(p) {
                        $('#filtroPromotor').append('<option value="' + p.id + '">' + p.nombre + '</option>');
                    });
                }
            });
        }

        // NOTA: los filtros de entidad se combinan con AND en el servidor.
        // No se limpian entre sí: el usuario puede combinar Cuenta + Cliente + fechas, etc.

        function exportarCSV() {
            const esc = function(v) {
                v = (v === null || v === undefined) ? '' : String(v);
                if (/[",\n\r]/.test(v)) return '"' + v.replace(/"/g, '""') + '"';
                return v;
            };
            const esCuenta = hayFiltroCuenta();
            let headers, rows;
            if (esCuenta) {
                headers = ['Fecha','Servicio','Tipo','Entrada','Salida','Saldo_Remanente','Moneda'];
                rows = [headers];
                datosCompletos.forEach(function(m) {
                    const s = getSaldoRemanente(m);
                    rows.push([
                        formatFecha(m.fecha, 'display'),
                        m.servicio_nombre || '',
                        m.tipo || '',
                        esEntradaCuenta(m) ? (m.monto || 0) : '',
                        esSalidaCuenta(m) ? (m.monto || 0) : '',
                        (s.nuevo === null || isNaN(s.nuevo)) ? '' : s.nuevo,
                        m.moneda || ''
                    ].concat([
                        m.cuenta_origen_nombre || '',
                        m.cuenta_destino_nombre || '',
                        m.cliente_nombre || '',
                        m.proveedor_nombre || '',
                        m.prestamista_nombre || '',
                        m.promotor_nombre || '',
                        m.descripcion || '',
                        m.estado || '',
                        m.usuario_nombre || ''
                    ]));
                });
                rows[0] = headers.concat(['Cta Origen','Cta Destino','Cliente','Proveedor','Prestamista','Promotor','Descripcion','Estado','Registro']);
            } else {
                headers = ['Fecha','Servicio','Tipo','Monto','Moneda'];
                rows = [headers];
                datosCompletos.forEach(function(m) {
                    rows.push([
                        formatFecha(m.fecha, 'display'),
                        m.servicio_nombre || '',
                        m.tipo || '',
                        m.monto || 0,
                        m.moneda || '',
                        m.cuenta_origen_nombre || '',
                        m.cuenta_destino_nombre || '',
                        m.cliente_nombre || '',
                        m.proveedor_nombre || '',
                        m.prestamista_nombre || '',
                        m.promotor_nombre || '',
                        m.descripcion || '',
                        m.estado || '',
                        m.usuario_nombre || ''
                    ]);
                });
                rows[0] = headers.concat(['Cta Origen','Cta Destino','Cliente','Proveedor','Prestamista','Promotor','Descripcion','Estado','Registro']);
            }
            const csv = rows.map(function(r) { return r.map(esc).join(','); }).join('\r\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            const sufijo = esCuenta ? '_cuenta_' + cuentaFiltroId() : '';
            link.download = 'movimientos' + sufijo + '_' + new Date().toISOString().slice(0,10) + '.csv';
            link.click();
        }

        $(document).ready(function() {
            cargarServicios();
            cargarEntidades();

            // Enter en búsqueda libre dispara el filtro del servidor
            $('#filtroTexto').on('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); aplicarFiltros(); }
            });
            // Cambiar cualquier filtro recarga (excepto texto, que usa Enter)
            $('#filtroFechaInicio, #filtroFechaFin, #filtroTipo, #filtroServicio, #filtroMoneda, #filtroCuenta, #filtroCliente, #filtroProveedor, #filtroPrestamista, #filtroPromotor, #filtroAnulados').on('change', function() {
                if (this.id === 'filtroTipo') marcarPillActivo($(this).val());
                aplicarFiltros();
            });
            // Pills rápidos de tipo <-> select sincronizados
            $('#tipoPills').on('click', '.tipo-pill', function() {
                setTipoFiltro($(this).data('tipo') || '');
            });

            tabla = $('#tablaMovimientos').DataTable({
                ajax: {
                    url: construirURL(),
                    dataSrc: function(json) {
                        const rows = Array.isArray(json) ? json : (Array.isArray(json.data) ? json.data : []);
                        datosCompletos = rows;
                        calcularKPIs(rows);
                        calcularResumenCuenta(rows);
                        sincronizarColumnaSaldo();
                        return rows;
                    }
                },
                columns: [
                    {
                        data: 'fecha',
                        render: function(data, type) { return formatFecha(data, type); }
                    },
                    { data: 'servicio_nombre', defaultContent: '<span class="text-muted">N/A</span>' },
                    {
                        data: 'tipo',
                        render: function(d) { return badgeTipo(d); }
                    },
                    {
                        data: null,
                        render: function(d) {
                            return '<span class="fw-bold">' + formatMonto(d.monto, d.moneda) + '</span>';
                        }
                    },
                    {
                        data: null,
                        visible: false,
                        orderable: false,
                        render: function(d, type, row) { return renderEntrada(row); }
                    },
                    {
                        data: null,
                        visible: false,
                        orderable: false,
                        render: function(d, type, row) { return renderSalida(row); }
                    },
                    {
                        data: 'moneda',
                        render: function(d) {
                            if (!d) return '';
                            const colors = { 'MXN': 'primary', 'USD': 'success', 'USDT': 'info' };
                            const cls = colors[d] || 'secondary';
                            return '<span class="badge bg-' + cls + ' bg-opacity-10 text-' + cls + ' border-0">' + d + '</span>';
                        }
                    },
                    {
                        data: null,
                        visible: false,
                        orderable: false,
                        render: function(d, type, row) {
                            if (type === 'sort' || type === 'type') {
                                const s = getSaldoRemanente(row);
                                return (s.nuevo === null || isNaN(s.nuevo)) ? -999999999 : s.nuevo;
                            }
                            return renderSaldoRemanente(row);
                        }
                    },
                    { data: 'cuenta_origen_nombre', render: function(d) { return textCell(d); } },
                    { data: 'cuenta_destino_nombre', render: function(d) { return textCell(d); } },
                    { data: 'cliente_nombre', render: function(d) { return textCell(d); } },
                    { data: 'proveedor_nombre', render: function(d) { return textCell(d); } },
                    { data: 'prestamista_nombre', render: function(d) { return textCell(d); } },
                    { data: 'promotor_nombre', render: function(d) { return textCell(d); } },
                    { data: 'descripcion', defaultContent: '' },
                    {
                        data: null,
                        render: function(d) {
                            let html = badgeEstado(d.estado);
                            if (d.es_reverso == 1) {
                                html += ' <span class="badge bg-dark bg-opacity-10 text-dark border-0" style="font-size:0.65rem;">Reverso</span>';
                            }
                            return html;
                        }
                    },
                    { data: 'usuario_nombre', render: function(d) { return textCell(d); } }
                ],
                createdRow: function(row, data) {
                    if (data.estado === 'anulado' || data.estado === 'corregido') {
                        $(row).addClass('fila-anulada');
                    }
                },
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                initComplete: function() {
                    sincronizarColumnaSaldo();
                }
            });
            actualizarPillsConteo();
        });
    </script>
</body>
</html>
