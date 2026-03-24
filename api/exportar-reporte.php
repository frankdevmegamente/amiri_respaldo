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
        
        // Operaciones divisas
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN servicio_id IN (2, 3) THEN monto * paridad ELSE 0 END) as mxn_recibido,
                SUM(CASE WHEN servicio_id IN (5, 6) THEN monto * paridad ELSE 0 END) as mxn_gastado
            FROM movimientos
            WHERE moneda IN ('USD', 'USDT')
            AND fecha BETWEEN ? AND ?
            AND activo = 1
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $divisas = $stmt->fetch(PDO::FETCH_ASSOC);
        $gananciaDivisas = ($divisas['mxn_recibido'] ?? 0) - ($divisas['mxn_gastado'] ?? 0);
        
        $datos = [
            ['concepto' => 'Comisiones AMIRI', 'valor' => '$' . number_format($comisionesAmiri, 2)],
            ['concepto' => 'Comisiones Promotores', 'valor' => '$' . number_format($comisionesPromotores, 2)],
            ['concepto' => 'Total Ventas Efectivo', 'valor' => '$' . number_format($ventas['total'], 2)],
            ['concepto' => 'Cantidad Ventas', 'valor' => $ventas['cantidad']],
            ['concepto' => 'Ganancia Divisas', 'valor' => '$' . number_format($gananciaDivisas, 2)],
            ['concepto' => 'GANANCIA TOTAL', 'valor' => '$' . number_format($comisionesAmiri + $gananciaDivisas, 2)],
        ];
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
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { color: #333; font-size: 18px; text-align: center; margin-bottom: 5px; }
                .periodo { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th { background-color: #4472C4; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .totales { background-color: #E2EFDA; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
            </style>
        </head>
        <body>
            <h1>' . htmlspecialchars($titulo) . '</h1>
            <p class="periodo">Período: ' . $fechaInicio . ' al ' . $fechaFin . '</p>
            <table>
                <thead>
                    <tr>';
        
        foreach ($columnas as $columna) {
            $html .= '<th>' . htmlspecialchars($columna) . '</th>';
        }
        
        $html .= '</tr>
                </thead>
                <tbody>';
        
        $totales = array_fill(0, count($columnas), 0);
        
        foreach ($datos as $registro) {
            $html .= '<tr>';
            $i = 0;
            foreach ($registro as $valor) {
                $class = is_numeric($valor) && $i > 0 ? 'text-right' : '';
                if (is_numeric($valor) && $i > 0) {
                    $totales[$i] += floatval($valor);
                    $valor = number_format($valor, 2);
                }
                $html .= '<td class="' . $class . '">' . htmlspecialchars($valor) . '</td>';
                $i++;
            }
            $html .= '</tr>';
        }
        
        // Fila de totales
        if ($reporte !== 'resumen' && count($datos) > 0) {
            $html .= '<tr class="totales"><td>TOTALES</td>';
            for ($i = 1; $i < count($columnas); $i++) {
                $html .= '<td class="text-right">' . ($totales[$i] > 0 ? number_format($totales[$i], 2) : '') . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
            </table>
            <p class="footer">Generado el ' . date('d/m/Y H:i:s') . ' - AMIRI App</p>
        </body>
        </html>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.pdf"');
        
        echo $dompdf->output();
        break;
}
