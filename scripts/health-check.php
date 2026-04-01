<?php

/**

 * Health Check del Sistema

 * Verifica estado de componentes críticos

 * 

 * Uso: php scripts/health-check.php

 */



// Cargar configuración

if (file_exists(__DIR__ . '/../.env')) {

    require_once __DIR__ . '/../src/config/dotenv.php';

    $dotenv = new DotEnv(__DIR__ . '/../.env');

    $dotenv->load();

}

require_once __DIR__ . '/../src/config/env.php';



// Colores para terminal

$colors = [

    'green' => "\033[32m",

    'red' => "\033[31m",

    'yellow' => "\033[33m",

    'blue' => "\033[34m",

    'reset' => "\033[0m"

];



function printStatus($check, $status, $message = '') {

    global $colors;

    

    $statusIcon = $status ? '✓' : '✗';

    $color = $status ? $colors['green'] : $colors['red'];

    $reset = $colors['reset'];

    

    echo sprintf("  %s%s%s %s", $color, $statusIcon, $reset, $check);

    if ($message) {

        echo " - $message";

    }

    echo PHP_EOL;

    

    return $status;

}



function printHeader($title) {

    global $colors;

    echo PHP_EOL;

    echo $colors['blue'] . "═══════════════════════════════════════════════════" . $colors['reset'] . PHP_EOL;

    echo $colors['blue'] . " $title" . $colors['reset'] . PHP_EOL;

    echo $colors['blue'] . "═══════════════════════════════════════════════════" . $colors['reset'] . PHP_EOL;

}



$allChecks = true;



// ===== 1. VERIFICAR ARCHIVOS CRÍTICOS =====

printHeader("1. ARCHIVOS CRÍTICOS");



$archivos = [

    'src/config/session.php' => 'Configuración de sesiones',

    'src/config/db.php' => 'Configuración de base de datos',

    'src/config/logger.php' => 'Sistema de logging',

    'src/config/validator.php' => 'Sistema de validación',

    'src/config/cache.php' => 'Sistema de caché',

    'src/config/rate-limiter.php' => 'Rate limiter',

    'src/config/api-middleware.php' => 'Middleware de APIs',

    '.htaccess' => 'Configuración de Apache',

    '.env.example' => 'Template de variables de entorno'

];



foreach ($archivos as $archivo => $descripcion) {

    $path = __DIR__ . '/../' . $archivo;

    $existe = file_exists($path);

    $allChecks = printStatus($descripcion, $existe, $existe ? "OK" : "FALTA: $archivo") && $allChecks;

}



// ===== 2. VERIFICAR DIRECTORIOS =====

printHeader("2. DIRECTORIOS NECESARIOS");



$directorios = [

    'logs' => 'Directorio de logs',

    'backups' => 'Directorio de backups',

    'uploads' => 'Directorio de uploads',

    'scripts' => 'Scripts de mantenimiento'

];



foreach ($directorios as $dir => $descripcion) {

    $path = __DIR__ . '/../' . $dir;

    $existe = is_dir($path);

    $escribible = $existe && is_writable($path);

    

    $status = $existe && $escribible;

    $mensaje = $existe ? ($escribible ? "OK (escribible)" : "NO ESCRIBIBLE") : "NO EXISTE";

    

    $allChecks = printStatus($descripcion, $status, $mensaje) && $allChecks;

}



// ===== 3. VERIFICAR CONEXIÓN A BASE DE DATOS =====

printHeader("3. BASE DE DATOS");



try {

    require_once __DIR__ . '/../src/config/db.php';

    $pdo = getPDO();

    

    printStatus("Conexión a MySQL", true, "Conectado");

    

    // Verificar tablas principales

    $tablas = [

        'usuarios', 'clientes', 'cuentas', 'movimientos', 

        'servicios', 'auditoria', 'promotores', 'proveedores'

    ];

    

    $stmt = $pdo->query("SHOW TABLES");

    $tablasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    

    $todasExisten = true;

    foreach ($tablas as $tabla) {

        $existe = in_array($tabla, $tablasExistentes);

        $todasExisten = $existe && $todasExisten;

        printStatus("Tabla: $tabla", $existe, $existe ? "OK" : "FALTA");

    }

    

    $allChecks = $todasExisten && $allChecks;

    

    // Verificar índices importantes

    $stmt = $pdo->query("SHOW INDEX FROM movimientos WHERE Key_name = 'idx_grupo_fecha'");

    $indice = $stmt->fetch();

    printStatus("Índices optimizados", (bool)$indice, $indice ? "OK" : "Ejecutar bd/indices_optimizacion.sql");

    

} catch (Exception $e) {

    printStatus("Conexión a MySQL", false, "ERROR: " . $e->getMessage());

    $allChecks = false;

}



// ===== 4. VERIFICAR EXTENSIONES PHP =====

printHeader("4. EXTENSIONES PHP");



$extensiones = [

    'pdo' => 'PDO (base de datos)',

    'pdo_mysql' => 'PDO MySQL driver',

    'mbstring' => 'Multibyte string',

    'json' => 'JSON',

    'zlib' => 'Compresión (backups)',

    'openssl' => 'OpenSSL (seguridad)',

    'session' => 'Sesiones'

];



foreach ($extensiones as $ext => $descripcion) {

    $cargada = extension_loaded($ext);

    $allChecks = printStatus($descripcion, $cargada, $cargada ? "OK" : "NO CARGADA") && $allChecks;

}



// ===== 5. VERIFICAR PERMISOS =====

printHeader("5. PERMISOS DE ARCHIVOS");



$archivosPermisos = [

    'logs' => 0755,

    'backups' => 0755,

    'uploads' => 0755,

    '.env' => 0600

];



foreach ($archivosPermisos as $archivo => $permisoEsperado) {

    $path = __DIR__ . '/../' . $archivo;

    

    if (file_exists($path)) {

        $permisoActual = fileperms($path) & 0777;

        $correcto = $permisoActual === $permisoEsperado;

        

        $mensaje = sprintf("Actual: %04o, Esperado: %04o", $permisoActual, $permisoEsperado);

        printStatus($archivo, $correcto, $mensaje);

    } else {

        printStatus($archivo, false, "NO EXISTE");

    }

}



// ===== 6. VERIFICAR BACKUPS =====

printHeader("6. BACKUPS");



$backupsDir = __DIR__ . '/../backups';

$backups = glob("$backupsDir/backup_*.sql*");



$hayBackups = count($backups) > 0;

printStatus("Backups existentes", $hayBackups, "Total: " . count($backups));



if ($hayBackups) {

    usort($backups, function($a, $b) {

        return filemtime($b) - filemtime($a);

    });

    

    $ultimoBackup = $backups[0];

    $edadBackup = time() - filemtime($ultimoBackup);

    $diasBackup = round($edadBackup / (24 * 60 * 60), 1);

    

    $backupReciente = $diasBackup < 2; // Menos de 2 días

    $mensaje = sprintf("Último backup: %s (hace %s días)", 

        basename($ultimoBackup), 

        $diasBackup

    );

    

    printStatus("Backup reciente", $backupReciente, $mensaje);

    $allChecks = $backupReciente && $allChecks;

} else {

    printStatus("Backup reciente", false, "SIN BACKUPS - Ejecutar scripts/backup.php");

    $allChecks = false;

}



// ===== 7. VERIFICAR LOGS =====

printHeader("7. SISTEMA DE LOGS");



$logsDir = __DIR__ . '/../logs';

$logFiles = ['error.log', 'warning.log', 'info.log', 'debug.log', 'audit.log'];



foreach ($logFiles as $logFile) {

    $path = "$logsDir/$logFile";

    $existe = file_exists($path);

    

    if ($existe) {

        $tamano = filesize($path);

        $tamanoMB = round($tamano / 1024 / 1024, 2);

        $grande = $tamanoMB > 50; // Mayor a 50 MB

        

        $mensaje = sprintf("%s MB%s", 

            $tamanoMB, 

            $grande ? " - GRANDE, considerar limpiar" : ""

        );

        

        printStatus($logFile, true, $mensaje);

    } else {

        printStatus($logFile, false, "NO EXISTE");

    }

}



// ===== 8. VERIFICAR ESPACIO EN DISCO =====

printHeader("8. ESPACIO EN DISCO");



$espacioLibre = disk_free_space(__DIR__);

$espacioTotal = disk_total_space(__DIR__);

$espacioLibreGB = round($espacioLibre / 1024 / 1024 / 1024, 2);

$porcentajeLibre = round(($espacioLibre / $espacioTotal) * 100, 1);



$espacioSuficiente = $porcentajeLibre > 10;

$mensaje = sprintf("%s GB libres (%s%%)", $espacioLibreGB, $porcentajeLibre);



printStatus("Espacio disponible", $espacioSuficiente, $mensaje);

$allChecks = $espacioSuficiente && $allChecks;



// ===== 9. VERIFICAR CONFIGURACIÓN PHP =====

printHeader("9. CONFIGURACIÓN PHP");



$configs = [

    'session.cookie_httponly' => ['esperado' => '1', 'actual' => ini_get('session.cookie_httponly')],

    'session.use_only_cookies' => ['esperado' => '1', 'actual' => ini_get('session.use_only_cookies')],

    'display_errors' => ['esperado' => '0', 'actual' => ini_get('display_errors')],

    'error_reporting' => ['esperado' => 'E_ALL', 'actual' => 'E_ALL']

];



foreach ($configs as $config => $valores) {

    $correcto = $valores['actual'] == $valores['esperado'];

    $mensaje = sprintf("Valor: %s (esperado: %s)", $valores['actual'], $valores['esperado']);

    printStatus($config, $correcto, $mensaje);

}



// ===== RESUMEN FINAL =====

printHeader("RESUMEN");



if ($allChecks) {

    echo $colors['green'];

    echo "  ✓ SISTEMA SALUDABLE - Todos los checks pasaron" . PHP_EOL;

    echo $colors['reset'];

    exit(0);

} else {

    echo $colors['red'];

    echo "  ✗ PROBLEMAS DETECTADOS - Revisar arriba" . PHP_EOL;

    echo $colors['reset'];

    echo PHP_EOL;

    echo "Recomendaciones:" . PHP_EOL;

    echo "  1. Crear archivos/directorios faltantes" . PHP_EOL;

    echo "  2. Verificar permisos de escritura" . PHP_EOL;

    echo "  3. Ejecutar backup si no hay recientes" . PHP_EOL;

    echo "  4. Revisar configuración de PHP" . PHP_EOL;

    exit(1);

}

