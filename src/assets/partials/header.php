 <!-- App Topstrip -->

 <div class="app-topstrip bg-dark py-2 px-3 d-flex align-items-center justify-content-between">

   <div class="d-flex align-items-center gap-3">

     <!-- Botón de menú (móviles) -->

     <button class="d-lg-none btn btn-link text-white p-0 me-2 sidebartoggler" id="headerCollapse" aria-label="Alternar menú">

       <i class="ti ti-menu-2 fs-5"></i>

     </button>

     <!-- Botón colapsar sidebar (desktop) -->

     <button class="d-none d-xl-block theme-toggle-btn" id="sidebarCollapseBtn" title="Colapsar/expandir menú" aria-label="Colapsar menú">

       <i class="ti ti-layout-sidebar fs-5"></i>

     </button>



     <!-- Logo -->

     <a class="d-flex align-items-center" href="index.php">

       <img src="src/assets/images/logos/dashboard.png" alt="Logo" width="120">

     </a>

   </div>



   <!-- Controles (derecha) -->

   <div class="d-flex align-items-center gap-3">

     <!-- Modo oscuro -->

     <button class="theme-toggle-btn" id="themeToggle" title="Modo oscuro">

       <i class="ti ti-moon"></i>

     </button>



      <!-- Pantalla completa -->

      <button class="fullscreen-btn" id="fullscreenToggle" title="Pantalla completa">

        <i class="ti ti-maximize"></i>

      </button>

      <!-- Selector de mes informativo -->
      <?php
      $mesActivo = $_SESSION['mes_activo'] ?? date('Y-m');
      $anioActual = (int)date('Y');
      $mesNumActual = (int)date('m');
      $nombresMes = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
      $label = $nombresMes[substr($mesActivo, 5, 2)] . ' ' . substr($mesActivo, 0, 4);

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
        <button class="btn btn-sm d-flex align-items-center gap-2 border-0 rounded-pill fw-medium px-3 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
          style="background:<?= $cerradoActivo ? 'rgba(220,53,69,0.15)' : 'rgba(25,135,84,0.15)' ?>;color:<?= $cerradoActivo ? '#dc3545' : '#198754' ?>;"
          title="Mes actual — solo informativo">
          <i class="ti ti-calendar fs-6"></i>
          <span id="mesActivoLabel"><?= $label ?></span>
          <?php if ($cerradoActivo): ?>
            <i class="ti ti-lock fs-6"></i>
          <?php else: ?>
            <i class="ti ti-circle-check fs-6"></i>
          <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end py-0" style="max-height:350px;overflow-y:auto;min-width:220px;">
          <li class="dropdown-header small text-muted py-2 px-3 border-bottom" style="cursor:default;background:#f8f9fa;">
            <i class="ti ti-info-circle me-1"></i> Estados de meses
            <span class="float-end"><span class="text-success">✅</span> abierto <span class="text-danger ms-1">🔒</span> cerrado</span>
          </li>
          <?php
          for ($anio = 2026; $anio <= $anioActual; $anio++):
              $mesInicio = ($anio === 2026) ? 4 : 1;
              $mesFin = ($anio === $anioActual) ? $mesNumActual : 12;
              if ($mesInicio > $mesFin) continue;
          ?>
            <li class="dropdown-item-text small fw-bold text-muted px-3 pt-2 pb-0" style="cursor:default;font-size:0.65rem;letter-spacing:0.5px;text-transform:uppercase;"><?= $anio ?></li>
            <?php for ($m = $mesInicio; $m <= $mesFin; $m++):
                $mesKey = sprintf('%04d-%02d', $anio, $m);
                $esActual = ($mesKey === $mesActivo);
                $cerrado = in_array($mesKey, $mesesCerrados);
            ?>
              <li class="dropdown-item d-flex align-items-center gap-2 px-3" style="cursor:default;<?= $esActual ? 'background:rgba(108,92,231,0.06);border-left:3px solid #6c5ce7;' : '' ?>">
                <i class="ti ti-<?= $cerrado ? 'lock' : 'circle-check' ?> text-<?= $cerrado ? 'danger' : 'success' ?>"></i>
                <span class="flex-grow-1"><?= $nombresMes[sprintf('%02d', $m)] . ' ' . $anio ?></span>
                <?php if ($esActual): ?>
                  <span class="badge rounded-pill" style="background:#6c5ce7;color:#fff;font-size:0.55rem;font-weight:600;">actual</span>
                <?php endif; ?>
              </li>
          <?php endfor; endfor; ?>
        </ul>
      </div>

       <!-- Nombre del usuario -->

      <div class="nav-item">

        <a class="user-name mb-0 text-white text-decoration-none" href="editar-perfil.php" title="Editar perfil">

          <i class="ti ti-user me-1"></i> <?= htmlspecialchars($nombre_usuario) ?>

        </a>

      </div>

      <!-- Cerrar sesión -->

      <div class="nav-item">

        <a class="user-name mb-0 text-white text-decoration-none" href="logout.php" title="Cerrar sesión">

          <i class="ti ti-logout"></i>

        </a>

      </div>

   </div>

 </div>