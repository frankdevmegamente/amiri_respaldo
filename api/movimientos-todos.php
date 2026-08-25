<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAdmin();

$pdo = getPDO();

try {
    $conditions = ["m.activo = 1"];
    $params = [];

    $incluirAnulados = isset($_GET['incluir_anulados']) && $_GET['incluir_anulados'] == '1';
    if ($incluirAnulados) {
        $conditions = ["1 = 1"];
    }

    if (!empty($_GET['fecha_inicio'])) {
        $conditions[] = "m.fecha >= ?";
        $params[] = $_GET['fecha_inicio'];
    }

    if (!empty($_GET['fecha_fin'])) {
        $conditions[] = "m.fecha <= ?";
        $params[] = $_GET['fecha_fin'];
    }

    if (!empty($_GET['servicio_id'])) {
        $conditions[] = "m.servicio_id = ?";
        $params[] = (int)$_GET['servicio_id'];
    }

    if (!empty($_GET['tipo'])) {
        $conditions[] = "m.tipo = ?";
        $params[] = $_GET['tipo'];
    }

    if (!empty($_GET['moneda'])) {
        $conditions[] = "m.moneda = ?";
        $params[] = $_GET['moneda'];
    }

    if (!empty($_GET['cuenta_id'])) {
        $cid = (int)$_GET['cuenta_id'];
        $conditions[] = "(m.cuenta_origen_id = ? OR m.cuenta_destino_id = ?)";
        $params[] = $cid;
        $params[] = $cid;
    }

    if (!empty($_GET['cliente_id'])) {
        $conditions[] = "m.cliente_id = ?";
        $params[] = (int)$_GET['cliente_id'];
    }

    if (!empty($_GET['proveedor_id'])) {
        $conditions[] = "m.proveedor_id = ?";
        $params[] = (int)$_GET['proveedor_id'];
    }

    if (!empty($_GET['prestamista_id'])) {
        $conditions[] = "m.prestamista_id = ?";
        $params[] = (int)$_GET['prestamista_id'];
    }

    if (!empty($_GET['promotor_id'])) {
        $conditions[] = "m.promotor_id = ?";
        $params[] = (int)$_GET['promotor_id'];
    }

    $whereClause = implode(' AND ', $conditions);

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
                s.nombre AS servicio_nombre,
                co.nombre AS cuenta_origen_nombre,
                cd.nombre AS cuenta_destino_nombre,
                cl.nombre AS cliente_nombre,
                pr.nombre AS proveedor_nombre,
                pt.nombre AS prestamista_nombre,
                pm.nombre AS promotor_nombre,
                u.nombre AS usuario_nombre
            FROM movimientos m
            LEFT JOIN servicios s ON s.id = m.servicio_id
            LEFT JOIN cuentas co ON co.id = m.cuenta_origen_id
            LEFT JOIN cuentas cd ON cd.id = m.cuenta_destino_id
            LEFT JOIN clientes cl ON cl.id = m.cliente_id
            LEFT JOIN proveedores pr ON pr.id = m.proveedor_id
            LEFT JOIN prestamistas pt ON pt.id = m.prestamista_id
            LEFT JOIN promotores pm ON pm.id = m.promotor_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            WHERE $whereClause
            ORDER BY m.fecha DESC, m.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
