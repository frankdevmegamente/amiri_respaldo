<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

?>

<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Clientes - Amiri</title>



    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
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
     [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
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

/* ─── Page Header ─────────────────────────────── */
.clientes-header { position: relative; overflow: hidden; border-radius: 16px !important; }
.clientes-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
.clientes-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
.clientes-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
.clientes-header .sub-badge { font-size: 1rem; color: #8c9ea8; }

/* ─── Metric Cards ────────────────────────────── */
.metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
.metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
.metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; }
.metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
.metric-footer { font-size: 0.8rem; color: #8c9ea8; }

[data-bs-theme="dark"] .clientes-header .sub-badge { color: #8c9ea8 !important; }
[data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(93,135,255,0.12) !important; }
[data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(19,222,185,0.12) !important; }
[data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255,174,31,0.12) !important; }
[data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(83,155,255,0.12) !important; }
[data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
</style>


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
                    <div class="card clientes-header shadow-sm border-0 rounded-4 mb-4">
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
                                            <h2 class="mb-0">Clientes</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Gestión de clientes y retenciones</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente">
                                        <i class="ti ti-plus"></i> Nuevo Cliente
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
                                        <span class="metric-label">Total Clientes</span>
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
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Activos</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-user-check"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiActivos">0</div>
                                    <div class="metric-footer">En operación</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Operan MXN</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-currency-peso"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiMxn">0</div>
                                    <div class="metric-footer">Pesos mexicanos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Operan Divisas</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-currency-dollar"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiDivisas">0</div>
                                    <div class="metric-footer">USD / USDT</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaClientes" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Promotor</th>
                                        <th>Retención %</th>
                                        <th>% Amiri</th>
                                        <th>% Promotor</th>
                                        <th>Contacto</th>
                                        <th>Cuenta Caja</th>
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



    <!-- MODAL -->

    <div class="modal fade" id="modalCliente" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">



                <div class="modal-header">

                    <h5 class="modal-title"><i class="ti ti-user-plus"></i> Cliente</h5>

                    <button class="btn-close" data-bs-dismiss="modal"></button>

                </div>



                <form id="formCliente">

                    <div class="modal-body">



                        <input type="hidden" name="id" id="id">



                        <div class="mb-3">

                            <label class="form-label">Nombre</label>

                            <input type="text" name="nombre" class="form-control" required>

                        </div>



                        <div class="mb-3">

                            <label class="form-label">Contacto</label>

                            <input type="text" name="contacto" class="form-control">

                        </div>



                        <!-- Tipo de Moneda - Checkboxes -->

                        <div class="mb-3">

                            <label class="form-label">Monedas que opera <span class="text-danger">*</span></label>

                            <div class="d-flex gap-4">

                                <div class="form-check">

                                    <input class="form-check-input moneda-check" type="checkbox" name="opera_mxn" id="operaMxn" value="1" checked>

                                    <label class="form-check-label" for="operaMxn">

                                        💵 MXN <small class="text-muted">(Venta de efectivo)</small>

                                    </label>

                                </div>

                                <div class="form-check">

                                    <input class="form-check-input moneda-check" type="checkbox" name="opera_usd" id="operaUsd" value="1">

                                    <label class="form-check-label" for="operaUsd">

                                        💰 USD <small class="text-muted">(Dólares)</small>

                                    </label>

                                </div>

                                <div class="form-check">

                                    <input class="form-check-input moneda-check" type="checkbox" name="opera_usdt" id="operaUsdt" value="1">

                                    <label class="form-check-label" for="operaUsdt">

                                        🪙 USDT

                                    </label>

                                </div>

                            </div>

                            <small class="text-muted">Selecciona al menos una moneda</small>

                        </div>



                        <!-- Campos solo para clientes MXN -->

                        <div id="camposMxn">

                            <div class="mb-3">

                                <label class="form-label">Promotor <span class="text-danger">*</span></label>

                                <select name="promotor_id" class="form-control"></select>

                            </div>



                            <div class="row">

                                <div class="col-4">

                                    <label>Retención Total</label>

                                    <input type="text" name="retencion_total" id="retencionTotal" class="form-control" readonly>

                                </div>

                                <div class="col-4">

                                    <label>% Amiri</label>

                                    <input type="text" step="0.01" id="porcentajeAmiri" name="porcentaje_amiri" class="form-control">

                                </div>

                                <div class="col-4" id="campoPromotorPorcentaje">

                                    <label>% Promotor</label>

                                    <input type="text" step="0.01" id="porcentajePromotor" name="porcentaje_promotor" class="form-control">

                                </div>

                            </div>

                        </div>



                        <div id="mensaje-validacion" class="alert mt-3 d-none"></div>



                    </div>



                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button class="btn btn-primary" type="submit">Guardar</button>

                    </div>



                </form>

            </div>

        </div>

    </div>



    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="src/assets/js/dark-mode.js"></script>

    <script src="src/assets/js/utils.js"></script>



    <script>

        const avatarColors = ['#5D87FF', '#13DEB9', '#FA896B', '#FFAE1F', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
        function getAvatarColor(name) {
            if (!name) return '#8c9ea8';
            let hash = 0;
            for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
            return avatarColors[Math.abs(hash) % avatarColors.length];
        }

        const tabla = $('#tablaClientes').DataTable({

            ajax: {

                url: 'api/clientes.php',

                dataSrc: ''

            },

            columns: [{

                    data: 'id'

                },

                {

                    data: 'nombre',

                    render: function(d, type, row) {
                        var n = d;
                        var color = getAvatarColor(n);
                        var initial = n ? n.charAt(0).toUpperCase() : '?';
                        return '<div class="d-flex align-items-center gap-2">' +
                               '<span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:' + color + '22;color:' + color + ';font-weight:700;font-size:0.85rem;">' + initial + '</span>' +
                               '<a href="detalle-cliente.php?id=' + row.id + '" class="text-decoration-none fw-semibold">' + n + '</a>' +
                               '</div>';
                    }

                },

                {

                    data: 'promotor'

                },

                {

                    data: 'retencion_total'

                },

                {

                    data: 'porcentaje_amiri'

                },

                {

                    data: 'porcentaje_promotor'

                },

                {

                    data: 'contacto',

                    render: (d) => d ? `<span class="no-capitalize">${d}</span>` : ''
                    

                },

                {

                    data: 'cuenta_nombre',

                    render: d => d ? d : '<span class="text-muted">Sin caja</span>'

                },

                {
                    data: null,
                    render: function(d) {
                        return `<div class="d-flex align-items-center gap-1">
                            <a href="historial-cliente.php?id=${d.id}" class="btn btn-sm btn-outline-secondary" title="Historial"><i class="ti ti-history"></i></a>
                            <button class="btn btn-sm btn-outline-warning editar" data-id="${d.id}" title="Editar"><i class="ti ti-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger eliminar" data-id="${d.id}" title="Eliminar"><i class="ti ti-trash"></i></button>
                        </div>`;
                    }
                }

            ],

            columnDefs: [
                { width: '5%', targets: 0 },  // ID
                { width: '5%', targets: 1 }, // Nombre
                { width: '15%', targets: 2 }, // Promotor
                { width: '8%', targets: 3 },  // Retención %
                { width: '8%', targets: 4 },  // % Amiri
                { width: '8%', targets: 5 },  // % Promotor
                { width: '15%', targets: 6 }, // Contacto
                { width: '15%', targets: 7 }, // Cuenta Caja
                { width: '6%', targets: 8 }   // Acciones
            ],
            autoWidth: false,
            scrollX: true,
            order: [[0, 'desc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }

        });



        // Limpiar formulario cuando se abre el modal

        $('#modalCliente').on('show.bs.modal', function(e) {

            // Cargar promotores primero (siempre necesario)

            $.get('api/promotores.php?activos=1', data => {

                const select = $('select[name="promotor_id"]');

                select.html('<option value="">Seleccione</option>');

                data.forEach(p => select.append(`<option value="${p.id}" data-nombre="${p.nombre}">${p.nombre}</option>`));

            });

            

            // Si no viene de editar, limpiar el formulario

            const button = $(e.relatedTarget);

            if (!button.hasClass('editar') && !button.parent().hasClass('editar')) {

                $('#formCliente')[0].reset();

                $('#id').val('');

                $('#porcentajeAmiri').val('0');

                $('#porcentajePromotor').val('0');

                $('#retencionTotal').val('0');

                $('#mensaje-validacion').addClass('d-none');

                $('#campoPromotorPorcentaje').show();

                // Resetear checkboxes

                $('#operaMxn').prop('checked', true);

                $('#operaUsd').prop('checked', false);

                $('#operaUsdt').prop('checked', false);

                toggleCamposMxn();

            }

        });



        // Función para mostrar/ocultar campos de MXN (promotor y porcentajes)

        function toggleCamposMxn() {

            const operaMxn = $('#operaMxn').is(':checked');

            if (operaMxn) {

                $('#camposMxn').show();

                $('select[name="promotor_id"]').prop('required', true);

            } else {

                $('#camposMxn').hide();

                $('select[name="promotor_id"]').prop('required', false);

                // Limpiar valores cuando no opera MXN

                $('select[name="promotor_id"]').val('');

                $('#porcentajeAmiri').val('0');

                $('#porcentajePromotor').val('0');

                $('#retencionTotal').val('0');

            }

        }



        // Evento cuando cambian los checkboxes de moneda

        $('.moneda-check').on('change', function() {

            toggleCamposMxn();

            

            // Validar que al menos uno esté seleccionado

            const algunoSeleccionado = $('.moneda-check:checked').length > 0;

            if (!algunoSeleccionado) {

                $(this).prop('checked', true); // No permitir deseleccionar todos

                showToast('Debe seleccionar al menos una moneda', 'warning');

            }

        });



        // Detectar cuando se selecciona un promotor

        $(document).on('change', 'select[name="promotor_id"]', function() {

            const selectedOption = $(this).find('option:selected');

            const nombrePromotor = selectedOption.data('nombre');

            

            // Si es AMIRI DIRECTO, ocultar campo % Promotor y dejarlo en 0

            if (nombrePromotor === 'AMIRI DIRECTO') {

                $('#campoPromotorPorcentaje').hide();

                $('#porcentajePromotor').val('0');

                calcular(); // Recalcular totales

            } else {

                $('#campoPromotorPorcentaje').show();

            }

        });



        function calcular() {

            const a = parseFloat($('#porcentajeAmiri').val()) || 0;

            const p = parseFloat($('#porcentajePromotor').val()) || 0;

            const total = (a + p).toFixed(2);

            $('#retencionTotal').val(total);



            const msg = $('#mensaje-validacion');

            if (total > 100) {

                msg.text('La suma no puede exceder 100%').removeClass().addClass('alert alert-danger');

            } else {

                msg.text(`Total ${total}%`).removeClass().addClass('alert alert-success');

            }

        }



        $('#porcentajeAmiri, #porcentajePromotor').on('input', calcular);



        $('#formCliente').submit(e => {

            e.preventDefault();

            

            const operaMxn = $('#operaMxn').is(':checked');

            

            // Validar porcentajes solo si opera MXN

            if (operaMxn && parseFloat($('#retencionTotal').val()) > 100) {

                showToast('La suma excede 100%', 'error');

                return;

            }

            

            // Validar que tenga promotor si opera MXN

            if (operaMxn && !$('select[name="promotor_id"]').val()) {

                showToast('Debe seleccionar un promotor para clientes MXN', 'error');

                return;

            }

            

            $.ajax({
                url: 'api/clientes.php',
                method: 'POST',
                data: $('#formCliente').serialize(),
                dataType: 'json',
                success: function() {
                    $('#modalCliente').modal('hide');
                    $('#formCliente')[0].reset();
                    $('#id').val('');
                    tabla.ajax.reload();
                    cargarResumen();
                    showToast('Cliente guardado', 'success');
                },
                error: function(xhr) {
                    let resp;
                    try { resp = xhr.responseJSON; } catch (e) {}
                    
                    if (resp?.details?.cliente_eliminado_id) {
                        const nombre = $('input[name="nombre"]').val();
                        if (confirm(`El cliente "${nombre}" fue eliminado anteriormente. ¿Restaurarlo?`)) {
                            const restoreId = resp.details.cliente_eliminado_id;
                            $.ajax({
                                url: 'api/clientes.php',
                                method: 'POST',
                                data: $('#formCliente').serialize() + '&id=' + restoreId,
                                dataType: 'json',
                                success: function() {
                                    $('#modalCliente').modal('hide');
                                    $('#formCliente')[0].reset();
                                    $('#id').val('');
                                    tabla.ajax.reload();
                                    cargarResumen();
                                    showToast('Cliente restaurado correctamente', 'success');
                                },
                                error: function(xhr2) {
                                    let msg = 'Error al restaurar';
                                    try { msg = xhr2.responseJSON?.error || msg; } catch (e) {}
                                    showToast(msg, 'error');
                                }
                            });
                        }
                    } else {
                        let msg = resp?.error || 'Error al guardar cliente';
                        showToast(msg, 'error');
                    }
                }
            });

        });



        $('#tablaClientes').on('click', '.editar', function() {

            const clienteId = $(this).data('id');

            

            // Primero abrir el modal

            $('#modalCliente').modal('show');

            

            // Esperar un momento para que el modal y los promotores se carguen

            setTimeout(() => {

                $.get('api/clientes.php?id=' + clienteId, d => {

                    // Llenar todos los campos del formulario

                    $('#id').val(d.id);

                    $('input[name="nombre"]').val(d.nombre);

                    $('input[name="contacto"]').val(d.contacto);

                    

                    // Cargar checkboxes de monedas

                    $('#operaMxn').prop('checked', d.opera_mxn == 1);

                    $('#operaUsd').prop('checked', d.opera_usd == 1);

                    $('#operaUsdt').prop('checked', d.opera_usdt == 1);

                    toggleCamposMxn();

                    

                    // Solo cargar datos de promotor si opera MXN

                    if (d.opera_mxn == 1) {

                        $('select[name="promotor_id"]').val(d.promotor_id);

                        $('#porcentajeAmiri').val(d.porcentaje_amiri);

                        $('#porcentajePromotor').val(d.porcentaje_promotor);

                        $('#retencionTotal').val(d.retencion_total);

                        

                        // Verificar si es AMIRI DIRECTO

                        const selectedOption = $('select[name="promotor_id"]').find('option:selected');

                        const nombrePromotor = selectedOption.data('nombre');

                        if (nombrePromotor === 'AMIRI DIRECTO') {

                            $('#campoPromotorPorcentaje').hide();

                        } else {

                            $('#campoPromotorPorcentaje').show();

                        }

                        

                        // Calcular y mostrar validación

                        calcular();

                    }

                }, 'json');

            }, 300);

        });



        $('#tablaClientes').on('click', '.eliminar', function() {

            if (!confirm('¿Eliminar cliente?')) return;

            const id = $(this).data('id');

            $.ajax({

                url: 'api/clientes.php?id=' + id,

                method: 'DELETE',

                success: () => {

                    tabla.ajax.reload();
                    cargarResumen();

                    showToast('Cliente eliminado', 'success');

                },

                error: function(xhr) {

                    let msg = 'Error al eliminar';

                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}

                    showToast(msg, 'error');

                }

            });

        });

        // ─── Resumen (KPI Cards) ─────────────────────
        function cargarResumen() {
            $.get('api/clientes.php?resumen=1', function(data) {
                $('#kpiTotal').text(data.total);
                $('#kpiActivos').text(data.activos);
                $('#kpiMxn').text(data.opera_mxn);
                $('#kpiDivisas').text(data.opera_divisas);
            }).fail(function() {
                $('#kpiTotal').text('Error');
            });
        }

        $(document).ready(function() {
            cargarResumen();
        });
    </script>

</body>



</html>
