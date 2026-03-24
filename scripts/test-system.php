<?php
/**
 * Script de pruebas automáticas del sistema
 * Verifica funcionalidades críticas sin necesidad de navegador
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/logger.php';
require_once __DIR__ . '/../src/config/validator.php';
require_once __DIR__ . '/../src/config/cache.php';
require_once __DIR__ . '/../src/config/rate-limiter.php';
require_once __DIR__ . '/../src/services/ClienteService.php';
require_once __DIR__ . '/../src/services/FlujoCajaService.php';

// Colores para terminal
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$NC = "\033[0m"; // No Color

$tests_passed = 0;
$tests_failed = 0;
$tests_total = 0;

function printResult($test_name, $passed, $message = '') {
    global $GREEN, $RED, $NC, $tests_passed, $tests_failed, $tests_total;
    $tests_total++;
    
    if ($passed) {
        $tests_passed++;
        echo "{$GREEN}✓{$NC} {$test_name}\n";
    } else {
        $tests_failed++;
        echo "{$RED}✗{$NC} {$test_name}";
        if ($message) echo " - {$message}";
        echo "\n";
    }
}

function printSection($title) {
    global $YELLOW, $NC;
    echo "\n{$YELLOW}═══════════════════════════════════════════════════\n";
    echo " {$title}\n";
    echo "═══════════════════════════════════════════════════{$NC}\n";
}

echo "\n";
echo "╔═══════════════════════════════════════════════════╗\n";
echo "║       PRUEBAS AUTOMÁTICAS - SISTEMA AMIRI        ║\n";
echo "╚═══════════════════════════════════════════════════╝\n";

// ============= TEST 1: CONEXIÓN A BASE DE DATOS =============
printSection("1. CONEXIÓN A BASE DE DATOS");

try {
    $pdo = getPDO();
    printResult("Conexión PDO", true);
    
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch(PDO::FETCH_ASSOC);
    printResult("Base de datos activa: {$db['db']}", true);
} catch (Exception $e) {
    printResult("Conexión PDO", false, $e->getMessage());
    exit(1);
}

// ============= TEST 2: LOGGER =============
printSection("2. SISTEMA DE LOGGING");

try {
    Logger::info('Test de logging desde script de pruebas');
    printResult("Logger::info()", file_exists(__DIR__ . '/../logs/info.log'));
    
    Logger::warning('Test warning');
    printResult("Logger::warning()", file_exists(__DIR__ . '/../logs/warning.log'));
    
    Logger::error('Test error');
    printResult("Logger::error()", file_exists(__DIR__ . '/../logs/error.log'));
    
    Logger::debug('Test debug');
    printResult("Logger::debug()", file_exists(__DIR__ . '/../logs/debug.log'));
    
    Logger::audit('Test audit', ['test' => true]);
    printResult("Logger::audit()", file_exists(__DIR__ . '/../logs/audit.log'));
} catch (Exception $e) {
    printResult("Sistema de logging", false, $e->getMessage());
}

// ============= TEST 3: VALIDATOR =============
printSection("3. SISTEMA DE VALIDACIÓN");

try {
    // Test required - campo faltante
    try {
        Validator::required([], ['nombre']);
        printResult("Validator::required() - campo vacío lanza excepción", false);
    } catch (ValidationException $e) {
        printResult("Validator::required() - campo vacío lanza excepción", true);
    }
    
    // Test required - campo presente
    try {
        $result = Validator::required(['nombre' => 'Juan'], ['nombre']);
        printResult("Validator::required() - campo presente pasa", $result === true);
    } catch (ValidationException $e) {
        printResult("Validator::required() - campo presente pasa", false);
    }
    
    // Test email válido
    try {
        $result = Validator::email('test@example.com', 'email');
        printResult("Validator::email() - email válido pasa", $result === 'test@example.com');
    } catch (ValidationException $e) {
        printResult("Validator::email() - email válido pasa", false);
    }
    
    // Test email inválido
    try {
        Validator::email('invalid-email', 'email');
        printResult("Validator::email() - email inválido lanza excepción", false);
    } catch (ValidationException $e) {
        printResult("Validator::email() - email inválido lanza excepción", true);
    }
    
    // Test numeric válido
    try {
        $result = Validator::numeric('123.45', null, null, 'monto');
        printResult("Validator::numeric() - numérico pasa", is_float($result));
    } catch (ValidationException $e) {
        printResult("Validator::numeric() - numérico pasa", false);
    }
    
    // Test numeric inválido
    try {
        Validator::numeric('abc', null, null, 'monto');
        printResult("Validator::numeric() - no numérico lanza excepción", false);
    } catch (ValidationException $e) {
        printResult("Validator::numeric() - no numérico lanza excepción", true);
    }
    
    // Test positiveInt válido
    try {
        $result = Validator::positiveInt(100, 'cantidad');
        printResult("Validator::positiveInt() - positivo pasa", $result === 100);
    } catch (ValidationException $e) {
        printResult("Validator::positiveInt() - positivo pasa", false);
    }
    
    // Test positiveInt inválido
    try {
        Validator::positiveInt(-5, 'cantidad');
        printResult("Validator::positiveInt() - negativo lanza excepción", false);
    } catch (ValidationException $e) {
        printResult("Validator::positiveInt() - negativo lanza excepción", true);
    }
} catch (Exception $e) {
    printResult("Sistema de validación", false, $e->getMessage());
}

// ============= TEST 4: CACHE =============
printSection("4. SISTEMA DE CACHÉ");

try {
    $cache = new SimpleCache();
    
    // Test set/get
    $cache->set('test_key', 'test_value', 60);
    $value = $cache->get('test_key');
    printResult("Cache::set() y get()", $value === 'test_value');
    
    // Test remember
    $counter = 0;
    $result = $cache->remember('test_remember', function() use (&$counter) {
        $counter++;
        return 'computed_value';
    }, 60);
    printResult("Cache::remember() - primera llamada", $result === 'computed_value' && $counter === 1);
    
    $result = $cache->remember('test_remember', function() use (&$counter) {
        $counter++;
        return 'computed_value';
    }, 60);
    printResult("Cache::remember() - segunda llamada (cached)", $result === 'computed_value' && $counter === 1);
    
    // Test invalidate
    $cache->invalidate('test_key');
    $value = $cache->get('test_key');
    printResult("Cache::invalidate()", $value === null);
    
    // Cleanup
    $cache->invalidate('test_remember');
} catch (Exception $e) {
    printResult("Sistema de caché", false, $e->getMessage());
}

// ============= TEST 5: RATE LIMITER =============
printSection("5. RATE LIMITER");

try {
    $limiter = new RateLimiter();
    
    // Test hit
    $result = $limiter->hit('test_ip', 5, 60);
    printResult("RateLimiter::hit() - primer intento", $result);
    
    // Test attempts
    $attempts = $limiter->attempts('test_ip');
    printResult("RateLimiter::attempts()", $attempts === 1);
    
    // Test availableIn
    $available = $limiter->availableIn('test_ip');
    printResult("RateLimiter::availableIn()", is_int($available));
    
    // Test clear
    $limiter->clear('test_ip');
    $attempts = $limiter->attempts('test_ip');
    printResult("RateLimiter::clear()", $attempts === 0);
} catch (Exception $e) {
    printResult("Rate Limiter", false, $e->getMessage());
}

// ============= TEST 6: CLIENTE SERVICE =============
printSection("6. CLIENTE SERVICE");

try {
    $clienteService = new ClienteService($pdo);
    
    // Test listar clientes
    $clientes = $clienteService->listarClientes(true);
    printResult("ClienteService::listarClientes()", is_array($clientes));
    
    // Test obtener cliente (si hay al menos uno)
    if (!empty($clientes)) {
        $primer_cliente = $clientes[0];
        $cliente = $clienteService->obtenerCliente($primer_cliente['id']);
        printResult("ClienteService::obtenerCliente()", 
            is_array($cliente) && isset($cliente['nombre']));
    } else {
        printResult("ClienteService::obtenerCliente()", true, "Sin clientes para probar");
    }
} catch (Exception $e) {
    printResult("Cliente Service", false, $e->getMessage());
}

// ============= TEST 7: FLUJO CAJA SERVICE =============
printSection("7. FLUJO DE CAJA SERVICE");

try {
    $flujoService = new FlujoCajaService($pdo);
    
    // Test obtener resumen
    $resumen = $flujoService->obtenerResumen(date('Y-m-01'), date('Y-m-d'));
    printResult("FlujoCajaService::obtenerResumen()", 
        is_array($resumen));
    
    // Test obtener inventario FIFO
    $inventario = $flujoService->obtenerInventarioFIFO('MXN', null);
    printResult("FlujoCajaService::obtenerInventarioFIFO()", 
        is_array($inventario));
} catch (Exception $e) {
    printResult("Flujo Caja Service", false, $e->getMessage());
}

// ============= TEST 8: TABLAS DE BASE DE DATOS =============
printSection("8. ESTRUCTURA DE BASE DE DATOS");

$tablas_requeridas = [
    'usuarios', 'clientes', 'cuentas', 'movimientos', 
    'servicios', 'auditoria', 'promotores', 'proveedores',
    'prestamistas', 'deudas_empresa', 'comisiones_promotores',
    'flujo', 'gastos', 'categorias_gasto', 'clientes_pagos', 
    'proveedores_pagos', 'ventas_efectivo'
];

foreach ($tablas_requeridas as $tabla) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tabla}'");
        $exists = $stmt->fetch();
        printResult("Tabla: {$tabla}", (bool)$exists);
    } catch (Exception $e) {
        printResult("Tabla: {$tabla}", false, $e->getMessage());
    }
}

// ============= TEST 9: ARCHIVOS DE CONFIGURACIÓN =============
printSection("9. ARCHIVOS DE CONFIGURACIÓN");

$archivos_config = [
    'src/config/db.php',
    'src/config/session.php',
    'src/config/logger.php',
    'src/config/validator.php',
    'src/config/cache.php',
    'src/config/rate-limiter.php',
    'src/config/api-middleware.php',
    'src/config/helpers.php',
    'src/config/auditoria.php',
    'src/config/dotenv.php'
];

foreach ($archivos_config as $archivo) {
    $path = __DIR__ . '/../' . $archivo;
    printResult($archivo, file_exists($path));
}

// ============= TEST 10: DIRECTORIOS =============
printSection("10. DIRECTORIOS NECESARIOS");

$directorios = [
    'logs' => 'logs',
    'backups' => 'backups',
    'uploads' => 'uploads',
    'scripts' => 'scripts'
];

foreach ($directorios as $nombre => $path) {
    $full_path = __DIR__ . '/../' . $path;
    $exists = is_dir($full_path);
    $writable = is_writable($full_path);
    printResult("{$nombre} (escribible)", $exists && $writable);
}

// ============= RESUMEN =============
echo "\n";
echo "═══════════════════════════════════════════════════\n";
echo " RESUMEN DE PRUEBAS\n";
echo "═══════════════════════════════════════════════════\n";
echo " Total:   {$tests_total}\n";
echo " {$GREEN}Pasadas: {$tests_passed}{$NC}\n";
echo " {$RED}Falladas: {$tests_failed}{$NC}\n";
echo "═══════════════════════════════════════════════════\n";

if ($tests_failed === 0) {
    echo "{$GREEN}✓ TODAS LAS PRUEBAS PASARON{$NC}\n\n";
    exit(0);
} else {
    echo "{$RED}✗ ALGUNAS PRUEBAS FALLARON{$NC}\n\n";
    exit(1);
}
