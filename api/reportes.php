<?php
/**
 * API de Reportes
 * Endpoint para obtener datos de reportes financieros
 */

require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$pdo = getPDO();

// Parámetros de fecha
$periodo = $_GET['periodo'] ?? 'mes'; // dia, semana, mes, anio, rango
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Hoy

// Ajustar fechas según período
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
        // Usar las fechas proporcionadas
        break;
}

$tipo = $_GET['tipo'] ?? 'resumen';

try {
    switch ($tipo) {
        
        // ==================== RESUMEN GENERAL ====================
        case 'resumen':
            $resultado = [];
            
            // 1. Comisiones AMIRI (ventas de efectivo)
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(comision_amiri), 0) as total
                FROM deudas_clientes 
                WHERE fecha BETWEEN ? AND ? AND activo = 1
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_amiri'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 2. Comisiones Promotores
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(comision_promotor), 0) as total
                FROM deudas_clientes 
                WHERE fecha BETWEEN ? AND ? AND activo = 1
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_promotores'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 3. Total ventas de efectivo
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(monto_total), 0) as total, COUNT(*) as cantidad
                FROM deudas_clientes 
                WHERE fecha BETWEEN ? AND ? AND activo = 1
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['ventas_efectivo_total'] = (float)$row['total'];
            $resultado['ventas_efectivo_cantidad'] = (int)$row['cantidad'];
            
            // 4. Operaciones de divisas
            $stmt = $pdo->prepare("
                SELECT 
                    fm.moneda,
                    s.nombre as servicio,
                    COUNT(*) as operaciones,
                    SUM(fm.monto) as total_monto,
                    AVG(fm.paridad) as tc_promedio
                FROM movimientos fm
                JOIN servicios s ON fm.servicio_id = s.id
                WHERE fm.moneda IN ('USD', 'USDT') 
                AND fm.fecha BETWEEN ? AND ?
                AND fm.activo = 1
                GROUP BY fm.moneda, fm.servicio_id, s.nombre
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['operaciones_divisas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 5. Ganancia estimada en divisas (diferencia entre venta y compra promedio)
            // Para cada venta, calcular: (tc_venta - tc_compra_promedio) * cantidad
            $stmt = $pdo->prepare("
                SELECT 
                    fm.moneda,
                    SUM(fm.monto) as total_vendido,
                    SUM(fm.monto * fm.paridad) as total_mxn_recibido
                FROM movimientos fm
                WHERE fm.servicio_id IN (2, 3) -- Ventas de USD y USDT
                AND fm.fecha BETWEEN ? AND ?
                AND fm.activo = 1
                GROUP BY fm.moneda
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $ventasDivisas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular costo promedio del inventario
            $gananciaDivisas = [];
            foreach ($ventasDivisas as $venta) {
                $stmtCosto = $pdo->prepare("
                    SELECT AVG(paridad) as costo_promedio 
                    FROM inventario_monedas 
                    WHERE moneda = ?
                ");
                $stmtCosto->execute([$venta['moneda']]);
                $costoPromedio = (float)$stmtCosto->fetch(PDO::FETCH_ASSOC)['costo_promedio'];
                
                $totalVendido = (float)$venta['total_vendido'];
                $totalRecibido = (float)$venta['total_mxn_recibido'];
                $costoTotal = $totalVendido * $costoPromedio;
                
                $gananciaDivisas[] = [
                    'moneda' => $venta['moneda'],
                    'vendido' => $totalVendido,
                    'mxn_recibido' => $totalRecibido,
                    'costo_compra' => $costoTotal,
                    'ganancia' => $totalRecibido - $costoTotal
                ];
            }
            $resultado['ganancia_divisas'] = $gananciaDivisas;
            
            // 6. Totales calculados
            $resultado['ganancia_divisas_total'] = array_sum(array_column($gananciaDivisas, 'ganancia'));
            $resultado['ganancia_total'] = $resultado['comisiones_amiri'] + $resultado['ganancia_divisas_total'];
            
            echo json_encode($resultado);
            break;
            
        // ==================== COMISIONES DETALLADAS ====================
        case 'comisiones':
            $subtipo = $_GET['subtipo'] ?? 'todas';
            
            if ($subtipo === 'amiri') {
                // Comisiones AMIRI por día
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(fecha) as fecha,
                        COUNT(*) as operaciones,
                        SUM(monto_total) as total_ventas,
                        SUM(comision_amiri) as comision
                    FROM deudas_clientes
                    WHERE fecha BETWEEN ? AND ? AND activo = 1
                    GROUP BY DATE(fecha)
                    ORDER BY fecha DESC
                ");
                $stmt->execute([$fechaInicio, $fechaFin]);
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($subtipo === 'promotores') {
                // Comisiones por promotor
                $promotorId = $_GET['promotor_id'] ?? null;
                
                if ($promotorId) {
                    // Comisiones de un promotor específico
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(vec.fecha) as fecha,
                            c.nombre as cliente,
                            vec.monto_total,
                            vec.comision_promotor as comision
                        FROM deudas_clientes vec
                        JOIN clientes c ON vec.cliente_id = c.id
                        WHERE vec.fecha BETWEEN ? AND ? 
                        AND vec.activo = 1
                        AND c.promotor_id = ?
                        ORDER BY vec.fecha DESC
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin, $promotorId]);
                } else {
                    // Resumen por promotor
                    $stmt = $pdo->prepare("
                        SELECT 
                            p.id as promotor_id,
                            p.nombre as promotor,
                            COUNT(vec.id) as operaciones,
                            SUM(vec.monto_total) as total_ventas,
                            SUM(vec.comision_promotor) as comision
                        FROM promotores p
                        LEFT JOIN clientes c ON c.promotor_id = p.id
                        LEFT JOIN deudas_clientes vec ON vec.cliente_id = c.id
                            AND vec.fecha BETWEEN ? AND ?
                            AND vec.activo = 1
                        WHERE p.activo = 1
                        GROUP BY p.id, p.nombre
                        HAVING comision > 0
                        ORDER BY comision DESC
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin]);
                }
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } else {
                // Todas las comisiones
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(fecha) as fecha,
                        SUM(comision_amiri) as comision_amiri,
                        SUM(comision_promotor) as comision_promotor,
                        SUM(comision_amiri + comision_promotor) as total
                    FROM deudas_clientes
                    WHERE fecha BETWEEN ? AND ? AND activo = 1
                    GROUP BY DATE(fecha)
                    ORDER BY fecha DESC
                ");
                $stmt->execute([$fechaInicio, $fechaFin]);
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode($resultado);
            break;
            
        // ==================== DIVISAS DETALLADAS ====================
        case 'divisas':
            $moneda = $_GET['moneda'] ?? 'USD';
            
            // Historial de operaciones
            $stmt = $pdo->prepare("
                SELECT 
                    fm.id,
                    fm.fecha,
                    s.nombre as servicio,
                    fm.monto as cantidad,
                    fm.paridad as tipo_cambio,
                    (fm.monto * fm.paridad) as monto_mxn,
                    COALESCE(c.nombre, '-') as cliente,
                    COALESCE(p.nombre, '-') as proveedor
                FROM movimientos fm
                JOIN servicios s ON fm.servicio_id = s.id
                LEFT JOIN clientes c ON fm.cliente_id = c.id
                LEFT JOIN proveedores p ON fm.proveedor_id = p.id
                WHERE fm.moneda = ?
                AND fm.fecha BETWEEN ? AND ?
                AND fm.activo = 1
                ORDER BY fm.fecha DESC, fm.id DESC
            ");
            $stmt->execute([$moneda, $fechaInicio, $fechaFin]);
            $operaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Resumen
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN servicio_id IN (5, 6) THEN monto ELSE 0 END) as total_comprado,
                    SUM(CASE WHEN servicio_id IN (2, 3) THEN monto ELSE 0 END) as total_vendido,
                    SUM(CASE WHEN servicio_id IN (5, 6) THEN monto * paridad ELSE 0 END) as mxn_gastado,
                    SUM(CASE WHEN servicio_id IN (2, 3) THEN monto * paridad ELSE 0 END) as mxn_recibido
                FROM movimientos
                WHERE moneda = ?
                AND fecha BETWEEN ? AND ?
                AND activo = 1
            ");
            $stmt->execute([$moneda, $fechaInicio, $fechaFin]);
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $resumen['ganancia_mxn'] = (float)$resumen['mxn_recibido'] - (float)$resumen['mxn_gastado'];
            
            echo json_encode([
                'operaciones' => $operaciones,
                'resumen' => $resumen
            ]);
            break;
            
        // ==================== GRÁFICAS ====================
        case 'graficas':
            $grafica = $_GET['grafica'] ?? 'comisiones_diarias';
            
            switch ($grafica) {
                case 'comisiones_diarias':
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(fecha) as fecha,
                            SUM(comision_amiri) as amiri,
                            SUM(comision_promotor) as promotores
                        FROM deudas_clientes
                        WHERE fecha BETWEEN ? AND ? AND activo = 1
                        GROUP BY DATE(fecha)
                        ORDER BY fecha
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin]);
                    break;
                    
                case 'ventas_diarias':
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(fecha) as fecha,
                            COUNT(*) as cantidad,
                            SUM(monto_total) as total
                        FROM deudas_clientes
                        WHERE fecha BETWEEN ? AND ? AND activo = 1
                        GROUP BY DATE(fecha)
                        ORDER BY fecha
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin]);
                    break;
                    
                case 'divisas_diarias':
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(fm.fecha) as fecha,
                            fm.moneda,
                            SUM(CASE WHEN fm.servicio_id IN (5, 6) THEN fm.monto ELSE 0 END) as compras,
                            SUM(CASE WHEN fm.servicio_id IN (2, 3) THEN fm.monto ELSE 0 END) as ventas
                        FROM movimientos fm
                        WHERE fm.moneda IN ('USD', 'USDT')
                        AND fm.fecha BETWEEN ? AND ?
                        AND fm.activo = 1
                        GROUP BY DATE(fm.fecha), fm.moneda
                        ORDER BY fecha
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin]);
                    break;
                    
                case 'comisiones_por_promotor':
                    $stmt = $pdo->prepare("
                        SELECT 
                            p.nombre as promotor,
                            SUM(vec.comision_promotor) as comision
                        FROM promotores p
                        JOIN clientes c ON c.promotor_id = p.id
                        JOIN deudas_clientes vec ON vec.cliente_id = c.id
                        WHERE vec.fecha BETWEEN ? AND ?
                        AND vec.activo = 1
                        AND p.activo = 1
                        GROUP BY p.id, p.nombre
                        HAVING comision > 0
                        ORDER BY comision DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$fechaInicio, $fechaFin]);
                    break;
            }
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        // ==================== VENTAS DE EFECTIVO ====================
        case 'ventas_efectivo':
            $stmt = $pdo->prepare("
                SELECT 
                    vec.id,
                    vec.fecha,
                    c.nombre as cliente,
                    p.nombre as promotor,
                    vec.monto_total,
                    vec.comision_amiri,
                    vec.comision_promotor,
                    vec.monto_neto_cliente,
                    vec.estado,
                    vec.monto_pagado,
                    vec.moneda
                FROM deudas_clientes vec
                JOIN clientes c ON vec.cliente_id = c.id
                LEFT JOIN promotores p ON c.promotor_id = p.id
                WHERE vec.fecha BETWEEN ? AND ?
                AND vec.activo = 1
                ORDER BY vec.fecha DESC
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        // ==================== LISTA DE PROMOTORES ====================
        case 'lista_promotores':
            $stmt = $pdo->query("
                SELECT id, nombre 
                FROM promotores 
                WHERE activo = 1 
                ORDER BY nombre
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
