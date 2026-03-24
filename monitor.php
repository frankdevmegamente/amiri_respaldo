<?php
/**
 * Dashboard de Monitoreo del Sistema
 * Muestra métricas en tiempo real: errores, queries, espacio, backups
 */
require_once __DIR__ . '/src/config/session.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/src/config/db.php';
require_once __DIR__ . '/src/config/logger.php';

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$pdo = getPDO();

// Obtener métricas
function obtenerMetricas($pdo) {
    $metricas = [];
    
    // 1. Errores recientes (últimas 24 horas)
    $logsDir = __DIR__ . '/logs';
    $errorLog = "$logsDir/error.log";
    $warningLog = "$logsDir/warning.log";
    
    $metricas['errores_24h'] = 0;
    $metricas['warnings_24h'] = 0;
    
    if (file_exists($errorLog)) {
        $lines = Logger::tail($errorLog, 1000);
        $hace24h = time() - (24 * 60 * 60);
        foreach ($lines as $line) {
            if (preg_match('/\[([\d\-]+ [\d:]+)\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp >= $hace24h) {
                    $metricas['errores_24h']++;
                }
            }
        }
    }
    
    if (file_exists($warningLog)) {
        $lines = Logger::tail($warningLog, 1000);
        $hace24h = time() - (24 * 60 * 60);
        foreach ($lines as $line) {
            if (preg_match('/\[([\d\-]+ [\d:]+)\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp >= $hace24h) {
                    $metricas['warnings_24h']++;
                }
            }
        }
    }
    
    // 2. Intentos de login fallidos (últimas 24 horas)
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) as total
            FROM auditoria
            WHERE accion = 'LOGIN_FAILED'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $metricas['login_fallidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $metricas['login_fallidos'] = 'N/A';
    }
    
    // 3. Tamaño de base de datos
    try {
        $stmt = $pdo->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb,
                COUNT(*) as tables
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
        ");
        $dbStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $metricas['db_size_mb'] = $dbStats['size_mb'] ?? 0;
        $metricas['db_tables'] = $dbStats['tables'] ?? 0;
    } catch (Exception $e) {
        $metricas['db_size_mb'] = 'N/A';
        $metricas['db_tables'] = 'N/A';
    }
    
    // 4. Registros de auditoría
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM auditoria");
        $metricas['auditoria_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $metricas['auditoria_total'] = 'N/A';
    }
    
    // 5. Espacio en disco
    $diskTotal = disk_total_space(__DIR__);
    $diskFree = disk_free_space(__DIR__);
    $metricas['disk_total_gb'] = round($diskTotal / 1024 / 1024 / 1024, 2);
    $metricas['disk_free_gb'] = round($diskFree / 1024 / 1024 / 1024, 2);
    $metricas['disk_used_gb'] = $metricas['disk_total_gb'] - $metricas['disk_free_gb'];
    $metricas['disk_percent'] = round(($diskFree / $diskTotal) * 100, 1);
    
    // 6. Backups
    $backupsDir = __DIR__ . '/backups';
    $backups = glob("$backupsDir/backup_*.sql*");
    $metricas['backups_count'] = count($backups);
    
    if (count($backups) > 0) {
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $ultimoBackup = $backups[0];
        $metricas['ultimo_backup'] = date('Y-m-d H:i:s', filemtime($ultimoBackup));
        $metricas['ultimo_backup_size'] = round(filesize($ultimoBackup) / 1024 / 1024, 2);
    } else {
        $metricas['ultimo_backup'] = 'Sin backups';
        $metricas['ultimo_backup_size'] = 0;
    }
    
    // 7. Estado de logs
    $metricas['logs'] = [];
    $logFiles = ['error.log', 'warning.log', 'info.log', 'debug.log', 'audit.log'];
    foreach ($logFiles as $logFile) {
        $path = "$logsDir/$logFile";
        if (file_exists($path)) {
            $metricas['logs'][$logFile] = [
                'size' => round(filesize($path) / 1024 / 1024, 2),
                'lines' => count(file($path)),
                'modified' => date('Y-m-d H:i:s', filemtime($path))
            ];
        }
    }
    
    // 8. Usuarios activos
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
        $metricas['usuarios_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $metricas['usuarios_activos'] = 'N/A';
    }
    
    // 9. Movimientos hoy
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) as total
            FROM movimientos
            WHERE DATE(fecha) = CURDATE()
        ");
        $metricas['movimientos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        $metricas['movimientos_hoy'] = 'N/A';
    }
    
    return $metricas;
}

$metricas = obtenerMetricas($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor del Sistema - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .metric-card {
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
        .status-good { color: #13deb9; }
        .status-warning { color: #ffae1f; }
        .status-danger { color: #fa896b; }
        .log-stats {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
        data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="container-fluid">
                
                <!-- Header -->
                <div class="card bg-light-info shadow-none position-relative overflow-hidden">
                    <div class="card-body px-4 py-3">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h4 id="myElement" class="fw-semibold mb-8"></h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Monitor</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-3 text-end">
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="ti ti-refresh"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métricas principales -->
                <div class="row">
                    <!-- Errores -->
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="ti ti-alert-triangle fs-8 <?= $metricas['errores_24h'] > 0 ? 'status-danger' : 'status-good' ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= $metricas['errores_24h'] ?></h4>
                                        <p class="mb-0 text-muted">Errores (24h)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warnings -->
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="ti ti-alert-circle fs-8 <?= $metricas['warnings_24h'] > 0 ? 'status-warning' : 'status-good' ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= $metricas['warnings_24h'] ?></h4>
                                        <p class="mb-0 text-muted">Warnings (24h)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login fallidos -->
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="ti ti-lock-off fs-8 <?= $metricas['login_fallidos'] > 10 ? 'status-danger' : 'status-good' ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= $metricas['login_fallidos'] ?></h4>
                                        <p class="mb-0 text-muted">Login fallidos (24h)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Movimientos hoy -->
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="ti ti-activity fs-8 status-good"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= $metricas['movimientos_hoy'] ?></h4>
                                        <p class="mb-0 text-muted">Movimientos hoy</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Base de datos y espacio -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="ti ti-database me-2"></i>Base de Datos</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Tamaño</p>
                                        <h4><?= $metricas['db_size_mb'] ?> MB</h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Tablas</p>
                                        <h4><?= $metricas['db_tables'] ?></h4>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <p class="text-muted mb-1">Auditoría</p>
                                        <h4><?= number_format($metricas['auditoria_total']) ?></h4>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <p class="text-muted mb-1">Usuarios activos</p>
                                        <h4><?= $metricas['usuarios_activos'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="ti ti-device-floppy me-2"></i>Espacio en Disco</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-muted mb-1">Total</p>
                                        <h4><?= $metricas['disk_total_gb'] ?> GB</h4>
                                    </div>
                                    <div class="col-4">
                                        <p class="text-muted mb-1">Usado</p>
                                        <h4><?= $metricas['disk_used_gb'] ?> GB</h4>
                                    </div>
                                    <div class="col-4">
                                        <p class="text-muted mb-1">Libre</p>
                                        <h4 class="<?= $metricas['disk_percent'] < 20 ? 'status-danger' : ($metricas['disk_percent'] < 40 ? 'status-warning' : 'status-good') ?>">
                                            <?= $metricas['disk_free_gb'] ?> GB
                                        </h4>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 10px;">
                                    <div class="progress-bar <?= $metricas['disk_percent'] < 20 ? 'bg-danger' : ($metricas['disk_percent'] < 40 ? 'bg-warning' : 'bg-success') ?>" 
                                         style="width: <?= 100 - $metricas['disk_percent'] ?>%"></div>
                                </div>
                                <p class="text-muted mt-2 mb-0 text-center"><?= $metricas['disk_percent'] ?>% libre</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backups -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0"><i class="ti ti-backup me-2"></i>Backups</h5>
                                    <a href="logs-viewer.php?file=backup.log" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-file-text"></i> Ver log de backups
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Backups disponibles</p>
                                        <h4><?= $metricas['backups_count'] ?></h4>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Último backup</p>
                                        <h4><?= $metricas['ultimo_backup'] ?></h4>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-muted mb-1">Tamaño último backup</p>
                                        <h4><?= $metricas['ultimo_backup_size'] ?> MB</h4>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="scripts/backup.bat" class="btn btn-success" download>
                                        <i class="ti ti-download"></i> Descargar script de backup
                                    </a>
                                    <button class="btn btn-info" onclick="window.open('backups', '_blank')">
                                        <i class="ti ti-folder"></i> Ver carpeta de backups
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0"><i class="ti ti-file-text me-2"></i>Estado de Logs</h5>
                                    <a href="logs-viewer.php" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i> Ver logs
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <th>Archivo</th>
                                                <th>Tamaño</th>
                                                <th>Líneas</th>
                                                <th>Última modificación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($metricas['logs'] as $nombre => $info): ?>
                                            <tr>
                                                <td><code><?= $nombre ?></code></td>
                                                <td><?= $info['size'] ?> MB</td>
                                                <td><?= number_format($info['lines']) ?></td>
                                                <td><?= $info['modified'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="ti ti-tool me-2"></i>Acciones Rápidas</h5>
                                <div class="btn-group" role="group">
                                    <a href="logs-viewer.php" class="btn btn-outline-primary">
                                        <i class="ti ti-file-text"></i> Ver Logs
                                    </a>
                                    <a href="auditoria.php" class="btn btn-outline-info">
                                        <i class="ti ti-history"></i> Auditoría
                                    </a>
                                    <a href="usuarios.php" class="btn btn-outline-success">
                                        <i class="ti ti-users"></i> Usuarios
                                    </a>
                                    <button class="btn btn-outline-warning" onclick="if(confirm('¿Limpiar logs y backups antiguos?')) window.location.href='scripts/cleanup.php'">
                                        <i class="ti ti-trash"></i> Limpiar archivos antiguos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/pantalla-completa.js"></script>
        <script src="https://unpkg.com/typeit/dist/index.umd.js"></script>
    <script>
    new TypeIt("#myElement", {
      strings: "Monitor del Sistema",
      speed: 80,
      waitUntilVisible: true,
      cursor: false
    }).go();
    </script>
</body>
</html>
