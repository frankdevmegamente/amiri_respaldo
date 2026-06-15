<?php

require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$pdo = getPDO();

$periodo = $_GET['periodo'] ?? 'mes';
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

switch ($periodo) {
    case 'dia':
        $fechaInicio = $fechaFin = date('Y-m-d');
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d', strtotime('monday this week'));
        $fechaFin = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'mes':
        $fechaInicio = date('Y-m-01');
        $fechaFin = date('Y-m-t');
        break;
    case 'anio':
        $fechaInicio = date('Y-01-01');
        $fechaFin = date('Y-12-31');
        break;
    case 'rango':
        break;
}

$tipo = $_GET['tipo'] ?? 'resumen';

$tiposValidos = ['resumen', 'retiros_diarios', 'detalle_dia', 'todos_movimientos', 'diario_servicio', 'lista_servicios'];
if (!in_array($tipo, $tiposValidos)) {
    echo json_encode(['error' => 'Tipo de reporte no válido']);
    exit;
}

$periodosValidos = ['dia', 'semana', 'mes', 'anio', 'rango'];
if (!in_array($periodo, $periodosValidos)) {
    echo json_encode(['error' => 'Período no válido']);
    exit;
}

try {
    switch ($tipo) {

        case 'resumen':
            $resultado = [];

            // Total depositado en efectivo (deudas_clientes)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(dc.monto_total), 0) as total, COUNT(*) as cantidad FROM deudas_clientes dc WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['depositado'] = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['depositado']['total'] = (float)$resultado['depositado']['total'];
            $resultado['depositado']['cantidad'] = (int)$resultado['depositado']['cantidad'];

            // Todos los servicios con actividad en el período
            $stmt = $pdo->prepare("
                SELECT s.id, s.nombre as servicio,
                       COALESCE(SUM(m.monto), 0) as total,
                       COUNT(*) as cantidad
                FROM servicios s
                INNER JOIN movimientos m ON m.servicio_id = s.id
                WHERE m.fecha BETWEEN ? AND ? AND m.activo = 1
                  AND (m.estado IS NULL OR m.estado = 'activo')
                GROUP BY s.id, s.nombre
                HAVING COUNT(*) > 0
                ORDER BY s.nombre
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($servicios as &$s) {
                $s['total'] = (float)$s['total'];
                $s['cantidad'] = (int)$s['cantidad'];
            }
            $resultado['servicios'] = $servicios;

            echo json_encode($resultado);
            break;

        case 'diario_servicio':
            $servicioId = (int)($_GET['servicio_id'] ?? 7);
            $tipoMov = $_GET['tipo_mov'] ?? '';

            $whereTipo = '';
            if ($tipoMov !== '') {
                $whereTipo = "AND m.tipo = " . $pdo->quote($tipoMov);
            }

            $sql = "SELECT DAY(m.fecha) as dia, COUNT(*) as cantidad, COALESCE(SUM(m.monto), 0) as total FROM movimientos m WHERE m.servicio_id = ? AND m.fecha BETWEEN ? AND ? AND m.activo = 1 $whereTipo GROUP BY DAY(m.fecha) ORDER BY dia";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$servicioId, $fechaInicio, $fechaFin]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'retiros_diarios':
            // Mantener compatibilidad: redirigir a diario_servicio con valores por defecto
            $_GET['servicio_id'] = 7;
            $_GET['tipo_mov'] = 'traspaso';
            // fall through

        case 'detalle_dia':
            $dia = $_GET['dia'] ?? date('d');
            $servicioId = (int)($_GET['servicio_id'] ?? 7);
            $tipoMov = $_GET['tipo_mov'] ?? '';

            $whereTipo = '';
            if ($tipoMov !== '') {
                $whereTipo = "AND m.tipo = " . $pdo->quote($tipoMov);
            }

            $sql = "SELECT m.id, m.creado_en, m.monto, m.descripcion, m.tipo, m.referencia_tipo, m.beneficiario, COALESCE(c.nombre, '-') as cliente, COALESCE(p.nombre, '-') as promotor, COALESCE(pr.nombre, '-') as proveedor, COALESCE(ps.nombre, '-') as prestamista FROM movimientos m LEFT JOIN clientes c ON m.cliente_id = c.id LEFT JOIN promotores p ON m.promotor_id = p.id LEFT JOIN proveedores pr ON m.proveedor_id = pr.id LEFT JOIN prestamistas ps ON m.prestamista_id = ps.id WHERE m.servicio_id = ? AND m.fecha BETWEEN ? AND ? AND DAY(m.fecha) = ? AND m.activo = 1 $whereTipo ORDER BY m.creado_en DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$servicioId, $fechaInicio, $fechaFin, (int)$dia]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'todos_movimientos':
            $tipoOperacion = $_GET['tipo_operacion'] ?? 'todos';
            $servicioId = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : null;

            $whereExtra = '';
            if ($tipoOperacion === 'retiros') {
                $whereExtra = "AND m.servicio_id = 7 AND m.tipo = 'traspaso'";
            } elseif ($tipoOperacion === 'depositos') {
                $whereExtra = "AND m.servicio_id IN (1, 2, 3)";
            } elseif ($tipoOperacion === 'transferencias') {
                $whereExtra = "AND m.servicio_id IN (11, 13, 14, 15)";
            }
            if ($servicioId) {
                $whereExtra = "AND m.servicio_id = $servicioId";
            }

            $sql = "SELECT m.creado_en, m.id, m.fecha, s.nombre as servicio, m.tipo, m.moneda, m.monto, m.paridad, m.descripcion, COALESCE(c.nombre, '-') as cliente, COALESCE(p.nombre, '-') as promotor, COALESCE(pr.nombre, '-') as proveedor FROM movimientos m JOIN servicios s ON m.servicio_id = s.id LEFT JOIN clientes c ON m.cliente_id = c.id LEFT JOIN promotores p ON m.promotor_id = p.id LEFT JOIN proveedores pr ON m.proveedor_id = pr.id WHERE m.fecha BETWEEN ? AND ? AND m.activo = 1 AND (m.estado IS NULL OR m.estado = 'activo') $whereExtra ORDER BY m.fecha DESC, m.id DESC LIMIT 500";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fechaInicio, $fechaFin]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'lista_servicios':
            $stmt = $pdo->query("
                SELECT DISTINCT m.servicio_id as id, s.nombre as servicio
                FROM movimientos m
                JOIN servicios s ON m.servicio_id = s.id
                WHERE m.activo = 1 AND (m.estado IS NULL OR m.estado = 'activo')
                ORDER BY s.nombre
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        default:
            echo json_encode(['error' => 'Tipo de reporte no válido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
