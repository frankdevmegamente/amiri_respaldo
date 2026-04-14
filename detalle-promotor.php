<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

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

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />

    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>

        .card-stats h3 { font-size: 1.8rem; font-weight: 700; }

        .table-salida { color: #fa896b !important; } /* Rojo para los pagos realizados */

        .table-entrada { color: #13deb9 !important; } /* Verde para comisiones ganadas */
 body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
    </style>

</head>



<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    
                    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4 mt-2">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Detalle de Comisiones</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a class="text-muted" href="promotores.php">Promotores</a></li>
                                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($promotor['nombre']) ?></li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3 text-end">
                                    <a href="promotores.php" class="btn btn-outline-primary">Volver</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Filtrar por:</label>
                                    <select class="form-select" id="tipoFiltro">
                                        <option value="mes">Mes</option>
                                        <option value="anio">Año</option>
                                        <option value="rango">Rango Personalizado</option>
                                        <option value="todo" selected>Todo el Historial</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="filtroMes" style="display:none;">
                                    <label class="form-label">Mes</label>
                                    <select class="form-select" id="selectMes">
                                        <option value="01">Enero</option>
                                        <option value="02">Febrero</option>
                                        <option value="03">Marzo</option>
                                        <option value="04">Abril</option>
                                        <option value="05">Mayo</option>
                                        <option value="06">Junio</option>
                                        <option value="07">Julio</option>
                                        <option value="08">Agosto</option>
                                        <option value="09">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="filtroAnio" style="display:none;">
                                    <label class="form-label">Año</label>
                                    <select class="form-select" id="selectAnio">
                                        <?php 
                                        $anioActual = date('Y');
                                        for($i = $anioActual; $i >= 2024; $i--): 
                                        ?>
                                            <option value="<?= $i ?>" <?= $i == $anioActual ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-2" id="filtroDesde" style="display:none;">
                                    <label class="form-label">Desde</label>
                                    <input type="date" class="form-control" id="fechaDesde">
                                </div>
                                <div class="col-md-2" id="filtroHasta" style="display:none;">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" class="form-control" id="fechaHasta" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" id="btnAplicarFiltro">
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

                    <div class="row card-stats">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-success border-4 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Comisiones Totales Generadas</h6>
                                    <h3 class="text-success" id="comisionesTotal">$0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-primary border-4 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Pagado al Promotor</h6>
                                    <h3 class="text-primary" id="comisionesPagadas">$0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-danger border-4 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Saldo Pendiente de Pago</h6>
                                    <h3 class="text-danger" id="comisionesPendientes">$0.00</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Historial de Comisiones y Pagos</h5>
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

                            <input type="number" step="0.01" class="form-control form-control-lg" name="monto_a_pagar" id="inputMontoPago" required>

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

        <script src="src/assets/js/dark-mode.js"></script>

        <script src="src/assets/js/pantalla-completa.js"></script>

        <script src="src/assets/js/utils.js"></script>



    <script>

        $(document).ready(function() {

            const promotorId = <?= $promotor_id ?>;

            let filtroActual = { tipo: 'todo' };



            // Manejar cambio de tipo de filtro

            $('#tipoFiltro').on('change', function() {

                const tipo = $(this).val();

                // Ocultar todos los filtros

                $('#filtroMes, #filtroAnio, #filtroDesde, #filtroHasta').hide();

                

                // Mostrar los filtros relevantes

                if (tipo === 'mes') {

                    $('#filtroMes, #filtroAnio').show();

                } else if (tipo === 'anio') {

                    $('#filtroAnio').show();

                } else if (tipo === 'rango') {

                    $('#filtroDesde, #filtroHasta').show();

                }

            });



            // Establecer mes actual por defecto

            $('#selectMes').val('<?= date('m') ?>');



            // Función para construir URL con filtros

            function construirURL() {

                let url = `api/comisiones-promotores.php?promotor_id=${promotorId}`;

                

                if (filtroActual.tipo === 'mes') {

                    url += `&mes=${filtroActual.mes}&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'anio') {

                    url += `&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'rango') {

                    url += `&fecha_desde=${filtroActual.desde}&fecha_hasta=${filtroActual.hasta}`;

                }

                

                return url;

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

                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }

            });



            // Aplicar filtro

            $('#btnAplicarFiltro').on('click', function() {

                const tipoFiltro = $('#tipoFiltro').val();

                

                if (tipoFiltro === 'mes') {

                    filtroActual = {

                        tipo: 'mes',

                        mes: $('#selectMes').val(),

                        anio: $('#selectAnio').val()

                    };

                } else if (tipoFiltro === 'anio') {

                    filtroActual = {

                        tipo: 'anio',

                        anio: $('#selectAnio').val()

                    };

                } else if (tipoFiltro === 'rango') {

                    const desde = $('#fechaDesde').val();

                    const hasta = $('#fechaHasta').val();

                    

                    if (!desde || !hasta) {

                        alert('Por favor selecciona ambas fechas');

                        return;

                    }

                    

                    filtroActual = {

                        tipo: 'rango',

                        desde: desde,

                        hasta: hasta

                    };

                } else {

                    filtroActual = { tipo: 'todo' };

                }

                

                // Recargar datos con nueva URL

                table.ajax.url(construirURL()).load();

                actualizarTotales();

            });



            // Generar PDF

            $('#btnGenerarPDF').on('click', function() {

                let url = `api/comisiones-promotores.php?promotor_id=${promotorId}&pdf=1`;

                

                if (filtroActual.tipo === 'mes') {

                    url += `&mes=${filtroActual.mes}&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'anio') {

                    url += `&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'rango') {

                    url += `&fecha_desde=${filtroActual.desde}&fecha_hasta=${filtroActual.hasta}`;

                }

                

                window.open(url, '_blank');

            });



            // 2. Función para actualizar los Cards de Totales

            function actualizarTotales() {

                let url = `api/comisiones-promotores.php?promotor_id=${promotorId}&totales=1`;

                

                if (filtroActual.tipo === 'mes') {

                    url += `&mes=${filtroActual.mes}&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'anio') {

                    url += `&anio=${filtroActual.anio}`;

                } else if (filtroActual.tipo === 'rango') {

                    url += `&fecha_desde=${filtroActual.desde}&fecha_hasta=${filtroActual.hasta}`;

                }

                

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

                        table.ajax.reload();

                        actualizarTotales();

                        alert("Pago registrado con éxito");

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
