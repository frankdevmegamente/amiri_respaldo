<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';

initApi();
requireAuth(['admin', 'usuario']);

if (($_SESSION['usuario_rol'] ?? '') === 'usuario' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonError('Solo lectura', 403);
}
$pdo = getPDO();
if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM deudores WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            exit;
        }
        // Lista con stats opcionales
        if (isset($_GET['con_stats'])) {
            $stmt = $pdo->query("
                SELECT d.*,
                    COUNT(p.id) as prestamos_count,
                    COALESCE(SUM(p.monto_total_con_interes),0) as total_con_interes,
                    COALESCE(SUM(p.monto_pagado),0) as total_pagado
                FROM deudores d
                LEFT JOIN prestamos_otorgados p ON p.deudor_id = d.id AND p.activo=1
                WHERE d.activo=1
                GROUP BY d.id ORDER BY d.nombre
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }
        $stmt = $pdo->query("SELECT * FROM deudores WHERE activo=1 ORDER BY nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $id = $_POST['id'] ?? null;

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['error'=>'Nombre obligatorio']);
            exit;
        }

        // Evitar duplicado por nombre (case-insensitive)
        $chk = $pdo->prepare("SELECT id FROM deudores WHERE LOWER(nombre)=LOWER(?) AND id<>COALESCE(?,0)");
        $chk->execute([$nombre, $id ?: 0]);
        if ($chk->fetch()) {
            http_response_code(400);
            echo json_encode(['error'=>'Ya existe un deudor con ese nombre']);
            exit;
        }

        if ($id) {
            $prev = $pdo->prepare("SELECT * FROM deudores WHERE id=?");
            $prev->execute([$id]);
            $ant = $prev->fetch(PDO::FETCH_ASSOC);
            $pdo->prepare("UPDATE deudores SET nombre=?, telefono=?, email=?, direccion=? WHERE id=?")
                ->execute([$nombre, $telefono ?: null, $email ?: null, $direccion ?: null, $id]);
            registrarAuditoria($pdo, ['accion'=>'UPDATE','modulo'=>'deudores','tabla'=>'deudores','registro_id'=>$id,'descripcion'=>"Actualizó deudor: $nombre",'datos_anteriores'=>$ant,'datos_nuevos'=>['nombre'=>$nombre]]);
            echo json_encode(['success'=>true,'id'=>$id]);
            exit;
        } else {
            $pdo->prepare("INSERT INTO deudores (nombre, telefono, email, direccion) VALUES (?,?,?,?)")
                ->execute([$nombre, $telefono ?: null, $email ?: null, $direccion ?: null]);
            $nid = $pdo->lastInsertId();
            registrarAuditoria($pdo, ['accion'=>'INSERT','modulo'=>'deudores','tabla'=>'deudores','registro_id'=>$nid,'descripcion'=>"Creó deudor: $nombre",'datos_nuevos'=>['nombre'=>$nombre]]);
            echo json_encode(['success'=>true,'id'=>$nid]);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $data);
        $id = $_GET['id'] ?? $data['id'] ?? null;
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID inválido']); exit; }
        // No permitir borrar si tiene préstamos activos
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM prestamos_otorgados WHERE deudor_id=? AND activo=1 AND estado!='pagado'");
        $cnt->execute([$id]);
        if ($cnt->fetchColumn() > 0) { http_response_code(400); echo json_encode(['error'=>'No se puede borrar: tiene préstamos pendientes']); exit; }
        $prev = $pdo->prepare("SELECT * FROM deudores WHERE id=?");
        $prev->execute([$id]);
        $ant = $prev->fetch(PDO::FETCH_ASSOC);
        $pdo->prepare("UPDATE deudores SET activo=0 WHERE id=?")->execute([$id]);
        if ($ant) registrarAuditoria($pdo, ['accion'=>'SOFT_DELETE','modulo'=>'deudores','tabla'=>'deudores','registro_id'=>$id,'descripcion'=>"Desactivó deudor: ".$ant['nombre'],'datos_anteriores'=>$ant]);
        echo json_encode(['success'=>true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
