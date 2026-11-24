<?php
require_once __DIR__ . '/src/config/session.php';
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
$cuentas = $pdo->query("SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN' AND tipo_cuenta IN (1, 2) ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Préstamos Otorgados | Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .loans-header { position: relative; overflow: hidden; border-radius: 16px !important; }
        .loans-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(197, 143, 184, 0.08); pointer-events: none; }
        .loans-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
        .loans-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .loans-header .sub-badge { font-size: 1rem; color: #9a9096; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }

        /* ─── Table extras ────────────────────────────── */
        td.details-control { cursor: pointer; text-align: center; width: 30px; }
        td.details-control i { transition: transform 0.2s; font-size: 1.1rem; }
        tr.shown td.details-control i { transform: rotate(90deg); }
        .child-table { margin: 8px 0 8px 40px; width: calc(100% - 40px); }
        .child-table td, .child-table th { padding: 6px 10px !important; font-size: 0.85rem; }

        /* ─── Dark mode ───────────────────────────────── */
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .card { background: var(--nx-surface) !important; border-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: var(--nx-surface) !important; color: #f5f2f4 !important; border-bottom-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #f5f2f4 !important; border-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05); }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #1a181c !important; border-color: rgba(255,255,255,0.12) !important; color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select, [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #1a181c; border: 1px solid rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .modal-content { background: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-footer { border-top-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-title { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-label { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] .input-group-text { background-color: #1a181c; border-color: rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #9a9096; }
        [data-bs-theme="dark"] .bg-light { background-color: var(--nx-surface-2) !important; }
        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(197, 143, 184, 0.12) !important; }
        [data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(25, 135, 84, 0.12) !important; }
        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.12) !important; }
        [data-bs-theme="dark"] .bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.12) !important; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- ════════════════════════════════════
                         PAGE HEADER
                         ════════════════════════════════════ -->
                    <div class="card loans-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(19,222,185,0.12);">
                                            <i class="ti ti-coin text-success" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Préstamos Otorgados</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Administración de préstamos</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalDeudor">
                                            <i class="ti ti-users"></i> Catálogo Deudores
                                        </button>
                                        <button class="btn btn-warning btn-sm" id="btnAnularGenericoPrestamos" title="Anular cualquier grupo (venta, gasto, préstamo, divisa)">
                                            <i class="ti ti-eraser"></i> Anular por Grupo
                                        </button>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPrestamo">
                                            <i class="ti ti-plus"></i> Nuevo Préstamo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         KPI METRIC CARDS
                         ════════════════════════════════════ -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total Prestado</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="ti ti-coin"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-primary" id="kpiTotalPrestado">$0.00</div>
                                    <div class="metric-footer">Histórico</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Cobrado</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-cash"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiCobrado">$0.00</div>
                                    <div class="metric-footer">Recuperado</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Pendiente</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-hourglass"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiPendiente">$0.00</div>
                                    <div class="metric-footer">Por cobrar</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Deudores</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-users"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiDeudores">0</div>
                                    <div class="metric-footer">Activos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         TABLE
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaOtorgados" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;"></th>
                                        <th>Deudor</th>
                                        <th>Préstamos</th>
                                        <th>Total Prestado</th>
                                        <th>Cobrado</th>
                                        <th>Pendiente</th>
                                        <th>Estatus</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         CATÁLOGO DEUDORES (gestión)
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold"><i class="ti ti-users text-primary me-2"></i>Catálogo de Deudores <small class="text-muted ms-2" id="countDeudores"></small></h6>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDeudor" id="btnNuevoDeudor"><i class="ti ti-user-plus"></i> Nuevo</button>
                        </div>
                        <div class="card-body p-3 table-responsive">
                            <table id="tablaDeudores" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Préstamos</th>
                                        <th>Total</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             MODAL: Cobro / Abono
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalCobro" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form id="formCobro" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-cash text-success"></i> Registrar Abono / Cobro</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2" id="resumenDeudor" style="display: none;">
                            <small>Cobrando a: <strong id="nombreDeudorCobro"></strong></small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Selecciona qué deuda(s) va a pagar o abonar</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="btnSelTodosCobro">Todos</button>
                                <button type="button" class="btn btn-outline-secondary" id="btnSelNingunoCobro">Ninguno</button>
                            </div>
                        </div>
                        <div id="contenedorPagosMultiples" class="mb-3 p-2 bg-light border rounded" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                        <div id="msgSinPendientes" class="alert alert-warning py-2 text-center" style="display: none;">
                            <small>Este deudor no tiene préstamos pendientes.</small>
                        </div>

                        <div class="alert alert-warning py-2 text-center" id="alertaDeudaTotal" style="display: none;">
                            <small>Deuda Total: <strong id="deudaTotalSugerida">$0.00</strong> &nbsp;|&nbsp; Seleccionado: <strong id="totalSeleccionadoCobro">$0.00</strong></small>
                        </div>

                        <div class="mb-3" id="grupoMontoGlobal">
                            <label class="form-label fw-bold text-success">¿Cuánto dinero estás recibiendo?</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" step="0.01" id="monto_a_pagar_input" class="form-control form-control-lg text-success fw-bold" readonly>
                            </div>
                            <small class="text-muted">Se calcula automáticamente con la suma de los préstamos seleccionados. Puedes ajustar el monto de cada préstamo para abonos parciales.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="ti ti-wallet"></i> Caja de Destino</label>
                            <select id="cuenta_cobro_id" class="form-select" required>
                                <option value="">Selecciona una cuenta...</option>
                                <?php foreach($cuentas as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> ($<?= number_format((float)$c['saldo_actual'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="ti ti-calendar"></i> Fecha del Cobro</label>
                            <input type="date" id="fecha_cobro" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="ti ti-device-floppy"></i> Confirmar Entrada de Efectivo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             MODAL: Nuevo Préstamo (MVP compuesto + catálogo)
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalNuevoPrestamo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form id="formNuevoPrestamo" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-circle-plus text-primary"></i> Registrar Nuevo Préstamo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="ti ti-user"></i> Deudor (catálogo)</label>
                                <select id="selectDeudor" name="deudor_id" class="form-select">
                                    <option value="">-- Selecciona o crea --</option>
                                </select>
                                <input type="text" id="inputNuevoDeudor" name="persona_nombre" class="form-control mt-2" placeholder="O escribe nuevo nombre y se creará automáticamente" />
                                <small class="text-muted">Puedes elegir del catálogo o escribir uno nuevo.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="ti ti-wallet"></i> Caja de Origen</label>
                                <select name="cuenta_id" class="form-select" required>
                                    <option value="">Seleccionar cuenta...</option>
                                    <?php foreach($cuentas as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> ($<?= number_format((float)$c['saldo_actual'], 2) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="ti ti-coin"></i> Monto a Prestar (capital)</label>
                                <input type="text" step="0.01" name="monto" id="inputMontoPrestamo" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="ti ti-percentage"></i> Tasa %</label>
                                <div class="input-group">
                                    <input type="text" step="0.01" name="tasa_porcentaje" id="inputTasa" class="form-control" placeholder="0" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">0% = sin interés (legado)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Por periodo</label>
                                <select name="tasa_periodo" id="selectTasaPeriodo" class="form-select">
                                    <option value="mensual" selected>Mensual</option>
                                    <option value="quincenal">Quincenal</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="diario">Diario</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><i class="ti ti-calendar"></i> Plazo</label>
                                <input type="number" name="plazo_valor" id="inputPlazoValor" class="form-control" placeholder="Ej. 2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unidad plazo</label>
                                <select name="plazo_unidad" id="selectPlazoUnidad" class="form-select">
                                    <option value="mes" selected>Mes(es)</option>
                                    <option value="quincena">Quincena(s)</option>
                                    <option value="semana">Semana(s)</option>
                                    <option value="dia">Día(s)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha vencimiento (auto)</label>
                                <input type="date" class="form-control" id="prevVencimiento" readonly>
                                <small class="text-muted" id="prevInteres">Interés: $0.00 | Total: $0.00</small>
                            </div>
                        </div>
                        <div class="alert alert-info py-2" id="previewPrestamo" style="display:none;">
                            <small><strong>Preview compuesto:</strong> Capital <span id="prevCapital">$0</span> + Interés <span id="prevInteresVal">$0</span> = <strong id="prevTotal">$0</strong> <span id="prevDetalle" class="text-muted"></span></small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label"><i class="ti ti-tag"></i> Concepto <small class="text-muted">(opcional)</small></label>
                            <input type="text" name="concepto" id="inputConceptoPrestamo" class="form-control" maxlength="255" placeholder="Ej: Para negocio, emergencia, etc.">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Confirmar préstamo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal crear/editar deudor (catálogo completo) -->
        <div class="modal fade" id="modalDeudor" tabindex="-1">
            <div class="modal-dialog">
                <form id="formDeudor" class="modal-content">
                    <div class="modal-header"><h6 class="modal-title" id="tituloDeudor"><i class="ti ti-user-plus"></i> Nuevo deudor</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="deudorId">
                        <div class="mb-2"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="deudorNombre" class="form-control" placeholder="Nombre completo" required></div>
                        <div class="mb-2"><label class="form-label">Teléfono</label><input type="text" name="telefono" id="deudorTelefono" class="form-control" placeholder="Teléfono"></div>
                        <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" id="deudorEmail" class="form-control" placeholder="Email"></div>
                        <div class="mb-2"><label class="form-label">Dirección</label><textarea name="direccion" id="deudorDireccion" class="form-control" rows="2" placeholder="Dirección"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary btn-sm">Guardar</button></div>
                </form>
            </div>
        </div>

        <?php include 'src/assets/partials/modal-anular.php'; ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>
        <script src="src/assets/js/anular.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            var fmt = function(n) {
                return parseFloat(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
            };

            // ─── Helpers compuesto JS (igual que PHP) ──────
            function calcInteresJS(capital, tasaPct, tasaPeriodo, plazoValor, plazoUnidad) {
                capital = parseFloat(capital)||0; tasaPct=parseFloat(tasaPct)||0; plazoValor=parseInt(plazoValor)||0;
                if (tasaPct<=0||!plazoValor) return {interes:0, total:capital, venc:null};
                const diasMap={dia:1,semana:7,quincena:15,mes:30,diario:1,semanal:7,quincenal:15,mensual:30,anual:365};
                const diasTasa=diasMap[tasaPeriodo]||30, diasPlazo=diasMap[plazoUnidad]||30;
                const n=(plazoValor*diasPlazo)/diasTasa;
                const total=capital*Math.pow(1+tasaPct/100,n);
                return {interes: Math.round((total-capital)*100)/100, total: Math.round(total*100)/100};
            }
            function calcVencimientoJS(plazoValor, plazoUnidad){
                if (!plazoValor) return '';
                const d=new Date();
                const v=parseInt(plazoValor);
                if (plazoUnidad==='quincena') d.setDate(d.getDate()+v*15);
                else if (plazoUnidad==='semana') d.setDate(d.getDate()+v*7);
                else if (plazoUnidad==='dia') d.setDate(d.getDate()+v);
                else d.setMonth(d.getMonth()+v);
                return d.toISOString().slice(0,10);
            }
            function actualizarPreview(){
                const cap=$('#inputMontoPrestamo').val(), tasa=$('#inputTasa').val(), per=$('#selectTasaPeriodo').val(), pv=$('#inputPlazoValor').val(), pu=$('#selectPlazoUnidad').val();
                const r=calcInteresJS(cap,tasa,per,pv,pu);
                const venc=calcVencimientoJS(pv,pu);
                $('#prevVencimiento').val(venc);
                $('#prevCapital').text('$'+fmt(cap||0));
                $('#prevInteresVal').text('$'+fmt(r.interes));
                $('#prevTotal').text('$'+fmt(r.total));
                $('#prevDetalle').text(tasa>0 && pv ? '('+tasa+'% '+per+' x '+pv+' '+pu+')' : '');
                $('#previewPrestamo').toggle(!!(tasa>0 && pv && cap));
                $('#prevInteres').text('Interés: $'+fmt(r.interes)+' | Total: $'+fmt(r.total));
            }
            $(document).on('input change', '#inputMontoPrestamo, #inputTasa, #selectTasaPeriodo, #inputPlazoValor, #selectPlazoUnidad', actualizarPreview);

            // Genérico: anular por grupo
            $(document).on('click', '#btnAnularGenericoPrestamos', function(){
                const g = prompt('Grupo ID a anular (Flujo → Ver detalle → Grupo #):');
                if (g && g.trim()) abrirAnularGenerico(g.trim());
            });
            // Anular préstamo específico desde child table (resuelve grupo_id por referencia_id)
            $(document).on('click', '.btn-anular-prestamo', function(){
                const pid = $(this).data('id');
                const nombre = $(this).data('nombre')||'';
                if(!confirm('¿Anular préstamo #'+pid+' de '+nombre+'? Se revertirá el capital y se marcará como anulado.')) return;
                // Resolver grupo_id vía API movimientos-correccion detalle? fallback: pedir grupo_id
                // Intentamos obtener grupo_id del préstamo vía GET
                $.getJSON('api/movimientos-correccion.php?action=detalle&id='+pid, function(){ /* no usado */ }).always(function(){
                    // Pedimos grupo_id al usuario si no lo tenemos, o buscamos vía endpoint custom
                    $.getJSON('api/prestamos-otorgados.php?persona_nombre='+encodeURIComponent(nombre), function(loans){
                        const loan = loans.find(l=> l.id==pid);
                        if (loan && loan.grupo_id) { abrirAnularGenerico(loan.grupo_id); }
                        else {
                            // Fallback: preguntar grupo_id (se puede ver en flujo.php)
                            const g = prompt('Grupo ID del préstamo #'+pid+' (ver en Flujo → grupo_id): Si no lo sabes, deja vacío para intentar por servicio)');
                            if (g) abrirAnularGenerico(g.trim());
                            else showToast('No se pudo resolver grupo_id. Usa Flujo → Ver detalle para copiar el Grupo #','error');
                        }
                    });
                });
            });

            // ─── Catálogo deudores ─────────────────────────
            let tablaDeudores = null;
            function cargarDeudores(){
                $.getJSON('api/deudores.php?con_stats=1', function(list){
                    const sel=$('#selectDeudor'); sel.empty().append('<option value="">-- Selecciona --</option>');
                    list.forEach(d=> sel.append('<option value="'+d.id+'">'+d.nombre+'</option>'));
                    // también para compat listaPersonas vieja
                    $('#listaPersonas').empty();
                    list.forEach(c=> $('#listaPersonas').append('<option value="'+c.nombre+'">'));
                    // actualizar contador header
                    $('#countDeudores').text('('+list.length+' deudores)');
                    // Render tabla catálogo
                    if (!tablaDeudores) {
                        tablaDeudores = $('#tablaDeudores').DataTable({
                            data: list,
                            columns: [
                                { data: 'nombre', render: n=> '<strong>'+n+'</strong>' },
                                { data: 'telefono', defaultContent:'—' },
                                { data: 'email', defaultContent:'—' },
                                { data: 'prestamos_count', className:'text-center', render: n=> '<span class="badge bg-light text-dark border-0">'+(n||0)+'</span>' },
                                { data: 'total_con_interes', render: (n,r)=> r.total_con_interes ? '$'+fmt(r.total_con_interes)+' <small class="text-muted">('+fmt(r.total_pagado||0)+' pagado)</small>' : '—' },
                                { data: null, className:'text-center', orderable:false, render: r=> '<div class="btn-group btn-group-sm"><button class="btn btn-outline-primary btn-editar-deudor" data-id="'+r.id+'" data-nombre="'+r.nombre.replace(/"/g,'&quot;')+'" data-telefono="'+(r.telefono||'')+'" data-email="'+(r.email||'')+'" data-direccion="'+(r.direccion||'').replace(/"/g,'&quot;')+'" title="Editar"><i class="ti ti-pencil"></i></button><button class="btn btn-outline-danger btn-borrar-deudor" data-id="'+r.id+'" data-nombre="'+r.nombre.replace(/"/g,'&quot;')+'" title="Borrar"><i class="ti ti-trash"></i></button></div>' }
                            ],
                            order: [[0,'asc']],
                            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json', emptyTable:'Sin deudores — crea uno' }
                        });
                    } else {
                        tablaDeudores.clear().rows.add(list).draw();
                    }
                });
            }
            // Editar deudor
            $(document).on('click','.btn-editar-deudor', function(){
                const b=$(this);
                $('#deudorId').val(b.data('id'));
                $('#deudorNombre').val(b.data('nombre'));
                $('#deudorTelefono').val(b.data('telefono'));
                $('#deudorEmail').val(b.data('email'));
                $('#deudorDireccion').val(b.data('direccion'));
                $('#tituloDeudor').html('<i class="ti ti-pencil"></i> Editar deudor');
                $('#modalDeudor').modal('show');
            });
            // Nuevo deudor limpia
            $('#btnNuevoDeudor').on('click', function(){
                $('#formDeudor')[0].reset(); $('#deudorId').val(''); $('#tituloDeudor').html('<i class="ti ti-user-plus"></i> Nuevo deudor');
            });
            // Borrar deudor
            $(document).on('click','.btn-borrar-deudor', function(){
                const id=$(this).data('id'), nombre=$(this).data('nombre');
                if(!confirm('¿Borrar deudor "'+nombre+'"? Solo si no tiene préstamos pendientes.')) return;
                $.ajax({ url:'api/deudores.php?id='+id, method:'DELETE', success:()=>{ showToast('Deudor borrado','success'); cargarDeudores(); table.ajax.reload(); cargarResumen(); }, error: xhr=> showToast(xhr.responseJSON?.error||'Error','error') });
            });

            // ─── Resumen (KPI Cards) ─────────────────────
            function cargarResumen() {
                $.get('api/prestamos-otorgados.php?resumen=1', function(data) {
                    $('#kpiTotalPrestado').html('$' + fmt(data.total_prestado) + '<br><small class="text-muted" style="font-size:0.7rem;">+ $'+fmt(data.total_interes)+' interés → $'+fmt(data.total_con_interes)+' total</small>');
                    $('#kpiCobrado').text('$' + fmt(data.total_cobrado));
                    $('#kpiPendiente').text('$' + fmt(data.total_pendiente));
                    $('#kpiDeudores').text(data.deudores);
                }).fail(function() {
                    $('#kpiTotalPrestado').text('Error');
                });
            }

            // ─── Avatar helper ───────────────────────────
            const avatarColors = ['var(--nx-primary)', 'var(--nx-success)', 'var(--nx-danger)', 'var(--nx-warning)', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
            function getAvatarColor(name) {
                if (!name) return '#9a9096';
                let hash = 0;
                for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
                return avatarColors[Math.abs(hash) % avatarColors.length];
            }

            // ─── DataTable ───────────────────────────────
            function buildChildHtml(loans) {
                if (!loans || !loans.length) return '<div class="text-muted text-center py-2">Sin préstamos</div>';
                var h = '<table class="table table-sm table-borderless child-table mb-0"><thead><tr class="text-muted" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;"><th>Fecha</th><th>Concepto</th><th>Capital</th><th>Interés</th><th>Total</th><th>Cobrado</th><th>Pendiente</th><th>Vence</th><th>Estatus</th><th>Acción</th></tr></thead><tbody>';
                loans.forEach(function(l) {
                    const total = parseFloat(l.monto_total_con_interes || l.monto_total);
                    var pend = total - parseFloat(l.monto_pagado);
                    var cls = l.estado === 'pagado' ? 'success' : l.estado === 'parcial' ? 'warning' : 'danger';
                    const tasaInfo = l.tasa_porcentaje>0 ? l.tasa_porcentaje+'% '+l.tasa_periodo : '0%';
                    const vence = l.fecha_vencimiento ? l.fecha_vencimiento.split(' ')[0].split('-').reverse().join('/') : '<span class="text-muted">—</span>';
                    const vencCls = l.fecha_vencimiento && new Date(l.fecha_vencimiento) < new Date() && l.estado!=='pagado' ? ' text-danger fw-bold' : '';
                    const conceptoRaw = (l.concepto || '').toString();
                    const conceptoEsc = conceptoRaw.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                    const conceptoCell = conceptoRaw ? '<span class="d-inline-block text-truncate" style="max-width:180px;" title="'+conceptoEsc+'">'+conceptoEsc+'</span>' : '<span class="text-muted">Sin concepto</span>';
                    const anularBtn = l.estado!=='pagado' ? '<button class="btn btn-sm btn-outline-danger btn-anular-prestamo" data-id="'+l.id+'" data-nombre="'+(l.deudor_nombre||l.persona_nombre||'').replace(/"/g,'&quot;')+'" title="Anular préstamo"><i class="ti ti-trash"></i></button>' : '';
                    const cobrarBtn = (l.estado!=='pagado' && pend > 0.009) ? '<button class="btn btn-sm btn-success btn-cobrar-prestamo me-1" data-id="'+l.id+'" data-nombre="'+(l.deudor_nombre||l.persona_nombre||'').replace(/"/g,'&quot;')+'" data-pendiente="'+pend.toFixed(2)+'" title="Abonar a este préstamo"><i class="ti ti-cash"></i></button>' : '';
                    h += '<tr><td>' + (l.fecha_prestamo ? l.fecha_prestamo.split(' ')[0].split('-').reverse().join('/') : '') + '</td><td>'+conceptoCell+'</td><td class="fw-bold">$' + fmt(l.monto_total) + '</td><td class="text-primary">$' + fmt(l.monto_interes||0) + '<br><small class="text-muted">'+tasaInfo+'</small></td><td class="fw-bold">$' + fmt(total) + '</td><td class="text-success">$' + fmt(l.monto_pagado) + '</td><td class="text-danger fw-bold">$' + fmt(pend) + '</td><td class="'+vencCls+'">'+vence+'</td><td><span class="badge bg-' + cls + ' bg-opacity-10 text-' + cls + ' border-0" style="font-size:0.7rem;">' + l.estado + '</span></td><td><div class="d-flex">'+cobrarBtn+anularBtn+'</div></td></tr>';
                });
                h += '</tbody></table>';
                return h;
            }

            var table = $('#tablaOtorgados').DataTable({
                ajax: { url: 'api/prestamos-otorgados.php?agrupado', dataSrc: '' },
                columns: [
                    {
                        data: null, orderable: false, className: 'details-control',
                        defaultContent: '<i class="ti ti-chevron-right text-muted"></i>'
                    },
                    {
                        data: 'persona_nombre',
                        render: function(n, t, r) {
                            const name = r.persona_nombre || n;
                            var color = getAvatarColor(name);
                            var initial = name ? name.charAt(0).toUpperCase() : '?';
                            const interes = r.total_interes ? '<br><small class="text-primary">+ $'+fmt(r.total_interes)+' interés</small>' : '';
                            return '<div class="d-flex align-items-center gap-2">' +
                                   '<span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:' + color + '22;color:' + color + ';font-weight:700;font-size:0.85rem;">' + initial + '</span>' +
                                   '<div><a href="detalle-deudor.php?nombre=' + encodeURIComponent(name) + '" class="fw-semibold text-decoration-none">' + name + '</a>'+interes+'</div>' +
                                   '</div>';
                        }
                    },
                    { data: 'total_prestamos', render: function(n) { return '<span class="badge bg-light text-dark border-0">' + n + '</span>'; } },
                    { data: 'total_prestado', render: function(n, t, r) { return ' <strong>$' + fmt(n) + '</strong><br><small class="text-muted">Total $'+fmt(r.total_con_interes||n)+'</small>'; } },
                    { data: 'total_cobrado', render: function(n) { return '<span style="color:var(--nx-success);font-weight:700;">$' + fmt(n) + '</span>'; } },
                    { data: 'total_pendiente', render: function(n, t, r) { return '<span style="color:var(--nx-warning);font-weight:700;">$' + fmt(n) + '</span>'; } },
                    {
                        data: null,
                        render: function(r) {
                            var pend = parseFloat(r.total_pendiente);
                            if (pend <= 0) return '<span class="badge bg-success bg-opacity-10 text-success border-0">Pagado</span>';
                            if (parseFloat(r.total_cobrado) > 0) return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Parcial</span>';
                            return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Pendiente</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(r) {
                            var pend = parseFloat(r.total_pendiente);
                            var btn = pend > 0 ? '<button class="btn btn-sm btn-success btn-cobrar-deudor" data-nombre="' + r.persona_nombre.replace(/"/g, '&quot;') + '" data-pendiente="' + pend + '"><i class="ti ti-cash"></i> Cobrar</button> ' : '';
                            return '<div class="d-flex align-items-center gap-1">' + btn + '<a href="detalle-deudor.php?nombre=' + encodeURIComponent(r.persona_nombre) + '" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="ti ti-eye"></i></a></div>';
                        }
                    }
                ],
                scrollX: true,
                order: [[5, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ─── Expand child rows ───────────────────────
            $('#tablaOtorgados tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var data = row.data();
                if (!data) return;

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    table.rows().every(function() { if (this.child.isShown()) { this.child.hide(); $(this.node()).removeClass('shown'); } });
                    $(this).html('<i class="ti ti-loader ti-spin text-primary"></i>');
                    $.get('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(data.persona_nombre), function(loans) {
                        row.child(buildChildHtml(loans)).show();
                        tr.addClass('shown');
                        tr.find('td.details-control').html('<i class="ti ti-chevron-right text-primary"></i>');
                    }).fail(function() {
                        tr.find('td.details-control').html('<i class="ti ti-chevron-right text-muted"></i>');
                    });
                }
            });

            // ─── Cobro por préstamo seleccionable ──────────
            function pendientePrestamo(l) {
                const total = parseFloat(l.monto_total_con_interes || l.monto_total);
                return Math.round((total - parseFloat(l.monto_pagado || 0)) * 100) / 100;
            }
            function fechaCorta(f) {
                if (!f) return '—';
                return String(f).split(' ')[0].split('-').reverse().join('/');
            }
            function renderListaCobro(loans, soloId) {
                const box = $('#contenedorPagosMultiples');
                box.empty();
                const pendientes = (loans || []).filter(l => l.estado !== 'pagado' && pendientePrestamo(l) > 0.009);
                if (!pendientes.length) {
                    box.hide();
                    $('#msgSinPendientes').show();
                    $('#alertaDeudaTotal').hide();
                    $('#monto_a_pagar_input').val('0.00');
                    return;
                }
                $('#msgSinPendientes').hide();
                var h = '<table class="table table-sm align-middle mb-0"><thead><tr class="text-muted" style="font-size:0.7rem;text-transform:uppercase;"><th style="width:34px;"></th><th>Préstamo</th><th class="text-end">Pendiente</th><th style="width:130px;" class="text-end">Abono</th></tr></thead><tbody>';
                pendientes.forEach(function(l) {
                    const pend = pendientePrestamo(l);
                    const total = parseFloat(l.monto_total_con_interes || l.monto_total);
                    const checked = (!soloId || String(l.id) === String(soloId)) ? 'checked' : '';
                    h += '<tr>' +
                        '<td class="text-center"><input type="checkbox" class="form-check-input check-cobro" data-id="' + l.id + '" data-pendiente="' + pend.toFixed(2) + '" ' + checked + '></td>' +
                        '<td><strong>#' + l.id + '</strong> <small class="text-muted">' + fechaCorta(l.fecha_prestamo) + '</small>' + (l.concepto ? '<br><small class="text-primary">' + String(l.concepto).replace(/</g,'&lt;') + '</small>' : '') + '<br>' +
                        '<small>Total $' + fmt(total) + ' <span class="text-success">(pagado $' + fmt(l.monto_pagado || 0) + ')</span></small></td>' +
                        '<td class="text-end text-danger fw-bold">$' + fmt(pend) + '</td>' +
                        '<td><div class="input-group input-group-sm"><span class="input-group-text">$</span>' +
                        '<input type="number" step="0.01" min="0" max="' + pend.toFixed(2) + '" value="' + pend.toFixed(2) + '" class="form-control text-end input-abono-cobro" data-id="' + l.id + '" data-pendiente="' + pend.toFixed(2) + '"></div>' +
                        '<button type="button" class="btn btn-link btn-sm p-0 btn-total-prestamo" data-id="' + l.id + '">total</button></td>' +
                        '</tr>';
                });
                h += '</tbody></table>';
                box.html(h).show();
                actualizarTotalCobro();
            }
            function actualizarTotalCobro() {
                let deuda = 0, sel = 0;
                $('.check-cobro').each(function() {
                    const pend = parseFloat($(this).data('pendiente')) || 0;
                    deuda += pend;
                    const id = $(this).data('id');
                    const inp = $('.input-abono-cobro[data-id="' + id + '"]');
                    let v = parseFloat(inp.val()) || 0;
                    if ($(this).is(':checked') && v > 0.009) sel += v;
                });
                deuda = Math.round(deuda * 100) / 100;
                sel = Math.round(sel * 100) / 100;
                $('#deudaTotalSugerida').text('$' + fmt(deuda));
                $('#totalSeleccionadoCobro').text('$' + fmt(sel));
                $('#monto_a_pagar_input').val(sel.toFixed(2));
                $('#alertaDeudaTotal').toggle(deuda > 0);
            }
            $(document).on('change', '.check-cobro', actualizarTotalCobro);
            $(document).on('input', '.input-abono-cobro', function() {
                const pend = parseFloat($(this).data('pendiente')) || 0;
                let v = parseFloat($(this).val()) || 0;
                if (v > pend) { v = pend; $(this).val(pend.toFixed(2)); }
                if (v < 0) { v = 0; $(this).val('0.00'); }
                const id = $(this).data('id');
                $('.check-cobro[data-id="' + id + '"]').prop('checked', v > 0.009);
                actualizarTotalCobro();
            });
            $(document).on('click', '.btn-total-prestamo', function() {
                const id = $(this).data('id');
                const inp = $('.input-abono-cobro[data-id="' + id + '"]');
                inp.val(parseFloat(inp.data('pendiente')).toFixed(2));
                $('.check-cobro[data-id="' + id + '"]').prop('checked', true);
                actualizarTotalCobro();
            });
            $('#btnSelTodosCobro').on('click', function() {
                $('.check-cobro').prop('checked', true);
                $('.input-abono-cobro').each(function() { $(this).val(parseFloat($(this).data('pendiente')).toFixed(2)); });
                actualizarTotalCobro();
            });
            $('#btnSelNingunoCobro').on('click', function() {
                $('.check-cobro').prop('checked', false);
                actualizarTotalCobro();
            });
            function abrirModalCobro(nombre, soloId) {
                $('#contenedorPagosMultiples').hide().empty();
                $('#msgSinPendientes').hide();
                $('#resumenDeudor').show().find('#nombreDeudorCobro').text(nombre);
                $('#alertaDeudaTotal').hide();
                $('#monto_a_pagar_input').val('0.00');
                $('#cuenta_cobro_id').val('');
                $('#fecha_cobro').val(new Date().toISOString().slice(0, 10));
                $('#formCobro').data('modo', 'individual').data('nombre', nombre);
                $('#contenedorPagosMultiples').html('<div class="text-center text-muted py-3"><i class="ti ti-loader ti-spin"></i> Cargando préstamos...</div>').show();
                $('#modalCobro').modal('show');
                $.getJSON('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(nombre), function(loans) {
                    renderListaCobro(loans, soloId);
                }).fail(function() {
                    $('#contenedorPagosMultiples').html('<div class="alert alert-danger py-2 mb-0">No se pudieron cargar los préstamos.</div>');
                });
            }
            $(document).on('click', '.btn-cobrar-deudor', function() {
                abrirModalCobro($(this).data('nombre'), null);
            });
            // Abono directo desde la fila del préstamo (tabla expandible)
            $(document).on('click', '.btn-cobrar-prestamo', function() {
                abrirModalCobro($(this).data('nombre'), $(this).data('id'));
            });

            // ─── Guardar nuevo préstamo ──────────────────
            $('#formNuevoPrestamo').submit(function(e) {
                e.preventDefault();
                // Resolver deudor: si hay select, usar ese; si input nuevo tiene texto, usarlo (creará)
                const selVal = $('#selectDeudor').val();
                const txtVal = $('#inputNuevoDeudor').val().trim();
                let deudorId = selVal || '';
                let personaNombre = txtVal;
                if (deudorId && !personaNombre) {
                    // tomamos nombre del select para compat
                    personaNombre = $('#selectDeudor option:selected').text().trim();
                }
                if (!deudorId && !personaNombre) return alert('Selecciona o escribe un deudor');
                var formData = $(this).serialize() + '&fecha_prestamo=' + new Date().toISOString().slice(0, 19).replace('T', ' ');
                // Asegurar que persona_nombre esté presente aunque venga de select
                if (personaNombre && formData.indexOf('persona_nombre=')===-1) formData += '&persona_nombre=' + encodeURIComponent(personaNombre);
                $.post('api/prestamos-otorgados.php', formData, function() {
                    $('#modalNuevoPrestamo').modal('hide');
                    $('#formNuevoPrestamo')[0].reset();
                    $('#selectDeudor').val('');
                    $('#previewPrestamo').hide();
                    table.ajax.reload();
                    cargarResumen();
                    cargarDeudores();
                }).fail(function(xhr) { alert(xhr.responseJSON?.error || "Error"); });
            });

            // Crear/editar deudor
            $('#formDeudor').submit(function(e){
                e.preventDefault();
                const fd = $(this).serialize();
                $.post('api/deudores.php', fd, function(res){
                    $('#modalDeudor').modal('hide');
                    $('#formDeudor')[0].reset(); $('#deudorId').val('');
                    cargarDeudores();
                    table.ajax.reload(); cargarResumen();
                    if(res.id) $('#selectDeudor').val(res.id);
                    showToast(res.id ? 'Deudor guardado':'Deudor creado','success');
                }).fail(x=> alert(x.responseJSON?.error||'Error'));
            });

            // ─── Procesar cobro (solo préstamos seleccionados) ──
            $('#formCobro').submit(function(e) {
                e.preventDefault();
                var btnSubmit = $(this).find('button[type="submit"]');
                btnSubmit.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');

                if (!$('#cuenta_cobro_id').val()) {
                    btnSubmit.prop('disabled', false).html('<i class="ti ti-device-floppy"></i> Confirmar Entrada de Efectivo');
                    return alert("Selecciona una cuenta de destino.");
                }

                var pagos = [];
                var error = '';
                $('.check-cobro:checked').each(function() {
                    const id = $(this).data('id');
                    const pend = parseFloat($(this).data('pendiente')) || 0;
                    const inp = $('.input-abono-cobro[data-id="' + id + '"]');
                    const abono = Math.round((parseFloat(inp.val()) || 0) * 100) / 100;
                    if (abono <= 0) return;
                    if (abono - pend > 0.009) { error = 'El abono del préstamo #' + id + ' excede su pendiente ($' + fmt(pend) + ').'; return false; }
                    pagos.push({ id: parseInt(id), abono: abono });
                });

                if (error) {
                    btnSubmit.prop('disabled', false).html('<i class="ti ti-device-floppy"></i> Confirmar Entrada de Efectivo');
                    return alert(error);
                }
                if (!pagos.length) {
                    btnSubmit.prop('disabled', false).html('<i class="ti ti-device-floppy"></i> Confirmar Entrada de Efectivo');
                    return alert("Selecciona al menos un préstamo e indica el monto a abonar.");
                }

                $.ajax({
                    url: 'api/prestamos-otorgados.php',
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        pagos: pagos,
                        cuenta_pago_id: $('#cuenta_cobro_id').val(),
                        fecha_pago: $('#fecha_cobro').val() + ' ' + new Date().toTimeString().split(' ')[0]
                    }),
                    success: function() {
                        $('#modalCobro').modal('hide');
                        table.ajax.reload();
                        cargarResumen();
                    },
                    error: function(xhr) { alert(xhr.responseJSON?.error || "Error al cobrar"); },
                    complete: function() { btnSubmit.prop('disabled', false).html('<i class="ti ti-device-floppy"></i> Confirmar Entrada de Efectivo'); }
                });
            });

            // ─── Mantener child rows visibles al recargar ─
            table.on('draw', function() {
                $('tr.shown').each(function() {
                    var row = table.row(this);
                    if (row.data()) {
                        $.get('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(row.data().persona_nombre), function(loans) {
                            row.child(buildChildHtml(loans)).show();
                        });
                    }
                });
            });

            // ─── Init ────────────────────────────────────
            $(document).ready(function() {
                cargarResumen();
                cargarDeudores();
                // compat clientes/promotores también
                $.get('api/clientes.php', function(data) {
                    if(data) data.forEach(function(c) { $('#listaPersonas').append('<option value="' + c.nombre + '">'); });
                }, 'json');
                $.get('api/promotores.php', function(data) {
                    if(data) data.forEach(function(p) { $('#listaPersonas').append('<option value="' + p.nombre + '">'); });
                }, 'json');
                // Limpiar preview al abrir modal
                $('#modalNuevoPrestamo').on('show.bs.modal', function(){ actualizarPreview(); });
            });
        </script>
    </div>
</body>
</html>
