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

<style>
     body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
        /* =========================================
   🌙 REGLAS PARA MODO OSCURO (Usuarios y DataTables)
   ========================================= */

/* 1. Textos generales y Tarjetas */
[data-bs-theme="dark"] h3,
[data-bs-theme="dark"] .page-breadcrumb h3 {
    color: #e9ecef !important;
}

[data-bs-theme="dark"] .card {
    background: #1a2234;
    border-color: #2c344c;
}

/* 2. Tabla principal (DataTables) */
[data-bs-theme="dark"] .table>thead>tr>th {
    background-color: #1a2234 !important;
    color: #e9ecef !important;
    border-bottom-color: #2c344c !important;
}

[data-bs-theme="dark"] .table>tbody>tr>td {
    color: #e9ecef !important;
    border-color: #2c344c !important;
}

/* Respetar el estilo "zebra" (striped) y hover en modo oscuro */
[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02);
}
[data-bs-theme="dark"] .table-hover>tbody>tr:hover>* {
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05);
}

/* 3. Controles de DataTables (Buscar, Paginación, Entradas) */
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate {
    color: #8c9ea8 !important;
}

/* Estilo para los botones de paginación */
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
    color: #8c9ea8 !important;
}
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #252f43 !important;
    border-color: #2c344c !important;
    color: #e9ecef !important;
}

/* Inputs del buscador de DataTables */
[data-bs-theme="dark"] .dataTables_wrapper select,
[data-bs-theme="dark"] .dataTables_wrapper input {
    background-color: #252f43;
    border: 1px solid #2c344c;
    color: #e9ecef;
}

/* 4. Modal de Agregar/Editar Usuario */
[data-bs-theme="dark"] .modal-content {
    background-color: #1a2234;
    border-color: #2c344c;
}
[data-bs-theme="dark"] .modal-header {
    border-bottom-color: #2c344c;
}
[data-bs-theme="dark"] .modal-footer {
    border-top-color: #2c344c;
}
[data-bs-theme="dark"] .modal-title {
    color: #e9ecef;
}
[data-bs-theme="dark"] .form-label {
    color: #e9ecef;
}

/* Campos de texto y select dentro del Modal */
[data-bs-theme="dark"] .form-control,
[data-bs-theme="dark"] .form-select {
    background-color: #252f43;
    border-color: #2c344c;
    color: #e9ecef;
}
[data-bs-theme="dark"] .form-control::placeholder {
    color: #8c9ea8;
}
</style>

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
                <div class="container-fluid px-4" style="max-width: 95%;">
                    
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
                        render: d => d ? '<span class="badge bg-success bg-opacity-10 text-success border-0">Sí</span>' : '<span class="badge bg-danger bg-opacity-10 text-danger border-0">No</span>'
                    },
                    {
                        data: null,
                        render: function(d) {
                            return `<div class="d-flex align-items-center gap-2">
                                <a href="detalle-prestamista.php?id=${d.id}" class="badge bg-info bg-opacity-10 text-info border-0 text-decoration-none" style="font-size: 0.75rem;" title="Detalles"><i class="ti ti-eye"></i> Detalles</a>
                                <button class="badge bg-warning bg-opacity-10 text-warning border-0 editar" style="cursor: pointer; font-size: 0.75rem;" data-id="${d.id}" title="Editar"><i class="ti ti-pencil"></i> Editar</button>
                                <button class="badge bg-danger bg-opacity-10 text-danger border-0 eliminar" style="cursor: pointer; font-size: 0.75rem;" data-id="${d.id}" title="Eliminar"><i class="ti ti-trash"></i> Eliminar</button>
                            </div>`;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                // Agregar el idioma español
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
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
