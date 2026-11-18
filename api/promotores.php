<?php

require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/api-middleware.php';

header('Content-Type: application/json');

$esAdmin = (($_SESSION['usuario_rol'] ?? '') === 'admin');
$esPromotor = (($_SESSION['usuario_rol'] ?? '') === 'promotor');
$esUsuario = (($_SESSION['usuario_rol'] ?? '') === 'usuario');

if (!isset($_SESSION['usuario_id']) || (!$esAdmin && !$esPromotor && !$esUsuario)) {

    http_response_code(401);

    echo json_encode(['error' => 'No autorizado']);

    exit;

}

if ($esUsuario && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo lectura']);
    exit;
}

$ownPromotorId = $esPromotor ? currentPromotorId() : null;
if ($esPromotor && !$ownPromotorId) {
    http_response_code(403);
    echo json_encode(['error' => 'Cuenta de promotor no vinculada']);
    exit;
}



$requireEnv = __DIR__ . '/../src/config/env.php';

if (file_exists($requireEnv)) {

    require_once $requireEnv;

}

require_once __DIR__ . '/../src/config/db.php';

$pdo = getPDO();



// GET: Listar o ver uno

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['resumen'])) {
        if ($esPromotor) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }
        $stmt = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(p.activo = 1) AS activos,
                SUM(p.activo = 0) AS inactivos,
                COALESCE(SUM(c.saldo_actual), 0) AS saldo_comisiones
            FROM promotores p
            LEFT JOIN cuentas c ON p.cuenta_id = c.id
        ");
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $r['total'] = (int)$r['total'];
        $r['activos'] = (int)$r['activos'];
        $r['inactivos'] = (int)$r['inactivos'];
        $r['saldo_comisiones'] = (float)$r['saldo_comisiones'];
        echo json_encode($r);
        exit;
    }

    if (isset($_GET['id'])) {

        // Un promotor solo puede ver su propio registro
        if ($esPromotor && (int)$_GET['id'] !== (int)$ownPromotorId) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }

        $stmt = $pdo->prepare("

            SELECT p.*, c.nombre AS cuenta_nombre, c.saldo_actual 

            FROM promotores p

            LEFT JOIN cuentas c ON p.cuenta_id = c.id

            WHERE p.id = ?

        ");

        $stmt->execute([$_GET['id']]);

        echo json_encode($stmt->fetch());

    } else {

        // Un promotor no puede listar a otros promotores
        if ($esPromotor) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }

        // Si se solicita solo activos

        if (isset($_GET['activos']) && $_GET['activos'] == '1') {

            $stmt = $pdo->query("

                SELECT p.*, c.nombre AS cuenta_nombre, c.saldo_actual 

                FROM promotores p

                LEFT JOIN cuentas c ON p.cuenta_id = c.id

                WHERE p.activo = 1 

                ORDER BY p.nombre

            ");

        } else {

            // Si no, mostrar todos

            $stmt = $pdo->query("

                SELECT p.*, c.nombre AS cuenta_nombre, c.saldo_actual 

                FROM promotores p

                LEFT JOIN cuentas c ON p.cuenta_id = c.id

                ORDER BY p.nombre

            ");

        }

        echo json_encode($stmt->fetchAll());

    }

}

// POST: Crear, actualizar o eliminar (solo admin)

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$esAdmin) {
        http_response_code(403);
        echo json_encode(['error' => 'Solo el administrador puede modificar promotores']);
        exit;
    }

    // Detectar si es una solicitud DELETE simulada (viene como JSON)

    $input = json_decode(file_get_contents('php://input'), true);



    if (isset($input['_method']) && $input['_method'] === 'DELETE') {

        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {

            http_response_code(400);

            echo json_encode(['error' => 'ID inválido']);

            exit;

        }



        try {

            // Primero obtener el cuenta_id asociado

            $stmt = $pdo->prepare("SELECT cuenta_id FROM promotores WHERE id = ?");

            $stmt->execute([$id]);

            $cuenta_id = $stmt->fetchColumn();

            

            // Si tiene cuenta asociada, desactivarla (soft-delete)

            if ($cuenta_id) {

                // Marcar movimientos como anulados (NUNCA borrar movimientos financieros)

                $stmt = $pdo->prepare("UPDATE movimientos SET estado = 'anulado', activo = 0 WHERE cuenta_origen_id = ? OR cuenta_destino_id = ?");

                $stmt->execute([$cuenta_id, $cuenta_id]);

                

                // Desactivar la cuenta (soft-delete)

                $stmt = $pdo->prepare("UPDATE cuentas SET activo = 0 WHERE id = ?");

                $stmt->execute([$cuenta_id]);

            }

            

            // Desactivar el promotor (soft-delete, NUNCA borrar)

            $stmt = $pdo->prepare("UPDATE promotores SET activo = 0 WHERE id = ?");

            $stmt->execute([$id]);

            

            echo json_encode(['success' => true]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => 'Error al eliminar promotor: ' . $e->getMessage()]);

        }

    }

    // Si no es DELETE simulado, leer como FormData normal

    else {

        $id = $_POST['id'] ?? null;

        $nombre = trim($_POST['nombre'] ?? '');

        $telefono = $_POST['telefono'] ?? null;

        $email = $_POST['email'] ?? null;

        $activo = filter_var($_POST['activo'] ?? 0, FILTER_VALIDATE_INT);

        if ($activo !== 1) $activo = 0;



        if (empty($nombre)) {

            http_response_code(400);

            echo json_encode(['error' => 'El nombre es obligatorio']);

            exit;

        }



        if ($id) {

            $stmt = $pdo->prepare("UPDATE promotores SET nombre=?, telefono=?, email=?, activo=? WHERE id=?");

            $stmt->execute([$nombre, $telefono, $email, $activo, $id]);
            $promotor_id = $id;

            // Vincular usuario existente si se indicó
            $vincular_usuario_id = filter_var($_POST['vincular_usuario_id'] ?? null, FILTER_VALIDATE_INT);
            if ($vincular_usuario_id) {
                $chk = $pdo->prepare("SELECT id, promotor_id FROM usuarios WHERE id = ? AND activo = 1");
                $chk->execute([$vincular_usuario_id]);
                $u = $chk->fetch();
                if ($u && empty($u['promotor_id'])) {
                    $pdo->prepare("UPDATE usuarios SET promotor_id = ?, rol = 'promotor' WHERE id = ?")->execute([$promotor_id, $vincular_usuario_id]);
                }
            }

        } else {

            // CREAR: Primero insertar el promotor

            $stmt = $pdo->prepare("INSERT INTO promotores (nombre, telefono, email, activo) VALUES (?, ?, ?, ?)");

            $stmt->execute([$nombre, $telefono, $email, $activo]);

            $promotor_id = $pdo->lastInsertId();

            

            // CREAR AUTOMÁTICAMENTE LA CAJA PARA COMISIONES

            $nombre_cuenta = "Comisiones - " . $nombre;

            $stmt = $pdo->prepare("

                INSERT INTO cuentas (nombre, tipo_cuenta, tipo_moneda, saldo_actual, activo)

                VALUES (?, 5, 'MXN', 0, 1)

            ");

            $stmt->execute([$nombre_cuenta]);

            $cuenta_id = $pdo->lastInsertId();

            

            // Actualizar el promotor con el ID de la cuenta

            $stmt = $pdo->prepare("UPDATE promotores SET cuenta_id = ? WHERE id = ?");

            $stmt->execute([$cuenta_id, $promotor_id]);

        }

        // Crear acceso de usuario para el promotor (opcional, solo admin)
        $crear_usuario = !empty($_POST['crear_usuario']);
        $usuario_email = trim($_POST['usuario_email'] ?? '');
        $usuario_password = $_POST['usuario_password'] ?? '';
        if ($crear_usuario && $usuario_email !== '' && $usuario_password !== '') {
            $chk = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1");
            $chk->execute([$usuario_email]);
            if (!$chk->fetch()) {
                $chk2 = $pdo->prepare("SELECT id FROM usuarios WHERE promotor_id = ? AND activo = 1");
                $chk2->execute([$promotor_id]);
                if (!$chk2->fetch()) {
                    $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, promotor_id) VALUES (?, ?, ?, 'promotor', ?)")
                        ->execute([$nombre, $usuario_email, password_hash($usuario_password, PASSWORD_BCRYPT), $promotor_id]);
                }
            }
        }

        echo json_encode(['success' => true, 'promotor_id' => $promotor_id ?? null]);

    }

}

