<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/config/validator.php';



initApi();

$esAdminUsuarios = (($_SESSION['usuario_rol'] ?? '') === 'admin');
$esPromotorUsuarios = (($_SESSION['usuario_rol'] ?? '') === 'promotor');

// Promotor solo puede consultar su propio registro; resto requiere admin
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $esPromotorUsuarios && isset($_GET['id']) && (int)$_GET['id'] === (int)($_SESSION['usuario_id'] ?? 0)) {
    // permitido, continuar sin requireAdmin
} else {
    requireAdmin();
    $esAdminUsuarios = true;
}

$pdo = getPDO();



// GET

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['resumen'])) {
        $stmt = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) AS admins,
                SUM(CASE WHEN rol = 'prestamista' THEN 1 ELSE 0 END) AS prestamistas,
                SUM(CASE WHEN rol = 'usuario' THEN 1 ELSE 0 END) AS usuarios,
                SUM(CASE WHEN rol = 'promotor' THEN 1 ELSE 0 END) AS promotores
            FROM usuarios
            WHERE activo = 1
        ");
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $r['total'] = (int)$r['total'];
        $r['admins'] = (int)$r['admins'];
        $r['prestamistas'] = (int)$r['prestamistas'];
        $r['usuarios'] = (int)$r['usuarios'];
        $r['promotores'] = (int)($r['promotores'] ?? 0);
        echo json_encode($r);
        exit;
    }

    if (isset($_GET['id'])) {

        $stmt = $pdo->prepare("SELECT id, nombre, email, rol, promotor_id, creado_en FROM usuarios WHERE id = ? AND activo = 1");

        $stmt->execute([$_GET['id']]);

        echo json_encode($stmt->fetch() ?: []);

        exit;

    }



    $stmt = $pdo->query("SELECT id, nombre, email, rol, promotor_id, creado_en FROM usuarios WHERE activo = 1 ORDER BY creado_en DESC");

    echo json_encode($stmt->fetchAll());

    exit;

}



// POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    $nombre = trim($_POST['nombre'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $password = $_POST['password'] ?? null;

    $rol = $_POST['rol'] ?? 'usuario';

    $promotor_id = filter_var($_POST['promotor_id'] ?? null, FILTER_VALIDATE_INT) ?: null;



    // Validar datos de entrada con Validator

    try {

        $validator = new Validator();

        $validator->required($_POST, ['nombre', 'email', 'rol']);

        $validator->length($nombre, 3, 100);

        $validator->email($email);

        $validator->enum($rol, ['admin', 'prestamista', 'usuario', 'promotor']);

        // Si es promotor, exigir vínculo válido
        if ($rol === 'promotor') {
            if (!$promotor_id) {
                throw new ValidationException('Debes vincular un promotor', ['promotor_id' => 'Selecciona el promotor']);
            }
            $chk = $pdo->prepare("SELECT id FROM promotores WHERE id = ? AND activo = 1");
            $chk->execute([$promotor_id]);
            if (!$chk->fetch()) {
                throw new ValidationException('Promotor no válido', ['promotor_id' => 'Promotor inexistente o inactivo']);
            }
            $dup = $pdo->prepare("SELECT id FROM usuarios WHERE promotor_id = ? AND activo = 1" . ($id ? " AND id != ?" : ""));
            $dup->execute($id ? [$promotor_id, $id] : [$promotor_id]);
            if ($dup->fetch()) {
                throw new ValidationException('Ese promotor ya tiene usuario', ['promotor_id' => 'Ya vinculado']);
            }
        } else {
            $promotor_id = null;
        }

        

        // Validar password solo si se proporciona (nuevo usuario o cambio de password)

        if (!$id || ($password !== '' && $password !== null)) {

            if (!$password || $password === '') {

                throw new ValidationException('La contraseña es requerida', ['password' => 'Este campo es requerido']);

            }

            $validator->length($password, 6, 255);

        }

    } catch (ValidationException $e) {

        jsonError($e->getMessage(), 400, $e->getErrors());

    }



    // Validar unicidad de email (excluyendo al propio usuario si es edición)

    $uniqueCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1" . ($id ? " AND id != ?" : ""));

    $uniqueCheck->execute($id ? [$email, $id] : [$email]);

    if ($uniqueCheck->fetch()) {

        jsonError('El email ya está en uso', 400, ['email' => 'Este correo ya está registrado']);

    }



    if ($id) {

        // Obtener datos anteriores

        $datos_anteriores = obtenerDatosAnteriores($pdo, 'usuarios', $id);

        

        // Actualizar

        if ($password !== '' && $password !== null) {

            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=?, promotor_id=? WHERE id=?");

            $stmt->execute([$nombre, $email, $hashed, $rol, $promotor_id, $id]);

            

            registrarAuditoria($pdo, [

                'accion' => 'UPDATE',

                'modulo' => 'usuarios',

                'tabla' => 'usuarios',

                'registro_id' => $id,

                'descripcion' => "Actualizó usuario: $nombre (incluyó cambio de contraseña)",

                'datos_anteriores' => $datos_anteriores,

                'datos_nuevos' => [

                    'nombre' => $nombre,

                    'email' => $email,

                    'rol' => $rol,

                    'password' => '[ACTUALIZADA]'

                ]

            ]);

        } else {

            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, promotor_id=? WHERE id=?");

            $stmt->execute([$nombre, $email, $rol, $promotor_id, $id]);

            

            registrarAuditoria($pdo, [

                'accion' => 'UPDATE',

                'modulo' => 'usuarios',

                'tabla' => 'usuarios',

                'registro_id' => $id,

                'descripcion' => "Actualizó usuario: $nombre",

                'datos_anteriores' => $datos_anteriores,

                'datos_nuevos' => [

                    'nombre' => $nombre,

                    'email' => $email,

                    'rol' => $rol

                ]

            ]);

        }

    } else {

        // Crear

        if (empty($password)) {

            http_response_code(400);

            echo json_encode(['error' => 'Contraseña obligatoria al crear']);

            exit;

        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, promotor_id) VALUES (?, ?, ?, ?, ?)");

        $stmt->execute([$nombre, $email, $hashed, $rol, $promotor_id]);

        $id = $pdo->lastInsertId();

        

        registrarAuditoria($pdo, [

            'accion' => 'INSERT',

            'modulo' => 'usuarios',

            'tabla' => 'usuarios',

            'registro_id' => $id,

            'descripcion' => "Creó usuario: $nombre ($rol)",

            'datos_nuevos' => [

                'id' => $id,

                'nombre' => $nombre,

                'email' => $email,

                'rol' => $rol

            ]

        ]);

    }



    $stmt = $pdo->prepare("SELECT id, nombre, email, rol, promotor_id, creado_en FROM usuarios WHERE id = ?");

    $stmt->execute([$id]);

    echo json_encode($stmt->fetch());

    exit;

}



// DELETE

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $input = json_decode(file_get_contents('php://input'), true);

    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);



    if (!$id || $id == $_SESSION['usuario_id']) {

        http_response_code(400);

        echo json_encode(['error' => 'ID inválido o no puedes eliminarte a ti mismo']);

        exit;

    }

    

    // Obtener datos antes de desactivar

    $datos_anteriores = obtenerDatosAnteriores($pdo, 'usuarios', $id);



    $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?")->execute([$id]);

    

    registrarAuditoria($pdo, [

        'accion' => 'DELETE',

        'modulo' => 'usuarios',

        'tabla' => 'usuarios',

        'registro_id' => $id,

        'descripcion' => "Desactivó usuario: {$datos_anteriores['nombre']}",

        'datos_anteriores' => $datos_anteriores

    ]);

    

    echo json_encode(['success' => true]);

    exit;

}



http_response_code(405);

echo json_encode(['error' => 'Método no permitido']);

