<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) { header('Location: login.php'); exit; }
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Prestamistas | Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
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
        .loans-header .sub-badge { font-size: 1rem; color: #9a9096; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }

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
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-users text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Prestamistas</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Administración de prestamistas</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPrestamista">
                                        <i class="ti ti-plus"></i> Nuevo Prestamista
                                    </button>
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
                                        <span class="metric-label">Total Prestamistas</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="ti ti-users"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-primary" id="kpiTotal">0</div>
                                    <div class="metric-footer">Registrados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Saldo en Deuda</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-coin"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiSaldo">$0.00</div>
                                    <div class="metric-footer">Por pagar</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Activos</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-check"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiActivos">0</div>
                                    <div class="metric-footer">Prestamistas activos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-danger"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Inactivos</span>
                                        <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="ti ti-x"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-danger" id="kpiInactivos">0</div>
                                    <div class="metric-footer">Desactivados</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         TABLE
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaPrestamistas" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th>Saldo Total</th>
                                        <th>Activo</th>
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
             MODAL: Prestamista
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalPrestamista" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-user text-primary"></i> <span id="tituloModal">Nuevo Prestamista</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formPrestamista">
                        <div class="modal-body">
                            <input type="hidden" id="id" name="id">
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-tag"></i> Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-phone"></i> Teléfono</label>
                                <input type="text" class="form-control" name="telefono">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-mail"></i> Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-map-pin"></i> Dirección</label>
                                <textarea class="form-control" name="direccion" rows="2"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="activoCheck" name="activo" value="1" checked>
                                <label class="form-check-label" for="activoCheck">Activo</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            var fmt = function(n) {
                return parseFloat(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
            };

            // ─── Avatar helper ───────────────────────────
            const avatarColors = ['var(--nx-primary)', 'var(--nx-success)', 'var(--nx-danger)', 'var(--nx-warning)', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
            function getAvatarColor(name) {
                if (!name) return '#9a9096';
                let hash = 0;
                for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
                return avatarColors[Math.abs(hash) % avatarColors.length];
            }

            // ─── Resumen (KPI Cards) ─────────────────────
            function cargarResumen() {
                $.get('api/prestamistas.php', function(data) {
                    if (!data) return;
                    $('#kpiTotal').text(data.length);
                    var saldoTotal = data.reduce(function(s, d) { return s + parseFloat(d.saldo_total || 0); }, 0);
                    $('#kpiSaldo').text('$' + fmt(saldoTotal));
                    var activos = data.filter(function(d) { return d.activo == 1; }).length;
                    $('#kpiActivos').text(activos);
                    $('#kpiInactivos').text(data.length - activos);
                }).fail(function() {
                    $('#kpiTotal').text('Error');
                });
            }

            // ─── DataTable ───────────────────────────────
            const tabla = $('#tablaPrestamistas').DataTable({
                ajax: {
                    url: 'api/prestamistas.php?activos=1',
                    dataSrc: ''
                },
                columns: [
                    { data: 'id' },
                    {
                        data: 'nombre',
                        render: function(n) {
                            var color = getAvatarColor(n);
                            var initial = n ? n.charAt(0).toUpperCase() : '?';
                            return '<div class="d-flex align-items-center gap-2">' +
                                   '<span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:' + color + '22;color:' + color + ';font-weight:700;font-size:0.85rem;">' + initial + '</span>' +
                                   '<span class="fw-semibold">' + n + '</span>' +
                                   '</div>';
                        }
                    },
                    {
                        data: 'telefono',
                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : '<span class="text-muted">—</span>'
                    },
                    {
                        data: 'email',
                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : '<span class="text-muted">—</span>'
                    },
                    {
                        data: 'direccion',
                        render: (d) => d || '<span class="text-muted">—</span>'
                    },
                    {
                        data: 'saldo_total',
                        render: function(d) {
                            var val = parseFloat(d) || 0;
                            return '<strong style="color:var(--nx-warning);">$' + fmt(val) + '</strong>';
                        }
                    },
                    {
                        data: 'activo',
                        render: d => d ? '<span class="badge bg-success bg-opacity-10 text-success border-0">Sí</span>' : '<span class="badge bg-danger bg-opacity-10 text-danger border-0">No</span>'
                    },
                    {
                        data: null,
                        render: function(d) {
                            return `<div class="d-flex align-items-center gap-1">
                                <a href="historial-prestamista.php?id=${d.id}" class="btn btn-sm btn-outline-secondary" title="Historial"><i class="ti ti-history"></i></a>
                                <a href="detalle-prestamista.php?id=${d.id}" class="btn btn-sm btn-outline-info" title="Gestión"><i class="ti ti-eye"></i></a>
                                <button class="btn btn-sm btn-outline-warning editar" data-id="${d.id}" title="Editar"><i class="ti ti-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger eliminar" data-id="${d.id}" title="Eliminar"><i class="ti ti-trash"></i></button>
                            </div>`;
                        }
                    }
                ],
                scrollX: true,
                order: [[0, 'desc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            // ─── Recalcular KPIs al cargar la tabla ──────
            tabla.on('draw', function() {
                cargarResumen();
            });

            // ─── Modal helpers ────────────────────────────
            function limpiarModal() {
                $('#id').val('');
                $('#formPrestamista')[0].reset();
                $('#activoCheck').prop('checked', true);
                $('#tituloModal').text('Nuevo Prestamista');
            }

            $('#formPrestamista').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = $('#id').val();
                formData.set('activo', $('#activoCheck').is(':checked') ? 1 : 0);
                if (id) formData.set('_method', 'PUT');
                $.ajax({
                    url: 'api/prestamistas.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#modalPrestamista').modal('hide');
                            limpiarModal();
                            tabla.ajax.reload();
                            showToast(id ? 'Prestamista actualizado' : 'Prestamista agregado', 'success');
                        } else {
                            alert('Error: ' + (response.error || 'Desconocido'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error al guardar';
                        try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}
                        alert('Error: ' + msg);
                    }
                });
            });

            $('#tablaPrestamistas').on('click', '.eliminar', function() {
                if (!confirm('¿Eliminar este prestamista?')) return;
                const id = $(this).data('id');
                $.ajax({
                    url: 'api/prestamistas.php?id=' + id,
                    method: 'DELETE',
                    success: function(response) {
                        tabla.ajax.reload();
                        showToast('Prestamista eliminado', 'success');
                    },
                    error: function(xhr) {
                        let msg = 'Error al eliminar';
                        try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}
                        showToast(msg, 'error');
                    }
                });
            });

            $('#tablaPrestamistas').on('click', '.editar', function() {
                const id = $(this).data('id');
                $.get('api/prestamistas.php?id=' + id, function(data) {
                    $('#id').val(data.id);
                    $('input[name="nombre"]').val(data.nombre);
                    $('input[name="telefono"]').val(data.telefono);
                    $('input[name="email"]').val(data.email);
                    $('textarea[name="direccion"]').val(data.direccion);
                    $('#activoCheck').prop('checked', data.activo == 1);
                    $('#tituloModal').text('Editar Prestamista');
                    $('#modalPrestamista').modal('show');
                });
            });

            function showToast(message, type = 'info') {
                var container = $('#toast-container');
                if (!container.length) {
                    $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>');
                    container = $('#toast-container');
                }
                var toast = $('<div class="toast align-items-center text-bg-' + type + ' border-0" role="alert"><div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
                container.append(toast);
                var bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
                bsToast.show();
                setTimeout(function() { toast.remove(); }, 3500);
            }

            $(document).ready(function() {
                limpiarModal();
                cargarResumen();
            });
        </script>
    </div>
</body>
</html>
