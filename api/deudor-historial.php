<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();

$pdo = getPDO();

try {
    $persona_nombre = trim($_GET['persona_nombre'] ?? '');

    if (empty($persona_nombre)) {
        http_response_code(400);
        echo json_encode(['error' => 'persona_nombre es requerido']);
        exit;
    }

    $sql = "SELECT
                m.id,
                m.grupo_id,
                m.fecha,
                m.tipo,
                m.monto,
                m.moneda,
                m.descripcion,
                m.estado,
                m.es_reverso,
                m.servicio_id,
                m.referencia_id,
                s.nombre AS servicio_nombre,
                co.nombre AS cuenta_origen_nombre,
                cd.nombre AS cuenta_destino_nombre,
                u.nombre AS usuario_nombre
            FROM movimientos m
            INNER JOIN prestamos_otorgados po ON po.id = m.referencia_id
            LEFT JOIN servicios s ON s.id = m.servicio_id
            LEFT JOIN cuentas co ON co.id = m.cuenta_origen_id
            LEFT JOIN cuentas cd ON cd.id = m.cuenta_destino_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            WHERE po.persona_nombre = ?
              AND m.activo = 1
              AND m.servicio_id IN (16, 17)
            ORDER BY m.fecha DESC, m.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$persona_nombre]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
