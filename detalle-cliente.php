<?php
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Obtener el ID del cliente de la URL
$cliente_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$cliente_id) {
    die("Error: ID de cliente no válido.");
}

// Función para obtener datos del cliente
function getCliente($pdo, $id)
{
    $stmt = $pdo->prepare("
        SELECT c.*, cu.nombre as cuenta_nombre, cu.saldo_actual as cuenta_saldo, p.nombre as promotor_nombre
        FROM clientes c
        LEFT JOIN cuentas cu ON c.cuenta_id = cu.id
        LEFT JOIN promotores p ON c.promotor_id = p.id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para obtener cuentas MXN disponibles para pago
function getCuentas($pdo)
{
    $stmt = $pdo->query("
        SELECT id, nombre, saldo_actual, tipo_moneda, tipo_cuenta
        FROM cuentas
        WHERE activo = 1
          AND tipo_moneda = 'MXN'
          AND tipo_cuenta IN (1, 2, 3)
          AND saldo_actual > 0
        ORDER BY tipo_cuenta, nombre
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$requireEnv = __DIR__ . '/src/config/env.php';
if (file_exists($requireEnv)) {
    require_once $requireEnv;
}
require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = getPDO();

    $cliente = getCliente($pdo, $cliente_id);

    if (!$cliente) {
        die("Error: Cliente no encontrado.");
    }

    $cuentas = getCuentas($pdo);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ventas Pendientes - <?= htmlspecialchars($cliente['nombre']) ?></title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <style>
        .detalle-container {
            margin-top: 20px;
        }
        #tabsCliente .nav-link {
            color: #6c757d;
        }
        #tabsCliente .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
            border-bottom: 2px solid #0d6efd;
            font-weight: 600;
        }
        #tabsCliente .nav-link:not(.active):hover {
            color: #0d6efd;
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
                <div class="container-fluid detalle-container">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-breadcrumb d-flex align-items-center mb-3">
                                <h3 class="mb-0"><i class="ti ti-user"></i> <?= htmlspecialchars($cliente['nombre']) ?></h3>
                                <a href="flujo.php" class="btn btn-secondary ms-auto"><i class="ti ti-arrow-left"></i> Volver a Flujo</a>
                            </div>
                        </div>
                    </div>

                    <!-- Información general del cliente -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="ti ti-info-circle"></i> Información del Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h5><strong>• Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?></h5>
                                            <h5><strong>• Contacto:</strong> <?= htmlspecialchars($cliente['contacto'] ?? 'N/A') ?></h5>
                                        </div>
                                        <div class="col-md-4">
                                            <h5><strong>• Promotor:</strong> <?= htmlspecialchars($cliente['promotor_nombre'] ?? 'N/A') ?></h5>
                                            <h5><strong>• Comisión Amiri:</strong> <?= htmlspecialchars($cliente['porcentaje_amiri'] ?? 0) ?>%</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <h5><strong>• Cuenta:</strong> <?= htmlspecialchars($cliente['cuenta_nombre'] ?? 'N/A') ?></h5>
                                            <h5><strong>• Saldo Actual:</strong> $<?= number_format((float)($cliente['cuenta_saldo'] ?? 0), 2, '.', ',') ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs de historial -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-tabs card-header-tabs" id="tabsCliente" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="tab-adeudos-btn" data-bs-toggle="tab" data-bs-target="#tab-adeudos" type="button" role="tab">
                                                <i class="ti ti-cash"></i> Adeudos y Pagos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="tab-divisas-btn" data-bs-toggle="tab" data-bs-target="#tab-divisas" type="button" role="tab">
                                                <i class="ti ti-currency-dollar"></i> Ventas USD / USDT
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="tabsClienteContent">

                                        <!-- Tab 1: Ventas de efectivo pendientes -->
                                        <div class="tab-pane fade show active" id="tab-adeudos" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="tablaVentas" class="table table-striped table-hover table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Fecha</th>
                                                            <th>Total recibido</th>
                                                            <th>Comisión AMIRI</th>
                                                            <th>Comisión Promotor</th>
                                                            <th>Total a pagar</th>
                                                            <th>Estado</th>
                                                            <th>Pagado</th>
                                                            <th>Pendiente</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Historial ventas USD/USDT -->
                                        <div class="tab-pane fade" id="tab-divisas" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="tablaVentasDivisas" class="table table-striped table-hover table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Moneda</th>
                                                            <th>Cantidad</th>
                                                            <th>TC Venta</th>
                                                            <th>Total MXN</th>
                                                            <th>TC Costo (FIFO)</th>
                                                            <th>Ganancia AMIRI</th>
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
        </div>

        <!-- Modal Pagar Venta -->
        <div class="modal fade" id="modalPagarVenta" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Pago de Venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formPagarVenta">
                        <input type="hidden" id="ventaId" name="id">
                        <div class="modal-body">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Monto Total de la Venta</label>
                                    <p id="montoTotalDisplay" class="form-control-plaintext text-primary fw-bold"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de la Venta</label>
                                    <p id="fechaDisplay" class="form-control-plaintext text-muted"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Comisión AMIRI</label>
                                    <p id="comisionAmiriDisplay" class="form-control-plaintext text-muted"></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Comisión Promotor</label>
                                    <p id="comisionPromotorDisplay" class="form-control-plaintext text-muted"></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Monto Neto Cliente</label>
                                    <p id="montoNetoDisplay" class="form-control-plaintext text-success fw-bold"></p>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Estado Actual</label>
                                    <p id="estadoActualDisplay" class="form-control-plaintext text-muted"></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Saldo Pendiente</label>
                                    <p id="saldoPendienteDisplay" class="form-control-plaintext text-danger fw-bold"></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Moneda</label>
                                    <p id="monedaDeudaDisplay" class="form-control-plaintext">
                                        <span id="badgeMoneda" class="badge bg-primary">MXN</span>
                                    </p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Cuenta para Pagar *</label>
                                    <select class="form-select" id="cuentaPagoSelect" name="cuenta_pago_id" required>
                                        <option value="">Selecciona una cuenta</option>
                                        <?php foreach ($cuentas as $c): ?>
                                            <option value="<?= $c['id'] ?>">
                                                <?= htmlspecialchars($c['nombre']) ?>
                                                (Saldo: $<?= number_format($c['saldo_actual'], 2) ?> MXN)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Se restará de ambas cuentas: del cliente (digital) y de nuestra caja (efectivo físico entregado)</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="ti ti-coin"></i> Monto a pagar:
                                        <span id="montoFormateado" class="text-success fw-bold ms-2"></span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="montoPagarFormatted" placeholder="0.00" required>
                                        <input type="hidden" name="monto_a_pagar" id="montoPagarHidden" required>
                                    </div>
                                    <div id="warningPagoExceso" class="alert alert-warning py-1 px-2 mt-1 mb-0" style="display:none;font-size:0.875rem;"></div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-success btn-sm" id="btnLiquidarDeuda" style="display: none;">
                                            <i class="ti ti-cash"></i> Liquidar Deuda Total
                                        </button>
                                    </div>
                                    <small class="text-muted">Disponible en cuenta cliente: <span id="saldoCuentaCliente"></span></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Pago</label>
                                    <input type="date" class="form-control" id="fechaPago" name="fecha_pago" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="alert alert-info" id="resumenPago" style="display: none;">
                                <h6>Resumen del Pago:</h6>
                                <p id="mensajePago"></p>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnGuardarPago">Guardar Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
        <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="src/assets/js/sidebarmenu.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="src/assets/js/dark-mode.js"></script>
        <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="src/assets/js/utils.js"></script>

        <script>
            /* ============================================================
            /* ============================================================
               UTILIDADES
            ============================================================ */
            // Reset al abrir para nuevo
            $('button[data-bs-target="#modalGasto"]').on('click', function() {
                $('#formGasto')[0].reset();
                $('#id').val('');
                $('#montoFormateado').text(''); // Limpiar formato
                cargarTiposGasto(); // Cargar tipos al abrir
            });

            // Formatear monto en tiempo real como guía visual
            $('#inputMonto').on('input', function() {
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

            function formatoMoneda(valor) {
                const n = parseFloat(valor) || 0;
                return n.toLocaleString("en-US", {
                    style: "currency",
                    currency: "MXN",
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

            function parseMoneda(texto) {
                if (!texto) return 0;
                let valor = texto.replace(/[^\d.,]/g, "");
                const milesComma = /^(\d{1,3})(,\d{3})*(\.\d{1,2})?$/;
                if (milesComma.test(valor)) {
                    valor = valor.replace(/,/g, "");
                    return parseFloat(valor);
                }
                const milesDot = /^(\d{1,3})(\.\d{3})*(,\d{1,2})?$/;
                if (milesDot.test(valor)) {
                    valor = valor.replace(/\./g, "").replace(",", ".");
                    return parseFloat(valor);
                }
                return parseFloat(valor) || 0;
            }


            /**
             * Formatear moneda según el tipo
             */
            function formatoMonedaDinamica(valor, moneda) {
                const n = parseFloat(valor) || 0;
                const simbolo = moneda === 'MXN' ? '$' : (moneda === 'USD' ? 'USD ' : 'USDT ');
                return simbolo + n.toLocaleString("en-US", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            /* ============================================================
               DATA TABLE
            ============================================================ */

            const tablaVentas = $('#tablaVentas').DataTable({
                ajax: {
                    url: 'api/ventas-efectivo-clientes.php?cliente_id=<?= $cliente_id ?>',
                    dataSrc: ""
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'fecha'
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            return formatoMonedaDinamica(data.monto_total, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            return formatoMonedaDinamica(data.comision_amiri, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            return formatoMonedaDinamica(data.comision_promotor, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            return formatoMonedaDinamica(data.monto_neto_cliente, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            const badgeMoneda = moneda === 'MXN' ? 'bg-primary' : (moneda === 'USD' ? 'bg-success' : 'bg-info');
                            const estado = data.estado;
                            const clsEstado = estado === "pagado" ? "bg-success" :
                                estado === "parcial" ? "bg-warning" : "bg-danger";
                            return `<span class="badge ${badgeMoneda} me-1">${moneda}</span><span class="badge ${clsEstado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            return formatoMonedaDinamica(data.monto_pagado, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            const moneda = data.moneda || 'MXN';
                            const pendiente = data.monto_neto_cliente - data.monto_pagado;
                            return formatoMonedaDinamica(pendiente, moneda);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            let btnPagar = '';
                            if (data.estado !== 'pagado') {
                                btnPagar = `<button class="btn btn-sm btn-warning pagar" data-id="${data.id}"><i class="ti ti-cash"></i> Pagar</button> `;
                            }
                            let btnEliminar = `<button class="btn btn-sm btn-danger eliminar" data-id="${data.id}"><i class="ti ti-trash"></i></button>`;
                            return btnPagar + btnEliminar;
                        }
                    }
                ],
                order: [
                    [1, "desc"]
                ]
            });

            /* ============================================================
               MODAL - ABRIR Y CARGAR DATOS
            ============================================================ */

            $('#tablaVentas').on('click', '.pagar', function() {
                const id = $(this).data("id");

                $.get('api/ventas-efectivo-clientes.php?id=' + id, data => {
                    // Determinar la moneda (por defecto MXN para registros existentes)
                    const moneda = data.moneda || 'MXN';
                    
                    $('#ventaId').val(data.id);
                    $('#montoTotalDisplay').text(formatoMonedaDinamica(data.monto_total, moneda));
                    $('#fechaDisplay').text(data.fecha);
                    $('#comisionAmiriDisplay').text(formatoMonedaDinamica(data.comision_amiri, moneda));
                    $('#comisionPromotorDisplay').text(formatoMonedaDinamica(data.comision_promotor, moneda));
                    $('#montoNetoDisplay').text(formatoMonedaDinamica(data.monto_neto_cliente, moneda));

                    const pendiente = data.monto_neto_cliente - data.monto_pagado;
                    $('#saldoPendienteDisplay').text(formatoMonedaDinamica(pendiente, moneda));
                    $('#estadoActualDisplay').text(data.estado.charAt(0).toUpperCase() + data.estado.slice(1));
                    
                    // Mostrar badge de moneda
                    const badgeClass = moneda === 'MXN' ? 'bg-primary' : (moneda === 'USD' ? 'bg-success' : 'bg-info');
                    $('#badgeMoneda').removeClass('bg-primary bg-success bg-info').addClass(badgeClass).text(moneda);

                    // Guardar moneda para uso posterior
                    $('#montoPagarFormatted').data('moneda', moneda);

                    // Mostrar saldo disponible del cliente
                    $('#saldoCuentaCliente').text(formatoMoneda(<?= $cliente['cuenta_saldo'] ?? 0 ?>));

                    $('#montoPagarFormatted').val("");
                    $('#montoPagarHidden').val("");
                    $('#cuentaPagoSelect').val("");
                    $('#montoFormateado').text(''); // Limpiar formato visual
                    $('#btnLiquidarDeuda').hide(); // Ocultar botón al inicio

                    // Guardar datos para validación
                    $('#montoPagarFormatted').data('pendiente', pendiente);
                    $('#montoPagarFormatted').data('saldo-cliente', <?= $cliente['cuenta_saldo'] ?? 0 ?>);

                    actualizarResumenPago(data.monto_neto_cliente, data.monto_pagado, 0);
                    $('#resumenPago').show();

                    $('#modalPagarVenta').modal("show");
                });
            });

            // Escuchar cambios en selector de cuenta para mostrar/ocultar botón de liquidar
            $('#cuentaPagoSelect').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const cuentaTexto = selectedOption.text();
                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;
                
                // Extraer saldo de la cuenta del texto "(Saldo: $1,234.56)" o "(Saldo: USD 1,234.56)" o "(Saldo: USDT 1,234.56)"
                const matchSaldo = cuentaTexto.match(/Saldo:\s*(?:\$|USD\s*|USDT\s*)([\d,]+\.\d{2})/);
                
                if (matchSaldo) {
                    const saldoCuenta = parseFloat(matchSaldo[1].replace(/,/g, ''));
                    
                    // Mostrar botón solo si la cuenta tiene saldo suficiente
                    if (saldoCuenta >= pendiente && pendiente > 0) {
                        $('#btnLiquidarDeuda').show();
                    } else {
                        $('#btnLiquidarDeuda').hide();
                    }
                } else {
                    $('#btnLiquidarDeuda').hide();
                }
            });

            // Evento clic en botón Liquidar Deuda Total
            $('#btnLiquidarDeuda').on('click', function() {
                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;
                const moneda = $('#montoPagarFormatted').data('moneda') || 'MXN';
                
                // Autocompletar el input con la deuda total
                $('#montoPagarFormatted').val(pendiente.toFixed(2));
                $('#montoPagarHidden').val(pendiente.toFixed(2));
                
                // Actualizar formato visual con la moneda correcta
                $('#montoFormateado').text('= ' + formatoMonedaDinamica(pendiente, moneda));
                
                // Actualizar resumen
                actualizarResumenPago(pendiente, 0, pendiente);
            });

            // Formatear monto en tiempo real como guía visual
            $('#montoPagarFormatted').on('input', function() {
                let valor = $(this).val().replace(/[^0-9.]/g, ''); // Solo números y punto
                const spanFormato = $('#montoFormateado');
                const moneda = $(this).data('moneda') || 'MXN';
                const pendiente = parseFloat($(this).data('pendiente')) || 0;

                // Actualizar campo hidden con valor sin formato
                $('#montoPagarHidden').val(valor);

                const valorNumerico = parseFloat(valor);
                const $warn = $('#warningPagoExceso');

                if (isNaN(valorNumerico) || valorNumerico === 0) {
                    spanFormato.text('');
                    $warn.hide();
                } else {
                    spanFormato.text('= ' + formatoMonedaDinamica(valorNumerico, moneda));

                    // Warning si el monto supera el pendiente
                    if (pendiente > 0 && valorNumerico > pendiente + 0.001) {
                        $warn.show().html(`<i class="ti ti-alert-triangle"></i> El monto ingresado (<strong>${formatoMonedaDinamica(valorNumerico, moneda)}</strong>) supera el adeudo pendiente (<strong>${formatoMonedaDinamica(pendiente, moneda)}</strong>)`);
                    } else {
                        $warn.hide();
                    }

                    // Actualizar resumen de pago
                    actualizarResumenPago(pendiente, 0, valorNumerico, moneda);
                }
            });

            function actualizarResumenPago(montoTotal, montoPagado, montoAdicional = 0, moneda = 'MXN') {
                montoTotal = parseFloat(montoTotal);
                montoPagado = parseFloat(montoPagado);
                montoAdicional = parseFloat(montoAdicional);

                let nuevoPagado = montoPagado + montoAdicional;
                let faltante = montoTotal - nuevoPagado;
                let estado = 'Pendiente';

                if (nuevoPagado >= montoTotal) estado = 'Pagado';
                else if (nuevoPagado > 0) estado = 'Parcial';

                let resumen = `Nuevo estado: <strong>${estado}</strong>.`;
                if (estado !== 'Pagado') {
                    resumen += ` Faltan ${formatoMonedaDinamica(faltante, moneda)} para saldar.`;
                } else {
                    resumen += ` <strong>¡Venta saldada completamente!</strong>`;
                }

                $('#mensajePago').html(resumen);
            }

            /* ============================================================
               GUARDAR PAGO
            ============================================================ */

            $('#btnGuardarPago').on('click', function() {
                const id = $('#ventaId').val();
                const montoAPagar = parseMoneda($('#montoPagarHidden').val());
                const cuentaPagoId = $('#cuentaPagoSelect').val();
                const fechaPago = $('#fechaPago').val();
                const pendiente = parseFloat($('#montoPagarFormatted').data('pendiente')) || 0;
                const moneda = $('#montoPagarFormatted').data('moneda') || 'MXN';

                if (montoAPagar <= 0) {
                    return alert("Ingresa un monto válido.");
                }

                if (!cuentaPagoId) {
                    return alert("Selecciona una cuenta de destino.");
                }

                if (pendiente > 0 && montoAPagar > pendiente + 0.001) {
                    $('#warningPagoExceso').show().html(`<i class="ti ti-alert-triangle"></i> No puedes pagar más del adeudo pendiente (<strong>${formatoMonedaDinamica(pendiente, moneda)}</strong>)`);
                    showToast('El monto supera el adeudo pendiente (' + formatoMonedaDinamica(pendiente, moneda) + ')', 'warning');
                    return;
                }

                $.ajax({
                    url: 'api/ventas-efectivo-clientes.php',
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id: id,
                        monto_a_pagar: montoAPagar,
                        cuenta_pago_id: cuentaPagoId,
                        fecha_pago: fechaPago
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#modalPagarVenta').modal('hide');
                            tablaVentas.ajax.reload(null, false);
                            showToast("Pago registrado correctamente", "success");

                            // Recargar página para actualizar saldo del cliente
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        let msg = "Error al guardar pago";
                        try {
                            msg = xhr.responseJSON?.error || msg;
                        } catch (e) {}
                        showToast(msg, "error");
                    }
                });
            });

            /* ============================================================
               DATA TABLE - VENTAS DIVISAS
            ============================================================ */

            // DataTables inside hidden tabs need columns adjusted on first show
            $('#tab-divisas-btn').one('shown.bs.tab', function() {
                tablaDivisas.columns.adjust().draw();
            });

            const tablaDivisas = $('#tablaVentasDivisas').DataTable({
                ajax: {
                    url: 'api/cliente-ventas-divisas.php?cliente_id=<?= $cliente_id ?>',
                    dataSrc: ""
                },
                order: [[0, "desc"]],
                columns: [
                    { data: 'fecha' },
                    {
                        data: 'moneda',
                        render: function(data) {
                            const cls = data === 'USD' ? 'bg-success' : 'bg-info';
                            return `<span class="badge ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: 'cantidad',
                        render: function(data, type, row) {
                            return parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 4 }) + ' ' + row.moneda;
                        }
                    },
                    {
                        data: 'tc_venta',
                        render: function(data) {
                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                        }
                    },
                    {
                        data: 'total_mxn',
                        render: function(data) {
                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    {
                        data: 'tc_costo_fifo',
                        render: function(data) {
                            if (!data) return '<span class="text-muted">—</span>';
                            return '$' + parseFloat(data).toLocaleString('es-MX', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                        }
                    },
                    {
                        data: 'ganancia_amiri',
                        render: function(data) {
                            const val = parseFloat(data);
                            const cls = val >= 0 ? 'text-success' : 'text-danger';
                            return `<span class="${cls}">$${val.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;
                        }
                    }
                ],
            });

            /* ============================================================
               ELIMINAR
            ============================================================ */

            $('#tablaVentas').on('click', '.eliminar', function() {
                const id = $(this).data("id");

                if (!confirm("¿Eliminar esta venta? Esta acción no se puede deshacer.")) return;

                $.ajax({
                    url: "api/ventas-efectivo-clientes.php?id=" + id,
                    method: "DELETE",
                    success() {
                        tablaVentas.ajax.reload();
                        showToast("Venta eliminada", "success");
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