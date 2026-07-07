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
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
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
            border-bottom: 3px solid #6c5ce7;
        }

        .ticket-title {
            font-size: 18px; font-weight: 600; letter-spacing: 1px;
            color: #6c5ce7; text-transform: uppercase;
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
        .text-primary-custom { color: #6c5ce7; font-weight: 700; }
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
        [data-bs-theme="dark"] .btn-outline-primary { color: #799dfb; border-color: #6c5ce7; }

        body[data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        body[data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* =========================================
           📱 MÓVIL (IDÉNTICO A ESCRITORIO, VERSIÓN MINIATURA)
           ========================================= */
        @media (max-width: 768px) {
            .ticket-card {
                padding: 20px; /* Menos espacio en los bordes para que quepa más info */
                max-width: 100%;
            }

            .ticket-header {
                margin-bottom: 10px;
                padding-bottom: 10px;
            }

            .ticket-header img {
                height: 28px !important; /* Logo mini */
            }

            .ticket-title {
                font-size: 11px;
            }

            .ticket-wrapper {
                padding: 0 10px;
            }

            .ticket-row {
                padding: 15px 0;
            }

            .commission-section {
                padding: 15px 0;
            }

            /* 1. FORZAMOS QUE TODO SIGA EN HORIZONTAL COMO EN PC */
            .responsive-grid .grid-item {
                display: flex !important; 
                flex-direction: row !important;
                justify-content: space-between !important; 
                align-items: center !important;
                padding: 10px 0 !important;
            }

            .responsive-grid .grid-item > div:nth-child(1) { text-align: left; }
            .responsive-grid .grid-item > div:nth-child(2) { margin-top: 0 !important; }

            .commission-section {
                padding: 10px 0;
            }

            /* 2. ACHICAMOS TEXTOS PARA EVITAR QUE BRINQUEN DE RENGLÓN */
            .text-muted-custom {
                font-size: 9px; 
                margin-bottom: 2px;
            }

            .fs-large {
                font-size: 16px; /* Números más compactos */
            }

            .badge-moneda {
                padding: 4px 8px;
                font-size: 9px;
            }

            .commission-section span, 
            .commission-section strong {
                font-size: 11px !important;
            }

            #outComisionTotal {
                font-size: 12px !important;
            }

            /* 3. FILA DE TOTAL IDÉNTICA A ESCRITORIO (Sin gradientes) */
            .ticket-row.total-row {
                padding: 12px 15px !important;
                margin-top: 15px !important;
                flex-direction: row !important; /* Obligamos a que estén lado a lado */
                justify-content: space-between !important;
                align-items: center !important;
                background: #f8faff !important; /* Fondo gris claro original */
                border: none !important;
            }

            [data-bs-theme="dark"] .ticket-row.total-row {
                background: #252f43 !important; /* Fondo oscuro original */
            }

            .ticket-row.total-row .text-muted-custom { 
                color: #6c5ce7 !important; 
                font-size: 10px !important;
            }
            
            .ticket-row.total-row .text-success-custom { 
                color: #36c76c !important; 
                font-size: 14px !important;
                text-align: right;
                word-break: break-word;
            }

            /* 4. COMPACTAMOS LAS OBSERVACIONES AL MÁXIMO */
            .observaciones-section {
                padding: 10px !important;
                margin-top: 10px !important;
            }

            .observaciones-section .text-primary-custom {
                font-size: 9px !important;
                margin-bottom: 2px !important;
            }

            #outObservacionesTxt {
                font-size: 10px !important; /* Letra pequeñita para que quepa mucho texto */
                line-height: 1.1 !important; /* Juntamos los renglones */
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
                height: 24px !important; /* Logo aún más pequeño */
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
                                            <input type="text" step="0.01" class="form-control" id="inputCantidad" value="">
                                            <span class="input-group-text bg-light text-muted fw-bold" id="labelMonedaAddon">USDT</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" id="lblTcBase">Tipo de Cambio Base</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="text" step="0.01" class="form-control" id="inputTcBase" value="">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3" id="grupoComisionInput">
                                        <label class="form-label fw-semibold">Comisión (%)</label>
                                        <div class="input-group">
                                            <input type="text" step="0.01" class="form-control" id="inputMargen" value="">
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
                                        <div class="text-muted-custom" style="color: #6c5ce7; margin-bottom: 0;" id="outTotalLabel">TOTAL A RECIBIR</div>
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
