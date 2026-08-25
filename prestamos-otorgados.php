<?php
require_once __DIR__ . '/src/config/session.php';
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') { header('Location: login.php'); exit; }
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
$cuentas = $pdo->query("SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN' AND tipo_cuenta IN (1, 2) ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Préstamos Otorgados | Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .loans-header { position: relative; overflow: hidden; border-radius: 16px !important; }
        .loans-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
        .loans-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
        .loans-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .loans-header .sub-badge { font-size: 1rem; color: #8c9ea8; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #8c9ea8; }

        /* ─── Table extras ────────────────────────────── */
        td.details-control { cursor: pointer; text-align: center; width: 30px; }
        td.details-control i { transition: transform 0.2s; font-size: 1.1rem; }
        tr.shown td.details-control i { transform: rotate(90deg); }
        .child-table { margin: 8px 0 8px 40px; width: calc(100% - 40px); }
        .child-table td, .child-table th { padding: 6px 10px !important; font-size: 0.85rem; }

        /* ─── Dark mode ───────────────────────────────── */
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #e9ecef !important; }
        [data-bs-theme="dark"] .card { background: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: #1a2234 !important; color: #e9ecef !important; border-bottom-color: #2c344c !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #e9ecef !important; border-color: #2c344c !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05); }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #8c9ea8 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #252f43 !important; border-color: #2c344c !important; color: #e9ecef !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select, [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #252f43; border: 1px solid #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .modal-content { background-color: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: #2c344c; }
        [data-bs-theme="dark"] .modal-footer { border-top-color: #2c344c; }
        [data-bs-theme="dark"] .modal-title { color: #e9ecef; }
        [data-bs-theme="dark"] .form-label { color: #e9ecef; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] .input-group-text { background-color: #252f43; border-color: #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #8c9ea8; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(93, 135, 255, 0.12) !important; }
        [data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(25, 135, 84, 0.12) !important; }
        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.12) !important; }
        [data-bs-theme="dark"] .bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.12) !important; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
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

                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             MODAL: Cobro / Abono
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalCobro" tabindex="-1">
            <div class="modal-dialog">
                <form id="formCobro" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-cash text-success"></i> Registrar Abono / Cobro</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2" id="resumenDeudor" style="display: none;">
                            <small>Cobrando a: <strong id="nombreDeudorCobro"></strong></small>
                        </div>

                        <div id="contenedorPagosMultiples" class="mb-3 p-3 bg-light border rounded" style="display: none;"></div>

                        <div class="alert alert-warning py-2 text-center" id="alertaDeudaTotal" style="display: none;">
                            <small>Deuda Total: <strong id="deudaTotalSugerida">$0.00</strong></small>
                        </div>

                        <div class="mb-3" id="grupoMontoGlobal">
                            <label class="form-label fw-bold text-success">¿Cuánto dinero estás recibiendo?</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" step="0.01" id="monto_a_pagar_input" class="form-control form-control-lg text-success fw-bold">
                            </div>
                            <small class="text-muted">Se distribuirá automáticamente entre los préstamos pendientes.</small>
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
             MODAL: Nuevo Préstamo
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalNuevoPrestamo" tabindex="-1">
            <div class="modal-dialog">
                <form id="formNuevoPrestamo" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus-circle text-primary"></i> Registrar Nuevo Préstamo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="ti ti-user"></i> Nombre de la Persona/Socio</label>
                            <input type="text" name="persona_nombre" class="form-control" list="listaPersonas" autocomplete="off" placeholder="Escribe o selecciona un nombre..." required>
                            <datalist id="listaPersonas"></datalist>
                            <small class="text-muted">Sugiere nombres de Promotores o Clientes.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="ti ti-coin"></i> Monto a Prestar</label>
                                <input type="text" step="0.01" name="monto" class="form-control" required>
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
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Confirmar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="src/assets/js/dark-mode.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            var fmt = function(n) {
                return parseFloat(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
            };

            // ─── Resumen (KPI Cards) ─────────────────────
            function cargarResumen() {
                $.get('api/prestamos-otorgados.php?resumen=1', function(data) {
                    $('#kpiTotalPrestado').text('$' + fmt(data.total_prestado));
                    $('#kpiCobrado').text('$' + fmt(data.total_cobrado));
                    $('#kpiPendiente').text('$' + fmt(data.total_pendiente));
                    $('#kpiDeudores').text(data.deudores);
                }).fail(function() {
                    $('#kpiTotalPrestado').text('Error');
                });
            }

            // ─── Avatar helper ───────────────────────────
            const avatarColors = ['#5D87FF', '#13DEB9', '#FA896B', '#FFAE1F', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
            function getAvatarColor(name) {
                if (!name) return '#8c9ea8';
                let hash = 0;
                for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
                return avatarColors[Math.abs(hash) % avatarColors.length];
            }

            // ─── DataTable ───────────────────────────────
            function buildChildHtml(loans) {
                if (!loans || !loans.length) return '<div class="text-muted text-center py-2">Sin préstamos</div>';
                var h = '<table class="table table-sm table-borderless child-table mb-0"><thead><tr class="text-muted" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;"><th>Fecha</th><th>Monto</th><th>Cobrado</th><th>Pendiente</th><th>Estatus</th></tr></thead><tbody>';
                loans.forEach(function(l) {
                    var pend = l.monto_total - l.monto_pagado;
                    var cls = l.estado === 'pagado' ? 'success' : l.estado === 'parcial' ? 'warning' : 'danger';
                    h += '<tr><td>' + (l.fecha_prestamo ? l.fecha_prestamo.split(' ')[0].split('-').reverse().join('/') : '') + '</td><td class="fw-bold">$' + fmt(l.monto_total) + '</td><td class="text-success">$' + fmt(l.monto_pagado) + '</td><td class="text-danger fw-bold">$' + fmt(pend) + '</td><td><span class="badge bg-' + cls + ' bg-opacity-10 text-' + cls + ' border-0" style="font-size:0.7rem;">' + l.estado + '</span></td></tr>';
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
                        render: function(n) {
                            var color = getAvatarColor(n);
                            var initial = n ? n.charAt(0).toUpperCase() : '?';
                            return '<div class="d-flex align-items-center gap-2">' +
                                   '<span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:' + color + '22;color:' + color + ';font-weight:700;font-size:0.85rem;">' + initial + '</span>' +
                                   '<a href="detalle-deudor.php?nombre=' + encodeURIComponent(n) + '" class="fw-semibold text-decoration-none">' + n + '</a>' +
                                   '</div>';
                        }
                    },
                    { data: 'total_prestamos', render: function(n) { return '<span class="badge bg-light text-dark border-0">' + n + '</span>'; } },
                    { data: 'total_prestado', render: function(n) { return ' <strong>$' + fmt(n) + '</strong>'; } },
                    { data: 'total_cobrado', render: function(n) { return '<span style="color:#13deb9;font-weight:700;">$' + fmt(n) + '</span>'; } },
                    { data: 'total_pendiente', render: function(n) { return '<span style="color:#ffae1f;font-weight:700;">$' + fmt(n) + '</span>'; } },
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

            // ─── Cobro individual ────────────────────────
            $(document).on('click', '.btn-cobrar-deudor', function() {
                var nombre = $(this).data('nombre');
                var pendiente = parseFloat($(this).data('pendiente'));
                $('#contenedorPagosMultiples').hide().empty();
                $('#resumenDeudor').show().find('#nombreDeudorCobro').text(nombre);
                $('#alertaDeudaTotal').show();
                $('#deudaTotalSugerida').text('$' + fmt(pendiente));
                $('#monto_a_pagar_input').val(pendiente.toFixed(2));
                $('#cuenta_cobro_id').val('');
                $('#fecha_cobro').val(new Date().toISOString().slice(0, 10));
                $('#formCobro').data('modo', 'individual').data('nombre', nombre);
                $('#modalCobro').modal('show');
            });

            // ─── Guardar nuevo préstamo ──────────────────
            $('#formNuevoPrestamo').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&fecha_prestamo=' + new Date().toISOString().slice(0, 19).replace('T', ' ');
                $.post('api/prestamos-otorgados.php', formData, function() {
                    $('#modalNuevoPrestamo').modal('hide');
                    $('#formNuevoPrestamo')[0].reset();
                    table.ajax.reload();
                    cargarResumen();
                }).fail(function(xhr) { alert(xhr.responseJSON?.error || "Error"); });
            });

            // ─── Procesar cobro ──────────────────────────
            $('#formCobro').submit(function(e) {
                e.preventDefault();
                var btnSubmit = $(this).find('button[type="submit"]');
                btnSubmit.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');

                var montoTotal = parseFloat($('#monto_a_pagar_input').val()) || 0;
                if (montoTotal <= 0) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("Ingresa un monto v\u00e1lido.");
                }

                if (!$('#cuenta_cobro_id').val()) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("Selecciona una cuenta de destino.");
                }

                var nombres = [$('#formCobro').data('nombre')];
                if (!nombres.length) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("No hay deudores seleccionados.");
                }

                $.getJSON('api/prestamos-otorgados.php', function(allLoans) {
                    var pagos = [];
                    var restante = montoTotal;
                    allLoans.forEach(function(p) {
                        if (nombres.indexOf(p.persona_nombre) !== -1 && p.estado !== 'pagado') {
                            var pendiente = p.monto_total - p.monto_pagado;
                            var abono = Math.min(pendiente, restante);
                            if (abono > 0.01) {
                                pagos.push({ id: p.id, abono: abono });
                                restante -= abono;
                            }
                        }
                    });

                    if (!pagos.length) {
                        btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                        return alert("No hay pr\u00e9stamos pendientes para los deudores seleccionados.");
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
                        complete: function() { btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo'); }
                    });
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

                $.get('api/clientes.php', function(data) {
                    if(data) data.forEach(function(c) { $('#listaPersonas').append('<option value="' + c.nombre + '">'); });
                }, 'json');
                $.get('api/promotores.php', function(data) {
                    if(data) data.forEach(function(p) { $('#listaPersonas').append('<option value="' + p.nombre + '">'); });
                }, 'json');
            });
        </script>
    </div>
</body>
</html>
