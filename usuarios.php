<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Usuarios - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Header -->
        <?php include 'src/assets/partials/header.php'; ?>

        <!-- Sidebar -->
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <!-- Main wrapper -->
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-users"></i> Usuarios</h3>
                                <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Nuevo Usuario</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body table-responsive">
                                    <table id="tablaUsuarios" class="table table-striped table-hover table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Rol</th>
                                                <th>Creado en</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Se llenará con AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de usuario -->
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
                                <input type="password" class="form-control" name="password" id="inputPassword">
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

        <!-- Contenedor para toasts (¡importante!) -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

        <!-- Scripts -->
        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="src/assets/js/dark-mode.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>

        <script>
            const currentUserId = <?= (int)($_SESSION['usuario_id'] ?? 0) ?>;

            // Limpiar modal al abrir para nuevo usuario
            $('button[data-bs-target="#modalUsuario"]').on('click', function() {
                $('#formUsuario')[0].reset();
                $('#id').val('');
                $('#modalTitle').html('<i class="ti ti-user-plus"></i> Nuevo Usuario');
                $('#labelPassword').text('Contraseña');
                $('#inputPassword').prop('required', true).attr('placeholder', 'Contraseña (requerida)');
            });

            // Cargar usuarios
            const tabla = $('#tablaUsuarios').DataTable({
                ajax: {
                    url: 'api/usuarios.php',
                    dataSrc: ''
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'email',
                        render: (d) => `<span class="no-capitalize">${d}</span>`
                    },
                    {
                        data: 'rol',
                        render: function(data) {
                            return data === 'admin' ? '<span class="badge bg-danger">Admin</span>' :
                                data === 'prestamista' ? '<span class="badge bg-warning text-dark">Prestamista</span>' :
                                '<span class="badge bg-info">Usuario</span>';
                        }
                    },
                    {
                        data: 'creado_en',
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const esActual = Number(data.id) === Number(currentUserId);
                            const btnEditar = `<button class="btn btn-sm btn-warning editar" data-id="${data.id}"><i class="ti ti-pencil"></i> Editar</button>`;
                            const btnEliminar = esActual ?
                                `<button class="btn btn-sm btn-danger" disabled title="No puedes desactivarte"><i class="ti ti-x"></i> Desactivar</button>` :
                                `<button class="btn btn-sm btn-danger eliminar" data-id="${data.id}"><i class="ti ti-x"></i> Desactivar</button>`;
                            return `${btnEditar} ${btnEliminar}`;
                        }
                    }
                ]
            });

            // Guardar usuario
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
                        showToast('Usuario guardado correctamente', 'success');
                    },
                    error: function(xhr) {
                        let message = 'Error al guardar el usuario';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            message = res.error || message;
                        } catch (e) {}
                        showToast(message, 'error');
                    }
                });
            });

            // Editar usuario
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
                    try {
                        const res = JSON.parse(xhr.responseText);
                        message = res.error || message;
                    } catch (e) {}
                    showToast(message, 'error');
                });
            });

            // Eliminar usuario
            $('#tablaUsuarios').on('click', '.eliminar', function() {
                const id = $(this).attr('data-id');
                if (confirm('Desactivar usuario?')) {
                    $.ajax({
                        url: 'api/usuarios.php',
                        method: 'DELETE',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            id: id
                        }),
                        success: function() {
                            tabla.ajax.reload();
                            showToast('Usuario desactivado correctamente', 'success');
                        },
                        error: function(xhr) {
                            let message = 'Error al eliminar el usuario';
                            try {
                                const res = JSON.parse(xhr.responseText);
                                message = res.error || message;
                            } catch (e) {}
                            showToast(message, 'error');
                        }
                    });
                }
            });
        </script>

    </div>
</body>

</html>