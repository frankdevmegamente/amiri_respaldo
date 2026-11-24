<?php
// src/assets/partials/helios-assets.php
// Punto único de carga de assets Helios. Se incluye desde header.php
// (fallback para páginas legacy) y puede incluirse al final de <head>.
$heliosAssetsLoaded = true;
$heliosCssVersion = 'helios35';
$heliosDownloadablesVersion = 'helios9';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="src/assets/css/nexo.css?v=<?php echo $heliosCssVersion; ?>" />
<link rel="stylesheet" href="src/assets/css/descargables-helios.css?v=<?php echo $heliosDownloadablesVersion; ?>" />
<script>
(function(){
  try {
    var saved = localStorage.getItem('darkMode');
    var dark = saved === null ? true : saved === 'true';
    document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    localStorage.setItem('darkMode', dark ? 'true' : 'false');
  } catch(e) {
    document.documentElement.setAttribute('data-bs-theme','dark');
  }
})();

/* Chart.js + selector de tema: el cambio aplica al instante, sin recargar. */
(function () {
  var DARK = {
    text: 'rgba(255,255,255,0.68)',
    grid: 'rgba(255,255,255,0.14)',
    border: 'rgba(255,255,255,0.12)',
    ticks: 'rgba(255,255,255,0.62)'
  };
  var originalDefaults = null;
  var lightGridOverride = null;

  function isDark() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark';
  }
  function captureDefaults() {
    if (originalDefaults || !window.Chart) return;
    originalDefaults = {
      color: Chart.defaults.color,
      borderColor: Chart.defaults.borderColor,
      gridColor: Chart.defaults.scale && Chart.defaults.scale.grid ? Chart.defaults.scale.grid.color : null,
      gridDrawBorder: Chart.defaults.scale && Chart.defaults.scale.grid ? Chart.defaults.scale.grid.drawBorder : null,
      ticksColor: Chart.defaults.scale && Chart.defaults.scale.ticks ? Chart.defaults.scale.ticks.color : null
    };
  }
  function lightGrid() {
    return lightGridOverride || (originalDefaults && originalDefaults.gridColor) || 'rgba(0,0,0,0.10)';
  }
  function applyDefaults() {
    if (!window.Chart) return;
    captureDefaults();
    if (isDark()) {
      Chart.defaults.color = DARK.text;
      Chart.defaults.borderColor = DARK.border;
      if (Chart.defaults.scale && Chart.defaults.scale.grid) {
        Chart.defaults.scale.grid.color = DARK.grid;
        Chart.defaults.scale.grid.drawBorder = false;
      }
      if (Chart.defaults.scale && Chart.defaults.scale.ticks) {
        Chart.defaults.scale.ticks.color = DARK.ticks;
      }
      return;
    }
    Chart.defaults.color = originalDefaults.color;
    Chart.defaults.borderColor = originalDefaults.borderColor;
    if (Chart.defaults.scale && Chart.defaults.scale.grid) {
      Chart.defaults.scale.grid.color = originalDefaults.gridColor;
      Chart.defaults.scale.grid.drawBorder = originalDefaults.gridDrawBorder;
    }
    if (Chart.defaults.scale && Chart.defaults.scale.ticks) {
      Chart.defaults.scale.ticks.color = originalDefaults.ticksColor;
    }
  }
  function applyToCharts() {
    if (!window.Chart) return;
    var dark = isDark();
    Object.keys(Chart.instances || {}).forEach(function (key) {
      var chart = Chart.instances[key];
      var scales = (chart.options && chart.options.scales) || {};
      Object.keys(scales).forEach(function (name) {
        var scale = scales[name];
        if (!scale) return;
        if (scale.grid) scale.grid.color = dark ? DARK.grid : lightGrid();
        if (scale.ticks) scale.ticks.color = dark ? DARK.ticks : ((originalDefaults && originalDefaults.ticksColor) || '#666');
      });
      var legend = chart.options && chart.options.plugins && chart.options.plugins.legend;
      if (legend && legend.labels) {
        legend.labels.color = dark ? DARK.text : ((originalDefaults && originalDefaults.color) || '#666');
      }
      chart.update();
    });
  }

  window.nxChartGridColor = function (lightColor) {
    if (lightColor) lightGridOverride = lightColor;
    return isDark() ? DARK.grid : (lightColor || lightGrid());
  };
  window.nxChartTheme = function () {
    applyDefaults();
    applyToCharts();
  };

  function updateToggle() {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;
    var dark = isDark();
    btn.innerHTML = dark
      ? '<i class="ti ti-sun"></i><span>Claro</span>'
      : '<i class="ti ti-moon"></i><span>Oscuro</span>';
    btn.setAttribute('aria-label', dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
    btn.title = btn.getAttribute('aria-label');
  }
  // El toggle lo maneja nexo-shell.js; aquí solo reaccionamos al cambio de atributo.
  document.addEventListener('DOMContentLoaded', function () {
    updateToggle();
    window.nxChartTheme();
    if (!window.MutationObserver || !window.Chart) return;
    new MutationObserver(function () {
      updateToggle();
      window.nxChartTheme();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
  });
})();
</script>
