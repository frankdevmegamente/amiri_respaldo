<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/cache.php';



initApi();

requireAuth(['admin', 'usuario']);

if (($_SESSION['usuario_rol'] ?? '') === 'usuario' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonError('Solo lectura', 403);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$pdo = getPDO();



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Usar cache para lista de tipos de gasto (datos estáticos)

    $data = SimpleCache::remember('categorias_gasto_list', function() use ($pdo) {

        if (isset($_GET['id'])) {

            $stmt = $pdo->prepare("SELECT * FROM categorias_gasto WHERE id = ?");

            $stmt->execute([$_GET['id']]);

            return $stmt->fetch();

        } else {

            $stmt = $pdo->query("SELECT * FROM categorias_gasto WHERE activo = 1 ORDER BY nombre");

            return $stmt->fetchAll();

        }

    }, 3600); // Cache por 1 hora

    

    jsonResponse($data);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');

    $id = $_POST['id'] ?? null;



    // Validar que el nombre no esté vacío

    if (empty($nombre)) {

        jsonError('El nombre es obligatorio', 400);

    }



    // Validar que no exista otro tipo con el mismo nombre

    $stmt = $pdo->prepare("SELECT id FROM categorias_gasto WHERE nombre = ?");

    $stmt->execute([$nombre]);

    if ($stmt->fetch()) {

        jsonError('Ya existe un tipo de gasto con ese nombre', 400);

    }



    // Insertar o actualizar

    if ($id) {

        $stmt = $pdo->prepare("UPDATE categorias_gasto SET nombre=? WHERE id=?");

        $stmt->execute([$nombre, $id]);

    } else {

        $stmt = $pdo->prepare("INSERT INTO categorias_gasto (nombre) VALUES (?)");

        $stmt->execute([$nombre]);

    }



    // Invalidar cache después de modificar

    SimpleCache::invalidate('categorias_gasto_list');

    jsonResponse(['success' => true]);

    

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id) {

        jsonError('ID inválido', 400);

    }

    

    // Soft-delete: desactivar tipo de gasto (NUNCA borrar)

    $stmt = $pdo->prepare("UPDATE categorias_gasto SET activo = 0, eliminado_en = NOW() WHERE id = ?");

    $stmt->execute([$id]);

    

    // Invalidar cache después de eliminar

    SimpleCache::invalidate('categorias_gasto_list');

    jsonResponse(['success' => true]);

}

