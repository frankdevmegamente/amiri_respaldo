<!-- src/assets/partials/header-nexo.php — Topbar nexo (piloto) -->
<?php
// Asegurar variables básicas
if (!isset($pagina_actual)) {
    $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';
}
$nombre_usuario = $nombre_usuario ?? ($_SESSION['usuario_nombre'] ?? 'Usuario');
$usuario_rol = $usuario_rol ?? ($_SESSION['usuario_rol'] ?? 'usuario');
?>
<header class="nx-topbar">
  <div class="nx-topbar-left">
    <button class="nx-icon-btn d-lg-none" id="mobileMenuBtn" type="button" aria-label="Abrir menú">
      <i class="ti ti-menu-2"></i>
    </button>
    <button class="nx-icon-btn d-none d-lg-inline-flex" id="sidebarToggle" type="button" title="Colapsar menú" aria-label="Colapsar menú">
      <i class="ti ti-chevron-left"></i>
    </button>
    <div class="nx-breadcrumb">Mi espacio / <strong id="breadcrumbCurrent">Dashboard</strong></div>
  </div>

  <div class="nx-topbar-right">
    <!-- Búsqueda -->
    <div class="nx-search d-none d-md-block">
      <i class="ti ti-search"></i>
      <input type="text" id="globalSearch" placeholder="Buscar movimientos, clientes..." aria-label="Buscar movimientos y clientes">
    </div>

    <!-- Selector de mes informativo -->
    <?php
    $mesActivo = $_SESSION['mes_activo'] ?? date('Y-m');
    $anioActual = (int)date('Y');
    $mesNumActual = (int)date('m');
    $nombresMes = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    $labelMes = $nombresMes[substr($mesActivo, 5, 2)] . ' ' . substr($mesActivo, 0, 4);

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
    <div class="dropdown d-none d-xl-block">
      <button class="nx-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Mes activo — solo informativo" style="width:auto;padding:0 10px;gap:6px;">
        <i class="ti ti-calendar"></i>
        <span style="font-size:12px;font-weight:600;"><?php echo htmlspecialchars($labelMes, ENT_QUOTES, 'UTF-8'); ?></span>
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
        for ($anio = 2026; $anio <= (int)date('Y'); $anio++):
            $mesInicio = ($anio === 2026) ? 4 : 1;
            $mesFin = ($anio === (int)date('Y')) ? (int)date('m') : 12;
            if ($mesInicio > $mesFin) continue;
        ?>
          <li class="dropdown-item-text small fw-bold text-muted px-3 pt-2 pb-0" style="cursor:default;font-size:0.65rem;letter-spacing:0.5px;text-transform:uppercase;"><?php echo $anio; ?></li>
          <?php for ($m = $mesInicio; $m <= $mesFin; $m++):
              $mesKey = sprintf('%04d-%02d', $anio, $m);
              $esActual = ($mesKey === $mesActivo);
              $cerrado = in_array($mesKey, $mesesCerrados);
          ?>
            <li class="dropdown-item d-flex align-items-center gap-2 px-3" style="cursor:default;<?php echo $esActual ? 'background:var(--nx-primary-soft);border-left:3px solid var(--nx-primary);' : ''; ?>">
              <i class="ti ti-<?php echo $cerrado ? 'lock' : 'circle-check'; ?> text-<?php echo $cerrado ? 'danger' : 'success'; ?>"></i>
              <span class="flex-grow-1"><?php echo $nombresMes[sprintf('%02d', $m)] . ' ' . $anio; ?></span>
              <?php if ($esActual): ?>
                <span class="badge rounded-pill" style="background:var(--nx-primary);color:#fff;font-size:0.55rem;font-weight:600;">actual</span>
              <?php endif; ?>
            </li>
          <?php endfor; endfor; ?>
      </ul>
    </div>

    <!-- Toggle tema -->
    <button class="nx-theme-toggle" id="themeToggle" type="button" aria-label="Cambiar tema">
      <i class="ti ti-moon"></i><span>Oscuro</span>
    </button>

    <!-- Pantalla completa -->
    <button class="nx-icon-btn" id="fullscreenToggle" type="button" title="Pantalla completa" aria-label="Pantalla completa">
      <i class="ti ti-maximize"></i>
    </button>

    <!-- Notificaciones -->
    <button class="nx-icon-btn" type="button" title="Notificaciones" aria-label="Notificaciones">
      <i class="ti ti-bell"></i>
    </button>

    <!-- Perfil usuario -->
    <div class="dropdown">
      <div class="nx-user" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" role="button">
        <?php
        $iniciales = '';
        $parts = preg_split('/\s+/u', trim($nombre_usuario), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $p) {
            $iniciales .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
            if (mb_strlen($iniciales, 'UTF-8') >= 2) break;
        }
        if ($iniciales === '') $iniciales = 'AM';
        ?>
        <span class="nx-avatar"><?php echo htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="nx-user-meta d-none d-xl-block">
          <div class="nx-user-name"><?php echo htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="nx-user-role"><?php echo htmlspecialchars(($usuario_rol === 'admin') ? 'Administrador' : (($usuario_rol === 'promotor') ? 'Promotor' : 'Usuario'), ENT_QUOTES, 'UTF-8'); ?></div>
        </span>
        <i class="ti ti-chevron-down nx-muted d-none d-md-inline"></i>
      </div>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="editar-perfil.php"><i class="ti ti-user me-2"></i>Mi perfil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="ti ti-logout me-2"></i>Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</header>
