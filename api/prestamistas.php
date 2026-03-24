<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';

initApi();
requireAdmin();
$pdo = getPDO();

/* METHOD OVERRIDE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

/* GET */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $pdo->prepare("
            SELECT p.*,
                   COALESCE(SUM(
                       CASE
                           WHEN pr.estado = 'pendiente' THEN pr.monto
                           WHEN pr.estado = 'parcial' THEN (pr.monto - pr.monto_pagado)
                           ELSE 0
                       END
                   ),0) AS saldo_total
            FROM prestamistas p
            LEFT JOIN deudas_empresa pr 
                ON pr.prestamista_id = p.id AND pr.activo = 1
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        exit;
    }

    // Si pide solo activos
    $activos = filter_input(INPUT_GET, 'activos', FILTER_VALIDATE_INT);
    
    $sql = "
        SELECT p.*,
               COALESCE(SUM(
                   CASE
                       WHEN pr.estado = 'pendiente' THEN pr.monto
                       WHEN pr.estado = 'parcial' THEN (pr.monto - pr.monto_pagado)
                       ELSE 0
                   END
               ),0) AS saldo_total
        FROM prestamistas p
        LEFT JOIN deudas_empresa pr 
            ON pr.prestamista_id = p.id AND pr.activo = 1
    ";
    
    if ($activos) {
        $sql .= "WHERE p.activo = 1 ";
    }
    
    $sql .= "GROUP BY p.id ORDER BY p.nombre";

    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
    exit;
}

/* POST (CREATE) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Nombre requerido']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO prestamistas (nombre, telefono, email, direccion, activo)
        VALUES (?,?,?,?,?)
    ");
    $stmt->execute([
        $nombre,
        $_POST['telefono'] ?? null,
        $_POST['email'] ?? null,
        $_POST['direccion'] ?? null,
        $_POST['activo'] ?? 1
    ]);
    
    $prestamista_id = $pdo->lastInsertId();
    
    registrarAuditoria($pdo, [
        'accion' => 'INSERT',
        'modulo' => 'prestamistas',
        'tabla' => 'prestamistas',
        'registro_id' => $prestamista_id,
        'descripcion' => "Creó prestamista: $nombre",
        'datos_nuevos' => [
            'id' => $prestamista_id,
            'nombre' => $nombre,
            'telefono' => $_POST['telefono'] ?? null,
            'email' => $_POST['email'] ?? null,
            'direccion' => $_POST['direccion'] ?? null
        ]
    ]);

    echo json_encode(['success' => true]);
    exit;
}

/* PUT (UPDATE) */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }
    
    $datos_anteriores = obtenerDatosAnteriores($pdo, 'prestamistas', $id);

    $stmt = $pdo->prepare("
        UPDATE prestamistas
        SET nombre=?, telefono=?, email=?, direccion=?, activo=?
        WHERE id=?
    ");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['telefono'] ?? null,
        $_POST['email'] ?? null,
        $_POST['direccion'] ?? null,
        $_POST['activo'] ?? 1,
        $id
    ]);
    
    registrarAuditoria($pdo, [
        'accion' => 'UPDATE',
        'modulo' => 'prestamistas',
        'tabla' => 'prestamistas',
        'registro_id' => $id,
        'descripcion' => "Actualizó prestamista: {$_POST['nombre']}",
        'datos_anteriores' => $datos_anteriores,
        'datos_nuevos' => [
            'nombre' => $_POST['nombre'],
            'telefono' => $_POST['telefono'] ?? null,
            'email' => $_POST['email'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'activo' => $_POST['activo'] ?? 1
        ]
    ]);

    echo json_encode(['success' => true]);
    exit;
}

/* DELETE (SOFT) */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    $datos_anteriores = obtenerDatosAnteriores($pdo, 'prestamistas', $id);

    $pdo->prepare("UPDATE prestamistas SET activo = 0 WHERE id = ?")
        ->execute([$id]);
    
    registrarAuditoria($pdo, [
        'accion' => 'DELETE',
        'modulo' => 'prestamistas',
        'tabla' => 'prestamistas',
        'registro_id' => $id,
        'descripcion' => "Desactivó prestamista: {$datos_anteriores['nombre']}",
        'datos_anteriores' => $datos_anteriores
    ]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
