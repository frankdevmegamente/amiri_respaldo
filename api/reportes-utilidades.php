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

$tiposValidos = ['resumen', 'por_servicio', 'por_cliente', 'por_promotor', 'grafica', 'detalle_dia', 'detalle_operaciones', 'comparativa'];
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

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_amiri'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_promotor), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_promotores'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['ganancia_divisas'] = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 2 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['ganancia_usd'] = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 3 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['ganancia_usdt'] = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $gastos = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['gastos_total'] = (float)$gastos['total'];
            $resultado['gastos_fijos'] = (float)$gastos['fijos'];
            $resultado['gastos_variables'] = (float)$gastos['variables'];

            $resultado['utilidad_bruta'] = $resultado['comisiones_amiri'] + $resultado['ganancia_divisas'];
            $resultado['utilidad_neta'] = $resultado['utilidad_bruta'] - $resultado['gastos_total'];

            $resultado['_explicacion'] = [
                'comisiones_amiri' => 'Utilidades generadas por las ventas de efectivo que AMIRI retiene como ganancia. Entre más altas, mejor rendimiento del negocio. Fuente: suma de comision_amiri en deudas_clientes.',
                'comisiones_promotores' => 'Utilidades que se pagan a los promotores por sus ventas. Es un costo variable directamente ligado al volumen de operaciones. Fuente: suma de comision_promotor en deudas_clientes.',
                'ganancia_divisas' => 'Utilidad obtenida por la compra/venta de dólares (USD) y Tether (USDT). Representa el margen de ganancia en operaciones cambiarias. Fuente: suma de monto en movimientos (servicio_id 2 y 3, tipo comision, cuenta_destino_id 1).',
                'gastos_total' => 'Total de gastos operativos del período, incluyendo gastos fijos (renta, nómina) y variables (pagos externos, transportes). Fuente: suma de monto en gastos.',
                'gastos_fijos' => 'Gastos que no cambian con el volumen de operaciones: renta del local, nómina del personal administrativo, servicios básicos, etc. Fuente: gastos donde clasificacion = fijo.',
                'gastos_variables' => 'Gastos que varían según la actividad: pagos externos, transportes, materiales, etc. Fuente: gastos donde clasificacion = variable.',
                'utilidad_bruta' => 'Ingresos totales antes de restar gastos operativos. Fórmula: Utilidad AMIRI + Ganancia por Divisas. Fuente: cálculo automático.',
                'utilidad_neta' => 'La ganancia o pérdida REAL del período después de todos los gastos. Si es positiva, el negocio es rentable. Si es negativa, se están gastando más ingresos de los que se generan. Fórmula: Utilidad Bruta - Total Gastos. Fuente: cálculo automático.',
            ];

            echo json_encode($resultado);
            break;

        case 'por_servicio':
            $stmt = $pdo->prepare("
                SELECT 
                    'Venta de Efectivo' as servicio, 1 as id,
                    COUNT(dc.id) as operaciones,
                    COALESCE(SUM(dc.comision_amiri), 0) as comisiones_amiri,
                    COALESCE(SUM(dc.comision_promotor), 0) as comisiones_promotor
                FROM deudas_clientes dc
                WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("
                SELECT s.nombre as servicio, s.id, COUNT(m.id) as operaciones, COALESCE(SUM(m.monto), 0) as ganancia_divisas
                FROM movimientos m
                JOIN servicios s ON m.servicio_id = s.id
                WHERE m.servicio_id IN (2,3) AND m.tipo = 'comision' AND m.cuenta_destino_id = 1 AND m.fecha BETWEEN ? AND ? AND m.activo = 1 AND m.estado = 'activo'
                GROUP BY s.id, s.nombre
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $divisas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($divisas as $d) {
                $d['comisiones_amiri'] = 0;
                $d['comisiones_promotor'] = 0;
                $d['total'] = (float)$d['ganancia_divisas'];
                $servicios[] = $d;
            }

            $totalUtilidad = 0;
            foreach ($servicios as &$s) {
                $s['comisiones_amiri'] = (float)($s['comisiones_amiri'] ?? 0);
                $s['comisiones_promotor'] = (float)($s['comisiones_promotor'] ?? 0);
                $s['ganancia_divisas'] = (float)($s['ganancia_divisas'] ?? 0);
                $s['total'] = ($s['comisiones_amiri'] + $s['ganancia_divisas']);
                $s['operaciones'] = (int)($s['operaciones'] ?? 0);
                $totalUtilidad += $s['total'];
            }
            unset($s);

            foreach ($servicios as &$s) {
                $s['porcentaje'] = $totalUtilidad > 0 ? round(($s['total'] / $totalUtilidad) * 100, 2) : 0;
            }
            unset($s);

            echo json_encode(['datos' => $servicios, 'total_utilidad' => $totalUtilidad]);
            break;

        case 'por_cliente':
            $stmt = $pdo->prepare("
                SELECT 
                    c.id as cliente_id, c.nombre as cliente,
                    COALESCE(p.nombre, 'DIRECTO AMIRI') as promotor,
                    COUNT(dc.id) as operaciones,
                    COALESCE(SUM(dc.monto_total), 0) as monto_total,
                    COALESCE(SUM(dc.comision_amiri), 0) as comision_amiri,
                    COALESCE(SUM(dc.comision_promotor), 0) as comision_promotor
                FROM clientes c
                LEFT JOIN promotores p ON c.promotor_id = p.id
                LEFT JOIN deudas_clientes dc ON dc.cliente_id = c.id AND dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre, p.nombre
                HAVING comision_amiri > 0
                ORDER BY comision_amiri DESC
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalUtilidad = array_sum(array_map(function($c) { return (float)$c['comision_amiri']; }, $clientes));

            foreach ($clientes as &$c) {
                $c['porcentaje'] = $totalUtilidad > 0 ? round(((float)$c['comision_amiri'] / $totalUtilidad) * 100, 2) : 0;
            }
            unset($c);

            echo json_encode(['datos' => $clientes, 'total_utilidad' => $totalUtilidad]);
            break;

        case 'por_promotor':
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(p.id, 0) as promotor_id,
                    COALESCE(p.nombre, 'DIRECTO AMIRI') as promotor,
                    COUNT(DISTINCT c.id) as clientes,
                    COUNT(dc.id) as operaciones,
                    COALESCE(SUM(dc.comision_amiri), 0) as comision_amiri,
                    COALESCE(SUM(dc.comision_promotor), 0) as comision_promotor
                FROM (
                    SELECT 0 as id, 'DIRECTO AMIRI' as nombre, 1 as activo
                    UNION ALL
                    SELECT id, nombre, activo FROM promotores WHERE activo = 1
                ) p
                LEFT JOIN clientes c ON (p.id = 0 AND c.promotor_id IS NULL) OR (p.id > 0 AND c.promotor_id = p.id)
                LEFT JOIN deudas_clientes dc ON dc.cliente_id = c.id AND dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                WHERE c.activo = 1
                GROUP BY p.id, p.nombre
                HAVING comision_amiri > 0
                ORDER BY comision_amiri DESC
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $promotores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalUtilidad = array_sum(array_map(function($p) { return (float)$p['comision_amiri']; }, $promotores));

            foreach ($promotores as &$p) {
                $p['porcentaje'] = $totalUtilidad > 0 ? round(((float)$p['comision_amiri'] / $totalUtilidad) * 100, 2) : 0;
            }
            unset($p);

            echo json_encode(['datos' => $promotores, 'total_utilidad' => $totalUtilidad]);
            break;

        case 'grafica':
            $desglose = $_GET['desglose'] ?? 'utilidad_bruta';

            if ($desglose === 'utilidad_bruta') {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE_FORMAT(dc.fecha, '%Y-%m') as mes,
                        COALESCE(SUM(dc.comision_amiri), 0) as comisiones,
                        COALESCE(SUM(dc.comision_promotor), 0) as comisiones_promotor
                    FROM deudas_clientes dc
                    WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                    GROUP BY DATE_FORMAT(dc.fecha, '%Y-%m')
                    ORDER BY mes
                ");
                $stmt->execute([$fechaInicio, $fechaFin]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($desglose === 'por_servicio') {
                $stmt = $pdo->prepare("
                    SELECT mes, servicio, SUM(total) as total FROM (
                        SELECT DATE_FORMAT(dc.fecha, '%Y-%m') as mes, 'Venta de Efectivo' as servicio, COALESCE(SUM(dc.comision_amiri), 0) as total
                        FROM deudas_clientes dc
                        WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                        GROUP BY DATE_FORMAT(dc.fecha, '%Y-%m')
                        UNION ALL
                        SELECT DATE_FORMAT(m.fecha, '%Y-%m') as mes, s.nombre as servicio, COALESCE(SUM(m.monto), 0) as total
                        FROM movimientos m
                        JOIN servicios s ON m.servicio_id = s.id
                        WHERE m.servicio_id IN (2,3) AND m.tipo = 'comision' AND m.cuenta_destino_id = 1 AND m.fecha BETWEEN ? AND ? AND m.activo = 1 AND m.estado = 'activo'
                        GROUP BY DATE_FORMAT(m.fecha, '%Y-%m'), s.nombre
                    ) subq
                    GROUP BY mes, servicio
                    ORDER BY mes, servicio
                ");
                $stmt->execute([$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($desglose === 'utilidad_diaria') {
                $stmt = $pdo->prepare("
                    SELECT 
                        dc.fecha as dia,
                        COALESCE(SUM(dc.comision_amiri), 0) as comisiones,
                        COALESCE(SUM(dc.comision_promotor), 0) as comisiones_promotor,
                        COALESCE(SUM(dc.monto_total), 0) as monto_total,
                        COUNT(dc.id) as operaciones
                    FROM deudas_clientes dc
                    WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                    GROUP BY dc.fecha
                    ORDER BY dc.fecha
                ");
                $stmt->execute([$fechaInicio, $fechaFin]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif ($desglose === 'por_promotor') {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE_FORMAT(dc.fecha, '%Y-%m') as mes,
                        COALESCE(p.nombre, 'DIRECTO AMIRI') as promotor,
                        COALESCE(SUM(dc.comision_amiri), 0) as total
                    FROM deudas_clientes dc
                    JOIN clientes c ON dc.cliente_id = c.id
                    LEFT JOIN promotores p ON c.promotor_id = p.id
                    WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1
                    GROUP BY DATE_FORMAT(dc.fecha, '%Y-%m'), COALESCE(p.nombre, 'DIRECTO AMIRI')
                    ORDER BY mes, promotor
                ");
                $stmt->execute([$fechaInicio, $fechaFin]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                echo json_encode([]);
            }
            break;

        case 'detalle_dia':
            $diaFecha = $_GET['dia'] ?? date('Y-m-d');
            $stmt = $pdo->prepare("
                SELECT 
                    dc.id,
                    dc.creado_en,
                    dc.monto_total as monto,
                    dc.comision_amiri,
                    dc.comision_promotor,
                    'Venta de Efectivo' as servicio,
                    c.nombre as cliente,
                    COALESCE(p.nombre, 'DIRECTO AMIRI') as promotor,
                    dc.moneda as referencia_tipo
                FROM deudas_clientes dc
                JOIN clientes c ON dc.cliente_id = c.id
                LEFT JOIN promotores p ON c.promotor_id = p.id
                WHERE dc.fecha = ? AND dc.activo = 1
                ORDER BY dc.creado_en DESC
            ");
            $stmt->execute([$diaFecha]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'detalle_operaciones':
            $where = "dc.fecha BETWEEN ? AND ? AND dc.activo = 1";
            $params = [$fechaInicio, $fechaFin];
            $clienteId = $_GET['cliente_id'] ?? null;
            $promotorId = $_GET['promotor_id'] ?? null;
            if ($clienteId) { $where .= " AND dc.cliente_id = ?"; $params[] = $clienteId; }
            if ($promotorId !== null) {
                if ($promotorId === '0') { $where .= " AND c.promotor_id IS NULL"; }
                else { $where .= " AND c.promotor_id = ?"; $params[] = $promotorId; }
            }
            $stmt = $pdo->prepare("
                SELECT 
                    dc.id,
                    dc.fecha,
                    dc.creado_en,
                    dc.monto_total as monto,
                    dc.comision_amiri,
                    dc.comision_promotor,
                    'Venta de Efectivo' as servicio,
                    c.nombre as cliente,
                    COALESCE(p.nombre, 'DIRECTO AMIRI') as promotor,
                    dc.moneda as referencia_tipo
                FROM deudas_clientes dc
                JOIN clientes c ON dc.cliente_id = c.id
                LEFT JOIN promotores p ON c.promotor_id = p.id
                WHERE $where
                ORDER BY dc.creado_en DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'comparativa':
            // Calcular período anterior del mismo tamaño
            $tsInicio = strtotime($fechaInicio);
            $tsFin = strtotime($fechaFin);
            $intervalo = $tsFin - $tsInicio;
            $prevFechaFin = date('Y-m-d', $tsInicio - 86400);
            $prevFechaInicio = date('Y-m-d', $tsInicio - $intervalo);

            $prev = [];
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$prevFechaInicio, $prevFechaFin]);
            $prev['comisiones_amiri'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
            $stmt->execute([$prevFechaInicio, $prevFechaFin]);
            $prev['ganancia_divisas'] = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$prevFechaInicio, $prevFechaFin]);
            $gastosPrev = $stmt->fetch(PDO::FETCH_ASSOC);
            $prev['gastos_total'] = (float)$gastosPrev['total'];

            $prev['utilidad_bruta'] = $prev['comisiones_amiri'] + $prev['ganancia_divisas'];
            $prev['utilidad_neta'] = $prev['utilidad_bruta'] - $prev['gastos_total'];

            echo json_encode($prev);
            break;

        default:
            echo json_encode(['error' => 'Tipo de reporte no válido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
