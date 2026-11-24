<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['admin','usuario'])) {
    header('Location: login.php');
    exit;
}

$proveedor_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$proveedor_id) {
    header('Location: flujo.php');
    exit;
}

$pdo = getPDO();
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener datos del proveedor (incluye MXN)
$stmt = $pdo->prepare("
    SELECT p.*, 
           cu.nombre AS cuenta_usd_nombre, cu.saldo_actual AS saldo_usd, cu.valor_mxn AS valor_mxn_usd,
           cusdt.nombre AS cuenta_usdt_nombre, cusdt.saldo_actual AS saldo_usdt, cusdt.valor_mxn AS valor_mxn_usdt,
           cmxn.nombre AS cuenta_mxn_nombre, cmxn.saldo_actual AS saldo_mxn
    FROM proveedores p
    LEFT JOIN cuentas cu ON p.cuenta_usd_id = cu.id
    LEFT JOIN cuentas cusdt ON p.cuenta_usdt_id = cusdt.id
    LEFT JOIN cuentas cmxn ON p.cuenta_mxn_id = cmxn.id
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$proveedor_id]);
$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    die("Error: Proveedor no encontrado.");
}

// Calcular totales
$total_usd = floatval($proveedor['saldo_usd'] ?? 0);
$total_usdt = floatval($proveedor['saldo_usdt'] ?? 0);
$total_mxn_directo = floatval($proveedor['saldo_mxn'] ?? 0);
$total_mxn = floatval($proveedor['valor_mxn_usd'] ?? 0) + floatval($proveedor['valor_mxn_usdt'] ?? 0) + $total_mxn_directo;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Proveedor - <?= htmlspecialchars($proveedor['nombre']) ?> - Amiri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        [data-bs-theme="dark"] .card { background: var(--nx-surface) !important; border-color: rgba(255,255,255,0.12); }
        [data-bs-theme="dark"] .table { color: #f5f2f4; }
        [data-bs-theme="dark"] .table thead th { background-color: var(--nx-surface) !important; color: #f5f2f4; }
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
                <div class="container-fluid px-4" style="max-width: 100%;">
                    <div class="row mb-3 mt-4">
                        <div class="col-12 d-flex align-items-center gap-2">
                            <a href="flujo.php" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-arrow-left"></i> Volver
                            </a>
                            <h3 class="mb-0"><i class="ti ti-building"></i> <?= htmlspecialchars($proveedor['nombre']) ?></h3>
                            <span class="badge bg-info bg-opacity-10 text-info border-0 ms-2">Proveedor</span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm border-warning">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Adeudo MXN</h6>
                                    <h3 class="mb-0 text-warning">$<?= number_format($total_mxn_directo, 2) ?> MXN</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-info">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Adeudo USD</h6>
                                    <h3 class="mb-0 text-primary"><?= number_format($total_usd, 2) ?> USD</h3>
                                    <small class="text-muted">$<?= number_format($proveedor['valor_mxn_usd'] ?? 0, 2) ?> MXN</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-success">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Adeudo USDT</h6>
                                    <h3 class="mb-0 text-success"><?= number_format($total_usdt, 2) ?> USDT</h3>
                                    <small class="text-muted">$<?= number_format($proveedor['valor_mxn_usdt'] ?? 0, 2) ?> MXN</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm border-dark">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Total en MXN</h6>
                                    <h3 class="mb-0 text-dark">$<?= number_format($total_mxn, 2) ?> MXN</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="ti ti-info-circle"></i> Información del Proveedor</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php if ($proveedor['telefono']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-phone"></i> Teléfono:</strong><br>
                                            <?= htmlspecialchars($proveedor['telefono']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($proveedor['email']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-mail"></i> Email:</strong><br>
                                            <?= htmlspecialchars($proveedor['email']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($proveedor['direccion']): ?>
                                        <div class="col-md-4 mb-2">
                                            <strong><i class="ti ti-map-pin"></i> Dirección:</strong><br>
                                            <?= htmlspecialchars($proveedor['direccion']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="ti ti-cash"></i> Pagar al Proveedor</h5>
                                    <span class="badge bg-white text-danger" id="badgeEstadoPago"><?php if($total_mxn>0.01) echo 'Con adeudo'; else echo 'Sin adeudo'; ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if($total_mxn>0.01): ?>
                                    <p class="text-muted small">Selecciona la moneda a pagar. El pago se descuenta de una cuenta MXN (Bancaria/Caja) y reduce el adeudo del proveedor.</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if($total_mxn_directo>0.01): ?>
                                        <button class="btn btn-warning" onclick="abrirPago('MXN')"><i class="ti ti-cash"></i> Pagar MXN ($<?= number_format($total_mxn_directo,2) ?>)</button>
                                        <?php endif; ?>
                                        <?php if($proveedor['acepta_usd'] && $total_usd>0.01): ?>
                                        <button class="btn btn-primary" onclick="abrirPago('USD')"><i class="ti ti-currency-dollar"></i> Pagar USD (<?= number_format($total_usd,2) ?>)</button>
                                        <?php endif; ?>
                                        <?php if($proveedor['acepta_usdt'] && $total_usdt>0.01): ?>
                                        <button class="btn btn-info" onclick="abrirPago('USDT')"><i class="ti ti-coin"></i> Pagar USDT (<?= number_format($total_usdt,2) ?>)</button>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-success mb-0"><i class="ti ti-check"></i> Sin adeudo pendiente con este proveedor.</div>
                                    <?php endif; ?>
                                    <hr>
                                    <a href="flujo.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Volver a Flujo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pago Proveedor Unificado (mismo que flujo.php) -->
    <div class="modal fade" id="modalPagoProveedor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title" id="pagoModalTitle"><i class="ti ti-cash text-warning"></i> Pagar Proveedor</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formPagoProveedor">
                    <div class="modal-body">
                        <input type="hidden" id="pagoProveedorId" value="<?= (int)$proveedor_id ?>">
                        <input type="hidden" id="pagoMoneda" value="MXN">
                        <input type="hidden" id="pagoParidad" value="1">
                        <div class="alert alert-secondary py-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold fs-6"><?= htmlspecialchars($proveedor['nombre']) ?></span>
                                <span class="badge border-0" id="pagoBadgeMoneda">MXN</span>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-6">
                                    <div class="text-muted small" id="pagoLabelAdeudo">Adeudo</div>
                                    <div class="fw-bold" id="pagoInfoAdeudo">—</div>
                                    <small class="text-muted" id="pagoInfoAdeudoMxn" style="display:none;"></small>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Total a pagar</div>
                                    <div class="fw-bold text-danger" id="pagoInfoTotal">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha de Pago *</label>
                            <input type="date" id="pagoFecha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cuenta de Pago (MXN) *</label>
                            <select id="pagoCuentaSelect" class="form-control" required><option value="">Seleccione cuenta</option></select>
                        </div>
                        <div class="mb-3" id="pagoGrupoMonto" style="display:none;">
                            <label class="form-label fw-semibold" id="pagoLabelMonto">Monto a Pagar *</label>
                            <div class="input-group">
                                <span class="input-group-text" id="pagoSimboloIzq">$</span>
                                <input type="text" id="pagoMonto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                                <span class="input-group-text" id="pagoSimboloDer">MXN</span>
                            </div>
                            <small id="pagoMontoLabel" class="text-success fw-bold mt-1 d-block" style="min-height:1.2em;"></small>
                            <small id="pagoEquivalencia" class="text-muted d-block" style="display:none;"></small>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" id="pagoInfoSaldo"></small>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnLiquidarTotal" style="display:none;"><i class="ti ti-check"></i> Liquidar Total</button>
                            </div>
                            <div id="pagoAlertaSaldo" class="alert alert-warning py-2 mt-2" style="display:none;"><i class="ti ti-alert-triangle"></i> El monto no puede superar el adeudo ni el saldo de la cuenta.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" id="btnProcesarPago" style="display:none;"><i class="ti ti-cash"></i> Procesar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script>
    const PROVEEDOR = <?= json_encode($proveedor, JSON_UNESCAPED_UNICODE) ?>;
    function formatMXN(v){ return (parseFloat(v)||0).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    function formatNativo(v){ return (parseFloat(v)||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    function showToast(msg,type){
        const bg = type==='success' ? 'bg-success' : type==='error' ? 'bg-danger' : 'bg-warning';
        const toast = $(`<div class="toast align-items-center text-white ${bg} border-0" role="alert"><div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
        $('.toast-container').append(toast); const bs=new bootstrap.Toast(toast[0],{delay:4000}); bs.show(); toast.on('hidden.bs.toast',()=>toast.remove());
    }
    function abrirPago(moneda){
        moneda = (moneda||'MXN').toUpperCase();
        let saldo, valorMxn, paridad;
        if(moneda==='USD'){ saldo=parseFloat(PROVEEDOR.saldo_usd||0); valorMxn=parseFloat(PROVEEDOR.valor_mxn_usd||0); }
        else if(moneda==='USDT'){ saldo=parseFloat(PROVEEDOR.saldo_usdt||0); valorMxn=parseFloat(PROVEEDOR.valor_mxn_usdt||0); }
        else { moneda='MXN'; saldo=parseFloat(PROVEEDOR.saldo_mxn||0); valorMxn=saldo; }
        if(saldo<0.001){ showToast('Sin adeudo en '+moneda,'warning'); return; }
        paridad = moneda==='MXN'?1:(saldo>0?valorMxn/saldo:0);
        $('#pagoMoneda').val(moneda); $('#pagoParidad').val(paridad);
        const badgeCls = moneda==='USD'?'bg-primary bg-opacity-10 text-primary':moneda==='USDT'?'bg-info bg-opacity-10 text-info':'bg-warning bg-opacity-10 text-warning';
        $('#pagoBadgeMoneda').attr('class','badge border-0 '+badgeCls).text(moneda);
        $('#pagoModalTitle').html('<i class="ti ti-cash text-warning"></i> Pagar Proveedor '+moneda);
        if(moneda==='MXN'){
            $('#pagoLabelAdeudo').text('Adeudo MXN'); $('#pagoInfoAdeudo').text('$'+formatMXN(saldo)+' MXN'); $('#pagoInfoAdeudoMxn').hide();
            $('#pagoInfoTotal').text('$'+formatMXN(valorMxn)+' MXN');
            $('#pagoLabelMonto').text('Monto a Pagar (MXN) *'); $('#pagoSimboloIzq').text('$'); $('#pagoSimboloDer').text('MXN');
            $('#formPagoProveedor').data('adeudo-mxn',valorMxn).data('adeudo-divisa',saldo).data('moneda',moneda).data('paridad',paridad);
        } else {
            $('#pagoLabelAdeudo').text('Adeudo '+moneda); $('#pagoInfoAdeudo').text(formatNativo(saldo)+' '+moneda);
            $('#pagoInfoAdeudoMxn').text('$'+formatMXN(valorMxn)+' MXN').show();
            $('#pagoInfoTotal').text('$'+formatMXN(valorMxn)+' MXN');
            $('#pagoLabelMonto').text('Monto a Pagar ('+moneda+') *'); $('#pagoSimboloIzq').text(''); $('#pagoSimboloDer').text(moneda);
            $('#formPagoProveedor').data('adeudo-mxn',valorMxn).data('adeudo-divisa',saldo).data('moneda',moneda).data('paridad',paridad);
        }
        $('#pagoFecha').val(new Date().toISOString().split('T')[0]);
        $('#pagoMonto').val(''); $('#pagoMontoLabel').text(''); $('#pagoEquivalencia').hide();
        $('#pagoGrupoMonto').hide(); $('#btnLiquidarTotal').hide(); $('#pagoAlertaSaldo').hide(); $('#btnProcesarPago').hide(); $('#pagoInfoSaldo').text('');
        $.get('api/cuentas.php?tipo_moneda=MXN', function(cuentas){
            const sel=$('#pagoCuentaSelect'); sel.html('<option value="">Seleccione cuenta</option>');
            const filtradas=cuentas.filter(c=>[1,2].includes(parseInt(c.tipo_cuenta)) && parseFloat(c.saldo_actual)>0);
            if(!filtradas.length) sel.append('<option value="" disabled>No hay cuentas MXN disponibles</option>');
            else filtradas.forEach(c=>{
                const saldo=parseFloat(c.saldo_actual||0).toFixed(2);
                const nombre=c.banco&&c.banco.trim()?c.nombre+' ('+c.banco+')':c.nombre;
                sel.append('<option value="'+c.id+'" data-saldo="'+saldo+'">'+nombre+' — Saldo: $'+parseFloat(saldo).toLocaleString('es-MX',{minimumFractionDigits:2})+' MXN</option>');
            });
            new bootstrap.Modal(document.getElementById('modalPagoProveedor')).show();
        });
    }
    $(document).on('change','#pagoCuentaSelect',function(){
        const saldoCuenta=parseFloat($(this).find('option:selected').attr('data-saldo'))||0;
        const adeudoMxn=parseFloat($('#formPagoProveedor').data('adeudo-mxn'))||0;
        const adeudoDivisa=parseFloat($('#formPagoProveedor').data('adeudo-divisa'))||adeudoMxn;
        const moneda=$('#formPagoProveedor').data('moneda')||'MXN';
        const paridad=parseFloat($('#formPagoProveedor').data('paridad'))||1;
        if(!$(this).val()){ $('#pagoGrupoMonto').hide(); $('#btnProcesarPago').hide(); return; }
        $('#pagoGrupoMonto').show(); $('#btnProcesarPago').show();
        if(moneda==='MXN') $('#pagoMonto').val('').attr('max',Math.min(saldoCuenta,adeudoMxn).toFixed(2));
        else $('#pagoMonto').val('').attr('max',Math.min(adeudoDivisa, paridad>0?saldoCuenta/paridad:0).toFixed(2));
        $('#pagoInfoSaldo').text('Saldo disponible: $'+saldoCuenta.toLocaleString('es-MX',{minimumFractionDigits:2})+' MXN');
        $('#pagoAlertaSaldo').hide();
        const puedeLiquidar = moneda==='MXN'?saldoCuenta>=adeudoMxn:paridad>0&&saldoCuenta>=adeudoMxn;
        if(puedeLiquidar) $('#btnLiquidarTotal').show(); else if(moneda!=='MXN'&&saldoCuenta>0) $('#btnLiquidarTotal').show(); else $('#btnLiquidarTotal').hide();
    });
    $(document).on('click','#btnLiquidarTotal',function(){
        const moneda=$('#formPagoProveedor').data('moneda')||'MXN';
        if(moneda==='MXN'){
            const adeudoMxn=parseFloat($('#formPagoProveedor').data('adeudo-mxn'))||0;
            const saldoCuenta=parseFloat($('#pagoCuentaSelect').find('option:selected').attr('data-saldo'))||0;
            $('#pagoMonto').val(Math.min(adeudoMxn,saldoCuenta).toFixed(2)).trigger('input');
        } else {
            const adeudoDivisa=parseFloat($('#formPagoProveedor').data('adeudo-divisa'))||0;
            const saldoCuenta=parseFloat($('#pagoCuentaSelect').find('option:selected').attr('data-saldo'))||0;
            const paridad=parseFloat($('#formPagoProveedor').data('paridad'))||1;
            $('#pagoMonto').val(Math.min(adeudoDivisa, paridad>0?saldoCuenta/paridad:0).toFixed(2)).trigger('input');
        }
    });
    $(document).on('input','#pagoMonto',function(){
        const val=parseFloat($(this).val())||0;
        const adeudoMxn=parseFloat($('#formPagoProveedor').data('adeudo-mxn'))||0;
        const adeudoDivisa=parseFloat($('#formPagoProveedor').data('adeudo-divisa'))||adeudoMxn;
        const moneda=$('#formPagoProveedor').data('moneda')||'MXN';
        const paridad=parseFloat($('#formPagoProveedor').data('paridad'))||1;
        const saldoCuenta=parseFloat($('#pagoCuentaSelect').find('option:selected').attr('data-saldo'))||0;
        const montoMxnEquiv = moneda==='MXN'?val:val*paridad;
        if(val>0){
            if(moneda==='MXN'){ $('#pagoMontoLabel').text('$'+val.toLocaleString('es-MX',{minimumFractionDigits:2})+' MXN'); $('#pagoEquivalencia').hide(); }
            else { $('#pagoMontoLabel').text(formatNativo(val)+' '+moneda); $('#pagoEquivalencia').text('≈ $'+montoMxnEquiv.toLocaleString('es-MX',{minimumFractionDigits:2})+' MXN (TC '+formatMXN(paridad)+')').show(); }
        } else { $('#pagoMontoLabel').text(''); $('#pagoEquivalencia').hide(); }
        const excede = moneda==='MXN'?val>Math.min(saldoCuenta,adeudoMxn)+0.001 : (val>adeudoDivisa+0.001 || montoMxnEquiv>saldoCuenta+0.001);
        if(excede){ $('#pagoAlertaSaldo').show(); $('#btnProcesarPago').prop('disabled',true); } else { $('#pagoAlertaSaldo').hide(); $('#btnProcesarPago').prop('disabled',false); }
    });
    $(document).on('submit','#formPagoProveedor',function(e){
        e.preventDefault();
        const montoInput=parseFloat($('#pagoMonto').val())||0;
        const moneda=$('#formPagoProveedor').data('moneda')||$('#pagoMoneda').val()||'MXN';
        const paridad=parseFloat($('#formPagoProveedor').data('paridad')||$('#pagoParidad').val()||1);
        if(!$('#pagoCuentaSelect').val()){ showToast('Selecciona una cuenta de pago','error'); return; }
        if(montoInput<=0){ showToast('Ingresa un monto a pagar','error'); return; }
        const monto = moneda==='MXN'?montoInput:montoInput*paridad;
        const payload={ proveedor_id: $('#pagoProveedorId').val(), cuenta_pago_id: $('#pagoCuentaSelect').val(), monto: monto, paridad: paridad, moneda: moneda, fecha: $('#pagoFecha').val()+' '+new Date().toTimeString().split(' ')[0] };
        const $btn=$('#btnProcesarPago').prop('disabled',true).html('<i class="ti ti-loader ti-spin"></i> Procesando...');
        $.ajax({ url:'api/proveedores-pagos.php', method:'POST', contentType:'application/json', data:JSON.stringify(payload),
            success: function(){ bootstrap.Modal.getInstance(document.getElementById('modalPagoProveedor')).hide(); showToast('Pago registrado','success'); setTimeout(()=>location.reload(),600); },
            error: function(xhr){ let msg='Error al registrar pago'; try{msg=JSON.parse(xhr.responseText).error||msg;}catch(e){} showToast(msg,'error'); $btn.prop('disabled',false).html('<i class="ti ti-cash"></i> Procesar Pago'); }
        });
    });
    </script>
</body>
</html>
