<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php';
require_once __DIR__ . '/src/services/ImportarSqlService.php';

set_time_limit(600);

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol = $_SESSION['usuario_rol'] ?? '';
$pagina_actual = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$tempDb = $_SESSION['import_temp_db'] ?? '';
$importInfo = $_SESSION['import_info'] ?? [];
$tableStats = $_SESSION['import_table_stats'] ?? [];
$validationDetails = $_SESSION['import_validation'] ?? [];
$compareInfo = $_SESSION['import_compare'] ?? [];
$syncInfo = $_SESSION['import_sync'] ?? [];
$importedFile = $_SESSION['import_file'] ?? '';

function findSqlFiles(): array {
    $dirs = [__DIR__, __DIR__ . '/bd', __DIR__ . '/backups', __DIR__ . '/scripts'];
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

$pdo = getPDO();
$localDb = $pdo->query("SELECT DATABASE()")->fetchColumn();

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($httpMethod === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? ($_GET['action'] ?? '');
    $service = new ImportarSqlService($pdo);

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

                $result = $service->importSql($selectedFile);
                if (!$result['success']) {
                    throw new Exception($result['error']);
                }

                $tempDb = $result['temp_db'];
                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $validation = $service->validateAll($tempDb, $tempPDO);
                $compare = $service->compareWithLocal($tempDb, $tempPDO, $validation['tables']);

                $totalInvalid = 0;
                foreach ($validation['tables'] as $t) {
                    $totalInvalid += $t['invalid_count'];
                }

                $_SESSION['import_temp_db'] = $tempDb;
                $_SESSION['import_info'] = $result;
                $_SESSION['import_table_stats'] = $validation['tables'];
                $_SESSION['import_validation'] = $validation['validation_details'];
                $_SESSION['import_compare'] = $compare;
                $_SESSION['import_sync'] = [];
                $_SESSION['import_file'] = basename($selectedFile);

                $tableStats = $validation['tables'];
                $validationDetails = $validation['validation_details'];
                $compareInfo = $compare;
                $importInfo = $result;
                $importedFile = basename($selectedFile);

                $parts = ["Importación completada en {$result['time']}s. {$result['total_records']} registros en " . count($result['tables']) . " tablas."];
                if ($totalInvalid > 0) $parts[] = "{$totalInvalid} registros con errores de validación.";
                if ($compare['total_missing'] > 0) $parts[] = "{$compare['total_missing']} registros nuevos para sincronizar.";
                $message = implode(' ', $parts);
                $messageType = $totalInvalid > 0 ? 'warning' : ($compare['total_missing'] > 0 ? 'info' : 'success');
                break;

            case 'validate':
                if (empty($tempDb)) throw new Exception('No hay BD temporal. Importa un archivo primero.');
                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $validation = $service->validateAll($tempDb, $tempPDO);
                $totalInvalid = 0;
                foreach ($validation['tables'] as $t) {
                    $totalInvalid += $t['invalid_count'];
                }
                $_SESSION['import_table_stats'] = $validation['tables'];
                $_SESSION['import_validation'] = $validation['validation_details'];
                $tableStats = $validation['tables'];
                $validationDetails = $validation['validation_details'];
                $message = "Validación completada. {$totalInvalid} registros con errores.";
                $messageType = $totalInvalid > 0 ? 'warning' : 'success';
                break;

            case 'preview_errors':
                if (empty($tempDb)) { http_response_code(400); echo json_encode(['error' => 'No hay BD temporal']); exit; }
                $table = $_GET['table'] ?? '';
                if (empty($table)) { http_response_code(400); echo json_encode(['error' => 'Tabla no especificada']); exit; }

                $stats = $_SESSION['import_table_stats'] ?? [];
                $tableStat = $stats[$table] ?? null;
                if (!$tableStat) { http_response_code(404); echo json_encode(['error' => 'Tabla no encontrada']); exit; }

                echo json_encode([
                    'table' => $table,
                    'errors' => $tableStat['errors'] ?? [],
                    'count_sql' => $tableStat['count_sql'],
                    'valid_count' => $tableStat['valid_count'],
                    'invalid_count' => $tableStat['invalid_count'],
                ]);
                exit;

            case 'preview_missing':
                if (empty($tempDb)) { http_response_code(400); echo json_encode(['error' => 'No hay BD temporal']); exit; }
                $table = $_GET['table'] ?? '';
                if (empty($table)) { http_response_code(400); echo json_encode(['error' => 'Tabla no especificada']); exit; }

                $stats = $_SESSION['import_table_stats'] ?? [];
                $tableStat = $stats[$table] ?? null;
                if (!$tableStat) { http_response_code(404); echo json_encode(['error' => 'Tabla no encontrada']); exit; }

                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $missingValidIds = $tableStat['missing_valid_ids'] ?? [];
                $pk = null;
                $stmt = $tempPDO->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_KEY = 'PRI' LIMIT 1");
                $stmt->execute([$tempDb, $table]);
                $pk = $stmt->fetchColumn() ?: null;

                $response = [
                    'table' => $table,
                    'status' => $tableStat['status'] ?? 'ok',
                    'missing_count' => $tableStat['missing_count'] ?? 0,
                    'missing_valid_count' => count($missingValidIds),
                    'pk' => $pk,
                    'rows' => [],
                    'columns' => [],
                ];

                if (!empty($missingValidIds) && $pk) {
                    $slice = array_slice($missingValidIds, 0, 200);
                    $placeholders = rtrim(str_repeat('?,', count($slice)), ',');
                    $stmt = $tempPDO->prepare("SELECT * FROM `{$tempDb}`.`{$table}` WHERE `{$pk}` IN ({$placeholders}) ORDER BY `{$pk}`");
                    $stmt->execute($slice);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($rows)) {
                        $response['columns'] = array_keys($rows[0]);
                        $response['rows'] = $rows;
                    }
                } elseif ($tableStat['missing_count'] > 0 && !$tableStat['exists_local']) {
                    $rows = $tempPDO->query("SELECT * FROM `{$tempDb}`.`{$table}` LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($rows)) {
                        $response['columns'] = array_keys($rows[0]);
                        $response['rows'] = $rows;
                    }
                }

                echo json_encode($response);
                exit;

            case 'sync_all':
                if (empty($tempDb)) throw new Exception('No hay BD temporal. Importa un archivo primero.');
                $tempPDO = new PDO("mysql:host=localhost;dbname={$tempDb};charset=utf8mb4", 'root', '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                $stats = &$_SESSION['import_table_stats'];
                $result = $service->syncAll($tempDb, $tempPDO, $stats);
                $_SESSION['import_sync'] = $result;
                $syncInfo = $result;

                $msgParts = ["Sincronización completada: {$result['total_inserted']} registros insertados."];
                if ($result['new_tables_created'] > 0) $msgParts[] = "{$result['new_tables_created']} tablas creadas.";
                if (!empty($result['errors'])) $msgParts[] = count($result['errors']) . " errores.";
                $message = implode(' ', $msgParts);
                $messageType = empty($result['errors']) ? 'success' : 'warning';
                if (!empty($result['errors'])) {
                    $message .= '<!--ERRORS-->' . implode('\\n', array_slice($result['errors'], 0, 10));
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

                $stats = &$_SESSION['import_table_stats'];
                if (!isset($stats[$table])) throw new Exception("Tabla '{$table}' no encontrada.");

                $result = $service->syncTable($tempDb, $tempPDO, $table, $stats[$table]);

                $message = "Tabla '{$table}': {$result['inserted']} registros sincronizados.";
                if (!empty($result['errors'])) $message .= ' ' . implode(', ', $result['errors']);
                $messageType = empty($result['errors']) ? 'success' : 'warning';
                break;

            case 'cleanup':
                if (!empty($tempDb)) {
                    $service->cleanup($tempDb);
                }
                unset(
                    $_SESSION['import_temp_db'], $_SESSION['import_info'], $_SESSION['import_table_stats'],
                    $_SESSION['import_validation'], $_SESSION['import_compare'], $_SESSION['import_sync'],
                    $_SESSION['import_file']
                );
                $tempDb = '';
                $importInfo = [];
                $tableStats = [];
                $validationDetails = [];
                $compareInfo = [];
                $syncInfo = [];
                $importedFile = '';
                $message = 'Base de datos temporal eliminada.';
                $messageType = 'info';
                break;

            case 'export_report':
                if (empty($tempDb)) throw new Exception('No hay datos para exportar.');
                $report = [
                    'exported_at' => date('Y-m-d H:i:s'),
                    'local_db' => $localDb,
                    'temp_db' => $tempDb,
                    'file' => $importedFile,
                    'import' => $_SESSION['import_info'] ?? [],
                    'validation' => $_SESSION['import_validation'] ?? [],
                    'compare' => $_SESSION['import_compare'] ?? [],
                    'sync' => $_SESSION['import_sync'] ?? [],
                    'tables' => $_SESSION['import_table_stats'] ?? [],
                ];
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="reporte_importacion_' . date('Ymd_His') . '.json"');
                echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }

    if (in_array($action, ['preview_errors', 'preview_missing'])) exit;

    $tableStats = $_SESSION['import_table_stats'] ?? [];
    $tempDb = $_SESSION['import_temp_db'] ?? '';
    $importInfo = $_SESSION['import_info'] ?? [];
    $validationDetails = $_SESSION['import_validation'] ?? [];
    $compareInfo = $_SESSION['import_compare'] ?? [];
    $syncInfo = $_SESSION['import_sync'] ?? [];
    $importedFile = $_SESSION['import_file'] ?? '';
}

$sqlFiles = findSqlFiles();
$hasTempDb = !empty($tempDb);
$totalTables = count($tableStats);
$totalRecords = $importInfo['total_records'] ?? 0;
$totalInvalid = array_sum(array_column($tableStats, 'invalid_count'));
$totalValid = array_sum(array_column($tableStats, 'valid_count'));
$totalMissing = array_sum(array_column($tableStats, 'missing_count'));
$tablesWithMissing = count(array_filter($tableStats, fn($r) => ($r['missing_count'] ?? 0) > 0));
$tablesWithErrors = count(array_filter($tableStats, fn($r) => ($r['invalid_count'] ?? 0) > 0));
$totalInserted = $syncInfo['total_inserted'] ?? 0;
$scriptUrl = 'importar-sql.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar SQL - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico">
    <link rel="stylesheet" href="src/assets/css/styles.min.css">
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="src/assets/css/layout-custom.css">
    <style>
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; }
        .card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .card-title { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .card-title i { font-size: 1.3rem; }
        .stat-box { background: #f8f9fa; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-box .num { font-size: 1.8rem; font-weight: 700; line-height: 1.2; }
        .stat-box .label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        .badge-status { font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 20px; font-weight: 500; }
        .table-result th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; border-top: none; }
        .table-result td { vertical-align: middle; font-size: 0.9rem; }
        .preview-wrap { max-height: 450px; overflow-y: auto; border: 1px solid #f5f2f4; border-radius: 8px; display: none; }
        .preview-wrap table { margin-bottom: 0; font-size: 0.8rem; }
        .preview-wrap th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; font-size: 0.75rem; white-space: nowrap; }
        .preview-wrap td { white-space: nowrap; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        .preview-loading { text-align: center; padding: 2rem; display: none; }
        .preview-loading .spinner { width: 40px; height: 40px; border: 4px solid #f5f2f4; border-top-color: var(--nx-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .sync-progress { display: none; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .error-detail { font-size: 0.8rem; border-left: 3px solid var(--nx-danger); padding-left: 8px; margin-bottom: 4px; }
        .error-detail .field { font-weight: 600; color: var(--nx-danger); }
        .val-summary { display: flex; gap: 12px; flex-wrap: wrap; }
        .val-summary .val-item { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 500; }
        .val-pass { background: #e8f5e9; color: #2e7d32; }
        .val-fail { background: #fbe9e7; color: #c62828; }
        [data-bs-theme="dark"] .card { background: #1f2535; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        [data-bs-theme="dark"] .stat-box { background: #232b3c; }
        [data-bs-theme="dark"] .stat-box .label { color: #9aa4c8; }
        [data-bs-theme="dark"] .table-result th { color: #9aa4c8; border-color: #2b3350; }
        [data-bs-theme="dark"] .table-result td { border-color: #2b3350; color: #c8cee8; }
        [data-bs-theme="dark"] .preview-wrap { border-color: #2b3350; }
        [data-bs-theme="dark"] .preview-wrap th { background: #232b3c; color: #e6ecff; border-color: #2b3350; }
        [data-bs-theme="dark"] .preview-wrap td { border-color: #2b3350; color: #c8cee8; }
        [data-bs-theme="dark"] .preview-loading .spinner { border-color: #2b3350; border-top-color: var(--nx-primary); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background: #232b3c; color: #e6ecff; border-color: #2b3350; }
        [data-bs-theme="dark"] .form-control:focus { background: #1f2535; color: #e6ecff; border-color: var(--nx-primary); box-shadow: 0 0 0 0.2rem rgba(93,135,255,0.25); }
        [data-bs-theme="dark"] .form-label { color: #c8cee8; }
        [data-bs-theme="dark"] .val-pass { background: #1b3a1b; color: #81c784; }
        [data-bs-theme="dark"] .val-fail { background: #3a1b1b; color: #ef9a9a; }
        [data-bs-theme="dark"] .btn-close { filter: invert(1); }
        [data-bs-theme="dark"] .error-detail { border-left-color: #ef5350; }
        [data-bs-theme="dark"] .error-detail .field { color: #ef5350; }
        .file-size { font-size: 0.75rem; color: #6c757d; }
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
                                        <i class="ti ti-database-import fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-semibold mb-0" style="font-size:1.2rem;">Importar y Validar SQL</h5>
                                        <small class="text-muted">Importa un dump .sql, valida los datos y sincroniza registros nuevos con tu BD local</small>
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
                                    <span class="badge bg-primary bg-opacity-10 text-primary border-0" style="font-size:0.8rem;">
                                        <i class="ti ti-server"></i> BD local: <strong><?= htmlspecialchars($localDb) ?></strong>
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
                                            <option value="<?= htmlspecialchars($f['path']) ?>"><?= htmlspecialchars($f['name']) ?> (<?= $f['size_hr'] ?>)</option>
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
                                            <i class="ti ti-database-import"></i> Importar, validar y comparar
                                        </button>
                                    </div>
                                    <div id="importProgress" class="sync-progress mt-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            <span>Importando y validando archivo SQL...</span>
                                        </div>
                                    </div>
                                </form>

                                <?php if ($hasTempDb): ?>
                                <hr>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="validate">
                                        <button type="submit" class="btn btn-outline-info btn-sm">
                                            <i class="ti ti-refresh"></i> Re-validar
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="export_report">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="ti ti-download"></i> Descargar reporte
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
                        <?php if ($hasTempDb): ?>
                        <div class="card mt-4">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3"><i class="ti ti-report-analytics" style="color:var(--nx-success);"></i> Resumen</h5>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-primary);"><?= number_format($totalTables) ?></div>
                                            <div class="label">Tablas</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-primary);"><?= number_format($totalRecords) ?></div>
                                            <div class="label">Registros en SQL</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-success);"><?= number_format($totalValid) ?></div>
                                            <div class="label">Válidos</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-danger);"><?= number_format($totalInvalid) ?></div>
                                            <div class="label">Inválidos</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-warning);"><?= number_format($totalMissing) ?></div>
                                            <div class="label">Nuevos (para sinc.)</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <div class="num" style="color:var(--nx-success);"><?= number_format($totalInserted) ?></div>
                                            <div class="label">Insertados</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-7">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3"><i class="ti ti-info-circle" style="color:var(--nx-success);"></i> Instrucciones</h5>
                                <ol class="small text-muted mb-0" style="line-height:1.8;">
                                    <li><strong>Selecciona</strong> un archivo <code>.sql</code> existente o <strong>sube</strong> uno nuevo.</li>
                                    <li>La herramienta <strong>importa</strong> el SQL a una BD temporal, <strong>valida</strong> los datos y <strong>compara</strong> contra tu BD local.</li>
                                    <li>Los registros <strong>inválidos</strong> se marcan con ❌ y no se sincronizarán.</li>
                                    <li>Revisa las tablas con errores haciendo clic en el número de errores.</li>
                                    <li>Usa <i class="ti ti-transfer"></i> <strong>Sincronizar</strong> para insertar solo registros válidos y nuevos.</li>
                                    <li>Al terminar, limpia la BD temporal.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($hasTempDb && !empty($tableStats)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h5 class="card-title mb-0"><i class="ti ti-report-analytics" style="color:var(--nx-primary);"></i> Resultados por Tabla</h5>
                                    <div class="d-flex gap-2 align-items-center">
                                        <?php if ($totalMissing > 0 && ($totalMissing - $totalInvalid) > 0): ?>
                                        <form method="POST" class="d-inline" id="syncAllForm">
                                            <input type="hidden" name="action" value="sync_all">
                                            <button type="submit" class="btn btn-success btn-sm" id="syncAllBtn">
                                                <i class="ti ti-transfer"></i> Sincronizar válidos (<?= number_format($totalMissing) ?>)
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
                                                <th style="text-align:right;">Reg. SQL</th>
                                                <th style="text-align:right;">Válidos</th>
                                                <th style="text-align:right;">Inválidos</th>
                                                <th style="text-align:right;">Reg. Local</th>
                                                <th style="text-align:right;">Nuevos</th>
                                                <th style="text-align:right;">Insert.</th>
                                                <th>Estado</th>
                                                <th style="text-align:center;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $idx = 0; foreach ($tableStats as $table => $r): $idx++; ?>
                                            <tr class="<?= ($r['invalid_count'] ?? 0) > 0 ? 'table-danger' : (($r['missing_count'] ?? 0) > 0 ? 'table-warning' : '') ?>">
                                                <td class="text-muted"><?= $idx ?></td>
                                                <td><strong><?= htmlspecialchars($table) ?></strong></td>
                                                <td style="text-align:right;"><?= number_format($r['count_sql']) ?></td>
                                                <td style="text-align:right;">
                                                    <?php if (($r['valid_count'] ?? 0) > 0): ?>
                                                        <span style="color:var(--nx-success);"><?= number_format($r['valid_count']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:right;">
                                                    <?php if (($r['invalid_count'] ?? 0) > 0): ?>
                                                        <a href="javascript:void(0)" onclick="previewErrors('<?= htmlspecialchars($table) ?>')" class="text-danger fw-bold" title="Ver errores">
                                                            <?= number_format($r['invalid_count']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="color:var(--nx-success);">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:right;">
                                                    <?= isset($r['count_local']) ? number_format($r['count_local']) : '<span class="text-muted">N/A</span>' ?>
                                                </td>
                                                <td style="text-align:right;">
                                                    <?php if (($r['missing_count'] ?? 0) > 0): ?>
                                                    <span class="fw-bold" style="color:var(--nx-warning);"><?= number_format($r['missing_count']) ?></span>
                                                    <?php else: ?>
                                                    <span style="color:var(--nx-success);">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:right;">
                                                    <?php if (($r['inserted_count'] ?? 0) > 0): ?>
                                                    <span style="color:var(--nx-success);"><?= number_format($r['inserted_count']) ?></span>
                                                    <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (($r['invalid_count'] ?? 0) > 0): ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border-0 badge-status">Con errores</span>
                                                    <?php elseif (($r['inserted_count'] ?? 0) > 0): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border-0 badge-status">Sincronizado</span>
                                                    <?php elseif (($r['missing_count'] ?? 0) > 0): ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning border-0 badge-status">Pendiente</span>
                                                    <?php elseif (!($r['exists_local'] ?? true)): ?>
                                                        <span class="badge bg-info bg-opacity-10 text-info border-0 badge-status">Nueva tabla</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border-0 badge-status">Completo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <?php if (($r['invalid_count'] ?? 0) > 0): ?>
                                                        <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="previewErrors('<?= htmlspecialchars($table) ?>')" title="Ver errores">
                                                            <i class="ti ti-alert-triangle"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if (($r['missing_count'] ?? 0) > 0 && ($r['inserted_count'] ?? 0) == 0): ?>
                                                        <button class="btn btn-sm btn-outline-info py-0 px-1" onclick="previewMissing('<?= htmlspecialchars($table) ?>')" title="Ver registros nuevos">
                                                            <i class="ti ti-eye"></i>
                                                        </button>
                                                        <?php if (($r['invalid_count'] ?? 0) == 0 || (($r['missing_valid_ids'] ?? []) && ($r['invalid_count'] ?? 0) > 0)): ?>
                                                        <form method="POST" class="d-inline sync-table-form">
                                                            <input type="hidden" name="action" value="sync_table">
                                                            <input type="hidden" name="sync_table" value="<?= htmlspecialchars($table) ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-1" title="Sincronizar válidos">
                                                                <i class="ti ti-transfer"></i>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Preview: Errors -->
                                            <tr class="preview-row" id="errors-<?= htmlspecialchars($table) ?>" style="display:none;">
                                                <td colspan="10" class="p-0">
                                                    <div class="preview-wrap p-3">
                                                        <div class="preview-loading">
                                                            <div class="spinner"></div>
                                                            <p class="text-muted small">Cargando errores...</p>
                                                        </div>
                                                        <div class="preview-content"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Preview: Missing -->
                                            <tr class="preview-row" id="missing-<?= htmlspecialchars($table) ?>" style="display:none;">
                                                <td colspan="10" class="p-0">
                                                    <div class="preview-wrap p-3">
                                                        <div class="preview-loading">
                                                            <div class="spinner"></div>
                                                            <p class="text-muted small">Cargando registros nuevos...</p>
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

                <?php if (!empty($validationDetails)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3"><i class="ti ti-checklist" style="color:var(--nx-primary);"></i> Validaciones Ejecutadas</h5>
                                <div class="val-summary mb-3">
                                    <?php
                                    $ruleLabels = [
                                        'enums' => 'ENUMs',
                                        'positive_amounts' => 'Montos positivos',
                                        'intra_fks' => 'FKs intra-SQL',
                                        'movement_consistency' => 'Consistencia movs.',
                                        'currency_coherence' => 'Coherencia moneda',
                                        'dates' => 'Fechas',
                                        'inventory_consistency' => 'Inventario',
                                    ];
                                    foreach ($validationDetails as $rule => $detail):
                                        $passed = $detail['total'] === 0;
                                    ?>
                                    <span class="val-item <?= $passed ? 'val-pass' : 'val-fail' ?>">
                                        <i class="ti ti-<?= $passed ? 'check' : 'x' ?>"></i>
                                        <?= $ruleLabels[$rule] ?? $rule ?>: <?= $detail['total'] ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="ti ti-info-circle"></i> Pasa el mouse sobre los badges para ver el detalle. Haz clic en el número de errores en la tabla para ver registros específicos.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
    if (!confirm('¿Sincronizar TODAS las tablas con registros nuevos?\nSolo se insertarán registros que pasaron validación.')) return false;
    $('#syncAllBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sincronizando...');
});

$('.sync-table-form').on('submit', function() {
    const btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
});

let previewOpen = null;

function previewErrors(table) {
    const row = $('#errors-' + table);
    const wrap = row.find('.preview-wrap');
    const loading = row.find('.preview-loading');
    const content = row.find('.preview-content');

    if (row.is(':visible')) {
        row.hide();
        wrap.hide();
        return;
    }

    closeOtherPreviews(table, 'errors');
    row.show();
    wrap.show();
    loading.show();
    content.empty();

    $.get('<?= $scriptUrl ?>?action=preview_errors&table=' + encodeURIComponent(table), function(data) {
        loading.hide();
        if (data.error) {
            content.html('<div class="alert alert-danger">' + data.error + '</div>');
            return;
        }
        if (!data.errors || data.errors.length === 0) {
            content.html('<div class="alert alert-info">No hay errores de validación en esta tabla.</div>');
            return;
        }
        let html = '<p class="small text-muted mb-2">Total: ' + data.count_sql + ' registros, ' +
                   '<span class="text-danger">' + data.invalid_count + ' inválidos</span>, ' +
                   '<span class="text-success">' + data.valid_count + ' válidos</span></p>';
        html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr>' +
                '<th>ID</th><th>Campo</th><th>Valor</th><th>Regla</th><th>Mensaje</th></tr></thead><tbody>';
        data.errors.forEach(function(err) {
            html += '<tr class="table-danger">' +
                    '<td>' + err.id_value + '</td>' +
                    '<td><span class="field">' + escapeHtml(err.field) + '</span></td>' +
                    '<td><code>' + escapeHtml(String(err.value)) + '</code></td>' +
                    '<td>' + escapeHtml(err.rule) + '</td>' +
                    '<td class="small">' + escapeHtml(err.message) + '</td>' +
                    '</tr>';
        });
        html += '</tbody></table></div>';
        content.html(html);
    }).fail(function() {
        loading.hide();
        content.html('<div class="alert alert-danger">Error al cargar errores.</div>');
    });
}

function previewMissing(table) {
    const row = $('#missing-' + table);
    const wrap = row.find('.preview-wrap');
    const loading = row.find('.preview-loading');
    const content = row.find('.preview-content');

    if (row.is(':visible')) {
        row.hide();
        wrap.hide();
        return;
    }

    closeOtherPreviews(table, 'missing');
    row.show();
    wrap.show();
    loading.show();
    content.empty();

    $.get('<?= $scriptUrl ?>?action=preview_missing&table=' + encodeURIComponent(table), function(data) {
        loading.hide();
        if (data.error) {
            content.html('<div class="alert alert-danger">' + data.error + '</div>');
            return;
        }
        if (!data.rows || data.rows.length === 0) {
            content.html('<div class="alert alert-info">No hay registros nuevos para mostrar.</div>');
            return;
        }
        let html = '<p class="small text-muted mb-2">Mostrando ' + data.rows.length + ' de ' + data.missing_count + ' registros nuevos';
        if (data.missing_valid_count < data.missing_count) {
            html += ' (<span class="text-success">' + data.missing_valid_count + ' válidos</span>)';
        }
        html += '</p>';
        html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr>';
        data.columns.forEach(function(col) {
            html += '<th>' + escapeHtml(col) + '</th>';
        });
        html += '</tr></thead><tbody>';
        data.rows.forEach(function(row) {
            html += '<tr>';
            data.columns.forEach(function(col) {
                let val = row[col];
                if (val === null) val = '<span class="text-muted">NULL</span>';
                else if (typeof val === 'number') val = val.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 6});
                else val = escapeHtml(String(val)).substring(0, 120);
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
        content.html('<div class="alert alert-danger">Error al cargar registros.</div>');
    });
}

function closeOtherPreviews(currentTable, type) {
    const allPreviews = document.querySelectorAll('.preview-row');
    allPreviews.forEach(function(el) {
        if (el.style.display !== 'none') {
            el.style.display = 'none';
            $(el).find('.preview-wrap').hide();
        }
    });
}

function escapeHtml(str) {
    const div = document.createElement('span');
    div.textContent = str;
    return div.innerHTML;
}
</script>
</body>
</html>
