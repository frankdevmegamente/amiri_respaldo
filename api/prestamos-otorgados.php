<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi(); 
requireAdmin(); 
$pdo = getPDO();

// ==========================================
// GET: Obtener lista de préstamos activos
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT p.*, c.nombre as cuenta_nombre FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id = c.id WHERE p.activo = 1 ORDER BY p.fecha_prestamo DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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
        $fecha = $_POST['fecha_prestamo'] ?? date('Y-m-d H:i:s');
        
        // Verificar saldo
        $s = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?"); 
        $s->execute([$cuenta_id]);
        if ($s->fetchColumn() < $monto) throw new Exception("Saldo insuficiente en la caja seleccionada.");

        $pdo->prepare("INSERT INTO prestamos_otorgados (persona_nombre, cuenta_id, monto_total, fecha_prestamo, estado) VALUES (?, ?, ?, ?, 'pendiente')")
            ->execute([trim($_POST['persona_nombre']), $cuenta_id, $monto, $fecha]);
        
        $p_id = $pdo->lastInsertId();
        $g_id = round(microtime(true) * 1000);

        $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, usuario_id, servicio_id, referencia_id, activo) VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, ?, 16, ?, 1)")
            ->execute([$g_id, $fecha, $monto, "Préstamo a ".$_POST['persona_nombre'], $cuenta_id, $_SESSION['usuario_id'], $p_id]);

        $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?")->execute([$monto, $cuenta_id]);
        
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
// PUT: Cobrar (Individual o Múltiple con CASCADA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $ids = $input['ids'] ?? [];
    $pago_total_solicitado = (float)($input['monto_a_pagar'] ?? 0);
    $cuenta_destino_id = $input['cuenta_pago_id'] ?? null;
    $fecha_pago = $input['fecha_pago'] ?? date('Y-m-d H:i:s');

    if (empty($ids) || !$cuenta_destino_id || $pago_total_solicitado <= 0) {
        http_response_code(400); 
        echo json_encode(['error' => 'Datos inválidos para el cobro']); 
        exit;
    }

    $pdo->beginTransaction();
    try {
        $monto_restante_por_repartir = $pago_total_solicitado;
        $monto_acumulado_procesado = 0;
        $g_id = round(microtime(true) * 1000);

        foreach ($ids as $prestamo_id) {
            if ($monto_restante_por_repartir <= 0.001) break; // Si ya se acabó el dinero, paramos.

            $stmt = $pdo->prepare("SELECT * FROM prestamos_otorgados WHERE id = ? AND activo = 1"); 
            $stmt->execute([$prestamo_id]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$p || $p['estado'] === 'pagado') continue;

            $pendiente = (float)$p['monto_total'] - (float)$p['monto_pagado'];
            
            // Lógica de Cascada
            $abono = ($monto_restante_por_repartir >= $pendiente) ? $pendiente : $monto_restante_por_repartir;
            
            $nuevo_pagado = (float)$p['monto_pagado'] + $abono;
            $nuevo_estado = ($nuevo_pagado >= (float)$p['monto_total'] - 0.01) ? 'pagado' : 'parcial';

            $pdo->prepare("UPDATE prestamos_otorgados SET monto_pagado = ?, estado = ? WHERE id = ?")
                ->execute([$nuevo_pagado, $nuevo_estado, $prestamo_id]);

            $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, usuario_id, servicio_id, referencia_id, activo) VALUES (?, ?, 'entrada', ?, 'MXN', ?, ?, ?, 17, ?, 1)")
                ->execute([$g_id, $fecha_pago, $abono, "Cobro de préstamo a {$p['persona_nombre']} (ID: #{$prestamo_id})", $cuenta_destino_id, $_SESSION['usuario_id'], $prestamo_id]);

            $monto_restante_por_repartir -= $abono;
            $monto_acumulado_procesado += $abono;
        }

        // Ingresamos a la caja solo lo que realmente se procesó
        $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")
            ->execute([$monto_acumulado_procesado, $cuenta_destino_id]);

        $pdo->commit(); 
        echo json_encode(['success' => true]);
    } catch (Exception $e) { 
        if ($pdo->inTransaction()) $pdo->rollBack(); 
        http_response_code(400); 
        echo json_encode(['error' => $e->getMessage()]); 
    }
    exit;
}