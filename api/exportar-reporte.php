<?php

/**

 * API de Exportación de Reportes

 * Genera archivos Excel, CSV y PDF

 */



require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../vendor/autoload.php';



use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Writer\Csv;

use PhpOffice\PhpSpreadsheet\Style\Fill;

use PhpOffice\PhpSpreadsheet\Style\Border;

use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Dompdf\Dompdf;

use Dompdf\Options;



if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    die('No autorizado');

}



$pdo = getPDO();



// Parámetros

$formato = $_GET['formato'] ?? 'excel'; // excel, csv, pdf

$reporte = $_GET['reporte'] ?? 'comisiones';

$periodo = $_GET['periodo'] ?? 'mes';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');

$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');



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

}



// Obtener datos según el tipo de reporte

$datos = [];

$titulo = '';

$columnas = [];



switch ($reporte) {

    case 'comisiones':

        $titulo = 'Reporte de Comisiones';

        $columnas = ['Fecha', 'Operaciones', 'Comisión AMIRI', 'Comisión Promotor', 'Total'];

        

        $stmt = $pdo->prepare("

            SELECT 

                DATE(fecha) as fecha,

                COUNT(*) as operaciones,

                SUM(comision_amiri) as comision_amiri,

                SUM(comision_promotor) as comision_promotor,

                SUM(comision_amiri + comision_promotor) as total

            FROM deudas_clientes

            WHERE fecha BETWEEN ? AND ? AND activo = 1

            GROUP BY DATE(fecha)

            ORDER BY fecha DESC

        ");

        $stmt->execute([$fechaInicio, $fechaFin]);

        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        break;

        

    case 'comisiones_promotores':

        $titulo = 'Comisiones por Promotor';

        $columnas = ['Promotor', 'Operaciones', 'Total Ventas', 'Comisión'];

        

        $stmt = $pdo->prepare("

            SELECT 

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

        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        break;

        

    case 'divisas':

        $moneda = $_GET['moneda'] ?? 'USD';

        $titulo = "Operaciones de Divisas ($moneda)";

        $columnas = ['Fecha', 'Servicio', 'Cantidad', 'Tipo Cambio', 'Monto MXN', 'Cliente/Proveedor'];

        

        $stmt = $pdo->prepare("

            SELECT 

                fm.fecha,

                s.nombre as servicio,

                fm.monto as cantidad,

                fm.paridad as tipo_cambio,

                (fm.monto * fm.paridad) as monto_mxn,

                COALESCE(c.nombre, p.nombre, '-') as persona

            FROM movimientos fm

            JOIN servicios s ON fm.servicio_id = s.id

            LEFT JOIN clientes c ON fm.cliente_id = c.id

            LEFT JOIN proveedores p ON fm.proveedor_id = p.id

            WHERE fm.moneda = ?

            AND fm.fecha BETWEEN ? AND ?

            AND fm.activo = 1

            ORDER BY fm.fecha DESC

        ");

        $stmt->execute([$moneda, $fechaInicio, $fechaFin]);

        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        break;

        

    case 'ventas_efectivo':

        $titulo = 'Ventas de Efectivo';

        $columnas = ['Fecha', 'Cliente', 'Promotor', 'Monto Total', 'Com. AMIRI', 'Com. Promotor', 'Estado', 'Moneda'];

        

        $stmt = $pdo->prepare("

            SELECT 

                vec.fecha,

                c.nombre as cliente,

                COALESCE(p.nombre, '-') as promotor,

                vec.monto_total,

                vec.comision_amiri,

                vec.comision_promotor,

                vec.estado,

                vec.moneda

            FROM deudas_clientes vec

            JOIN clientes c ON vec.cliente_id = c.id

            LEFT JOIN promotores p ON c.promotor_id = p.id

            WHERE vec.fecha BETWEEN ? AND ?

            AND vec.activo = 1

            ORDER BY vec.fecha DESC

        ");

        $stmt->execute([$fechaInicio, $fechaFin]);

        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        break;

        

    case 'resumen':

        $titulo = 'Resumen General';

        $columnas = ['Concepto', 'Valor'];

        

        // Comisiones AMIRI
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $comisionesAmiri = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Comisiones Promotores
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_promotor), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $comisionesPromotores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Ventas de efectivo
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto_total), 0) as total, COUNT(*) as cantidad FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ganancia Divisas (FIFO real: comisiones de servicios 2 y 3)
        $stmtUSD = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 2 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
        $stmtUSD->execute([$fechaInicio, $fechaFin]);
        $gananciaUSD = (float)$stmtUSD->fetchColumn();

        $stmtUSDT = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id = 3 AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
        $stmtUSDT->execute([$fechaInicio, $fechaFin]);
        $gananciaUSDT = (float)$stmtUSDT->fetchColumn();

        $gananciaDivisas = $gananciaUSD + $gananciaUSDT;
        $gananciaBruta = $comisionesAmiri + $gananciaDivisas;

        // Gastos operativos
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
        $gastosTotal = (float)$gastos['total_gastos'];
        $gastosFijos = (float)$gastos['gastos_fijos'];
        $gastosVariables = (float)$gastos['gastos_variables'];

        // Utilidad del mes
        $utilidadNeta = $gananciaBruta - $gastosTotal;

        // Utilidades acumuladas (flujo histórico)
        $stmtUtil = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1");
        $utilidadesAcumuladas = (float)$stmtUtil->fetchColumn();

        $datos = [
            ['concepto' => 'Comisiones AMIRI', 'valor' => '$' . number_format($comisionesAmiri, 2)],
            ['concepto' => 'Comisiones Promotores', 'valor' => '$' . number_format($comisionesPromotores, 2)],
            ['concepto' => 'Total Ventas Efectivo', 'valor' => '$' . number_format($ventas['total'], 2)],
            ['concepto' => 'Cantidad Ventas', 'valor' => $ventas['cantidad']],
            ['concepto' => 'Ganancia Divisas (USD + USDT)', 'valor' => '$' . number_format($gananciaDivisas, 2)],
            ['concepto' => 'Ganancia Bruta', 'valor' => '$' . number_format($gananciaBruta, 2)],
            ['concepto' => 'Gastos Fijos', 'valor' => '$' . number_format($gastosFijos, 2)],
            ['concepto' => 'Gastos Variables', 'valor' => '$' . number_format($gastosVariables, 2)],
            ['concepto' => 'Gastos Totales', 'valor' => '$' . number_format($gastosTotal, 2)],
            ['concepto' => 'Utilidad del Mes', 'valor' => '$' . number_format($utilidadNeta, 2)],
            ['concepto' => 'Utilidades Acum. (Flujo)', 'valor' => '$' . number_format($utilidadesAcumuladas, 2)],
        ];

        break;

    case 'utilidades':
        $titulo = 'Reporte de Utilidades';

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(comision_amiri), 0) as total FROM deudas_clientes WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $comisiones = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total FROM movimientos WHERE servicio_id IN (2,3) AND tipo = 'comision' AND cuenta_destino_id = 1 AND fecha BETWEEN ? AND ? AND activo = 1 AND estado = 'activo'");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $divisas = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COALESCE(SUM(CASE WHEN clasificacion = 'fijo' THEN monto ELSE 0 END), 0) as fijos, COALESCE(SUM(CASE WHEN clasificacion = 'variable' THEN monto ELSE 0 END), 0) as variables FROM gastos WHERE fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $g = $stmt->fetch(PDO::FETCH_ASSOC);

        $bruta = $comisiones + $divisas;
        $neta = $bruta - (float)$g['total'];

        $datos = [
            ['concepto' => 'Comisiones AMIRI', 'valor' => '$' . number_format($comisiones, 2)],
            ['concepto' => 'Ganancia Divisas (USD+USDT)', 'valor' => '$' . number_format($divisas, 2)],
            ['concepto' => 'Utilidad Bruta', 'valor' => '$' . number_format($bruta, 2)],
            ['concepto' => 'Gastos Fijos', 'valor' => '$' . number_format($g['fijos'], 2)],
            ['concepto' => 'Gastos Variables', 'valor' => '$' . number_format($g['variables'], 2)],
            ['concepto' => 'Gastos Totales', 'valor' => '$' . number_format($g['total'], 2)],
            ['concepto' => 'Utilidad Neta', 'valor' => '$' . number_format($neta, 2)],
        ];
        $columnas = ['Concepto', 'Valor'];
        break;

    case 'servicios':
        $titulo = 'Reporte de Servicios';

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COUNT(*) as cantidad FROM movimientos WHERE servicio_id = 7 AND fecha BETWEEN ? AND ? AND activo = 1 AND tipo = 'traspaso'");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $retiros = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(dc.monto_total), 0) as total, COUNT(*) as cantidad FROM deudas_clientes dc WHERE dc.fecha BETWEEN ? AND ? AND dc.activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $depositos = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total, COUNT(*) as cantidad FROM movimientos WHERE servicio_id IN (11, 13, 14, 15) AND fecha BETWEEN ? AND ? AND activo = 1");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $transferencias = $stmt->fetch(PDO::FETCH_ASSOC);

        $datos = [
            ['concepto' => 'Total Retiros Efectivo', 'valor' => '$' . number_format($retiros['total'], 2)],
            ['concepto' => 'Cantidad Retiros', 'valor' => $retiros['cantidad']],
            ['concepto' => 'Total Depósitos', 'valor' => '$' . number_format($depositos['total'], 2)],
            ['concepto' => 'Cantidad Depósitos', 'valor' => $depositos['cantidad']],
            ['concepto' => 'Total Transferencias', 'valor' => '$' . number_format($transferencias['total'], 2)],
            ['concepto' => 'Cantidad Transferencias', 'valor' => $transferencias['cantidad']],
        ];
        $columnas = ['Concepto', 'Valor'];
        break;

    case 'capital_amiri':
        $titulo = 'Capital Amiri';

        $stmt = $pdo->query("SELECT * FROM cierres_mensuales ORDER BY mes DESC LIMIT 1");
        $ultimoCorte = $stmt->fetch(PDO::FETCH_ASSOC);
        $capitalCerrado = $ultimoCorte ? (float)$ultimoCorte['capital_acumulado'] : 0;

        $stmt = $pdo->query("SELECT COALESCE(SUM(saldo_actual), 0) as total FROM cuentas WHERE tipo_cuenta = 7 AND activo = 1");
        $utilidadesAcumuladas = (float)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM retiros_capital");
        $totalRetiros = (float)$stmt->fetchColumn();

        $datos = [
            ['concepto' => 'Capital Acumulado', 'valor' => '$' . number_format($utilidadesAcumuladas, 2)],
            ['concepto' => 'Último Corte', 'valor' => $ultimoCorte ? $ultimoCorte['mes'] : 'Sin cortes'],
            ['concepto' => 'Capital en Cortes', 'valor' => '$' . number_format($capitalCerrado, 2)],
            ['concepto' => 'Total Retiros', 'valor' => '$' . number_format($totalRetiros, 2)],
        ];
        $columnas = ['Concepto', 'Valor'];
        break;

    case 'estado_cuenta_cliente':
        $clienteId = filter_var($_GET['cliente_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$clienteId) {
            http_response_code(400);
            die('Falta el parámetro cliente_id');
        }

        $stmtCli = $pdo->prepare("
            SELECT c.*, p.nombre AS promotor_nombre, cu.nombre AS cuenta_nombre
            FROM clientes c
            LEFT JOIN promotores p ON c.promotor_id = p.id
            LEFT JOIN cuentas cu ON c.cuenta_id = cu.id
            WHERE c.id = ?
        ");
        $stmtCli->execute([$clienteId]);
        $cliInfo = $stmtCli->fetch(PDO::FETCH_ASSOC);
        if (!$cliInfo) {
            http_response_code(404);
            die('Cliente no encontrado');
        }

        $titulo = 'Estado de Cuenta - ' . $cliInfo['nombre'];

        // Filtros opcionales: rango de fechas y sección (ventas/pagos/ambos).
        // Sin parámetros = historial completo (comportamiento original).
        $ctaSeccion = strtolower($_GET['seccion'] ?? 'ambos');
        if (!in_array($ctaSeccion, ['ventas', 'pagos', 'ambos'], true)) {
            $ctaSeccion = 'ambos';
        }
        $ctaFini = $_GET['fecha_inicio'] ?? null;
        $ctaFfin = $_GET['fecha_fin'] ?? null;
        if ($ctaFini !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ctaFini)) {
            $ctaFini = null;
        }
        if ($ctaFfin !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ctaFfin)) {
            $ctaFfin = null;
        }
        $ctaPeriodoTxt = ($ctaFini || $ctaFfin)
            ? ('Del ' . ($ctaFini ?: 'inicio') . ' al ' . ($ctaFfin ?: 'hoy'))
            : 'Historial completo';

        // Todas las ventas del cliente (historial completo o rango)
        $sqlV = "SELECT id, fecha, monto_total, comision_amiri, comision_promotor,
                   monto_neto_cliente, monto_pagado,
                   (monto_neto_cliente - monto_pagado) AS pendiente,
                   estado, moneda
            FROM deudas_clientes
            WHERE cliente_id = ? AND activo = 1";
        $paramsV = [$clienteId];
        if ($ctaFini) {
            $sqlV .= " AND fecha >= ?";
            $paramsV[] = $ctaFini;
        }
        if ($ctaFfin) {
            $sqlV .= " AND fecha <= ?";
            $paramsV[] = $ctaFfin;
        }
        $sqlV .= " ORDER BY fecha ASC, id ASC";
        $stmtV = $pdo->prepare($sqlV);
        $stmtV->execute($paramsV);
        $ventasCta = ($ctaSeccion === 'pagos') ? [] : $stmtV->fetchAll(PDO::FETCH_ASSOC);

        // Pagos aplicados al cliente (servicio 13, activos, sin reversos internos)
        $sqlP = "SELECT m.fecha, m.monto, m.moneda, m.descripcion,
                   COALESCE(cu.nombre, '-') AS cuenta_pago
            FROM movimientos m
            LEFT JOIN cuentas cu ON m.cuenta_origen_id = cu.id
            WHERE m.cliente_id = ? AND m.servicio_id = 13
              AND m.estado = 'activo' AND m.activo = 1
              AND (m.es_reverso = 0 OR m.es_reverso IS NULL)";
        $paramsP = [$clienteId];
        if ($ctaFini) {
            $sqlP .= " AND m.fecha >= ?";
            $paramsP[] = $ctaFini;
        }
        if ($ctaFfin) {
            $sqlP .= " AND m.fecha <= ?";
            $paramsP[] = $ctaFfin;
        }
        $sqlP .= " ORDER BY m.fecha ASC, m.id ASC";
        $stmtP = $pdo->prepare($sqlP);
        $stmtP->execute($paramsP);
        $pagosCta = ($ctaSeccion === 'ventas') ? [] : $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // Resumen (adeudo = solo pendientes/parciales)
        $resumenCta = [
            'total_vendido' => 0, 'total_neto' => 0, 'total_pagado' => 0,
            'pendiente' => 0, 'n_ventas' => count($ventasCta),
            'n_pendientes' => 0, 'n_pagos' => count($pagosCta),
            'total_pagos_mov' => 0, 'pend_por_moneda' => [],
        ];
        foreach ($ventasCta as $v) {
            $resumenCta['total_vendido'] += (float)$v['monto_total'];
            $resumenCta['total_neto'] += (float)$v['monto_neto_cliente'];
            $resumenCta['total_pagado'] += (float)$v['monto_pagado'];
            if (in_array($v['estado'], ['pendiente', 'parcial'], true)) {
                $pend = (float)$v['monto_neto_cliente'] - (float)$v['monto_pagado'];
                $resumenCta['pendiente'] += $pend;
                $resumenCta['n_pendientes']++;
                $mon = $v['moneda'] ?: 'MXN';
                if (!isset($resumenCta['pend_por_moneda'][$mon])) {
                    $resumenCta['pend_por_moneda'][$mon] = 0;
                }
                $resumenCta['pend_por_moneda'][$mon] += $pend;
            }
        }
        foreach ($pagosCta as $pg) {
            $resumenCta['total_pagos_mov'] += (float)$pg['monto'];
        }

        // Fallback para Excel/CSV: tabla de ventas
        $columnas = ['Fecha', 'Venta #', 'Monto Total', 'Neto Cliente', 'Pagado', 'Pendiente', 'Estado', 'Moneda'];
        $datos = [];
        foreach ($ventasCta as $v) {
            $datos[] = [
                'fecha' => $v['fecha'],
                'venta' => $v['id'],
                'monto_total' => (float)$v['monto_total'],
                'neto' => (float)$v['monto_neto_cliente'],
                'pagado' => (float)$v['monto_pagado'],
                'pendiente' => (float)$v['pendiente'],
                'estado' => $v['estado'],
                'moneda' => $v['moneda'] ?: 'MXN',
            ];
        }
        break;

}
 
 
 
// Generar archivo según formato

$nombreArchivo = 'reporte_' . $reporte . '_' . date('Y-m-d_H-i-s');



switch ($formato) {

    case 'excel':

    case 'csv':

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        

        // Título

        $sheet->setCellValue('A1', $titulo);

        $sheet->mergeCells('A1:' . chr(64 + count($columnas)) . '1');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        

        // Período

        $sheet->setCellValue('A2', "Período: $fechaInicio al $fechaFin");

        $sheet->mergeCells('A2:' . chr(64 + count($columnas)) . '2');

        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        

        // Encabezados

        $col = 'A';

        foreach ($columnas as $columna) {

            $sheet->setCellValue($col . '4', $columna);

            $sheet->getStyle($col . '4')->getFont()->setBold(true);

            $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');

            $sheet->getStyle($col . '4')->getFont()->getColor()->setRGB('FFFFFF');

            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $col++;

        }

        

        // Datos

        $fila = 5;

        $totales = array_fill(0, count($columnas), 0);

        

        foreach ($datos as $registro) {

            $col = 'A';

            $i = 0;

            foreach ($registro as $key => $valor) {

                $sheet->setCellValue($col . $fila, $valor);

                

                // Formatear números

                if (is_numeric($valor) && $i > 0) {

                    $sheet->getStyle($col . $fila)->getNumberFormat()->setFormatCode('#,##0.00');

                    $totales[$i] += floatval($valor);

                }

                

                $col++;

                $i++;

            }

            $fila++;

        }

        

        // Fila de totales (solo si hay datos numéricos)

        if ($reporte !== 'resumen') {

            $sheet->setCellValue('A' . $fila, 'TOTALES');

            $sheet->getStyle('A' . $fila)->getFont()->setBold(true);

            $col = 'B';

            for ($i = 1; $i < count($columnas); $i++) {

                if ($totales[$i] > 0) {

                    $sheet->setCellValue($col . $fila, $totales[$i]);

                    $sheet->getStyle($col . $fila)->getNumberFormat()->setFormatCode('#,##0.00');

                    $sheet->getStyle($col . $fila)->getFont()->setBold(true);

                }

                $col++;

            }

            $sheet->getStyle('A' . $fila . ':' . chr(64 + count($columnas)) . $fila)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');

        }

        

        // Auto-ajustar columnas

        foreach (range('A', chr(64 + count($columnas))) as $col) {

            $sheet->getColumnDimension($col)->setAutoSize(true);

        }

        

        // Descargar

        if ($formato === 'excel') {

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.xlsx"');

            header('Cache-Control: max-age=0');

            

            $writer = new Xlsx($spreadsheet);

            $writer->save('php://output');

        } else {

            header('Content-Type: text/csv; charset=utf-8');

            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.csv"');

            

            $writer = new Csv($spreadsheet);

            $writer->setDelimiter(',');

            $writer->setEnclosure('"');

            $writer->save('php://output');

        }

        break;

        

    case 'pdf':

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        // Generar HTML
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
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
            .bt { border-top: 2px solid #333; }
            .bb { border-bottom: 1px dashed #ddd; }
            table.detail { width: 100%; border-collapse: collapse; margin-top: 5px; }
            table.detail td { padding: 5px 7px; border-bottom: 1px solid #eee; }
            .footer { text-align: center; color: #999; font-size: 9px; margin-top: 18px; padding-top: 8px; border-top: 1px solid #eee; }
        </style></head><body>';

        if ($reporte === 'resumen') {
            $fmt = function($n) { return '$' . number_format($n, 2); };
            $diff = $utilidadesAcumuladas - $utilidadNeta;
            $html .= '
            <div class="header">
                <h1>' . htmlspecialchars($titulo) . '</h1>
                <p>Período: ' . $fechaInicio . ' al ' . $fechaFin . '</p>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:12px;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #28a745;text-align:center;">
                        <div style="font-size:10px;color:#28a745;font-weight:600;">Ganancia Bruta</div>
                        <div style="font-size:16px;font-weight:bold;color:#28a745;margin-top:3px;">' . $fmt($gananciaBruta) . '</div>
                    </td>
                    <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #dc3545;text-align:center;">
                        <div style="font-size:10px;color:#dc3545;font-weight:600;">Gastos Totales</div>
                        <div style="font-size:16px;font-weight:bold;color:#dc3545;margin-top:3px;">' . $fmt($gastosTotal) . '</div>
                    </td>
                    <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #007bff;text-align:center;">
                        <div style="font-size:10px;color:#007bff;font-weight:600;">Utilidad del Mes</div>
                        <div style="font-size:16px;font-weight:bold;color:#007bff;margin-top:3px;">' . $fmt($utilidadNeta) . '</div>
                    </td>
                    <td style="width:25%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #6f42c1;text-align:center;">
                        <div style="font-size:10px;color:#6f42c1;font-weight:600;">Flujo Acumulado</div>
                        <div style="font-size:16px;font-weight:bold;color:#6f42c1;margin-top:3px;">' . $fmt($utilidadesAcumuladas) . '</div>
                    </td>
                </tr>
            </table>

            <div class="section">
                <div class="s-title g">(+) Ingresos</div>
                <table class="info">
                    <tr><td class="lbl">Comisiones AMIRI</td><td align="right" class="val g">' . $fmt($comisionesAmiri) . '</td></tr>
                    <tr><td class="lbl">Ganancia Divisas (USD + USDT)</td><td align="right" class="val g">' . $fmt($gananciaDivisas) . '</td></tr>
                    <tr><td class="lbl" style="font-weight:bold;padding-top:6px;" class="bt">Ganancia Bruta</td><td align="right" class="val g" style="font-weight:bold;padding-top:6px;" class="bt">' . $fmt($gananciaBruta) . '</td></tr>
                </table>
            </div>

            <div class="section">
                <div class="s-title r">(-) Gastos</div>
                <table class="info">
                    <tr><td class="lbl">Gastos Fijos</td><td align="right" class="val r">' . $fmt($gastosFijos) . '</td></tr>
                    <tr><td class="lbl">Gastos Variables</td><td align="right" class="val r">' . $fmt($gastosVariables) . '</td></tr>
                    <tr><td class="lbl" style="font-weight:bold;padding-top:6px;">Gastos Totales</td><td align="right" class="val r" style="font-weight:bold;padding-top:6px;">' . $fmt($gastosTotal) . '</td></tr>
                </table>
            </div>

            <div class="section">
                <div class="s-title b">(=) Resultado del Período</div>
                <table class="info">
                    <tr><td class="lbl" style="font-size:14px;font-weight:bold;">Utilidad del Mes</td><td align="right" class="val b" style="font-size:15px;font-weight:bold;">' . $fmt($utilidadNeta) . '</td></tr>
                </table>
            </div>

            <div class="section">
                <div class="s-title p">Histórico</div>
                <table class="info">
                    <tr><td class="lbl">Utilidades Acumuladas (Flujo)</td><td align="right" class="val d">' . $fmt($utilidadesAcumuladas) . '</td></tr>
                    <tr><td class="lbl" style="border-bottom:1px dashed #ddd;padding-bottom:6px;">Diferencia (meses anteriores)</td><td align="right" class="val d" style="border-bottom:1px dashed #ddd;padding-bottom:6px;">' . $fmt($diff) . '</td></tr>
                </table>
                <table class="detail">
                    <tr><td style="font-weight:bold;padding-top:7px;" colspan="2">Detalle completo</td></tr>
                    <tr><td>Comisiones AMIRI</td><td align="right" style="font-weight:600;">' . $fmt($comisionesAmiri) . '</td></tr>
                    <tr><td>Comisiones Promotores</td><td align="right" style="font-weight:600;">' . $fmt($comisionesPromotores) . '</td></tr>
                    <tr><td>Total Ventas Efectivo</td><td align="right" style="font-weight:600;">' . $fmt($ventas['total']) . '</td></tr>
                    <tr><td>Cantidad de Ventas</td><td align="right" style="font-weight:600;">' . $ventas['cantidad'] . '</td></tr>
                </table>
            </div>';
        } elseif ($reporte === 'estado_cuenta_cliente') {
            $fmt = function($n) { return '$' . number_format((float)$n, 2); };
            $r = $resumenCta;
            $pendMonedas = '';
            foreach ($r['pend_por_moneda'] as $mon => $tot) {
                $pendMonedas .= '<tr><td class="lbl">Pendiente ' . htmlspecialchars($mon) . '</td><td align="right" class="val r">' . $fmt($tot) . '</td></tr>';
            }
            $filasVentas = '';
            foreach ($ventasCta as $v) {
                $pend = (float)$v['monto_neto_cliente'] - (float)$v['monto_pagado'];
                $filasVentas .= '<tr>'
                    . '<td>' . htmlspecialchars($v['fecha']) . '</td>'
                    . '<td align="center">#' . (int)$v['id'] . '</td>'
                    . '<td align="right">' . $fmt($v['monto_total']) . '</td>'
                    . '<td align="right">' . $fmt($v['monto_neto_cliente']) . '</td>'
                    . '<td align="right">' . $fmt($v['monto_pagado']) . '</td>'
                    . '<td align="right" style="font-weight:bold;">' . $fmt($pend) . '</td>'
                    . '<td align="center">' . htmlspecialchars($v['estado']) . '</td>'
                    . '<td align="center">' . htmlspecialchars($v['moneda'] ?: 'MXN') . '</td>'
                    . '</tr>';
            }
            $filasPagos = '';
            foreach ($pagosCta as $pg) {
                $filasPagos .= '<tr>'
                    . '<td>' . htmlspecialchars($pg['fecha']) . '</td>'
                    . '<td align="right">' . $fmt($pg['monto']) . '</td>'
                    . '<td align="center">' . htmlspecialchars($pg['moneda']) . '</td>'
                    . '<td>' . htmlspecialchars($pg['cuenta_pago']) . '</td>'
                    . '<td>' . htmlspecialchars(mb_substr($pg['descripcion'] ?? '', 0, 60)) . '</td>'
                    . '</tr>';
            }
            if ($filasPagos === '') {
                $filasPagos = '<tr><td colspan="5" align="center" style="color:#999;">Sin pagos registrados</td></tr>';
            }
            $html .= '
            <div class="header">
                <h1>Estado de Cuenta</h1>
                <p>' . htmlspecialchars($cliInfo['nombre']) . ' &mdash; ' . htmlspecialchars($ctaPeriodoTxt) . ' &mdash; Corte al ' . date('d/m/Y') . '</p>
            </div>

            <div class="section">
                <div class="s-title b">Datos del Cliente</div>
                <table class="info">
                    <tr><td class="lbl">Cliente</td><td align="right" class="val d">' . htmlspecialchars($cliInfo['nombre']) . '</td></tr>
                    <tr><td class="lbl">Promotor</td><td align="right" class="val d">' . htmlspecialchars($cliInfo['promotor_nombre'] ?? 'N/A') . '</td></tr>
                    <tr><td class="lbl">Comisión AMIRI / Promotor</td><td align="right" class="val d">' . htmlspecialchars($cliInfo['porcentaje_amiri'] ?? 0) . '% / ' . htmlspecialchars($cliInfo['porcentaje_promotor'] ?? 0) . '%</td></tr>
                    <tr><td class="lbl">Cuenta vinculada</td><td align="right" class="val d">' . htmlspecialchars($cliInfo['cuenta_nombre'] ?? 'N/A') . '</td></tr>
                </table>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:12px;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width:33%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #007bff;text-align:center;">
                        <div style="font-size:10px;color:#007bff;font-weight:600;">Total Vendido (' . (int)$r['n_ventas'] . ' ops)</div>
                        <div style="font-size:15px;font-weight:bold;color:#007bff;margin-top:3px;">' . $fmt($r['total_vendido']) . '</div>
                    </td>
                    <td style="width:33%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #28a745;text-align:center;">
                        <div style="font-size:10px;color:#28a745;font-weight:600;">Total Pagado</div>
                        <div style="font-size:15px;font-weight:bold;color:#28a745;margin-top:3px;">' . $fmt($r['total_pagado']) . '</div>
                    </td>
                    <td style="width:34%;padding:6px;border:1px solid #dee2e6;border-left:4px solid #dc3545;text-align:center;">
                        <div style="font-size:10px;color:#dc3545;font-weight:600;">Saldo Pendiente (' . (int)$r['n_pendientes'] . ' ops)</div>
                        <div style="font-size:15px;font-weight:bold;color:#dc3545;margin-top:3px;">' . $fmt($r['pendiente']) . '</div>
                    </td>
                </tr>
            </table>

            <div class="section">
                <div class="s-title r">Pendiente por Moneda</div>
                <table class="info">' . $pendMonedas . '</table>
            </div>';

            if ($ctaSeccion !== 'pagos') {
            $html .= '
            <div class="section">
                <div class="s-title b">Detalle de Ventas (' . (int)$r['n_ventas'] . ')</div>
                <table class="detail">
                    <tr style="background:#4472C4;color:white;">
                        <th style="padding:6px;text-align:left;">Fecha</th>
                        <th style="padding:6px;">Venta</th>
                        <th style="padding:6px;text-align:right;">Total</th>
                        <th style="padding:6px;text-align:right;">Neto</th>
                        <th style="padding:6px;text-align:right;">Pagado</th>
                        <th style="padding:6px;text-align:right;">Pendiente</th>
                        <th style="padding:6px;">Estado</th>
                        <th style="padding:6px;">Mon</th>
                    </tr>
                    ' . $filasVentas . '
                    <tr style="background:#E2EFDA;font-weight:bold;">
                        <td colspan="2" style="padding:7px;">TOTALES</td>
                        <td align="right" style="padding:7px;">' . $fmt($r['total_vendido']) . '</td>
                        <td align="right" style="padding:7px;">' . $fmt($r['total_neto']) . '</td>
                        <td align="right" style="padding:7px;">' . $fmt($r['total_pagado']) . '</td>
                        <td align="right" style="padding:7px;">' . $fmt($r['pendiente']) . '</td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>';
            }
            if ($ctaSeccion !== 'ventas') {
            $html .= '
            <div class="section">
                <div class="s-title g">Pagos Aplicados (' . (int)$r['n_pagos'] . ')</div>
                <table class="detail">
                    <tr style="background:#28a745;color:white;">
                        <th style="padding:6px;text-align:left;">Fecha</th>
                        <th style="padding:6px;text-align:right;">Monto</th>
                        <th style="padding:6px;">Mon</th>
                        <th style="padding:6px;text-align:left;">Cuenta</th>
                        <th style="padding:6px;text-align:left;">Descripción</th>
                    </tr>
                    ' . $filasPagos . '
                    <tr style="background:#E2EFDA;font-weight:bold;">
                        <td style="padding:7px;">TOTAL PAGOS</td>
                        <td align="right" style="padding:7px;">' . $fmt($r['total_pagos_mov']) . '</td>
                        <td colspan="3"></td>
                    </tr>
                </table>
            </div>';
            }
        } else {
            $html .= '
            <div class="header">
                <h1>' . htmlspecialchars($titulo) . '</h1>
                <p>Período: ' . $fechaInicio . ' al ' . $fechaFin . '</p>
            </div>
            <div class="section">
                <table class="detail">
                    <thead>
                        <tr style="background:#4472C4;color:white;">
                            ' . implode('', array_map(function($c) { return '<th style="padding:8px;text-align:left;">' . htmlspecialchars($c) . '</th>'; }, $columnas)) . '
                        </tr>
                    </thead>
                    <tbody>';

            $totales = array_fill(0, count($columnas), 0);

            foreach ($datos as $registro) {
                $html .= '<tr>';
                $i = 0;
                foreach ($registro as $valor) {
                    $html .= '<td style="padding:6px 8px;border-bottom:1px solid #eee;' . (is_numeric($valor) && $i > 0 ? 'text-align:right;' : '') . '">';
                    if (is_numeric($valor) && $i > 0) {
                        $totales[$i] += floatval($valor);
                        $html .= number_format($valor, 2);
                    } else {
                        $html .= htmlspecialchars($valor);
                    }
                    $html .= '</td>';
                    $i++;
                }
                $html .= '</tr>';
            }

            if (count($datos) > 0) {
                $html .= '<tr style="background:#E2EFDA;font-weight:bold;">';
                $html .= '<td style="padding:8px;">TOTALES</td>';
                for ($i = 1; $i < count($columnas); $i++) {
                    $html .= '<td style="padding:8px;text-align:right;">' . ($totales[$i] > 0 ? number_format($totales[$i], 2) : '') . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div>';
        }

        $html .= '<div class="footer">Generado el ' . date('d/m/Y H:i:s') . ' - AMIRI App</div>
        </body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.pdf"');

        echo $dompdf->output();
        break;

}

