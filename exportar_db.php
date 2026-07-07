<?php
// exportar-bd.php - sube esto a tu servidor sandbox y accede via navegador
require_once __DIR__ . '/src/config/db.php';
$pdo = getPDO();
header('Content-Type: text/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
// Cabecera
echo "-- Backup generado el " . date('Y-m-d H:i:s') . "\n\n";
// Obtener todas las tablas
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    // CREATE TABLE
    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
    $row = $stmt->fetch();
    echo "\n\n" . $row['Create Table'] . ";\n\n";
    // Datos
    $rows = $pdo->query("SELECT * FROM `$table`");
    $cols = $rows->columnCount();
    $first = true;
    
    while ($row = $rows->fetch(PDO::FETCH_NUM)) {
        if ($first) {
            echo "INSERT INTO `$table` VALUES ";
            $first = false;
        } else {
            echo ",";
        }
        $vals = array_map(function($v) {
            return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
        }, $row);
        echo "(" . implode(',', $vals) . ")";
    }
    if (!$first) echo ";\n";
}   