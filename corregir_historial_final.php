<?php
/**
 * Corrección final: Agregar 16 movimientos faltantes al historial
 */

require_once __DIR__ . '/src/config/db.php';

$pdo = getPDO();

echo "=== CORRECCIÓN FINAL DE HISTORIAL ===\n\n";

// Obtener movimientos sin historial
$sql = "
    SELECT 
        m.id as movimiento_id,
        m.fecha,
        m.tipo,
        m.monto,
        m.cuenta_origen_id,
        m.cuenta_destino_id
    FROM movimientos m
    LEFT JOIN historial_saldos h ON h.movimiento_id = m.id
    WHERE m.activo = 1 AND m.estado = 'activo' AND h.id IS NULL
    ORDER BY m.fecha, m.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$movimientos_faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Encontrados " . count($movimientos_faltantes) . " movimientos sin historial:\n\n";

if (count($movimientos_faltantes) > 0) {
    try {
        $pdo->beginTransaction();
        
        foreach ($movimientos_faltantes as $mov) {
            echo "   Procesando movimiento #{$mov['movimiento_id']} ({$mov['tipo']} - \${$mov['monto']})\n";
            
            // Para cuenta destino
            if ($mov['cuenta_destino_id']) {
                // Obtener saldo actual de la cuenta
                $saldo_actual = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                $saldo_actual->execute([$mov['cuenta_destino_id']]);
                $saldo_dest = $saldo_actual->fetchColumn();
                
                // Calcular saldo anterior (restar el monto si fue entrada)
                if ($mov['tipo'] == 'entrada' || $mov['tipo'] == 'traspaso' || $mov['tipo'] == 'comision') {
                    $saldo_anterior = $saldo_dest - $mov['monto'];
                } else {
                    $saldo_anterior = $saldo_dest + $mov['monto'];
                }
                
                $insert = $pdo->prepare("
                    INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insert->execute([
                    $mov['cuenta_destino_id'],
                    $mov['movimiento_id'],
                    $mov['tipo'],
                    $mov['monto'],
                    $saldo_anterior,
                    $saldo_dest
                ]);
                
                echo "      ✓ Historial creado para cuenta destino #{$mov['cuenta_destino_id']}\n";
            }
            
            // Para cuenta origen
            if ($mov['cuenta_origen_id']) {
                // Obtener saldo actual de la cuenta
                $saldo_actual = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");
                $saldo_actual->execute([$mov['cuenta_origen_id']]);
                $saldo_org = $saldo_actual->fetchColumn();
                
                // Calcular saldo anterior (sumar el monto si fue salida)
                if ($mov['tipo'] == 'salida' || $mov['tipo'] == 'traspaso') {
                    $saldo_anterior = $saldo_org + $mov['monto'];
                } else {
                    $saldo_anterior = $saldo_org - $mov['monto'];
                }
                
                $insert = $pdo->prepare("
                    INSERT INTO historial_saldos (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insert->execute([
                    $mov['cuenta_origen_id'],
                    $mov['movimiento_id'],
                    $mov['tipo'],
                    $mov['monto'],
                    $saldo_anterior,
                    $saldo_org
                ]);
                
                echo "      ✓ Historial creado para cuenta origen #{$mov['cuenta_origen_id']}\n";
            }
        }
        
        $pdo->commit();
        echo "\n✅ Historial completado exitosamente\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "\n❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ No hay movimientos faltantes\n";
}

echo "\nVerificando...\n";
$total_mov = $pdo->query("SELECT COUNT(*) FROM movimientos WHERE activo = 1 AND estado = 'activo'")->fetchColumn();
$total_hist = $pdo->query("SELECT COUNT(DISTINCT movimiento_id) FROM historial_saldos")->fetchColumn();

echo "Movimientos activos: $total_mov\n";
echo "Con historial: $total_hist\n";

if ($total_mov == $total_hist) {
    echo "\n✅ ¡TODO EL HISTORIAL ESTÁ COMPLETO!\n";
} else {
    echo "\n⚠️ Aún faltan " . ($total_mov - $total_hist) . " registros\n";
}
