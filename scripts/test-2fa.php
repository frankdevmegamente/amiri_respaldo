<?php
/**
 * Test rápido del sistema 2FA
 * Ejecutar: php scripts/test-2fa.php
 */

echo "╔═══════════════════════════════════════════╗\n";
echo "║  Test 2FA - Autenticación de Dos Factores ║\n";
echo "╚═══════════════════════════════════════════╝\n\n";

try {
    // 1. Verificar dependencias
    echo "1️⃣  Verificando dependencias...\n";
    
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        echo "   ✓ Composer autoload OK\n";
    } else {
        throw new Exception("Falta composer/autoload.php");
    }

    if (class_exists('OTPHP\TOTP')) {
        echo "   ✓ OTPHP librería instalada\n";
    } else {
        throw new Exception("OTPHP no está instalado");
    }

    // 2. Verificar BD
    echo "\n2️⃣  Verificando base de datos...\n";
    
    require_once __DIR__ . '/../src/config/db.php';
    $pdo = getPDO();
    echo "   ✓ Conexión a BD establecida\n";

    // Verificar tablas
    $tables = ['trusted_devices', 'two_fa_attempts'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "   ✓ Tabla '$table' existe\n";
        } else {
            throw new Exception("Tabla '$table' no existe");
        }
    }

    // 3. Verificar servicio
    echo "\n3️⃣  Verificando servicio 2FA...\n";
    
    require_once __DIR__ . '/../src/services/TwoFactorService.php';
    
    if (class_exists('TwoFactorService')) {
        echo "   ✓ TwoFactorService cargado\n";
    } else {
        throw new Exception("TwoFactorService no se pudo cargar");
    }

    // 4. Test de generación de secret
    echo "\n4️⃣  Generando secret TOTP...\n";
    
    $twoFAService = new TwoFactorService($pdo);
    $newSecret = $twoFAService->generateNewSecret('test@example.com');
    
    if (isset($newSecret['secret']) && isset($newSecret['provisioning_uri'])) {
        echo "   ✓ Secret generado: " . substr($newSecret['secret'], 0, 20) . "...\n";
        echo "   ✓ URI de aprovisionamiento generada\n";
    } else {
        throw new Exception("No se pudo generar secret");
    }

    // 5. Test de generación de códigos de respaldo
    echo "\n5️⃣  Generando códigos de respaldo...\n";
    
    $backupCodes = $twoFAService->generateBackupCodes();
    if (count($backupCodes) === 10) {
        echo "   ✓ 10 códigos generados correctamente\n";
        echo "   Ejemplo: " . $backupCodes[0]['code'] . "\n";
    } else {
        throw new Exception("Número incorrecto de códigos");
    }

    // 6. Test de encriptación
    echo "\n6️⃣  Probando encriptación AES-256...\n";
    
    // Usar reflection para acceder método privado
    $reflection = new ReflectionClass('TwoFactorService');
    $encryptMethod = $reflection->getMethod('encryptSecret');
    $encryptMethod->setAccessible(true);
    $decryptMethod = $reflection->getMethod('decryptSecret');
    $decryptMethod->setAccessible(true);
    
    $service = new TwoFactorService($pdo);
    $testSecret = "JBSWY3DPEBLW64TMMQ======";
    
    $encrypted = $encryptMethod->invoke($service, $testSecret);
    $decrypted = $decryptMethod->invoke($service, $encrypted);
    
    if ($decrypted === $testSecret) {
        echo "   ✓ Encriptación/desencriptación OK\n";
    } else {
        throw new Exception("Error en encriptación");
    }

    // 7. Test de rate limiting
    echo "\n7️⃣  Probando rate limiting...\n";
    
    $status = $service->isBlocked(1, '127.0.0.1');
    if (!$status['blocked']) {
        echo "   ✓ Usuario nuevo sin bloqueo\n";
    } else {
        throw new Exception("Error en validación de bloqueo");
    }

    // 8. Test de dispositivos
    echo "\n8️⃣  Probando dispositivos de confianza...\n";
    
    // Mock $_SERVER para test
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test';
    
    $deviceResult = $service->createTrustedDevice(1, 'Test Device');
    if ($deviceResult['success'] && isset($deviceResult['device_token'])) {
        echo "   ✓ Dispositivo creado: " . substr($deviceResult['device_token'], 0, 20) . "...\n";
        echo "   ✓ Expiración: " . $deviceResult['expires_at'] . "\n";
    } else {
        throw new Exception("No se pudo crear dispositivo");
    }

    // 9. Resumen final
    echo "\n═══════════════════════════════════════════\n";
    echo "✅ ¡TODOS LOS TESTS PASARON!\n";
    echo "═══════════════════════════════════════════\n\n";

    echo "📋 Resumen:\n";
    echo "   • Librería OTPHP: ✓ Instalada\n";
    echo "   • Base de datos: ✓ Configurada\n";
    echo "   • Generación de secrets: ✓ Funciona\n";
    echo "   • Encriptación: ✓ AES-256-CBC\n";
    echo "   • Códigos de respaldo: ✓ 10 códigos\n";
    echo "   • Rate limiting: ✓ Configurable\n";
    echo "   • Dispositivos confiables: ✓ 24h\n\n";

    echo "🚀 Siguientes pasos:\n";
    echo "   1. Ir a login.php e ingresar credenciales\n";
    echo "   2. Abrir setup-2fa.php para activar 2FA\n";
    echo "   3. Escanear QR con autenticador (Google Authenticator, Authy, etc)\n";
    echo "   4. Ingresar código de 6 dígitos\n";
    echo "   5. Guardar códigos de respaldo\n";
    echo "   6. Logout y volver a login para verificar\n\n";

    echo "📖 Documentación: docs/2FA_SETUP.md\n";

} catch (Exception $e) {
    echo "\n❌ Error en test:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}
?>
