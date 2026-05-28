<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

$pagina_actual = basename($_SERVER['PHP_SELF']);

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Promotores - Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css  " />

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

</head>

<style>
        .body-wrapper {
            /* Aumentamos esto para librar completamente la altura del header azul */
            padding-top: 100px !important; 
        }
        
        .body-wrapper-inner, 
        .body-wrapper .container-fluid {
            /* Le damos un pequeño respiro visual para que el título no quede pegando con el header */
            padding-top: 15px !important;
            margin-top: 0 !important;
        }
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



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-user-plus"></i> Promotores</h3>
                                <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalPromotor">+ Nuevo Promotor</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaPromotores" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Email</th>
                                                <th>Cuenta Comisiones</th>
                                                <th>Saldo Comisiones</th>
                                                <th>Activo</th>
                                                <th>Creado</th>
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

        <div class="modal fade" id="modalPromotor" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title"><i class="ti ti-user-plus"></i> <span id="modal-title">Nuevo Promotor</span></h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <form id="formPromotor">

                        <div class="modal-body">

                            <input type="hidden" id="id" name="id">

                            <div class="mb-3">

                                <label class="form-label">Nombre</label>

                                <input type="text" class="form-control" name="nombre" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Teléfono</label>

                                <input type="text" class="form-control" name="telefono" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Email</label>

                                <input type="email" class="form-control" name="email" required>

                            </div>

                            <div class="mb-3 form-check">

                                <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>

                                <label class="form-check-label" for="activo">Activo</label>

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


        <div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-xl"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-list-details"></i> Historial de Movimientos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaMovimientos" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Servicio</th>
                                <th>Tipo</th>
                                <th>Moneda</th>
                                <th>Monto</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>



        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <script src="src/assets/js/sidebarmenu.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js  "></script>

        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js  "></script>

        <script src="src/assets/js/dark-mode.js"></script>

        <script src="src/assets/js/pantalla-completa.js"></script>

        <script src="src/assets/js/utils.js"></script>



        <script>

            // Función showToast inline (ya que no se carga utils.js)

            function showToast(message, type = 'success') {

                const bgColor = {

                    success: 'bg-success',

                    error: 'bg-danger',

                    warning: 'bg-warning text-dark',

                    info: 'bg-info text-dark'

                }[type] || 'bg-secondary';



                const toastHtml = `

                    <div class="toast ${bgColor} text-white" role="alert" aria-live="assertive" aria-atomic="true">

                        <div class="toast-header ${bgColor === 'bg-warning text-dark' || bgColor === 'bg-info text-dark' ? '' : 'text-white'}">

                            <strong class="me-auto">

                                ${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : type === 'warning' ? 'Advertencia' : 'Información'}

                            </strong>

                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>

                        </div>

                        <div class="toast-body">

                            ${message}

                        </div>

                    </div>

                `;



                const container = document.querySelector('.toast-container');

                container.innerHTML = toastHtml;

                const toastElement = container.querySelector('.toast');

                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });

                toast.show();

            }

        </script>



        <script>

            // DEBUG: Verificar que el script se ejecuta

            console.log('🔵 promotores.php - Script iniciado');

            console.log('🔵 usuario_rol desde PHP:', '<?= $_SESSION['usuario_rol'] ?? "undefined" ?>');

            

            const tabla = $('#tablaPromotores').DataTable({

                ajax: {

                    url: 'api/promotores.php?activos=1',

                    dataSrc: function(json) {

                        // Filtrar para ocultar "AMIRI DIRECTO"

                        return json.filter(function(promotor) {

                            return promotor.nombre !== 'AMIRI DIRECTO';

                        });

                    },

                    error: function(xhr) {

                        if (xhr.status === 401) {

                            window.location.href = 'login.php';

                        }

                    }

                },

                columns: [{

                        data: 'id'

                    },

                    {

                        data: 'nombre',

                        render: (d, type, row) => `<a href="detalle-promotor.php?id=${row.id}" class="text-decoration-none fw-bold">${d}</a>`

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

                        data: 'cuenta_nombre',

                        render: d => d ? d : '<span class="text-muted">Sin cuenta</span>'

                    },

                    {

                        data: 'saldo_actual',

                        render: d => d ? '$' + parseFloat(d).toFixed(2) : '$0.00'

                    },

                    {
                        data: 'activo',
                        render: d => d == 1 ? '<span class="badge bg-success bg-opacity-10 text-success border-0">Sí</span>' : '<span class="badge bg-danger bg-opacity-10 text-danger border-0">No</span>'
                    },
                    {
                        data: 'creado_en',
                        render: d => d ? new Date(d).toLocaleDateString('es-MX') : ''
                    },
                    // Dentro de la configuración de columnas de tu DataTable en promotores.php
{
    data: null,
    render: function(d) {
        return `<div class="d-flex align-items-center gap-2">
            <a href="historial-promotor.php?id=${d.id}" class="badge bg-info bg-opacity-10 text-info border-0 text-decoration-none" style="font-size: 0.75rem;" title="Historial"><i class="ti ti-history"></i> Movimientos</a>
            
            <a href="detalle-promotor.php?id=${d.id}" class="badge bg-primary bg-opacity-10 text-primary border-0 text-decoration-none" style="font-size: 0.75rem;" title="Comisiones"><i class="ti ti-cash"></i> Comisiones</a>
            
            <button class="badge bg-warning bg-opacity-10 text-warning border-0 editar" data-id="${d.id}"><i class="ti ti-pencil"></i></button>
            <button class="badge bg-danger bg-opacity-10 text-danger border-0 eliminar" data-id="${d.id}"><i class="ti ti-trash"></i></button>
        </div>`;
    }
}

                ],

                scrollX: true,

                order: [[0, 'desc']]

            });



            // Variable para saber si estamos editando

            let estaEditando = false;



            // Limpiar formulario solo cuando se abre para nuevo

            $('#modalPromotor').on('show.bs.modal', function() {

                if (!estaEditando) {

                    // Limpiar el formulario para nuevo registro

                    $('#formPromotor')[0].reset();

                    // Limpiar el ID

                    $('#id').val('');

                    // Cambiar título

                    $('#modal-title').text('Nuevo Promotor');

                    // Asegurar checkbox activo por defecto

                    $('#activo').prop('checked', true);

                }

                // Si está editando, no limpiar nada

            });



            $('#formPromotor').on('submit', e => {

                e.preventDefault();

                const formData = new FormData($('#formPromotor')[0]);



                // Siempre enviar el valor del checkbox

                formData.set('activo', $('#activo').is(':checked') ? 1 : 0);



                $.ajax({

                    url: 'api/promotores.php',

                    method: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    success: () => {

                        $('#modalPromotor').modal('hide');

                        tabla.ajax.reload();

                        showToast('Promotor guardado correctamente', 'success');

                    },

                    error: xhr => {

                        let msg = 'Error al guardar';

                        try {

                            msg = JSON.parse(xhr.responseText).error || msg;

                        } catch (e) {}

                        showToast(msg, 'error');

                    }

                });

            });



            // Indicar que estamos editando antes de cargar datos

            $('#tablaPromotores').on('click', '.editar', function() {

                const id = $(this).data('id');

                estaEditando = true; // Marcar que estamos editando

                $.get('api/promotores.php?id=' + id, d => {

                    $('#id').val(d.id);

                    $('input[name="nombre"]').val(d.nombre);

                    $('input[name="telefono"]').val(d.telefono || '');

                    $('input[name="email"]').val(d.email || '');

                    $('#activo').prop('checked', d.activo == 1);

                    $('#modal-title').text('Editar Promotor');

                    $('#modalPromotor').modal('show');

                    estaEditando = false; // Resetear bandera

                });

            });



            $('#tablaPromotores').on('click', '.eliminar', function() {

                if (confirm('¿Eliminar promotor?')) {

                    $.ajax({

                        url: 'api/promotores.php',

                        method: 'POST',

                        contentType: 'application/json',

                        data: JSON.stringify({

                            id: $(this).data('id'),

                            _method: 'DELETE'

                        }),

                        success: () => {

                            tabla.ajax.reload();

                            showToast('Promotor eliminado', 'success');

                        },

                        error: xhr => {

                            let msg = 'Error al eliminar';

                            try {

                                const res = JSON.parse(xhr.responseText);

                                msg = res.error || msg;

                            } catch (e) {}

                            showToast(msg, 'error');

                        }

                    });

                }

            });

            let tablaMovimientos; // Variable global para la tabla secundaria

$('#tablaPromotores').on('click', '.detalles', function() {
    const idPromotor = $(this).data('id');

    // Mostramos el modal
    $('#modalDetalles').modal('show');

    // Si la tabla de movimientos ya estaba abierta antes, la destruimos para cargar los datos del nuevo promotor
    if ($.fn.DataTable.isDataTable('#tablaMovimientos')) {
        $('#tablaMovimientos').DataTable().destroy();
    }

    // Inicializamos el DataTable de movimientos
    tablaMovimientos = $('#tablaMovimientos').DataTable({
        ajax: {
            url: `api/obtener_movimientos.php?tipo=promotor&id=${idPromotor}`,
            dataSrc: '' // Como nuestra API devuelve un array directo, lo dejamos vacío
        },
        columns: [
            {
                data: 'fecha',
                render: d => d ? new Date(d).toLocaleDateString('es-MX') : ''
            },
            { 
                data: 'servicio', 
                render: d => d ? `<span class="fw-semibold">${d}</span>` : '<span class="text-muted">N/A</span>' 
            },
            {
                data: 'tipo',
                render: function(d) {
                    if(!d) return '';
                    let tipoLower = d.toLowerCase();
                    if(tipoLower === 'entrada') return '<span class="badge bg-success bg-opacity-10 text-success border-0">Entrada</span>';
                    if(tipoLower === 'salida') return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Salida</span>';
                    if(tipoLower === 'comision') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Comisión</span>';
                    return `<span class="badge bg-secondary bg-opacity-10 text-secondary border-0 capitalize">${d}</span>`;
                }
            },
            { data: 'moneda' },
            {
                data: 'monto',
                render: d => d ? `<span class="fw-bold">$${parseFloat(d).toFixed(2)}</span>` : '$0.00'
            },
            { data: 'descripcion' },
            {
                data: 'estado',
                render: function(d) {
                    if(!d) return '';
                    let estadoLower = d.toLowerCase();
                    if(estadoLower === 'completado' || estadoLower === 'aprobado') return '<span class="text-success fw-bold"><i class="ti ti-check"></i> '+d+'</span>';
                    if(estadoLower === 'pendiente') return '<span class="text-warning fw-bold"><i class="ti ti-clock"></i> '+d+'</span>';
                    if(estadoLower === 'cancelado') return '<span class="text-danger fw-bold"><i class="ti ti-x"></i> '+d+'</span>';
                    return d;
                }
            }
        ],
        scrollX: true,
        order: [[0, 'desc']], // Ordenar por fecha más reciente
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' // Español
        }
    });
});

        </script>

</body>



</html>
