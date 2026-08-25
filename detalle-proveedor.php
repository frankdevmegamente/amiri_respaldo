<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$proveedor_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$proveedor_id) {
    header('Location: flujo.php');
    exit;
}

$pdo = getPDO();
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos del proveedor
$stmt = $pdo->prepare("
    SELECT p.*, 
           cu.nombre AS cuenta_usd_nombre, cu.saldo_actual AS saldo_usd, cu.valor_mxn AS valor_mxn_usd,
           cusdt.nombre AS cuenta_usdt_nombre, cusdt.saldo_actual AS saldo_usdt, cusdt.valor_mxn AS valor_mxn_usdt
    FROM proveedores p
    LEFT JOIN cuentas cu ON p.cuenta_usd_id = cu.id
    LEFT JOIN cuentas cusdt ON p.cuenta_usdt_id = cusdt.id
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$proveedor_id]);
$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    die("Error: Proveedor no encontrado.");
}

// Calcular totales
$total_usd = floatval($proveedor['saldo_usd'] ?? 0);
$total_usdt = floatval($proveedor['saldo_usdt'] ?? 0);
$total_mxn = floatval($proveedor['valor_mxn_usd'] ?? 0) + floatval($proveedor['valor_mxn_usdt'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Proveedor - <?= htmlspecialchars($proveedor['nombre']) ?> - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        [data-bs-theme="dark"] .card { background: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .table { color: #e9ecef; }
        [data-bs-theme="dark"] .table thead th { background-color: #1a2234 !important; color: #e9ecef; }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
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
                                <i class="ti ti-arrow-left"></i> Volver
                            </a>
                            <h3 class="mb-0"><i class="ti ti-building"></i> <?= htmlspecialchars($proveedor['nombre']) ?></h3>
                            <span class="badge bg-info bg-opacity-10 text-info border-0 ms-2">Proveedor</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-info">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Adeudo USD</h6>
                                    <h3 class="mb-0 text-primary"><?= number_format($total_usd, 2) ?> USD</h3>
                                    <small class="text-muted">$<?= number_format($proveedor['valor_mxn_usd'] ?? 0, 2) ?> MXN</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-success">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Adeudo USDT</h6>
                                    <h3 class="mb-0 text-success"><?= number_format($total_usdt, 2) ?> USDT</h3>
                                    <small class="text-muted">$<?= number_format($proveedor['valor_mxn_usdt'] ?? 0, 2) ?> MXN</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-warning">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Total en MXN</h6>
                                    <h3 class="mb-0 text-warning">$<?= number_format($total_mxn, 2) ?> MXN</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="ti ti-info-circle"></i> Información del Proveedor</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php if ($proveedor['telefono']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-phone"></i> Teléfono:</strong><br>
                                            <?= htmlspecialchars($proveedor['telefono']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($proveedor['email']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-mail"></i> Email:</strong><br>
                                            <?= htmlspecialchars($proveedor['email']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($proveedor['direccion']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-map-pin"></i> Dirección:</strong><br>
                                            <?= htmlspecialchars($proveedor['direccion']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="ti ti-cash"></i> Pagar al Proveedor</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="ti ti-info-circle"></i> Para pagar al proveedor, vuelve al <a href="flujo.php" class="alert-link">Flujo</a> y selecciona el servicio correspondiente (Compra de Dólares/USDT) para registrar el pago.
                                    </div>
                                    <a href="flujo.php" class="btn btn-primary">
                                        <i class="ti ti-exchange"></i> Ir a Flujo para Pagar
                                    </a>
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
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/utils.js"></script>
</body>
</html>
