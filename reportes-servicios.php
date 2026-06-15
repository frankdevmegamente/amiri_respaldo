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
    <title>Servicios - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        .nav-pills .nav-link { border-radius: 8px; padding: 10px 20px; }
        .nav-pills .nav-link.active { background: linear-gradient(135deg, #5D87FF, #49BEFF); }
        .chart-container { position: relative; height: 300px; }
        .chart-container-clickable { cursor: pointer; }
        body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro { display: none !important; }
        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro { display: block !important; }
    </style>
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
                <div class="row mb-4 page-header">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4" style="position:relative;overflow:hidden;">
                            <div style="position:absolute;top:-60px;right:-60px;width:180px;height:180px;background:#5D87FF;opacity:0.08;border-radius:50%;z-index:0;pointer-events:none;"></div>
                            <div style="position:absolute;bottom:-40px;left:-40px;width:120px;height:120px;background:#13deb9;opacity:0.08;border-radius:50%;z-index:0;pointer-events:none;"></div>
                            <div class="card-body p-4" style="position:relative;z-index:1;">
                                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                        <i class="ti ti-clipboard-list fs-5"></i>
                                    </div>
                                    <h5 class="card-title fw-semibold mb-0">Reporte de Servicios</h5>
                                </div>
                                <p class="text-muted mb-0">Monitoreo detallado de retiros de efectivo y operaciones por servicio</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card filter-card shadow-sm">
                            <div class="card-body py-3">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Período</label>
                                        <select class="form-select" id="selectPeriodo">
                                            <option value="mes" selected>Este Mes</option>
                                            <option value="mes_pasado">Mes Pasado</option>
                                            <option value="anio">Este Año</option>
                                            <option value="rango">Fecha Personalizada</option>
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
                                        <span class="filter-badge-periodo" id="rangoFechas"><i class="ti ti-calendar"></i> Este Mes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tarjetas de Resumen -->
                <div class="row mb-4" id="cardsResumen"></div>
                <!-- Gráfica de Operaciones por Día -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <h6 class="fw-semibold mb-0"><i class="ti ti-chart-bar text-warning me-2"></i>Operaciones por Día</h6>
                                <select class="form-select form-select-sm w-auto" id="selectServicioGrafica" style="max-width:250px;">
                                    <option value="">Cargando servicios...</option>
                                </select>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Haz clic en una barra para ver el detalle de las operaciones de ese día</p>
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
                            <div class="card-header bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold mb-0"><i class="ti ti-list me-2"></i>Todos los Movimientos</h6>
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
                            <div class="card-body p-4">
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
<script src="src/assets/js/dark-mode.js"></script>
<script src="src/assets/js/utils.js"></script>
<script src="src/assets/js/pantalla-completa.js"></script>
<script>
let chartServicioDiario = null;
let dtMovimientos = null;

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

const resumenCardStyles = [
    { icon: 'ti ti-cash', color: 'success' },
    { icon: 'ti ti-cash-off', color: 'danger' },
    { icon: 'ti ti-transfer', color: 'info' },
    { icon: 'ti ti-currency-dollar', color: 'warning' },
    { icon: 'ti ti-coin', color: 'primary' },
    { icon: 'ti ti-credit-card', color: 'secondary' },
    { icon: 'ti ti-building-bank', color: 'dark' },
    { icon: 'ti ti-receipt', color: 'danger' },
    { icon: 'ti ti-report-money', color: 'success' },
    { icon: 'ti ti-exchange', color: 'info' },
    { icon: 'ti ti-arrows-left-right', color: 'warning' },
    { icon: 'ti ti-affiliate', color: 'primary' },
    { icon: 'ti ti-pig-money', color: 'secondary' },
    { icon: 'ti ti-wallet', color: 'dark' },
    { icon: 'ti ti-file-invoice', color: 'danger' },
    { icon: 'ti ti-businessplan', color: 'success' },
];

function crearTarjetaResumen(nombre, total, cantidad, icon, color, delay) {
    var iconClass = icon || 'ti ti-cash';
    var colorClass = color || 'primary';
    var delayStyle = delay !== undefined ? ' style="animation-delay:' + delay + 's;"' : '';
    return '<div class="col-xl-3 col-lg-4 col-md-6 mb-3">' +
        '<div class="card card-stat metric-card border-0 shadow-sm h-100 animating"' + delayStyle + '>' +
        '<div class="metric-accent bg-' + colorClass + '"></div>' +
        '<div class="card-body p-3">' +
        '<div class="d-flex justify-content-between align-items-start mb-2">' +
        '<span class="metric-label">' + nombre + '</span>' +
        '<div class="metric-icon bg-' + colorClass + ' bg-opacity-10 text-' + colorClass + '">' +
        '<i class="' + iconClass + '"></i>' +
        '</div>' +
        '</div>' +
        '<div class="metric-value">$' + formatoNumero(total) + '</div>' +
        '<div class="metric-footer">' +
        '<i class="ti ti-repeat"></i> <strong>' + (cantidad || 0) + '</strong> operaciones' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
}

function cargarResumen() {
    mostrarCarga(true);
    $.getJSON('api/reportes-servicios.php?tipo=resumen&' + getParams())
        .done(function(data) {
            var html = '';
            var idx = 0;
            // Tarjeta de depositado (viene de deudas_clientes)
            if (data.depositado) {
                html += crearTarjetaResumen('Total Depositado', data.depositado.total, data.depositado.cantidad, 'ti ti-cash', 'success', idx * 0.05);
                idx++;
            }
            // Tarjetas por cada servicio
            if (data.servicios && data.servicios.length > 0) {
                data.servicios.forEach(function(s, i) {
                    var style = resumenCardStyles[(i + 1) % resumenCardStyles.length];
                    html += crearTarjetaResumen(s.servicio, s.total, s.cantidad, style.icon, style.color, idx * 0.05);
                    idx++;
                });
            }
            $('#cardsResumen').html(html);
            $('#rangoFechas').html('<i class="ti ti-calendar"></i> ' + $('#selectPeriodo option:selected').text());
        })
        .fail(function() {
            mostrarError('Error al cargar el resumen');
        })
        .always(function() { mostrarCarga(false); });
}

function cargarListaServicios() {
    $.getJSON('api/reportes-servicios.php?tipo=lista_servicios')
        .done(function(data) {
            let html = '<option value="">-- Seleccionar servicio --</option>';
            data.forEach(function(s) {
                html += `<option value="${s.id}">${s.servicio}</option>`;
            });
            $('#selectServicioGrafica').html(html);
            $('#selectServicioGrafica').val('7');
            // Dispara el evento change para cargar la gráfica inicial
            $('#selectServicioGrafica').trigger('change');
        })
        .fail(function() {
            $('#selectServicioGrafica').html('<option value="">Error al cargar servicios</option>');
        });
}

function cargarGraficaServicio() {
    const servicioId = $('#selectServicioGrafica').val();
    if (!servicioId) {
        if (chartServicioDiario) { chartServicioDiario.destroy(); chartServicioDiario = null; }
        return;
    }

    mostrarCarga(true);
    $.getJSON(`api/reportes-servicios.php?tipo=diario_servicio&servicio_id=${servicioId}&${getParams()}`)
        .done(function(data) {
            const dias = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
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
            var gradient = ctx.createLinearGradient(0, 0, 0, 300);
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
    const servicioId = $('#selectServicioGrafica').val();
    const nombreServicio = $('#selectServicioGrafica option:selected').text();
    if (!servicioId) return;

    mostrarCarga(true);
    $('#modalServicioTitulo').text(nombreServicio);
    $('#modalDiaTitulo').text(dia);
    $.getJSON(`api/reportes-servicios.php?tipo=detalle_dia&servicio_id=${servicioId}&dia=${dia}&${getParams()}`)
        .done(function(data) {
            let html = '';
            let total = 0;
            data.forEach(function(r) {
                const monto = parseFloat(r.monto) || 0;
                total += monto;
                var entidad = r.beneficiario
                    ? '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Beneficiario</span> ' + r.beneficiario
                    : (r.prestamista && r.prestamista !== '-'
                        ? '<span class="badge bg-purple bg-opacity-10 text-purple border-0" style="background:rgba(108,92,231,0.1)!important;color:#6c5ce7!important;">Prestamista</span> ' + r.prestamista
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

function cargarTablaMovimientos() {
    const tipoOperacion = $('#selectTipoOperacion').val();
    mostrarCarga(true);
    $.getJSON(`api/reportes-servicios.php?tipo=todos_movimientos&tipo_operacion=${tipoOperacion}&${getParams()}`)
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

$('#selectServicioGrafica').on('change', function() {
    cargarGraficaServicio();
});

$('#selectTipoOperacion').on('change', function() { cargarTablaMovimientos(); });

$('#btnFiltrar').on('click', function() {
    cargarResumen(); cargarGraficaServicio(); cargarTablaMovimientos();
});

$(document).ready(function() {
    $('#fechaInicio').val(new Date().toISOString().slice(0, 8) + '01');
    $('#fechaFin').val(new Date().toISOString().slice(0, 10));
    $('body').append('<div id="loadingOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);z-index:9998;"><div class="position-absolute top-50 start-50 translate-middle"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Cargando...</span></div></div></div>');
    cargarResumen(); cargarListaServicios(); cargarTablaMovimientos();
});
</script>
</body>
</html>
