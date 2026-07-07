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
require_once __DIR__ . '/../src/config/helpers.php';



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

        validarMesNoCerrado($pdo, $fecha);
        validarMesNoMayorQueSesion($fecha);

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

        

        // Calcular total en MXN (respetar total manual si el usuario lo ingresó)
        if (isset($_POST['total_mxn']) && $_POST['total_mxn'] !== '') {
            $total_mxn = round(floatval($_POST['total_mxn']), 2);
            // Recalcular TC efectivo para que ganancia FIFO coincida con el total real
            if ($cantidad > 0) {
                $tipo_cambio = round($total_mxn / $cantidad, 6);
            }
        } else {
            $total_mxn = round($cantidad * $tipo_cambio, 2);
        }

        

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

            actualizarSaldoCuenta($pdo, $cuenta_id, $cantidad, 'entrada', $movimiento_id, "Compra de $cantidad $moneda a TC $tipo_cambio");

            

            // 2. Pago a crédito o de contado

            if ($tipo_pago === 'credito') {

                // Crédito: generar adeudo con proveedor

                if ($cuenta_proveedor_id) {

                        $stmtMov->execute([

                            $grupo_id, $fecha, $servicio_id, 'salida', $moneda, $cantidad,

                            null, $cuenta_proveedor_id, $cliente_id, $proveedor_id, $tipo_cambio,

                            "Adeudo a proveedor: $cantidad $moneda (TC: $tipo_cambio)"

                        ]);
                        $mov_adeudo_id = $pdo->lastInsertId();

                        actualizarSaldoCuenta($pdo, $cuenta_proveedor_id, $cantidad, 'entrada', $mov_adeudo_id, "Adeudo proveedor: $cantidad $moneda");

                        // Actualizar valor_mxn con el equivalente en MXN del adeudo
                        $valor_mxn_adeudo = round($cantidad * $tipo_cambio, 2);
                        $pdo->prepare("UPDATE cuentas SET valor_mxn = COALESCE(valor_mxn, 0) + ? WHERE id = ?")
                            ->execute([$valor_mxn_adeudo, $cuenta_proveedor_id]);

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
                $mov_contado_id = $pdo->lastInsertId();

                actualizarSaldoCuenta($pdo, $cuenta_pago_id, $total_mxn, 'salida', $mov_contado_id, "Pago contado: $$total_mxn MXN por $cantidad $moneda");

            }

            

            // Registrar en inventario FIFO

            $stmtInv = $pdo->prepare("

                INSERT INTO inventario_monedas (moneda, fecha, cantidad_original, cantidad_restante, paridad, cuenta_id, movimiento_id)

                VALUES (?, ?, ?, ?, ?, ?, ?)

            ");

            $stmtInv->execute([$moneda, $fecha, $cantidad, $cantidad, $tipo_cambio, $cuenta_id, $movimiento_id]);

            

        } else {
            /**
             * VENTA de divisas:
             * 1. Entrada MXN a cuenta empresa seleccionada (CONTADO SIEMPRE)
             * 2. FIFO aplicado de inmediato: descuenta de inventario y wallet
             * 3. Reparto de Ganancia: Comisión Promotor y Utilidad Neta Amiri
             * 4. Registro vinculado al cliente (sin crear deuda)
             */

            if (!$cuenta_id) {
                throw new Exception('Debes seleccionar una cuenta MXN para recibir el pago del cliente');
            }

            // --- 1. OBTENER DATOS DEL PROMOTOR ---
            $porcentaje_promotor = 0;
            $promotor_id = null;
            $cuenta_promotor_id = null;

            if ($cliente_id) {
                $stmtCli = $pdo->prepare("
                    SELECT c.promotor_id, c.porcentaje_promotor, p.cuenta_id AS cuenta_promotor_id 
                    FROM clientes c 
                    LEFT JOIN promotores p ON c.promotor_id = p.id 
                    WHERE c.id = ?
                ");
                $stmtCli->execute([$cliente_id]);
                $cli = $stmtCli->fetch(PDO::FETCH_ASSOC);
                if ($cli) {
                    $promotor_id = $cli['promotor_id'];
                    $cuenta_promotor_id = $cli['cuenta_promotor_id'];
                    $porcentaje_promotor = floatval($cli['porcentaje_promotor']);
                }
            }

            // 2. Registrar entrada de MXN a la cuenta empresa (Caja) INMEDIATA
            $stmtMov->execute([
                $grupo_id, $fecha, $servicio_id, 'entrada', 'MXN', $total_mxn,
                null, $cuenta_id, $cliente_id, $proveedor_id, $tipo_cambio,
                "Venta de $cantidad $moneda a TC $tipo_cambio"
            ]);
            $movimiento_venta_id = $pdo->lastInsertId();

            actualizarSaldoCuenta($pdo, $cuenta_id, $total_mxn, 'entrada', $movimiento_venta_id, "Venta de $cantidad $moneda a TC $tipo_cambio");

            // 3. FIFO: consumir inventario de la moneda y descontar wallets
            $stmtFIFO = $pdo->prepare("
                SELECT im.id, im.cantidad_restante, im.paridad, im.cuenta_id
                FROM inventario_monedas im
                INNER JOIN cuentas c ON im.cuenta_id = c.id
                WHERE im.moneda = :moneda AND im.activo = 1 AND im.cantidad_restante > 0.001
                  AND c.tipo_cuenta IN (1, 2)
                ORDER BY im.fecha ASC
                FOR UPDATE
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
            $walletDeductions = [];

            foreach ($lotes as $lote) {
                if ($pendiente <= 0.001) break;
                $cantidadRestante = (float) $lote['cantidad_restante'];
                if ($cantidadRestante <= 0.001) continue;
                $consumir     = min($pendiente, $cantidadRestante);
                $gananciaLote = round($consumir * ($tipo_cambio - (float) $lote['paridad']), 2);
                $gananciaTotal = round($gananciaTotal + $gananciaLote, 2);

                $stmtConsumo->execute([
                    $movimiento_venta_id, $lote['id'], $consumir,
                    $lote['paridad'], $tipo_cambio, $gananciaLote
                ]);

                $stmtDeductInv->execute([$consumir, $lote['id']]);

                $wId = $lote['cuenta_id'];
                $walletDeductions[$wId] = ($walletDeductions[$wId] ?? 0) + $consumir;

                $pendiente -= $consumir;
            }

            if ($pendiente > 0.001) {
                throw new Exception("Inventario insuficiente de $moneda. Faltan " . round($pendiente, 4) . " unidades");
            }

            // Descontar del saldo de cada cuenta wallet
            foreach ($walletDeductions as $wId => $wCantidad) {
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'salida', $moneda, $wCantidad,
                    $wId, null, $cliente_id, $proveedor_id, $tipo_cambio,
                    "Salida de $wCantidad $moneda del wallet por venta"
                ]);
                $mov_wallet_id = $pdo->lastInsertId();
                actualizarSaldoCuenta($pdo, $wId, $wCantidad, 'salida', $mov_wallet_id, "Salida wallet: $wCantidad $moneda por venta");
            }

            // --- 4. REPARTO DE GANANCIAS (COMISION PROMOTOR) ---
            $comision_promotor = 0;
            if ($promotor_id && $cuenta_promotor_id) {
                // La comisión del promotor se calcula sobre el volumen en MXN
                $comision_promotor = round($total_mxn * ($porcentaje_promotor / 100), 2);
            }
            
            $utilidad_amiri = round($gananciaTotal - $comision_promotor, 2);

            // Acreditar Utilidad Amiri
            if (abs($utilidad_amiri) > 0.001) {
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $utilidad_amiri,
                    null, 1, $cliente_id, $proveedor_id, $tipo_cambio,
                    "Utilidad neta FIFO por venta de $cantidad $moneda"
                ]);
                $mov_utilidad_id = $pdo->lastInsertId();
                actualizarSaldoCuenta($pdo, 1, $utilidad_amiri, 'entrada', $mov_utilidad_id, "Utilidad neta FIFO por venta de $cantidad $moneda");
            }

            // Pagar comisión al promotor
            if ($comision_promotor > 0.001) {
                $stmtMov->execute([
                    $grupo_id, $fecha, $servicio_id, 'comision', 'MXN', $comision_promotor,
                    null, $cuenta_promotor_id, $cliente_id, $promotor_id, $tipo_cambio,
                    "Comisión Promotor por venta de $cantidad $moneda"
                ]);
                $mov_comision_id = $pdo->lastInsertId();
                actualizarSaldoCuenta($pdo, $cuenta_promotor_id, $comision_promotor, 'entrada', $mov_comision_id, "Comisión Promotor por venta de $cantidad $moneda");
            }

            // 5. Registro histórico (sin deuda)
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



/* ===== DELETE (Reversión con trazabilidad) ===== */

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

        // Crear nuevo grupo para los movimientos reverso
        $stmtMax = $pdo->query("SELECT COALESCE(MAX(grupo_id), 0) + 1 FROM movimientos");
        $nuevo_grupo = intval($stmtMax->fetchColumn());
        $reversos_creados = 0;

        $primerMov = $movimientos[0];
        $esCompra = in_array($primerMov['servicio_id'], [5, 6]); // 5=Compra USD, 6=Compra USDT

        foreach ($movimientos as $mov) {
            $monto_reverso = abs($mov['monto']);
            $descripcion_reverso = 'REVERSO: ' . ($mov['descripcion'] ?? 'Movimiento #' . $mov['id']);

            // 1. Crear movimiento reverso con cuentas invertidas y es_reverso=1
            $stmtRev = $pdo->prepare("
                INSERT INTO movimientos
                (grupo_id, fecha, servicio_id, tipo, moneda, monto,
                 cuenta_origen_id, cuenta_destino_id, cliente_id, promotor_id,
                 proveedor_id, prestamista_id, paridad, descripcion,
                 es_reverso, movimiento_original_id, estado, activo)
                VALUES (?, CURDATE(), ?, 'ajuste', ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        1, ?, 'activo', 1)
            ");
            $stmtRev->execute([
                $nuevo_grupo, $mov['servicio_id'], $mov['moneda'], $monto_reverso,
                $mov['cuenta_destino_id'], $mov['cuenta_origen_id'],
                $mov['cliente_id'], $mov['promotor_id'],
                $mov['proveedor_id'], $mov['prestamista_id'],
                $mov['paridad'], $descripcion_reverso,
                $mov['id']
            ]);
            $reverso_id = $pdo->lastInsertId();
            $reversos_creados++;

            // 2. Revertir saldos usando actualizarSaldoCuenta (respeta trigger)
            $tipo = $mov['tipo'];
            $moneda = $mov['moneda'];

            // ── COMPRA (servicio 5/6) ──────────────────────────────────
            // Caso 1: Entrada USD/USDT a wallet
            if ($esCompra && $tipo === 'entrada' && $mov['cuenta_destino_id'] && in_array($moneda, ['USD', 'USDT'])) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_destino_id'], $monto_reverso, 'salida', $reverso_id, $descripcion_reverso);
                // Desactivar lote FIFO creado por esta compra
                $pdo->prepare("UPDATE inventario_monedas SET activo = 0 WHERE movimiento_id = ?")->execute([$mov['id']]);
            }

            // Caso 2: Salida USD/USDT a proveedor (compra a crédito)
            if ($esCompra && $tipo === 'salida' && in_array($moneda, ['USD', 'USDT']) && $mov['cuenta_destino_id'] && $mov['proveedor_id']) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_destino_id'], $monto_reverso, 'salida', $reverso_id, $descripcion_reverso);
                // Revertir valor_mxn adicional (no cubierto por actualizarSaldoCuenta)
                $valorMxn = round($mov['monto'] * $mov['paridad'], 2);
                $pdo->prepare("UPDATE cuentas SET valor_mxn = GREATEST(0, valor_mxn - ?) WHERE id = ?")
                    ->execute([$valorMxn, $mov['cuenta_destino_id']]);
            }

            // Caso 3: Salida MXN por pago de contado (Compra)
            if ($esCompra && $tipo === 'salida' && $moneda === 'MXN' && $mov['cuenta_origen_id']) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_origen_id'], $monto_reverso, 'entrada', $reverso_id, $descripcion_reverso);
            }

            // ── VENTA (servicio 2/3) ───────────────────────────────────
            // Caso 4: Entrada MXN a cuenta empresa + restaurar FIFO
            if (!$esCompra && $tipo === 'entrada' && $moneda === 'MXN' && $mov['cuenta_destino_id']) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_destino_id'], $monto_reverso, 'salida', $reverso_id, $descripcion_reverso);
                // Restaurar inventario FIFO (consumos ligados a este movimiento MXN)
                $stmtConsumos = $pdo->prepare("
                    SELECT ci.inventario_id, ci.cantidad_consumida
                    FROM consumos_inventario ci WHERE ci.movimiento_id = ?
                ");
                $stmtConsumos->execute([$mov['id']]);
                foreach ($stmtConsumos->fetchAll(PDO::FETCH_ASSOC) as $consumo) {
                    $pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = cantidad_restante + ? WHERE id = ?")
                        ->execute([$consumo['cantidad_consumida'], $consumo['inventario_id']]);
                }
                // Backward compat: cancelar deudas_clientes (modelo anterior)
                $pdo->prepare("UPDATE deudas_clientes SET activo = 0 WHERE movimiento_venta_id = ? AND estado = 'pendiente'")
                    ->execute([$mov['id']]);
            }

            // Caso 5: Salida USD/USDT de wallet
            if (!$esCompra && $tipo === 'salida' && in_array($moneda, ['USD', 'USDT']) && $mov['cuenta_origen_id']) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_origen_id'], $monto_reverso, 'entrada', $reverso_id, $descripcion_reverso);
            }

            // Caso 6: Comisión (entrada a cuenta destino — Utilidad o Promotor)
            if (!$esCompra && $tipo === 'comision' && $mov['cuenta_destino_id']) {
                actualizarSaldoCuenta($pdo, $mov['cuenta_destino_id'], $monto_reverso, 'salida', $reverso_id, $descripcion_reverso);
            }

            // 3. Marcar movimiento original como anulado
            $pdo->prepare("UPDATE movimientos SET estado = 'anulado', activo = 0 WHERE id = ?")->execute([$mov['id']]);
        }

        // Registrar auditoría
        registrarAuditoria($pdo, [
            'accion' => 'DELETE',
            'modulo' => 'flujo_divisas',
            'tabla' => 'movimientos',
            'registro_id' => $grupo_id,
            'descripcion' => "Reversión del grupo #$grupo_id ($reversos_creados movimientos)"
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Grupo revertido correctamente ($reversos_creados reversos)"]);

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

