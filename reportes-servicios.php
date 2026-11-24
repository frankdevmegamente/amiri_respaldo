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
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }

        /* ─── Page Header ─── */
        .servicios-header { position:relative; overflow:hidden; border-radius:16px !important; }
        .servicios-header .bg-pattern { position:absolute; top:-50%; right:-10%; width:400px; height:400px; border-radius:50%; background:rgba(197,143,184,0.08); pointer-events:none; }
        .servicios-header .bg-pattern-2 { position:absolute; bottom:-30%; left:20%; width:250px; height:250px; border-radius:50%; background:rgba(197,143,184,0.08); pointer-events:none; }
        .servicios-header h2 { font-size:1.75rem; font-weight:700; margin-bottom:0.25rem; }
        .servicios-header .subtitle { font-size:0.85rem; color:#9a9096; }

        /* ─── Metric Cards ─── */
        .metric-card { border-radius:12px; overflow:hidden; transition:all 0.2s ease; position:relative; cursor:pointer; }
        .metric-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,0.08) !important; }
        .metric-card .metric-accent { position:absolute; top:0; left:0; right:0; height:3px; }
        .metric-card .card-body { padding:1rem 1.1rem; }
        .metric-card .metric-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.3px; color:#9a9096; font-weight:600; }
        .metric-card .metric-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:14px; flex-shrink:0; }
        .metric-card .metric-value { font-size:1.25rem; font-weight:700; color: var(--nx-text); line-height:1.2; margin-top:2px; }
        .metric-card .metric-footer { font-size:0.68rem; color:#9a9096; margin-top:6px; }
        .metric-card.selected { outline:2px solid #c58fb8; outline-offset:-2px; box-shadow:0 4px 16px rgba(197,143,184,0.2)!important; }
        .metric-card.selected .metric-accent { height:4px; }

        /* ─── Chart / Table Cards ─── */
        .section-card { border-radius:14px; overflow:hidden; }
        .section-card .card-header { padding:1rem 1.25rem; background:transparent; border-bottom-color:rgba(0,0,0,0.04); }
        .section-card .card-header h6 { font-size:0.85rem; font-weight:600; }
        .chart-container { position:relative; height:300px; }
        .chart-container-clickable { cursor:pointer; }

        /* ─── Dark Mode ─── */
        [data-bs-theme="dark"] .metric-card .metric-value { color:#ffffff; }
        [data-bs-theme="dark"] .metric-card { background:var(--nx-surface-2) !important; }
        [data-bs-theme="dark"] .metric-card .metric-label { color:#9a9096; }
        [data-bs-theme="dark"] .metric-card .metric-footer { color:#9a9096; }
        [data-bs-theme="dark"] .section-card { background:var(--nx-surface-2) !important; }
        [data-bs-theme="dark"] .section-card .card-header { border-bottom-color:rgba(255,255,255,0.04); }
        [data-bs-theme="dark"] .servicios-header .subtitle { color:#9a9096; }

        [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro { display: block !important; }

        .animating { animation:fadeInUp 0.4s ease forwards; opacity:0; }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
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
                <!-- ═══ PAGE HEADER ═══ -->
                <div class="card servicios-header shadow-sm border-0 rounded-4 mb-4">
                    <div class="bg-pattern"></div>
                    <div class="bg-pattern-2"></div>
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md">
                                <div class="d-flex align-items-center gap-3 mb-1">
                                    <div class="d-flex align-items-center justify-content-center rounded-3"
                                         style="width:48px;height:48px;background:rgba(197,143,184,0.12);">
                                        <i class="ti ti-clipboard-list text-primary" style="font-size:1.6rem;"></i>
                                    </div>
                                    <div>
                                        <h2 class="mb-0">Reporte de Servicios</h2>
                                        <span class="subtitle"><i class="ti ti-circle" style="font-size:6px;vertical-align:middle;margin-right:4px;"></i>Monitoreo detallado de operaciones por servicio</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width:auto;">
                                        <span class="input-group-text bg-transparent"><i class="ti ti-calendar"></i></span>
                                        <select class="form-select form-select-sm" id="selectPeriodo" style="min-width:140px;">
                                            <option value="mes" selected>Este Mes</option>
                                            <option value="mes_pasado">Mes Pasado</option>
                                            <option value="anio">Este Año</option>
                                            <option value="rango">Fecha Personalizada</option>
                                        </select>
                                    </div>
                                    <div id="fechaInicioGroup" style="display:none;">
                                        <input type="date" class="form-control form-control-sm" id="fechaInicio" style="width:140px;">
                                    </div>
                                    <div id="fechaFinGroup" style="display:none;">
                                        <input type="date" class="form-control form-control-sm" id="fechaFin" style="width:140px;">
                                    </div>
                                    <button class="btn btn-primary btn-sm" id="btnFiltrar">
                                        <i class="ti ti-filter"></i> Aplicar
                                    </button>
                                    <span class="filter-badge-periodo small text-muted" id="rangoFechas"><i class="ti ti-calendar"></i> Este Mes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ═══ KPI CARDS ═══ -->
                <div class="row g-3 mb-4" id="cardsResumen"></div>

                <!-- ═══ CHART ═══ -->
                <div class="card section-card shadow-sm border-0 mb-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0"><i class="ti ti-chart-bar text-warning me-2"></i>Operaciones por Día</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3"><i class="ti ti-click me-1"></i>Haz clic en una barra para ver el detalle de operaciones de ese día</p>
                        <div class="chart-container chart-container-clickable">
                            <canvas id="chartServicioDiario"></canvas>
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
                            <tr id="theadDetalleDiaRow">
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-end">Monto</th>
                                <th>Moneda</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleDia"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr id="tfootDetalleDiaRow">
                                <td colspan="3">TOTAL</td>
                                <td class="text-end" id="totalDetalleDia">$0.00</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detalle de Operación (desglose contable) -->
<div class="modal fade modal-premium" id="modalDetalleOperacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-file-description me-2"></i><span id="modalDetalleOperacionLabel">Detalle de Operación</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="detalleOperacionSummary" style="display:none;"></div>
                <div class="table-responsive mt-2">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th class="text-end">Monto</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleOperacion"></tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="text-end" id="totalDetalleOperacion">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>if (window.nxChartTheme) window.nxChartTheme();</script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="src/assets/js/utils.js"></script>
<script src="src/assets/js/pantalla-completa.js"></script>
<script>
let chartServicioDiario = null;
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

const resumenCardStyles = [
    { icon: 'ti ti-cash', color: 'success' },
    { icon: 'ti ti-cash-off', color: 'danger' },
    { icon: 'ti ti-transfer-in', color: 'info' },
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

function crearTarjetaResumen(nombre, total, cantidad, icon, color, delay, servicioId, subtitulo, totalMxn, ganancia) {
    var iconClass = icon || 'ti ti-cash';
    var colorClass = color || 'primary';
    var delayStyle = delay !== undefined ? ' style="animation-delay:' + delay + 's;"' : '';
    var sid = servicioId || 0;
    var subHtml = subtitulo ? '<div class="metric-subtitle text-muted small">' + subtitulo + '</div>' : '';
    var montoHtml = '<span class="d-block">$' + formatoNumero(total) + '</span>';
    if (totalMxn !== undefined && totalMxn !== null) {
        montoHtml += '<span class="d-block small text-muted mt-1"><i class="ti ti-rotate-clockwise"></i> $' + formatoNumero(totalMxn) + ' MXN recibidos</span>';
    }
    if (ganancia !== undefined && ganancia !== null && totalMxn !== undefined && totalMxn !== null && ganancia !== 0) {
        var reposicion = totalMxn - ganancia;
        var gananciaClass = ganancia >= 0 ? 'text-success' : 'text-danger';
        montoHtml += '<div class="d-block small mt-1" style="padding-left:1rem;border-left:2px solid var(--nx-border);">' +
            '<span class="d-block text-muted"><i class="ti ti-recycle"></i> $' + formatoNumero(reposicion) + ' reposición inventario</span>' +
            '<span class="d-block ' + gananciaClass + ' fw-bold"><i class="ti ti-trending-up"></i> $' + formatoNumero(ganancia) + ' utilidad Amiri</span>' +
            '</div>';
    } else if (ganancia !== undefined && ganancia !== null && ganancia !== 0) {
        var gananciaClass = ganancia >= 0 ? 'text-success' : 'text-danger';
        montoHtml += '<span class="d-block small ' + gananciaClass + ' fw-bold mt-1"><i class="ti ti-trending-up"></i> Utilidad: $' + formatoNumero(ganancia) + '</span>';
    }
    return '<div class="col-xl-3 col-lg-4 col-md-6 mb-3">' +
        '<div class="card card-stat metric-card border-0 shadow-sm h-100 animating"' + delayStyle +
        ' data-servicio-id="' + sid + '" onclick="seleccionarServicio(' + sid + ',\'' + nombre.replace(/'/g, "\\'") + '\')">' +
        '<div class="metric-accent bg-' + colorClass + '"></div>' +
        '<div class="card-body p-3">' +
        '<div class="d-flex justify-content-between align-items-start mb-2">' +
        '<span class="metric-label">' + nombre + '</span>' +
        '<div class="metric-icon bg-' + colorClass + ' bg-opacity-10 text-' + colorClass + '">' +
        '<i class="' + iconClass + '"></i>' +
        '</div>' +
        '</div>' +
        subHtml +
        '<div class="metric-value">' + montoHtml + '</div>' +
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
            // Tarjetas por cada servicio
            if (data.servicios && data.servicios.length > 0) {
                data.servicios.forEach(function(s, i) {
                    if (s.id == 1) {
                        html += crearTarjetaResumen(s.servicio, s.total, s.cantidad, 'ti ti-building-store', 'primary', idx * 0.05, s.id, 'Total de venta en efectivo (antes de comisiones)');
                        idx++;
                        return;
                    }
                    var style = resumenCardStyles[idx % resumenCardStyles.length];
                    var esDivisaVenta = (s.id == 2 || s.id == 3);
                    html += crearTarjetaResumen(s.servicio, s.total, s.cantidad, style.icon, style.color, idx * 0.05, s.id, null, esDivisaVenta ? s.total_mxn : null, esDivisaVenta ? s.ganancia : null);
                    idx++;
                });
            }
            $('#cardsResumen').html(html);
            $('#rangoFechas').html('<i class="ti ti-calendar"></i> ' + $('#selectPeriodo option:selected').text());
            // Re-aplicar selección previa después de refrescar las cards
            if (selectedServicioId) {
                $('.metric-card').removeClass('selected');
                $('.metric-card[data-servicio-id="' + selectedServicioId + '"]').addClass('selected');
            } else {
                seleccionarServicio(1, 'Venta de Efectivo');
            }
        })
        .fail(function() {
            mostrarError('Error al cargar el resumen');
        })
        .always(function() { mostrarCarga(false); });
}

function seleccionarServicio(id, nombre) {
    selectedServicioId = id;
    selectedServicioNombre = nombre || '';
    // Quitar .selected de todas las cards y ponerla en la clickeada
    $('.metric-card').removeClass('selected');
    $('.metric-card[data-servicio-id="' + id + '"]').addClass('selected');
    // Si es 0 (Total Depositado), no filtrar
    if (id === 0) {
        selectedServicioId = null;
        selectedServicioNombre = '';
    }
    cargarGraficaServicio();
}

function cargarGraficaServicio() {
    const servicioId = selectedServicioId;
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
            gradient.addColorStop(0, 'rgba(197,143,184,0.85)');
            gradient.addColorStop(1, 'rgba(197,143,184,0.2)');

            chartServicioDiario = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dias.map(function(d) { return 'Día ' + d; }),
                    datasets: [{
                        label: 'Monto',
                        data: valores,
                        backgroundColor: gradient,
                        borderColor: 'rgba(197,143,184,0.5)',
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
                            backgroundColor: 'rgba(11,11,12,0.92)',
                            titleColor: '#ffffff',
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
                            grid: { color: nxChartGridColor('rgba(0,0,0,0.04)'), drawBorder: false },
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
    const servicioId = selectedServicioId;
    const nombreServicio = selectedServicioNombre;
    if (!servicioId) return;

    mostrarCarga(true);
    $('#modalServicioTitulo').text(nombreServicio);
    $('#modalDiaTitulo').text(dia);

    // Obtener mes/año del período seleccionado (misma lógica que getParams)
    const periodo = $('#selectPeriodo').val();
    let anio, mes;
    if (periodo === 'mes_pasado') {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        anio = d.getFullYear();
        mes = String(d.getMonth() + 1).padStart(2, '0');
    } else if (periodo === 'rango') {
        const fi = $('#fechaInicio').val();
        if (fi) {
            const p = fi.split('-');
            anio = p[0]; mes = p[1];
        } else {
            const d = new Date();
            anio = d.getFullYear();
            mes = String(d.getMonth() + 1).padStart(2, '0');
        }
    } else {
        const d = new Date();
        anio = d.getFullYear();
        mes = String(d.getMonth() + 1).padStart(2, '0');
    }
    const diaStr = String(dia).padStart(2, '0');
    const fecha = `${anio}-${mes}-${diaStr}`;

    $.getJSON(`api/reportes-servicios.php?tipo=operaciones&servicio_id=${servicioId}&periodo=rango&fecha_inicio=${fecha}&fecha_fin=${fecha}`)
        .done(function(data) {
            const tieneBotonDetalle = [1, 2, 3, 5, 6].indexOf(servicioId) !== -1;

            // ─── Dynamic headers ───
            if (tieneBotonDetalle) {
                $('#theadDetalleDiaRow').html(
                    '<th>#</th><th>Fecha</th><th>Cliente</th><th class="text-end">Monto</th><th>Moneda</th><th class="text-center">Acción</th>'
                );
            } else {
                $('#theadDetalleDiaRow').html(
                    '<th>#</th><th>Fecha</th><th>Tipo</th><th>Origen / Destino</th><th class="text-end">Monto</th><th>Moneda</th>'
                );
            }

            let html = '';
            let total = 0;
            data.forEach(function(r, idx) {
                const monto = parseFloat(r.monto_total || r.monto) || 0;
                total += monto;
                const moneda = r.moneda || 'MXN';
                const opId = r.id;
                var badgeMoneda = moneda === 'MXN'
                    ? 'bg-success bg-opacity-10 text-success border-0'
                    : (moneda === 'USD'
                        ? 'bg-primary bg-opacity-10 text-primary border-0'
                        : 'bg-info bg-opacity-10 text-info border-0');

                if (tieneBotonDetalle) {
                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td>' + (r.fecha || '-') + '</td>' +
                        '<td>' + (r.cliente || '-') + '</td>' +
                        '<td class="text-end text-amount positivo">' + formatoMoneda(monto, moneda) + '</td>' +
                        '<td><span class="badge ' + badgeMoneda + '">' + moneda + '</span></td>' +
                        '<td class="text-center"><button class="btn btn-sm btn-outline-primary" onclick="abrirDetalleOperacion(' + servicioId + ', ' + opId + ')"><i class="ti ti-eye"></i></button></td>' +
                        '</tr>';
                } else {
                    var badgeTipo = r.tipo === 'entrada'
                        ? 'bg-success bg-opacity-10 text-success border-0'
                        : (r.tipo === 'salida'
                            ? 'bg-danger bg-opacity-10 text-danger border-0'
                            : (r.tipo === 'traspaso'
                                ? 'bg-info bg-opacity-10 text-info border-0'
                                : 'bg-secondary bg-opacity-10 text-secondary border-0'));
                    var entidad = '';
                    if (r.tipo === 'traspaso') {
                        if (r.cuenta_origen && r.cuenta_origen !== '-') entidad += 'Salió de: ' + r.cuenta_origen;
                        if (r.cuenta_destino && r.cuenta_destino !== '-') entidad += (entidad ? '<br>' : '') + 'Entró a: ' + r.cuenta_destino;
                        if (!entidad) entidad = r.descripcion && r.descripcion !== '-' ? r.descripcion : '-';
                    } else {
                        entidad = r.cliente && r.cliente !== '-' ? r.cliente
                            : (r.promotor && r.promotor !== '-' ? r.promotor
                                : (r.proveedor && r.proveedor !== '-' ? r.proveedor
                                    : (r.descripcion && r.descripcion !== '-' ? r.descripcion : '-')));
                    }
                    var signoClase = monto >= 0 ? 'positivo' : 'negativo';
                    var signo = monto < 0 ? '-' : '';
                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td>' + (r.fecha || '-') + '</td>' +
                        '<td><span class="badge ' + badgeTipo + '">' + (r.tipo || '-') + '</span></td>' +
                        '<td>' + entidad + '</td>' +
                        '<td class="text-end text-amount ' + signoClase + '">' + signo + formatoMoneda(Math.abs(monto), moneda) + '</td>' +
                        '<td><span class="badge ' + badgeMoneda + '">' + moneda + '</span></td>' +
                        '</tr>';
                }
            });

            // ─── Dynamic footer (oculto) ───
            $('#tfootDetalleDiaRow').html('');

            if (!html) {
                var colspan = 6;
                $('#bodyDetalleDia').html('<tr><td colspan="' + colspan + '" class="text-center text-muted py-3">No hay operaciones este d&iacute;a</td></tr>');
            } else {
                $('#bodyDetalleDia').html(html);
            }
            $('#modalDetalleDia').modal('show');
        })
        .fail(function() {
            mostrarError('Error al cargar operaciones del día');
        })
        .always(function() { mostrarCarga(false); });
}

function abrirDetalleOperacion(servicioId, operacionId) {
    // Cerrar modal del día primero para evitar conflicto de backdrops
    $('#modalDetalleDia').one('hidden.bs.modal', function() {
        const nombreServicio = selectedServicioNombre || '';
        $('#modalDetalleOperacionLabel').text('Detalle - ' + nombreServicio);
        mostrarCarga(true);
        $.getJSON(`api/reportes-servicios.php?tipo=detalle_operacion&servicio_id=${servicioId}&operacion_id=${operacionId}`)
            .done(function(data) {
                $('#detalleOperacionSummary').html('');

                // ─── Movimientos table ───
                var movs = data.movimientos || data;
                var html = '';
                var total = 0;
                (movs).forEach(function(r) {
                    const monto = parseFloat(r.monto) || 0;
                    total += monto;
                    var badgeTipo = r.tipo === 'entrada'
                        ? 'bg-success bg-opacity-10 text-success border-0'
                        : (r.tipo === 'salida'
                            ? 'bg-danger bg-opacity-10 text-danger border-0'
                            : (r.tipo === 'comision'
                                ? 'bg-warning bg-opacity-10 text-warning border-0'
                                : 'bg-info bg-opacity-10 text-info border-0'));
                    html += '<tr>' +
                        '<td>' + formatearFechaHora(r.creado_en) + '</td>' +
                        '<td><span class="badge ' + badgeTipo + '">' + r.tipo + '</span></td>' +
                        '<td class="text-end text-amount ' + (monto >= 0 ? 'positivo' : 'negativo') + '">$' + formatoNumero(monto) + '</td>' +
                        '<td>' + (r.descripcion || '-') + '</td>' +
                        '</tr>';
                });
                $('#bodyDetalleOperacion').html(html || '<tr><td colspan="4" class="text-center">Sin detalle disponible</td></tr>');
                $('#totalDetalleOperacion').parent().hide();
                $('#modalDetalleOperacion').modal('show');
            })
            .fail(function() {
                mostrarError('Error al cargar detalle de operación');
            })
            .always(function() { mostrarCarga(false); });
    }).modal('hide');
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
    cargarResumen();
    if (selectedServicioId) { cargarGraficaServicio(); }
});

$('#fechaInicio, #fechaFin').on('change', function() {
    if ($('#selectPeriodo').val() === 'rango') {
        cargarResumen();
        if (selectedServicioId) { cargarGraficaServicio(); }
    }
});

$('#btnFiltrar').on('click', function() {
    selectedServicioId = null;
    selectedServicioNombre = '';
    $('.metric-card').removeClass('selected');
    if (chartServicioDiario) { chartServicioDiario.destroy(); chartServicioDiario = null; }
    cargarResumen();
});

// Interceptor global de 401: si la API responde no autorizado, redirigir al login
$(document).ajaxError(function(event, jqxhr) {
    if (jqxhr.status === 401) {
        window.location.href = 'login.php';
    }
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
