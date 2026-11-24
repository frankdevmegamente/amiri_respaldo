<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$pagina_actual = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FIFO - Sistema Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>
        .body-wrapper {
            padding-top: 100px !important;
        }
        .body-wrapper-inner,
        .body-wrapper .container-fluid {
            padding-top: 15px !important;
            margin-top: 0 !important;
        }

        .kpi-card {
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
        }
        .kpi-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .kpi-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--nx-text-muted);
            line-height: 1.3;
        }
        .kpi-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--nx-text);
            line-height: 1.1;
            letter-spacing: -0.3px;
        }
        [data-bs-theme="dark"] .kpi-value {
            color: #ffffff;
        }
        [data-bs-theme="dark"] .kpi-label {
            color: var(--nx-text-muted);
        }

        .nav-moneda .nav-link {
            border: none;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 10px 10px 0 0;
            color: var(--nx-text-muted);
        }
        .nav-moneda .nav-link.active {
            color: #fff;
            background: #c58fb8;
        }
        .nav-moneda .nav-link:not(.active):hover {
            color: #c58fb8;
            background: rgba(197, 143, 184, 0.06);
        }

        .nav-subtabs .nav-link {
            border: none;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 8px;
            color: var(--nx-text-muted);
        }
        .nav-subtabs .nav-link.active {
            color: #fff;
            background: #c58fb8;
        }
        .nav-subtabs .nav-link:not(.active):hover {
            color: #c58fb8;
            background: rgba(197, 143, 184, 0.06);
        }

        /* FIFO dark table overrides removed - Helios */
        [data-bs-theme="dark"] .table-dark th {
            background-color: var(--nx-border) !important;
            color: var(--nx-text) !important;
        }

        .month-nav-btn {
            background: none;
            border: 1px solid var(--nx-border);
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            color: var(--nx-text-muted);
            transition: all 0.2s ease;
        }
        .month-nav-btn:hover {
            background: var(--nx-surface-2);
            color: #c58fb8;
            border-color: #c58fb8;
        }
        [data-bs-theme="dark"] .month-nav-btn {
            border-color: var(--nx-border);
            color: var(--nx-text-muted);
        }
        [data-bs-theme="dark"] .month-nav-btn:hover {
            background: var(--nx-border);
            color: #c58fb8;
            border-color: #c58fb8;
        }
        #fifoLoading {
            transition: opacity 0.3s ease;
        }
        .consumo-table td, .consumo-table th {
            padding: 2px 8px;
            font-size: 0.85em;
        }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include __DIR__ . '/src/assets/partials/header.php'; ?>
        <?php include __DIR__ . '/src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- Page Header -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4" style="position: relative; overflow: hidden;">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>

                        <div class="card-body p-4" style="position: relative; z-index: 1;">
                            <div class="row align-items-center g-3 mb-4">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="ti ti-currency-tenge fs-4"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: var(--nx-text);">Módulo FIFO</h4>
                                            <small class="text-muted">Control de inventarios y ventas USDT / USD</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select form-select-sm" id="selectPeriodo" style="min-width:130px;">
                                            <option value="todo" selected>Todo el período</option>
                                            <option value="mes">Este Mes</option>
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
                                        <button class="btn btn-sm btn-outline-primary" onclick="cargarMonedaActiva()">
                                            <i class="ti ti-refresh me-1"></i>Actualizar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI Cards -->
                            <div class="row g-3" id="kpiRow">
                                <div class="col-md-3">
                                    <div class="card metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-primary"></div>
                                        <div class="card-body d-flex align-items-center gap-3 p-3">
                                            <div class="kpi-icon" style="background: rgba(197,143,184,0.14); color: var(--nx-primary);"><i class="ti ti-currency-tenge"></i></div>
                                            <div>
                                                <div class="kpi-label">USDT Disponible</div>
                                                <div class="kpi-value" id="kpiUsdtDisponible">0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-primary"></div>
                                        <div class="card-body d-flex align-items-center gap-3 p-3">
                                            <div class="kpi-icon" style="background: rgba(61,220,132,0.14); color: var(--nx-success);"><i class="ti ti-currency-dollar"></i></div>
                                            <div>
                                                <div class="kpi-label">USD Disponible</div>
                                                <div class="kpi-value" id="kpiUsdDisponible">0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-primary"></div>
                                        <div class="card-body d-flex align-items-center gap-3 p-3">
                                            <div class="kpi-icon" style="background: rgba(251,191,36,0.14); color: var(--nx-warning);"><i class="ti ti-trending-up"></i></div>
                                            <div>
                                                <div class="kpi-label">Ganancia USDT</div>
                                                <div class="kpi-value" id="kpiGananciaUsdt">$0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card metric-card border-0 shadow-sm h-100"><div class="metric-accent bg-primary"></div>
                                        <div class="card-body d-flex align-items-center gap-3 p-3">
                                            <div class="kpi-icon" style="background: rgba(197,143,184,0.14); color: var(--nx-primary);"><i class="ti ti-trending-up"></i></div>
                                            <div>
                                                <div class="kpi-label">Ganancia USD</div>
                                                <div class="kpi-value" id="kpiGananciaUsd">$0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FIFO Content Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <ul class="nav nav-moneda" id="monedaTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tabUsdt">
                                        <i class="ti ti-currency-tenge me-1"></i>USDT
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tabUsd">
                                        <i class="ti ti-currency-dollar me-1"></i>USD
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <!-- Tab USDT -->
                                <div class="tab-pane fade show active" id="tabUsdt">
                                    <ul class="nav nav-subtabs mb-3" id="subTabsUsdt">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#lotesUsdt">Lotes de Compra</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#ventasUsdt">Ventas FIFO</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="lotesUsdt">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped" id="lotesTableUsdt">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Lote</th>
                                                            <th>Fecha Compra</th>
                                                            <th>USDT Comprados</th>
                                                            <th>Restante</th>
                                                            <th>Precio Compra</th>
                                                            <th>Total MXN</th>
                                                            <th>Descripción</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="ventasUsdt">
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="ventasTableUsdt">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Fecha Venta</th>
                                                            <th>Descripción</th>
                                                            <th>USDT Vendidos</th>
                                                            <th>Total MXN</th>
                                                            <th>Ganancia</th>
                                                            <th>Detalle FIFO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab USD -->
                                <div class="tab-pane fade" id="tabUsd">
                                    <ul class="nav nav-subtabs mb-3" id="subTabsUsd">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#lotesUsd">Lotes de Compra</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#ventasUsd">Ventas FIFO</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="lotesUsd">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped" id="lotesTableUsd">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Lote</th>
                                                            <th>Fecha Compra</th>
                                                            <th>USD Comprados</th>
                                                            <th>Restante</th>
                                                            <th>Precio Compra</th>
                                                            <th>Total MXN</th>
                                                            <th>Descripción</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="ventasUsd">
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="ventasTableUsd">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Fecha Venta</th>
                                                            <th>Descripción</th>
                                                            <th>USD Vendidos</th>
                                                            <th>Total MXN</th>
                                                            <th>Ganancia</th>
                                                            <th>Detalle FIFO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center py-3" id="fifoLoading">
                                <span class="spinner-border spinner-border-sm me-2"></span>Cargando datos FIFO...
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
    <script src="src/assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="src/assets/js/pantalla-completa.js"></script>
    <script src="src/assets/js/utils.js"></script>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <script>
        let dtLotesUsdt = null;
        let dtVentasUsdt = null;
        let dtLotesUsd = null;
        let dtVentasUsd = null;

        function getParams() {
            const periodo = $('#selectPeriodo').val();
            if (periodo === 'todo') return 'periodo=todo';
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

        function fmt(n) {
            return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatoMoneda(value) {
            if (typeof value !== 'number') value = Number(value) || 0;
            return '$' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function badgeEstado(restante, original) {
            restante = parseFloat(restante);
            original = parseFloat(original);
            if (restante <= 0) return '<span class="badge bg-success">Consumido</span>';
            if (restante >= original) return '<span class="badge bg-primary">Disponible</span>';
            return '<span class="badge bg-warning text-dark">Parcial</span>';
        }

        function recargarAmbasMonedas() {
            cargarMoneda('USDT');
            cargarMoneda('USD');
        }

        async function cargarMoneda(moneda) {
            const loading = document.getElementById('fifoLoading');
            loading.style.display = '';
            try {
                let url = 'api/fifo.php?moneda=' + moneda + '&' + getParams();
                const resp = await fetch(url);
                const data = await resp.json();
                if (data.error) { console.error('FIFO error:', data.error); return; }

                const isUsdt = moneda === 'USDT';
                const suffix = isUsdt ? 'Usdt' : 'Usd';

                // Update KPIs (globales, sin filtro)
                if (isUsdt) {
                    document.getElementById('kpiUsdtDisponible').textContent = fmt(data.resumen.total_restante);
                    document.getElementById('kpiGananciaUsdt').textContent = formatoMoneda(data.resumen.total_ganancia);
                } else {
                    document.getElementById('kpiUsdDisponible').textContent = fmt(data.resumen.total_restante);
                    document.getElementById('kpiGananciaUsd').textContent = formatoMoneda(data.resumen.total_ganancia);
                }

                // Populate Lotes table
                const lotesTbody = document.querySelector('#lotesTable' + suffix + ' tbody');
                lotesTbody.innerHTML = '';
                data.lotes.forEach(function(l) {
                    const row = lotesTbody.insertRow();
                    row.innerHTML = '<td>#' + l.id + '</td>' +
                        '<td>' + (l.fecha || '').split(' ')[0] + '</td>' +
                        '<td class="text-end">' + fmt(l.cantidad_original) + '</td>' +
                        '<td class="text-end">' + fmt(l.cantidad_restante) + '</td>' +
                        '<td class="text-end">$' + parseFloat(l.precio_compra).toFixed(2) + '</td>' +
                        '<td class="text-end">' + formatoMoneda(l.total_mxn) + '</td>' +
                        '<td>' + (l.descripcion || '') + '</td>' +
                        '<td>' + badgeEstado(l.cantidad_restante, l.cantidad_original) + '</td>';
                });

                // (Re)init DataTable for Lotes
                if (isUsdt) {
                    if (dtLotesUsdt) { dtLotesUsdt.destroy(); dtLotesUsdt = null; }
                    if (data.lotes.length) {
                        dtLotesUsdt = $('#lotesTableUsdt').DataTable({
                            pageLength: 25,
                            order: [[1, 'desc']],
                            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' }
                        });
                    }
                } else {
                    if (dtLotesUsd) { dtLotesUsd.destroy(); dtLotesUsd = null; }
                    if (data.lotes.length) {
                        dtLotesUsd = $('#lotesTableUsd').DataTable({
                            pageLength: 25,
                            order: [[1, 'desc']],
                            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' }
                        });
                    }
                }

                // Populate Ventas table
                const ventasTbody = document.querySelector('#ventasTable' + suffix + ' tbody');
                ventasTbody.innerHTML = '';
                data.ventas.forEach(function(v) {
                    const gananciaClass = v.ganancia_total >= 0 ? 'text-success' : 'text-danger';
                    let consumosHtml = '<table class="table table-sm table-borderless mb-0 consumo-table">' +
                        '<tr><th>Lote</th><th>Fecha Lote</th><th>' + moneda + '</th><th>Precio Compra</th><th>Precio Venta</th><th>Ganancia</th></tr>';
                    v.consumos.forEach(function(c) {
                        const gClass = c.ganancia >= 0 ? 'text-success' : 'text-danger';
                        consumosHtml += '<tr>' +
                            '<td>#' + c.lote_id + '</td>' +
                            '<td>' + (c.lote_fecha || '').split(' ')[0] + '</td>' +
                            '<td class="text-end">' + fmt(c.cantidad) + '</td>' +
                            '<td class="text-end">$' + c.precio_compra.toFixed(2) + '</td>' +
                            '<td class="text-end">$' + c.precio_venta.toFixed(2) + '</td>' +
                            '<td class="text-end ' + gClass + '">' + formatoMoneda(c.ganancia) + '</td>' +
                        '</tr>';
                    });
                    consumosHtml += '</table>';

                    const row = ventasTbody.insertRow();
                    row.innerHTML = '<td>' + (v.fecha || '').split(' ')[0] + '</td>' +
                        '<td>' + (v.descripcion || '') + '</td>' +
                        '<td class="text-end">' + fmt(v.total_moneda) + '</td>' +
                        '<td class="text-end">' + formatoMoneda(v.total_mxn) + '</td>' +
                        '<td class="text-end ' + gananciaClass + ' fw-bold">' + formatoMoneda(v.ganancia_total) + '</td>' +
                        '<td>' + consumosHtml + '</td>';
                });

                // (Re)init DataTable for Ventas
                if (isUsdt) {
                    if (dtVentasUsdt) { dtVentasUsdt.destroy(); dtVentasUsdt = null; }
                    if (data.ventas.length) {
                        dtVentasUsdt = $('#ventasTableUsdt').DataTable({
                            pageLength: 25,
                            order: [[0, 'desc']],
                            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' }
                        });
                    }
                } else {
                    if (dtVentasUsd) { dtVentasUsd.destroy(); dtVentasUsd = null; }
                    if (data.ventas.length) {
                        dtVentasUsd = $('#ventasTableUsd').DataTable({
                            pageLength: 25,
                            order: [[0, 'desc']],
                            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' }
                        });
                    }
                }

            } catch (err) {
                console.error('Error cargando FIFO:', err);
                document.querySelector('#fifoLoading').innerHTML = '<span class="text-danger">Error al cargar datos FIFO</span>';
            } finally {
                loading.style.display = 'none';
            }
        }

        function cargarMonedaActiva() {
            const activeTab = document.querySelector('#monedaTabs .nav-link.active');
            if (!activeTab) return;
            const href = activeTab.getAttribute('href');
            const moneda = href === '#tabUsdt' ? 'USDT' : 'USD';
            cargarMoneda(moneda);
        }

        // Listen for tab changes to reload data
        document.querySelectorAll('#monedaTabs .nav-link').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                const moneda = e.target.getAttribute('href') === '#tabUsdt' ? 'USDT' : 'USD';
                cargarMoneda(moneda);
            });
        });

        // ─── Period Filter ──────────────────────────────────
        $(document).on('change', '#selectPeriodo', function() {
            if ($(this).val() === 'rango') {
                $('#fechaInicioGroup, #fechaFinGroup').show();
            } else {
                $('#fechaInicioGroup, #fechaFinGroup').hide();
                recargarAmbasMonedas();
            }
        });

        $(document).on('click', '#btnFiltrar', function() {
            recargarAmbasMonedas();
        });

        // Init on load
        document.addEventListener('DOMContentLoaded', function() {
            recargarAmbasMonedas();
        });
    </script>

</body>
</html>
