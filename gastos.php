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



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 95%;">
                    
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
                                    <table id="tablaGastos" class="table table-hover align-middle w-100">
                                        <thead>
    <tr>
        <th>Fecha</th>
        <th>Cuenta</th>
        <th>Clasificación</th> <th>Tipo de Gasto</th>
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
    <label class="form-label">Clasificación (Fijo/Variable) *</label>
    <select class="form-select" name="clasificacion" required>
        <option value="">-- Selecciona una clasificación --</option>
        <option value="fijo">Gasto Fijo</option>
        <option value="variable">Gasto Variable</option>
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
                columns: [
                    {
                        data: 'fecha',
                        title: 'Fecha',
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            
                            // 1. Extraemos la fecha exacta ignorando zonas horarias
                            const soloFecha = data.split(' ')[0]; 
                            const partes = soloFecha.split('-'); 
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            // 2. La fecha de captura exacta
                            let infoExtra = '';
                            if (row.creado_en) {
                                let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                                infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            }
                            
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },
                    { data: 'cuenta_nombre' },
                    { data: 'tipo_gasto_nombre' },
                    { 
                        data: 'clasificacion',
                        render: function(data) {
                            if (data === 'fijo') return '<span class="badge bg-primary bg-opacity-10 text-primary border-0">Fijo</span>';
                            if (data === 'variable') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Variable</span>';
                            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">Sin definir</span>';
                        }
                    },
                    { 
                        data: 'monto', 
                        render: function(data) { 
                            const monto = parseFloat(data) || 0;
                            return '<strong class="">$' + monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong>'; 
                        } 
                    },
                    { data: 'concepto' }
                ],
                order: [
                    [0, 'desc']
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
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
