<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';
require_once __DIR__ . '/../src/config/validator.php';

initApi();
requireAuth(['admin', 'usuario', 'prestamista']);
$pdo = getPDO();
$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT id, nombre, email, rol, creado_en FROM usuarios WHERE id = ? AND activo = 1');
    $stmt->execute([$usuarioId]);
    jsonResponse($stmt->fetch() ?: []);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? null;

    try {
        $validator = new Validator();
        $validator->required($_POST, ['nombre', 'email']);
        $validator->length($nombre, 3, 100);
        $validator->email($email);

        if ($password !== null && $password !== '') {
            $validator->length($password, 6, 255);
        }
    } catch (ValidationException $e) {
        jsonError($e->getMessage(), 400, $e->getErrors());
    }

    $uniqueCheck = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND activo = 1 AND id != ?');
    $uniqueCheck->execute([$email, $usuarioId]);
    if ($uniqueCheck->fetch()) {
        jsonError('El email ya está en uso', 400, ['email' => 'Este correo ya está registrado']);
    }

    $datos_anteriores = obtenerDatosAnteriores($pdo, 'usuarios', $usuarioId);

    if ($password !== null && $password !== '') {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, email=?, password=? WHERE id=?');
        $stmt->execute([$nombre, $email, $hashed, $usuarioId]);

        registrarAuditoria($pdo, [
            'accion' => 'UPDATE',
            'modulo' => 'perfil',
            'tabla' => 'usuarios',
            'registro_id' => $usuarioId,
            'descripcion' => "Actualizó su perfil (incluyó cambio de contraseña)",
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => [
                'nombre' => $nombre,
                'email' => $email,
                'password' => '[ACTUALIZADA]'
            ]
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, email=? WHERE id=?');
        $stmt->execute([$nombre, $email, $usuarioId]);

        registrarAuditoria($pdo, [
            'accion' => 'UPDATE',
            'modulo' => 'perfil',
            'tabla' => 'usuarios',
            'registro_id' => $usuarioId,
            'descripcion' => 'Actualizó su perfil',
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => [
                'nombre' => $nombre,
                'email' => $email
            ]
        ]);
    }

    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_email'] = $email;

    $stmt = $pdo->prepare('SELECT id, nombre, email, rol, creado_en FROM usuarios WHERE id = ?');
    $stmt->execute([$usuarioId]);
    jsonResponse($stmt->fetch());
}

jsonError('Método no permitido', 405);
