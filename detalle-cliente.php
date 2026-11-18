<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



// Obtener el ID del cliente de la URL

$cliente_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);



if (!$cliente_id) {

    die("Error: ID de cliente no v�lido.");

}



// Funci�n para obtener datos del cliente

function getCliente($pdo, $id)

{

    $stmt = $pdo->prepare("

        SELECT c.*, cu.nombre as cuenta_nombre, cu.saldo_actual as cuenta_saldo, p.nombre as promotor_nombre

        FROM clientes c

        LEFT JOIN cuentas cu ON c.cuenta_id = cu.id

        LEFT JOIN promotores p ON c.promotor_id = p.id

        WHERE c.id = ?

    ");

    $stmt->execute([$id]);

    return $stmt->fetch();

}



// Funci�n para obtener cuentas MXN disponibles para pago

function getCuentas($pdo)

{

    $stmt = $pdo->query("

        SELECT id, nombre, saldo_actual, tipo_moneda, tipo_cuenta

        FROM cuentas

        WHERE activo = 1

          AND tipo_moneda = 'MXN'

          AND tipo_cuenta IN (1, 2, 3)

          AND saldo_actual > 0

        ORDER BY tipo_cuenta, nombre

    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}



$requireEnv = __DIR__ . '/src/config/env.php';

if (file_exists($requireEnv)) {

    require_once $requireEnv;

}

require_once __DIR__ . '/src/config/db.php';



try {

    $pdo = getPDO();



    $cliente = getCliente($pdo, $cliente_id);



    if (!$cliente) {

        die("Error: Cliente no encontrado.");

    }



    $cuentas = getCuentas($pdo);

} catch (PDOException $e) {

    die("Error de conexi�n: " . $e->getMessage());

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Ventas Pendientes - <?= htmlspecialchars($cliente['nombre']) ?></title>

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
            /* Le damos un peque�o respiro visual para que el t�tulo no quede pegando con el header */
            padding-top: 15px !important;
            margin-top: 0 !important;
        }
        .detalle-container {

            margin-top: 20px;

        }

        #tabsCliente .nav-link {

            color: #6c757d;

        }

        #tabsCliente .nav-link.active {
            color: #5D87FF; /* Morado Amiri */
            background-color: transparent;
            border-bottom: 2px solid #5D87FF;
            font-weight: 600;
        }
        #tabsCliente .nav-link:not(.active):hover {
            color: #5D87FF; /* Morado Amiri al pasar el mouse */
        }
 [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }

        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .page-breadcrumb,
        [data-bs-theme="dark"] .page-breadcrumb h3,
        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-body,
        [data-bs-theme="dark"] .modal-content,
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            background-color: #1f2535 !important;
            border-color: #2b3350 !important;
            color: #e6ecff !important;
        }

        [data-bs-theme="dark"] .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .table,
        [data-bs-theme="dark"] .table thead th,
        [data-bs-theme="dark"] .table tbody td,
        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #1f2535 !important;
            color: #e6ecff !important;
            border-color: #2b3350 !important;
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) {
            background-color: #232b3c !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .input-group-text,
        [data-bs-theme="dark"] .form-control-plaintext,
        [data-bs-theme="dark"] .alert,
        [data-bs-theme="dark"] .badge,
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            background-color: #232b3c !important;
            color: #e6ecff !important;
            border-color: #2b3350 !important;
        }

        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #ffffff !important;
            border-bottom-color: #6c5ce7 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 {
            background-color: rgba(93, 135, 255, 0.12) !important;
        }

        [data-bs-theme="dark"] .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.12) !important;
        }

        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.12) !important;
        }
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3 mt-2">
                                <h3 class="mb-0"><i class="ti ti-user"></i> <?= htmlspecialchars($cliente['nombre']) ?></h3>
                                <a href="api/exportar-reporte.php?formato=pdf&reporte=estado_cuenta_cliente&cliente_id=<?= (int)$cliente_id ?>" class="btn btn-danger ms-auto"><i class="ti ti-file-type-pdf"></i> Estado de cuenta (PDF)</a>
                                <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#modalReporteFiltrado"><i class="ti ti-filter"></i> Reporte por fechas</button>
                                <a href="flujo.php" class="btn btn-secondary ms-2"><i class="ti ti-arrow-left"></i> Volver a Flujo</a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0 rounded-4" style="position: relative; overflow: hidden;">
                                
                                <div style="position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: #5D87FF; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>
                                <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: #13deb9; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="ti ti-user fs-5"></i>
                                        </div>
                                        <h5 class="card-title fw-semibold mb-0">Perfil del Cliente</h5>
                                    </div>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-id fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Nombre Completo</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($cliente['nombre']) ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-phone fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Contacto</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($cliente['contacto'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-user fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Promotor Asignado</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($cliente['promotor_nombre'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-percentage fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Comisi�n AMIRI</p>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border-0 mt-1 fs-6"><?= htmlspecialchars($cliente['porcentaje_amiri'] ?? 0) ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-dark bg-opacity-10 text-dark rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-building-bank fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cuenta Vinculada</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($cliente['cuenta_nombre'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-wallet fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Saldo a Favor</p>
                                                    <h6 class="text-success fw-bolder mb-0 fs-4 mt-1">$<?= number_format((float)($cliente['cuenta_saldo'] ?? 0), 2, '.', ',') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-tabs card-header-tabs" id="tabsCliente" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="tab-adeudos-btn" data-bs-toggle="tab" data-bs-target="#tab-adeudos" type="button" role="tab">
                                                <i class="ti ti-cash"></i> Adeudos y Pagos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="tab-divisas-btn" data-bs-toggle="tab" data-bs-target="#tab-divisas" type="button" role="tab">
                                                <i class="ti ti-currency-dollar"></i> Ventas USD / USDT
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <div class="tab-content" id="tabsClienteContent">

                                        <div class="tab-pane fade show active" id="tab-adeudos" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="tablaVentas" class="table table-hover align-middle" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 40px;"><input type="checkbox" id="checkAllVentas"></th>
                                                            <th>ID</th>
                                                            <th>Fecha</th>
                                                            <th>Total recibido</th>
                                                            <th>Comisi�n AMIRI</th>
                                                            <th>%</th>
                                                            <th>Comisi�n Promotor</th>
                                                            <th>%</th>
                                                            <th>Total a pagar</th>
                                                            <th>Estado</th>
                                                            <th>Pagado</th>
                                                            <th>Pendiente</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="tab-divisas" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="tablaVentasDivisas" class="table table-striped table-hover table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Moneda</th>
                                                            <th>Cantidad</th>
                                                            <th>TC Venta</th>
                                                            <th>Total MXN</th>
                                                            <th>TC Costo (FIFO)</th>
                                                            <th>Ganancia AMIRI</th>
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
            </div>
        </div>


        <!-- Modal Reporte por fechas -->

        <div class="modal fade" id="modalReporteFiltrado" tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">Reporte por fechas (PDF)</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <div class="row g-3">

                            <div class="col-6">

                                <label class="form-label">Desde</label>

                                <input type="date" class="form-control" id="repFechaInicio">

                            </div>

                            <div class="col-6">

                                <label class="form-label">Hasta</label>

                                <input type="date" class="form-control" id="repFechaFin" value="<?= date('Y-m-d') ?>">

                            </div>

                            <div class="col-12">

                                <label class="form-label">Incluir</label>

                                <select class="form-select" id="repSeccion">

                                    <option value="ambos">Ventas y pagos</option>

                                    <option value="ventas">Solo ventas</option>

                                    <option value="pagos">Solo pagos</option>

                                </select>

                            </div>

                        </div>

                        <small class="text-muted d-block mt-2">Vacío = historial completo.</small>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button type="button" class="btn btn-danger" id="btnGenerarReporteFiltrado"><i class="ti ti-file-type-pdf"></i> Generar PDF</button>

                    </div>

                </div>

            </div>

        </div>

        <script>
            document.getElementById('btnGenerarReporteFiltrado').addEventListener('click', function() {
                const params = new URLSearchParams({
                    formato: 'pdf',
                    reporte: 'estado_cuenta_cliente',
                    cliente_id: '<?= (int)$cliente_id ?>',
                    seccion: document.getElementById('repSeccion').value
                });
                const fi = document.getElementById('repFechaInicio').value;
                const ff = document.getElementById('repFechaFin').value;
                if (fi) params.set('fecha_inicio', fi);
                if (ff) params.set('fecha_fin', ff);
                window.location.href = 'api/exportar-reporte.php?' + params.toString();
            });
        </script>

        <!-- Bot�n flotante para pagos m�ltiples -->
        <div id="btnPagarMultipleContainer" style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 1050;">
            <button class="btn btn-warning shadow-lg px-4 py-2" id="btnPagarMultiple" style="border-radius: 50px; font-weight: bold;">
                <i class="ti ti-cash me-2"></i> Pagar (<span id="contadorSeleccionadas">0</span>) Ventas
            </button>
        </div>

        <!-- Modal Pagar Venta -->

        <div class="modal fade" id="modalPagarVenta" tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">Registrar Pago de Venta</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <form id="formPagarVenta">

                        <input type="hidden" id="ventaId" name="id">

                        <div class="modal-body">

    <div class="alert alert-info py-2" id="resumenPagoMultiple" style="display: none;">
        <small>Pagando a: <strong id="nombreClientePago"></strong></small>
    </div>

    <div class="alert alert-warning py-2 text-center" id="alertaDeudaTotal" style="display: none;">
        <small>Deuda Total: <strong id="deudaTotalCliente">$0.00</strong></small>
    </div>

                            <div id="contenedorPagosMultiples" class="mb-3 p-3 bg-light border rounded" style="display: none;"></div>

                    <div class="mb-3" id="grupoMontoGlobal" style="display: none;">
                        <label class="form-label fw-bold text-success">¿Cuánto dinero vas a pagar?</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" step="0.01" id="monto_a_pagar_input_multi" class="form-control form-control-lg text-success fw-bold">
                        </div>
                        <small class="text-muted">Se distribuirá automáticamente entre las ventas seleccionadas.</small>
                    </div>

                            <div class="row mb-2">

                                <div class="col-md-6">

                                    <label class="form-label">Monto Total de la Venta</label>

                                    <p id="montoTotalDisplay" class="form-control-plaintext text-primary fw-bold"></p>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">Fecha de la Venta</label>

                                    <p id="fechaDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                            </div>



                            <div class="row mb-2">

                                <div class="col-md-4">

                                    <label class="form-label">Comisi�n AMIRI</label>

                                    <p id="comisionAmiriDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label">Comisi�n Promotor</label>

                                    <p id="comisionPromotorDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label">Monto Neto Cliente</label>

                                    <p id="montoNetoDisplay" class="form-control-plaintext text-success fw-bold"></p>

                                </div>

                            </div>



                            <div class="row mb-2">

                                <div class="col-md-4">

                                    <label class="form-label">Estado Actual</label>

                                    <p id="estadoActualDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label">Saldo Pendiente</label>

                                    <p id="saldoPendienteDisplay" class="form-control-plaintext text-danger fw-bold"></p>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label">Moneda</label>

                                    <p id="monedaDeudaDisplay" class="form-control-plaintext">

                                        <span id="badgeMoneda" class="badge bg-primary">MXN</span>

                                    </p>

                                </div>

                            </div>



                            <div class="row mb-3">

                                <div class="col-md-12">

                                    <label class="form-label">Cuenta para Pagar *</label>

                                    <select class="form-select" id="cuentaPagoSelect" name="cuenta_pago_id" required>

                                        <option value="">Selecciona una cuenta</option>

                                        <?php foreach ($cuentas as $c): ?>

                                            <option value="<?= $c['id'] ?>">

                                                <?= htmlspecialchars($c['nombre']) ?>

                                                (Saldo: $<?= number_format($c['saldo_actual'], 2) ?> MXN)

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <small class="text-muted">Se restar� de ambas cuentas: del cliente (digital) y de nuestra caja (efectivo f�sico entregado)</small>

                                </div>

                            </div>



                            <div class="row mb-3">

                                <div class="col-md-6">

                                    <label class="form-label">

                                        <i class="ti ti-coin"></i> Monto a pagar:

                                        <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">$</span>

                                        <input type="text" class="form-control" id="montoPagarFormatted" placeholder="0.00" required>

                                        <input type="hidden" name="monto_a_pagar" id="montoPagarHidden" required>

                                    </div>

                                    <div id="warningPagoExceso" class="alert alert-warning py-1 px-2 mt-1 mb-0" style="display:none;font-size:0.875rem;"></div>

                                    <div class="mt-2">

                                        <button type="button" class="btn btn-success btn-sm" id="btnLiquidarDeuda" style="display: none;">

                                            <i class="ti ti-cash"></i> Liquidar Deuda Total

                                        </button>

                                    </div>

                                    <small class="text-muted">Disponible en cuenta cliente: <span id="saldoCuentaCliente"></span></small>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">Fecha de Pago</label>

                                    <input type="date" class="form-control" id="fechaPago" name="fecha_pago" value="<?= date('Y-m-d') ?>">

                                </div>

                            </div>



                            <div class="alert alert-info" id="resumenPago" style="display: none;">

                                <h6>Resumen del Pago:</h6>

                                <p id="mensajePago"></p>

                            </div>

                        </div>



                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                            <button type="button" class="btn btn-primary" id="btnGuardarPago">Guardar Pago</button>

                        </div>

                    </form>

                </div>

            </div>

        </div>



        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <script src="src/assets/js/sidebarmenu.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script src="src/assets/js/dark-mode.js"></script>

        <script src="src/assets/js/pantalla-completa.js"></script>

        <script src="src/assets/js/utils.js"></script>



        <script>

            /* ============================================================

            /* ============================================================

               UTILIDADES

            ============================================================ */

            // Reset al abrir para nuevo

            $('button[data-bs-target="#modalGasto"]').on('click', function() {

                $('#formGasto')[0].reset();

                $('#id').val('');

                $('#montoFormateado').text(''); // Limpiar formato

                cargarTiposGasto(); // Cargar tipos al abrir

            });



            // Formatear monto en tiempo real como gu�a visual

            $('#inputMonto').on('input', function() {

                const valor = parseFloat($(this).val());

                const spanFormato = $('#montoFormateado');



                if (isNaN(valor) || valor === 0) {

                    spanFormato.text('');

                } else {

                    const formato = new Intl.NumberFormat('es-MX', {

                        style: 'currency',

                        currency: 'MXN',

                        minimumFractionDigits: 2,

                        maximumFractionDigits: 2

                    }).format(valor);

                    spanFormato.text('= ' + formato);

                }

            });



            function formatoMoneda(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString("en-US", {

                    style: "currency",

                    currency: "MXN",

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            function formatoNumero(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString("en-US", {

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            function parseMoneda(texto) {

                if (!texto) return 0;

                let valor = texto.replace(/[^\d.,]/g, "");

                const milesComma = /^(\d{1,3})(,\d{3})*(\.\d{1,2})?$/;

                if (milesComma.test(valor)) {

                    valor = valor.replace(/,/g, "");

                    return parseFloat(valor);

                }

                const milesDot = /^(\d{1,3})(\.\d{3})*(,\d{1,2})?$/;

                if (milesDot.test(valor)) {

                    valor = valor.replace(/\./g, "").replace(",", ".");

                    return parseFloat(valor);

                }

                return parseFloat(valor) || 0;

            }





            /**

             * Formatear moneda seg�n el tipo

             */

            function formatoMonedaDinamica(valor, moneda) {

                const n = parseFloat(valor) || 0;

                const simbolo = moneda === 'MXN' ? '$' : (moneda === 'USD' ? 'USD ' : 'USDT ');

                return simbolo + n.toLocaleString("en-US", {

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            /* ============================================================

               DATA TABLE

            ============================================================ */



            const tablaVentas = $('#tablaVentas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },

                ajax: {

                    url: 'api/ventas-efectivo-clientes.php?cliente_id=<?= $cliente_id ?>',

                    dataSrc: ""

                },

                columns: [
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function(data) {
                            if (data.estado !== 'pagado') {
                                return `<input type="checkbox" class="form-check-input pago-checkbox shadow-sm" style="cursor:pointer; width: 1.2rem; height: 1.2rem;" data-id="${data.id}" data-pendiente="${data.monto_neto_cliente - data.monto_pagado}">`;
                            }
                            return '';
                        }
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'fecha', // <-- Ahora jalamos la fecha operativa real
                        render: function(data, type, row) {
                            if (!data) return '';
                            if (type === 'sort' || type === 'type') return data;
                            // Formatear fecha operativa de YYYY-MM-DD a DD/MM/YYYY
                            const partes = data.split('-');
                            const fechaOperacion = partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : data;
                            
                            // Mostrar la fecha de captura original en peque�o
                            let infoExtra = '';
                            if (row.creado_en) {
                                const fechaC = new Date(row.creado_en);
                                if (!isNaN(fechaC)) {
                                    const captura = fechaC.toLocaleString('es-MX', {
                                        day: '2-digit', month: '2-digit', year: 'numeric',
                                        hour: '2-digit', minute: '2-digit', hour12: true
                                    });
                                    infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                                }
                            }
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            return formatoMonedaDinamica(data.monto_total, moneda);

                        }

                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            return formatoMonedaDinamica(data.comision_amiri, moneda);

                        }

                    },

                    {

                        data: null,

                        className: 'text-center',

                        render: function(data) {

                            const total = parseFloat(data.monto_total) || 0;

                            const com = parseFloat(data.comision_amiri) || 0;

                            if (total <= 0 || com <= 0) return '<span class="text-muted">�</span>';

                            const pct = (com / total) * 100;

                            return `<span class="badge bg-primary bg-opacity-10 text-primary">${pct.toFixed(2)}%</span>`;

                        }

                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            return formatoMonedaDinamica(data.comision_promotor, moneda);

                        }

                    },

                    {

                        data: null,

                        className: 'text-center',

                        render: function(data) {

                            const total = parseFloat(data.monto_total) || 0;

                            const com = parseFloat(data.comision_promotor) || 0;

                            if (total <= 0 || com <= 0) return '<span class="text-muted">�</span>';

                            const pct = (com / total) * 100;

                            return `<span class="badge bg-warning bg-opacity-10 text-warning">${pct.toFixed(2)}%</span>`;

                        }

                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            return formatoMonedaDinamica(data.monto_neto_cliente, moneda);

                        }

                    },

                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            const badgeMoneda = moneda === 'MXN' ? 'bg-primary bg-opacity-10 text-primary' : (moneda === 'USD' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info');
                            const estado = data.estado;
                            const clsEstado = estado === "pagado" ? "bg-success bg-opacity-10 text-success" :
                                estado === "parcial" ? "bg-warning bg-opacity-10 text-warning" : "bg-danger bg-opacity-10 text-danger";
                            return `<span class="badge ${badgeMoneda} me-1">${moneda}</span><span class="badge ${clsEstado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                        }
                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            return formatoMonedaDinamica(data.monto_pagado, moneda);

                        }

                    },

                    {

                        data: null,

                        render: function(data) {

                            const moneda = data.moneda || 'MXN';

                            const pendiente = data.monto_neto_cliente - data.monto_pagado;

                            return formatoMonedaDinamica(pendiente, moneda);

                        }

                    },

                    {
                        data: null,
                        render: function(data) {
                            let btnPagar = '';
                            if (data.estado !== 'pagado') {
                                // Bot�n Pagar estilo Badge (Naranja/Warning como lo ten�as originalmente)
                                btnPagar = `<button class="badge bg-warning bg-opacity-10 text-warning border-0 pagar me-2" style="cursor: pointer; font-size: 0.75rem;" data-id="${data.id}"><i class="ti ti-cash"></i> Pagar</button>`;
                            }
                            
                            // Bot�n Eliminar estilo Badge (Rojo/Danger)
                            let btnEliminar = `<button class="badge bg-danger bg-opacity-10 text-danger border-0 eliminar" style="cursor: pointer; font-size: 0.75rem;" data-id="${data.id}" title="Eliminar"><i class="ti ti-trash"></i> Eliminar</button>`;
                            
                            return btnPagar + btnEliminar;
                        }
                    }

                ],
                columnDefs: [
                    { targets: 0, orderable: false }
                ],
                order: [
                    [2, "desc"]
                ]
            });



            /* ============================================================

               MODAL - ABRIR Y CARGAR DATOS

            ============================================================ */
            // L�gica para Selecci�n M�ltiple
            let ventasSeleccionadas = [];
            let totalPendienteMultiple = 0;

            $(document).on('change', '#checkAllVentas', function() { $('.pago-checkbox').prop('checked', $(this).prop('checked')).trigger('change'); });

            $('#tablaVentas tbody').on('change', '.pago-checkbox', function() {
                const id = $(this).data('id');
                const pendiente = parseFloat($(this).data('pendiente'));

                if ($(this).is(':checked')) {
                    ventasSeleccionadas.push({ id, pendiente });
                    totalPendienteMultiple += pendiente;
                } else {
                    ventasSeleccionadas = ventasSeleccionadas.filter(val => val.id !== id);
                    totalPendienteMultiple -= pendiente;
                    $('#checkAllVentas').prop('checked', false);
                }

                $('#contadorSeleccionadas').text(ventasSeleccionadas.length);

                if (ventasSeleccionadas.length > 1) {
                    $('#btnPagarMultipleContainer').fadeIn();
                } else {
                    $('#btnPagarMultipleContainer').fadeOut();
                }
            });

            // Al hacer clic en el bot�n de pago m�ltiple
            $('#btnPagarMultiple').on('click', function() {
                let ids = [];
                let htmlPagos = '<h6 class="mb-3 text-primary"><i class="ti ti-list-check"></i> Ventas seleccionadas:</h6>';

                $('#tablaVentas .pago-checkbox:checked').each(function() {
                    let id = $(this).data('id');
                    let pendiente = parseFloat($(this).data('pendiente'));
                    ids.push(id);

                    htmlPagos += '<div class="d-flex justify-content-between align-items-center py-1 px-2 bg-white border rounded mb-1 pago-item" data-id="' + id + '" data-pendiente="' + pendiente + '"><span class="fw-bold">Venta #' + id + '</span><span class="text-danger fw-bold">' + formatoMoneda(pendiente) + '</span></div>';
                });

                $('#contenedorPagosMultiples').html(htmlPagos).fadeIn();

                $('#montoTotalDisplay').closest('.row').hide();
                $('#comisionAmiriDisplay').closest('.row').hide();
                $('#estadoActualDisplay').closest('.row').hide();
                $('#montoPagarFormatted').closest('.row.mb-3').hide();
                $('#grupoMontoGlobal').show();

                $('#cuentaPagoSelect').val('');
                $('#btnLiquidarDeuda').hide();
                $('#resumenPagoMultiple').hide();
                $('#fechaPago').val("<?= date('Y-m-d') ?>");

                var seleccionados = $('.pago-checkbox:checked');
                var count = seleccionados.length;
                var totalPendiente = 0;
                seleccionados.each(function() { totalPendiente += parseFloat($(this).data('pendiente')); });
                $('#resumenPagoMultiple').show().find('#nombreClientePago').text('Pago m\u00faltiple (' + count + ' ventas)');
                $('#alertaDeudaTotal').show();
                $('#deudaTotalCliente').text('$' + totalPendiente.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                $('#monto_a_pagar_input_multi').val(totalPendiente.toFixed(2));

                $('#formPagarVenta').data('modo', 'masivo').data('ids', ids);
                $('#modalPagarVenta').modal('show');
            });

            // Limpiar selecci�n cuando la tabla se recargue
            tablaVentas.on('draw', function() {
                ventasSeleccionadas = [];
                totalPendienteMultiple = 0;
                $('#btnPagarMultipleContainer').hide();
                $('#checkAllVentas').prop('checked', false);
            });


            $(document).on('input', '.input-abono-multiple', function() {
                let max = parseFloat($(this).attr('max')) || 0;
                let val = parseFloat($(this).val()) || 0;
                if (val > max) {
                    $(this).val(max.toFixed(2));
                    showToast("No puedes abonar m�s del saldo pendiente", "warning");
                }
            });

            $('#tablaVentas').on('click', '.pagar', function() {
                $('#contenedorPagosMultiples').hide().empty();

                $('#montoTotalDisplay').closest('.row').show();
                $('#comisionAmiriDisplay').closest('.row').show();
                $('#estadoActualDisplay').closest('.row').show();
                $('#montoPagarFormatted').closest('.row.mb-3').show();

                $('#montoPagarFormatted').prop('readonly', false);
                $('#formPagarVenta').data('modo', 'individual');

                const id = $(this).data("id");

                $.get('api/ventas-efectivo-clientes.php?id=' + id, data => {

                    const moneda = data.moneda || 'MXN';

                    $('#ventaId').val(data.id);
                    $('#montoTotalDisplay').text(formatoMonedaDinamica(data.monto_total, moneda));
                    $('#fechaDisplay').text(data.fecha);
                    $('#comisionAmiriDisplay').text(formatoMonedaDinamica(data.comision_amiri, moneda));
                    $('#comisionPromotorDisplay').text(formatoMonedaDinamica(data.comision_promotor, moneda));
                    $('#montoNetoDisplay').text(formatoMonedaDinamica(data.monto_neto_cliente, moneda));

                    const pendiente = data.monto_neto_cliente - data.monto_pagado;
                    $('#saldoPendienteDisplay').text(formatoMonedaDinamica(pendiente, moneda));
                    $('#estadoActualDisplay').text(data.estado.charAt(0).toUpperCase() + data.estado.slice(1));

                    const badgeClass = moneda === 'MXN' ? 'bg-primary bg-opacity-10 text-primary' : (moneda === 'USD' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info');
                    $('#badgeMoneda').attr('class', 'badge border-0 ' + badgeClass).text(moneda);

                    $('#montoPagarFormatted').data('moneda', moneda);
                    $('#saldoCuentaCliente').text(formatoMoneda(<?= $cliente['cuenta_saldo'] ?? 0 ?>));

                    $('#montoPagarFormatted').val("");
                    $('#montoPagarHidden').val("");
                    $('#cuentaPagoSelect').val("");
                    $('#montoFormateado').text('');
                    $('#btnLiquidarDeuda').hide();

                    $('#montoPagarFormatted').data('pendiente', pendiente);
                    $('#montoPagarFormatted').data('saldo-cliente', <?= $cliente['cuenta_saldo'] ?? 0 ?>);

                    actualizarResumenPago(data.monto_neto_cliente, data.monto_pagado, 0);
                    $('#resumenPago').show();

                    $('#modalPagarVenta').modal("show");
                });
            });



            // Escuchar cambios en selector de cuenta para mostrar/ocultar bot�n de liquidar

            $('#cuentaPagoSelect').on('change', function() {

                const selectedOption = $(this).find('option:selected');

                const cuentaTexto = selectedOption.text();

                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;

                

                // Extraer saldo de la cuenta del texto "(Saldo: $1,234.56)" o "(Saldo: USD 1,234.56)" o "(Saldo: USDT 1,234.56)"

                const matchSaldo = cuentaTexto.match(/Saldo:\s*(?:\$|USD\s*|USDT\s*)([\d,]+\.\d{2})/);

                

                if (matchSaldo) {

                    const saldoCuenta = parseFloat(matchSaldo[1].replace(/,/g, ''));

                    

                    // Mostrar bot�n solo si la cuenta tiene saldo suficiente

                    if (saldoCuenta >= pendiente && pendiente > 0) {

                        $('#btnLiquidarDeuda').show();

                    } else {

                        $('#btnLiquidarDeuda').hide();

                    }

                } else {

                    $('#btnLiquidarDeuda').hide();

                }

            });



            // Evento clic en bot�n Liquidar Deuda Total

            $('#btnLiquidarDeuda').on('click', function() {

                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;

                const moneda = $('#montoPagarFormatted').data('moneda') || 'MXN';

                

                // Autocompletar el input con la deuda total

                $('#montoPagarFormatted').val(pendiente.toFixed(2));

                $('#montoPagarHidden').val(pendiente.toFixed(2));

                

                // Actualizar formato visual con la moneda correcta

                $('#montoFormateado').text('= ' + formatoMonedaDinamica(pendiente, moneda));

                

                // Actualizar resumen

                actualizarResumenPago(pendiente, 0, pendiente);

            });



            // Formatear monto en tiempo real como gu�a visual

            $('#montoPagarFormatted').on('input', function() {

                let valor = $(this).val().replace(/[^0-9.]/g, ''); // Solo n�meros y punto

                const spanFormato = $('#montoFormateado');

                const moneda = $(this).data('moneda') || 'MXN';

                const pendiente = parseFloat($(this).data('pendiente')) || 0;



                // Actualizar campo hidden con valor sin formato

                $('#montoPagarHidden').val(valor);



                const valorNumerico = parseFloat(valor);

                const $warn = $('#warningPagoExceso');



                if (isNaN(valorNumerico) || valorNumerico === 0) {

                    spanFormato.text('');

                    $warn.hide();

                } else {

                    spanFormato.text('= ' + formatoMonedaDinamica(valorNumerico, moneda));



                    // Warning si el monto supera el pendiente

                    if (pendiente > 0 && valorNumerico > pendiente + 0.001) {

                        $warn.show().html(`<i class="ti ti-alert-triangle"></i> El monto ingresado (<strong>${formatoMonedaDinamica(valorNumerico, moneda)}</strong>) supera el adeudo pendiente (<strong>${formatoMonedaDinamica(pendiente, moneda)}</strong>)`);

                    } else {

                        $warn.hide();

                    }



                    // Actualizar resumen de pago

                    actualizarResumenPago(pendiente, 0, valorNumerico, moneda);

                }

            });



            function actualizarResumenPago(montoTotal, montoPagado, montoAdicional = 0, moneda = 'MXN') {

                montoTotal = parseFloat(montoTotal);

                montoPagado = parseFloat(montoPagado);

                montoAdicional = parseFloat(montoAdicional);



                let nuevoPagado = montoPagado + montoAdicional;

                let faltante = montoTotal - nuevoPagado;

                let estado = 'Pendiente';



                if (nuevoPagado >= montoTotal) estado = 'Pagado';

                else if (nuevoPagado > 0) estado = 'Parcial';



                let resumen = `Nuevo estado: <strong>${estado}</strong>.`;

                if (estado !== 'Pagado') {

                    resumen += ` Faltan ${formatoMonedaDinamica(faltante, moneda)} para saldar.`;

                } else {

                    resumen += ` <strong>�Venta saldada completamente!</strong>`;

                }



                $('#mensajePago').html(resumen);

            }



            /* ============================================================

               GUARDAR PAGO

            ============================================================ */



            $('#btnGuardarPago').on('click', function() {
                const cuentaPagoId = $('#cuentaPagoSelect').val();
                const fechaElegida = $('#fechaPago').val();
                const horaActual = new Date().toTimeString().split(' ')[0];
                const fechaPagoCompleta = fechaElegida ? (fechaElegida + ' ' + horaActual) : '';
                const modoPago = $('#formPagarVenta').data('modo') || 'individual';

                if (!cuentaPagoId) return alert("Selecciona una cuenta de pago.");

                let arrayPagos = [];

                if (modoPago === 'masivo') {
                    let montoTotal = parseFloat($('#monto_a_pagar_input_multi').val()) || 0;
                    if (montoTotal <= 0) return alert("Ingresa un monto valido.");
                    let restante = montoTotal;
                    $('.pago-item').each(function() {
                        let id = $(this).data('id');
                        let pendiente = parseFloat($(this).data('pendiente')) || 0;
                        let abono = Math.min(pendiente, restante);
                        if (abono > 0.01) {
                            arrayPagos.push({ id: id, abono: abono });
                            restante -= abono;
                        }
                    });
                    if (arrayPagos.length === 0) return alert("No se pudo distribuir el monto entre las ventas seleccionadas.");
                } else {
                    const id = $('#ventaId').val();
                    const montoAPagar = parseMoneda($('#montoPagarHidden').val());
                    if (montoAPagar <= 0) return alert("Ingresa un monto v�lido.");
                    arrayPagos.push({ id: id, abono: montoAPagar });
                }

                const btnOriginal = $(this).html();
                $(this).html('<i class="ti ti-loader"></i> Procesando...').prop('disabled', true);

                $.ajax({
                    url: 'api/ventas-efectivo-clientes.php',
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        pagos: arrayPagos,
                        cuenta_pago_id: cuentaPagoId,
                        fecha_pago: fechaPagoCompleta
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#modalPagarVenta').modal('hide');
                            showToast("Pago registrado correctamente. Redirigiendo a Flujo...", "success");
                            setTimeout(() => { window.location.href = 'flujo.php'; }, 1500);
                        }
                    },
                    error: function(xhr) {
                        let msg = "Error al guardar pago";
                        try { msg = xhr.responseJSON?.error || msg; } catch (e) {}
                        showToast(msg, "error");
                    },
                    complete: function() {
                        $('#btnGuardarPago').html(btnOriginal).prop('disabled', false);
                    }
                });
            });



            /* ============================================================

               DATA TABLE - VENTAS DIVISAS

            ============================================================ */



            // DataTables inside hidden tabs need columns adjusted on first show

            $('#tab-divisas-btn').one('shown.bs.tab', function() {

                tablaDivisas.columns.adjust().draw();

            });



            const tablaDivisas = $('#tablaVentasDivisas').DataTable({

                ajax: {

                    url: 'api/cliente-ventas-divisas.php?cliente_id=<?= $cliente_id ?>',

                    dataSrc: ""

                },

                order: [[0, "desc"]],
                columns: [
                    { 
                        data: 'fecha', // <-- Ahora jalamos la fecha operativa real
                        render: function(data, type, row) {
                            if (!data) return '';
                            if (type === 'sort' || type === 'type') return data;
                            const partes = data.split('-');
                            const fechaOperacion = partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : data;
                            
                            let infoExtra = '';
                            if (row.creado_en) {
                                const fechaC = new Date(row.creado_en);
                                if (!isNaN(fechaC)) {
                                    const captura = fechaC.toLocaleString('es-MX', {
                                        day: '2-digit', month: '2-digit', year: 'numeric',
                                        hour: '2-digit', minute: '2-digit', hour12: true
                                    });
                                    infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                                }
                            }
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },
                    {
                        data: 'moneda',
                        className: 'text-center',
                        render: function(data) {
                            const cls = data === 'USD' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info';
                            return `<span class="badge ${cls}">${data}</span>`;
                        }
                    },
                    {

                        data: 'cantidad',

                        render: function(data, type, row) {

                            return parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) + ' ' + row.moneda;

                        }

                    },

                    {

                        data: 'tc_venta',

                        render: function(data) {

                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 4, maximumFractionDigits: 4 });

                        }

                    },

                    {

                        data: 'total_mxn',

                        render: function(data) {

                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                        }

                    },

                    {

                        data: 'tc_costo_fifo',

                        render: function(data) {

                            if (!data) return '<span class="text-muted">�</span>';

                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 4, maximumFractionDigits: 4 });

                        }

                    },

                    {

                        data: 'ganancia_amiri',

                        render: function(data) {

                            const val = parseFloat(data);

                            const cls = val >= 0 ? 'text-success' : 'text-danger';

                            return `<span class="${cls}">$${val.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;

                        }

                    }

                ],

            });



            /* ============================================================

               ELIMINAR

            ============================================================ */



            $('#tablaVentas').on('click', '.eliminar', function() {

                const id = $(this).data("id");



                if (!confirm("�Eliminar esta venta? Esta acci�n no se puede deshacer.")) return;



                $.ajax({

                    url: "api/ventas-efectivo-clientes.php?id=" + id,

                    method: "DELETE",

                    success() {

                        tablaVentas.ajax.reload();

                        showToast("Venta eliminada", "success");

                    },

                    error() {

                        showToast("Error al eliminar", "error");

                    }

                });

            });



            /* ============================================================

               TOAST

            ============================================================ */



            function showToast(msg, type = "info") {

                let c = document.getElementById("toast-container");

                if (!c) {

                    c = document.createElement("div");

                    c.id = "toast-container";

                    c.style.position = "fixed";

                    c.style.top = "20px";

                    c.style.right = "20px";

                    c.style.zIndex = "99999";

                    document.body.appendChild(c);

                }



                const t = document.createElement("div");

                t.className = `toast bg-${type} text-white`;

                t.style.marginBottom = "10px";

                t.innerHTML = `<div class="toast-body">${msg}</div>`;

                c.appendChild(t);



                setTimeout(() => t.remove(), 3000);

            }

        </script>



</body>



</html>
