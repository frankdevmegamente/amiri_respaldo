<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre']);
    $banco = trim($_POST['banco']);
    $cuenta = trim($_POST['cuenta']);
    $clabe = trim($_POST['clabe']);

    if (!$id || empty($nombre)) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE directorio_depositos SET nombre = ?, banco = ?, cuenta = ?, clabe = ? WHERE id = ?");
        $stmt->execute([$nombre, $banco, $cuenta, $clabe, $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}