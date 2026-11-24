<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {

    header('Location: login.php');

    exit;

}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';



// Obtener el ID del promotor de la URL

$promotor_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);



if (!$promotor_id) {

    die("Error: ID de promotor no válido.");

}



require_once __DIR__ . '/src/config/db.php';



// Función para obtener datos del promotor

function getPromotor($pdo, $id)

{

    $stmt = $pdo->prepare("

        SELECT p.*, cu.nombre as cuenta_nombre, cu.saldo_actual as cuenta_saldo

        FROM promotores p

        LEFT JOIN cuentas cu ON cu.id = p.cuenta_id

        WHERE p.id = ?

    ");

    $stmt->execute([$id]);

    return $stmt->fetch();

}



// Obtener cuentas de la empresa (solo cajas de empresa con saldo positivo)

function getCuentasEmpresa($pdo)

{

    $stmt = $pdo->query("SELECT id, nombre, banco, saldo_actual, tipo_moneda FROM cuentas WHERE activo = 1 AND es_caja_empresa = 1 AND COALESCE(saldo_actual,0) > 0 ORDER BY nombre");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}



try {

    $pdo = getPDO();

    $promotor = getPromotor($pdo, $promotor_id);



    if (!$promotor) {

        die("Error: Promotor no encontrado.");

    }



    $cuentasEmpresa = getCuentasEmpresa($pdo);

} catch (PDOException $e) {

    die("Error de conexión: " . $e->getMessage());

}

?>



<!DOCTYPE html>

<html lang="es">



<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Detalles de Comisiones</title>

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
        
        #tabsPromotor .nav-link {
            color: var(--nx-text-muted);
        }

        #tabsPromotor .nav-link.active {
            color: var(--nx-primary); /* Morado Amiri */
            background-color: transparent;
            border-bottom: 2px solid var(--nx-primary);
            font-weight: 600;
        }
        #tabsPromotor .nav-link:not(.active):hover {
            color: var(--nx-primary); /* Morado Amiri al pasar el mouse */
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
            background-color: rgba(197, 143, 184, 0.12) !important;
        }

        [data-bs-theme="dark"] .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.12) !important;
        }

        [data-bs-theme="dark"] .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.12) !important;
        }
    </style>

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
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
                                            <h2 class="mb-0"><?= htmlspecialchars($promotor['nombre']) ?></h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Detalle del promotor</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <a href="promotores.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left"></i> Volver a Promotores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <div class="row align-items-end">
                                        <div class="col-md-2">
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
                                            <button class="btn btn-primary w-100" id="btnFiltrar">
                                                <i class="ti ti-filter"></i> Aplicar
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-danger w-100" id="btnGenerarPDF">
                                                <i class="ti ti-file-text"></i> Generar PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de Totales -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-success border-4 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-cash fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Comisiones Totales Generadas</p>
                                            <h4 class="text-success fw-bolder mb-0" id="comisionesTotal">$0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-primary border-4 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-check fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Pagado al Promotor</p>
                                            <h4 class="text-primary fw-bolder mb-0" id="comisionesPagadas">$0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 rounded-4 border-start border-danger border-4 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="ti ti-clock fs-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Saldo Pendiente de Pago</p>
                                            <h4 class="text-danger fw-bolder mb-0" id="comisionesPendientes">$0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Comisiones -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-4 px-4">
                                    <h5 class="mb-0 fw-semibold"><i class="ti ti-list-details me-2"></i>Historial de Comisiones y Pagos</h5>
                                    <button class="btn btn-primary" id="btnPagarComisiones">
                                        <i class="ti ti-cash"></i> Registrar Pago
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table id="tablaComisiones" class="table table-hover align-middle" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Descripción / Servicio</th>
                                                    <th>Tipo</th>
                                                    <th>Monto</th>
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



    <div class="modal fade" id="modalPagarComisiones" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">Registrar Pago de Comisiones</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <form id="formPagarComisiones">

                    <input type="hidden" name="promotor_id" value="<?= $promotor_id ?>">

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label text-danger fw-bold">Saldo Pendiente: <span id="labelPendiente">$0.00</span></label>

                        </div>

                        

                        <div class="mb-3">

                            <label class="form-label">Cuenta de Origen (Nuestra Caja/Banco) *</label>

                            <select class="form-select" name="cuenta_pago_id" id="cuentaPagoSelectPromotor" required>

                                <option value="">Seleccione cuenta...</option>

                                <?php foreach ($cuentasEmpresa as $c): 

                                    $moneda = $c['tipo_moneda'] ?? 'MXN';

                                    $simbolo = $moneda === 'MXN' ? '$' : ($moneda . ' ');

                                    $saldo = number_format($c['saldo_actual'], 2);

                                    $nombreCuenta = htmlspecialchars($c['nombre']);

                                    if (!empty($c['banco'])) {

                                        $nombreCuenta .= ' (' . htmlspecialchars($c['banco']) . ')';

                                    }

                                ?>

                                    <option value="<?= $c['id'] ?>"><?= $nombreCuenta ?> (Saldo: <?= $simbolo ?><?= $saldo ?>)</option>

                                <?php endforeach; ?>

                            </select>

                        <div class="mb-3">

                            <label class="form-label">

                                Monto a Pagar (MXN)

                                <span id="montoFormateado" class="text-success fw-bold ms-2"></span>

                            </label>

                            <input type="text" step="0.01" class="form-control form-control-lg" name="monto_a_pagar" id="inputMontoPago" required>

                            <div class="mt-2">

                                <button type="button" class="btn btn-success btn-sm" id="btnLiquidarDeudaPromotor" style="display: none;">

                                    <i class="ti ti-cash"></i> Liquidar Deuda Total

                                </button>

                            </div>

                        </div>

                        

                        <div class="mb-3">

                            <label class="form-label">Fecha de Pago</label>

                            <input type="date" class="form-control" name="fecha_pago" value="<?= date('Y-m-d') ?>">

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>

                        <button type="submit" class="btn btn-primary">Confirmar Pago</button>

                    </div>

                </form>

            </div>

        </div>

    </div>



        <!-- Scripts -->

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <script src="src/assets/js/sidebarmenu.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js      "></script>

        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js      "></script>

        <script src="src/assets/js/pantalla-completa.js"></script>

        <script src="src/assets/js/utils.js"></script>



    <script>

        $(document).ready(function() {

            const promotorId = <?= $promotor_id ?>;





            // ─── Period Filter ──────────────────────────────────
            function getParams() {
                const periodo = $('#selectPeriodo').val();
                if (periodo === 'todo') return 'periodo=todo';
                if (periodo === 'mes_pasado') {
                    const d = new Date();
                    d.setMonth(d.getMonth() - 1);
                    const anio = d.getFullYear();
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const dias = new Date(anio, d.getMonth() + 1, 0).getDate();
                    return 'periodo=rango&fecha_inicio=' + anio + '-' + mes + '-01&fecha_fin=' + anio + '-' + mes + '-' + String(dias).padStart(2, '0');
                }
                if (periodo === 'rango') {
                    return 'periodo=rango&fecha_inicio=' + $('#fechaInicio').val() + '&fecha_fin=' + $('#fechaFin').val();
                }
                return 'periodo=' + periodo;
            }

            function construirURL() {
                return `api/comisiones-promotores.php?promotor_id=${promotorId}&${getParams()}`;
            }



            // 1. Inicializar DataTable

            const table = $('#tablaComisiones').DataTable({

                ajax: {

                    url: construirURL(),

                    dataSrc: ""

                },

                columns: [
                    {
                        data: 'fecha',
                        title: 'Fecha',
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            if (type === 'sort' || type === 'type') return data;
                            
                            // Fecha de la operación principal (sin la zona horaria que resta un día)
                            const soloFecha = data.split(' ')[0]; 
                            const partes = soloFecha.split('-'); 
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            let infoExtra = '';
                            
                            // Fecha real de cuando lo capturaste en el sistema
                            if (row.creado_en) {
                                let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                                infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            }
                            
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
                        }
                    },

                    { 

                        data: null,

                        render: function(data) {

                            return `<strong>${data.servicio || 'Pago'}</strong><br><small class="text-muted">${data.descripcion}</small>`;

                        }

                    },

                    { 

                        data: 'tipo',

                        render: function(tipo) {

                            return tipo === 'comision' 

                                ? '<span class="badge bg-light-success text-success">Comisión Ganada</span>' 

                                : '<span class="badge bg-light-danger text-danger">Pago Realizado</span>';

                        }

                    },

                    { 

                        data: 'comision',

                        render: function(monto, type, row) {

                            const clase = row.tipo === 'comision' ? 'text-success' : 'text-danger';

                            const prefijo = row.tipo === 'comision' ? '+' : '-';

                            return `<span class="fw-bold ${clase}">${prefijo}${formatoMoneda(monto)}</span>`;

                        }

                    }

                ],

                order: [[0, 'desc']],

                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }

            });



            // ─── Period events ────────────────────────────────
            $('#selectPeriodo').on('change', function() {
                if ($(this).val() === 'rango') {
                    $('#fechaInicioGroup, #fechaFinGroup').show();
                } else {
                    $('#fechaInicioGroup, #fechaFinGroup').hide();
                    table.ajax.url(construirURL()).load();
                    actualizarTotales();
                }
            });

            // Aplicar filtro

            $('#btnFiltrar').on('click', function() {
                const params = getParams();
                if ($('#selectPeriodo').val() === 'rango') {
                    if (!$('#fechaInicio').val() || !$('#fechaFin').val()) {
                        alert('Por favor selecciona ambas fechas');
                        return;
                    }
                }
                table.ajax.url(construirURL()).load();
                actualizarTotales();
            });

            // Generar PDF

            $('#btnGenerarPDF').on('click', function() {
                let url = `api/comisiones-promotores.php?promotor_id=${promotorId}&pdf=1&${getParams()}`;
                window.open(url, '_blank');
            });

            // 2. Función para actualizar los Cards de Totales

            function actualizarTotales() {
                let url = `api/comisiones-promotores.php?promotor_id=${promotorId}&totales=1&${getParams()}`;

                

                $.get(url, function(data) {
                    $('#comisionesTotal').text(formatoMoneda(data.total_comisiones));
                    $('#comisionesPagadas').text(formatoMoneda(data.total_pagado));
                    $('#comisionesPendientes').text(formatoMoneda(data.total_pendiente));
                    
                    // Actualizar label en el modal
                    $('#labelPendiente').text(formatoMoneda(data.total_pendiente));
                    
                    // ACÁ MATAMOS AL FANTASMA: Obligamos al 'max' a tener máximo 2 decimales
                    $('#inputMontoPago').attr('max', parseFloat(data.total_pendiente).toFixed(2));
                    
                    // Deshabilitar botón si no hay deuda
                    $('#btnPagarComisiones').prop('disabled', parseFloat(data.total_pendiente) <= 0.001);
                });

            }



            actualizarTotales();



            // 3. Abrir Modal

            $('#btnPagarComisiones').click(function() {

                $('#inputMontoPago').val(''); // Limpiar campo

                $('#montoFormateado').text(''); // Limpiar formato

                $('#cuentaPagoSelectPromotor').val(''); // Limpiar cuenta

                $('#btnLiquidarDeudaPromotor').hide(); // Ocultar botón

                $('#modalPagarComisiones').modal('show');

            });



            // Escuchar cambios en selector de cuenta para mostrar/ocultar botón de liquidar

            $('#cuentaPagoSelectPromotor').on('change', function() {

                const selectedOption = $(this).find('option:selected');

                const cuentaTexto = selectedOption.text();

                const pendiente = parseFloat($('#labelPendiente').text().replace(/[^0-9.-]+/g, '')) || 0;

                

                // Extraer saldo de la cuenta del texto "(Saldo: $1,234.56)"

                const matchSaldo = cuentaTexto.match(/Saldo:\s*\$([\d,]+\.\d{2})/);

                

                if (matchSaldo) {

                    const saldoCuenta = parseFloat(matchSaldo[1].replace(/,/g, ''));

                    

                    // Mostrar botón solo si la cuenta tiene saldo suficiente

                    if (saldoCuenta >= pendiente && pendiente > 0) {

                        $('#btnLiquidarDeudaPromotor').show();

                    } else {

                        $('#btnLiquidarDeudaPromotor').hide();

                    }

                } else {

                    $('#btnLiquidarDeudaPromotor').hide();

                }

            });



            // Evento clic en botón Liquidar Deuda Total

            $('#btnLiquidarDeudaPromotor').on('click', function() {

                const pendiente = parseFloat($('#labelPendiente').text().replace(/[^0-9.-]+/g, '')) || 0;

                

                // Autocompletar el input con la deuda total

                $('#inputMontoPago').val(pendiente.toFixed(2));

                

                // Actualizar formato visual

                const formato = new Intl.NumberFormat('es-MX', {

                    style: 'currency',

                    currency: 'MXN',

                    minimumFractionDigits: 2,

                    maximumFractionDigits: 2

                }).format(pendiente);

                $('#montoFormateado').text('= ' + formato);

            });



            // Formatear monto en tiempo real como guía visual

            $('#inputMontoPago').on('input', function() {

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



            // 4. Enviar Pago (PUT)

            $('#formPagarComisiones').submit(function(e) {

                e.preventDefault();

                const formData = {

                    promotor_id: promotorId,

                    monto_a_pagar: $('#inputMontoPago').val(),

                    cuenta_pago_id: $('select[name="cuenta_pago_id"]').val(),

                    fecha_pago: $('input[name="fecha_pago"]').val()

                };



                $.ajax({

                    url: 'api/comisiones-promotores.php',

                    method: 'PUT',

                    contentType: 'application/json',

                    data: JSON.stringify(formData),

                    success: function(response) {
                        $('#modalPagarComisiones').modal('hide');
                        alert("Pago registrado con éxito. Redirigiendo a Flujo...");
                        setTimeout(() => { window.location.href = 'flujo.php'; }, 1500);
                    },

                    error: function(xhr) {

                        alert(xhr.responseJSON?.error || "Error al procesar el pago");

                    }

                });

            });



            function formatoMoneda(valor) {

                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);

            }

        });

    </script>

</body>

</html>
