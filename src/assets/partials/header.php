<?php
// src/assets/partials/header.php — Helios shell (shared)
// Maintains legacy IDs used by existing pages/scripts where possible.
if (!isset($pagina_actual)) {
    $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';
}
$nombre_usuario = $nombre_usuario ?? ($_SESSION['usuario_nombre'] ?? 'Usuario');
$usuario_rol = $usuario_rol ?? ($_SESSION['usuario_rol'] ?? 'usuario');
?>
<!-- Helios shell assets (injected for legacy pages that don't include them in <head>) -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="src/assets/css/nexo.css?v=helios25" />
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
<style>.left-sidebar,.app-topstrip{display:none!important}body{font-family:var(--nx-font,'Montserrat',sans-serif)!important;}</style>
<script>document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('nexo-body');});</script>

<header class="nx-topbar">
  <div class="nx-topbar-left">
    <button class="nx-icon-btn d-lg-none sidebartoggler" id="mobileMenuBtn" type="button" aria-label="Abrir menú">
      <i class="ti ti-menu-2"></i>
    </button>
    <button class="nx-icon-btn d-none d-lg-inline-flex" id="sidebarToggle" type="button" title="Colapsar menú" aria-label="Colapsar menú">
      <i class="ti ti-chevron-left"></i>
    </button>
    <div class="nx-breadcrumb">Mi espacio / <strong id="breadcrumbCurrent"><?php echo htmlspecialchars(ucfirst(str_replace(['.php','-','_'], ['',' ',' '], $pagina_actual)) ?: 'Página'); ?></strong></div>
  </div>

  <div class="nx-topbar-right">
    <div class="nx-search d-none d-md-block">
      <i class="ti ti-search"></i>
      <input type="text" id="globalSearch" placeholder="Buscar..." aria-label="Buscar">
    </div>

    <?php
    $mesActivo = $_SESSION['mes_activo'] ?? date('Y-m');
    $anioActual = (int)date('Y');
    $mesNumActual = (int)date('m');
    $nombresMes = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    $labelMes = ($nombresMes[substr($mesActivo, 5, 2)] ?? '') . ' ' . substr($mesActivo, 0, 4);
    $mesesCerrados = [];
    $headerPdoPath = __DIR__ . '/../../config/db.php';
    if (file_exists($headerPdoPath)) {
        require_once $headerPdoPath;
        try {
            $pdoHeader = getPDO();
            $stmt = $pdoHeader->query("SELECT mes FROM cierres_mensuales");
            $mesesCerrados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {}
    }
    $cerradoActivo = in_array($mesActivo, $mesesCerrados);
    ?>
    <div class="dropdown" id="selectorMesActivo">
      <button class="nx-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false"
        title="Mes actual: <?php echo htmlspecialchars($labelMes); ?>" style="width:auto;padding:0 12px;gap:6px;border-radius:999px;">
        <i class="ti ti-calendar"></i>
        <span id="mesActivoLabel" class="d-none d-sm-inline" style="font-size:12px;font-weight:600;"><?php echo htmlspecialchars($labelMes); ?></span>
        <?php if ($cerradoActivo): ?>
          <i class="ti ti-lock" style="font-size:12px;color:var(--nx-danger);"></i>
        <?php else: ?>
          <i class="ti ti-circle-check" style="font-size:12px;color:var(--nx-success);"></i>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end py-0" style="max-height:350px;overflow-y:auto;min-width:220px;">
        <li class="dropdown-header small text-muted py-2 px-3 border-bottom" style="cursor:default;background:var(--nx-surface-2);">
          <i class="ti ti-info-circle me-1"></i> Estados de meses
          <span class="float-end"><span style="color:var(--nx-success)">●</span> abierto <span style="color:var(--nx-danger)" class="ms-1">●</span> cerrado</span>
        </li>
        <?php
        for ($anio = 2026; $anio <= $anioActual; $anio++):
            $mesInicio = ($anio === 2026) ? 4 : 1;
            $mesFin = ($anio === $anioActual) ? $mesNumActual : 12;
            if ($mesInicio > $mesFin) continue;
        ?>
          <li class="dropdown-item-text small fw-bold text-muted px-3 pt-2 pb-0" style="cursor:default;font-size:0.65rem;letter-spacing:0.5px;text-transform:uppercase;"><?php echo $anio; ?></li>
          <?php for ($m = $mesInicio; $m <= $mesFin; $m++):
              $mesKey = sprintf('%04d-%02d', $anio, $m);
              $esActual = ($mesKey === $mesActivo);
              $cerrado = in_array($mesKey, $mesesCerrados);
          ?>
            <li class="dropdown-item d-flex align-items-center gap-2 px-3" style="cursor:default;<?php echo $esActual ? 'background:var(--nx-primary-soft);border-left:3px solid var(--nx-primary);' : ''; ?>">
              <i class="ti ti-<?php echo $cerrado ? 'lock' : 'circle-check'; ?>" style="color:var(--nx-<?php echo $cerrado ? 'danger' : 'success'; ?>);"></i>
              <span class="flex-grow-1"><?php echo ($nombresMes[sprintf('%02d', $m)] ?? '') . ' ' . $anio; ?></span>
              <?php if ($esActual): ?>
                <span class="badge rounded-pill" style="background:var(--nx-primary);color:#fff;font-size:0.55rem;font-weight:600;">actual</span>
              <?php endif; ?>
            </li>
        <?php endfor; endfor; ?>
      </ul>
    </div>

    <button class="nx-icon-btn" id="fullscreenToggle" type="button" title="Pantalla completa" aria-label="Pantalla completa">
      <i class="ti ti-maximize"></i>
    </button>

    <button class="nx-theme-toggle" id="themeToggle" type="button" title="Cambiar tema" aria-label="Cambiar tema">
      <i class="ti ti-moon"></i><span>Oscuro</span>
    </button>

    <div class="dropdown">
      <button class="nx-user border-0 bg-transparent p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario" style="cursor:pointer;">
        <span class="nx-avatar"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($nombre_usuario, 0, 1))); ?></span>
        <span class="nx-user-meta d-none d-sm-block text-start">
          <div class="nx-user-name"><?php echo htmlspecialchars($nombre_usuario); ?></div>
          <div class="nx-user-role"><?php echo htmlspecialchars($usuario_rol); ?></div>
        </span>
        <i class="ti ti-chevron-down nx-muted d-none d-sm-inline" style="font-size:14px"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width:180px">
        <li><a class="dropdown-item" href="editar-perfil.php"><i class="ti ti-user me-2"></i>Mi perfil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="ti ti-logout me-2"></i>Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</header>
