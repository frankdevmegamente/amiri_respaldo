<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$proveedor_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$proveedor_id) {
    die("Error: ID de proveedor no válido.");
}

require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT nombre FROM proveedores WHERE id = ?");
    $stmt->execute([$proveedor_id]);
    $proveedor = $stmt->fetch();

    if (!$proveedor) {
        die("Error: Proveedor no encontrado.");
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
    <title>Historial de Movimientos - <?= htmlspecialchars($proveedor['nombre']) ?></title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
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
                                <h3 class="mb-0"><i class="ti ti-history"></i> Historial de Proveedor: <?= htmlspecialchars($proveedor['nombre']) ?></h3>
                                <a href="proveedores.php" class="btn btn-secondary ms-auto"><i class="ti ti-arrow-left"></i> Volver a Proveedores</a>
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
    <script src="src/assets/js/utils.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaHistorial').DataTable({
                ajax: {
                    // Usamos el tipo "proveedor" para filtrar en la API
                    url: 'api/obtener_movimientos.php?tipo=proveedor&id=<?= $proveedor_id ?>',
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
                    { data: 'servicio', defaultContent: '<span class="text-muted">N/A</span>' },
                    { 
                        data: 'tipo',
                        render: function(d) {
                            if(!d) return '';
                            const t = d.toLowerCase();
                            if(t === 'entrada') return '<span class="badge bg-success bg-opacity-10 text-success border-0">Entrada</span>';
                            if(t === 'salida') return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Salida</span>';
                            return `<span class="badge bg-secondary bg-opacity-10 text-secondary border-0 text-capitalize">${d}</span>`;
                        }
                    },
                    { 
                        data: 'monto',
                        render: d => d ? new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(d) : '$0.00'
                    },
                    { data: 'descripcion', defaultContent: '' },
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
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });
    </script>
</body>
</html>