<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi(); 
requireAdmin(); 
$pdo = getPDO();

// ========================================================================
// PARCHE MÁGICO: Crear los servicios 16 y 17 automáticamente si no existen
// Esto arregla la "Diferencia Negativa" en la pantalla de Flujo
// ========================================================================
$pdo->exec("INSERT IGNORE INTO servicios (id, nombre, descripcion, tipo_movimiento, activo) VALUES (16, 'Préstamo Otorgado', 'Salida de dinero por préstamo a terceros', 'salida', 1)");
$pdo->exec("INSERT IGNORE INTO servicios (id, nombre, descripcion, tipo_movimiento, activo) VALUES (17, 'Cobro de Préstamo', 'Entrada de dinero por cobro de préstamo', 'entrada', 1)");

// ==========================================
// GET: Obtener lista de préstamos activos
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['agrupado'])) {
            $stmt = $pdo->query("SELECT persona_nombre, COUNT(*) as total_prestamos, SUM(monto_total) as total_prestado, SUM(monto_pagado) as total_cobrado, SUM(monto_total - monto_pagado) as total_pendiente, GROUP_CONCAT(id) as ids FROM prestamos_otorgados WHERE activo = 1 GROUP BY persona_nombre ORDER BY total_pendiente DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif (isset($_GET['persona_nombre'])) {
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as cuenta_nombre FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id = c.id WHERE p.activo = 1 AND p.persona_nombre = ? ORDER BY p.fecha_prestamo DESC, p.id DESC");
            $stmt->execute([$_GET['persona_nombre']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT p.*, c.nombre as cuenta_nombre FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id = c.id WHERE p.activo = 1 ORDER BY p.fecha_prestamo DESC, p.id DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en BD: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// POST: Registrar Nuevo Préstamo (Salida)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $monto = (float)$_POST['monto'];
        $cuenta_id = $_POST['cuenta_id'];
        $persona_nombre = trim($_POST['persona_nombre']);
        
        $fecha_input = $_POST['fecha_prestamo'] ?? date('Y-m-d');
        $fecha = (strlen($fecha_input) === 10) ? $fecha_input . ' ' . date('H:i:s') : $fecha_input;
        
        $s = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE"); 
        $s->execute([$cuenta_id]);
        $saldo_anterior = (float)$s->fetchColumn();
        
        if ($saldo_anterior < $monto) {
            throw new Exception("Saldo insuficiente. Tienes $" . number_format($saldo_anterior, 2));
        }

        $saldo_nuevo = $saldo_anterior - $monto;

        $pdo->prepare("INSERT INTO prestamos_otorgados (persona_nombre, cuenta_id, monto_total, fecha_prestamo, estado) VALUES (?, ?, ?, ?, 'pendiente')")
            ->execute([$persona_nombre, $cuenta_id, $monto, $fecha]);
        
        $p_id = $pdo->lastInsertId();
        $g_id = round(microtime(true) * 1000);

        // AQUÍ ESTÁ LA CORRECCIÓN CLAVE: Se añade beneficiario (el nombre de la persona)
        // en lugar de referencia_tipo
        $pdo->prepare("
            INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, usuario_id, servicio_id, referencia_id, beneficiario, activo) 
            VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, ?, 16, ?, ?, 1)
        ")->execute([$g_id, explode(' ', $fecha)[0], $monto, "Préstamo a ".$persona_nombre, $cuenta_id, $_SESSION['usuario_id'], $p_id, $persona_nombre]);
        
        $mov_id = $pdo->lastInsertId();

        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldo_nuevo, $cuenta_id]);
        
        $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")
            ->execute([$cuenta_id, $mov_id, $monto, $saldo_anterior, $saldo_nuevo]);
        
        $pdo->commit(); 
        echo json_encode(['success' => true]);
    } catch (Exception $e) { 
        if ($pdo->inTransaction()) $pdo->rollBack(); 
        http_response_code(400); 
        echo json_encode(['error' => $e->getMessage()]); 
    }
    exit;
}

// ==========================================
// PUT: Cobrar (Lógica directa por arreglo de pagos)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $pagos = $input['pagos'] ?? [];
    $cuenta_destino_id = $input['cuenta_pago_id'] ?? null;
    
    $fecha_input = $input['fecha_pago'] ?? date('Y-m-d');
    $fecha_pago = (strlen($fecha_input) === 10) ? $fecha_input . ' ' . date('H:i:s') : $fecha_input;

    if (empty($pagos) || !$cuenta_destino_id) {
        http_response_code(400); 
        echo json_encode(['error' => 'Datos inválidos para el cobro']); 
        exit;
    }

    require_once __DIR__ . '/../src/config/helpers.php';

    $pdo->beginTransaction();
    try {
        $g_id = round(microtime(true) * 1000);
        $monto_procesado_total = 0;

        foreach ($pagos as $pagoItem) {
            $prestamo_id = (int)$pagoItem['id'];
            $abono = (float)$pagoItem['abono'];
            
            if ($abono <= 0) continue;

            $stmt = $pdo->prepare("SELECT * FROM prestamos_otorgados WHERE id = ? AND estado != 'pagado' AND activo = 1 FOR UPDATE");
            $stmt->execute([$prestamo_id]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$p) continue;

            $pendiente = (float)$p['monto_total'] - (float)$p['monto_pagado'];
            
            if ($abono > $pendiente) $abono = $pendiente;

            $nuevo_pagado = (float)$p['monto_pagado'] + $abono;
            $nuevo_estado = ($nuevo_pagado >= (float)$p['monto_total'] - 0.001) ? 'pagado' : 'parcial';

            $pdo->prepare("UPDATE prestamos_otorgados SET monto_pagado = ?, estado = ?, actualizado_en = NOW() WHERE id = ?")
                ->execute([$nuevo_pagado, $nuevo_estado, $prestamo_id]);

            $pdo->prepare("
                INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, usuario_id, servicio_id, referencia_id, beneficiario, activo) 
                VALUES (?, ?, 'entrada', ?, 'MXN', ?, ?, ?, 17, ?, ?, 1)
            ")->execute([
                $g_id, 
                explode(' ', $fecha_pago)[0], 
                $abono, 
                "Cobro de préstamo a " . $p['persona_nombre'], 
                $cuenta_destino_id, 
                $_SESSION['usuario_id'], 
                $prestamo_id,
                $p['persona_nombre']
            ]);

            $mov_id = $pdo->lastInsertId();

            actualizarSaldoCuenta($pdo, $cuenta_destino_id, $abono, 'entrada', $mov_id, "Cobro de préstamo a " . $p['persona_nombre']);

            $monto_procesado_total += $abono;
        }

        if ($monto_procesado_total <= 0) throw new Exception("No se procesó ningún monto válido.");

        $pdo->commit(); 
        echo json_encode(['success' => true]);
    } catch (Exception $e) { 
        if ($pdo->inTransaction()) $pdo->rollBack(); 
        http_response_code(400); 
        echo json_encode(['error' => $e->getMessage()]); 
    }
    exit;
}