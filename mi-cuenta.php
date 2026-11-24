<?php
require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$rol = $_SESSION['usuario_rol'] ?? '';
// Solo promotores (y admins vinculados a un promotor) pueden ver esta página
$promotor_id = null;
if ($rol === 'promotor') {
    $promotor_id = isset($_SESSION['promotor_id']) ? (int)$_SESSION['promotor_id'] : null;
} elseif ($rol === 'admin' && !empty($_SESSION['promotor_id'])) {
    $promotor_id = (int)$_SESSION['promotor_id'];
} else {
    header('Location: index.php');
    exit;
}

if (!$promotor_id) {
    die("Error: cuenta de promotor no vinculada. Contacta al administrador.");
}

require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT p.*, cu.nombre as cuenta_nombre, cu.saldo_actual as cuenta_saldo
        FROM promotores p
        LEFT JOIN cuentas cu ON cu.id = p.cuenta_id
        WHERE p.id = ? AND p.activo = 1
    ");
    $stmt->execute([$promotor_id]);
    $promotor = $stmt->fetch();
    if (!$promotor) {
        die("Error: promotor no encontrado o inactivo.");
    }
} catch (Exception $e) {
    die("Error de conexión.");
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi Cuenta - <?= htmlspecialchars($promotor['nombre']) ?></title>
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
        [data-bs-theme="dark"] .card { background-color: var(--nx-surface) !important; border-color: var(--nx-border) !important; color: var(--nx-text) !important; }
        [data-bs-theme="dark"] .table, [data-bs-theme="dark"] .table thead th, [data-bs-theme="dark"] .table tbody td { background-color: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: var(--nx-surface-2) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
        [data-bs-theme="dark"] .modal-content { background-color: var(--nx-surface) !important; border-color: var(--nx-border) !important; color: var(--nx-text) !important; }
        .perfil-avatar { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; background: rgba(93,135,255,.12); color: var(--nx-primary); }
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

                    <!-- Perfil -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md d-flex align-items-center gap-3">
                                    <div class="perfil-avatar"><?= strtoupper(htmlspecialchars(mb_substr($promotor['nombre'], 0, 1))) ?></div>
                                    <div>
                                        <h3 class="mb-0"><i class="ti ti-user"></i> <?= htmlspecialchars($promotor['nombre']) ?></h3>
                                        <div class="text-muted small">
                                            <span id="perfilTelefono"><?= htmlspecialchars($promotor['telefono'] ?? 'Sin teléfono') ?></span>
                                            · <span id="perfilEmail"><?= htmlspecialchars($promotor['email'] ?? '') ?></span>
                                        </div>
                                        <div class="mt-1">
                                            <span class="badge bg-info bg-opacity-10 text-info border-0">
                                                <i class="ti ti-wallet"></i> <?= htmlspecialchars($promotor['cuenta_nombre'] ?? 'Sin cuenta') ?>
                                                · <span id="perfilSaldo">$<?= number_format((float)($promotor['cuenta_saldo'] ?? 0), 2) ?></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto d-flex gap-2">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPerfil">
                                        <i class="ti ti-pencil"></i> Editar perfil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-end g-2">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Período:</label>
                                    <select class="form-select" id="selectPeriodo">
                                        <option value="todo" selected>Todo el Historial</option>
                                        <option value="mes">Este Mes</option>
                                        <option value="mes_pasado">Mes Pasado</option>
                                        <option value="anio">Este Año</option>
                                        <option value="rango">Fecha Personalizada</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="fechaInicioGroup" style="display:none;">
                                    <label class="form-label">Desde</label>
                                    <input type="date" class="form-control" id="fechaInicio">
                                </div>
                                <div class="col-md-2" id="fechaFinGroup" style="display:none;">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-control" id="fechaFin" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" id="btnFiltrar"><i class="ti ti-filter"></i> Aplicar</button>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger w-100" id="btnGenerarPDF"><i class="ti ti-file-text"></i> PDF</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="row mb-4 g-3">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-success border-4 h-100">
                                <div class="card-body">
                                    <p class="text-muted mb-0 fw-semibold small text-uppercase">Comisiones generadas</p>
                                    <h4 class="text-success fw-bolder mb-0" id="comisionesTotal">$0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-primary border-4 h-100">
                                <div class="card-body">
                                    <p class="text-muted mb-0 fw-semibold small text-uppercase">Total pagado</p>
                                    <h4 class="text-primary fw-bolder mb-0" id="comisionesPagadas">$0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-danger border-4 h-100">
                                <div class="card-body">
                                    <p class="text-muted mb-0 fw-semibold small text-uppercase">Saldo pendiente</p>
                                    <h4 class="text-danger fw-bolder mb-0" id="comisionesPendientes">$0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comisiones -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="mb-0 fw-semibold"><i class="ti ti-list-details me-2"></i>Mis comisiones y pagos</h5>
                            <small class="text-muted">Solo lectura: los pagos los registra el administrador.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="tablaComisiones" class="table table-hover align-middle" style="width:100%">
                                    <thead><tr><th>Fecha</th><th>Descripción / Servicio</th><th>Tipo</th><th>Monto</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Movimientos relacionados -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="mb-0 fw-semibold"><i class="ti ti-history me-2"></i>Movimientos relacionados a mí</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="tablaMovimientos" class="table table-hover align-middle" style="width:100%">
                                    <thead><tr><th>Fecha</th><th>Servicio</th><th>Tipo</th><th>Monto</th><th>Descripción</th><th>Estado</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar perfil -->
    <div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar mi perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formPerfil">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="fNombre" value="<?= htmlspecialchars($promotor['nombre']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="fTelefono" value="<?= htmlspecialchars($promotor['telefono'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email de acceso</label>
                            <input type="email" class="form-control no-capitalize" name="email" id="fEmail" value="<?= htmlspecialchars($promotor['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña (opcional)</label>
                            <input type="password" class="form-control" name="password" id="fPassword" placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script>
        $(document).ready(function() {
            function getParams() {
                const periodo = $('#selectPeriodo').val();
                if (periodo === 'todo') return 'periodo=todo';
                if (periodo === 'mes_pasado') {
                    const d = new Date(); d.setMonth(d.getMonth() - 1);
                    const anio = d.getFullYear(), mes = String(d.getMonth() + 1).padStart(2, '0');
                    const dias = new Date(anio, d.getMonth() + 1, 0).getDate();
                    return 'periodo=rango&fecha_inicio=' + anio + '-' + mes + '-01&fecha_fin=' + anio + '-' + mes + '-' + String(dias).padStart(2, '0');
                }
                if (periodo === 'rango') return 'periodo=rango&fecha_inicio=' + $('#fechaInicio').val() + '&fecha_fin=' + $('#fechaFin').val();
                return 'periodo=' + periodo;
            }
            function urlComisiones() { return 'api/comisiones-promotores.php?' + getParams(); }

            const table = $('#tablaComisiones').DataTable({
                ajax: { url: urlComisiones(), dataSrc: "" },
                columns: [
                    { data: 'fecha', render: function(data, type, row) {
                        if (!data) return 'N/A';
                        if (type === 'sort' || type === 'type') return data;
                        const partes = data.split(' ')[0].split('-');
                        return '<span class="fw-bold">' + partes[2] + '/' + partes[1] + '/' + partes[0] + '</span>';
                    }},
                    { data: null, render: function(d) { return '<strong>' + (d.servicio || 'Pago') + '</strong><br><small class="text-muted">' + (d.descripcion || '') + '</small>'; }},
                    { data: 'tipo', render: function(t) { return t === 'comision' ? '<span class="badge bg-light-success text-success">Comisión Ganada</span>' : '<span class="badge bg-light-danger text-danger">Pago Realizado</span>'; }},
                    { data: 'comision', render: function(m, type, row) {
                        const clase = row.tipo === 'comision' ? 'text-success' : 'text-danger';
                        const pref = row.tipo === 'comision' ? '+' : '-';
                        return '<span class="fw-bold ' + clase + '">' + pref + formatoMoneda(m) + '</span>';
                    }}
                ],
                order: [[0, 'desc']],
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });

            const mov = $('#tablaMovimientos').DataTable({
                ajax: { url: 'api/obtener_movimientos.php?tipo=promotor', dataSrc: "" },
                columns: [
                    { data: 'fecha', render: function(d, t) { if (!d) return 'N/A'; if (t === 'sort' || t === 'type') return d; const p = d.split(' ')[0].split('-'); return p[2] + '/' + p[1] + '/' + p[0]; }},
                    { data: 'servicio', defaultContent: 'N/A' },
                    { data: 'tipo', render: function(d) { return d ? '<span class="badge bg-secondary text-capitalize">' + d + '</span>' : ''; }},
                    { data: 'monto', render: function(d) { return d ? formatoMoneda(d) : '$0.00'; }},
                    { data: 'descripcion', defaultContent: '' },
                    { data: 'estado', defaultContent: '' }
                ],
                order: [[0, 'desc']],
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });

            function actualizarTotales() {
                $.get('api/comisiones-promotores.php?totales=1&' + getParams(), function(data) {
                    $('#comisionesTotal').text(formatoMoneda(data.total_comisiones));
                    $('#comisionesPagadas').text(formatoMoneda(data.total_pagado));
                    $('#comisionesPendientes').text(formatoMoneda(data.total_pendiente));
                });
            }
            actualizarTotales();

            $('#selectPeriodo').on('change', function() {
                if ($(this).val() === 'rango') { $('#fechaInicioGroup, #fechaFinGroup').show(); }
                else { $('#fechaInicioGroup, #fechaFinGroup').hide(); table.ajax.url(urlComisiones()).load(); actualizarTotales(); }
            });
            $('#btnFiltrar').on('click', function() {
                if ($('#selectPeriodo').val() === 'rango' && (!$('#fechaInicio').val() || !$('#fechaFin').val())) { alert('Selecciona ambas fechas'); return; }
                table.ajax.url(urlComisiones()).load(); actualizarTotales();
            });
            $('#btnGenerarPDF').on('click', function() { window.open('api/comisiones-promotores.php?pdf=1&' + getParams(), '_blank'); });

            $('#formPerfil').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'api/mi-cuenta.php', method: 'POST', data: new FormData(this),
                    processData: false, contentType: false,
                    success: function(res) {
                        $('#modalPerfil').modal('hide');
                        $('#fTelefono').val(res.telefono || '');
                        $('#perfilTelefono').text(res.telefono || 'Sin teléfono');
                        if (res.nombre) { $('#fNombre').val(res.nombre); }
                        $('#fPassword').val('');
                        showToast('Perfil actualizado', 'success');
                    },
                    error: function(xhr) {
                        let m = 'Error al guardar';
                        try { m = JSON.parse(xhr.responseText).error || m; } catch (e) {}
                        showToast(m, 'error');
                    }
                });
            });

            function formatoMoneda(v) {
                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(v) || 0);
            }
        });
    </script>
</body>
</html>
