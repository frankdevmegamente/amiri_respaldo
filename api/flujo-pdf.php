<?php

// api/flujo-pdf.php - Generar PDF de Flujo con Filtros

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../vendor/autoload.php';



use Dompdf\Dompdf;

use Dompdf\Options;



initApi();

requireAdmin();

$pdo = getPDO();



try {

    // Obtener filtros

    $moneda = $_GET['moneda'] ?? 'MXN';

    $anio = $_GET['anio'] ?? '';

    $mes = $_GET['mes'] ?? '';

    $fechaDesde = $_GET['fecha_desde'] ?? '';

    $fechaHasta = $_GET['fecha_hasta'] ?? '';

    $servicio = $_GET['servicio'] ?? '';



    // Construir condiciones WHERE

    $whereConditions = ["fm.activo = 1"];

    $params = [];



    if ($moneda !== 'TODAS') {

        $whereConditions[] = "fm.moneda = :moneda";

        $params[':moneda'] = $moneda;

    }



    if (!empty($anio)) {

        $whereConditions[] = "YEAR(fm.fecha) = :anio";

        $params[':anio'] = $anio;

    }



    if (!empty($mes)) {

        $whereConditions[] = "MONTH(fm.fecha) = :mes";

        $params[':mes'] = ltrim($mes, '0');

    }



    if (!empty($fechaDesde)) {

        $whereConditions[] = "fm.fecha >= :fecha_desde";

        $params[':fecha_desde'] = $fechaDesde;

    }



    if (!empty($fechaHasta)) {

        $whereConditions[] = "fm.fecha <= :fecha_hasta";

        $params[':fecha_hasta'] = $fechaHasta;

    }



    if (!empty($servicio)) {

        $whereConditions[] = "s.nombre = :servicio";

        $params[':servicio'] = $servicio;

    }



    $whereClause = implode(' AND ', $whereConditions);



    // Query para obtener datos agrupados

    $sql = "

        SELECT 

            MIN(fm.id) as id,

            fm.grupo_id,

            fm.fecha,

            s.nombre as servicio,

            COUNT(*) as cantidad_movimientos,

            fm.moneda,

            SUM(CASE WHEN fm.tipo = 'entrada' THEN fm.monto ELSE 0 END) as total_entradas,

            SUM(CASE WHEN fm.tipo = 'salida' THEN fm.monto ELSE 0 END) as total_salidas,

            SUM(CASE WHEN fm.tipo = 'comision' THEN fm.monto ELSE 0 END) as total_comisiones,

            GREATEST(

                MAX(CASE WHEN fm.tipo = 'entrada' THEN fm.monto ELSE 0 END),

                MAX(CASE WHEN fm.tipo = 'salida' THEN fm.monto ELSE 0 END),

                MAX(CASE WHEN fm.tipo = 'traspaso' THEN fm.monto ELSE 0 END)

            ) as monto_principal,

            GROUP_CONCAT(DISTINCT COALESCE(co.nombre, '') SEPARATOR ', ') as cuentas_origen,

            GROUP_CONCAT(DISTINCT COALESCE(cd.nombre, '') SEPARATOR ', ') as cuentas_destino,

            MAX(COALESCE(cl.nombre, '')) as cliente,

            MAX(COALESCE(p.nombre, '')) as proveedor,

            MAX(COALESCE(pr.nombre, '')) as promotor,

            MAX(fm.paridad) as paridad,

            MAX(fm.descripcion) as descripcion

        FROM movimientos fm

        LEFT JOIN servicios s ON fm.servicio_id = s.id

        LEFT JOIN cuentas co ON fm.cuenta_origen_id = co.id

        LEFT JOIN cuentas cd ON fm.cuenta_destino_id = cd.id

        LEFT JOIN clientes cl ON fm.cliente_id = cl.id

        LEFT JOIN proveedores p ON fm.proveedor_id = p.id

        LEFT JOIN promotores pr ON fm.promotor_id = pr.id

        WHERE $whereClause

        GROUP BY fm.grupo_id, fm.fecha, s.nombre, fm.moneda

        ORDER BY fm.fecha DESC

    ";



    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {

        $stmt->bindValue($key, $value);

    }

    $stmt->execute();

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // Calcular totales

    $totalMontoEntradas = array_sum(array_column($datos, 'total_entradas'));

    $totalMontoSalidas = array_sum(array_column($datos, 'total_salidas'));

    $totalMontoComisiones = array_sum(array_column($datos, 'total_comisiones'));

    $totalMovimientos = count($datos);



    // Crear título del reporte

    $tituloFiltros = [];

    if (!empty($anio)) $tituloFiltros[] = "Año: $anio";

    if (!empty($mes)) {

        $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', 

                  '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];

        $tituloFiltros[] = "Mes: " . $meses[$mes];

    }

    if (!empty($fechaDesde)) $tituloFiltros[] = "Desde: " . date('d/m/Y', strtotime($fechaDesde));

    if (!empty($fechaHasta)) $tituloFiltros[] = "Hasta: " . date('d/m/Y', strtotime($fechaHasta));

    if (!empty($servicio)) $tituloFiltros[] = "Servicio: $servicio";

    

    $subtitulo = !empty($tituloFiltros) ? implode(' | ', $tituloFiltros) : 'Todos los movimientos';



    // Crear HTML para el PDF

    $html = '

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset="UTF-8">

        <style>

            body {

                font-family: Arial, sans-serif;

                font-size: 10px;

                margin: 20px;

            }

            .header {

                text-align: center;

                margin-bottom: 20px;

                border-bottom: 3px solid #004aad;

                padding-bottom: 10px;

            }

            .header h1 {

                color: #004aad;

                margin: 0;

                font-size: 24px;

            }

            .header .subtitle {

                color: #666;

                font-size: 11px;

                margin-top: 5px;

            }

            .info-box {

                background-color: #f8f9fa;

                padding: 10px;

                border-radius: 5px;

                margin-bottom: 15px;

            }

            .info-box .label {

                font-weight: bold;

                color: #333;

            }

            table {

                width: 100%;

                border-collapse: collapse;

                margin-top: 10px;

            }

            th {

                background-color: #004aad;

                color: white;

                padding: 8px;

                text-align: left;

                font-size: 9px;

            }

            td {

                padding: 6px;

                border-bottom: 1px solid #ddd;

                font-size: 9px;

            }

            tr:nth-child(even) {

                background-color: #f9f9f9;

            }

            .totales {

                margin-top: 15px;

                padding: 10px;

                background-color: #e8f4f8;

                border-left: 4px solid #004aad;

            }

            .totales .row {

                display: flex;

                justify-content: space-between;

                margin: 5px 0;

            }

            .totales .label {

                font-weight: bold;

            }

            .text-success {

                color: #28a745;

                font-weight: bold;

            }

            .text-danger {

                color: #dc3545;

                font-weight: bold;

            }

            .text-warning {

                color: #ffc107;

                font-weight: bold;

            }

            .footer {

                position: fixed;

                bottom: 0;

                width: 100%;

                text-align: center;

                font-size: 8px;

                color: #666;

                border-top: 1px solid #ddd;

                padding-top: 5px;

            }

            .badge {

                display: inline-block;

                padding: 3px 8px;

                border-radius: 3px;

                font-size: 8px;

                font-weight: bold;

            }

            .badge-secondary {

                background-color: #6c757d;

                color: white;

            }

            .badge-info {

                background-color: #17a2b8;

                color: white;

            }

        </style>

    </head>

    <body>

        <div class="header">

            <h1>📊 REPORTE DE FLUJO - SISTEMA AMIRI</h1>

            <div class="subtitle">Moneda: ' . htmlspecialchars($moneda) . ' | ' . $subtitulo . '</div>

            <div class="subtitle">Generado: ' . date('d/m/Y H:i:s') . '</div>

        </div>



        <div class="info-box">

            <span class="label">Total de Movimientos:</span> ' . $totalMovimientos . ' | 

            <span class="label">Entradas:</span> <span class="text-success">$' . number_format($totalMontoEntradas, 2) . '</span> | 

            <span class="label">Salidas:</span> <span class="text-danger">$' . number_format($totalMontoSalidas, 2) . '</span> | 

            <span class="label">Comisiones:</span> <span class="text-warning">$' . number_format($totalMontoComisiones, 2) . '</span>

        </div>



        <table>

            <thead>

                <tr>

                    <th>Grupo</th>

                    <th>Fecha</th>

                    <th>Servicio</th>

                    <th>Movs.</th>

                    <th>Monto Principal</th>

                    <th>Moneda</th>

                    <th>Cliente</th>

                    <th>Proveedor</th>

                    <th>Promotor</th>

                </tr>

            </thead>

            <tbody>';



    foreach ($datos as $row) {

        $html .= '

                <tr>

                    <td><span class="badge badge-secondary">#' . htmlspecialchars($row['grupo_id']) . '</span></td>

                    <td>' . date('d/m/Y', strtotime($row['fecha'])) . '</td>

                    <td><strong>' . htmlspecialchars($row['servicio']) . '</strong></td>

                    <td><span class="badge badge-info">' . $row['cantidad_movimientos'] . '</span></td>

                    <td class="text-success">$' . number_format($row['monto_principal'], 2) . '</td>

                    <td>' . htmlspecialchars($row['moneda']) . '</td>

                    <td>' . htmlspecialchars($row['cliente'] ?: '-') . '</td>

                    <td>' . htmlspecialchars($row['proveedor'] ?: '-') . '</td>

                    <td>' . htmlspecialchars($row['promotor'] ?: '-') . '</td>

                </tr>';

    }



    $html .= '

            </tbody>

        </table>



        <div class="totales">

            <div class="row">

                <span class="label">TOTAL ENTRADAS:</span>

                <span class="text-success">$' . number_format($totalMontoEntradas, 2) . ' ' . $moneda . '</span>

            </div>

            <div class="row">

                <span class="label">TOTAL SALIDAS:</span>

                <span class="text-danger">$' . number_format($totalMontoSalidas, 2) . ' ' . $moneda . '</span>

            </div>

            <div class="row">

                <span class="label">TOTAL COMISIONES:</span>

                <span class="text-warning">$' . number_format($totalMontoComisiones, 2) . ' ' . $moneda . '</span>

            </div>

            <div class="row">

                <span class="label">BALANCE:</span>

                <span class="' . ($totalMontoEntradas - $totalMontoSalidas >= 0 ? 'text-success' : 'text-danger') . '">

                    $' . number_format($totalMontoEntradas - $totalMontoSalidas, 2) . ' ' . $moneda . '

                </span>

            </div>

        </div>



        <div class="footer">

            Sistema Amiri - Reporte de Flujo | Página {PAGE_NUM} de {PAGE_COUNT}

        </div>

    </body>

    </html>';



    // Configurar Dompdf

    $options = new Options();

    $options->set('isHtml5ParserEnabled', true);

    $options->set('isRemoteEnabled', true);

    $options->set('defaultFont', 'Arial');



    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'landscape');

    $dompdf->render();



    // Nombre del archivo

    $nombreArchivo = 'Flujo_' . $moneda . '_' . date('Y-m-d_His') . '.pdf';



    // Enviar PDF al navegador

    $dompdf->stream($nombreArchivo, ['Attachment' => false]);



} catch (Exception $e) {

    error_log('Error en flujo-pdf: ' . $e->getMessage());

    echo 'Error al generar el PDF: ' . $e->getMessage();

}

