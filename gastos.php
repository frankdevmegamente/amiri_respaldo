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

    <title>Gastos - Amiri</title>

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
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-file-dollar"></i> Gastos</h3>
                                <div class="ms-auto d-flex gap-2">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGasto">+ Nuevo Gasto</button>
                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalAgregarTipo">+ Registrar Tipo de Gasto</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAgregarTipo" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo Tipo de Gasto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="formAgregarTipo">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" class="form-control" name="nombre" required>
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

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-4">
                                    <table id="tablaGastos" class="table table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Cuenta</th>
                                                <th>Tipo de Gasto</th>
                                                <th>Monto</th>
                                                <th>Concepto</th>
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

        <div class="modal fade" id="modalGasto" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">Nuevo Gasto</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <form id="formGasto">

                        <div class="modal-body">

                            <input type="hidden" id="id" name="id">

                            <div class="mb-3">

                                <label class="form-label">Fecha</label>

                                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Cuenta</label>

                                <select class="form-select" name="cuenta_id" required>

                                    <option value="">-- Selecciona una cuenta --</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Tipo de Gasto *</label>

                                <select class="form-select" name="categoria_id" required>

                                    <option value="">-- Selecciona un tipo --</option>

                                    <!-- Se llenará con AJAX -->

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">

                                    <i class="ti ti-coin"></i> Monto 

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">$</span>

                                    <input type="number" step="0.01" class="form-control" name="monto" id="inputMonto" required min="0.01" placeholder="0.00">

                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Concepto</label>

                                <textarea class="form-control" name="concepto" rows="2"></textarea>

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

            // Cargar tipos de gasto en el select

            function cargarTiposGasto() {

                $.get('api/tipos-gasto.php', function(tipos) {

                    const select = $('select[name="categoria_id"]');

                    select.empty().append('<option value="">-- Selecciona un tipo --</option>');

                    tipos.forEach(tipo => {

                        // ✅ Ya no se verifica si está activo

                        select.append(`<option value="${tipo.id}">${tipo.nombre}</option>`);

                    });

                });

            }



            const tabla = $('#tablaGastos').DataTable({

                ajax: {

                    url: 'api/gastos.php',

                    dataSrc: ''

                },

                columns: [{

                        data: 'fecha'

                    },

                    {

                        data: 'cuenta_nombre'

                    },

                    {

                        data: 'tipo_gasto_nombre'

                    },

                    {

                        data: 'monto',

                        render: d => '$' + parseFloat(d).toLocaleString('en-US', {

                            minimumFractionDigits: 2,

                            maximumFractionDigits: 2

                        })

                    },

                    {

                        data: 'concepto'

                    },

                ],

                order: [

                    [0, 'desc']

                ]

            });



            // Cargar cuentas al abrir modal (solo bancarias y caja de efectivo con saldo disponible)

            $('#modalGasto').on('show.bs.modal', function() {

                $.get('api/cuentas.php?solo_gastos=1&con_saldo=1', function(cuentas) {

                    const sel = $('select[name="cuenta_id"]');

                    sel.empty().append('<option value="">-- Selecciona una cuenta --</option>');

                    cuentas.forEach(c => {

                        const saldo = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(c.saldo_actual);

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        const label = c.numero_cuenta ? `${nombreCuenta} - Saldo: ${saldo}` : `${nombreCuenta} - Saldo: ${saldo}`;

                        sel.append(`<option value="${c.id}" data-saldo="${c.saldo_actual}">${label}</option>`);

                    });

                    // Seleccionar cuenta con "efectivo" en el nombre por defecto

                    const efectivo = cuentas.find(c => c.nombre.toLowerCase().includes('efectivo'));

                    if (efectivo) sel.val(efectivo.id);

                });

            });



            // Reset al abrir para nuevo

            $('button[data-bs-target="#modalGasto"]').on('click', function() {

                $('#formGasto')[0].reset();

                $('#id').val('');

                $('#montoFormateado').text(''); // Limpiar formato

                cargarTiposGasto(); // Cargar tipos al abrir

            });



            // Formatear monto en tiempo real como guía visual

            $('#inputMonto').on('input', function() {

                const valor = parseFloat($(this).val());

                const spanFormato = $('#montoFormateado');

                

                if (isNaN(valor) || valor === 0) {

                    spanFormato.text('');

                } else {

                    const formato = new Intl.NumberFormat('es-MX', {

                        style: 'currency',

                        currency: 'MXN',

                        minimumFractionDigits: 2,

                        maximumFractionDigits: 2

                    }).format(valor);

                    spanFormato.text('= ' + formato);

                }

            });



            // Guardar

            $('#formGasto').on('submit', e => {

                e.preventDefault();



                const cuentaId = $('select[name="cuenta_id"]').val();

                const saldo = parseFloat($('select[name="cuenta_id"] option:selected').data('saldo'));

                const monto = parseFloat($('input[name="monto"]').val());



                if (monto > saldo) {

                    showToast(`El monto excede el saldo disponible (${formatoMoneda(saldo)})`, 'error');

                    return;

                }



                const fd = new FormData($('#formGasto')[0]);

                $.ajax({

                    url: 'api/gastos.php',

                    method: 'POST',

                    data: fd,

                    processData: false,

                    contentType: false,

                    success: () => {

                        $('#modalGasto').modal('hide');

                        tabla.ajax.reload();

                        showToast('Gasto guardado', 'success');

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



            // Guardar nuevo tipo de gasto

            $('#formAgregarTipo').on('submit', e => {

                e.preventDefault();

                const formElement = $('#formAgregarTipo')[0];

                const fd = new FormData(formElement);



                // Depuración: Ver qué valor se envía

                console.log('Valor enviado para "nombre":', fd.get('nombre'));



                $.ajax({

                    url: 'api/tipos-gasto.php',

                    method: 'POST',

                    data: fd,

                    processData: false,

                    contentType: false,

                    success: () => {

                        $('#modalAgregarTipo').modal('hide');

                        showToast('Tipo de gasto agregado', 'success');

                        cargarTiposGasto();

                    },

                    error: xhr => {

                        let msg = 'Error desconocido';

                        try {

                            const res = JSON.parse(xhr.responseText);

                            msg = res.error || msg;

                        } catch (e) {

                            msg = xhr.responseText || `Error ${xhr.status}: ${xhr.statusText}`;

                        }



                        // Mostrar error dentro del modal

                        const modalBody = $('#modalAgregarTipo .modal-body');

                        modalBody.prepend(`

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    ${msg}

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            `);

                    }

                });

            });

        </script>

</body>



</html>