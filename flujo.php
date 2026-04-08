<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



$requireEnv = __DIR__ . '/src/config/env.php';

if (file_exists($requireEnv)) require_once $requireEnv;

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();



// Servicios MXN (de BD)

$serviciosMXN = $pdo->query("

    SELECT id, nombre FROM servicios

    WHERE activo = 1 AND id IN (1, 4, 7)

    ORDER BY FIELD(id, 1, 4, 7)

")->fetchAll(PDO::FETCH_ASSOC);



// Servicios Divisas (IDs reales de la BD)

$serviciosDivisas = [

    ['id' => 5,  'nombre' => 'Compra de Dólares'],

    ['id' => 2,  'nombre' => 'Venta de Dólares'],

    ['id' => 6,  'nombre' => 'Compra de USDT'],

    ['id' => 3,  'nombre' => 'Venta de USDT'],

];

?>

<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8">

    <title>Flujo - Amiri</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">

    <link rel="stylesheet" href="src/assets/css/styles.min.css">

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="src/assets/css/dark-mode.css">

    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">

    <link rel="stylesheet" href="src/assets/css/layout-custom.css">

    <style>

    /* =========================================
   🌙 ESTILOS PARA TARJETAS DE RESUMEN EN MODO OSCURO (BLINDADO)
   ========================================= */

/* 1. RECUPERAR EL TAMAÑO ORIGINAL (Venciendo la regla global de padding) */
body[data-bs-theme="dark"] .card .card-header.header-saldos,
body[data-bs-theme="dark"] .card .card-header.header-capital,
body[data-bs-theme="dark"] .card .card-header.header-prestamos {
    padding: 16px 20px !important; /* Devuelve el tamaño grande */
    border-radius: 12px 12px 0 0 !important;
}

/* 2. SALDOS BANCARIOS (SUCCESS) */
body[data-bs-theme="dark"] .card .header-saldos {
    background-color: rgba(102, 187, 106, 0.15) !important;
    border-bottom: 1px solid rgba(102, 187, 106, 0.3) !important;
}
body[data-bs-theme="dark"] .card .header-saldos span,
body[data-bs-theme="dark"] .card .header-saldos i {
    color: #66bb6a !important; /* Verde exacto de tu plantilla */
}

/* 3. CAPITAL (WARNING - Este color faltaba en tu dark-mode.css) */
body[data-bs-theme="dark"] .card .header-capital {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border-bottom: 1px solid rgba(255, 193, 7, 0.3) !important;
}
body[data-bs-theme="dark"] .card .header-capital span,
body[data-bs-theme="dark"] .card .header-capital i {
    color: #ffc107 !important; /* Amarillo brillante */
}

/* 4. PRÉSTAMOS (DANGER) */
body[data-bs-theme="dark"] .card .header-prestamos {
    background-color: rgba(239, 83, 80, 0.15) !important;
    border-bottom: 1px solid rgba(239, 83, 80, 0.3) !important;
}
body[data-bs-theme="dark"] .card .header-prestamos span,
body[data-bs-theme="dark"] .card .header-prestamos i {
    color: #ef5350 !important; /* Rojo exacto de tu plantilla */
}

        #modalDetalle .table-hover tbody tr:hover,

        #modalDetalle .table-hover tbody tr:hover td {

            background-color: rgba(255, 255, 255, 0.95) !important;

            color: inherit !important;

        }

        #modalDetalle .table-hover tbody tr:hover .text-muted  { color: #000 !important; }

        #modalDetalle .table-hover tbody tr:hover .text-success { color: #28a745 !important; }

        #modalDetalle .table-hover tbody tr:hover .text-danger  { color: #dc3545 !important; }

        #modalDetalle .table-hover tbody tr:hover .text-warning { color: #ffc107 !important; }



        .badge-usd  { background: linear-gradient(135deg, #0ea5e9, #2563eb); color: white; }

        .badge-usdt { background: linear-gradient(135deg, #10b981, #059669); color: white; }
 body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
        /* =========================================
   🌙 REGLAS PARA MODO OSCURO (Usuarios y DataTables)
   ========================================= */

/* 1. Textos generales y Tarjetas */
[data-bs-theme="dark"] h3,
[data-bs-theme="dark"] .page-breadcrumb h3 {
    color: #e9ecef !important;
}

[data-bs-theme="dark"] .card {
    background: #1a2234;
    border-color: #2c344c;
}

/* 2. Tabla principal (DataTables) */
[data-bs-theme="dark"] .table>thead>tr>th {
    background-color: #1a2234 !important;
    color: #e9ecef !important;
    border-bottom-color: #2c344c !important;
}

[data-bs-theme="dark"] .table>tbody>tr>td {
    color: #e9ecef !important;
    border-color: #2c344c !important;
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
    color: #8c9ea8 !important;
}

/* Estilo para los botones de paginación */
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
    color: #8c9ea8 !important;
}
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #252f43 !important;
    border-color: #2c344c !important;
    color: #e9ecef !important;
}

/* Inputs del buscador de DataTables */
[data-bs-theme="dark"] .dataTables_wrapper select,
[data-bs-theme="dark"] .dataTables_wrapper input {
    background-color: #252f43;
    border: 1px solid #2c344c;
    color: #e9ecef;
}

/* 4. Modal de Agregar/Editar Usuario */
[data-bs-theme="dark"] .modal-content {
    background-color: #1a2234;
    border-color: #2c344c;
}
[data-bs-theme="dark"] .modal-header {
    border-bottom-color: #2c344c;
}
[data-bs-theme="dark"] .modal-footer {
    border-top-color: #2c344c;
}
[data-bs-theme="dark"] .modal-title {
    color: #e9ecef;
}
[data-bs-theme="dark"] .form-label {
    color: #e9ecef;
}

/* Campos de texto y select dentro del Modal */
[data-bs-theme="dark"] .form-control,
[data-bs-theme="dark"] .form-select {
    background-color: #252f43;
    border-color: #2c344c;
    color: #e9ecef;
}
[data-bs-theme="dark"] .form-control::placeholder {
    color: #8c9ea8;
}
/* =========================================
   🌙 ESTILOS PARA TARJETAS DE RESUMEN EN MODO OSCURO Y CLARO
   ========================================= */

/* 1. ASEGURAR TAMAÑO Y FORMATO EN AMBOS MODOS (Claro y Oscuro) */
.header-saldos, .header-capital, .header-prestamos {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    padding: 16px 20px !important;
    display: flex;
    align-items: center;
}

/* 2. MODO OSCURO: FONDOS TRANSLÚCIDOS (Efecto Badge) */
body[data-bs-theme="dark"] .header-saldos, .dark-mode .header-saldos {
    background-color: rgba(25, 135, 84, 0.15) !important;
    border-bottom: 1px solid #2c344c !important;
}
body[data-bs-theme="dark"] .header-capital, .dark-mode .header-capital {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border-bottom: 1px solid #2c344c !important;
}
body[data-bs-theme="dark"] .header-prestamos, .dark-mode .header-prestamos {
    background-color: rgba(220, 53, 69, 0.15) !important;
    border-bottom: 1px solid #2c344c !important;
}

/* 3. MODO OSCURO: FORZAR COLORES DE TEXTO E ICONOS (Para vencer al text-white) */
body[data-bs-theme="dark"] .header-saldos, .dark-mode .header-saldos,
body[data-bs-theme="dark"] .header-saldos *, .dark-mode .header-saldos * {
    color: #36c76c !important; /* Verde brillante */
}
body[data-bs-theme="dark"] .header-capital, .dark-mode .header-capital,
body[data-bs-theme="dark"] .header-capital *, .dark-mode .header-capital * {
    color: #ffc107 !important; /* Amarillo brillante */
}
body[data-bs-theme="dark"] .header-prestamos, .dark-mode .header-prestamos,
body[data-bs-theme="dark"] .header-prestamos *, .dark-mode .header-prestamos * {
    color: #fc4b6c !important; /* Rojo brillante */
}
    </style>

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"

        data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 95%;">

                    <div class="row mb-3 mt-2">
                        <div class="col-12 d-flex align-items-center gap-2">
                            <h3 class="mb-0"><i class="ti ti-exchange"></i> Flujo</h3>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow border-3" id="cardDiferencia">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center">
                                                <div class="p-3 rounded-circle me-3" id="diferenciaIcon" style="background-color: rgba(255,255,255,0.2);">
                                                    <i class="ti ti-calculator" style="font-size: 2rem;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-muted">DIFERENCIA</h6>
                                                    <p class="mb-0" id="diferenciaTexto" style="font-size: 0.9rem;">Calculando...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h1 class="mb-0 fw-bold" id="diferenciaMonto">$0.00</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">

                        <div class="col-lg-4 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header header-saldos bg-success text-white fw-bold fs-5 py-3">
    <span><i class="ti ti-wallet"></i> SALDOS BANCARIOS</span>
</div>
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                            <tr>
                                                <th>Cuenta</th>
                                                <th>Tipo</th>
                                                <th class="text-end" id="th-saldos-monto">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="saldosBody">
                                            <tr><td colspan="3">Cargando...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header header-capital bg-warning text-white fw-bold fs-5 py-3">
    <span><i class="ti ti-building-bank"></i> CAPITAL</span>
</div>
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                            <tr>
                                                <th>Cuenta</th>
                                                <th>Tipo</th>
                                                <th class="text-end" id="th-capital-monto">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="capitalBody">
                                            <tr><td colspan="3">Cargando...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header header-prestamos bg-danger text-white fw-bold fs-5 py-3">
    <span><i class="ti ti-coin"></i> PRÉSTAMOS / PROVEEDORES</span>
</div>
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th class="text-end">MXN</th>
                                            </tr>
                                        </thead>
                                        <tbody id="obligacionesBody">
                                            <tr><td colspan="3">Cargando...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-check form-switch mb-0 me-2">
                                <input class="form-check-input" type="checkbox" id="switchMXN" role="switch">
                                <label class="form-check-label fw-semibold" for="switchMXN">
                                    <i class="ti ti-currency-dollar"></i> Ver en MXN
                                </label>
                            </div>
                            <div class="ms-auto d-flex gap-2">
                                <button class="btn btn-outline-danger" id="btnRevertirUltimo">
                                    <i class="ti ti-arrow-back-up"></i> Revertir Último
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFlujo">
                                    + Nuevo Movimiento
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white pt-4 pb-0 border-bottom">
                                    <ul class="nav nav-tabs border-0" id="tabsFlujo" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active border-0 border-bottom border-2 border-primary text-primary fw-semibold" id="tab-activos" data-vista="activos" data-bs-toggle="tab" type="button" role="tab" style="background: transparent;">
                                                <i class="ti ti-list-check fs-5 me-1"></i> Movimientos Activos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link border-0 text-muted fw-semibold" id="tab-anulados" data-vista="anulados" data-bs-toggle="tab" type="button" role="tab" style="background: transparent;">
                                                <i class="ti ti-trash-x fs-5 me-1"></i> Anulados / Revertidos
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <table id="tablaFlujo" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>Grupo</th>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Servicio</th>
                                                <th>Movimientos</th>
                                                <th>Monto</th>
                                                <th>Moneda</th>
                                                <th>Involucrados</th>
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



    <!-- Modal Nuevo Movimiento -->

    <div class="modal fade" id="modalFlujo" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">Nuevo Movimiento</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <form id="formFlujo">

                    <div class="modal-body">

                        <input type="hidden" id="movimientoId" name="id">



                        <!-- Fecha -->

                        <div class="mb-3">

                            <label class="form-label">Fecha</label>

                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>

                        </div>



                        <!-- Servicio -->

                        <div class="mb-3">

                            <label class="form-label">Servicio</label>

                            <select class="form-select" name="servicio" id="servicioSelect" required>

                                <option value="">-- Selecciona un servicio --</option>

                                <optgroup label="— MXN —">

                                    <?php foreach ($serviciosMXN as $s): ?>

                                        <option value="<?= $s['nombre'] ?>" data-id="<?= $s['id'] ?>"><?= $s['nombre'] ?></option>

                                    <?php endforeach; ?>

                                </optgroup>

                                <optgroup label="— Divisas —">

                                    <?php foreach ($serviciosDivisas as $s): ?>

                                        <option value="<?= $s['nombre'] ?>" data-id="<?= $s['id'] ?>"><?= $s['nombre'] ?></option>

                                    <?php endforeach; ?>

                                </optgroup>

                            </select>

                        </div>



                        <!-- Cliente -->

                        <div class="mb-3" id="clienteGroup" style="display:none;">

                            <label class="form-label">Cliente</label>

                            <select class="form-select" name="cliente_id" id="clienteSelect">

                                <option value="">-- Sin cliente --</option>

                            </select>

                        </div>



                        <!-- Proveedor -->

                        <div class="mb-3" id="proveedorGroup" style="display:none;">

                            <label class="form-label">Proveedor</label>

                            <select class="form-select" name="proveedor_id" id="proveedorSelect">

                                <option value="">-- Sin proveedor --</option>

                            </select>

                        </div>



                        <!-- Cuenta -->

                        <div class="mb-3" id="cuentaGroup" style="display:none;">

                            <label class="form-label" id="cuentaLabel">Cuenta</label>

                            <select class="form-select" name="cuenta_id" id="cuentaSelect">

                                <option value="">-- Selecciona una cuenta --</option>

                            </select>

                        </div>



                        <!-- Campos dinámicos -->

                        <div id="camposDinamicos"></div>



                        <!-- Totales / Desglose -->

                        <div class="mb-3" id="totalesGroup" style="display:none;">

                            <div class="card border-primary">

                                <div class="card-header bg-light">

                                    <h6 class="mb-0"><i class="ti ti-calculator"></i> Desglose de la Operación</h6>

                                </div>

                                <div class="card-body" id="desgloseBody"></div>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button type="submit" class="btn btn-primary" id="botonGuardar">Guardar</button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- Modal Detalle -->

    <div class="modal fade" id="modalDetalle" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">Detalle del Movimiento</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body" id="detalleContent"></div>

                <div class="modal-footer">

                    <div class="me-auto" id="detalleEstado"></div>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>



    <!-- Modal Anulación -->

    <div class="modal fade" id="modalAnular" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-danger text-white">

                    <h5 class="modal-title"><i class="ti ti-alert-triangle"></i> Anular Movimiento</h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-warning">

                        <i class="ti ti-info-circle"></i> Esta acción creará un movimiento de reverso. Los saldos se ajustarán automáticamente.

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">Motivo de la anulación <span class="text-danger">*</span></label>

                        <textarea id="motivoAnulacion" class="form-control" rows="3" placeholder="Explique el motivo..."></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <button type="button" id="btnConfirmarAnular" class="btn btn-danger">

                        <i class="ti ti-check"></i> Confirmar Anulación

                    </button>

                </div>

            </div>

        </div>

    </div>



    <!-- Modal Revertir Último Movimiento -->

    <div class="modal fade" id="modalRevertirUltimo" tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">

                    <h5 class="modal-title"><i class="ti ti-arrow-back-up"></i> Revertir Último Movimiento</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body" id="revertirUltimoBody">

                    <div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <button type="button" id="btnConfirmarRevertir" class="btn btn-warning" disabled>

                        <i class="ti ti-arrow-back-up"></i> Confirmar Reversión

                    </button>

                </div>

            </div>

        </div>

    </div>



    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="src/assets/js/dark-mode.js"></script>

    <script src="src/assets/js/utils.js"></script>

    <script src="src/assets/js/pantalla-completa.js"></script>



    <style>

        [data-bs-theme="dark"] #totalesGroup .card            { background: #0f172a; border-color: #1f2937; }

        [data-bs-theme="dark"] #totalesGroup .card-header     { background: linear-gradient(135deg, #1d4ed8, #2563eb); border-color: transparent; color: #e5e7eb; }

        [data-bs-theme="dark"] #desgloseBody table            { background: #0b1220; color: #e5e7eb; }

        [data-bs-theme="dark"] #desgloseBody .table-warning   { background: #4b5563; color: #e5e7eb; }

        [data-bs-theme="dark"] #desgloseBody .table-info      { background: #374151; color: #e5e7eb; }

        [data-bs-theme="dark"] #desgloseBody .table-success   { background: #14532d; color: #e5e7eb; }

        [data-bs-theme="dark"] #desgloseBody .alert-info      { background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #e0f2fe; border: none; }

        [data-bs-theme="dark"] .modal-content                 { background: #0b1220; border-color: #1f2937; }

        [data-bs-theme="dark"] .input-group-text,

        [data-bs-theme="dark"] .form-control,

        [data-bs-theme="dark"] .form-select                   { background: #111827; border-color: #1f2937; color: #e5e7eb; }

        [data-bs-theme="dark"] .form-control:focus,

        [data-bs-theme="dark"] .form-select:focus             { border-color: #2563eb; box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.35); }



        #modalFlujo .form-select,

        #modalFlujo .form-control  { height: 48px; padding: 12px 16px; font-size: 1rem; cursor: pointer; }

        #modalFlujo .form-label    { font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }

        #modalFlujo .form-select   { background-position: right 16px center; }

    </style>



    <script>

    $(document).ready(function () {



        // ─── Formateadores ───────────────────────────────────────────────────

        function formatMXN(v) {

            return (parseFloat(v) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        }

        function formatNativo(v) {

            return (parseFloat(v) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        }



        // ─── Switch: mostrar en MXN o en moneda nativa ───────────────────────

        let mostrarEnMXN = false;

        $('#switchMXN').on('change', function () {

            mostrarEnMXN = $(this).is(':checked');

            let label = mostrarEnMXN ? 'MXN' : 'Monto';

            $('#th-saldos-monto, #th-capital-monto').text(label);

            cargarSaldos();

            cargarCapital();

            cargarObligaciones();

        });



        // ─── DataTable ───────────────────────────────────────────────────────

        var table = $('#tablaFlujo').DataTable({

            ajax: { url: 'api/flujo-lista.php?servicios_ids=1,2,3,4,5,6,7,8,9,10,11,12,13,14,15', dataSrc: 'data' },

            columns: [
                {
                    data: 'grupo_id', title: 'Grupo', visible: false,
                    render: d => '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">#' + d + '</span>'
                },
                { 
                    data: 'fecha', title: 'Fecha',
                    render: function(data, type, row) {
                        if (!data) return '';
                        // Esta es la fecha REAL de la operación que tú elegiste
                        const partes = data.split('-');
                        const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`;
                        
                        let infoExtra = '';
                        
                        // Si el movimiento fue anulado, mostrar con letras rojas cuándo lo anulaste
                        if (row.estado === 'anulado' || row.estado === 'corregido') {
                            let anulado = (row.corregido_en && typeof formatearFechaHora === 'function') ? formatearFechaHora(row.corregido_en) : (row.corregido_en || row.creado_en);
                            infoExtra = `<br><small class="text-danger" style="font-size: 0.7rem;"><i class="ti ti-trash-x"></i> Anulado: ${anulado}</small>`;
                        } 
                        // Si el movimiento es normal, mostrar cuándo lo capturaste
                        else if (row.creado_en) {
                            let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                            infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                        }
                        
                        // La fecha operativa siempre manda arriba y en negritas
                        return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
                    }
                },
                {
                    data: 'tipo_principal', title: 'Tipo', defaultContent: '',
                    render: function (d) {
                        if (!d) return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">-</span>';
                        if (d === 'Entrada')       return '<span class="badge bg-success bg-opacity-10 text-success border-0">✓ Entrada</span>';
                        if (d === 'Salida')        return '<span class="badge bg-danger bg-opacity-10 text-danger border-0">✗ Salida</span>';
                        if (d === 'Traspaso')      return '<span class="badge bg-primary bg-opacity-10 text-primary border-0">⇄ Traspaso</span>';
                        if (d === 'Entrada/Salida') return '<span class="badge bg-warning bg-opacity-10 text-warning border-0">⇅ Entrada/Salida</span>';
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">' + d + '</span>';
                    }
                },
                {
                    data: 'servicio', title: 'Servicio',
                    render: d => '<strong class="text-dark">' + d + '</strong>'
                },
                {
                    data: 'cantidad_movimientos', title: 'Movimientos',
                    render: d => '<span class="badge bg-info bg-opacity-10 text-info border-0">' + d + ' mov.</span>'
                },
                {
                    data: null, title: 'Monto',
                    render: function (data, type, row) {
                        let monto = parseFloat(row.monto_principal);
                        return '<span class="fw-bold text-success">$' + monto.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
                    }
                },
                {
                    data: 'moneda', title: 'Moneda',
                    render: function (d) {
                        if (d === 'USD')  return '<span class="badge bg-primary bg-opacity-10 text-primary border-0">USD</span>';
                        if (d === 'USDT') return '<span class="badge bg-info bg-opacity-10 text-info border-0">USDT</span>';
                        return '<span class="badge bg-success bg-opacity-10 text-success border-0">' + d + '</span>';
                    }
                },
                {
                    data: null, title: 'Involucrados',
                    render: function (data, type, row) {
                        let html = '<div style="font-size:0.85rem; line-height: 1.4;">';
                        if (row.cliente  && row.cliente.trim())  html += '<div><i class="ti ti-user text-primary"></i> <strong class="text-dark">Cliente:</strong> <span class="text-muted">' + row.cliente + '</span></div>';
                        if (row.proveedor && row.proveedor.trim()) html += '<div><i class="ti ti-building text-info"></i> <strong class="text-dark">Proveedor:</strong> <span class="text-muted">' + row.proveedor + '</span></div>';
                        if (row.promotor  && row.promotor.trim())  html += '<div><i class="ti ti-user-star text-warning"></i> <strong class="text-dark">Promotor:</strong> <span class="text-muted">' + row.promotor + '</span></div>';
                        if (row.cuentas_origen && row.cuentas_origen.trim()) {
                            let c = row.cuentas_origen.split(',').filter(x => x.trim());
                            if (c.length) html += '<div><i class="ti ti-arrow-right text-danger"></i> <strong class="text-dark">Origen:</strong> <span class="text-muted">' + c[0] + '</span></div>';
                        }
                        if (row.cuentas_destino && row.cuentas_destino.trim()) {
                            let c = row.cuentas_destino.split(',').filter(x => x.trim());
                            if (c.length) html += '<div><i class="ti ti-arrow-left text-success"></i> <strong class="text-dark">Destino:</strong> <span class="text-muted">' + c[0] + '</span></div>';
                        }
                        html += '</div>';
                        return html || '<small class="text-muted">Sin datos</small>';
                    }
                },
                {
                    data: null, title: 'Acciones', orderable: false,
                    render: (data, type, row) =>
                        '<button class="badge bg-primary bg-opacity-10 text-primary border-0 btn-ver" style="cursor: pointer; font-size: 0.75rem;" data-id="' + row.id + '" data-grupo="' + row.grupo_id + '"><i class="ti ti-eye"></i> Ver Detalle</button>'
                }
            ],

            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },

            pageLength: 25,

            order: [[1, 'desc']],

            responsive: true

        });

        // ─── EVENTO PARA CAMBIAR ENTRE PESTAÑAS (ACTIVOS / ANULADOS) ───
        $('#tabsFlujo button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            // Cambiar estilos de la pestaña activa
            $('#tabsFlujo button').removeClass('border-bottom border-2 border-primary text-primary').addClass('text-muted');
            $(e.target).removeClass('text-muted').addClass('border-bottom border-2 border-primary text-primary');

            // Recargar la tabla con la nueva vista
            let vistaActual = $(e.target).data('vista');
            let nuevaUrl = 'api/flujo-lista.php?servicios_ids=1,2,3,4,5,6,7,8,9,10,11,12,13,14,15&vista=' + vistaActual;
            table.ajax.url(nuevaUrl).load();
        });



        // ─── Resúmenes ───────────────────────────────────────────────────────



        function cargarSaldos() {

            $.getJSON('api/flujo-resumen.php?tipo=saldos&moneda=TODAS', function (data) {

                let html = '', totalMxn = 0;

                if (data.cuentas && data.cuentas.length) {

                    data.cuentas.forEach(function (c) {

                        let saldo = parseFloat(c.saldo_actual || 0);

                        let mxn   = c.tipo_moneda === 'MXN' ? saldo : parseFloat(c.valor_mxn || 0);

                        totalMxn += mxn;

                        let montoDisplay = mostrarEnMXN

                            ? '$' + formatMXN(mxn) + ' MXN'

                            : (c.tipo_moneda === 'MXN' ? '$' + formatMXN(saldo) + ' MXN' : formatNativo(saldo) + ' ' + c.tipo_moneda);

                        let badge = c.tipo_moneda === 'USD'  ? '<span class="badge badge-usd">USD</span>' :

                                    c.tipo_moneda === 'USDT' ? '<span class="badge badge-usdt">USDT</span>' :

                                    '<span class="badge bg-secondary">MXN</span>';

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        html += `<tr>

                            <td>${nombreCuenta}</td>

                            <td>${badge}</td>

                            <td class="text-end fw-bold">${montoDisplay}</td>

                        </tr>`;

                    });

                }

                if (!html) html = `<tr><td colspan="3" class="text-center text-muted py-3">No hay cuentas</td></tr>`;

                $('#saldosBody').html(html);

            });

        }



        function cargarCapital() {

            $.getJSON('api/flujo-resumen.php?tipo=capital&moneda=TODAS', function (data) {

                let html = '', totalMxn = 0;

                if (data.cuentas && data.cuentas.length) {

                    data.cuentas.forEach(function (c) {

                        let saldo = parseFloat(c.saldo_actual || 0);

                        let mxn   = c.tipo_moneda === 'MXN' ? saldo : parseFloat(c.valor_mxn || 0);

                        if (Math.abs(saldo) < 0.01) return;

                        totalMxn += mxn;



                        let nombre = c.nombre;

                        if (c.es_deuda_cliente && c.cliente_id) {

                            nombre = `<a href="detalle-cliente.php?id=${c.cliente_id}" class="text-decoration-none">${c.nombre}</a>`;

                        } else if (c.es_deuda_promotor && c.promotor_id) {

                            nombre = `<a href="detalle-promotor.php?id=${c.promotor_id}" class="text-decoration-none">${c.nombre}</a>`;

                        }



                        let montoDisplay = mostrarEnMXN

                            ? '$' + formatMXN(mxn) + ' MXN'

                            : (c.tipo_moneda === 'MXN' ? '$' + formatMXN(saldo) + ' MXN' : formatNativo(saldo) + ' ' + c.tipo_moneda);



                        html += `<tr>

                            <td>${nombre}</td>

                            <td><span class="badge bg-warning text-dark">${c.tipo_cuenta_nombre || 'Capital'}</span></td>

                            <td class="text-end fw-bold">${montoDisplay}</td>

                        </tr>`;

                    });

                }

                if (!html) html = `<tr><td colspan="3" class="text-center text-muted py-3">No hay fondos de capital</td></tr>`;

                $('#capitalBody').html(html);

            });

        }



        function cargarObligaciones() {

            let entries = [], loaded = 0;



            function render() {

                loaded++;

                if (loaded < 2) return;



                let html = '', totalMxn = 0;

                entries.forEach(function (e) {

                    let monto = parseFloat(e.monto || 0);

                    let mxn   = e.tipo_moneda === 'MXN' ? monto : parseFloat(e.valor_mxn || 0);

                    if (Math.abs(monto) < 0.01) return;

                    totalMxn += mxn;



                    let montoDisplay = '$' + formatMXN(mxn) + ' MXN';

                    let badgeCls = e.tipo === 'Prestamista' ? 'bg-danger' :

                                   e.tipo_moneda === 'USD'  ? 'badge-usd' : 'badge-usdt';

                    let nombreCell = e.tipo === 'Proveedor'

                        ? `<a href="proveedores.php" class="text-decoration-none fw-semibold">${e.nombre}</a>`

                        : e.nombre;

                    html += `<tr>

                        <td>${nombreCell}</td>

                        <td><span class="badge ${badgeCls}">${e.tipo}</span></td>

                        <td class="text-end fw-bold">${montoDisplay}</td>

                    </tr>`;

                });

                if (!html) html = `<tr><td colspan="3" class="text-center text-muted py-3">Sin obligaciones pendientes</td></tr>`;

                $('#obligacionesBody').html(html);

            }



            // Prestamistas MXN

            $.getJSON('api/flujo-resumen.php?tipo=prestamos&moneda=MXN', function (data) {

                if (data && data.length) {

                    data.forEach(function (p) {

                        entries.push({

                            nombre:      p.nombre,

                            tipo:        'Prestamista',

                            monto:       parseFloat(p.total_adeudado || 0),

                            tipo_moneda: 'MXN',

                            valor_mxn:   parseFloat(p.total_adeudado || 0)

                        });

                    });

                }

                render();

            }).fail(render);



            // Proveedores USD/USDT

            $.getJSON('api/flujo-resumen.php?tipo=proveedor&moneda=TODAS', function (data) {

                if (data && data.proveedores) {

                    data.proveedores.forEach(function (p) {

                        entries.push({

                            nombre:      p.nombre,

                            tipo:        'Proveedor',

                            monto:       parseFloat(p.saldo_actual || 0),

                            tipo_moneda: p.tipo_moneda,

                            valor_mxn:   parseFloat(p.valor_mxn || 0)

                        });

                    });

                }

                render();

            }).fail(render);

        }



        function cargarDiferencia() {

            $.getJSON('api/flujo-resumen.php?tipo=diferencia&moneda=TODAS', function (data) {

                const diferencia = parseFloat(data.diferencia || 0);

                const $card     = $('#cardDiferencia');

                const $cardBody = $card.find('.card-body');

                const $monto    = $('#diferenciaMonto');

                const $texto    = $('#diferenciaTexto');

                const $icon     = $('#diferenciaIcon');



                $monto.text('$' + formatMXN(diferencia) + ' MXN');



                if (Math.abs(diferencia) < 0.01) {

                    $card.removeClass('border-danger border-warning').addClass('border-success');

                    $cardBody.css('background-color', 'rgba(25,135,84,0.1)');

                    $monto.removeClass('text-danger text-warning').addClass('text-success');

                    $icon.css('background-color', 'rgba(25,135,84,0.2)');

                    $texto.html('<i class="ti ti-check-circle"></i> <strong>Todo cuadra correctamente</strong>');

                } else if (diferencia < 0) {

                    $card.removeClass('border-success border-warning').addClass('border-danger');

                    $cardBody.css('background-color', 'rgba(220,53,69,0.1)');

                    $monto.removeClass('text-success text-warning').addClass('text-danger');

                    $icon.css('background-color', 'rgba(220,53,69,0.2)');

                    $texto.html('<i class="ti ti-alert-triangle"></i> <strong>ALERTA:</strong> Falta dinero en el sistema');

                } else {

                    $card.removeClass('border-success border-danger').addClass('border-warning');

                    $cardBody.css('background-color', 'rgba(255,193,7,0.1)');

                    $monto.removeClass('text-success text-danger').addClass('text-warning');

                    $icon.css('background-color', 'rgba(255,193,7,0.2)');

                    $texto.html('<i class="ti ti-info-circle"></i> <strong>ATENCIÓN:</strong> Sobra dinero (revisar préstamos o capital)');

                }



                const desglose = ` | Saldos: $${formatMXN(data.total_saldos || 0)} − (Capital: $${formatMXN(data.total_capital || 0)} + Obligaciones: $${formatMXN(data.total_prestamos || 0)})`;

                $texto.append(`<br><small class="text-muted">${desglose}</small>`);

            });

        }



        function cargarResumenes() {

            cargarSaldos();

            cargarCapital();

            cargarObligaciones();

            cargarDiferencia();

        }



        cargarResumenes();



        // ─── Carga de selectores del modal ───────────────────────────────────



        function cargarClientesMXN() {

            $.getJSON('api/clientes.php?moneda=MXN', function (data) {

                let html = '<option value="">-- Sin cliente --</option>';

                data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);

                $('#clienteSelect').html(html);

            });

        }



        function cargarClientesDivisas(moneda) {

            $.getJSON('api/clientes.php?moneda=' + moneda, function (data) {

                let html = '<option value="">-- Sin cliente --</option>';

                data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);

                $('#clienteSelect').html(html);

            });

        }



        function cargarProveedoresMXN() {

            let html = '<option value="">-- Sin proveedor/cliente --</option>';

            $.getJSON('api/proveedores.php?activos=1', function (data) {

                data.forEach(p => html += `<option value="${p.id}" data-tipo="proveedor">${p.nombre}</option>`);

                $.getJSON('api/clientes.php', function (clients) {

                    clients.forEach(c => html += `<option value="${c.id}" data-tipo="cliente">${c.nombre}</option>`);

                    $('#proveedorSelect').html(html);

                });

            });

        }



        function cargarProveedoresDivisas(moneda) {

            $.getJSON('api/proveedores.php?acepta_moneda=' + moneda + '&activos=1', function (data) {

                let html = '<option value="">-- Sin proveedor --</option>';

                data.forEach(p => html += `<option value="${p.id}">${p.nombre}</option>`);

                $('#proveedorSelect').html(html);

            });

        }



        function cargarCuentasMXNPago() {

            $.getJSON('api/cuentas.php?tipo_moneda=MXN&es_caja_empresa=1&con_saldo=1', function (data) {

                let html = '<option value="">-- Selecciona la cuenta --</option>';

                data.forEach(c => {

                    let nombreCuenta = c.nombre;

                    if (c.banco && c.banco.trim()) {

                        nombreCuenta += ' (' + c.banco + ')';

                    }

                    html += `<option value="${c.id}">${nombreCuenta} ($${formatMXN(c.saldo_actual)} MXN)</option>`;

                });

                $('#cuentaPagoContadoSelect').html(html);

            });

        }



        function cargarCuentasMXN(soloBancarias) {

            let url = soloBancarias ? 'api/cuentas.php?tipo_moneda=MXN' : 'api/cuentas.php';

            $.getJSON(url, function (data) {

                let html = '<option value="">-- Selecciona una cuenta --</option>';

                data.forEach(function (c) {

                    if (!soloBancarias || parseInt(c.tipo_cuenta) === 1) {

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        html += `<option value="${c.id}">${nombreCuenta} (MXN ${formatMXN(c.saldo_actual)})</option>`;

                    }

                });

                $('#cuentaSelect').html(html);

            });

        }



        function cargarCuentasDivisas(moneda) {

            $.getJSON('api/cuentas.php?tipo_moneda=' + moneda + '&es_caja_empresa=1', function (data) {

                let html = '<option value="">-- Selecciona una cuenta --</option>';

                data.forEach(c => {

                    let nombreCuenta = c.nombre;

                    if (c.banco && c.banco.trim()) {

                        nombreCuenta += ' (' + c.banco + ')';

                    }

                    html += `<option value="${c.id}">${nombreCuenta} (${formatNativo(c.saldo_actual)} ${moneda})</option>`;

                });

                $('#cuentaSelect').html(html);

                $('#cuentaLabel').text('Wallet / Cuenta de ' + moneda);

            });

        }



        function cargarCuentasVenta() {

            $.getJSON('api/cuentas-venta-divisas.php', function (data) {

                let html = '<option value="">-- Selecciona una cuenta --</option>';

                data.forEach(c => html += `<option value="${c.id}">${c.nombre} ($${formatMXN(c.saldo_actual)} MXN)</option>`);

                $('#cuentaSelect').html(html);

                $('#cuentaLabel').text('Cuenta donde recibirás el pago (MXN)');

            });

        }



        // ─── Cambio de cliente (para Venta de Efectivo) ──────────────────────

        $(document).on('change', '#clienteSelect', function () {

            let cliente_id    = $(this).val();

            let servicioNombre = $('#servicioSelect').val();

            if (servicioNombre === 'Venta de Efectivo' && cliente_id) {

                $.getJSON('api/clientes.php?id=' + cliente_id, function (data) {

                    if (!data) return;

                    let porcAmiri    = parseFloat(data.porcentaje_amiri)    || 0;

                    let porcPromotor = parseFloat(data.porcentaje_promotor) || 0;

                    $('.input-porc-amiri').val(porcAmiri);

                    $('.input-porc-promotor').val(porcPromotor);

                    if ((data.promotor || '') === 'AMIRI DIRECTO') {

                        $('#campoPromotorPorcentaje').hide();

                        $('.input-porc-promotor').val(0);

                    } else {

                        $('#campoPromotorPorcentaje').show();

                    }

                    $('.input-monto').trigger('input');

                });

            }

        });



        // ─── Cambio de servicio ───────────────────────────────────────────────

        $(document).on('change', '#servicioSelect', function () {

            let $opt           = $(this).find(':selected');

            let servicioNombre = $opt.val();

            let servicioId     = parseInt($opt.data('id')) || 0;

            let $campos        = $('#camposDinamicos');



            $campos.empty();

            $('#totalesGroup, #clienteGroup, #cuentaGroup, #proveedorGroup').hide();

            $('#cuentaLabel').text('Cuenta');



            if (!servicioNombre) return;



            let esDivisas = [2, 3, 5, 6].includes(servicioId);



            if (esDivisas) {

                // ── Lógica Divisas ────────────────────────────────────────

                let moneda  = servicioNombre.includes('Dólares') ? 'USD' : 'USDT';

                let esCompra = servicioNombre.includes('Compra');



                cargarClientesDivisas(moneda);

                cargarProveedoresDivisas(moneda);

                if (esCompra) {

                    cargarCuentasDivisas(moneda);

                } else {

                    cargarCuentasVenta();

                }



                if (!esCompra) $('#clienteGroup').show();

                if (esCompra)  $('#proveedorGroup').show();

                $('#cuentaGroup').show();



                if (esCompra) {

                    $campos.html(`

                        <div class="mb-3">

                            <small class="text-muted label-formato-cantidad"></small>

                            <label class="form-label">Cantidad de ${moneda} <span class="text-muted">(monto a comprar)</span></label>

                            <input type="number" step="0.01" class="form-control input-cantidad" required>

                        </div>

                        <div class="mb-3">

                            <small class="text-muted label-formato-tc"></small>

                            <label class="form-label">Tipo de Cambio <span class="text-muted">(MXN por 1 ${moneda})</span></label>

                            <input type="number" step="0.0001" class="form-control input-tc" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">Total MXN <span class="text-muted">(referencia)</span></label>

                            <div class="input-group">

                                <span class="input-group-text">$</span>

                                <input type="text" class="form-control input-total-mxn bg-light" readonly>

                                <span class="input-group-text">MXN</span>

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-bold">Tipo de Pago *</label>

                            <div class="d-flex gap-2">

                                <button type="button" class="btn btn-outline-danger btn-tipo-pago active" data-tipo="credito">

                                    <i class="ti ti-credit-card"></i> A Crédito

                                </button>

                                <button type="button" class="btn btn-outline-success btn-tipo-pago" data-tipo="contado">

                                    <i class="ti ti-cash"></i> De Contado

                                </button>

                            </div>

                            <input type="hidden" class="input-tipo-pago" value="credito">

                        </div>

                        <div class="mb-3" id="grupoCuentaPagoContado" style="display:none;">

                            <label class="form-label fw-bold text-success">Cuenta de Pago (Contado) *</label>

                            <select class="form-select" id="cuentaPagoContadoSelect">

                                <option value="">-- Selecciona la cuenta --</option>

                            </select>

                            <small class="text-success"><i class="ti ti-arrow-right"></i> Se descontará el MXN de esta cuenta inmediatamente</small>

                        </div>

                    `);

                    // Manejar toggle contado/crédito

                    $(document).off('click', '.btn-tipo-pago').on('click', '.btn-tipo-pago', function() {

                        $('.btn-tipo-pago').removeClass('active');

                        $(this).addClass('active');

                        const tipo = $(this).data('tipo');

                        $('.input-tipo-pago').val(tipo);

                        if (tipo === 'contado') {

                            $('#grupoCuentaPagoContado').show();

                            cargarCuentasMXNPago();

                        } else {

                            $('#grupoCuentaPagoContado').hide();

                        }

                    });

                } else {

                    $campos.html(`

                        <div class="mb-3">

                            <small class="text-muted label-formato-cantidad"></small>

                            <label class="form-label">Cantidad de ${moneda} <span class="text-muted">(monto a vender)</span></label>

                            <input type="number" step="0.01" class="form-control input-cantidad" required>

                            <small id="disponibleDivisasInfo" class="text-muted"><i class="ti ti-loader ti-spin"></i> Consultando inventario...</small>

                        </div>

                        <div class="mb-3">

                            <small class="text-muted label-formato-tc"></small>

                            <label class="form-label">Tipo de Cambio <span class="text-muted">(precio por ${moneda} en MXN)</span></label>

                            <input type="number" step="0.0001" class="form-control input-tc" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-bold text-success">Total MXN a depositar en cuenta</label>

                            <div class="input-group">

                                <span class="input-group-text">$</span>

                                <input type="text" class="form-control input-total-mxn bg-light fw-bold" readonly>

                                <span class="input-group-text">MXN</span>

                            </div>

                            <small class="text-success"><i class="ti ti-arrow-right"></i> Este monto se sumará a la cuenta seleccionada</small>

                        </div>

                    `);



                    // Consultar inventario disponible y mostrarlo; guardar en data-max para validación

                    $.getJSON('api/flujo-divisas.php?tipo=inventario&moneda=' + moneda, function(res) {

                        const disp = parseFloat(res.disponible || 0);

                        const $info = $('#disponibleDivisasInfo');

                        if (disp > 0.0001) {

                            $info.removeClass('text-danger').addClass('text-success')

                                 .html(`<i class="ti ti-check"></i> Disponible en inventario: <strong>${formatNativo(disp)} ${moneda}</strong>`);

                        } else {

                            $info.removeClass('text-success').addClass('text-danger')

                                 .html(`<i class="ti ti-alert-triangle"></i> Sin inventario de ${moneda} disponible`);

                        }

                        $('.input-cantidad').data('max-disponible', disp);

                    }).fail(function() {

                        $('#disponibleDivisasInfo').text('No se pudo consultar inventario');

                    });

                }



            } else {

                // ── Lógica MXN ────────────────────────────────────────────

                switch (servicioNombre) {



                    case 'Venta de Efectivo':

                        $('#clienteGroup').show();

                        $('#cuentaGroup').show();

                        cargarClientesMXN();

                        cargarCuentasMXN(true);

                        $campos.html(`

                            <div class="mb-3">

                                <label class="form-label">

                                    Monto

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <input type="number" step="0.01" class="form-control input-monto" id="inputMontoVenta" required>

                            </div>

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">% Amiri</label>

                                        <input type="number" step="0.01" class="form-control input-porc-amiri" value="0" required>

                                    </div>

                                </div>

                                <div class="col-md-6" id="campoPromotorPorcentaje">

                                    <div class="mb-3">

                                        <label class="form-label">% Promotor</label>

                                        <input type="number" step="0.01" class="form-control input-porc-promotor" value="0" required>

                                    </div>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Retención Total (%)</label>

                                <div class="input-group">

                                    <input type="number" step="0.01" class="form-control input-retencion-total" value="0" disabled readonly>

                                    <span class="input-group-text bg-info text-white"><i class="ti ti-percentage"></i></span>

                                </div>

                                <small class="text-muted">Suma automática de % Amiri + % Promotor</small>

                            </div>

                        `);

                        $('#totalesGroup').show();

                        break;



                    case 'Cambio de Flujo':

                        $('#cuentaGroup').hide();

                        $campos.html(`

                            <div class="mb-3">

                                <label class="form-label">Cuenta Origen *</label>

                                <select class="form-select" id="cuentaOrigen" name="origen_id" required></select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Cuenta Destino *</label>

                                <select class="form-select" id="cuentaDestino" name="destino_id" required></select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">

                                    Monto *

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <input type="number" step="0.01" class="form-control input-monto" id="inputMontoCambio" required>

                                <div id="warningMontoCambio" class="alert alert-warning py-1 px-2 mt-1 mb-0" style="display:none;font-size:0.875rem;"></div>

                            </div>

                        `);

                        // Fetch todas las cuentas de empresa (sin filtro de saldo) para origen y destino

                        $.getJSON('api/cuentas.php?solo_gastos=1&tipo_moneda=MXN&es_caja_empresa=1', function (todasCuentas) {

                            // Origen: solo cuentas con saldo > 0

                            const cuentasConSaldo = todasCuentas.filter(c => parseFloat(c.saldo_actual) > 0);



                            function buildOpcionesOrigen(excluirId) {

                                let html = '<option value="">-- Selecciona una cuenta --</option>';

                                const lista = cuentasConSaldo.filter(c => c.id != excluirId);

                                if (!lista.length) {

                                    html += '<option disabled>No hay cuentas con saldo disponible</option>';

                                } else {

                                    lista.forEach(function (c) {

                                        let tipo = c.tipo_cuenta == 1 ? 'Bancaria' : 'Caja';

                                        let nombreCuenta = c.nombre;

                                        if (c.banco && c.banco.trim()) {

                                            nombreCuenta += ' (' + c.banco + ')';

                                        }

                                        html += `<option value="${c.id}" data-saldo="${c.saldo_actual}" data-tipo="${c.tipo_cuenta}">${nombreCuenta} (${tipo} - $${formatMXN(c.saldo_actual)})</option>`;

                                    });

                                }

                                return html;

                            }



                            function buildOpcionesDestino(excluirId) {

                                let html = '<option value="">-- Selecciona una cuenta --</option>';

                                const lista = todasCuentas.filter(c => c.id != excluirId);

                                if (!lista.length) {

                                    html += '<option disabled>No hay cuentas disponibles</option>';

                                } else {

                                    lista.forEach(function (c) {

                                        let tipo = c.tipo_cuenta == 1 ? 'Bancaria' : 'Caja';

                                        html += `<option value="${c.id}" data-tipo="${c.tipo_cuenta}">${c.nombre} (${tipo} - $${formatMXN(c.saldo_actual)})</option>`;

                                    });

                                }

                                return html;

                            }



                            $('#cuentaOrigen').html(buildOpcionesOrigen(null));

                            $('#cuentaDestino').html(buildOpcionesDestino(null));



                            $('#cuentaOrigen').off('change').on('change', function () {

                                let oid = $(this).val(), did = $('#cuentaDestino').val();

                                $('#cuentaDestino').html(buildOpcionesDestino(oid));

                                if (did && did != oid) $('#cuentaDestino').val(did);

                                // Re-validar monto al cambiar origen

                                $('.input-monto').trigger('input');

                            });

                            $('#cuentaDestino').off('change').on('change', function () {

                                let did = $(this).val(), oid = $('#cuentaOrigen').val();

                                $('#cuentaOrigen').html(buildOpcionesOrigen(did));

                                if (oid && oid != did) $('#cuentaOrigen').val(oid);

                            });

                        });

                        break;



                    case 'Retiro de Efectivo':

                        $('#cuentaGroup, #clienteGroup, #proveedorGroup').hide();

                        $campos.html(`

                            <div class="mb-3">

                                <label class="form-label">Cuenta Bancaria (Origen) *</label>

                                <select class="form-select" id="cuentaBancariaOrigen" name="cuenta_bancaria_id" required></select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Tipo de Retiro *</label>

                                <select class="form-select" name="tipo_retiro" required>

                                    <option value="">-- Selecciona un tipo --</option>

                                    <option value="sin_tarjeta">Sin Tarjeta</option>

                                    <option value="cobro_cheque">Cobro en Cheque</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">

                                    Monto *

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <input type="number" step="0.01" class="form-control input-monto" id="inputMontoRetiro" required>

                            </div>

                        `);

                        $.getJSON('api/cuentas.php?tipo_cuenta=1&con_saldo=1', function (cuentas) {

                            let html = '<option value="">-- Selecciona una cuenta bancaria --</option>';

                            if (!cuentas.length) {

                                html += '<option disabled>No hay cuentas bancarias con saldo</option>';

                            } else {

                                cuentas.forEach(c => {

                                    let nombreCuenta = c.nombre;

                                    if (c.banco && c.banco.trim()) {

                                        nombreCuenta += ' (' + c.banco + ')';

                                    }

                                    html += `<option value="${c.id}">${nombreCuenta} - $${formatMXN(c.saldo_actual)}</option>`;

                                });

                            }

                            $('#cuentaBancariaOrigen').html(html);

                        });

                        break;

                }

            }

        });



        // ─── Cálculo automático MXN (Venta de Efectivo) ──────────────────────

        $(document).on('input', '.input-paridad, .input-monto, .input-porc-amiri, .input-porc-promotor', function () {

            let servicioNombre = $('#servicioSelect').val();

            if (!['Venta de Efectivo', 'Cambio de Flujo', 'Retiro de Efectivo'].includes(servicioNombre)) return;



            if (servicioNombre === 'Venta de Efectivo') {

                let pa = parseFloat($('.input-porc-amiri').val())    || 0;

                let pp = parseFloat($('.input-porc-promotor').val()) || 0;

                $('.input-retencion-total').val((pa + pp).toFixed(2));

            }



            let monto = parseFloat($('.input-monto').val()) || 0;

            let span  = $('#montoFormateado');

            span.text(monto > 0 ? '= $' + formatMXN(monto) : '');



            // Validar monto vs saldo origen en Cambio de Flujo

            if (servicioNombre === 'Cambio de Flujo') {

                const $warn = $('#warningMontoCambio');

                if ($warn.length) {

                    const saldoOrigen = parseFloat($('#cuentaOrigen option:selected').attr('data-saldo')) || 0;

                    if ($('#cuentaOrigen').val() && monto > 0 && monto > saldoOrigen + 0.001) {

                        $warn.show().html(`<i class="ti ti-alert-triangle"></i> El monto (<strong>$${formatMXN(monto)}</strong>) supera el saldo disponible en la cuenta origen (<strong>$${formatMXN(saldoOrigen)}</strong> MXN)`);

                    } else {

                        $warn.hide();

                    }

                }

            }



            if (servicioNombre === 'Venta de Efectivo') {

                let pa  = parseFloat($('.input-porc-amiri').val())    || 0;

                let pp  = parseFloat($('.input-porc-promotor').val()) || 0;

                let ca  = monto * (pa / 100);

                let cp  = monto * (pp / 100);

                let tot = monto - ca - cp;

                $('#desgloseBody').html(`

                    <table class="table table-sm mb-0">

                        <tr><td><strong>💵 Monto Total:</strong></td><td class="text-end"><strong>$${formatMXN(monto)}</strong></td></tr>

                        <tr class="table-warning"><td><i class="ti ti-building-bank"></i> Comisión AMIRI (${pa}%):</td><td class="text-end">$${formatMXN(ca)}</td></tr>

                        <tr class="table-info"><td><i class="ti ti-user"></i> Comisión Promotor (${pp}%):</td><td class="text-end">$${formatMXN(cp)}</td></tr>

                        <tr class="table-success"><td><strong><i class="ti ti-cash"></i> Total al Cliente:</strong></td><td class="text-end"><strong>$${formatMXN(tot)}</strong></td></tr>

                    </table>

                    <div class="alert alert-info mt-2 mb-0 py-2">

                        <small><i class="ti ti-info-circle"></i> El cliente recibirá $${formatMXN(tot)} en efectivo.</small>

                    </div>

                `);

            } else {

                let total = parseFloat($('.input-paridad').val() || 1) * monto;

                $('#desgloseBody').html(`<div class="alert alert-primary mb-0"><strong>Total:</strong> $${formatMXN(total)} MXN</div>`);

            }

        });



        // ─── Cálculo automático Divisas (cantidad × TC) + labels de formato ─────

        $(document).on('input', '.input-cantidad, .input-tc', function () {

            let cantidad = parseFloat($('.input-cantidad').val()) || 0;

            let tc       = parseFloat($('.input-tc').val())       || 0;

            let totalMxn = cantidad * tc;



            $('.input-total-mxn').val(formatMXN(totalMxn));



            // Labels con formato de comas encima de los inputs

            if (cantidad > 0) {

                $('.label-formato-cantidad').text('= ' + formatNativo(cantidad));

            } else {

                $('.label-formato-cantidad').text('');

            }

            if (tc > 0) {

                $('.label-formato-tc').text('= $' + formatMXN(tc));

            } else {

                $('.label-formato-tc').text('');

            }

        });



        // ─── Reset modal ──────────────────────────────────────────────────────

        function resetModal() {

            let form = $('#formFlujo')[0];

            if (form) form.reset();

            $('#clienteGroup, #proveedorGroup, #cuentaGroup, #totalesGroup').hide();

            $('#camposDinamicos').empty();

            $('#desgloseBody').empty();

            $('#cuentaLabel').text('Cuenta');

        }



        $('#modalFlujo').on('show.bs.modal',   resetModal);

        $('#modalFlujo').on('hidden.bs.modal', resetModal);



        $('#modalDetalle').on('hidden.bs.modal', function () {

            $('#detalleContent').empty();

            $('#btnAnular').data('grupo', '');

        });



        // ─── Ver Detalle ──────────────────────────────────────────────────────

        $(document).on('click', '.btn-ver', function () {

            let movimiento_id = $(this).data('id');

            $.getJSON('api/flujo-detalle.php?id=' + movimiento_id, function (data) {

                mostrarDetalle(data);

                $('#modalDetalle').modal('show');

            }).fail(function () {

                showToast('Error al cargar el detalle del movimiento', 'error');

            });

        });



        function mostrarDetalle(data) {

            let html = `

                <div class="card border-0 mb-3">

                    <div class="card-header bg-primary text-white">

                        <h6 class="mb-0"><i class="ti ti-info-circle"></i> Información General</h6>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4">

                                <strong><i class="ti ti-hash"></i> Grupo:</strong><br>

                                <span class="badge bg-secondary fs-6">#${data.grupo_id}</span>

                            </div>

                            <div class="col-md-4">

                                <strong><i class="ti ti-calendar"></i> Fecha:</strong><br>

                                ${data.fecha}

                            </div>

                            <div class="col-md-4">

                                <strong><i class="ti ti-package"></i> Servicio:</strong><br>

                                <span class="badge bg-info">${data.servicio_nombre || data.servicio}</span>

                            </div>

                        </div>

                        <hr>

                        <div class="row">`;



            if (data.cliente_nombre)  html += `<div class="col-md-4"><strong><i class="ti ti-user"></i> Cliente:</strong><br>${data.cliente_nombre}</div>`;

            if (data.proveedor_nombre) html += `<div class="col-md-4"><strong><i class="ti ti-building"></i> Proveedor:</strong><br>${data.proveedor_nombre}</div>`;

            if (data.promotor_nombre)  html += `<div class="col-md-4"><strong><i class="ti ti-user-star"></i> Promotor:</strong><br>${data.promotor_nombre}</div>`;



            html += `

                            <div class="col-md-4">

                                <strong><i class="ti ti-list-numbers"></i> Movimientos:</strong><br>

                                <span class="badge bg-success">${data.total_movimientos || (data.movimientos ? data.movimientos.length : 0)}</span>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="card border-0">

                    <div class="card-header bg-dark text-white">

                        <h6 class="mb-0"><i class="ti ti-list-details"></i> Detalle de Movimientos</h6>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover mb-0">

                                <thead class="table-dark">

                                    <tr>

                                        <th style="width:50px">#</th>

                                        <th style="width:120px">Tipo</th>

                                        <th>Descripción</th>

                                        <th style="width:80px">Moneda</th>

                                        <th class="text-end" style="width:130px">Monto</th>

                                        <th style="width:180px">Origen</th>

                                        <th style="width:180px">Destino</th>

                                    </tr>

                                </thead>

                                <tbody>`;



            let num = 1;

            (data.movimientos || []).forEach(function (mov) {

                let tipoBadge = '';

                if      (mov.tipo === 'entrada')  tipoBadge = '<span class="badge bg-success"><i class="ti ti-arrow-down"></i> Entrada</span>';

                else if (mov.tipo === 'salida')   tipoBadge = '<span class="badge bg-danger"><i class="ti ti-arrow-up"></i> Salida</span>';

                else if (mov.tipo === 'comision') tipoBadge = '<span class="badge bg-warning text-dark"><i class="ti ti-coin"></i> Comisión</span>';

                else if (mov.tipo === 'traspaso') tipoBadge = '<span class="badge bg-info"><i class="ti ti-exchange"></i> Traspaso</span>';

                else                             tipoBadge = '<span class="badge bg-secondary">' + mov.tipo + '</span>';



                let montoClass = mov.tipo === 'entrada' ? 'text-success' : mov.tipo === 'salida' ? 'text-danger' : 'text-warning';

                html += `

                    <tr>

                        <td class="fw-bold text-center">#${num}</td>

                        <td>${tipoBadge}</td>

                        <td><small class="text-muted">${mov.descripcion || '-'}</small></td>

                        <td class="text-center"><strong>${mov.moneda}</strong></td>

                        <td class="text-end">

                            <strong class="fs-6 ${montoClass}">$${formatMXN(mov.monto)}</strong>

                        </td>

                        <td><small class="text-muted">

                            ${mov.cuenta_origen_nombre ? '<i class="ti ti-wallet"></i> ' + mov.cuenta_origen_nombre + (mov.cuenta_origen_banco ? ' (' + mov.cuenta_origen_banco + ')' : '') :

                              (mov.cliente_nombre && !mov.cuenta_origen_nombre ? '<i class="ti ti-user"></i> ' + mov.cliente_nombre : '-')}

                        </small></td>

                        <td><small class="text-muted">

                            ${mov.cuenta_destino_nombre ? '<i class="ti ti-wallet"></i> ' + mov.cuenta_destino_nombre + (mov.cuenta_destino_banco ? ' (' + mov.cuenta_destino_banco + ')' : '') : '-'}

                        </small></td>

                    </tr>`;

                num++;

            });



            html += `</tbody></table></div></div></div>`;



            // Inventario asociado

            if (data.inventario && data.inventario.length) {

                html += `

                    <div class="card border-0 mt-3">

                        <div class="card-header bg-warning text-dark">

                            <h6 class="mb-0"><i class="ti ti-package"></i> Inventario Asociado</h6>

                        </div>

                        <div class="card-body p-0">

                            <table class="table table-sm table-bordered mb-0">

                                <thead class="table-light"><tr><th>ID</th><th>Cantidad Original</th><th>Cantidad Restante</th><th>Paridad</th></tr></thead>

                                <tbody>`;

                data.inventario.forEach(function (inv) {

                    html += `<tr><td>${inv.id}</td><td>${formatNativo(inv.cantidad_original)}</td><td>${formatNativo(inv.cantidad_restante)}</td><td>${formatNativo(inv.paridad)}</td></tr>`;

                });

                html += `</tbody></table></div></div>`;

            }



            // Desglose FIFO

            if (data.consumos_fifo && data.consumos_fifo.length) {

                let gananciaTotal = parseFloat(data.ganancia_total || 0);

                let gananciaClass = gananciaTotal >= 0 ? 'bg-success' : 'bg-danger';

                let gananciaIcon  = gananciaTotal >= 0 ? 'trending-up' : 'trending-down';

                html += `

                    <div class="card border-0 mt-3">

                        <div class="card-header ${gananciaClass} text-white">

                            <h6 class="mb-0">

                                <i class="ti ti-${gananciaIcon}"></i> Desglose de Ganancia (FIFO)

                                <span class="float-end fw-bold">Total: $${formatMXN(gananciaTotal)} MXN</span>

                            </h6>

                        </div>

                        <div class="card-body p-0">

                            <table class="table table-sm table-hover mb-0">

                                <thead class="table-light">

                                    <tr><th>Fecha Compra</th><th class="text-center">Cantidad</th><th class="text-end">TC Compra</th><th class="text-end">TC Venta</th><th class="text-end">Ganancia</th></tr>

                                </thead>

                                <tbody>`;

                data.consumos_fifo.forEach(function (c) {

                    let gan       = parseFloat(c.ganancia);

                    let ganCls    = gan >= 0 ? 'text-success' : 'text-danger';

                    let fechaStr  = c.fecha_compra ? c.fecha_compra.split(' ')[0] : 'N/A';

                    html += `<tr>

                        <td><small class="text-muted">${fechaStr}</small></td>

                        <td class="text-center">${formatNativo(c.cantidad_consumida)}</td>

                        <td class="text-end">$${formatMXN(c.paridad_compra)}</td>

                        <td class="text-end">$${formatMXN(c.paridad_venta)}</td>

                        <td class="text-end fw-bold ${ganCls}">${gan >= 0 ? '+' : ''}$${formatMXN(gan)}</td>

                    </tr>`;

                });

                html += `</tbody></table></div></div>`;

            }



            $('#detalleContent').html(html);



            // Estado y botón anular

            $('#btnAnular').data('grupo', data.grupo_id);

            let estado = data.estado || (data.movimientos && data.movimientos.length ? (data.movimientos[0].estado || 'activo') : 'activo');

            if (estado === 'activo') {

                $('#btnAnular').show();

                $('#detalleEstado').html('<span class="badge bg-success"><i class="ti ti-check"></i> Activo</span>');

            } else if (estado === 'anulado') {

                $('#btnAnular').hide();

                $('#detalleEstado').html('<span class="badge bg-danger"><i class="ti ti-x"></i> Anulado</span>');

            } else if (estado === 'corregido') {

                $('#btnAnular').hide();

                $('#detalleEstado').html('<span class="badge bg-warning"><i class="ti ti-edit"></i> Corregido</span>');

            }

        }



        // ─── Revertir Último Movimiento ───────────────────────────────────────

        $('#btnRevertirUltimo').on('click', function () {

            $('#revertirUltimoBody').html('<div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>');

            $('#btnConfirmarRevertir').prop('disabled', true);

            $('#modalRevertirUltimo').modal('show');



            $.getJSON('api/movimientos-correccion.php?action=ultimo', function (res) {

                if (!res.success || !res.movimiento) {

                    $('#revertirUltimoBody').html('<div class="alert alert-info">No hay movimientos activos para revertir.</div>');

                    return;

                }

                const m   = res.movimiento;

                const grp = res.grupo || [];

                let filas = grp.map(function (mov) {

                    const montoClass = mov.tipo === 'entrada' ? 'text-success' : mov.tipo === 'salida' ? 'text-danger' : 'text-warning';

                    return `<tr>

                        <td><span class="badge bg-secondary">${mov.tipo}</span></td>

                        <td>${mov.moneda}</td>

                        <td class="text-end fw-bold ${montoClass}">$${(parseFloat(mov.monto)||0).toLocaleString('es-MX',{minimumFractionDigits:2})}</td>

                        <td><small>${mov.cuenta_origen_nombre || '-'}</small></td>

                        <td><small>${mov.cuenta_destino_nombre || '-'}</small></td>

                        <td><small class="text-muted">${mov.descripcion || '-'}</small></td>

                    </tr>`;

                }).join('');



                $('#revertirUltimoBody').html(`

                    <div class="alert alert-warning mb-3">

                        <i class="ti ti-alert-triangle"></i>

                        <strong>¿Revertir este movimiento?</strong> Esta acción creará un movimiento de reverso. Los saldos se ajustarán automáticamente.

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-4"><strong>Grupo:</strong> <span class="badge bg-secondary">#${m.grupo_id}</span></div>

                        <div class="col-md-4"><strong>Fecha:</strong> ${m.fecha}</div>

                        <div class="col-md-4"><strong>Servicio:</strong> <span class="badge bg-info">${m.servicio_nombre || '-'}</span></div>

                    </div>

                    ${m.cliente_nombre ? `<p><strong>Cliente:</strong> ${m.cliente_nombre}</p>` : ''}

                    ${m.proveedor_nombre ? `<p><strong>Proveedor:</strong> ${m.proveedor_nombre}</p>` : ''}

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead class="table-dark"><tr><th>Tipo</th><th>Moneda</th><th class="text-end">Monto</th><th>Origen</th><th>Destino</th><th>Descripción</th></tr></thead>

                            <tbody>${filas}</tbody>

                        </table>

                    </div>

                `);

                $('#btnConfirmarRevertir').prop('disabled', false)

                    .data('grupo', m.grupo_id)

                    .data('servicio_id', parseInt(m.servicio_id) || 0);

            }).fail(function () {

                $('#revertirUltimoBody').html('<div class="alert alert-danger">Error al obtener el último movimiento.</div>');

            });

        });



        $('#btnConfirmarRevertir').on('click', function () {

            const grupoId    = $(this).data('grupo');

            const servicioId = $(this).data('servicio_id') || 0;

            if (!grupoId) return;

            const $btn = $(this);

            $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');



            // Divisas (2=Venta Dólares, 3=Venta USDT, 5=Compra Dólares, 6=Compra USDT)

            // → usar DELETE api/flujo-divisas.php que maneja FIFO, inventario y deuda de proveedor

            // MXN (1=Venta de Efectivo, 4=Cambio de Flujo, 7=Retiro de Efectivo)

            // → usar anularPorGrupo de MovimientoCorreccionService (revierte saldos genéricamente)

            const esDivisas = [2, 3, 5, 6].includes(servicioId);



            let ajaxConfig;

            if (esDivisas) {

                ajaxConfig = {

                    url: 'api/flujo-divisas.php',

                    method: 'DELETE',

                    contentType: 'application/x-www-form-urlencoded',

                    data: 'grupo_id=' + grupoId,

                    dataType: 'json'

                };

            } else {

                ajaxConfig = {

                    url: 'api/movimientos-correccion.php',

                    method: 'POST',

                    data: { action: 'anular', grupo_id: grupoId, motivo: 'Reversión del último movimiento' },

                    dataType: 'json'

                };

            }



            $.ajax(Object.assign(ajaxConfig, {

                success: function (res) {

                    if (res.success) {

                        $('#modalRevertirUltimo').modal('hide');

                        table.ajax.reload();

                        cargarResumenes();

                        showToast('Movimiento revertido correctamente', 'success');

                    } else {

                        showToast(res.error || 'Error al revertir', 'error');

                    }

                },

                error: function (xhr) {

                    showToast(xhr.responseJSON?.error || 'Error al revertir', 'error');

                },

                complete: function () {

                    $btn.prop('disabled', false).html('<i class="ti ti-arrow-back-up"></i> Confirmar Reversión');

                }

            }));

        });



        // ─── Anulación ────────────────────────────────────────────────────────

        $('#btnAnular').on('click', function () {

            let grupoId = $(this).data('grupo');

            if (!grupoId) { showToast('No se pudo identificar el movimiento', 'error'); return; }

            $('#motivoAnulacion').val('');

            $('#modalDetalle').modal('hide');

            $('#modalAnular').modal('show');

        });



        $('#btnConfirmarAnular').on('click', function () {

            let grupoId = $('#btnAnular').data('grupo');

            let motivo  = $('#motivoAnulacion').val().trim();

            if (!motivo) { showToast('Debes ingresar un motivo para la anulación', 'warning'); return; }



            let $btn = $(this);

            $btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Procesando...');



            $.ajax({

                url: 'api/movimientos-correccion.php',

                method: 'POST',

                data: { action: 'anular', grupo_id: grupoId, motivo: motivo },

                dataType: 'json',

                success: function (res) {

                    if (res.success) {

                        $('#modalAnular').modal('hide');

                        table.ajax.reload();

                        cargarResumenes();

                        let msg = res.reversos_creados

                            ? 'Grupo anulado correctamente. Se crearon ' + res.reversos_creados + ' reversos.'

                            : 'Movimiento anulado correctamente.';

                        showToast(msg, 'success');

                    } else {

                        showToast(res.error || 'Error al anular', 'error');

                    }

                },

                error: function (xhr) {

                    showToast(xhr.responseJSON?.error || 'Error al anular', 'error');

                },

                complete: function () {

                    $btn.prop('disabled', false).html('<i class="ti ti-check"></i> Confirmar Anulación');

                }

            });

        });



        // ─── Guardar movimiento ───────────────────────────────────────────────

        $('#formFlujo').on('submit', function (e) {

            e.preventDefault();



            let $opt           = $('#servicioSelect').find(':selected');

            let servicioNombre = $opt.val();

            let servicioId     = parseInt($opt.data('id')) || 0;

            let esDivisas      = [2, 3, 5, 6].includes(servicioId);



            if (esDivisas) {

                // Validar que no se venda más de lo disponible en inventario

                // IDs de venta: 2=Venta de Dólares, 3=Venta de USDT

                let esVenta = [2, 3].includes(servicioId);

                if (esVenta) {

                    let cantInput   = $('.input-cantidad');

                    let cantVal     = parseFloat(cantInput.val()) || 0;

                    let maxDisp     = parseFloat(cantInput.data('max-disponible')) || 0;

                    if (maxDisp > 0 && cantVal > maxDisp + 0.0001) {

                        showToast('No puedes vender más de lo disponible en inventario (' + formatNativo(maxDisp) + ')', 'error');

                        return;

                    }

                }



                // ── POST a api/flujo-divisas.php ──────────────────────────

                let formData = {

                    fecha:          $('input[name="fecha"]').val(),

                    servicio:       servicioNombre,

                    cliente_id:     $('#clienteSelect').val()   || '',

                    proveedor_id:   $('#proveedorSelect').val() || '',

                    cuenta_id:      $('#cuentaSelect').val()    || '',

                    cantidad:       $('.input-cantidad').val(),

                    tipo_cambio:    $('.input-tc').val(),

                    tipo_pago:      $('.input-tipo-pago').val() || 'credito',

                    cuenta_pago_id: $('#cuentaPagoContadoSelect').val() || ''

                };

                $.ajax({

                    url: 'api/flujo-divisas.php',

                    method: 'POST',

                    data: formData,

                    dataType: 'json',

                    success: function (res) {

                        if (res.success) {

                            $('#modalFlujo').modal('hide');

                            table.ajax.reload();

                            cargarResumenes();

                            showToast('Movimiento guardado correctamente', 'success');

                        } else {

                            showToast(res.error || 'Error al guardar', 'error');

                        }

                    },

                    error: function (xhr) {

                        showToast(xhr.responseJSON?.error || 'Error al guardar', 'error');

                    }

                });



            } else {

                // ── POST a api/flujo.php ──────────────────────────────────

                let payload = {

                    fecha:        $('input[name="fecha"]').val(),

                    servicio:     servicioNombre,

                    cliente_id:   $('#clienteSelect').val()   || null,

                    proveedor_id: $('#proveedorSelect').val() || null,

                    cuenta_id:    $('#cuentaSelect').val()    || null,

                    monto:        parseFloat($('.input-monto').val()) || 0,

                    paridad:      parseFloat($('.input-paridad').val()) || 1

                };

                if (servicioNombre === 'Cambio de Flujo') {

                    const saldoOrigen = parseFloat($('#cuentaOrigen option:selected').attr('data-saldo')) || 0;

                    if (payload.monto > saldoOrigen + 0.001) {

                        showToast('No puedes mover más del saldo disponible en la cuenta origen ($' + formatMXN(saldoOrigen) + ' MXN)', 'error');

                        return;

                    }

                    payload.origen_id  = parseInt($('select[name="origen_id"]').val())  || null;

                    payload.destino_id = parseInt($('select[name="destino_id"]').val()) || null;

                }

                if (servicioNombre === 'Retiro de Efectivo') {

                    payload.cuenta_bancaria_id = parseInt($('select[name="cuenta_bancaria_id"]').val()) || null;

                    payload.tipo_retiro        = $('select[name="tipo_retiro"]').val() || null;

                }

                if (servicioNombre === 'Venta de Efectivo') {

                    payload.porcentaje_amiri    = parseFloat($('.input-porc-amiri').val())    || 0;

                    payload.porcentaje_promotor = parseFloat($('.input-porc-promotor').val()) || 0;

                }

                $.ajax({

                    url: 'api/flujo.php',

                    method: 'POST',

                    contentType: 'application/json',

                    data: JSON.stringify(payload),

                    success: function (res) {

                        if (res.success) {

                            $('#modalFlujo').modal('hide');

                            table.ajax.reload();

                            cargarResumenes();

                            showToast('Movimiento guardado correctamente', 'success');

                        } else {

                            showToast(res.error || 'Error al guardar', 'error');

                        }

                    },

                    error: function (xhr) {

                        showToast(xhr.responseJSON?.error || 'Error al guardar movimiento', 'error');

                    }

                });

            }

        });



    });

    </script>



</body>

</html>

