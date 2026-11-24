<?php
require_once __DIR__ . '/src/config/db.php';
session_start();
set_time_limit(600);

$mysqlCli = 'C:\\xampp\\mysql\\bin\\mysql.exe';
if (!file_exists($mysqlCli)) {
    $mysqlCli = 'mysql';
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$pagina_actual = basename($_SERVER['PHP_SELF']);

$message = '';
$messageType = '';
$tempDb = $_SESSION['sync_temp_db'] ?? '';
$results = $_SESSION['sync_results'] ?? [];
$importedFile = $_SESSION['sync_file'] ?? '';

function findSqlFiles(): array
{
    $dirs = [
        __DIR__,
        __DIR__ . '/bd',
        __DIR__ . '/backups',
        __DIR__ . '/scripts',
    ];
    $files = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        foreach (glob($dir . '/*.sql') as $path) {
            $size = filesize($path);
            $files[] = [
                'path' => $path,
                'name' => basename($path),
                'dir' => basename(dirname($path)),
                'size' => $size,
                'size_hr' => $size > 1048576 ? round($size / 1048576, 2) . ' MB' : round($size / 1024, 1) . ' KB',
            ];
        }
    }
    usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
    return $files;
}

function getPrimaryKey(PDO $pdo, string $db, string $table): ?string
{
    $stmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = " . $pdo->quote($db) . " AND TABLE_NAME = " . $pdo->quote($table) . " AND COLUMN_KEY = 'PRI' LIMIT 1");
    $col = $stmt->fetchColumn();
    return $col ?: null;
}

function getTableCount(PDO $pdo, string $db, string $table): int
{
    try {
        return (int)$pdo->query("SELECT COUNT(*) FROM `{$db}`.`{$table}`")->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getCommonColumns(PDO $pdo, string $db1, string $table1, string $db2, string $table2): array
{
    $q1 = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = " . $pdo->quote($db1) . " AND TABLE_NAME = " . $pdo->quote($table1));
    $q2 = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = " . $pdo->quote($db2) . " AND TABLE_NAME = " . $pdo->quote($table2));
    $cols1 = $q1->fetchAll(PDO::FETCH_COLUMN);
    $cols2 = $q2->fetchAll(PDO::FETCH_COLUMN);
    return array_intersect($cols1, $cols2);
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1024, 1) . ' KB';
}

$pdo = getPDO();
$localDb = $pdo->query("SELECT DATABASE()")->fetchColumn();

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($httpMethod === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? ($_GET['action'] ?? '');

    try {
        switch ($action) {
            case 'import':
                $selectedFile = '';

                if (!empty($_FILES['sql_upload']) && $_FILES['sql_upload']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $origName = basename($_FILES['sql_upload']['name']);
                    $destPath = $uploadDir . $origName;
                    if (file_exists($destPath)) {
                        $destPath = $uploadDir . pathinfo($origName, PATHINFO_FILENAME) . '_' . date('Ymd_His') . '.' . pathinfo($origName, PATHINFO_EXTENSION);
                    }
                    move_uploaded_file($_FILES['sql_upload']['tmp_name'], $destPath);
                    $selectedFile = $destPath;
                } elseif (!empty($_POST['sql_file'])) {
                    $selectedFile = $_POST['sql_file'];
                }

                if (empty($selectedFile) || !file_exists($selectedFile)) {
                    throw new Exception('Selecciona o sube un archivo SQL válido.');
                }

                $tempDb = 'sync_temp_' . date('Ymd_His');
                $importStart = microtime(true);
                $importOk = false;
                $mysqlOutput = [];

                $createCmd = "\"{$mysqlCli}\" --user=root --execute=" . escapeshellarg("CREATE DATABASE `{$tempDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci") . " 2>&1";
                exec($createCmd, $mysqlOutput, $exitCode);
                if ($exitCode !== 0) {
                    throw new Exception('Error al crear BD temporal: ' . implode("\n", $mysqlOutput));
                }

                $importCmd = "\"{$mysqlCli}\" --user=root {$tempDb} < " . escapeshellarg($selectedFile) . " 2>&1";
                exec($importCmd, $mysqlOutput, $exitCode);
                if ($exitCode !== 0) {
                    $dropCmd = "\"{$mysqlCli}\" --user=root --execute=" . escapeshellarg("DROP DATABASE IF EXISTS `{$tempDb}`") . " 2>&1";
                    exec($dropCmd);
                    throw new Exception('Error al importar SQL: ' . implode("\n", array_slice($mysqlOutput, 0, 5)));
                }

                $importTime = round(microtime(true) - $importStart, 1);

                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $tables = $tempPDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                if (empty($tables)) {
                    $tempPDO = null;
                    $dropCmd = "\"{$mysqlCli}\" --user=root --execute=" . escapeshellarg("DROP DATABASE IF EXISTS `{$tempDb}`") . " 2>&1";
                    exec($dropCmd);
                    throw new Exception('El archivo SQL no contiene tablas.');
                }

                $allResults = [];
                $totalMissingAll = 0;
                $tablesWithMissing = 0;

                foreach ($tables as $table) {
                    $tableType = $tempPDO->query("SELECT TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . $tempPDO->quote($tempDb) . " AND TABLE_NAME = " . $tempPDO->quote($table))->fetchColumn();
                    if ($tableType !== 'BASE TABLE') continue;

                    $pk = getPrimaryKey($tempPDO, $tempDb, $table);
                    $countSql = getTableCount($tempPDO, $tempDb, $table);

                    if ($countSql === 0) {
                        $allResults[] = [
                            'table' => $table,
                            'pk' => $pk,
                            'count_sql' => 0,
                            'count_local' => 0,
                            'missing_count' => 0,
                            'missing_ids' => [],
                            'status' => 'empty',
                            'exists_local' => true,
                        ];
                        continue;
                    }

                    $existsLocal = false;
                    try {
                        $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1");
                        $existsLocal = true;
                    } catch (Exception $e) {
                        $existsLocal = false;
                    }

                    if (!$existsLocal) {
                        $allResults[] = [
                            'table' => $table,
                            'pk' => $pk,
                            'count_sql' => $countSql,
                            'count_local' => 0,
                            'missing_count' => $countSql,
                            'missing_ids' => [],
                            'status' => 'new_table',
                            'exists_local' => false,
                        ];
                        $totalMissingAll += $countSql;
                        $tablesWithMissing++;
                        continue;
                    }

                    $countLocal = getTableCount($pdo, $localDb, $table);

                    if ($pk) {
                        $pdo->exec("SET SESSION group_concat_max_len = 1000000");
                        $allIds = $tempPDO->query("SELECT `{$pk}` FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                        $missingIds = [];

                        $chunks = array_chunk($allIds, 500);
                        foreach ($chunks as $chunk) {
                            $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                            $stmt = $pdo->prepare("SELECT `{$pk}` FROM `{$table}` WHERE `{$pk}` IN ({$placeholders})");
                            $stmt->execute($chunk);
                            $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            $missingIds = array_merge($missingIds, array_diff($chunk, $existing));
                        }

                        $missingCount = count($missingIds);
                    } else {
                        $allIds = $tempPDO->query("SELECT COUNT(*) as c FROM `{$table}`")->fetchColumn();
                        $missingCount = max(0, $countSql - $countLocal);
                        $missingIds = [];
                    }

                    if ($missingCount > 0) $tablesWithMissing++;
                    $totalMissingAll += $missingCount;

                    $allResults[] = [
                        'table' => $table,
                        'pk' => $pk,
                        'count_sql' => $countSql,
                        'count_local' => $countLocal,
                        'missing_count' => $missingCount,
                        'missing_ids' => $missingIds,
                        'status' => $missingCount > 0 ? 'missing' : 'ok',
                        'exists_local' => true,
                    ];
                }

                if (isset($_SESSION['sync_temp_db']) && $_SESSION['sync_temp_db'] !== $tempDb) {
                    $oldDb = $_SESSION['sync_temp_db'];
                    $dropCmd = "\"{$mysqlCli}\" --user=root --execute=" . escapeshellarg("DROP DATABASE IF EXISTS `{$oldDb}`") . " 2>&1";
                    exec($dropCmd);
                }

                $_SESSION['sync_temp_db'] = $tempDb;
                $_SESSION['sync_results'] = $allResults;
                $_SESSION['sync_file'] = basename($selectedFile);

                $message = "Archivo importado en {$importTime}s. {$totalMissingAll} registros faltantes en {$tablesWithMissing} tablas.";
                $messageType = $totalMissingAll > 0 ? 'info' : 'success';
                break;

            case 'refresh':
                if (empty($tempDb)) throw new Exception('No hay BD temporal. Importa un archivo primero.');

                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $tables = $tempPDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                $allResults = [];
                $totalMissingAll = 0;
                $tablesWithMissing = 0;

                foreach ($tables as $table) {
                    $tableType = $tempPDO->query("SELECT TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . $tempPDO->quote($tempDb) . " AND TABLE_NAME = " . $tempPDO->quote($table))->fetchColumn();
                    if ($tableType !== 'BASE TABLE') continue;

                    $pk = getPrimaryKey($tempPDO, $tempDb, $table);
                    $countSql = getTableCount($tempPDO, $tempDb, $table);

                    if ($countSql === 0) {
                        $allResults[] = ['table' => $table, 'pk' => $pk, 'count_sql' => 0, 'count_local' => 0, 'missing_count' => 0, 'missing_ids' => [], 'status' => 'empty', 'exists_local' => true];
                        continue;
                    }

                    $existsLocal = false;
                    try { $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1"); $existsLocal = true; } catch (Exception $e) {}

                    if (!$existsLocal) {
                        $allResults[] = ['table' => $table, 'pk' => $pk, 'count_sql' => $countSql, 'count_local' => 0, 'missing_count' => $countSql, 'missing_ids' => [], 'status' => 'new_table', 'exists_local' => false];
                        $totalMissingAll += $countSql;
                        $tablesWithMissing++;
                        continue;
                    }

                    $countLocal = getTableCount($pdo, $localDb, $table);

                    if ($pk) {
                        $allIds = $tempPDO->query("SELECT `{$pk}` FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                        $missingIds = [];
                        $chunks = array_chunk($allIds, 500);
                        foreach ($chunks as $chunk) {
                            $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                            $stmt = $pdo->prepare("SELECT `{$pk}` FROM `{$table}` WHERE `{$pk}` IN ({$placeholders})");
                            $stmt->execute($chunk);
                            $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            $missingIds = array_merge($missingIds, array_diff($chunk, $existing));
                        }
                        $missingCount = count($missingIds);
                    } else {
                        $missingCount = max(0, $countSql - $countLocal);
                        $missingIds = [];
                    }

                    if ($missingCount > 0) $tablesWithMissing++;
                    $totalMissingAll += $missingCount;

                    $allResults[] = [
                        'table' => $table, 'pk' => $pk, 'count_sql' => $countSql, 'count_local' => $countLocal,
                        'missing_count' => $missingCount, 'missing_ids' => $missingIds,
                        'status' => $missingCount > 0 ? 'missing' : 'ok', 'exists_local' => true,
                    ];
                }

                $_SESSION['sync_results'] = $allResults;
                $message = "Comparación actualizada. {$totalMissingAll} registros faltantes en {$tablesWithMissing} tablas.";
                $messageType = $totalMissingAll > 0 ? 'info' : 'success';
                break;

            case 'preview':
                $table = $_GET['table'] ?? '';
                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $result = null;
                foreach ($_SESSION['sync_results'] as $r) {
                    if ($r['table'] === $table) { $result = $r; break; }
                }
                if (!$result) { http_response_code(404); echo json_encode(['error' => 'Tabla no encontrada']); exit; }

                if ($result['status'] === 'new_table') {
                    $data = $tempPDO->query("SELECT * FROM `{$table}` LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode(['table' => $table, 'status' => 'new_table', 'columns' => !empty($data) ? array_keys($data[0]) : [], 'rows' => $data]);
                    exit;
                }

                $dbg = [
                    'has_pk' => !empty($result['pk']),
                    'pk' => $result['pk'] ?? null,
                    'has_missing_ids' => !empty($result['missing_ids']),
                    'missing_ids_count' => is_array($result['missing_ids'] ?? null) ? count($result['missing_ids']) : 0,
                    'missing_count' => $result['missing_count'] ?? 0,
                    'type_missing_ids' => gettype($result['missing_ids'] ?? null),
                ];

                if (!empty($result['pk']) && !empty($result['missing_ids'])) {
                    $missingSlice = array_slice($result['missing_ids'], 0, 200);
                    $dbg['slice_count'] = count($missingSlice);
                    $dbg['slice_sample'] = array_slice($missingSlice, 0, 5);
                    $placeholders = rtrim(str_repeat('?,', count($missingSlice)), ',');
                    $stmt = $tempPDO->prepare("SELECT * FROM `{$table}` WHERE `{$result['pk']}` IN ({$placeholders}) ORDER BY `{$result['pk']}`");
                    $stmt->execute($missingSlice);
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $dbg['rows_found'] = count($data);
                    echo json_encode(['table' => $table, 'status' => 'missing', 'debug' => $dbg, 'columns' => !empty($data) ? array_keys($data[0]) : [], 'rows' => $data]);
                    exit;
                }

                if ($result['missing_count'] > 0 && empty($result['missing_ids'])) {
                    $data = $tempPDO->query("SELECT * FROM `{$table}` LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode(['table' => $table, 'status' => 'missing', 'debug' => $dbg, 'columns' => !empty($data) ? array_keys($data[0]) : [], 'rows' => $data, 'note' => 'No se pudo determinar IDs exactos (sin PK), mostrando registros del SQL.']);
                    exit;
                }

                echo json_encode(['table' => $table, 'status' => 'ok', 'debug' => $dbg, 'columns' => [], 'rows' => []]);
                exit;

            case 'sync_all':
                if (empty($tempDb)) throw new Exception('No hay BD temporal. Importa un archivo primero.');
                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                $pdo->beginTransaction();

                $synced = 0;
                $errors = [];
                $newTablesCreated = 0;

                foreach ($_SESSION['sync_results'] as &$result) {
                    if ($result['missing_count'] === 0) continue;

                    if (!$result['exists_local']) {
                        try {
                            $createStmt = $tempPDO->query("SHOW CREATE TABLE `{$result['table']}`")->fetch();
                            $createSQL = $createStmt['Create Table'];
                            $createSQL = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $createSQL);
                            $pdo->exec($createSQL);
                            $newTablesCreated++;
                        } catch (Exception $e) {
                            $errors[] = "{$result['table']}: error al crear tabla - " . $e->getMessage();
                            continue;
                        }
                    }

                    if (empty($result['pk']) || empty($result['missing_ids'])) {
                        $commonCols = getCommonColumns($pdo, $localDb, $result['table'], $tempDb, $result['table']);
                        if (!empty($commonCols)) {
                            $colList = '`' . implode('`,`', $commonCols) . '`';
                            try {
                                $pdo->exec("INSERT IGNORE INTO `{$result['table']}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$result['table']}`");
                                $synced += $result['missing_count'];
                                $result['missing_count'] = 0;
                                $result['missing_ids'] = [];
                                $result['status'] = 'ok';
                                $result['count_local'] = getTableCount($pdo, $localDb, $result['table']);
                            } catch (Exception $e) {
                                $errors[] = "{$result['table']}: " . $e->getMessage();
                            }
                        } else {
                            $errors[] = "{$result['table']}: no hay columnas comunes entre las tablas";
                        }
                        continue;
                    }

                    $missingSlice = $result['missing_ids'];
                    $pk = $result['pk'];
                    $totalInserted = 0;

                    $commonCols = getCommonColumns($pdo, $localDb, $result['table'], $tempDb, $result['table']);
                    if (empty($commonCols)) {
                        $errors[] = "{$result['table']}: no hay columnas comunes";
                        continue;
                    }

                    $colList = '`' . implode('`,`', $commonCols) . '`';

                    $chunks = array_chunk($missingSlice, 100);
                    foreach ($chunks as $chunk) {
                        $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                        try {
                            $stmt = $pdo->prepare("INSERT IGNORE INTO `{$result['table']}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$result['table']}` WHERE `{$pk}` IN ({$placeholders})");
                            $stmt->execute($chunk);
                            $totalInserted += count($chunk);
                        } catch (Exception $e) {
                            $errors[] = "{$result['table']} (lote): " . $e->getMessage();
                        }
                    }

                    $synced += $totalInserted;
                    $result['missing_count'] = 0;
                    $result['missing_ids'] = [];
                    $result['status'] = 'ok';
                    $result['count_local'] = getTableCount($pdo, $localDb, $result['table']);
                }
                unset($result);

                $pdo->commit();
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

                $_SESSION['sync_results'] = $_SESSION['sync_results'];

                $msgParts = ["Sincronización completada: {$synced} registros insertados en total"];
                if ($newTablesCreated > 0) $msgParts[] = "{$newTablesCreated} tablas creadas";
                if (!empty($errors)) $msgParts[] = count($errors) . " errores";
                $message = implode(', ', $msgParts) . '.';
                $messageType = empty($errors) ? 'success' : 'warning';
                if (!empty($errors)) {
                    $message .= '<!--ERRORS-->' . implode('\\n', array_slice($errors, 0, 10));
                }
                break;

            case 'sync_table':
                $table = $_POST['sync_table'] ?? '';
                if (empty($table)) throw new Exception('No se especificó la tabla.');
                if (empty($tempDb)) throw new Exception('No hay BD temporal.');

                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $result = null;
                foreach ($_SESSION['sync_results'] as &$r) {
                    if ($r['table'] === $table) { $result = &$r; break; }
                }
                if (!$result) throw new Exception("Tabla '{$table}' no encontrada en resultados.");
                if ($result['missing_count'] === 0) throw new Exception("No hay registros faltantes en '{$table}'.");

                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                $pdo->beginTransaction();

                if (!$result['exists_local']) {
                    $createStmt = $tempPDO->query("SHOW CREATE TABLE `{$result['table']}`")->fetch();
                    $createSQL = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $createStmt['Create Table']);
                    $pdo->exec($createSQL);
                }

                if (empty($result['pk']) || empty($result['missing_ids'])) {
                    $commonCols = getCommonColumns($pdo, $localDb, $result['table'], $tempDb, $result['table']);
                    $colList = '`' . implode('`,`', $commonCols) . '`';
                    $pdo->exec("INSERT IGNORE INTO `{$result['table']}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$result['table']}`");
                    $insertedCount = $result['missing_count'];
                } else {
                    $missingSlice = $result['missing_ids'];
                    $pk = $result['pk'];
                    $commonCols = getCommonColumns($pdo, $localDb, $result['table'], $tempDb, $result['table']);
                    $colList = '`' . implode('`,`', $commonCols) . '`';
                    $insertedCount = 0;

                    $chunks = array_chunk($missingSlice, 100);
                    foreach ($chunks as $chunk) {
                        $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                        $stmt = $pdo->prepare("INSERT IGNORE INTO `{$result['table']}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$result['table']}` WHERE `{$pk}` IN ({$placeholders})");
                        $stmt->execute($chunk);
                        $insertedCount += count($chunk);
                    }
                }

                $pdo->commit();
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

                $result['missing_count'] = 0;
                $result['missing_ids'] = [];
                $result['status'] = 'ok';
                $result['count_local'] = getTableCount($pdo, $localDb, $table);
                unset($result);

                $message = "Tabla '{$table}': {$insertedCount} registros sincronizados.";
                $messageType = 'success';
                break;

            case 'cleanup':
                if (!empty($tempDb)) {
                    $dropCmd = "\"{$mysqlCli}\" --user=root --execute=" . escapeshellarg("DROP DATABASE IF EXISTS `{$tempDb}`") . " 2>&1";
                    exec($dropCmd);
                }
                unset($_SESSION['sync_temp_db'], $_SESSION['sync_results'], $_SESSION['sync_file']);
                $tempDb = '';
                $results = [];
                $importedFile = '';
                $message = 'Base de datos temporal eliminada.';
                $messageType = 'info';
                break;
        }
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $message = $e->getMessage();
        $messageType = 'danger';
    }

    if (in_array($action, ['preview'])) exit;

    $results = $_SESSION['sync_results'] ?? [];
    $tempDb = $_SESSION['sync_temp_db'] ?? '';
    $importedFile = $_SESSION['sync_file'] ?? '';
}

$sqlFiles = findSqlFiles();
$hasTempDb = !empty($tempDb);
$totalMissing = array_sum(array_column($results, 'missing_count'));
$tablesWithMissing = count(array_filter($results, fn($r) => $r['missing_count'] > 0));
$totalTables = count($results);
$importedFile = $_SESSION['sync_file'] ?? '';

$scriptUrl = 'sincronizar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; }
        .card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .card-title { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .card-title i { font-size: 1.3rem; }
        .stat-box { background: var(--nx-surface-2) !important; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-box .num { font-size: 1.8rem; font-weight: 700; line-height: 1.2; }
        .stat-box .label { font-size: 0.75rem; color: var(--nx-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        .badge-status { font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 20px; font-weight: 500; }
        .table-result th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--nx-text-muted); font-weight: 600; border-top: none; }
        .table-result td { vertical-align: middle; font-size: 0.9rem; }
        .preview-wrap { max-height: 450px; overflow-y: auto; border: 1px solid #f5f2f4; border-radius: 8px; display: none; }
        .preview-wrap table { margin-bottom: 0; font-size: 0.8rem; }
        .preview-wrap th { position: sticky; top: 0; background: var(--nx-surface-2) !important; z-index: 1; font-size: 0.75rem; white-space: nowrap; }
        .preview-wrap td { white-space: nowrap; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        .preview-loading { text-align: center; padding: 2rem; display: none; }
        .preview-loading .spinner { width: 40px; height: 40px; border: 4px solid #f5f2f4; border-top-color: var(--nx-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .sync-progress { display: none; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        [data-bs-theme="dark"] .card { background: var(--nx-surface); box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        [data-bs-theme="dark"] .stat-box { background: var(--nx-surface-2); }
        [data-bs-theme="dark"] .stat-box .label { color: var(--nx-text-muted); }
        [data-bs-theme="dark"] .table-result th { color: var(--nx-text-muted); border-color: var(--nx-border); }
        [data-bs-theme="dark"] .table-result td { border-color: var(--nx-border); color: #c8cee8; }
        [data-bs-theme="dark"] .preview-wrap { border-color: var(--nx-border); }
        [data-bs-theme="dark"] .preview-wrap th { background: var(--nx-surface-2); color: var(--nx-text); border-color: var(--nx-border); }
        [data-bs-theme="dark"] .preview-wrap td { border-color: var(--nx-border); color: #c8cee8; }
        [data-bs-theme="dark"] .preview-loading .spinner { border-color: var(--nx-border); border-top-color: var(--nx-primary); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background: var(--nx-surface-2); color: var(--nx-text); border-color: var(--nx-border); }
        [data-bs-theme="dark"] .form-control:focus { background: var(--nx-surface); color: var(--nx-text); border-color: var(--nx-primary); box-shadow: 0 0 0 0.2rem rgba(197,143,184,0.25); }
        [data-bs-theme="dark"] .form-label { color: #c8cee8; }
        [data-bs-theme="dark"] .btn-close { filter: invert(1); }
        .file-size { font-size: 0.75rem; color: var(--nx-text-muted); }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php include __DIR__ . '/src/assets/partials/header.php'; ?>
    <?php include __DIR__ . '/src/assets/partials/sidebar.php'; ?>
    <div class="body-wrapper">
        <div class="body-wrapper-inner">
            <div class="container-fluid px-4" style="max-width:100%;">

                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show fade-in" role="alert">
                    <i class="ti ti-<?= $messageType === 'success' ? 'circle-check' : ($messageType === 'danger' ? 'alert-triangle' : ($messageType === 'warning' ? 'alert-circle' : 'info-circle')) ?> me-1"></i>
                    <?php if (str_contains($message, '<!--ERRORS-->')): ?>
                        <?php $parts = explode('<!--ERRORS-->', $message, 2); ?>
                        <?= htmlspecialchars($parts[0]) ?>
                        <div class="mt-2 small">
                            <strong>Errores:</strong><br>
                            <?php foreach (explode('\\n', $parts[1]) as $e): ?>
                                <span class="text-danger">• <?= htmlspecialchars($e) ?></span><br>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <?= htmlspecialchars($message) ?>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="position:relative;overflow:hidden;">
                            <div style="position:absolute;top:-60px;right:-60px;width:180px;height:180px;background:var(--nx-primary);opacity:0.08;border-radius:50%;pointer-events:none;"></div>
                            <div style="position:absolute;bottom:-40px;left:-40px;width:120px;height:120px;background:var(--nx-success);opacity:0.08;border-radius:50%;pointer-events:none;"></div>
                            <div class="card-body p-4" style="position:relative;z-index:1;">
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                        <i class="ti ti-transfer-in fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-semibold mb-0" style="font-size:1.2rem;">Sincronizar Base de Datos</h5>
                                        <small class="text-muted">Importa un dump .sql y migra los registros faltantes a tu BD local</small>
                                    </div>
                                </div>
                                <?php if ($hasTempDb): ?>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <span class="badge bg-info bg-opacity-10 text-info border-0" style="font-size:0.8rem;">
                                        <i class="ti ti-database"></i> BD temporal: <strong><?= htmlspecialchars($tempDb) ?></strong>
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border-0" style="font-size:0.8rem;">
                                        <i class="ti ti-file-text"></i> <?= htmlspecialchars($importedFile) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3"><i class="ti ti-file-upload" style="color:var(--nx-primary);"></i> Archivo SQL</h5>
                                <form method="POST" enctype="multipart/form-data" id="importForm">
                                    <input type="hidden" name="action" value="import">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Seleccionar archivo existente</label>
                                        <select class="form-select" name="sql_file" id="sqlFile">
                                            <option value="">-- Elegir archivo --</option>
                                            <?php foreach ($sqlFiles as $f): ?>
                                            <option value="<?= htmlspecialchars($f['path']) ?>" data-size="<?= $f['size_hr'] ?>">
                                                <?= htmlspecialchars($f['name']) ?> (<?= $f['size_hr'] ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="text-center text-muted small mb-2">— o —</div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subir archivo</label>
                                        <input type="file" class="form-control" name="sql_upload" accept=".sql">
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary" id="importBtn">
                                            <i class="ti ti-database-import"></i> Importar y comparar
                                        </button>
                                    </div>
                                    <div id="importProgress" class="sync-progress mt-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            <span>Importando archivo SQL, esto puede tomar unos momentos...</span>
                                        </div>
                                    </div>
                                </form>

                                <?php if ($hasTempDb): ?>
                                <hr>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="refresh">
                                        <button type="submit" class="btn btn-outline-info btn-sm">
                                            <i class="ti ti-refresh"></i> Re-comparar
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="cleanup">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="ti ti-trash"></i> Limpiar BD temporal
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3"><i class="ti ti-info-circle" style="color:var(--nx-success);"></i> Instrucciones</h5>
                                <ol class="small text-muted mb-0" style="line-height:1.8;">
                                    <li><strong>Selecciona</strong> un archivo <code>.sql</code> existente o <strong>sube</strong> uno nuevo (dump de producción).</li>
                                    <li>La herramienta <strong>importa</strong> el archivo a una BD temporal y <strong>compara</strong> todas las tablas contra tu BD local.</li>
                                    <li>Revisa los resultados: tablas con registros faltantes aparecen resaltadas.</li>
                                    <li>Haz clic en <span class="badge bg-info bg-opacity-10 text-info border-0">Ver</span> para previsualizar los registros faltantes.</li>
                                    <li>Usa <button class="btn btn-success btn-sm py-0 px-1" style="font-size:0.7rem;pointer-events:none;"><i class="ti ti-transfer-in"></i></button> <strong>Sincronizar</strong> para insertar los registros faltantes en tu BD local.</li>
                                    <li>Al terminar, limpia la BD temporal con <span class="badge bg-danger bg-opacity-10 text-danger border-0">Limpiar</span>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($hasTempDb && !empty($results)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h5 class="card-title mb-0"><i class="ti ti-report-analytics" style="color:var(--nx-primary);"></i> Resultados</h5>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border-0"><?= $totalTables ?> tablas</span>
                                        <span class="badge bg-<?= $totalMissing > 0 ? 'warning' : 'success' ?> bg-opacity-10 text-<?= $totalMissing > 0 ? 'warning' : 'success' ?> border-0">
                                            <?= $totalMissing > 0 ? $totalMissing . ' faltantes' : 'Completo' ?>
                                        </span>
                                        <?php if ($totalMissing > 0): ?>
                                        <form method="POST" class="d-inline" id="syncAllForm">
                                            <input type="hidden" name="action" value="sync_all">
                                            <button type="submit" class="btn btn-success btn-sm" id="syncAllBtn">
                                                <i class="ti ti-transfer-in"></i> Sincronizar todos (<?= $totalMissing ?>)
                                            </button>
                                            <div id="syncAllProgress" class="sync-progress" style="display:none;">
                                                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                                <small>Sincronizando...</small>
                                            </div>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-result table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tabla</th>
                                                <th style="text-align:right;">Registros SQL</th>
                                                <th style="text-align:right;">Registros Local</th>
                                                <th style="text-align:right;">Faltantes</th>
                                                <th>Estado</th>
                                                <th style="text-align:center;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $idx = 0; foreach ($results as $r): $idx++; ?>
                                            <tr class="<?= $r['missing_count'] > 0 ? 'table-warning' : ($r['status'] === 'new_table' ? 'table-info' : '') ?>">
                                                <td class="text-muted"><?= $idx ?></td>
                                                <td><strong><?= htmlspecialchars($r['table']) ?></strong></td>
                                                <td style="text-align:right;"><?= number_format($r['count_sql']) ?></td>
                                                <td style="text-align:right;"><?= $r['exists_local'] ? number_format($r['count_local']) : '<span class="text-muted">N/A</span>' ?></td>
                                                <td style="text-align:right;">
                                                    <?php if ($r['missing_count'] > 0): ?>
                                                    <span class="fw-bold" style="color:var(--nx-danger);"><?= number_format($r['missing_count']) ?></span>
                                                    <?php else: ?>
                                                    <span style="color:var(--nx-success);">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($r['status'] === 'ok'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border-0 badge-status">Completo</span>
                                                    <?php elseif ($r['status'] === 'missing'): ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning border-0 badge-status">Incompleto</span>
                                                    <?php elseif ($r['status'] === 'new_table'): ?>
                                                        <span class="badge bg-info bg-opacity-10 text-info border-0 badge-status">No existe en local</span>
                                                    <?php elseif ($r['status'] === 'empty'): ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 badge-status">Sin datos</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;">
                                                    <?php if ($r['missing_count'] > 0 || $r['status'] === 'new_table'): ?>
                                                    <button class="btn btn-sm btn-outline-info py-0 px-1" onclick="previewTable('<?= htmlspecialchars($r['table']) ?>', this)" title="Ver registros faltantes">
                                                        <i class="ti ti-eye"></i> Ver
                                                    </button>
                                                    <form method="POST" class="d-inline sync-table-form">
                                                        <input type="hidden" name="action" value="sync_table">
                                                        <input type="hidden" name="sync_table" value="<?= htmlspecialchars($r['table']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-1" title="Sincronizar tabla">
                                                            <i class="ti ti-transfer-in"></i>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr class="preview-row" id="preview-<?= htmlspecialchars($r['table']) ?>" style="display:none;">
                                                <td colspan="7" class="p-0">
                                                    <div class="preview-wrap p-3">
                                                        <div class="preview-loading">
                                                            <div class="spinner"></div>
                                                            <p class="text-muted small">Cargando registros...</p>
                                                        </div>
                                                        <div class="preview-content"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="src/assets/js/sidebarmenu.js"></script>
<script>
$('#importForm').on('submit', function() {
    const hasSelect = $('#sqlFile').val() !== '';
    const hasUpload = $('input[name="sql_upload"]')[0].files.length > 0;
    if (!hasSelect && !hasUpload) {
        alert('Selecciona un archivo SQL existente o sube uno nuevo.');
        return false;
    }
    $('#importBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importando...');
    $('#importProgress').show();
});

$('#syncAllForm').on('submit', function() {
    if (!confirm('¿Sincronizar TODAS las tablas con registros faltantes?')) return false;
    $('#syncAllBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sincronizando...');
});

$('.sync-table-form').on('submit', function() {
    const btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
});

let previewOpen = null;

function previewTable(table, btn) {
    const row = $('#preview-' + table);
    const wrap = row.find('.preview-wrap');
    const loading = row.find('.preview-loading');
    const content = row.find('.preview-content');

    if (row.is(':visible')) {
        row.hide();
        wrap.hide();
        $(btn).html('<i class="ti ti-eye"></i> Ver');
        return;
    }

    if (previewOpen && previewOpen !== table) {
        const prevRow = $('#preview-' + previewOpen);
        prevRow.hide();
        prevRow.find('.preview-wrap').hide();
        $('button').filter(function() { return $(this).text().includes('Ver') && $(this).closest('tr').next('tr').attr('id') === 'preview-' + previewOpen; }).html('<i class="ti ti-eye"></i> Ver');
    }
    previewOpen = table;

    row.show();
    wrap.show();
    loading.show();
    content.empty();
    $(btn).html('<i class="ti ti-eye-off"></i> Cerrar');

    $.get('<?= $scriptUrl ?>?action=preview&table=' + encodeURIComponent(table), function(data) {
        loading.hide();
        if (data.error) {
            content.html('<div class="alert alert-danger">' + data.error + '</div>');
            return;
        }
        let html = '';
        if (data.note) {
            html += '<div class="alert alert-warning small py-2 mb-2"><i class="ti ti-info-circle"></i> ' + data.note + '</div>';
        }
        if (data.debug) {
            html += '<div class="small text-muted mb-2" style="font-family:monospace;font-size:0.7rem;">debug: ' + JSON.stringify(data.debug) + '</div>';
        }
        if (!data.rows || data.rows.length === 0) {
            content.html(html + '<div class="alert alert-info">No hay registros para mostrar.</div>');
            return;
        }
        html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr>';
        data.columns.forEach(function(col) {
            html += '<th>' + col + '</th>';
        });
        html += '</tr></thead><tbody>';
        data.rows.forEach(function(row) {
            html += '<tr>';
            data.columns.forEach(function(col) {
                let val = row[col];
                if (val === null) val = '<span class="text-muted">NULL</span>';
                else if (typeof val === 'number') val = val.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 6});
                else val = $('<span>').text(String(val)).html().substring(0, 120);
                html += '<td>' + val + '</td>';
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        if (data.rows.length >= 200) {
            html += '<p class="text-muted small mt-2 mb-0"><i class="ti ti-info-circle"></i> Mostrando primeros 200 registros</p>';
        }
        content.html(html);
    }).fail(function() {
        loading.hide();
        content.html('<div class="alert alert-danger">Error al cargar la previsualización.</div>');
    });
}
</script>
</body>
</html>
