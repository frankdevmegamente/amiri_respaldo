<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();
$pdo = getPDO();

try {
    // GET: listar o buscar por id
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM directorio_depositos WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'No encontrado']);
                exit;
            }
            echo json_encode($row);
            exit;
        }
        // todos=1 incluye inactivos
        if (isset($_GET['todos'])) {
            $stmt = $pdo->query("SELECT * FROM directorio_depositos ORDER BY nombre ASC");
        } else {
            $stmt = $pdo->query("SELECT * FROM directorio_depositos WHERE estado = 'activo' ORDER BY nombre ASC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // POST: crear o actualizar
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $banco = trim($_POST['banco'] ?? '');
        $cuenta = trim($_POST['cuenta'] ?? '');
        $clabe = trim($_POST['clabe'] ?? '');

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre obligatorio']);
            exit;
        }

        // Verificar duplicado (nombre + cuenta + banco)
        $chk = $pdo->prepare("SELECT id FROM directorio_depositos WHERE LOWER(nombre)=LOWER(?) AND cuenta=? AND banco=? AND id<>COALESCE(?,0)");
        $chk->execute([$nombre, $cuenta, $banco, $id ?: 0]);
        if ($chk->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe un cliente con esos datos']);
            exit;
        }

        if ($id) {
            $prev = $pdo->prepare("SELECT * FROM directorio_depositos WHERE id=?");
            $prev->execute([$id]);
            $ant = $prev->fetch(PDO::FETCH_ASSOC);
            $pdo->prepare("UPDATE directorio_depositos SET nombre=?, banco=?, cuenta=?, clabe=? WHERE id=?")
                ->execute([$nombre, $banco ?: null, $cuenta ?: null, $clabe ?: null, $id]);
            echo json_encode(['success' => true, 'id' => $id]);
            exit;
        } else {
            $pdo->prepare("INSERT INTO directorio_depositos (nombre, banco, cuenta, clabe, estado) VALUES (?,?,?,?, 'activo')")
                ->execute([$nombre, $banco ?: null, $cuenta ?: null, $clabe ?: null]);
            $nid = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'id' => $nid]);
            exit;
        }
    }

    // DELETE: soft delete (o reactivar con ?reactivar=1)
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $data);
        $id = $_GET['id'] ?? $data['id'] ?? null;
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }
        $reactivar = isset($_GET['reactivar']) || isset($data['reactivar']);
        $nuevoEstado = $reactivar ? 'activo' : 'inactivo';
        $pdo->prepare("UPDATE directorio_depositos SET estado = ? WHERE id = ?")->execute([$nuevoEstado, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
