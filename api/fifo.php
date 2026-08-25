<?php

require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';

requireAdmin();

$moneda = strtoupper($_GET['moneda'] ?? 'USDT');
if (!in_array($moneda, ['USDT', 'USD'], true)) {
    jsonError('Moneda no válida. Use USDT o USD.', 400);
}

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$mes = $_GET['mes'] ?? '';
if ($mes && !preg_match('/^\d{4}-\d{2}$/', $mes)) {
    jsonError('Formato de mes inválido. Use YYYY-MM.', 400);
}
if ($mes) {
    $fechaInicio = $mes . '-01';
    $fechaFin = date('Y-m-t', strtotime($fechaInicio));
}
$tieneFiltro = ($fechaInicio && $fechaFin);

try {
    $pdo = getPDO();

    // ============================================
    // RESUMEN (con filtro opcional de fechas)
    // ============================================
    $sqlResumen = "
        SELECT COUNT(*) as total_lotes,
               COALESCE(SUM(cantidad_original), 0) as total_original,
               COALESCE(SUM(cantidad_restante), 0) as total_restante,
               COALESCE(SUM(ROUND(cantidad_original * paridad, 2)), 0) as total_mxn_compras
        FROM inventario_monedas
        WHERE moneda = ? AND activo = 1
    ";
    $paramsResumen = [$moneda];
    if ($tieneFiltro) {
        $sqlResumen .= " AND fecha BETWEEN ? AND ?";
        $paramsResumen[] = $fechaInicio;
        $paramsResumen[] = $fechaFin;
    }
    $stmt = $pdo->prepare($sqlResumen);
    $stmt->execute($paramsResumen);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

    $sqlVentasResumen = "
        SELECT COUNT(DISTINCT ci.movimiento_id) as total_ventas,
               COALESCE(SUM(ci.cantidad_consumida), 0) as total_vendido,
               COALESCE(SUM(ci.ganancia), 0) as total_ganancia
        FROM consumos_inventario ci
        JOIN inventario_monedas im ON ci.inventario_id = im.id
        JOIN movimientos mv ON ci.movimiento_id = mv.id
        WHERE im.moneda = ? AND mv.activo = 1
    ";
    $paramsVentasResumen = [$moneda];
    if ($tieneFiltro) {
        $sqlVentasResumen .= " AND mv.fecha BETWEEN ? AND ?";
        $paramsVentasResumen[] = $fechaInicio;
        $paramsVentasResumen[] = $fechaFin;
    }
    $stmt = $pdo->prepare($sqlVentasResumen);
    $stmt->execute($paramsVentasResumen);
    $ventasResumen = $stmt->fetch(PDO::FETCH_ASSOC);

    $resumen['total_ventas'] = (int)$ventasResumen['total_ventas'];
    $resumen['total_vendido'] = (float)$ventasResumen['total_vendido'];
    $resumen['total_ganancia'] = (float)$ventasResumen['total_ganancia'];
    $resumen['total_mxn_ventas'] = 0;

    // ============================================
    // 1. Lotes de compra (con filtro opcional de fechas)
    // ============================================
    $sqlLotes = "
        SELECT im.id, im.fecha, im.cantidad_original, im.cantidad_restante,
               im.paridad as precio_compra,
               ROUND(im.cantidad_original * im.paridad, 2) as total_mxn,
               m.descripcion
        FROM inventario_monedas im
        JOIN movimientos m ON im.movimiento_id = m.id
        WHERE im.moneda = ? AND im.activo = 1
    ";
    $paramsLotes = [$moneda];
    if ($tieneFiltro) {
        $sqlLotes .= " AND im.fecha BETWEEN ? AND ?";
        $paramsLotes[] = $fechaInicio;
        $paramsLotes[] = $fechaFin;
    }
    $sqlLotes .= " ORDER BY im.fecha ASC, im.id ASC";
    $stmt = $pdo->prepare($sqlLotes);
    $stmt->execute($paramsLotes);
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================
    // 2. Ventas con consumos FIFO (con filtro opcional de fechas)
    // ============================================
    $sqlVentas = "
        SELECT ci.movimiento_id as venta_id, mv.fecha as venta_fecha,
               mv.descripcion as venta_desc,
               ci.inventario_id as lote_id, ci.cantidad_consumida,
               ci.paridad_compra, ci.paridad_venta, ci.ganancia,
               im.fecha as lote_fecha
        FROM consumos_inventario ci
        JOIN inventario_monedas im ON ci.inventario_id = im.id
        JOIN movimientos mv ON ci.movimiento_id = mv.id
        WHERE im.moneda = ? AND mv.activo = 1
    ";
    $paramsVentas = [$moneda];
    if ($tieneFiltro) {
        $sqlVentas .= " AND mv.fecha BETWEEN ? AND ?";
        $paramsVentas[] = $fechaInicio;
        $paramsVentas[] = $fechaFin;
    }
    $sqlVentas .= " ORDER BY mv.fecha ASC, ci.inventario_id ASC";
    $stmt = $pdo->prepare($sqlVentas);
    $stmt->execute($paramsVentas);
    $consumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar consumos por venta
    $ventas = [];
    foreach ($consumos as $c) {
        $vid = $c['venta_id'];
        if (!isset($ventas[$vid])) {
            $ventas[$vid] = [
                'id' => $vid,
                'fecha' => $c['venta_fecha'],
                'descripcion' => $c['venta_desc'],
                'total_moneda' => 0,
                'total_mxn' => 0,
                'ganancia_total' => 0,
                'consumos' => []
            ];
        }
        $ventas[$vid]['consumos'][] = [
            'lote_id' => $c['lote_id'],
            'cantidad' => (float)$c['cantidad_consumida'],
            'precio_compra' => (float)$c['paridad_compra'],
            'precio_venta' => (float)$c['paridad_venta'],
            'ganancia' => (float)$c['ganancia'],
            'lote_fecha' => $c['lote_fecha']
        ];
        $ventas[$vid]['total_moneda'] += (float)$c['cantidad_consumida'];
        $ventas[$vid]['total_mxn'] += (float)$c['cantidad_consumida'] * (float)$c['paridad_venta'];
        $ventas[$vid]['ganancia_total'] += (float)$c['ganancia'];
    }
    $ventas = array_values($ventas);

    $sqlMxn = "
        SELECT COALESCE(SUM(ci.cantidad_consumida * ci.paridad_venta), 0) as total_mxn_ventas
        FROM consumos_inventario ci
        JOIN inventario_monedas im ON ci.inventario_id = im.id
        JOIN movimientos mv ON ci.movimiento_id = mv.id
        WHERE im.moneda = ? AND mv.activo = 1
    ";
    $paramsMxn = [$moneda];
    if ($tieneFiltro) {
        $sqlMxn .= " AND mv.fecha BETWEEN ? AND ?";
        $paramsMxn[] = $fechaInicio;
        $paramsMxn[] = $fechaFin;
    }
    $stmtMxn = $pdo->prepare($sqlMxn);
    $stmtMxn->execute($paramsMxn);
    $resumen['total_mxn_ventas'] = (float)$stmtMxn->fetchColumn();

    jsonResponse([
        'moneda' => $moneda,
        'resumen' => $resumen,
        'lotes' => $lotes,
        'ventas' => $ventas
    ], 200);

} catch (Exception $e) {
    jsonError('Error al obtener datos FIFO: ' . $e->getMessage(), 500);
}
