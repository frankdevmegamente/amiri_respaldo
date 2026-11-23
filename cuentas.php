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

    <title>Cuentas - Amiri</title>

    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />

    <link rel="stylesheet" href="src/assets/css/styles.min.css" />

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

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
        #cuentasTab .nav-link.active {
            color: var(--nx-primary) !important;
            background-color: transparent !important;
            border-bottom: 2px solid var(--nx-primary) !important;
            border-top: none;
            border-left: none;
            border-right: none;
            font-weight: 600;
        }
        #cuentasTab .nav-link:not(.active) {
            border: none;
            color: #64748b;
        }
        #cuentasTab .nav-link:not(.active):hover {
            color: var(--nx-primary);
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
           ESTILOS TARJETA DATOS BANCARIOS (Adaptable)
           ========================================= */
        .tarjeta-bancaria {
            width: 100%; max-width: 380px; 
            background: var(--nx-surface); 
            border-radius: 16px; padding: 30px 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            position: relative; overflow: hidden; 
            border: 1px solid #eef2f6;
        }
        .tb-texto-principal { color: #1e293b; font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
        .tb-texto-secundario { color: #9a9096; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        .tb-cuenta { color: var(--nx-primary); font-size: 1.4rem; font-weight: 800; letter-spacing: 1px; word-break: break-all; }
        .tb-linea { border-top: 1px dashed #e2e8f0; }

        /* MODO OSCURO PARA LA TARJETA */
        [data-bs-theme="dark"] .tarjeta-bancaria,
        .dark-mode .tarjeta-bancaria {
            background: #1a181c !important;
            border-color: rgba(255,255,255,0.12) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }
        [data-bs-theme="dark"] .tb-texto-principal,
        .dark-mode .tb-texto-principal {
            color: #f5f2f4 !important;
        }
        [data-bs-theme="dark"] .tb-linea,
        .dark-mode .tb-linea {
            border-top: 1px dashed rgba(255,255,255,0.12) !important;
        }

        /* ─── Page Header ─────────────────────────────── */
        .cuentas-header { position: relative; overflow: visible; border-radius: 16px !important; }
        .cuentas-header .bg-pattern { position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(93, 135, 255, 0.08); pointer-events: none; }
        .cuentas-header .bg-pattern-2 { position: absolute; bottom: -30%; left: 20%; width: 250px; height: 250px; border-radius: 50%; background: rgba(19, 222, 185, 0.06); pointer-events: none; }
        .cuentas-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .cuentas-header .sub-badge { font-size: 1rem; color: #9a9096; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card { position: relative; border-radius: 12px !important; transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9a9096; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #9a9096; }

        [data-bs-theme="dark"] .cuentas-header .sub-badge { color: #9a9096 !important; }
        [data-bs-theme="dark"] .bg-primary.bg-opacity-10 { background-color: rgba(93,135,255,0.12) !important; }
        [data-bs-theme="dark"] .bg-success.bg-opacity-10 { background-color: rgba(19,222,185,0.12) !important; }
        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 { background-color: rgba(255,174,31,0.12) !important; }
        [data-bs-theme="dark"] .bg-info.bg-opacity-10 { background-color: rgba(83,155,255,0.12) !important; }
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
                    <!-- ════════════════════════════════════
                         PAGE HEADER
                         ════════════════════════════════════ -->
                    <div class="card cuentas-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-wallet text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Cuentas</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Administración de cuentas bancarias y cajas</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="dropdown">
                                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                            <i class="ti ti-plus"></i> Nueva Cuenta
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="mostrarFormulario(1)">Cuenta Bancaria</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="mostrarFormulario(2)">Caja</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         KPI METRIC CARDS
                         ════════════════════════════════════ -->
                    <div class="row g-3 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100" onclick="switchTab('bancaria')" title="Ver cuentas bancarias">
                                <div class="metric-accent bg-primary"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Total Cuentas</span>
                                        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-primary" id="kpiTotal">0</div>
                                    <div class="metric-footer">Registradas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100" onclick="switchTab('bancaria')" title="Ver cuentas bancarias">
                                <div class="metric-accent bg-success"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Bancarias</span>
                                        <div class="metric-icon bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-building-bank"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-success" id="kpiBancarias">0</div>
                                    <div class="metric-footer">Cuentas de banco</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100" onclick="switchTab('caja')" title="Ver cajas internas">
                                <div class="metric-accent bg-warning"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Cajas</span>
                                        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="ti ti-safe"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-warning" id="kpiCajas">0</div>
                                    <div class="metric-footer">Cajas internas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card metric-card border-0 shadow-sm h-100" onclick="switchTab('bancaria')" title="Ver cuentas activas">
                                <div class="metric-accent bg-info"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="metric-label">Activas</span>
                                        <div class="metric-icon bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-circle-check"></i>
                                        </div>
                                    </div>
                                    <div class="metric-value text-info" id="kpiActivas">0</div>
                                    <div class="metric-footer">En uso</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" id="cuentasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bancaria-tab" data-bs-toggle="tab" data-bs-target="#bancaria" type="button" role="tab">Cuentas Bancarias</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="caja-tab" data-bs-toggle="tab" data-bs-target="#caja" type="button" role="tab">Cajas</button>
                        </li>

                    </ul>

                    <div class="tab-content" id="cuentasTabContent">

                        <div class="tab-pane fade show active" id="bancaria" role="tabpanel">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaCuentasBancarias" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre / Razón Social</th>
                                                <th>Número / CLABE</th>
                                                <th>Banco</th>
                                                <th>Tipo de moneda</th>
                                                <th>Saldo</th>
                                                <th>Documento</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="caja" role="tabpanel">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaCajas" class="table table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre de la Caja</th>
                                                <th>Tipo de moneda</th>
                                                <th>Saldo Actual</th>
                                                <th>Estado</th>
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



        <!-- Modal -->

        <div class="modal fade" id="modalCuenta" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title" id="modalTitulo">Nueva Cuenta</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>

                    <form id="formCuenta">

                        <div class="modal-body">

                            <input type="hidden" id="cuenta_id" name="id">

                            <input type="hidden" id="tipo_cuenta" name="tipo_cuenta" value="1">

                            <div class="mb-3">

                                <label class="form-label">Nombre / Razón Social *</label>

                                <input type="text" class="form-control" name="nombre" id="nombre" required>

                            </div>

                            <div class="mb-3" id="campoNumero">

                                <label class="form-label">Número de cuenta o CLABE *</label>

                                <input type="text" class="form-control no-capitalize" name="numero_cuenta" id="numero_cuenta" required>

                            </div>

                            <div class="mb-3" id="campoBanco">

                                <label class="form-label">Banco *</label>

                                <select class="form-select" name="banco" id="banco" required>

                                    <option value="" selected disabled>Selecciona un banco</option>

                                    <option value="BBVA">BBVA</option>

                                    <option value="BANORTE">BANORTE</option>

                                    <option value="FONDEADORA">FONDEADORA</option>

                                    <option value="ALBO">ALBO</option>

                                    <option value="SCOTIABANK">SCOTIABANK</option>

                                    <option value="SANTANDER">SANTANDER</option>

                                    <option value="BANREGIO">BANREGIO</option>

                                     <option value="BANBAJIO">BANBAJIO</option>

                                     <option value="MERCADO PAGO">MERCADO PAGO</option>

                                     <option value="BANCO PLATA">BANCO PLATA</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Tipo de Moneda *</label>

                                <select class="form-select" name="tipo_moneda" id="tipo_moneda" required>

                                    <option value="MXN">MXN</option>

                                    <option value="USD">USD</option>

                                    <option value="USDT">USDT</option>

                                </select>

                            </div>

                            <div class="mb-3" id="campoDocumento">

                                <label class="form-label">Documento (PDF/Imagen)</label>

                                <input type="file" class="form-control" name="documento" id="documento" accept=".pdf,.jpg,.jpeg,.png,.gif">

                                <small class="text-muted">Formatos: PDF, JPG, PNG, GIF (máx 5MB)</small>

                                <div id="documentoActual" class="mt-2"></div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">Estado</label>

                                <select class="form-select" name="activo" id="activo">

                                    <option value="1">Activa</option>

                                    <option value="0">Inactiva</option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <div class="form-check">

                                    <input type="checkbox" class="form-check-input" name="es_caja_empresa" id="es_caja_empresa" value="1">

                                    <label class="form-check-label" for="es_caja_empresa">

                                        Marcar como Caja de la Empresa

                                    </label>

                                </div>

                                <small class="text-muted">Si está marcada, esta cuenta se mostrará como opción para pagar a clientes</small>

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

       <div class="modal fade" id="modalCompartirCuenta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title text-muted fs-4">Generador de Datos Bancarios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex justify-content-center py-4">
                        
                        <div id="tarjetaCuentaAmiri" class="tarjeta-bancaria">
                            
                            <div style="position: absolute; top: -60px; right: -60px; width: 160px; height: 160px; background: var(--nx-primary); opacity: 0.08; border-radius: 50%;"></div>
                            <div style="position: absolute; bottom: -40px; left: -40px; width: 100px; height: 100px; background: var(--nx-success); opacity: 0.08; border-radius: 50%;"></div>
                            
                            <div class="text-center mb-4" style="position: relative; z-index: 2;">
                                <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro mx-auto" style="height: 45px; max-width: 100%; object-fit: contain;" />

                                <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro mx-auto" style="height: 45px; max-width: 100%; object-fit: contain; display: none;" />
                                
                                <p class="tb-texto-secundario mb-0" style="margin-top: 5px;">Datos Bancarios</p>
                            </div>
                            
                            <div class="mb-3" style="position: relative; z-index: 2;">
                                <small class="tb-texto-secundario">Beneficiario / Razón Social</small>
                                <div id="genNombre" class="tb-texto-principal">-</div>
                            </div>
                            
                            <div class="mb-3" style="position: relative; z-index: 2;">
                                <small class="tb-texto-secundario">Banco</small>
                                <div id="genBanco" class="tb-texto-principal">-</div>
                            </div>
                            
                            <div class="mb-4" style="position: relative; z-index: 2;">
                                <small class="tb-texto-secundario">Cuenta / CLABE</small>
                                <div id="genCuenta" class="tb-cuenta">-</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 mt-2 tb-linea" style="position: relative; z-index: 2;">
                                <div>
                                    <small class="tb-texto-secundario">Moneda</small>
                                    <div id="genMoneda" style="display: block; font-weight: 800; font-size: 1.1rem;">-</div>
                                </div>
                                <!-- <div class="text-end"> -->
                                    <!-- <i class="ti ti-shield-check text-success fs-1 opacity-50"></i> -->
                                <!-- </div> -->
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-success" id="btnDescargarImagen" onclick="descargarImagenCuenta()">
                            <i class="ti ti-download"></i> Descargar Imagen
                        </button>
                    </div>
                </div>
            </div>
        </div>



        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <script src="src/assets/js/sidebarmenu.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script src="src/assets/js/utils.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


        <script>

            let tablaBancarias, tablaCajas;



            $(document).ready(function() {



                tablaBancarias = $('#tablaCuentasBancarias').DataTable({

                    ajax: {

                        url: 'api/cuentas.php?tipo_cuenta=1',

                        dataSrc: ''

                    },

                    columns: columnasBancarias(),

                    scrollX: true,

                    order: [[0, 'desc']],

                    language: {

                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"

                    }

                });



                tablaCajas = $('#tablaCajas').DataTable({

                    ajax: {

                        url: 'api/cuentas.php?tipo_cuenta=2',

                        dataSrc: ''

                    },

                    columns: columnasCajas(),

                    scrollX: true,

                    order: [[0, 'desc']],

                    language: {

                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"

                    }

                });



                $('#formCuenta').submit(function(e) {

                    e.preventDefault();



                    const formData = new FormData(this);



                    $.ajax({

                        url: 'api/cuentas.php',

                        method: 'POST',

                        data: formData,

                        processData: false,

                        contentType: false,

                        success: function() {

                            $('#modalCuenta').modal('hide');

                            tablaBancarias.ajax.reload();
                            tablaCajas.ajax.reload();
                            cargarResumen();

                            showToast('Cuenta guardada', 'success');

                        },

                        error: function(xhr) {

                            showToast(xhr.responseJSON?.error || 'Error', 'error');

                        }

                    });

                });

                cargarResumen();

            });



            function columnasBancarias() {

                return [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'numero_cuenta',

                        render: (d) => d ? `<span class="no-capitalize">${d}</span>` : ''

                    },

                    {

                        data: 'banco'

                    },

                    {

                        data: 'tipo_moneda'

                    },

                    {

                        data: 'saldo_actual',

                        render: d => new Intl.NumberFormat('es-MX', {

                            style: 'currency',

                            currency: 'MXN'

                        }).format(d)

                    },

                    {

                        data: 'documento_path',

                        render: d => d ? `<a href="${d}" target="_blank" class="btn btn-sm btn-info"><i class="ti ti-download"></i></a>` : '<span class="text-muted">-</span>'

                    },

                    {

                        data: 'activo',

                        render: d => d == 1 ? badgeOK() : badgeNO()

                    },

                    {

                        data: null,

                        render: d => botones(d.id, 1)

                    }

                ];

            }



            function columnasCajas() {

                return [{

                        data: 'id'

                    },

                    {

                        data: 'nombre'

                    },

                    {

                        data: 'tipo_moneda'

                    },

                    {

                        data: 'saldo_actual',

                        render: d => new Intl.NumberFormat('es-MX', {

                            style: 'currency',

                            currency: 'MXN'

                        }).format(d)

                    },

                    {

                        data: 'activo',

                        render: d => d == 1 ? badgeOK() : badgeNO()

                    },

                    {

                        data: null,

                        render: d => botones(d.id, 2)

                    }

                ];

            }



            function editarCuenta(id, tipo) {

                $.get('api/cuentas.php?id=' + id, function(d) {

                    $('#cuenta_id').val(d.id);

                    $('#nombre').val(d.nombre);

                    $('#tipo_moneda').val(d.tipo_moneda);

                    $('#activo').val(d.activo);

                    $('#tipo_cuenta').val(tipo);

                    $('#es_caja_empresa').prop('checked', d.es_caja_empresa == 1);



                    if (tipo == 1) {

                        $('#campoNumero, #campoBanco, #campoDocumento').show();

                        $('#numero_cuenta').val(d.numero_cuenta);

                        $('#banco').val(d.banco);



                        // Mostrar documento actual si existe

                        if (d.documento_path) {

                            $('#documentoActual').html(`<a href="${d.documento_path}" target="_blank" class="btn btn-sm btn-info">Ver documento actual</a>`);

                        } else {

                            $('#documentoActual').html('');

                        }

                    } else {

                        $('#campoNumero, #campoBanco, #campoDocumento').hide();

                    }



                    $('#modalCuenta').modal('show');

                });

            }



            function eliminarCuenta(id) {

                if (!confirm('¿Eliminar cuenta?')) return;



                $.ajax({

                    url: 'api/cuentas.php',

                    method: 'DELETE',

                    data: {

                        id

                    },

                    success: () => {

                        tablaBancarias.ajax.reload();
                        tablaCajas.ajax.reload();
                        cargarResumen();

                        showToast('Cuenta eliminada', 'success');

                    }

                });

            }



            // Etiquetas de estado con estilo suave
            const badgeOK = () => '<span class="badge bg-success bg-opacity-10 text-success border-0">Activa</span>';
            const badgeNO = () => '<span class="badge bg-danger bg-opacity-10 text-danger border-0">Inactiva</span>';
            
            // Botones de acción con estilo Soft Badge
            const botones = (id, tipo) => {
                let btnCompartir = '';
                if (tipo == 1) { // El botón compartir solo sale en Cuentas Bancarias
                    btnCompartir = `
                    <button class="badge bg-info bg-opacity-10 text-info border-0 me-2" style="cursor: pointer; font-size: 0.75rem;" onclick="abrirGeneradorCuenta(${id})" title="Compartir Datos">
                        <i class="ti ti-share"></i> Compartir
                    </button>`;
                }
                return `
                <div class="d-flex align-items-center flex-wrap gap-1">
                    <a class="badge bg-primary bg-opacity-10 text-primary border-0 me-2 text-decoration-none" style="cursor: pointer; font-size: 0.75rem;" href="movimientos.php?cuenta_id=${id}" title="Ver estado de cuenta: entradas, salidas y saldo remanente">
                        <i class="ti ti-arrows-exchange"></i> Movimientos
                    </a>
                    ${btnCompartir}
                    <button class="badge bg-warning bg-opacity-10 text-warning border-0 me-2" style="cursor: pointer; font-size: 0.75rem;" onclick="editarCuenta(${id},${tipo})" title="Editar">
                        <i class="ti ti-pencil"></i> Editar
                    </button>
                    <button class="badge bg-danger bg-opacity-10 text-danger border-0" style="cursor: pointer; font-size: 0.75rem;" onclick="eliminarCuenta(${id})" title="Eliminar">
                        <i class="ti ti-trash"></i> Eliminar
                    </button>
                </div>
                `;
            };



            function mostrarFormulario(tipo) {

                // Resetear formulario

                $('#formCuenta')[0].reset();



                // Limpiar ID (nuevo registro)

                $('#cuenta_id').val('');

                $('#documentoActual').html('');

                $('#es_caja_empresa').prop('checked', false);



                // Setear tipo de cuenta

                $('#tipo_cuenta').val(tipo);



                if (tipo == 1) {

                    // Cuenta bancaria

                    $('label[for="nombre"]').text('Nombre / Razón Social *');

                    $('#modalTitulo').text('Nueva Cuenta Bancaria');

                    $('#campoNumero, #campoBanco, #campoDocumento').show();

                    $('#numero_cuenta').prop('required', true);

                    $('#banco').prop('required', true);

                } else {

                    // Caja

                    $('label[for="nombre"]').text('Nombre de la Caja *');

                    $('#modalTitulo').text('Nueva Caja');

                    $('#campoNumero, #campoBanco, #campoDocumento').hide();

                    $('#numero_cuenta').prop('required', false);

                    $('#banco').prop('required', false);

                }



                // Mostrar modal

                $('#modalCuenta').modal('show');

            }

            function abrirGeneradorCuenta(id) {
                // Obtenemos los datos de la cuenta desde tu API
                $.get('api/cuentas.php?id=' + id, function(d) {
                    $('#genNombre').text(d.nombre);
                    $('#genBanco').text(d.banco);
                    $('#genCuenta').text(d.numero_cuenta);
                    
                    // Colores de la moneda según sea MXN, USD o USDT
                    const colorMoneda = d.tipo_moneda === 'MXN' ? 'var(--nx-primary)' : (d.tipo_moneda === 'USD' ? 'var(--nx-success)' : '#0ea5e9');
                    $('#genMoneda').css('color', colorMoneda).text(d.tipo_moneda);
                    
                    // Mostramos el modal
                    $('#modalCompartirCuenta').modal('show');
                });
            }
            
            function descargarImagenCuenta() {
                const btn = $('#btnDescargarImagen');
                const textoOriginal = btn.html();
                const tarjeta = document.querySelector("#tarjetaCuentaAmiri");
                const esModoOscuro = document.body.getAttribute('data-bs-theme') === 'dark' ||
                                     document.body.classList.contains('dark-mode');

                btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Generando...');

                const estilosOriginales = {
                    background: tarjeta.style.background,
                    border: tarjeta.style.border,
                    boxShadow: tarjeta.style.boxShadow,
                    borderRadius: tarjeta.style.borderRadius,
                    overflow: tarjeta.style.overflow
                };
                const logoClaro = tarjeta.querySelector('.logo-modo-claro');
                const logoOscuro = tarjeta.querySelector('.logo-modo-oscuro');
                const logoClaroDisplay = logoClaro ? logoClaro.style.display : null;
                const logoOscuroDisplay = logoOscuro ? logoOscuro.style.display : null;

                if (esModoOscuro) {
                    tarjeta.style.background = '#1a181c';
                    tarjeta.style.border = '1px solid rgba(255,255,255,0.12)';
                    tarjeta.style.boxShadow = '0 10px 30px rgba(0,0,0,0.5)';
                    if (logoClaro) logoClaro.style.display = 'none';
                    if (logoOscuro) logoOscuro.style.display = 'block';
                }

                tarjeta.style.borderRadius = '0';
                tarjeta.style.overflow = 'visible';

                html2canvas(tarjeta, {
                    scale: 3,
                    backgroundColor: esModoOscuro ? '#1a181c' : '#ffffff',
                    useCORS: true
                }).then(canvas => {
                    tarjeta.style.borderRadius = estilosOriginales.borderRadius;
                    tarjeta.style.overflow = estilosOriginales.overflow;
                    if (esModoOscuro) {
                        tarjeta.style.background = estilosOriginales.background;
                        tarjeta.style.border = estilosOriginales.border;
                        tarjeta.style.boxShadow = estilosOriginales.boxShadow;
                        if (logoClaro) logoClaro.style.display = logoClaroDisplay;
                        if (logoOscuro) logoOscuro.style.display = logoOscuroDisplay;
                    }

                    let link = document.createElement('a');
                    link.download = 'Datos_Bancarios_Amiri.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    btn.prop('disabled', false).html(textoOriginal);
                    showToast('Imagen descargada correctamente', 'success');
                }).catch(err => {
                    tarjeta.style.borderRadius = estilosOriginales.borderRadius;
                    tarjeta.style.overflow = estilosOriginales.overflow;
                    if (esModoOscuro) {
                        tarjeta.style.background = estilosOriginales.background;
                        tarjeta.style.border = estilosOriginales.border;
                        tarjeta.style.boxShadow = estilosOriginales.boxShadow;
                        if (logoClaro) logoClaro.style.display = logoClaroDisplay;
                        if (logoOscuro) logoOscuro.style.display = logoOscuroDisplay;
                    }
                    btn.prop('disabled', false).html(textoOriginal);
                    showToast('Hubo un error al generar la imagen', 'error');
                });
            }

        // ─── Resumen (KPI Cards) ─────────────────────
        function cargarResumen() {
            $.get('api/cuentas.php?resumen=1', function(data) {
                $('#kpiTotal').text(data.total);
                $('#kpiBancarias').text(data.bancarias);
                $('#kpiCajas').text(data.cajas);
                $('#kpiActivas').text(data.activas);

                if (data.bancarias_lista && data.bancarias_lista.length)
                    $('.col-xl-3.col-md-6').eq(1).find('.metric-card').attr('title', data.bancarias_lista.join('\n'));
                if (data.cajas_lista && data.cajas_lista.length)
                    $('.col-xl-3.col-md-6').eq(2).find('.metric-card').attr('title', data.cajas_lista.join('\n'));
            }).fail(function() {
                $('#kpiTotal').text('Error');
            });
        }

        function switchTab(tab) {
            var tabEl = document.getElementById(tab + '-tab');
            if (tabEl) {
                var bsTab = new bootstrap.Tab(tabEl);
                bsTab.show();
                tabEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        </script>



</body>



</html>
