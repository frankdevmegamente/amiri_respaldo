<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
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
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }

        [data-bs-theme="dark"] .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro { display: block !important; }

        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #e9ecef !important; }
        [data-bs-theme="dark"] .card { background: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: #1a2234 !important; color: #e9ecef !important; border-bottom-color: #2c344c !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #e9ecef !important; border-color: #2c344c !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.05); }

        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select,
        [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #252f43; border: 1px solid #2c344c; color: #e9ecef; }

        [data-bs-theme="dark"] .badge.bg-success { background-color: rgba(19,222,185,0.15) !important; color: #13DEB9 !important; }
        [data-bs-theme="dark"] .badge.bg-danger { background-color: rgba(250,137,107,0.15) !important; color: #FA896B !important; }
        [data-bs-theme="dark"] .badge.bg-info { background-color: rgba(93,135,255,0.15) !important; color: #5D87FF !important; }
        [data-bs-theme="dark"] .badge.bg-secondary { background-color: rgba(140,158,168,0.15) !important; color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .badge.bg-warning { background-color: rgba(255,174,31,0.15) !important; color: #FFAE1F !important; }

        .mov-header { position: relative; overflow: hidden; border-radius: 16px !important; }
        .mov-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93,135,255,0.08); pointer-events: none; }
        .mov-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19,222,185,0.06); pointer-events: none; }
        .mov-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .mov-header .sub-badge { font-size: 1rem; color: #8c9ea8; }

        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #8c9ea8; }

        [data-bs-theme="dark"] .mov-header .sub-badge { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(93,135,255,0.12) !important; }
        [data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(19,222,185,0.12) !important; }
        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255,174,31,0.12) !important; }
        [data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(83,155,255,0.12) !important; }
        [data-bs-theme="dark"] .bg-danger.bg-opacity-10 { background-color: rgba(250,137,107,0.12) !important; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }

        .filtros-bar { background: #f8f9fa; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; }
        [data-bs-theme="dark"] .filtros-bar { background: #1e293b; }
        .filtros-bar .form-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; margin-bottom: 4px; }
        .filtros-bar .form-control, .filtros-bar .form-select { font-size: 0.85rem; }

        tr.fila-anulada { opacity: 0.55; }
        tr.fila-anulada td { text-decoration: line-through; }
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
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-arrows-transfer text-primary" style="font-size:1.6rem;"></i>
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
                        <div class="col-xl-3 col-md-6">
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
                        <div class="col-xl-3 col-md-6">
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
                        <div class="col-xl-3 col-md-6">
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
                        <div class="col-xl-3 col-md-6">
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
                            <div class="col-xl-2 col-md-4 d-flex align-items-end gap-3">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="filtroAnulados">
                                    <label class="form-check-label" for="filtroAnulados" style="font-size:0.85rem;">Anulados</label>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="aplicarFiltros()">
                                    <i class="ti ti-search"></i> Buscar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label">Cuenta</label>
                                <select class="form-select" id="filtroCuenta">
                                    <option value="">Todas</option>
                                </select>
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
                                <label class="form-label">Promotor</label>
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
                                                <th>Moneda</th>
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
    <script src="src/assets/js/dark-mode.js"></script>
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

            if (fi) params.push('fecha_inicio=' + fi);
            if (ff) params.push('fecha_fin=' + ff);
            if (tipo) params.push('tipo=' + tipo);
            if (servicio) params.push('servicio_id=' + servicio);
            if (moneda) params.push('moneda=' + moneda);
            if (cuenta) params.push('cuenta_id=' + cuenta);
            if (cliente) params.push('cliente_id=' + cliente);
            if (proveedor) params.push('proveedor_id=' + proveedor);
            if (prestamista) params.push('prestamista_id=' + prestamista);
            if (promotor) params.push('promotor_id=' + promotor);
            if (anulados) params.push('incluir_anulados=1');

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

        function formatFecha(data) {
            if (!data) return 'N/A';
            const partes = data.split(' ')[0].split('-');
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        function textCell(val) {
            return val || '<span class="text-muted">-</span>';
        }

        function calcularKPIs(data) {
            let total = data.length;
            let entradas = 0, salidas = 0, traspasos = 0;

            data.forEach(function(m) {
                if (m.estado === 'anulado') return;
                const t = (m.tipo || '').toLowerCase();
                if (t === 'entrada') entradas++;
                else if (t === 'salida') salidas++;
                else if (t === 'traspaso') traspasos++;
            });

            $('#kpiTotal').text(total.toLocaleString('es-MX'));
            $('#kpiEntradas').text(entradas.toLocaleString('es-MX'));
            $('#kpiSalidas').text(salidas.toLocaleString('es-MX'));
            $('#kpiTraspasos').text(traspasos.toLocaleString('es-MX'));
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
            tabla.ajax.url(construirURL());
            tabla.ajax.reload(function() {
                calcularKPIs(datosCompletos);
            });
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
            aplicarFiltros();
        }

        function cargarEntidades() {
            $.getJSON('api/cuentas.php', function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(c) {
                        const label = c.nombre + (c.banco ? ' (' + c.banco + ')' : '') + ' [' + c.tipo_moneda + ']';
                        $('#filtroCuenta').append('<option value="' + c.id + '">' + label + '</option>');
                    });
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

        const entidadSelects = ['#filtroCuenta', '#filtroCliente', '#filtroProveedor', '#filtroPrestamista', '#filtroPromotor'];
        entidadSelects.forEach(function(sel) {
            $(sel).on('change', function() {
                if ($(this).val()) {
                    entidadSelects.forEach(function(other) {
                        if (other !== sel) $(other).val('');
                    });
                }
            });
        });

        function exportarCSV() {
            const rows = [['Fecha','Servicio','Tipo','Monto','Moneda','Cta Origen','Cta Destino','Cliente','Proveedor','Prestamista','Promotor','Descripcion','Estado','Registro']];
            datosCompletos.forEach(function(m) {
                rows.push([
                    formatFecha(m.fecha),
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
                    (m.descripcion || '').replace(/,/g, ';'),
                    m.estado || '',
                    m.usuario_nombre || ''
                ]);
            });
            const csv = rows.map(function(r) { return r.join(','); }).join('\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'movimientos_' + new Date().toISOString().slice(0,10) + '.csv';
            link.click();
        }

        $(document).ready(function() {
            cargarServicios();
            cargarEntidades();

            tabla = $('#tablaMovimientos').DataTable({
                ajax: {
                    url: construirURL(),
                    dataSrc: function(json) {
                        datosCompletos = json;
                        calcularKPIs(json);
                        return json;
                    }
                },
                columns: [
                    {
                        data: 'fecha',
                        render: function(data) { return formatFecha(data); }
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
                        data: 'moneda',
                        render: function(d) {
                            if (!d) return '';
                            const colors = { 'MXN': 'primary', 'USD': 'success', 'USDT': 'info' };
                            const cls = colors[d] || 'secondary';
                            return '<span class="badge bg-' + cls + ' bg-opacity-10 text-' + cls + ' border-0">' + d + '</span>';
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
                }
            });
        });
    </script>
</body>
</html>
