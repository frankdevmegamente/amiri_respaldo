<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'usuario';

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
$pagina_actual = 'testdienio.php';
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Nexo Amiri - Resumen financiero</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
  <link rel="stylesheet" href="src/assets/libs/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="src/assets/css/testdienio.css" />
  <script>
    (function(){
      try {
        var dark = localStorage.getItem('darkMode') === 'true';
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
      } catch(e) {}
    })();
  </script>
  <style>
    /* small page-specific safeguards */
    .nx-muted { color: var(--nx-text-muted); }
    .nx-preview-note { font-size: 12px; color: var(--nx-text-muted); }
  </style>
</head>
<body class="nexo-body">
  <div class="nexo-app" id="nexoApp">
    <!-- Sidebar -->
    <aside class="nx-sidebar" id="nxSidebar">
      <div class="nx-sidebar-inner">
        <a href="index.php" class="nx-brand" title="Ir al dashboard actual">
          <span class="nx-brand-mark">A</span>
          <span class="nx-brand-text">
            <div class="nx-brand-title">Amiri</div>
            <div class="nx-brand-sub">FINANZAS EMPRESARIALES</div>
          </span>
        </a>

        <div class="nx-workspace">
          <div class="nx-workspace-card" role="button" tabindex="0" aria-label="Espacio de trabajo">
            <div>
              <div class="nx-ws-name">Amiri App</div>
              <div class="nx-ws-sub">Espacio de trabajo</div>
            </div>
            <i class="ti ti-selector nx-ws-caret"></i>
          </div>
        </div>

        <nav class="nx-nav" aria-label="Navegación principal">
          <div class="nx-section">PRINCIPAL</div>

          <a href="#" class="nx-nav-item active" data-view="resumen">
            <i class="ti ti-layout-dashboard"></i><span class="nx-nav-label">Resumen</span>
          </a>

          <?php if (in_array($usuario_rol, ['admin', 'usuario'])): ?>
          <a href="cuentas.php" class="nx-nav-item" data-view="cuentas">
            <i class="ti ti-wallet"></i><span class="nx-nav-label">Cuentas</span>
          </a>
          <a href="movimientos.php" class="nx-nav-item" data-view="movimientos">
            <i class="ti ti-list-details"></i><span class="nx-nav-label">Movimientos</span>
          </a>
          <a href="clientes.php" class="nx-nav-item" data-view="clientes">
            <i class="ti ti-users"></i><span class="nx-nav-label">Clientes</span>
            <span class="nx-badge">24</span>
          </a>
          <a href="flujo.php" class="nx-nav-item" data-view="flujo">
            <i class="ti ti-arrows-exchange"></i><span class="nx-nav-label">Flujo</span>
          </a>

          <div class="nx-nav-group open">
            <button type="button" class="nx-nav-item nx-nav-group-toggle" aria-expanded="true">
              <i class="ti ti-chart-bar"></i><span class="nx-nav-label">Reportes</span>
              <i class="ti ti-chevron-down nx-caret"></i>
            </button>
            <div class="nx-subnav">
              <a href="reportes.php" class="nx-nav-item"><i class="ti ti-point"></i><span class="nx-nav-label">Reportes totales</span></a>
              <a href="reportes-utilidades.php" class="nx-nav-item"><i class="ti ti-point"></i><span class="nx-nav-label">Utilidades</span></a>
              <a href="reportes-servicios.php" class="nx-nav-item"><i class="ti ti-point"></i><span class="nx-nav-label">Servicios</span></a>
            </div>
          </div>

          <div class="nx-section">OPERACIONES</div>
          <a href="capital-amiri.php" class="nx-nav-item"><i class="ti ti-crown"></i><span class="nx-nav-label">Capital Amiri</span></a>
          <a href="gastos.php" class="nx-nav-item"><i class="ti ti-file-dollar"></i><span class="nx-nav-label">Gastos</span></a>
          <a href="promotores.php" class="nx-nav-item"><i class="ti ti-user-plus"></i><span class="nx-nav-label">Promotores</span></a>
          <a href="proveedores.php" class="nx-nav-item"><i class="ti ti-building-store"></i><span class="nx-nav-label">Proveedores</span></a>
          <a href="prestamistas.php" class="nx-nav-item"><i class="ti ti-brand-cashapp"></i><span class="nx-nav-label">Prestamistas</span></a>
          <a href="prestamos-otorgados.php" class="nx-nav-item"><i class="ti ti-cash-banknote"></i><span class="nx-nav-label">Préstamos otorgados</span></a>
          <?php endif; ?>

          <div class="nx-section">ADMINISTRACIÓN</div>
          <a href="editar-perfil.php" class="nx-nav-item"><i class="ti ti-settings"></i><span class="nx-nav-label">Configuración</span></a>
          <?php if ($usuario_rol === 'admin'): ?>
          <a href="usuarios.php" class="nx-nav-item"><i class="ti ti-user"></i><span class="nx-nav-label">Usuarios</span></a>
          <a href="auditoria.php" class="nx-nav-item"><i class="ti ti-file-search"></i><span class="nx-nav-label">Auditoría</span></a>
          <a href="monitor.php" class="nx-nav-item"><i class="ti ti-activity"></i><span class="nx-nav-label">Monitor</span></a>
          <?php endif; ?>
          <a href="#" class="nx-nav-item" data-view="ayuda"><i class="ti ti-help-circle"></i><span class="nx-nav-label">Centro de ayuda</span></a>
          <a href="logout.php" class="nx-nav-item"><i class="ti ti-logout"></i><span class="nx-nav-label">Cerrar sesión</span></a>
        </nav>

        <div class="nx-sidebar-footer">
          <div class="nx-status-card">
            <span class="nx-status-dot"></span>
            <div class="nx-status-text">
              <div class="nx-status-title">Todo está al día</div>
              <div class="nx-status-sub" id="syncTime">Última sincronización · 10:42</div>
            </div>
          </div>
          <div class="nx-plan">AMIRI APP · PLAN EMPRESA</div>
        </div>
      </div>
    </aside>

    <div class="nx-overlay" id="nxOverlay"></div>

    <!-- Topbar -->
    <header class="nx-topbar">
      <div class="nx-topbar-left">
        <button class="nx-icon-btn d-lg-none" id="mobileMenuBtn" type="button" aria-label="Abrir menú"><i class="ti ti-menu-2"></i></button>
        <button class="nx-icon-btn d-none d-lg-inline-flex" id="sidebarToggle" type="button" title="Colapsar menú" aria-label="Colapsar menú"><i class="ti ti-chevron-left"></i></button>
        <div class="nx-breadcrumb">Mi espacio / <strong id="breadcrumbCurrent">Resumen financiero</strong></div>
      </div>
      <div class="nx-topbar-right">
        <div class="nx-search">
          <i class="ti ti-search"></i>
          <input type="text" id="globalSearch" placeholder="Buscar movimientos, clientes..." aria-label="Buscar movimientos y clientes">
        </div>
        <button class="nx-theme-toggle" id="themeToggle" type="button" aria-label="Cambiar tema">
          <i class="ti ti-moon"></i><span>Oscuro</span>
        </button>
        <button class="nx-icon-btn" type="button" title="Notificaciones" aria-label="Notificaciones"><i class="ti ti-bell"></i></button>
        <div class="dropdown">
          <div class="nx-user" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" role="button">
            <span class="nx-avatar"><?php echo htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="nx-user-meta d-none d-xl-block">
              <div class="nx-user-name"><?php echo htmlspecialchars($usuario_nombre, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="nx-user-role"><?php echo htmlspecialchars($rol_label, ENT_QUOTES, 'UTF-8'); ?></div>
            </span>
            <i class="ti ti-chevron-down nx-muted d-none d-md-inline"></i>
          </div>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="editar-perfil.php"><i class="ti ti-user me-2"></i>Mi perfil</a></li>
            <li><a class="dropdown-item" href="index.php"><i class="ti ti-layout-dashboard me-2"></i>Dashboard actual</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="ti ti-logout me-2"></i>Cerrar sesión</a></li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Main -->
    <main class="nx-main">
      <div class="nx-content">
        <div class="nx-page-header">
          <div>
            <h1 class="nx-page-title" id="pageTitle">Resumen financiero</h1>
            <p class="nx-page-sub" id="pageSub">Una vista clara de las finanzas de tu empresa. <span class="nx-preview-note">(Vista previa interactiva con datos de ejemplo, adaptada a Amiri)</span></p>
          </div>
          <div class="nx-page-actions">
            <select class="nx-select" id="rangeSelect" aria-label="Rango de fechas">
              <option selected>1 – 30 junio, 2025</option>
              <option>1 – 31 mayo, 2025</option>
              <option>1 – 30 abril, 2025</option>
              <option>Últimos 6 meses</option>
            </select>
            <button class="nx-btn-primary" id="btnNewMovement" type="button"><i class="ti ti-plus"></i> Nuevo movimiento</button>
          </div>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-3" id="kpiRow"></div>

        <!-- Row 2 -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-xl-8">
            <div class="nx-card h-100">
              <div class="nx-card-header">
                <div>
                  <h2 class="nx-card-title">Flujo de efectivo</h2>
                  <p class="nx-card-sub">Ingresos y egresos de los últimos 6 meses</p>
                </div>
                <select class="nx-select" aria-label="Periodo de gráfica">
                  <option selected>Mensual</option>
                  <option>Semanal</option>
                </select>
              </div>
              <div class="nx-card-body">
                <div id="cashflowChart" class="nx-chart"></div>
                <div class="nx-legend">
                  <span><span class="nx-dot" style="background:#6C5CE7"></span>Ingresos</span>
                  <span><span class="nx-dot" style="background:#94A3B8"></span>Egresos</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-4">
            <div class="nx-card h-100">
              <div class="nx-card-header">
                <div>
                  <h2 class="nx-card-title">Mis cuentas</h2>
                  <p class="nx-card-sub">3 cuentas conectadas</p>
                </div>
                <a href="cuentas.php" class="nx-link">Ver todas →</a>
              </div>
              <div class="nx-card-body pt-2" id="accountsList"></div>
            </div>
          </div>
        </div>

        <!-- Row 3 -->
        <div class="row g-3">
          <div class="col-12 col-xl-8">
            <div class="nx-card h-100">
              <div class="nx-card-header">
                <div>
                  <h2 class="nx-card-title">Movimientos recientes</h2>
                </div>
                <a href="movimientos.php" class="nx-link">Ver todos →</a>
              </div>
              <div class="nx-tabs" role="tablist">
                <div class="nx-tab active" data-tab="todos" role="tab">Todos</div>
                <div class="nx-tab" data-tab="ingresos" role="tab">Ingresos</div>
                <div class="nx-tab" data-tab="egresos" role="tab">Egresos</div>
              </div>
              <div class="nx-table-wrap">
                <table class="nx-table">
                  <thead>
                    <tr>
                      <th>FECHA</th>
                      <th>CONCEPTO</th>
                      <th>CUENTA</th>
                      <th>IMPORTE</th>
                      <th>ESTADO</th>
                    </tr>
                  </thead>
                  <tbody id="movementsBody"></tbody>
                </table>
              </div>
              <div class="nx-table-footer">
                <div id="movementsInfo">Mostrando 0 de 0 movimientos</div>
                <div class="nx-pagination" id="movementsPagination"></div>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-4">
            <div class="nx-card h-100">
              <div class="nx-card-header">
                <div>
                  <h2 class="nx-card-title">Clientes por cobrar</h2>
                  <p class="nx-card-sub">Da seguimiento a tus próximos cobros.</p>
                </div>
                <a href="clientes.php" class="nx-link">Ver todos →</a>
              </div>
              <div class="nx-card-body pt-2" id="receivablesList"></div>
            </div>
          </div>
        </div>
      </div>

      <footer class="nx-footer">
        <div>Todos los importes se muestran en pesos mexicanos (MXN).</div>
        <div id="lastUpdate">Información actualizada · 30 jun 2025, 10:42</div>
      </footer>
    </main>
  </div>

  <!-- Modal ejemplo: Nuevo movimiento -->
  <div class="modal fade" id="modalNuevoMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: var(--nx-surface); color: var(--nx-text); border:1px solid var(--nx-border); border-radius:14px;">
        <div class="modal-header" style="border-bottom:1px solid var(--nx-border);">
          <h5 class="modal-title">Nuevo movimiento (vista previa)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2 nx-muted">Esta es una acción interactiva de ejemplo en la preview. En producción se conectaría al flujo real de movimientos.</p>
          <div class="row g-2">
            <div class="col-12"><label class="form-label mb-1">Concepto</label><input type="text" class="form-control" placeholder="Ej. Pago de factura"></div>
            <div class="col-6"><label class="form-label mb-1">Importe</label><input type="text" class="form-control" placeholder="$0.00"></div>
            <div class="col-6"><label class="form-label mb-1">Cuenta</label><select class="form-select"><option>BBVA</option><option>Santander</option><option>Banorte</option></select></div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--nx-border);">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: var(--nx-primary); border-color: var(--nx-primary);">Guardar (demo)</button>
        </div>
      </div>
    </div>
  </div>

  <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="src/assets/js/testdienio-data.js"></script>
  <script src="src/assets/js/testdienio.js"></script>
</body>
</html>
