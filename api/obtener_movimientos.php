<?php
require_once __DIR__ . '/../src/config/api-middleware.php'; // Usa el de dashboard.php
require_once __DIR__ . '/../src/config/db.php';

if (ob_get_length()) ob_clean(); // Limpia cualquier espacio en blanco accidental
header('Content-Type: application/json; charset=utf-8');

$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? '';

$columnas = ['promotor' => 'promotor_id', 'cliente' => 'cliente_id', 'proveedor' => 'proveedor_id', 'prestamista' => 'prestamista_id'];

if (!isset($columnas[$tipo]) || !$id) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getPDO();
    $col = $columnas[$tipo];
    $stmt = $pdo->prepare("SELECT m.*, s.nombre as servicio FROM movimientos m LEFT JOIN servicios s ON s.id = m.servicio_id WHERE m.$col = ? ORDER BY m.fecha DESC");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}