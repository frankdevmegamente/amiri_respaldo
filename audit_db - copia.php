<?php

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

echo "=== AUDITORÍA BASE DE DATOS AMIRI ===\n\n";

// 1. Verificar saldos de cuentas vs movimientos
echo "1. VERIFICACIÓN DE SALDOS DE CUENTAS\n";
$stmt = $pdo->query("
    SELECT 
        c.id,
        c.nombre,
        c.tipo_moneda,
        c.saldo_actual AS saldo_en_cuenta,
        COALESCE(SUM(
            CASE 
                WHEN m.tipo = 'entrada' AND m.cuenta_destino_id = c.id THEN m.monto
                WHEN m.tipo = 'salida' AND m.cuenta_origen_id = c.id THEN -m.monto
                WHEN m.tipo = 'comision' AND m.cuenta_destino_id = c.id THEN m.monto
                ELSE 0
            END
        ), 0) AS saldo_calculado_movimientos
    FROM cuentas c
    LEFT JOIN movimientos m ON (m.cuenta_destino_id = c.id OR m.cuenta_origen_id = c.id) AND m.activo = 1
    WHERE c.activo = 1
    GROUP BY c.id, c.nombre, c.tipo_moneda, c.saldo_actual
    HAVING ABS(c.saldo_actual - COALESCE(SUM(
        CASE 
            WHEN m.tipo = 'entrada' AND m.cuenta_destino_id = c.id THEN m.monto
            WHEN m.tipo = 'salida' AND m.cuenta_origen_id = c.id THEN -m.monto
            WHEN m.tipo = 'comision' AND m.cuenta_destino_id = c.id THEN m.monto
            ELSE 0
        END
    ), 0)) > 0.01
");

$inconsistencias = $stmt->fetchAll();
if (count($inconsistencias) > 0) {
    echo "❌ INCONSISTENCIAS ENCONTRADAS:\n";
    foreach ($inconsistencias as $row) {
        echo "   Cuenta #{$row['id']} ({$row['nombre']}): En cuenta={$row['saldo_en_cuenta']}, Calculado={$row['saldo_calculado_movimientos']}\n";
    }
} else {
    echo "✅ Todos los saldos coinciden\n\n";
}

// 2. Verificar historial_saldos faltante
echo "2. VERIFICAR HISTORIAL_SALDOS FALTANTE\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as total_movimientos FROM movimientos WHERE activo = 1
");
$total_mov = $stmt->fetch()['total_movimientos'];

$stmt = $pdo->query("
    SELECT COUNT(DISTINCT movimiento_id) as total_con_historial FROM historial_saldos
");
$total_hist = $stmt->fetch()['total_con_historial'];

echo "   Movimientos activos: $total_mov\n";
echo "   Movimientos con historial: $total_hist\n";
if ($total_mov > $total_hist) {
    echo "   ❌ Faltan " . ($total_mov - $total_hist) . " registros en historial_saldos\n";
} else {
    echo "   ✅ Historial completo\n\n";
}

// 3. Verificar inventario FIFO vs saldos wallets
echo "3. VERIFICAR INVENTARIO FIFO VS WALLETS\n";
$stmt = $pdo->query("
    SELECT 
        c.id AS cuenta_id,
        c.nombre,
        c.saldo_actual AS saldo_wallet,
        COALESCE(SUM(im.cantidad_restante), 0) AS inventario_restante
    FROM cuentas c
    LEFT JOIN inventario_monedas im ON im.cuenta_id = c.id AND im.activo = 1 AND im.cantidad_restante > 0
    WHERE c.tipo_moneda IN ('USD', 'USDT') AND c.activo = 1 AND c.tipo_cuenta IN (1, 2)
    GROUP BY c.id, c.nombre, c.saldo_actual
    HAVING ABS(c.saldo_actual - COALESCE(SUM(im.cantidad_restante), 0)) > 0.01
");

$wallets_inconsistentes = $stmt->fetchAll();
if (count($wallets_inconsistentes) > 0) {
    echo "❌ WALLETS INCONSISTENTES:\n";
    foreach ($wallets_inconsistentes as $row) {
        echo "   Wallet #{$row['cuenta_id']} ({$row['nombre']}): Saldo={$row['saldo_wallet']}, Inventario={$row['inventario_restante']}\n";
    }
} else {
    echo "✅ Wallets y FIFO coinciden\n\n";
}

// 4. Verificar préstamos
echo "4. VERIFICAR PRÉSTAMOS\n";
$stmt = $pdo->query("
    SELECT 
        'Cliente' AS tipo,
        pc.id,
        pc.cliente_id,
        pc.monto_total,
        pc.monto_pagado,
        pc.monto_total - pc.monto_pagado AS saldo_calculado,
        pc.saldo_pendiente
    FROM prestamos_clientes pc
    WHERE ABS((pc.monto_total - pc.monto_pagado) - pc.saldo_pendiente) > 0.01
    UNION ALL
    SELECT 
        'Prestamista' AS tipo,
        pp.id,
        pp.prestamista_id AS cliente_id,
        pp.monto_total,
        pp.monto_pagado,
        pp.monto_total - pp.monto_pagado AS saldo_calculado,
        pp.saldo_pendiente
    FROM prestamos_prestamistas pp
    WHERE ABS((pp.monto_total - pp.monto_pagado) - pp.saldo_pendiente) > 0.01
");

$prestamos_inconsistentes = $stmt->fetchAll();
if (count($prestamos_inconsistentes) > 0) {
    echo "❌ PRÉSTAMOS INCONSISTENTES:\n";
    foreach ($prestamos_inconsistentes as $row) {
        echo "   {$row['tipo']} #{$row['id']}: Saldo real={$row['saldo_calculado']}, Registrado={$row['saldo_pendiente']}\n";
    }
} else {
    echo "✅ Préstamos consistentes\n\n";
}

// 5. Verificar consumos inventario sin inventario
echo "5. VERIFICAR CONSUMOS HUÉRFANOS\n";
$stmt = $pdo->query("
    SELECT ci.id, ci.movimiento_id, ci.inventario_id
    FROM consumos_inventario ci
    LEFT JOIN inventario_monedas im ON ci.inventario_id = im.id
    WHERE im.id IS NULL
");
$consumos_huerfanos = $stmt->fetchAll();
if (count($consumos_huerfanos) > 0) {
    echo "❌ CONSUMOS SIN INVENTARIO: " . count($consumos_huerfanos) . " encontrados\n";
} else {
    echo "✅ No hay consumos huérfanos\n\n";
}

// 6. Resumen de cuenta Comisiones AMIRI (ID 1)
echo "6. CUENTA COMISIONES AMIRI (ID 1)\n";
$stmt = $pdo->query("SELECT saldo_actual FROM cuentas WHERE id = 1");
$cuenta1 = $stmt->fetch();
echo "   Saldo actual: " . ($cuenta1['saldo_actual'] ?? 'N/A') . "\n\n";

echo "=== AUDITORÍA COMPLETADA ===\n";
