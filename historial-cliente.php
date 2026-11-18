<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$cliente_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$cliente_id) {
    die("Error: ID de cliente no válido.");
}

require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT nombre FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        die("Error: Cliente no encontrado.");
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
    <title>Historial de Movimientos - <?= htmlspecialchars($cliente['nombre']) ?></title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<style>
    .body-wrapper {
        padding-top: 100px !important; 
    }
    
    .body-wrapper-inner, 
    .body-wrapper .container-fluid {
        padding-top: 15px !important;
        margin-top: 0 !important;
    }

    /* =========================================
       ✨ LÓGICA DEL LOGO (MODO OSCURO)
       ========================================= */
    [data-bs-theme="dark"] .logo-modo-claro {
        display: none !important;
    }

    [data-bs-theme="dark"] .logo-modo-oscuro {
        display: block !important;
    }

    /* =========================================
       🌙 REGLAS PARA MODO OSCURO (Tablas e Historial)
       ========================================= */
    [data-bs-theme="dark"] h3,
    [data-bs-theme="dark"] .page-breadcrumb h3 {
        color: #e9ecef !important;
    }

    [data-bs-theme="dark"] .card {
        background: #1a2234;
        border-color: #2c344c;
    }

    [data-bs-theme="dark"] .table>thead>tr>th {
        background-color: #1a2234 !important;
        color: #e9ecef !important;
        border-bottom-color: #2c344c !important;
    }

    [data-bs-theme="dark"] .table>tbody>tr>td {
        color: #e9ecef !important;
        border-color: #2c344c !important;
    }

    /* Estilo Zebra y Hover */
    [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
        box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02);
    }
    [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* {
        box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05);
    }

    /* Controles de DataTables */
    [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
    [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
    [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
    [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate {
        color: #8c9ea8 !important;
    }

    [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #8c9ea8 !important;
    }
    
    [data-bs-theme="dark"] .dataTables_wrapper select,
    [data-bs-theme="dark"] .dataTables_wrapper input {
        background-color: #252f43;
        border: 1px solid #2c344c;
        color: #e9ecef;
    }

    /* =========================================
       🎨 BADGES VIBRANTES (MODO OSCURO)
       ========================================= */
    [data-bs-theme="dark"] .badge.bg-success {
        background-color: rgba(19, 222, 185, 0.15) !important;
        color: #13DEB9 !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-danger {
        background-color: rgba(250, 137, 107, 0.15) !important;
        color: #FA896B !important;
    }

    /* Este es el que estabas buscando para Comisión */
    [data-bs-theme="dark"] .badge.bg-info {
        background-color: rgba(93, 135, 255, 0.15) !important;
        color: #5D87FF !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-secondary {
        background-color: rgba(140, 158, 168, 0.15) !important;
        color: #8c9ea8 !important;
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
                                <h3 class="mb-0"><i class="ti ti-history"></i> Historial de Cliente: <?= htmlspecialchars($cliente['nombre']) ?></h3>
                                <a href="clientes.php" class="btn btn-secondary ms-auto"><i class="ti ti-arrow-left"></i> Volver a Clientes</a>
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
                    // Usamos el tipo "cliente" para filtrar en la API universal
                    url: 'api/obtener_movimientos.php?tipo=cliente&id=<?= $cliente_id ?>',
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
        
        // Asignamos colores específicos según el tipo
        if(t === 'entrada') {
            return '<span class="badge bg-success bg-opacity-10 text-success border-0">Entrada</span>';
        }
        if(t === 'salida') {
            return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Salida</span>';
        }
        if(t === 'comision' || t === 'comisión') {
            return '<span class="badge bg-info bg-opacity-10 text-info border-0">Comisión</span>';
        }
        
        // Para cualquier otro tipo, usamos el gris secundario
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
                            const cls = (e === 'completado' || e === 'aprobado' || e === 'pagado') ? 'success' : (e === 'pendiente' || e === 'parcial' ? 'warning' : 'danger');
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