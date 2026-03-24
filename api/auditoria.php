<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();
$pdo = getPDO();

try {
    // GET: Obtener estadísticas (prioridad)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['estadisticas'])) {
        // Total de registros
        $total = $pdo->query("SELECT COUNT(*) FROM auditoria")->fetchColumn();

        // Por acción
        $stmt = $pdo->query("
            SELECT accion, COUNT(*) as total
            FROM auditoria
            GROUP BY accion
            ORDER BY total DESC
        ");
        $por_accion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Por módulo
        $stmt = $pdo->query("
            SELECT modulo, COUNT(*) as total
            FROM auditoria
            GROUP BY modulo
            ORDER BY total DESC
            LIMIT 10
        ");
        $por_modulo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Por usuario
        $stmt = $pdo->query("
            SELECT u.nombre, COUNT(*) as total
            FROM auditoria a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            GROUP BY a.usuario_id
            ORDER BY total DESC
            LIMIT 10
        ");
        $por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Últimas 24 horas
        $ultimas_24h = $pdo->query("
            SELECT COUNT(*) 
            FROM auditoria 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->fetchColumn();

        echo json_encode([
            'total' => $total,
            'ultimas_24h' => $ultimas_24h,
            'por_accion' => $por_accion,
            'por_modulo' => $por_modulo,
            'por_usuario' => $por_usuario
        ]);
        exit;
    }

    // GET: Obtener registros de auditoría con filtros
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Si piden un registro específico por ID
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT a.*, u.nombre AS usuario_nombre
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            exit;
        }
        
        // Construir query con filtros
        $where = [];
        $params = [];
        
        // Filtro por usuario
        if (!empty($_GET['usuario_id'])) {
            $where[] = "a.usuario_id = ?";
            $params[] = $_GET['usuario_id'];
        }
        
        // Filtro por acción
        if (!empty($_GET['accion'])) {
            $where[] = "a.accion = ?";
            $params[] = $_GET['accion'];
        }
        
        // Filtro por módulo
        if (!empty($_GET['modulo'])) {
            $where[] = "a.modulo = ?";
            $params[] = $_GET['modulo'];
        }
        
        // Filtro por rango de fechas
        if (!empty($_GET['fecha_inicio'])) {
            $where[] = "DATE(a.created_at) >= ?";
            $params[] = $_GET['fecha_inicio'];
        }
        
        if (!empty($_GET['fecha_fin'])) {
            $where[] = "DATE(a.created_at) <= ?";
            $params[] = $_GET['fecha_fin'];
        }
        
        $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "
            SELECT 
                a.*,
                u.nombre AS usuario_nombre
            FROM auditoria a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            $whereClause
            ORDER BY a.created_at DESC
            LIMIT 1000
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener auditoría: ' . $e->getMessage()]);
}
