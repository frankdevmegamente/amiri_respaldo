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
    <title>Servicios (v2) - Amiri</title>
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
        .chart-container { position: relative; height: 260px; }
        .chart-container-clickable { cursor: pointer; }
        .table-reportes th { background-color: #f8f9fa; font-weight: 600; }
        [data-bs-theme="dark"] .table-reportes th { background-color: #1e293b; }
        .metric-card-serv { transition: all 0.2s ease; position: relative; overflow: hidden; cursor: pointer; }
        .metric-card-serv:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.07) !important; }
        [data-bs-theme="dark"] .metric-card-serv:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important; }
        .metric-card-serv .top-strip { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 0.75rem 0.75rem 0 0; }
        .metric-card-serv.active { border-color: var(--nx-primary) !important; box-shadow: 0 0 0 2px rgba(93,135,255,0.3) !important; }
        .metric-card-serv.active .top-strip { height: 4px !important; }
        .svc-icon { width: 32px; height: 32px; }
        .svc-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--nx-text-muted); line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        [data-bs-theme="dark"] .svc-label { color: #8b92a8; }
        .svc-value { font-size: 1.1rem; font-weight: 800; color: var(--nx-text); line-height: 1.2; letter-spacing: -0.2px; }
        [data-bs-theme="dark"] .svc-value { color: #e8ecf0; }
        .svc-footer { font-size: 0.65rem; color: #8b92a8; padding-top: 4px; margin-top: 4px; border-top: 1px solid #eef0f4; }
        [data-bs-theme="dark"] .svc-footer { color: #6b7280; border-top-color: #2d3140; }
        [data-bs-theme="dark"] .metric-card-serv { background-color: #1f2535 !important; border-color: #2b3350 !important; }
        [data-bs-theme="dark"] .metric-card-serv .svc-icon { background-color: rgba(255,255,255,0.06) !important; }
        .metric-card-serv[data-servicio-id]:hover { border-color: #cbd5e1 !important; }
        [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro { display: block !important; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
    data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php include 'src/assets/partials/header.php'; ?>
    <?php include 'src/assets/partials/sidebar.php'; ?>
    <div class="body-wrapper">
        <div class="body-wrapper-inner">
            <div class="container-fluid px-4" style="max-width: 100%;">
                <!-- Header + Filtros Combinados -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body px-4 py-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">
                                                <i class="ti ti-clipboard-list fs-6"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-semibold mb-0" style="font-size:1rem;">Reporte de Servicios</h5>
                                                <p class="text-muted mb-0" style="font-size:0.75rem;">Operaciones por servicio — cards, gráfica diaria y tabla de movimientos</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                                            <div style="min-width:130px;">
                                                <select class="form-select form-select-sm" id="selectPeriodo">
                                                    <option value="mes" selected>Este Mes</option>
                                                    <option value="mes_pasado">Mes Pasado</option>
                                                    <option value="anio">Este Año</option>
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
                <!-- Tarjetas de Resumen -->
                <div class="row mb-3" id="cardsResumen"></div>
                <!-- Gráfica de Operaciones por Día -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent py-2">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h6 class="fw-semibold mb-0" style="font-size:0.85rem;"><i class="ti ti-chart-bar text-warning me-2"></i>Operaciones por Día</h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border-0" id="chartServicioLabel">Todos los servicios</span>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <p class="text-muted small mb-2">Haz clic en una barra para ver el detalle de las operaciones de ese día</p>
                                <div class="chart-container chart-container-clickable">
                                    <canvas id="chartServicioDiario"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Todos los Movimientos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent py-2">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-semibold mb-0" style="font-size:0.85rem;"><i class="ti ti-list me-2"></i>Todos los Movimientos</h6>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border-0" id="tablaServicioLabel" style="display:none;"></span>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <select class="form-select form-select-sm w-auto" id="selectTipoOperacion">
                                            <option value="todos">Todos</option>
                                            <option value="retiros">Retiros</option>
                                            <option value="depositos">Depósitos</option>
                                            <option value="transferencias">Transferencias</option>
                                        </select>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-download"></i> Exportar
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="exportarReporte('excel')"><i class="ti ti-file-spreadsheet text-success"></i> Excel</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="exportarReporte('csv')"><i class="ti ti-file-text text-info"></i> CSV</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="exportarReporte('pdf')"><i class="ti ti-file-text text-danger"></i> PDF</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover table-reportes" id="tablaMovimientos">
                                        <thead>
                                            <tr>
                                                <th data-priority="1" class="no-capitalize" style="width:36px;">#</th>
                                                <th data-priority="1">Fecha</th>
                                                <th data-priority="1">Servicio</th>
                                                <th data-priority="2">Tipo</th>
                                                <th data-priority="3">Moneda</th>
                                                <th data-priority="1" class="text-end">Monto</th>
                                                <th data-priority="4">Cliente</th>
                                                <th data-priority="4">Promotor</th>
                                                <th data-priority="4">Proveedor</th>
                                                <th data-priority="5">Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyMovimientos"></tbody>
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
<!-- Modal Detalle del Día -->
<div class="modal fade modal-premium" id="modalDetalleDia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
		<h5 class="modal-title"><i class="ti ti-list-details me-2"></i>Detalle <span id="modalServicioTitulo"></span> - Día <span id="modalDiaTitulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Entidad</th>
                                <th>Tipo</th>
                                <th class="text-end">Monto</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleDia"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3">TOTAL</td>
                                <td class="text-end" id="totalDetalleDia">$0.00</td>
                                <td></td>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="src/assets/js/utils.js"></script>
<script src="src/assets/js/pantalla-completa.js"></script>
<script>
let chartServicioDiario = null;
let dtMovimientos = null;
let selectedServicioId = null;
let selectedServicioNombre = '';

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

function formatearFechaHora(dt) {
    if (!dt) return '-';
    const d = new Date(dt);
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getParams() {
    const periodo = $('#selectPeriodo').val();
    if (periodo === 'mes_pasado') {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        const anio = d.getFullYear();
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const dias = new Date(anio, d.getMonth() + 1, 0).getDate();
        return `periodo=rango&fecha_inicio=${anio}-${mes}-01&fecha_fin=${anio}-${mes}-${dias}`;
    }
    if (periodo === 'rango') {
        return `periodo=rango&fecha_inicio=${$('#fechaInicio').val()}&fecha_fin=${$('#fechaFin').val()}`;
    }
    return `periodo=${periodo}`;
}

function mostrarCarga(mostrar) {
    $('#loadingOverlay').toggle(mostrar);
}

function cargarResumen() {
    mostrarCarga(true);
    $.getJSON('api/reportes-servicios.php?tipo=resumen&' + getParams())
        .done(function(data) {
            var html = '';
            // Depositado card (always first)
            if (data.depositado) {
                html += '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">' +
                    '<div class="card metric-card-serv border-0 shadow-sm h-100">' +
                    '<div class="top-strip bg-success"></div>' +
                    '<div class="card-body py-2 px-3">' +
                    '<div class="d-flex align-items-center gap-2 mb-1">' +
                    '<div class="svc-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center flex-shrink-0"><i class="ti ti-cash" style="font-size:16px;"></i></div>' +
                    '<span class="svc-label flex-grow-1">Total Depositado</span>' +
                    '</div>' +
                    '<div class="svc-value">$' + formatoNumero(data.depositado.total) + '</div>' +
                    '<div class="svc-footer"><i class="ti ti-repeat"></i> <strong>' + (data.depositado.cantidad || 0) + '</strong> ops</div>' +
                    '</div></div></div>';
            }
            // Cards de servicios
            if (data.servicios && data.servicios.length > 0) {
                var colores = ['primary','info','warning','danger','secondary','dark','success'];
                var iconos = ['ti ti-building','ti ti-currency-dollar','ti ti-coin','ti ti-exchange','ti ti-transfer','ti ti-credit-card','ti ti-wallet','ti ti-businessplan','ti ti-report-money','ti ti-pig-money'];
                data.servicios.forEach(function(s, i) {
                    var c = colores[i % colores.length];
                    var ic = iconos[i % iconos.length];
                    var activo = selectedServicioId && parseInt(selectedServicioId) === parseInt(s.id) ? ' active' : '';
                    html += '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-2">' +
                        '<div class="card metric-card-serv border-0 shadow-sm h-100' + activo + '" data-servicio-id="' + s.id + '" data-servicio-nombre="' + (s.servicio || '') + '">' +
                        '<div class="top-strip bg-' + c + '"></div>' +
                        '<div class="card-body py-2 px-3">' +
                        '<div class="d-flex align-items-center gap-2 mb-1">' +
                        '<div class="svc-icon bg-' + c + ' bg-opacity-10 text-' + c + ' rounded d-flex align-items-center justify-content-center flex-shrink-0"><i class="' + ic + '" style="font-size:16px;"></i></div>' +
                        '<span class="svc-label flex-grow-1">' + (s.servicio || '—') + '</span>' +
                        '</div>' +
                        '<div class="svc-value">$' + formatoNumero(s.total) + '</div>' +
                        '<div class="svc-footer"><i class="ti ti-repeat"></i> <strong>' + (s.cantidad || 0) + '</strong> ops</div>' +
                        '</div></div></div>';
                });
            }
            $('#cardsResumen').html(html);
            $('#rangoFechas').html('<i class="ti ti-calendar me-1"></i>' + $('#selectPeriodo option:selected').text());
            // Auto-seleccionar la primera card si no hay selección activa
            if (!selectedServicioId) {
                var firstCard = $('.metric-card-serv[data-servicio-id]').first();
                if (firstCard.length) {
                    firstCard.trigger('click');
                } else {
                    cargarTablaMovimientos();
                    cargarGraficaServicio(null, 'Todos los servicios');
                }
            }
        })
        .fail(function() {
            mostrarError('Error al cargar el resumen');
        })
        .always(function() { mostrarCarga(false); });
}


function cargarGraficaServicio(servicioId, servicioNombre) {
    if (!servicioId) {
        if (chartServicioDiario) { chartServicioDiario.destroy(); chartServicioDiario = null; }
        $('#chartServicioLabel').text('Todos los servicios');
        return;
    }

    mostrarCarga(true);
    $.getJSON(`api/reportes-servicios.php?tipo=diario_servicio&servicio_id=${servicioId}&${getParams()}`)
        .done(function(data) {
            const dias = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
            $('#chartServicioLabel').text(servicioNombre || 'Todos los servicios');
            const valores = dias.map(function(d) {
                const item = data.find(function(r) { return parseInt(r.dia) === d; });
                return item ? parseFloat(item.total) || 0 : 0;
            });
            const cantidades = dias.map(function(d) {
                const item = data.find(function(r) { return parseInt(r.dia) === d; });
                return item ? parseInt(item.cantidad) || 0 : 0;
            });

            const canvas = document.getElementById('chartServicioDiario');
            if (!canvas) return;
            if (chartServicioDiario) { chartServicioDiario.destroy(); chartServicioDiario = null; }

            const ctx = canvas.getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(93, 135, 255, 0.85)');
            gradient.addColorStop(1, 'rgba(93, 135, 255, 0.2)');

            chartServicioDiario = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dias.map(function(d) { return 'Día ' + d; }),
                    datasets: [{
                        label: 'Monto',
                        data: valores,
                        backgroundColor: gradient,
                        borderColor: 'rgba(93,135,255,0.5)',
                        borderWidth: 1,
                        borderRadius: { topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: function(e, elements) {
                        if (elements.length > 0) {
                            const dia = elements[0].index + 1;
                            const monto = valores[elements[0].index];
                            if (monto > 0) {
                                abrirDetalleDia(dia);
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(20, 25, 40, 0.9)',
                            titleColor: '#e8ecf0',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(items) { return items[0].label; },
                                label: function(context) {
                                    return 'Monto: $' + formatoNumero(context.raw);
                                },
                                afterLabel: function(context) {
                                    var ops = cantidades[context.dataIndex] || 0;
                                    return 'Operaciones: ' + ops;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: {
                                callback: function(v) {
                                    if (v >= 1000) { return '$' + (v / 1000).toFixed(0) + 'k'; }
                                    return '$' + v.toFixed(0);
                                },
                                font: { size: 10 }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, font: { size: 9 }, maxTicksLimit: 15 }
                        }
                    },
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    }
                }
            });
        })
        .fail(function() {
            mostrarError('Error al cargar la gráfica');
        })
        .always(function() { mostrarCarga(false); });
}

function abrirDetalleDia(dia) {
    if (!selectedServicioId) return;

    mostrarCarga(true);
    $('#modalServicioTitulo').text(selectedServicioNombre);
    $('#modalDiaTitulo').text(dia);
    $.getJSON(`api/reportes-servicios.php?tipo=detalle_dia&servicio_id=${selectedServicioId}&dia=${dia}&${getParams()}`)
        .done(function(data) {
            let html = '';
            let total = 0;
            data.forEach(function(r) {
                const monto = parseFloat(r.monto) || 0;
                total += monto;
                var entidad = r.beneficiario
                    ? '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Beneficiario</span> ' + r.beneficiario
                    : (r.prestamista && r.prestamista !== '-'
                        ? '<span class="badge bg-purple bg-opacity-10 text-purple border-0" style="background:rgba(108,92,231,0.1)!important;color:#c58fb8!important;">Prestamista</span> ' + r.prestamista
                        : (r.proveedor && r.proveedor !== '-'
                            ? '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Proveedor</span> ' + r.proveedor
                            : (r.promotor && r.promotor !== '-'
                                ? '<span class="badge bg-info bg-opacity-10 text-info border-0">Promotor</span> ' + r.promotor
                                : (r.cliente && r.cliente !== '-'
                                    ? '<span class="badge bg-primary bg-opacity-10 text-primary border-0">Cliente</span> ' + r.cliente
                                    : (r.referencia_tipo
                                        ? '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Tipo</span> ' + r.referencia_tipo.replace(/_/g, ' ')
                                        : '<span class="text-muted">-</span>')))));
                var badgeTipo = r.tipo === 'entrada'
                    ? 'bg-success bg-opacity-10 text-success border-0'
                    : (r.tipo === 'salida'
                        ? 'bg-danger bg-opacity-10 text-danger border-0'
                        : 'bg-info bg-opacity-10 text-info border-0');
                html += '<tr>' +
                    '<td>' + formatearFechaHora(r.creado_en) + '</td>' +
                    '<td>' + entidad + '</td>' +
                    '<td><span class="badge ' + badgeTipo + '">' + r.tipo + '</span></td>' +
                    '<td class="text-end text-amount ' + (monto >= 0 ? 'positivo' : 'negativo') + '">$' + formatoNumero(monto) + '</td>' +
                    '<td class="desc-cell" title="' + (r.descripcion || '') + '">' + (r.descripcion || '-') + '</td>' +
                    '</tr>';
            });
            $('#bodyDetalleDia').html(html || '<tr><td colspan="5" class="text-center">No hay registros este d&iacute;a</td></tr>');
            $('#totalDetalleDia').text('$' + formatoNumero(total));
            $('#modalDetalleDia').modal('show');
        })
        .fail(function() {
            mostrarError('Error al cargar detalle');
        })
        .always(function() { mostrarCarga(false); });
}

function cargarTablaMovimientos(servicioId) {
    const tipoOperacion = $('#selectTipoOperacion').val();
    mostrarCarga(true);
    var url = `api/reportes-servicios.php?tipo=todos_movimientos&tipo_operacion=${tipoOperacion}&${getParams()}`;
    if (servicioId) url += `&servicio_id=${servicioId}`;
    $.getJSON(url)
        .done(function(data) {
            if (dtMovimientos) { dtMovimientos.destroy(); dtMovimientos = null; }
            let html = '';
            data.forEach(function(r, idx) {
                const monto = parseFloat(r.monto) || 0;
                const moneda = r.moneda || 'MXN';
                const signoClase = r.tipo === 'entrada' || r.tipo === 'deposito' ? 'positivo' : 'negativo';
                const signo = signoClase === 'negativo' ? '-' : '';
                var badgeTipo = r.tipo === 'entrada'
                    ? 'bg-success bg-opacity-10 text-success border-0'
                    : (r.tipo === 'salida'
                        ? 'bg-danger bg-opacity-10 text-danger border-0'
                        : 'bg-info bg-opacity-10 text-info border-0');
                var badgeMoneda = moneda === 'MXN'
                    ? 'bg-success bg-opacity-10 text-success border-0'
                    : (moneda === 'USD'
                        ? 'bg-primary bg-opacity-10 text-primary border-0'
                        : 'bg-info bg-opacity-10 text-info border-0');
                html += '<tr>' +
                    '<td><span class="row-num">' + (idx + 1) + '</span></td>' +
                    '<td>' + formatearFechaHora(r.creado_en) + '</td>' +
                    '<td><span class="badge bg-primary bg-opacity-10 text-primary border-0">' + (r.servicio || '-') + '</span></td>' +
                    '<td><span class="badge ' + badgeTipo + '">' + r.tipo + '</span></td>' +
                    '<td><span class="badge ' + badgeMoneda + '">' + moneda + '</span></td>' +
                    '<td class="text-end text-amount ' + signoClase + '">' + signo + formatoMoneda(monto, moneda) + '</td>' +
                    '<td>' + (r.cliente || '-') + '</td>' +
                    '<td>' + (r.promotor || '-') + '</td>' +
                    '<td>' + (r.proveedor || '-') + '</td>' +
                    '<td class="desc-cell" title="' + (r.descripcion || '') + '">' + (r.descripcion || '-') + '</td>' +
                    '</tr>';
            });
            $('#bodyMovimientos').html(html || '<tr><td colspan="10" class="text-center">No hay movimientos</td></tr>');
            dtMovimientos = $('#tablaMovimientos').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                pageLength: 25,
                order: [[1, 'desc']],
                columnDefs: [
                    { responsivePriority: 1, targets: [0, 1, 2, 5] },
                    { responsivePriority: 2, targets: [3] },
                    { responsivePriority: 3, targets: [4] },
                    { responsivePriority: 4, targets: [6, 7, 8] },
                    { responsivePriority: 5, targets: [9] },
                    { orderable: false, targets: [0] }
                ]
            });
            if (servicioId && selectedServicioNombre) {
                $('#tablaServicioLabel').text('Mostrando: ' + selectedServicioNombre).show();
            } else {
                $('#tablaServicioLabel').hide();
            }
        })
        .fail(function() {
            mostrarError('Error al cargar movimientos');
        })
        .always(function() { mostrarCarga(false); });
}

function exportarReporte(formato) {
    window.location.href = 'api/exportar-reporte.php?reporte=servicios&formato=' + formato + '&' + getParams();
}

function mostrarError(msg) {
    var toast = '<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index:9998;" role="alert">' +
        '<i class="ti ti-alert-circle me-1"></i> ' + msg +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';
    $('body').append(toast);
    setTimeout(function() { $('.alert-danger').alert('close'); }, 5000);
}

$('#selectPeriodo').on('change', function() {
    if ($(this).val() === 'rango') { $('#fechaInicioGroup, #fechaFinGroup').show(); }
    else { $('#fechaInicioGroup, #fechaFinGroup').hide(); }
});

$('#selectTipoOperacion').on('change', function() { cargarTablaMovimientos(selectedServicioId); });

// Card click: toggle service selection for the chart
$(document).on('click', '.metric-card-serv[data-servicio-id]', function() {
    var id = $(this).data('servicio-id');
    var nombre = $(this).data('servicio-nombre');
    if (selectedServicioId && parseInt(selectedServicioId) === parseInt(id)) {
        // Deselect
        selectedServicioId = null;
        selectedServicioNombre = '';
        $('.metric-card-serv[data-servicio-id]').removeClass('active');
        cargarGraficaServicio(null, 'Todos los servicios');
        cargarTablaMovimientos();
    } else {
        // Select this service
        selectedServicioId = id;
        selectedServicioNombre = nombre;
        $('.metric-card-serv[data-servicio-id]').removeClass('active');
        $(this).addClass('active');
        cargarGraficaServicio(id, nombre);
        cargarTablaMovimientos(id);
    }
});

$('#btnFiltrar').on('click', function() {
    selectedServicioId = null;
    selectedServicioNombre = '';
    $('.metric-card-serv[data-servicio-id]').removeClass('active');
    cargarResumen();
});

$(document).ready(function() {
    $('#fechaInicio').val(new Date().toISOString().slice(0, 8) + '01');
    $('#fechaFin').val(new Date().toISOString().slice(0, 10));
    $('body').append('<div id="loadingOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);z-index:9998;"><div class="position-absolute top-50 start-50 translate-middle"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Cargando...</span></div></div></div>');
    cargarResumen();
});
</script>
</body>
</html>
