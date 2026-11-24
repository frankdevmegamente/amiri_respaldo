<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

    header('Location: login.php');

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



// Obtener el ID del prestamista de la URL

$prestamista_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);



if (!$prestamista_id) {

    die("Error: ID de prestamista no válido.");

}



// Función para obtener datos del prestamista

function getPrestamista($pdo, $id)

{

    $stmt = $pdo->prepare("SELECT id, nombre, telefono, email, direccion FROM prestamistas WHERE id = ?");

    $stmt->execute([$id]);

    return $stmt->fetch();

}



// Función para obtener cuentas activas de empresa (solo MXN)

// $soloConSaldo: true para pagos (dinero que sale), false para préstamos (dinero que entra)

function getCuentas($pdo, $soloConSaldo = false)

{

    $sql = "SELECT id, nombre, banco, saldo_actual, tipo_moneda FROM cuentas WHERE activo = 1 AND es_caja_empresa = 1 AND tipo_moneda = 'MXN' AND tipo_cuenta <> 3";

    if ($soloConSaldo) {

        $sql .= " AND COALESCE(saldo_actual,0) > 0";

    }

    $sql .= " ORDER BY nombre";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}



$requireEnv = __DIR__ . '/src/config/env.php';

if (file_exists($requireEnv)) {

    require_once $requireEnv;

}

require_once __DIR__ . '/src/config/db.php';



try {

    $pdo = getPDO();



    $prestamista = getPrestamista($pdo, $prestamista_id);



    if (!$prestamista) {

        die("Error: Prestamista no encontrado.");

    }



    $cuentasDestino = getCuentas($pdo, false); // Para préstamos (dinero entra)

    $cuentasPago = getCuentas($pdo, true);     // Para pagos (dinero sale, requiere saldo)

} catch (PDOException $e) {

    die("Error de conexión: " . $e->getMessage());

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Detalles de Préstamos - <?= htmlspecialchars($prestamista['nombre']) ?></title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css      " />

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
        [data-bs-theme="dark"] .modal-footer,
        [data-bs-theme="dark"] .table-light {
            background-color: var(--nx-surface) !important;
            border-color: var(--nx-border) !important;
            color: var(--nx-text) !important;
        }

        [data-bs-theme="dark"] .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        [data-bs-theme="dark"] .table,
        [data-bs-theme="dark"] .table thead th,
        [data-bs-theme="dark"] .table tbody td,
        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: var(--nx-surface) !important;
            color: var(--nx-text) !important;
            border-color: var(--nx-border) !important;
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) {
            background-color: var(--nx-surface-2) !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .input-group-text,
        [data-bs-theme="dark"] .form-control-plaintext,
        [data-bs-theme="dark"] .alert,
        [data-bs-theme="dark"] .badge,
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            background-color: var(--nx-surface-2) !important;
            color: var(--nx-text) !important;
            border-color: var(--nx-border) !important;
        }

        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #ffffff !important;
            border-bottom-color: #c58fb8 !important;
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
                    
                    <div class="card mov-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(197,143,184,0.14);">
                                            <i class="ti ti-user text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?= htmlspecialchars($prestamista['nombre']) ?></h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Detalle del prestamista</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <a href="prestamistas.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left"></i> Volver a Prestamistas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Información del Prestamista -->
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100" style="position: relative; overflow: hidden;">
                                
                                <div style="position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: var(--nx-primary); opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>
                                <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: var(--nx-success); opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

                                <div class="card-body p-4" style="position: relative; z-index: 1;">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="ti ti-user fs-5"></i>
                                        </div>
                                        <h5 class="card-title fw-semibold mb-0">Información del Prestamista</h5>
                                    </div>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-id fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Nombre</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($prestamista['nombre']) ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-phone fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Teléfono</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($prestamista['telefono'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-mail fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Email</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($prestamista['email'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                    <i class="ti ti-map-pin fs-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Dirección</p>
                                                    <h6 class="fw-bold mb-0 fs-5 mt-1"><?= htmlspecialchars($prestamista['direccion'] ?? 'N/A') ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Saldos -->
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="ti ti-report-money fs-5"></i>
                                        </div>
                                        <h5 class="card-title fw-semibold mb-0">Resumen de Saldos</h5>
                                    </div>
                                    <div id="resumenSaldos">
                                        <div class="text-center py-3">
                                            <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                                            <small class="text-muted ms-2">Cargando...</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario Nuevo Préstamo -->
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="ti ti-plus fs-5"></i>
                                        </div>
                                        <h5 class="card-title fw-semibold mb-0">Agregar Nuevo Préstamo</h5>
                                    </div>
                                    
                                    <form id="formNuevoPrestamo">
                                        <input type="hidden" name="prestamista_id" value="<?= $prestamista['id'] ?>">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-bold">
                                                    Monto
                                                    <span id="montoFormateadoNuevo" class="text-success fw-bold ms-2"></span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control" id="montoInputFormatted" name="monto_formatted" placeholder="0.00" required>
                                                    <input type="hidden" name="monto" id="montoInput" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Cuenta de Destino</label>
                                                <select class="form-select" name="cuenta_id" required>
                                                    <option value="">Selecciona una cuenta</option>
                                                    <?php foreach ($cuentasDestino as $c): 
                                                        $nombreCuenta = htmlspecialchars($c['nombre']);
                                                        if (!empty($c['banco'])) {
                                                            $nombreCuenta .= ' (' . htmlspecialchars($c['banco']) . ')';
                                                        }
                                                    ?>
                                                        <option value="<?= $c['id'] ?>"><?= $nombreCuenta ?> (Saldo: $<?= number_format((float)($c['saldo_actual'] ?? 0), 2, '.', ',') ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <input type="hidden" name="estado" value="pendiente">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Descripción</label>
                                                <input type="text" class="form-control" name="descripcion" placeholder="Descripción opcional">
                                            </div>
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-success w-100"><i class="ti ti-plus me-2"></i>Agregar Préstamo</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 px-4">
                                    <h5 class="mb-0 fw-semibold"><i class="ti ti-list-details me-2"></i>Préstamos Pendientes</h5>
                                    <button id="btnPagarSeleccionados" class="btn btn-success btn-sm" style="display: none;">
                                        <i class="ti ti-cash"></i> Pagar Seleccionados (<span id="countSeleccionados">0</span>)
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table id="tablaPrestamos" class="table table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30px;"><input type="checkbox" id="checkAllPrestamos"></th>
                                                    <th>Fecha</th>
                                                    <th>Monto</th>
                                                    <th>Descripción</th>
                                                    <th>Estado</th>
                                                    <th>Pagado</th>
                                                    <th>Concepto Pago</th>
                                                    <th>Fecha Pago</th>
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
        </div>





        <div class="modal fade" id="modalPagarPrestamo" tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Pago a Prestamista</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="formEditarPrestamo" method="POST" action="">

                        <input type="hidden" name="_method" value="PUT">

                        <input type="hidden" id="editarId" name="id">

                        <input type="hidden" id="montoPagadoHidden">

                        
                        <div class="modal-body">
    <div class="alert alert-info py-2" id="resumenPrestamista" style="display: none;">
        <small>Pagando a: <strong id="nombrePrestamistaPago"></strong></small>
    </div>

    <div class="alert alert-warning py-2 text-center" id="alertaDeudaTotalPrestamista" style="display: none;">
        <small>Deuda Total: <strong id="deudaTotalPrestamista">$0.00</strong></small>
    </div>

    <div id="contenedorPagosMultiples" class="mb-3 p-3 bg-light border rounded" style="display: none;"></div>

                    <div class="mb-3" id="grupoMontoGlobal" style="display: none;">
                        <label class="form-label fw-bold text-success">¿Cuánto dinero vas a pagar?</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" step="0.01" id="monto_a_pagar_input_multi" class="form-control form-control-lg text-success fw-bold">
                        </div>
                        <small class="text-muted">Se distribuirá automáticamente entre los préstamos seleccionados.</small>
                    </div>

                    <div class="row mb-2">

                                <div class="col-md-6">

                                    <label class="form-label">Monto Total del Préstamo</label>

                                    <p id="montoTotalDisplay" class="form-control-plaintext text-primary fw-bold"></p>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">Fecha del Préstamo</label>

                                    <p id="fechaDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                            </div>



                            <div class="row mb-2">

                                <div class="col-md-6">

                                    <label class="form-label">Estado Actual</label>

                                    <p id="estadoActualDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">Saldo Pendiente</label>

                                    <p id="saldoPendienteDisplay" class="form-control-plaintext text-danger fw-bold"></p>

                                </div>

                            </div>



                            <div class="row mb-2">

                                <div class="col-md-12">

                                    <label class="form-label">Descripción</label>

                                    <p id="descripcionDisplay" class="form-control-plaintext text-muted"></p>

                                </div>

                            </div>



                            <div class="row mb-3">

                                <div class="col-md-12">

                                    <label class="form-label">Caja de Origen</label>

                                    <select class="form-select" id="cuentaPagoSelect" name="cuenta_pago_id" required>

                                        <option value="">Selecciona una cuenta</option>

                                        <?php foreach ($cuentasPago as $c): 

                                            $moneda = $c['tipo_moneda'] ?? 'MXN';

                                            $simbolo = $moneda === 'MXN' ? '$' : ($moneda . ' ');

                                            $saldo = number_format((float)($c['saldo_actual'] ?? 0), 2, '.', ',');

                                            $nombreCuenta = htmlspecialchars($c['nombre']);

                                            if (!empty($c['banco'])) {

                                                $nombreCuenta .= ' (' . htmlspecialchars($c['banco']) . ')';

                                            }

                                        ?>

                                            <option value="<?= $c['id'] ?>"><?= $nombreCuenta ?> (Saldo: <?= $simbolo ?><?= $saldo ?>)</option>

                                        <?php endforeach; ?>

                                    </select>

                                    <small class="text-muted">Selecciona la cuenta desde donde se pagará al prestamista</small>

                                </div>

                            </div>



                            <div class="row mb-3">

                                <div class="col-md-6">

                                    <label class="form-label fw-bold text-success">
                                        ¿Cuánto dinero vas a pagar?
                                        <span id="montoFormateadoPago" class="text-success fw-bold ms-2"></span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control form-control-lg text-success fw-bold" id="montoPagarFormatted" name="monto_a_pagar_formatted" placeholder="0.00" required>
                                        <input type="hidden" name="monto_a_pagar" id="montoPagarHidden" required>
                                    </div>
                                    <small class="text-muted">Se distribuirá automáticamente entre los préstamos pendientes.</small>

                                    <div class="mt-2">

                                        <button type="button" class="btn btn-success btn-sm" id="btnLiquidarDeudaPrestamista" style="display: none;">

                                            <i class="ti ti-cash"></i> Liquidar Deuda Total

                                        </button>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">Fecha de Pago</label>

                                    <input type="date" class="form-control" id="editarFechaPago" name="fecha_pago" value="<?= date('Y-m-d') ?>">

                                </div>

                            </div>

                            <div class="row mb-3">

                                <div class="col-md-12">

                                    <label class="form-label">Concepto del Pago</label>

                                    <input type="text" class="form-control" id="conceptoPago" name="concepto_pago" placeholder="Ej: Pago mensual, Liquidación, etc.">

                                    <small class="text-muted">Descripción opcional que aparecerá en el movimiento</small>

                                </div>

                            </div>

                            <div class="alert alert-info" id="resumenPago" style="display: none;">

                                <h6>Resumen del Pago:</h6>

                                <p id="mensajePago"></p>

                            </div>

                        </div>



                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                            <button type="button" class="btn btn-success" id="btnGuardarEdicion">Confirmar</button>

                        </div>

                    </form>

                </div>

            </div>

        </div>



        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <script src="src/assets/js/sidebarmenu.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js      "></script>

        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js      "></script>

        <script src="src/assets/js/pantalla-completa.js"></script>

        <script src="src/assets/js/utils.js"></script>



        <script>

            /* ============================================================

   UTILIDADES

============================================================ */



            // Formatea número a moneda MXN

            function formatoMoneda(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString("en-US", {

                    style: "currency",

                    currency: "MXN",

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            // Formatea para inputs (solo cambia visual) sin símbolo $

            function formatearMoneda(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString("en-US", {

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



            // --- PARSEADOR DE MONEDA CORREGIDO (MX/EU) ---

            function parseMoneda(texto) {

                if (!texto) return 0;



                let valor = texto.replace(/[^\d.,]/g, "");



                // Formato MX: 1,234.56

                const milesComma = /^(\d{1,3})(,\d{3})*(\.\d{1,2})?$/;

                if (milesComma.test(valor)) {

                    valor = valor.replace(/,/g, "");

                    return parseFloat(valor);

                }



                // Formato EU: 1.234,56

                const milesDot = /^(\d{1,3})(\.\d{3})*(,\d{1,2})?$/;

                if (milesDot.test(valor)) {

                    valor = valor.replace(/\./g, "").replace(",", ".");

                    return parseFloat(valor);

                }



                return parseFloat(valor) || 0;

            }



            /* ============================================================

               DATA TABLE

            ============================================================ */



            const tablaPrestamos = $('#tablaPrestamos').DataTable({
                ajax: {
                    url: 'api/prestamos-prestamistas.php?prestamista_id=<?= $prestamista_id ?>',
                    dataSrc: ""
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            if (row.estado === 'pagado') return ''; // Ocultar si ya está pagado
                            let pendiente = parseFloat(row.monto) - parseFloat(row.monto_pagado);
                            return `<input type="checkbox" class="check-prestamo form-check-input" data-id="${row.id}" data-pendiente="${pendiente}">`;
                        }
                    },
                    {
                        data: 'fecha',
                        title: 'Fecha',
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            if (type === 'sort' || type === 'type') return data;
                            
                            const soloFecha = data.split(' ')[0]; 
                            const partes = soloFecha.split('-'); 
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            let infoExtra = '';
                            
                            if (row.creado_en) {
                                let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                                infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            }
                            
                            return `<span class="fw-bold text-body">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },

                    {

                        data: 'monto',

                        render: d => formatoMoneda(d)

                    },

                    {

                        data: 'descripcion'

                    },

                    {
                        data: 'estado',
                        render: estado => {
                            const cls = estado === "pagado" ? "success" : estado === "parcial" ? "warning" : "danger";
                            return `<span class="badge bg-${cls} bg-opacity-10 text-${cls} border-0">
                                ${estado.charAt(0).toUpperCase() + estado.slice(1)}
                            </span>`;
                        }
                    },
                    {
                        data: 'monto_pagado',
                        render: d => formatoMoneda(d)
                    },
                    {
                        data: 'ultimo_concepto_pago',
                        title: 'Concepto Pago',
                        render: function(data, type, row) {
                            if (!data || data === '') return '<span class="text-muted">—</span>';
                            let estado = row.estado || '';
                            let badge = estado === 'pagado' ? 'success' : estado === 'parcial' ? 'warning' : 'secondary';
                            return `<span class="badge bg-${badge} bg-opacity-10 text-${badge} border-0" style="font-size: 0.75rem;">${data}</span>`;
                        }
                    },
                    {
                        data: 'fecha_pago',
                        title: 'Fecha Pago',
                        render: function(data, type, row) {
                            if (!data || data === '0000-00-00 00:00:00' || data === '0000-00-00') return '<span class="text-muted">N/A</span>';
                            if (type === 'sort' || type === 'type') return data;
                            
                            const soloFecha = data.split(' ')[0]; 
                            const partes = soloFecha.split('-'); 
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(data) : data;
                            let infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            
                            return `<span class="fw-bold text-body">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            let btnPagar = '';
                            if (data.estado !== 'pagado') {
                                btnPagar = `<button class="badge bg-primary bg-opacity-10 text-primary border-0 pagar me-2" style="cursor: pointer; font-size: 0.75rem;" data-id="${data.id}" title="Pagar"><i class="ti ti-cash"></i> Pagar</button>`;
                            }
                            let btnEliminar = `<button class="badge bg-danger bg-opacity-10 text-danger border-0 eliminar" style="cursor: pointer; font-size: 0.75rem;" data-id="${data.id}" title="Eliminar"><i class="ti ti-trash"></i> Eliminar</button>`;
                            return `<div class="d-flex align-items-center">${btnPagar}${btnEliminar}</div>`;
                        }
                    }
                ],
                order: [[1, 'desc']],
                // Agregar el idioma español
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            /* ============================================================
               CARGA DE SALDOS / RESUMEN
            ============================================================ */
            function cargarResumenSaldos() {
                $.get('api/prestamos-prestamistas.php?resumen=1&prestamista_id=<?= $prestamista_id ?>', function(data) {
                    const totalPrestado = parseFloat(data.total_prestado) || 0;
                    const totalPagado = parseFloat(data.total_pagado) || 0;
                    const saldoPendiente = parseFloat(data.saldo_pendiente) || 0;

                    const pct = totalPrestado > 0 ? ((totalPagado / totalPrestado) * 100).toFixed(1) : 0;

                    $('#resumenSaldos').html(`
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                            <div>
                                <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Prestado</p>
                                <h4 class="fw-bold mb-0 text-primary mt-1">${formatoMoneda(totalPrestado)}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="ti ti-arrow-up-right text-primary fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                            <div>
                                <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Pagado</p>
                                <h4 class="fw-bold mb-0 text-success mt-1">${formatoMoneda(totalPagado)}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="ti ti-arrow-down-left text-success fs-5"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between p-3 bg-warning bg-opacity-10 rounded border border-warning border-opacity-25">
                            <div>
                                <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Saldo Pendiente</p>
                                <h3 class="fw-bold mb-0 text-warning mt-1">${formatoMoneda(saldoPendiente)}</h3>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning bg-opacity-20 text-dark">${pct}% pagado</span>
                            </div>
                        </div>
                    `);
                }).fail(function() {
                    $('#resumenSaldos').html('<div class="alert alert-danger mb-0">Error al cargar resumen</div>');
                });
            }

            cargarResumenSaldos();

            /* ============================================================
               LÓGICA DE PAGO MÚLTIPLE
            ============================================================ */
            $(document).on('change', '#checkAllPrestamos', function() {
                $('.check-prestamo').prop('checked', $(this).prop('checked')).trigger('change');
            });

            $(document).on('change', '.check-prestamo', function() {
                let seleccionados = $('.check-prestamo:checked');
                let totalPendiente = 0;
                
                seleccionados.each(function() {
                    totalPendiente += parseFloat($(this).data('pendiente'));
                });

                if (seleccionados.length > 0) {
                    $('#btnPagarSeleccionados').fadeIn();
                    $('#countSeleccionados').text(seleccionados.length);
                } else {
                    $('#btnPagarSeleccionados').fadeOut();
                    $('#checkAllPrestamos').prop('checked', false);
                }
            });

            $('#btnPagarSeleccionados').click(function() {
                let ids = [];
                let htmlPagos = '<h6 class="mb-3 text-primary"><i class="ti ti-list-check"></i> Préstamos seleccionados:</h6>';

                $('.check-prestamo:checked').each(function() {
                    let id = $(this).data('id');
                    let pendiente = parseFloat($(this).data('pendiente'));
                    ids.push(id);

                    htmlPagos += `
                        <div class="d-flex justify-content-between align-items-center py-1 px-2 bg-white border rounded mb-1 pago-item" data-id="${id}" data-pendiente="${pendiente}">
                            <span class="fw-bold">Préstamo #${id}</span>
                            <span class="text-danger fw-bold">${formatoMoneda(pendiente)}</span>
                        </div>
                    `;
                });

                $('#contenedorPagosMultiples').html(htmlPagos).fadeIn();

                $('#montoTotalDisplay').closest('.row').hide();
                $('#estadoActualDisplay').closest('.row').hide();
                $('#descripcionDisplay').closest('.row').hide();
                $('#montoPagarFormatted').closest('.row.mb-3').hide();
                $('#grupoMontoGlobal').show();

                $('#cuentaPagoSelect').val('');
                $('#btnLiquidarDeudaPrestamista').hide();
                $('#resumenPago').hide();
                $('#editarFechaPago').val("<?= date('Y-m-d') ?>");

                var seleccionados = $('.check-prestamo:checked');
                var count = seleccionados.length;
                var totalPendiente = 0;
                seleccionados.each(function() { totalPendiente += parseFloat($(this).data('pendiente')); });
                $('#resumenPrestamista').show().find('#nombrePrestamistaPago').text('Pago múltiple (' + count + ' préstamos)');
                $('#alertaDeudaTotalPrestamista').show();
                $('#deudaTotalPrestamista').text('$' + totalPendiente.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                $('#monto_a_pagar_input_multi').val(totalPendiente.toFixed(2));

                $('#formEditarPrestamo').data('modo', 'masivo').data('ids', ids);
                $('#modalPagarPrestamo').modal('show');
            });


            /* ============================================================

               MODAL - ABRIR Y CARGAR DATOS

            ============================================================ */



            $('#tablaPrestamos').on('click', '.pagar', function() {
                $('#contenedorPagosMultiples').hide().empty();
                $('#grupoMontoGlobal').hide();
                
                $('#montoTotalDisplay').closest('.row').show();
                $('#estadoActualDisplay').closest('.row').show();
                $('#descripcionDisplay').closest('.row').show();
                $('#montoPagarFormatted').closest('.row.mb-3').show(); 
                
                $('#montoPagarFormatted').prop('readonly', false); 
                $('#formEditarPrestamo').data('modo', 'individual');
                
                const id = $(this).data("id");



                $.get('api/prestamos-prestamistas.php?id=' + id, data => {



                    console.log("=== DATOS DEL PRÉSTAMO CARGADOS ===");

                    console.log(data);

                    console.log("Monto pagado anterior:", data.monto_pagado);

                    console.log("Monto total:", data.monto);



                    $('#editarId').val(data.id);



                    $('#montoPagadoHidden').val(data.monto_pagado);

                    $('#montoTotalDisplay').text(formatoMoneda(data.monto));

                    $('#fechaDisplay').text(data.fecha);

                    $('#descripcionDisplay').text(data.descripcion || "N/A");
$('#montoPagarFormatted').val("").prop('readonly', false); // <-- Aquí quitamos el candado
$('#montoPagarHidden').val("");

                    $('#montoFormateadoPago').text(''); // Limpiar formato visual

                    $('#cuentaPagoSelect').val(""); // Resetear cuenta de pago

                    $('#btnLiquidarDeudaPrestamista').hide(); // Ocultar botón al inicio



                    const pendiente = data.monto - data.monto_pagado;



                    $('#saldoPendienteDisplay').text(formatoMoneda(pendiente));

                    $('#estadoActualDisplay').text(

                        data.estado.charAt(0).toUpperCase() + data.estado.slice(1)

                    );



                    // Guardar pendiente para validaciones

                    $('#montoPagarFormatted').data('pendiente', pendiente);



                    // Mostrar resumen y deuda total
                    var prestamistaNombre = "<?= htmlspecialchars($prestamista['nombre']) ?>";
                    $('#resumenPrestamista').show().find('#nombrePrestamistaPago').text(prestamistaNombre);
                    $('#alertaDeudaTotalPrestamista').show();
                    $('#deudaTotalPrestamista').text('$' + pendiente.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $('#formEditarPrestamo').data('nombre', prestamistaNombre);
                    $('#resumenPago').show();
                    
                    // Si la fecha de pago ya existe usamos esa, si no, la de hoy
                    if(data.fecha_pago) {
                        $('#editarFechaPago').val(data.fecha_pago.split(' ')[0]);
                    } else {
                        $('#editarFechaPago').val("<?= date('Y-m-d') ?>");
                    }



                    $('#modalPagarPrestamo').modal("show");

                });

            });



            // Escuchar cambios en selector de cuenta para mostrar/ocultar botón de liquidar

            $('#cuentaPagoSelect').on('change', function() {

                const selectedOption = $(this).find('option:selected');

                const cuentaTexto = selectedOption.text();

                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;

                

                // Extraer saldo de la cuenta del texto "(Saldo: $1,234.56)"

                const matchSaldo = cuentaTexto.match(/Saldo:\s*\$([\d,]+\.\d{2})/);

                

                if (matchSaldo) {

                    const saldoCuenta = parseFloat(matchSaldo[1].replace(/,/g, ''));

                    

                    // Mostrar botón solo si la cuenta tiene saldo suficiente

                    if (saldoCuenta >= pendiente && pendiente > 0) {

                        $('#btnLiquidarDeudaPrestamista').show();

                    } else {

                        $('#btnLiquidarDeudaPrestamista').hide();

                    }

                } else {

                    $('#btnLiquidarDeudaPrestamista').hide();

                }

            });



            // Evento clic en botón Liquidar Deuda Total

            $('#btnLiquidarDeudaPrestamista').on('click', function() {

                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;

                

                // Autocompletar el input con la deuda total

                $('#montoPagarFormatted').val(pendiente.toFixed(2));

                $('#montoPagarHidden').val(pendiente.toFixed(2));

                

                // Actualizar formato visual

                const formato = new Intl.NumberFormat('es-MX', {

                    style: 'currency',

                    currency: 'MXN',

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                }).format(pendiente);

                $('#montoFormateadoPago').text('= ' + formato);

                

                // Actualizar resumen

                const montoTotal = parseFloat($('#montoTotalDisplay').text().replace(/[^0-9.-]+/g, ''));

                const montoPagado = parseFloat($('#montoPagadoHidden').val());

                actualizarResumenPago(montoTotal, montoPagado, pendiente);

            });



            // Función para actualizar el resumen visual del pago

            function actualizarResumenPago(montoTotal, montoPagado, montoAdicional = 0) {

                montoTotal = parseFloat(montoTotal);

                montoPagado = parseFloat(montoPagado);

                montoAdicional = parseFloat(montoAdicional);



                let nuevoPagado = montoPagado + montoAdicional;

                let faltante = montoTotal - nuevoPagado;

                let estado = 'Pendiente';



                if (nuevoPagado >= montoTotal) estado = 'Pagado';

                else if (nuevoPagado > 0) estado = 'Parcial';



                let resumen = `Nuevo estado: <strong>${estado.toLowerCase()}</strong>.`;

                if (estado !== 'Pagado') {

                    resumen += ` Faltan ${formatoMoneda(faltante)} para saldar.`;

                } else {

                    resumen += ` <strong>¡Deuda saldada!</strong>`;

                }



                $('#mensajePago').html(resumen);

            }



            /* ============================================================

               INPUT MONTO A PAGAR

            ============================================================ */



            $('#montoPagarFormatted').on('input', function() {

                let val = parseMoneda(this.value);

                const montoTotal = parseMoneda($('#montoTotalDisplay').text());

                const montoPagadoAnterior = parseMoneda($('#montoPagadoHidden').val() || 0);



                const pendiente = montoTotal - montoPagadoAnterior;



                if (val > pendiente) {

                    val = pendiente;

                    alert(`El monto no puede exceder el pendiente (${formatoMoneda(pendiente)}).`);

                }



                $('#montoPagarHidden').val(val);

                // $(this).val(formatearMoneda(val));



                // Actualizar formato visual en tiempo real

                const spanFormato = $('#montoFormateadoPago');

                if (val === 0 || isNaN(val)) {

                    spanFormato.text('');

                } else {

                    const formato = new Intl.NumberFormat('es-MX', {

                        style: 'currency',

                        currency: 'MXN',

                        minimumFractionDigits: 2,

                        maximumFractionDigits: 2

                    }).format(val);

                    spanFormato.text('= ' + formato);

                }



                actualizarResumenPago(montoTotal, montoPagadoAnterior, val);



                $('#resumenPago').show();

            });



            // Resetear modal al cerrarse
            $('#modalPagarPrestamo').on('hidden.bs.modal', function() {
                $('#resumenPrestamista').hide();
                $('#alertaDeudaTotalPrestamista').hide();
                $('#grupoMontoGlobal').hide();
            });

            /* ============================================================

               GUARDAR PAGO

            ============================================================ */



            $(document).on('click', '#btnGuardarEdicion', function() {
                const cuentaPagoId = $('#cuentaPagoSelect').val();
                const fechaPagoCompletada = $('#editarFechaPago').val() + ' ' + new Date().toTimeString().split(' ')[0];

                if (!cuentaPagoId) return alert("Selecciona una cuenta de pago.");

                let modoPago = $('#formEditarPrestamo').data('modo');
                let arrayPagos = []; // Aquí guardaremos la lista exacta de qué pagar

                if (modoPago === 'masivo') {
                    let montoTotal = parseFloat($('#monto_a_pagar_input_multi').val()) || 0;
                    if (montoTotal <= 0) return alert("Ingresa un monto válido.");
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
                    if (arrayPagos.length === 0) return alert("No se pudo distribuir el monto entre los préstamos seleccionados.");
                } else {
                    // Pago individual normal
                    const id = $('#editarId').val();
                    const montoAPagar = parseMoneda($('#montoPagarFormatted').val());
                    if (montoAPagar <= 0) return alert("Ingresa un monto válido.");
                    arrayPagos.push({ id: id, abono: montoAPagar });
                }

                // Deshabilitar botón para evitar doble clic
                const btnOriginal = $(this).html();
                $(this).html('<i class="ti ti-loader"></i> Procesando...').prop('disabled', true);

                $.ajax({
                    url: 'api/prestamos-prestamistas.php',
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        pagos: arrayPagos, // Enviamos el arreglo con las instrucciones exactas
                        cuenta_pago_id: cuentaPagoId,
                        fecha_pago: fechaPagoCompletada,
                        descripcion: $('#conceptoPago').val()
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#modalPagarPrestamo').modal('hide');
                            showToast("Pago registrado correctamente", "success");
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        let msg = "Error al guardar";
                        try { msg = xhr.responseJSON?.error || msg; } catch (e) {}
                        showToast(msg, "error");
                    },
                    complete: function() {
                        $('#btnGuardarEdicion').html(btnOriginal).prop('disabled', false);
                    }
                });
            });



            /* ============================================================

               AGREGAR NUEVO PRÉSTAMO

            ============================================================ */


            // Formatear monto en tiempo real para nuevo préstamo

            $('#montoInputFormatted').on('input', function() {

                const val = parseMoneda(this.value);

                $('#montoInput').val(val);

                

                // Actualizar formato visual en tiempo real

                const spanFormato = $('#montoFormateadoNuevo');

                if (val === 0 || isNaN(val)) {

                    spanFormato.text('');

                } else {

                    const formato = new Intl.NumberFormat('es-MX', {

                        style: 'currency',

                        currency: 'MXN',

                        minimumFractionDigits: 2,

                        maximumFractionDigits: 2

                    }).format(val);

                    spanFormato.text('= ' + formato);

                }

            });



            $('#formNuevoPrestamo').on('submit', function(e) {

                e.preventDefault();

                const fechaElegida = $('input[name="fecha"]').val();

                $.post(

                    "api/prestamos-prestamistas.php", {

                        prestamista_id: $('input[name="prestamista_id"]').val(),

                        monto: parseMoneda($('#montoInput').val()),

                        fecha: fechaElegida,

                        cuenta_id: $('select[name="cuenta_id"]').val(),

                        estado: "pendiente",

                        descripcion: $('textarea[name="descripcion"]').val()

                    },

                    res => {

                        if (res.success) {

                            this.reset();

                            $('#montoFormateadoNuevo').text(''); // Limpiar formato visual

                            tablaPrestamos.ajax.reload();

                            cargarResumenSaldos();

                            showToast("Préstamo agregado", "success");

                        } else {

                            showToast("Error: " + (res.error || "Desconocido"), "error");

                        }

                    },

                    "json"

                );

            });



            /* ============================================================

               ELIMINAR

            ============================================================ */



            $('#tablaPrestamos').on('click', '.eliminar', function() {

                const id = $(this).data("id");



                if (!confirm("¿Eliminar este préstamo?")) return;



                $.ajax({

                    url: "api/prestamos-prestamistas.php?id=" + id,

                    method: "DELETE",

                    success() {

                        tablaPrestamos.ajax.reload();

                        cargarResumenSaldos();

                        showToast("Préstamo eliminado", "success");

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
