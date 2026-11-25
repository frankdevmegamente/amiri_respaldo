<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

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

// Obtener clientes activos
$stmt = $pdo->query("SELECT * FROM directorio_depositos WHERE estado = 'activo' ORDER BY nombre ASC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extraer lista de bancos únicos
$bancos_unicos = [];
foreach($clientes as $c) {
    $b = trim($c['banco'] ?? '');
    if($b !== '' && !in_array($b, $bancos_unicos)) $bancos_unicos[] = $b;
}
sort($bancos_unicos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CRUD Transferencias - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        .crud-header { background: var(--nx-accent-gradient); border-radius: 16px; padding: 24px; color: white; margin-bottom: 20px; }
        .ticket-wrapper { background-color: var(--nx-surface-2); padding: 40px; border-radius: var(--nx-radius); display: flex; justify-content: center; }
        .ticket-card { background: var(--nx-surface); width: 100%; max-width: 580px; border-radius: var(--nx-radius); padding: 35px; box-shadow: var(--nx-shadow); color: var(--nx-text); font-family: var(--nx-font); border: 1px solid var(--nx-border); }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid var(--nx-primary); }
        .ticket-title { font-size: 18px; font-weight: 700; letter-spacing: 1.5px; color: var(--nx-primary); text-transform: uppercase; white-space: nowrap; }
        .client-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px dashed var(--nx-border); }
        .client-info { display: flex; flex-direction: column; gap: 4px; }
        .client-name { font-size: 15px; font-weight: 700; color: var(--nx-text); }
        .client-account { font-size: 12px; color: var(--nx-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .client-amount { background-color: var(--nx-primary-soft); color: var(--nx-primary); padding: 8px 16px; border-radius: 6px; font-size: 16px; font-weight: 700; text-align: right; min-width: 130px; }
        .ticket-row.total-row { background: rgba(197, 143, 184, 0.04); border: none; border-radius: 8px; padding: 20px; margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .text-muted-custom { color: var(--nx-text-muted); font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .text-success-custom { color: var(--nx-success); }
        .select2-container--default .select2-selection--single { height: 40px; border: 1px solid var(--nx-border); border-radius: 6px; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
        .btn-crud { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }
        [data-bs-theme="dark"] .ticket-wrapper { background-color: transparent !important; }
        [data-bs-theme="dark"] .ticket-card { background: var(--nx-surface-2); border-color: var(--nx-border); box-shadow: var(--nx-shadow); }
        [data-bs-theme="dark"] .ticket-header { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .client-name { color: var(--nx-text); }
        [data-bs-theme="dark"] .client-account { color: #9a9096; }
        [data-bs-theme="dark"] .client-row { border-bottom-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .client-amount { background-color: rgba(197, 143, 184, 0.15); color: var(--nx-primary); }
        [data-bs-theme="dark"] #placeholderTicket, [data-bs-theme="dark"] .text-muted-custom { color: #9a9096 !important; }
        [data-bs-theme="dark"] .ticket-row.total-row { background-color: #1a181c !important; }
        [data-bs-theme="dark"] .select2-container--default .select2-selection--single { background-color: var(--nx-surface-2); border-color: var(--nx-border); }
        [data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--nx-text); }
        [data-bs-theme="dark"] .select2-dropdown { background-color: var(--nx-surface-2); border-color: var(--nx-border); }
        [data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field { background-color: var(--nx-surface-2); border-color: var(--nx-border); color: var(--nx-text); }
        [data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true] { background-color: var(--nx-surface-2); color: var(--nx-text); }
        [data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--nx-primary); color: #0a0709; }
        @media (max-width: 768px) {
            .ticket-wrapper { padding: 15px 10px !important; }
            .ticket-card { padding: 20px 15px !important; }
            .client-amount { min-width: auto !important; font-size: 14px !important; padding: 6px 10px !important; }
            .client-name { font-size: 13px !important; }
            .client-account { font-size: 11px !important; }
        }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
                    <!-- Header CRUD -->
                    <div class="crud-header d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0 fw-bold"><i class="ti ti-database"></i> Gestión de Directorio de Transferencias</h3>
                            <small class="opacity-75">CRUD completo: Crear, Leer, Actualizar y Eliminar clientes del directorio</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalCliente" id="btnNuevoCliente">
                                <i class="ti ti-plus me-1"></i> Nuevo Cliente
                            </button>
                            <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalUpload">
                                <i class="ti ti-upload me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                    <!-- Tabla CRUD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body p-3">
                            <table id="tablaDirectorio" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Banco</th>
                                        <th>Cuenta</th>
                                        <th>CLABE</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($clientes as $c): ?>
                                    <tr data-id="<?= $c['id'] ?>">
                                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary">#<?= $c['id'] ?></span></td>
                                        <td class="fw-bold"><?= htmlspecialchars($c['nombre']) ?></td>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($c['banco']) ?></span></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($c['cuenta'] ?: '—') ?></small></td>
                                        <td><small class="font-monospace"><?= htmlspecialchars($c['clabe'] ?: '—') ?></small></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary btn-crud btn-editar-cliente me-1" title="Editar"
                                                data-id="<?= $c['id'] ?>" data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                                                data-banco="<?= htmlspecialchars($c['banco']) ?>" data-cuenta="<?= htmlspecialchars($c['cuenta']) ?>"
                                                data-clabe="<?= htmlspecialchars($c['clabe']) ?>">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-crud btn-eliminar-cliente" title="Eliminar"
                                                data-id="<?= $c['id'] ?>" data-nombre="<?= htmlspecialchars($c['nombre']) ?>">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Sección Ticket -->
                    <div class="row">
                        <div class="col-lg-5 col-md-5 mb-4">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold mb-4"><i class="ti ti-receipt"></i> Armar Ticket</h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-primary">1. Filtrar por Banco</label>
                                        <select class="form-select" id="filtroBanco">
                                            <option value="">-- Todos los bancos --</option>
                                            <?php foreach($bancos_unicos as $b): ?>
                                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">2. Buscar Cliente</label>
                                        <select class="form-control" id="selectCliente" style="width: 100%;"></select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">3. Monto a Transferir</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="text" step="0.01" class="form-control" id="inputMonto" placeholder="Ej. 15000.00">
                                        </div>
                                    </div>
                                    <button class="btn btn-success w-100 mb-3" id="btnAgregarTicket">
                                        <i class="ti ti-plus"></i> Agregar al Ticket
                                    </button>
                                    <button class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" id="btnDescargar">
                                        <i class="ti ti-download me-2"></i> Descargar Ticket
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 col-md-7">
                            <div class="ticket-wrapper">
                                <div class="ticket-card" id="vistaPreviaTicket" style="position: relative; overflow: hidden;">

                                    <div class="ticket-header" style="position: relative; z-index: 1;">
                                        <div class="d-flex align-items-center" style="max-width: 50%; overflow: hidden;">
                                            <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro" style="height: 45px; max-width: 100%; object-fit: contain;" />
                                            <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; max-width: 100%; object-fit: contain; display: none;" />
                                        </div>
                                        <div class="text-end" style="margin-left: 15px;">
                                            <div class="ticket-title">TRANSFERENCIAS</div>
                                        </div>
                                    </div>
                                    <div class="client-list" id="ticketItemsContainer">
                                        <div class="text-center text-muted py-4" id="placeholderTicket">
                                            <i class="ti ti-receipt fs-1 text-light"></i><br>
                                            El ticket está vacío. Agrega transferencias desde el panel izquierdo.
                                        </div>
                                    </div>
                                    <div class="ticket-row total-row" style="display: none;" id="contenedorTotal">
                                        <div>
                                            <div class="text-muted-custom mb-1" style="color: var(--nx-primary);">TOTAL TRANSFERENCIAS</div>
                                        </div>
                                        <div class="fs-large text-success-custom fw-bolder" id="outTotal" style="font-size: 26px;">$0.00 MXN</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Crear/Editar Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCliente">
                    <div class="modal-header" style="background: var(--nx-accent-gradient); color: white;">
                        <h5 class="modal-title" id="tituloModalCliente"><i class="ti ti-user-plus"></i> Nuevo Cliente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre" id="edit_nombre" required placeholder="Ej. Juan Perez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Banco</label>
                            <input type="text" class="form-control" name="banco" id="edit_banco" placeholder="Ej. BBVA">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cuenta</label>
                            <input type="text" class="form-control" name="cuenta" id="edit_cuenta" placeholder="Ej. 1234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CLABE</label>
                            <input type="text" class="form-control" name="clabe" id="edit_clabe" placeholder="Ej. 012345678901234567">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                            <i class="ti ti-device-floppy me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal: Subir Excel -->
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
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // === DataTable para directorio ===
        $(document).ready(function() {
            $('#tablaDirectorio').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }]
            });
        });
        // === CRUD: Editar ===
        $(document).on('click', '.btn-editar-cliente', function() {
            const d = $(this).data();
            $('#edit_id').val(d.id);
            $('#edit_nombre').val(d.nombre);
            $('#edit_banco').val(d.banco);
            $('#edit_cuenta').val(d.cuenta);
            $('#edit_clabe').val(d.clabe);
            $('#tituloModalCliente').html('<i class="ti ti-pencil"></i> Editar Cliente');
            $('#modalCliente').modal('show');
        });
        // === CRUD: Nuevo ===
        $('#btnNuevoCliente').on('click', function() {
            $('#formCliente')[0].reset();
            $('#edit_id').val('');
            $('#tituloModalCliente').html('<i class="ti ti-user-plus"></i> Nuevo Cliente');
        });
        // === CRUD: Guardar ===
        $('#formCliente').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnGuardarCliente');
            btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Guardando...');
            $.ajax({
                url: 'api/directorio-depositos.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        $('#modalCliente').modal('hide');
                        showToast('Cliente guardado correctamente', 'success');
                        setTimeout(() => { location.reload(); }, 800);
                    } else {
                        showToast('Error: ' + (res.error || 'Respuesta inválida'), 'error');
                        btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Guardar');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error al guardar';
                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}
                    showToast(msg, 'error');
                    btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Guardar');
                }
            });
        });
        // === CRUD: Eliminar ===
        $(document).on('click', '.btn-eliminar-cliente', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            if (!confirm('¿Eliminar cliente "' + nombre + '"?')) return;
            const btn = $(this);
            btn.prop('disabled', true);
            $.ajax({
                url: 'api/directorio-depositos.php?id=' + id,
                method: 'DELETE',
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        showToast('Cliente eliminado', 'success');
                        setTimeout(() => { location.reload(); }, 800);
                    } else {
                        showToast('Error: ' + (res.error || 'Desconocido'), 'error');
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    let msg = 'Error al eliminar';
                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}
                    showToast(msg, 'error');
                    btn.prop('disabled', false);
                }
            });
        });
        // === Ticket: filtro banco ===
        const todosLosClientes = <?php echo json_encode($clientes); ?>;
        function cargarBuscadorClientes(filtroBanco = '') {
            const select = $('#selectCliente');
            if (select.hasClass("select2-hidden-accessible")) select.select2('destroy');
            select.empty();
            select.append('<option value="">-- Escribe el nombre o selecciona --</option>');
            todosLosClientes.forEach(c => {
                const bancoDelCliente = c.banco ? c.banco.trim() : '';
                if (filtroBanco === '' || bancoDelCliente === filtroBanco) {
                    const clabeStr = c.clabe ? c.clabe : 'SIN CLABE REGISTRADA';
                    select.append(`<option value="${c.id}" data-nombre="${c.nombre}" data-banco="${bancoDelCliente}" data-clabe="${clabeStr}">${c.nombre} - ${bancoDelCliente}</option>`);
                }
            });
            select.select2({ width: '100%', language: { noResults: () => "No se encontraron clientes" } });
        }
        cargarBuscadorClientes('');
        $('#filtroBanco').on('change', function() { cargarBuscadorClientes($(this).val()); });
        // === Ticket: agregar ===
        const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
        let totalMxn = 0;
        let itemsEnTicket = 0;
        $('#btnAgregarTicket').on('click', function() {
            const select = $('#selectCliente').find(':selected');
            const montoRaw = $('#inputMonto').val();
            const monto = parseFloat(montoRaw);
            if (select.val() === "" || !select.val()) { showToast('Por favor, selecciona un cliente del directorio.', 'error'); return; }
            if (isNaN(monto) || monto <= 0) { showToast('Por favor, ingresa un monto válido.', 'error'); return; }
            const nombre = select.data('nombre');
            const banco = select.data('banco');
            const clabe = select.data('clabe');
            const colorBanco = getColorBanco(banco);
            if (itemsEnTicket === 0) { $('#placeholderTicket').hide(); $('#contenedorTotal').show(); }
            const uniqueId = 'item_' + Date.now();
            let itemHtml = `
                <div class="client-row position-relative" id="${uniqueId}">
                    <div class="client-info">
                        <div class="client-name">${nombre.toUpperCase()}</div>
                        <div class="client-account">CLABE: ${clabe} - <span class="fw-bolder" style="color: ${colorBanco};">${banco.toUpperCase()}</span></div>
                    </div>
                    <div class="client-amount">${formatoMoneda.format(monto)}</div>
                    <button class="btn btn-sm text-danger position-absolute end-0 top-0 btn-quitar-item" data-id="${uniqueId}" data-monto="${monto}" style="transform: translateX(120%);" title="Quitar del ticket">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;
            $('#ticketItemsContainer').append(itemHtml);
            totalMxn += monto;
            itemsEnTicket++;
            $('#outTotal').text(formatoMoneda.format(totalMxn) + ' MXN');
            $('#selectCliente').val('').trigger('change');
            $('#inputMonto').val('');
            showToast('Transferencia agregada', 'success');
        });
        // === Ticket: quitar ===
        $('#ticketItemsContainer').on('click', '.btn-quitar-item', function() {
            const id = $(this).data('id');
            const montoRestar = parseFloat($(this).data('monto'));
            $(`#${id}`).remove();
            totalMxn -= montoRestar;
            itemsEnTicket--;
            $('#outTotal').text(formatoMoneda.format(totalMxn) + ' MXN');
            if (itemsEnTicket === 0) { $('#placeholderTicket').show(); $('#contenedorTotal').hide(); }
        });
        // === Ticket: descargar ===
        $('#btnDescargar').on('click', function() {
            if (itemsEnTicket === 0) { showToast('Agrega al menos un cliente al ticket antes de descargar.', 'error'); return; }
            const ticket = document.getElementById('vistaPreviaTicket');
            const btn = $(this);
            const textoOriginal = btn.html();
            $('.btn-quitar-item').hide();
            btn.html('<i class="ti ti-loader ti-spin me-2"></i> Generando...');
            btn.prop('disabled', true);
            const originalMaxWidth = ticket.style.maxWidth;
            const originalWidth = ticket.style.width;
            ticket.style.maxWidth = 'none';
            ticket.style.width = '480px';
            html2canvas(ticket, {
                backgroundColor: null,
                scale: 3,
                logging: false,
                useCORS: true,
                onclone: function(doc) {
                    doc.body.classList.add('descargable-export');
                    const el = doc.getElementById('vistaPreviaTicket');
                    if (el) {
                        el.style.background = '#302a2e';
                        el.style.border = '1px solid #4a4045';
                        el.style.boxShadow = 'none';
                        el.style.borderRadius = '24px';
                        el.style.overflow = 'hidden';
                    }
                }
            }).then(canvas => {
                const enlace = document.createElement('a');
                const fechaTxt = new Date().toISOString().slice(0,10);
                enlace.download = `Deposito_Masivo_Amiri_${fechaTxt}.png`;
                enlace.href = canvas.toDataURL("image/png");
                enlace.click();
                ticket.style.maxWidth = originalMaxWidth;
                ticket.style.width = originalWidth;
                $('.btn-quitar-item').show();
                btn.html(textoOriginal).prop('disabled', false);
                showToast('¡Ticket generado con éxito!', 'success');
            }).catch(err => {
                console.error(err);
                ticket.style.maxWidth = originalMaxWidth;
                ticket.style.width = originalWidth;
                $('.btn-quitar-item').show();
                btn.html(textoOriginal).prop('disabled', false);
                showToast('Hubo un error al generar la imagen', 'error');
            });
        });
        // === Upload Excel ===
        document.getElementById('formSubirExcel').addEventListener('submit', function(e) {
            e.preventDefault();
            let file = document.getElementById('archivo_excel').files[0];
            if (!file) return;
            let btn = document.getElementById('btnProcesarExcel');
            btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Procesando...';
            btn.disabled = true;
            let reader = new FileReader();
            reader.onload = function(e) {
                let data = new Uint8Array(e.target.result);
                let workbook = XLSX.read(data, {type: 'array'});
                let primeraHoja = workbook.SheetNames[0];
                let filas = XLSX.utils.sheet_to_json(workbook.Sheets[primeraHoja], {header: 1});
                filas.shift();
                $.ajax({
                    url: 'api/procesar-excel-depositos.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(filas),
                    success: function(response) {
                        if(response.success) { location.reload(); }
                        else { alert("Error: " + response.error); btn.innerHTML = 'Subir y Guardar'; btn.disabled = false; }
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        });
        // === Color por banco ===
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
            if (b.includes('uala') || b.includes('ualá')) return '#f2385a';
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
