<?php

/**

 * Script de Backup Automático de Base de Datos

 * Genera backups diarios con rotación automática

 * 

 * Uso: php scripts/backup.php

 * Cronjob diario: 0 2 * * * php /path/to/scripts/backup.php

 */



// Cargar configuración

if (file_exists(__DIR__ . '/../.env')) {

    require_once __DIR__ . '/../src/config/dotenv.php';

    $dotenv = new DotEnv(__DIR__ . '/../.env');

    $dotenv->load();

}

require_once __DIR__ . '/../src/config/env.php';



// Configuración de backup

$backupDir = __DIR__ . '/../backups';

$maxBackups = defined('BACKUP_RETENTION_DAYS') ? BACKUP_RETENTION_DAYS : 7; // Mantener últimos 7 días

$timestamp = date('Y-m-d_H-i-s');

$backupFile = "$backupDir/backup_$timestamp.sql";

$logFile = __DIR__ . '/../logs/backup.log';



// Credenciales de base de datos

$host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');

$dbname = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'guiaspar_amiri_db');

$user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'root');

$password = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : '');



/**

 * Escribir en log

 */

function logBackup($message, $level = 'INFO') {

    global $logFile;

    $timestamp = date('Y-m-d H:i:s');

    $line = "[$timestamp] [$level] $message" . PHP_EOL;

    file_put_contents($logFile, $line, FILE_APPEND);

    echo $line;

}



/**

 * Generar backup de MySQL usando PHP (alternativa a mysqldump)

 */

function generarBackupPHP() {

    global $host, $dbname, $user, $password, $backupFile;

    

    logBackup("Iniciando backup de base de datos usando PHP: $dbname");

    

    try {

        require_once __DIR__ . '/../src/config/db.php';

        $pdo = getPDO();

        

        // Obtener todas las tablas

        $stmt = $pdo->query("SHOW TABLES");

        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        

        $sql = "-- Backup de base de datos: $dbname\n";

        $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        

        foreach ($tablas as $tabla) {

            logBackup("  Procesando tabla: $tabla");

            

            // DROP TABLE

            $sql .= "-- Tabla: $tabla\n";

            $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";

            

            // CREATE TABLE

            $createStmt = $pdo->query("SHOW CREATE TABLE `$tabla`");

            $create = $createStmt->fetch(PDO::FETCH_ASSOC);

            $sql .= $create['Create Table'] . ";\n\n";

            

            // INSERT datos

            $dataStmt = $pdo->query("SELECT * FROM `$tabla`");

            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            

            if (count($rows) > 0) {

                foreach ($rows as $row) {

                    $values = array_map(function($val) use ($pdo) {

                        return $val === null ? 'NULL' : $pdo->quote($val);

                    }, $row);

                    

                    $sql .= "INSERT INTO `$tabla` VALUES (" . implode(', ', $values) . ");\n";

                }

                $sql .= "\n";

            }

        }

        

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        

        // Escribir archivo

        file_put_contents($backupFile, $sql);

        

        $size = filesize($backupFile);

        $sizeMB = round($size / 1024 / 1024, 2);

        

        logBackup("Backup generado exitosamente: $backupFile ($sizeMB MB)");

        

        // Comprimir backup

        comprimirBackup($backupFile);

        

        return true;

        

    } catch (Exception $e) {

        logBackup("ERROR al generar backup PHP: " . $e->getMessage(), 'ERROR');

        return false;

    }

}



/**

 * Generar backup de MySQL usando mysqldump (método original)

 */

function generarBackup() {

    global $host, $dbname, $user, $password, $backupFile;

    

    logBackup("Intentando backup con mysqldump...");

    

    // Comando mysqldump (Windows - Laragon)

    $mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';

    

    // Si no existe en esa ruta, buscar en PATH

    if (!file_exists($mysqldumpPath)) {

        $mysqldumpPath = 'mysqldump';

    }

    

    // Construir comando

    $command = sprintf(

        '"%s" --host=%s --user=%s %s --single-transaction --routines --triggers %s > "%s" 2>&1',

        $mysqldumpPath,

        $host,

        $user,

        empty($password) ? '' : '--password=' . $password,

        $dbname,

        $backupFile

    );

    

    // Ejecutar comando

    exec($command, $output, $returnCode);

    

    if ($returnCode !== 0) {

        logBackup("mysqldump falló, usando método PHP alternativo", 'WARNING');

        return generarBackupPHP();

    }

    

    // Verificar que el archivo se creó

    if (!file_exists($backupFile) || filesize($backupFile) === 0) {

        logBackup("Archivo de mysqldump vacío, usando método PHP alternativo", 'WARNING');

        return generarBackupPHP();

    }

    

    $size = filesize($backupFile);

    $sizeMB = round($size / 1024 / 1024, 2);

    

    logBackup("Backup con mysqldump exitoso: $backupFile ($sizeMB MB)");

    

    // Comprimir backup

    comprimirBackup($backupFile);

    

    return true;

}



/**

 * Comprimir archivo SQL a .gz

 */

function comprimirBackup($file) {

    if (!extension_loaded('zlib')) {

        logBackup("Extensión zlib no disponible, no se comprimirá el backup", 'WARNING');

        return false;

    }

    

    $gzFile = $file . '.gz';

    

    $fp = fopen($file, 'rb');

    $gzfp = gzopen($gzFile, 'wb9');

    

    if (!$fp || !$gzfp) {

        logBackup("ERROR al comprimir backup", 'ERROR');

        return false;

    }

    

    while (!feof($fp)) {

        gzwrite($gzfp, fread($fp, 1024 * 512));

    }

    

    fclose($fp);

    gzclose($gzfp);

    

    // Eliminar archivo SQL original

    unlink($file);

    

    $sizeOriginal = filesize($gzFile);

    $sizeMB = round($sizeOriginal / 1024 / 1024, 2);

    

    logBackup("Backup comprimido: $gzFile ($sizeMB MB)");

    

    return true;

}



/**

 * Eliminar backups antiguos

 */

function limpiarBackupsAntiguos() {

    global $backupDir, $maxBackups;

    

    logBackup("Limpiando backups antiguos (mantener últimos $maxBackups)");

    

    // Obtener lista de backups ordenados por fecha

    $backups = glob("$backupDir/backup_*.sql*");

    

    if (count($backups) === 0) {

        logBackup("No hay backups para limpiar");

        return;

    }

    

    // Ordenar por fecha de modificación (más recientes primero)

    usort($backups, function($a, $b) {

        return filemtime($b) - filemtime($a);

    });

    

    // Eliminar backups que excedan el límite

    $eliminados = 0;

    for ($i = $maxBackups; $i < count($backups); $i++) {

        if (unlink($backups[$i])) {

            $eliminados++;

            logBackup("Eliminado: " . basename($backups[$i]));

        }

    }

    

    if ($eliminados > 0) {

        logBackup("Se eliminaron $eliminados backup(s) antiguo(s)");

    } else {

        logBackup("No hay backups antiguos que eliminar");

    }

}



/**

 * Verificar espacio en disco

 */

function verificarEspacio() {

    global $backupDir;

    

    $espacioLibre = disk_free_space($backupDir);

    $espacioTotal = disk_total_space($backupDir);

    $espacioLibreGB = round($espacioLibre / 1024 / 1024 / 1024, 2);

    $espacioTotalGB = round($espacioTotal / 1024 / 1024 / 1024, 2);

    $porcentajeLibre = round(($espacioLibre / $espacioTotal) * 100, 2);

    

    logBackup("Espacio en disco: $espacioLibreGB GB libres de $espacioTotalGB GB ($porcentajeLibre%)");

    

    if ($porcentajeLibre < 10) {

        logBackup("ADVERTENCIA: Espacio en disco bajo (<10%)", 'WARNING');

    }

    

    return $porcentajeLibre > 5; // Retornar false si queda menos del 5%

}



/**

 * Ejecución principal

 */

try {

    logBackup("========== INICIO DE BACKUP ==========");

    

    // Verificar espacio en disco

    if (!verificarEspacio()) {

        logBackup("ERROR: Espacio insuficiente en disco, abortando backup", 'ERROR');

        exit(1);

    }

    

    // Generar backup

    if (!generarBackup()) {

        logBackup("ERROR: Falló la generación del backup", 'ERROR');

        exit(1);

    }

    

    // Limpiar backups antiguos

    limpiarBackupsAntiguos();

    

    logBackup("========== BACKUP COMPLETADO ==========");

    

    exit(0);

    

} catch (Exception $e) {

    logBackup("EXCEPCIÓN: " . $e->getMessage(), 'ERROR');

    logBackup("Stack trace: " . $e->getTraceAsString(), 'ERROR');

    exit(1);

}

