<?php

/**

 * Script para aplicar migraciones 2FA

 * Ejecutar: php scripts/migrate-2fa.php

 */



require_once __DIR__ . '/../src/config/db.php';



echo "╔══════════════════════════════════════════╗\n";

echo "║  Migrando Schema para 2FA (TOTP)        ║\n";

echo "╚══════════════════════════════════════════╝\n\n";



try {

    $pdo = getPDO();



    // Leer archivo de migraciones

    $migrations = file_get_contents(__DIR__ . '/../src/migrations/001_add_2fa_support.sql');



    // Dividir por ';' y ejecutar cada comando

    $commands = array_filter(array_map('trim', explode(';', $migrations)));



    foreach ($commands as $command) {

        if (empty($command)) continue;



        echo "Ejecutando: " . substr($command, 0, 60) . "...\n";



        try {

            $pdo->exec($command);

            echo "  ✓ OK\n";

        } catch (PDOException $e) {

            // Ignorar errores de columnas que ya existen

            if (strpos($e->getMessage(), 'Duplicate column') !== false) {

                echo "  ⚠ Columna ya existe\n";

            } else if (strpos($e->getMessage(), 'already exists') !== false) {

                echo "  ⚠ Tabla ya existe\n";

            } else {

                throw $e;

            }

        }

    }



    echo "\n✅ Migraciones completadas exitosamente!\n\n";



    // Verificar que las tablas se crearon

    echo "Verificando tablas creadas:\n";

    

    $tables = [

        'usuarios' => ['two_fa_enabled', 'two_fa_secret', 'two_fa_backup_codes'],

        'trusted_devices' => ['usuario_id', 'device_token', 'expires_at'],

        'two_fa_attempts' => ['usuario_id', 'failed_attempts', 'blocked_until']

    ];



    foreach ($tables as $table => $columns) {

        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table");

        $stmt->execute();

        $existingColumns = array_column($stmt->fetchAll(), 'Field');



        echo "\n📊 Tabla: $table\n";



        foreach ($columns as $col) {

            if (in_array($col, $existingColumns)) {

                echo "  ✓ Columna: $col\n";

            } else {

                echo "  ✗ Falta columna: $col\n";

            }

        }

    }



    echo "\n═══════════════════════════════════════════\n";

    echo "✅ 2FA está listo para usar!\n";

    echo "\n📖 Ver documentación: docs/2FA_SETUP.md\n";

    echo "🔗 Panel de usuario: setup-2fa.php\n";

    echo "═══════════════════════════════════════════\n";



} catch (Exception $e) {

    echo "\n❌ Error durante migración:\n";

    echo $e->getMessage() . "\n";

    exit(1);

}

?>

