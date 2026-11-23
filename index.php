<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {

  header('Location: login.php');

  exit;

}

// Los promotores entran directo a su cuenta
if (($_SESSION['usuario_rol'] ?? '') === 'promotor') {

  header('Location: mi-cuenta.php');

  exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

?>

<!doctype html>

<html lang="es">



<head>

  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Dashboard - AmiriApp</title>

  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="src/assets/css/styles.min.css" />

  <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

  <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
  <link rel="stylesheet" href="src/assets/css/nexo.css" />

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>

<style>
      .body-wrapper {
            padding-top: 100px !important; 
        }
        
        .body-wrapper-inner, 
        .body-wrapper .container-fluid {
            padding-top: 15px !important;
            margin-top: 0 !important;
        }
   [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }

        /* =========================================
           Overrides mínimos para Helios (nexo.css maneja el resto)
           ========================================= */
        [data-bs-theme="dark"] .text-gray { color: #f5f2f4 !important; }
        [data-bs-theme="dark"] #usd-mxn,
        [data-bs-theme="dark"] #btc-usd,
        [data-bs-theme="dark"] #eth-usd,
        [data-bs-theme="dark"] #usdt-usd { color: var(--nx-text, #fff) !important; }

            color: white !important;
        }
        [data-bs-theme="dark"] .card-header-tabs .nav-link.active {
            background: rgba(93, 135, 255, 0.3);
        }

        .month-nav-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: transparent;
            color: inherit;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .month-nav-btn:hover {
            background: rgba(93, 135, 255, 0.1);
            border-color: var(--nx-primary);
        }
        [data-bs-theme="dark"] .month-nav-btn {
            border-color: rgba(255,255,255,0.12);
        }
        [data-bs-theme="dark"] .month-nav-btn:hover {
            background: rgba(93, 135, 255, 0.2);
        }
</style>

<body class="nexo-body">

  <div class="nexo-app" id="nexoApp">

    <!-- Header / Sidebar nexo -->
    <?php include 'src/assets/partials/header.php'; ?>
    <?php include 'src/assets/partials/sidebar.php'; ?>

    <main class="nx-main">
      <div class="nx-content">

          <!-- ============================================
               PAGE HEADER
               ============================================ -->
          <div class="card dashboard-header shadow-sm border-0 rounded-4 mb-4">
            <div class="bg-pattern"></div>
            <div class="bg-pattern-2"></div>
            <div class="card-body p-4 position-relative">
              <div class="row align-items-center g-3">
                <div class="col-md-6">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                      <i class="ti ti-dashboard"></i> Panel de Control
                    </span>
                  </div>
                  <h2 class="mb-0 mt-2" id="mesLabel">Cargando...</h2>
                  <span class="mes-badge" id="mesEstado"><i class="ti ti-circle"></i> Cargando...</span>
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center justify-content-md-end gap-2">
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
                    <button class="btn btn-outline-primary btn-sm" id="btnPDF" title="Generar PDF">
                      <i class="ti ti-file-text"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="btnRefrescar" title="Refrescar">
                      <i class="ti ti-refresh"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================
               CRYPTO / TIPO DE CAMBIO (Compacto)
               ============================================ -->
          <div class="row g-2 mb-3" id="cryptoRow">
            <div class="col-6 col-md-3">
              <div class="card crypto-compact metric-card border-0 shadow-sm">
                <div class="metric-accent bg-primary"></div>
                <div class="card-body">
                  <div class="d-flex align-items-center gap-2">
                    <div class="crypto-icon bg-primary bg-opacity-10 text-primary">
                      <i class="ti ti-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="metric-label">USD / MXN</div>
                      <div class="crypto-price" id="usd-mxn">
                        <span class="spinner-border spinner-border-sm" style="width:12px;height:12px"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card crypto-compact metric-card border-0 shadow-sm">
                <div class="metric-accent bg-warning"></div>
                <div class="card-body">
                  <div class="d-flex align-items-center gap-2">
                    <div class="crypto-icon bg-warning bg-opacity-10 text-warning">
                      <i class="ti ti-currency-bitcoin"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="metric-label">Bitcoin</div>
                      <div class="crypto-price" id="btc-usd">
                        <span class="spinner-border spinner-border-sm" style="width:12px;height:12px"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card crypto-compact metric-card border-0 shadow-sm">
                <div class="metric-accent bg-info"></div>
                <div class="card-body">
                  <div class="d-flex align-items-center gap-2">
                    <div class="crypto-icon bg-info bg-opacity-10 text-info">
                      <i class="ti ti-currency-ethereum"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="metric-label">Ethereum</div>
                      <div class="crypto-price" id="eth-usd">
                        <span class="spinner-border spinner-border-sm" style="width:12px;height:12px"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card crypto-compact metric-card border-0 shadow-sm">
                <div class="metric-accent bg-success"></div>
                <div class="card-body">
                  <div class="d-flex align-items-center gap-2">
                    <div class="crypto-icon bg-success bg-opacity-10 text-success">
                      <i class="ti ti-currency-tenge"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="metric-label">USDT</div>
                      <div class="crypto-price" id="usdt-usd">
                        <span class="spinner-border spinner-border-sm" style="width:12px;height:12px"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================
               KPI METRIC CARDS
               ============================================ -->
          <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
              <div class="card metric-card border-0 shadow-sm h-100">
                <div class="metric-accent bg-info"></div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="metric-label">Utilidad Neta</span>
                    <div class="metric-icon bg-info bg-opacity-10 text-info">
                      <i class="ti ti-cash"></i>
                    </div>
                  </div>
                  <div class="metric-value text-info" id="kpiUtilidad">$0.00</div>
                  <div class="metric-footer" id="kpiUtilidadFooter">
                    <i class="ti ti-calendar"></i> <span>Del mes</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card metric-card border-0 shadow-sm h-100">
                <div class="metric-accent bg-success"></div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="metric-label">Ingresos Brutos</span>
                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                      <i class="ti ti-trending-up"></i>
                    </div>
                  </div>
                  <div class="metric-value text-success" id="kpiIngresos">$0.00</div>
                  <div class="metric-footer">
                    <i class="ti ti-building-bank"></i> <span id="kpiIngresosFooter">Comisiones + Divisas</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card metric-card border-0 shadow-sm h-100">
                <div class="metric-accent bg-danger"></div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="metric-label">Gastos Totales</span>
                    <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                      <i class="ti ti-shopping-cart"></i>
                    </div>
                  </div>
                  <div class="metric-value text-danger" id="kpiGastos">$0.00</div>
                  <div class="metric-footer">
                    <i class="ti ti-tag"></i> <span id="kpiGastosFooter">Fijos + Variables</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="card metric-card border-0 shadow-sm h-100">
                <div class="metric-accent bg-warning"></div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="metric-label">Capital Social</span>
                    <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                      <i class="ti ti-wallet"></i>
                    </div>
                  </div>
                  <div class="metric-value text-warning" id="kpiCapital">$0.00</div>
                  <div class="metric-footer">
                    <i class="ti ti-wallet"></i> <span id="kpiCapitalFooter">Disponible</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================
               RESULTADO DEL MES + CHARTS
               ============================================ -->
          <div class="row g-3 mb-4">
            <div class="col-xl-4">
              <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                  <h5 class="mb-0"><i class="ti ti-report-money"></i> Resultado del Mes</h5>
                </div>
                <div class="card-body">
                  <div id="resultadoBody">
                    <div class="text-center py-4">
                      <span class="spinner-border spinner-border-sm me-2"></span>Cargando...
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-8">
              <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                  <h5 class="mb-0"><i class="ti ti-chart-area"></i> Gráficas</h5>
                  <ul class="nav nav-tabs card-header-tabs" id="chartTabs">
                    <li class="nav-item">
                      <a class="nav-link text-white active" data-bs-toggle="tab" href="#tabEvolucion">
                        <i class="ti ti-trending-up"></i> Evolución
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-white" data-bs-toggle="tab" href="#tabDistribucion">
                        <i class="ti ti-pie-chart"></i> Distribución
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabEvolucion">
                      <div id="chartComisionesAmiri"></div>
                    </div>
                    <div class="tab-pane fade" id="tabDistribucion">
                      <div id="chartGastosDona" class="d-flex justify-content-center" style="max-height:320px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================
               FIFO USDT
               ============================================ -->
          <div class="card shadow-sm mb-4" style="display:none">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="ti ti-currency-tenge"></i> FIFO USDT</h5>
              <ul class="nav nav-tabs card-header-tabs" id="fifoTabs">
                <li class="nav-item">
                  <a class="nav-link text-white active" data-bs-toggle="tab" href="#lotesTab">Lotes de Compra</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white" data-bs-toggle="tab" href="#ventasTab">Ventas FIFO</a>
                </li>
              </ul>
            </div>
            <div class="card-body p-4">
              <div class="tab-content">
                <div class="tab-pane fade show active" id="lotesTab">
                  <div class="table-responsive">
                    <table class="table table-hover table-striped" id="lotesTable">
                      <thead class="table-dark">
                        <tr>
                          <th>Lote</th>
                          <th>Fecha Compra</th>
                          <th>USDT Comprados</th>
                          <th>Restante</th>
                          <th>Precio Compra</th>
                          <th>Total MXN</th>
                          <th>Estado</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="ventasTab">
                  <div class="table-responsive">
                    <table class="table table-hover" id="ventasTable">
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
              <div class="text-center py-3" id="fifoLoading">
                <span class="spinner-border spinner-border-sm me-2"></span>Cargando datos FIFO...
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>

  <!-- Scripts -->

  <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

  <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

  <script src="src/assets/js/sidebarmenu.js"></script>
  <script src="src/assets/js/nexo-shell.js"></script>

  <script src="src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script src="src/assets/libs/simplebar/dist/simplebar.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <!-- dark-mode.js reemplazado por nexo-shell.js + bridge CSS -->

  <script src="src/assets/js/pantalla-completa.js"></script>

  <script src="src/assets/js/utils.js"></script>

  <!-- Toast Container -->

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

  <script>

    // ─── State ───────────────────────────────────────────────
    let comisionesChart = null;
    let gastosDonaChart = null;

    const nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const mesesCortos = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    function fmt(n) {
        return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatoMoneda(value, currency) {
        if (typeof value !== 'number') value = Number(value) || 0;
        return '$' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatMXN(n) {
        return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ─── Period Filter ───────────────────────────────────────
    function getFilterParams() {
        const periodo = $('#selectPeriodo').val();
        const now = new Date();
        if (periodo === 'mes_pasado') {
            const d = new Date(); d.setMonth(d.getMonth() - 1);
            const anio = d.getFullYear(), mes = String(d.getMonth()+1).padStart(2,'0');
            const dias = new Date(anio, d.getMonth()+1, 0).getDate();
            return { periodo: 'mes_pasado', mes: mes, anio: anio, fechaInicio: anio+'-'+mes+'-01', fechaFin: anio+'-'+mes+'-'+dias };
        }
        if (periodo === 'anio') {
            return { periodo: 'anio', anio: now.getFullYear() };
        }
        if (periodo === 'rango') {
            return { periodo: 'rango', fechaInicio: $('#fechaInicio').val(), fechaFin: $('#fechaFin').val() };
        }
        // mes (default)
        const mes = String(now.getMonth()+1).padStart(2,'0');
        return { periodo: 'mes', mes: mes, anio: now.getFullYear() };
    }

    function getFilterQueryString() {
        const p = getFilterParams();
        if (p.periodo === 'anio') return 'anio=' + p.anio;
        if (p.periodo === 'rango') return 'fecha_desde=' + p.fechaInicio + '&fecha_hasta=' + p.fechaFin;
        return 'mes=' + p.mes + '&anio=' + p.anio;
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

    $('#btnRefrescar').on('click', function() { actualizarDashboard(); });

    // ─── Cargar datos del dashboard ──────────────────────────
    function actualizarDashboard() {
        const fp = getFilterParams();
        const periodo = $('#selectPeriodo').val();
        var periodoTexto = { 'mes': 'Este Mes', 'mes_pasado': 'Mes Pasado', 'anio': 'Este Año', 'rango': 'Fecha Personalizada' };
        var label = periodoTexto[periodo] || 'Este Mes';
        $('#rangoFechas').html('<i class="ti ti-calendar me-1"></i>' + label);

        if (fp.periodo === 'anio') {
            $('#mesLabel').text('Año ' + fp.anio);
        } else if (fp.periodo === 'rango') {
            $('#mesLabel').text(fp.fechaInicio + ' al ' + fp.fechaFin);
        } else {
            const mm = parseInt(fp.mes);
            const aa = parseInt(fp.anio);
            $('#mesLabel').text(nombresMeses[mm] + ' ' + aa);
        }

        const qs = getFilterQueryString();
        cargarResumen(qs);
        cargarComisionesChart(qs);
        cargarTipoDeCambioYCripto();
    }

    function cargarResumen(qs) {
        $.getJSON('api/capital.php?tipo=resumen&' + qs, function(data) {
            let m = data.mes_actual ? parseInt(data.mes_actual.substr(5,2)) : new Date().getMonth()+1;
            let a = data.mes_actual ? data.mes_actual.substr(0,4) : new Date().getFullYear();

            // Estado del mes
            let estadoHtml = data.mes_cerrado
                ? '<span class="text-danger"><i class="ti ti-lock"></i> Cerrado</span>'
                : '<span class="text-success"><i class="ti ti-circle-check"></i> Abierto</span>';
            $('#mesEstado').html(estadoHtml);

            // KPI Cards
            let utilidad = parseFloat(data.utilidad_mes) || 0;
            let ingresos = (parseFloat(data.comisiones_amiri) || 0) + (parseFloat(data.ganancia_divisas) || 0);
            let gastos = parseFloat(data.gastos_total) || 0;
            let capital = parseFloat(data.capital_actual) || 0;

            $('#kpiUtilidad').text(formatoMoneda(utilidad));
            $('#kpiIngresos').text(formatoMoneda(ingresos));
            $('#kpiGastos').text(formatoMoneda(gastos));
            $('#kpiCapital').text(formatoMoneda(capital));

            $('#kpiUtilidadFooter').html('<i class="ti ti-calendar"></i> ' + nombresMeses[m] + ' ' + a);
            $('#kpiCapitalFooter').html('<i class="ti ti-wallet"></i> Disponible');

            // Resultado del Mes (progress bars)
            let comisiones = parseFloat(data.comisiones_amiri) || 0;
            let divisas = parseFloat(data.ganancia_divisas) || 0;
            let gastosF = parseFloat(data.gastos_fijos) || 0;
            let gastosV = parseFloat(data.gastos_variables) || 0;
            let totalIngresos = comisiones + divisas;
            let maxVal = Math.max(totalIngresos, gastos, 1);

            let pctCom = (comisiones / maxVal * 100).toFixed(1);
            let pctDiv = (divisas / maxVal * 100).toFixed(1);
            let pctGF = (gastosF / maxVal * 100).toFixed(1);
            let pctGV = (gastosV / maxVal * 100).toFixed(1);
            let pctUtil = (utilidad / maxVal * 100).toFixed(1);

            // Porcentaje de gastos sobre ingresos
            let gastRatio = totalIngresos > 0 ? (gastos / totalIngresos * 100) : 0;

            $('#resultadoBody').html(`
                <div class="result-item">
                    <div class="result-label">
                        <span><i class="ti ti-building-bank text-primary"></i> Comisiones AMIRI</span>
                        <span class="fw-bold text-primary">${formatoMoneda(comisiones)}</span>
                    </div>
                    <div class="result-bar">
                        <div class="result-bar-fill bg-primary" style="width:${pctCom}%"></div>
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">
                        <span><i class="ti ti-currency-exchange text-success"></i> Ganancia Divisas</span>
                        <span class="fw-bold text-success">${formatoMoneda(divisas)}</span>
                    </div>
                    <div class="result-bar">
                        <div class="result-bar-fill bg-success" style="width:${pctDiv}%"></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="result-item">
                    <div class="result-label">
                        <span><i class="ti ti-tag text-danger"></i> Gastos Fijos</span>
                        <span class="fw-bold text-danger">${formatoMoneda(gastosF)}</span>
                    </div>
                    <div class="result-bar">
                        <div class="result-bar-fill bg-danger" style="width:${pctGF}%"></div>
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">
                        <span><i class="ti ti-tag text-warning"></i> Gastos Variables</span>
                        <span class="fw-bold text-warning">${formatoMoneda(gastosV)}</span>
                    </div>
                    <div class="result-bar">
                        <div class="result-bar-fill bg-warning" style="width:${pctGV}%"></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="result-item">
                    <div class="result-label">
                        <span class="fw-bold"><i class="ti ti-cash text-info"></i> Utilidad Neta</span>
                        <span class="fw-bold fs-5 text-info">${formatoMoneda(utilidad)}</span>
                    </div>
                    <div class="result-bar" style="height:10px">
                        <div class="result-bar-fill bg-info" style="width:${pctUtil}%"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                    <span class="text-muted"><i class="ti ti-wallet"></i> Capital Acumulado:</span>
                    <span class="fw-bold">${formatoMoneda(capital)}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted"><i class="ti ti-currency-dollar"></i> Gastos / Ingresos:</span>
                    <span class="fw-bold ${gastRatio > 80 ? 'text-danger' : gastRatio > 50 ? 'text-warning' : 'text-success'}">${gastRatio.toFixed(1)}%</span>
                </div>
            `);

            // Dona chart (gastos distribution)
            renderGastosDona(comisiones, divisas, gastosF, gastosV);

        }).fail(function() {
            $('#kpiUtilidad').text('Error');
            $('#resultadoBody').html('<div class="text-danger text-center py-3">Error al cargar datos</div>');
        });
    }

    // ─── Comisiones Chart (ApexCharts Area) ──────────────────
    function cargarComisionesChart(qs) {
        $.getJSON('api/dashboard.php?tipo=comisiones_amiri&' + qs, function(data) {
            if (data.error) { console.error(data.error); return; }
            renderComisionesChart(data.grafica_fechas || [], data.grafica_montos || []);
        }).fail(function() { console.error('Error cargando comisiones chart'); });
    }

    function renderComisionesChart(fechas, montos) {
        if (comisionesChart) comisionesChart.destroy();

        let options = {
            series: [{ name: 'Comisiones', data: montos }],
            chart: { type: 'area', height: 260, toolbar: { show: false }, parentHeightOffset: 0 },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: fechas, labels: { rotate: -45 } },
            yaxis: { labels: { formatter: function(v) { return '$' + v.toFixed(2); } } },
            colors: ['#c58fb8'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
            grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4 },
            tooltip: { y: { formatter: function(v) { return formatoMoneda(v); } } }
        };
        comisionesChart = new ApexCharts(document.querySelector("#chartComisionesAmiri"), options);
        comisionesChart.render();
    }

    // ─── Gastos Dona Chart (Chart.js) ────────────────────────
    function renderGastosDona(comisiones, divisas, gastosF, gastosV) {
        if (gastosDonaChart) gastosDonaChart.destroy();

        let ctx = document.getElementById('chartGastosDona');
        if (!ctx) return;
        ctx.innerHTML = '<canvas></canvas>';
        let canvas = ctx.querySelector('canvas');

        let hasData = comisiones > 0 || divisas > 0 || gastosF > 0 || gastosV > 0;
        if (!hasData) {
            ctx.innerHTML = '<div class="text-muted text-center py-5"><i class="ti ti-circle-off" style="font-size:2rem"></i><br>Sin datos para este mes</div>';
            return;
        }

        gastosDonaChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Comisiones AMIRI', 'Ganancia Divisas', 'Gastos Fijos', 'Gastos Variables'],
                datasets: [{
                    data: [comisiones, divisas, gastosF, gastosV],
                    backgroundColor: ['#c58fb8', '#3ddc84', '#ff7a7a', '#fbbf24'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 }, color: getComputedStyle(document.documentElement).getPropertyValue('--nx-text-muted').trim() || '#9a9096' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                let pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + formatoMoneda(ctx.parsed) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ─── Crypto / Tipo de Cambio ─────────────────────────────
    async function cargarTipoDeCambioYCripto() {
        try {
            const fixerApiKey = '9d6323700a7e2c2a794682393667bef6';
            const fixerResp = await fetch('https://data.fixer.io/api/latest?access_key=' + fixerApiKey + '&symbols=USD,MXN');
            const fixerData = await fixerResp.json();

            let usdToMxn = 0;
            if (fixerData.success) {
                usdToMxn = fixerData.rates.MXN / fixerData.rates.USD;
                document.getElementById('usd-mxn').innerHTML = `
                    <div>${formatoMoneda(usdToMxn, '')} <small class="text-muted" style="font-size:0.7rem">MXN</small></div>
                    <div class="crypto-change text-muted"><small>1 USD</small></div>
                `;
            } else {
                document.getElementById('usd-mxn').textContent = '—';
            }

            const cmcResp = await fetch('api/cripto.php');
            const cmcData = await cmcResp.json();

            function formatoCambioPorcentual(change) {
                if (change == null || isNaN(change)) return '';
                let signo = change >= 0 ? '▲' : '▼';
                let clase = change >= 0 ? 'cambio-positivo' : 'cambio-negativo';
                return '<span class="crypto-change ' + clase + '">' + signo + ' ' + Math.abs(change).toFixed(2) + '%</span>';
            }

            // Bitcoin
            let btcCard = document.getElementById('btc-usd');
            if (cmcData && cmcData.BTC) {
                let b = cmcData.BTC;
                btcCard.innerHTML = `
                    <div>${formatoMoneda(b.USD, '')} <small class="text-muted" style="font-size:0.7rem">USD</small></div>
                    <div>${formatoMoneda(b.MXN, '')} <small class="text-muted" style="font-size:0.7rem">MXN</small></div>
                    <div style="margin-top:0.25rem">${formatoCambioPorcentual(b.percent_change_24h)}</div>
                `;
            } else {
                btcCard.textContent = '—';
            }

            // Ethereum
            let ethCard = document.getElementById('eth-usd');
            if (cmcData && cmcData.ETH) {
                let e = cmcData.ETH;
                ethCard.innerHTML = `
                    <div>${formatoMoneda(e.USD, '')} <small class="text-muted" style="font-size:0.7rem">USD</small></div>
                    <div>${formatoMoneda(e.MXN, '')} <small class="text-muted" style="font-size:0.7rem">MXN</small></div>
                    <div style="margin-top:0.25rem">${formatoCambioPorcentual(e.percent_change_24h)}</div>
                `;
            } else {
                ethCard.textContent = '—';
            }

            // USDT
            let usdtCard = document.getElementById('usdt-usd');
            if (cmcData && cmcData.USDT) {
                let u = cmcData.USDT;
                usdtCard.innerHTML = `
                    <div>${formatoMoneda(u.USD, '')} <small class="text-muted" style="font-size:0.7rem">USD</small></div>
                    <div>${formatoMoneda(u.MXN, '')} <small class="text-muted" style="font-size:0.7rem">MXN</small></div>
                    <div style="margin-top:0.25rem">${formatoCambioPorcentual(u.percent_change_24h)}</div>
                `;
            } else {
                usdtCard.textContent = '—';
            }

        } catch (err) {
            console.error('Error al cargar tasas:', err);
            document.getElementById('usd-mxn').textContent = '—';
            document.getElementById('btc-usd').textContent = '—';
            document.getElementById('eth-usd').textContent = '—';
            document.getElementById('usdt-usd').textContent = '—';
        }
    }

    // ─── PDF ─────────────────────────────────────────────────
    $('#btnPDF').on('click', function() {
        const fp = getFilterParams();
        let url = 'api/dashboard.php?tipo=comisiones_amiri&pdf=1&' + getFilterQueryString();
        window.open(url, '_blank');
    });

    // ─── FIFO USDT ───────────────────────────────────────────
    async function cargarFifoUsdt() {
        $('#fifoLoading').show();
        try {
            const resp = await fetch('api/dashboard.php?tipo=fifo_usdt');
            const data = await resp.json();
            $('#fifoLoading').hide();
            if (data.error) { console.error('FIFO error:', data.error); return; }

            const lotesTbody = $('#lotesTable tbody');
            lotesTbody.empty();
            data.lotes.forEach(function(l) {
                const restante = parseFloat(l.cantidad_restante);
                const original = parseFloat(l.cantidad_original);
                let estado, badgeClass;
                if (restante <= 0) { estado = 'Consumido'; badgeClass = 'bg-success'; }
                else if (restante >= original) { estado = 'Disponible'; badgeClass = 'bg-primary'; }
                else { estado = 'Parcial'; badgeClass = 'bg-warning text-dark'; }
                lotesTbody.append('<tr>\
                    <td>#' + l.id + '</td>\
                    <td>' + l.fecha.split(' ')[0] + '</td>\
                    <td class="text-end">' + original.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>\
                    <td class="text-end">' + restante.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>\
                    <td class="text-end">$' + parseFloat(l.precio_compra).toFixed(2) + '</td>\
                    <td class="text-end">' + formatoMoneda(l.total_mxn) + '</td>\
                    <td><span class="badge ' + badgeClass + '">' + estado + '</span></td>\
                </tr>');
            });

            const ventasTbody = $('#ventasTable tbody');
            ventasTbody.empty();
            data.ventas.forEach(function(v) {
                const gananciaClass = v.ganancia_total >= 0 ? 'text-success' : 'text-danger';
                let consumosHtml = '<table class="table table-sm table-borderless mb-0" style="font-size:0.85em">\
                    <tr><th>Lote</th><th>Fecha Lote</th><th>USDT</th><th>Precio Compra</th><th>Precio Venta</th><th>Ganancia</th></tr>';
                v.consumos.forEach(function(c) {
                    const gClass = c.ganancia >= 0 ? 'text-success' : 'text-danger';
                    consumosHtml += '<tr>\
                        <td>#' + c.lote_id + '</td>\
                        <td>' + c.lote_fecha.split(' ')[0] + '</td>\
                        <td class="text-end">' + c.cantidad.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>\
                        <td class="text-end">$' + c.precio_compra.toFixed(2) + '</td>\
                        <td class="text-end">$' + c.precio_venta.toFixed(2) + '</td>\
                        <td class="text-end ' + gClass + '">' + formatoMoneda(c.ganancia) + '</td>\
                    </tr>';
                });
                consumosHtml += '</table>';

                ventasTbody.append('<tr>\
                    <td>' + v.fecha.split(' ')[0] + '</td>\
                    <td>' + v.descripcion + '</td>\
                    <td class="text-end">' + v.total_usdt.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>\
                    <td class="text-end">' + formatoMoneda(v.total_mxn) + '</td>\
                    <td class="text-end ' + gananciaClass + ' fw-bold">' + formatoMoneda(v.ganancia_total) + '</td>\
                    <td>' + consumosHtml + '</td>\
                </tr>');
            });
        } catch (err) {
            console.error('Error cargando FIFO:', err);
            $('#fifoLoading').html('<span class="text-danger">Error al cargar datos FIFO</span>');
        }
    }

    // ─── Init ────────────────────────────────────────────────
    actualizarDashboard();
    cargarFifoUsdt();

  </script>

</body>

</html>
