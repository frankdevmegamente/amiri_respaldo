<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';

initApi();
requireAdminOrPromotor();

$pdo = getPDO();
$esAdmin = (($_SESSION['usuario_rol'] ?? '') === 'admin');
$ownPromotorId = currentPromotorId();

// Resolver promotor objetivo: admin puede pasar ?id, promotor siempre el propio
$promotorId = null;
if ($esAdmin && isset($_GET['id'])) {
    $promotorId = filter_var($_GET['id'], FILTER_VALIDATE_INT) ?: null;
}
if (!$promotorId && isset($_POST['promotor_id']) && $esAdmin) {
    $promotorId = filter_var($_POST['promotor_id'], FILTER_VALIDATE_INT) ?: null;
}
if (!$promotorId) {
    $promotorId = $esAdmin ? null : $ownPromotorId;
}
if (!$esAdmin) {
    requirePromotorSelf($promotorId);
}
if (!$promotorId) {
    jsonError('ID de promotor requerido', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("
        SELECT p.id, p.nombre, p.telefono, p.email, p.activo, p.creado_en,
               c.nombre AS cuenta_nombre, c.saldo_actual,
               u.id AS usuario_id, u.email AS usuario_email
        FROM promotores p
        LEFT JOIN cuentas c ON c.id = p.cuenta_id
        LEFT JOIN usuarios u ON u.promotor_id = p.id AND u.activo = 1
        WHERE p.id = ?
    ");
    $stmt->execute([$promotorId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        jsonError('Promotor no encontrado', 404);
    }
    jsonResponse($row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Edición limitada: teléfono del promotor + nombre/email/password del usuario propio.
    // Admin puede editar teléfono de cualquiera; promotor solo el suyo.
    $telefono = trim($_POST['telefono'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($telefono !== '' && strlen($telefono) > 20) {
        jsonError('Teléfono inválido', 400);
    }
    if ($nombre !== '' && (strlen($nombre) < 3 || strlen($nombre) > 100)) {
        jsonError('Nombre inválido', 400);
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonError('Email inválido', 400);
    }
    if ($password !== '' && strlen($password) < 6) {
        jsonError('La contraseña debe tener al menos 6 caracteres', 400);
    }

    // Actualizar teléfono en promotores
    if ($telefono !== '' || $esAdmin) {
        $stmt = $pdo->prepare("UPDATE promotores SET telefono = ? WHERE id = ?");
        $stmt->execute([$telefono !== '' ? $telefono : null, $promotorId]);
    }

    // Actualizar datos del usuario vinculado (solo si es el propio usuario o admin)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE promotor_id = ? AND activo = 1");
    $stmt->execute([$promotorId]);
    $uid = $stmt->fetchColumn();
    if ($uid && ($esAdmin || (int)$uid === (int)($_SESSION['usuario_id'] ?? 0))) {
        if ($email !== '') {
            $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1 AND id != ?");
            $chk->execute([$email, $uid]);
            if ($chk->fetch()) {
                jsonError('El email ya está en uso', 400);
            }
        }
        $sets = [];
        $params = [];
        if ($nombre !== '') { $sets[] = "nombre = ?"; $params[] = $nombre; }
        if ($email !== '') { $sets[] = "email = ?"; $params[] = $email; }
        if ($password !== '') { $sets[] = "password = ?"; $params[] = password_hash($password, PASSWORD_BCRYPT); }
        if ($sets) {
            $params[] = $uid;
            $pdo->prepare("UPDATE usuarios SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
            if (!$esAdmin) {
                if ($nombre !== '') $_SESSION['usuario_nombre'] = $nombre;
                if ($email !== '') $_SESSION['usuario_email'] = $email;
            }
        }
    }

    registrarAuditoria($pdo, [
        'accion' => 'UPDATE',
        'modulo' => 'mi-cuenta-promotor',
        'tabla' => 'promotores',
        'registro_id' => $promotorId,
        'descripcion' => 'Actualizó su perfil de promotor'
    ]);

    $stmt = $pdo->prepare("
        SELECT p.id, p.nombre, p.telefono, p.email, p.activo, c.nombre AS cuenta_nombre, c.saldo_actual
        FROM promotores p LEFT JOIN cuentas c ON c.id = p.cuenta_id WHERE p.id = ?
    ");
    $stmt->execute([$promotorId]);
    jsonResponse($stmt->fetch(PDO::FETCH_ASSOC));
}

jsonError('Método no permitido', 405);
