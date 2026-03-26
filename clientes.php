<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

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

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">

                    <div class="d-flex align-items-center mb-3 mt-2">
                        <h3 class="mb-0"><i class="ti ti-users"></i> Clientes</h3>
                        <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCliente">
                            + Nuevo Cliente
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body p-4">
                            <table id="tablaClientes" class="table table-striped table-bordered w-100">
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

                                    <input type="number" name="retencion_total" id="retencionTotal" class="form-control" readonly>

                                </div>

                                <div class="col-4">

                                    <label>% Amiri</label>

                                    <input type="number" step="0.01" id="porcentajeAmiri" name="porcentaje_amiri" class="form-control">

                                </div>

                                <div class="col-4" id="campoPromotorPorcentaje">

                                    <label>% Promotor</label>

                                    <input type="number" step="0.01" id="porcentajePromotor" name="porcentaje_promotor" class="form-control">

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

                    render: (d, type, row) => `<a href="detalle-cliente.php?id=${row.id}" class="text-decoration-none fw-bold">${d}</a>`

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

                    render: d => {

                        let botones = '';

                        botones += `<button class="btn btn-warning btn-sm editar" data-id="${d.id}"><i class="ti ti-pencil"></i></button>`;

                        botones += `<button class="btn btn-danger btn-sm eliminar" data-id="${d.id}"><i class="ti ti-x"></i></button>`;

                        return botones;

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
            order: [[0, 'desc']]

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

            

            $.post('api/clientes.php', $('#formCliente').serialize(), () => {

                $('#modalCliente').modal('hide');

                $('#formCliente')[0].reset();

                $('#id').val('');

                tabla.ajax.reload();

                showToast('Cliente guardado', 'success');

            }, 'json');

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

                    showToast('Cliente eliminado', 'success');

                },

                error: function(xhr) {

                    let msg = 'Error al eliminar';

                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}

                    showToast(msg, 'error');

                }

            });

        });

    </script>

</body>



</html>