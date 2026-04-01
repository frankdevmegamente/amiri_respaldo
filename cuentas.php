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

    <title>Cuentas - Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>
        #cuentasTab .nav-link.active {
            color: #5D87FF !important;
            background-color: transparent !important;
            border-bottom: 2px solid #5D87FF !important;
            border-top: none;
            border-left: none;
            border-right: none;
            font-weight: 600;
        }
        #cuentasTab .nav-link:not(.active) {
            border: none;
            color: #64748b;
        }
        #cuentasTab .nav-link:not(.active):hover {
            color: #5D87FF;
        }
         body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
    </style>

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 95%;">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3 mt-2">
                                <h3 class="mb-0"><i class="ti ti-wallet"></i> Cuentas</h3>
                                <div class="ms-auto">
                                    <div class="dropdown">
                                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                            + Nueva Cuenta
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarFormulario(1)">Cuenta Bancaria</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarFormulario(2)">Caja</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" id="cuentasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bancaria-tab" data-bs-toggle="tab" data-bs-target="#bancaria" type="button" role="tab">Cuentas Bancarias</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="caja-tab" data-bs-toggle="tab" data-bs-target="#caja" type="button" role="tab">Cajas</button>
                        </li>

                    </ul>

                    <div class="tab-content" id="cuentasTabContent">

                        <div class="tab-pane fade show active" id="bancaria" role="tabpanel">
                            <div class="card">
                                <div class="card-body p-4">
                                    <table id="tablaCuentasBancarias" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre / Razón Social</th>
                                                <th>Número / CLABE</th>
                                                <th>Banco</th>
                                                <th>Tipo de moneda</th>
                                                <th>Saldo</th>
                                                <th>Documento</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="caja" role="tabpanel">
                            <div class="card">
                                <div class="card-body p-4">
                                    <table id="tablaCajas" class="table table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre de la Caja</th>
                                                <th>Tipo de moneda</th>
                                                <th>Saldo Actual</th>
                                                <th>Estado</th>
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

        <div class="modal fade" id="modalCuenta" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title" id="modalTitulo">Nueva Cuenta</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>

                    <form id="formCuenta">

                        <div class="modal-body">

                            <input type="hidden" id="cuenta_id" name="id">

                            <input type="hidden" id="tipo_cuenta" name="tipo_cuenta" value="1">

                            <div class="mb-3">

                                <label class="form-label">Nombre / Razón Social *</label>

                                <input type="text" class="form-control" name="nombre" id="nombre" required>

                            </div>

                            <div class="mb-3" id="campoNumero">

                                <label class="form-label">Número de cuenta o CLABE *</label>

                                <input type="text" class="form-control no-capitalize" name="numero_cuenta" id="numero_cuenta" required>

                            </div>

                            <div class="mb-3" id="campoBanco">

                                <label class="form-label">Banco *</label>

                                <select class="form-select" name="banco" id="banco" required>

                                    <option value="" selected disabled>Selecciona un banco</option>

                                    <option value="BBVA">BBVA</option>

                                    <option value="BANORTE">BANORTE</option>

                                    <option value="FONDEADORA">FONDEADORA</option>

                                    <option value="ALBO">ALBO</option>

                                    <option value="SCOTIABANK">SCOTIABANK</option>

                                    <option value="SANTANDER">SANTANDER</option>

                                    <option value="BANREGIO">BANREGIO</option>

                                    <option value="BANBAJIO">BANBAJIO</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Tipo de Moneda *</label>

                                <select class="form-select" name="tipo_moneda" id="tipo_moneda" required>

                                    <option value="MXN">MXN</option>

                                    <option value="USD">USD</option>

                                    <option value="USDT">USDT</option>

                                </select>

                            </div>

                            <div class="mb-3" id="campoDocumento">

                                <label class="form-label">Documento (PDF/Imagen)</label>

                                <input type="file" class="form-control" name="documento" id="documento" accept=".pdf,.jpg,.jpeg,.png,.gif">

                                <small class="text-muted">Formatos: PDF, JPG, PNG, GIF (máx 5MB)</small>

                                <div id="documentoActual" class="mt-2"></div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Estado</label>

                                <select class="form-select" name="activo" id="activo">

                                    <option value="1">Activa</option>

                                    <option value="0">Inactiva</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <div class="form-check">

                                    <input type="checkbox" class="form-check-input" name="es_caja_empresa" id="es_caja_empresa" value="1">

                                    <label class="form-check-label" for="es_caja_empresa">

                                        Marcar como Caja de la Empresa

                                    </label>

                                </div>

                                <small class="text-muted">Si está marcada, esta cuenta se mostrará como opción para pagar a clientes</small>

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

        <script src="src/assets/js/utils.js"></script>



        <script>

            let tablaBancarias, tablaCajas;



            $(document).ready(function() {



                tablaBancarias = $('#tablaCuentasBancarias').DataTable({

                    ajax: {

                        url: 'api/cuentas.php?tipo_cuenta=1',

                        dataSrc: ''

                    },

                    columns: columnasBancarias(),

                    order: [[0, 'desc']],

                    language: {

                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"

                    }

                });



                tablaCajas = $('#tablaCajas').DataTable({

                    ajax: {

                        url: 'api/cuentas.php?tipo_cuenta=2',

                        dataSrc: ''

                    },

                    columns: columnasCajas(),

                    order: [[0, 'desc']],

                    language: {

                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"

                    }

                });



                $('#formCuenta').submit(function(e) {

                    e.preventDefault();



                    const formData = new FormData(this);



                    $.ajax({

                        url: 'api/cuentas.php',

                        method: 'POST',

                        data: formData,

                        processData: false,

                        contentType: false,

                        success: function() {

                            $('#modalCuenta').modal('hide');

                            tablaBancarias.ajax.reload();

                            tablaCajas.ajax.reload();

                            showToast('Cuenta guardada', 'success');

                        },

                        error: function(xhr) {

                            showToast(xhr.responseJSON?.error || 'Error', 'error');

                        }

                    });

                });

            });



            function columnasBancarias() {

                return [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'numero_cuenta',

                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : ''

                    },

                    {

                        data: 'banco'

                    },

                    {

                        data: 'tipo_moneda'

                    },

                    {

                        data: 'saldo_actual',

                        render: d => new Intl.NumberFormat('es-MX', {

                            style: 'currency',

                            currency: 'MXN'

                        }).format(d)

                    },

                    {

                        data: 'documento_path',

                        render: d => d ? `<a href="${d}" target="_blank" class="btn btn-sm btn-info"><i class="ti ti-download"></i></a>` : '<span class="text-muted">-</span>'

                    },

                    {

                        data: 'activo',

                        render: d => d == 1 ? badgeOK() : badgeNO()

                    },

                    {

                        data: null,

                        render: d => botones(d.id, 1)

                    }

                ];

            }



            function columnasCajas() {

                return [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'tipo_moneda'

                    },

                    {

                        data: 'saldo_actual',

                        render: d => new Intl.NumberFormat('es-MX', {

                            style: 'currency',

                            currency: 'MXN'

                        }).format(d)

                    },

                    {

                        data: 'activo',

                        render: d => d == 1 ? badgeOK() : badgeNO()

                    },

                    {

                        data: null,

                        render: d => botones(d.id, 2)

                    }

                ];

            }



            function editarCuenta(id, tipo) {

                $.get('api/cuentas.php?id=' + id, function(d) {

                    $('#cuenta_id').val(d.id);

                    $('#nombre').val(d.nombre);

                    $('#tipo_moneda').val(d.tipo_moneda);

                    $('#activo').val(d.activo);

                    $('#tipo_cuenta').val(tipo);

                    $('#es_caja_empresa').prop('checked', d.es_caja_empresa == 1);



                    if (tipo == 1) {

                        $('#campoNumero, #campoBanco, #campoDocumento').show();

                        $('#numero_cuenta').val(d.numero_cuenta);

                        $('#banco').val(d.banco);



                        // Mostrar documento actual si existe

                        if (d.documento_path) {

                            $('#documentoActual').html(`<a href="${d.documento_path}" target="_blank" class="btn btn-sm btn-info">Ver documento actual</a>`);

                        } else {

                            $('#documentoActual').html('');

                        }

                    } else {

                        $('#campoNumero, #campoBanco, #campoDocumento').hide();

                    }



                    $('#modalCuenta').modal('show');

                });

            }



            function eliminarCuenta(id) {

                if (!confirm('¿Eliminar cuenta?')) return;



                $.ajax({

                    url: 'api/cuentas.php',

                    method: 'DELETE',

                    data: {

                        id

                    },

                    success: () => {

                        tablaBancarias.ajax.reload();

                        tablaCajas.ajax.reload();

                        showToast('Cuenta eliminada', 'success');

                    }

                });

            }



            // Etiquetas de estado con estilo suave
            const badgeOK = () => '<span class="badge bg-success bg-opacity-10 text-success border-0">Activa</span>';
            const badgeNO = () => '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Inactiva</span>';
            
            // Botones de acción con estilo Soft Badge
            const botones = (id, tipo) => `
<div class="d-flex align-items-center">
    <button class="badge bg-warning bg-opacity-10 text-warning border-0 me-2" style="cursor: pointer; font-size: 0.75rem;" onclick="editarCuenta(${id},${tipo})" title="Editar">
        <i class="ti ti-pencil"></i> Editar
    </button>
    <button class="badge bg-danger bg-opacity-10 text-danger border-0" style="cursor: pointer; font-size: 0.75rem;" onclick="eliminarCuenta(${id})" title="Eliminar">
        <i class="ti ti-trash"></i> Eliminar
    </button>
</div>
`;



            function mostrarFormulario(tipo) {

                // Resetear formulario

                $('#formCuenta')[0].reset();



                // Limpiar ID (nuevo registro)

                $('#cuenta_id').val('');

                $('#documentoActual').html('');

                $('#es_caja_empresa').prop('checked', false);



                // Setear tipo de cuenta

                $('#tipo_cuenta').val(tipo);



                if (tipo == 1) {

                    // Cuenta bancaria

                    $('label[for="nombre"]').text('Nombre / Razón Social *');

                    $('#modalTitulo').text('Nueva Cuenta Bancaria');

                    $('#campoNumero, #campoBanco, #campoDocumento').show();

                    $('#numero_cuenta').prop('required', true);

                    $('#banco').prop('required', true);

                } else {

                    // Caja

                    $('label[for="nombre"]').text('Nombre de la Caja *');

                    $('#modalTitulo').text('Nueva Caja');

                    $('#campoNumero, #campoBanco, #campoDocumento').hide();

                    $('#numero_cuenta').prop('required', false);

                    $('#banco').prop('required', false);

                }



                // Mostrar modal

                $('#modalCuenta').modal('show');

            }

        </script>



</body>



</html>
