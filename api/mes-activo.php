<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

initApi();

function obtenerCierres() {
    require_once __DIR__ . '/../src/config/db.php';
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT mes FROM cierres_mensuales ORDER BY mes DESC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function jsonConCierres($extra = []) {
    return array_merge([
        'success' => true,
        'mes_activo' => $_SESSION['mes_activo'] ?? date('Y-m'),
        'cierres' => obtenerCierres()
    ], $extra);
}

// GET: Obtener el mes activo de la sesión y lista de meses cerrados
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(jsonConCierres());
    exit;
}

// POST: Guardar el mes activo en la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $mes = $input['mes'] ?? null;

    if (!$mes || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de mes inválido (YYYY-MM)']);
        exit;
    }

    $_SESSION['mes_activo'] = $mes;
    session_write_close();

    echo json_encode(jsonConCierres(['mes_activo' => $mes]));
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
