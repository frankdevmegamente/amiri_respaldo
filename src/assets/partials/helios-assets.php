<?php
// src/assets/partials/helios-assets.php
// Punto único de carga de assets Helios. Se incluye desde header.php
// (fallback para páginas legacy) y puede incluirse al final de <head>.
$heliosAssetsLoaded = true;
$heliosCssVersion = 'helios28';
$heliosDownloadablesVersion = 'helios6';
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
</script>
