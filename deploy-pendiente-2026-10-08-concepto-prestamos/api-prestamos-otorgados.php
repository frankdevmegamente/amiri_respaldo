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

// Helpers compuesto
function calcVencimiento($fechaBase, $valor, $unidad) {
    if (!$valor) return null;
    $d = new DateTime($fechaBase);
    $map = ['dia'=>'days','semana'=>'weeks','quincena'=>'days','mes'=>'months'];
    // quincena = 15 días
    if ($unidad === 'quincena') $d->modify('+' . (intval($valor)*15) . ' days');
    elseif ($unidad === 'semana') $d->modify('+' . intval($valor) . ' weeks');
    elseif ($unidad === 'dia') $d->modify('+' . intval($valor) . ' days');
    else $d->modify('+' . intval($valor) . ' months');
    return $d->format('Y-m-d');
}
function calcInteresCompuesto($capital, $tasaPct, $tasaPeriodo, $plazoValor, $plazoUnidad) {
    $capital = floatval($capital);
    $tasa = floatval($tasaPct)/100;
    if ($tasa <= 0 || !$plazoValor) return [0, $capital];
    // Convertir plazo a número de periodos de tasa
    // días por unidad
    $diasMap = ['dia'=>1,'semana'=>7,'quincena'=>15,'mes'=>30,'diario'=>1,'semanal'=>7,'quincenal'=>15,'mensual'=>30,'anual'=>365];
    $diasTasa = $diasMap[$tasaPeriodo] ?? 30;
    $diasPlazo = $diasMap[$plazoUnidad] ?? 30;
    $n = ($plazoValor * $diasPlazo) / $diasTasa;
    $total = $capital * pow(1 + $tasa, $n);
    $interes = round($total - $capital, 2);
    $total = round($total, 2);
    return [$interes, $total];
}

// ==========================================
// GET: Obtener lista de préstamos activos
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['resumen'])) {
            $stmt = $pdo->query("SELECT COUNT(DISTINCT CASE WHEN (p.monto_total_con_interes - p.monto_pagado) > 0.009 THEN COALESCE(d.nombre, p.persona_nombre) END) AS deudores, COALESCE(SUM(p.monto_total),0) AS total_prestado, COALESCE(SUM(p.monto_total_con_interes),0) AS total_con_interes, COALESCE(SUM(p.monto_interes),0) AS total_interes, COALESCE(SUM(p.monto_pagado),0) AS total_cobrado, COALESCE(SUM(p.monto_total_con_interes - p.monto_pagado),0) AS total_pendiente FROM prestamos_otorgados p LEFT JOIN deudores d ON d.id=p.deudor_id WHERE p.activo=1");
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            $r['deudores'] = (int)$r['deudores'];
            $r['total_prestado'] = (float)$r['total_prestado'];
            $r['total_con_interes'] = (float)$r['total_con_interes'];
            $r['total_interes'] = (float)$r['total_interes'];
            $r['total_cobrado'] = (float)$r['total_cobrado'];
            $r['total_pendiente'] = (float)$r['total_pendiente'];
            echo json_encode($r);
            exit;
        }
        if (isset($_GET['agrupado'])) {
            $stmt = $pdo->query("SELECT COALESCE(d.nombre,p.persona_nombre) as persona_nombre, p.deudor_id, COUNT(*) as total_prestamos, SUM(p.monto_total) as total_prestado, SUM(p.monto_total_con_interes) as total_con_interes, SUM(p.monto_interes) as total_interes, SUM(p.monto_pagado) as total_cobrado, SUM(p.monto_total_con_interes - p.monto_pagado) as total_pendiente, GROUP_CONCAT(p.id) as ids FROM prestamos_otorgados p LEFT JOIN deudores d ON d.id=p.deudor_id WHERE p.activo=1 GROUP BY COALESCE(d.nombre,p.persona_nombre), p.deudor_id ORDER BY total_pendiente DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif (isset($_GET['persona_nombre'])) {
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as cuenta_nombre, COALESCE(d.nombre,p.persona_nombre) as deudor_nombre, (SELECT m.grupo_id FROM movimientos m WHERE m.servicio_id=16 AND m.referencia_id=p.id AND m.estado='activo' ORDER BY m.id DESC LIMIT 1) as grupo_id FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id=c.id LEFT JOIN deudores d ON d.id=p.deudor_id WHERE p.activo=1 AND COALESCE(d.nombre,p.persona_nombre)=? ORDER BY p.fecha_prestamo DESC, p.id DESC");
            $stmt->execute([$_GET['persona_nombre']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif (isset($_GET['deudor_id'])) {
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as cuenta_nombre, COALESCE(d.nombre,p.persona_nombre) as deudor_nombre, (SELECT m.grupo_id FROM movimientos m WHERE m.servicio_id=16 AND m.referencia_id=p.id AND m.estado='activo' ORDER BY m.id DESC LIMIT 1) as grupo_id FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id=c.id LEFT JOIN deudores d ON d.id=p.deudor_id WHERE p.activo=1 AND p.deudor_id=? ORDER BY p.fecha_prestamo DESC, p.id DESC");
            $stmt->execute([$_GET['deudor_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT p.*, c.nombre as cuenta_nombre, COALESCE(d.nombre,p.persona_nombre) as deudor_nombre, (SELECT m.grupo_id FROM movimientos m WHERE m.servicio_id=16 AND m.referencia_id=p.id AND m.estado='activo' ORDER BY m.id DESC LIMIT 1) as grupo_id FROM prestamos_otorgados p LEFT JOIN cuentas c ON p.cuenta_id=c.id LEFT JOIN deudores d ON d.id=p.deudor_id WHERE p.activo=1 ORDER BY p.fecha_prestamo DESC, p.id DESC");
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
        $monto = (float)($_POST['monto'] ?? 0);
        $cuenta_id = $_POST['cuenta_id'] ?? null;
        if ($monto <= 0) {
            throw new Exception("Monto inválido. Debe ser mayor a $0.00");
        }
        if (empty($cuenta_id) || !filter_var($cuenta_id, FILTER_VALIDATE_INT)) {
            throw new Exception("Cuenta de origen obligatoria");
        }
        // Compat: persona_nombre o deudor_id
        $deudor_id = $_POST['deudor_id'] ?? null;
        $persona_nombre = trim($_POST['persona_nombre'] ?? '');
        // Si viene deudor_id, resolver nombre
        if ($deudor_id) {
            $stmtD = $pdo->prepare("SELECT nombre FROM deudores WHERE id=?");
            $stmtD->execute([$deudor_id]);
            $persona_nombre = $stmtD->fetchColumn() ?: $persona_nombre;
        }
        if ($persona_nombre === '' && !$deudor_id) {
            throw new Exception("Deudor obligatorio");
        }
        // Si solo viene nombre sin id, crear/buscar deudor automáticamente
        if (!$deudor_id && $persona_nombre !== '') {
            $stmtD = $pdo->prepare("SELECT id FROM deudores WHERE LOWER(nombre)=LOWER(?)");
            $stmtD->execute([$persona_nombre]);
            $deudor_id = $stmtD->fetchColumn();
            if (!$deudor_id) {
                $pdo->prepare("INSERT INTO deudores (nombre) VALUES (?)")->execute([$persona_nombre]);
                $deudor_id = $pdo->lastInsertId();
            }
        }

        $tasa = floatval($_POST['tasa_porcentaje'] ?? 0);
        $tasa_periodo = $_POST['tasa_periodo'] ?? 'mensual';
        $plazo_valor = $_POST['plazo_valor'] ? intval($_POST['plazo_valor']) : null;
        $plazo_unidad = $_POST['plazo_unidad'] ?? 'mes';
        $frecuencia = $_POST['frecuencia_pago'] ?? 'unico';

        $fecha_input = $_POST['fecha_prestamo'] ?? date('Y-m-d');
        validarMesNoCerrado($pdo, $fecha_input);
        $fecha = (strlen($fecha_input) === 10) ? $fecha_input . ' ' . date('H:i:s') : $fecha_input;
        // Concepto opcional (max 255)
        $concepto = trim($_POST['concepto'] ?? '');
        if (mb_strlen($concepto) > 255) $concepto = mb_substr($concepto, 0, 255);
        $conceptoDb = $concepto === '' ? null : $concepto;
        $fecha_venc = calcVencimiento(explode(' ', $fecha)[0], $plazo_valor, $plazo_unidad);
        list($monto_interes, $monto_total_con_interes) = calcInteresCompuesto($monto, $tasa, $tasa_periodo, $plazo_valor, $plazo_unidad);
        if ($tasa == 0) { $monto_interes = 0; $monto_total_con_interes = $monto; $fecha_venc = null; }
        
        $s = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ? FOR UPDATE"); 
        $s->execute([$cuenta_id]);
        $saldo_anterior = (float)$s->fetchColumn();
        
        if ($saldo_anterior < $monto) {
            throw new Exception("Saldo insuficiente. Tienes $" . number_format($saldo_anterior, 2));
        }

        $saldo_nuevo = $saldo_anterior - $monto;
        $descripcionMov = "Préstamo a ".$persona_nombre . ($concepto !== '' ? " - ".$concepto : "");

        // INSERT compatible: detecta columnas existentes (sandbox vs produccion)
        $colsDb = $pdo->query("SHOW COLUMNS FROM prestamos_otorgados")->fetchAll(PDO::FETCH_COLUMN);
        $has = function($c) use ($colsDb) { return in_array($c, $colsDb); };
        $insCols = []; $insVals = [];
        $put = function($col, $val) use ($has, &$insCols, &$insVals) { if ($has($col)) { $insCols[] = $col; $insVals[] = $val; } };
        $put('persona_nombre', $persona_nombre);
        $put('concepto', $conceptoDb);
        $put('deudor_id', $deudor_id ?: null);
        $put('cuenta_id', $cuenta_id);
        $put('monto_total', $monto);
        $put('monto_interes', $has('monto_interes') ? $monto_interes : null);
        $put('monto_total_con_interes', $has('monto_total_con_interes') ? $monto_total_con_interes : null);
        $put('tasa_porcentaje', $has('tasa_porcentaje') ? $tasa : null);
        $put('tasa_periodo', $has('tasa_periodo') ? $tasa_periodo : null);
        $put('plazo_valor', $has('plazo_valor') ? $plazo_valor : null);
        $put('plazo_unidad', $has('plazo_unidad') ? $plazo_unidad : null);
        $put('frecuencia_pago', $has('frecuencia_pago') ? $frecuencia : null);
        $put('fecha_prestamo', $fecha);
        $put('fecha_vencimiento', $has('fecha_vencimiento') ? $fecha_venc : null);
        $put('tipo_interes', $has('tipo_interes') ? 'compuesto' : null);
        $put('estado', 'pendiente');
        // descripcion legacy en sandbox
        $put('descripcion', $has('descripcion') ? $descripcionMov ?? ("Préstamo a ".$persona_nombre . ($concepto !== '' ? " - ".$concepto : "")) : null);
        $put('activo', $has('activo') ? 1 : null);
        // filtrar nulos de columnas no soportadas ya hecho por $has; quitar entradas null solo si columna no acepta? se dejan
        $ph = implode(',', array_fill(0, count($insCols), '?'));
        $pdo->prepare("INSERT INTO prestamos_otorgados (" . implode(',', $insCols) . ") VALUES ($ph)")->execute($insVals);
        $p_id = $pdo->lastInsertId();
        $g_id = round(microtime(true) * 1000);

        // AQUÍ ESTÁ LA CORRECCIÓN CLAVE: Se añade beneficiario (el nombre de la persona)
        // en lugar de referencia_tipo
        $pdo->prepare("
            INSERT INTO movimientos (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, usuario_id, servicio_id, referencia_id, beneficiario, activo) 
            VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, ?, 16, ?, ?, 1)
        ")->execute([$g_id, explode(' ', $fecha)[0], $monto, $descripcionMov, $cuenta_id, $_SESSION['usuario_id'], $p_id, $persona_nombre]);
        
        $mov_id = $pdo->lastInsertId();

        $pdo->exec("SET @hyperion_saldo_auth = 1");
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

    try {
        validarMesNoCerrado($pdo, $fecha_input);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

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

            $total_a_cobrar = floatval($p['monto_total_con_interes'] ?: $p['monto_total']);
            $pendiente = $total_a_cobrar - (float)$p['monto_pagado'];
            
            if ($abono > $pendiente) $abono = $pendiente;

            $nuevo_pagado = (float)$p['monto_pagado'] + $abono;
            $nuevo_estado = ($nuevo_pagado >= $total_a_cobrar - 0.001) ? 'pagado' : 'parcial';

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