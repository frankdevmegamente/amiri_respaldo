<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/config/validator.php';



initApi();

requireAdmin();

$pdo = getPDO();



// GET

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['id'])) {

        $stmt = $pdo->prepare("SELECT id, nombre, email, rol, creado_en FROM usuarios WHERE id = ? AND activo = 1");

        $stmt->execute([$_GET['id']]);

        echo json_encode($stmt->fetch() ?: []);

        exit;

    }



    $stmt = $pdo->query("SELECT id, nombre, email, rol, creado_en FROM usuarios WHERE activo = 1 ORDER BY creado_en DESC");

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



    // Validar datos de entrada con Validator

    try {

        $validator = new Validator();

        $validator->required($_POST, ['nombre', 'email', 'rol']);

        $validator->length($nombre, 3, 100);

        $validator->email($email);

        $validator->enum($rol, ['admin', 'prestamista', 'usuario']);

        

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

            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");

            $stmt->execute([$nombre, $email, $hashed, $rol, $id]);

            

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

            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");

            $stmt->execute([$nombre, $email, $rol, $id]);

            

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

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");

        $stmt->execute([$nombre, $email, $hashed, $rol]);

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



    $stmt = $pdo->prepare("SELECT id, nombre, email, rol, creado_en FROM usuarios WHERE id = ?");

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

