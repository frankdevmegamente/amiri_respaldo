<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/helpers.php';

initApi(); 
requireAdmin(); 
$pdo = getPDO();

/* ==========================================================
   1. GET: LEER DEUDAS (TABLA O INDIVIDUAL)
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $prestamista_id = isset($_GET['prestamista_id']) ? (int)$_GET['prestamista_id'] : 0;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM deudas_empresa WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($prestamo ?: ['error' => 'No encontrado']);
        } elseif ($prestamista_id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM deudas_empresa WHERE prestamista_id = ? AND activo = 1 ORDER BY estado = 'pagado', fecha DESC, id DESC");
            $stmt->execute([$prestamista_id]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM deudas_empresa WHERE activo = 1 ORDER BY estado = 'pagado', fecha DESC, id DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/* ==========================================================
   2. POST: CREAR NUEVO PRÉSTAMO
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prestamista_id = $_POST['prestamista_id'] ?? null;
        $cuenta_id = $_POST['cuenta_id'] ?? null;
        $monto = (float)($_POST['monto'] ?? 0);
        $fecha = $_POST['fecha'] ?? null;

        if (!$prestamista_id || !$cuenta_id || $monto <= 0 || !$fecha) {
            throw new Exception("Datos insuficientes para crear el préstamo");
        }

        $pdo->beginTransaction();
        
        $pdo->prepare("INSERT INTO deudas_empresa (prestamista_id, cuenta_id, monto, fecha, descripcion, estado, monto_pagado, activo) VALUES (?,?,?,?,?,'pendiente',0,1)")
            ->execute([$prestamista_id, $cuenta_id, $monto, $fecha, $_POST['descripcion'] ?? null]);
        
        $prestamo_id = $pdo->lastInsertId();
        $grupo_id = round(microtime(true) * 1000);
        
        $stmt = $pdo->prepare("SELECT nombre FROM prestamistas WHERE id = ?");
        $stmt->execute([$prestamista_id]);
        $prestamista_nombre = $stmt->fetchColumn();
        
        $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, prestamista_id, usuario_id, servicio_id, referencia_id, activo) VALUES (?, ?, 'entrada', ?, 'MXN', ?, ?, ?, ?, 10, ?, 1)")
            ->execute([$grupo_id, $fecha, $monto, 'Préstamo de ' . $prestamista_nombre, $cuenta_id, $prestamista_id, $_SESSION['usuario_id'], $prestamo_id]);

        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual + ? WHERE id = ?")->execute([$monto, $cuenta_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(400); echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/* ==========================================================
   PUT (ABONO CON CONTROL MANUAL DE CANTIDADES UNIFICADO)
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pagos = $input['pagos'] ?? []; 
    $cuenta_id = $input['cuenta_pago_id'] ?? null;
    $fecha_pago = $input['fecha_pago'] ?? date('Y-m-d H:i:s');

    if (empty($pagos) || !$cuenta_id) {
        http_response_code(400); echo json_encode(['error' => 'Datos inválidos']); exit;
    }

    $pdo->beginTransaction();
    try {
        // --- 1. FILTRO DE SEGURIDAD (CANDADO ANTI-SOBREPAGOS) ---
        $pagos_reales = [];
        $total_real_a_pagar = 0;

        foreach ($pagos as $p) {
            $id = (int)$p['id'];
            $abono_solicitado = (float)$p['abono'];
            if ($abono_solicitado <= 0) continue;

            $stmtD = $pdo->prepare("SELECT monto, monto_pagado, prestamista_id FROM deudas_empresa WHERE id = ? AND activo = 1 FOR UPDATE");
            $stmtD->execute([$id]);
            $deuda = $stmtD->fetch(PDO::FETCH_ASSOC);

            if (!$deuda) continue;

            $pendiente = round((float)$deuda['monto'] - (float)$deuda['monto_pagado'], 2);
            
            // MAGIA AQUÍ: Si el abono es mayor al pendiente, lo bajamos matemáticamente al límite exacto.
            $abono_seguro = ($abono_solicitado > $pendiente) ? $pendiente : $abono_solicitado;

            if ($abono_seguro > 0) {
                $pagos_reales[] = [
                    'id' => $id,
                    'abono' => $abono_seguro,
                    'prestamista_id' => $deuda['prestamista_id'],
                    'monto' => $deuda['monto'],
                    'monto_pagado' => $deuda['monto_pagado']
                ];
                $total_real_a_pagar += $abono_seguro;
            }
        }

        if ($total_real_a_pagar <= 0) {
            throw new Exception("No hay deudas válidas para abonar.");
        }

        // --- 2. VALIDACIÓN DE LA CAJA REAL ---
        $stmtS = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE");
        $stmtS->execute([$cuenta_id]);
        if ($stmtS->fetchColumn() < $total_real_a_pagar) {
            throw new Exception("Saldo insuficiente en caja. Necesitas $" . number_format($total_real_a_pagar, 2));
        }

        $g_id = round(microtime(true) * 1000);

        // --- 3. APLICACIÓN DE PAGOS VERIFICADOS ---
        foreach ($pagos_reales as $p) {
            $nuevo_pagado = $p['monto_pagado'] + $p['abono'];
            $estado = ($nuevo_pagado >= $p['monto'] - 0.01) ? 'pagado' : 'parcial';
            
            $pdo->prepare("UPDATE deudas_empresa SET monto_pagado = ?, estado = ?, fecha_pago = ? WHERE id = ?")
                ->execute([$nuevo_pagado, $estado, $fecha_pago, $p['id']]);
            
            // Registrar movimiento de salida (pago al prestamista)
            $stmtMov = $pdo->prepare("INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, prestamista_id, usuario_id, servicio_id, referencia_id, activo) VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, ?, ?, 14, ?, 1)");
            $stmtMov->execute([$g_id, $fecha_pago, $p['abono'], "Abono manual a préstamo #{$p['id']}", $cuenta_id, $p['prestamista_id'], $_SESSION['usuario_id'], $p['id']]);
            $movimiento_id = $pdo->lastInsertId();
            
            // Restar saldo de cuenta origen con registro en historial_saldos
            actualizarSaldoCuenta($pdo, $cuenta_id, $p['abono'], 'salida', $movimiento_id, "Abono manual a préstamo #{$p['id']}");
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(400); echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/* ==========================================================
   4. DELETE: ELIMINAR PRÉSTAMO
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT * FROM deudas_empresa WHERE id = ?");
        $stmt->execute([$id]);
        $d = $stmt->fetch();

        if ($d['monto_pagado'] > 0) throw new Exception("No se puede eliminar con abonos existentes");

        $pdo->prepare("UPDATE deudas_empresa SET activo = 0 WHERE id = ?")->execute([$id]);
        
        // Restar saldo de cuenta usando función centralizada que registra en historial_saldos
        $stmtSaldo = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE");
        $stmtSaldo->execute([$d['cuenta_id']]);
        $saldo_anterior = (float)$stmtSaldo->fetchColumn();
        $saldo_nuevo = $saldo_anterior - $d['monto'];
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$saldo_nuevo, $d['cuenta_id']]);
        
        // Registrar en historial_saldos
        $stmtMov = $pdo->prepare("SELECT id FROM movimientos WHERE referencia_id = ? AND servicio_id = 10 AND estado = 'activo' ORDER BY id DESC LIMIT 1");
        $stmtMov->execute([$id]);
        $movimiento = $stmtMov->fetch(PDO::FETCH_ASSOC);
        $movimiento_id = $movimiento ? $movimiento['id'] : null;
        
        $pdo->prepare("INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo) VALUES (?, ?, 'salida', ?, ?, ?)")
            ->execute([$d['cuenta_id'], $movimiento_id, $d['monto'], $saldo_anterior, $saldo_nuevo]);
        
        // Anular movimiento en flujo
        $pdo->prepare("UPDATE movimientos SET estado = 'anulado', motivo_correccion = 'Préstamo eliminado manualmente desde el panel' WHERE referencia_id = ? AND servicio_id = 10")->execute([$id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(400); echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
/* ==========================================================
   1. GET (LEER DEUDAS PARA LA TABLA O INDIVIDUAL)
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $prestamista_id = isset($_GET['prestamista_id']) ? (int)$_GET['prestamista_id'] : 0;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // <-- ESTO ES LO QUE FALTABA
    
    try {
        if ($id > 0) {
            // Busca un solo préstamo para llenar el modal de pago individual
            $stmt = $pdo->prepare("SELECT * FROM deudas_empresa WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prestamo) {
                echo json_encode($prestamo);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Préstamo no encontrado']);
            }
        } elseif ($prestamista_id > 0) {
            // Busca las deudas específicas del prestamista para la tabla
            $stmt = $pdo->prepare("SELECT * FROM deudas_empresa WHERE prestamista_id = ? AND activo = 1 ORDER BY estado = 'pagado', fecha DESC, id DESC");
            $stmt->execute([$prestamista_id]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Muestra todas las deudas activas
            $stmt = $pdo->query("SELECT * FROM deudas_empresa WHERE activo = 1 ORDER BY estado = 'pagado', fecha DESC, id DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al leer la base de datos: ' . $e->getMessage()]);
    }
    exit;
}


/* POST (CREAR PRÉSTAMO) */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    try {

        $prestamista_id = $_POST['prestamista_id'] ?? null;

        $cuenta_id = $_POST['cuenta_id'] ?? null;

        $monto = (float)($_POST['monto'] ?? 0);

        $fecha = $_POST['fecha'] ?? null;



        if (!$prestamista_id || !$cuenta_id || $monto <= 0 || !$fecha) {

            Logger::warning('Datos inválidos para crear préstamo', $_POST);

            http_response_code(400);

            echo json_encode(['error' => 'Datos inválidos', 'datos' => $_POST]);

            exit;

        }



        $pdo->beginTransaction();

        

        $pdo->prepare("

            INSERT INTO deudas_empresa

            (prestamista_id, cuenta_id, monto, fecha, descripcion, estado, monto_pagado, activo)

            VALUES (?,?,?,?,?,'pendiente',0,1)

        ")->execute([

            $prestamista_id,

            $cuenta_id,

            $monto,

            $fecha,

            $_POST['descripcion'] ?? null

        ]);

        

        $prestamo_id = $pdo->lastInsertId();

        

        // Generar ID de grupo único para este préstamo

        $grupo_id = round(microtime(true) * 1000);

        

        // Obtener nombre del prestamista

        $stmt = $pdo->prepare("SELECT nombre FROM prestamistas WHERE id = ?");

        $stmt->execute([$prestamista_id]);

        $prestamista_nombre = $stmt->fetchColumn();

        

        // Registrar en movimientos con prestamista_id Y grupo_id
        // servicio_id = 10 (Préstamo de Prestamista)
        // moneda = MXN por defecto para préstamos
        $stmtMov = $pdo->prepare("
            INSERT INTO movimientos
            (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_destino_id, prestamista_id, usuario_id, servicio_id, referencia_id, activo)
            VALUES (?, ?, 'entrada', ?, 'MXN', ?, ?, ?, 10, ?, 1)
        ");
        $stmtMov->execute([
            $grupo_id,
            $fecha,
            $monto,
            'Préstamo de ' . $prestamista_nombre . ': ' . ($_POST['descripcion'] ?? 'Sin descripción'),
            $cuenta_id,
            $prestamista_id,
            $_SESSION['usuario_id'],
            $prestamo_id // <--- EL HILO CONDUCTOR (ID de la deuda)
        ]);
        $movimiento_id = $pdo->lastInsertId();
        
        // Actualizar saldo usando la función centralizada que registra en historial_saldos
        actualizarSaldoCuenta($pdo, $cuenta_id, $monto, 'entrada', $movimiento_id, 'Préstamo de ' . $prestamista_nombre);

        

        // Registrar auditoría

        registrarAuditoria($pdo, [

            'accion' => 'INSERT',

            'modulo' => 'prestamos',

            'tabla' => 'deudas_empresa',

            'registro_id' => $prestamo_id,

            'descripcion' => "Registró préstamo: $" . number_format($monto, 2) . " de $prestamista_nombre",

            'datos_nuevos' => [

                'id' => $prestamo_id,

                'prestamista_id' => $prestamista_id,

                'prestamista_nombre' => $prestamista_nombre,

                'cuenta_id' => $cuenta_id,

                'monto' => $monto,

                'fecha' => $fecha,

                'descripcion' => $_POST['descripcion'] ?? null

            ]

        ]);



        $pdo->commit();

        

        Logger::info('Préstamo creado exitosamente', ['prestamo_id' => $prestamo_id]);

        echo json_encode(['success' => true, 'prestamo_id' => $prestamo_id]);

        exit;

        

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        Logger::error('Error al crear préstamo', [

            'message' => $e->getMessage(),

            'trace' => $e->getTraceAsString()

        ]);

        http_response_code(500);

        echo json_encode(['error' => 'Error al crear préstamo: ' . $e->getMessage()]);

        exit;

    }

}







/* DELETE (BORRADO PROFUNDO Y AUTOMATIZADO) */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    // 1. Obtener datos del préstamo antes de desactivar
    $stmt = $pdo->prepare("
        SELECT pp.*, p.nombre AS prestamista_nombre
        FROM deudas_empresa pp
        JOIN prestamistas p ON pp.prestamista_id = p.id
        WHERE pp.id = ?
    ");
    $stmt->execute([$id]);
    $datos_anteriores = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos_anteriores) {
        http_response_code(404);
        echo json_encode(['error' => 'Préstamo no encontrado']);
        exit;
    }

    // 2. CANDADO DE SEGURIDAD: Evitar borrar préstamos que ya tienen pagos
    if ((float)$datos_anteriores['monto_pagado'] > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede eliminar un préstamo que ya tiene abonos. Por favor, revierte los pagos primero.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 3. Desactivar el préstamo de la tabla de deudas (Soft Delete)
        $pdo->prepare("
            UPDATE deudas_empresa SET activo = 0 WHERE id = ?
        ")->execute([$id]);

        // 4. RESTAR EL DINERO DE LA CAJA AUTOMÁTICAMENTE
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $pdo->prepare("
            UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?
        ")->execute([$datos_anteriores['monto'], $datos_anteriores['cuenta_id']]);

        // 5. BUSCAR Y ANULAR EL MOVIMIENTO DEL FLUJO
        // Buscamos el movimiento exacto que metió este dinero para anularlo
        $stmtMov = $pdo->prepare("
            SELECT grupo_id 
            FROM movimientos 
            WHERE prestamista_id = ? AND cuenta_destino_id = ? AND monto = ? AND servicio_id = 10 AND estado = 'activo'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtMov->execute([
            $datos_anteriores['prestamista_id'], 
            $datos_anteriores['cuenta_id'], 
            $datos_anteriores['monto']
        ]);
        $movimiento = $stmtMov->fetch(PDO::FETCH_ASSOC);

        if ($movimiento) {
            $pdo->prepare("
                UPDATE movimientos 
                SET estado = 'anulado', motivo_correccion = 'Préstamo eliminado manualmente desde el panel' 
                WHERE grupo_id = ?
            ")->execute([$movimiento['grupo_id']]);
        }
        
        // 6. Registrar en auditoría el proceso completo
        registrarAuditoria($pdo, [
            'accion' => 'DELETE',
            'modulo' => 'prestamos',
            'tabla' => 'deudas_empresa',
            'registro_id' => $id,
            'descripcion' => "Eliminó préstamo de {$datos_anteriores['prestamista_nombre']} por $" . number_format($datos_anteriores['monto'], 2) . " y restó el saldo de la caja automáticamente.",
            'datos_anteriores' => $datos_anteriores
        ]);

        $pdo->commit();
        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el préstamo y ajustar la caja: ' . $e->getMessage()]);
        exit;
    }
}



http_response_code(405);

echo json_encode(['error' => 'Método no permitido']);