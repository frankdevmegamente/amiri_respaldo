<?php
/**
 * Script para actualizar los servicios de movimientos
 * Asigna el servicio correcto a cada tipo de movimiento
 */

require_once __DIR__ . '/../src/config/db.php';

try {
    $pdo = getPDO();
    $pdo->beginTransaction();
    
    echo "=== Actualizando registros de movimientos ===\n\n";
    
    // 1. Préstamos de prestamistas (entrada)
    $sql = "UPDATE movimientos 
            SET servicio_id = 10 
            WHERE prestamista_id IS NOT NULL 
            AND tipo = 'entrada' 
            AND (servicio_id = 1 OR servicio_id IS NULL)";
    $stmt = $pdo->query($sql);
    echo "✅ Préstamos de prestamistas: {$stmt->rowCount()} registros actualizados\n";
    
    // 2. Pagos de préstamos (salida)
    $sql = "UPDATE movimientos 
            SET servicio_id = 14 
            WHERE prestamista_id IS NOT NULL 
            AND tipo = 'salida'
            AND (servicio_id = 1 OR servicio_id IS NULL)";
    $stmt = $pdo->query($sql);
    echo "✅ Pagos de préstamos: {$stmt->rowCount()} registros actualizados\n";
    
    // 3. Gastos
    $sql = "UPDATE movimientos 
            SET servicio_id = 12 
            WHERE gasto_id IS NOT NULL";
    $stmt = $pdo->query($sql);
    echo "✅ Gastos: {$stmt->rowCount()} registros actualizados\n";
    
    // 4. Pagos a proveedores
    $sql = "UPDATE movimientos 
            SET servicio_id = 11 
            WHERE proveedor_id IS NOT NULL 
            AND tipo = 'salida'";
    $stmt = $pdo->query($sql);
    echo "✅ Pagos a proveedores: {$stmt->rowCount()} registros actualizados\n";
    
    // 5. Pagos a clientes (no ventas)
    $sql = "UPDATE movimientos 
            SET servicio_id = 13 
            WHERE cliente_id IS NOT NULL 
            AND tipo IN ('salida', 'traspaso') 
            AND servicio_id NOT IN (1, 2, 3, 5, 6)";
    $stmt = $pdo->query($sql);
    echo "✅ Pagos a clientes: {$stmt->rowCount()} registros actualizados\n";
    
    // 6. Pagos de comisiones a promotores
    $sql = "UPDATE movimientos 
            SET servicio_id = 15 
            WHERE promotor_id IS NOT NULL 
            AND tipo = 'salida'
            AND descripcion LIKE '%Pago de Comisiones%'";
    $stmt = $pdo->query($sql);
    echo "✅ Pagos de comisiones a promotores: {$stmt->rowCount()} registros actualizados\n";
    
    $pdo->commit();
    
    echo "\n=== Actualización completada exitosamente ===\n";
    
    // Mostrar resumen de servicios
    echo "\n=== Resumen de movimientos por servicio ===\n";
    $stmt = $pdo->query("
        SELECT s.nombre, COUNT(*) as total
        FROM movimientos fm
        LEFT JOIN servicios s ON fm.servicio_id = s.id
        WHERE fm.activo = 1
        GROUP BY fm.servicio_id, s.nombre
        ORDER BY total DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo str_pad($row['nombre'] ?? 'Sin servicio', 30) . ": " . $row['total'] . "\n";
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
