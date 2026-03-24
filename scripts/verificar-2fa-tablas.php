<?php
require_once dirname(__DIR__) . '/src/config/db.php';

try {
    $pdo = getPDO();
    echo "✓ Conexión a BD exitosa\n\n";
    
    // Verificar tabla trusted_devices
    $stmt = $pdo->query("SHOW TABLES LIKE 'trusted_devices'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla 'trusted_devices' existe\n";
        
        // Contar registros
        $count = $pdo->query("SELECT COUNT(*) FROM trusted_devices")->fetchColumn();
        echo "  - Registros: $count\n";
        
        // Mostrar estructura
        $cols = $pdo->query("DESCRIBE trusted_devices")->fetchAll(PDO::FETCH_COLUMN);
        echo "  - Columnas: " . implode(', ', $cols) . "\n\n";
    } else {
        echo "✗ Tabla 'trusted_devices' NO existe\n\n";
    }
    
    // Verificar tabla two_fa_attempts
    $stmt = $pdo->query("SHOW TABLES LIKE 'two_fa_attempts'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla 'two_fa_attempts' existe\n";
        
        // Contar registros
        $count = $pdo->query("SELECT COUNT(*) FROM two_fa_attempts")->fetchColumn();
        echo "  - Registros: $count\n";
        
        // Mostrar estructura
        $cols = $pdo->query("DESCRIBE two_fa_attempts")->fetchAll(PDO::FETCH_COLUMN);
        echo "  - Columnas: " . implode(', ', $cols) . "\n\n";
    } else {
        echo "✗ Tabla 'two_fa_attempts' NO existe\n\n";
    }
    
    // Verificar columnas en usuarios
    echo "Verificando columnas 2FA en tabla usuarios:\n";
    $campos = ['two_fa_enabled', 'two_fa_secret', 'two_fa_backup_codes', 'two_fa_created_at'];
    foreach ($campos as $campo) {
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE '$campo'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ $campo existe\n";
        } else {
            echo "  ✗ $campo NO existe\n";
        }
    }
    
    // Contar usuarios con 2FA activo
    echo "\n";
    $count = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE two_fa_enabled = 1")->fetchColumn();
    echo "Usuarios con 2FA activo: $count\n";
    
    // Mostrar usuarios con 2FA
    if ($count > 0) {
        $stmt = $pdo->query("
            SELECT id, nombre, email, two_fa_created_at 
            FROM usuarios 
            WHERE two_fa_enabled = 1
        ");
        echo "\nDetalles:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['nombre']} ({$row['email']}) - Activado: {$row['two_fa_created_at']}\n";
        }
    }
    
    // Verificar dispositivos confiables
    echo "\n";
    $count = $pdo->query("SELECT COUNT(*) FROM trusted_devices WHERE expires_at > NOW()")->fetchColumn();
    echo "Dispositivos confiables activos: $count\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("
            SELECT td.device_name, td.ip_address, td.expires_at, u.nombre
            FROM trusted_devices td
            JOIN usuarios u ON td.usuario_id = u.id
            WHERE td.expires_at > NOW()
        ");
        echo "\nDetalles:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['nombre']}: {$row['device_name']} ({$row['ip_address']}) - Expira: {$row['expires_at']}\n";
        }
    }
    
    echo "\n✓ Verificación completa\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
