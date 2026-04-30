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
        /* =========================================
           ESTILOS DEL TICKET (HÍBRIDO: ESCRITORIO CLÁSICO / MÓVIL MODERNO)
           ========================================= */
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        
        .ticket-wrapper {
            background-color: transparent; padding: 0; 
            display: flex; justify-content: center; align-items: flex-start;
        }

        /* DISEÑO BASE (ESCRITORIO - TU VERSIÓN ORIGINAL) */
        .ticket-card {
            background: #ffffff;
            width: 100%; max-width: 550px;
            border-radius: 0px;
            padding: 45px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.03);
            color: #2A3547;
            font-family: 'Manrope', sans-serif;
            position: relative;
        }

        .ticket-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; padding-bottom: 25px;
            border-bottom: 3px solid #5d87ff;
        }

        .ticket-title {
            font-size: 18px; font-weight: 600; letter-spacing: 1px;
            color: #5d87ff; text-transform: uppercase;
        }

        /* Contenedor Híbrido: En escritorio actúa como lista vertical */
        .responsive-grid { display: block; }
        
        /* En escritorio, cada item es una fila con línea punteada */
        .responsive-grid .grid-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 0; border-bottom: 1px dashed #ebf1f6;
        }

        .ticket-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 0; border-bottom: 1px dashed #ebf1f6;
        }

        .commission-section { padding: 20px 0; border-bottom: 1px dashed #ebf1f6; }

        .text-muted-custom { 
            color: #7c8fac; font-size: 13px; text-transform: uppercase; 
            font-weight: 600; margin-bottom: 8px; display: block;
        }

        .fs-large { font-size: 26px; font-weight: 700; color: #1a1a1a; }
        .text-primary-custom { color: #5d87ff; font-weight: 700; }
        .text-success-custom { color: #36c76c; font-weight: 800; font-size: 34px; }

        .ticket-row.total-row {
            background: #f8faff; border: none; border-radius: 12px;
            padding: 30px 25px; margin-top: 30px;
        }

        .badge-moneda { padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 12px; }
        .badge-moneda.usdt { background-color: #e6fcf5; color: #20c997; }
        .badge-moneda.mxn { background-color: #ebf1f6; color: #7c8fac; }

        /* =========================================
           🌙 REGLAS PARA MODO OSCURO (GLOBAL)
           ========================================= */
        [data-bs-theme="dark"] .ticket-card { background: #1a2234; border-color: #2c344c; box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3); }
        [data-bs-theme="dark"] .fs-large { color: #ffffff !important; }
        [data-bs-theme="dark"] .ticket-row,
        [data-bs-theme="dark"] .responsive-grid .grid-item,
        [data-bs-theme="dark"] .commission-section { border-bottom-color: #2c344c; }
        [data-bs-theme="dark"] .ticket-row.total-row { background: #252f43; }
        [data-bs-theme="dark"] .commission-section span,
        [data-bs-theme="dark"] .ticket-card strong { color: #e9ecef !important; }
        [data-bs-theme="dark"] .text-muted-custom { color: #8c9ea8; }
        [data-bs-theme="dark"] .badge-moneda.usdt { background-color: rgba(32, 201, 151, 0.15); }
        [data-bs-theme="dark"] .badge-moneda.mxn { background-color: rgba(255, 255, 255, 0.1); color: #e9ecef;}
        [data-bs-theme="dark"] #ticketObservaciones { background: #252f43 !important; border-color: #2c344c !important; }
        [data-bs-theme="dark"] #outObservacionesTxt { color: #e9ecef !important; }
        [data-bs-theme="dark"] .btn-check:checked + .btn-outline-primary { color: #ffffff !important; }
        [data-bs-theme="dark"] .btn-outline-primary { color: #799dfb; border-color: #5d87ff; }

        body[data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        body[data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* =========================================
           📱 MÓVIL (DISEÑO MODERNO Y COMPACTO TIPO APP)
           ========================================= */
        @media (max-width: 768px) {
            .ticket-card {
                padding: 20px 15px; /* Reducir márgenes */
                border-radius: 16px; /* Bordes redondeados modernos */
                border: 1px solid #ebf1f6;
            }
            [data-bs-theme="dark"] .ticket-card { border-color: #2c344c; }
            
            .ticket-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom-width: 2px; }
            .ticket-header img { height: 28px !important; }
            .ticket-title { font-size: 14px; }
            
            /* Aquí el Grid se rompe en 2 columnas para celular */
            .responsive-grid {
                display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
                margin-bottom: 15px;
            }
            
            /* Quitamos las líneas punteadas y el formato fila */
            .responsive-grid .grid-item { display: block; padding: 0; border-bottom: none; }
            .responsive-grid .grid-item > div:nth-child(2) { margin-top: 6px; } /* Separa la moneda hacia abajo */
            
            .commission-section { padding: 15px 0; margin-bottom: 5px; }
            .fs-large { font-size: 20px; }
            .badge-moneda { padding: 4px 10px; font-size: 11px; }
            .text-muted-custom { font-size: 11px; margin-bottom: 4px; }

            /* El Total se vuelve una tarjeta de color vibrante */
            .ticket-row.total-row {
                background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
                padding: 20px 15px; flex-direction: column; align-items: flex-start;
                gap: 5px; margin-top: 20px; border-radius: 12px;
            }
            .ticket-row.total-row .text-muted-custom { color: rgba(255,255,255,0.8) !important; }
            .ticket-row.total-row .text-success-custom { color: #ffffff !important; font-size: 26px; }

            /* Modo oscuro para la tarjeta del total en móvil */
            [data-bs-theme="dark"] .ticket-row.total-row {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                border: 1px solid #374151;
            }
        }
        
        /* =========================================
           📱 ESTILOS RESPONSIVOS PARA MÓVIL
           ========================================= */
        @media (max-width: 768px) {
            .ticket-card {
                padding: 25px 20px; /* Reducir padding en móvil */
                max-width: 100%; /* Ocupar todo el ancho disponible */
            }

            .ticket-header img {
                height: 35px !important; /* Logo más pequeño en móvil */
            }

            .ticket-title {
                font-size: 14px; /* Título más pequeño */
            }

            .fs-large {
                font-size: 22px; /* Números más pequeños */
            }

            .text-success-custom {
                font-size: 28px; /* Total más pequeño pero aún prominente */
            }

            .ticket-row {
                padding: 15px 0; /* Menos padding en filas */
            }

            .commission-section {
                padding: 15px 0;
            }

            .ticket-row.total-row {
                padding: 20px 15px; /* Ajustar padding del total */
                margin-top: 20px;
            }

            .observaciones-section {
                padding: 15px; /* Menos padding en observaciones */
            }

            /* Ajustar el contenedor del preview para que no se desface */
            .ticket-wrapper {
                padding: 0 10px; /* Pequeño padding lateral */
            }

            /* Asegurar que el total se vea bien alineado */
            .ticket-row.total-row .text-success-custom {
                text-align: right;
                word-break: break-word; /* Evitar que se rompa el texto */
            }
        }

        @media (max-width: 480px) {
            .ticket-card {
                padding: 20px 15px; /* Aún menos padding en móviles pequeños */
            }

            .ticket-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .ticket-header img {
                height: 30px !important; /* Logo aún más pequeño */
            }

            .fs-large {
                font-size: 20px;
            }

            .text-success-custom {
                font-size: 24px;
            }

            .ticket-row.total-row {
                padding: 15px 10px;
            }
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
                        <h3 class="mb-0"><i class="ti ti-calculator"></i> Calculo de ventas y compra USDT a MXN</h3>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-5 mb-4 order-1 order-lg-1">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold mb-4">Datos de Cotización</h5>
                                    
                                    <div class="mb-4">
                                        <div class="btn-group w-100 shadow-sm" role="group">
                                            <input type="radio" class="btn-check" name="tipoOperacion" id="opVenta" value="venta" checked autocomplete="off">
                                            <label class="btn btn-outline-primary fw-bold" for="opVenta"><i class="ti ti-arrow-up-right"></i> VENTA</label>

                                            <input type="radio" class="btn-check" name="tipoOperacion" id="opCompra" value="compra" autocomplete="off">
                                            <label class="btn btn-outline-primary fw-bold" for="opCompra"><i class="ti ti-arrow-down-left"></i> COMPRA</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Moneda</label>
                                        <select class="form-select" id="inputMoneda">
                                            <option value="USDT">USDT</option>
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
                                        <label class="form-label fw-semibold" id="lblTcBase">Tipo de Cambio Base</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="number" step="0.01" class="form-control" id="inputTcBase" value="">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3" id="grupoComisionInput">
                                        <label class="form-label fw-semibold">Comisión (%)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control" id="inputMargen" value="">
                                            <span class="input-group-text bg-light">%</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Observaciones (Opcional)</label>
                                        <textarea class="form-control" id="inputObservaciones" rows="2" placeholder="Ej. COTIZACIÓN CON DÓLARES..."></textarea>
                                    </div>
                                    
                                    <hr class="my-4">

                                    <div class="mb-3" id="grupoTcFinalInput">
                                        <label class="form-label text-primary fw-bold" id="lblTcFinal">Tipo de Cambio Final</label>
                                        <input type="text" class="form-control text-primary fw-bold bg-primary bg-opacity-10 border-0" id="inputTcFinal" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-success fw-bold" id="lblTotal">Total a Recibir (MXN)</label>
                                        <input type="text" class="form-control text-success fw-bold fs-5 bg-success bg-opacity-10 border-0" id="inputTotal" readonly>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-4 py-2 d-flex align-items-center justify-content-center fw-semibold shadow-sm" id="btnDescargar">
                                        <i class="ti ti-download me-2 fs-5"></i> Descargar Cotización
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7 order-2 order-lg-2">
                            <div class="ticket-wrapper">
                                <div class="ticket-card" id="vistaPreviaTicket" style="position: relative; overflow: hidden;">
                                    
                                    <div style="position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: #5D87FF; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: #13deb9; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

                                    <div class="ticket-header" style="position: relative; z-index: 1;">
                                        <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro" style="height: 45px; max-width: 100%; object-fit: contain;" />
                                        <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; max-width: 100%; object-fit: contain; display: none;" />
                                        <div class="ticket-title" id="outTicketTitle">CÁLCULO DE VENTA</div>
                                    </div>

                                    <div class="responsive-grid" style="position: relative; z-index: 1;">
                                        <div class="grid-item">
                                            <div>
                                                <span class="text-muted-custom">CANTIDAD</span>
                                                <div class="fs-large" id="outCantidad">0.00</div>
                                            </div>
                                            <div>
                                                <span class="badge-moneda usdt" id="outMoneda">USDT</span>
                                            </div>
                                        </div>

                                        <div class="grid-item">
                                            <div>
                                                <span class="text-muted-custom">TIPO DE CAMBIO</span>
                                                <div class="fs-large text-primary-custom">
                                                    <span id="outTcFinalTxt">$0.00</span> 
                                                </div>
                                            </div>
                                            <div>
                                                <span class="badge-moneda mxn">MXN</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="commission-section" id="ticketComision" style="position: relative; z-index: 1;">
                                        <span class="text-muted-custom">COMISIÓN</span>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size: 15px;">PORCENTAJE: <strong id="outMargenTxt">0.00%</strong></span>
                                        </div>
                                        <div style="font-size: 14px; color: #7c8fac;">
                                            TOTAL DE COMISIÓN: <span id="outComisionTotal" style="font-weight: 600; color: #2A3547;">$0.00 MXN</span>
                                        </div>
                                    </div>

                                    <div class="ticket-row total-row d-flex justify-content-between align-items-center" style="position: relative; z-index: 1;">
                                        <div class="text-muted-custom" style="color: #5d87ff; margin-bottom: 0;" id="outTotalLabel">TOTAL A RECIBIR</div>
                                        <div class="text-success-custom" id="outTotalTxt">$0.00 MXN</div>
                                    </div>

                                    <div class="observaciones-section mt-4" id="ticketObservaciones" style="display: none; background: rgba(93, 135, 255, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(93, 135, 255, 0.2); position: relative; z-index: 1;">
                                        <div class="text-primary-custom mb-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">OBSERVACIONES</div>
                                        <div id="outObservacionesTxt" style="font-size: 15px; color: #2A3547; white-space: pre-wrap; line-height: 1.5; font-weight: 500;"></div>
                                    </div>

                                    <div class="text-center mt-5" style="position: relative; z-index: 1;">
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
                const operacion = $('input[name="tipoOperacion"]:checked').val();
                const moneda = $('#inputMoneda').val();
                const cantidad = parseFloat($('#inputCantidad').val()) || 0;
                const tcBase = parseFloat($('#inputTcBase').val()) || 0;
                const margenPorcentaje = parseFloat($('#inputMargen').val()) || 0;
                const observaciones = $('#inputObservaciones').val().trim();

                const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
                const formatoNumero = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                // ⚡ ESTA LÍNEA DEBE IR AQUÍ (Fuera del if) para que siempre se vea en la preview
                $('#outCantidad').text(formatoNumero.format(cantidad));

                if (operacion === 'venta') {
                    // --- MODO VENTA ---
                    $('#grupoComisionInput').show();
                    $('#grupoTcFinalInput').show();
                    $('#ticketComision').show();
                    
                    $('#lblTcBase').text('Tipo de Cambio Base');
                    $('#outTicketTitle').text('CÁLCULO DE VENTA');
                    $('#labelMonedaAddon').text(moneda); 
                    $('#outMoneda').text(moneda);
                    $('#lblTotal').text('Total a Recibir (MXN)');
                    $('#outTotalLabel').text('TOTAL A RECIBIR');

                    const subtotal = cantidad * tcBase;
                    const montoComision = subtotal * (margenPorcentaje / 100);
                    const totalMxn = subtotal + montoComision;
                    const tcFinal = tcBase; // Mostrar exactamente el tipo de cambio base sin sumar margen

                    $('#inputTcFinal').val('$' + tcFinal.toFixed(6)); 
                    $('#inputTotal').val(formatoMoneda.format(totalMxn) + ' MXN');
                    
                    $('#outTcFinalTxt').text('$' + parseFloat(tcFinal.toFixed(6))); 
                    $('#outMargenTxt').text(parseFloat(margenPorcentaje.toFixed(2)) + '%');
                    $('#outComisionTotal').text(formatoMoneda.format(montoComision) + ' MXN');
                    $('#outTotalTxt').text(formatoMoneda.format(totalMxn) + ' MXN');

                } else {
                    // --- MODO COMPRA ---
                    $('#grupoComisionInput').hide();
                    $('#grupoTcFinalInput').hide();
                    $('#ticketComision').hide();
                    
                    $('#lblTcBase').text('Tipo de Cambio');
                    $('#outTicketTitle').text('CÁLCULO DE COMPRA');
                    
                    // Ajuste de unidades (Entran MXN en el input)
                    $('#labelMonedaAddon').text('MXN'); 
                    $('#outMoneda').text('MXN');
                    $('#lblTotal').text('Total a Entregar (' + moneda + ')');
                    $('#outTotalLabel').text('TOTAL A ENTREGAR');

                    // Lógica de DIVISIÓN
                    const resultDivisa = tcBase > 0 ? (cantidad / tcBase) : 0;

                    $('#inputTotal').val(formatoNumero.format(resultDivisa) + ' ' + moneda);
                    
                    $('#outTcFinalTxt').text('$' + parseFloat(tcBase.toFixed(6))); 
                    $('#outTotalTxt').text(formatoNumero.format(resultDivisa) + ' ' + moneda);
                }

                // Lógica de observaciones
                if (observaciones !== '') {
                    $('#ticketObservaciones').show();
                    $('#outObservacionesTxt').text(observaciones);
                } else {
                    $('#ticketObservaciones').hide();
                }

                actualizarFechaYHora(); 
            }

            // Detectar cambios en cualquier input o botón de opción
            $('#inputMoneda, #inputCantidad, #inputTcBase, #inputMargen, #inputObservaciones').on('input change', calcularYActualizar);
            $('input[name="tipoOperacion"]').on('change', calcularYActualizar);
            
            calcularYActualizar(); // Ejecutar al cargar

            $('#btnDescargar').on('click', function() {
                // Actualizamos la hora justo antes de tomar la foto para que sea exacta
                actualizarFechaYHora(); 
                
                const ticket = document.getElementById('vistaPreviaTicket');
                const btn = $(this);
                const textoOriginal = btn.html();
                btn.html('<i class="ti ti-loader ti-spin me-2"></i> Generando...');
                btn.prop('disabled', true);

                // AQUI ESTÁ LA MAGIA: backgroundColor: null
                html2canvas(ticket, { backgroundColor: null, scale: 3 }).then(canvas => {
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
