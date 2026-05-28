<?php

require_once __DIR__ . '/../src/config/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

// checkSessionTimeout() ya se ejecutó en session.php
// Si llegamos aquí, la sesión está activa y LAST_ACTIVITY ya se actualizó

echo json_encode(['success' => true, 'message' => 'Sesión extendida']);