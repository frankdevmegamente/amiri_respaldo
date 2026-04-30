<?php
require_once __DIR__ . '/src/config/session.php';
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') { header('Location: login.php'); exit; }
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
$cuentas = $pdo->query("SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Préstamos Otorgados | Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        .text-pending { color: #ffae1f; font-weight: 700; }
        .text-collected { color: #13deb9; font-weight: 700; }
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }

        body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }

        body[data-bs-theme="dark"] .card,
        body[data-bs-theme="dark"] .page-breadcrumb,
        body[data-bs-theme="dark"] .page-breadcrumb h3,
        body[data-bs-theme="dark"] .card-header,
        body[data-bs-theme="dark"] .card-body,
        body[data-bs-theme="dark"] .modal-content,
        body[data-bs-theme="dark"] .modal-header,
        body[data-bs-theme="dark"] .modal-footer,
        body[data-bs-theme="dark"] .table-light {
            background-color: #1f2535 !important;
            border-color: #2b3350 !important;
            color: #e6ecff !important;
        }

        body[data-bs-theme="dark"] .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        body[data-bs-theme="dark"] .table,
        body[data-bs-theme="dark"] .table thead th,
        body[data-bs-theme="dark"] .table tbody td,
        body[data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #1f2535 !important;
            color: #e6ecff !important;
            border-color: #2b3350 !important;
        }

        body[data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) {
            background-color: #232b3c !important;
        }

        body[data-bs-theme="dark"] .form-control,
        body[data-bs-theme="dark"] .form-select,
        body[data-bs-theme="dark"] .input-group-text,
        body[data-bs-theme="dark"] .form-control-plaintext,
        body[data-bs-theme="dark"] .alert,
        body[data-bs-theme="dark"] .badge,
        body[data-bs-theme="dark"] .nav-tabs .nav-link {
            background-color: #232b3c !important;
            color: #e6ecff !important;
            border-color: #2b3350 !important;
        }

        body[data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #ffffff !important;
            border-bottom-color: #5d87ff !important;
        }

        body[data-bs-theme="dark"] .bg-light {
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        body[data-bs-theme="dark"] .bg-primary.bg-opacity-10 {
            background-color: rgba(93, 135, 255, 0.12) !important;
        }

        body[data-bs-theme="dark"] .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.12) !important;
        }

        body[data-bs-theme="dark"] .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.12) !important;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
                    
                    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4 mt-2">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Préstamos Otorgados</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a class="text-muted" href="index.php">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Dinero por Cobrar</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3 text-end">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPrestamo">
                                        <i class="ti ti-plus"></i> Nuevo Préstamo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body p-4 table-responsive">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Lista de Deudores</h5>
                                <button id="btnCobroMasivo" class="btn btn-success btn-sm" style="display: none;">
                                    <i class="ti ti-cash"></i> Realizar Cobro (<span id="countCheck">0</span>)
                                </button>
                            </div>
                            <table id="tablaOtorgados" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30px;"><input type="checkbox" id="checkAll"></th>
                                        <th>Fecha</th>
                                        <th>Beneficiario</th>
                                        <th>Monto Prestado</th>
                                        <th>Cobrado</th>
                                        <th>Pendiente</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
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

    <div class="modal fade" id="modalNuevoPrestamo" tabindex="-1">
        <div class="modal-dialog">
            <form id="formNuevoPrestamo" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Persona/Socio</label>
                        <input type="text" name="persona_nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monto a Prestar</label>
                            <input type="number" step="0.01" name="monto" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Caja de Origen</label>
                            <select name="cuenta_id" class="form-select" required>
                                <option value="">Seleccionar cuenta...</option>
                                <?php foreach($cuentas as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> ($<?= number_format((float)$c['saldo_actual'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Confirmar Salida de Dinero</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCobro" tabindex="-1">
        <div class="modal-dialog">
            <form id="formCobro" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Abono / Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 text-center">
                        <small>Deuda Total Seleccionada: <strong id="deudaTotalSugerida">$0.00</strong></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">¿Cuánto dinero estás recibiendo en este momento?</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" id="monto_a_pagar_input" class="form-control form-control-lg text-success fw-bold" required>
                        </div>
                        <small class="text-muted">Escribe la cantidad exacta. El sistema la repartirá automáticamente.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Caja de Destino (Donde entra el dinero)</label>
                        <select id="cuenta_cobro_id" class="form-select" required>
                            <option value="">Selecciona una cuenta...</option>
                            <?php foreach($cuentas as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha del Cobro</label>
                        <input type="date" id="fecha_cobro" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirmar Entrada de Efectivo</button>
                </div>
            </form>
        </div>
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/pantalla-completa.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#tablaOtorgados').DataTable({
                ajax: { url: 'api/prestamos-otorgados.php', dataSrc: "" },
                columns: [
                    {
                        data: null, orderable: false,
                        render: function(d, t, row) {
                            if (row.estado === 'pagado') return '';
                            return `<input type="checkbox" class="check-p form-check-input" data-id="${row.id}" data-monto="${row.monto_total - row.monto_pagado}">`;
                        }
                    },
                    { data: 'fecha_prestamo', render: f => f.split(' ')[0] },
                    { data: 'persona_nombre', render: n => `<span class="fw-bold">${n}</span>` },
                    { data: 'monto_total', render: m => `$${parseFloat(m).toLocaleString('es-MX', {minimumFractionDigits:2})}` },
                    { data: 'monto_pagado', render: m => `<span class="text-collected">$${parseFloat(m).toLocaleString('es-MX', {minimumFractionDigits:2})}</span>` },
                    { data: null, render: r => `<span class="text-pending">$${(r.monto_total - r.monto_pagado).toLocaleString('es-MX', {minimumFractionDigits:2})}</span>` },
                    { 
                        data: 'estado',
                        render: e => {
                            const colors = { 'pendiente': 'danger', 'parcial': 'warning', 'pagado': 'success' };
                            return `<span class="badge bg-light-${colors[e]} text-${colors[e]} text-uppercase border-0 p-2">${e}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: r => {
                            if(r.estado === 'pagado') return '<span class="text-muted"><i class="ti ti-check"></i> Liquidado</span>';
                            return `<button class="badge bg-primary bg-opacity-10 text-primary border-0 btn-cobrar" data-id="${r.id}" data-monto="${r.monto_total - r.monto_pagado}"><i class="ti ti-cash"></i> Cobrar</button>`;
                        }
                    }
                ],
                order: [[1, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // Selección Múltiple
            $('#checkAll').change(function() { $('.check-p').prop('checked', $(this).prop('checked')).trigger('change'); });
            $(document).on('change', '.check-p', function() {
                const sel = $('.check-p:checked');
                $('#countCheck').text(sel.length);
                sel.length > 0 ? $('#btnCobroMasivo').fadeIn() : $('#btnCobroMasivo').fadeOut();
            });

            // Botón Cobro Masivo
            $('#btnCobroMasivo').click(function() {
                let ids = [], total = 0;
                $('.check-p:checked').each(function() {
                    ids.push($(this).data('id'));
                    total += parseFloat($(this).data('monto'));
                });
                abrirModalCobro(ids, total);
            });

            // Botón Cobro Individual
            $(document).on('click', '.btn-cobrar', function() {
                abrirModalCobro([$(this).data('id')], $(this).data('monto'));
            });

            function abrirModalCobro(ids, montoTotalSugerido) {
                $('#formCobro').data('ids', ids);
                $('#deudaTotalSugerida').text(`$${montoTotalSugerido.toLocaleString('es-MX', {minimumFractionDigits:2})}`);
                
                // Aquí llenamos el input con el total sugerido, pero el usuario puede borrarlo
                $('#monto_a_pagar_input').val(montoTotalSugerido.toFixed(2));
                
                $('#modalCobro').modal('show');
            }

            // Registrar Préstamo
            $('#formNuevoPrestamo').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize() + '&fecha_prestamo=' + new Date().toISOString().slice(0, 19).replace('T', ' ');
                $.post('api/prestamos-otorgados.php', formData, function() {
                    $('#modalNuevoPrestamo').modal('hide');
                    $('#formNuevoPrestamo')[0].reset();
                    table.ajax.reload();
                }).fail(function(xhr) { alert(xhr.responseJSON?.error || "Error"); });
            });

            // Procesar Cobro (Abono Editable)
            $('#formCobro').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'api/prestamos-otorgados.php',
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        ids: $(this).data('ids'),
                        monto_a_pagar: $('#monto_a_pagar_input').val(), // Tomamos lo que el usuario haya escrito
                        cuenta_pago_id: $('#cuenta_cobro_id').val(),
                        fecha_pago: $('#fecha_cobro').val() + ' ' + new Date().toTimeString().split(' ')[0]
                    }),
                    success: function() {
                        $('#modalCobro').modal('hide');
                        $('#btnCobroMasivo').hide();
                        $('#checkAll').prop('checked', false);
                        table.ajax.reload();
                    },
                    error: function(xhr) { alert(xhr.responseJSON?.error || "Error al cobrar"); }
                });
            });
        });
    </script>
</body>
</html>