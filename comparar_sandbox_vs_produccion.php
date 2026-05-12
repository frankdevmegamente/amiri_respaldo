<?php
require_once __DIR__ . '/src/config/db.php';

echo "=== COMPARACIÓN: guiarspar_amiri_db_sandbox (12).sql vs guiaspar_amiri_produccion ===\n\n";

// 1. Obtener estructura de la BD actual (producción)
$pdo = getPDO();

$stmt = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY TABLE_NAME, ORDINAL_POSITION
");
$tables_prod = [];
while ($row = $stmt->fetch()) {
    $tables_prod[$row['TABLE_NAME']][] = $row;
}

// 2. Parsear el archivo SQL sandbox para extraer estructuras
$sqlFile = __DIR__ . '/guiaspar_amiri_db_sandbox (12).sql';
$content = file_get_contents($sqlFile);

// Extraer CREATE TABLE statements
preg_match_all('/CREATE TABLE `(\w+)`\s*\((.*?)\)\s*ENGINE=/s', $content, $matches, PREG_SET_ORDER);

$tables_sandbox = [];
foreach ($matches as $m) {
    $tableName = $m[1];
    $columnsStr = $m[2];
    
    // Parsear columnas
    $lines = explode("\n", $columnsStr);
    $columns = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/`(\w+)`\s+(\w+(?:\([^)]+\))?(?:\s+\w+)*)/', $line, $colMatch)) {
            $columns[] = $colMatch[1] . ': ' . $colMatch[2];
        }
    }
    $tables_sandbox[$tableName] = $columns;
}

// 3. Comparar tablas
$tablesOnlyProd = array_diff(array_keys($tables_prod), array_keys($tables_sandbox));
$tablesOnlySandbox = array_diff(array_keys($tables_sandbox), array_keys($tables_prod));
$commonTables = array_intersect(array_keys($tables_prod), array_keys($tables_sandbox));

echo "TABLAS SOLO EN PRODUCCIÓN (" . count($tablesOnlyProd) . "):\n";
foreach ($tablesOnlyProd as $t) echo "  - $t\n";
echo "\n";

echo "TABLAS SOLO EN SANDBOX (" . count($tablesOnlySandbox) . "):\n";
foreach ($tablesOnlySandbox as $t) echo "  - $t\n";
echo "\n";

echo "TABLAS COMUNES (" . count($commonTables) . "): " . implode(', ', $commonTables) . "\n\n";

// 4. Comparar estructura de tablas comunes
echo "=== DIFERENCIAS EN TABLAS COMUNES ===\n\n";
$diffCount = 0;

foreach ($commonTables as $table) {
    if (strpos($table, 'v_') === 0) continue; // Skip views
    
    // Obtener columnas de producción
    $stmt = $pdo->query("DESCRIBE `$table`");
    $prodCols = [];
    while ($row = $stmt->fetch()) {
        $prodCols[$row['Field']] = $row['Type'] . ($row['Null'] == 'NO' ? ' NOT NULL' : '') . 
                                   ($row['Default'] ? ' DEFAULT ' . $row['Default'] : '') .
                                   ($row['Key'] ? ' ' . $row['Key'] : '') .
                                   ($row['Extra'] ? ' ' . $row['Extra'] : '');
    }
    
    // Obtener columnas del sandbox (del archivo SQL)
    preg_match("/CREATE TABLE `$table`\s*\(.*?\)\s*ENGINE=/s", $content, $tableMatch);
    if (!$tableMatch) continue;
    
    preg_match("/CREATE TABLE `$table`\s*\((.*?)\)\s*ENGINE=/s", $content, $colsMatch);
    if (!$colsMatch) continue;
    
    $sandboxCols = [];
    $lines = explode("\n", $colsMatch[1]);
    foreach ($lines as $line) {
        $line = trim($line, " \t\n\r,");
        if (preg_match('/`(\w+)`\s+(.+)/', $line, $colMatch)) {
            $sandboxCols[$colMatch[1]] = trim($colMatch[2]);
        }
    }
    
    // Comparar
    $onlyProd = array_diff_key($prodCols, $sandboxCols);
    $onlySandbox = array_diff_key($sandboxCols, $prodCols);
    $common = array_intersect_key($prodCols, $sandboxCols);
    
    $hasDiff = false;
    foreach ($common as $col => $type) {
        if (strtolower($prodCols[$col]) != strtolower($sandboxCols[$col])) {
            $hasDiff = true;
        }
    }
    
    if ($onlyProd || $onlySandbox || $hasDiff) {
        $diffCount++;
        echo "TABLA: $table\n";
        if ($onlyProd) {
            echo "  Columnas solo en PRODUCCIÓN:\n";
            foreach ($onlyProd as $col => $type) echo "    + $col: $type\n";
        }
        if ($onlySandbox) {
            echo "  Columnas solo en SANDBOX:\n";
            foreach ($onlySandbox as $col => $type) echo "    + $col: $type\n";
        }
        foreach ($common as $col => $type) {
            if (strtolower($prodCols[$col]) != strtolower($sandboxCols[$col])) {
                echo "  DIFERENCIA en '$col':\n";
                echo "    PROD:  " . $prodCols[$col] . "\n";
                echo "    SBOX:  " . $sandboxCols[$col] . "\n";
            }
        }
        echo "\n";
    }
}

if ($diffCount == 0) {
    echo "No se encontraron diferencias en las tablas comunes.\n";
}

echo "\n=== RESUMEN ===\n";
echo "Tablas solo en producción: " . count($tablesOnlyProd) . "\n";
echo "Tablas solo en sandbox: " . count($tablesOnlySandbox) . "\n";
echo "Tablas con diferencias: $diffCount\n";
