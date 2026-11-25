<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

$nombre = $_GET['nombre'] ?? '';
if (!$nombre) {
    header('Location: flujo.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as cuenta_nombre, COALESCE(p.monto_total_con_interes, p.monto_total,0) as total_con_interes_calc
    FROM prestamos_otorgados p 
    LEFT JOIN cuentas c ON p.cuenta_id = c.id 
    LEFT JOIN deudores d ON d.id = p.deudor_id
    WHERE (p.persona_nombre = ? OR d.nombre = ?) AND p.activo = 1 
    ORDER BY p.fecha_prestamo DESC
");
$stmt->execute([$nombre, $nombre]);
$prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_prestado = 0;
$total_con_interes = 0;
$total_pagado = 0;
$total_pendiente = 0;
foreach ($prestamos as $p) {
    $total = floatval($p['monto_total_con_interes'] ?: $p['monto_total']);
    $total_prestado += floatval($p['monto_total']);
    $total_con_interes += $total;
    $total_pagado += floatval($p['monto_pagado']);
}
$total_pendiente = $total_con_interes - $total_pagado;

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Deudor - <?= htmlspecialchars($nombre) ?> - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <link rel="stylesheet" href="css/detalle-deudor.css">
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
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
                                <div class="d-flex align-items-center gap-2">
                                    <a href="flujo.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="ti ti-arrow-left"></i> Volver
                                    </a>
                                    <h3 class="mb-0"><i class="ti ti-user"></i> <?= htmlspecialchars($nombre) ?></h3>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border-0 ms-2">Deudor</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100" style="position: relative; overflow: hidden;">

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-arrow-up-right fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Prestado</p>
                                            <h3 class="mb-0 text-warning fw-bold">$<?= number_format($total_prestado, 2) ?></h3>
                                            <small class="text-muted">+ $<?= number_format($total_con_interes - $total_prestado, 2) ?> interés → $<?= number_format($total_con_interes, 2) ?> MXN</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100" style="position: relative; overflow: hidden;">

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-arrow-down-left fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Pagado</p>
                                            <h3 class="mb-0 text-success fw-bold">$<?= number_format($total_pagado, 2) ?></h3>
                                            <small class="text-muted">MXN</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100" style="position: relative; overflow: hidden;">

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-alert-triangle fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Pendiente</p>
                                            <h3 class="mb-0 text-danger fw-bold">$<?= number_format($total_pendiente, 2) ?></h3>
                                            <small class="text-muted">MXN</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 px-4">
                                    <h5 class="fw-semibold mb-0"><i class="ti ti-list-details me-2"></i>Préstamos Otorgados</h5>
                                    <?php if ($total_pendiente > 0): ?>
                                    <a href="flujo.php" class="btn btn-sm btn-success">
                                        <i class="ti ti-cash"></i> Ir a Flujo para Cobrar
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-4">
                                    <?php if (empty($prestamos)): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="ti ti-inbox" style="font-size: 3rem;"></i>
                                            <p class="mt-2">No hay préstamos registrados</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover w-100" id="tablaPrestamos">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Concepto</th>
                                                        <th>Monto Total</th>
                                                        <th>Pagado</th>
                                                        <th>Pendiente</th>
                                                        <th>Estado</th>
                                                        <th>Cuenta Origen</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($prestamos as $p): 
                                                        $total = floatval($p['monto_total_con_interes'] ?: $p['monto_total']);
                                                        $pendiente = $total - floatval($p['monto_pagado']);
                                                        $badge = $p['estado'] === 'pagado' ? 'success' : ($p['estado'] === 'parcial' ? 'warning' : 'danger');
                                                    ?>
                                                        <tr>
                                                            <td><?= date('d/m/Y', strtotime($p['fecha_prestamo'])) ?><?= $p['fecha_vencimiento'] ? '<br><small class="text-muted">Vence '.date('d/m/Y', strtotime($p['fecha_vencimiento'])).'</small>' : '' ?><?= $p['tasa_porcentaje']>0 ? '<br><small class="text-primary">'.$p['tasa_porcentaje'].'% '.$p['tasa_periodo'].'</small>' : '' ?></td>
                                                            <td><?= !empty($p['concepto']) ? htmlspecialchars($p['concepto']) : '<span class="text-muted">Sin concepto</span>' ?></td>
                                                            <td class="fw-bold">$<?= number_format($p['monto_total'], 2) ?><br><small class="text-primary">+$<?= number_format(floatval($p['monto_interes']),2) ?> → $<?= number_format($total,2) ?></small></td>
                                                            <td class="text-success">$<?= number_format($p['monto_pagado'], 2) ?></td>
                                                            <td class="text-danger fw-bold">$<?= number_format($pendiente, 2) ?></td>
                                                            <td><span class="badge bg-<?= $badge ?> bg-opacity-10 text-<?= $badge ?> border-0"><?= ucfirst($p['estado']) ?></span></td>
                                                            <td><?= htmlspecialchars($p['cuenta_nombre'] ?? 'N/A') ?></td>
                                                            <td>
                                                                <?php if ($p['estado'] !== 'pagado'): ?>
                                                                    <button class="btn btn-sm btn-success btn-cobrar" data-id="<?= $p['id'] ?>" data-nombre="<?= htmlspecialchars($nombre) ?>" data-pendiente="<?= $pendiente ?>" data-total="<?= $total ?>">
                                                                        <i class="ti ti-cash"></i> Cobrar
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header border-0 pt-4 px-4" style="background: linear-gradient(135deg, var(--nx-success) 0%, var(--nx-success) 100%);">
                                    <h5 class="mb-0 fw-semibold text-white"><i class="ti ti-cash me-2"></i>Registrar Cobro de Préstamo</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form id="formCobro">
                                        <input type="hidden" id="persona_nombre" value="<?= htmlspecialchars($nombre) ?>">
                                        <div class="row align-items-end">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-semibold">Fecha</label>
                                                <input type="date" class="form-control" id="fechaCobro" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-semibold">Monto a Cobrar</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control" id="montoCobro" required>
                                                </div>
                                                <small class="text-muted">Pendiente total: $<span id="totalPendiente"><?= number_format($total_pendiente, 2) ?></span></small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Cuenta Destino</label>
                                                <select class="form-select" id="cuentaDestino" required>
                                                    <option value="">-- Selecciona --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="ti ti-check"></i> Cobrar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/utils.js"></script>

    <script>
    $(document).ready(function() {
        $('#tablaPrestamos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            pageLength: 10
        });

        $.getJSON('api/cuentas.php?tipo_moneda=MXN&es_caja_empresa=1', function(data) {
            let html = '<option value="">-- Selecciona --</option>';
            data.forEach(c => {
                html += `<option value="${c.id}">${c.nombre} ($${parseFloat(c.saldo_actual).toLocaleString('es-MX', {minimumFractionDigits:2})})</option>`;
            });
            $('#cuentaDestino').html(html);
        });

        $(document).on('click', '.btn-cobrar', function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            let pendiente = $(this).data('pendiente');

            $('#montoCobro').val(pendiente.toFixed(2));
            $('#montoCobro').data('prestamo_id', id);
            $('html, body').animate({ scrollTop: $('#formCobro').offset().top }, 500);
        });

        $('#formCobro').on('submit', function(e) {
            e.preventDefault();

            let monto = parseFloat($('#montoCobro').val());
            let cuenta_id = $('#cuentaDestino').val();
            let fecha = $('#fechaCobro').val();
            let persona = $('#persona_nombre').val();

            if (!monto || monto <= 0) {
                showToast('Ingresa un monto válido', 'error');
                return;
            }

            if (!cuenta_id) {
                showToast('Selecciona una cuenta destino', 'error');
                return;
            }

            $.getJSON('api/prestamos-otorgados.php', function(prestamos) {
                let pagos = [];
                let restante = monto;

                prestamos.forEach(function(p) {
                    const nom = p.deudor_nombre || p.persona_nombre;
                    if (nom === persona && p.estado !== 'pagado') {
                        const total = parseFloat(p.monto_total_con_interes || p.monto_total);
                        let pendiente = total - parseFloat(p.monto_pagado);
                        let abono = Math.min(pendiente, restante);
                        if (abono > 0) {
                            pagos.push({ id: p.id, abono: abono });
                            restante -= abono;
                        }
                    }
                });

                if (pagos.length === 0) {
                    showToast('No hay préstamos pendientes', 'error');
                    return;
                }

                $.ajax({
                    url: 'api/prestamos-otorgados.php',
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        pagos: pagos,
                        cuenta_pago_id: cuenta_id,
                        fecha_pago: fecha
                    }),
                    success: function(res) {
                        if (res.success) {
                            showToast('Cobro registrado correctamente. Redirigiendo a Flujo...', 'success');
                            setTimeout(() => { window.location.href = 'flujo.php'; }, 1500);
                        } else {
                            showToast('Error: ' + (res.error || 'Desconocido'), 'error');
                        }
                    },
                    error: function(xhr) {
                        showToast('Error al procesar el cobro', 'error');
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
