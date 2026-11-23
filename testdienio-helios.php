<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id   = $_SESSION['usuario_id'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol    = $_SESSION['usuario_rol'] ?? 'usuario';
$mes_activo     = $_SESSION['mes_activo'] ?? date('Y-m');

function inicialesNombre($nombre) {
    $nombre = trim((string)$nombre);
    if ($nombre === '') return 'AM';
    $parts = preg_split('/\s+/u', $nombre, -1, PREG_SPLIT_NO_EMPTY);
    $ini = '';
    foreach ($parts as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
        if (mb_strlen($ini, 'UTF-8') >= 2) break;
    }
    return $ini !== '' ? $ini : 'AM';
}
$iniciales = inicialesNombre($usuario_nombre);
$rol_label = ($usuario_rol === 'admin') ? 'Administrador' : (($usuario_rol === 'promotor') ? 'Promotor' : 'Usuario');

// ---------- Mock data (solo para esta prueba visual) ----------
$mock = [
  'meta' => [
    'updated' => '30 jun 2025, 10:42',
    'sync'    => '10:42',
    'currency'=> 'MXN',
  ],
  'kpis' => [
    ['id'=>'saldo',     'label'=>'Saldo total',        'value'=>1284650.00, 'change'=>'+8.2%',  'changeType'=>'pos',  'note'=>'vs. mes anterior', 'icon'=>'ti-wallet'],
    ['id'=>'ingresos',  'label'=>'Ingresos del mes',   'value'=>486200.00,  'change'=>'+12.4%', 'changeType'=>'pos',  'note'=>'vs. mes anterior', 'icon'=>'ti-arrow-up-right'],
    ['id'=>'egresos',   'label'=>'Egresos del mes',    'value'=>312800.00,  'change'=>'-4.1%',  'changeType'=>'neg',  'note'=>'vs. mes anterior', 'icon'=>'ti-arrow-down-right'],
    ['id'=>'porcobrar', 'label'=>'Por cobrar',         'value'=>148500.00,  'change'=>'12 facturas','changeType'=>'warn','note'=>'4 próximas a vencer','icon'=>'ti-file-invoice'],
  ],
  'cashflow' => [
    'categories'=> ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
    'ingresos'   => [320000, 360000, 410000, 380000, 430000, 486200, 450000, 470000, 490000, 510000, 530000, 560000],
    'egresos'    => [240000, 260000, 300000, 280000, 295000, 312800, 300000, 320000, 340000, 360000, 380000, 400000],
  ],
  'accounts' => [
    ['bank'=>'BBVA',        'last4'=>'4821', 'type'=>'Cuenta operativa',     'balance'=>842450.00,  'color'=>'#004481'],
    ['bank'=>'Santander',   'last4'=>'1093', 'type'=>'Cuenta de nómina',     'balance'=>318200.00,  'color'=>'#EC0000'],
    ['bank'=>'Banorte',     'last4'=>'0876', 'type'=>'Reserva empresarial',  'balance'=>124000.00,  'color'=>'#EB0029'],
    ['bank'=>'Scotiabank',  'last4'=>'7710', 'type'=>'Fondo de emergencia',  'balance'=> 67500.00,  'color'=>'#006A44'],
  ],
  'movements' => [
    ['date'=>'30 jun','concept'=>'Pago de factura #1048','sub'=>'Grupo Horizonte','account'=>'BBVA','amount'=>48500.00,  'type'=>'ingreso',  'status'=>'Completado'],
    ['date'=>'29 jun','concept'=>'Servicios de software','sub'=>'Suscripción mensual','account'=>'BBVA','amount'=>-3200.00, 'type'=>'egreso',   'status'=>'Completado'],
    ['date'=>'28 jun','concept'=>'Pago a proveedor','sub'=>'Soluciones Integrales','account'=>'Santander','amount'=>-18400.00,'type'=>'egreso',   'status'=>'Completado'],
    ['date'=>'27 jun','concept'=>'Factura #1045','sub'=>'Estudio Meridiano','account'=>'BBVA','amount'=>32000.00, 'type'=>'ingreso',  'status'=>'Pendiente'],
    ['date'=>'26 jun','concept'=>'Cobro servicio logístico','sub'=>'Comercial del Norte','account'=>'Banorte','amount'=>12500.00,'type'=>'ingreso', 'status'=>'Completado'],
    ['date'=>'25 jun','concept'=>'Pago nómina quincenal','sub'=>'Recursos Humanos','account'=>'Santander','amount'=>-45200.00,'type'=>'egreso',  'status'=>'Completado'],
    ['date'=>'24 jun','concept'=>'Factura #1042','sub'=>'Nova Consultores','account'=>'BBVA','amount'=>21000.00, 'type'=>'ingreso',  'status'=>'Completado'],
    ['date'=>'23 jun','concept'=>'Renta oficina','sub'=>'Inmobiliaria Centro','account'=>'BBVA','amount'=>-28000.00,'type'=>'egreso',   'status'=>'Completado'],
    ['date'=>'22 jun','concept'=>'Reembolso viáticos','sub'=>'Equipo comercial','account'=>'Santander','amount'=>-5600.00,'type'=>'egreso',   'status'=>'Completado'],
    ['date'=>'21 jun','concept'=>'Anticipo proyecto','sub'=>'Grupo Horizonte','account'=>'BBVA','amount'=>40000.00,'type'=>'ingreso',  'status'=>'Completado'],
  ],
  'receivables' => [
    ['client'=>'Grupo Horizonte',  'due'=>'Vence hoy - Factura #1052', 'amount'=>68500.00, 'warn'=>true],
    ['client'=>'Estudio Meridiano','due'=>'03 jul - Factura #1056',    'amount'=>42000.00, 'warn'=>false],
    ['client'=>'Nova Consultores', 'due'=>'05 jul - Factura #1060',    'amount'=>28000.00, 'warn'=>false],
    ['client'=>'Comercial del Norte','due'=>'08 jul - Factura #1063',  'amount'=>10000.00, 'warn'=>false],
  ],
];

function fmt($n) {
    return '$' . number_format((float)$n, 2, '.', ',');
}
?>
<!doctype html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Helios - Amiri Financiero</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
  <link rel="stylesheet" href="src/assets/libs/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="src/assets/css/testdienio-helios.css" />
  <script>
    (function(){
      try {
        var dark = localStorage.getItem('heliosDark') === 'true';
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        if (dark) document.body.classList.add('h-body'); else document.body.classList.remove('h-body');
      } catch(e) {}
    })();
  </script>
  <style>
    .h-muted { color: var(--h-text-muted); }
    .text-success-h { color: var(--h-success) !important; }
    .text-danger-h { color: var(--h-danger) !important; }
    .text-info-h { color: var(--h-info) !important; }
  </style>
</head>
<body class="h-body">
  <div class="h-app" id="hApp">

    <!-- Overlay móvil para sidebar -->
    <div class="h-overlay" id="hOverlay"></div>

    <!-- Contenido centrado (estilo Helios): sidebar + área de contenido en grid de 2 columnas -->
    <div class="h-wrap" id="hWrap">
      <!-- Sidebar dentro del contenedor centrado (desktop: columna del grid; móvil: drawer) -->
      <aside class="h-sidebar" id="hSidebar">
        <div class="h-sidebar-inner">
          <a href="index.php" class="h-brand" title="Ir al dashboard real">
            <span class="h-brand-mark">A</span>
            <span class="h-brand-text">
              <div class="h-brand-title">Amiri</div>
              <div class="h-brand-sub">FINANZAS EMPRESARIALES</div>
            </span>
          </a>

          <div class="h-section">PRINCIPAL</div>
          <nav class="h-nav" id="hNav">
            <a href="#" class="h-nav-item active" data-view="resumen">
              <i class="ti ti-layout-dashboard"></i><span>Resumen</span>
            </a>
            <a href="#" class="h-nav-item" data-view="cuentas">
              <i class="ti ti-wallet"></i><span>Cuentas</span>
            </a>
            <a href="#" class="h-nav-item" data-view="movimientos">
              <i class="ti ti-list-details"></i><span>Movimientos</span>
            </a>
            <a href="#" class="h-nav-item" data-view="clientes">
              <i class="ti ti-users"></i><span>Clientes</span><span class="h-badge">24</span>
            </a>
            <a href="#" class="h-nav-item" data-view="flujo">
              <i class="ti ti-arrows-exchange"></i><span>Flujo</span>
            </a>

            <div class="h-nav-group open">
              <button type="button" class="h-nav-item h-nav-group-toggle" aria-expanded="true">
                <i class="ti ti-chart-bar"></i><span>Reportes</span>
                <i class="ti ti-chevron-down h-caret"></i>
              </button>
              <div class="h-subnav">
                <a href="#" class="h-nav-item"><i class="ti ti-point"></i><span>Reportes totales</span></a>
                <a href="#" class="h-nav-item"><i class="ti ti-point"></i><span>Utilidades</span></a>
                <a href="#" class="h-nav-item"><i class="ti ti-point"></i><span>Servicios</span></a>
              </div>
            </div>

            <div class="h-section">OPERACIONES</div>
            <a href="#" class="h-nav-item"><i class="ti ti-crown"></i><span>Capital Amiri</span></a>
            <a href="#" class="h-nav-item"><i class="ti ti-file-dollar"></i><span>Gastos</span></a>
            <a href="#" class="h-nav-item"><i class="ti ti-user-plus"></i><span>Promotores</span></a>
            <a href="#" class="h-nav-item"><i class="ti ti-building-store"></i><span>Proveedores</span></a>
            <a href="#" class="h-nav-item"><i class="ti ti-brand-cashapp"></i><span>Prestamistas</span></a>
          </nav>

          <div class="h-footer-nav">
            <a href="#" class="h-footer-item"><i class="ti ti-settings"></i><span>Ajustes</span></a>
            <a href="#" class="h-footer-item"><i class="ti ti-support"></i><span>Soporte</span></a>
          </div>
        </div>
      </aside>

      <main class="h-main">

        <!-- Bienvenida + acciones -->
        <header class="h-welcome">
          <div>
            <h1>Hola, <?php echo htmlspecialchars(explode(' ', $usuario_nombre)[0], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Tu portafolio financiero en un vistazo. <strong class="h-muted"><?php echo htmlspecialchars($mes_activo); ?></strong></p>
          </div>
          <div class="h-welcome-actions">
            <button class="h-theme-toggle" id="hThemeToggle" aria-label="Cambiar tema">
              <i class="ti ti-moon"></i><span id="hThemeLabel">Oscuro</span>
            </button>
            <div class="h-avatar"><?php echo htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8'); ?></div>
            <span class="h-muted" style="font-size:0.88rem;"><?php echo htmlspecialchars($rol_label, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </header>

        <!-- Barra de controles -->
        <div class="h-controlbar">
          <div class="h-pills">
            <button class="h-pill active" id="pillResumen">Resumen</button>
            <button class="h-pill" id="pillBilletera">Billetera</button>
            <button class="h-pill" id="pillHerramientas">Herramientas</button>
          </div>
          <div class="h-search">
            <i class="ti ti-search"></i>
            <input type="text" id="hGlobalSearch" placeholder="Buscar movimientos, clientes..." aria-label="Buscar">
          </div>
        </div>

        <!-- Fila KPI (3 columnas visibles) -->
        <div class="h-kpi-grid">
          <?php foreach (array_slice($mock['kpis'], 0, 3) as $k): ?>
          <div class="h-kpi">
            <div class="h-kpi-accent" style="background: <?= $k['changeType']==='pos' ? 'var(--h-success)' : ($k['changeType']==='neg' ? 'var(--h-danger)' : 'var(--h-warn)') ?>;"></div>
            <div class="h-kpi-label"><?php echo htmlspecialchars($k['label']); ?></div>
            <div class="h-kpi-value"><?php echo fmt($k['value']); ?></div>
            <div class="h-kpi-change <?= $k['changeType']==='pos'?'pos':($k['changeType']==='neg'?'neg':'') ?>">
              <i class="ti ti-<?php echo $k['changeType']==='pos'?'arrow-up-right':($k['changeType']==='neg'?'arrow-down-right':'alert-circle'); ?>"></i>
              <?php echo htmlspecialchars($k['change']); ?>
            </div>
            <div class="h-kpi-note"><?php echo htmlspecialchars($k['note']); ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Fila intermedia: Watchlist + Mini portafolio -->
        <div class="h-grid-mid" style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">
          <!-- Watchlist -->
          <div class="h-card">
            <div class="h-card-body">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h5 class="h-card-title">Watchlist</h5>
                <span class="h-pill" style="font-size:0.78rem;padding:4px 10px;">Más vistos</span>
              </div>
              <table class="h-table">
                <thead>
                  <tr><th style="width:44%">Activo</th><th style="width:22%">Mercado</th><th style="width:34%" style="text-align:right">Valor</th></tr>
                </thead>
                <tbody>
                  <?php
                  $wl = [
                    ['nombre'=>'Spotify','mercado'=>'NYSE SPOT','valor'=>'$11770.3','cambio'=>'+16.51%','pos'=>true],
                    ['nombre'=>'Amazon','mercado'=>'NASDAQ AMZN','valor'=>'$10280.8','cambio'=>'+8.11%','pos'=>true],
                    ['nombre'=>'MSFT','mercado'=>'NASDAQ MSFT','valor'=>'$8510.2','cambio'=>'+4.89%','pos'=>true],
                    ['nombre'=>'NVDA','mercado'=>'NYSE NVDA','valor'=>'$2110.2','cambio'=>'+2.12%','pos'=>true],
                  ];
                  foreach ($wl as $w):
                  ?>
                  <tr>
                    <td>
                      <div style="display:flex;align-items:center;gap:8px;">
                        <span class="h-muted" style="font-size:0.82rem;"><?php echo htmlspecialchars($w['nombre']); ?></div>
                      </td>
                      <td class="h-muted" style="font-size:0.82rem;"><?php echo htmlspecialchars($w['mercado']); ?></td>
                      <td style="text-align:right;">
                        <div style="display:flex;flex-direction:column;align-items:flex-end;">
                          <span class="h-portfolio-value"><?php echo htmlspecialchars($w['valor']); ?></span>
                          <span class="h-portfolio-change <?= $w['pos']?'pos':'neg' ?>">
                            <i class="ti ti-<?php echo $w['pos']?'arrow-up':'arrow-down'; ?>"></i> <?php echo htmlspecialchars($w['cambio']); ?>
                          </span>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Mini portafolio -->
          <div class="h-card" style="min-height:0;">
            <div class="h-card-body">
              <h5 class="h-card-title">Mi portafolio</h5>
              <div class="h-portfolio-grid">
                <?php foreach ($mock['accounts'] as $a): ?>
                <div class="h-portfolio-card">
                  <div class="h-portfolio-avatar"><?php echo strtoupper(substr(htmlspecialchars($a['bank']),0,1)); ?></div>
                  <div class="h-portfolio-info">
                    <span class="h-portfolio-name"><?php echo htmlspecialchars($a['type']); ?></span>
                    <span class="h-portfolio-units"><?php echo htmlspecialchars($a['bank']); ?> · •••• <?php echo htmlspecialchars($a['last4']); ?></span>
                  </div>
                  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px;">
                    <span class="h-portfolio-value"><?php echo fmt($a['balance']); ?></span>
                    <span class="h-portfolio-change pos">
                      <i class="ti ti-arrow-up"></i> +2.1%
                    </span>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfico de área ancho completo -->
        <div class="h-card h-chart-card">
          <div class="h-card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <h5 class="h-card-title">Rendimiento del portafolio</h5>
              <div class="h-chart-controls">
                <button class="h-chart-toggle active">1D</button>
                <button class="h-chart-toggle">1W</button>
                <button class="h-chart-toggle">1M</button>
                <button class="h-chart-toggle">6M</button>
                <button class="h-chart-toggle">1Y</button>
              </div>
            </div>
            <canvas id="hChartArea"></canvas>
          </div>
        </div>

        <!-- Pie de prueba -->
        <div style="padding:18px;color:var(--h-text-muted);font-size:0.8rem;border-top:1px solid var(--h-border);text-align:center;">
          Prueba visual Helios — datos simulados. No afecta al dashboard real.
        </div>

      </main>
    </div>
  </div>

  <script>
    (function(){
      'use strict';

      var data = <?php echo json_encode($mock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      var app = document.getElementById('hApp');
      var sidebar = document.getElementById('hSidebar');
      var overlay = document.getElementById('hOverlay');
      var isMobile = window.innerWidth < 992;

      // ---------- Tema ----------
      var themeBtn = document.getElementById('hThemeToggle');
      var themeLabel = document.getElementById('hThemeLabel');
      function getTheme(){ return localStorage.getItem('heliosDark') === 'true' ? 'dark' : 'light'; }
      function setTheme(t){
        localStorage.setItem('heliosDark', t === 'dark' ? 'true' : 'false');
        document.documentElement.setAttribute('data-bs-theme', t);
        if (t === 'dark') document.body.classList.add('h-body'); else document.body.classList.remove('h-body');
        themeBtn.innerHTML = t === 'dark'
          ? '<i class="ti ti-sun"></i><span id="hThemeLabel">Claro</span>'
          : '<i class="ti ti-moon"></i><span id="hThemeLabel">Oscuro</span>';
        themeBtn.setAttribute('aria-label', t === 'dark' ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
        renderChart();
      }
      themeBtn.addEventListener('click', function(){ setTheme(getTheme() === 'dark' ? 'light' : 'dark'); });

      // ---------- Sidebar ----------
      function toggleSidebar(force){
        var open;
        if (typeof force === 'boolean') open = force;
        else open = !sidebar.classList.contains('open');
        if (isMobile) {
          sidebar.classList.toggle('open', open);
          overlay.classList.toggle('active', open);
        } else {
          app.classList.toggle('h-sidebar-collapsed', !open);
        }
      }
      // En desktop inicial: expandido; móvil: cerrado
      if (!isMobile) sidebar.classList.add('open');
      overlay.addEventListener('click', function(){ toggleSidebar(false); });

      // ---------- Nav activo ----------
      document.querySelectorAll('.h-nav-item').forEach(function(item){
        item.addEventListener('click', function(e){
          if (item.classList.contains('h-nav-group-toggle')) return;
          document.querySelectorAll('.h-nav-item.active').forEach(function(el){ el.classList.remove('active'); });
          item.classList.add('active');
          if (isMobile) toggleSidebar(false);
        });
      });
      document.querySelectorAll('.h-nav-group-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
          var group = btn.parentElement;
          var open = group.classList.toggle('open');
          btn.setAttribute('aria-expanded', open ? 'true' : 'false');
          var caret = btn.querySelector('.h-caret');
          if (caret) caret.style.transform = open ? 'rotate(180deg)' : '';
        });
      });

      // ---------- Pills ----------
      document.querySelectorAll('.h-pill').forEach(function(pill){
        pill.addEventListener('click', function(){
          document.querySelectorAll('.h-pill.active').forEach(function(el){ el.classList.remove('active'); });
          this.classList.add('active');
        });
      });
      document.querySelectorAll('.h-chart-toggle').forEach(function(t){
        t.addEventListener('click', function(){
          document.querySelectorAll('.h-chart-toggle.active').forEach(function(el){ el.classList.remove('active'); });
          this.classList.add('active');
          renderChart();
        });
      });

      // ---------- Chart (Canvas API puro, sin librerías externas) ----------
      var canvas = document.getElementById('hChartArea');
      var ctx = canvas.getContext('2d');
      var dpr = window.devicePixelRatio || 1;
      function resizeCanvas(){
        var rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = 300 * dpr;
        ctx.scale(dpr, dpr);
        canvas.style.width = rect.width + 'px';
        canvas.style.height = '300px';
        renderChart();
      }
      window.addEventListener('resize', resizeCanvas);
      window.addEventListener('load', resizeCanvas);

      function fmtMoney(n){
        return '$' + Number(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
      }

      function renderChart(){
        if (!canvas) return;
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        var cats = data.cashflow.categories;
        var inc = data.cashflow.ingresos;
        var egr = data.cashflow.egresos;
        var n = cats.length;

        var net = inc.map(function(v,i){ return v - egr[i]; });
        var max = Math.max.apply(null, net);
        var min = Math.min.apply(null, net);
        var pad = (max - min) * 0.25 || max * 0.25 || 100000;
        var top = max + pad;
        var bot = min - pad;

        var x0 = 60, x1 = w - 40;
        var yTop = 28, yBot = h - 46;

        function px(i){ return x0 + (i / (n - 1)) * (x1 - x0); }
        function py(v){ return yBot - ((v - bot) / (top - bot)) * (yBot - yTop); }

        var grad = ctx.createLinearGradient(0, yTop, 0, yBot);
        grad.addColorStop(0, 'rgba(197,143,184,0.35)');
        grad.addColorStop(1, 'rgba(197,143,184,0.02)');

        ctx.beginPath();
        ctx.moveTo(x0, yBot);
        for (var i=0;i<n;i++){ ctx.lineTo(px(i), py(net[i])); }
        ctx.lineTo(x1, yBot);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        ctx.beginPath();
        for (var i=0;i<n;i++){ ctx.lineTo(px(i), py(net[i])); }
        ctx.strokeStyle = '#c58fb8';
        ctx.lineWidth = 2;
        ctx.lineJoin = 'round';
        ctx.stroke();

        for (var i=0;i<n;i++){
          var x = px(i), y = py(net[i]);
          ctx.beginPath();
          ctx.arc(x, y, 3.5, 0, Math.PI*2);
          ctx.fillStyle = '#fff';
          ctx.fill();
          ctx.strokeStyle = '#c58fb8';
          ctx.lineWidth = 1.5;
          ctx.stroke();
        }

        ctx.fillStyle = '#9a9096';
        ctx.font = '11px Montserrat, sans-serif';
        ctx.textAlign = 'right';
        for (var t=0;t<=4;t++){
          var v = bot + (top - bot) * (t/4);
          var y = py(v);
          ctx.fillText(fmtMoney(v).replace('$',''), x0 - 10, y + 4);
        }

        ctx.textAlign = 'center';
        for (var i=0;i<n;i+=2){
          ctx.fillText(cats[i], px(i), yBot + 18);
        }
      }

    })();
  </script>
</body>
</html>
