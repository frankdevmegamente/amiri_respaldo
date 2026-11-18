<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

initApi();
requireAuth(['admin', 'usuario']);

if (($_SESSION['usuario_rol'] ?? '') === 'usuario' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonError('Solo lectura', 403);
}

$pdo = getPDO();

try {
    $conditions = [];
    $params = [];

    $incluirAnulados = isset($_GET['incluir_anulados']) && $_GET['incluir_anulados'] == '1';
    if ($incluirAnulados) {
        // Mostrar todo el historial, sin filtrar por activo ni estado
        $conditions[] = "1 = 1";
    } else {
        // Por defecto: solo movimientos vigentes
        $conditions[] = "m.activo = 1";
        $conditions[] = "m.estado = 'activo'";
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
        $tiposValidos = ['entrada', 'salida', 'traspaso', 'comision', 'ajuste'];
        $tipo = strtolower(trim($_GET['tipo']));
        if (in_array($tipo, $tiposValidos, true)) {
            $conditions[] = "m.tipo = ?";
            $params[] = $tipo;
        }
    }

    if (!empty($_GET['moneda'])) {
        $monedasValidas = ['MXN', 'USD', 'USDT'];
        $moneda = strtoupper(trim($_GET['moneda']));
        if (in_array($moneda, $monedasValidas, true)) {
            $conditions[] = "m.moneda = ?";
            $params[] = $moneda;
        }
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
        $pid = (int)$_GET['promotor_id'];
        // El promotor solo se guarda directo en comisiones/pagos. El resto de la
        // operación cuelga del cliente (clientes.promotor_id), por eso se busca
        // por ambas vías. cl ya está en LEFT JOIN en el SELECT principal.
        $conditions[] = "(m.promotor_id = ? OR cl.promotor_id = ?)";
        $params[] = $pid;
        $params[] = $pid;
    }

    if (!empty($_GET['q'])) {
        $q = '%' . trim($_GET['q']) . '%';
        $conditions[] = "(m.descripcion LIKE ? OR m.grupo_id LIKE ? OR s.nombre LIKE ? OR co.nombre LIKE ? OR cd.nombre LIKE ? OR cl.nombre LIKE ? OR pr.nombre LIKE ? OR pt.nombre LIKE ? OR pm.nombre LIKE ?)";
        for ($i = 0; $i < 9; $i++) {
            $params[] = $q;
        }
    }

    $whereClause = implode(' AND ', $conditions);

    // Orden tipo banco: con filtro de cuenta, de viejo a nuevo por defecto.
    // Se puede forzar con ?orden=asc|desc desde el frontend.
    $ordenParam = strtolower(trim($_GET['orden'] ?? ''));
    if ($ordenParam !== 'asc' && $ordenParam !== 'desc') {
        $ordenParam = !empty($_GET['cuenta_id']) ? 'asc' : 'desc';
    }
    $orderDir = $ordenParam === 'asc' ? 'ASC' : 'DESC';

    $sql = "SELECT
                m.id,
                m.grupo_id,
                m.fecha,
                m.tipo,
                m.monto,
                m.moneda,
                m.descripcion,
                m.estado,
                m.activo,
                m.es_reverso,
                m.cuenta_origen_id,
                m.cuenta_destino_id,
                s.nombre AS servicio_nombre,
                co.nombre AS cuenta_origen_nombre,
                cd.nombre AS cuenta_destino_nombre,
                cl.nombre AS cliente_nombre,
                pr.nombre AS proveedor_nombre,
                pt.nombre AS prestamista_nombre,
                pm.nombre AS promotor_nombre,
                u.nombre AS usuario_nombre,
                ho.saldo_anterior AS saldo_origen_anterior,
                ho.saldo_nuevo AS saldo_origen_nuevo,
                hd.saldo_anterior AS saldo_destino_anterior,
                hd.saldo_nuevo AS saldo_destino_nuevo
            FROM movimientos m
            LEFT JOIN servicios s ON s.id = m.servicio_id
            LEFT JOIN cuentas co ON co.id = m.cuenta_origen_id
            LEFT JOIN cuentas cd ON cd.id = m.cuenta_destino_id
            LEFT JOIN clientes cl ON cl.id = m.cliente_id
            LEFT JOIN proveedores pr ON pr.id = m.proveedor_id
            LEFT JOIN prestamistas pt ON pt.id = m.prestamista_id
            LEFT JOIN promotores pm ON pm.id = m.promotor_id
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN historial_saldos ho ON ho.movimiento_id = m.id AND ho.cuenta_id = m.cuenta_origen_id
            LEFT JOIN historial_saldos hd ON hd.movimiento_id = m.id AND hd.cuenta_id = m.cuenta_destino_id
            WHERE $whereClause
            ORDER BY m.fecha $orderDir, m.id $orderDir";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
