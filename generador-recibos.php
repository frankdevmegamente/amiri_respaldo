<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
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
    <title>Calculo de ventas USDT a MXN - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        /* ESTILOS DEL TICKET */
        .ticket-wrapper {
    background-color: transparent; /* Quitamos el fondo gris para limpiar la vista */
    padding: 0; /* Eliminamos el espacio superior para que quede al nivel del formulario */
    display: flex;
    justify-content: center;
    align-items: flex-start; /* Asegura que la tarjeta se pegue a la parte superior */
}

        .ticket-card {
            background: #ffffff;
            width: 100%;
            max-width: 550px;
            border-radius: 15px;
            padding: 45px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.03);
            color: #2A3547;
            font-family: 'Manrope', sans-serif;
            position: relative;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 3px solid #5d87ff;
        }

        .ticket-title {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #5d87ff; /* <-- Color ajustado para que coincida con la línea */
            text-transform: uppercase;
        }

        .ticket-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px dashed #ebf1f6;
        }

        .commission-section {
            padding: 20px 0;
            border-bottom: 1px dashed #ebf1f6;
        }

        .text-muted-custom { 
            color: #7c8fac; 
            font-size: 13px; 
            text-transform: uppercase; 
            font-weight: 600; 
            margin-bottom: 8px;
            display: block;
        }

        .fs-large { font-size: 26px; font-weight: 700; color: #1a1a1a; }
        .text-primary-custom { color: #5d87ff; font-weight: 700; }
        .text-success-custom { color: #36c76c; font-weight: 800; font-size: 34px; }

        .ticket-row.total-row {
            background: #f8faff;
            border: none;
            border-radius: 12px;
            padding: 30px 25px;
            margin-top: 30px;
        }

        .badge-moneda.usdt {
            background-color: #e6fcf5;
            color: #20c997;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700;
        }

        /* =========================================
   🌙 REGLAS PARA MODO OSCURO EN EL TICKET
   ========================================= */
[data-bs-theme="dark"] .ticket-card {
    background: #1a2234; /* Fondo oscuro del dashboard */
    border-color: #2c344c;
    box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
}

[data-bs-theme="dark"] .fs-large {
    color: #ffffff !important; /* Números grandes en blanco */
}

[data-bs-theme="dark"] .ticket-row,
[data-bs-theme="dark"] .commission-section {
    border-bottom-color: #2c344c; /* Líneas punteadas oscuras */
}

[data-bs-theme="dark"] .ticket-row.total-row {
    background: #252f43; /* Fondo oscuro para la caja del total */
}

/* Forzar que los textos que tenían color oscuro fijo cambien a claro */
[data-bs-theme="dark"] .commission-section span,
[data-bs-theme="dark"] .ticket-card strong {
    color: #e9ecef !important; 
}

[data-bs-theme="dark"] .text-muted-custom {
    color: #8c9ea8; /* Gris más claro para que resalte en el fondo oscuro */
}

[data-bs-theme="dark"] .badge-moneda.usdt {
    background-color: rgba(32, 201, 151, 0.15); /* Fondo sutil para el badge USDT */
}

/* Fondo blanco para el logo en modo oscuro */
        /* .ticket-header img {
            background-color: #ffffff !important;
            padding: 8px 15px !important;
            border-radius: 10px !important;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.05);
        } */
        /* Intercambio de logos automático para Modo Oscuro */
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
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
                    
                    <div class="d-flex align-items-center mb-4 mt-2">
                        <h3 class="mb-0"><i class="ti ti-calculator"></i> Calculo de ventas USDT a MXN</h3>
                    </div>

                    <div class="row">
                        <div class="col-lg-5 col-md-5 mb-4">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold mb-4">Datos de Cotización</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Moneda</label>
                                        <select class="form-select" id="inputMoneda">
                                            <option value="USDT">USDT</option>
                                            <!-- <option value="USD">USD</option> -->
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cantidad</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" id="inputCantidad" value="">
                                            <span class="input-group-text bg-light text-muted fw-bold" id="labelMonedaAddon">USDT</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tipo de Cambio Base</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="number" step="0.01" class="form-control" id="inputTcBase" value="">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Comisión (%)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" id="inputMargen" value="">
                                            <span class="input-group-text bg-light">%</span>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">

                                    <div class="mb-3">
                                        <label class="form-label text-primary fw-bold">Tipo de Cambio Final</label>
                                        <input type="text" class="form-control text-primary fw-bold bg-primary bg-opacity-10 border-0" id="inputTcFinal" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-success fw-bold">Total a Recibir (MXN)</label>
                                        <input type="text" class="form-control text-success fw-bold fs-5 bg-success bg-opacity-10 border-0" id="inputTotal" readonly>
                                    </div>

                                    <button class="btn btn-primary w-100 mt-4 py-2 d-flex align-items-center justify-content-center fw-semibold shadow-sm" id="btnDescargar">
                                        <i class="ti ti-download me-2 fs-5"></i> Descargar Cotización
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 col-md-7">
                            <div class="ticket-wrapper">
                                <div class="ticket-card" id="vistaPreviaTicket">
                                    <div class="ticket-header">
                                        <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro" style="height: 45px; max-width: 100%; object-fit: contain;" />

<img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; max-width: 100%; object-fit: contain; display: none;" />
                                        <div class="ticket-title">CALCULO DE VENTA</div>
                                    </div>

                                    <div class="ticket-row">
                                        <div>
                                            <span class="text-muted-custom">CANTIDAD</span>
                                            <div class="fs-large" id="outCantidad">0.00</div>
                                        </div>
                                        <div>
                                            <span class="badge-moneda usdt" id="outMoneda">USDT</span>
                                        </div>
                                    </div>

                                    <div class="ticket-row">
                                        <div>
                                            <span class="text-muted-custom">TIPO DE CAMBIO</span>
                                            <div class="fs-large text-primary-custom">
                                                <span id="outTcFinalTxt">$0.00</span> <small style="font-size: 14px; color: #7c8fac;">MXN</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="commission-section">
                                        <span class="text-muted-custom">COMISIÓN</span>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size: 15px;">PORCENTAJE: <strong id="outMargenTxt">0.00%</strong></span>
                                            <!-- <span style="font-size: 18px; font-weight: 700;" id="outComisionMonto">$0.00 MXN</span> -->
                                        </div>
                                        <div style="font-size: 14px; color: #7c8fac;">
                                            TOTAL DE COMISIÓN: <span id="outComisionTotal" style="font-weight: 600; color: #2A3547;">$0.00 MXN</span>
                                        </div>
                                    </div>

                                    <div class="ticket-row total-row d-flex justify-content-between align-items-center">
                                        <div class="text-muted-custom" style="color: #5d87ff; margin-bottom: 0;">TOTAL A RECIBIR</div>
                                        <div class="text-success-custom" id="outTotalTxt">$0.00 MXN</div>
                                    </div>

                                    <div class="text-center mt-5">
                                        <div class="text-muted-custom mb-1" style="font-size: 11px;">GENERADO EL: <span id="outFechaHoy"></span></div>
                                        <div style="font-size: 11px; color: #a1b0cb; font-style: italic;">* Esta cotización tiene una vigencia de 30 minutos a partir de su emisión.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/pantalla-completa.js"></script>
    <script src="src/assets/js/utils.js"></script>

    <script>
        $(document).ready(function() {
            
            // <-- Función para actualizar la fecha y hora cada vez que se modifique o descargue
            function actualizarFechaYHora() {
                const fechaOpciones = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };
                $('#outFechaHoy').text(new Date().toLocaleString('es-MX', fechaOpciones).toUpperCase());
            }

            function calcularYActualizar() {
                const moneda = $('#inputMoneda').val();
                const cantidad = parseFloat($('#inputCantidad').val()) || 0;
                const tcBase = parseFloat($('#inputTcBase').val()) || 0;
                const margenPorcentaje = parseFloat($('#inputMargen').val()) || 0;

                $('#labelMonedaAddon').text(moneda);

                const subtotal = cantidad * tcBase;
const montoComision = subtotal * (margenPorcentaje / 100);
// Ahora sí sumamos el subtotal más la comisión
const totalMxn = subtotal + montoComision;

                const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
                const formatoNumero = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                $('#inputTcFinal').val('$' + tcBase.toFixed(2));
                $('#inputTotal').val(formatoMoneda.format(totalMxn) + ' MXN');

                $('#outMoneda').text(moneda);
                $('#outCantidad').text(formatoNumero.format(cantidad));
                $('#outTcFinalTxt').text('$' + formatoNumero.format(tcBase));
                $('#outMargenTxt').text(margenPorcentaje.toFixed(2) + '%');
                $('#outComisionMonto').text(formatoMoneda.format(montoComision) + ' MXN');
                $('#outComisionTotal').text(formatoMoneda.format(montoComision) + ' MXN');
                $('#outTotalTxt').text(formatoMoneda.format(totalMxn) + ' MXN');
                
                // Actualiza la hora visualmente mientras llenas el formulario
                actualizarFechaYHora(); 
            }

            $('#inputMoneda, #inputCantidad, #inputTcBase, #inputMargen').on('input change', calcularYActualizar);
            calcularYActualizar();

            $('#btnDescargar').on('click', function() {
                // Actualizamos la hora justo antes de tomar la foto para que sea exacta
                actualizarFechaYHora(); 
                
                const ticket = document.getElementById('vistaPreviaTicket');
                const btn = $(this);
                const textoOriginal = btn.html();
                btn.html('<i class="ti ti-loader ti-spin me-2"></i> Generando...');
                btn.prop('disabled', true);

                html2canvas(ticket, { backgroundColor: '#ffffff', scale: 3 }).then(canvas => {
                    const enlace = document.createElement('a');
                    enlace.download = `Cotizacion_${new Date().toISOString().slice(0,10)}.png`;
                    enlace.href = canvas.toDataURL("image/png");
                    enlace.click();
                    btn.html(textoOriginal).prop('disabled', false);
                });
            });
        });
    </script>
</body>
</html>
