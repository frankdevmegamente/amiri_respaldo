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

    <title>Prestamistas - Amiri</title>

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
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-user"></i> Prestamistas</h3>
                                <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalPrestamista">+ Nuevo Prestamista</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaPrestamistas" class="table table-striped table-hover table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Email</th>
                                                <th>Dirección</th>
                                                <th>Saldo Total</th>
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



        <!-- Modal de prestamista -->

        <div class="modal fade" id="modalPrestamista" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title"><i class="ti ti-user"></i> <span id="tituloModal">Nuevo Prestamista</span></h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <form id="formPrestamista">

                        <div class="modal-body">

                            <input type="hidden" id="id" name="id">

                            <div class="mb-3">

                                <label class="form-label">Nombre *</label>

                                <input type="text" class="form-control" name="nombre" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Teléfono</label>

                                <input type="text" class="form-control" name="telefono">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Email</label>

                                <input type="email" class="form-control" name="email">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Dirección</label>

                                <textarea class="form-control" name="direccion" rows="2"></textarea>

                            </div>

                            <div class="mb-3 form-check">

                                <input type="checkbox" class="form-check-input" id="activoCheck" name="activo" value="1" checked>

                                <label class="form-check-label" for="activoCheck">Activo</label>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                            <button type="submit" class="btn btn-primary">Guardar</button>

                        </div>

                    </form>

                </div>

            </div>

        </div>



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

            function formatoMoneda(valor) {

                if (isNaN(valor)) valor = 0;

                return parseFloat(valor).toLocaleString('en-US', {

                    style: 'currency',

                    currency: 'MXN',

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            const tabla = $('#tablaPrestamistas').DataTable({

                ajax: {

                    url: 'api/prestamistas.php?activos=1',

                    dataSrc: ''

                },

                columns: [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'telefono',

                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : ''

                    },

                    {

                        data: 'email',

                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : ''

                    },

                    {

                        data: 'direccion'

                    },

                    {

                        data: 'saldo_total',

                        render: d => formatoMoneda(d)

                    },

                    {

                        data: 'activo',

                        render: d => d ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>'

                    },

                    {

                        data: null,

                        render: d => `

                        <a href="detalle-prestamista.php?id=${d.id}" class="btn btn-sm btn-info"><i class="ti ti-eye"></i> Detalles</a>

                        <button class="btn btn-sm btn-warning editar" data-id="${d.id}"><i class="ti ti-edit"></i> Editar</button>

                        <button class="btn btn-sm btn-danger eliminar" data-id="${d.id}"><i class="ti ti-trash"></i> Eliminar</button>

                    `

                    }

                ],

                order: [

                    [0, 'desc']

                ]

            });



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



                // Manejar checkbox activo

                formData.set('activo', $('#activoCheck').is(':checked') ? 1 : 0);



                // Si es actualización, agregar _method=PUT

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

                        try {

                            msg = JSON.parse(xhr.responseText).error || msg;

                        } catch (e) {}

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

                let container = document.getElementById('toast-container');

                if (!container) {

                    container = document.createElement('div');

                    container.id = 'toast-container';

                    container.style.position = 'fixed';

                    container.style.top = '20px';

                    container.style.right = '20px';

                    container.style.zIndex = '1000';

                    document.body.appendChild(container);

                }



                const toast = document.createElement('div');

                toast.className = `toast bg-${type} text-white`;

                toast.innerHTML = `<div class="toast-body">${message}</div>`;

                toast.style.marginBottom = '10px';

                container.insertAdjacentElement('afterbegin', toast);



                setTimeout(() => toast.remove(), 3000);

            }



            $(document).ready(function() {

                limpiarModal();

            });

        </script>

</body>



</html>