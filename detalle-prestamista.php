<?php

require_once __DIR__ . '/src/config/session.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {

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

    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />

    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />

    <style>

        /* Opcional: Añadir un poco de espacio */

        .detalle-container {

            margin-top: 20px;

        }

    </style>

</head>

<style>
     body[data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        body[data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
</style>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"

        data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>



        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-user"></i> <?= htmlspecialchars($prestamista['nombre']) ?></h3>
                                <a href="prestamistas.php" class="btn btn-secondary ms-auto"><i class="ti ti-arrow-left"></i> Volver a Prestamistas</a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="ti ti-info-circle"></i> Información del Prestamista</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5><strong>• Nombre:</strong> <?= htmlspecialchars($prestamista['nombre']) ?></h5>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5><strong>• Teléfono:</strong> <?= htmlspecialchars($prestamista['telefono'] ?? 'N/A') ?></h5>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5><strong>• Email:</strong> <?= htmlspecialchars($prestamista['email'] ?? 'N/A') ?></h5>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5><strong>• Dirección:</strong> <?= htmlspecialchars($prestamista['direccion'] ?? 'N/A') ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0"><i class="ti ti-plus"></i> Agregar Nuevo Préstamo</h5>
                                </div>
                                <div class="card-body">
                                    <form id="formNuevoPrestamo">
                                        <input type="hidden" name="prestamista_id" value="<?= $prestamista['id'] ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    Monto
                                                    <span id="montoFormateadoNuevo" class="text-success fw-bold ms-2"></span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control" id="montoInputFormatted" name="monto_formatted" placeholder="0.00" required>
                                                    <input type="hidden" name="monto" id="montoInput" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
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
                                        </div>
                                        <input type="hidden" name="estado" value="pendiente">
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <label class="form-label">Descripción</label>
                                                <textarea class="form-control" name="descripcion" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-success"><i class="ti ti-plus"></i> Agregar Préstamo</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-4 table-responsive">
                                    <table id="tablaPrestamos" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Monto Pagado</th>
                                                <th>Fecha Pago</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            </tbody>
                                    </table>
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

                        <h5 class="modal-title">Pagar Préstamo</h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <form id="formEditarPrestamo" method="POST" action="">

                        <input type="hidden" name="_method" value="PUT">

                        <input type="hidden" id="editarId" name="id">

                        <input type="hidden" id="montoPagadoHidden">

                        <div class="modal-body">

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

                                    <label class="form-label">Cuenta de Pago *</label>

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

                                    <label class="form-label">

                                        Monto a Pagar *

                                        <span id="montoFormateadoPago" class="text-success fw-bold ms-2"></span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">$</span>

                                        <input type="text" class="form-control" id="montoPagarFormatted" name="monto_a_pagar_formatted" placeholder="0.00" required>

                                        <input type="hidden" name="monto_a_pagar" id="montoPagarHidden" required>

                                    </div>

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



                            <div class="alert alert-info" id="resumenPago" style="display: none;">

                                <h6>Resumen del Pago:</h6>

                                <p id="mensajePago"></p>

                            </div>

                        </div>



                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                            <button type="button" class="btn btn-primary" id="btnGuardarEdicion">Guardar Pago</button>

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

        <script src="src/assets/js/dark-mode.js"></script>

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

                columns: [{

                        data: 'id'

                    },

                    {
                        data: 'fecha',
                        title: 'Fecha',
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            
                            // 1. EXTRAEMOS LA FECHA EXACTA EN TEXTO (Ignorando las zonas horarias)
                            // Si data es "2026-03-31 15:30:00", lo cortamos a "2026-03-31"
                            const soloFecha = data.split(' ')[0]; 
                            // Lo separamos por guiones [2026, 03, 31]
                            const partes = soloFecha.split('-'); 
                            // Lo armamos al estilo latino: DD/MM/YYYY
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            let infoExtra = '';
                            
                            // 2. LA FECHA DE CAPTURA (Esa sí dejamos que use tu función normal)
                            if (row.creado_en) {
                                let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(row.creado_en) : row.creado_en;
                                infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            }
                            
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
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
                        data: 'fecha_pago',
                        title: 'Fecha Pago',
                        render: function(data, type, row) {
                            if (!data || data === '0000-00-00 00:00:00' || data === '0000-00-00') return '<span class="text-muted">N/A</span>';
                            
                            // 1. Extraemos la fecha exacta ignorando zonas horarias
                            const soloFecha = data.split(' ')[0]; 
                            const partes = soloFecha.split('-'); 
                            const fechaOperacion = `${partes[2]}/${partes[1]}/${partes[0]}`; 
                            
                            // 2. La fecha de captura exacta (Usamos el mismo 'data' porque ahí guardamos la hora)
                            let captura = typeof formatearFechaHora === 'function' ? formatearFechaHora(data) : data;
                            let infoExtra = `<br><small class="text-muted" style="font-size: 0.7rem;"><i class="ti ti-device-floppy"></i> Capturado: ${captura}</small>`;
                            
                            return `<span class="fw-bold text-dark">${fechaOperacion}</span>${infoExtra}`;
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
                order: [
                    [1, "desc"]
                ],
                // Agregar el idioma español
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });



            /* ============================================================

               MODAL - ABRIR Y CARGAR DATOS

            ============================================================ */



            $('#tablaPrestamos').on('click', '.pagar', function() {

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

                    $('#montoPagarFormatted').val("");

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



                    // Mostrar resumen actualizado con monto adicional 0 (porque aún no hay pago nuevo)

                    actualizarResumenPago(data.monto, data.monto_pagado, 0);



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



            /* ============================================================

               GUARDAR PAGO

            ============================================================ */



            $(document).on('click', '#btnGuardarEdicion', function() {



                console.log("CLICK EN GUARDAR PAGO"); // <-- ESTO DEBE SALIR SI TRABAJA

                console.log("Monto total:", $('#montoTotalDisplay').text());

                console.log("Monto pagado anterior (hidden):", $('#montoPagadoHidden').val());

                console.log("Monto a pagar ingresado:", $('#montoPagarFormatted').val());

                console.log("Cuenta de pago:", $('#cuentaPagoSelect').val());



                const id = $('#editarId').val();

                const montoAPagar = parseMoneda($('#montoPagarFormatted').val());

                const cuentaPagoId = $('#cuentaPagoSelect').val();

                // AQUÍ LE AGREGAMOS LA HORA AL PAGO
                const fechaElegida = $('#editarFechaPago').val();
                const horaActual = new Date().toTimeString().split(' ')[0];
                const fechaPagoCompletada = fechaElegida ? (fechaElegida + ' ' + horaActual) : '';



                if (montoAPagar <= 0) {

                    return alert("Ingresa un monto válido.");

                }



                if (!cuentaPagoId) {

                    return alert("Selecciona una cuenta de pago.");

                }



                const montoTotal = parseMoneda($('#montoTotalDisplay').text());

                const montoPagadoAnterior = parseMoneda($('#montoPagadoHidden').val() || 0);



                let nuevoMontoPagado = montoPagadoAnterior + montoAPagar;



                let nuevoEstado = "pendiente";

                if (nuevoMontoPagado >= montoTotal) {

                    nuevoEstado = "pagado";

                    nuevoMontoPagado = montoTotal;

                } else if (nuevoMontoPagado > 0) {

                    nuevoEstado = "parcial";

                }



                console.log("Enviando datos:", {

                    id,

                    monto_a_pagar: montoAPagar,

                    cuenta_pago_id: cuentaPagoId,

                    fecha_pago: fechaPagoCompletada

                });



                $.ajax({

                    url: 'api/prestamos-prestamistas.php',

                    method: 'PUT',

                    contentType: 'application/json',

                    data: JSON.stringify({

                        id: id,

                        monto_a_pagar: montoAPagar,

                        cuenta_pago_id: cuentaPagoId,

                        fecha_pago: fechaPagoCompletada

                    }),

                    success: function(response) {

                        console.log('Respuesta API:', response);

                        if (response.success) {

                            // Cerrar modal

                            $('#modalPagarPrestamo').modal('hide');



                            // Recargar DataTable

                            tablaPrestamos.ajax.reload(null, false); // false = mantener página actual

                            showToast("Pago registrado correctamente", "success");

                        }

                    },

                    error: function(xhr) {

                        console.error('ERROR AJAX:', xhr.responseJSON);

                        let msg = "Error al guardar";

                        try {

                            msg = xhr.responseJSON?.error || msg;

                        } catch (e) {}

                        showToast(msg, "error");

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

                // AQUÍ LE AGREGAMOS LA HORA AL NUEVO PRÉSTAMO
                const fechaElegida = $('input[name="fecha"]').val();
                const horaActual = new Date().toTimeString().split(' ')[0]; // Genera HH:MM:SS
                const fechaConHora = fechaElegida + ' ' + horaActual;

                $.post(

                    "api/prestamos-prestamistas.php", {

                        prestamista_id: $('input[name="prestamista_id"]').val(),

                        monto: parseMoneda($('#montoInput').val()),

                        fecha: fechaConHora,

                        cuenta_id: $('select[name="cuenta_id"]').val(),

                        estado: "pendiente",

                        descripcion: $('textarea[name="descripcion"]').val()

                    },

                    res => {

                        if (res.success) {

                            this.reset();

                            $('#montoFormateadoNuevo').text(''); // Limpiar formato visual

                            tablaPrestamos.ajax.reload();

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