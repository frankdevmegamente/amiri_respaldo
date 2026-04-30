<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no válido']);
    exit;
}

$filas = json_decode(file_get_contents('php://input'), true);

if (!$filas || !is_array($filas)) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos válidos']);
    exit;
}

$pdo = getPDO();
$checkStmt = $pdo->prepare("SELECT id, estado FROM directorio_depositos WHERE nombre = ? AND cuenta = ? AND banco = ? LIMIT 1");
$insertStmt = $pdo->prepare("INSERT INTO directorio_depositos (nombre, banco, cuenta, clabe, estado) VALUES (?, ?, ?, ?, 'activo')");
$updateStmt = $pdo->prepare("UPDATE directorio_depositos SET estado = 'activo', clabe = ? WHERE id = ?");

$insertados = 0; $reactivados = 0; $duplicados = 0;

foreach ($filas as $fila) {
    // Si la fila está vacía, saltarla
    if (empty($fila) || !isset($fila[0])) continue;

    $nombre = trim($fila[0] ?? '');
    $banco  = trim($fila[1] ?? '');
    $cuenta = trim($fila[2] ?? '');
    $clabe  = trim($fila[3] ?? '');

    $checkStmt->execute([$nombre, $cuenta, $banco]);
    $clienteExistente = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($clienteExistente) {
        if ($clienteExistente['estado'] === 'inactivo') {
            $updateStmt->execute([$clabe, $clienteExistente['id']]);
            $reactivados++;
        } else {
            $duplicados++;
        }
    } else {
        $insertStmt->execute([$nombre, $banco, $cuenta, $clabe]);
        $insertados++;
    }
}

// Puedes guardar el mensaje en la sesión para mostrarlo al recargar la página
$_SESSION['mensaje_directorio'] = "$insertados nuevos, $reactivados reactivados, $duplicados omitidos.";

echo json_encode(['success' => true]);