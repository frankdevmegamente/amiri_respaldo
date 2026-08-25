<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();



// Servicios de divisas (IDs 8-11)

$serviciosDivisas = [

    ['id' => 8, 'nombre' => 'Compra de Dólares'],

    ['id' => 9, 'nombre' => 'Venta de Dólares'],

    ['id' => 10, 'nombre' => 'Compra de USDT'],

    ['id' => 11, 'nombre' => 'Venta de USDT']

];

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8">

    <title>Flujo Divisas - Amiri</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">

    <link rel="stylesheet" href="src/assets/css/styles.min.css">

    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="src/assets/css/dark-mode.css">

    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">

    <link rel="stylesheet" href="src/assets/css/layout-custom.css">

    <style>

        /* Hover para tablas en modal de detalle */

        #modalDetalle .table-hover tbody tr:hover,

        #modalDetalle .table-hover tbody tr:hover td {

            background-color: rgba(255, 255, 255, 0.95) !important;

            color: inherit !important;

        }



        #modalDetalle .table-hover tbody tr:hover .text-muted {

            color: #000000 !important;

        }



        #modalDetalle .table-hover tbody tr:hover .text-success {

            color: #28a745 !important;

        }



        #modalDetalle .table-hover tbody tr:hover .text-danger {

            color: #dc3545 !important;

        }



        #modalDetalle .table-hover tbody tr:hover .text-warning {

            color: #ffc107 !important;

        }



        /* Badges de moneda */

        .badge-usd {

            background: linear-gradient(135deg, #0ea5e9, #2563eb);

            color: white;

        }



        .badge-usdt {

            background: linear-gradient(135deg, #10b981, #059669);

            color: white;

        }
 [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
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



                <div class="container-fluid">



                    <!-- Header -->

                    <div class="row mb-3">

                        <div class="col-12 d-flex align-items-center gap-2">

                            <h3 class="mb-0">

                                <i class="ti ti-currency-dollar"></i> Flujo Divisas (USD/USDT)

                            </h3>



                            <!-- Filtro de Moneda -->

                            <div class="ms-auto">

                                <select class="form-select" id="filtroMoneda" style="width: 150px;">

                                    <option value="USD">USD (Dólares)</option>

                                    <option value="USDT">USDT (Tether)</option>

                                </select>

                            </div>

                        </div>

                    </div>



                    <!-- DIFERENCIA -->

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

                                                    <h6 class="mb-1 text-muted">DIFERENCIA <span id="monedaActual" class="badge badge-usd">USD</span></h6>

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



                    <!-- Resúmenes -->

                    <div class="row mb-4">

                        <!-- SALDOS -->

                        <div class="col-lg-4 mb-3">

                            <div class="card shadow-sm h-100">

                                <div class="card-header bg-success text-white">

                                    <span><i class="ti ti-wallet"></i> SALDOS BANCARIOS</span>

                                </div>

                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">

                                    <table class="table table-sm table-striped mb-0">

                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">

                                            <tr>

                                                <th>Cuenta</th>

                                                <th>Tipo</th>

                                                <th class="text-end">Monto</th>

                                                <th class="text-end">MXN</th>

                                            </tr>

                                        </thead>

                                        <tbody id="saldosBody">

                                            <tr>

                                                <td colspan="3">Cargando...</td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>



                        <!-- CAPITAL -->

                        <div class="col-lg-4 mb-3">

                            <div class="card shadow-sm h-100">

                                <div class="card-header bg-warning text-white">

                                    <span><i class="ti ti-building-bank"></i> CAPITAL</span>

                                </div>

                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">

                                    <table class="table table-sm table-striped mb-0">

                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">

                                            <tr>

                                                <th>Cuenta</th>

                                                <th>Tipo</th>

                                                <th class="text-end">Monto</th>

                                                <th class="text-end">MXN</th>

                                            </tr>

                                        </thead>

                                        <tbody id="capitalBody">

                                            <tr>

                                                <td colspan="3">Cargando...</td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>



                        <!-- PROVEEDOR -->

                        <div class="col-lg-4 mb-3">

                            <div class="card shadow-sm h-100">

                                <div class="card-header bg-info text-white">

                                    <span><i class="ti ti-building-store"></i> PROVEEDOR</span>

                                </div>

                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">

                                    <table class="table table-sm table-striped mb-0">

                                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">

                                            <tr>

                                                <th>Proveedor</th>

                                                <th>Moneda</th>

                                                <th class="text-end">Monto</th>

                                                <th class="text-end">MXN</th>

                                            </tr>

                                        </thead>

                                        <tbody id="proveedorBody">

                                            <tr>

                                                <td colspan="3">Cargando...</td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!-- Botón Nuevo Movimiento -->

                    <div class="row mb-3">

                        <div class="col-12 d-flex align-items-center gap-2">

                            <div class="ms-auto">

                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFlujo">

                                    + Nuevo Movimiento

                                </button>

                            </div>

                        </div>

                    </div>



                    <!-- Tabla Flujo -->

                    <div class="row">

                        <div class="col-12">

                            <div class="card">

                                <div class="card-body table-responsive">

                                    <table id="tablaFlujo" class="table table-striped w-100">

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

                    <h5 class="modal-title">Nuevo Movimiento de Divisas</h5>

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

                                <?php foreach ($serviciosDivisas as $s): ?>

                                    <option value="<?= $s['nombre'] ?>" data-id="<?= $s['id'] ?>"><?= $s['nombre'] ?></option>

                                <?php endforeach; ?>

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

                                <div class="card-body" id="desgloseBody">

                                    <!-- Se llenará dinámicamente según el servicio -->

                                </div>

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



    <!-- Modal Detalle Movimiento -->

    <div class="modal fade" id="modalDetalle" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">Detalle del Movimiento</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body" id="detalleContent">

                    <!-- Se cargará dinámicamente -->

                </div>

                <div class="modal-footer">

                    <div class="me-auto" id="detalleEstado"></div>

                    <button type="button" class="btn btn-warning" id="btnAnular" data-grupo="">

                        <i class="ti ti-x"></i> Anular Movimiento

                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>



    <!-- Modal Confirmar Anulación -->

    <div class="modal fade" id="modalAnular" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title"><i class="ti ti-alert-triangle"></i> Anular Movimiento</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="alert alert-warning">

                        <i class="ti ti-info-circle"></i>

                        Esta acción creará un movimiento de reverso para anular la operación.

                        El historial se mantendrá intacto para auditoría.

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">Motivo de la anulación *</label>

                        <textarea class="form-control" id="motivoAnulacion" rows="3"

                            placeholder="Ej: Error en el monto registrado, cliente equivocado, etc."></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                    <button type="button" class="btn btn-warning" id="btnConfirmarAnular">

                        <i class="ti ti-check"></i> Confirmar Anulación

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

        /* Ajustes visuales para modo oscuro */

        [data-bs-theme="dark"] #totalesGroup .card {

            background: #0f172a;

            border-color: #1f2937;

        }



        [data-bs-theme="dark"] #totalesGroup .card-header {

            background: linear-gradient(135deg, #1d4ed8, #2563eb);

            border-color: transparent;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] #desgloseBody table {

            background: #0b1220;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] #desgloseBody .table-warning {

            background: #4b5563;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] #desgloseBody .table-info {

            background: #374151;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] #desgloseBody .table-success {

            background: #14532d;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] #desgloseBody .alert-info {

            background: linear-gradient(135deg, #0ea5e9, #2563eb);

            color: #e0f2fe;

            border: none;

        }



        [data-bs-theme="dark"] .modal-content {

            background: #0b1220;

            border-color: #1f2937;

        }



        [data-bs-theme="dark"] .input-group-text,

        [data-bs-theme="dark"] .form-control,

        [data-bs-theme="dark"] .form-select {

            background: #111827;

            border-color: #1f2937;

            color: #e5e7eb;

        }



        [data-bs-theme="dark"] .form-control:focus,

        [data-bs-theme="dark"] .form-select:focus {

            border-color: #2563eb;

            box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.35);

        }



        #modalFlujo .form-select,

        #modalFlujo .form-control {

            height: 48px;

            padding: 12px 16px;

            font-size: 1rem;

            cursor: pointer;

        }



        #modalFlujo .form-label {

            font-weight: 600;

            margin-bottom: 8px;

            font-size: 0.95rem;

        }



        #modalFlujo .form-select {

            background-position: right 16px center;

        }

    </style>



    <script>

        $(document).ready(function() {

            window.__mostrarConvertido = false;



            // Función para formatear números (enteros, sin decimales)

            function formatoNumero(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString('en-US', {

                    minimumFractionDigits: 0,

                    maximumFractionDigits: 0

                });

            }



            // Función para formatear MXN (con 2 decimales)

            function formatearMXN(valor) {

                const n = parseFloat(valor) || 0;

                return n.toLocaleString('es-MX', {

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                });

            }



            // Moneda actual seleccionada

            let monedaActual = 'USD';



            // Actualizar badge de moneda

            function actualizarBadgeMoneda() {

                const $badge = $('#monedaActual');

                if (monedaActual === 'USD') {

                    $badge.removeClass('badge-usdt').addClass('badge-usd').text('USD');

                } else {

                    $badge.removeClass('badge-usd').addClass('badge-usdt').text('USDT');

                }

            }



            // Mapa de moneda a servicios_ids (incluye ventas MXN)

            function getServiciosIds(moneda) {

                return moneda === 'USD' ? '5,2' : '6,3';

            }



            // Cambio de filtro de moneda

            $('#filtroMoneda').on('change', function() {

                monedaActual = $(this).val();

                actualizarBadgeMoneda();

                cargarResumenes();

                table.ajax.url('api/flujo-lista.php?servicios_ids=' + getServiciosIds(monedaActual)).load();

            });



            // Inicializar DataTable

            var table = $('#tablaFlujo').DataTable({

                ajax: {

                    url: 'api/flujo-lista.php?servicios_ids=5,2',

                    dataSrc: 'data'

                },

                columns: [{

                        data: 'grupo_id',

                        title: 'Grupo',

                        visible: false,

                        render: d => '<span class="badge bg-secondary">#' + d + '</span>'

                    },

                    {

                        data: 'fecha',

                        title: 'Fecha'

                    },

                    {

                        data: 'tipo_principal',

                        title: 'Tipo',

                        defaultContent: '',

                        render: function(data) {

                            if (!data) return '<span class="badge bg-secondary">-</span>';

                            let badge = '';

                            if (data === 'Entrada') badge = '<span class="badge bg-success">✓ Entrada</span>';

                            else if (data === 'Salida') badge = '<span class="badge bg-danger">✗ Salida</span>';

                            else if (data === 'Traspaso') badge = '<span class="badge bg-primary">⇄ Traspaso</span>';

                            else if (data === 'Entrada/Salida') badge = '<span class="badge bg-warning">⇅ Entrada/Salida</span>';

                            else badge = '<span class="badge bg-secondary">' + data + '</span>';

                            return badge;

                        }

                    },

                    {

                        data: 'servicio',

                        title: 'Servicio',

                        render: d => '<strong>' + d + '</strong>'

                    },

                    {

                        data: 'cantidad_movimientos',

                        title: 'Movimientos',

                        render: d => '<span class="badge bg-info">' + d + ' mov.</span>'

                    },

                    {

                        data: null,

                        title: 'Monto',

                        render: function(data, type, row) {

                            // Soporte para mostrar monto convertido

                            let convertir = window.__mostrarConvertido || false;

                            let monto = parseFloat(row.monto_principal);

                            let tc = parseFloat(row.tipo_cambio || row.paridad || 0);

                            let moneda = row.moneda;

                            if (convertir && tc > 0) {

                                let convertido = monto * tc;

                                return `<span class="fw-bold text-primary">${convertido.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} MXN</span><br><small class="text-muted">${monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${moneda}</small>`;

                            } else {

                                return `<span class="fw-bold text-success">${monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${moneda}</span>`;

                            }

                        }

                    },

                    {

                        data: 'moneda',

                        title: 'Moneda',

                        render: d => {

                            let cls = d === 'USD' ? 'badge-usd' : 'badge-usdt';

                            return '<span class="badge ' + cls + '">' + d + '</span>';

                        }

                    },

                    {

                        data: null,

                        title: 'Involucrados',

                        render: function(data, type, row) {

                            let html = '<div style="font-size: 0.875rem;">';

                            if (row.cliente && row.cliente.trim() !== '') html += '<div><i class="ti ti-user text-primary"></i> <strong>Cliente:</strong> ' + row.cliente + '</div>';

                            if (row.proveedor && row.proveedor.trim() !== '') html += '<div><i class="ti ti-building text-info"></i> <strong>Proveedor:</strong> ' + row.proveedor + '</div>';

                            if (row.cuentas_origen && row.cuentas_origen.trim() !== '') {

                                let cuentas = row.cuentas_origen.split(',').filter(c => c.trim() !== '');

                                if (cuentas.length > 0) html += '<div><i class="ti ti-arrow-right text-danger"></i> <strong>Origen:</strong> ' + cuentas[0] + '</div>';

                            }

                            if (row.cuentas_destino && row.cuentas_destino.trim() !== '') {

                                let cuentas = row.cuentas_destino.split(',').filter(c => c.trim() !== '');

                                if (cuentas.length > 0) html += '<div><i class="ti ti-arrow-left text-success"></i> <strong>Destino:</strong> ' + cuentas[0] + '</div>';

                            }

                            html += '</div>';

                            return html || '<small class="text-muted">Sin datos</small>';

                        }

                    },

                    {

                        data: null,

                        title: 'Acciones',

                        orderable: false,

                        render: (data, type, row) => '<button class="btn btn-sm btn-primary btn-ver" data-id="' + row.id + '" data-grupo="' + row.grupo_id + '"><i class="ti ti-eye"></i> Ver</button>'

                    }

                ],

                language: {

                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'

                },

                pageLength: 25,

                order: [

                    [1, 'desc']

                ],

                scrollX: true

            });







            // Switch para alternar montos convertidos (actualiza celdas directamente)

            $('#switchConversion').on('change', function() {

                let convertir = $(this).is(':checked');

                table.rows().every(function() {

                    let row = this.data();

                    let monto = parseFloat(row.monto_principal);

                    let tc = parseFloat(row.tipo_cambio || row.paridad || 0);

                    let convertido = convertir && tc > 0 ? (monto * tc) : monto;

                    let display = convertir && tc > 0 ?

                        `<span class="fw-bold text-primary">${convertido.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} MXN</span><br><small class="text-muted">${monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${row.moneda}</small>` :

                        `<span class="fw-bold text-success">${monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${row.moneda}</span>`;

                    // Actualiza la celda de monto (6ta columna, índice 5)

                    $(this.node()).find('td:eq(5)').html(display);

                });

            });



            // Ver detalle

            $(document).on('click', '.btn-ver', function() {

                let movimiento_id = $(this).data('id');

                $.getJSON('api/flujo-detalle.php?id=' + movimiento_id, function(data) {

                    mostrarDetalle(data);

                    $('#modalDetalle').modal('show');

                }).fail(function() {

                    alert('Error al cargar el detalle del movimiento');

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

                                <div class="col-md-4"><strong>Grupo:</strong> <span class="badge bg-secondary">#${data.grupo_id}</span></div>

                                <div class="col-md-4"><strong>Fecha:</strong> ${data.fecha}</div>

                                <div class="col-md-4"><strong>Servicio:</strong> ${data.servicio}</div>

                            </div>

                        </div>

                    </div>

                    <div class="card border-0 mb-3">

                        <div class="card-header bg-secondary text-white">

                            <h6 class="mb-0"><i class="ti ti-list"></i> Movimientos</h6>

                        </div>

                        <div class="card-body p-0">

                            <table class="table table-sm table-hover mb-0">

                                <thead><tr><th>Tipo</th><th>Cuenta</th><th>Monto</th><th>Moneda</th><th>Concepto</th></tr></thead>

                                <tbody>`;



                if (data.movimientos && data.movimientos.length > 0) {

                    data.movimientos.forEach(m => {

                        let tipoNormalizado = m.tipo.charAt(0).toUpperCase() + m.tipo.slice(1);

                        let tipoClass = m.tipo === 'entrada' ? 'text-success' : 'text-danger';

                        let tipoIcon = m.tipo === 'entrada' ? '✓' : '✗';

                        let cuenta = m.cuenta_destino_nombre || m.cuenta_origen_nombre || '-';

                        let montoFormateado = parseFloat(m.monto).toLocaleString('es-MX', {

                            minimumFractionDigits: 0,

                            maximumFractionDigits: 0

                        });

                        html += `<tr>

                            <td class="${tipoClass}">${tipoIcon} ${tipoNormalizado}</td>

                            <td>${cuenta}</td>

                            <td class="fw-bold">${montoFormateado} ${m.moneda}</td>

                            <td><span class="badge ${m.moneda === 'USD' ? 'badge-usd' : 'badge-usdt'}">${m.moneda}</span></td>

                            <td>${m.descripcion || '-'}</td>

                        </tr>`;

                    });

                } else {

                    html += '<tr><td colspan="5" class="text-center text-muted">Sin movimientos</td></tr>';

                }



                html += '</tbody></table></div></div>';



                // Mostrar desglose de ganancias FIFO si existe

                if (data.consumos_fifo && data.consumos_fifo.length > 0) {

                    let gananciaTotal = parseFloat(data.ganancia_total || 0);

                    let gananciaClass = gananciaTotal >= 0 ? 'text-success' : 'text-danger';

                    let gananciaIcon = gananciaTotal >= 0 ? 'trending-up' : 'trending-down';



                    html += `

                    <div class="card border-0">

                        <div class="card-header ${gananciaTotal >= 0 ? 'bg-success' : 'bg-danger'} text-white">

                            <h6 class="mb-0">

                                <i class="ti ti-${gananciaIcon}"></i> Desglose de Ganancia (FIFO)

                                <span class="float-end fw-bold">

                                    Total: $${gananciaTotal.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})} MXN

                                </span>

                            </h6>

                        </div>

                        <div class="card-body p-0">

                            <table class="table table-sm table-hover mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th>Fecha Compra</th>

                                        <th class="text-center">Cantidad</th>

                                        <th class="text-end">TC Compra</th>

                                        <th class="text-end">TC Venta</th>

                                        <th class="text-end">Ganancia</th>

                                    </tr>

                                </thead>

                                <tbody>`;



                    data.consumos_fifo.forEach(c => {

                        let cantidad = parseFloat(c.cantidad_consumida);

                        let tcCompra = parseFloat(c.paridad_compra);

                        let tcVenta = parseFloat(c.paridad_venta);

                        let ganancia = parseFloat(c.ganancia);

                        let gananciaItemClass = ganancia >= 0 ? 'text-success' : 'text-danger';

                        let fechaCompra = c.fecha_compra ? c.fecha_compra.split(' ')[0] : 'N/A';



                        html += `<tr>

                            <td><small class="text-muted">${fechaCompra}</small></td>

                            <td class="text-center">${cantidad.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>

                            <td class="text-end">$${tcCompra.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>

                            <td class="text-end">$${tcVenta.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>

                            <td class="text-end fw-bold ${gananciaItemClass}">

                                ${ganancia >= 0 ? '+' : ''}$${ganancia.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}

                            </td>

                        </tr>`;

                    });



                    html += `</tbody>

                            </table>

                        </div>

                    </div>`;

                }



                $('#detalleContent').html(html);



                // Guardar el grupo_id para la anulación y estado

                $('#btnAnular').data('grupo', data.grupo_id);



                // Verificar estado del movimiento

                if (data.movimientos && data.movimientos.length > 0) {

                    let estado = data.movimientos[0].estado || 'activo';

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

            }



            // Handler para abrir modal de anulación

            $('#btnAnular').on('click', function() {

                let grupoId = $(this).data('grupo');

                if (!grupoId) {

                    showToast('Error: No se encontró el grupo de movimientos', 'error');

                    return;

                }

                $('#motivoAnulacion').val('');

                $('#modalDetalle').modal('hide');

                $('#modalAnular').modal('show');

            });



            // Confirmar anulación

            $('#btnConfirmarAnular').on('click', function() {

                let grupoId = $('#btnAnular').data('grupo');

                let motivo = $('#motivoAnulacion').val().trim();



                if (!motivo) {

                    showToast('Debes ingresar un motivo para la anulación', 'warning');

                    return;

                }



                // Deshabilitar botón mientras procesa

                let $btn = $(this);

                $btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Procesando...');



                $.ajax({

                    url: 'api/movimientos-correccion.php',

                    method: 'POST',

                    data: {

                        action: 'anular',

                        grupo_id: grupoId,

                        motivo: motivo

                    },

                    dataType: 'json',

                    success: function(res) {

                        if (res.success) {

                            $('#modalAnular').modal('hide');

                            table.ajax.reload();

                            cargarResumenes();

                            let msg = res.reversos_creados ?

                                'Grupo anulado correctamente. Se crearon ' + res.reversos_creados + ' reversos.' :

                                'Movimiento anulado correctamente.';

                            showToast(msg, 'success');

                        } else {

                            showToast(res.error || 'Error al anular', 'error');

                        }

                    },

                    error: function(xhr) {

                        showToast(xhr.responseJSON?.error || 'Error al anular', 'error');

                    },

                    complete: function() {

                        $btn.prop('disabled', false).html('<i class="ti ti-check"></i> Confirmar Anulación');

                    }

                });

            });



            // Cargar resúmenes

            function cargarResumenes() {

                let simbolo = monedaActual; // USD o USDT



                // SALDOS BANCARIOS

                $.getJSON('api/flujo-resumen.php?tipo=saldos&moneda=' + monedaActual, function(data) {

                    let html = '';

                    let total = 0;

                    let totalMxn = 0;

                    if (data.cuentas) {

                        data.cuentas.forEach(c => {

                            let saldo = parseFloat(c.saldo_actual || 0);

                            if (Math.abs(saldo) < 0.01) return; // omitir saldos en cero

                            let esMxn = c.tipo_moneda === 'MXN';

                            let mxn = esMxn ? saldo : parseFloat(c.valor_mxn || 0);

                            if (!esMxn) total += saldo;

                            totalMxn += mxn;

                            let saldoDisplay = esMxn

                                ? `$${formatearMXN(saldo)} MXN`

                                : `${formatoNumero(saldo)} ${simbolo}`;

                            html += `<tr>

                                <td>${c.nombre}${c.banco ? ' (' + c.banco + ')' : ''}</td>

                                <td><span class="badge bg-primary">${c.tipo_cuenta_nombre || 'N/A'}</span></td>

                                <td class="text-end fw-bold">${saldoDisplay}</td>

                                <td class="text-end text-muted">$${formatearMXN(mxn)}</td>

                            </tr>`;

                        });

                    }

                    if (!html) {

                        html = '<tr><td colspan="4" class="text-center text-muted py-3">No hay cuentas</td></tr>';

                    }

                    html += `<tr class="table-success fw-bold">

                        <td colspan="2">TOTAL</td>

                        <td class="text-end">${formatoNumero(total)} ${simbolo}</td>

                        <td class="text-end">$${formatearMXN(totalMxn)}</td>

                    </tr>`;

                    $('#saldosBody').html(html);

                });



                // CAPITAL

                $.getJSON('api/flujo-resumen.php?tipo=capital&moneda=' + monedaActual, function(data) {

                    let html = '';

                    let total = 0;

                    let totalMxn = 0;

                    if (data.cuentas) {

                        data.cuentas.forEach(c => {

                            let saldo = parseFloat(c.saldo_actual || 0);

                            if (Math.abs(saldo) < 0.01) return; // omitir saldos en cero

                            let esDivisa = c.tipo_moneda === simbolo;

                            let mxn = esDivisa

                                ? parseFloat(c.valor_mxn || 0)

                                : parseFloat(c.valor_mxn || saldo);

                            total += saldo;

                            totalMxn += mxn;

                            let saldoDisplay = esDivisa

                                ? `${formatoNumero(saldo)} ${simbolo}`

                                : `$${formatearMXN(saldo)} MXN`;

                            let mxnDisplay = mxn > 0 ? `$${formatearMXN(mxn)}` : '—';

                            let enlace = c.es_deuda_cliente && c.cliente_id

                                ? `<a href="detalle-cliente.php?id=${c.cliente_id}" class="text-decoration-none">${c.nombre}${c.banco ? ' (' + c.banco + ')' : ''}</a>`

                                : `${c.nombre}${c.banco ? ' (' + c.banco + ')' : ''}`;

                            html += `<tr>

                                <td>${enlace}</td>

                                <td><span class="badge bg-warning text-dark">${c.tipo_cuenta_nombre || 'Capital'}</span></td>

                                <td class="text-end fw-bold">${saldoDisplay}</td>

                                <td class="text-end text-muted">${mxnDisplay}</td>

                            </tr>`;

                        });

                    }

                    if (!html) {

                        html = '<tr><td colspan="4" class="text-center text-muted py-3">No hay fondos de capital</td></tr>';

                    }

                    html += `<tr class="table-warning fw-bold">

                        <td colspan="2">TOTAL</td>

                        <td class="text-end">${formatoNumero(total)} ${simbolo}</td>

                        <td class="text-end">$${formatearMXN(totalMxn)}</td>

                    </tr>`;

                    $('#capitalBody').html(html);

                });



                // PROVEEDOR (adeudos en MONTOS de divisa + valor MXN)

                $.getJSON('api/flujo-resumen.php?tipo=proveedor&moneda=' + monedaActual, function(data) {

                    let html = '';

                    let total = 0;

                    let totalMxn = 0;

                    if (data.proveedores) {

                        data.proveedores.forEach(p => {

                            let adeudo = parseFloat(p.saldo_actual || 0);

                            if (Math.abs(adeudo) < 0.01) return; // omitir saldos en cero

                            let mxn = parseFloat(p.valor_mxn || 0);

                            total += adeudo;

                            totalMxn += mxn;

                            let badgeClass = p.tipo_moneda === 'USD' ? 'badge-usd' : (p.tipo_moneda === 'MXN' ? 'badge-mxn' : 'badge-usdt');

                            html += `<tr>

                                <td>${p.nombre}</td>

                                <td><span class="badge ${badgeClass}">${p.tipo_moneda}</span></td>

                                <td class="text-end fw-bold">${formatoNumero(adeudo)} ${p.tipo_moneda}</td>

                                <td class="text-end text-muted">$${formatearMXN(mxn)}</td>

                            </tr>`;

                        });

                    }

                    if (!html) {

                        html = '<tr><td colspan="4" class="text-center text-muted py-3">Sin deudas pendientes</td></tr>';

                    }

                    html += `<tr class="table-info fw-bold">

                        <td colspan="2">TOTAL</td>

                        <td class="text-end">${formatoNumero(total)} ${simbolo}</td>

                        <td class="text-end">$${formatearMXN(totalMxn)}</td>

                    </tr>`;

                    $('#proveedorBody').html(html);

                });



                // DIFERENCIA

                cargarDiferencia();

            }



            function cargarDiferencia() {

                $.getJSON('api/flujo-resumen.php?tipo=diferencia&moneda=' + monedaActual, function(data) {

                    const diferencia = parseFloat(data.diferencia || 0);

                    const $card = $('#cardDiferencia');

                    const $cardBody = $card.find('.card-body');

                    const $monto = $('#diferenciaMonto');

                    const $texto = $('#diferenciaTexto');

                    const $icon = $('#diferenciaIcon');



                    // Diferencia siempre en MXN

                    $monto.text('$' + formatearMXN(diferencia) + ' MXN');



                    if (Math.abs(diferencia) < 0.01) {

                        $card.removeClass('border-danger border-warning').addClass('border-success');

                        $cardBody.css('background-color', 'rgba(25, 135, 84, 0.1)');

                        $monto.removeClass('text-danger text-warning').addClass('text-success');

                        $icon.css('background-color', 'rgba(25, 135, 84, 0.2)');

                        $texto.html('<i class="ti ti-check-circle"></i> <strong>Todo cuadra correctamente</strong>');

                    } else if (diferencia < 0) {

                        $card.removeClass('border-success border-warning').addClass('border-danger');

                        $cardBody.css('background-color', 'rgba(220, 53, 69, 0.1)');

                        $monto.removeClass('text-success text-warning').addClass('text-danger');

                        $icon.css('background-color', 'rgba(220, 53, 69, 0.2)');

                        $texto.html('<i class="ti ti-alert-triangle"></i> <strong>ALERTA:</strong> Faltan divisas');

                    } else {

                        $card.removeClass('border-success border-danger').addClass('border-warning');

                        $cardBody.css('background-color', 'rgba(255, 193, 7, 0.1)');

                        $monto.removeClass('text-success text-danger').addClass('text-warning');

                        $icon.css('background-color', 'rgba(255, 193, 7, 0.2)');

                        $texto.html('<i class="ti ti-info-circle"></i> <strong>ATENCIÓN:</strong> Sobran divisas');

                    }



                    // Fórmula: Saldos - (Capital + Proveedor) [todo en MXN]

                    const desglose = ` | Saldos Bancarios: $${formatearMXN(data.total_saldos || 0)} - (Capital: $${formatearMXN(data.total_capital || 0)} + Proveedores: $${formatearMXN(data.total_proveedor || 0)})`;

                    $texto.append(`<br><small class="text-muted">${desglose}</small>`);

                });

            }



            // Cargar clientes según moneda

            function cargarClientes(moneda) {

                let url = 'api/clientes.php?moneda=' + moneda;

                $.getJSON(url, function(data) {

                    let html = '<option value="">-- Sin cliente --</option>';

                    data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);

                    $('#clienteSelect').html(html);

                });

            }



            // Cargar proveedores según moneda

            function cargarProveedores(moneda) {

                $.getJSON('api/proveedores.php?tipo_moneda=' + moneda + '&activos=1', function(data) {

                    let html = '<option value="">-- Sin proveedor --</option>';

                    data.forEach(p => html += `<option value="${p.id}">${p.nombre}</option>`);

                    $('#proveedorSelect').html(html);

                });

            }



            // Cargar cuentas USD/USDT para Compra (wallet de divisas)

            function cargarCuentas(moneda) {

                $.getJSON('api/cuentas.php?tipo_moneda=' + moneda + '&es_caja_empresa=1', function(data) {

                    let html = '<option value="">-- Selecciona una cuenta --</option>';

                    data.forEach(c => {

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        html += `<option value="${c.id}">${nombreCuenta} (${formatoNumero(c.saldo_actual)} ${moneda})</option>`;

                    });

                    $('#cuentaSelect').html(html);

                    $('#cuentaLabel').text('Wallet / Cuenta de ' + moneda);

                });

            }



            // Cargar cuentas MXN para Venta (donde se recibe el pago del cliente)

            // Usa endpoint dedicado: solo bancarias (tipo 1) y caja (tipo 2) en MXN

            // NO comparte lógica con flujo.php para evitar efectos colaterales

            function cargarCuentasVenta() {

                $.getJSON('api/cuentas-venta-divisas.php', function(data) {

                    let html = '<option value="">-- Selecciona una cuenta --</option>';

                    data.forEach(c => {

                        let nombreCuenta = c.nombre;

                        if (c.banco && c.banco.trim()) {

                            nombreCuenta += ' (' + c.banco + ')';

                        }

                        html += `<option value="${c.id}">${nombreCuenta} (${formatearMXN(c.saldo_actual)} MXN)</option>`;

                    });

                    $('#cuentaSelect').html(html);

                    $('#cuentaLabel').text('Cuenta donde recibirás el pago (MXN)');

                });

            }



            // Evento de cambio de servicio

            $(document).on('change', '#servicioSelect', function() {

                let servicio = $(this).val();

                let $campos = $('#camposDinamicos');

                $campos.empty();

                $('#totalesGroup').hide();

                $('#clienteGroup, #cuentaGroup, #proveedorGroup').hide();



                if (!servicio) return;



                // Determinar moneda según servicio

                let moneda = servicio.includes('Dólares') ? 'USD' : 'USDT';

                let esCompra = servicio.includes('Compra');



                cargarClientes(moneda);

                cargarProveedores(moneda);

                if (esCompra) {

                    cargarCuentas(moneda);   // Wallet USD/USDT

                } else {

                    cargarCuentasVenta();    // Cuenta MXN (bancaria/caja)

                }



                // Mostrar campos según el servicio

                if (!esCompra) $('#clienteGroup').show();

                if (esCompra) $('#proveedorGroup').show();

                $('#cuentaGroup').show();



                // Campos dinámicos según operación

                if (esCompra) {

                    // Compra: compramos divisas al proveedor

                    $campos.html(`

                        <div class="mb-3">

                            <label class="form-label">Cantidad de ${moneda} <span class="text-muted">(monto a comprar)</span></label>

                            <input type="text" step="0.01" class="form-control input-cantidad" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">Tipo de Cambio <span class="text-muted">(solo para reportes)</span></label>

                            <input type="text" step="0.0001" class="form-control input-tc" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">Total MXN <span class="text-muted">(referencia)</span></label>

                            <input type="text" step="0.01" class="form-control input-total-mxn-editable" required>

                            <small class="text-muted">Ingresa el total o edita el TC para calcularlo automáticamente</small>

                        </div>

                    `);

                } else {

                    // Venta: recibimos MXN del cliente en nuestra cuenta

                    $campos.html(`

                        <div class="mb-3">

                            <label class="form-label">Cantidad de ${moneda} <span class="text-muted">(monto a vender al cliente)</span></label>

                            <input type="text" step="0.01" class="form-control input-cantidad" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">Tipo de Cambio <span class="text-muted">(precio por ${moneda} en MXN)</span></label>

                            <input type="text" step="0.0001" class="form-control input-tc" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-bold text-success">Total MXN a depositar en cuenta</label>

                            <div class="input-group">

                                <span class="input-group-text">$</span>

                                <input type="text" step="0.01" class="form-control input-total-mxn-editable fw-bold" required>

                                <span class="input-group-text">MXN</span>

                            </div>

                            <small class="text-success"><i class="ti ti-arrow-right"></i> Ingresa el total o edita TC para calcularlo automáticamente</small>

                        </div>

                    `);

                }



                // Calcular total MXN (bidireccional: cantidad×TC ↔ total)

                $(document).off('input', '.input-cantidad, .input-tc, .input-total-mxn-editable').on('input', '.input-cantidad, .input-tc, .input-total-mxn-editable', function(e) {

                    let $target  = $(e.target);

                    let cantidad = parseFloat($('.input-cantidad').val()) || 0;

                    let tc       = parseFloat($('.input-tc').val()) || 0;

                    let total    = parseFloat(($('.input-total-mxn-editable').val() || '').replace(/,/g, '')) || 0;

                    if ($target.hasClass('input-total-mxn-editable')) {

                        // Editaron el Total MXN → recalcular TC

                        if (cantidad > 0 && total > 0) {

                            tc = total / cantidad;

                            $('.input-tc').val(tc.toFixed(4));

                        }

                    } else {

                        // Editaron cantidad o TC → recalcular Total MXN

                        if (cantidad > 0 && tc > 0) {

                            total = cantidad * tc;

                            $('.input-total-mxn-editable').val(total.toFixed(2));

                        }

                    }

                });

            });



            // Submit del formulario

            $('#formFlujo').on('submit', function(e) {

                e.preventDefault();



                let formData = {

                    fecha: $('input[name="fecha"]').val(),

                    servicio: $('#servicioSelect').val(),

                    cliente_id: $('#clienteSelect').val(),

                    proveedor_id: $('#proveedorSelect').val(),

                    cuenta_id: $('#cuentaSelect').val(),

                    cantidad: $('.input-cantidad').val(),

                    tipo_cambio: $('.input-tc').val(),

                    total_mxn: ($('.input-total-mxn-editable').val() || '').replace(/,/g, '')

                };



                $.ajax({

                    url: 'api/flujo-divisas.php',

                    method: 'POST',

                    data: formData,

                    dataType: 'json',

                    success: function(res) {

                        if (res.success) {

                            $('#modalFlujo').modal('hide');

                            $('#formFlujo')[0].reset();

                            table.ajax.reload();

                            cargarResumenes();

                            showToast('Movimiento guardado correctamente', 'success');

                        } else {

                            showToast(res.error || 'Error al guardar', 'error');

                        }

                    },

                    error: function(xhr) {

                        showToast(xhr.responseJSON?.error || 'Error al guardar', 'error');

                    }

                });

            });



            // Limpiar formulario al abrir modal

            $('#modalFlujo').on('show.bs.modal', function() {

                $('#formFlujo')[0].reset();

                $('#camposDinamicos').empty();

                $('#totalesGroup, #clienteGroup, #cuentaGroup, #proveedorGroup').hide();

            });



            // Inicializar

            cargarResumenes();

        });

    </script>



</body>



</html>
