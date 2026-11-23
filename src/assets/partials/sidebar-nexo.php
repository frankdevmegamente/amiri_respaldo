<!-- src/assets/partials/sidebar-nexo.php — Sidebar nexo (piloto, Amiri real) -->
<?php
if (!isset($pagina_actual)) {
    $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '') ?: '';
}
$usuario_rol = $usuario_rol ?? ($_SESSION['usuario_rol'] ?? 'usuario');
?>
<aside class="nx-sidebar">
  <div class="nx-sidebar-inner">
    <!-- Brand -->
    <a href="index.php" class="nx-brand" title="Ir al dashboard">
      <span class="nx-brand-mark">A</span>
      <span class="nx-brand-text">
        <div class="nx-brand-title">Amiri</div>
        <div class="nx-brand-sub">FINANZAS EMPRESARIALES</div>
      </span>
    </a>

    <!-- Workspace -->
    <div class="nx-workspace">
      <div class="nx-workspace-card" role="button" tabindex="0" aria-label="Espacio de trabajo">
        <div>
          <div class="nx-ws-name">Amiri App</div>
          <div class="nx-ws-sub">Espacio de trabajo</div>
        </div>
        <i class="ti ti-selector nx-ws-caret"></i>
      </div>
    </div>

    <!-- Nav -->
    <nav class="nx-nav" aria-label="Navegación principal">
      <div class="nx-section">PRINCIPAL</div>
      <a href="index.php" class="nx-nav-item <?php echo ($pagina_actual === 'index.php') ? 'active' : ''; ?>" data-view="resumen">
        <i class="ti ti-layout-dashboard"></i><span class="nx-nav-label">Dashboard</span>
      </a>

      <?php if (in_array($usuario_rol, ['admin', 'usuario'])): ?>
        <div class="nx-section">OPERACIONES</div>
        <a href="flujo.php" class="nx-nav-item <?php echo ($pagina_actual === 'flujo.php') ? 'active' : ''; ?>">
          <i class="ti ti-arrows-exchange"></i><span class="nx-nav-label">Flujo</span>
        </a>
        <a href="cuentas.php" class="nx-nav-item <?php echo (in_array($pagina_actual, ['cuentas.php','cuentas_bancarias.php','cuentas_cajas.php'])) ? 'active' : ''; ?>">
          <i class="ti ti-wallet"></i><span class="nx-nav-label">Cuentas</span>
        </a>
        <a href="movimientos.php" class="nx-nav-item <?php echo ($pagina_actual === 'movimientos.php') ? 'active' : ''; ?>">
          <i class="ti ti-list-details"></i><span class="nx-nav-label">Movimientos</span>
        </a>
        <a href="capital-amiri.php" class="nx-nav-item <?php echo ($pagina_actual === 'capital-amiri.php') ? 'active' : ''; ?>">
          <i class="ti ti-crown"></i><span class="nx-nav-label">Capital Amiri</span>
        </a>
        <a href="gastos.php" class="nx-nav-item <?php echo ($pagina_actual === 'gastos.php') ? 'active' : ''; ?>">
          <i class="ti ti-file-dollar"></i><span class="nx-nav-label">Gastos</span>
        </a>

        <div class="nx-section">CONTRAPARTES</div>
        <a href="clientes.php" class="nx-nav-item <?php echo ($pagina_actual === 'clientes.php') ? 'active' : ''; ?>">
          <i class="ti ti-users"></i><span class="nx-nav-label">Clientes</span>
          <span class="nx-badge">24</span>
        </a>
        <a href="promotores.php" class="nx-nav-item <?php echo ($pagina_actual === 'promotores.php') ? 'active' : ''; ?>">
          <i class="ti ti-user-plus"></i><span class="nx-nav-label">Promotores</span>
        </a>
        <a href="proveedores.php" class="nx-nav-item <?php echo ($pagina_actual === 'proveedores.php') ? 'active' : ''; ?>">
          <i class="ti ti-building-store"></i><span class="nx-nav-label">Proveedores</span>
        </a>
        <a href="prestamistas.php" class="nx-nav-item <?php echo ($pagina_actual === 'prestamistas.php') ? 'active' : ''; ?>">
          <i class="ti ti-brand-cashapp"></i><span class="nx-nav-label">Prestamistas</span>
        </a>
        <a href="prestamos-otorgados.php" class="nx-nav-item <?php echo ($pagina_actual === 'prestamos-otorgados.php') ? 'active' : ''; ?>">
          <i class="ti ti-cash-banknote"></i><span class="nx-nav-label">Préstamos Otorgados</span>
        </a>

        <div class="nx-nav-group <?php echo (in_array($pagina_actual, ['reportes.php','reportes-utilidades.php','reportes-servicios.php','reportes-servicios-v2.php','reportes-utilidades-v2.php'])) ? 'open' : ''; ?>">
          <button type="button" class="nx-nav-item nx-nav-group-toggle" aria-expanded="<?php echo (in_array($pagina_actual, ['reportes.php','reportes-utilidades.php','reportes-servicios.php'])) ? 'true' : 'false'; ?>">
            <i class="ti ti-chart-bar"></i><span class="nx-nav-label">Reportes</span>
            <i class="ti ti-chevron-down nx-caret"></i>
          </button>
          <div class="nx-subnav">
            <a href="reportes.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes.php') ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Reportes Totales</span>
            </a>
            <a href="reportes-utilidades.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes-utilidades.php') ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Utilidades</span>
            </a>
            <a href="reportes-servicios.php" class="nx-nav-item <?php echo ($pagina_actual === 'reportes-servicios.php') ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Servicios</span>
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($usuario_rol === 'admin'): ?>
        <div class="nx-section">HERRAMIENTAS</div>
        <div class="nx-nav-group">
          <button type="button" class="nx-nav-item nx-nav-group-toggle" aria-expanded="false">
            <i class="ti ti-tools"></i><span class="nx-nav-label">Generadores</span>
            <i class="ti ti-chevron-down nx-caret"></i>
          </button>
          <div class="nx-subnav">
            <a href="generador-recibos.php" class="nx-nav-item <?php echo ($pagina_actual === 'generador-recibos.php') ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Cotizador Divisas</span>
            </a>
            <a href="generador-depositos.php" class="nx-nav-item <?php echo ($pagina_actual === 'generador-depositos.php') ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Depósito Efectivo</span>
            </a>
            <a href="generador-transferencias.php" class="nx-nav-item <?php echo (in_array($pagina_actual, ['generador-transferencias.php','generador-transferencias-crud.php'])) ? 'active' : ''; ?>">
              <i class="ti ti-point"></i><span class="nx-nav-label">Depósito Transferencia</span>
            </a>
          </div>
        </div>
        <a href="fifo.php" class="nx-nav-item <?php echo ($pagina_actual === 'fifo.php') ? 'active' : ''; ?>">
          <i class="ti ti-currency-tenge"></i><span class="nx-nav-label">FIFO</span>
        </a>

        <div class="nx-section">ADMINISTRACIÓN</div>
        <a href="usuarios.php" class="nx-nav-item <?php echo ($pagina_actual === 'usuarios.php') ? 'active' : ''; ?>">
          <i class="ti ti-user"></i><span class="nx-nav-label">Usuarios</span>
        </a>
        <a href="auditoria.php" class="nx-nav-item <?php echo ($pagina_actual === 'auditoria.php') ? 'active' : ''; ?>">
          <i class="ti ti-file-search"></i><span class="nx-nav-label">Auditoría</span>
        </a>
        <a href="monitor.php" class="nx-nav-item <?php echo ($pagina_actual === 'monitor.php') ? 'active' : ''; ?>">
          <i class="ti ti-activity"></i><span class="nx-nav-label">Monitor</span>
        </a>
      <?php endif; ?>

      <?php if ($usuario_rol === 'promotor'): ?>
        <div class="nx-section">MI ESPACIO</div>
        <a href="mi-cuenta.php" class="nx-nav-item <?php echo ($pagina_actual === 'mi-cuenta.php') ? 'active' : ''; ?>">
          <i class="ti ti-user-circle"></i><span class="nx-nav-label">Mi Cuenta</span>
        </a>
        <a href="editar-perfil.php" class="nx-nav-item <?php echo ($pagina_actual === 'editar-perfil.php') ? 'active' : ''; ?>">
          <i class="ti ti-settings"></i><span class="nx-nav-label">Editar Perfil</span>
        </a>
      <?php endif; ?>

      <hr style="border-color: var(--nx-border); margin: 12px 0;">
      <a href="logout.php" class="nx-nav-item">
        <i class="ti ti-logout"></i><span class="nx-nav-label">Cerrar sesión</span>
      </a>
    </nav>

    <!-- Footer -->
    <div class="nx-sidebar-footer">
      <div class="nx-status-card">
        <span class="nx-status-dot"></span>
        <div class="nx-status-text">
          <div class="nx-status-title">Todo está al día</div>
          <div class="nx-status-sub" id="syncTime">Última sincronización · --:--</div>
        </div>
      </div>
      <div class="nx-plan">AMIRI APP · PLAN EMPRESA</div>
    </div>
  </div>
</aside>
<div class="nx-overlay" id="nxOverlay"></div>
