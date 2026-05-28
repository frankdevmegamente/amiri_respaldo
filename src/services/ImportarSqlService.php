<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/logger.php';
require_once __DIR__ . '/../config/auditoria.php';

class ImportarSqlService {

    private $pdo;

    private static $enumFields = [
        'movimientos' => [
            'tipo' => ['entrada', 'salida', 'traspaso', 'comision', 'ajuste'],
            'moneda' => ['MXN', 'USD', 'USDT'],
            'estado' => ['activo', 'anulado', 'corregido'],
        ],
        'cuentas' => [
            'tipo_moneda' => ['MXN', 'USD', 'USDT'],
        ],
        'deudas_clientes' => [
            'moneda' => ['MXN', 'USD', 'USDT'],
            'estado' => ['pendiente', 'pagado', 'parcial'],
        ],
        'deudas_empresa' => [
            'estado' => ['pendiente', 'pagado', 'parcial'],
        ],
        'gastos' => [
            'clasificacion' => ['fijo', 'variable'],
        ],
        'inventario_monedas' => [
            'moneda' => ['USD', 'USDT'],
            'estado' => ['disponible', 'parcial', 'agotado'],
        ],
        'historial_saldos' => [
            'tipo' => ['entrada', 'salida'],
        ],
        'usuarios' => [
            'rol' => ['admin', 'prestamista', 'usuario'],
        ],
        'proveedores' => [
            'tipo_moneda' => ['USD', 'USDT'],
        ],
        'servicios' => [
            'tipo_movimiento' => ['entrada', 'salida'],
        ],
        'prestamos_otorgados' => [
            'estado' => ['pendiente', 'parcial', 'pagado'],
        ],
    ];

    private static $positiveFields = [
        'movimientos' => ['monto'],
        'cuentas' => ['saldo_actual', 'valor_mxn'],
        'gastos' => ['monto'],
        'deudas_clientes' => ['monto_total'],
        'deudas_empresa' => ['monto'],
        'inventario_monedas' => ['cantidad_original', 'cantidad_restante'],
        'pagos_promotores' => ['monto_pagado'],
        'prestamos_otorgados' => ['monto_total'],
        'consumos_inventario' => ['cantidad_consumida', 'paridad_compra', 'paridad_venta'],
    ];

    private static $dateFields = [
        'movimientos' => ['fecha'],
        'prestamos_otorgados' => ['fecha'],
        'deudas_clientes' => ['fecha_vencimiento'],
        'deudas_empresa' => ['fecha'],
        'inventario_monedas' => ['fecha_compra'],
        'gastos' => ['fecha'],
    ];

    private static $fkRelations = [
        'movimientos' => [
            ['column' => 'cuenta_origen_id', 'ref_table' => 'cuentas', 'nullable' => true],
            ['column' => 'cuenta_destino_id', 'ref_table' => 'cuentas', 'nullable' => true],
            ['column' => 'cliente_id', 'ref_table' => 'clientes', 'nullable' => true],
            ['column' => 'promotor_id', 'ref_table' => 'promotores', 'nullable' => true],
            ['column' => 'proveedor_id', 'ref_table' => 'proveedores', 'nullable' => true],
            ['column' => 'prestamista_id', 'ref_table' => 'prestamistas', 'nullable' => true],
            ['column' => 'servicio_id', 'ref_table' => 'servicios', 'nullable' => false],
            ['column' => 'movimiento_original_id', 'ref_table' => 'movimientos', 'nullable' => true],
        ],
        'clientes' => [
            ['column' => 'promotor_id', 'ref_table' => 'promotores', 'nullable' => true],
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => false],
        ],
        'cuentas' => [
            ['column' => 'tipo_cuenta', 'ref_table' => 'tipos_cuenta', 'nullable' => false, 'id_column' => 'id'],
        ],
        'deudas_clientes' => [
            ['column' => 'cliente_id', 'ref_table' => 'clientes', 'nullable' => false],
            ['column' => 'movimiento_venta_id', 'ref_table' => 'movimientos', 'nullable' => true, 'id_column' => 'grupo_id'],
        ],
        'deudas_empresa' => [
            ['column' => 'prestamista_id', 'ref_table' => 'prestamistas', 'nullable' => false],
        ],
        'gastos' => [
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => false],
            ['column' => 'movimiento_id', 'ref_table' => 'movimientos', 'nullable' => true],
            ['column' => 'categoria_id', 'ref_table' => 'categorias_gasto', 'nullable' => false],
        ],
        'historial_saldos' => [
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => false],
            ['column' => 'movimiento_id', 'ref_table' => 'movimientos', 'nullable' => false],
        ],
        'inventario_monedas' => [
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => false],
            ['column' => 'movimiento_id', 'ref_table' => 'movimientos', 'nullable' => true],
        ],
        'consumos_inventario' => [
            ['column' => 'movimiento_id', 'ref_table' => 'movimientos', 'nullable' => false],
            ['column' => 'inventario_id', 'ref_table' => 'inventario_monedas', 'nullable' => false],
        ],
        'pagos_promotores' => [
            ['column' => 'promotor_id', 'ref_table' => 'promotores', 'nullable' => false],
            ['column' => 'cuenta_pago_id', 'ref_table' => 'cuentas', 'nullable' => false],
            ['column' => 'cuenta_promotor_id', 'ref_table' => 'cuentas', 'nullable' => false],
            ['column' => 'movimiento_id', 'ref_table' => 'movimientos', 'nullable' => true],
        ],
        'prestamos_otorgados' => [
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => false],
        ],
        'promotores' => [
            ['column' => 'cuenta_id', 'ref_table' => 'cuentas', 'nullable' => true],
        ],
        'proveedores' => [
            ['column' => 'cuenta_usd_id', 'ref_table' => 'cuentas', 'nullable' => true],
            ['column' => 'cuenta_usdt_id', 'ref_table' => 'cuentas', 'nullable' => true],
        ],
    ];

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? getPDO();
        $this->mysqlCli = $this->findMysqlCli();
    }

    private function findMysqlCli(): string {
        $paths = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysql.exe',
            'C:\\wamp\\bin\\mysql\\mysql8.0\\bin\\mysql.exe',
            'mysql',
        ];
        foreach ($paths as $p) {
            if (file_exists($p) || $p === 'mysql') return $p;
        }
        return 'mysql';
    }

    public function importSql(string $filePath): array {
        $tempDb = 'import_validate_' . date('Ymd_His');
        $start = microtime(true);
        $output = [];
        $exitCode = 0;

        $host = 'localhost';
        $user = 'root';
        $pass = '';

        $createCmd = sprintf(
            '"%s" --host=%s --user=%s --execute=%s 2>&1',
            $this->mysqlCli, $host, $user,
            escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$tempDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")
        );
        exec($createCmd, $output, $exitCode);
        if ($exitCode !== 0) {
            return ['success' => false, 'error' => 'Error al crear BD temporal: ' . implode("\n", $output)];
        }

        $importCmd = sprintf(
            '"%s" --host=%s --user=%s %s < %s 2>&1',
            $this->mysqlCli, $host, $user, $tempDb, escapeshellarg($filePath)
        );
        exec($importCmd, $output, $exitCode);
        if ($exitCode !== 0) {
            $this->cleanup($tempDb);
            $lines = array_slice($output, 0, 10);
            return ['success' => false, 'error' => 'Error al importar SQL: ' . implode("\n", $lines)];
        }

        $elapsed = round(microtime(true) - $start, 1);

        $tempPDO = $this->getTempPDO($tempDb);
        $tables = $tempPDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            $this->cleanup($tempDb);
            return ['success' => false, 'error' => 'El archivo SQL no contiene tablas.'];
        }

        $totalRecords = 0;
        foreach ($tables as $table) {
            $t = $tempPDO->query("SELECT TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . $tempPDO->quote($tempDb) . " AND TABLE_NAME = " . $tempPDO->quote($table))->fetchColumn();
            if ($t === 'BASE TABLE') {
                $totalRecords += (int)$tempPDO->query("SELECT COUNT(*) FROM `{$tempDb}`.`{$table}`")->fetchColumn();
            }
        }

        Logger::info("SQL importado a BD temporal {$tempDb}", [
            'file' => basename($filePath),
            'tables' => count($tables),
            'records' => $totalRecords,
            'time' => $elapsed . 's'
        ]);

        return [
            'success' => true,
            'temp_db' => $tempDb,
            'tables' => $tables,
            'total_records' => $totalRecords,
            'time' => $elapsed,
            'file_size' => filesize($filePath),
        ];
    }

    public function validateAll(string $tempDb, PDO $tempPDO): array {
        $allErrors = [];
        $allTableStats = [];
        $tables = $tempPDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $t = $tempPDO->query("SELECT TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . $tempPDO->quote($tempDb) . " AND TABLE_NAME = " . $tempPDO->quote($table))->fetchColumn();
            if ($t !== 'BASE TABLE') continue;
            $totalCount = (int)$tempPDO->query("SELECT COUNT(*) FROM `{$tempDb}`.`{$table}`")->fetchColumn();
            $allTableStats[$table] = [
                'count_sql' => $totalCount,
                'errors' => [],
                'invalid_ids' => [],
                'valid_count' => $totalCount,
                'invalid_count' => 0,
            ];
        }

        $validators = [
            'enums' => 'validateEnums',
            'positive_amounts' => 'validatePositiveAmounts',
            'intra_fks' => 'validateIntraFks',
            'movement_consistency' => 'validateMovementConsistency',
            'currency_coherence' => 'validateCurrencyCoherence',
            'dates' => 'validateDates',
            'inventory_consistency' => 'validateInventoryConsistency',
        ];

        $validationDetails = [];

        foreach ($validators as $ruleName => $method) {
            $errors = $this->$method($tempDb, $tempPDO);
            $validationDetails[$ruleName] = [
                'total' => count($errors),
                'errors' => $errors,
            ];
            foreach ($errors as $err) {
                $table = $err['table'];
                if (!isset($allTableStats[$table])) continue;
                $allTableStats[$table]['errors'][] = $err;
                $idVal = $err['id_value'];
                if (!in_array($idVal, $allTableStats[$table]['invalid_ids'])) {
                    $allTableStats[$table]['invalid_ids'][] = $idVal;
                }
            }
        }

        foreach ($allTableStats as $table => &$stats) {
            $stats['invalid_count'] = count($stats['invalid_ids']);
            $stats['valid_count'] = $stats['count_sql'] - $stats['invalid_count'];
        }
        unset($stats);

        return [
            'validation_details' => $validationDetails,
            'tables' => $allTableStats,
        ];
    }

    public function compareWithLocal(string $tempDb, PDO $tempPDO, array &$tableStats): array {
        $localDb = $this->pdo->query("SELECT DATABASE()")->fetchColumn();
        $tables = $tempPDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $totalMissing = 0;
        $tablesWithMissing = 0;

        foreach ($tables as $table) {
            $t = $tempPDO->query("SELECT TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . $tempPDO->quote($tempDb) . " AND TABLE_NAME = " . $tempPDO->quote($table))->fetchColumn();
            if ($t !== 'BASE TABLE') continue;

            if (!isset($tableStats[$table])) {
                $totalCount = (int)$tempPDO->query("SELECT COUNT(*) FROM `{$tempDb}`.`{$table}`")->fetchColumn();
                $tableStats[$table] = [
                    'count_sql' => $totalCount,
                    'errors' => [],
                    'invalid_ids' => [],
                    'valid_count' => $totalCount,
                    'invalid_count' => 0,
                ];
            }

            $countSql = $tableStats[$table]['count_sql'];

            $existsLocal = false;
            try {
                $this->pdo->query("SELECT 1 FROM `{$table}` LIMIT 1");
                $existsLocal = true;
            } catch (Exception $e) {
                $existsLocal = false;
            }

            $tableStats[$table]['exists_local'] = $existsLocal;

            if (!$existsLocal) {
                $tableStats[$table]['missing_count'] = $countSql;
                $tableStats[$table]['missing_ids'] = [];
                $tableStats[$table]['missing_valid_ids'] = [];
                $tableStats[$table]['status'] = 'new_table';
                if ($countSql > 0) {
                    $totalMissing += $countSql;
                    $tablesWithMissing++;
                }
                continue;
            }

            $countLocal = (int)$this->pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $tableStats[$table]['count_local'] = $countLocal;

            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table);

            if ($pk) {
                $allIds = $tempPDO->query("SELECT `{$pk}` FROM `{$tempDb}`.`{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                $missingIds = [];
                $chunks = array_chunk($allIds, 500);
                foreach ($chunks as $chunk) {
                    $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                    $stmt = $this->pdo->prepare("SELECT `{$pk}` FROM `{$table}` WHERE `{$pk}` IN ({$placeholders})");
                    $stmt->execute($chunk);
                    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $missingIds = array_merge($missingIds, array_diff($chunk, $existing));
                }
                $missingCount = count($missingIds);
            } else {
                $missingCount = max(0, $countSql - $countLocal);
                $missingIds = [];
            }

            $invalidIds = $tableStats[$table]['invalid_ids'] ?? [];
            $missingValidIds = array_values(array_diff($missingIds, $invalidIds));

            $tableStats[$table]['missing_count'] = $missingCount;
            $tableStats[$table]['missing_ids'] = $missingIds;
            $tableStats[$table]['missing_valid_ids'] = $missingValidIds;
            $tableStats[$table]['status'] = $missingCount > 0 ? 'missing' : 'ok';

            if ($missingCount > 0) {
                $totalMissing += $missingCount;
                $tablesWithMissing++;
            }
        }

        return [
            'total_missing' => $totalMissing,
            'tables_with_missing' => $tablesWithMissing,
        ];
    }

    public function syncAll(string $tempDb, PDO $tempPDO, array &$tableStats): array {
        $totalInserted = 0;
        $newTablesCreated = 0;
        $errors = [];
        $auditRecords = [];

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tableStats as $table => &$stats) {
            if ($stats['missing_count'] === 0) continue;

            $missingValidIds = $stats['missing_valid_ids'] ?? [];
            $missingCount = $stats['missing_count'];

            if (!$stats['exists_local']) {
                try {
                    $createStmt = $tempPDO->query("SHOW CREATE TABLE `{$tempDb}`.`{$table}`")->fetch();
                    $createSQL = $createStmt['Create Table'];
                    $createSQL = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $createSQL);
                    $this->pdo->exec($createSQL);
                    $newTablesCreated++;
                    $stats['exists_local'] = true;
                } catch (Exception $e) {
                    $errors[] = "{$table}: error al crear tabla - " . $e->getMessage();
                    $stats['sync_error'] = $e->getMessage();
                    continue;
                }
            }

            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table);

            if (empty($pk) || empty($missingValidIds)) {
                $commonCols = $this->getCommonColumns($tempPDO, $tempDb, $table, $this->pdo, $table);
                if (!empty($commonCols)) {
                    $colList = '`' . implode('`,`', $commonCols) . '`';
                    try {
                        $this->pdo->exec("INSERT IGNORE INTO `{$table}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$table}`");
                        $inserted = $this->pdo->rowCount();
                        $totalInserted += $inserted;
                        $stats['inserted_count'] = ($stats['inserted_count'] ?? 0) + $inserted;
                        $stats['status'] = 'synced';
                        $this->logAuditSync($table, $inserted);
                    } catch (Exception $e) {
                        $errors[] = "{$table}: " . $e->getMessage();
                        $stats['sync_error'] = $e->getMessage();
                    }
                } else {
                    $errors[] = "{$table}: no hay columnas comunes";
                }
                continue;
            }

            $commonCols = $this->getCommonColumns($tempPDO, $tempDb, $table, $this->pdo, $table);
            if (empty($commonCols)) {
                $errors[] = "{$table}: no hay columnas comunes";
                continue;
            }
            $colList = '`' . implode('`,`', $commonCols) . '`';

            $insertedInTable = 0;
            $chunks = array_chunk($missingValidIds, 100);
            foreach ($chunks as $chunk) {
                $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                try {
                    $stmt = $this->pdo->prepare("INSERT IGNORE INTO `{$table}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$table}` WHERE `{$pk}` IN ({$placeholders})");
                    $stmt->execute($chunk);
                    $insertedInTable += count($chunk);
                } catch (Exception $e) {
                    $errors[] = "{$table} (lote): " . $e->getMessage();
                }
            }

            $totalInserted += $insertedInTable;
            $stats['inserted_count'] = ($stats['inserted_count'] ?? 0) + $insertedInTable;
            $stats['status'] = 'synced';
            $stats['missing_count'] = 0;
            $stats['missing_ids'] = [];

            $this->logAuditSync($table, $insertedInTable);

            if ($pk) {
                $allIds = $tempPDO->query("SELECT `{$pk}` FROM `{$tempDb}`.`{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                $existingIds = [];
                $chunks = array_chunk($allIds, 500);
                foreach ($chunks as $chunk) {
                    $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                    $stmt = $this->pdo->prepare("SELECT `{$pk}` FROM `{$table}` WHERE `{$pk}` IN ({$placeholders})");
                    $stmt->execute($chunk);
                    $existingIds = array_merge($existingIds, $stmt->fetchAll(PDO::FETCH_COLUMN));
                }
                $newMissing = array_diff($allIds, $existingIds);
                if (!empty($newMissing)) {
                    $errors[] = "{$table}: {$missingCount} IDs no se pudieron insertar (posiblemente duplicados existentes)";
                }
            }
        }
        unset($stats);

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        Logger::info("Sincronización completada", [
            'inserted' => $totalInserted,
            'new_tables' => $newTablesCreated,
            'errors' => count($errors),
        ]);

        return [
            'total_inserted' => $totalInserted,
            'new_tables_created' => $newTablesCreated,
            'errors' => $errors,
        ];
    }

    public function syncTable(string $tempDb, PDO $tempPDO, string $table, array &$tableStat): array {
        $inserted = 0;
        $errors = [];

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        if (!$tableStat['exists_local']) {
            try {
                $createStmt = $tempPDO->query("SHOW CREATE TABLE `{$tempDb}`.`{$table}`")->fetch();
                $createSQL = $createStmt['Create Table'];
                $createSQL = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $createSQL);
                $this->pdo->exec($createSQL);
                $tableStat['exists_local'] = true;
            } catch (Exception $e) {
                $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                return ['inserted' => 0, 'errors' => ["Error al crear tabla {$table}: " . $e->getMessage()]];
            }
        }

        $missingValidIds = $tableStat['missing_valid_ids'] ?? [];
        $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table);

        if (empty($pk) || empty($missingValidIds)) {
            $commonCols = $this->getCommonColumns($tempPDO, $tempDb, $table, $this->pdo, $table);
            if (!empty($commonCols)) {
                $colList = '`' . implode('`,`', $commonCols) . '`';
                try {
                    $this->pdo->exec("INSERT IGNORE INTO `{$table}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$table}`");
                    $inserted = $this->pdo->rowCount();
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        } else {
            $commonCols = $this->getCommonColumns($tempPDO, $tempDb, $table, $this->pdo, $table);
            if (!empty($commonCols)) {
                $colList = '`' . implode('`,`', $commonCols) . '`';
                $chunks = array_chunk($missingValidIds, 100);
                foreach ($chunks as $chunk) {
                    $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
                    try {
                        $stmt = $this->pdo->prepare("INSERT IGNORE INTO `{$table}` ({$colList}) SELECT {$colList} FROM `{$tempDb}`.`{$table}` WHERE `{$pk}` IN ({$placeholders})");
                        $stmt->execute($chunk);
                        $inserted += count($chunk);
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        if ($inserted > 0) {
            $this->logAuditSync($table, $inserted);
        }

        $tableStat['inserted_count'] = ($tableStat['inserted_count'] ?? 0) + $inserted;
        $tableStat['status'] = 'synced';
        $tableStat['missing_count'] = 0;

        Logger::info("Tabla sincronizada: {$table}", ['inserted' => $inserted]);

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    public function cleanup(string $tempDb): void {
        if (empty($tempDb)) return;
        $cmd = sprintf(
            '"%s" --user=root --execute=%s 2>&1',
            $this->mysqlCli,
            escapeshellarg("DROP DATABASE IF EXISTS `{$tempDb}`")
        );
        exec($cmd);
        Logger::info("BD temporal eliminada: {$tempDb}");
    }

    private function validateEnums(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        foreach (self::$enumFields as $table => $fields) {
            $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, $table);
            if (!$tableExists) continue;
            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table) ?: 'id';

            foreach ($fields as $column => $validValues) {
                $colExists = $this->columnExistsInTemp($tempPDO, $tempDb, $table, $column);
                if (!$colExists) continue;

                $placeholders = implode(',', array_fill(0, count($validValues), '?'));
                $sql = "SELECT `{$pk}` as id_val, `{$column}` as val FROM `{$tempDb}`.`{$table}` WHERE `{$column}` IS NOT NULL AND `{$column}` NOT IN ({$placeholders}) LIMIT 200";
                $stmt = $tempPDO->prepare($sql);
                $stmt->execute($validValues);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $errors[] = [
                        'table' => $table,
                        'id_field' => $pk,
                        'id_value' => $row['id_val'],
                        'field' => $column,
                        'value' => $row['val'],
                        'expected' => implode(', ', $validValues),
                        'rule' => 'enum',
                        'message' => "Valor '{$row['val']}' no válido para {$column}. Permitidos: " . implode(', ', $validValues),
                    ];
                }
            }
        }
        return $errors;
    }

    private function validatePositiveAmounts(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        foreach (self::$positiveFields as $table => $fields) {
            $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, $table);
            if (!$tableExists) continue;
            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table) ?: 'id';

            foreach ($fields as $column) {
                $colExists = $this->columnExistsInTemp($tempPDO, $tempDb, $table, $column);
                if (!$colExists) continue;

                $comparator = (in_array($column, ['saldo_actual', 'valor_mxn', 'cantidad_restante'])) ? '< 0' : '<= 0';
                $extraCondition = '';
                if ($table === 'movimientos') {
                    $extraCondition = " AND NOT (tipo = 'ajuste' AND es_reverso = 1)";
                }

                $sql = "SELECT `{$pk}` as id_val, `{$column}` as val FROM `{$tempDb}`.`{$table}` WHERE (`{$column}` IS NULL OR `{$column}` {$comparator}){$extraCondition} LIMIT 200";
                $stmt = $tempPDO->query($sql);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $val = $row['val'];
                    if ($val === null) {
                        $msg = "{$column} es NULL (debe ser > 0)";
                    } else {
                        $msg = "{$column} = {$val} (debe ser > 0)";
                    }
                    $errors[] = [
                        'table' => $table,
                        'id_field' => $pk,
                        'id_value' => $row['id_val'],
                        'field' => $column,
                        'value' => $val ?? 'NULL',
                        'rule' => 'positive_amount',
                        'message' => $msg,
                    ];
                }
            }
        }
        return $errors;
    }

    private function validateIntraFks(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        $localRefs = [];

        foreach (self::$fkRelations as $table => $relations) {
            $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, $table);
            if (!$tableExists) continue;
            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table) ?: 'id';

            foreach ($relations as $rel) {
                $column = $rel['column'];
                $refTable = $rel['ref_table'];
                $nullable = $rel['nullable'] ?? false;
                $idColumn = $rel['id_column'] ?? 'id';

                $colExists = $this->columnExistsInTemp($tempPDO, $tempDb, $table, $column);
                if (!$colExists) continue;

                $refExists = $this->tableExistsInTemp($tempPDO, $tempDb, $refTable);

                $nullClause = $nullable ? "" : " AND `{$column}` IS NOT NULL";

                $sql = "SELECT t.`{$pk}` as id_val, t.`{$column}` as val
                        FROM `{$tempDb}`.`{$table}` t
                        LEFT JOIN `{$tempDb}`.`{$refTable}` r ON t.`{$column}` = r.`{$idColumn}`";

                if ($refExists) {
                    $sql .= " WHERE r.`{$idColumn}` IS NULL{$nullClause} LIMIT 200";
                } else {
                    $sql .= " WHERE 1=1{$nullClause} LIMIT 200";
                }

                $stmt = $tempPDO->query($sql);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    if ($row['val'] === null) {
                        if (!$nullable) {
                            $errors[] = [
                                'table' => $table,
                                'id_field' => $pk,
                                'id_value' => $row['id_val'],
                                'field' => $column,
                                'value' => 'NULL',
                                'rule' => 'intra_fk',
                                'message' => "{$column} es NULL pero es requerido",
                            ];
                        }
                        continue;
                    }
                    if (!$refExists) {
                        $errors[] = [
                            'table' => $table,
                            'id_field' => $pk,
                            'id_value' => $row['id_val'],
                            'field' => $column,
                            'value' => $row['val'],
                            'rule' => 'intra_fk',
                            'message' => "{$column}={$row['val']} pero la tabla de referencia {$refTable} no existe en el SQL",
                        ];
                    } else {
                        $errors[] = [
                            'table' => $table,
                            'id_field' => $pk,
                            'id_value' => $row['id_val'],
                            'field' => $column,
                            'value' => $row['val'],
                            'rule' => 'intra_fk',
                            'message' => "{$column}={$row['val']} no existe en {$refTable}",
                        ];
                    }
                }
            }
        }
        return $errors;
    }

    private function validateMovementConsistency(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, 'movimientos');
        if (!$tableExists) return $errors;

        $pk = $this->getPrimaryKey($tempPDO, $tempDb, 'movimientos') ?: 'id';

        $checks = [
            [
                'name' => 'entrada_sin_destino',
                'sql' => "SELECT `{$pk}` as id_val, tipo, cuenta_origen_id, cuenta_destino_id FROM `{$tempDb}`.`movimientos` WHERE tipo = 'entrada' AND cuenta_destino_id IS NULL AND NOT (es_reverso = 1) LIMIT 200",
                'msg' => "Movimiento tipo 'entrada' sin cuenta_destino_id",
            ],
            [
                'name' => 'salida_sin_origen',
                'sql' => "SELECT `{$pk}` as id_val, tipo, cuenta_origen_id, cuenta_destino_id, proveedor_id FROM `{$tempDb}`.`movimientos` WHERE tipo = 'salida' AND cuenta_origen_id IS NULL AND (proveedor_id IS NULL OR cuenta_destino_id IS NULL) AND NOT (es_reverso = 1) LIMIT 200",
                'msg' => "Movimiento tipo 'salida' sin cuenta_origen_id ni proveedor con destino",
            ],
            [
                'name' => 'traspaso_sin_cuentas',
                'sql' => "SELECT `{$pk}` as id_val, tipo, cuenta_origen_id, cuenta_destino_id FROM `{$tempDb}`.`movimientos` WHERE tipo = 'traspaso' AND (cuenta_origen_id IS NULL OR cuenta_destino_id IS NULL) AND NOT (es_reverso = 1) LIMIT 200",
                'msg' => "Movimiento tipo 'traspaso' requiere cuenta_origen_id y cuenta_destino_id",
            ],
        ];

        foreach ($checks as $check) {
            $stmt = $tempPDO->query($check['sql']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $errors[] = [
                    'table' => 'movimientos',
                    'id_field' => $pk,
                    'id_value' => $row['id_val'],
                    'field' => 'tipo',
                    'value' => $row['tipo'],
                    'rule' => 'movement_consistency',
                    'message' => $check['msg'] . " (ID: {$row['id_val']})",
                ];
            }
        }

        return $errors;
    }

    private function validateCurrencyCoherence(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, 'movimientos');
        if (!$tableExists) return $errors;

        $pk = $this->getPrimaryKey($tempPDO, $tempDb, 'movimientos') ?: 'id';

        $sql = "SELECT m.`{$pk}` as id_val, m.moneda, m.cuenta_origen_id, m.cuenta_destino_id,
                       co.tipo_moneda as moneda_origen, cd.tipo_moneda as moneda_destino,
                       co.tipo_cuenta as tipo_cuenta_origen, cd.tipo_cuenta as tipo_cuenta_destino
                FROM `{$tempDb}`.`movimientos` m
                LEFT JOIN `{$tempDb}`.`cuentas` co ON m.cuenta_origen_id = co.id
                LEFT JOIN `{$tempDb}`.`cuentas` cd ON m.cuenta_destino_id = cd.id
                WHERE (m.cuenta_origen_id IS NOT NULL AND co.id IS NOT NULL AND co.tipo_cuenta NOT IN (4,5) AND m.moneda != co.tipo_moneda)
                   OR (m.cuenta_destino_id IS NOT NULL AND cd.id IS NOT NULL AND cd.tipo_cuenta NOT IN (4,5) AND m.moneda != cd.tipo_moneda)
                LIMIT 200";

        $stmt = $tempPDO->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $detail = [];
            if ($row['cuenta_origen_id'] && $row['moneda_origen'] && $row['moneda'] !== $row['moneda_origen']) {
                $detail[] = "origen #{$row['cuenta_origen_id']} espera {$row['moneda_origen']}";
            }
            if ($row['cuenta_destino_id'] && $row['moneda_destino'] && $row['moneda'] !== $row['moneda_destino']) {
                $detail[] = "destino #{$row['cuenta_destino_id']} espera {$row['moneda_destino']}";
            }
            $errors[] = [
                'table' => 'movimientos',
                'id_field' => $pk,
                'id_value' => $row['id_val'],
                'field' => 'moneda',
                'value' => $row['moneda'],
                'rule' => 'currency_coherence',
                'message' => "Moneda '{$row['moneda']}' no coincide con cuenta: " . implode('; ', $detail),
            ];
        }

        return $errors;
    }

    private function validateDates(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        foreach (self::$dateFields as $table => $fields) {
            $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, $table);
            if (!$tableExists) continue;
            $pk = $this->getPrimaryKey($tempPDO, $tempDb, $table) ?: 'id';

            foreach ($fields as $column) {
                $colExists = $this->columnExistsInTemp($tempPDO, $tempDb, $table, $column);
                if (!$colExists) continue;

                $sql = "SELECT `{$pk}` as id_val, `{$column}` as val
                        FROM `{$tempDb}`.`{$table}`
                        WHERE `{$column}` IS NOT NULL
                          AND CAST(`{$column}` AS DATE) NOT BETWEEN '2000-01-01' AND CURDATE() + INTERVAL 1 DAY
                        LIMIT 200";
                $stmt = $tempPDO->query($sql);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $errors[] = [
                        'table' => $table,
                        'id_field' => $pk,
                        'id_value' => $row['id_val'],
                        'field' => $column,
                        'value' => $row['val'],
                        'rule' => 'date',
                        'message' => "Fecha '{$row['val']}' fuera de rango válido (2000-01-01 hasta hoy+1 día)",
                    ];
                }
            }
        }
        return $errors;
    }

    private function validateInventoryConsistency(string $tempDb, PDO $tempPDO): array {
        $errors = [];
        $tableExists = $this->tableExistsInTemp($tempPDO, $tempDb, 'inventario_monedas');
        if (!$tableExists) return $errors;

        $pk = $this->getPrimaryKey($tempPDO, $tempDb, 'inventario_monedas') ?: 'id';

        $sql = "SELECT `{$pk}` as id_val, cantidad_original, cantidad_restante
                FROM `{$tempDb}`.`inventario_monedas`
                WHERE cantidad_restante > cantidad_original
                LIMIT 200";
        $stmt = $tempPDO->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $errors[] = [
                'table' => 'inventario_monedas',
                'id_field' => $pk,
                'id_value' => $row['id_val'],
                'field' => 'cantidad_restante',
                'value' => $row['cantidad_restante'],
                'rule' => 'inventory_consistency',
                'message' => "cantidad_restante ({$row['cantidad_restante']}) > cantidad_original ({$row['cantidad_original']})",
            ];
        }

        return $errors;
    }

    private function getTempPDO(string $tempDb): PDO {
        return new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private function tableExistsInTemp(PDO $tempPDO, string $tempDb, string $table): bool {
        $stmt = $tempPDO->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $stmt->execute([$tempDb, $table]);
        return $stmt->fetchColumn() > 0;
    }

    private function columnExistsInTemp(PDO $tempPDO, string $tempDb, string $table, string $column): bool {
        $stmt = $tempPDO->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$tempDb, $table, $column]);
        return $stmt->fetchColumn() > 0;
    }

    private function getPrimaryKey(PDO $pdo, string $db, string $table): ?string {
        $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_KEY = 'PRI' LIMIT 1");
        $stmt->execute([$db, $table]);
        $col = $stmt->fetchColumn();
        return $col ?: null;
    }

    private function getCommonColumns(PDO $pdo1, string $db1, string $table1, PDO $pdo2, string $table2): array {
        $q1 = $pdo1->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = " . $pdo1->quote($db1) . " AND TABLE_NAME = " . $pdo1->quote($table1));
        $q2 = $pdo2->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = " . $pdo2->quote($this->pdo->query("SELECT DATABASE()")->fetchColumn()) . " AND TABLE_NAME = " . $pdo2->quote($table2));
        $cols1 = $q1->fetchAll(PDO::FETCH_COLUMN);
        $cols2 = $q2->fetchAll(PDO::FETCH_COLUMN);
        return array_intersect($cols1, $cols2);
    }

    private function logAuditSync(string $table, int $count): void {
        try {
            registrarAuditoria($this->pdo, [
                'accion' => 'IMPORT_SQL',
                'modulo' => 'importacion',
                'tabla' => $table,
                'descripcion' => "Importación SQL: {$count} registros insertados en {$table}",
                'datos_nuevos' => ['registros_insertados' => $count, 'tabla' => $table],
            ]);
        } catch (Exception $e) {
            error_log("Error al registrar auditoría de importación: " . $e->getMessage());
        }
    }
}
