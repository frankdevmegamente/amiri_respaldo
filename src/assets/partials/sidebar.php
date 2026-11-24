<?php
// src/assets/partials/sidebar.php — Helios shell (shared)
if (!isset($pagina_actual)) {
    $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';
}
$usuario_rol = $usuario_rol ?? ($_SESSION['usuario_rol'] ?? 'usuario');
?>
<aside class="nx-sidebar">
  <div class="nx-sidebar-inner">
    <a href="index.php" class="nx-brand" title="Ir al dashboard">
      <img src="src/assets/images/logos/logo2-sin-fondo.png" alt="Logo Amiri" class="nx-brand-logo nx-brand-logo-light">
      <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="nx-brand-logo nx-brand-logo-dark">
    </a>

    <nav class="nx-nav" aria-label="Navegación principal">
      <div class="nx-section">PRINCIPAL</div>
      <a href="index.php" class="nx-nav-item <?php echo ($pagina_actual === 'index.php') ? 'active' : ''; ?>" data-title="Dashboard">
        <i class="ti ti-layout-dashboard"></i><span class="nx-nav-label">Dashboard</span>
      </a>

      <?php if (in_array($usuario_rol, ['admin', 'usuario'])): ?>
        <div class="nx-section">OPERACIONES</div>
        <a href="flujo.php" class="nx-nav-item <?php echo ($pagina_actual === 'flujo.php') ? 'active' : ''; ?>" data-title="Flujo">
          <i class="ti ti-arrows-exchange"></i><span class="nx-nav-label">Flujo</span>
        </a>
        <a href="cuentas.php" class="nx-nav-item <?php echo in_array($pagina_actual, ['cuentas.php','cuentas_bancarias.php','cuentas_cajas.php']) ? 'active' : ''; ?>" data-title="Cuentas">
          <i class="ti ti-wallet"></i><span class="nx-nav-label">Cuentas</span>
        </a>
        <a href="movimientos.php" class="nx-nav-item <?php echo ($pagina_actual === 'movimientos.php') ? 'active' : ''; ?>" data-title="Movimientos">
          <i class="ti ti-list-details"></i><span class="nx-nav-label">Movimientos</span>
        </a>
        <a href="capital-amiri.php" class="nx-nav-item <?php echo ($pagina_actual === 'capital-amiri.php') ? 'active' : ''; ?>" data-title="Capital Amiri">
          <i class="ti ti-crown"></i><span class="nx-nav-label">Capital Amiri</span>
        </a>
        <a href="gastos.php" class="nx-nav-item <?php echo ($pagina_actual === 'gastos.php') ? 'active' : ''; ?>" data-title="Gastos">
          <i class="ti ti-file-dollar"></i><span class="nx-nav-label">Gastos</span>
        </a>

        <div class="nx-section">CONTRAPARTES</div>
        <a href="clientes.php" class="nx-nav-item <?php echo ($pagina_actual === 'clientes.php') ? 'active' : ''; ?>" data-title="Clientes">
          <i class="ti ti-users"></i><span class="nx-nav-label">Clientes</span>
        </a>
        <a href="promotores.php" class="nx-nav-item <?php echo ($pagina_actual === 'promotores.php') ? 'active' : ''; ?>" data-title="Promotores">
          <i class="ti ti-user-plus"></i><span class="nx-nav-label">Promotores</span>
        </a>
        <a href="proveedores.php" class="nx-nav-item <?php echo ($pagina_actual === 'proveedores.php') ? 'active' : ''; ?>" data-title="Proveedores">
          <i class="ti ti-building-store"></i><span class="nx-nav-label">Proveedores</span>
        </a>
        <a href="prestamistas.php" class="nx-nav-item <?php echo ($pagina_actual === 'prestamistas.php') ? 'active' : ''; ?>" data-title="Prestamistas">
          <i class="ti ti-brand-cashapp"></i><span class="nx-nav-label">Prestamistas</span>
        </a>
        <a href="prestamos-otorgados.php" class="nx-nav-item <?php echo ($pagina_actual === 'prestamos-otorgados.php') ? 'active' : ''; ?>" data-title="Préstamos Otorgados">
          <i class="ti ti-cash-banknote"></i><span class="nx-nav-label">Préstamos Otorgados</span>
        </a>

        <?php $reportesOpen = in_array($pagina_actual, ['reportes.php','reportes-utilidades.php','reportes-servicios.php']); ?>
        <div class="nx-nav-group <?php echo $reportesOpen ? 'open' : ''; ?>">
          <button class="nx-nav-item nx-nav-group-toggle" type="button" aria-expanded="<?php echo $reportesOpen ? 'true' : 'false'; ?>">
            <i class="ti ti-chart-bar"></i><span class="nx-nav-label">Reportes</span><i class="ti ti-chevron-down nx-caret"></i>
          </button>
          <div class="nx-subnav">
            <a href="reportes.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes.php') ? 'active' : ''; ?>"><span class="nx-nav-label">Reportes Totales</span></a>
            <a href="reportes-utilidades.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes-utilidades.php') ? 'active' : ''; ?>"><span class="nx-nav-label">Utilidades</span></a>
            <a href="reportes-servicios.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes-servicios.php') ? 'active' : ''; ?>"><span class="nx-nav-label">Servicios</span></a>
          </div>
        </div>

        <?php if ($usuario_rol === 'admin'): ?>
          <div class="nx-section">HERRAMIENTAS</div>
          <?php $genOpen = in_array($pagina_actual, ['generador-recibos.php','generador-depositos.php','generador-transferencias.php','generador-transferencias-crud.php']); ?>
          <div class="nx-nav-group <?php echo $genOpen ? 'open' : ''; ?>">
            <button class="nx-nav-item nx-nav-group-toggle" type="button" aria-expanded="<?php echo $genOpen ? 'true' : 'false'; ?>">
              <i class="ti ti-tools"></i><span class="nx-nav-label">Generadores</span><i class="ti ti-chevron-down nx-caret"></i>
            </button>
            <div class="nx-subnav">
              <a href="generador-recibos.php" class="nx-nav-item <?php echo ($pagina_actual === 'generador-recibos.php') ? 'active' : ''; ?>"><span class="nx-nav-label">Cotizador Divisas</span></a>
              <a href="generador-depositos.php" class="nx-nav-item <?php echo ($pagina_actual === 'generador-depositos.php') ? 'active' : ''; ?>"><span class="nx-nav-label">Depósito Efectivo</span></a>
              <a href="generador-transferencias.php" class="nx-nav-item <?php echo in_array($pagina_actual, ['generador-transferencias.php','generador-transferencias-crud.php']) ? 'active' : ''; ?>"><span class="nx-nav-label">Depósito Transferencia</span></a>
            </div>
          </div>
          <a href="fifo.php" class="nx-nav-item <?php echo ($pagina_actual === 'fifo.php') ? 'active' : ''; ?>" data-title="FIFO">
            <i class="ti ti-currency-tenge"></i><span class="nx-nav-label">FIFO</span>
          </a>

          <div class="nx-section">ADMINISTRACIÓN</div>
          <a href="usuarios.php" class="nx-nav-item <?php echo ($pagina_actual === 'usuarios.php') ? 'active' : ''; ?>" data-title="Usuarios">
            <i class="ti ti-user"></i><span class="nx-nav-label">Usuarios</span>
          </a>
          <a href="auditoria.php" class="nx-nav-item <?php echo ($pagina_actual === 'auditoria.php') ? 'active' : ''; ?>" data-title="Auditoría">
            <i class="ti ti-file-search"></i><span class="nx-nav-label">Auditoría</span>
          </a>
          <a href="monitor.php" class="nx-nav-item <?php echo ($pagina_actual === 'monitor.php') ? 'active' : ''; ?>" data-title="Monitor">
            <i class="ti ti-activity"></i><span class="nx-nav-label">Monitor</span>
          </a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($usuario_rol === 'promotor'): ?>
        <div class="nx-section">MI ESPACIO</div>
        <a href="mi-cuenta.php" class="nx-nav-item <?php echo ($pagina_actual === 'mi-cuenta.php') ? 'active' : ''; ?>"><i class="ti ti-user-circle"></i><span class="nx-nav-label">Mi Cuenta</span></a>
        <a href="editar-perfil.php" class="nx-nav-item <?php echo ($pagina_actual === 'editar-perfil.php') ? 'active' : ''; ?>"><i class="ti ti-settings"></i><span class="nx-nav-label">Editar Perfil</span></a>
      <?php endif; ?>
    </nav>
  </div>
</aside>

<div class="nx-overlay" aria-hidden="true"></div>

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
        <h4 class="fw-bold mb-3">Sesión próxima a expirar</h4>
        <p class="text-muted mb-0">Has estado 3 horas sin actividad en el sistema.</p>
        <p class="text-muted">¿Deseas continuar utilizando la plataforma?</p>
      </div>
      <div class="modal-footer border-0 justify-content-center pt-0">
        <button id="btnExtendSession" class="btn btn-primary btn-lg px-5"><i class="ti ti-check me-2"></i>Sí, continuar</button>
        <button id="btnLogoutSession" class="btn btn-outline-danger btn-lg px-4"><i class="ti ti-logout me-2"></i>Cerrar sesión</button>
      </div>
    </div>
  </div>
</div>

<script src="src/assets/js/nexo-shell.js?v=helios34"></script>
<script src="src/assets/js/session-timeout.js"></script>
<script>
// Compat mínima: nexo-shell.js maneja el toggle. Esto solo asegura clase y estado legacy.
(function(){
  var w = document.getElementById('main-wrapper');
  if (!w) return;
  w.classList.add('nexo-app');
  try {
    if (localStorage.getItem('amiri_sidebar_mini') === '1' || localStorage.getItem('nexoSidebarCollapsed') === '1') {
      w.classList.add('sidebar-collapsed','sidebar-mini');
    }
  } catch(e){}
})();
</script>
