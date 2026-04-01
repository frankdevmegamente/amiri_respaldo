<!-- src/html/partials/sidebar.php -->

<?php

// Asegurar que exista la variable $pagina_actual para evitar warnings en logs

if (!isset($pagina_actual)) {

  $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';

}

?>

<aside class="left-sidebar">

  <div class="brand-logo d-flex align-items-center justify-content-between">

    <a href="./index.php" class="text-nowrap logo-img">

      <img src="src/assets/images/logos/logo2-sin-fondo.png" alt="Logo Amiri" class="logo-modo-claro" style="height: 45px; margin-top:20px; max-width: 100%; object-fit: contain;" />

<img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; margin-top:20px; max-width: 100%; object-fit: contain; display: none;" />

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
            <i class="ti ti-photo-star"></i> </span>
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

          <li class="sidebar-item <?= in_array($pagina_actual, ['reportes.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="reportes.php" aria-expanded="false">

              <i class="ti ti-chart-bar"></i>

              <span class="hide-menu">Reportes</span>

            </a>

          </li>

          <!-- <li class="sidebar-item <?= in_array($pagina_actual, ['servicios.php']) ? 'active' : '' ?>">

            <a class="sidebar-link" href="servicios.php" aria-expanded="false">

              <i class="ti ti-clipboard"></i>

              <span class="hide-menu">Servicios</span>

            </a>

          </li> -->

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

<button type="button" class="sidebar-overlay d-xl-none sidebartoggler" aria-label="Cerrar menú"></button>



<script>
  (function() {
    var STORAGE_KEY = 'amiri_sidebar_mini';
    var wrapper = document.getElementById('main-wrapper');

    // Restaurar estado inmediatamente (sin flash)
    if (wrapper && localStorage.getItem(STORAGE_KEY) === '1') {
      wrapper.classList.add('sidebar-mini');
      wrapper.setAttribute('data-sidebartype', 'mini-sidebar'); // <--- ¡Línea nueva agregada!
    }

    window.addEventListener('load', function() {
      var btn = document.getElementById('sidebarCollapseBtn');
      if (!btn || !wrapper) return;

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

      if (wrapper.classList.contains('sidebar-mini')) initTooltips();

      btn.addEventListener('click', function() {
        var isMini = wrapper.classList.toggle('sidebar-mini');
        
        // <--- ¡Línea mágica agregada para recorrer el contenedor! --->
        wrapper.setAttribute('data-sidebartype', isMini ? 'mini-sidebar' : 'full');
        
        localStorage.setItem(STORAGE_KEY, isMini ? '1' : '0');
        if (isMini) initTooltips();
        else destroyTooltips();
      });
    });
  })();
</script>
