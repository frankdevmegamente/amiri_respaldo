<!-- src/html/partials/sidebar.php -->

<?php

// Asegurar que exista la variable $pagina_actual para evitar warnings en logs

if (!isset($pagina_actual)) {

  $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';

}

?>

<aside class="left-sidebar">

  <div class="brand-logo d-flex align-items-center justify-content-center border-bottom border-light border-opacity-10 py-2">
    <a href="./index.php" class="text-nowrap logo-img">
      <img src="src/assets/images/logos/logo2.png" alt="Amiri App Logo" class="logo-modo-claro" style="max-width: 100%; width: auto; height: 40px;" />
      <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Amiri App Logo" class="logo-modo-oscuro" style="max-width: 100%; width: auto; height: 40px; display: none;" />
    </a>

    <ul class="navbar-nav">

      <li class="nav-item d-block d-xl-none">

        <button type="button" class="nav-link sidebartoggler" aria-label="Alternar menú">

          <i class="ti ti-menu-2"></i>

        </button>

      </li>

    </ul>



  </div>

  <div class="scroll-sidebar" data-simplebar>

    <nav class="sidebar-nav">



      <ul id="sidebarnav">

        <li class="nav-small-cap">

          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>

          <span class="hide-menu">General</span>

        </li>

        <li class="sidebar-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">

          <a class="sidebar-link" href="index.php" aria-expanded="false">

            <i class="ti ti-layout-dashboard"></i>

            <span class="hide-menu">Dashboard</span>

          </a>

        </li>

        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>

          <li class="nav-small-cap">

            <span class="hide-menu">Administración</span>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['flujo.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="flujo.php" aria-expanded="false">

              <i class="ti ti-arrows-exchange"></i>

              <span class="hide-menu">Flujo</span>

            </a>

          </li>

          <!-- <li class="sidebar-item <?= in_array($pagina_actual, ['flujo-divisas.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="flujo-divisas.php" aria-expanded="false">

              <i class="ti ti-currency-dollar"></i>

              <span class="hide-menu">Flujo Divisas</span>

            </a>

          </li> -->

          <li class="sidebar-item <?= in_array($pagina_actual, ['cuentas.php', 'cuentas_bancarias.php', 'cuentas_cajas.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="cuentas.php" aria-expanded="false">

              <i class="ti ti-wallet"></i>

              <span class="hide-menu">Cuentas</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['capital-amiri.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="capital-amiri.php" aria-expanded="false">

              <i class="ti ti-crown"></i>

              <span class="hide-menu">Capital Amiri</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['clientes.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="clientes.php" aria-expanded="false">

              <i class="ti ti-users"></i>

              <span class="hide-menu">Clientes</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['promotores.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="promotores.php" aria-expanded="false">

              <i class="ti ti-user-plus"></i>

              <span class="hide-menu">Promotores</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['proveedores.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="proveedores.php" aria-expanded="false">

              <i class="ti ti-building-store"></i>

              <span class="hide-menu">Proveedores</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['prestamistas.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="prestamistas.php" aria-expanded="false">

              <i class="ti ti-brand-cashapp"></i>

              <span class="hide-menu">Prestamistas</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['prestamos-otorgados.php']) ? 'active' : '' ?>">
            <a class="sidebar-link" href="prestamos-otorgados.php" aria-expanded="false">
              <i class="ti ti-cash-banknote"></i>
              <span class="hide-menu">Préstamos Otorgados</span>
            </a>
          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['gastos.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="gastos.php" aria-expanded="false">

              <i class="ti ti-file-dollar"></i>

              <span class="hide-menu">Gastos</span>

            </a>

          </li>
          
          <li class="sidebar-item <?= in_array($pagina_actual, ['usuarios.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="usuarios.php" aria-expanded="false">

              <i class="ti ti-user"></i>

              <span class="hide-menu">Usuarios</span>

            </a>

          </li>

        <li class="sidebar-item">
    <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
        <span>
            <i class="ti ti-tools"></i> </span>
        <span class="hide-menu">Generadores</span>
    </a>
    <ul aria-expanded="false" class="collapse first-level">
        
        <li class="sidebar-item">
            <a href="generador-recibos.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i> </div>
                <span class="hide-menu">Cotizador Divisas</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="generador-depositos.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i>
                </div>
                <span class="hide-menu">Depósito Efectivo</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="generador-transferencias.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i>
                </div>
                <span class="hide-menu">Depósito Transferencia</span>
            </a>
        </li>

        </ul>
</li>

<li class="sidebar-item">
    <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
        <span>
            <i class="ti ti-chart-bar"></i>
        </span>
        <span class="hide-menu">Reportes</span>
    </a>
    <ul aria-expanded="false" class="collapse first-level">
        <li class="sidebar-item <?= in_array($pagina_actual, ['reportes.php']) ? 'active' : '' ?>">
            <a href="reportes.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i>
                </div>
                <span class="hide-menu">Reportes Totales</span>
            </a>
        </li>
        <li class="sidebar-item <?= in_array($pagina_actual, ['reportes-utilidades.php']) ? 'active' : '' ?>">
            <a href="reportes-utilidades.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i>
                </div>
                <span class="hide-menu">Utilidades</span>
            </a>
        </li>
        <li class="sidebar-item <?= in_array($pagina_actual, ['reportes-servicios.php']) ? 'active' : '' ?>">
            <a href="reportes-servicios.php" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle fs-2"></i>
                </div>
                <span class="hide-menu">Servicios</span>
            </a>
        </li>
    </ul>
</li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['auditoria.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="auditoria.php" aria-expanded="false">

              <i class="ti ti-file-search"></i>

              <span class="hide-menu">Auditoría</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['monitor.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="monitor.php" aria-expanded="false">

              <i class="ti ti-activity"></i>

              <span class="hide-menu">Monitor</span>

            </a>

          </li>

          <li class="sidebar-item <?= in_array($pagina_actual, ['fifo.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="fifo.php" aria-expanded="false">

              <i class="ti ti-currency-tenge"></i>

              <span class="hide-menu">FIFO</span>

            </a>

          </li>

        <?php endif; ?>

        <hr>

        <!-- Logout -->

        <li class="sidebar-item mt-4">

          <a class="sidebar-link text-danger" href="logout.php" aria-expanded="false">

            <i class="ti ti-logout"></i>

            <span class="hide-menu">Cerrar sesión</span>

          </a>

        </li>

      </ul>

    </nav>

  </div>

</aside>

<!-- Modal de sesión próxima a expirar -->
<div class="modal fade" id="modalSessionTimeout" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalSessionTimeoutLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title w-100 text-center" id="modalSessionTimeoutLabel">
          <i class="ti ti-clock-hour-4" style="font-size:3rem;color:#ffc107;"></i>
        </h5>
      </div>
      <div class="modal-body text-center pt-3">
        <h4 class="fw-bold mb-3">Sesi&oacute;n pr&oacute;xima a expirar</h4>
        <p class="text-muted mb-0">Has estado 3 horas sin actividad en el sistema.</p>
        <p class="text-muted">¿Deseas continuar utilizando la plataforma?</p>
      </div>
      <div class="modal-footer border-0 justify-content-center pt-0">
        <button id="btnExtendSession" class="btn btn-primary btn-lg px-5">
          <i class="ti ti-check me-2"></i>S&iacute;, continuar
        </button>
        <button id="btnLogoutSession" class="btn btn-outline-danger btn-lg px-4">
          <i class="ti ti-logout me-2"></i>Cerrar sesi&oacute;n
        </button>
      </div>
    </div>
  </div>
</div>

<button type="button" class="sidebar-overlay d-xl-none sidebartoggler" aria-label="Cerrar menú"></button>



<script>
  (function() {
    var STORAGE_KEY = 'amiri_sidebar_mini';
    var wrapper = document.getElementById('main-wrapper');

    // Restaurar estado inmediatamente (sin flash)
    if (wrapper && localStorage.getItem(STORAGE_KEY) === '1') {
      wrapper.classList.add('sidebar-mini');
      wrapper.setAttribute('data-sidebartype', 'mini-sidebar'); 
    }

    window.addEventListener('load', function() {
      var btn = document.getElementById('sidebarCollapseBtn');
      
      function initTooltips() {
        document.querySelectorAll('#sidebarnav .sidebar-link').forEach(function(el) {
          var span = el.querySelector('.hide-menu');
          if (!span || !window.bootstrap) return;
          el.setAttribute('title', span.textContent.trim());
          el.setAttribute('data-bs-toggle', 'tooltip');
          el.setAttribute('data-bs-placement', 'right');
          bootstrap.Tooltip.getOrCreateInstance(el);
        });
      }

      function destroyTooltips() {
        document.querySelectorAll('#sidebarnav .sidebar-link').forEach(function(el) {
          var instance = window.bootstrap && bootstrap.Tooltip.getInstance(el);
          if (instance) instance.dispose();
          el.removeAttribute('data-bs-toggle');
          el.removeAttribute('title');
        });
      }

      if (wrapper && wrapper.classList.contains('sidebar-mini')) initTooltips();

      if (btn && wrapper) {
          btn.addEventListener('click', function() {
            var isMini = wrapper.classList.toggle('sidebar-mini');
            
            wrapper.setAttribute('data-sidebartype', isMini ? 'mini-sidebar' : 'full');
            
            localStorage.setItem(STORAGE_KEY, isMini ? '1' : '0');
            if (isMini) initTooltips();
            else destroyTooltips();
          });
      }
      
      // --- CONTROLADOR DE HAMBURGUESA CORREGIDO ---
      var botonesMenu = document.querySelectorAll('.sidebartoggler');
      
      botonesMenu.forEach(function(boton) {
        boton.addEventListener('click', function(e) {
          e.preventDefault();
          if (wrapper) {
            wrapper.classList.toggle('show-sidebar');
          }
        });
      });
      
    });
  })();
</script>

<script src="src/assets/js/session-timeout.js"></script>
