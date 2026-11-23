<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

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

    WHERE activo = 1 AND id IN (1, 4, 7, 20)

    ORDER BY FIELD(id, 1, 4, 7, 20)

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

    <link rel="stylesheet" href="src/assets/css/layout-custom.css">

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
    /* =========================================
   🌙 ESTILOS PARA TARJETAS DE RESUMEN EN MODO OSCURO (BLINDADO)
   ========================================= */

/* 1. RECUPERAR EL TAMAÑO ORIGINAL (Venciendo la regla global de padding) */
[data-bs-theme="dark"] .card .card-header.header-saldos,
[data-bs-theme="dark"] .card .card-header.header-capital,
[data-bs-theme="dark"] .card .card-header.header-prestamos {
    padding: 16px 20px !important; /* Devuelve el tamaño grande */
    border-radius: 12px 12px 0 0 !important;
}

/* 2. SALDOS BANCARIOS (SUCCESS) */
[data-bs-theme="dark"] .card .header-saldos {
    background-color: rgba(102, 187, 106, 0.15) !important;
    border-bottom: 1px solid rgba(102, 187, 106, 0.3) !important;
}
[data-bs-theme="dark"] .card .header-saldos span,
[data-bs-theme="dark"] .card .header-saldos i {
    color: #66bb6a !important; /* Verde exacto de tu plantilla */
}

/* 3. CAPITAL (WARNING - Este color faltaba en tu dark-mode.css) */
[data-bs-theme="dark"] .card .header-capital {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border-bottom: 1px solid rgba(255, 193, 7, 0.3) !important;
}
[data-bs-theme="dark"] .card .header-capital span,
[data-bs-theme="dark"] .card .header-capital i {
    color: #ffc107 !important; /* Amarillo brillante */
}

/* 4. PRÉSTAMOS (DANGER) */
[data-bs-theme="dark"] .card .header-prestamos {
    background-color: rgba(239, 83, 80, 0.15) !important;
    border-bottom: 1px solid rgba(239, 83, 80, 0.3) !important;
}
[data-bs-theme="dark"] .card .header-prestamos span,
[data-bs-theme="dark"] .card .header-prestamos i {
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
[data-bs-theme="dark"] .header-saldos, .dark-mode .header-saldos {
    background-color: rgba(25, 135, 84, 0.15) !important;
    border-bottom: 1px solid rgba(255,255,255,0.12) !important;
}
[data-bs-theme="dark"] .header-capital, .dark-mode .header-capital {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border-bottom: 1px solid rgba(255,255,255,0.12) !important;
}
[data-bs-theme="dark"] .header-prestamos, .dark-mode .header-prestamos {
    background-color: rgba(220, 53, 69, 0.15) !important;
    border-bottom: 1px solid rgba(255,255,255,0.12) !important;
}

/* ── Capital destacado: Capital Amiri / Utilidades AMIRI ──────────── */
.capital-destacado { background: #fff8e1 !important; border-left: 4px solid #ffc107 !important; font-weight: 700 !important; position: sticky; z-index: 11; }
.capital-destacado td { background: transparent !important; font-weight: 700 !important; }
.capital-destacado:nth-of-type(1) { top: 38px; }
.capital-destacado:nth-of-type(2) { top: 76px; }
.capital-destacado .ti { color: #ffb300 !important; }
[data-bs-theme="dark"] .capital-destacado, .dark-mode .capital-destacado { background: rgba(255,193,7,0.18) !important; border-left-color: #ffc107 !important; }
[data-bs-theme="dark"] .capital-destacado td, .dark-mode .capital-destacado td { color: #ffe08a !important; }

/* 3. MODO OSCURO: FORZAR COLORES DE TEXTO E ICONOS (Para vencer al text-white) */
[data-bs-theme="dark"] .header-saldos, .dark-mode .header-saldos,
[data-bs-theme="dark"] .header-saldos *, .dark-mode .header-saldos * {
    color: #36c76c !important; /* Verde brillante */
}
[data-bs-theme="dark"] .header-capital, .dark-mode .header-capital,
[data-bs-theme="dark"] .header-capital *, .dark-mode .header-capital * {
    color: #ffc107 !important; /* Amarillo brillante */
}
[data-bs-theme="dark"] .header-prestamos, .dark-mode .header-prestamos,
[data-bs-theme="dark"] .header-prestamos *, .dark-mode .header-prestamos * {
    color: #fc4b6c !important; /* Rojo brillante */
}
/* =========================================
           🎨 BADGES VIBRANTES (MODO OSCURO)
           ========================================= */
        [data-bs-theme="dark"] .table .badge.bg-success { background-color: rgba(19, 222, 185, 0.15) !important; color: var(--nx-success) !important; background-image: none !important; }
        [data-bs-theme="dark"] .table .badge.bg-danger { background-color: rgba(250, 137, 107, 0.15) !important; color: var(--nx-danger) !important; background-image: none !important; }
        [data-bs-theme="dark"] .table .badge.bg-info { background-color: rgba(93, 135, 255, 0.15) !important; color: var(--nx-primary) !important; background-image: none !important; }
        [data-bs-theme="dark"] .table .badge.bg-warning { background-color: rgba(255, 174, 31, 0.15) !important; color: var(--nx-warning) !important; background-image: none !important; }
        [data-bs-theme="dark"] .table .badge.bg-primary { background-color: rgba(93, 135, 255, 0.15) !important; color: var(--nx-primary) !important; background-image: none !important; }
        [data-bs-theme="dark"] .table .badge.bg-secondary { background-color: rgba(140, 158, 168, 0.15) !important; color: #9a9096 !important; background-image: none !important; }
        
        /* Badges Especiales de Cripto/Dólares en Flujo */
        [data-bs-theme="dark"] .table .badge.badge-usd { background-color: rgba(37, 99, 235, 0.15) !important; background-image: none !important; color: #3b82f6 !important; }
        [data-bs-theme="dark"] .table .badge.badge-usdt { background-color: rgba(16, 185, 129, 0.15) !important; background-image: none !important; color: #10b981 !important; }

        /* =========================================
           📦 CAJA BLANCA DE INVOLUCRADOS (MODO OSCURO)
           ========================================= */
        [data-bs-theme="dark"] .table>tbody>tr>td .bg-light {
            background-color: #1a181c !important; /* Fondo gris oscuro / azulado */
            border-color: rgba(255,255,255,0.12) !important; /* Borde oscuro sutil */
        }

    </style>

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"

        data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

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
                                    <div id="diferenciaDetalle" class="mt-2" style="font-size:0.85rem;"></div>
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
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
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
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
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
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
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
                                <div class="card-body p-4 table-responsive">
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

                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-t', strtotime($_SESSION['mes_activo'] ?? date('Y-m'))) ?>" required>

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



    <!-- Modal Proveedor -->

    <div class="modal fade" id="modalProveedor" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header bg-info bg-opacity-10">

                    <h5 class="modal-title"><i class="ti ti-building text-info"></i> <span id="proveedorModalNombre">Proveedor</span></h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>

                </div>

            </div>

        </div>

    </div>



    <!-- Modal Pago Proveedor (unificado MXN / USD / USDT) -->

    <div class="modal fade" id="modalPagoProveedorMXN" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-warning bg-opacity-10">

                    <h5 class="modal-title" id="pagoMxnModalTitle"><i class="ti ti-cash text-warning"></i> Pagar Proveedor</h5>

                    <button class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <form id="formPagoProveedorMXN">

                    <div class="modal-body">

                        <input type="hidden" id="pagoMxnProveedorId" name="proveedor_id">

                        <input type="hidden" id="pagoMxnMoneda" name="moneda_proveedor" value="MXN">

                        <input type="hidden" id="pagoMxnParidad" value="1">



                        <div class="alert alert-secondary py-2 mb-3">

                            <div class="d-flex justify-content-between align-items-center mb-1">

                                <span class="fw-bold fs-6" id="pagoMxnNombreProveedor"></span>

                                <span class="badge border-0" id="pagoMxnBadgeMoneda" style="font-size: 0.85rem;">MXN</span>

                            </div>

                            <div class="row text-center mt-2">

                                <div class="col-6">

                                    <div class="text-muted small" id="pagoMxnLabelAdeudo">Adeudo</div>

                                    <div class="fw-bold" id="pagoMxnInfoAdeudo">—</div>

                                    <small class="text-muted" id="pagoMxnInfoAdeudoMxn" style="display:none;"></small>

                                </div>

                                <div class="col-6">

                                    <div class="text-muted small">Total a pagar</div>

                                    <div class="fw-bold text-danger" id="pagoMxnInfoTotal">—</div>

                                </div>

                            </div>

                        </div>



                        <div class="mb-3">

                            <label class="form-label fw-semibold">Fecha de Pago *</label>

                            <input type="date" id="pagoMxnFecha" class="form-control" required>

                        </div>



                        <div class="mb-3">

                            <label class="form-label fw-semibold">Cuenta de Pago *</label>

                            <select id="pagoMxnCuentaSelect" name="cuenta_pago_id" class="form-control" required>

                                <option value="">Seleccione cuenta</option>

                            </select>

                        </div>



                        <div class="mb-3" id="pagoMxnGrupoMonto" style="display:none;">

                            <label class="form-label fw-semibold" id="pagoMxnLabelMonto">Monto a Pagar (MXN) *</label>

                            <div class="input-group">

                                <span class="input-group-text" id="pagoMxnSimboloIzq">$</span>

                                <input type="text" id="pagoMxnMonto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>

                                <span class="input-group-text" id="pagoMxnSimboloDer">MXN</span>

                            </div>

                            <small id="pagoMxnMontoLabel" class="text-success fw-bold mt-1 d-block" style="min-height:1.2em;"></small>
                            <small id="pagoMxnEquivalencia" class="text-muted d-block" style="display:none;"></small>

                            <div class="d-flex justify-content-between align-items-center mt-1">

                                <small class="text-muted" id="pagoMxnInfoSaldo"></small>

                                <button type="button" class="btn btn-outline-success btn-sm" id="btnMxnLiquidarTotal" style="display:none;">

                                    <i class="ti ti-check"></i> Liquidar Total

                                </button>

                            </div>

                            <div id="pagoMxnAlertaSaldo" class="alert alert-warning py-2 mt-2" style="display:none;">

                                <i class="ti ti-alert-triangle"></i> El monto no puede superar el adeudo ni el saldo de la cuenta.

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <button type="submit" class="btn btn-warning" id="btnMxnProcesarPago" style="display:none;">

                            <i class="ti ti-cash"></i> Procesar Pago

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- Modal Deudor (historial completo) -->

    <div class="modal fade" id="modalDeudor" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header bg-warning bg-opacity-10">

                    <h5 class="modal-title"><i class="ti ti-user text-warning"></i> <span id="deudorModalNombre">Deudor</span></h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>

                </div>

            </div>

        </div>

    </div>

    <!-- Modal Cobro Deudor Directo (igual que Proveedores: pago en MXN) -->
    <div class="modal fade" id="modalCobroDeudor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success bg-opacity-10">
                    <h5 class="modal-title" id="cobroDeudorModalTitle"><i class="ti ti-cash text-success"></i> Cobrar Deudor</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCobroDeudorDirecto">
                    <div class="modal-body">
                        <input type="hidden" id="cobroDeudorNombre">
                        <div class="alert alert-secondary py-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold fs-6" id="cobroDeudorNombreDisplay">—</span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border-0" style="font-size:0.85rem;">MXN</span>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-6">
                                    <div class="text-muted small">Pendiente por cobrar</div>
                                    <div class="fw-bold text-danger" id="cobroDeudorInfoPendiente">—</div>
                                    <small class="text-muted" id="cobroDeudorInfoCobrado"></small>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Total a cobrar</div>
                                    <div class="fw-bold" id="cobroDeudorInfoTotal">—</div>
                                    <small class="text-muted" id="cobroDeudorInfoInteres" style="display:none;"></small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha de Cobro *</label>
                            <input type="date" id="cobroDeudorFecha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cuenta Destino (MXN) *</label>
                            <select id="cobroDeudorCuenta" class="form-control" required><option value="">Seleccione cuenta</option></select>
                        </div>
                        <div class="mb-3" id="cobroDeudorGrupoMonto" style="display:none;">
                            <label class="form-label fw-semibold">Monto a Cobrar (MXN) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" id="cobroDeudorMonto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                                <span class="input-group-text">MXN</span>
                            </div>
                            <small id="cobroDeudorMontoLabel" class="text-success fw-bold mt-1 d-block" style="min-height:1.2em;"></small>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" id="cobroDeudorInfoSaldo"></small>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnCobroLiquidar" style="display:none;"><i class="ti ti-check"></i> Liquidar Total</button>
                            </div>
                            <div id="cobroDeudorAlerta" class="alert alert-warning py-2 mt-2" style="display:none;"><i class="ti ti-alert-triangle"></i> El monto no puede superar el pendiente.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnCobroProcesar" style="display:none;"><i class="ti ti-check"></i> Procesar Cobro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>



    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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

        [data-bs-theme="dark"] .modal-content { background: var(--nx-surface) !important; color: var(--nx-text) !important; border-color: var(--nx-border) !important; }

        [data-bs-theme="dark"] .input-group-text,

        [data-bs-theme="dark"] .form-control,

        [data-bs-theme="dark"] .form-select                   { background: #111827; border-color: #1f2937; color: #e5e7eb; }

        [data-bs-theme="dark"] .form-control:focus,

        [data-bs-theme="dark"] .form-select:focus             { border-color: #2563eb; box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.35); }



        #modalFlujo .form-select,

        #modalFlujo .form-control  { height: 48px; padding: 12px 16px; font-size: 1rem; cursor: pointer; }

        #modalFlujo .form-label    { font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }

        #modalFlujo .form-select   { background-position: right 16px center; }

        /* ── Dark mode: Modales Proveedor / Deudor ────────────────────── */
        /* Cards dentro de los modales */
        [data-bs-theme="dark"] #modalProveedor .card,
        [data-bs-theme="dark"] #modalDeudor .card                 { background-color: #111827 !important; border-color: #1f2937 !important; }

        [data-bs-theme="dark"] #modalProveedor .card-body,
        [data-bs-theme="dark"] #modalDeudor .card-body            { background-color: transparent !important; color: #e5e7eb !important; }

        [data-bs-theme="dark"] #modalProveedor .card-header,
        [data-bs-theme="dark"] #modalDeudor .card-header          { background-color: #1e293b !important; border-color: #1f2937 !important; color: #e5e7eb !important; }

        /* Tablas */
        [data-bs-theme="dark"] #modalProveedor .table,
        [data-bs-theme="dark"] #modalDeudor .table                 { color: #e5e7eb !important; --bs-table-bg: transparent !important; }

        [data-bs-theme="dark"] #modalProveedor .table > :not(caption) > * > *,
        [data-bs-theme="dark"] #modalDeudor .table > :not(caption) > * > *   { background-color: transparent !important; color: #e5e7eb !important; border-color: #1f2937 !important; }

        [data-bs-theme="dark"] #modalProveedor .table thead th,
        [data-bs-theme="dark"] #modalDeudor .table thead th        { background-color: #1e293b !important; color: #94a3b8 !important; border-color: #1f2937 !important; }

        [data-bs-theme="dark"] #modalProveedor .table-striped > tbody > tr:nth-of-type(odd) > *,
        [data-bs-theme="dark"] #modalDeudor .table-striped > tbody > tr:nth-of-type(odd) > *  { background-color: rgba(255,255,255,0.02) !important; }

        [data-bs-theme="dark"] #modalProveedor .table-hover tbody tr:hover > *,
        [data-bs-theme="dark"] #modalDeudor .table-hover tbody tr:hover > *    { background-color: rgba(99,102,241,0.12) !important; color: #e5e7eb !important; }

        [data-bs-theme="dark"] #modalProveedor .table-responsive,
        [data-bs-theme="dark"] #modalDeudor .table-responsive      { border-color: #1f2937 !important; }

        /* Textos */
        [data-bs-theme="dark"] #modalProveedor .text-muted,
        [data-bs-theme="dark"] #modalDeudor .text-muted            { color: #6b7280 !important; }

        [data-bs-theme="dark"] #modalProveedor .text-body,
        [data-bs-theme="dark"] #modalDeudor .text-body             { color: #e5e7eb !important; }

        [data-bs-theme="dark"] #modalProveedor h4,
        [data-bs-theme="dark"] #modalDeudor h4,
        [data-bs-theme="dark"] #modalProveedor h6,
        [data-bs-theme="dark"] #modalDeudor h6,
        [data-bs-theme="dark"] #modalProveedor h5,
        [data-bs-theme="dark"] #modalDeudor h5                     { color: #e5e7eb !important; }

        [data-bs-theme="dark"] #modalProveedor small,
        [data-bs-theme="dark"] #modalDeudor small                  { color: #9ca3af !important; }

        [data-bs-theme="dark"] #modalProveedor .form-label,
        [data-bs-theme="dark"] #modalDeudor .form-label            { color: #d1d5db !important; }

        /* Badges */
        [data-bs-theme="dark"] #modalProveedor .badge,
        [data-bs-theme="dark"] #modalDeudor .badge                 { background-image: none !important; }

        /* Input groups */
        [data-bs-theme="dark"] #modalProveedor .input-group-text,
        [data-bs-theme="dark"] #modalDeudor .input-group-text      { background-color: #1e293b !important; border-color: #1f2937 !important; color: #e5e7eb !important; }

        /* Alertas */
        [data-bs-theme="dark"] #modalProveedor .alert-danger,
        [data-bs-theme="dark"] #modalDeudor .alert-danger          { background-color: rgba(220,53,69,0.15) !important; color: #f8d7da !important; border-color: rgba(220,53,69,0.3) !important; }

        /* Rows */
        [data-bs-theme="dark"] #modalProveedor .row,
        [data-bs-theme="dark"] #modalDeudor .row                    { --bs-gutter-x: 1rem; }

        /* Strong / bold */
        [data-bs-theme="dark"] #modalProveedor strong,
        [data-bs-theme="dark"] #modalDeudor strong                  { color: #e5e7eb !important; }

        /* p tags */
        [data-bs-theme="dark"] #modalProveedor p,
        [data-bs-theme="dark"] #modalDeudor p                       { color: #e5e7eb !important; }

        /* Iconos */
        [data-bs-theme="dark"] #modalProveedor .ti,
        [data-bs-theme="dark"] #modalDeudor .ti                     { color: inherit !important; }

        /* hr */
        [data-bs-theme="dark"] #modalProveedor hr,
        [data-bs-theme="dark"] #modalDeudor hr                      { border-color: #1f2937 !important; }

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

        function buildEfectosReverso(res) {
          var html = '<hr><h6 style="color:#ffc107;font-weight:600;margin-top:14px;"><i class="ti ti-list-details"></i> Efectos del Reverso:</h6>';
          html += '<p class="text-muted" style="font-size:.85rem;margin-bottom:10px;">Se crear\u00e1n <strong>' + (res.total_movimientos_reverso || 0) + '</strong> movimiento(s) de reverso (tipo: ajuste) que afectar\u00e1n las siguientes cuentas:</p>';
          if (res.efectos && res.efectos.length > 0) {
            html += '<div style="border-radius:8px;padding:8px 12px;">';
            $.each(res.efectos, function(i, e) {
              var diffClass = e.diferencia >= 0 ? 'text-success' : 'text-danger';
              var diffSign = e.diferencia >= 0 ? '+' : '';
              html += '<div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:.85rem;">' +
                '<div><strong>' + (e.cuenta_nombre || '-') + '</strong></div>' +
                '<div class="' + diffClass + '" style="font-weight:600;">$' + formatMXN(e.saldo_anterior) + ' <i class="ti ti-arrow-right"></i> $' + formatMXN(e.saldo_nuevo) + ' <span class="' + diffClass + '">(' + diffSign + '$' + formatMXN(Math.abs(e.diferencia)) + ')</span></div></div>';
            });
            html += '</div>';
          }
          if (res.deudas_afectadas && res.deudas_afectadas.length > 0) {
            $.each(res.deudas_afectadas, function(i, d) {
              html += '<div class="mt-2 bg-warning bg-opacity-10" style="border:1px solid rgba(255,193,7,0.2);border-radius:8px;padding:10px;font-size:.85rem;">';
              html += '<strong><i class="ti ti-refresh"></i> ' + (d.entidad || '') + ' #' + d.id + ':</strong><br>';
              if (d.accion) {
                html += d.accion;
              } else {
                html += 'monto_pagado: <strong>$' + formatMXN(d.monto_pagado_anterior) + '</strong> <i class="ti ti-arrow-right"></i> <strong>$' + formatMXN(d.monto_pagado_nuevo) + '</strong> | ';
                html += 'estado: <strong>' + (d.estado_anterior || '') + '</strong> <i class="ti ti-arrow-right"></i> <strong>' + (d.estado_nuevo || '') + '</strong>';
              }
              html += '</div>';
            });
          }
          return html;
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

        processing: true,
            serverSide: true,

            ajax: { url: 'api/flujo-lista.php?servicios_ids=1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20', dataSrc: 'data' },

            columns: [
                {
                    data: 'grupo_id', title: 'Grupo', visible: false,
                    render: d => '<span class="badge bg-secondary bg-opacity-10 text-secondary border-0">#' + d + '</span>'
                },
                { 
                    data: 'fecha', title: 'Fecha',
                    render: function(data, type, row) {
                        if (!data) return '';
                        
                        // ¡MAGIA DE ORDENAMIENTO!: Le damos la fecha cruda a la tabla para que sepa ordenar
                        if (type === 'sort' || type === 'type') {
                            return data; 
                        }

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
                        return `<span class="fw-bold text-body">${fechaOperacion}</span>${infoExtra}`;
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
                    render: d => '<strong class="text-body">' + d + '</strong>'
                },
                {
                    data: 'cantidad_movimientos', title: 'Movimientos',
                    render: d => '<span class="badge bg-info bg-opacity-10 text-info border-0">' + d + ' mov.</span>'
                },
                {
                    data: null, title: 'Monto',
                    render: function (data, type, row) {
                        let monto = parseFloat(row.monto_principal || 0);
                        let html = '<span class="fw-bold text-success">$' + monto.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
                        // Desglose préstamo otorgado con interés compuesto
                        if (row.servicio === 'Préstamo Otorgado' && row.po_total) {
                            let cap = parseFloat(row.po_capital || 0);
                            let interes = parseFloat(row.po_interes || 0);
                            if (interes > 0.01) {
                                let tasa = row.po_tasa ? parseFloat(row.po_tasa).toFixed(2) + '% ' + (row.po_tasa_periodo || 'mensual') : '';
                                let venc = row.po_vencimiento ? row.po_vencimiento.split('-').reverse().join('/') : '';
                                html += '<br><small class="text-muted" style="font-size:0.70rem;">Cap $' + cap.toLocaleString('es-MX',{minimumFractionDigits:2}) + ' + Int $' + interes.toLocaleString('es-MX',{minimumFractionDigits:2}) + '</small>';
                                if (tasa) html += '<br><small class="badge bg-warning bg-opacity-10 text-warning border-0" style="font-size:0.65rem;">' + tasa + '</small>';
                                if (venc) html += ' <small class="text-muted" style="font-size:0.65rem;">vence ' + venc + '</small>';
                            }
                        }
                        return html;
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
                    data: null, 
                    title: 'Movimiento',
                    width: '100px',
                    render: function (data, type, row) {
                        let html = '<div style="font-size:0.85rem; line-height: 1.5; min-width: 150px;">';
                        
                        // 1. LA PERSONA (A quién se le paga o quién nos paga)
                        let personaHtml = '';
                        if (row.cliente  && row.cliente.trim())  personaHtml += '<div class="mb-1"><i class="ti ti-user text-primary"></i> <strong class="text-body">Cliente:</strong> <span class="text-muted">' + row.cliente + '</span></div>';
                        if (row.proveedor && row.proveedor.trim()) personaHtml += '<div class="mb-1"><i class="ti ti-building text-info"></i> <strong class="text-body">Proveedor:</strong> <span class="text-muted">' + row.proveedor + '</span></div>';
                        if (row.promotor  && row.promotor.trim())  personaHtml += '<div class="mb-1"><i class="ti ti-user-star text-warning"></i> <strong class="text-body">Promotor:</strong> <span class="text-muted">' + row.promotor + '</span></div>';
                        if (row.prestamista && row.prestamista.trim()) personaHtml += '<div class="mb-1"><i class="ti ti-businessplan text-danger"></i> <strong class="text-body">Prestamista:</strong> <span class="text-muted">' + row.prestamista + '</span></div>';
                        if (row.beneficiario && row.beneficiario.trim()) personaHtml += '<div class="mb-1"><i class="ti ti-users text-success"></i> <strong class="text-body">Beneficiario:</strong> <span class="text-muted">' + row.beneficiario + '</span></div>';
                        
                        if (personaHtml) {
                            html += '<div class="p-2 mb-2 rounded bg-light border border-light-subtle">' + personaHtml + '</div>';
                        }

                        // 2. LAS CUENTAS REALES (Filtramos la basura virtual)
                        function limpiarCuentas(cuentasString) {
                            if (!cuentasString || !cuentasString.trim()) return [];
                            return cuentasString.split(/[,|]/)
                                .map(x => x.trim())
                                .filter(x => x !== '')
                                .filter(x => !x.startsWith('Caja -') && !x.startsWith('Comisiones -') && !x.startsWith('Caja Proveedor'));
                        }

                        let unicosOrigen = [...new Set(limpiarCuentas(row.cuentas_origen))];
                        let unicosDestino = [...new Set(limpiarCuentas(row.cuentas_destino))];

                        // 3. LÓGICA HUMANA DE LECTURA (Lo que pidió Jorge)
                        let tipoMov = row.tipo_principal || '';

                        if (tipoMov === 'Salida') {
                            // Si es una salida (pago a cliente/proveedor), SOLO decimos de dónde salió el dinero
                            if (unicosOrigen.length) {
                                html += '<div><i class="ti ti-arrow-up text-danger"></i> <strong class="text-body">Salió de:</strong><br><span class="text-muted ps-3 d-block">• ' + unicosOrigen.join('<br>• ') + '</span></div>';
                            }
                        }
                        else if (tipoMov === 'Entrada') {
                            // Si es una entrada, SOLO decimos a qué banco nuestro entró
                            if (unicosDestino.length) {
                                html += '<div><i class="ti ti-arrow-down text-success"></i> <strong class="text-body">Entró a:</strong><br><span class="text-muted ps-3 d-block">• ' + unicosDestino.join('<br>• ') + '</span></div>';
                            }
                        }
                        else {
                            // Si es un Traspaso o movimiento combinado, mostramos ambas
                            if (unicosOrigen.length) html += '<div><i class="ti ti-arrow-up text-danger"></i> <strong class="text-body">Salió de:</strong><br><span class="text-muted ps-3 d-block">• ' + unicosOrigen.join('<br>• ') + '</span></div>';
                            if (unicosDestino.length) html += '<div class="mt-1"><i class="ti ti-arrow-down text-success"></i> <strong class="text-body">Entró a:</strong><br><span class="text-muted ps-3 d-block">• ' + unicosDestino.join('<br>• ') + '</span></div>';
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

            scrollX: true

        });

        // ─── EVENTO PARA CAMBIAR ENTRE PESTAÑAS (ACTIVOS / ANULADOS) ───
        $('#tabsFlujo button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            // Cambiar estilos de la pestaña activa
            $('#tabsFlujo button').removeClass('border-bottom border-2 border-primary text-primary').addClass('text-muted');
            $(e.target).removeClass('text-muted').addClass('border-bottom border-2 border-primary text-primary');

            // Recargar la tabla con la nueva vista
            let vistaActual = $(e.target).data('vista');
            let nuevaUrl = 'api/flujo-lista.php?servicios_ids=1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20&vista=' + vistaActual;
            table.ajax.url(nuevaUrl).load();
        });



        // ─── Resúmenes ───────────────────────────────────────────────────────



        function cargarSaldos() {
            $.getJSON('api/flujo-resumen.php?tipo=saldos&moneda=TODAS', function (data) {
                let html = '', totalMxn = 0;
                if (data.cuentas && data.cuentas.length) {
                    
                    // ✨ MAGIA APLICADA: Ordenar de mayor a menor basado en el valor real
                    data.cuentas.sort(function(a, b) {
                        let mxnA = a.tipo_moneda === 'MXN' ? parseFloat(a.saldo_actual || 0) : parseFloat(a.valor_mxn || 0);
                        let mxnB = b.tipo_moneda === 'MXN' ? parseFloat(b.saldo_actual || 0) : parseFloat(b.valor_mxn || 0);
                        return mxnB - mxnA; // El mayor arriba, el menor abajo
                    });

                    data.cuentas.forEach(function (c) {
                        let saldo = parseFloat(c.saldo_actual || 0);
                        if (Math.abs(saldo) < 0.01) return;
                        let mxn   = c.tipo_moneda === 'MXN' ? saldo : parseFloat(c.valor_mxn || 0);
                        totalMxn += mxn;
                        let montoDisplay = mostrarEnMXN
                            ? '$' + formatMXN(mxn) + ' MXN'
                            : (c.tipo_moneda === 'MXN' ? '$' + formatMXN(saldo) + ' MXN' : formatNativo(saldo) + ' ' + c.tipo_moneda);
                        let badge = c.tipo_moneda === 'USD'  ? '<span class="badge bg-primary bg-opacity-10 text-primary border-0">USD</span>' :
                                    c.tipo_moneda === 'USDT' ? '<span class="badge bg-info bg-opacity-10 text-info border-0">USDT</span>' :
                                    '<span class="badge bg-success bg-opacity-10 text-success border-0">MXN</span>';
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

                    const esDestacado = c => {
                        const n = (c.nombre || '').trim();
                        return n === 'Capital Amiri' || n.toUpperCase() === 'UTILIDADES AMIRI' || n === 'Utilidades AMIRI';
                    };
                    const destacados = data.cuentas.filter(esDestacado);
                    const resto = data.cuentas.filter(c => !esDestacado(c));

                    // Orden fijo para destacados: Capital Amiri primero, luego Utilidades
                    destacados.sort((a,b) => {
                        const order = { 'Capital Amiri': 0, 'Utilidades AMIRI': 1, 'Utilidades Amiri': 1 };
                        return (order[a.nombre] ?? 99) - (order[b.nombre] ?? 99);
                    });
                    resto.sort(function(a, b) {
                        let mxnA = a.tipo_moneda === 'MXN' ? parseFloat(a.saldo_actual || 0) : parseFloat(a.valor_mxn || 0);
                        let mxnB = b.tipo_moneda === 'MXN' ? parseFloat(b.saldo_actual || 0) : parseFloat(b.valor_mxn || 0);
                        return mxnB - mxnA;
                    });
                    const ordenadas = [...destacados, ...resto];

                    ordenadas.forEach(function (c) {
                        const destacado = esDestacado(c);

                        let saldo = parseFloat(c.saldo_actual || 0);

                        let mxn   = c.tipo_moneda === 'MXN' ? saldo : parseFloat(c.valor_mxn || 0);

                        // Destacados se muestran aunque estén en 0 para que no se pierdan
                        if (!destacado && Math.abs(saldo) < 0.01) return;

                        totalMxn += mxn;



                        let nombre = c.nombre;
                        if (destacado) {
                            const icon = c.nombre === 'Capital Amiri' ? 'ti ti-pig-money' : 'ti ti-building-bank';
                            nombre = `<span class="d-inline-flex align-items-center gap-1"><i class="${icon}"></i> ${c.nombre} <i class="ti ti-star-filled" style="font-size:0.7rem; color:#ffb300;"></i></span>`;
                        } else if (c.es_deuda_cliente && c.cliente_id) {

                            nombre = `<a href="detalle-cliente.php?id=${c.cliente_id}" class="text-decoration-none">${c.nombre}</a>`;

                        } else if (c.es_deuda_promotor && c.promotor_id) {

                            nombre = `<a href="detalle-promotor.php?id=${c.promotor_id}" class="text-decoration-none">${c.nombre}</a>`;

                        }



                        let montoDisplay = mostrarEnMXN

                            ? '$' + formatMXN(mxn) + ' MXN'

                            : (c.tipo_moneda === 'MXN' ? '$' + formatMXN(saldo) + ' MXN' : formatNativo(saldo) + ' ' + c.tipo_moneda);

                        const filaCls = destacado ? 'capital-destacado' : '';
                        const badgeCls = destacado ? 'bg-warning text-dark border' : 'bg-warning bg-opacity-10 text-warning border-0';

                        html += `<tr class="${filaCls}">

                            <td>${nombre}</td>

                            <td><span class="badge ${badgeCls}">${c.tipo_cuenta_nombre || 'Capital'}</span></td>

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
                if (loaded < 3) return; // <--- Cambiamos a 3 peticiones

                let html = '', totalMxn = 0;
                let tipoOrden = { 'Prestamista': 0, 'Proveedor': 1, 'Deudor': 2 };
                entries.sort(function(a, b) {
                    let tipoA = tipoOrden[a.tipo] !== undefined ? tipoOrden[a.tipo] : 3;
                    let tipoB = tipoOrden[b.tipo] !== undefined ? tipoOrden[b.tipo] : 3;
                    if (tipoA !== tipoB) return tipoA - tipoB;
                    let mxnA = a.tipo_moneda === 'MXN' ? a.monto : (a.valor_mxn || 0);
                    let mxnB = b.tipo_moneda === 'MXN' ? b.monto : (b.valor_mxn || 0);
                    return mxnB - mxnA;
                });
                entries.forEach(function (e) {
                    let monto = parseFloat(e.monto || 0);
                    let mxn   = e.tipo_moneda === 'MXN' ? monto : parseFloat(e.valor_mxn || 0);
                    if (Math.abs(monto) < 0.01) return;
                    totalMxn += mxn;

                    let montoDisplay = '$' + formatMXN(mxn) + ' MXN';
                    
                    // Asignar colores según quién nos debe o a quién le debemos
                    let badgeCls = 'bg-secondary bg-opacity-10 text-secondary border-0';
                    if (e.tipo === 'Prestamista') badgeCls = 'bg-danger bg-opacity-10 text-danger border-0';
                    else if (e.tipo === 'Proveedor') badgeCls = e.tipo_moneda === 'USD' ? 'bg-primary bg-opacity-10 text-primary border-0' : 'bg-info bg-opacity-10 text-info border-0';
                    else if (e.tipo === 'Deudor') badgeCls = 'bg-warning bg-opacity-10 text-warning border-0';

                    let nombreCell = e.nombre;
                    if (e.tipo === 'Prestamista') {
                        nombreCell = `<a href="detalle-prestamista.php?id=${e.id || ''}" class="text-decoration-none fw-semibold text-danger">${e.nombre}</a>`;
                    } else if (e.tipo === 'Proveedor') {
                        const monedaRow = (e.tipo_moneda || 'MXN').toUpperCase();
                        nombreCell = `<a href="#" class="text-decoration-none fw-semibold text-info" onclick="abrirPagoProveedorDirecto(${e.id || 0}, '${monedaRow}'); return false;" title="Pagar ${monedaRow} - click para pagar directo">${e.nombre}</a> <a href="#" class="text-muted ms-1" style="font-size:0.8rem;" onclick="abrirProveedorModal(${e.id || 0}); return false;" title="Ver historial"><i class="ti ti-history"></i></a>`;
                    } else if (e.tipo === 'Deudor') {
                        nombreCell = `<a href="#" class="text-decoration-none fw-semibold text-warning" onclick="abrirCobroDeudorDirecto('${encodeURIComponent(e.nombre)}'); return false;" title="Cobrar - click para cobrar directo">${e.nombre}</a> <a href="#" class="text-muted ms-1" style="font-size:0.8rem;" onclick="abrirDeudorModal('${encodeURIComponent(e.nombre)}'); return false;" title="Ver historial"><i class="ti ti-history"></i></a>`;
                    }
                        
                    html += `<tr>
                        <td>${nombreCell}</td>
                        <td><span class="badge ${badgeCls}">${e.tipo}</span></td>
                        <td class="text-end fw-bold">${montoDisplay}</td>
                    </tr>`;
                });
                if (!html) html = `<tr><td colspan="3" class="text-center text-muted py-3">Sin obligaciones pendientes</td></tr>`;
                $('#obligacionesBody').html(html);
            }

            // 1. Prestamistas MXN (Lo que debemos nosotros)
            $.getJSON('api/flujo-resumen.php?tipo=prestamos&moneda=MXN', function (data) {
                if (data && data.length) {
                    data.forEach(function (p) {
                        entries.push({
                            id:          p.id || null,
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

            // 2. Proveedores USD/USDT (Lo que debemos nosotros)
            $.getJSON('api/flujo-resumen.php?tipo=proveedor&moneda=TODAS', function (data) {
                if (data && data.proveedores) {
                    data.proveedores.forEach(function (p) {
                        entries.push({
                            id:          p.id || null,
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

            // 3. Préstamos Otorgados (Lo que NOS DEBEN a nosotros) <-- incluye interés compuesto
            $.getJSON('api/prestamos-otorgados.php', function (data) {
                if (data && data.length) {
                    let agrupado = {};
                    data.forEach(function (p) {
                        let total = parseFloat(p.monto_total_con_interes || p.monto_total || 0);
                        let pendiente = total - parseFloat(p.monto_pagado || 0);
                        // fallback si monto_total_con_interes es 0 por bug viejo
                        if (pendiente <= 0.001 && total === 0) pendiente = parseFloat(p.monto_total || 0) - parseFloat(p.monto_pagado || 0);
                        if (pendiente > 0.001) {
                            let key = p.deudor_nombre || p.persona_nombre;
                            if (!agrupado[key]) agrupado[key] = { total: 0, ids: [] };
                            agrupado[key].total += pendiente;
                            agrupado[key].ids.push(p.id);
                        }
                    });
                    
                    for (let persona in agrupado) {
                        entries.push({
                            nombre:      persona,
                            tipo:        'Deudor',
                            monto:       agrupado[persona].total,
                            tipo_moneda: 'MXN',
                            valor_mxn:   agrupado[persona].total,
                            ids:         agrupado[persona].ids.join(',')
                        });
                    }
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

                const $detalle  = $('#diferenciaDetalle');

                const expl      = data.explicacion || [];

                const resumen   = data.resumen || '';



                $monto.text('$' + formatMXN(diferencia) + ' MXN');



                // Construir bloque de detalle (siempre visible cuando hay factores)
                let detalleHtml = '';
                if (expl.length > 0) {
                    detalleHtml += '<div class="p-2 rounded" style="background-color:rgba(0,0,0,0.03);">';
                    expl.forEach(function(item) {
                        const icono = item.tipo === 'prestamos_otorgados' ? 'ti ti-hand-stop' : 'ti ti-info-circle';
                        detalleHtml += '<div class="d-flex align-items-start mb-1">';
                        detalleHtml += '<i class="' + icono + ' me-2 mt-1"></i>';
                        detalleHtml += '<div><strong class="text-body">' + item.concepto + ':</strong> ';
                        detalleHtml += '<span class="fw-bold">$' + formatMXN(Math.abs(item.monto)) + '</span>';
                        if (item.detalle) {
                            detalleHtml += '<br><small class="text-muted">' + item.detalle + '</small>';
                        }
                        detalleHtml += '</div></div>';
                    });
                    detalleHtml += '</div>';
                }

                if (Math.abs(diferencia) < 0.01) {
                    $card.removeClass('border-danger border-warning border-info').addClass('border-success');
                    $cardBody.css('background-color', 'rgba(25,135,84,0.1)');
                    $monto.removeClass('text-danger text-warning text-info').addClass('text-success');
                    $icon.css('background-color', 'rgba(25,135,84,0.2)');
                    $texto.html('<i class="ti ti-check-circle"></i> <strong>Todo cuadra correctamente</strong>');
                    $detalle.html('').hide();
                } else if (diferencia < 0) {
                    $card.removeClass('border-success border-warning border-info').addClass('border-danger');
                    $cardBody.css('background-color', 'rgba(220,53,69,0.1)');
                    $monto.removeClass('text-success text-warning text-info').addClass('text-danger');
                    $icon.css('background-color', 'rgba(220,53,69,0.2)');
                    let msg = '<i class="ti ti-alert-triangle"></i> <strong>ALERTA:</strong> Falta dinero en el sistema';
                    if (resumen) msg += '<br><small class="text-muted">' + resumen + '</small>';
                    $texto.html(msg);
                    $detalle.html(detalleHtml).show();
                    cargarAlertasIntegridad($detalle);
                } else {
                    $card.removeClass('border-success border-danger border-info').addClass('border-warning');
                    $cardBody.css('background-color', 'rgba(255,193,7,0.1)');
                    $monto.removeClass('text-success text-danger text-info').addClass('text-warning');
                    $icon.css('background-color', 'rgba(255,193,7,0.2)');
                    let msg = '<i class="ti ti-info-circle"></i> <strong>ATENCIÓN:</strong> Sobra dinero (revisar préstamos o capital)';
                    if (resumen) msg += '<br><small class="text-muted">' + resumen + '</small>';
                    $texto.html(msg);
                    $detalle.html(detalleHtml).show();
                }

                const retirosPart = data.total_retiros ? ` + Retiros: $${formatMXN(data.total_retiros)}` : '';
                const desglose = ` | Saldos: $${formatMXN(data.total_saldos || 0)} − (Capital: $${formatMXN(data.total_capital || 0)}${retirosPart} + Obligaciones: $${formatMXN(data.total_prestamos || 0)})`;
                $texto.append(`<br><small class="text-muted">${desglose}</small>`);
            });

        }



        // Solo ante descuadre: trae api/integridad.php y anexa las pruebas FIFO fallidas
        // (test #5 inventario, test #10 consumos/saldos-vs-inventario) para apuntar la causa.
        // Se filtran solo esas dos para no ahogar la señal con deuda histórica de otros tests.
        function cargarAlertasIntegridad($detalle) {
            $.getJSON('api/integridad.php', function (res) {
                const malas = (res.tests || []).filter(function (t) { return (t.num === 5 || t.num === 10) && t.status === 'error'; });
                if (!malas.length) return;
                let html = '<div class="mt-2 p-2 rounded border border-danger" style="background-color:rgba(220,53,69,0.06);">';
                html += '<strong><i class="ti ti-shield-alert"></i> Posibles causas (integridad):</strong>';
                malas.forEach(function (t) {
                    html += '<div class="mt-1"><span class="badge bg-danger">Test #' + t.num + ' ' + t.nombre + '</span>';
                    html += '<div class="small">' + t.msg + '</div>';
                    (t.detalles || []).slice(0, 5).forEach(function (d) {
                        html += '<div class="small text-muted">• ' + (d.error || d.msg || '') + '</div>';
                    });
                    html += '</div>';
                });
                html += '</div>';
                $detalle.append(html);
            }).fail(function () { /* integridad no disponible: no bloquear */ });
        }



        function cargarResumenes() {

            cargarSaldos();

            cargarCapital();

            cargarObligaciones();

            cargarDiferencia();

        }



        cargarResumenes();



        // ─── Pago directo a Proveedor (acceso directo desde card obligaciones) ──

        window.pagarProveedorDirecto = function(id) {
            if (!id) return;

            $.getJSON('api/proveedores.php?id=' + id, function(proveedor) {
                var saldoMxn  = parseFloat(proveedor.saldo_mxn  || 0);
                var saldoUsd  = parseFloat(proveedor.saldo_usd  || 0);
                var saldoUsdt = parseFloat(proveedor.saldo_usdt || 0);

                var tieneMxn  = saldoMxn  > 0.001;
                var tieneUsd  = proveedor.acepta_usd  && saldoUsd  > 0.001;
                var tieneUsdt = proveedor.acepta_usdt && saldoUsdt > 0.001;

                // Si solo tiene un tipo de deuda, abrir directamente
                if (tieneMxn && !tieneUsd && !tieneUsdt) {
                    window.abrirModalPagoMxn(proveedor, saldoMxn);
                } else if (tieneUsd && !tieneMxn && !tieneUsdt) {
                    pagarProveedorDesdeModal(id, 'USD', saldoUsd);
                } else if (tieneUsdt && !tieneMxn && !tieneUsd) {
                    pagarProveedorDesdeModal(id, 'USDT', saldoUsdt);
                } else if (tieneMxn) {
                    // Si tiene MXN (con o sin otros), priorizar MXN
                    window.abrirModalPagoMxn(proveedor, saldoMxn);
                } else {
                    // Fallback: abrir modal de detalle
                    abrirProveedorModal(id);
                }
            });
        };

        // Modal unificado para MXN / USD / USDT (usa api/proveedores-pagos.php)
        window.abrirModalPagoProveedor = function(proveedor, moneda) {
            moneda = (moneda || 'MXN').toUpperCase();
            var saldoMxnDirecto = parseFloat(proveedor.saldo_mxn || 0);
            var saldoUsd = parseFloat(proveedor.saldo_usd || 0);
            var saldoUsdt = parseFloat(proveedor.saldo_usdt || 0);
            var valorMxnUsd = parseFloat(proveedor.valor_mxn_usd || 0);
            var valorMxnUsdt = parseFloat(proveedor.valor_mxn_usdt || 0);
            var saldo, valorMxn, paridad;
            if (moneda === 'USD') { saldo = saldoUsd; valorMxn = valorMxnUsd; }
            else if (moneda === 'USDT') { saldo = saldoUsdt; valorMxn = valorMxnUsdt; }
            else { moneda = 'MXN'; saldo = saldoMxnDirecto; valorMxn = saldoMxnDirecto; }
            if (saldo < 0.001) { showToast('Sin adeudo en ' + moneda, 'warning'); return; }
            paridad = (moneda === 'MXN') ? 1 : (saldo > 0 ? valorMxn / saldo : 0);

            var $modalProv = $('#modalProveedor');
            if ($modalProv.hasClass('show')) {
                $modalProv.modal('hide');
                $modalProv.one('hidden.bs.modal', function() {
                    doAbrirModalPagoProveedor(proveedor, moneda, saldo, valorMxn, paridad);
                });
                setTimeout(function(){
                    if (!$('#modalPagoProveedorMXN').hasClass('show')) doAbrirModalPagoProveedor(proveedor, moneda, saldo, valorMxn, paridad);
                }, 400);
                return;
            }
            doAbrirModalPagoProveedor(proveedor, moneda, saldo, valorMxn, paridad);
        };
        // Click directo desde card obligaciones: abre modal de pago sin pasar por historial
        window.abrirPagoProveedorDirecto = function(id, monedaSugerida) {
            if (!id) return;
            monedaSugerida = (monedaSugerida || '').toUpperCase();
            $.getJSON('api/proveedores.php?id=' + id, function(proveedor) {
                if (!proveedor || !proveedor.id) { showToast('Proveedor no encontrado','error'); return; }
                // Si la fila sugiere una moneda y hay adeudo, usarla; si no, auto-detectar
                var saldoMxn = parseFloat(proveedor.saldo_mxn || 0);
                var saldoUsd = parseFloat(proveedor.saldo_usd || 0);
                var saldoUsdt = parseFloat(proveedor.saldo_usdt || 0);
                var valorMxnUsd = parseFloat(proveedor.valor_mxn_usd || 0);
                var valorMxnUsdt = parseFloat(proveedor.valor_mxn_usdt || 0);
                var moneda = monedaSugerida;
                var tieneSugerida = false;
                if (moneda === 'USD' && saldoUsd > 0.001 && proveedor.acepta_usd) tieneSugerida = true;
                else if (moneda === 'USDT' && saldoUsdt > 0.001 && proveedor.acepta_usdt) tieneSugerida = true;
                else if (moneda === 'MXN' && saldoMxn > 0.001) tieneSugerida = true;
                if (!tieneSugerida) {
                    // Auto-detección: priorizar la moneda de la fila si corresponde, si no la primera con adeudo
                    if (moneda && ['MXN','USD','USDT'].includes(moneda)) {
                        // fila con moneda pero sin saldo (deuda ya saldada?) -> buscar cualquiera con saldo
                        if (saldoMxn > 0.001) moneda = 'MXN';
                        else if (saldoUsd > 0.001) moneda = 'USD';
                        else if (saldoUsdt > 0.001) moneda = 'USDT';
                        else moneda = 'MXN';
                    } else {
                        if (saldoMxn > 0.001) moneda = 'MXN';
                        else if (saldoUsd > 0.001) moneda = 'USD';
                        else if (saldoUsdt > 0.001) moneda = 'USDT';
                        else { showToast('Sin adeudo pendiente','warning'); return; }
                    }
                }
                window.abrirModalPagoProveedor(proveedor, moneda);
            }).fail(function(){ showToast('Error al cargar proveedor','error'); });
        };
        // Compatibilidad: wrapper MXN antiguo
        window.abrirModalPagoMxn = function(proveedor, saldoMxn) {
            // si saldoMxn no coincide con proveedor.saldo_mxn, forzar moneda MXN igualmente
            return window.abrirModalPagoProveedor(proveedor, 'MXN');
        };
        function doAbrirModalPagoProveedor(proveedor, moneda, saldo, valorMxn, paridad) {
            $('#pagoMxnProveedorId').val(proveedor.id);
            $('#pagoMxnMoneda').val(moneda);
            $('#pagoMxnParidad').val(paridad);
            $('#pagoMxnNombreProveedor').text(proveedor.nombre);
            // Badge / titulo dinamico
            var badgeCls = moneda === 'USD' ? 'bg-primary bg-opacity-10 text-primary' : moneda === 'USDT' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning';
            $('#pagoMxnBadgeMoneda').attr('class', 'badge border-0 ' + badgeCls).text(moneda);
            $('#pagoMxnModalTitle').html('<i class="ti ti-cash text-warning"></i> Pagar Proveedor ' + moneda);
            if (moneda === 'MXN') {
                $('#pagoMxnLabelAdeudo').text('Adeudo MXN');
                $('#pagoMxnInfoAdeudo').text('$' + saldo.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');
                $('#pagoMxnInfoAdeudoMxn').hide();
                $('#pagoMxnInfoTotal').text('$' + valorMxn.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');
            } else {
                $('#pagoMxnLabelAdeudo').text('Adeudo ' + moneda);
                $('#pagoMxnInfoAdeudo').text(formatNativo(saldo) + ' ' + moneda);
                $('#pagoMxnInfoAdeudoMxn').text('$' + valorMxn.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN').show();
                $('#pagoMxnInfoTotal').text('$' + valorMxn.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');
            }
            // Como en proveedores.php: siempre se paga en MXN
            $('#pagoMxnLabelMonto').text('Monto a Pagar (MXN) *');
            $('#pagoMxnSimboloIzq').text('$'); $('#pagoMxnSimboloDer').text('MXN');
            $('#formPagoProveedorMXN').data('adeudo-mxn', valorMxn).data('adeudo-divisa', saldo).data('moneda', moneda).data('paridad', paridad);

            // Fecha de hoy
            $('#pagoMxnFecha').val(new Date().toISOString().split('T')[0]);

            // Reset
            $('#pagoMxnMonto').val('');
            $('#pagoMxnMontoLabel').text('');
            $('#pagoMxnEquivalencia').hide().text('');
            $('#pagoMxnGrupoMonto').hide();
            $('#btnMxnLiquidarTotal').hide();
            $('#pagoMxnAlertaSaldo').hide();
            $('#btnMxnProcesarPago').hide();
            $('#pagoMxnInfoSaldo').text('');

            // Cargar cuentas MXN de pago
            $.get('api/cuentas.php?tipo_moneda=MXN', function(cuentas) {
                var sel = $('#pagoMxnCuentaSelect');
                sel.html('<option value="">Seleccione cuenta</option>');
                var filtradas = cuentas.filter(function(c) {
                    return [1, 2].includes(parseInt(c.tipo_cuenta)) && parseFloat(c.saldo_actual) > 0;
                });
                if (filtradas.length === 0) {
                    sel.append('<option value="" disabled>No hay cuentas MXN disponibles</option>');
                } else {
                    filtradas.forEach(function(c) {
                        var saldo = parseFloat(c.saldo_actual || 0).toFixed(2);
                        var nombreCuenta = c.nombre;
                        if (c.banco && c.banco.trim()) nombreCuenta += ' (' + c.banco + ')';
                        sel.append('<option value="' + c.id + '" data-saldo="' + saldo + '">' + nombreCuenta + ' — Saldo: $' + parseFloat(saldo).toLocaleString('es-MX',{minimumFractionDigits:2}) + ' MXN</option>');
                    });
                }
                $('#modalPagoProveedorMXN').modal('show');
            });
        }

        // Cambio de cuenta en modal pago proveedor (unificado - pago siempre en MXN como proveedores.php)
        $(document).on('change', '#pagoMxnCuentaSelect', function() {
            var saldoCuenta = parseFloat($(this).find('option:selected').attr('data-saldo')) || 0;
            var adeudoMxn   = parseFloat($('#formPagoProveedorMXN').data('adeudo-mxn')) || 0;

            if (!$(this).val()) {
                $('#pagoMxnGrupoMonto').hide();
                $('#btnMxnProcesarPago').hide();
                return;
            }

            $('#pagoMxnGrupoMonto').show();
            $('#btnMxnProcesarPago').show();
            $('#pagoMxnMonto').val('').attr('max', Math.min(saldoCuenta, adeudoMxn).toFixed(2));
            $('#pagoMxnInfoSaldo').text('Saldo disponible: $' + saldoCuenta.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' MXN');
            $('#pagoMxnAlertaSaldo').hide();

            if (saldoCuenta >= adeudoMxn) {
                $('#btnMxnLiquidarTotal').show();
            } else {
                $('#btnMxnLiquidarTotal').hide();
            }
        });

        // Liquidar total (unificado - MXN)
        $(document).on('click', '#btnMxnLiquidarTotal', function() {
            var adeudoMxn = parseFloat($('#formPagoProveedorMXN').data('adeudo-mxn')) || 0;
            var saldoCuenta = parseFloat($('#pagoMxnCuentaSelect').find('option:selected').attr('data-saldo')) || 0;
            var maximo = Math.min(adeudoMxn, saldoCuenta);
            $('#pagoMxnMonto').val(maximo.toFixed(2)).trigger('input');
        });

        // Validación en tiempo real del monto (siempre MXN como en proveedores.php:1949)
        $(document).on('input', '#pagoMxnMonto', function() {
            var val         = parseFloat($(this).val()) || 0;
            var adeudoMxn   = parseFloat($('#formPagoProveedorMXN').data('adeudo-mxn')) || 0;
            var adeudoDivisa= parseFloat($('#formPagoProveedorMXN').data('adeudo-divisa')) || adeudoMxn;
            var moneda      = $('#formPagoProveedorMXN').data('moneda') || 'MXN';
            var paridad     = parseFloat($('#formPagoProveedorMXN').data('paridad')) || 1;
            var saldoCuenta = parseFloat($('#pagoMxnCuentaSelect').find('option:selected').attr('data-saldo')) || 0;
            var maximoMxn = Math.min(saldoCuenta, adeudoMxn);

            if (val > 0) {
                $('#pagoMxnMontoLabel').text('$' + val.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' MXN');
                if (moneda !== 'MXN' && paridad > 0) {
                    var equivDivisa = val / paridad;
                    $('#pagoMxnEquivalencia').text('≈ ' + formatNativo(equivDivisa) + ' ' + moneda + ' (TC ' + formatMXN(paridad) + ')').show();
                } else {
                    $('#pagoMxnEquivalencia').hide();
                }
            } else {
                $('#pagoMxnMontoLabel').text('');
                $('#pagoMxnEquivalencia').hide();
            }

            var excede = val > maximoMxn + 0.001;

            if (excede) {
                $('#pagoMxnAlertaSaldo').show();
                $('#btnMxnProcesarPago').prop('disabled', true);
            } else {
                $('#pagoMxnAlertaSaldo').hide();
                $('#btnMxnProcesarPago').prop('disabled', false);
            }
        });

        // Procesar pago proveedor (unificado MXN/USD/USDT)
        $(document).on('submit', '#formPagoProveedorMXN', function(e) {
            e.preventDefault();

            var montoInput = parseFloat($('#pagoMxnMonto').val()) || 0;
            var moneda = $('#formPagoProveedorMXN').data('moneda') || $('#pagoMxnMoneda').val() || 'MXN';
            var paridad = parseFloat($('#formPagoProveedorMXN').data('paridad') || $('#pagoMxnParidad').val() || 1);
            if (!$('#pagoMxnCuentaSelect').val()) {
                showToast('Selecciona una cuenta de pago', 'error');
                return;
            }
            if (montoInput <= 0) {
                showToast('Ingresa un monto a pagar', 'error');
                return;
            }
            // Siempre en MXN como en proveedores.php (el input ya es MXN)
            var monto = montoInput;
            if (paridad <= 0) {
                showToast('Paridad inválida', 'error');
                return;
            }

            var fechaElegida = $('#pagoMxnFecha').val();
            var horaActual = new Date().toTimeString().split(' ')[0];

            var payload = {
                proveedor_id:   $('#pagoMxnProveedorId').val(),
                cuenta_pago_id: $('#pagoMxnCuentaSelect').val(),
                monto:          monto,
                paridad:        paridad,
                moneda:         moneda,
                fecha:          fechaElegida + ' ' + horaActual
            };

            var $btn = $('#btnMxnProcesarPago').prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');

            $.ajax({
                url: 'api/proveedores-pagos.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function() {
                    $('#modalPagoProveedorMXN').modal('hide');
                    showToast('Pago registrado', 'success');
                    setTimeout(function() { location.reload(); }, 500);
                },
                error: function(xhr) {
                    var msg = 'Error al registrar pago';
                    try { msg = JSON.parse(xhr.responseText).error || msg; } catch (e) {}
                    showToast(msg, 'error');
                    $btn.prop('disabled', false).html('<i class="ti ti-cash"></i> Procesar Pago');
                }
            });
        });

        // Modal detalle proveedor (mantenido como fallback)
        window.abrirProveedorModal = function(id) {
            if (!id) return;
            var $modal = $('#modalProveedor');
            $modal.find('.modal-body').html('<div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>');
            $modal.find('#proveedorModalNombre').text('Proveedor');
            $modal.modal('show');

            var proveedor = null;
            var historial = [];

            $.getJSON('api/proveedores.php?id=' + id, function (data) {
                proveedor = data;
                window.proveedor = data; window._proveedorActual = data;
                $modal.find('#proveedorModalNombre').text(proveedor.nombre || 'Proveedor');
                cargarHistorial();
            }).fail(function() {
                $modal.find('.modal-body').html('<div class="alert alert-danger">Error al cargar datos del proveedor</div>');
            });

            function cargarHistorial() {
                $.getJSON('api/movimientos-todos.php?proveedor_id=' + id, function (data) {
                    historial = data || [];
                    renderizarProveedor();
                }).fail(function() {
                    historial = [];
                    renderizarProveedor();
                });
            }

            function renderizarProveedor() {
                if (!proveedor) return;

                var saldoUsd = parseFloat(proveedor.saldo_usd || 0);
                var saldoUsdt = parseFloat(proveedor.saldo_usdt || 0);
                var saldoMxn = parseFloat(proveedor.saldo_mxn || 0);
                var valorMxnUsd = parseFloat(proveedor.valor_mxn_usd || 0);
                var valorMxnUsdt = parseFloat(proveedor.valor_mxn_usdt || 0);
                var totalMxn = valorMxnUsd + valorMxnUsdt + saldoMxn;

                var html = '';

                html += '<div class="row mb-4">';

                if (saldoMxn > 0.001) {
                    html += '<div class="col-md-3"><div class="card border-warning"><div class="card-body text-center">';
                    html += '<p class="text-muted mb-1" style="font-size:0.75rem;">ADEUDO MXN</p>';
                    html += '<h4 class="mb-0 text-warning fw-bold">$' + formatMXN(saldoMxn) + '</h4>';
                    html += '</div></div></div>';
                }

                if (saldoUsd > 0.001) {
                    html += '<div class="col-md-3"><div class="card border-primary"><div class="card-body text-center">';
                    html += '<p class="text-muted mb-1" style="font-size:0.75rem;">ADEUDO USD</p>';
                    html += '<h4 class="mb-0 text-primary fw-bold">' + formatNativo(saldoUsd) + ' <small>USD</small></h4>';
                    html += '<small class="text-muted">$' + formatMXN(valorMxnUsd) + ' MXN</small>';
                    html += '</div></div></div>';
                }

                if (saldoUsdt > 0.001) {
                    html += '<div class="col-md-3"><div class="card border-info"><div class="card-body text-center">';
                    html += '<p class="text-muted mb-1" style="font-size:0.75rem;">ADEUDO USDT</p>';
                    html += '<h4 class="mb-0 text-info fw-bold">' + formatNativo(saldoUsdt) + ' <small>USDT</small></h4>';
                    html += '<small class="text-muted">$' + formatMXN(valorMxnUsdt) + ' MXN</small>';
                    html += '</div></div></div>';
                }

                html += '<div class="col-md-3"><div class="card border-secondary"><div class="card-body text-center">';
                html += '<p class="text-muted mb-1" style="font-size:0.75rem;">TOTAL ADEUDO</p>';
                html += '<h4 class="mb-0 text-secondary fw-bold">$' + formatMXN(totalMxn) + ' MXN</h4>';
                html += '</div></div></div>';

                html += '</div>';

                html += '<div class="d-flex justify-content-between align-items-center mb-3">';
                html += '<h6 class="mb-0"><i class="ti ti-list-details"></i> Historial de Movimientos</h6>';
                html += '<div class="d-flex gap-2">';

                if (saldoMxn > 0.001) {
                    html += '<button class="btn btn-warning btn-sm" onclick="window.abrirModalPagoProveedor(window.proveedor, \'MXN\');">';
                    html += '<i class="ti ti-cash"></i> Pagar MXN</button>';
                }
                if (proveedor.acepta_usd && saldoUsd > 0.001) {
                    html += '<button class="btn btn-primary btn-sm" onclick="window.abrirModalPagoProveedor(window.proveedor, \'USD\');">';
                    html += '<i class="ti ti-cash"></i> Pagar en USD</button>';
                }
                if (proveedor.acepta_usdt && saldoUsdt > 0.001) {
                    html += '<button class="btn btn-info btn-sm" onclick="window.abrirModalPagoProveedor(window.proveedor, \'USDT\');">';
                    html += '<i class="ti ti-cash"></i> Pagar en USDT</button>';
                }

                html += '</div></div>';

                if (historial.length > 0) {
                    html += '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Fecha</th><th>Servicio</th><th>Tipo</th><th>Monto</th><th>Moneda</th><th>Descripción</th></tr></thead><tbody>';
                    historial.forEach(function (m) {
                        var tipoBadge = m.tipo === 'entrada' ? 'success' : (m.tipo === 'salida' ? 'danger' : 'secondary');
                        var partes = (m.fecha || '').split('-');
                        var fechaFmt = partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : m.fecha;
                        html += '<tr>';
                        html += '<td>' + fechaFmt + '</td>';
                        html += '<td>' + (m.servicio_nombre || '-') + '</td>';
                        html += '<td><span class="badge bg-' + tipoBadge + ' bg-opacity-10 text-' + tipoBadge + ' border-0">' + (m.tipo || '-') + '</span></td>';
                        html += '<td class="fw-bold">$' + formatMXN(m.monto) + '</td>';
                        html += '<td>' + (m.moneda || 'MXN') + '</td>';
                        html += '<td><small class="text-muted">' + (m.descripcion || '-') + '</small></td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="text-center text-muted py-3"><i class="ti ti-inbox" style="font-size:2rem;"></i><p class="mt-2">Sin movimientos registrados</p></div>';
                }

                $modal.find('.modal-body').html(html);
            }
        }



        // Legacy: ahora delega al modal unificado de pago (no abre Nuevo Movimiento)
        window.pagarProveedorDesdeModal = function(proveedorId, moneda, saldoPendiente) {
            moneda = (moneda || 'USD').toUpperCase();
            // Intentar usar objeto proveedor ya cargado en el modal si coincide
            try {
                // Si proveedor global existe en el closure, usarlo
                $.getJSON('api/proveedores.php?id=' + proveedorId, function(p) {
                    window.abrirModalPagoProveedor(p, moneda);
                }).fail(function(){
                    // Fallback antiguo: abrir Nuevo Movimiento solo si falla el nuevo flujo
                    $('#modalProveedor').modal('hide');
                    var servicioNombre = moneda === 'USD' ? 'Compra de Dólares' : 'Compra de USDT';
                    $('#servicioSelect').val(servicioNombre).trigger('change');
                    setTimeout(function () {
                        $('#proveedorSelect').val(proveedorId).trigger('change');
                        $('#modalFlujo').modal('show');
                    }, 300);
                });
            } catch(e) {
                $.getJSON('api/proveedores.php?id=' + proveedorId, function(p) { window.abrirModalPagoProveedor(p, moneda); });
            }
        }



        // ─── Cobro Deudor Directo (igual que Proveedores: pago en MXN sin historial) ─
        window.abrirCobroDeudorDirecto = function(nombreEncoded) {
            var nombre = decodeURIComponent(nombreEncoded);
            if (!nombre) return;
            var $modal = $('#modalCobroDeudor');
            $modal.find('#cobroDeudorNombreDisplay').text(nombre);
            $modal.find('#cobroDeudorInfoPendiente').html('<i class="ti ti-loader ti-spin"></i> Cargando...');
            $modal.modal('show');

            $.getJSON('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(nombre), function(prestamos) {
                prestamos = prestamos || [];
                var totalCapital = 0, totalConInteres = 0, totalInteres = 0, totalPagado = 0;
                prestamos.forEach(function(p){
                    totalCapital += parseFloat(p.monto_total || 0);
                    totalConInteres += parseFloat(p.monto_total_con_interes || p.monto_total || 0);
                    totalInteres += parseFloat(p.monto_interes || 0);
                    totalPagado += parseFloat(p.monto_pagado || 0);
                });
                var totalPendiente = totalConInteres - totalPagado;
                if (totalPendiente < 0.01) { showToast('Sin pendiente por cobrar','warning'); $modal.modal('hide'); return; }

                $('#cobroDeudorNombre').val(nombre);
                $('#cobroDeudorNombreDisplay').text(nombre);
                $('#cobroDeudorInfoPendiente').text('$' + formatMXN(totalPendiente) + ' MXN');
                $('#cobroDeudorInfoCobrado').text('cobrado $' + formatMXN(totalPagado));
                $('#cobroDeudorInfoTotal').text('$' + formatMXN(totalConInteres) + ' MXN');
                if (totalInteres > 0.01) $('#cobroDeudorInfoInteres').text('Int $' + formatMXN(totalInteres)).show(); else $('#cobroDeudorInfoInteres').hide();
                $modal.data('prestamos', prestamos).data('nombre', nombre);
                $('#cobroDeudorFecha').val(new Date().toISOString().slice(0,10));
                $('#cobroDeudorMonto').val(''); $('#cobroDeudorMontoLabel').text(''); $('#cobroDeudorGrupoMonto').hide(); $('#btnCobroLiquidar').hide(); $('#cobroDeudorAlerta').hide(); $('#btnCobroProcesar').hide(); $('#cobroDeudorInfoSaldo').text('');
                $.getJSON('api/cuentas.php?tipo_moneda=MXN&es_caja_empresa=1', function(cuentas){
                    var opts = '<option value="">-- Selecciona --</option>';
                    cuentas.forEach(function(c){ opts += '<option value="' + c.id + '">' + c.nombre + ' ($' + formatMXN(c.saldo_actual) + ')</option>'; });
                    $('#cobroDeudorCuenta').html(opts);
                    $('#cobroDeudorGrupoMonto').show(); // mostrar ya para que el change lo active
                });
            }).fail(function(){ showToast('Error al cargar deudor','error'); $modal.modal('hide'); });
        };
        $(document).on('change', '#cobroDeudorCuenta', function(){
            if (!$(this).val()) { $('#cobroDeudorGrupoMonto').hide(); $('#btnCobroProcesar').hide(); return; }
            $('#cobroDeudorGrupoMonto').show(); $('#btnCobroProcesar').show();
            var pendiente = parseFloat(($('#cobroDeudorInfoPendiente').text().replace(/[^0-9.-]/g,'') || 0));
            // recalcular pendiente real desde data
            var prestamos = $('#modalCobroDeudor').data('prestamos') || [];
            var totalPend = 0; prestamos.forEach(function(p){ totalPend += parseFloat(p.monto_total_con_interes || p.monto_total || 0) - parseFloat(p.monto_pagado || 0); });
            $('#cobroDeudorMonto').val('').attr('max', totalPend.toFixed(2));
            if (totalPend > 0.01) $('#btnCobroLiquidar').show(); else $('#btnCobroLiquidar').hide();
        });
        $(document).on('click', '#btnCobroLiquidar', function(){
            var prestamos = $('#modalCobroDeudor').data('prestamos') || [];
            var totalPend = 0; prestamos.forEach(function(p){ totalPend += parseFloat(p.monto_total_con_interes || p.monto_total || 0) - parseFloat(p.monto_pagado || 0); });
            $('#cobroDeudorMonto').val(totalPend.toFixed(2)).trigger('input');
        });
        $(document).on('input', '#cobroDeudorMonto', function(){
            var val = parseFloat($(this).val()) || 0;
            var prestamos = $('#modalCobroDeudor').data('prestamos') || [];
            var totalPend = 0; prestamos.forEach(function(p){ totalPend += parseFloat(p.monto_total_con_interes || p.monto_total || 0) - parseFloat(p.monto_pagado || 0); });
            if (val > 0) $('#cobroDeudorMontoLabel').text('$' + formatMXN(val) + ' MXN'); else $('#cobroDeudorMontoLabel').text('');
            if (val > totalPend + 0.001) { $('#cobroDeudorAlerta').show(); $('#btnCobroProcesar').prop('disabled', true); } else { $('#cobroDeudorAlerta').hide(); $('#btnCobroProcesar').prop('disabled', false); }
        });
        $(document).on('submit', '#formCobroDeudorDirecto', function(e){
            e.preventDefault();
            var nombre = $('#modalCobroDeudor').data('nombre');
            var prestamos = $('#modalCobroDeudor').data('prestamos') || [];
            guardarCobroDeudorModal(nombre, prestamos, true);
        });

        // ─── Modal Deudor (historial completo) ──────────────────────────────────

        window.abrirDeudorModal = function(nombreEncoded) {
            var nombre = decodeURIComponent(nombreEncoded);
            if (!nombre) return;
            var $modal = $('#modalDeudor');
            $modal.find('.modal-body').html('<div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Cargando...</div>');
            $modal.find('#deudorModalNombre').text(nombre);
            $modal.modal('show');

            var prestamos = [];
            var historial = [];

            $.getJSON('api/prestamos-otorgados.php?persona_nombre=' + encodeURIComponent(nombre), function (data) {
                prestamos = data || [];
                cargarHistorialDeudor();
            }).fail(function() {
                prestamos = [];
                cargarHistorialDeudor();
            });

            function cargarHistorialDeudor() {
                $.getJSON('api/deudor-historial.php?persona_nombre=' + encodeURIComponent(nombre), function (data) {
                    historial = data || [];
                    renderizarDeudor();
                }).fail(function() {
                    historial = [];
                    renderizarDeudor();
                });
            }

            function renderizarDeudor() {
                var totalCapital = 0, totalConInteres = 0, totalInteres = 0, totalPagado = 0;
                prestamos.forEach(function (p) {
                    totalCapital += parseFloat(p.monto_total || 0);
                    totalConInteres += parseFloat(p.monto_total_con_interes || p.monto_total || 0);
                    totalInteres += parseFloat(p.monto_interes || 0);
                    totalPagado += parseFloat(p.monto_pagado || 0);
                });
                var totalPendiente = totalConInteres - totalPagado;

                var html = '';

                // Cards de resumen con interés
                html += '<div class="row mb-4">';
                html += '<div class="col-md-3"><div class="card border-warning"><div class="card-body text-center">';
                html += '<p class="text-muted mb-1" style="font-size:0.70rem;">CAPITAL PRESTADO</p>';
                html += '<h5 class="mb-0 text-warning fw-bold">$' + formatMXN(totalCapital) + '</h5>';
                html += '</div></div></div>';
                html += '<div class="col-md-3"><div class="card border-info"><div class="card-body text-center">';
                html += '<p class="text-muted mb-1" style="font-size:0.70rem;">INTERÉS</p>';
                html += '<h5 class="mb-0 text-info fw-bold">$' + formatMXN(totalInteres) + '</h5>';
                html += '</div></div></div>';
                html += '<div class="col-md-3"><div class="card border-primary"><div class="card-body text-center">';
                html += '<p class="text-muted mb-1" style="font-size:0.70rem;">TOTAL A COBRAR</p>';
                html += '<h5 class="mb-0 text-primary fw-bold">$' + formatMXN(totalConInteres) + '</h5>';
                html += '</div></div></div>';
                html += '<div class="col-md-3"><div class="card border-danger"><div class="card-body text-center">';
                html += '<p class="text-muted mb-1" style="font-size:0.70rem;">PENDIENTE</p>';
                html += '<h5 class="mb-0 text-danger fw-bold">$' + formatMXN(totalPendiente) + '</h5>';
                html += '<small class="text-muted">cobrado $' + formatMXN(totalPagado) + '</small>';
                html += '</div></div></div>';
                html += '</div>';

                // Tabla de prestamos con desglose
                html += '<h6 class="mb-2"><i class="ti ti-list-details"></i> Prestamos</h6>';
                if (prestamos.length > 0) {
                    html += '<div class="table-responsive mb-4"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Fecha</th><th>Capital</th><th>Interés</th><th>Total</th><th>Pagado</th><th>Pendiente</th><th>Tasa/Plazo</th><th>Estado</th><th>Cuenta</th></tr></thead><tbody>';
                    prestamos.forEach(function (p) {
                        var total = parseFloat(p.monto_total_con_interes || p.monto_total || 0);
                        var pendiente = total - parseFloat(p.monto_pagado || 0);
                        var badge = p.estado === 'pagado' ? 'success' : (p.estado === 'parcial' ? 'warning' : 'danger');
                        var partes = (p.fecha_prestamo || '').split('-');
                        var fechaFmt = partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : p.fecha_prestamo;
                        var tasaTxt = (p.tasa_porcentaje && parseFloat(p.tasa_porcentaje)>0) ? parseFloat(p.tasa_porcentaje).toFixed(2)+'% '+ (p.tasa_periodo||'') : '-';
                        var plazoTxt = p.plazo_valor ? p.plazo_valor+' '+ (p.plazo_unidad||'') : '-';
                        var interes = parseFloat(p.monto_interes||0);
                        html += '<tr>';
                        html += '<td>' + fechaFmt + '</td>';
                        html += '<td>$' + formatMXN(p.monto_total) + '</td>';
                        html += '<td class="text-info">$' + formatMXN(interes) + '</td>';
                        html += '<td class="fw-bold">$' + formatMXN(total) + '</td>';
                        html += '<td class="text-success">$' + formatMXN(p.monto_pagado) + '</td>';
                        html += '<td class="text-danger fw-bold">$' + formatMXN(pendiente) + '</td>';
                        html += '<td><small>' + tasaTxt + '<br>' + plazoTxt + '</small></td>';
                        html += '<td><span class="badge bg-' + badge + ' bg-opacity-10 text-' + badge + ' border-0">' + (p.estado || '-') + '</span></td>';
                        html += '<td>' + (p.cuenta_nombre || 'N/A') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="text-center text-muted py-3"><i class="ti ti-inbox" style="font-size:2rem;"></i><p class="mt-2">Sin prestamos registrados</p></div>';
                }

                // Formulario de cobro (solo si hay pendiente)
                if (totalPendiente > 0.01) {
                    html += '<div class="card border-success mb-3"><div class="card-header bg-success bg-opacity-10">';
                    html += '<h6 class="mb-0 text-success"><i class="ti ti-cash"></i> Registrar Cobro</h6></div>';
                    html += '<div class="card-body">';
                    html += '<form id="formCobroDeudorModal">';
                    html += '<div class="row align-items-end">';
                    html += '<div class="col-md-3 mb-2"><label class="form-label fw-semibold">Fecha</label>';
                    html += '<input type="date" class="form-control" id="fechaCobroDeudor" value="' + new Date().toISOString().slice(0,10) + '" required></div>';
                    html += '<div class="col-md-3 mb-2"><label class="form-label fw-semibold">Monto a Cobrar</label>';
                    html += '<div class="input-group"><span class="input-group-text">$</span>';
                    html += '<input type="number" step="0.01" min="0.01" class="form-control" id="montoCobroDeudor" max="' + totalPendiente.toFixed(2) + '" required></div>';
                    html += '<small class="text-muted">Pendiente: $' + formatMXN(totalPendiente) + '</small></div>';
                    html += '<div class="col-md-4 mb-2"><label class="form-label fw-semibold">Cuenta Destino</label>';
                    html += '<select class="form-select" id="cuentaDestinoDeudor" required>';
                    html += '<option value="">-- Selecciona --</option></select></div>';
                    html += '<div class="col-md-2 mb-2"><button type="submit" class="btn btn-success w-100">';
                    html += '<i class="ti ti-check"></i> Cobrar</button></div>';
                    html += '</div></form>';
                    html += '<div id="resumenCobroDeudor" class="mt-2"></div>';
                    html += '</div></div>';
                }

                // Historial de movimientos
                html += '<h6 class="mb-2"><i class="ti ti-history"></i> Historial de Movimientos</h6>';
                if (historial.length > 0) {
                    html += '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead><tr><th>Fecha</th><th>Servicio</th><th>Tipo</th><th>Monto</th><th>Cuenta Origen</th><th>Cuenta Destino</th></tr></thead><tbody>';
                    historial.forEach(function (m) {
                        var tipoBadge = m.tipo === 'entrada' ? 'success' : 'danger';
                        var partes = (m.fecha || '').split('-');
                        var fechaFmt = partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : m.fecha;
                        html += '<tr>';
                        html += '<td>' + fechaFmt + '</td>';
                        html += '<td>' + (m.servicio_nombre || '-') + '</td>';
                        html += '<td><span class="badge bg-' + tipoBadge + ' bg-opacity-10 text-' + tipoBadge + ' border-0">' + (m.tipo || '-') + '</span></td>';
                        html += '<td class="fw-bold">$' + formatMXN(m.monto) + '</td>';
                        html += '<td>' + (m.cuenta_origen_nombre || '-') + '</td>';
                        html += '<td>' + (m.cuenta_destino_nombre || '-') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class="text-center text-muted py-3"><small>Sin movimientos registrados</small></div>';
                }

                $modal.find('.modal-body').html(html);

                // Cargar cuentas destino
                $.getJSON('api/cuentas.php?tipo_moneda=MXN&es_caja_empresa=1', function (cuentas) {
                    var opts = '<option value="">-- Selecciona --</option>';
                    cuentas.forEach(function (c) {
                        opts += '<option value="' + c.id + '">' + c.nombre + ' ($' + formatMXN(c.saldo_actual) + ')</option>';
                    });
                    $('#cuentaDestinoDeudor').html(opts);
                });

                // Evento submit del formulario de cobro
                $('#formCobroDeudorModal').on('submit', function (e) {
                    e.preventDefault();
                    guardarCobroDeudorModal(nombre, prestamos);
                });
            }
        }



        // ─── Guardar cobro de deudor desde modal ────────────────────────────

        function guardarCobroDeudorModal(personaNombre, prestamos, esDirecto) {
            var isDirect = !!esDirecto || $('#modalCobroDeudor').hasClass('show');
            var monto = parseFloat(isDirect ? $('#cobroDeudorMonto').val() : $('#montoCobroDeudor').val());
            var cuentaId = isDirect ? $('#cobroDeudorCuenta').val() : $('#cuentaDestinoDeudor').val();
            var fecha = isDirect ? $('#cobroDeudorFecha').val() : $('#fechaCobroDeudor').val();

            if (!monto || monto <= 0) { showToast('Ingresa un monto válido', 'error'); return; }
            if (!cuentaId) { showToast('Selecciona una cuenta destino', 'error'); return; }

            // Distribucion en cascada: pagar el prestamo mas antiguo primero — sobre total con interés
            var pagos = [];
            var restante = monto;
            prestamos.forEach(function (p) {
                if (restante <= 0.01) return;
                var total = parseFloat(p.monto_total_con_interes || p.monto_total || 0);
                var pendiente = total - parseFloat(p.monto_pagado || 0);
                if (pendiente > 0.01 && (p.deudor_nombre === personaNombre || p.persona_nombre === personaNombre)) {
                    var abono = Math.min(pendiente, restante);
                    if (abono > 0.01) {
                        pagos.push({ id: p.id, abono: parseFloat(abono.toFixed(2)) });
                        restante -= abono;
                    }
                }
            });

            if (pagos.length === 0) {
                showToast('No hay prestamos pendientes para esta persona', 'error');
                return;
            }

            $.ajax({
                url: 'api/prestamos-otorgados.php',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    pagos: pagos,
                    cuenta_pago_id: parseInt(cuentaId),
                    fecha_pago: fecha
                }),
                success: function (res) {
                    if (res.success) {
                        showToast('Cobro registrado correctamente', 'success');
                        $('#modalDeudor').modal('hide');
                        $('#modalCobroDeudor').modal('hide');
                        cargarObligaciones();
                        cargarDiferencia();
                    } else {
                        showToast(res.error || 'Error al guardar', 'error');
                    }
                },
                error: function () {
                    showToast('Error al procesar el cobro', 'error');
                }
            });
        }



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



        function cargarProveedoresMXN(soloProveedores = false) {

            let html = soloProveedores
                ? '<option value="">-- Selecciona proveedor --</option>'
                : '<option value="">-- Sin proveedor/cliente --</option>';

            $.getJSON('api/proveedores.php?activos=1', function (data) {

                data.forEach(p => html += `<option value="${p.id}" data-tipo="proveedor">${p.nombre}</option>`);

                if (!soloProveedores) {

                    $.getJSON('api/clientes.php', function (clients) {

                        clients.forEach(c => html += `<option value="${c.id}" data-tipo="cliente">${c.nombre}</option>`);

                        $('#proveedorSelect').html(html);

                    });

                } else {

                    $('#proveedorSelect').html(html);

                }

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
            $.getJSON('api/cuentas.php?tipo_moneda=MXN&es_caja_empresa=1', function (data) {
                let html = '<option value="">-- Selecciona la cuenta --</option>';
                data.forEach(c => {
                    let nombreCuenta = c.nombre;
                    if (c.banco && c.banco.trim()) {
                        nombreCuenta += ' (' + c.banco + ')';
                    }
                    // AQUÍ ESTÁ LA MAGIA: data-saldo="${c.saldo_actual}"
                    html += `<option value="${c.id}" data-saldo="${c.saldo_actual}">${nombreCuenta} ($${formatMXN(c.saldo_actual)} MXN)</option>`;
                });
                $('#cuentaPagoContadoSelect').html(html);
            });
        }



        function cargarCuentasMXN(soloBancarias) {

            let url = soloBancarias
                ? 'api/cuentas.php?solo_gastos=1&tipo_moneda=MXN'
                : 'api/cuentas.php?tipo_moneda=MXN';

            $.getJSON(url, function (data) {

                let html = '<option value="">-- Selecciona una cuenta --</option>';

                let lista = soloBancarias
                    ? (data || []).filter(function (c) {
                        let t = parseInt(c.tipo_cuenta);
                        return t === 1 || t === 2;
                    })
                    : (data || []).filter(function (c) {
                        return parseInt(c.tipo_cuenta) !== 3 && parseInt(c.tipo_cuenta) !== 7;
                    });

                if (!lista.length) {
                    html += '<option value="" disabled>No hay cuentas MXN disponibles</option>';
                }

                lista.forEach(function (c) {

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        html += `<option value="${c.id}">${nombreCuenta} (MXN ${formatMXN(c.saldo_actual)})</option>`;

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

                            <input type="text" step="0.01" class="form-control input-cantidad" required>

                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Total a Pagar (MXN) *
                                <span class="label-formato-total text-success fw-bold ms-2"></span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" step="0.01" class="form-control input-total-mxn-editable fw-bold" required>
                                <span class="input-group-text">MXN</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted label-formato-tc"></small>
                            <label class="form-label">Tipo de Cambio <span class="text-muted">(calculado o manual)</span></label>
                            <input type="text" step="0.0001" class="form-control input-tc" required>
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
                            <div id="warningFondoInsuficiente" class="alert alert-warning py-1 px-2 mt-1 mb-0" style="display:none;font-size:0.875rem;"></div>
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

                            <input type="text" step="0.01" class="form-control input-cantidad" required>

                            <small id="disponibleDivisasInfo" class="text-muted"><i class="ti ti-loader ti-spin"></i> Consultando inventario...</small>

                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                Total MXN a recibir *
                                <span class="label-formato-total text-success fw-bold ms-2"></span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" step="0.01" class="form-control input-total-mxn-editable fw-bold text-success" required>
                                <span class="input-group-text">MXN</span>
                            </div>
                            <small class="text-success"><i class="ti ti-arrow-right"></i> Este monto se sumará a la cuenta seleccionada</small>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted label-formato-tc"></small>
                            <label class="form-label">Tipo de Cambio <span class="text-muted">(calculado o manual)</span></label>
                            <input type="text" step="0.0001" class="form-control input-tc" required>
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

                        $('#cuentaLabel').text('Cuenta bancaria destino (MXN)');

                        $campos.html(`

                            <div class="mb-3">

                                <label class="form-label">

                                    Monto

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <input type="text" step="0.01" class="form-control input-monto" id="inputMontoVenta" required>

                            </div>

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">% Amiri</label>

                                        <input type="text" step="0.01" class="form-control input-porc-amiri" value="0" required>

                                    </div>

                                </div>

                                <div class="col-md-6" id="campoPromotorPorcentaje">

                                    <div class="mb-3">

                                        <label class="form-label">% Promotor</label>

                                        <input type="text" step="0.01" class="form-control input-porc-promotor" value="0" required>

                                    </div>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Retención Total (%)</label>

                                <div class="input-group">

                                    <input type="text" step="0.01" class="form-control input-retencion-total" value="0" disabled readonly>

                                    <span class="input-group-text bg-info text-white"><i class="ti ti-percentage"></i></span>

                                </div>

                                <small class="text-muted">Suma automática de % Amiri + % Promotor</small>

                            </div>

                        `);

                        $('#totalesGroup').show();

                        break;



                    case 'Compra de Efectivo':

                        $('#proveedorGroup').show();

                        $('#cuentaGroup').show();

                        cargarProveedoresMXN(true);

                        cargarCuentasMXN(true);

                        $('#cuentaLabel').text('Cuenta bancaria de cobro (MXN)');

                        $campos.html(`

                            <div class="mb-3">

                                <label class="form-label">

                                    Monto de la operaci&oacute;n

                                    <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                                </label>

                                <input type="text" step="0.01" class="form-control input-monto" required>

                            </div>

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">% Amiri</label>

                                        <input type="text" step="0.01" class="form-control input-porc-amiri" value="0" required>

                                    </div>

                                </div>

                                <div class="col-md-6" id="campoPromotorPorcentaje">

                                    <div class="mb-3">

                                        <label class="form-label">% Promotor</label>

                                        <input type="text" step="0.01" class="form-control input-porc-promotor" value="0" required>

                                    </div>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Retenci&oacute;n Total (%)</label>

                                <div class="input-group">

                                    <input type="text" step="0.01" class="form-control input-retencion-total" value="0" disabled readonly>

                                    <span class="input-group-text bg-info text-white"><i class="ti ti-percentage"></i></span>

                                </div>

                                <small class="text-muted">Suma autom&aacute;tica de % Amiri + % Promotor</small>

                            </div>

                            <div class="mb-3">

                                <label class="form-label fw-bold">Tipo de Pago *</label>

                                <div class="d-flex gap-2">

                                    <button type="button" class="btn btn-outline-danger btn-tipo-pago active" data-tipo="credito">

                                        <i class="ti ti-credit-card"></i> A Cr&eacute;dito

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

                                <div id="warningFondoInsuficiente" class="alert alert-warning py-1 px-2 mt-1 mb-0" style="display:none;font-size:0.875rem;"></div>

                                <small class="text-success"><i class="ti ti-arrow-right"></i> Se descontar&aacute; el pago de esta cuenta inmediatamente</small>

                            </div>

                        `);

                        $('#totalesGroup').show();

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

                                <input type="text" step="0.01" class="form-control input-monto" id="inputMontoCambio" required>

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
    let nombreCuenta = c.nombre;
    if (c.banco && c.banco.trim()) {
        nombreCuenta += ' (' + c.banco + ')';
    }
    // Quitamos la variable 'tipo' (Bancaria/Caja) del texto visible
    html += `<option value="${c.id}" data-saldo="${c.saldo_actual}" data-tipo="${c.tipo_cuenta}">${nombreCuenta} ($${formatMXN(c.saldo_actual)})</option>`;
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
            // Lógica para mostrar el banco
            let nombreCuenta = c.nombre;
            if (c.banco && c.banco.trim()) {
                nombreCuenta += ' (' + c.banco + ')';
            }
            
            // Quitamos la variable 'tipo' (Bancaria/Caja) del texto visible
            html += `<option value="${c.id}" data-tipo="${c.tipo_cuenta}">${nombreCuenta} ($${formatMXN(c.saldo_actual)})</option>`;
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

                                <label class="form-label">Cuenta / Caja de Origen *</label>

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

                                <input type="text" step="0.01" class="form-control input-monto" id="inputMontoRetiro" required>

                                <div id="warningMontoRetiro" class="mt-2 text-danger fw-bold" style="display:none"></div>

                            </div>

                        `);

                        $.getJSON('api/cuentas.php?solo_gastos=1&con_saldo=1&tipo_moneda=MXN', function (cuentas) {

                            let html = '<option value="">-- Selecciona una cuenta o caja --</option>';

                            if (!cuentas.length) {

                                html += '<option disabled>No hay cuentas o cajas con saldo</option>';

                            } else {

                                cuentas.forEach(c => {

                                    if (parseInt(c.tipo_cuenta) !== 1 && parseInt(c.tipo_cuenta) !== 2) return;

                                    let nombreCuenta = c.nombre;

                                    if (c.banco && c.banco.trim()) {

                                        nombreCuenta += ' (' + c.banco + ')';

                                    }

                                    html += `<option value="${c.id}" data-saldo="${c.saldo_actual}">${nombreCuenta} - $${formatMXN(c.saldo_actual)}</option>`;

                                });

                            }

                            $('#cuentaBancariaOrigen').html(html);

                        });

                        break;

                }

            }

        });



        // ─── Cálculo automático MXN (Venta/Compra de Efectivo) ──────────────

        $(document).on('input', '.input-paridad, .input-monto, .input-porc-amiri, .input-porc-promotor', function () {

            let servicioNombre = $('#servicioSelect').val();

            if (!['Venta de Efectivo', 'Compra de Efectivo', 'Cambio de Flujo', 'Retiro de Efectivo'].includes(servicioNombre)) return;



            if (servicioNombre === 'Venta de Efectivo' || servicioNombre === 'Compra de Efectivo') {

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

            // Validar monto vs saldo origen en Retiro de Efectivo
            if (servicioNombre === 'Retiro de Efectivo') {
                const $warn = $('#warningMontoRetiro');
                if ($warn.length) {
                    const saldoOrigen = parseFloat($('#cuentaBancariaOrigen option:selected').attr('data-saldo')) || 0;
                    if ($('#cuentaBancariaOrigen').val() && monto > 0 && monto > saldoOrigen + 0.001) {
                        $warn.show().html(`<i class="ti ti-alert-triangle"></i> El monto (<strong>$${formatMXN(monto)}</strong>) supera el saldo disponible (<strong>$${formatMXN(saldoOrigen)}</strong> MXN)`);
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

                        <small><i class="ti ti-info-circle"></i> El cliente recibir&aacute; $${formatMXN(tot)} en efectivo.</small>

                    </div>

                `);

            } else if (servicioNombre === 'Compra de Efectivo') {

                let pa  = parseFloat($('.input-porc-amiri').val())    || 0;

                let pp  = parseFloat($('.input-porc-promotor').val()) || 0;

                let ca  = monto * (pa / 100);

                let cp  = monto * (pp / 100);

                let tot = monto - ca - cp;

                $('#desgloseBody').html(`

                    <table class="table table-sm mb-0">

                        <tr><td><strong>💵 Monto de la operaci&oacute;n:</strong></td><td class="text-end"><strong>$${formatMXN(monto)}</strong></td></tr>

                        <tr class="table-warning"><td><i class="ti ti-building-bank"></i> Comisi&oacute;n AMIRI (${pa}%):</td><td class="text-end">-$${formatMXN(ca)}</td></tr>

                        <tr class="table-info"><td><i class="ti ti-user"></i> Comisi&oacute;n Promotor (${pp}%):</td><td class="text-end">-$${formatMXN(cp)}</td></tr>

                        <tr class="table-success"><td><strong><i class="ti ti-cash"></i> AMIRI paga al Cliente:</strong></td><td class="text-end"><strong>$${formatMXN(tot)}</strong></td></tr>

                    </table>

                    <div class="alert alert-info mt-2 mb-0 py-2">

                        <small><i class="ti ti-info-circle"></i> AMIRI recibe $${formatMXN(monto)} y paga $${formatMXN(tot)} al cliente. Comisiones se descuentan de Utilidades.</small>

                    </div>

                `);

            } else {

                let total = parseFloat($('.input-paridad').val() || 1) * monto;

                $('#desgloseBody').html(`<div class="alert alert-primary mb-0"><strong>Total:</strong> $${formatMXN(total)} MXN</div>`);

            }

        });



        // ─── Cálculo automático Divisas (cantidad × TC) + labels de formato ─────

        // ─── Cálculo automático Divisas (Bidireccional) ─────────────────────────
        $(document).on('input', '.input-cantidad, .input-tc, .input-total-mxn-editable', function (e) {
            let $target  = $(e.target);
            let cantidad = parseFloat($('.input-cantidad').val()) || 0;
            let tc       = parseFloat($('.input-tc').val()) || 0;
            let total    = parseFloat($('.input-total-mxn-editable').val()) || 0;

            if ($target.hasClass('input-total-mxn-editable')) {
                // Si escribes el Total MXN -> Calcula automáticamente el Tipo de Cambio
                if (cantidad > 0 && total > 0) {
                    tc = total / cantidad;
                    $('.input-tc').val(tc.toFixed(4));
                }
            } else if ($target.hasClass('input-tc')) {
                // Si escribes el Tipo de Cambio -> Calcula automáticamente el Total MXN
                if (cantidad > 0 && tc > 0) {
                    total = cantidad * tc;
                    $('.input-total-mxn-editable').val(total.toFixed(2));
                }
            } else if ($target.hasClass('input-cantidad')) {
                // Si cambias la Cantidad -> Prioriza actualizar el Total si ya hay un TC
                if (tc > 0) {
                    total = cantidad * tc;
                    $('.input-total-mxn-editable').val(total.toFixed(2));
                } else if (total > 0 && cantidad > 0) {
                    tc = total / cantidad;
                    $('.input-tc').val(tc.toFixed(4));
                }
            }

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
            // Espejito con comas para el Total MXN
            if (total > 0) {
                $('.label-formato-total').text('= $' + formatMXN(total));
            } else {
                $('.label-formato-total').text('');
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



            if (data.cliente_nombre)  html += `<div class="col-md-4 mb-2"><strong><i class="ti ti-user"></i> Cliente:</strong><br>${data.cliente_nombre}</div>`;
            if (data.proveedor_nombre) html += `<div class="col-md-4 mb-2"><strong><i class="ti ti-building"></i> Proveedor:</strong><br>${data.proveedor_nombre}</div>`;
            if (data.promotor_nombre)  html += `<div class="col-md-4 mb-2"><strong><i class="ti ti-user-star"></i> Promotor:</strong><br>${data.promotor_nombre}</div>`;
            if (data.prestamista_nombre) html += `<div class="col-md-4 mb-2"><strong><i class="ti ti-businessplan"></i> Prestamista:</strong><br>${data.prestamista_nombre}</div>`;
            if (data.beneficiario_nombre) html += `<div class="col-md-4 mb-2"><strong><i class="ti ti-users"></i> Beneficiario:</strong><br>${data.beneficiario_nombre}</div>`;



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

                    ${res.efectos ? buildEfectosReverso(res) : ''}

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

                // Validar que en compras de contado la cuenta de origen tenga saldo suficiente
                let esCompra = [5, 6].includes(servicioId); // 5=Compra USD, 6=Compra USDT
                if (esCompra && $('.input-tipo-pago').val() === 'contado') {
                    // Leemos el total, quitándole las comas por si tiene formato
                    let inputTotal = $('.input-total-mxn-editable').length > 0 ? $('.input-total-mxn-editable').val() : $('.input-total-mxn').val();
                    let totalAPagar = parseFloat((inputTotal || '0').replace(/,/g, '')) || 0;
                    let saldoCuenta = parseFloat($('#cuentaPagoContadoSelect option:selected').data('saldo')) || 0;
                    
                    if ($('#cuentaPagoContadoSelect').val() && totalAPagar > saldoCuenta + 0.001) {
                        showToast('Fondos insuficientes: El monto a pagar supera el saldo de la cuenta seleccionada.', 'error');
                        
                        // Mostrar la alerta visual
                        $('#warningFondoInsuficiente').show().html('<i class="ti ti-alert-triangle"></i> El total a pagar (<strong>$' + formatMXN(totalAPagar) + '</strong>) supera el saldo de la cuenta (<strong>$' + formatMXN(saldoCuenta) + '</strong>)');
                        return;
                    }
                }

                // ── POST a api/flujo-divisas.php ──────────────────────────



                // ── POST a api/flujo-divisas.php ──────────────────────────

                let formData = {

                    fecha:          $('input[name="fecha"]').val(),

                    servicio:       servicioNombre,

                    cliente_id:     $('#clienteSelect').val()   || '',

                    proveedor_id:   $('#proveedorSelect').val() || '',

                    cuenta_id:      $('#cuentaSelect').val()    || '',

                    cantidad:       $('.input-cantidad').val(),

                    tipo_cambio:    $('.input-tc').val(),

                    total_mxn:      ($('.input-total-mxn-editable').val() || '').replace(/,/g, ''),

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

                    const saldoOrigen = parseFloat($('#cuentaBancariaOrigen option:selected').attr('data-saldo')) || 0;
                    if (payload.monto > saldoOrigen + 0.001) {
                        showToast('Saldo insuficiente. Disponible: $' + formatMXN(saldoOrigen) + ' MXN', 'error');
                        return;
                    }

                }

                if (servicioNombre === 'Venta de Efectivo') {

                    payload.porcentaje_amiri    = parseFloat($('.input-porc-amiri').val())    || 0;

                    payload.porcentaje_promotor = parseFloat($('.input-porc-promotor').val()) || 0;

                }

                if (servicioNombre === 'Compra de Efectivo') {

                    payload.porcentaje_amiri    = parseFloat($('.input-porc-amiri').val())    || 0;

                    payload.porcentaje_promotor = parseFloat($('.input-porc-promotor').val()) || 0;

                    payload.tipo_pago           = $('.input-tipo-pago').val() || 'credito';

                    payload.cuenta_pago_id      = $('#cuentaPagoContadoSelect').val() || null;

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

