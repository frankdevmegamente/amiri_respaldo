<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

// Prevenir cache del navegador
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$pdo = getPDO();

// Auto-crear tabla
$pdo->exec("CREATE TABLE IF NOT EXISTS directorio_depositos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    banco VARCHAR(100),
    cuenta VARCHAR(50),
    clabe VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
try { $pdo->exec("ALTER TABLE directorio_depositos ADD COLUMN estado VARCHAR(20) DEFAULT 'activo'"); } catch (PDOException $e) {}

// KPIs
$total = $pdo->query("SELECT COUNT(*) FROM directorio_depositos WHERE estado='activo'")->fetchColumn();
$inactivos = $pdo->query("SELECT COUNT(*) FROM directorio_depositos WHERE estado='inactivo'")->fetchColumn();
$bancos = $pdo->query("SELECT COUNT(DISTINCT banco) FROM directorio_depositos WHERE estado='activo'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Directorio de Transferencias - Amiri</title>
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
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .card { background: var(--nx-surface) !important; border-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: var(--nx-surface) !important; color: #f5f2f4 !important; border-bottom-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #f5f2f4 !important; border-color: rgba(255,255,255,0.12) !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length, [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter, [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info, [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #9a9096 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current, [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #1a181c !important; border-color: rgba(255,255,255,0.12) !important; color: #f5f2f4 !important; }
        [data-bs-theme="dark"] .dataTables_wrapper select, [data-bs-theme="dark"] .dataTables_wrapper input { background-color: #1a181c; border: 1px solid rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .modal-content { background: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-footer { border-top-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .modal-title { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-label { color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #1a181c; border-color: rgba(255,255,255,0.12); color: #f5f2f4; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #9a9096; }
        [data-bs-theme="dark"] .bg-light { background-color: var(--nx-surface-2) !important; }
        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }
        .directorio-header { position: relative; overflow: hidden; border-radius: 16px !important; }
        .directorio-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
        .directorio-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
        .directorio-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .directorio-header .sub-badge { font-size: 1rem; color: #9a9096; }
        .badge-soft-primary { background: rgba(93,135,255,0.1); color: var(--nx-primary); border: none; }
        .badge-soft-danger { background: rgba(220,53,69,0.1); color: #dc3545; border: none; }
        .badge-soft-success { background: rgba(25,135,84,0.1); color: #198754; border: none; }
        /* ─── Ticket ─────────────────────────────────── */
        .ticket-wrapper { background-color: #f4f6f9; padding: 20px; border-radius: 12px; display: flex; justify-content: center; }
        .ticket-card { background: var(--nx-surface); width: 100%; max-width: 580px; border-radius: 0; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); color: var(--nx-text); position: relative; overflow: hidden; }
        .ticket-deco { position: absolute; border-radius: 50%; pointer-events: none; }
        .ticket-deco-1 { top: -60px; right: -60px; width: 180px; height: 180px; background: var(--nx-primary); opacity: 0.08; }
        .ticket-deco-2 { bottom: -40px; left: -40px; width: 120px; height: 120px; background: var(--nx-success); opacity: 0.08; }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #c58fb8; position: relative; z-index: 1; }
        .ticket-logo { max-width: 50%; overflow: hidden; }
        .ticket-logo img { height: 45px; max-width: 100%; object-fit: contain; }
        .ticket-title { font-size: 18px; font-weight: 700; letter-spacing: 1.5px; color: #c58fb8; text-transform: uppercase; white-space: nowrap; margin-left: 15px; }
        .ticket-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px dashed #ebf1f6; position: relative; }
        .ticket-item-nombre { font-size: 15px; font-weight: 700; color: var(--nx-text); }
        .ticket-item-cuenta { font-size: 12px; color: #7c8fac; text-transform: uppercase; letter-spacing: 0.5px; }
        .ticket-item-monto { background: rgba(93,135,255,0.1); color: #c58fb8; padding: 8px 16px; border-radius: 6px; font-size: 16px; font-weight: 700; text-align: right; min-width: 130px; }
        .ticket-quitar { position: absolute; right: 0; top: 50%; transform: translate(120%, -50%); }
        .ticket-total { background: rgba(93,135,255,0.04); border-radius: 8px; padding: 20px; margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .ticket-total-label { color: #7c8fac; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .ticket-total-value { color: #36c76c; font-weight: 800; font-size: 26px; }
        [data-bs-theme="dark"] .ticket-wrapper { background-color: transparent !important; }
        [data-bs-theme="dark"] .ticket-card { background: #1a181c; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        [data-bs-theme="dark"] .ticket-header { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .ticket-item { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .ticket-item-nombre { color: #f5f2f4; }
        [data-bs-theme="dark"] .ticket-item-cuenta { color: #9a9096; }
        [data-bs-theme="dark"] .ticket-item-monto { background: rgba(93,135,255,0.15); color: #799dfb; }
        [data-bs-theme="dark"] .ticket-total { background-color: #1a181c !important; }
        [data-bs-theme="dark"] #placeholderTicket { color: #9a9096 !important; }
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
                    <!-- PAGE HEADER -->
                    <div class="card directorio-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-database text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Directorio de Transferencias</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> CRUD de clientes para transferencias</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" id="btnNuevo">
                                            <i class="ti ti-plus"></i> Nuevo Cliente
                                        </button>
                                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUpload">
                                            <i class="ti ti-upload"></i> Subir Excel
                                        </button>
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTicket" id="btnVerTicket">
                                            <i class="ti ti-receipt"></i> Ticket
                                            <span class="badge bg-white text-success ms-1" id="ticketCount">0</span>
                                            <span class="ms-2" id="ticketTotalBadge">$0.00</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI CARDS -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total Activos</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="ti ti-users"></i></div>
                                    </div>
                                    <div class="metric-value text-primary"><?= $total ?></div>
                                    <div class="metric-footer">En el directorio</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Bancos</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success"><i class="ti ti-building-bank"></i></div>
                                    </div>
                                    <div class="metric-value text-success"><?= $bancos ?></div>
                                    <div class="metric-footer">Diferentes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Inactivos</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="ti ti-user-off"></i></div>
                                    </div>
                                    <div class="metric-value text-warning"><?= $inactivos ?></div>
                                    <div class="metric-footer">Eliminados (soft)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info"><i class="ti ti-database"></i></div>
                                    </div>
                                    <div class="metric-value text-info"><?= $total + $inactivos ?></div>
                                    <div class="metric-footer">Registrados</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TABLA -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaDirectorio" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Banco</th>
                                        <th>Cuenta</th>
                                        <th>CLABE</th>
                                        <th style="width:150px;">Monto a transferir</th>
                                        <th class="text-center" style="width:190px;">Acciones</th>
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
    <!-- MODAL -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo"><i class="ti ti-user-plus"></i> Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCliente">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="cuenta_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required placeholder="Ej. Juan Perez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Banco</label>
                            <input type="text" class="form-control" name="banco" id="banco" placeholder="Ej. BBVA">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cuenta</label>
                            <input type="text" class="form-control" name="cuenta" id="cuenta" placeholder="Ej. 1234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CLABE</label>
                            <input type="text" class="form-control" name="clabe" id="clabe" placeholder="Ej. 012345678901234567">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>
    <!-- Modal Ticket (vista previa) -->
    <div class="modal fade" id="modalTicket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-receipt"></i> Ticket de Transferencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ticket-wrapper">
                        <div class="ticket-card" id="vistaPreviaTicket">
                            <div class="ticket-deco ticket-deco-1"></div>
                            <div class="ticket-deco ticket-deco-2"></div>
                            <div class="ticket-header">
                                <div class="ticket-logo">
                                    <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro" />
                                    <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="display:none;" />
                                </div>
                                <div class="ticket-title">TRANSFERENCIAS</div>
                            </div>
                            <div id="ticketItemsContainer">
                                <div class="text-center text-muted py-4" id="placeholderTicket">
                                    <i class="ti ti-receipt" style="font-size:3rem;opacity:0.3;"></i><br>
                                    El ticket está vacío. Agrega montos y pulsa <strong>➕ Ticket</strong> en la tabla.
                                </div>
                            </div>
                            <div class="ticket-total" id="contenedorTotal" style="display:none;">
                                <div class="ticket-total-label">TOTAL TRANSFERENCIAS</div>
                                <div class="ticket-total-value" id="outTotal">$0.00 MXN</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="btnLimpiarTicket">
                        <i class="ti ti-trash"></i> Limpiar Ticket
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="btnDescargar">
                        <i class="ti ti-download"></i> Descargar Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Upload Excel -->
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formSubirExcel">
                    <div class="modal-header">
                        <h5 class="modal-title">Subir Machote de Clientes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Sube tu archivo de Excel llenado <strong>(.xlsx)</strong>.</p>
                        <input class="form-control" type="file" id="archivo_excel" accept=".xlsx" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnProcesarExcel">Subir y Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script>
        let tablaDirectorio;
        const montosTicket = {};   // { idCliente: monto }
        $(document).ready(function() {
            tablaDirectorio = $('#tablaDirectorio').DataTable({
                ajax: { url: 'api/directorio-depositos.php', dataSrc: '' },
                columns: [
                    { data: 'id', render: d => '<span class="badge bg-secondary bg-opacity-10 text-secondary">#'+d+'</span>' },
                    { data: 'nombre', render: d => '<strong>'+d+'</strong>' },
                    { data: 'banco', render: d => d ? '<span class="badge bg-primary bg-opacity-10 text-primary border-0">'+d+'</span>' : '<span class="text-muted">—</span>' },
                    { data: 'cuenta', render: d => d ? '<span class="no-capitalize">'+d+'</span>' : '<span class="text-muted">—</span>' },
                    { data: 'clabe', render: d => d ? '<small class="font-monospace">'+d+'</small>' : '<span class="text-muted">—</span>' },
                    {
                        data: null, orderable: false, searchable: false,
                        render: d => '<div class="input-group input-group-sm">' +
                                    '<span class="input-group-text">$</span>' +
                                    '<input type="number" step="0.01" min="0" class="form-control input-monto-ticket" ' +
                                    'placeholder="0.00" data-id="' + d.id + '">' +
                                    '</div>'
                    },
                    { data: null, orderable: false, searchable: false, className: 'text-center', render: d => `
                        <button class="btn btn-sm btn-success me-1 btn-agregar-ticket" data-id="${d.id}" title="Agregar al ticket"><i class="ti ti-plus"></i></button>
                        <button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="${d.id}" title="Editar"><i class="ti ti-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${d.id}" title="Eliminar"><i class="ti ti-trash"></i></button>
                    ` }
                ],
                order: [[1, 'asc']],
                createdRow: function(row, data) {
                    $(row).attr('data-id', data.id).attr('data-nombre', data.nombre);
                },
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
            // Al redibujar la tabla (paginación, búsqueda, recarga) restaurar los montos capturados
            // y refrescar el ticket
            $('#tablaDirectorio').on('draw', function() {
                $('#tablaDirectorio tbody tr').each(function() {
                    const id = $(this).attr('data-id');
                    if (id && montosTicket[id] != null) {
                        $(this).find('.input-monto-ticket').val(montosTicket[id]);
                    }
                });
                refrescarTicket();
            });
        });
        // Nuevo
        $('#btnNuevo').on('click', function() {
            $('#formCliente')[0].reset();
            $('#cuenta_id').val('');
            $('#modalTitulo').html('<i class="ti ti-user-plus"></i> Nuevo Cliente');
        });
        // Editar
        $(document).on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.get('api/directorio-depositos.php?id=' + id, function(d) {
                $('#cuenta_id').val(d.id);
                $('#nombre').val(d.nombre);
                $('#banco').val(d.banco);
                $('#cuenta').val(d.cuenta);
                $('#clabe').val(d.clabe);
                $('#modalTitulo').html('<i class="ti ti-pencil"></i> Editar Cliente');
                $('#modalCliente').modal('show');
            });
        });
        // Guardar
        $('#formCliente').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/directorio-depositos.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#modalCliente').modal('hide');
                        tablaDirectorio.ajax.reload();
                        showToast('Cliente guardado', 'success');
                    } else {
                        showToast(res.error || 'Error', 'error');
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.error || 'Error', 'error');
                }
            });
        });
        // Eliminar
        $(document).on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            const nombre = $(this).closest('tr').attr('data-nombre') || '';
            if (!confirm('¿Eliminar "' + nombre + '"?')) return;
            $.ajax({
                url: 'api/directorio-depositos.php?id=' + id,
                method: 'DELETE',
                success: function(res) {
                    if (res.success) {
                        tablaDirectorio.ajax.reload();
                        showToast('Eliminado', 'success');
                    } else {
                        showToast(res.error || 'Error', 'error');
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.error || 'Error', 'error');
                }
            });
        });
        // ═══════════════════════════════════════════════
        //  TICKET INTEGRADO EN LA TABLA CRUD
        //  - Columna "Monto a transferir" con input por fila
        //  - Botón ➕ por fila que agrega ese cliente al ticket
        //  - Vista previa en modal
        // ═══════════════════════════════════════════════
        const itemsTicket = [];   // { id, nombre, banco, clabe, monto }
        const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

        function totalTicket() {
            return itemsTicket.reduce((sum, it) => sum + it.monto, 0);
        }
        function refrescarTicket() {
            // Badge del botón en el header
            $('#ticketCount').text(itemsTicket.length);
            $('#ticketTotalBadge').text(formatoMoneda.format(totalTicket()));
            // Vista previa dentro del modal
            const cont = $('#ticketItemsContainer');
            cont.empty();
            if (!itemsTicket.length) {
                cont.append(
                    $('<div>', { class: 'text-center text-muted py-4', id: 'placeholderTicket' })
                        .append($('<i>', { class: 'ti ti-receipt', style: 'font-size:3rem;opacity:0.3;' }))
                        .append($('<br>'))
                        .append(document.createTextNode('El ticket está vacío. Agrega montos y pulsa ➕ Ticket en la tabla.'))
                );
                $('#contenedorTotal').hide();
                return;
            }
            itemsTicket.forEach(it => {
                const colorBanco = getColorBanco(it.banco);
                const $item = $('<div>', { class: 'ticket-item', 'data-id': it.id });
                $('<div>').append(
                    $('<div>', { class: 'ticket-item-nombre', text: it.nombre.toUpperCase() }),
                    $('<div>', { class: 'ticket-item-cuenta' }).append(
                        document.createTextNode('CLABE: ' + (it.clabe || 'SIN CLABE REGISTRADA') + ' - '),
                        $('<span>', { style: 'font-weight:700;color:' + colorBanco, text: (it.banco || '').toUpperCase() })
                    )
                ).appendTo($item);
                $('<div>', { class: 'ticket-item-monto', text: formatoMoneda.format(it.monto) }).appendTo($item);
                $('<button>', { class: 'btn btn-sm text-danger ticket-quitar btn-quitar-item', 'data-id': it.id, title: 'Quitar del ticket' })
                    .append($('<i>', { class: 'ti ti-x' })).appendTo($item);
                cont.append($item);
            });
            $('#contenedorTotal').show();
            $('#outTotal').text(formatoMoneda.format(totalTicket()) + ' MXN');
        }
        // Capturar el monto escrito en la fila
        $('#tablaDirectorio').on('input', '.input-monto-ticket', function() {
            const id = $(this).data('id');
            const v = parseFloat($(this).val());
            if (v > 0) montosTicket[id] = v;
            else delete montosTicket[id];
        });
        // Agregar al ticket desde la fila
        $(document).on('click', '.btn-agregar-ticket', function() {
            const id = String($(this).data('id'));
            const $fila = $(this).closest('tr');
            const input = $fila.find('.input-monto-ticket');
            const monto = parseFloat(input.val());
            if (!monto || monto <= 0) {
                showToast('Escribe el monto a transferir en la fila.', 'error');
                input.trigger('focus');
                return;
            }
            const cliente = tablaDirectorio.rows().data().toArray().find(c => String(c.id) === id);
            if (!cliente) { showToast('No se encontró el cliente.', 'error'); return; }
            const item = {
                id: id,
                nombre: cliente.nombre,
                banco: cliente.banco || '',
                clabe: cliente.clabe || '',
                monto: monto
            };
            const existente = itemsTicket.findIndex(it => it.id === id);
            if (existente >= 0) itemsTicket[existente] = item;
            else itemsTicket.push(item);
            montosTicket[id] = monto;
            refrescarTicket();
            showToast((existente >= 0 ? 'Actualizado' : 'Agregado') + ': ' + cliente.nombre, 'success');
        });
        // Quitar del ticket
        $('#ticketItemsContainer').on('click', '.btn-quitar-item', function() {
            const id = String($(this).data('id'));
            const idx = itemsTicket.findIndex(it => it.id === id);
            if (idx >= 0) itemsTicket.splice(idx, 1);
            refrescarTicket();
        });
        // Limpiar ticket
        $('#btnLimpiarTicket').on('click', function() {
            if (!itemsTicket.length) { showToast('El ticket ya está vacío.', 'warning'); return; }
            if (!confirm('¿Limpiar el ticket? Se quitarán todos los clientes agregados.')) return;
            itemsTicket.length = 0;
            Object.keys(montosTicket).forEach(k => delete montosTicket[k]);
            $('#tablaDirectorio tbody .input-monto-ticket').val('');
            refrescarTicket();
            showToast('Ticket limpiado', 'success');
        });
        // Descargar ticket (PNG)
        $('#btnDescargar').on('click', function() {
            if (!itemsTicket.length) { showToast('Agrega al menos un cliente al ticket antes de descargar.', 'error'); return; }
            const ticket = document.getElementById('vistaPreviaTicket');
            const btn = $(this);
            const textoOriginal = btn.html();
            $('.ticket-quitar').hide();
            btn.html('<i class="ti ti-loader ti-spin"></i> Generando...');
            btn.prop('disabled', true);
            const originalMaxWidth = ticket.style.maxWidth;
            const originalWidth = ticket.style.width;
            ticket.style.maxWidth = 'none';
            ticket.style.width = '580px';
            html2canvas(ticket, { backgroundColor: null, scale: 3, logging: false }).then(canvas => {
                const enlace = document.createElement('a');
                enlace.download = 'Deposito_Masivo_Amiri_' + new Date().toISOString().slice(0,10) + '.png';
                enlace.href = canvas.toDataURL('image/png');
                enlace.click();
                ticket.style.maxWidth = originalMaxWidth;
                ticket.style.width = originalWidth;
                $('.ticket-quitar').show();
                btn.html(textoOriginal).prop('disabled', false);
                showToast('¡Ticket generado con éxito!', 'success');
            }).catch(err => {
                console.error(err);
                ticket.style.maxWidth = originalMaxWidth;
                ticket.style.width = originalWidth;
                $('.ticket-quitar').show();
                btn.html(textoOriginal).prop('disabled', false);
                showToast('Hubo un error al generar la imagen', 'error');
            });
        });
        // Subir Excel (importación masiva)
        document.getElementById('formSubirExcel').addEventListener('submit', function(e) {
            e.preventDefault();
            const file = document.getElementById('archivo_excel').files[0];
            if (!file) return;
            const btn = document.getElementById('btnProcesarExcel');
            btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Procesando...';
            btn.disabled = true;
            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const filas = XLSX.utils.sheet_to_json(workbook.Sheets[workbook.SheetNames[0]], { header: 1 });
                filas.shift();
                $.ajax({
                    url: 'api/procesar-excel-depositos.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(filas),
                    success: function(response) {
                        if (response.success) { location.reload(); }
                        else { showToast('Error: ' + response.error, 'error'); btn.innerHTML = 'Subir y Guardar'; btn.disabled = false; }
                    },
                    error: function() {
                        showToast('Error al procesar el archivo', 'error');
                        btn.innerHTML = 'Subir y Guardar';
                        btn.disabled = false;
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        });
        // Color por banco
        function getColorBanco(bancoNombre) {
            const b = (bancoNombre || '').toLowerCase().trim();
            if (b.includes('bbva') || b.includes('bancomer')) return '#004481';
            if (b.includes('santander')) return '#ec0000';
            if (b.includes('banorte')) return '#eb0029';
            if (b.includes('hsbc')) return '#db0011';
            if (b.includes('banamex') || b.includes('citi')) return '#056dae';
            if (b.includes('scotia')) return '#ed1b24';
            if (b.includes('inbursa')) return '#00508c';
            if (b.includes('azteca')) return '#00a22d';
            if (b.includes('coppel') || b.includes('bancoppel')) return '#005da3';
            if (b.includes('bajio') || b.includes('banbajio')) return '#fbb700';
            if (b.includes('afirme')) return '#00973a';
            if (b.includes('banregio')) return '#eb6f00';
            if (b.includes('mifel')) return '#005a8c';
            if (b.includes('invex')) return '#002f6c';
            if (b.includes('multiva')) return '#003865';
            if (b.includes('banjercito')) return '#12322b';
            if (b.includes('bienestar') || b.includes('bansefi')) return '#611232';
            if (b.includes('nu') || b.includes('nubank')) return '#8a05be';
            if (b.includes('plata')) return '#1a1a1a';
            if (b.includes('mercado') || b.includes('mercadopago')) return '#009ee3';
            if (b.includes('spin') || b.includes('oxxo')) return '#ff6e00';
            if (b.includes('hey') || b.includes('heybanco')) return '#000000';
            if (b.includes('stori')) return '#00c08b';
            if (b.includes('klar')) return '#000000';
            if (b.includes('uala')) return '#f2385a';
            if (b.includes('fondeadora')) return '#121212';
            if (b.includes('albo')) return '#002f87';
            if (b.includes('broxel')) return '#00a2d3';
            if (b.includes('rappi') || b.includes('rappipay')) return '#ff4600';
            if (b.includes('bineo')) return '#ff0066';
            if (b.includes('cetes')) return '#104a3e';
            return '#c58fb8';
        }
    </script>
</body>
</html>
