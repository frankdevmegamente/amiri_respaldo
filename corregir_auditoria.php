<?php
/**
 * SCRIPT DE CORRECCIÓN - SISTEMA AMIRI
 * Corrige los problemas encontrados en la auditoría
 * 
 * USO:
 * php corregir_auditoria.php --dry-run (solo mostrar cambios)
 * php corregir_auditoria.php --apply (aplicar cambios)
 */

require_once __DIR__ . '/src/config/db.php';

// Procesar argumentos de línea de comandos
$dry_run = true;
$apply = false;

if (isset($argv[1])) {
    if ($argv[1] === '--apply') {
        $dry_run = false;
        $apply = true;
    } elseif ($argv[1] === '--dry-run') {
        $dry_run = true;
    }
}

$pdo = getPDO();
$total_correcciones = 0;

if ($dry_run) {
    echo "=== MODO DRY-RUN (Solo mostrar cambios) ===\n";
    echo "Para aplicar cambios usa: php corregir_auditoria.php --apply\n\n";
} else {
    echo "=== APLICANDO CORRECCIONES ===\n";
    echo "⚠️  HACIENDO BACKUP DE SEGURIDAD...\n";
    
    // Crear backup antes de aplicar cambios
    $backup_file = __DIR__ . '/backups/backup_correccion_' . date('Y-m-d_H-i-s') . '.sql';
    $db_name = 'guiaspar_amiri_produccion';
    $username = 'root';
    
    // Usar ruta completa para mysqldump en XAMPP
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    if (!file_exists($mysqldump)) {
        $mysqldump = 'mysqldump';
    }
    
    $command = "\"{$mysqldump}\" --user={$username} {$db_name} > \"{$backup_file}\" 2>&1";
    echo "   Ejecutando: " . basename($mysqldump) . "\n";
    system($command, $retval);
    
    if ($retval === 0 && file_exists($backup_file)) {
        echo "✅ Backup creado: " . basename($backup_file) . "\n\n";
    } else {
        echo "⚠️  ADVERTENCIA: No se pudo crear backup automático, pero se continuará.\n";
        echo "   Puedes crear backup manual con: mysqldump -u root guiaspar_amiri_produccion > backup.sql\n\n";
    }
}

echo "Base de datos: guiaspar_amiri_produccion\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// ===========================================
// 1. CORREGIR SALDOS DE CUENTAS
// Recálculo basado en movimientos reales
// ===========================================
echo "1. CORRIGIENDO SALDOS DE CUENTAS...\n";

$sql_saldos_incorrectos = "
    SELECT 
        c.id,
        c.nombre,
        c.tipo_moneda,
        c.saldo_actual AS saldo_actual,
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

$stmt = $pdo->prepare($sql_saldos_incorrectos);
$stmt->execute();
$cuentas_corregir = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cuentas_corregir) > 0) {
    echo "   Encontradas " . count($cuentas_corregir) . " cuentas con saldo incorrecto:\n";
    
    foreach ($cuentas_corregir as $cuenta) {
        $diff = abs($cuenta['saldo_actual'] - $cuenta['saldo_calculado']);
        echo "      #{$cuenta['id']} ({$cuenta['nombre']}) - {$cuenta['tipo_moneda']}\n";
        echo "         Saldo actual: {$cuenta['saldo_actual']} → Nuevo saldo: {$cuenta['saldo_calculado']} (Diff: $diff)\n";
        
        if ($apply) {
            try {
                $update = $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?");
                $update->execute([$cuenta['saldo_calculado'], $cuenta['id']]);
                echo "         ✅ Actualizado\n";
            } catch (Exception $e) {
                echo "         ❌ Error: " . $e->getMessage() . "\n";
            }
        }
        $total_correcciones++;
    }
} else {
    echo "   ✅ Todos los saldos ya son correctos\n";
}
echo "\n";

// ===========================================
// 2. CREAR HISTORIAL DE SALDOS FALTANTE
// ===========================================
echo "2. CREANDO HISTORIAL DE SALDOS FALTANTE...\n";

// Obtener movimientos sin historial
$sql_mov_sin_hist = "
    SELECT 
        m.id as movimiento_id,
        m.fecha,
        m.tipo,
        m.monto,
        m.cuenta_origen_id,
        m.cuenta_destino_id,
        m.moneda
    FROM movimientos m
    LEFT JOIN historial_saldos h ON h.movimiento_id = m.id
    WHERE m.activo = 1 AND m.estado = 'activo' AND h.id IS NULL
    ORDER BY m.fecha, m.id
";

$stmt = $pdo->prepare($sql_mov_sin_hist);
$stmt->execute();
$movimientos_sin_hist = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($movimientos_sin_hist) > 0) {
    echo "   Encontrados " . count($movimientos_sin_hist) . " movimientos sin historial\n";
    
    if ($apply) {
        try {
            $pdo->beginTransaction();
            
            // Crear historial para cada movimiento
            foreach ($movimientos_sin_hist as $mov) {
                // Obtener saldo anterior de la cuenta
                if ($mov['cuenta_destino_id']) {
                    $saldo_anterior_destino = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                    $saldo_anterior_destino->execute([$mov['cuenta_destino_id']]);
                    $saldo_ant_dest = $saldo_anterior_destino->fetchColumn();
                    
                    // Restar el monto para obtener saldo anterior (porque el movimiento ya se ejecutó)
                    if ($mov['tipo'] == 'entrada' || $mov['tipo'] == 'traspaso' || $mov['tipo'] == 'comision') {
                        $saldo_anterior = $saldo_ant_dest - $mov['monto'];
                    } else {
                        $saldo_anterior = $saldo_ant_dest + $mov['monto'];
                    }
                    
                    $insert_hist = $pdo->prepare("
                        INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $insert_hist->execute([
                        $mov['cuenta_destino_id'],
                        $mov['movimiento_id'],
                        $mov['tipo'],
                        $mov['monto'],
                        $saldo_anterior,
                        $saldo_ant_dest
                    ]);
                }
                
                if ($mov['cuenta_origen_id']) {
                    $saldo_anterior_origen = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                    $saldo_anterior_origen->execute([$mov['cuenta_origen_id']]);
                    $saldo_ant_org = $saldo_anterior_origen->fetchColumn();
                    
                    // Sumar el monto para obtener saldo anterior
                    if ($mov['tipo'] == 'salida' || $mov['tipo'] == 'traspaso') {
                        $saldo_anterior = $saldo_ant_org + $mov['monto'];
                    } else {
                        $saldo_anterior = $saldo_ant_org - $mov['monto'];
                    }
                    
                    $insert_hist = $pdo->prepare("
                        INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $insert_hist->execute([
                        $mov['cuenta_origen_id'],
                        $mov['movimiento_id'],
                        $mov['tipo'],
                        $mov['monto'],
                        $saldo_anterior,
                        $saldo_ant_org
                    ]);
                }
            }
            
            $pdo->commit();
            echo "   ✅ Creados " . count($movimientos_sin_hist) . " registros de historial\n";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   (Dry-run: no se insertaron registros)\n";
    }
    
    $total_correcciones += count($movimientos_sin_hist);
} else {
    echo "   ✅ No faltan registros de historial\n";
}
echo "\n";

// ===========================================
// 3. CORREGIR GASTOS SIN MOVIMIENTO
// ===========================================
echo "3. CORRIGIENDO GASTOS SIN MOVIMIENTO...\n";

$sql_gastos_sin_mov = "
    SELECT g.id, g.monto, g.cuenta_id, g.categoria_id, g.fecha, g.concepto
    FROM gastos g
    WHERE g.activo = 1 AND g.movimiento_id IS NULL
";

$stmt = $pdo->prepare($sql_gastos_sin_mov);
$stmt->execute();
$gastos_sin_mov = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($gastos_sin_mov) > 0) {
    echo "   Encontrados " . count($gastos_sin_mov) . " gastos sin movimiento:\n";
    
    if ($apply) {
        try {
            $pdo->beginTransaction();
            
            foreach ($gastos_sin_mov as $gasto) {
                // Crear movimiento para el gasto
                $insert_mov = $pdo->prepare("
                    INSERT INTO movimientos (fecha, creado_en, servicio_id, tipo, moneda, monto, cuenta_origen_id, descripcion, usuario_id, activo, estado)
                    SELECT ?, NOW(), s.id, 'salida', c.tipo_moneda, ?, ?, CONCAT('Gasto: ', ?), 1, 1, 'activo'
                    FROM servicios s, cuentas c
                    WHERE s.nombre = 'Gasto' AND c.id = ?
                ");
                $insert_mov->execute([
                    $gasto['fecha'],
                    $gasto['monto'],
                    $gasto['cuenta_id'],
                    $gasto['concepto'] ?: 'Gasto operativo',
                    $gasto['cuenta_id']
                ]);
                
                $movimiento_id = $pdo->lastInsertId();
                
                // Actualizar gasto con el movimiento_id
                $update_gasto = $pdo->prepare("UPDATE gastos SET movimiento_id = ? WHERE id = ?");
                $update_gasto->execute([$movimiento_id, $gasto['id']]);
                
                // Actualizar saldo de la cuenta
                $update_cuenta = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?");
                $update_cuenta->execute([$gasto['monto'], $gasto['cuenta_id']]);
                
                echo "      Gasto #{$gasto['id']} → Movimiento #$movimiento_id creado\n";
            }
            
            $pdo->commit();
            echo "   ✅ Gastos vinculados con sus movimientos\n";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    } else {
        foreach ($gastos_sin_mov as $gasto) {
            echo "      Gasto #{$gasto['id']} - $" . number_format($gasto['monto'], 2) . " (Cuenta: {$gasto['cuenta_id']})\n";
        }
        echo "   (Dry-run: no se crearon movimientos)\n";
    }
    
    $total_correcciones += count($gastos_sin_mov);
} else {
    echo "   ✅ Todos los gastos ya tienen movimiento\n";
}
echo "\n";

// ===========================================
// 4. CORREGIR ESTADO DE PRÉSTAMOS
// ===========================================
echo "4. CORRIGIENDO ESTADO DE PRÉSTAMOS...\n";

// Préstamos cliente
$sql_prestamos_clientes = "
    SELECT 
        pc.id,
        pc.monto_total,
        pc.monto_pagado,
        pc.estado as estado_actual,
        (pc.monto_total - pc.monto_pagado) as saldo_calculado
    FROM deudas_clientes pc
    WHERE pc.activo = 1
";

$stmt = $pdo->prepare($sql_prestamos_clientes);
$stmt->execute();
$prestamos_clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prestamos_corregir = 0;

foreach ($prestamos_clientes as $prestamo) {
    $saldo_real = $prestamo['monto_total'] - $prestamo['monto_pagado'];
    
    // Determinar estado correcto
    $estado_correcto = 'pendiente';
    if (abs($saldo_real) < 0.01) {
        $estado_correcto = 'pagado';
    } elseif ($saldo_real > 0 && $prestamo['monto_pagado'] > 0) {
        $estado_correcto = 'parcial';
    }
    
    if ($prestamo['estado_actual'] != $estado_correcto) {
        echo "      Préstamo Cliente #{$prestamo['id']}: {$prestamo['estado_actual']} → $estado_correcto (Saldo: $saldo_real)\n";
        
        if ($apply) {
            $update = $pdo->prepare("UPDATE deudas_clientes SET estado = ? WHERE id = ?");
            $update->execute([$estado_correcto, $prestamo['id']]);
        }
        $prestamos_corregir++;
    }
}

// Préstamos empresa
$sql_prestamos_empresa = "
    SELECT 
        pe.id,
        pe.monto,
        pe.monto_pagado,
        pe.estado as estado_actual,
        (pe.monto - pe.monto_pagado) as saldo_calculado
    FROM deudas_empresa pe
    WHERE pe.activo = 1
";

$stmt = $pdo->prepare($sql_prestamos_empresa);
$stmt->execute();
$prestamos_empresa = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($prestamos_empresa as $prestamo) {
    $saldo_real = $prestamo['monto'] - $prestamo['monto_pagado'];
    
    // Determinar estado correcto
    $estado_correcto = 'pendiente';
    if (abs($saldo_real) < 0.01) {
        $estado_correcto = 'pagado';
    } elseif ($saldo_real > 0 && $prestamo['monto_pagado'] > 0) {
        $estado_correcto = 'parcial';
    }
    
    if ($prestamo['estado_actual'] != $estado_correcto) {
        echo "      Deuda Empresa #{$prestamo['id']}: {$prestamo['estado_actual']} → $estado_correcto (Saldo: $saldo_real)\n";
        
        if ($apply) {
            $update = $pdo->prepare("UPDATE deudas_empresa SET estado = ? WHERE id = ?");
            $update->execute([$estado_correcto, $prestamo['id']]);
        }
        $prestamos_corregir++;
    }
}

if ($prestamos_corregir > 0) {
    echo "   ✅ Corregidos $prestamos_corregir préstamos\n";
    $total_correcciones += $prestamos_corregir;
} else {
    echo "   ✅ Todos los préstamos ya tienen estado correcto\n";
}
echo "\n";

// ===========================================
// RESUMEN FINAL
// ===========================================
echo "=== RESUMEN DE CORRECCIONES ===\n";
echo "Total de correcciones necesarias: $total_correcciones\n";

if ($dry_run) {
    echo "\n⚠️  MODO DRY-RUN: No se aplicaron cambios\n";
    echo "Para aplicar correcciones ejecuta: php corregir_auditoria.php --apply\n";
} else {
    echo "\n✅ CORRECCIONES APLICADAS EXITOSAMENTE\n";
    
    // Verificar después de corrección
    echo "\nEjecutando auditoría post-corrección...\n";
    echo "==========================================\n\n";
    
    // Re-ejecutar verificación de saldos
    $stmt = $pdo->prepare($sql_saldos_incorrectos);
    $stmt->execute();
    $cuentas_restantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($cuentas_restantes) > 0) {
        echo "⚠️  Aún quedan " . count($cuentas_restantes) . " cuentas con saldo incorrecto\n";
    } else {
        echo "✅ Todos los saldos son correctos ahora\n";
    }
}

echo "\nProceso completado.\n";
