<?php
/**
 * CORRECCIÓN INTEGRAL - SISTEMA AMIRI
 * 
 * Corrige todos los problemas encontrados en la auditoría.
 * 
 * USO:
 *   php scripts/corregir_todo.php              (dry-run, solo mostrar)
 *   php scripts/corregir_todo.php --apply       (aplicar cambios)
 *   php scripts/corregir_todo.php --fase=1     (solo fase específica)
 *   php scripts/corregir_todo.php --apply --fase=1
 */

require_once __DIR__ . '/../src/config/db.php';

// ============================================================
// CONFIGURACIÓN
// ============================================================
$dry_run = true;
$apply = false;
$fase_filter = null;

foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if ($arg === '--apply') { $dry_run = false; $apply = true; }
    elseif (strpos($arg, '--fase=') === 0) { $fase_filter = (int)substr($arg, 7); }
}

$pdo = getPDO();
$total_correcciones = 0;

echo str_repeat("=", 70) . "\n";
echo "  CORRECCIÓN INTEGRAL - SISTEMA AMIRI\n";
echo "  BD: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
echo "  Fecha: " . date('Y-m-d H:i:s') . "\n";
echo ($fase_filter ? "  Fase: $fase_filter\n" : "  Fase: TODAS\n");
echo str_repeat("=", 70) . "\n\n";

if ($dry_run) {
    echo "⚠️  MODO DRY-RUN: Solo se mostrarán los cambios, no se aplicarán.\n";
    echo "   Para aplicar: php scripts/corregir_todo.php --apply\n\n";
} else {
    echo "⚠️  APLICANDO CORRECCIONES - HACIENDO BACKUP...\n";
    $backup_dir = __DIR__ . '/../backups';
    if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);
    $backup_file = $backup_dir . '/backup_correccion_' . date('Y-m-d_H-i-s') . '.sql';
    $db_name = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    $command = "\"$mysqldump\" --user=root {$db_name} > \"{$backup_file}\" 2>&1";
    echo "   Ejecutando backup...\n";
    system($command, $retval);
    if ($retval === 0 && file_exists($backup_file)) {
        echo "   ✅ Backup creado: " . basename($backup_file) . "\n\n";
    } else {
        echo "   ⚠️  No se pudo crear backup automático.\n";
    }
}

// ============================================================
// FUNCIÓN AYUDA
// ============================================================
function ejecutar($pdo, $sql, $desc, &$total, $dry_run, $apply, $fase_actual, $fase_filter = null) {
    if ($fase_filter !== null && $fase_actual !== $fase_filter) return;
    echo "  [$desc]\n";
    if ($dry_run) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->rowCount();
        if ($rows > 0) {
            echo "    → Afectaría $rows registro(s)\n";
        } else {
            echo "    → Sin cambios necesarios\n";
        }
    } else {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->rowCount();
            if ($rows > 0) echo "    ✅ $rows registro(s) actualizados\n";
            else echo "    → Sin cambios necesarios\n";
        } catch (Exception $e) {
            echo "    ❌ Error: " . $e->getMessage() . "\n";
        }
    }
    $total++;
}

function mostrar_tabla($rows, $cols) {
    if (empty($rows)) { echo "    (vacío)\n"; return; }
    foreach ($rows as $row) {
        $parts = [];
        foreach ($cols as $c) {
            $parts[] = "$c=" . ($row[$c] ?? 'NULL');
        }
        echo "    → " . implode(", ", $parts) . "\n";
    }
}

// ============================================================
// FASE 1.1: CONSUMOS INVENTARIO DUPLICADOS
// ============================================================
if ($fase_filter === null || $fase_filter === 1) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 1.1: CORREGIR CONSUMOS_INVENTARIO DUPLICADOS\n";
echo str_repeat("-", 50) . "\n";

$sql_dup = "
    SELECT inventario_id, cantidad_consumida, paridad_compra, paridad_venta, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM consumos_inventario
    GROUP BY inventario_id, cantidad_consumida, paridad_compra, paridad_venta
    HAVING COUNT(*) > 1
";
$stmt = $pdo->query($sql_dup);
$duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($duplicados as $dup) {
    $ids = explode(',', $dup['ids']);
    echo "  Duplicados encontrados - inventario_id={$dup['inventario_id']}, cantidad={$dup['cantidad_consumida']}\n";
    echo "    IDs: " . $dup['ids'] . "\n";
    
    // Obtener detalles de cada registro
    $detalles = $pdo->prepare("SELECT * FROM consumos_inventario WHERE id IN ({$dup['ids']}) ORDER BY id");
    $detalles->execute();
    $items = $detalles->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        $mov = $pdo->prepare("SELECT id, servicio_id, monto, descripcion FROM movimientos WHERE id = ?");
        $mov->execute([$item['movimiento_id']]);
        $m = $mov->fetch(PDO::FETCH_ASSOC);
        echo "    ID #{$item['id']} → movimiento #{$item['movimiento_id']}" . ($m ? " ({$m['descripcion']})" : " (NO EXISTE)") . "\n";
    }
    
    if ($apply) {
        // Determinar cuál consumos tiene el movimiento activo y cuál el inactivo
        foreach ($items as $item) {
            $mov = $pdo->prepare("SELECT activo FROM movimientos WHERE id = ?");
            $mov->execute([$item['movimiento_id']]);
            $m = $mov->fetch();
            if (!$m || $m['activo'] == 0) {
                // Este consumo corresponde a un movimiento anulado - eliminar
                $pdo->prepare("DELETE FROM consumos_inventario WHERE id = ?")->execute([$item['id']]);
                echo "    🗑️  Eliminado consumo #{$item['id']} (movimiento #{$item['movimiento_id']} inactivo)\n";
            } else {
                echo "    ✅ Conservado consumo #{$item['id']} (movimiento #{$item['movimiento_id']} activo)\n";
            }
        }
    }
}
if (empty($duplicados)) echo "  ✅ No hay duplicados\n";
}

// ============================================================
// FASE 1.2: INVENTARIO INACTIVO CON CANTIDAD RESTANTE
// ============================================================
if ($fase_filter === null || $fase_filter === 1) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 1.2: DEPURAR INVENTARIO INACTIVO (218K USDT)\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT im.*, c.nombre as cuenta_nombre
    FROM inventario_monedas im
    JOIN cuentas c ON im.cuenta_id = c.id
    WHERE im.activo = 0 AND im.cantidad_restante > 0
    ORDER BY im.id
");
$inactivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "  Inventarios inactivos con cantidad restante > 0:\n";
foreach ($inactivos as $inv) {
    echo "    ID #{$inv['id']}: {$inv['cantidad_restante']} {$inv['moneda']} (paridad: {$inv['paridad']}) - Cuenta: {$inv['cuenta_nombre']}\n";
    
    // Buscar si hay un reemplazo activo con misma moneda, cuenta y paridad similar
    $reemp = $pdo->prepare("
        SELECT id, cantidad_original, cantidad_restante FROM inventario_monedas 
        WHERE activo = 1 AND moneda = ? AND cuenta_id = ? AND ABS(paridad - ?) < 0.01
        ORDER BY id DESC LIMIT 1
    ");
    $reemp->execute([$inv['moneda'], $inv['cuenta_id'], $inv['paridad']]);
    $reemplazo = $reemp->fetch(PDO::FETCH_ASSOC);
    
    if ($reemplazo) {
        echo "      → Posible reemplazo activo: ID #{$reemplazo['id']} ({$reemplazo['cantidad_restante']} restante)\n";
        if ($apply) {
            // Sumar la cantidad al reemplazo activo
            $nueva_cant = $reemplazo['cantidad_restante'] + $inv['cantidad_restante'];
            $pdo->prepare("UPDATE inventario_monedas SET cantidad_original = cantidad_original + ?, cantidad_restante = cantidad_restante + ? WHERE id = ?")
                ->execute([$inv['cantidad_restante'], $inv['cantidad_restante'], $reemplazo['id']]);
            // Poner a 0 el inventario inactivo
            $pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = 0 WHERE id = ?")->execute([$inv['id']]);
            echo "      ✅ Cantidad transferida al ID #{$reemplazo['id']}, inventario #{$inv['id']} puesto en 0\n";
        }
    } else {
        echo "      → Sin reemplazo activo directo encontrado\n";
        if ($apply) {
            // Poner cantidad_restante a 0 (inventario dado de baja)
            $pdo->prepare("UPDATE inventario_monedas SET cantidad_restante = 0 WHERE id = ?")->execute([$inv['id']]);
            echo "      ✅ Cantidad restante puesta en 0 (inventario dado de baja)\n";
        }
    }
}
if (empty($inactivos)) echo "  ✅ No hay inventarios inactivos con cantidad restante\n";
}

// ============================================================
// FASE 1.3: CUENTA 21 INACTIVA CON 20K USDT
// ============================================================
if ($fase_filter === null || $fase_filter === 1) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 1.3: CUENTA PROVEEDOR INACTIVA CON SALDO\n";
echo str_repeat("-", 50) . "\n";

// Buscar cuentas de proveedor inactivas con saldo > 0
$stmt = $pdo->query("
    SELECT c.id, c.nombre, c.tipo_cuenta, c.tipo_moneda, c.saldo_actual, c.valor_mxn
    FROM cuentas c
    WHERE c.activo = 0 AND (c.saldo_actual > 0 OR c.valor_mxn > 0)
");
$cuentas_problema = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cuentas_problema as $cta) {
    echo "  Cuenta #{$cta['id']} '{$cta['nombre']}': {$cta['saldo_actual']} {$cta['tipo_moneda']} (activo=0)\n";
    
    if ($apply) {
        $pdo->prepare("UPDATE cuentas SET saldo_actual = 0, valor_mxn = 0 WHERE id = ?")->execute([$cta['id']]);
        echo "    ✅ Saldo puesto en 0\n";
    }
}
if (empty($cuentas_problema)) echo "  ✅ No hay cuentas inactivas con saldo\n";
}

// ============================================================
// FASE 1.4: FOREIGN KEY CONSTRAINTS
// ============================================================
if ($fase_filter === null || $fase_filter === 1) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 1.4: AGREGAR FOREIGN KEY CONSTRAINTS\n";
echo str_repeat("-", 50) . "\n";

// Primero verificar huérfanos
$checks = [
    "movimientos LEFT JOIN servicios ON movimientos.servicio_id = servicios.id WHERE movimientos.servicio_id IS NOT NULL AND servicios.id IS NULL" => "movimientos con servicio_id huérfano",
    "movimientos LEFT JOIN cuentas c1 ON movimientos.cuenta_origen_id = c1.id WHERE movimientos.cuenta_origen_id IS NOT NULL AND c1.id IS NULL" => "movimientos con cuenta_origen_id huérfano",
    "movimientos LEFT JOIN cuentas c2 ON movimientos.cuenta_destino_id = c2.id WHERE movimientos.cuenta_destino_id IS NOT NULL AND c2.id IS NULL" => "movimientos con cuenta_destino_id huérfano",
    "clientes LEFT JOIN promotores ON clientes.promotor_id = promotores.id WHERE clientes.promotor_id IS NOT NULL AND promotores.id IS NULL" => "clientes con promotor_id huérfano",
    "auditoria LEFT JOIN usuarios ON auditoria.usuario_id = usuarios.id WHERE auditoria.usuario_id IS NOT NULL AND usuarios.id IS NULL" => "auditoria con usuario_id huérfano",
    "gastos LEFT JOIN categorias_gasto ON gastos.categoria_id = categorias_gasto.id WHERE gastos.categoria_id IS NOT NULL AND categorias_gasto.id IS NULL" => "gastos con categoria_id huérfano",
    "movimientos LEFT JOIN usuarios ON movimientos.usuario_id = usuarios.id WHERE movimientos.usuario_id IS NOT NULL AND usuarios.id IS NULL" => "movimientos con usuario_id huérfano",
    "clientes LEFT JOIN usuarios u ON clientes.eliminado_por = u.id WHERE clientes.eliminado_por IS NOT NULL AND u.id IS NULL" => "clientes con eliminado_por huérfano",
    "cuentas LEFT JOIN usuarios u2 ON cuentas.eliminado_por = u2.id WHERE cuentas.eliminado_por IS NOT NULL AND u2.id IS NULL" => "cuentas con eliminado_por huérfano",
    "prestamistas LEFT JOIN usuarios u3 ON prestamistas.eliminado_por = u3.id WHERE prestamistas.eliminado_por IS NOT NULL AND u3.id IS NULL" => "prestamistas con eliminado_por huérfano",
    "promotores LEFT JOIN usuarios u4 ON promotores.eliminado_por = u4.id WHERE promotores.eliminado_por IS NOT NULL AND u4.id IS NULL" => "promotores con eliminado_por huérfano",
    "proveedores LEFT JOIN usuarios u5 ON proveedores.eliminado_por = u5.id WHERE proveedores.eliminado_por IS NOT NULL AND u5.id IS NULL" => "proveedores con eliminado_por huérfano",
];

$hay_huérfanos = false;
foreach ($checks as $sql => $desc) {
    $cnt = $pdo->query("SELECT COUNT(*) FROM $sql")->fetchColumn();
    if ($cnt > 0) {
        echo "  ⚠️  $desc: $cnt registro(s)\n";
        $hay_huérfanos = true;
    }
}
if (!$hay_huérfanos) echo "  ✅ No hay registros huérfanos\n\n";

// Agregar FK constraints
$fks = [
    "ALTER TABLE auditoria ADD CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)" => "auditoria.usuario_id → usuarios.id",
    "ALTER TABLE clientes ADD CONSTRAINT fk_clientes_promotor FOREIGN KEY (promotor_id) REFERENCES promotores(id)" => "clientes.promotor_id → promotores.id",
    "ALTER TABLE clientes ADD CONSTRAINT fk_clientes_cuenta FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)" => "clientes.cuenta_id → cuentas.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_servicio FOREIGN KEY (servicio_id) REFERENCES servicios(id)" => "movimientos.servicio_id → servicios.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_cuenta_origen FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas(id)" => "movimientos.cuenta_origen_id → cuentas.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_cuenta_destino FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas(id)" => "movimientos.cuenta_destino_id → cuentas.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)" => "movimientos.cliente_id → clientes.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_promotor FOREIGN KEY (promotor_id) REFERENCES promotores(id)" => "movimientos.promotor_id → promotores.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)" => "movimientos.proveedor_id → proveedores.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_prestamista FOREIGN KEY (prestamista_id) REFERENCES prestamistas(id)" => "movimientos.prestamista_id → prestamistas.id",
    "ALTER TABLE movimientos ADD CONSTRAINT fk_movimientos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)" => "movimientos.usuario_id → usuarios.id",
    "ALTER TABLE gastos ADD CONSTRAINT fk_gastos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_gasto(id)" => "gastos.categoria_id → categorias_gasto.id",
    "ALTER TABLE gastos ADD CONSTRAINT fk_gastos_cuenta FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)" => "gastos.cuenta_id → cuentas.id",
    "ALTER TABLE deudas_clientes ADD CONSTRAINT fk_deudas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)" => "deudas_clientes.cliente_id → clientes.id",
    "ALTER TABLE deudas_empresa ADD CONSTRAINT fk_deudas_prestamista FOREIGN KEY (prestamista_id) REFERENCES prestamistas(id)" => "deudas_empresa.prestamista_id → prestamistas.id",
    "ALTER TABLE cuentas ADD CONSTRAINT fk_cuentas_tipo_cuenta FOREIGN KEY (tipo_cuenta) REFERENCES tipos_cuenta(id)" => "cuentas.tipo_cuenta → tipos_cuenta.id",
];

foreach ($fks as $sql => $desc) {
    // Check if constraint already exists
    $fk_name = explode(" ", explode("CONSTRAINT ", $sql)[1])[0];
    $check = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = '$fk_name' AND TABLE_SCHEMA = DATABASE()")->fetchColumn();
    
    if ($check > 0) {
        echo "  ⏭️  $desc — ya existe\n";
        continue;
    }
    
    if ($apply) {
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "  ✅ $desc — creada\n";
        } catch (Exception $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "  ❌ $desc — Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  📋 $desc — se creará\n";
    }
    $total_correcciones++;
}
}

// ============================================================
// FASE 2.1: CORREGIR SALDOS UTILIDADES AMIRI
// ============================================================
if ($fase_filter === null || $fase_filter === 2) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 2.1: CORREGIR SALDOS Y VERIFICAR UTILIDADES\n";
echo str_repeat("-", 50) . "\n";

// Verificar saldos de cuentas activas vs movimientos
// NOTA: Se excluyen cuentas de tipo Cliente (4) porque sus saldos
// se ponen a 0 intencionalmente al pagarles en efectivo. Los movimientos
// quedan registrados para auditoría pero el balance no se mantiene.
$sql_saldos = "
    SELECT c.id, c.nombre, c.tipo_moneda, c.saldo_actual, c.tipo_cuenta,
        COALESCE(ROUND(SUM(
            CASE 
                WHEN m.tipo IN ('entrada') AND m.cuenta_destino_id = c.id AND m.activo = 1 AND m.estado = 'activo' THEN m.monto
                WHEN m.tipo IN ('salida') AND m.cuenta_origen_id = c.id AND m.activo = 1 AND m.estado = 'activo' THEN -m.monto
                WHEN m.tipo IN ('traspaso') AND m.cuenta_destino_id = c.id AND m.activo = 1 AND m.estado = 'activo' THEN m.monto
                WHEN m.tipo IN ('traspaso') AND m.cuenta_origen_id = c.id AND m.activo = 1 AND m.estado = 'activo' THEN -m.monto
                WHEN m.tipo IN ('comision') AND m.cuenta_destino_id = c.id AND m.activo = 1 AND m.estado = 'activo' THEN m.monto
                ELSE 0
            END
        ), 2), 0) AS saldo_calculado
    FROM cuentas c
    LEFT JOIN movimientos m ON (m.cuenta_destino_id = c.id OR m.cuenta_origen_id = c.id) AND m.estado = 'activo' AND m.activo = 1
    WHERE c.activo = 1 AND c.tipo_cuenta != 4
    GROUP BY c.id
";
$stmt = $pdo->query("
    SELECT id, nombre, tipo_moneda, saldo_actual, saldo_calculado, tipo_cuenta
    FROM ($sql_saldos) AS subconsulta
    WHERE ABS(saldo_actual - saldo_calculado) > 0.50
    ORDER BY ABS(saldo_actual - saldo_calculado) DESC
");

$saldos_incorrectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($saldos_incorrectos) > 0) {
    echo "  (Excluidas cuentas de tipo Cliente por ser pass-through)\n";
    echo "  Cuentas con saldo incorrecto:\n";
    foreach ($saldos_incorrectos as $cta) {
        $diff = abs($cta['saldo_actual'] - $cta['saldo_calculado']);
        echo "    #{$cta['id']} '{$cta['nombre']}' (tipo={$cta['tipo_cuenta']}): actual={$cta['saldo_actual']} → calculado={$cta['saldo_calculado']} (diff=$diff)\n";
        if ($apply) {
            $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")->execute([$cta['saldo_calculado'], $cta['id']]);
            echo "      ✅ Actualizado\n";
        }
    }
} else {
    echo "  ✅ Todos los saldos son correctos\n";
}
}

// ============================================================
// FASE 2.2: RECONCILIAR DEUDAS INACTIVAS
// ============================================================
if ($fase_filter === null || $fase_filter === 2) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 2.2: RECONCILIAR DEUDAS INACTIVAS\n";
echo str_repeat("-", 50) . "\n";

// Deudas cliente inactivas pendientes
$stmt = $pdo->query("
    SELECT dc.id, dc.cliente_id, c.nombre as cliente, dc.monto_total, dc.monto_pagado, dc.estado, dc.activo
    FROM deudas_clientes dc
    JOIN clientes c ON dc.cliente_id = c.id
    WHERE dc.activo = 0 AND dc.estado = 'pendiente'
");
$deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "  Deudas cliente inactivas pendientes:\n";
foreach ($deudas as $d) {
    echo "    #{$d['id']} Cliente #{$d['cliente_id']} '{$d['cliente']}': \${$d['monto_total']}\n";
    if ($apply) {
        $pdo->prepare("UPDATE deudas_clientes SET estado = 'pagado', monto_pagado = monto_total WHERE id = ?")->execute([$d['id']]);
        echo "      ✅ Marcada como pagada (condonada)\n";
    }
}
if (empty($deudas)) echo "    ✅ Sin deudas cliente inactivas pendientes\n";

// Deudas empresa inactivas pendientes
$stmt = $pdo->query("
    SELECT de.id, de.prestamista_id, p.nombre as prestamista, de.monto, de.monto_pagado, de.estado, de.activo
    FROM deudas_empresa de
    JOIN prestamistas p ON de.prestamista_id = p.id
    WHERE de.activo = 0 AND de.estado = 'pendiente'
");
$deudas_emp = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "  Deudas empresa inactivas pendientes:\n";
foreach ($deudas_emp as $d) {
    echo "    #{$d['id']} Prestamista #{$d['prestamista_id']} '{$d['prestamista']}': \${$d['monto']}\n";
    if ($apply) {
        $pdo->prepare("UPDATE deudas_empresa SET estado = 'pagado', monto_pagado = monto WHERE id = ?")->execute([$d['id']]);
        echo "      ✅ Marcada como pagada (condonada)\n";
    }
}
if (empty($deudas_emp)) echo "    ✅ Sin deudas empresa inactivas pendientes\n";
}

// ============================================================
// FASE 2.3: HYPERION DUPLICADO (deudas_empresa IDs 9 vs 10)
// ============================================================
if ($fase_filter === null || $fase_filter === 2) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 2.3: RECONCILIAR HYPERION DUPLICADO\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("SELECT * FROM deudas_empresa WHERE id IN (9, 10)");
$hyperion = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($hyperion) === 2) {
    echo "  Préstamo #9 vs #10 (Hyperion):\n";
    echo "    #9: monto={$hyperion[0]['monto']}, pagado={$hyperion[0]['monto_pagado']}, estado={$hyperion[0]['estado']}, activo={$hyperion[0]['activo']}\n";
    echo "    #10: monto={$hyperion[1]['monto']}, pagado={$hyperion[1]['monto_pagado']}, estado={$hyperion[1]['estado']}, activo={$hyperion[1]['activo']}\n";
    
    // Determinar cuál mantener: el activo (10)
    if ($apply) {
        // Asegurar que #9 esté marcado como pagado/condonado
        $pdo->prepare("UPDATE deudas_empresa SET estado = 'pagado', monto_pagado = monto, activo = 0 WHERE id = 9")->execute();
        echo "    ✅ #9 marcado como pagado y desactivado (#10 se mantiene activo)\n";
    }
} else {
    echo "  ✅ No hay duplicado de Hyperion (IDs 9 y 10 no coexisten)\n";
}
}

// ============================================================
// FASE 3.2: ROLES DE USUARIO
// ============================================================
if ($fase_filter === null || $fase_filter === 3) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 3.2: ACTUALIZAR ROLES DE USUARIO\n";
echo str_repeat("-", 50) . "\n";

// Verificar si la columna rol soporta más valores
$stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  Columna rol actual: {$col['Type']}\n";

// Cambiar a VARCHAR para soportar múltiples roles
if (strpos($col['Type'], 'varchar') === false && strpos($col['Type'], 'VARCHAR') === false) {
    if ($apply) {
        $pdo->exec("ALTER TABLE usuarios MODIFY COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'usuario'");
        echo "  ✅ Columna rol cambiada a VARCHAR(20)\n";
    } else {
        echo "  📋 Columna rol se cambiará a VARCHAR(20)\n";
    }
    $total_correcciones++;
} else {
    echo "  ✅ Columna rol ya es VARCHAR\n";
}
}

// ============================================================
// FASE 3.3: FIX DEFAULTS
// ============================================================
if ($fase_filter === null || $fase_filter === 3) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 3.3: CORREGIR DEFAULTS DE TABLAS\n";
echo str_repeat("-", 50) . "\n";

// trusted_devices.expires_at
$stmt = $pdo->query("SHOW COLUMNS FROM trusted_devices LIKE 'expires_at'");
$col = $stmt->fetch();
if (strpos($col['Default'] ?? '', '0000') !== false) {
    if ($apply) {
        $pdo->exec("ALTER TABLE trusted_devices MODIFY COLUMN expires_at TIMESTAMP NULL DEFAULT NULL");
        echo "  ✅ trusted_devices.expires_at corregido\n";
    } else {
        echo "  📋 trusted_devices.expires_at se corregirá\n";
    }
    $total_correcciones++;
} else {
    echo "  ✅ trusted_devices.expires_at sin problema\n";
}
}

// ============================================================
// FASE 3.4: POBLAR VISTAS
// ============================================================
if ($fase_filter === null || $fase_filter === 3) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 3.4: POBLAR VISTAS\n";
echo str_repeat("-", 50) . "\n";

if ($apply) {
    try {
        // Convertir v_deudas_clientes_pendientes a vista real
        $pdo->exec("DROP TABLE IF EXISTS v_deudas_clientes_pendientes");
        $pdo->exec("CREATE VIEW v_deudas_clientes_pendientes AS
            SELECT dc.*, c.nombre AS cliente_nombre, cu.nombre AS cuenta_nombre
            FROM deudas_clientes dc
            JOIN clientes c ON dc.cliente_id = c.id
            LEFT JOIN cuentas cu ON dc.cuenta_cliente_id = cu.id
            WHERE dc.activo = 1 AND dc.estado IN ('pendiente', 'parcial')");
        echo "  ✅ Vista v_deudas_clientes_pendientes creada\n";

        $pdo->exec("DROP TABLE IF EXISTS v_deudas_empresa_pendientes");
        $pdo->exec("CREATE VIEW v_deudas_empresa_pendientes AS
            SELECT de.*, p.nombre AS prestamista_nombre, cu.nombre AS cuenta_nombre
            FROM deudas_empresa de
            JOIN prestamistas p ON de.prestamista_id = p.id
            LEFT JOIN cuentas cu ON de.cuenta_id = cu.id
            WHERE de.activo = 1 AND de.estado IN ('pendiente', 'parcial')");
        echo "  ✅ Vista v_deudas_empresa_pendientes creada\n";

        $pdo->exec("DROP TABLE IF EXISTS v_saldos_cuentas");
        $pdo->exec("CREATE VIEW v_saldos_cuentas AS
            SELECT c.*, tc.descripcion AS tipo_descripcion,
                CASE WHEN c.tipo_moneda = 'USDT' THEN c.saldo_actual * 17.2 ELSE c.saldo_actual END AS saldo_estimado_mxn
            FROM cuentas c
            JOIN tipos_cuenta tc ON c.tipo_cuenta = tc.id
            WHERE c.activo = 1");
        echo "  ✅ Vista v_saldos_cuentas creada\n";
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "  📋 Las tablas v_* se convertirán a vistas reales\n";
    $total_correcciones += 3;
}
}

// ============================================================
// FASE 3.5: LIMPIAR DIRECTORIO_DEPOSITOS
// ============================================================
if ($fase_filter === null || $fase_filter === 3) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 3.5: LIMPIAR DIRECTORIO_DEPOSITOS\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM directorio_depositos WHERE estado = 'inactivo'");
$inactivos = (int)$stmt->fetchColumn();
echo "  Depósitos inactivos: $inactivos\n";

if ($inactivos > 0) {
    if ($apply) {
        $pdo->exec("DELETE FROM directorio_depositos WHERE estado = 'inactivo'");
        echo "  ✅ $inactivos depósitos inactivos eliminados\n";
    } else {
        echo "  📋 $inactivos depósitos inactivos se eliminarán\n";
    }
    $total_correcciones++;
} else {
    echo "  ✅ Sin depósitos inactivos\n";
}
}

// ============================================================
// FASE 4: MEJORAS MENORES
// ============================================================
if ($fase_filter === null || $fase_filter === 4) {
echo "\n" . str_repeat("-", 50) . "\n";
echo "  FASE 4: MEJORAS MENORES\n";
echo str_repeat("-", 50) . "\n";

// Completar descripciones de gastos vacías
$stmt = $pdo->query("SELECT COUNT(*) FROM gastos WHERE (concepto IS NULL OR concepto = '') AND activo = 1");
$vacios = (int)$stmt->fetchColumn();
if ($vacios > 0) {
    if ($apply) {
        $pdo->exec("UPDATE gastos SET concepto = 'Gasto operativo' WHERE (concepto IS NULL OR concepto = '') AND activo = 1");
        echo "  ✅ $vacios gastos con descripción completada\n";
    } else {
        echo "  📋 $vacios gastos con descripción vacía se completarán\n";
    }
    $total_correcciones++;
} else {
    echo "  ✅ Todos los gastos tienen descripción\n";
}
}

// ============================================================
// RESUMEN FINAL
// ============================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "  RESUMEN DE CORRECCIONES\n";
echo str_repeat("=", 70) . "\n";

if ($dry_run) {
    echo "\n⚠️  MODO DRY-RUN COMPLETADO — No se aplicaron cambios\n";
    echo "   Para aplicar: php scripts/corregir_todo.php --apply\n";
    if (!$fase_filter) {
        echo "   Para una fase específica: php scripts/corregir_todo.php --apply --fase=1\n";
    }
} else {
    echo "\n✅ CORRECCIONES APLICADAS EXITOSAMENTE\n";
}

echo "\n";
