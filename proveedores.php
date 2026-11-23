<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

?>

<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Proveedores - Amiri</title>



    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
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
     [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
        /* =========================================
   🌙 REGLAS PARA MODO OSCURO (Usuarios y DataTables)
   ========================================= */

/* 1. Textos generales y Tarjetas */
[data-bs-theme="dark"] h3,
[data-bs-theme="dark"] .page-breadcrumb h3 {
    color: #f5f2f4 !important;
}

[data-bs-theme="dark"] .card { background: var(--nx-surface) !important;
    border-color: rgba(255,255,255,0.12);
}

/* 2. Tabla principal (DataTables) */
[data-bs-theme="dark"] .table>thead>tr>th {
    background-color: var(--nx-surface) !important;
    color: #f5f2f4 !important;
    border-bottom-color: rgba(255,255,255,0.12) !important;
}

[data-bs-theme="dark"] .table>tbody>tr>td {
    color: #f5f2f4 !important;
    border-color: rgba(255,255,255,0.12) !important;
}

/* Respetar el estilo "zebra" (striped) y hover en modo oscuro */
[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02);
}
[data-bs-theme="dark"] .table-hover>tbody>tr:hover>* {
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05);
}

/* 3. Controles de DataTables (Buscar, Paginación, Entradas) */
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate {
    color: #9a9096 !important;
}

/* Estilo para los botones de paginación */
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
    color: #9a9096 !important;
}
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #1a181c !important;
    border-color: rgba(255,255,255,0.12) !important;
    color: #f5f2f4 !important;
}

[data-bs-theme="dark"] .badge.badge-moneda.bg-success.bg-opacity-10,
[data-bs-theme="dark"] .badge.badge-moneda.bg-info.bg-opacity-10,
[data-bs-theme="dark"] .badge.badge-moneda.bg-secondary.bg-opacity-10 {
    background-image: none !important;
    border: 0 !important;
}

[data-bs-theme="dark"] .badge.badge-moneda.bg-success.bg-opacity-10 {
    background-color: rgba(19, 222, 185, 0.15) !important;
    color: var(--nx-success) !important;
}

[data-bs-theme="dark"] .badge.badge-moneda.bg-info.bg-opacity-10 {
    background-color: rgba(93, 135, 255, 0.15) !important;
    color: var(--nx-primary) !important;
}

[data-bs-theme="dark"] .badge.badge-moneda.bg-secondary.bg-opacity-10 {
    background-color: rgba(140, 158, 168, 0.15) !important;
    color: #9a9096 !important;
}

/* Inputs del buscador de DataTables */
[data-bs-theme="dark"] .dataTables_wrapper select,
[data-bs-theme="dark"] .dataTables_wrapper input {
    background-color: #1a181c;
    border: 1px solid rgba(255,255,255,0.12);
    color: #f5f2f4;
}

/* 4. Modal de Agregar/Editar Usuario */
[data-bs-theme="dark"] .modal-content { background: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }
[data-bs-theme="dark"] .modal-header {
    border-bottom-color: rgba(255,255,255,0.12);
}
[data-bs-theme="dark"] .modal-footer {
    border-top-color: rgba(255,255,255,0.12);
}
[data-bs-theme="dark"] .modal-title {
    color: #f5f2f4;
}
[data-bs-theme="dark"] .form-label {
    color: #f5f2f4;
}

/* Campos de texto y select dentro del Modal */
[data-bs-theme="dark"] .form-control,
[data-bs-theme="dark"] .form-select {
    background-color: #1a181c;
    border-color: rgba(255,255,255,0.12);
    color: #f5f2f4;
}
[data-bs-theme="dark"] .form-control::placeholder {
    color: #9a9096;
}

/* ─── Page Header ─────────────────────────────── */
.proveedores-header { position: relative; overflow: hidden; border-radius: 16px !important; }
.proveedores-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
.proveedores-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
.proveedores-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
.proveedores-header .sub-badge { font-size: 1rem; color: #9a9096; }

/* ─── Metric Cards ────────────────────────────── */
.metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
.metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
.metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
.metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
.metric-footer { font-size: 0.8rem; color: #9a9096; }

[data-bs-theme="dark"] .proveedores-header .sub-badge { color: #9a9096 !important; }
[data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(93,135,255,0.12) !important; }
[data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(19,222,185,0.12) !important; }
[data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255,174,31,0.12) !important; }
[data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(83,155,255,0.12) !important; }
[data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
</style>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <!-- ════════════════════════════════════
                         PAGE HEADER
                         ════════════════════════════════════ -->
                    <div class="card proveedores-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-building-store text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Proveedores</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Gestión de proveedores y pagos</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProveedor">
                                        <i class="ti ti-plus"></i> Nuevo Proveedor
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         KPI METRIC CARDS
                         ════════════════════════════════════ -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total Proveedores</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="ti ti-building-store"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-primary" id="kpiTotal">0</div>
                                    <div class="metric-footer">Registrados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Activos</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-user-check"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiActivos">0</div>
                                    <div class="metric-footer">En operación</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Aceptan USD</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-currency-dollar"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiUsd">0</div>
                                    <div class="metric-footer">Dólares</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Aceptan USDT</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-coin"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiUsdt">0</div>
                                    <div class="metric-footer">Tether</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4 table-responsive">
                            <table id="tablaProveedores" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Moneda</th>
                                        <th>Celular</th>
                                        <th>Correo</th>
                                        <th>Cuenta Caja</th>
                                        <th>Adeudo</th>
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



    <!-- MODAL -->

    <div class="modal fade" id="modalProveedor" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">



                <div class="modal-header">

                    <h5 class="modal-title"><i class="ti ti-building-store"></i> Proveedor</h5>

                    <button class="btn-close" data-bs-dismiss="modal"></button>

                </div>



                <form id="formProveedor">

                    <div class="modal-body">



                        <input type="hidden" name="id" id="id">



                        <div class="mb-3">

                            <label class="form-label">Nombre *</label>

                            <input type="text" name="nombre" class="form-control" required>

                        </div>



                        <div class="mb-3">

                            <label class="form-label">Celular</label>

                            <input type="text" name="telefono" class="form-control">

                        </div>



                        <div class="mb-3">

                            <label class="form-label">Correo</label>

                            <input type="email" name="email" class="form-control">

                        </div>



                        <div class="mb-3">

                            <label class="form-label">Monedas que acepta *</label>

                            <div class="d-flex gap-4 mt-1">

                                <div class="form-check">

                                    <input class="form-check-input" type="checkbox" name="acepta_usd" id="chkUsd" value="1">

                                    <label class="form-check-label" for="chkUsd">

                                        <span class="badge badge-moneda bg-success bg-opacity-10 text-success border-0" style="font-size: 0.75rem;">USD</span> Dólares

                                    </label>

                                </div>

                                <div class="form-check">

                                    <input class="form-check-input" type="checkbox" name="acepta_usdt" id="chkUsdt" value="1">

                                    <label class="form-check-label" for="chkUsdt">

                                        <span class="badge badge-moneda bg-info bg-opacity-10 text-info border-0" style="font-size: 0.75rem;">USDT</span> Tether

                                    </label>

                                </div>

                            </div>

                            <small class="text-muted">Selecciona al menos una</small>

                        </div>



                    </div>



                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button class="btn btn-primary" type="submit">Guardar</button>

                    </div>



                </form>

            </div>

        </div>

    </div>



    <!-- MODAL PAGO PROVEEDOR -->

    <div class="modal fade" id="modalPagoProveedor" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title"><i class="ti ti-cash"></i> Pagar Proveedor</h5>

                    <button class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <form id="formPagoProveedor">

                    <div class="modal-body">

                        <input type="hidden" id="pagoProveedorId" name="proveedor_id">

                        <input type="hidden" id="monedaProveedor" name="moneda_proveedor">

                        <input type="hidden" id="pagoParidadProveedor">



                        <!-- Contexto del adeudo -->

                        <div class="alert alert-secondary py-2 mb-3">

                            <div class="d-flex justify-content-between align-items-center mb-1">

                                <span class="fw-bold fs-6" id="nombreProveedorDisplay"></span>

                                <span id="badgeMonedaProveedor" class="badge badge-moneda bg-success bg-opacity-10 text-success border-0" style="font-size: 0.85rem;">USD</span>

                            </div>

                            <div class="row text-center mt-2">

                                <div class="col-4">

                                    <div class="text-muted small">Divisas adeudadas</div>

                                    <div class="fw-bold" id="infoAdeudoDivisa">—</div>

                                </div>

                                <div class="col-4">

                                    <div class="text-muted small">TC compra</div>

                                    <div class="fw-bold" id="infoTcCompra">—</div>

                                </div>

                                <div class="col-4">

                                    <div class="text-muted small">Total a pagar</div>

                                    <div class="fw-bold text-danger" id="infoTotalMxn">—</div>

                                </div>

                            </div>

                        </div>



                        <!-- Cuenta de pago -->

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha de Pago *</label>
                            <input type="date" id="inputFechaPagoProveedor" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">Cuenta de Pago *</label>

                            <select id="cuentaPagoProveedorSelect" name="cuenta_pago_id" class="form-control" required>

                                <option value="">Seleccione cuenta</option>

                            </select>

                        </div>



                        <!-- Monto a pagar -->

                        <div class="mb-3" id="grupoMontoPago" style="display:none;">

                            <label class="form-label fw-semibold">Monto a Pagar (MXN) *</label>

                            <div class="input-group">

                                <span class="input-group-text">$</span>

                                <input type="text" id="inputMontoPagoProveedor" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>

                                <span class="input-group-text">MXN</span>

                            </div>

                            <small id="labelMontoFormateadoPago" class="text-success fw-bold mt-1 d-block" style="min-height:1.2em;"></small>

                            <div class="d-flex justify-content-between align-items-center mt-1">

                                <small class="text-muted" id="infoSaldoCuentaPago"></small>

                                <button type="button" class="btn btn-outline-success btn-sm" id="btnLiquidarAdeudo" style="display:none;">

                                    <i class="ti ti-check"></i> Liquidar Total

                                </button>

                            </div>

                            <div id="alertaSaldoInsuficiente" class="alert alert-warning py-2 mt-2" style="display:none;">

                                <i class="ti ti-alert-triangle"></i> El monto no puede superar el adeudo total ni el saldo de la cuenta.

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button type="submit" class="btn btn-primary" id="btnProcesarPago" style="display:none;">

                            <i class="ti ti-cash"></i> Procesar Pago

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="src/assets/js/utils.js"></script>



    <script>

        const avatarColors = ['var(--nx-primary)', 'var(--nx-success)', 'var(--nx-danger)', 'var(--nx-warning)', '#539BFF', '#6F42C1', '#E83E8C', '#20C997'];
        function getAvatarColor(name) {
            if (!name) return '#9a9096';
            let hash = 0;
            for (let i = 0; i < name.length; i++) { hash = name.charCodeAt(i) + ((hash << 5) - hash); }
            return avatarColors[Math.abs(hash) % avatarColors.length];
        }

        const tabla = $('#tablaProveedores').DataTable({

            ajax: {

                url: 'api/proveedores.php?activos=1',

                dataSrc: ''

            },

            columns: [

                { data: 'id' },

                {
                    data: 'nombre',
                    render: function(d, type, row) {
                        var n = d;
                        var color = getAvatarColor(n);
                        var initial = n ? n.charAt(0).toUpperCase() : '?';
                        return '<div class="d-flex align-items-center gap-2">' +
                               '<span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:' + color + '22;color:' + color + ';font-weight:700;font-size:0.85rem;">' + initial + '</span>' +
                               '<span class="fw-semibold">' + n + '</span>' +
                               '</div>';
                    }
                },

                {

                    data: null,

                    render: function(row) {

                        let badges = '';

                        if (row.acepta_usd)  badges += '<span class="badge badge-moneda bg-success bg-opacity-10 text-success border-0 me-1" style="font-size: 0.75rem;">USD</span>';

                        if (row.acepta_usdt) badges += '<span class="badge badge-moneda bg-info bg-opacity-10 text-info border-0 me-1" style="font-size: 0.75rem;">USDT</span>';

                        if (!badges) badges = '<span class="badge badge-moneda bg-secondary bg-opacity-10 text-secondary border-0" style="font-size: 0.75rem;">-</span>';

                        return badges;

                    }

                },

                { data: 'telefono', render: d => d ? `<span class="no-capitalize">${d}</span>` : '-' },

                { data: 'email', render: d => d ? `<span class="no-capitalize">${d}</span>` : '-' },

                {

                    data: null,

                    render: function(row) {

                        let cuentas = '';

                        if (row.cuenta_usd_nombre)  cuentas += `<div><span class="badge badge-moneda bg-success bg-opacity-10 text-success border-0 me-1" style="font-size: 0.75rem;">USD</span>${row.cuenta_usd_nombre}</div>`;

                        if (row.cuenta_usdt_nombre) cuentas += `<div><span class="badge badge-moneda bg-info bg-opacity-10 text-info border-0 me-1" style="font-size: 0.75rem;">USDT</span>${row.cuenta_usdt_nombre}</div>`;

                        return cuentas || '<span class="text-muted">Sin caja</span>';

                    }

                },

                {

                    data: null,

                    render: function(row) {

                        let adeudos = '';

                        if (parseFloat(row.saldo_mxn || 0) > 0.001) {

                            adeudos += `<div>MXN $${parseFloat(row.saldo_mxn).toLocaleString('es-MX', {minimumFractionDigits: 2})}</div>`;

                        }

                        if (row.acepta_usd && parseFloat(row.saldo_usd || 0) > 0.001) {

                            adeudos += `<div>USD ${parseFloat(row.saldo_usd).toFixed(2)}</div>`;

                        }

                        if (row.acepta_usdt && parseFloat(row.saldo_usdt || 0) > 0.001) {

                            adeudos += `<div>USDT ${parseFloat(row.saldo_usdt).toFixed(2)}</div>`;

                        }

                        return adeudos || '<span class="text-muted">Sin adeudo</span>';

                    }

                },

                {
                    data: null,
                    render: d => {
                        let botones = '<div class="d-flex align-items-center gap-1">';
                        
                        botones += `<a href="historial-proveedor.php?id=${d.id}" class="btn btn-sm btn-outline-secondary" title="Historial"><i class="ti ti-history"></i></a>`;

                        const tieneAdeudoMxn  = parseFloat(d.saldo_mxn  || 0) > 0.001;
                        const tieneAdeudoUsd  = d.acepta_usd  && parseFloat(d.saldo_usd  || 0) > 0.001;
                        const tieneAdeudoUsdt = d.acepta_usdt && parseFloat(d.saldo_usdt || 0) > 0.001;
                        
                        if (tieneAdeudoMxn)  botones += `<button class="btn btn-sm btn-outline-warning pagar-mxn" data-id="${d.id}" data-moneda="MXN" title="Pagar MXN"><i class="ti ti-cash"></i></button>`;
                        if (tieneAdeudoUsd)  botones += `<button class="btn btn-sm btn-outline-primary pagar" data-id="${d.id}" data-moneda="USD" title="Pagar USD"><i class="ti ti-cash"></i></button>`;
                        if (tieneAdeudoUsdt) botones += `<button class="btn btn-sm btn-outline-info pagar" data-id="${d.id}" data-moneda="USDT" title="Pagar USDT"><i class="ti ti-cash"></i></button>`;
                        
                        botones += `<button class="btn btn-sm btn-outline-warning editar" data-id="${d.id}" title="Editar"><i class="ti ti-pencil"></i></button>`;
                        botones += `<button class="btn btn-sm btn-outline-danger eliminar" data-id="${d.id}" title="Eliminar"><i class="ti ti-trash"></i></button>`;
                        
                        botones += '</div>';
                        return botones;
                    }
                }

            ],

            scrollX: true,

            order: [[0, 'desc']],

            language: {

                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"

            }

        });



        let abriendo_editar = false;



        // Limpiar formulario cuando se abre el modal (solo si no es editar)

        $('#modalProveedor').on('show.bs.modal', function(e) {

            if (!abriendo_editar) {

                $('#formProveedor')[0].reset();

                $('#id').val('');

            }

            abriendo_editar = false;

        });



        // Enviar formulario

        $('#formProveedor').on('submit', function(e) {

            e.preventDefault();



            $.post('api/proveedores.php', $(this).serialize(), function(res) {

                if (res.success) {

                    $('#modalProveedor').modal('hide');

                    tabla.ajax.reload();
                    cargarResumen();

                    showToast('Proveedor guardado correctamente', 'success');

                }

            }).fail(function(xhr) {

                showToast(xhr.responseJSON?.error || 'Error al guardar', 'error');

            });

        });



        // Editar

        $(document).on('click', '.editar', function() {

            const id = $(this).data('id');

            $.get('api/proveedores.php?id=' + id, function(data) {

                abriendo_editar = true;

                $('#id').val(data.id);

                $('input[name="nombre"]').val(data.nombre);

                $('input[name="telefono"]').val(data.telefono);

                $('input[name="email"]').val(data.email);

                $('#chkUsd').prop('checked',  !!parseInt(data.acepta_usd  || 0));

                $('#chkUsdt').prop('checked', !!parseInt(data.acepta_usdt || 0));

                $('#modalProveedor').modal('show');

            });

        });



        // Eliminar

        $(document).on('click', '.eliminar', function() {

            if (!confirm('¿Eliminar este proveedor?')) return;



            const id = $(this).data('id');

            $.ajax({

                url: 'api/proveedores.php',

                method: 'DELETE',

                data: { id },

                success: function() {

                    tabla.ajax.reload();
                    cargarResumen();

                    showToast('Proveedor eliminado', 'success');

                },

                error: function() {

                    showToast('Error al eliminar', 'error');

                }

            });

        });



        /**

         * Formatear moneda según el tipo

         */

        function formatoMonedaDinamica(valor, moneda) {

            const n = parseFloat(valor) || 0;

            const simbolo = moneda === 'USD' ? 'USD ' : 'USDT ';

            return simbolo + n.toLocaleString("en-US", {

                minimumFractionDigits: 2,

                maximumFractionDigits: 2

            });

        }



        // Pago al proveedor

        $(document).on('click', '.pagar', function() {

            const id     = $(this).data('id');

            const moneda = $(this).data('moneda') || 'USD';

            $.get('api/proveedores.php?id=' + id, function(data) {

                $('#pagoProveedorId').val(data.id);

                $('#monedaProveedor').val(moneda);

                $('#nombreProveedorDisplay').text(data.nombre);



                const badgeClass = moneda === 'USD' ? 'bg-success' : 'bg-info';

                $('#badgeMonedaProveedor').removeClass('bg-success bg-info').addClass(badgeClass).text(moneda);



                // Adeudo según moneda

                const adeudoDivisa = moneda === 'USD'

                    ? parseFloat(data.saldo_usd  || 0)

                    : parseFloat(data.saldo_usdt || 0);

                const adeudoMxn = moneda === 'USD'

                    ? parseFloat(data.valor_mxn_usd  || 0)

                    : parseFloat(data.valor_mxn_usdt || 0);

                // TC compra promedio (lo necesita la API para reducir el saldo en divisa)

                const tcCompra = adeudoDivisa > 0 ? adeudoMxn / adeudoDivisa : 0;



                // Llenar contexto informativo

                $('#infoAdeudoDivisa').text(adeudoDivisa.toLocaleString('es-MX', {minimumFractionDigits:4, maximumFractionDigits:4}) + ' ' + moneda);

                $('#infoTcCompra').text('$' + tcCompra.toLocaleString('es-MX', {minimumFractionDigits:4, maximumFractionDigits:4}));

                $('#infoTotalMxn').text('$' + adeudoMxn.toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MXN');



                // Guardar paridad y adeudo total en data del form para uso posterior

                $('#pagoParidadProveedor').val(tcCompra.toFixed(8));

                $('#formPagoProveedor').data('adeudo-mxn', adeudoMxn);



                // Resetear campos de monto y ocultar sección de pago

                $('#inputMontoPagoProveedor').val('');

                $('#labelMontoFormateadoPago').text('');

                $('#grupoMontoPago').hide();

                $('#btnLiquidarAdeudo').hide();

                $('#alertaSaldoInsuficiente').hide();

                $('#btnProcesarPago').hide();

                $('#infoSaldoCuentaPago').text('');



                // Cargar cuentas MXN — solo Bancarias (1) y Cajas (2), saldo > 0

                $.get('api/cuentas.php?tipo_moneda=MXN', function(cuentas) {

                    const sel = $('#cuentaPagoProveedorSelect');

                    sel.html('<option value="">Seleccione cuenta</option>');

                    const filtradas = cuentas.filter(c => [1, 2].includes(parseInt(c.tipo_cuenta)) && parseFloat(c.saldo_actual) > 0);

                    if (filtradas.length === 0) {

                        sel.append('<option value="" disabled>No hay cuentas MXN disponibles</option>');

                    } else {

                        filtradas.forEach(c => {

                            const saldo = parseFloat(c.saldo_actual || 0).toFixed(2);

                            let nombreCuenta = c.nombre;

                            if (c.banco && c.banco.trim()) {

                                nombreCuenta += ' (' + c.banco + ')';

                            }

                            sel.append(`<option value="${c.id}" data-saldo="${saldo}">${nombreCuenta} — Saldo: $${parseFloat(saldo).toLocaleString('es-MX',{minimumFractionDigits:2})} MXN</option>`);

                        });

                    }

                    $('#modalPagoProveedor').modal('show');

                });

            });

        });



        // Pago MXN al proveedor (Compra de Efectivo)

        $(document).on('click', '.pagar-mxn', function() {

            const id = $(this).data('id');

            $.get('api/proveedores.php?id=' + id, function(data) {

                $('#pagoProveedorId').val(data.id);

                $('#monedaProveedor').val('MXN');

                $('#nombreProveedorDisplay').text(data.nombre);

                $('#badgeMonedaProveedor').removeClass('bg-success bg-info').addClass('bg-warning').text('MXN');

                // Adeudo MXN

                const adeudoMxn = parseFloat(data.saldo_mxn || 0);

                // Para MXN, paridad es 1

                $('#infoAdeudoDivisa').text('MXN $' + adeudoMxn.toLocaleString('es-MX', {minimumFractionDigits:2}));

                $('#infoTcCompra').text('$1.0000');

                $('#infoTotalMxn').text('$' + adeudoMxn.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');

                $('#pagoParidadProveedor').val('1');

                $('#formPagoProveedor').data('adeudo-mxn', adeudoMxn);

                // Resetear campos

                $('#inputMontoPagoProveedor').val('');

                $('#labelMontoFormateadoPago').text('');

                $('#grupoMontoPago').hide();

                $('#btnLiquidarAdeudo').hide();

                $('#alertaSaldoInsuficiente').hide();

                $('#btnProcesarPago').hide();

                $('#infoSaldoCuentaPago').text('');

                // Cuentas MXN de pago

                $.get('api/cuentas.php?tipo_moneda=MXN', function(cuentas) {

                    const sel = $('#cuentaPagoProveedorSelect');

                    sel.html('<option value="">Seleccione cuenta</option>');

                    const filtradas = cuentas.filter(c => [1, 2].includes(parseInt(c.tipo_cuenta)) && parseFloat(c.saldo_actual) > 0);

                    if (filtradas.length === 0) {

                        sel.append('<option value="" disabled>No hay cuentas MXN disponibles</option>');

                    } else {

                        filtradas.forEach(c => {

                            const saldo = parseFloat(c.saldo_actual || 0).toFixed(2);

                            let nombreCuenta = c.nombre;

                            if (c.banco && c.banco.trim()) {

                                nombreCuenta += ' (' + c.banco + ')';

                            }

                            sel.append(`<option value="${c.id}" data-saldo="${saldo}">${nombreCuenta} — Saldo: $${parseFloat(saldo).toLocaleString('es-MX',{minimumFractionDigits:2})} MXN</option>`);

                        });

                    }

                    $('#modalPagoProveedor').modal('show');

                });

            });

        });



        // Al cambiar cuenta: mostrar input de monto y condicionalmente "Liquidar Total"

        $(document).on('change', '#cuentaPagoProveedorSelect', function() {

            const saldoCuenta = parseFloat($(this).find('option:selected').attr('data-saldo')) || 0;

            const adeudoMxn   = parseFloat($('#formPagoProveedor').data('adeudo-mxn')) || 0;



            if (!$(this).val()) {

                $('#grupoMontoPago').hide();

                $('#btnProcesarPago').hide();

                return;

            }



            // Mostrar sección de monto y botón procesar

            $('#grupoMontoPago').show();

            $('#btnProcesarPago').show();

            $('#inputMontoPagoProveedor').val('').attr('max', Math.min(saldoCuenta, adeudoMxn).toFixed(2));

            $('#infoSaldoCuentaPago').text('Saldo disponible: $' + saldoCuenta.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');

            $('#alertaSaldoInsuficiente').hide();



            // Mostrar "Liquidar Total" solo si el saldo alcanza para cubrir todo

            if (saldoCuenta >= adeudoMxn) {

                $('#btnLiquidarAdeudo').show();

            } else {

                $('#btnLiquidarAdeudo').hide();

            }

        });



        // Botón "Liquidar Total" — autocompleta el input con el adeudo completo

        $(document).on('click', '#btnLiquidarAdeudo', function() {

            const adeudoMxn = parseFloat($('#formPagoProveedor').data('adeudo-mxn')) || 0;

            $('#inputMontoPagoProveedor').val(adeudoMxn.toFixed(2)).trigger('input');

        });



        // Validación en tiempo real del input de monto

        $(document).on('input', '#inputMontoPagoProveedor', function() {

            const val         = parseFloat($(this).val()) || 0;

            const adeudoMxn   = parseFloat($('#formPagoProveedor').data('adeudo-mxn')) || 0;

            const saldoCuenta = parseFloat($('#cuentaPagoProveedorSelect').find('option:selected').attr('data-saldo')) || 0;

            const maximo      = Math.min(saldoCuenta, adeudoMxn);



            // Etiqueta formateada

            if (val > 0) {

                $('#labelMontoFormateadoPago').text('$' + val.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            } else {

                $('#labelMontoFormateadoPago').text('');

            }



            if (val > maximo) {

                $('#alertaSaldoInsuficiente').show();

                $('#btnProcesarPago').prop('disabled', true);

            } else {

                $('#alertaSaldoInsuficiente').hide();

                $('#btnProcesarPago').prop('disabled', false);

            }

        });



        // Procesar pago

        $(document).on('submit', '#formPagoProveedor', function(e) {

            e.preventDefault();

            const monto = parseFloat($('#inputMontoPagoProveedor').val()) || 0;



            if (!$('#cuentaPagoProveedorSelect').val()) {

                showToast('Selecciona una cuenta de pago', 'error');

                return;

            }

            if (monto <= 0) {

                showToast('Ingresa un monto a pagar', 'error');

                return;

            }



            // Obtenemos tu fecha elegida y le sumamos la hora actual para que no sea 00:00:00
            const fechaElegida = $('#inputFechaPagoProveedor').val();
            const horaActual = new Date().toTimeString().split(' ')[0];

            const payload = {
                proveedor_id:   $('#pagoProveedorId').val(),
                cuenta_pago_id: $('#cuentaPagoProveedorSelect').val(),
                monto:          monto,
                paridad:        parseFloat($('#pagoParidadProveedor').val()),
                moneda:         $('#monedaProveedor').val(),
                fecha:          fechaElegida + ' ' + horaActual
            };



            const $btn = $('#btnProcesarPago').prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');

            $.ajax({

                url: 'api/proveedores-pagos.php',

                method: 'POST',

                contentType: 'application/json',

                data: JSON.stringify(payload),

                success: function() {

                    $('#modalPagoProveedor').modal('hide');

                    tabla.ajax.reload();
                    cargarResumen();

                    showToast('Pago registrado', 'success');

                },

                error: function(xhr) {

                    let msg = 'Error al registrar pago';

                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}

                    showToast(msg, 'error');

                    $btn.prop('disabled', false).html('<i class="ti ti-cash"></i> Procesar Pago');

                }

            });

        });

        // ─── Resumen (KPI Cards) ─────────────────────
        function cargarResumen() {
            $.get('api/proveedores.php?resumen=1', function(data) {
                $('#kpiTotal').text(data.total);
                $('#kpiActivos').text(data.activos);
                $('#kpiUsd').text(data.aceptan_usd);
                $('#kpiUsdt').text(data.aceptan_usdt);
            }).fail(function() {
                $('#kpiTotal').text('Error');
            });
        }

        $(document).ready(function() {
            cargarResumen();
        });
    </script>



</body>



</html>

