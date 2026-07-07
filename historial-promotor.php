<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$promotor_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$promotor_id) {
    die("Error: ID de promotor no válido.");
}

require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = getPDO();
    // Obtenemos solo la información básica para el encabezado
    $stmt = $pdo->prepare("SELECT nombre FROM promotores WHERE id = ?");
    $stmt->execute([$promotor_id]);
    $promotor = $stmt->fetch();

    if (!$promotor) {
        die("Error: Promotor no encontrado.");
    }
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Historial de Movimientos - <?= htmlspecialchars($promotor['nombre']) ?></title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
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
            border-bottom-color: #6c5ce7 !important;
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
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-history"></i> Historial: <?= htmlspecialchars($promotor['nombre']) ?></h3>
                                <div class="ms-auto d-flex gap-2">
                                    <a href="detalle-promotor.php?id=<?= $promotor_id ?>" class="btn btn-info"><i class="ti ti-cash"></i> Ver Comisiones</a>
                                    <a href="promotores.php" class="btn btn-secondary"><i class="ti ti-arrow-left"></i> Volver</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaHistorial" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Servicio</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
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
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/utils.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaHistorial').DataTable({
                ajax: {
                    url: 'api/obtener_movimientos.php?tipo=promotor&id=<?= $promotor_id ?>',
                    dataSrc: ""
                },
                columns: [
                    { 
                        data: 'fecha',
                        render: function(data, type, row) {
                            if(!data) return 'N/A';
                            if (type === 'sort' || type === 'type') return data;
                            const partes = data.split(' ')[0].split('-'); 
                            return `${partes[2]}/${partes[1]}/${partes[0]}`; 
                        }
                    },
                    { data: 'servicio', defaultContent: 'N/A' },
                    { 
                        data: 'tipo',
                        render: function(d) {
                            if(!d) return '';
                            const t = d.toLowerCase();
                            if(t === 'entrada') return '<span class="badge bg-success bg-opacity-10 text-success border-0">Entrada</span>';
                            if(t === 'salida') return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Salida</span>';
                            if(t === 'comision') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">Comisión</span>';
                            return `<span class="badge bg-secondary bg-opacity-10 text-secondary border-0 text-capitalize">${d}</span>`;
                        }
                    },
                    { 
                        data: 'monto',
                        render: d => d ? new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(d) : '$0.00'
                    },
                    { data: 'descripcion' },
                    { 
                        data: 'estado',
                        render: function(d) {
                            if(!d) return '';
                            const e = d.toLowerCase();
                            const cls = (e === 'completado' || e === 'aprobado') ? 'success' : (e === 'pendiente' ? 'warning' : 'danger');
                            return `<span class="text-${cls} fw-bold text-capitalize">${d}</span>`;
                        }
                    }
                ],
                order: [[0, "desc"]],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        });
    </script>
</body>
</html>