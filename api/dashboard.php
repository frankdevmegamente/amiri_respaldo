<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



/**

 * Genera un PDF con el historial de comisiones de AMIRI

 */

function generarPDFComisionesAmiri($pdo, $totalComisiones, $totalesExtra, $graficaData, $params, $filtroFecha, $cuentaAmiriId) {

    $gananciaBruta = $totalesExtra['ganancia_bruta'];
    $gananciaDivisas = $totalesExtra['ganancia_divisas'];
    $gananciaUSD = $totalesExtra['ganancia_usd'];
    $gananciaUSDT = $totalesExtra['ganancia_usdt'];
    $totalGastos = $totalesExtra['gastos_fijos'] + $totalesExtra['gastos_variables'];
    $gastosFijos = $totalesExtra['gastos_fijos'];
    $gastosVariables = $totalesExtra['gastos_variables'];
    $utilidadNeta = $totalesExtra['utilidad_neta'];
    $utilidadesAcumuladas = $totalesExtra['utilidades_acumuladas'];

    // Eliminar el parámetro de cuenta porque ya no es necesario
    if (isset($params[':cuentaId'])) {
        unset($params[':cuentaId']);
    }

    // Obtener detalle de comisiones SOLO de AMIRI
    $sqlDetalle = "SELECT fm.fecha, 'Comisión Amiri' as descripcion, 'Venta de Efectivo' as servicio, fm.comision_amiri as monto, 
                   cl.nombre as cliente
                   FROM deudas_clientes fm
                   LEFT JOIN clientes cl ON fm.cliente_id = cl.id
                   WHERE fm.activo = 1";
    
    $sqlDetalle .= $filtroFecha . " ORDER BY fm.fecha DESC";

    $stmt = $pdo->prepare($sqlDetalle);
    $stmt->execute($params);
    $comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Determinar período para el título
    $periodo = 'Todo el Historial';
    if (isset($_GET['mes']) && isset($_GET['anio'])) {
        $meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio',
                  '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
        $periodo = $meses[$_GET['mes']] . ' ' . $_GET['anio'];
    } elseif (isset($_GET['anio'])) {
        $periodo = 'Año ' . $_GET['anio'];
    } elseif (isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta'])) {
        $periodo = $_GET['fecha_desde'] . ' al ' . $_GET['fecha_hasta'];
    }

    $fmt = function($n) { return '$' . number_format($n, 2); };
    $diff = $utilidadesAcumuladas - $utilidadNeta;

    // Generar HTML para el PDF
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte Financiero - <?= $periodo ?></title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
            .header { background: #2c3e50; color: white; padding: 18px; margin-bottom: 18px; text-align: center; }
            .header h1 { margin: 0; font-size: 20px; }
            .header p { margin: 4px 0 0; color: #bdc3c7; font-size: 12px; }
            .section { background: #fff; border: 1px solid #dee2e6; padding: 14px; margin-bottom: 12px; }
            .s-title { font-size: 13px; font-weight: bold; padding-bottom: 7px; border-bottom: 2px solid; margin-bottom: 8px; }
            .s-title.g { color: #28a745; border-color: #28a745; }
            .s-title.r { color: #dc3545; border-color: #dc3545; }
            .s-title.b { color: #007bff; border-color: #007bff; }
            .s-title.p { color: #6f42c1; border-color: #6f42c1; }
            table.info { width: 100%; border-collapse: collapse; }
            table.info td { padding: 4px 6px; }
            .lbl { color: #666; }
            .val { font-weight: 600; }
            .val.g { color: #28a745; }
            .val.r { color: #dc3545; }
            .val.b { color: #007bff; }
            .val.p { color: #6f42c1; }
            .val.d { color: #333; }
            table.detail { width: 100%; border-collapse: collapse; margin-top: 5px; }
            table.detail th { background: #5D87FF; color: white; padding: 8px; text-align: left; }
            table.detail td { padding: 6px 8px; border-bottom: 1px solid #eee; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .footer { text-align: center; color: #999; font-size: 9px; margin-top: 18px; padding-top: 8px; border-top: 1px solid #eee; }
            @media print { body { margin: 0; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Reporte Financiero AMIRI</h1>
            <p>Período: <?= htmlspecialchars($periodo) ?> | Generado: <?= date('d/m/Y H:i') ?></p>
        </div>

        <table style="width:100%;border-collapse:collapse;margin-bottom:12px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #28a745;text-align:center;">
                    <div style="font-size:10px;color:#28a745;font-weight:600;">Ganancia Bruta</div>
                    <div style="font-size:16px;font-weight:bold;color:#28a745;margin-top:3px;"><?= $fmt($gananciaBruta) ?></div>
                </td>
                <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #dc3545;text-align:center;">
                    <div style="font-size:10px;color:#dc3545;font-weight:600;">Gastos Totales</div>
                    <div style="font-size:16px;font-weight:bold;color:#dc3545;margin-top:3px;"><?= $fmt($totalGastos) ?></div>
                </td>
                <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #007bff;text-align:center;">
                    <div style="font-size:10px;color:#007bff;font-weight:600;">Utilidad del Mes</div>
                    <div style="font-size:16px;font-weight:bold;color:#007bff;margin-top:3px;"><?= $fmt($utilidadNeta) ?></div>
                </td>
                <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #6f42c1;text-align:center;">
                    <div style="font-size:10px;color:#6f42c1;font-weight:600;">Flujo Acumulado</div>
                    <div style="font-size:16px;font-weight:bold;color:#6f42c1;margin-top:3px;"><?= $fmt($utilidadesAcumuladas) ?></div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="s-title g">(+) Ingresos</div>
            <table class="info">
                <tr><td class="lbl">Comisiones AMIRI</td><td align="right" class="val g"><?= $fmt($totalComisiones) ?></td></tr>
                <tr><td class="lbl">Ganancia Divisas</td><td align="right" class="val g"><?= $fmt($gananciaDivisas) ?></td></tr>
                <tr><td class="lbl" style="padding-left:15px;">&nbsp;&nbsp;&nbsp;• USD (Físico)</td><td align="right" class="val" style="color:#555;"><?= $fmt($gananciaUSD) ?></td></tr>
                <tr><td class="lbl" style="padding-left:15px;">&nbsp;&nbsp;&nbsp;• USDT (Tether)</td><td align="right" class="val" style="color:#555;"><?= $fmt($gananciaUSDT) ?></td></tr>
                <tr><td class="lbl" style="font-weight:bold;padding-top:6px;border-top:2px solid #333;">Ganancia Bruta</td><td align="right" class="val g" style="font-weight:bold;padding-top:6px;border-top:2px solid #333;"><?= $fmt($gananciaBruta) ?></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="s-title r">(-) Gastos</div>
            <table class="info">
                <tr><td class="lbl">Gastos Fijos</td><td align="right" class="val r"><?= $fmt($gastosFijos) ?></td></tr>
                <tr><td class="lbl">Gastos Variables</td><td align="right" class="val r"><?= $fmt($gastosVariables) ?></td></tr>
                <tr><td class="lbl" style="font-weight:bold;padding-top:6px;border-top:2px solid #333;">Gastos Totales</td><td align="right" class="val r" style="font-weight:bold;padding-top:6px;border-top:2px solid #333;"><?= $fmt($totalGastos) ?></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="s-title b">(=) Resultado del Período</div>
            <table class="info">
                <tr><td class="lbl" style="font-size:14px;font-weight:bold;">Utilidad del Mes</td><td align="right" class="val b" style="font-size:15px;font-weight:bold;"><?= $fmt($utilidadNeta) ?></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="s-title p">Histórico</div>
            <table class="info">
                <tr><td class="lbl">Utilidades Acumuladas (Flujo)</td><td align="right" class="val d"><?= $fmt($utilidadesAcumuladas) ?></td></tr>
                <tr><td class="lbl" style="border-bottom:1px dashed #ddd;padding-bottom:6px;">Diferencia (meses anteriores)</td><td align="right" class="val d" style="border-bottom:1px dashed #ddd;padding-bottom:6px;"><?= $fmt($diff) ?></td></tr>
            </table>
        </div>

        <h2 style="font-size:13px;font-weight:bold;color:#333;margin-top:20px;">Detalle de Comisiones</h2>
        <table class="detail">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th style="text-align:right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comisiones as $com): ?>
                <tr>
                    <td><?= htmlspecialchars($com['fecha']) ?></td>
                    <td><?= htmlspecialchars($com['servicio'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($com['cliente'] ?? '-') ?></td>
                    <td style="text-align:right;font-weight:600;color:#28a745;"><?= $fmt($com['monto']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:#e3f2fd;font-weight:bold;">
                    <td colspan="3" style="text-align:right;padding:8px;">TOTAL:</td>
                    <td style="text-align:right;padding:8px;color:#28a745;"><?= $fmt($totalComisiones) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">AMIRI - Sistema de Gestión | Generado el <?= date('d/m/Y H:i:s') ?></div>

        <script>window.onload = function() { window.print(); };</script>
    </body>
    </html>
    <?php
    exit;
}



initApi();

requireAuth(['admin', 'usuario']); // Dashboard accesible para admin y usuario



try {

    $pdo = getPDO();



    // ============================================

    // ENDPOINT: COMISIONES AMIRI

    // ============================================

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'comisiones_amiri') {

        // Construir filtros de fecha

        $filtroFecha = '';

        $params = [];

        

        if (isset($_GET['mes']) && isset($_GET['anio'])) {

            $mes = str_pad($_GET['mes'], 2, '0', STR_PAD_LEFT);

            $anio = $_GET['anio'];

            $filtroFecha = " AND DATE_FORMAT(fm.fecha, '%Y-%m') = :periodo";

            $params[':periodo'] = "$anio-$mes";

        } elseif (isset($_GET['anio'])) {

            $filtroFecha = " AND YEAR(fm.fecha) = :anio";

            $params[':anio'] = $_GET['anio'];

        } elseif (isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta'])) {

            $filtroFecha = " AND fm.fecha BETWEEN :desde AND :hasta";

            $params[':desde'] = $_GET['fecha_desde'];

            $params[':hasta'] = $_GET['fecha_hasta'];

        } elseif (!empty($_SESSION['mes_activo'])) {
            $filtroFecha = " AND DATE_FORMAT(fm.fecha, '%Y-%m') = :periodo";
            $params[':periodo'] = $_SESSION['mes_activo'];
        }



        // Obtener cuenta de comisiones de AMIRI usando el tipo de cuenta correcto

        $stmt = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");

        $cuentaAmiri = $stmt->fetch(PDO::FETCH_ASSOC);

        $cuentaAmiriId = $cuentaAmiri ? (int)$cuentaAmiri['id'] : null;

        $saldoCuenta = $cuentaAmiri ? (float)$cuentaAmiri['saldo_actual'] : 0;



        // Obtenemos los params sin :cuentaId para la nueva consulta
        $paramsComisiones = $params;

        // Total de comisiones generadas SOLO de AMIRI (Sincronizado con Reportes)
        $sql = "SELECT COALESCE(SUM(comision_amiri), 0) as total 
                FROM deudas_clientes fm
                WHERE fm.activo = 1";
        
        $sql .= $filtroFecha;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsComisiones);
        $totalComisiones = (float)$stmt->fetchColumn();

        // Ganancia Divisas (USD + USDT) para sumar al total (Sync con Reportes)
        $filtroFechaMov = str_replace('fm.fecha', 'fecha', $filtroFecha);
        $sqlUSD = "SELECT COALESCE(SUM(monto), 0) FROM movimientos WHERE servicio_id = 2 AND tipo = 'comision' AND cuenta_destino_id = 1 AND activo = 1" . $filtroFechaMov;
        $sqlUSDT = "SELECT COALESCE(SUM(monto), 0) FROM movimientos WHERE servicio_id = 3 AND tipo = 'comision' AND cuenta_destino_id = 1 AND activo = 1" . $filtroFechaMov;
        $stmtUSD = $pdo->prepare($sqlUSD);
        $stmtUSD->execute($paramsComisiones);
        $gananciaUSD = (float)$stmtUSD->fetchColumn();
        $stmtUSDT = $pdo->prepare($sqlUSDT);
        $stmtUSDT->execute($paramsComisiones);
        $gananciaUSDT = (float)$stmtUSDT->fetchColumn();
        $totalGananciaDivisas = $gananciaUSD + $gananciaUSDT;

        // Datos para la gráfica (agrupados por fecha) SOLO de AMIRI (Sincronizado con Reportes)
        $sqlGrafica = "SELECT DATE(fm.fecha) as fecha, SUM(comision_amiri) as total
                       FROM deudas_clientes fm
                       WHERE fm.activo = 1";
        
        $sqlGrafica .= $filtroFecha . " GROUP BY DATE(fm.fecha) ORDER BY fecha ASC";
        
        $stmt = $pdo->prepare($sqlGrafica);
        $stmt->execute($paramsComisiones);
        $graficaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornamos el cuentaId al array principal porque los Gastos de abajo sí lo necesitan
        $params[':cuentaId'] = $cuentaAmiriId;



        $grafica_fechas = [];

        $grafica_montos = [];

        foreach ($graficaData as $row) {

            $grafica_fechas[] = $row['fecha'];

            $grafica_montos[] = (float)$row['total'];

        }



        // Calcular total de gastos y su desglose (Fijos / Variables) - sincronizado con Reportes
        $filtroFechaGastos = str_replace('fm.fecha', 'fecha', $filtroFecha);
        $sqlGastos = "
            SELECT 
                COALESCE(SUM(monto), 0) as total, 
                COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, 
                COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables
            FROM gastos
            WHERE activo = 1";

        $sqlGastos .= $filtroFechaGastos;

        $stmtGastos = $pdo->prepare($sqlGastos);
        $stmtGastos->execute($paramsComisiones);
        $gastosRow = $stmtGastos->fetch(PDO::FETCH_ASSOC);

        $totalGastos = (float)$gastosRow['total'];
        $gastosFijos = (float)$gastosRow['fijos'];
        $gastosVariables = (float)$gastosRow['variables'];

        // Calcular remanente (comisiones + divisas - gastos)
        $remanente = ($totalComisiones + $totalGananciaDivisas) - $totalGastos;

        // Utilidades acumuladas históricas (cuenta Capital)
        $stmtUtil = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1");
        $utilidadesAcumuladas = (float)$stmtUtil->fetchColumn();

        // GENERAR PDF
        if (isset($_GET['pdf'])) {
            $totalesExtra = [
                'ganancia_bruta' => $totalComisiones + $totalGananciaDivisas,
                'ganancia_divisas' => $totalGananciaDivisas,
                'ganancia_usd' => $gananciaUSD,
                'ganancia_usdt' => $gananciaUSDT,
                'gastos_fijos' => $gastosFijos,
                'gastos_variables' => $gastosVariables,
                'utilidad_neta' => $remanente,
                'utilidades_acumuladas' => $utilidadesAcumuladas,
                'comisiones_promotores' => 0,
            ];
            generarPDFComisionesAmiri($pdo, $totalComisiones, $totalesExtra, $graficaData, $params, $filtroFecha, $cuentaAmiriId);
            exit;
        }

        echo json_encode([
            'total_comisiones' => $totalComisiones,
            'total_gastos' => $totalGastos,
            'gastos_fijos' => $gastosFijos,
            'gastos_variables' => $gastosVariables,
            'remanente' => $remanente,
            'utilidades_acumuladas' => $utilidadesAcumuladas,
            'grafica_fechas' => $grafica_fechas,
            'grafica_montos' => $grafica_montos,
            'total_ganancia_divisas' => $totalGananciaDivisas,
            '_explicacion' => [
                'remanente' => 'Utilidad neta del período. Fórmula: (Comisiones AMIRI + Ganancia Divisas) - Gastos Totales. La diferencia con "Utilidades acum. (flujo)" en reportes es el saldo arrastrado de meses anteriores.',
                'total_comisiones' => 'Suma de comision_amiri en deudas_clientes activas del período.',
                'total_gastos' => 'Suma de gastos operativos (fijos + variables) registrados en el período.',
                'total_ganancia_divisas' => 'Utilidad FIFO generada por compra/venta de USD y USDT.',
            ]
        ]);
        exit;

    }

    // ============================================

    // ENDPOINT: FIFO USDT

    // ============================================

    if (isset($_GET['tipo']) && $_GET['tipo'] === 'fifo_usdt') {

        // 1. Lotes de compra USDT
        $stmt = $pdo->prepare("
            SELECT im.id, im.fecha, im.cantidad_original, im.cantidad_restante,
                   im.paridad as precio_compra,
                   ROUND(im.cantidad_original * im.paridad, 2) as total_mxn,
                   m.descripcion
            FROM inventario_monedas im
            JOIN movimientos m ON im.movimiento_id = m.id
            WHERE im.moneda = 'USDT' AND im.activo = 1
            ORDER BY im.fecha ASC, im.id ASC
        ");
        $stmt->execute();
        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ventas con consumos FIFO
        $stmt = $pdo->prepare("
            SELECT ci.movimiento_id as venta_id, mv.fecha as venta_fecha,
                   mv.descripcion as venta_desc,
                   ci.inventario_id as lote_id, ci.cantidad_consumida,
                   ci.paridad_compra, ci.paridad_venta, ci.ganancia,
                   im.fecha as lote_fecha
            FROM consumos_inventario ci
            JOIN inventario_monedas im ON ci.inventario_id = im.id
            JOIN movimientos mv ON ci.movimiento_id = mv.id
            WHERE im.moneda = 'USDT' AND mv.activo = 1
            ORDER BY mv.fecha ASC, ci.inventario_id ASC
        ");
        $stmt->execute();
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
                    'total_usdt' => 0,
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
            $ventas[$vid]['total_usdt'] += (float)$c['cantidad_consumida'];
            $ventas[$vid]['total_mxn'] += (float)$c['cantidad_consumida'] * (float)$c['paridad_venta'];
            $ventas[$vid]['ganancia_total'] += (float)$c['ganancia'];
        }
        $ventas = array_values($ventas);

        echo json_encode([
            'lotes' => $lotes,
            'ventas' => $ventas
        ], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================

    // DASHBOARD PRINCIPAL

    // ============================================



    // Filtro de fecha

    $filtro = $_GET['filtro'] ?? 'mes';

    $fecha_inicio = match ($filtro) {

        'dia' => date('Y-m-d'),

        'semana' => date('Y-m-d', strtotime('-6 days')),

        'anio' => date('Y-01-01'),

        default => date('Y-m-01'), // 'mes' por defecto

    };



    // === DESGLOSE DE CAPITAL (desde movimientos y deudas_clientes) ===
    // CAPITAL: solo tipo 3 (igual que el card de Capital) + deudas_clientes + promotores
    // NO incluir tipo 4 ni 5: esas cuentas se suman via deudas_clientes y promotores para evitar doble conteo
    $stmtCap = $pdo->prepare("SELECT COALESCE(SUM(saldo_actual),0) AS total FROM cuentas WHERE activo=1 AND tipo_cuenta = 3 AND creado_en >= ?");
    $stmtCap->execute([$fecha_inicio]);
    $cliente_efectivo = (float)$stmtCap->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtCliMxn = $pdo->prepare("SELECT COALESCE(SUM(monto_neto_cliente-monto_pagado),0) AS total FROM deudas_clientes WHERE estado IN('pendiente','parcial') AND activo=1 AND (moneda='MXN' OR moneda IS NULL) AND fecha >= ?");
    $stmtCliMxn->execute([$fecha_inicio]);
    $cliente_efectivo += (float)$stmtCliMxn->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtProm = $pdo->prepare("SELECT COALESCE(SUM(c.saldo_actual),0) AS total FROM promotores p INNER JOIN cuentas c ON p.cuenta_id=c.id WHERE p.activo=1 AND p.es_interno=0 AND c.saldo_actual>0.01 AND p.creado_en >= ?");
    $stmtProm->execute([$fecha_inicio]);
    $promotor_efectivo = (float)$stmtProm->fetch(PDO::FETCH_ASSOC)['total'];

    // Comisiones AMIRI desde deudas_clientes
    $stmtCom = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri),0) AS total FROM deudas_clientes WHERE activo=1 AND estado IN('pendiente','parcial') AND fecha >= ?");
    $stmtCom->execute([$fecha_inicio]);
    $comisiones_amiri = (float)$stmtCom->fetch(PDO::FETCH_ASSOC)['total'];

    $capital_total = $cliente_efectivo + $promotor_efectivo + $comisiones_amiri;



    // === PRÉSTAMOS TOTALES (desde deudas_empresa) ===

    $stmt = $pdo->query("

        SELECT COALESCE(SUM(

            CASE

                WHEN pp.estado = 'pendiente' THEN pp.monto

                WHEN pp.estado = 'parcial' THEN (pp.monto - pp.monto_pagado)

                ELSE 0

            END

        ), 0) AS total_prestamos

        FROM deudas_empresa pp

        INNER JOIN prestamistas p ON pp.prestamista_id = p.id

        WHERE p.activo = 1

    ");

    $prestamos_total = (float) $stmt->fetchColumn();



    // === PRÉSTAMOS POR BENEFICIARIO (desde deudas_empresa) ===
    $stmt = $pdo->prepare("
        SELECT
            p.nombre as caja,
            SUM(de.monto - de.monto_pagado) as total
        FROM deudas_empresa de
        JOIN prestamistas p ON de.prestamista_id = p.id
        WHERE de.estado IN ('pendiente', 'parcial')
        AND de.activo = 1
        AND de.fecha >= ?
        GROUP BY p.id, p.nombre
    ");
    $stmt->execute([$fecha_inicio]);
    $prestamos_por_caja = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // === SALDOS POR CUENTA (desde flujo) ===

    // $stmt = $pdo->query("

    //     SELECT f.cuenta,

    //            SUM(CASE WHEN f.tipo = 'entrada' THEN f.monto ELSE -f.monto END) as saldo,

    //            c.nombre as nombre_cuenta,

    //            c.banco as banco_cuenta

    //     FROM flujo f

    //     LEFT JOIN cuentas c ON f.cuenta = c.nombre

    //     GROUP BY f.cuenta

    // ");

    $stmt = $pdo->query("SELECT
            c.nombre as cuenta,
            c.saldo_actual as saldo,
            c.nombre as nombre_cuenta,
            c.banco as banco_cuenta
        FROM cuentas c
        WHERE c.activo = 1 AND c.saldo_actual != 0
        ORDER BY c.nombre
        ");

    $saldos = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $cuentas_resumen = [];

    $saldos_resumen = [];

    foreach ($saldos as $s) {

        $saldoFloat = (float)$s['saldo'];

        if ($saldoFloat != 0.0) {

            $nombre_completo = $s['nombre_cuenta'] ?? $s['cuenta'];

            $banco = $s['banco_cuenta'] ?? '';

            $nombre_formateado = trim("$nombre_completo $banco");

            $cuentas_resumen[] = $nombre_formateado;

            $saldos_resumen[] = $saldoFloat;

        }

    }



    // === FLUJO DIARIO (últimos 30 días) ===

    $stmt = $pdo->prepare("
        SELECT fecha,
               SUM(CASE WHEN tipo IN ('entrada','comision') THEN monto ELSE 0 END) as entradas,
               SUM(CASE WHEN tipo = 'salida' THEN monto ELSE 0 END) as salidas
        FROM movimientos
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND activo = 1
        GROUP BY fecha
        ORDER BY fecha
    ");

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $map = [];

    foreach ($rows as $r) {

        $map[$r['fecha']] = [

            'entradas' => (float)$r['entradas'],

            'salidas'  => (float)$r['salidas']

        ];

    }



    $fechas_flujo = [];

    $entradas = [];

    $salidas = [];

    for ($i = 29; $i >= 0; $i--) {

        $d = date('Y-m-d', strtotime("-$i days"));

        $fechas_flujo[] = $d;

        if (isset($map[$d])) {

            $entradas[] = $map[$d]['entradas'];

            $salidas[] = $map[$d]['salidas'];

        } else {

            $entradas[] = 0.0;

            $salidas[] = 0.0;

        }

    }



    // === DATOS PARA GRÁFICA DE PASTEL ===

    $cuentas_pie = [];

    $saldos_pie = [];

    foreach ($saldos as $s) {

        $saldoFloat = (float)$s['saldo'];

        if ($saldoFloat != 0.0) {

            $cuentas_pie[] = $s['cuenta'];

            $saldos_pie[] = $saldoFloat;

        }

    }



    // === DATOS PARA TABLA DE PRÉSTAMOS (desde deudas_empresa) ===

    $stmt_prestamistas = $pdo->query("

        SELECT p.nombre,

               COALESCE(SUM(

                   CASE

                       WHEN pp.estado = 'pendiente' THEN pp.monto

                       WHEN pp.estado = 'parcial' THEN (pp.monto - pp.monto_pagado)

                       ELSE 0

                   END

               ), 0) AS saldo

        FROM prestamistas p

        LEFT JOIN deudas_empresa pp ON p.id = pp.prestamista_id

        WHERE p.activo = 1

        GROUP BY p.id, p.nombre

        HAVING saldo != 0

        ORDER BY saldo DESC

    ");

    $prestamistas_saldos = $stmt_prestamistas->fetchAll(PDO::FETCH_ASSOC);



    echo json_encode([

        'capital' => [

            'total' => $capital_total,

            'cliente_efectivo' => $cliente_efectivo,

            'promotor_efectivo' => $promotor_efectivo,

            'comisiones_amiri' => $comisiones_amiri

        ],

        'prestamos' => $prestamos_total,

        'prestamos_por_caja' => $prestamos_por_caja,

        'prestamistas_saldos' => $prestamistas_saldos,

        'total_saldos' => array_sum($saldos_resumen),

        'dif' => array_sum($saldos_resumen) - $capital_total,

        'flujo' => [

            'fechas' => $fechas_flujo,

            'entradas' => $entradas,

            'salidas' => $salidas

        ],

        'saldos_pie' => [

            'cuentas' => $cuentas_pie,

            'saldos' => $saldos_pie

        ],

        'saldos_resumen' => [

            'cuentas' => $cuentas_resumen,

            'saldos' => $saldos_resumen

        ]

    ], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

} catch (PDOException $ex) {

    http_response_code(500);

    error_log("DB error en dashboard.php: " . $ex->getMessage());

    echo json_encode(['error' => 'Error interno del servidor: ' . $ex->getMessage()]);

    exit;

}

