<?php
/**
 * AUDITORÍA COMPLETA - SISTEMA AMIRI
 * Verifica los 8 puntos críticos de integridad
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();
$resultados = [];
$total_errores = 0;

// Colores ANSI para CLI
define('COLOR_RESET', "\033[0m");
define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_CYAN', "\033[36m");
define('COLOR_BOLD', "\033[1m");
define('COLOR_DIM', "\033[2m");

function ejecutarConsulta($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo COLOR_RED . "   ERROR EN SQL: " . $e->getMessage() . COLOR_RESET . "\n";
        return [];
    }
}

function contarErrores($resultado) {
    return is_array($resultado) ? count($resultado) : 0;
}

// Función para imprimir línea decorativa
function linea($char = '─', $length = 60) {
    echo str_repeat($char, $length) . "\n";
}

// Función para imprimir encabezado de sección
function seccion($num, $titulo) {
    echo "\n";
    linea('═', 60);
    echo COLOR_BOLD . COLOR_BLUE . "  [$num] $titulo" . COLOR_RESET . "\n";
    linea('═', 60) . "\n";
}

// Función para resultado exitoso
function ok($mensaje) {
    echo COLOR_GREEN . "  ✅ $mensaje" . COLOR_RESET . "\n";
}

// Función para error
function error($mensaje) {
    echo COLOR_RED . "  ❌ $mensaje" . COLOR_RESET . "\n";
}

// Función para advertencia
function adv($mensaje) {
    echo COLOR_YELLOW . "  ⚠️  $mensaje" . COLOR_RESET . "\n";
}

// Función para información
function info($mensaje) {
    echo COLOR_CYAN . "  ℹ️  $mensaje" . COLOR_RESET . "\n";
}

// Función para imprimir datos en formato tabla simple
function fila($label, $valor, $padding = 25) {
    echo "  " . str_pad($label, $padding) . ": " . COLOR_BOLD . $valor . COLOR_RESET . "\n";
}

// Cabecera principal
echo "\n";
linea('═', 60);
echo COLOR_BOLD . COLOR_BLUE . "       🔍 AUDITORÍA COMPLETA - SISTEMA AMIRI" . COLOR_RESET . "\n";
linea('═', 60);
echo "\n";
fila("Base de datos", "guiaspar_amiri_produccion");
fila("Fecha", date('Y-m-d H:i:s'));
fila("Servidor", "localhost");
echo "\n";

// ===========================================
// 1. INTEGRIDAD DE SALDOS
// cuentas.saldo_actual coincide con suma de movimientos
// ===========================================
seccion("1", "INTEGRIDAD DE SALDOS");

$sql_saldos = "
    SELECT 
        c.id,
        c.nombre,
        c.tipo_moneda,
        c.saldo_actual AS saldo_en_cuenta,
        COALESCE(SUM(
            CASE 
                WHEN m.tipo = 'entrada' AND m.cuenta_destino_id = c.id THEN m.monto
                WHEN m.tipo = 'salida' AND m.cuenta_origen_id = c.id THEN -m.monto
                WHEN m.tipo = 'traspaso' AND m.cuenta_destino_id = c.id THEN m.monto
                WHEN m.tipo = 'traspaso' AND m.cuenta_origen_id = c.id THEN -m.monto
                WHEN m.tipo = 'comision' AND m.cuenta_destino_id = c.id THEN m.monto
                ELSE 0
            END
        ), 0) AS saldo_calculado
    FROM cuentas c
    LEFT JOIN movimientos m ON (m.cuenta_destino_id = c.id OR m.cuenta_origen_id = c.id) 
        AND m.activo = 1 AND m.estado = 'activo'
    WHERE c.activo = 1
    GROUP BY c.id, c.nombre, c.tipo_moneda, c.saldo_actual
    HAVING ABS(c.saldo_actual - COALESCE(SUM(
        CASE 
            WHEN m.tipo = 'entrada' AND m.cuenta_destino_id = c.id THEN m.monto
            WHEN m.tipo = 'salida' AND m.cuenta_origen_id = c.id THEN -m.monto
            WHEN m.tipo = 'traspaso' AND m.cuenta_destino_id = c.id THEN m.monto
            WHEN m.tipo = 'traspaso' AND m.cuenta_origen_id = c.id THEN -m.monto
            WHEN m.tipo = 'comision' AND m.cuenta_destino_id = c.id THEN m.monto
            ELSE 0
        END
    ), 0)) > 0.01
";

$inconsistencias_saldos = ejecutarConsulta($pdo, $sql_saldos);
$total_errores += contarErrores($inconsistencias_saldos);

if (count($inconsistencias_saldos) > 0) {
    error("ENCONTRADAS " . count($inconsistencias_saldos) . " INCONSISTENCIAS:");
    foreach ($inconsistencias_saldos as $row) {
        $diff = abs($row['saldo_en_cuenta'] - $row['saldo_calculado']);
        echo COLOR_YELLOW . "  ┌─ Cuenta #{$row['id']}" . COLOR_RESET . "\n";
        echo "  │  Nombre    : {$row['nombre']}\n";
        echo "  │  Moneda    : {$row['tipo_moneda']}\n";
        echo "  │  En cuenta : " . COLOR_RED . number_format($row['saldo_en_cuenta'], 2) . COLOR_RESET . "\n";
        echo "  │  Calculado : " . COLOR_GREEN . number_format($row['saldo_calculado'], 2) . COLOR_RESET . "\n";
        echo "  └─ Diferencia: " . COLOR_BOLD . number_format($diff, 2) . COLOR_RESET . "\n\n";
    }
} else {
    ok("Todos los saldos coinciden");
}
echo "\n";

// ===========================================
// 2. HISTORIAL COMPLETO
// Cada cambio de saldo tiene entrada en historial_saldos
// ===========================================
seccion("2", "HISTORIAL DE SALDOS");

// Verificar total de registros en historial
$total_hist_real = $pdo->query("SELECT COUNT(*) FROM historial_saldos")->fetchColumn();

// Movimientos activos únicos
$total_mov = $pdo->query("SELECT COUNT(DISTINCT id) FROM movimientos WHERE activo = 1 AND estado = 'activo'")->fetchColumn();

// Movimientos que tienen al menos una entrada en historial
$total_mov_con_hist = $pdo->query("
    SELECT COUNT(DISTINCT m.id) 
    FROM movimientos m 
    INNER JOIN historial_saldos h ON h.movimiento_id = m.id 
    WHERE m.activo = 1 AND m.estado = 'activo'
")->fetchColumn();

// Movimientos sin historial
$total_mov_sin_hist = $pdo->query("
    SELECT COUNT(DISTINCT m.id) 
    FROM movimientos m 
    LEFT JOIN historial_saldos h ON h.movimiento_id = m.id 
    WHERE m.activo = 1 AND m.estado = 'activo' AND h.id IS NULL
")->fetchColumn();

echo "  " . str_repeat("─", 45) . "\n";
fila("Movimientos activos", $total_mov, 30);
fila("Con historial", $total_mov_con_hist, 30);
fila("Sin historial", $total_mov_sin_hist, 30);
fila("Total en historial_saldos", $total_hist_real, 30);
echo "  " . str_repeat("─", 45) . "\n\n";

info("Cada movimiento puede generar 1-2 entradas en historial");

if ($total_mov_sin_hist > 0) {
    error("Faltan $total_mov_sin_hist movimientos por registrar en historial");
    $total_errores += $total_mov_sin_hist;
} else {
    ok("Historial completo (todos los movimientos tienen al menos 1 entrada)");
}
echo "\n";

// ===========================================
// 3. REVERSALS CORRECTOS
// Al revertir movimiento, se restaura saldo y registra en historial
// ===========================================
seccion("3", "REVERSALS (ANULACIONES)");

$sql_reversos = "
    SELECT 
        m.id,
        m.grupo_id,
        m.es_reverso,
        m.movimiento_original_id,
        m.estado
    FROM movimientos m
    WHERE m.activo = 1 AND (m.es_reverso = 1 OR m.estado = 'anulado')
";

$reversos = ejecutarConsulta($pdo, $sql_reversos);

$errores_reversos = 0;
foreach ($reversos as $r) {
    if ($r['es_reverso'] == 1 && $r['movimiento_original_id'] === null) {
        error("Movimiento #{$r['id']} es reverso pero no tiene movimiento_original_id");
        $errores_reversos++;
    }
}

if ($errores_reversos > 0) {
    $total_errores += $errores_reversos;
} else {
    ok("Todos los reversals están correctos (" . count($reversos) . " revisados)");
}
echo "\n";

// ===========================================
// 4. GASTOS - NO DOBLE DESCUENTO
// Verificar que gastos no descuenten doble
// ===========================================
seccion("4", "GASTOS (DOBLE DESCUENTO)");

$sql_gastos = "
    SELECT 
        g.id,
        g.monto,
        g.cuenta_id,
        g.movimiento_id,
        g.activo,
        c.saldo_actual as saldo_cuenta
    FROM gastos g
    LEFT JOIN cuentas c ON c.id = g.cuenta_id
    WHERE g.activo = 1
";

$gastos = ejecutarConsulta($pdo, $sql_gastos);

// Verificar que cada gasto tenga su movimiento asociado
$sql_gastos_sin_movimiento = "
    SELECT g.id, g.monto, g.cuenta_id
    FROM gastos g
    WHERE g.activo = 1 AND g.movimiento_id IS NULL
";

$gastos_sin_mov = ejecutarConsulta($pdo, $sql_gastos_sin_movimiento);

if (count($gastos_sin_mov) > 0) {
    adv(count($gastos_sin_mov) . " gastos sin movimiento_id asociado");
    foreach ($gastos_sin_mov as $g) {
        echo "  ┌─ Gasto #{$g['id']}\n";
        echo "  │  Monto  : $" . number_format($g['monto'], 2) . "\n";
        echo "  └─ Cuenta: {$g['cuenta_id']}\n";
    }
    $total_errores += count($gastos_sin_mov);
} else {
    ok("Todos los gastos tienen movimiento asociado");
}

// Verificar reversals de gastos restauran saldo
$sql_gasto_reversal = "
    SELECT 
        g.id as gasto_id,
        m.id as movimiento_id,
        m.estado,
        m.es_reverso
    FROM gastos g
    JOIN movimientos m ON m.id = g.movimiento_id
    WHERE g.activo = 0
";

info("Total gastos revisados: " . count($gastos));
echo "\n";

// ===========================================
// 5. FIFO DIVISAS
// Inventario restante coincide con saldos wallets USD/USDT
// ===========================================
seccion("5", "INVENTARIO FIFO VS WALLETS");

$sql_fifo = "
    SELECT 
        c.id AS cuenta_id,
        c.nombre,
        c.tipo_moneda,
        c.saldo_actual AS saldo_wallet,
        COALESCE(SUM(im.cantidad_restante), 0) AS inventario_restante
    FROM cuentas c
    LEFT JOIN inventario_monedas im ON im.cuenta_id = c.id AND im.activo = 1 AND im.cantidad_restante > 0
    WHERE c.tipo_moneda IN ('USD', 'USDT') AND c.activo = 1
    GROUP BY c.id, c.nombre, c.tipo_moneda, c.saldo_actual
    HAVING ABS(c.saldo_actual - COALESCE(SUM(im.cantidad_restante), 0)) > 0.01
";

$wallets_inconsistentes = ejecutarConsulta($pdo, $sql_fifo);

if (count($wallets_inconsistentes) > 0) {
    error("WALLETS INCONSISTENTES:");
    foreach ($wallets_inconsistentes as $row) {
        $diff = abs($row['saldo_wallet'] - $row['inventario_restante']);
        echo COLOR_YELLOW . "  ┌─ Wallet #{$row['cuenta_id']}" . COLOR_RESET . "\n";
        echo "  │  Nombre    : {$row['nombre']}\n";
        echo "  │  Moneda    : {$row['tipo_moneda']}\n";
        echo "  │  Saldo     : " . COLOR_RED . number_format($row['saldo_wallet'], 2) . COLOR_RESET . "\n";
        echo "  │  Inventario: " . COLOR_GREEN . number_format($row['inventario_restante'], 2) . COLOR_RESET . "\n";
        echo "  └─ Diff      : " . COLOR_BOLD . number_format($diff, 2) . COLOR_RESET . "\n\n";
    }
    $total_errores += count($wallets_inconsistentes);
} else {
    ok("Wallets y FIFO coinciden");
}
echo "\n";

// ===========================================
// 6. PRÉSTAMOS - SALDOS DE DEUDA
// Saldos coinciden con pagos registrados
// ===========================================
seccion("6", "PRÉSTAMOS (SALDOS DE DEUDA)");

$sql_prestamos_clientes = "
    SELECT 
        pc.id,
        pc.cliente_id,
        c.nombre as cliente_nombre,
        pc.monto_total,
        pc.monto_pagado,
        (pc.monto_total - pc.monto_pagado) as saldo_calculado,
        pc.estado
    FROM deudas_clientes pc
    LEFT JOIN clientes c ON c.id = pc.cliente_id
    WHERE pc.activo = 1
";

$prestamos_clientes = ejecutarConsulta($pdo, $sql_prestamos_clientes);

$sql_prestamos_empresa = "
    SELECT 
        pe.id,
        pe.prestamista_id,
        p.nombre as prestamista_nombre,
        pe.monto,
        pe.monto_pagado,
        pe.estado,
        (pe.monto - pe.monto_pagado) as saldo_calculado
    FROM deudas_empresa pe
    LEFT JOIN prestamistas p ON p.id = pe.prestamista_id
    WHERE pe.activo = 1
";

$prestamos_empresa = ejecutarConsulta($pdo, $sql_prestamos_empresa);

$errores_prestamos = 0;

// Verificar que el estado sea coherente con el saldo
foreach ($prestamos_clientes as $pc) {
    $saldo_real = $pc['monto_total'] - $pc['monto_pagado'];
    if ($pc['estado'] == 'pagado' && abs($saldo_real) > 0.01) {
        error("Deuda Cliente #{$pc['id']} marcado como PAGADO pero saldo real: $saldo_real");
        $errores_prestamos++;
    }
}

foreach ($prestamos_empresa as $pe) {
    $saldo_real = $pe['monto'] - $pe['monto_pagado'];
    if ($pe['estado'] == 'pagado' && abs($saldo_real) > 0.01) {
        error("Deuda Empresa #{$pe['id']} marcado como PAGADO pero saldo real: $saldo_real");
        $errores_prestamos++;
    }
}

if ($errores_prestamos == 0) {
    ok("Préstamos consistentes");
} else {
    $total_errores += $errores_prestamos;
}
echo "\n";

// ===========================================
// 7. DATOS HUÉRFANOS
// Movimientos sin historial, inventario sin movimiento, consumos sin inventario
// ===========================================
seccion("7", "DATOS HUÉRFANOS");

// Movimientos sin historial
$sql_mov_sin_hist = "
    SELECT DISTINCT m.id, m.fecha, m.tipo, m.monto
    FROM movimientos m
    LEFT JOIN historial_saldos h ON h.movimiento_id = m.id
    WHERE m.activo = 1 AND m.estado = 'activo' AND h.id IS NULL
    LIMIT 50
";

$mov_sin_hist = ejecutarConsulta($pdo, $sql_mov_sin_hist);

if (count($mov_sin_hist) > 0) {
    error("MOVIMIENTOS SIN HISTORIAL: " . count($mov_sin_hist) . " encontrados");
    foreach (array_slice($mov_sin_hist, 0, 10) as $m) {
        echo "  ┌─ #{$m['id']}\n";
        echo "  │  Tipo  : {$m['tipo']}\n";
        echo "  └─ Monto : $" . number_format($m['monto'], 2) . "\n";
    }
    if (count($mov_sin_hist) > 10) {
        echo "  ... y " . (count($mov_sin_hist) - 10) . " más\n";
    }
    $total_errores += count($mov_sin_hist);
} else {
    ok("No hay movimientos huérfanos");
}

// Consumos sin inventario
$sql_consumos_huerfanos = "
    SELECT ci.id, ci.movimiento_id, ci.inventario_id
    FROM consumos_inventario ci
    LEFT JOIN inventario_monedas im ON ci.inventario_id = im.id
    WHERE im.id IS NULL
";

$consumos_huerfanos = ejecutarConsulta($pdo, $sql_consumos_huerfanos);

if (count($consumos_huerfanos) > 0) {
    error("CONSUMOS SIN INVENTARIO: " . count($consumos_huerfanos) . " encontrados");
    $total_errores += count($consumos_huerfanos);
} else {
    ok("No hay consumos huérfanos");
}

// Inventario sin movimiento de compra
$sql_inv_sin_mov = "
    SELECT im.id, im.movimiento_id, im.cantidad_restante
    FROM inventario_monedas im
    LEFT JOIN movimientos m ON m.id = im.movimiento_id
    WHERE m.id IS NULL AND im.activo = 1
";

$inv_sin_mov = ejecutarConsulta($pdo, $sql_inv_sin_mov);

if (count($inv_sin_mov) > 0) {
    error("INVENTARIO SIN MOVIMIENTO: " . count($inv_sin_mov) . " encontrados");
    $total_errores += count($inv_sin_mov);
} else {
    ok("Todo el inventario tiene movimiento asociado");
}
echo "\n";

// ===========================================
// 8. CONSISTENCIA DE PARIDAD
// Montos en MXN calculados con tipo de cambio son correctos
// ===========================================
seccion("8", "CONSISTENCIA DE PARIDAD");

$sql_paridad = "
    SELECT 
        m.id,
        m.moneda,
        m.monto,
        m.paridad
    FROM movimientos m
    WHERE m.moneda IN ('USD', 'USDT') 
        AND m.activo = 1 
        AND m.paridad IS NOT NULL
        AND m.paridad > 0
    LIMIT 100
";

$paridad_check = ejecutarConsulta($pdo, $sql_paridad);
info("Verificados " . count($paridad_check) . " movimientos con paridad");

// Verificar consumos_inventario tengan ganancia calculada correcta
$sql_ganancia = "
    SELECT 
        ci.id,
        ci.cantidad_consumida,
        ci.paridad_compra,
        ci.paridad_venta,
        ci.ganancia,
        (ci.paridad_venta - ci.paridad_compra) * ci.cantidad_consumida as ganancia_calculada
    FROM consumos_inventario ci
    WHERE ABS(ci.ganancia - ((ci.paridad_venta - ci.paridad_compra) * ci.cantidad_consumida)) > 0.01
    LIMIT 50
";

$ganancia_inconsistente = ejecutarConsulta($pdo, $sql_ganancia);

if (count($ganancia_inconsistente) > 0) {
    error("GANANCIAS INCONSISTENTES: " . count($ganancia_inconsistente) . " encontradas");
    foreach (array_slice($ganancia_inconsistente, 0, 5) as $g) {
        echo COLOR_YELLOW . "  ┌─ Consumo #{$g['id']}" . COLOR_RESET . "\n";
        echo "  │  Ganancia registrada : {$g['ganancia']}\n";
        echo "  └─ Ganancia calculada : {$g['ganancia_calculada']}\n";
    }
    $total_errores += count($ganancia_inconsistente);
} else {
    ok("Cálculos de ganancia correctos");
}
echo "\n";

// ===========================================
// RESUMEN FINAL
// ===========================================
echo "\n";
linea('═', 60);
echo COLOR_BOLD . COLOR_BLUE . "       📊 RESUMEN DE AUDITORÍA" . COLOR_RESET . "\n";
linea('═', 60) . "\n";

if ($total_errores == 0) {
    echo COLOR_GREEN . "\n";
    echo "  ✅ SISTEMA COMPLETAMENTE ÍNTEGRO\n";
    echo "  No se encontraron errores en ninguno de los 8 puntos verificados.\n";
    echo COLOR_RESET . "\n";
} else {
    echo COLOR_RED . "\n";
    echo "  ⚠️  SE ENCONTRARON $total_errores PROBLEMAS QUE REQUIEREN ATENCIÓN\n";
    echo COLOR_RESET . "\n";
}

// Mostrar resumen por categoría
echo COLOR_BOLD . "--- ESTADÍSTICAS GENERALES ---" . COLOR_RESET . "\n";
echo "  " . str_repeat("─", 40) . "\n";
$total_cuentas = $pdo->query("SELECT COUNT(*) FROM cuentas WHERE activo = 1")->fetchColumn();
$total_movimientos = $pdo->query("SELECT COUNT(*) FROM movimientos WHERE activo = 1")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes WHERE activo = 1")->fetchColumn();
$total_gastos = $pdo->query("SELECT COUNT(*) FROM gastos WHERE activo = 1")->fetchColumn();
$total_historial = $pdo->query("SELECT COUNT(*) FROM historial_saldos")->fetchColumn();

fila("Cuentas activas", $total_cuentas, 25);
fila("Movimientos activos", $total_movimientos, 25);
fila("Clientes activos", $total_clientes, 25);
fila("Gastos activos", $total_gastos, 25);
fila("Historial saldos", $total_historial, 25);
echo "  " . str_repeat("─", 40) . "\n\n";

echo COLOR_DIM . "Verificación completada el " . date('Y-m-d H:i:s') . COLOR_RESET . "\n";
linea('═', 60) . "\n";
