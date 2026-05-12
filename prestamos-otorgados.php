<?php
require_once __DIR__ . '/src/config/session.php';
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') { header('Location: login.php'); exit; }
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
$cuentas = $pdo->query("SELECT id, nombre, saldo_actual FROM cuentas WHERE activo = 1 AND tipo_moneda = 'MXN' AND tipo_cuenta IN (1, 2) ORDER BY nombre ASC")->fetchAll();
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
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/prestamos-otorgados.css">
    <style>
        td.details-control {
            cursor: pointer;
            text-align: center;
            width: 30px;
        }
        td.details-control i {
            transition: transform 0.2s;
            font-size: 1.1rem;
        }
        tr.shown td.details-control i {
            transform: rotate(90deg);
        }
        .child-table {
            margin: 8px 0 8px 40px;
            width: calc(100% - 40px);
        }
        .child-table td, .child-table th {
            padding: 6px 10px !important;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h4 class="fw-semibold mb-0"><i class="ti ti-coin"></i> Préstamos Otorgados</h4>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <button id="btnCobroMasivo" class="btn btn-success btn-sm" style="display:none;">
                                        <i class="ti ti-cash"></i> Cobro (<span id="countCheck">0</span>)
                                    </button>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPrestamo">
                                        <i class="ti ti-plus"></i> Nuevo Préstamo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaOtorgados" class="table table-hover align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;"></th>
                                            <th style="width: 30px;"><input type="checkbox" id="checkAll"></th>
                                            <th>Deudor</th>
                                            <th>Préstamos</th>
                                            <th>Total Prestado</th>
                                            <th>Cobrado</th>
                                            <th>Pendiente</th>
                                            <th>Estatus</th>
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
    </div>

    <div class="modal fade" id="modalCobro" tabindex="-1">
        <div class="modal-dialog">
            <form id="formCobro" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Abono / Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" id="resumenDeudor" style="display: none;">
                        <small>Cobrando a: <strong id="nombreDeudorCobro"></strong></small>
                    </div>

                    <div id="contenedorPagosMultiples" class="mb-3 p-3 bg-light border rounded" style="display: none;"></div>

                    <div class="alert alert-warning py-2 text-center" id="alertaDeudaTotal" style="display: none;">
                        <small>Deuda Total: <strong id="deudaTotalSugerida">$0.00</strong></small>
                    </div>

                    <div class="mb-3" id="grupoMontoGlobal">
                        <label class="form-label fw-bold text-success">¿Cuánto dinero estás recibiendo?</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" step="0.01" id="monto_a_pagar_input" class="form-control form-control-lg text-success fw-bold">
                        </div>
                        <small class="text-muted">Se distribuirá automáticamente entre los préstamos pendientes.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Caja de Destino</label>
                        <select id="cuenta_cobro_id" class="form-select" required>
                            <option value="">Selecciona una cuenta...</option>
                            <?php foreach($cuentas as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> ($<?= number_format((float)$c['saldo_actual'], 2) ?>)</option>
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
                        <input type="text" name="persona_nombre" class="form-control" list="listaPersonas" autocomplete="off" placeholder="Escribe o selecciona un nombre..." required>
                        <datalist id="listaPersonas"></datalist>
                        <small class="text-muted">Sugiere nombres de Promotores o Clientes.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monto a Prestar</label>
                            <input type="text" step="0.01" name="monto" class="form-control" required>
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
            $.get('api/clientes.php', function(data) {
                if(data) data.forEach(c => $('#listaPersonas').append(`<option value="${c.nombre}">`));
            }, 'json');
            $.get('api/promotores.php', function(data) {
                if(data) data.forEach(p => $('#listaPersonas').append(`<option value="${p.nombre}">`));
            }, 'json');

            var fmt = function(n) {
                return parseFloat(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
            };

            function buildChildHtml(loans) {
                if (!loans || !loans.length) return '<div class="text-muted text-center py-2">Sin préstamos</div>';
                var h = '<table class="table table-sm table-borderless child-table mb-0"><thead><tr class="text-muted" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;"><th>Fecha</th><th>Monto</th><th>Cobrado</th><th>Pendiente</th><th>Estatus</th></tr></thead><tbody>';
                loans.forEach(function(l) {
                    var pend = l.monto_total - l.monto_pagado;
                    var cls = l.estado === 'pagado' ? 'success' : l.estado === 'parcial' ? 'warning' : 'danger';
                    h += '<tr><td>' + (l.fecha_prestamo ? l.fecha_prestamo.split(' ')[0].split('-').reverse().join('/') : '') + '</td><td class="fw-bold">$' + fmt(l.monto_total) + '</td><td class="text-success">$' + fmt(l.monto_pagado) + '</td><td class="text-danger fw-bold">$' + fmt(pend) + '</td><td><span class="badge bg-' + cls + ' bg-opacity-10 text-' + cls + ' border-0" style="font-size:0.7rem;">' + l.estado + '</span></td></tr>';
                });
                h += '</tbody></table>';
                return h;
            }

            var table = $('#tablaOtorgados').DataTable({
                ajax: { url: 'api/prestamos-otorgados.php?agrupado', dataSrc: '' },
                columns: [
                    {
                        data: null, orderable: false, className: 'details-control',
                        defaultContent: '<i class="ti ti-chevron-right text-muted"></i>'
                    },
                    {
                        data: null, orderable: false,
                        render: function(d, t, row) {
                            if (parseFloat(row.total_pendiente) <= 0) return '';
                            return '<input type="checkbox" class="check-d form-check-input" data-nombre="' + row.persona_nombre.replace(/"/g, '&quot;') + '" data-pendiente="' + row.total_pendiente + '">';
                        }
                    },
                    {
                        data: 'persona_nombre',
                        render: function(n) {
                            return '<a href="detalle-deudor.php?nombre=' + encodeURIComponent(n) + '" class="fw-bold text-decoration-none">' + n + '</a>';
                        }
                    },
                    { data: 'total_prestamos', render: function(n) { return '<span class="badge bg-light text-dark border-0">' + n + '</span>'; } },
                    { data: 'total_prestado', render: function(n) { return '$' + fmt(n); } },
                    { data: 'total_cobrado', render: function(n) { return '<span class="text-collected">$' + fmt(n) + '</span>'; } },
                    { data: 'total_pendiente', render: function(n) { return '<span class="text-pending">$' + fmt(n) + '</span>'; } },
                    {
                        data: null,
                        render: function(r) {
                            var pend = parseFloat(r.total_pendiente);
                            if (pend <= 0) return '<span class="badge bg-success bg-opacity-10 text-success border-0">Pagado</span>';
                            if (parseFloat(r.total_cobrado) > 0) return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Parcial</span>';
                            return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Pendiente</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(r) {
                            var pend = parseFloat(r.total_pendiente);
                            var btn = pend > 0 ? '<button class="btn btn-sm btn-success btn-cobrar-deudor" data-nombre="' + r.persona_nombre.replace(/"/g, '&quot;') + '" data-pendiente="' + pend + '"><i class="ti ti-cash"></i> Cobrar</button> ' : '';
                            return '<div class="d-flex align-items-center gap-1">' + btn + '<a href="detalle-deudor.php?nombre=' + encodeURIComponent(r.persona_nombre) + '" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="ti ti-eye"></i></a></div>';
                        }
                    }
                ],
                order: [[6, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            $('#tablaOtorgados tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var data = row.data();
                if (!data) return;

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    table.rows().every(function() { if (this.child.isShown()) { this.child.hide(); $(this.node()).removeClass('shown'); } });
                    $(this).html('<i class="ti ti-loader ti-spin text-primary"></i>');
                    $.get('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(data.persona_nombre), function(loans) {
                        row.child(buildChildHtml(loans)).show();
                        tr.addClass('shown');
                        tr.find('td.details-control').html('<i class="ti ti-chevron-right text-primary"></i>');
                    }).fail(function() {
                        tr.find('td.details-control').html('<i class="ti ti-chevron-right text-muted"></i>');
                    });
                }
            });

            $('#checkAll').change(function() {
                $('.check-d').prop('checked', $(this).prop('checked')).trigger('change');
            });
            $(document).on('change', '.check-d', function() {
                var sel = $('.check-d:checked');
                $('#countCheck').text(sel.length);
                if (sel.length > 0) {
                    $('#btnCobroMasivo').fadeIn();
                    $('#checkAll').prop('checked', sel.length === $('.check-d').length);
                } else {
                    $('#btnCobroMasivo').fadeOut();
                    $('#checkAll').prop('checked', false);
                }
            });

            $(document).on('click', '.btn-cobrar-deudor', function() {
                var nombre = $(this).data('nombre');
                var pendiente = parseFloat($(this).data('pendiente'));
                $('#contenedorPagosMultiples').hide().empty();
                $('#resumenDeudor').show().find('#nombreDeudorCobro').text(nombre);
                $('#alertaDeudaTotal').show();
                $('#deudaTotalSugerida').text('$' + fmt(pendiente));
                $('#monto_a_pagar_input').val(pendiente.toFixed(2));
                $('#cuenta_cobro_id').val('');
                $('#fecha_cobro').val(new Date().toISOString().slice(0, 10));
                $('#formCobro').data('modo', 'individual').data('nombre', nombre);
                $('#modalCobro').modal('show');
            });

            $('#btnCobroMasivo').click(function() {
                var seleccionados = [];
                $('.check-d:checked').each(function() {
                    seleccionados.push({ nombre: $(this).data('nombre'), pendiente: parseFloat($(this).data('pendiente')) });
                });
                if (!seleccionados.length) return;

                var totalPendiente = seleccionados.reduce(function(s, d) { return s + d.pendiente; }, 0);
                var htmlPagos = '<h6 class="mb-3 text-primary"><i class="ti ti-list-check"></i> Deudores seleccionados:</h6>';
                seleccionados.forEach(function(d) {
                    htmlPagos += '<div class="d-flex justify-content-between align-items-center py-1 px-2 bg-white border rounded mb-1"><span class="fw-bold">' + d.nombre + '</span><span class="text-danger fw-bold">$' + fmt(d.pendiente) + '</span></div>';
                });

                $('#contenedorPagosMultiples').html(htmlPagos).fadeIn();
                $('#resumenDeudor').hide();
                $('#alertaDeudaTotal').show();
                $('#deudaTotalSugerida').text('$' + fmt(totalPendiente));
                $('#monto_a_pagar_input').val(totalPendiente.toFixed(2));
                $('#cuenta_cobro_id').val('');
                $('#fecha_cobro').val(new Date().toISOString().slice(0, 10));
                $('#formCobro').data('modo', 'masivo').data('nombres', seleccionados.map(function(d) { return d.nombre; }));
                $('#modalCobro').modal('show');
            });

            $('#formNuevoPrestamo').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize() + '&fecha_prestamo=' + new Date().toISOString().slice(0, 19).replace('T', ' ');
                $.post('api/prestamos-otorgados.php', formData, function() {
                    $('#modalNuevoPrestamo').modal('hide');
                    $('#formNuevoPrestamo')[0].reset();
                    table.ajax.reload();
                }).fail(function(xhr) { alert(xhr.responseJSON?.error || "Error"); });
            });

            $('#formCobro').submit(function(e) {
                e.preventDefault();
                var btnSubmit = $(this).find('button[type="submit"]');
                btnSubmit.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');

                var montoTotal = parseFloat($('#monto_a_pagar_input').val()) || 0;
                if (montoTotal <= 0) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("Ingresa un monto v\u00e1lido.");
                }

                if (!$('#cuenta_cobro_id').val()) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("Selecciona una cuenta de destino.");
                }

                var nombres = [];
                var modoPago = $('#formCobro').data('modo');
                if (modoPago === 'masivo') {
                    nombres = $('#formCobro').data('nombres') || [];
                } else {
                    nombres = [$('#formCobro').data('nombre')];
                }
                if (!nombres.length) {
                    btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                    return alert("No hay deudores seleccionados.");
                }

                $.getJSON('api/prestamos-otorgados.php', function(allLoans) {
                    var pagos = [];
                    var restante = montoTotal;
                    allLoans.forEach(function(p) {
                        if (nombres.indexOf(p.persona_nombre) !== -1 && p.estado !== 'pagado') {
                            var pendiente = p.monto_total - p.monto_pagado;
                            var abono = Math.min(pendiente, restante);
                            if (abono > 0.01) {
                                pagos.push({ id: p.id, abono: abono });
                                restante -= abono;
                            }
                        }
                    });

                    if (!pagos.length) {
                        btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo');
                        return alert("No hay pr\u00e9stamos pendientes para los deudores seleccionados.");
                    }

                    $.ajax({
                        url: 'api/prestamos-otorgados.php',
                        method: 'PUT',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            pagos: pagos,
                            cuenta_pago_id: $('#cuenta_cobro_id').val(),
                            fecha_pago: $('#fecha_cobro').val() + ' ' + new Date().toTimeString().split(' ')[0]
                        }),
                        success: function() {
                            $('#modalCobro').modal('hide');
                            $('#btnCobroMasivo').hide();
                            $('#checkAll').prop('checked', false);
                            table.ajax.reload();
                        },
                        error: function(xhr) { alert(xhr.responseJSON?.error || "Error al cobrar"); },
                        complete: function() { btnSubmit.prop('disabled', false).text('Confirmar Entrada de Efectivo'); }
                    });
                });
            });

            table.on('draw', function() {
                $('tr.shown').each(function() {
                    var row = table.row(this);
                    if (row.data()) {
                        $.get('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(row.data().persona_nombre), function(loans) {
                            row.child(buildChildHtml(loans)).show();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
