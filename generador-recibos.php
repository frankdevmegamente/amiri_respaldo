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
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        /* =========================================
           HELIOS DARK GLASS - CALCULO DE VENTA TICKET
           ========================================= */
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }

        .ticket-wrapper {
            background-color: transparent; padding: 0;
            display: flex; justify-content: center; align-items: flex-start;
        }

        /* Page background glow */
        .ticket-card {
            position: relative;
            width: 100%; max-width: 420px;
            border-radius: 24px;
            padding: 22px 20px;
            background: linear-gradient(160deg, #3d3539 0%, #2a2427 100%);
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
            color: #fff;
            font-family: 'Montserrat', 'Poppins', sans-serif;
            overflow: hidden;
        }

        /* Top-right mauve glow */
        .ticket-card::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(197,143,184,0.25) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Bottom-left green glow */
        .ticket-card::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(61,220,132,0.12) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .ticket-card > * { position: relative; z-index: 1; }

        /* Clean export - hide glows that cause black boxes in PNG */
        .ticket-card.exporting::before,
        .ticket-card.exporting::after { display: none !important; }

        /* Header */
        .ticket-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 16px; padding-bottom: 14px;
            border-bottom: none;
            position: relative;
        }
        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, #5a4552, #d9a3cc, transparent);
        }

        .ticket-header img { height: 40px; max-width: 100%; object-fit: contain; }

        .ticket-title {
            font-size: 13px; font-weight: 500; letter-spacing: 1.5px;
            color: #d9a3cc; text-transform: uppercase;
        }

        /* Cards (sections) */
        .ticket-section {
            background: linear-gradient(160deg, #121012 0%, #0b0b0c 100%);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 16px 18px;
            margin-bottom: 12px;
            /* solid translucent fallback (no backdrop-filter for export compat) */
            background: linear-gradient(160deg, #141214 0%, #0e0c0e 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04), 0 4px 16px rgba(0,0,0,0.3);
        }

        .text-muted-custom {
            color: #9a9096; font-size: 10px; text-transform: uppercase;
            font-weight: 600; margin-bottom: 4px; display: block; letter-spacing: 0.5px;
        }

        .fs-large { font-size: 28px; font-weight: 700; color: #fff; line-height: 1.1; }
        .text-primary-custom { color: #c58fb8; font-weight: 700; }
        .text-success-custom { color: #3ddc84; font-weight: 800; font-size: 32px; line-height: 1.1; }

        /* Grid items (cantidad, tipo de cambio) */
        .responsive-grid { display: block; }
        .responsive-grid .grid-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0;
        }
        .responsive-grid .grid-item > div:nth-child(1) { text-align: left; }
        .responsive-grid .grid-item > div:nth-child(2) { margin-top: 0 !important; }

        .badge-moneda {
            padding: 6px 14px; border-radius: 999px; font-weight: 700; font-size: 11px;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .badge-moneda.usdt { background-color: rgba(61,220,132,0.12); color: #3ddc84; border-color: rgba(61,220,132,0.3); }
        .badge-moneda.mxn { background-color: #1a171a; color: #fff; border-color: rgba(255,255,255,0.12); }

        /* Commission section */
        .commission-section { border: none; }
        .commission-section span { color: #fff; font-size: 14px; }
        .commission-section strong { color: #fff; }

        /* Total row (highlighted) */
        .ticket-row.total-row {
            background: linear-gradient(135deg, #0b0b0c 0%, #1a1216 100%);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 18px;
            margin-top: 12px;
            position: relative;
            overflow: hidden;
        }
        .ticket-row.total-row::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(197,143,184,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .ticket-row.total-row .text-muted-custom { color: #d9a3cc; margin-bottom: 0; }
        .ticket-row.total-row .text-success-custom {
            color: #3ddc84;
            text-shadow: 0 0 20px rgba(61,220,132,0.25);
        }

        /* Observaciones */
        .observaciones-section {
            background: rgba(197,143,184,0.06);
            padding: 16px; border-radius: 16px;
            border: 1px solid rgba(197,143,184,0.2);
            margin-top: 12px;
        }
        .observaciones-section .text-primary-custom { color: #d9a3cc; }

        /* Footer note */
        .ticket-footer-note {
            font-size: 11px; color: #9a9096; font-style: italic;
            text-align: center; margin-top: 16px;
        }

        /* Dark mode (already dark, but keep for consistency) */

        /* Mobile */
        @media (max-width: 768px) {
            .ticket-card { padding: 18px 16px; max-width: 100%; border-radius: 20px; }
            .ticket-header img { height: 32px !important; }
            .ticket-title { font-size: 11px; }
            .fs-large { font-size: 22px; }
            .text-success-custom { font-size: 26px; }
            .badge-moneda { padding: 4px 10px; font-size: 9px; }
            .ticket-row.total-row { padding: 14px 16px; }
            .text-muted-custom { font-size: 9px; }
        }


    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
<?php include 'src/assets/partials/helios-assets.php'; ?>
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
                                    <h5 class="card-title fw-semibold mb-4">Datos de Cotizaci&oacute;n</h5>
                                    
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
                                        <label class="form-label fw-semibold">Comisi&oacute;n (%)</label>
                                        <div class="input-group">
                                            <input type="text" step="0.01" class="form-control" id="inputMargen" value="">
                                            <span class="input-group-text bg-light">%</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Observaciones (Opcional)</label>
                                        <textarea class="form-control" id="inputObservaciones" rows="2" placeholder="Ej. COTIZACI&Oacute;N CON D&Oacute;LARES..."></textarea>
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
                                        <i class="ti ti-download me-2 fs-5"></i> Descargar Cotizaci&oacute;n
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7 order-2 order-lg-2">
                            <div class="ticket-wrapper">
                                 <div class="ticket-card" id="vistaPreviaTicket">
                                     <div class="ticket-header">
                                         <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; max-width: 100%; object-fit: contain;" />
                                         <div class="ticket-title" id="outTicketTitle">C&Aacute;LCULO DE VENTA</div>
                                     </div>

                                     <div class="ticket-section responsive-grid">
                                         <div class="grid-item">
                                             <div>
                                                 <span class="text-muted-custom">CANTIDAD</span>
                                                 <div class="fs-large" id="outCantidad">0.00</div>
                                             </div>
                                             <div>
                                                 <span class="badge-moneda usdt" id="outMoneda">USDT</span>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="ticket-section responsive-grid">
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

                                     <div class="ticket-section commission-section" id="ticketComision">
                                         <span class="text-muted-custom">COMISI&Oacute;N</span>
                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <span style="font-size: 15px;">PORCENTAJE: <strong id="outMargenTxt">0.00%</strong></span>
                                         </div>
                                         <div style="font-size: 14px; color: #9a9096;">
                                             TOTAL DE COMISI&Oacute;N: <span id="outComisionTotal" style="font-weight: 600; color: #fff;">$0.00 MXN</span>
                                         </div>
                                     </div>

                                     <div class="ticket-row total-row d-flex justify-content-between align-items-center">
                                         <div class="text-muted-custom" id="outTotalLabel">TOTAL A RECIBIR</div>
                                         <div class="text-success-custom" id="outTotalTxt">$0.00 MXN</div>
                                     </div>

                                     <div class="observaciones-section" id="ticketObservaciones" style="display: none;">
                                         <div class="text-primary-custom mb-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">OBSERVACIONES</div>
                                         <div id="outObservacionesTxt" style="font-size: 15px; color: #fff; white-space: pre-wrap; line-height: 1.5; font-weight: 500;"></div>
                                     </div>

                                     <div class="ticket-footer-note">* Esta cotizaci&oacute;n tiene una vigencia de 30 minutos a partir de su emisi&oacute;n.</div>
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
                html2canvas(ticket, {
                    backgroundColor: null,
                    scale: 3,
                    useCORS: true,
                    onclone: function(doc) {
                        var el = doc.getElementById('vistaPreviaTicket');
                        if (el) {
                            el.style.boxShadow = 'none';
                            el.style.background = '#302a2e';
                            el.style.borderRadius = '24px';
                            el.style.overflow = 'hidden';
                            el.classList.add('exporting');
                            el.querySelectorAll('.ticket-section').forEach(function(s) {
                                s.style.background = '#141214';
                                s.style.boxShadow = 'none';
                            });
                            var total = el.querySelector('.ticket-row.total-row');
                            if (total) { total.style.background = '#141214'; total.style.boxShadow = 'none'; }
                        }
                    }
                }).then(canvas => {
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
