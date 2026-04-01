<?php

/**

 * Script de Limpieza de Logs y Backups

 * Elimina archivos antiguos según configuración de retención

 * 

 * Uso: php scripts/cleanup.php

 * Cronjob semanal: 0 3 * * 0 php /path/to/scripts/cleanup.php

 */



// Configuración

$logsDir = __DIR__ . '/../logs';

$backupsDir = __DIR__ . '/../backups';

$logFile = "$logsDir/cleanup.log";



// Días de retención (configurable)

$retencionLogs = 30;      // Mantener logs de últimos 30 días

$retencionBackups = 7;    // Mantener backups de últimos 7 días



/**

 * Escribir en log

 */

function logCleanup($message, $level = 'INFO') {

    global $logFile;

    $timestamp = date('Y-m-d H:i:s');

    $line = "[$timestamp] [$level] $message" . PHP_EOL;

    file_put_contents($logFile, $line, FILE_APPEND);

    echo $line;

}



/**

 * Limpiar archivos antiguos de un directorio

 */

function limpiarDirectorio($dir, $pattern, $diasRetencion, $tipo) {

    logCleanup("Limpiando $tipo de $dir (retención: $diasRetencion días)");

    

    $archivos = glob("$dir/$pattern");

    $ahora = time();

    $segundosRetencion = $diasRetencion * 24 * 60 * 60;

    $eliminados = 0;

    $espacioLiberado = 0;

    

    foreach ($archivos as $archivo) {

        $edad = $ahora - filemtime($archivo);

        

        if ($edad > $segundosRetencion) {

            $tamano = filesize($archivo);

            if (unlink($archivo)) {

                $eliminados++;

                $espacioLiberado += $tamano;

                $diasArchivo = round($edad / (24 * 60 * 60));

                logCleanup("  Eliminado: " . basename($archivo) . " ($diasArchivo días de antigüedad)");

            }

        }

    }

    

    if ($eliminados > 0) {

        $espacioMB = round($espacioLiberado / 1024 / 1024, 2);

        logCleanup("  ✓ Eliminados $eliminados archivo(s), liberados $espacioMB MB");

    } else {

        logCleanup("  ✓ No hay archivos antiguos que eliminar");

    }

    

    return $eliminados;

}



/**

 * Obtener estadísticas de directorio

 */

function obtenerEstadisticas($dir, $pattern) {

    $archivos = glob("$dir/$pattern");

    $cantidad = count($archivos);

    $espacioTotal = 0;

    

    foreach ($archivos as $archivo) {

        $espacioTotal += filesize($archivo);

    }

    

    $espacioMB = round($espacioTotal / 1024 / 1024, 2);

    

    return [

        'cantidad' => $cantidad,

        'espacio' => $espacioTotal,

        'espacioMB' => $espacioMB

    ];

}



/**

 * Limpiar logs rotados antiguos

 */

function limpiarLogsRotados() {

    global $logsDir, $retencionLogs;

    

    $patterns = [

        'error.*.log',

        'warning.*.log',

        'info.*.log',

        'debug.*.log',

        'audit.*.log'

    ];

    

    $totalEliminados = 0;

    

    foreach ($patterns as $pattern) {

        $eliminados = limpiarDirectorio($logsDir, $pattern, $retencionLogs, 'logs rotados');

        $totalEliminados += $eliminados;

    }

    

    return $totalEliminados;

}



/**

 * Limpiar backups antiguos

 */

function limpiarBackupsAntiguos() {

    global $backupsDir, $retencionBackups;

    

    $patterns = [

        'backup_*.sql',

        'backup_*.sql.gz'

    ];

    

    $totalEliminados = 0;

    

    foreach ($patterns as $pattern) {

        $eliminados = limpiarDirectorio($backupsDir, $pattern, $retencionBackups, 'backups');

        $totalEliminados += $eliminados;

    }

    

    return $totalEliminados;

}



/**

 * Mostrar resumen

 */

function mostrarResumen() {

    global $logsDir, $backupsDir;

    

    logCleanup("========== RESUMEN ==========");

    

    // Estadísticas de logs

    $statsLogs = obtenerEstadisticas($logsDir, '*.log*');

    logCleanup("Logs actuales: {$statsLogs['cantidad']} archivos ({$statsLogs['espacioMB']} MB)");

    

    // Estadísticas de backups

    $statsBackups = obtenerEstadisticas($backupsDir, 'backup_*');

    logCleanup("Backups actuales: {$statsBackups['cantidad']} archivos ({$statsBackups['espacioMB']} MB)");

    

    // Espacio en disco

    $espacioLibre = disk_free_space($logsDir);

    $espacioTotal = disk_total_space($logsDir);

    $espacioLibreGB = round($espacioLibre / 1024 / 1024 / 1024, 2);

    $porcentajeLibre = round(($espacioLibre / $espacioTotal) * 100, 2);

    

    logCleanup("Espacio libre en disco: $espacioLibreGB GB ($porcentajeLibre%)");

}



/**

 * Ejecución principal

 */

try {

    logCleanup("========== INICIO DE LIMPIEZA ==========");

    

    // Limpiar logs rotados

    $logsEliminados = limpiarLogsRotados();

    

    // Limpiar backups

    $backupsEliminados = limpiarBackupsAntiguos();

    

    // Mostrar resumen

    mostrarResumen();

    

    logCleanup("========== LIMPIEZA COMPLETADA ==========");

    logCleanup("Total eliminado: $logsEliminados logs, $backupsEliminados backups");

    

    exit(0);

    

} catch (Exception $e) {

    logCleanup("EXCEPCIÓN: " . $e->getMessage(), 'ERROR');

    logCleanup("Stack trace: " . $e->getTraceAsString(), 'ERROR');

    exit(1);

}

