<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

    header('Location: login.php');

    exit;

}



$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

$pagina_actual = basename($_SERVER['PHP_SELF']);



require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();



// Obtener lista de usuarios para filtros

$usuarios = $pdo->query("SELECT id, nombre FROM usuarios WHERE activo = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="es" data-bs-theme="light">



<head>

    <meta charset="UTF-8" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Auditoría - Sistema Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

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

        .badge-action {

            font-size: 0.75rem;

            padding: 0.35rem 0.65rem;

        }



        .json-viewer {

            background: #f8f9fa;

            border: 1px solid #dee2e6;

            border-radius: 4px;

            padding: 10px;

            font-family: 'Courier New', monospace;

            font-size: 0.85rem;

            max-height: 300px;

            overflow-y: auto;

        }



        body[data-bs-theme="dark"] .json-viewer {

            background: #1a2234;

            border-color: #2e3a52;

            color: #e0e0e0;

        }



        .stat-card {

            border-left: 4px solid #5D87FF;

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
        body[data-bs-theme="dark"] .modal-footer {
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
            color: #3B82F6 !important;
        }

        body[data-bs-theme="dark"] .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: #13DEB9 !important;
        }

        body[data-bs-theme="dark"] .bg-info.bg-opacity-10 {
            background-color: rgba(23, 162, 184, 0.12) !important;
            color: #5D87FF !important;
        }

        body[data-bs-theme="dark"] .bg-danger.bg-opacity-10 {
            background-color: rgba(220, 53, 69, 0.12) !important;
            color: #F05464 !important;
        }

        body[data-bs-theme="dark"] .bg-secondary.bg-opacity-10 {
            background-color: rgba(108, 117, 125, 0.12) !important;
            color: #8C9EA8 !important;
        }

        body[data-bs-theme="dark"] .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.12) !important;
            color: #FFAE1F !important;
        }
    </style>

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include __DIR__ . '/src/assets/partials/header.php'; ?>

        <?php include __DIR__ . '/src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4" style="position: relative; overflow: hidden;">
                                
                                <div style="position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: #5D87FF; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>
                                <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: #13deb9; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="ti ti-shield-check fs-5"></i>
                                        </div>
                                        <h5 class="card-title fw-semibold mb-0">Auditoría del Sistema</h5>
                                    </div>
                                    
                                    <p class="text-muted mb-0">Monitorea todas las actividades y cambios realizados en el sistema</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="estadisticas">
                        <div class="col-md-3 col-sm-6">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total de Registros</h6>
                                    <h3 class="fw-bold" id="stat-total">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Registros de Hoy</h6>
                                    <h3 class="fw-bold" id="stat-hoy">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Esta Semana</h6>
                                    <h3 class="fw-bold" id="stat-semana">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Este Mes</h6>
                                    <h3 class="fw-bold" id="stat-mes">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card filter-card shadow-sm">
                                <div class="card-body py-3">
                                    <div class="row align-items-end g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Usuario</label>
                                            <select class="form-select" id="filtroUsuario" name="usuario_id">
                                                <option value="">Todos</option>
                                                <?php foreach ($usuarios as $u): ?>
                                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Acción</label>
                                            <select class="form-select" id="filtroAccion" name="accion">
                                                <option value="">Todas</option>
                                                <option value="LOGIN">LOGIN</option>
                                                <option value="LOGOUT">LOGOUT</option>
                                                <option value="INSERT">INSERT</option>
                                                <option value="UPDATE">UPDATE</option>
                                                <option value="DELETE">DELETE</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Módulo</label>
                                            <select class="form-select" id="filtroModulo" name="modulo">
                                                <option value="">Todos</option>
                                                <option value="auth">Auth</option>
                                                <option value="clientes">Clientes</option>
                                                <option value="gastos">Gastos</option>
                                                <option value="usuarios">Usuarios</option>
                                                <option value="comisiones-promotores">Comisiones</option>
                                                <option value="prestamistas">Prestamistas</option>
                                                <option value="prestamos">Préstamos</option>
                                                <option value="proveedores">Proveedores</option>
                                                <option value="cuentas">Cuentas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Desde</label>
                                            <input type="date" class="form-control" id="filtroFechaInicio" name="fecha_inicio">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold">Hasta</label>
                                            <input type="date" class="form-control" id="filtroFechaFin" name="fecha_fin">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" onclick="aplicarFiltros()">
                                                <i class="ti ti-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Auditoría -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold">Registro de Actividades</h6>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-success" onclick="exportarExcel()">
                                                <i class="ti ti-file-spreadsheet"></i> Excel
                                            </button>
                                            <button class="btn btn-outline-info" onclick="exportarCSV()">
                                                <i class="ti ti-file-text"></i> CSV
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="limpiarFiltros()">
                                                <i class="ti ti-x"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tablaAuditoria" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Fecha/Hora</th>
                                                    <th>Usuario</th>
                                                    <th>Acción</th>
                                                    <th>Módulo</th>
                                            <th>Descripción</th>
                                            <th>IP</th>
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



    <!-- Modal Detalles -->

    <div class="modal fade" id="modalDetalles" tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">Detalles de Auditoría</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body" id="detallesContent">

                    <div class="row mb-3">

                        <div class="col-md-6">

                            <p><strong>Usuario:</strong> <span id="det-usuario"></span></p>

                            <p><strong>Acción:</strong> <span id="det-accion"></span></p>

                            <p><strong>Módulo:</strong> <span id="det-modulo"></span></p>

                        </div>

                        <div class="col-md-6">

                            <p><strong>Fecha/Hora:</strong> <span id="det-fecha"></span></p>

                            <p><strong>IP:</strong> <span id="det-ip"></span></p>

                            <p><strong>Navegador:</strong> <span id="det-navegador"></span></p>

                        </div>

                    </div>

                    <hr>

                    <div class="mb-3">

                        <h6>Descripción:</h6>

                        <p id="det-descripcion"></p>

                    </div>

                    <div class="mb-3" id="contenedor-datos-anteriores" style="display:none;">

                        <h6>Datos Anteriores:</h6>

                        <div class="json-viewer" id="det-datos-anteriores"></div>

                    </div>

                    <div class="mb-3" id="contenedor-datos-nuevos" style="display:none;">

                        <h6>Datos Nuevos:</h6>

                        <div class="json-viewer" id="det-datos-nuevos"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="src/assets/js/dark-mode.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://unpkg.com/typeit/dist/index.umd.js"></script>

    <script>

        new TypeIt("#myElement", {

            strings: "Auditoría del Sistema",

            speed: 80,

            waitUntilVisible: true,

            cursor: false

        }).go();

    </script>



    <script>

        let tabla;



        $(document).ready(function() {

            cargarEstadisticas();

            inicializarTabla();

        });



        function cargarEstadisticas() {

            $.get('api/auditoria.php?estadisticas=1', function(data) {

                $('#stat-total').text(data.total.toLocaleString());

                $('#stat-24h').text(data.ultimas_24h.toLocaleString());

                $('#stat-usuarios').text(data.por_usuario.length);

                $('#stat-modulos').text(data.por_modulo.length);

            });

        }



        function inicializarTabla() {

            tabla = $('#tablaAuditoria').DataTable({

                ajax: {

                    url: 'api/auditoria.php',

                    dataSrc: ''

                },

                columns: [{

                        data: 'id'

                    },

                    {

                        data: 'created_at',

                        render: function(data) {

                            const fecha = new Date(data);

                            return fecha.toLocaleString('es-MX');

                        }

                    },

                    {

                        data: 'usuario_nombre'

                    },

                    {

                        data: 'accion',

                        render: function(data) {

                            const badges = {

                                'LOGIN': 'bg-info bg-opacity-10 text-info border-0',

                                'LOGOUT': 'bg-secondary bg-opacity-10 text-secondary border-0',

                                'INSERT': 'bg-success bg-opacity-10 text-success border-0',

                                'UPDATE': 'bg-warning bg-opacity-10 text-warning border-0',

                                'DELETE': 'bg-danger bg-opacity-10 text-danger border-0'

                            };

                            return `<span class="badge ${badges[data] || 'bg-dark'} badge-action">${data}</span>`;

                        }

                    },

                    {

                        data: 'modulo'

                    },

                    {

                        data: 'descripcion',

                        render: function(data) {

                            if (!data) return '-';

                            return data.length > 50 ? data.substring(0, 50) + '...' : data;

                        }

                    },

                    {

                        data: 'ip_address'

                    },

                    {

                        data: null,

                        render: function(data, type, row) {

                            return `<button class="badge bg-primary bg-opacity-10 text-primary border-0" onclick="verDetalles(${row.id})" style="cursor:pointer; font-size:0.75rem;">

                                <i class="ti ti-eye"></i> Ver

                            </button>`;

                        }

                    }

                ],

                order: [

                    [1, 'desc']

                ],

                language: {

                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'

                },

                pageLength: 25

            });

        }



        function aplicarFiltros() {

            const params = new URLSearchParams($('#formFiltros').serialize());

            tabla.ajax.url('api/auditoria.php?' + params.toString()).load();

        }



        function verDetalles(id) {

            $.get(`api/auditoria.php?id=${id}`, function(data) {

                $('#det-usuario').text(data.usuario_nombre || 'Desconocido');

                $('#det-accion').html(`<span class="badge bg-primary bg-opacity-10 text-primary border-0">${data.accion}</span>`);

                $('#det-modulo').text(data.modulo);

                $('#det-fecha').text(new Date(data.created_at).toLocaleString('es-MX'));

                $('#det-ip').text(data.ip_address || '-');

                $('#det-navegador').text(data.user_agent || '-');

                $('#det-descripcion').text(data.descripcion || 'Sin descripción');



                // Datos anteriores

                if (data.datos_anteriores) {

                    try {

                        const anterior = typeof data.datos_anteriores === 'string' ?

                            JSON.parse(data.datos_anteriores) :

                            data.datos_anteriores;

                        $('#det-datos-anteriores').html('<pre>' + JSON.stringify(anterior, null, 2) + '</pre>');

                        $('#contenedor-datos-anteriores').show();

                    } catch (e) {

                        $('#contenedor-datos-anteriores').hide();

                    }

                } else {

                    $('#contenedor-datos-anteriores').hide();

                }



                // Datos nuevos

                if (data.datos_nuevos) {

                    try {

                        const nuevo = typeof data.datos_nuevos === 'string' ?

                            JSON.parse(data.datos_nuevos) :

                            data.datos_nuevos;

                        $('#det-datos-nuevos').html('<pre>' + JSON.stringify(nuevo, null, 2) + '</pre>');

                        $('#contenedor-datos-nuevos').show();

                    } catch (e) {

                        $('#contenedor-datos-nuevos').hide();

                    }

                } else {

                    $('#contenedor-datos-nuevos').hide();

                }



                $('#modalDetalles').modal('show');

            });

        }

    </script>

</body>



</html>
