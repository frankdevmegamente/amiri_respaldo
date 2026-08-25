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

    try {

        $diskTotal = disk_total_space(__DIR__);

        $diskFree = disk_free_space(__DIR__);

        $metricas['disk_total_gb'] = round($diskTotal / 1024 / 1024 / 1024, 2);

        $metricas['disk_free_gb'] = round($diskFree / 1024 / 1024 / 1024, 2);

        $metricas['disk_used_gb'] = $metricas['disk_total_gb'] - $metricas['disk_free_gb'];

        $metricas['disk_percent'] = $diskTotal > 0 ? round(($diskFree / $diskTotal) * 100, 1) : 0;

    } catch (Exception $e) {

        $metricas['disk_total_gb'] = 'N/A';

        $metricas['disk_free_gb'] = 'N/A';

        $metricas['disk_used_gb'] = 'N/A';

        $metricas['disk_percent'] = 0;

    }

    

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

            $lineCount = 0;

            $handle = fopen($path, 'r');

            if ($handle) {

                while (!feof($handle)) {

                    fgets($handle);

                    $lineCount++;

                }

                fclose($handle);

            }

            $metricas['logs'][$logFile] = [

                'size' => round(filesize($path) / 1024 / 1024, 2),

                'lines' => $lineCount,

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

<html lang="es" data-bs-theme="light">

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
        .body-wrapper { padding-top: 100px !important; }
        .body-wrapper-inner, .body-wrapper .container-fluid { padding-top: 15px !important; margin-top: 0 !important; }
        [data-bs-theme="dark"] .logo-modo-claro, .dark-mode .logo-modo-claro { display: none !important; }
        [data-bs-theme="dark"] .logo-modo-oscuro, .dark-mode .logo-modo-oscuro { display: block !important; }

        /* ─── Page Header ─────────────────────────────── */
        .monitor-header {
            position: relative;
            overflow: hidden;
            border-radius: 16px !important;
        }
        .monitor-header .bg-pattern {
            position: absolute; top: -50%; right: -10%;
            width: 400px; height: 400px; border-radius: 50%;
            background: rgba(93, 135, 255, 0.08); pointer-events: none;
        }
        .monitor-header .bg-pattern-2 {
            position: absolute; bottom: -30%; left: 20%;
            width: 250px; height: 250px; border-radius: 50%;
            background: rgba(19, 222, 185, 0.06); pointer-events: none;
        }
        .monitor-header h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
        .monitor-header .sub-badge { font-size: 1rem; color: #8c9ea8; }

        /* ─── Metric Cards ────────────────────────────── */
        .metric-card {
            position: relative;
            border-radius: 12px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
        .metric-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 12px 12px 0 0; }
        .metric-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8c9ea8; }
        .metric-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .metric-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.25rem; }
        .metric-footer { font-size: 0.8rem; color: #8c9ea8; }

        .status-good { color: #13deb9; }
        .status-warning { color: #ffae1f; }
        .status-danger { color: #fa896b; }
        .log-stats { font-size: 0.85rem; }

        /* ─── Dark mode ───────────────────────────────── */
        [data-bs-theme="dark"] h3, [data-bs-theme="dark"] .page-breadcrumb h3 { color: #e9ecef !important; }
        [data-bs-theme="dark"] .card { background: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .table>thead>tr>th { background-color: #1a2234 !important; color: #e9ecef !important; border-bottom-color: #2c344c !important; }
        [data-bs-theme="dark"] .table>tbody>tr>td { color: #e9ecef !important; border-color: #2c344c !important; }
        [data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.02); }
        [data-bs-theme="dark"] .table-hover>tbody>tr:hover>* { box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05); }
        [data-bs-theme="dark"] .modal-content { background-color: #1a2234; border-color: #2c344c; }
        [data-bs-theme="dark"] .modal-header { border-bottom-color: #2c344c; }
        [data-bs-theme="dark"] .modal-footer { border-top-color: #2c344c; }
        [data-bs-theme="dark"] .modal-title { color: #e9ecef; }
        [data-bs-theme="dark"] .form-label { color: #e9ecef; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #252f43; border-color: #2c344c; color: #e9ecef; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #8c9ea8; }
        [data-bs-theme="dark"] .bg-light { background-color: #1e293b !important; }
        [data-bs-theme="dark"] .progress { background-color: #2c344c; }
        [data-bs-theme="dark"] code { color: #e9ecef; }
    </style>

    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>

<body>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"

        data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">



        <?php include 'src/assets/partials/header.php'; ?>

        <?php include 'src/assets/partials/sidebar.php'; ?>



        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid px-4" style="max-width: 100%;">

                    <div class="card monitor-header shadow-sm border-0 rounded-4 mb-4">
                        <div class="bg-pattern"></div>
                        <div class="bg-pattern-2"></div>
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md">
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-3"
                                             style="width:48px;height:48px;background:rgba(93,135,255,0.12);">
                                            <i class="ti ti-device-heart-monitor text-primary" style="font-size:1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">Monitor del Sistema</h2>
                                            <span class="sub-badge"><i class="ti ti-circle"></i> Estado y rendimiento del sistema en tiempo real</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <button class="btn btn-primary btn-sm" onclick="location.reload()">
                                        <i class="ti ti-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card metric-card border-0 shadow-sm h-100">
                            <div class="metric-accent <?= $metricas['errores_24h'] > 0 ? 'bg-danger' : 'bg-success' ?>"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Errores</span>
                                    <div class="metric-icon <?= $metricas['errores_24h'] > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' ?>">
                                        <i class="ti ti-alert-triangle"></i>
                                    </div>
                                </div>
                                <div class="metric-value <?= $metricas['errores_24h'] > 0 ? 'text-danger' : 'text-success' ?>" id="metric-errores"><?= $metricas['errores_24h'] ?></div>
                                <div class="metric-footer">Errores en las últimas 24h</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card metric-card border-0 shadow-sm h-100">
                            <div class="metric-accent <?= $metricas['warnings_24h'] > 0 ? 'bg-warning' : 'bg-success' ?>"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Warnings</span>
                                    <div class="metric-icon <?= $metricas['warnings_24h'] > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' ?>">
                                        <i class="ti ti-alert-circle"></i>
                                    </div>
                                </div>
                                <div class="metric-value <?= $metricas['warnings_24h'] > 0 ? 'text-warning' : 'text-success' ?>"><?= $metricas['warnings_24h'] ?></div>
                                <div class="metric-footer">Warnings en las últimas 24h</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card metric-card border-0 shadow-sm h-100">
                            <div class="metric-accent <?= $metricas['login_fallidos'] > 10 ? 'bg-danger' : 'bg-success' ?>"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Login fallidos</span>
                                    <div class="metric-icon <?= $metricas['login_fallidos'] > 10 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' ?>">
                                        <i class="ti ti-lock-off"></i>
                                    </div>
                                </div>
                                <div class="metric-value <?= $metricas['login_fallidos'] > 10 ? 'text-danger' : 'text-success' ?>"><?= $metricas['login_fallidos'] ?></div>
                                <div class="metric-footer">Intentos fallidos (24h)</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card metric-card border-0 shadow-sm h-100">
                            <div class="metric-accent bg-info"></div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="metric-label">Movimientos</span>
                                    <div class="metric-icon bg-info bg-opacity-10 text-info">
                                        <i class="ti ti-activity"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-info"><?= $metricas['movimientos_hoy'] ?></div>
                                <div class="metric-footer">Movimientos registrados hoy</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
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
                        <div class="card h-100 border-0 shadow-sm">
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

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
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

                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
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
                                            <?php if (empty($metricas['logs'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    <i class="ti ti-info-circle me-1"></i>Sin logs disponibles
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($metricas['logs'] as $nombre => $info): ?>
                                            <tr>
                                                <td><code><?= $nombre ?></code></td>
                                                <td><?= $info['size'] ?> MB</td>
                                                <td><?= number_format($info['lines']) ?></td>
                                                <td><?= $info['modified'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="ti ti-tool me-2"></i>Acciones Rápidas</h5>
                                <div class="d-flex flex-wrap gap-2" role="group">
                                    <a href="logs-viewer.php" class="btn btn-outline-primary" title="Ver registros de log del sistema">
                                        <i class="ti ti-file-text"></i> Ver Logs
                                    </a>
                                    <a href="auditoria.php" class="btn btn-outline-info" title="Historial de auditoría del sistema">
                                        <i class="ti ti-history"></i> Auditoría
                                    </a>
                                    <a href="usuarios.php" class="btn btn-outline-success" title="Gestionar usuarios del sistema">
                                        <i class="ti ti-users"></i> Usuarios
                                    </a>
                                    <button class="btn btn-outline-warning" title="Eliminar logs y backups antiguos" data-bs-toggle="modal" data-bs-target="#modalCleanup">
                                        <i class="ti ti-trash"></i> Limpiar archivos antiguos
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" title="Resetear datos financieros del sistema" data-bs-toggle="modal" data-bs-target="#modalResetDatos">
                                        <i class="ti ti-refresh"></i> Reset de Datos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>


    <!-- Modal Limpiar Archivos Antiguos -->
    <div class="modal fade" id="modalCleanup" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="ti ti-trash text-warning me-2"></i>Limpiar Archivos Antiguos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="ti ti-info-circle fs-5 me-2"></i>
                        <div>Se eliminar&aacute;n logs y backups con m&aacute;s de 30 d&iacute;as de antigüedad.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="window.location.href='scripts/cleanup.php'">
                        <i class="ti ti-trash me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reset de Datos -->
    <div class="modal fade" id="modalResetDatos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="ti ti-alert-triangle text-danger me-2"></i>Reset de Datos Financieros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="ti ti-info-circle fs-5 me-2"></i>
                        <div><strong>Esta acci&oacute;n es irreversible.</strong> Se eliminar&aacute;n todos los datos financieros.</div>
                    </div>
                    <p class="mb-2"><strong>Se resetear&aacute;:</strong></p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="ti ti-x text-danger me-1"></i> Movimientos / transacciones</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Saldos de cuentas (se pondr&aacute;n en $0)</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Inventario de monedas / FIFO</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Deudas (clientes y empresa)</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Pr&eacute;stamos otorgados</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Gastos, pagos a promotores</li>
                        <li><i class="ti ti-x text-danger me-1"></i> Cierres mensuales y retiros de capital</li>
                    </ul>
                    <p class="mb-2"><strong>Se conservar&aacute;:</strong></p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="ti ti-check text-success me-1"></i> Usuarios</li>
                        <li><i class="ti ti-check text-success me-1"></i> Clientes, promotores, proveedores, prestamistas</li>
                        <li><i class="ti ti-check text-success me-1"></i> Cuentas (con saldo en $0)</li>
                        <li><i class="ti ti-check text-success me-1"></i> Cat&aacute;logos (servicios, categor&iacute;as, tipos de cuenta)</li>
                    </ul>
                    <div class="mb-0">
                        <label class="form-label">Escribe <strong>RESET</strong> para confirmar:</label>
                        <input type="text" id="confirmResetInput" class="form-control" placeholder="RESET" autocomplete="off">
                    </div>
                    <div id="resetStatus" class="mt-3" style="display:none;"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnResetDatos" class="btn btn-danger" disabled>
                        <i class="ti ti-refresh me-1"></i>Resetear Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('confirmResetInput');
        const btn = document.getElementById('btnResetDatos');
        const status = document.getElementById('resetStatus');

        input.addEventListener('input', function() {
            btn.disabled = this.value.trim() !== 'RESET';
        });

        btn.addEventListener('click', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Reseteando...';
            status.style.display = 'none';

            fetch('api/reset-datos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    status.className = 'mt-3 alert alert-success';
                    status.innerHTML = '<i class="ti ti-check-circle me-1"></i> ' + data.message;
                    status.style.display = 'block';
                    btn.innerHTML = '<i class="ti ti-refresh me-1"></i>Resetear Datos';
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            })
            .catch(function(err) {
                status.className = 'mt-3 alert alert-danger';
                status.innerHTML = '<i class="ti ti-x-circle me-1"></i> ' + err.message;
                status.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-refresh me-1"></i>Resetear Datos';
            });
        });
    });
    </script>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>

    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/assets/js/sidebarmenu.js"></script>

    <script src="src/assets/js/dark-mode.js"></script>

    <script src="src/assets/js/pantalla-completa.js"></script>

    <script src="src/assets/js/utils.js"></script>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

</body>

</html>

