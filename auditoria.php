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

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Auditoría - Sistema Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .auditoria-header {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
        }
        .auditoria-header .bg-pattern {
            position: absolute; top: -50%; right: -10%;
            width: 400px; height: 400px; border-radius: 50%;
            background: rgba(93, 135, 255, 0.08); pointer-events: none;
        }
        .auditoria-header .bg-pattern-2 {
            position: absolute; bottom: -30%; left: 20%;
            width: 250px; height: 250px; border-radius: 50%;
            background: rgba(19, 222, 185, 0.06); pointer-events: none;
        }
        .auditoria-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .auditoria-header .sub-badge { font-size: 1rem; color: #9a9096; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card {
            position: relative;
            border-radius: 12px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }

        /* ─── JSON Viewer ─────────────────────────────── */
        .json-viewer {
            background: var(--nx-surface-2) !important;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 300px;
            overflow-y: auto;
        }

        /* ─── Dark mode ───────────────────────────────── */
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .card { background: var(--nx-surface) !important; border-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: var(--nx-surface) !important; color: #f5f2f4 !important; border-bottom-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #f5f2f4 !important; border-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05); }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #1a181c !important; border-color: rgba(255,255,255,0.12) !important; color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select, [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #1a181c; border: 1px solid rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .json-viewer { background: #1a181c; border-color: #2e3a52; color: #e0e0e0; }
        [data-bs-theme="dark"] .modal-content { background: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-footer { border-top-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-title { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-label { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #1a181c; border-color: rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #9a9096; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
    </style>

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    <div class="card auditoria-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-shield-check text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Auditoría</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Monitoreo de actividades del sistema</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success btn-sm" onclick="exportarExcel()">
                                            <i class="ti ti-file-spreadsheet"></i> Excel
                                        </button>
                                        <button class="btn btn-outline-info btn-sm" onclick="exportarCSV()">
                                            <i class="ti ti-file-text"></i> CSV
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="limpiarFiltros()">
                                            <i class="ti ti-x"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4" id="estadisticas">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-database"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="stat-total">0</div>
                                    <div class="metric-footer">Registros en el sistema</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Últimas 24h</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-clock"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="stat-24h">0</div>
                                    <div class="metric-footer">Actividades recientes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Usuarios</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-users"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="stat-usuarios">0</div>
                                    <div class="metric-footer">Con actividad registrada</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Módulos</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="ti ti-apps"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-primary" id="stat-modulos">0</div>
                                    <div class="metric-footer">Módulos activos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card filter-card shadow-sm border-0">
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
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaAuditoria" class="table table-hover align-middle w-100">
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

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="src/assets/js/pantalla-completa.js"></script>

    <script src="src/assets/js/utils.js"></script>



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

        function exportarCSV() {
            var data = tabla.rows({ filter: 'applied' }).data();
            if (!data.length) { alert('No hay datos para exportar'); return; }
            var keys = Object.keys(data[0]);
            var csv = keys.map(function(k) { return '"' + k + '"'; }).join(',');
            data.each(function(row) {
                csv += '\n' + keys.map(function(k) {
                    var v = (row[k] || '').toString().replace(/"/g, '""');
                    return '"' + v + '"';
                }).join(',');
            });
            var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'auditoria_' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
        }

        function exportarExcel() {
            var data = tabla.rows({ filter: 'applied' }).data();
            if (!data.length) { alert('No hay datos para exportar'); return; }
            var keys = Object.keys(data[0]);
            var html = '<table><thead><tr>' + keys.map(function(k) { return '<th>' + k + '</th>'; }).join('') + '</tr></thead><tbody>';
            data.each(function(row) {
                html += '<tr>' + keys.map(function(k) { return '<td>' + (row[k] || '') + '</td>'; }).join('') + '</tr>';
            });
            html += '</tbody></table>';
            var blob = new Blob(['\uFEFF' + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'auditoria_' + new Date().toISOString().slice(0,10) + '.xls';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
        }

        function limpiarFiltros() {
            $('#filtroUsuario, #filtroAccion, #filtroModulo').val('');
            $('#filtroFechaInicio, #filtroFechaFin').val('');
            tabla.ajax.url('api/auditoria.php').load();
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

                        render: function(data, type) {
                            if (type === 'sort' || type === 'type') return data;
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

            var params = [];
            $('#filtroUsuario, #filtroAccion, #filtroModulo, #filtroFechaInicio, #filtroFechaFin').each(function() {
                if ($(this).val()) params.push($(this).attr('name') + '=' + encodeURIComponent($(this).val()));
            });
            tabla.ajax.url('api/auditoria.php' + (params.length ? '?' + params.join('&') : '')).load();

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

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

</body>



</html>
