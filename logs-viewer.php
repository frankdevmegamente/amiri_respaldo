<?php
/**
 * Visor de logs - Sistema de monitoreo
 * Solo accesible por administradores
 */
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/logger.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$logFile = $_GET['file'] ?? 'error.log';
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 100;

// Lista de archivos de log disponibles
$logFiles = glob(__DIR__ . '/logs/*.log');
$logFiles = array_map('basename', $logFiles);

// Obtener contenido del log
$logContent = Logger::tail($logFile, $lines);

// Obtener estadísticas
$stats = Logger::getStats();

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Logs - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode.css">
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .log-viewer {
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            padding: 20px;
            border-radius: 8px;
            max-height: 600px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.6;
            border: 1px solid var(--bs-border-color);
        }
        .log-line {
            margin: 2px 0;
            padding: 4px 8px;
            border-left: 3px solid transparent;
            transition: background 0.2s;
        }
        .log-line:hover {
            background: var(--bs-tertiary-bg);
        }
        .log-ERROR {
            border-left-color: #fa896b;
            background: rgba(250, 137, 107, 0.1);
        }
        .log-WARNING {
            border-left-color: #ffae1f;
            background: rgba(255, 174, 31, 0.1);
        }
        .log-INFO {
            border-left-color: #13deb9;
            background: rgba(19, 222, 185, 0.05);
        }
        .log-DEBUG {
            border-left-color: #539bff;
            opacity: 0.8;
        }
        .timestamp {
            color: var(--bs-secondary-color);
        }
        .level {
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }
        .level-ERROR { 
            background: #fa896b; 
            color: white; 
        }
        .level-WARNING { 
            background: #ffae1f; 
            color: #000; 
        }
        .level-INFO { 
            background: #13deb9; 
            color: #000; 
        }
        .level-DEBUG { 
            background: #539bff; 
            color: white; 
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
                                <h4 class="fw-semibold mb-8">Visor de Logs</h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="monitor.php">Monitor</a></li>
                                        <li class="breadcrumb-item active">Logs</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-3 text-end">
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="ti ti-refresh"></i> Recargar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Archivo de log:</label>
                                <select class="form-select" id="logFileSelect" onchange="changeLogFile()">
                                    <?php foreach ($logFiles as $file): ?>
                                        <option value="<?= $file ?>" <?= $file === $logFile ? 'selected' : '' ?>>
                                            <?= $file ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Líneas a mostrar:</label>
                                <select class="form-select" id="linesSelect" onchange="changeLines()">
                                    <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100</option>
                                    <option value="200" <?= $lines === 200 ? 'selected' : '' ?>>200</option>
                                    <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar:</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="Buscar en logs..." onkeyup="filterLogs()">
                            </div>
                        </div>

                        <!-- Estadísticas -->
                        <?php if (isset($stats[$logFile])): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card bg-light-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="ti ti-file-text fs-8"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Tamaño</h6>
                                                <h4 class="mb-0"><?= $stats[$logFile]['size_mb'] ?> MB</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="ti ti-list-numbers fs-8"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Líneas</h6>
                                                <h4 class="mb-0"><?= number_format($stats[$logFile]['lines']) ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="ti ti-clock fs-8"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-muted">Modificado</h6>
                                                <h6 class="mb-0"><?= $stats[$logFile]['modified'] ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Visor de logs -->
                        <div class="log-viewer" id="logViewer">
                            <?php if (empty($logContent)): ?>
                                <div class="text-center text-muted py-5">
                                    📄 No hay entradas en este log
                                </div>
                            <?php else: ?>
                                <?php foreach (array_reverse($logContent) as $line): ?>
                                    <?php
                                    // Extraer nivel del log
                                    preg_match('/\[(ERROR|WARNING|INFO|DEBUG)\]/', $line, $matches);
                                    $level = $matches[1] ?? '';
                                    ?>
                                    <div class="log-line log-<?= $level ?>" data-line="<?= htmlspecialchars($line) ?>">
                                        <?= htmlspecialchars($line) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-3">
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyLogs()">
                                <i class="ti ti-copy"></i> Copiar logs
                            </button>
                            <a href="monitor.php" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-arrow-left"></i> Volver al Monitor
                            </a>
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
    <script>
        function changeLogFile() {
            const file = document.getElementById('logFileSelect').value;
            const lines = document.getElementById('linesSelect').value;
            window.location.href = `?file=${file}&lines=${lines}`;
        }

        function changeLines() {
            const file = document.getElementById('logFileSelect').value;
            const lines = document.getElementById('linesSelect').value;
            window.location.href = `?file=${file}&lines=${lines}`;
        }

        function filterLogs() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const lines = document.querySelectorAll('.log-line');
            
            lines.forEach(line => {
                const text = line.getAttribute('data-line').toLowerCase();
                line.style.display = text.includes(search) ? 'block' : 'none';
            });
        }

        function copyLogs() {
            const logViewer = document.getElementById('logViewer');
            const text = logViewer.innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Logs copiados al portapapeles');
            }).catch(() => {
                alert('Error al copiar logs');
            });
        }

        // Auto-scroll al final
        const logViewer = document.getElementById('logViewer');
        if (logViewer) {
            logViewer.scrollTop = logViewer.scrollHeight;
        }
    </script>
</body>
</html>
