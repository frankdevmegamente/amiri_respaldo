<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



/**

 * Genera un PDF con el historial de comisiones de AMIRI

 */

function generarPDFComisionesAmiri($pdo, $totalComisiones, $totalGastos, $remanente, $graficaData, $params, $filtroFecha, $cuentaAmiriId) {

    // Eliminar el parámetro de cuenta porque ya no es necesario
    if (isset($params[':cuentaId'])) {
        unset($params[':cuentaId']);
    }

    // Obtener detalle de comisiones SOLO de AMIRI (Sincronizado con Reportes)
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

    

    // Generar HTML para el PDF

    header('Content-Type: text/html; charset=UTF-8');

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset="UTF-8">

        <title>Comisiones AMIRI - <?= $periodo ?></title>

        <style>

            body { font-family: Arial, sans-serif; margin: 20px; }

            h1 { color: #333; border-bottom: 3px solid #5D87FF; padding-bottom: 10px; }

            h2 { color: #666; margin-top: 30px; }

            table { width: 100%; border-collapse: collapse; margin-top: 20px; }

            th { background-color: #5D87FF; color: white; padding: 10px; text-align: left; }

            td { padding: 8px; border-bottom: 1px solid #ddd; }

            tr:nth-child(even) { background-color: #f9f9f9; }

            .resumen { background-color: #f0f8ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }

            .resumen div { margin: 5px 0; font-size: 16px; }

            .comision { color: #28a745; font-weight: bold; }

            .total { font-size: 18px; font-weight: bold; }

            .logo { text-align: center; margin-bottom: 20px; }

            .logo h1 { color: #5D87FF; font-size: 2.5rem; margin: 0; }

            @media print {

                body { margin: 0; }

                button { display: none; }

            }

        </style>

    </head>

    <body>

        <div class="logo">

            <h1>🔷 AMIRI</h1>

            <p style="color: #666; margin-top: 0;">Sistema de Gestión de Comisiones</p>

        </div>

        

        <h1>Reporte de Comisiones AMIRI</h1>

        <div class="resumen">

            <div><strong>Período:</strong> <?= $periodo ?></div>

            <div><strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i') ?></div>

        </div>

        

        <div class="resumen">

            <div class="comision"><strong>Comisiones Generadas:</strong> $<?= number_format($totalComisiones, 2) ?></div>

            <div style="color: #dc3545;"><strong>Gastos Generados:</strong> $<?= number_format($totalGastos, 2) ?></div>

            <div class="total" style="color: #0dcaf0;"><strong>Remanente:</strong> $<?= number_format($remanente, 2) ?></div>

        </div>

        

        <h2>Resumen por Fecha</h2>

        <table>

            <thead>

                <tr>

                    <th>Fecha</th>

                    <th style="text-align: right;">Total del Día</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($graficaData as $row): ?>

                <tr>

                    <td><?= htmlspecialchars($row['fecha']) ?></td>

                    <td style="text-align: right;" class="comision">$<?= number_format($row['total'], 2) ?></td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        

        <h2>Detalle de Comisiones</h2>

        <table>

            <thead>

                <tr>

                    <th>Fecha</th>

                    <th>Servicio</th>

                    <th>Cliente</th>

                    <th>Descripción</th>

                    <th style="text-align: right;">Monto</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($comisiones as $com): ?>

                <tr>

                    <td><?= htmlspecialchars($com['fecha']) ?></td>

                    <td><strong><?= htmlspecialchars($com['servicio'] ?? 'N/A') ?></strong></td>

                    <td><?= htmlspecialchars($com['cliente'] ?? '-') ?></td>

                    <td><small><?= htmlspecialchars($com['descripcion']) ?></small></td>

                    <td style="text-align: right;" class="comision">$<?= number_format($com['monto'], 2) ?></td>

                </tr>

                <?php endforeach; ?>

                <tr style="background-color: #e3f2fd; font-weight: bold;">

                    <td colspan="4" style="text-align: right;">TOTAL:</td>

                    <td style="text-align: right;" class="comision">$<?= number_format($totalComisiones, 2) ?></td>

                </tr>

            </tbody>

        </table>

        

        <div style="margin-top: 30px; text-align: center; color: #999;">

            <small>AMIRI - Sistema de Gestión de Comisiones</small>

        </div>

        

        <script>

            window.onload = function() {

                window.print();

            };

        </script>

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



        // Calcular total de gastos y su desglose (Fijos / Variables)
        $sqlGastos = "
            SELECT
                COALESCE(SUM(g.monto), 0) as total,
                COALESCE(SUM(CASE WHEN g.clasificacion = 'fijo' THEN g.monto ELSE 0 END), 0) as fijos,
                COALESCE(SUM(CASE WHEN g.clasificacion = 'variable' THEN g.monto ELSE 0 END), 0) as variables
            FROM movimientos fm
            LEFT JOIN gastos g ON fm.gasto_id = g.id
            WHERE fm.tipo = 'salida' AND fm.activo = 1
            AND (fm.estado = 'activo' OR fm.estado IS NULL) AND (g.id IS NULL OR g.activo = 1)
            AND fm.cuenta_origen_id = :cuentaId";

        $sqlGastos .= $filtroFecha;

        $stmtGastos = $pdo->prepare($sqlGastos);
        $stmtGastos->execute($params);
        $gastosRow = $stmtGastos->fetch(PDO::FETCH_ASSOC);

        $totalGastos = (float)$gastosRow['total'];
        $gastosFijos = (float)$gastosRow['fijos'];
        $gastosVariables = (float)$gastosRow['variables'];

        // Calcular remanente (comisiones - gastos)
        $remanente = $totalComisiones - $totalGastos;

        // GENERAR PDF
        if (isset($_GET['pdf'])) {
            generarPDFComisionesAmiri($pdo, $totalComisiones, $totalGastos, $remanente, $graficaData, $params, $filtroFecha, $cuentaAmiriId);
            exit;
        }

        echo json_encode([
            'total_comisiones' => $totalComisiones,
            'total_gastos' => $totalGastos,
            'gastos_fijos' => $gastosFijos,
            'gastos_variables' => $gastosVariables,
            'remanente' => $remanente,
            'grafica_fechas' => $grafica_fechas,
            'grafica_montos' => $grafica_montos
        ]);
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

