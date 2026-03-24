<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/cache.php';

initApi();
requireAdmin();
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Usar cache para lista de servicios (datos estáticos)
    $data = SimpleCache::remember('servicios_list', function() use ($pdo) {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            return $stmt->fetch();
        } else {
            $stmt = $pdo->query("SELECT * FROM servicios ORDER BY nombre");
            return $stmt->fetchAll();
        }
    }, 3600); // Cache por 1 hora
    
    jsonResponse($data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? null;
    $activo = filter_var($_POST['activo'] ?? 1, FILTER_VALIDATE_BOOLEAN);

    if (empty($nombre) || !in_array($tipo_movimiento, ['entrada', 'salida'])) {
        jsonError('Datos inválidos', 400);
    }

    if ($id && $id > 0) {
        // Actualizar (editar)
        $stmt = $pdo->prepare("UPDATE servicios SET nombre=?, descripcion=?, tipo_movimiento=?, activo=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $tipo_movimiento, $activo, $id]);
    } else {
        // Crear nuevo
        $stmt = $pdo->prepare("INSERT INTO servicios (nombre, descripcion, tipo_movimiento, activo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $tipo_movimiento, $activo]);
    }
    
    // Invalidar cache después de modificar
    SimpleCache::invalidate('servicios_list');
    jsonResponse(['success' => true, 'message' => $id ? 'Servicio actualizado' : 'Servicio creado']);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $datos);
    $id = filter_var($datos['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        jsonError('ID no proporcionado o inválido', 400);
    }

    // Soft-delete: desactivar servicio (NUNCA borrar)
    $stmt = $pdo->prepare("UPDATE servicios SET activo = 0, eliminado_en = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    // Invalidar cache después de eliminar
    SimpleCache::invalidate('servicios_list');
    jsonResponse(['success' => true, 'message' => 'Servicio eliminado']);
}
