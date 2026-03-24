<?php
/**
 * API para flujo de divisas (USD/USDT)
 * 
 * Servicios soportados:
 * - Compra de Dólares (ID 5)
 * - Venta de Dólares (ID 2)
 * - Compra de USDT (ID 6)
 * - Venta de USDT (ID 3)
 */

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';

initApi();
requireAdmin();
$pdo = getPDO();

// Mapeo de servicios a IDs (según tabla servicios en BD)
$serviciosMap = [
    'Compra de Dólares' => 5,
    'Venta de Dólares' => 2,
    'Compra de USDT' => 6,
    'Venta de USDT' => 3
];

/* ===== POST (Crear movimiento) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar datos requeridos
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $servicio = $_POST['servicio'] ?? '';
        $cliente_id = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $proveedor_id = !empty($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
        $cuenta_id = !empty($_POST['cuenta_id']) ? (int)$_POST['cuenta_id'] : null;
        $cantidad = floatval($_POST['cantidad'] ?? 0);
        $tipo_cambio = floatval($_POST['tipo_cambio'] ?? 0);
        
        if (empty($servicio)) {
            throw new Exception('El servicio es requerido');
        }
        
        if (!isset($serviciosMap[$servicio])) {
            throw new Exception('Servicio no válido: ' . $servicio);
        }
        
        if ($cantidad <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }
        
        if ($tipo_cambio <= 0) {
            throw new Exception('El tipo de cambio debe ser mayor a 0');
        }
        
        // Determinar moneda según servicio
        $moneda = (strpos($servicio, 'Dólares') !== false) ? 'USD' : 'USDT';
        $esCompra = (strpos($servicio, 'Compra') !== false);
        $servicio_id = $serviciosMap[$servicio];
        
        // Calcular total en MXN
        $total_mxn = $cantidad * $tipo_cambio;
        
        // Generar grupo_id único
        $grupo_id = round(microtime(true) * 1000);
        
        $pdo->beginTransaction();
        
        // Tipo de pago para compra: 'credito' (genera adeudo) o 'contado' (paga inmediato)
        $tipo_pago = $_POST['tipo_pago'] ?? 'credito';
        $cuenta_pago_id = !empty($_POST['cuenta_pago_id']) ? (int)$_POST['cuenta_pago_id'] : null;

        // Obtener cuenta del proveedor para la moneda correspondiente
        $cuenta_proveedor_id = null;
        if ($proveedor_id && $tipo_pago === 'credito') {
            $campoCuenta = ($moneda === 'USD') ? 'cuenta_usd_id' : 'cuenta_usdt_id';
            $stmt = $pdo->prepare("SELECT $campoCuenta AS cuenta_id, cuenta_id AS cuenta_id_legacy, nombre FROM proveedores WHERE id = ?");
            $stmt->execute([$proveedor_id]);
            $prov = $stmt->fetch(PDO::FETCH_ASSOC);
            $cuenta_proveedor_id = $prov['cuenta_id'] ?? $prov['cuenta_id_legacy'] ?? null;

            // Si no tiene cuenta para esa moneda, crearla automáticamente
            if ($prov && !$cuenta_proveedor_id) {
                $nombre_cuenta = 'Caja Proveedor ' . $moneda . ' - ' . $prov['nombre'];
                $stmtCheck = $pdo->prepare("SELECT id FROM cuentas WHERE nombre = ?");
                $stmtCheck->execute([$nombre_cuenta]);
                $cuentaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($cuentaExistente) {
                    $cuenta_proveedor_id = $cuentaExistente['id'];
                    $pdo->prepare("UPDATE cuentas SET activo = 1 WHERE id = ?")
                        ->execute([$cuenta_proveedor_id]);
                } else {
                    $stmtCuenta = $pdo->prepare("
                        INSERT INTO cuentas (nombre, tipo_cuenta, tipo_moneda, saldo_actual, activo)
                        VALUES (?, 6, ?, 0, 1)
                    ");
                    $stmtCuenta->execute([$nombre_cuenta, $moneda]);
                    $cuenta_proveedor_id = $pdo->lastInsertId();
                }

                // Vincular la cuenta al proveedor según la moneda
                $colCuenta = ($moneda === 'USD') ? 'cuenta_usd_id' : 'cuenta_usdt_id';
                $pdo->prepare("UPDATE proveedores SET $colCuenta = ? WHERE id = ?")
                    ->execute([$cuenta_proveedor_id, $proveedor_id]);
            }
        }
        
        // Preparar statement para insertar movimientos
        $stmtMov = $pdo->prepare("
            INSERT INTO movimientos 
            (grupo_id, fecha, servicio_id, tipo, moneda, monto, cuenta_origen_id, cuenta_destino_id, 
             cliente_id, proveedor_id, paridad, descripcion, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        if ($esCompra) {
            /**
             * COMPRA de divisas:
             * - Compramos USD/USDT al proveedor
             * - El cliente nos paga en MXN
             * 
             * Movimientos:
             * 1. Entrada de USD/USDT a nuestra cuenta (lo que compramos)
             * 2. Salida de MXN de cuenta bancaria (lo que pagamos al proveedor)
             * 3. Registrar deuda con proveedor (si aplica)
             */
            
            // 1. Entrada de divisas a cuenta seleccionada
            $stmtMov->execute([
                $grupo_id, $fecha, $servicio_id, 'entrada', $moneda, $cantidad,
                null, $cuenta_id, $cliente_id, $proveedor_id, $tipo_cambio,
                "Compra de $cantidad $moneda a TC $tipo_cambio"
            ]);
            
            // Obtener el ID del movimiento para inventario FIFO
            $movimiento_id = $pdo->lastInsertId();
            
            // Actualizar saldo de cuenta destino
            $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")->execute([$cantidad, $cuenta_id]);
            
            // 2. Pago a crédito o de contado
            if ($tipo_pago === 'credito') {
                // Crédito: generar adeudo con proveedor
                if ($cuenta_proveedor_id) {
                    $stmtMov->execute([
                        $grupo_id, $fecha, $servicio_id, 'salida', $moneda, $cantidad,
                        null, $cuenta_proveedor_id, $cliente_id, $proveedor_id, $tipo_cambio,
                        "Adeudo a proveedor: $cantidad $moneda (TC: $tipo_cambio)"
                    ]);
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ?, valor_mxn = valor_mxn + ? WHERE id = ?")
                        ->execute([$cantidad, $total_mxn, $cuenta_proveedor_id]);
                }
            } else {
                // Contado: descontar del MXN de la cuenta de pago inmediatamente
                if (!$cuenta_pago_id) {
                    throw new Exception('Debes seleccionar la cuenta de pago para compra de contado');
                }
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'salida', 'MXN', $total_mxn,
                    $cuenta_pago_id, null, $cliente_id, $proveedor_id, $tipo_cambio,
                    "Pago de contado: $$total_mxn MXN por $cantidad $moneda (TC: $tipo_cambio)"
                ]);
                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                    ->execute([$total_mxn, $cuenta_pago_id]);
            }
            
            // Registrar en inventario FIFO
            $stmtInv = $pdo->prepare("
                INSERT INTO inventario_monedas (moneda, fecha, cantidad_original, cantidad_restante, paridad, cuenta_id, movimiento_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInv->execute([$moneda, $fecha, $cantidad, $cantidad, $tipo_cambio, $cuenta_id, $movimiento_id]);
            
        } else {
            /**
             * VENTA de divisas (pago inmediato):
             * 1. Entrada MXN a cuenta empresa seleccionada
             * 2. FIFO aplicado de inmediato: descuenta de inventario_monedas y saldo wallet
             * 3. Ganancia (TC venta - TC compra) × cantidad → Utilidades AMIRI (id=1)
             * 4. Movimiento de salida del wallet por cuenta
             * 5. Registro vinculado al cliente para historial/reportes (sin adeudo)
             */

            if (!$cuenta_id) {
                throw new Exception('Debes seleccionar una cuenta MXN para recibir el pago del cliente');
            }

            // 1. Registrar entrada de MXN a la cuenta empresa
            $stmtMov->execute([
                $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $total_mxn,
                null, $cuenta_id, $cliente_id, $proveedor_id, $tipo_cambio,
                "Venta de $cantidad $moneda a TC $tipo_cambio"
            ]);
            $movimiento_venta_id = $pdo->lastInsertId();

            $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")
                ->execute([$total_mxn, $cuenta_id]);

            // 2. FIFO: consumir inventario de la moneda y descontar wallets
            $stmtFIFO = $pdo->prepare("
                SELECT im.id, im.cantidad_restante, im.paridad, im.cuenta_id
                FROM inventario_monedas im
                INNER JOIN cuentas c ON im.cuenta_id = c.id
                WHERE im.moneda = :moneda AND im.activo = 1 AND im.cantidad_restante > 0
                  AND c.tipo_cuenta IN (1, 2)
                ORDER BY im.fecha ASC
            ");
            $stmtFIFO->execute(['moneda' => $moneda]);
            $lotes = $stmtFIFO->fetchAll(PDO::FETCH_ASSOC);

            if (empty($lotes)) {
                throw new Exception("No hay inventario de $moneda disponible para vender");
            }

            $stmtConsumo = $pdo->prepare("
                INSERT INTO consumos_inventario
                    (movimiento_id, inventario_id, cantidad_consumida, paridad_compra, paridad_venta, ganancia)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtDeductInv = $pdo->prepare("
                UPDATE inventario_monedas SET cantidad_restante = cantidad_restante - ? WHERE id = ?
            ");

            $pendiente        = $cantidad;
            $gananciaTotal    = 0;
            $walletDeductions = []; // [cuenta_id => cantidad]

            foreach ($lotes as $lote) {
                if ($pendiente <= 0.001) break;
                $consumir     = min($pendiente, floatval($lote['cantidad_restante']));
                $gananciaLote = $consumir * ($tipo_cambio - floatval($lote['paridad']));
                $gananciaTotal += $gananciaLote;

                $stmtConsumo->execute([
                    $movimiento_venta_id, $lote['id'], $consumir,
                    $lote['paridad'], $tipo_cambio, $gananciaLote
                ]);

                // Descontar del inventario FIFO
                $stmtDeductInv->execute([$consumir, $lote['id']]);

                // Acumular deducción por cuenta de wallet
                $wId = $lote['cuenta_id'];
                $walletDeductions[$wId] = ($walletDeductions[$wId] ?? 0) + $consumir;

                $pendiente -= $consumir;
            }

            if ($pendiente > 0.001) {
                throw new Exception("Inventario insuficiente de $moneda. Faltan " . round($pendiente, 4) . " unidades");
            }

            // 3. Descontar del saldo de cada cuenta wallet y registrar movimiento de salida
            $stmtWallet = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?");
            foreach ($walletDeductions as $wId => $wCantidad) {
                $stmtWallet->execute([$wCantidad, $wId]);
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'salida', $moneda, $wCantidad,
                    $wId, null, $cliente_id, $proveedor_id, $tipo_cambio,
                    "Salida de $wCantidad $moneda del wallet por venta"
                ]);
            }

            // 4. Acreditar ganancia a Utilidades AMIRI (id=1)
            if (abs($gananciaTotal) > 0.001) {
                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = 1")
                    ->execute([$gananciaTotal]);
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $gananciaTotal,
                    null, 1, $cliente_id, $proveedor_id, $tipo_cambio,
                    "Utilidad FIFO por venta de $cantidad $moneda a TC $tipo_cambio"
                ]);
            }

            // 5. Registro histórico vinculado al cliente (sin actualizar saldo de cuenta cliente)
            if ($cliente_id) {
                $stmtCli = $pdo->prepare("SELECT cuenta_id FROM clientes WHERE id = ?");
                $stmtCli->execute([$cliente_id]);
                $cliData = $stmtCli->fetch(PDO::FETCH_ASSOC);
                if ($cliData && $cliData['cuenta_id']) {
                    $stmtMov->execute([
                        $grupo_id, $fecha, $servicio_id, 'entrada', $moneda, $cantidad,
                        null, $cliData['cuenta_id'], $cliente_id, $proveedor_id, $tipo_cambio,
                        "Registro venta: $cantidad $moneda a TC $tipo_cambio al cliente"
                    ]);
                    // No se actualiza el saldo de la cuenta del cliente — solo es registro histórico
                }
            }
        }
        
        // Registrar auditoría
        registrarAuditoria($pdo, [
            'accion' => 'INSERT',
            'modulo' => 'flujo_divisas',
            'tabla' => 'movimientos',
            'registro_id' => $grupo_id,
            'descripcion' => "$servicio: $cantidad $moneda (TC: $tipo_cambio)",
            'datos_nuevos' => [
                'servicio' => $servicio,
                'moneda' => $moneda,
                'cantidad' => $cantidad,
                'tipo_cambio' => $tipo_cambio,
                'cliente_id' => $cliente_id,
                'proveedor_id' => $proveedor_id
            ]
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'grupo_id' => $grupo_id,
            'message' => 'Movimiento registrado correctamente'
        ]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

/* ===== GET (Listar movimientos) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Inventario disponible de una moneda
    if (isset($_GET['tipo']) && $_GET['tipo'] === 'inventario') {
        $moneda = strtoupper($_GET['moneda'] ?? 'USD');
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(im.cantidad_restante), 0) AS disponible
            FROM inventario_monedas im
            WHERE im.moneda = ? AND im.activo = 1 AND im.cantidad_restante > 0
        ");
        $stmt->execute([$moneda]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['disponible' => floatval($row['disponible'])]);
        exit;
    }

    $moneda = $_GET['moneda'] ?? 'USD';
    
    // Obtener IDs de servicios de divisas según moneda
    if ($moneda === 'USD') {
        $servicios_ids = [5, 2]; // Compra (5) / Venta (2) Dólares
    } else {
        $servicios_ids = [6, 3]; // Compra (6) / Venta (3) USDT
    }
    
    $placeholders = implode(',', array_fill(0, count($servicios_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT 
            fm.id,
            fm.grupo_id,
            DATE_FORMAT(fm.fecha, '%Y-%m-%d') as fecha,
            fm.tipo,
            fm.moneda,
            fm.monto,
            fm.paridad as tipo_cambio,
            fm.descripcion,
            s.nombre as servicio,
            c.nombre as cliente,
            p.nombre as proveedor,
            cu.nombre as cuenta
        FROM movimientos fm
        LEFT JOIN servicios s ON fm.servicio_id = s.id
        LEFT JOIN clientes c ON fm.cliente_id = c.id
        LEFT JOIN proveedores p ON fm.proveedor_id = p.id
        LEFT JOIN cuentas cu ON COALESCE(fm.cuenta_destino_id, fm.cuenta_origen_id) = cu.id
        WHERE fm.servicio_id IN ($placeholders) AND fm.activo = 1
        ORDER BY fm.fecha DESC, fm.id DESC
        LIMIT 500
    ");
    $stmt->execute($servicios_ids);
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
}

/* ===== DELETE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $grupo_id = $data['grupo_id'] ?? null;
    
    if (!$grupo_id) {
        http_response_code(400);
        echo json_encode(['error' => 'grupo_id es requerido']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();

        // Obtener movimientos del grupo para revertir saldos
        $stmt = $pdo->prepare("SELECT * FROM movimientos WHERE grupo_id = ? AND activo = 1");
        $stmt->execute([$grupo_id]);
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($movimientos)) {
            throw new Exception('No se encontraron movimientos activos para este grupo');
        }

        $primerMov = $movimientos[0];
        $esCompra = in_array($primerMov['servicio_id'], [5, 6]); // 5=Compra USD, 6=Compra USDT

        if ($esCompra) {
            foreach ($movimientos as $mov) {
                // Revertir entrada de divisas al wallet
                if ($mov['tipo'] === 'entrada' && $mov['cuenta_destino_id']
                    && in_array($mov['moneda'], ['USD', 'USDT'])) {
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                        ->execute([$mov['monto'], $mov['cuenta_destino_id']]);
                    // Revertir lotes FIFO creados por esta compra
                    $pdo->prepare("UPDATE inventario_monedas SET activo = 0 WHERE movimiento_id = ?")
                        ->execute([$mov['id']]);
                }
                // Revertir adeudo con proveedor (compra a crédito)
                if ($mov['tipo'] === 'salida' && in_array($mov['moneda'], ['USD', 'USDT'])
                    && $mov['cuenta_destino_id'] && $mov['proveedor_id']) {
                    $valorMxnRevertir = $mov['monto'] * $mov['paridad'];
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ?, valor_mxn = valor_mxn - ? WHERE id = ?")
                        ->execute([$mov['monto'], $valorMxnRevertir, $mov['cuenta_destino_id']]);
                }
                // Revertir pago de contado (salida MXN de cuenta de pago)
                if ($mov['tipo'] === 'salida' && $mov['moneda'] === 'MXN' && $mov['cuenta_origen_id']) {
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")
                        ->execute([$mov['monto'], $mov['cuenta_origen_id']]);
                }
            }
        } else {
            // VENTA: revertir todos los efectos del nuevo modelo
            $gananciaTotal = 0;

            foreach ($movimientos as $mov) {
                // Revertir entrada MXN a cuenta empresa + restaurar inventario FIFO
                if ($mov['tipo'] === 'entrada' && $mov['moneda'] === 'MXN' && $mov['cuenta_destino_id']) {
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")
                        ->execute([$mov['monto'], $mov['cuenta_destino_id']]);

                    // Restaurar inventario FIFO (consumos ligados a este movimiento MXN)
                    $stmtConsumos = $pdo->prepare("
                        SELECT ci.inventario_id, ci.cantidad_consumida, ci.ganancia
                        FROM consumos_inventario ci WHERE ci.movimiento_id = ?
                    ");
                    $stmtConsumos->execute([$mov['id']]);
                    foreach ($stmtConsumos->fetchAll(PDO::FETCH_ASSOC) as $consumo) {
                        $pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = cantidad_restante + ? WHERE id = ?")
                            ->execute([$consumo['cantidad_consumida'], $consumo['inventario_id']]);
                        $gananciaTotal += floatval($consumo['ganancia']);
                    }

                    // Backward compat: cancelar deudas_clientes si existieran (modelo anterior)
                    $pdo->prepare("UPDATE deudas_clientes SET activo = 0 WHERE movimiento_venta_id = ? AND estado = 'pendiente'")
                        ->execute([$mov['id']]);
                }

                // Revertir salida de divisas del wallet (nuevo modelo)
                if ($mov['tipo'] === 'salida' && in_array($mov['moneda'], ['USD', 'USDT'])
                    && $mov['cuenta_origen_id']) {
                    $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")
                        ->execute([$mov['monto'], $mov['cuenta_origen_id']]);
                }
            }

            // Revertir ganancia de Utilidades AMIRI
            if (abs($gananciaTotal) > 0.001) {
                $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = 1")
                    ->execute([$gananciaTotal]);
            }
        }

        // Marcar movimientos como inactivos
        $pdo->prepare("UPDATE movimientos SET activo = 0 WHERE grupo_id = ?")->execute([$grupo_id]);
        
        // Registrar auditoría
        registrarAuditoria($pdo, [
            'accion' => 'DELETE',
            'modulo' => 'flujo_divisas',
            'tabla' => 'movimientos',
            'registro_id' => $grupo_id,
            'descripcion' => "Eliminación de movimientos grupo #$grupo_id"
        ]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Movimientos eliminados']);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Método no soportado
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
