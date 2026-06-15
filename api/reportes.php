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
            
            // 1. Comisiones AMIRI total (todos los estados)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_amiri'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 1b. Comisiones AMIRI cobradas (solo pagado)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'pagado'");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_amiri_cobradas'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 1c. Comisiones AMIRI por cobrar (pendiente + parcial)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1 AND estado IN ('pendiente','parcial')");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_amiri_por_cobrar'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 2. Comisiones Promotores total (todos los estados)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_promotor), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_promotores'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 2b. Comisiones Promotores cobradas (solo pagado)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_promotor), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'pagado'");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_promotores_cobradas'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 2c. Comisiones Promotores por cobrar (pendiente + parcial)
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_promotor), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1 AND estado IN ('pendiente','parcial')");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['comisiones_promotores_por_cobrar'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // 3. Total ventas de efectivo
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto_total), 0) as total, COUNT(*) as cantidad FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['ventas_efectivo_total'] = (float)$row['total'];
            $resultado['ventas_efectivo_cantidad'] = (int)$row['cantidad'];
            
            // 4. Operaciones de divisas
            $stmt = $pdo->prepare("
                SELECT fm.moneda, s.nombre as servicio, COUNT(DISTINCT fm.grupo_id) as operaciones, SUM(fm.monto) as total_monto, AVG(fm.paridad) as tc_promedio
                FROM movimientos fm JOIN servicios s ON fm.servicio_id = s.id
                WHERE fm.moneda IN ('USD', 'USDT') AND fm.fecha BETWEEN ? AND ? AND fm.activo = 1
                AND ((fm.servicio_id IN (5, 6) AND fm.tipo = 'entrada') OR (fm.servicio_id IN (2, 3) AND fm.tipo = 'salida'))
                GROUP BY fm.moneda, fm.servicio_id, s.nombre
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $resultado['operaciones_divisas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 5. Ganancia REAL en divisas (utilidad FIFO exacta desglosada)
            // Utilidad de Dólares Físicos (Servicio ID 2 = Venta de USD)
            $stmtUSD = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 2 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1");
            $stmtUSD->execute([$fechaInicio, $fechaFin]);
            $gananciaUSD = (float)$stmtUSD->fetchColumn();

            // Utilidad de USDT (Servicio ID 3 = Venta de USDT)
            $stmtUSDT = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 3 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1");
            $stmtUSDT->execute([$fechaInicio, $fechaFin]);
            $gananciaUSDT = (float)$stmtUSDT->fetchColumn();

            $resultado['ganancia_usd'] = $gananciaUSD;
            $resultado['ganancia_usdt'] = $gananciaUSDT;
            $resultado['ganancia_divisas_total'] = $gananciaUSD + $gananciaUSDT;
            
            // 6. Consultar Gastos Operativos
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(monto), 0) as total_gastos, 
                    COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as gastos_fijos, 
                    COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as gastos_variables
                FROM gastos
                WHERE fecha BETWEEN ? AND ? AND activo = 1
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            $gastos = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['gastos_total'] = (float)$gastos['total_gastos'];
            $resultado['gastos_fijos'] = (float)$gastos['gastos_fijos'];
            $resultado['gastos_variables'] = (float)$gastos['gastos_variables'];

            // 6b. Saldo acumulado de Utilidades AMIRI
            $stmtUtil = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1");
            $resultado['utilidades_acumuladas'] = (float)$stmtUtil->fetchColumn();

            // 7. Totales calculados (devengo: todas las comisiones, no solo cobradas)
            $resultado['ganancia_bruta'] = $resultado['comisiones_amiri'] + $resultado['ganancia_divisas_total'];
            $resultado['ganancia_bruta_por_cobrar'] = $resultado['comisiones_amiri_por_cobrar'];
            $resultado['resultado_bruta_menos_fijos'] = $resultado['ganancia_bruta'] - $resultado['gastos_fijos'];
            $resultado['resultado_operativo_real'] = $resultado['ganancia_bruta'] - $resultado['gastos_total'];
            $resultado['ganancia_neta'] = $resultado['resultado_operativo_real'];

            // Metadata explicativa para tooltips en frontend
            $resultado['_explicacion'] = [
                'comisiones_amiri' => 'Suma de la columna comision_amiri en la tabla deudas_clientes (todos los estados: pagado + pendiente + parcial). Representa la comisión que AMIRI retiene por cada venta de efectivo.',
                'comisiones_amiri_cobradas' => 'Suma de comision_amiri solo de registros con estado = pagado en deudas_clientes.',
                'comisiones_amiri_por_cobrar' => 'Suma de comision_amiri de registros con estado pendiente o parcial — comisiones generadas pero aún no cobradas.',
                'comisiones_promotores' => 'Suma de comision_promotor en deudas_clientes. Es la parte de la comisión que se paga al promotor que captó la venta.',
                'ganancia_usd' => 'Utilidad FIFO generada por la compra/venta de Dólares Físicos (servicio Venta USD). Corresponde a movimientos tipo comision del servicio_id = 2.',
                'ganancia_usdt' => 'Utilidad FIFO generada por la compra/venta de USDT (servicio Venta USDT). Corresponde a movimientos tipo comision del servicio_id = 3.',
                'ganancia_divisas_total' => 'Suma de ganancia USD + ganancia USDT. Representa la utilidad total generada por operaciones de divisas (FIFO).',
                'ventas_efectivo_total' => 'Suma de monto_total en deudas_clientes activas. Representa el volumen total de ventas de efectivo realizadas en el período.',
                'ventas_efectivo_cantidad' => 'Número total de operaciones de venta de efectivo registradas en el período.',
                'gastos_total' => 'Suma de todos los gastos operativos registrados en la tabla gastos (fijos + variables).',
                'gastos_fijos' => 'Gastos operativos clasificados como fijos (renta, sueldos, servicios básicos, etc.).',
                'gastos_variables' => 'Gastos operativos clasificados como variables (comisiones externas, transportes, etc.).',
                'ganancia_bruta' => 'Resultado operativo antes de gastos. Fórmula: Comisiones AMIRI (devengadas) + Ganancia Divisas (FIFO). Representa el ingreso total generado por la operación.',
                'resultado_operativo_real' => 'Ganancia o pérdida real del período. Fórmula: Ganancia Bruta - Gastos Totales. Refleja el resultado neto de las operaciones del negocio.',
                'utilidades_acumuladas' => 'Saldo acumulado histórico de la cuenta "Utilidades AMIRI" (Capital). Incluye utilidades de períodos anteriores más el resultado del mes actual. La diferencia contra "Utilidades del mes" es el saldo arrastrado de meses previos.',
            ];
            
            echo json_encode($resultado);
            break;
        case 'comisiones':
            $subtipo = $_GET['subtipo'] ?? 'todas';
            
            if ($subtipo === 'amiri') {
                $stmt = $pdo->prepare("
                    SELECT 
                        MAX(creado_en) as creado_en, 
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
                $promotorId = $_GET['promotor_id'] ?? null;
                
                if ($promotorId) {
                    $stmt = $pdo->prepare("
                        SELECT 
                            vec.creado_en, 
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
                $stmt = $pdo->prepare("
                    SELECT 
                        MAX(creado_en) as creado_en, 
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
                    fm.creado_en, 
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
                AND (
                    (fm.servicio_id IN (5, 6) AND fm.tipo = 'entrada') OR 
                    (fm.servicio_id IN (2, 3) AND fm.tipo = 'salida')
                )
                ORDER BY fm.fecha DESC, fm.id DESC
                LIMIT 500
            ");
            $stmt->execute([$moneda, $fechaInicio, $fechaFin]);
            $operaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Resumen 
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN servicio_id IN (5, 6) AND tipo = 'entrada' THEN monto ELSE 0 END) as total_comprado,
                    SUM(CASE WHEN servicio_id IN (2, 3) AND tipo = 'salida' THEN monto ELSE 0 END) as total_vendido,
                    SUM(CASE WHEN servicio_id IN (5, 6) AND tipo = 'entrada' THEN monto * paridad ELSE 0 END) as mxn_gastado,
                    SUM(CASE WHEN servicio_id IN (2, 3) AND tipo = 'salida' THEN monto * paridad ELSE 0 END) as mxn_recibido
                FROM movimientos
                WHERE moneda = ?
                AND fecha BETWEEN ? AND ?
                AND activo = 1
            ");
            $stmt->execute([$moneda, $fechaInicio, $fechaFin]);
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 🔥 CORRECCIÓN: Buscar la utilidad por el ID del servicio, no por la moneda
            // Si el usuario eligió USDT, buscamos el servicio 3. Si eligió USD, buscamos el 2.
            $servicioVenta = ($moneda === 'USDT') ? 3 : 2;
            
            $stmtGanancia = $pdo->prepare("
                SELECT COALESCE(SUM(monto), 0) as ganancia_real
                FROM movimientos 
                WHERE servicio_id = ? 
                AND tipo = 'comision'
                AND fecha BETWEEN ? AND ?
                AND activo = 1
            ");
            $stmtGanancia->execute([$servicioVenta, $fechaInicio, $fechaFin]);
            
            // Pisamos la variable con la Ganancia Real
            $resumen['ganancia_mxn'] = (float)$stmtGanancia->fetch(PDO::FETCH_ASSOC)['ganancia_real'];
            
            // Dato extra por si quieres poner "Flujo de Caja" en tu HTML después
            $resumen['flujo_caja'] = (float)$resumen['mxn_recibido'] - (float)$resumen['mxn_gastado'];
            
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
                    // CORREGIDO: Sin duplicar volumen en las gráficas de líneas/barras
                    $stmt = $pdo->prepare("
                        SELECT 
                            DATE(fm.fecha) as fecha,
                            fm.moneda,
                            SUM(CASE WHEN fm.servicio_id IN (5, 6) AND fm.tipo = 'entrada' THEN fm.monto ELSE 0 END) as compras,
                            SUM(CASE WHEN fm.servicio_id IN (2, 3) AND fm.tipo = 'salida' THEN fm.monto ELSE 0 END) as ventas
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
                    vec.creado_en, 
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
                LIMIT 500
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