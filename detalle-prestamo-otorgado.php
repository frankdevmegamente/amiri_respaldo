<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$prestamo_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$prestamo_id) {
    header('Location: flujo.php');
    exit;
}

$pdo = getPDO();
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos del préstamo
$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as cuenta_nombre 
    FROM prestamos_otorgados p 
    LEFT JOIN cuentas c ON p.cuenta_id = c.id 
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$prestamo_id]);
$prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestamo) {
    die("Error: Préstamo no encontrado.");
}

$total_con_interes = floatval($prestamo['monto_total_con_interes'] ?: $prestamo['monto_total']);
$pendiente = $total_con_interes - floatval($prestamo['monto_pagado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Préstamo #<?= $prestamo_id ?> - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">

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
                    <div class="row mb-3 mt-4">
                        <div class="col-12 d-flex align-items-center gap-2">
                            <a href="flujo.php" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-arrow-left"></i> Volver a Flujo
                            </a>
                            <a href="detalle-deudor.php?nombre=<?= urlencode($prestamo['persona_nombre']) ?>" class="btn btn-outline-warning btn-sm">
                                <i class="ti ti-user"></i> Ver todos los préstamos de <?= htmlspecialchars($prestamo['persona_nombre']) ?>
                            </a>
                            <h3 class="mb-0"><i class="ti ti-coin"></i> Préstamo #<?= $prestamo_id ?></h3>
                            <span class="badge bg-<?= $prestamo['estado'] === 'pagado' ? 'success' : ($prestamo['estado'] === 'parcial' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $prestamo['estado'] === 'pagado' ? 'success' : ($prestamo['estado'] === 'parcial' ? 'warning' : 'danger') ?> border-0 ms-2">
                                <?= ucfirst($prestamo['estado']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm border-primary">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Persona</h6>
                                    <h5 class="mb-0"><?= htmlspecialchars($prestamo['persona_nombre']) ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-warning">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Monto Total</h6>
                                    <h3 class="mb-0 text-warning">$<?= number_format($prestamo['monto_total'], 2) ?> MXN</h3>
                                    <small class="text-primary">+ $<?= number_format(floatval($prestamo['monto_interes']),2) ?> interés → $<?= number_format($total_con_interes,2) ?></small>
                                    <?php if($prestamo['tasa_porcentaje']>0): ?><br><small class="text-muted"><?= $prestamo['tasa_porcentaje'] ?>% <?= $prestamo['tasa_periodo'] ?> x <?= $prestamo['plazo_valor'] ?> <?= $prestamo['plazo_unidad'] ?><?= $prestamo['fecha_vencimiento'] ? ' vence '.date('d/m/Y', strtotime($prestamo['fecha_vencimiento'])):'' ?></small><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-success">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Pagado</h6>
                                    <h3 class="mb-0 text-success">$<?= number_format($prestamo['monto_pagado'], 2) ?> MXN</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-danger">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Pendiente</h6>
                                    <h3 class="mb-0 text-danger">$<?= number_format($pendiente, 2) ?> MXN</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="ti ti-info-circle"></i> Información del Préstamo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <strong>Fecha del Préstamo:</strong><br>
                                            <?= date('d/m/Y H:i', strtotime($prestamo['fecha_prestamo'])) ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Estado:</strong><br>
                                            <span class="badge bg-<?= $prestamo['estado'] === 'pagado' ? 'success' : ($prestamo['estado'] === 'parcial' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($prestamo['estado']) ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Cuenta Origen:</strong><br>
                                            <?= htmlspecialchars($prestamo['cuenta_nombre'] ?? 'N/A') ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Actualizado:</strong><br>
                                            <?= date('d/m/Y H:i', strtotime($prestamo['actualizado_en'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="ti ti-cash"></i> Cobrar este Préstamo</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($prestamo['estado'] === 'pagado'): ?>
                                        <div class="alert alert-success">
                                            <i class="ti ti-check"></i> Este préstamo ya está pagado completamente.
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="ti ti-info-circle"></i> Para cobrar este préstamo, ve a <a href="flujo.php" class="alert-link">Flujo</a> y registra el cobro.
                                        </div>
                                        <a href="flujo.php" class="btn btn-success">
                                            <i class="ti ti-exchange"></i> Ir a Flujo para Cobrar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/utils.js"></script>
</body>
</html>
