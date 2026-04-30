<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {

  header('Location: login.php');

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

  <link rel="stylesheet" href="src/assets/css/styles.min.css" />

  <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

  <link rel="stylesheet" href="src/assets/css/dark-mode.css" />

  <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

</head>

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
   body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
        /* =========================================
           🌙 REGLAS PARA MODO OSCURO (Dashboard Principal)
           ========================================= */
        
        /* 1. Tarjetas Generales y Textos */
        [data-bs-theme="dark"] .card {
            background-color: #1a2234 !important;
            border-color: #2c344c !important;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2) !important;
        }
        
        [data-bs-theme="dark"] .text-gray { color: #e9ecef !important; }
        [data-bs-theme="dark"] .text-muted { color: #8c9ea8 !important; }
        
        /* 2. Encabezado Azul de la tarjeta de la Gráfica */
        [data-bs-theme="dark"] .card-header.bg-primary {
            background-color: rgba(93, 135, 255, 0.15) !important;
            border-bottom: 1px solid #2c344c !important;
            padding: 16px 20px !important; /* Mismo tamaño que en flujo */
        }
        
        [data-bs-theme="dark"] .card-header.bg-primary h5,
        [data-bs-theme="dark"] .card-header.bg-primary i {
            color: #5D87FF !important; /* Azul neón brillante */
            font-weight: 700 !important;
        }

        /* 3. Inputs y Selectores (Filtros de Mes, Año, etc.) */
        [data-bs-theme="dark"] .form-label { color: #e9ecef !important; }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #252f43 !important;
            border-color: #2c344c !important;
            color: #e9ecef !important;
        }
        
        /* 4. Tarjetas de Resumen (Comisiones, Gastos, Remanente) */
        [data-bs-theme="dark"] .border-success { border-color: #13DEB9 !important; background-color: rgba(19, 222, 185, 0.05) !important; }
        [data-bs-theme="dark"] .border-danger { border-color: #FA896B !important; background-color: rgba(250, 137, 107, 0.05) !important; }
        [data-bs-theme="dark"] .border-info { border-color: #5D87FF !important; background-color: rgba(93, 135, 255, 0.05) !important; }
        
        [data-bs-theme="dark"] .text-success { color: #13DEB9 !important; }
        [data-bs-theme="dark"] .text-danger { color: #FA896B !important; }
        [data-bs-theme="dark"] .text-info { color: #5D87FF !important; }
        [data-bs-theme="dark"] .text-warning { color: #FFAE1F !important; }
        [data-bs-theme="dark"] .text-primary { color: #5D87FF !important; }
        
        /* 5. Vencer los estilos fijos (color: #666) de las tarjetas Cripto */
        [data-bs-theme="dark"] .card-title[style] { color: #e9ecef !important; }
        [data-bs-theme="dark"] #usd-mxn,
        [data-bs-theme="dark"] #btc-usd,
        [data-bs-theme="dark"] #eth-usd,
        [data-bs-theme="dark"] #usdt-usd { color: #e9ecef !important; }
        [data-bs-theme="dark"] .cambio-positivo { color: #13DEB9 !important; }
        [data-bs-theme="dark"] .cambio-negativo { color: #FA896B !important; }
        
        /* 6. Arreglar los colores de la Gráfica (ApexCharts) */
        [data-bs-theme="dark"] .apexcharts-text { fill: #8c9ea8 !important; }
        [data-bs-theme="dark"] .apexcharts-gridline { stroke: #2c344c !important; }
        [data-bs-theme="dark"] .apexcharts-tooltip {
            background: #1a2234 !important;
            border: 1px solid #2c344c !important;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3) !important;
        }
        [data-bs-theme="dark"] .apexcharts-tooltip-title {
            background: #252f43 !important;
            border-bottom: 1px solid #2c344c !important;
            color: #e9ecef !important;
        }
        [data-bs-theme="dark"] .apexcharts-tooltip-text {
            color: #e9ecef !important;
        }
</style>

<body>

  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

    data-sidebar-position="fixed" data-header-position="fixed">



    <!-- Header / Sidebar -->

    <?php include 'src/assets/partials/header.php'; ?>

    <?php include 'src/assets/partials/sidebar.php'; ?>



    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid px-4" style="max-width: 100%;">
          <div class="card-body text-center py-4 mt-2">
            <h1 class="text-gray mb-0 fw-bold" id="myElement" style="font-size: 2.5rem; letter-spacing: 1px;"></h1>
          </div>
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0"><i class="ti ti-building-bank"></i> Comisiones AMIRI</h5>
            </div>
            <div class="card-body p-4">
              <div class="row align-items-end mb-4">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Filtrar por:</label>
                  <select class="form-select" id="tipoFiltroAmiri">
                    <option value="mes" selected>Mes</option>
                    <option value="anio">Año</option>
                    <option value="rango">Rango Personalizado</option>
                    <option value="todo">Todo el Historial</option>
                  </select>
                </div>
                <div class="col-md-2" id="filtroMesAmiri">
                  <label class="form-label">Mes</label>
                  <select class="form-select" id="selectMesAmiri">
                    <?php $mesActual = date('m'); ?>
                    <option value="01" <?= $mesActual == '01' ? 'selected' : '' ?>>Enero</option>
                    <option value="02" <?= $mesActual == '02' ? 'selected' : '' ?>>Febrero</option>
                    <option value="03" <?= $mesActual == '03' ? 'selected' : '' ?>>Marzo</option>
                    <option value="04" <?= $mesActual == '04' ? 'selected' : '' ?>>Abril</option>
                    <option value="05" <?= $mesActual == '05' ? 'selected' : '' ?>>Mayo</option>
                    <option value="06" <?= $mesActual == '06' ? 'selected' : '' ?>>Junio</option>
                    <option value="07" <?= $mesActual == '07' ? 'selected' : '' ?>>Julio</option>
                    <option value="08" <?= $mesActual == '08' ? 'selected' : '' ?>>Agosto</option>
                    <option value="09" <?= $mesActual == '09' ? 'selected' : '' ?>>Septiembre</option>
                    <option value="10" <?= $mesActual == '10' ? 'selected' : '' ?>>Octubre</option>
                    <option value="11" <?= $mesActual == '11' ? 'selected' : '' ?>>Noviembre</option>
                    <option value="12" <?= $mesActual == '12' ? 'selected' : '' ?>>Diciembre</option>
                  </select>
                </div>
                <div class="col-md-2" id="filtroAnioAmiri">
                  <label class="form-label">Año</label>
                  <select class="form-select" id="selectAnioAmiri">
                    <?php
                    $anioActual = date('Y');
                    for ($i = $anioActual; $i >= 2024; $i--):
                    ?>
                      <option value="<?= $i ?>" <?= $i == $anioActual ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="col-md-2" id="filtroDesdeAmiri" style="display:none;">
                  <label class="form-label">Desde</label>
                  <input type="date" class="form-control" id="fechaDesdeAmiri">
                </div>
                <div class="col-md-2" id="filtroHastaAmiri" style="display:none;">
                  <label class="form-label">Hasta</label>
                  <input type="date" class="form-control" id="fechaHastaAmiri" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                  <button class="btn btn-primary w-100" id="btnAplicarFiltroAmiri">
                    <i class="ti ti-filter"></i> Aplicar
                  </button>
                </div>
                <div class="col-md-2">
                  <button class="btn btn-danger w-100" id="btnGenerarPDFAmiri">
                    <i class="ti ti-file-text"></i> Generar PDF
                  </button>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="card shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body">
                      <h6 class="text-muted">Comisiones Generadas</h6>
                      <h3 class="text-success" id="comisionesAmiriTotal">$0.00</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body">
                      <h6 class="text-muted">Gastos Generados</h6>
                      <h3 class="text-danger" id="gastosGeneradosAmiri">$0.00</h3>
                      <small class="text-muted fw-bold d-block mt-1">
                          Fijos: <span id="gastosFijosAmiri" class="text-primary">$0.00</span> | 
                          Variables: <span id="gastosVarAmiri" class="text-warning">$0.00</span>
                      </small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card shadow-sm border-start border-info border-4 h-100">
                    <div class="card-body">
                      <h6 class="text-muted">Remanente</h6>
                      <h3 class="text-info" id="remanenteAmiri">$0.00</h3>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div id="chartComisionesAmiri"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row" id="resumen-cards"></div>

          <div class="row mt-4">
            <div class="col-12 col-md-6 col-xl-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center" style="padding: 1.5rem; text-align: center;">
                  <h4 class="card-title mb-3" style="font-size: 1.5rem; font-weight: 600; color: #666;"><i class="ti ti-cash-banknote"></i> USD ↔ MXN</h4>
                  <div id="usd-mxn" style="font-size: 1.8rem; line-height: 1.3; font-family: 'Roboto Mono', monospace;">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Cargando...
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center" style="padding: 1.5rem;">
                  <h5 class="card-title mb-3" style="font-size: 1.5rem; font-weight: 600; color: #666;"><i class="ti ti-currency-bitcoin"></i> BTC</h5>
                  <div id="btc-usd" style="font-size: 1.2rem; line-height: 1.6; font-family: 'Roboto Mono', monospace;">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Cargando...
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center" style="padding: 1.5rem;">
                  <h5 class="card-title mb-3" style="font-size: 1.5rem; font-weight: 600; color: #666;"><i class="ti ti-currency-ethereum"></i> ETH</h5>
                  <div id="eth-usd" style="font-size: 1.2rem; line-height: 1.6; font-family: 'Roboto Mono', monospace;">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Cargando...
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center" style="padding: 1.5rem;">
                  <h5 class="card-title mb-3" style="font-size: 1.5rem; font-weight: 600; color: #666;"><i class="ti ti-currency-tenge"></i> USDT</h5>
                  <div id="usdt-usd" style="font-size: 1.2rem; line-height: 1.6; font-family: 'Roboto Mono', monospace;">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Cargando...
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



  <!-- Scripts -->

  <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

  <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

  <script src="src/assets/js/sidebarmenu.js"></script>

  <script src="src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>

  <script src="src/assets/libs/simplebar/dist/simplebar.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script src="src/assets/js/dark-mode.js"></script>

  <script src="src/assets/js/pantalla-completa.js"></script>

  <script src="src/assets/js/utils.js"></script>

  <script src="https://unpkg.com/typeit/dist/index.umd.js"></script>

  <script>

    new TypeIt("#myElement", {

      strings: "¡Bienvenido <?= htmlspecialchars($nombre_usuario) ?>!",

      speed: 80,

      startDelay: 900,

      waitUntilVisible: true,

      cursor: false

    }).go();

  </script>



  <!-- Toast Container -->

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>



  <script>

    let flujoChart = null;

    let saldosPieChart = null;

    let comisionesAmiriChart = null;

    let filtroAmiriActual = {

      tipo: 'mes',

      mes: '<?= date('m') ?>',

      anio: '<?= date('Y') ?>'

    };



    // util: formatea número a moneda local (MXN por defecto)

    function formatoMoneda(value, currency = 'MXN') {

      if (typeof value !== 'number') value = Number(value) || 0;

      return value.toLocaleString('en-US', {

        style: 'currency',

        currency: currency,

        minimumFractionDigits: 2,

        maximumFractionDigits: 2

      });

    }



    // ========================================

    // COMISIONES AMIRI

    // ========================================



    // Manejar cambio de tipo de filtro

    $('#tipoFiltroAmiri').on('change', function() {

      const tipo = $(this).val();

      $('#filtroMesAmiri, #filtroAnioAmiri, #filtroDesdeAmiri, #filtroHastaAmiri').hide();



      if (tipo === 'mes') {

        $('#filtroMesAmiri, #filtroAnioAmiri').show();

      } else if (tipo === 'anio') {

        $('#filtroAnioAmiri').show();

      } else if (tipo === 'rango') {

        $('#filtroDesdeAmiri, #filtroHastaAmiri').show();

      }

    });



    // Establecer mes actual

    $('#selectMesAmiri').val('<?= date('m') ?>');



    // Construir URL con filtros

    function construirURLAmiri(endpoint = '') {

      let url = `api/dashboard.php?tipo=comisiones_amiri${endpoint}`;



      if (filtroAmiriActual.tipo === 'mes') {

        url += `&mes=${filtroAmiriActual.mes}&anio=${filtroAmiriActual.anio}`;

      } else if (filtroAmiriActual.tipo === 'anio') {

        url += `&anio=${filtroAmiriActual.anio}`;

      } else if (filtroAmiriActual.tipo === 'rango') {

        url += `&fecha_desde=${filtroAmiriActual.desde}&fecha_hasta=${filtroAmiriActual.hasta}`;

      }



      return url;

    }



    // Cargar datos de comisiones AMIRI

    function cargarComisionesAmiri() {
      $.get(construirURLAmiri(), function(data) {
        if (data.error) {
          console.error('Error en API:', data.error);
          $('#comisionesAmiriTotal').text('$0.00');
          $('#gastosGeneradosAmiri').text('$0.00');
          $('#gastosFijosAmiri').text('$0.00'); // Reseteamos en caso de error
          $('#gastosVarAmiri').text('$0.00'); // Reseteamos en caso de error
          $('#remanenteAmiri').text('$0.00');
          return;
        }

        // --- ¡Aquí es el lugar correcto, cuando la API responde con éxito! ---
        $('#comisionesAmiriTotal').text(formatoMoneda(data.total_comisiones));
        $('#gastosGeneradosAmiri').text(formatoMoneda(data.total_gastos));
        
        // Alimentamos los span con la nueva información de Fijos y Variables
        $('#gastosFijosAmiri').text(formatoMoneda(data.gastos_fijos));
        $('#gastosVarAmiri').text(formatoMoneda(data.gastos_variables));

        $('#remanenteAmiri').text(formatoMoneda(data.remanente));

        // Renderizar gráfica
        renderComisionesAmiriChart(data.grafica_fechas || [], data.grafica_montos || []);
      }).fail(function(xhr, status, error) {
        console.error('Error HTTP:', status, error);
        $('#comisionesAmiriTotal').text('Error');
        $('#gastosGeneradosAmiri').text('Error');
        $('#gastosFijosAmiri').text('Error');
        $('#gastosVarAmiri').text('Error');
        $('#remanenteAmiri').text('Error');
      });
    }



    // Renderizar gráfica de comisiones

    function renderComisionesAmiriChart(fechas, montos) {

      if (comisionesAmiriChart) {

        comisionesAmiriChart.destroy();

      }



      const options = {

        series: [{

          name: 'Comisiones',

          data: montos

        }],

        chart: {

          type: 'area',

          height: 300,

          toolbar: {

            show: false

          }

        },

        dataLabels: {

          enabled: false

        },

        stroke: {

          curve: 'smooth',

          width: 2

        },

        xaxis: {

          categories: fechas,

          labels: {

            rotate: -45

          }

        },

        yaxis: {

          labels: {

            formatter: function(val) {

              return '$' + val.toFixed(2);

            }

          }

        },

        colors: ['#5D87FF'],

        fill: {

          type: 'gradient',

          gradient: {

            shadeIntensity: 1,

            opacityFrom: 0.7,

            opacityTo: 0.3,

          }

        },

        tooltip: {

          y: {

            formatter: function(val) {

              return formatoMoneda(val);

            }

          }

        }

      };



      comisionesAmiriChart = new ApexCharts(document.querySelector("#chartComisionesAmiri"), options);

      comisionesAmiriChart.render();

    }



    // Aplicar filtro

    $('#btnAplicarFiltroAmiri').on('click', function() {

      const tipoFiltro = $('#tipoFiltroAmiri').val();



      if (tipoFiltro === 'mes') {

        filtroAmiriActual = {

          tipo: 'mes',

          mes: $('#selectMesAmiri').val(),

          anio: $('#selectAnioAmiri').val()

        };

      } else if (tipoFiltro === 'anio') {

        filtroAmiriActual = {

          tipo: 'anio',

          anio: $('#selectAnioAmiri').val()

        };

      } else if (tipoFiltro === 'rango') {

        const desde = $('#fechaDesdeAmiri').val();

        const hasta = $('#fechaHastaAmiri').val();



        if (!desde || !hasta) {

          alert('Por favor selecciona ambas fechas');

          return;

        }



        filtroAmiriActual = {

          tipo: 'rango',

          desde: desde,

          hasta: hasta

        };

      } else {

        filtroAmiriActual = {

          tipo: 'todo'

        };

      }



      cargarComisionesAmiri();

    });



    // Generar PDF

    $('#btnGenerarPDFAmiri').on('click', function() {

      let url = 'api/dashboard.php?tipo=comisiones_amiri&pdf=1';



      if (filtroAmiriActual.tipo === 'mes') {

        url += `&mes=${filtroAmiriActual.mes}&anio=${filtroAmiriActual.anio}`;

      } else if (filtroAmiriActual.tipo === 'anio') {

        url += `&anio=${filtroAmiriActual.anio}`;

      } else if (filtroAmiriActual.tipo === 'rango') {

        url += `&fecha_desde=${filtroAmiriActual.desde}&fecha_hasta=${filtroAmiriActual.hasta}`;

      }



      window.open(url, '_blank');

    });



    // Cargar comisiones al inicio

    cargarComisionesAmiri();



    // ========================================

    // RESTO DEL DASHBOARD

    // ========================================



    async function cargarTipoDeCambioYCripto() {

      try {

        // =========================

        // FIXER API - USD → MXN

        // =========================

        const fixerApiKey = '9d6323700a7e2c2a794682393667bef6';

        const fixerResp = await fetch(`https://data.fixer.io/api/latest?access_key=${fixerApiKey}&symbols=USD,MXN`);

        const fixerData = await fixerResp.json();



        let usdToMxn = 0;

        if (fixerData.success) {

          usdToMxn = fixerData.rates.MXN / fixerData.rates.USD;

          const formattedUsdToMxn = usdToMxn.toLocaleString(undefined, {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

          });

          document.getElementById('usd-mxn').innerHTML = `

        <div style="font-size: 1.2rem;">1 USD = ${formattedUsdToMxn} MXN</div>

      `;

        } else {

          console.error('Error Fixer:', fixerData.error);

          document.getElementById('usd-mxn').textContent = '—';

        }



        // =========================

        // COINMARKETCAP - Backend PHP

        // =========================

        const cmcResp = await fetch('api/cripto.php'); // llama a tu PHP

        const cmcData = await cmcResp.json();



        function formatoCambioPorcentual(change) {

          if (change == null || isNaN(change)) return '';

          const signo = change >= 0 ? '▲' : '▼';

          const clase = change >= 0 ? 'cambio-positivo' : 'cambio-negativo';

          return `<span class="${clase}">${signo} ${Math.abs(change).toFixed(2)}%</span>`;

        }



        // 🔹 Bitcoin

        const btcCard = document.getElementById('btc-usd');

        if (cmcData?.BTC) {

          const btcUSD = cmcData.BTC.USD;

          const btcMXN = cmcData.BTC.MXN;

          const btcChangeUSD = cmcData.BTC.percent_change_24h; // Ahora sí lo tienes



          btcCard.innerHTML = `

        <div>${formatoMoneda(btcUSD, 'USD').replace('$', '$')}</div>

        <div>${formatoMoneda(btcMXN, 'MXN')}</div>

        <div style="margin-top: 0.25rem;">${formatoCambioPorcentual(btcChangeUSD)}</div>

      `;

        } else {

          btcCard.textContent = '—';

        }



        // Ethereum

        const ethCard = document.getElementById('eth-usd');

        if (cmcData?.ETH) {

          const ethUSD = cmcData.ETH.USD;

          const ethMXN = cmcData.ETH.MXN;

          const ethChangeUSD = cmcData.ETH.percent_change_24h; // Ahora sí lo tienes



          ethCard.innerHTML = `

        <div>${formatoMoneda(ethUSD, 'USD').replace('$', '$')}</div>

        <div>${formatoMoneda(ethMXN, 'MXN')}</div>

        <div style="margin-top: 0.25rem;">${formatoCambioPorcentual(ethChangeUSD)}</div>

      `;

        } else {

          ethCard.textContent = '—';

        }



      } catch (err) {

        console.error('Error al cargar tasas:', err);

        document.getElementById('usd-mxn').textContent = '—';

        document.getElementById('btc-usd').textContent = '—';

        document.getElementById('eth-usd').textContent = '—';

      }

    }



    // inicial

    cargarTipoDeCambioYCripto();

  </script>

</body>



</html>