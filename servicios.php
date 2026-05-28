<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

    header('Location: login.php');

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

$pagina_actual = basename($_SERVER['PHP_SELF']);

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Servicios - Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

</head>

<style>
     body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
</style>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">

            <div class="body-wrapper-inner">

                <div class="container-fluid">

                    <div class="row">

                        <div class="col-12">

                            <div class="page-breadcrumb d-flex align-items-center mb-3">

                                <h3 class="mb-0"><i class="ti ti-clipboard"></i> Servicios</h3>

                                <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalServicio" onclick="limpiarFormulario()">+ Nuevo Servicio</button>

                            </div>

                        </div>

                    </div>



                    <div class="row">

                        <div class="col-12">

                            <div class="card">

                                <div class="card-body table-responsive">

                                    <table id="tablaServicios" class="table table-striped" style="width:100%">

                                        <thead>

                                            <tr>

                                                <th>ID</th>

                                                <th>Nombre</th>

                                                <th>Descripción</th>

                                                <th>Tipo de Movimiento</th>

                                                <th>Activo</th>

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

            </div>

        </div>



        <!-- Modal -->

        <div class="modal fade" id="modalServicio" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title" id="tituloModal">Nuevo Servicio</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="limpiarFormulario()"></button>

                    </div>

                    <form id="formServicio">

                        <div class="modal-body">

                            <input type="hidden" id="id" name="id">

                            <div class="mb-3">

                                <label class="form-label">Nombre *</label>

                                <input type="text" class="form-control" name="nombre" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Descripción</label>

                                <textarea class="form-control" name="descripcion" rows="2"></textarea>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Tipo de Movimiento *</label>

                                <select class="form-select" name="tipo_movimiento" required>

                                    <option value="" selected disabled>Selecciona un tipo</option>

                                    <option value="entrada">Entrada</option>

                                    <option value="salida">Salida</option>

                                </select>

                            </div>

                            <div class="mb-3 form-check">

                                <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>

                                <label class="form-check-label" for="activo">Activo</label>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="limpiarFormulario()">Cancelar</button>

                            <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>

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

            let esEdicion = false; // Variable para controlar si es edición o creación



            const tabla = $('#tablaServicios').DataTable({

                ajax: {

                    url: 'api/servicios.php',

                    dataSrc: ''

                },

                columns: [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'descripcion'

                    },

                    {

                        data: 'tipo_movimiento',

                        render: d => d === 'entrada' ? '<span class="badge bg-success">Entrada</span>' : '<span class="badge bg-danger">Salida</span>'

                    },

                    {

                        data: 'activo',

                        render: d => d == 1 ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>'

                    },

                    {

                        data: null,

                        render: d => `

                <button class="btn btn-sm btn-warning editar" data-id="${d.id}"><i class="ti ti-edit"></i> Editar</button>

                <button class="btn btn-sm btn-danger eliminar" data-id="${d.id}"><i class="ti ti-trash"></i> Eliminar</button>

            `

                    }

                ],

                scrollX: true,
                order: [[0, 'desc']]

            });



            // Función para limpiar el formulario y restablecer al estado inicial

            function limpiarFormulario() {

                $('#formServicio')[0].reset();

                $('#id').val('');

                $('#activo').prop('checked', true); // Valor por defecto para nuevo registro

                $('#tituloModal').text('Nuevo Servicio');

                $('#btnGuardar').text('Guardar');

                esEdicion = false;

            }



            $('#formServicio').on('submit', e => {

                e.preventDefault();

                const fd = new FormData($('#formServicio')[0]);

                fd.set('activo', $('#activo').is(':checked') ? 1 : 0);



                let url = 'api/servicios.php';

                let method = 'POST';



                // Si es edición, el ID ya está establecido

                if (esEdicion) {

                    // El ID se envía en el FormData, el backend lo detecta y actualiza

                } else {

                    // Para creación, aseguramos que no se envíe un ID vacío

                    if (!fd.get('id') || fd.get('id') === '') {

                        fd.delete('id');

                    }

                }



                $.ajax({

                    url: url,

                    method: method,

                    data: fd,

                    processData: false,

                    contentType: false,

                    success: () => {

                        $('#modalServicio').modal('hide');

                        limpiarFormulario(); // Limpiar después de guardar

                        tabla.ajax.reload();

                        showToast(esEdicion ? 'Servicio actualizado correctamente' : 'Servicio guardado correctamente', 'success');

                    },

                    error: xhr => {

                        let msg = esEdicion ? 'Error al actualizar servicio' : 'Error al guardar servicio';

                        try {

                            msg = JSON.parse(xhr.responseText).error || msg;

                        } catch (e) {}

                        showToast(msg, 'error');

                    }

                });

            });



            $('#tablaServicios').on('click', '.editar', function() {

                const id = $(this).data('id');

                esEdicion = true; // Marcamos que es edición



                $.get('api/servicios.php?id=' + id, d => {

                    $('#id').val(d.id);

                    $('input[name="nombre"]').val(d.nombre);

                    $('textarea[name="descripcion"]').val(d.descripcion || '');

                    $('select[name="tipo_movimiento"]').val(d.tipo_movimiento);

                    $('#activo').prop('checked', d.activo == 1);

                    $('#tituloModal').text('Editar Servicio');

                    $('#btnGuardar').text('Actualizar');

                    $('#modalServicio').modal('show');

                });

            });



            $('#tablaServicios').on('click', '.eliminar', function() {

                if (confirm('¿Eliminar servicio?')) {

                    $.ajax({

                        url: 'api/servicios.php',

                        method: 'DELETE',

                        data: {

                            id: $(this).data('id')

                        },

                        success: () => {

                            tabla.ajax.reload();

                            showToast('Servicio eliminado', 'success');

                        },

                        error: () => showToast('Error al eliminar', 'error')

                    });

                }

            });

        </script>

</body>



</html>
