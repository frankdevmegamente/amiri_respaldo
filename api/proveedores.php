<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';



initApi();

requireAdmin();

$pdo = getPDO();



/* ===== GET ===== */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['resumen'])) {
        $stmt = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(activo = 1) AS activos,
                SUM(acepta_usd = 1) AS aceptan_usd,
                SUM(acepta_usdt = 1) AS aceptan_usdt
            FROM proveedores
        ");
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $r['total'] = (int)$r['total'];
        $r['activos'] = (int)$r['activos'];
        $r['aceptan_usd'] = (int)$r['aceptan_usd'];
        $r['aceptan_usdt'] = (int)$r['aceptan_usdt'];
        echo json_encode($r);
        exit;
    }

    if (isset($_GET['id'])) {

        $stmt = $pdo->prepare("

            SELECT p.*,

                   cu.nombre AS cuenta_nombre, cu.saldo_actual, cu.valor_mxn,

                   cusd.nombre  AS cuenta_usd_nombre,  cusd.saldo_actual  AS saldo_usd,  cusd.valor_mxn  AS valor_mxn_usd,

                   cusdt.nombre AS cuenta_usdt_nombre, cusdt.saldo_actual AS saldo_usdt, cusdt.valor_mxn AS valor_mxn_usdt

            FROM proveedores p

            LEFT JOIN cuentas cu    ON p.cuenta_id      = cu.id

            LEFT JOIN cuentas cusd  ON p.cuenta_usd_id  = cusd.id

            LEFT JOIN cuentas cusdt ON p.cuenta_usdt_id = cusdt.id

            WHERE p.id = ?

        ");

        $stmt->execute([$_GET['id']]);

        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));

        exit;

    }



    // Construir query base

    $sql = "SELECT p.*,

                   cu.nombre AS cuenta_nombre, cu.saldo_actual,

                   cusd.nombre AS cuenta_usd_nombre, cusd.saldo_actual AS saldo_usd, cusd.valor_mxn AS valor_mxn_usd,

                   cusdt.nombre AS cuenta_usdt_nombre, cusdt.saldo_actual AS saldo_usdt, cusdt.valor_mxn AS valor_mxn_usdt

            FROM proveedores p

            LEFT JOIN cuentas cu   ON p.cuenta_id     = cu.id

            LEFT JOIN cuentas cusd  ON p.cuenta_usd_id  = cusd.id

            LEFT JOIN cuentas cusdt ON p.cuenta_usdt_id = cusdt.id

            WHERE 1=1";

    $params = [];



    if (isset($_GET['activos']) && $_GET['activos'] == '1') {

        $sql .= " AND p.activo = 1";

    }



    // Filtrar por moneda que acepta el proveedor

    if (isset($_GET['acepta_moneda'])) {

        $m = $_GET['acepta_moneda'];

        if ($m === 'USD')  { $sql .= " AND p.acepta_usd = 1";  }

        if ($m === 'USDT') { $sql .= " AND p.acepta_usdt = 1"; }

    }

    // Backward compat: tipo_moneda

    if (isset($_GET['tipo_moneda']) && in_array($_GET['tipo_moneda'], ['USD', 'USDT'])) {

        $m = $_GET['tipo_moneda'];

        if ($m === 'USD')  { $sql .= " AND p.acepta_usd = 1";  }

        if ($m === 'USDT') { $sql .= " AND p.acepta_usdt = 1"; }

    }



    $sql .= " ORDER BY p.nombre";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    exit;

}



/* ===== POST (CREATE / UPDATE) ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    $id        = $_POST['id'] ?? null;

    $nombre    = trim($_POST['nombre']);

    $email     = trim($_POST['email'] ?? '');

    $telefono  = trim($_POST['telefono'] ?? '');

    $acepta_usd  = !empty($_POST['acepta_usd'])  ? 1 : 0;

    $acepta_usdt = !empty($_POST['acepta_usdt']) ? 1 : 0;



    if (empty($nombre)) {

        http_response_code(400);

        echo json_encode(['error' => 'El nombre es obligatorio']);

        exit;

    }

    if (!$acepta_usd && !$acepta_usdt) {

        http_response_code(400);

        echo json_encode(['error' => 'Debes seleccionar al menos una moneda (USD o USDT)']);

        exit;

    }



    // Helper para crear o reactivar una cuenta de proveedor

    $crearCuentaProveedor = function($nombreCuenta, $tipoMoneda) use ($pdo) {

        $stmtCheck = $pdo->prepare("SELECT id FROM cuentas WHERE nombre = ?");

        $stmtCheck->execute([$nombreCuenta]);

        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {

            $pdo->prepare("UPDATE cuentas SET activo = 1 WHERE id = ?")->execute([$existing['id']]);

            return $existing['id'];

        }

        $stmtC = $pdo->prepare("INSERT INTO cuentas (nombre, tipo_cuenta, tipo_moneda, saldo_actual, activo) VALUES (?, 6, ?, 0, 1)");

        $stmtC->execute([$nombreCuenta, $tipoMoneda]);

        return $pdo->lastInsertId();

    };



    try {

        if ($id) {

            $datos_anteriores = obtenerDatosAnteriores($pdo, 'proveedores', $id);



            $stmt = $pdo->prepare("

                UPDATE proveedores SET nombre = ?, email = ?, telefono = ?,

                    acepta_usd = ?, acepta_usdt = ?

                WHERE id = ?

            ");

            $stmt->execute([$nombre, $email, $telefono, $acepta_usd, $acepta_usdt, $id]);



            registrarAuditoria($pdo, [

                'accion'          => 'UPDATE',

                'modulo'          => 'proveedores',

                'tabla'           => 'proveedores',

                'registro_id'     => $id,

                'descripcion'     => "Actualizó proveedor: $nombre",

                'datos_anteriores' => $datos_anteriores,

                'datos_nuevos'    => compact('nombre','email','telefono','acepta_usd','acepta_usdt')

            ]);

        } else {

            // INSERT

            $stmt = $pdo->prepare("

                INSERT INTO proveedores (nombre, email, telefono, acepta_usd, acepta_usdt, activo)

                VALUES (?, ?, ?, ?, ?, 1)

            ");

            $stmt->execute([$nombre, $email, $telefono, $acepta_usd, $acepta_usdt]);

            $proveedor_id = $pdo->lastInsertId();



            registrarAuditoria($pdo, [

                'accion'      => 'INSERT',

                'modulo'      => 'proveedores',

                'tabla'       => 'proveedores',

                'registro_id' => $proveedor_id,

                'descripcion' => "Creó proveedor: $nombre",

                'datos_nuevos' => compact('proveedor_id','nombre','email','telefono','acepta_usd','acepta_usdt')

            ]);



            // Crear cuenta USD si aplica

            if ($acepta_usd) {

                $cuenta_usd_id = $crearCuentaProveedor('Caja Proveedor USD - ' . $nombre, 'USD');

                $pdo->prepare("UPDATE proveedores SET cuenta_usd_id = ? WHERE id = ?")->execute([$cuenta_usd_id, $proveedor_id]);

            }

            // Crear cuenta USDT si aplica

            if ($acepta_usdt) {

                $cuenta_usdt_id = $crearCuentaProveedor('Caja Proveedor USDT - ' . $nombre, 'USDT');

                $pdo->prepare("UPDATE proveedores SET cuenta_usdt_id = ? WHERE id = ?")->execute([$cuenta_usdt_id, $proveedor_id]);

            }

        }



        echo json_encode(['success' => true]);

    } catch (PDOException $e) {

        http_response_code(500);

        echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);

    }

    exit;

}



/* ===== DELETE ===== */

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    parse_str(file_get_contents("php://input"), $data);

    $id = (int)$data['id'];

    

    try {

        // Obtener datos del proveedor antes de eliminarlo/desactivarlo

        $datos_proveedor = obtenerDatosAnteriores($pdo, 'proveedores', $id);

        $nombre_proveedor = $datos_proveedor['nombre'] ?? 'Desconocido';

        

        // Soft-delete: SIEMPRE desactivar, NUNCA borrar físicamente

        $stmt = $pdo->prepare("UPDATE proveedores SET activo = 0 WHERE id = ?");

        $stmt->execute([$id]);

        

        // Desactivar cuentas asociadas (USD, USDT y legacy)

        foreach (['cuenta_id', 'cuenta_usd_id', 'cuenta_usdt_id'] as $campo) {

            if (!empty($datos_proveedor[$campo])) {

                $pdo->prepare("UPDATE cuentas SET activo = 0 WHERE id = ?")->execute([$datos_proveedor[$campo]]);

            }

        }

        

        registrarAuditoria($pdo, [

            'accion' => 'SOFT_DELETE',

            'modulo' => 'proveedores',

            'tabla' => 'proveedores',

            'registro_id' => $id,

            'descripcion' => "Desactivó proveedor: $nombre_proveedor",

            'datos_anteriores' => $datos_proveedor

        ]);

        

        echo json_encode(['success' => true, 'message' => 'Proveedor eliminado']);

    } catch (Exception $e) {

        http_response_code(500);

        echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);

    }

    exit;

}

