<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$currentUserId = (int)($_SESSION['usuario_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Usuarios - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .users-header {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
        }
        .users-header .bg-pattern {
            position: absolute; top: -50%; right: -10%;
            width: 400px; height: 400px; border-radius: 50%;
            background: rgba(93, 135, 255, 0.08); pointer-events: none;
        }
        .users-header .bg-pattern-2 {
            position: absolute; bottom: -30%; left: 20%;
            width: 250px; height: 250px; border-radius: 50%;
            background: rgba(19, 222, 185, 0.06); pointer-events: none;
        }
        .users-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .users-header .sub-badge { font-size: 1rem; color: #8c9ea8; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card {
            position: relative;
            border-radius: 12px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #8c9ea8; }

        /* ─── Avatar ──────────────────────────────────── */
        .avatar-initials {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 700; flex-shrink: 0;
        }

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
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #252f43; border-color: #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #8c9ea8; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
         data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- ════════════════════════════════════
                         PAGE HEADER
                         ════════════════════════════════════ -->
                    <div class="card users-header shadow-sm border-0 rounded-4 mb-4">
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
                                            <h2 class="mb-0">Usuarios</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Administración de usuarios</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                        <i class="ti ti-plus"></i> Nuevo Usuario
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
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-users"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiTotal">0</div>
                                    <div class="metric-footer">Usuarios activos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-danger"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Admins</span>
                                        <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="ti ti-shield"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-danger" id="kpiAdmins">0</div>
                                    <div class="metric-footer">Con acceso total</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Prestamistas</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-coin"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiPrestamistas">0</div>
                                    <div class="metric-footer">Módulo préstamos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-secondary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Usuarios</span>
                                        <div class="metric-icon bg-secondary bg-opacity-10 text-secondary">
                                            <i class="ti ti-user"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-secondary" id="kpiUsuarios">0</div>
                                    <div class="metric-footer">Acceso básico</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         TABLE
                         ════════════════════════════════════ -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaUsuarios" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Registrado</th>
                                        <th>Acciones</th>
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
             MODAL: Nuevo / Editar Usuario
             ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="modalUsuario" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle"><i class="ti ti-user-plus"></i> Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formUsuario">
                        <div class="modal-body">
                            <input type="hidden" id="id" name="id">
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-id-badge"></i> Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-mail"></i> Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" id="labelPassword"><i class="ti ti-lock"></i> Contraseña</label>
                                <input type="password" class="form-control" name="password" id="inputPassword" placeholder="Contraseña (requerida)">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="ti ti-shield"></i> Rol</label>
                                <select class="form-select" name="rol" required>
                                    <option value="" selected disabled>Selecciona una opción</option>
                                    <option value="admin">Admin</option>
                                    <option value="prestamista">Prestamista</option>
                                    <option value="usuario">Usuario</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ti ti-x"></i> Cancelar</button>
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
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="src/assets/js/dark-mode.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>

        <script>
            const currentUserId = <?= $currentUserId ?>;

            // ─── Avatar helper ───────────────────────────
            const avatarColors = ['#5D87FF', '#13DEB9', '#FA896B', '#FFAE1F', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
            function getAvatarColor(name) {
                let hash = 0;
                for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
                return avatarColors[Math.abs(hash) % avatarColors.length];
            }

            // ─── Resumen (KPI Cards) ─────────────────────
            function cargarResumen() {
                $.get('api/usuarios.php?resumen=1', function(data) {
                    $('#kpiTotal').text(data.total);
                    $('#kpiAdmins').text(data.admins);
                    $('#kpiPrestamistas').text(data.prestamistas);
                    $('#kpiUsuarios').text(data.usuarios);
                }).fail(function() {
                    $('#kpiTotal').text('Error');
                });
            }

            // ─── DataTable ───────────────────────────────
            const badgeRol = {
                'admin': '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Admin</span>',
                'prestamista': '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Prestamista</span>',
                'usuario': '<span class="badge bg-info bg-opacity-10 text-info border-0">Usuario</span>'
            };

            const tabla = $('#tablaUsuarios').DataTable({
                ajax: { url: 'api/usuarios.php', dataSrc: '' },
                columns: [
                    {
                        data: 'nombre',
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'type') return data;
                            let initial = data ? data.charAt(0).toUpperCase() : '?';
                            let color = getAvatarColor(data || '');
                            let badge = Number(row.id) === currentUserId
                                ? ' <span class="badge bg-success bg-opacity-10 text-success border-0" style="font-size:0.6rem;">Tú</span>'
                                : '';
                            return '<div class="d-flex align-items-center gap-2">' +
                                   '<span class="avatar-initials" style="background:' + color + '22;color:' + color + ';">' + initial + '</span>' +
                                   '<span class="fw-semibold">' + data + '</span>' + badge +
                                   '</div>';
                        }
                    },
                    { data: 'email', render: function(d) { return '<span class="no-capitalize" style="font-size:0.9rem;">' + d + '</span>'; } },
                    {
                        data: 'rol',
                        render: function(data) { return badgeRol[data] || badgeRol['usuario']; }
                    },
                    {
                        data: 'creado_en',
                        render: function(data) {
                            if (!data) return '—';
                            let d = new Date(data);
                            return '<span class="small text-muted">' + d.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }) + '</span>' +
                                   '<br><small style="font-size:0.65rem;color:#8c9ea8;">' + d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) + '</small>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const esActual = Number(data.id) === Number(currentUserId);
                            const btnEditar = '<button class="btn btn-sm btn-outline-primary editar me-1" data-id="' + data.id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                            const btnEliminar = esActual
                                ? '<button class="btn btn-sm btn-outline-secondary" disabled title="No puedes desactivarte"><i class="ti ti-power"></i></button>'
                                : '<button class="btn btn-sm btn-outline-danger eliminar" data-id="' + data.id + '" title="Desactivar"><i class="ti ti-power"></i></button>';
                            return '<div class="d-flex">' + btnEditar + btnEliminar + '</div>';
                        }
                    }
                ],
                scrollX: true,
                order: [[3, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ─── Modal ───────────────────────────────────
            $('button[data-bs-target="#modalUsuario"]').on('click', function() {
                $('#formUsuario')[0].reset();
                $('#id').val('');
                $('#modalTitle').html('<i class="ti ti-user-plus"></i> Nuevo Usuario');
                $('#labelPassword').text('Contraseña');
                $('#inputPassword').prop('required', true).attr('placeholder', 'Contraseña (requerida)');
            });

            $('#formUsuario').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: 'api/usuarios.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#modalUsuario').modal('hide');
                        tabla.ajax.reload();
                        cargarResumen();
                        showToast('Usuario guardado correctamente', 'success');
                    },
                    error: function(xhr) {
                        let message = 'Error al guardar el usuario';
                        try { const res = JSON.parse(xhr.responseText); message = res.error || message; } catch(e) {}
                        showToast(message, 'error');
                    }
                });
            });

            // ─── Editar ──────────────────────────────────
            $('#tablaUsuarios').on('click', '.editar', function() {
                const id = $(this).data('id');
                $.get('api/usuarios.php?id=' + id, function(data) {
                    $('#id').val(data.id);
                    $('input[name="nombre"]').val(data.nombre);
                    $('input[name="email"]').val(data.email);
                    $('select[name="rol"]').val(data.rol);
                    $('#modalTitle').html('<i class="ti ti-pencil"></i> Editando Usuario');
                    $('#labelPassword').text('Nueva contraseña');
                    $('#inputPassword').prop('required', false).attr('placeholder', 'Dejar vacío para no cambiar');
                    $('#modalUsuario').modal('show');
                }).fail(function(xhr) {
                    let message = 'Error al cargar el usuario';
                    try { const res = JSON.parse(xhr.responseText); message = res.error || message; } catch(e) {}
                    showToast(message, 'error');
                });
            });

            // ─── Eliminar ────────────────────────────────
            $('#tablaUsuarios').on('click', '.eliminar', function() {
                const id = $(this).data('id');
                if (confirm('Desactivar usuario?')) {
                    $.ajax({
                        url: 'api/usuarios.php',
                        method: 'DELETE',
                        contentType: 'application/json',
                        data: JSON.stringify({ id: id }),
                        success: function() {
                            tabla.ajax.reload();
                            cargarResumen();
                            showToast('Usuario desactivado correctamente', 'success');
                        },
                        error: function(xhr) {
                            let message = 'Error al eliminar el usuario';
                            try { const res = JSON.parse(xhr.responseText); message = res.error || message; } catch(e) {}
                            showToast(message, 'error');
                        }
                    });
                }
            });

            // ─── Init ────────────────────────────────────
            $(document).ready(function() {
                cargarResumen();
            });
        </script>
    </div>
</body>
</html>
