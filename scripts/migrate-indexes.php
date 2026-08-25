<?php
/**
 * Script para aplicar migración de índices de rendimiento
 * Ejecutar: php scripts/migrate-indexes.php
 */

require_once __DIR__ . '/../src/config/db.php';

echo "╔══════════════════════════════════════════╗\n";
echo "║  Agregando índices de rendimiento        ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

try {
    $pdo = getPDO();

    $migrations = file_get_contents(__DIR__ . '/../src/migrations/008_add_performance_indexes.sql');

    $commands = array_filter(array_map('trim', explode(';', $migrations)));

    foreach ($commands as $command) {
        if (empty($command)) continue;

        echo "Ejecutando: " . substr($command, 0, 80) . "...\n";

        try {
            $pdo->exec($command);
            echo "  ✓ OK\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "  ⚠ Índice ya existe\n";
            } else if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  ⚠ Ya existe\n";
            } else {
                throw $e;
            }
        }
    }

    echo "\n✅ Migración de índices completada!\n\n";

    // Verificar índices creados
    echo "Verificando índices:\n";
    $tablesToCheck = [
        'movimientos' => ['idx_perf_divisas'],
        'deudas_clientes' => ['idx_fecha_activo'],
        'gastos' => ['idx_fecha_activo'],
    ];

    foreach ($tablesToCheck as $table => $expectedIndexes) {
        $stmt = $pdo->prepare("SHOW INDEX FROM `$table`");
        $stmt->execute();
        $existingIndexes = array_unique(array_column($stmt->fetchAll(), 'Key_name'));

        echo "\n📊 Tabla: $table\n";
        foreach ($expectedIndexes as $idx) {
            if (in_array($idx, $existingIndexes)) {
                echo "  ✓ Índice: $idx\n";
            } else {
                echo "  ✗ Falta índice: $idx\n";
            }
        }
    }

    echo "\n═══════════════════════════════════════════\n";
    echo "✅ Índices listos!\n";
    echo "═══════════════════════════════════════════\n";

} catch (Exception $e) {
    echo "\n❌ Error durante migración:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
