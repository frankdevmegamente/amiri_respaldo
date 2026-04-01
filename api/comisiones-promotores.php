<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';



/**

 * Genera un PDF con el historial de comisiones del promotor

 */

function generarPDF($pdo, $promotor, $promotor_id, $cuentaPromotorId, $filtroFecha, $params) {

    $filtroFechaMovimientos = str_replace('fecha', 'fm.fecha', $filtroFecha);

    $filtroFechaPagos = str_replace('fecha', 'pcp.fecha_pago', $filtroFecha);

    

    // Obtener los datos
    $stmt = $pdo->prepare("
        (SELECT 
            fm.id, fm.grupo_id, s.nombre AS servicio, fm.fecha, 
            fm.monto AS comision, fm.descripcion, 'comision' AS tipo,
            fm.creado_en /* <-- AGREGADO */
        FROM movimientos fm
        LEFT JOIN servicios s ON fm.servicio_id = s.id
        WHERE fm.activo = 1 AND fm.tipo = 'comision'
          AND (fm.promotor_id = :pId OR fm.cuenta_destino_id = :cId)
          $filtroFechaMovimientos)
        
        UNION ALL
        
        (SELECT 
            pcp.id, NULL AS grupo_id, 'Pago de Comisiones' AS servicio, 
            pcp.fecha_pago AS fecha, pcp.monto_pagado AS comision, 
            pcp.descripcion, 'pago' AS tipo,
            pcp.creado_en /* <-- AGREGADO */
        FROM pagos_promotores pcp
        WHERE pcp.activo = 1 AND pcp.promotor_id = :pId
        $filtroFechaPagos)
        
        ORDER BY fecha DESC, id DESC
    ");

    $stmt->execute($params);

    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

    // Calcular totales

    $total_comisiones = 0;

    $total_pagos = 0;

    foreach ($movimientos as $mov) {

        if ($mov['tipo'] === 'comision') {

            $total_comisiones += $mov['comision'];

        } else {

            $total_pagos += $mov['comision'];

        }

    }

    $saldo_pendiente = $total_comisiones - $total_pagos;

    

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

        <title>Historial de Comisiones - <?= htmlspecialchars($promotor['nombre']) ?></title>

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

            .pago { color: #dc3545; font-weight: bold; }

            .total { font-size: 18px; font-weight: bold; }

            @media print {

                body { margin: 0; }

                button { display: none; }

            }

        </style>

    </head>

    <body>

        <h1>Historial de Comisiones</h1>

        <div class="resumen">

            <div><strong>Promotor:</strong> <?= htmlspecialchars($promotor['nombre']) ?></div>

            <div><strong>Período:</strong> <?= $periodo ?></div>

            <div><strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i') ?></div>

        </div>

        

        <div class="resumen">

            <div class="comision"><strong>Total Comisiones Generadas:</strong> $<?= number_format($total_comisiones, 2) ?></div>

            <div class="pago"><strong>Total Pagado:</strong> $<?= number_format($total_pagos, 2) ?></div>

            <div class="total"><strong>Saldo Pendiente:</strong> $<?= number_format($saldo_pendiente, 2) ?></div>

        </div>

        

        <h2>Detalle de Movimientos</h2>

        <table>

            <thead>

                <tr>

                    <th>Fecha</th>

                    <th>Descripción / Servicio</th>

                    <th>Tipo</th>

                    <th style="text-align: right;">Monto</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($movimientos as $mov): ?>

                <tr>

                    <td><?= htmlspecialchars($mov['fecha']) ?></td>

                    <td>

                        <strong><?= htmlspecialchars($mov['servicio']) ?></strong><br>

                        <small><?= htmlspecialchars($mov['descripcion']) ?></small>

                    </td>

                    <td><?= $mov['tipo'] === 'comision' ? 'Comisión Ganada' : 'Pago Realizado' ?></td>

                    <td style="text-align: right;" class="<?= $mov['tipo'] ?>">

                        <?= $mov['tipo'] === 'comision' ? '+' : '-' ?>$<?= number_format($mov['comision'], 2) ?>

                    </td>

                </tr>

                <?php endforeach; ?>

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



/**

 * Función para obtener el promotor y su cuenta de comisiones asociada.

 * Busca por cuenta_id directa, por relación entidad o por nombre (legacy).

 */

function obtenerPromotorYCuenta(PDO $pdo, int $promotorId): array

{

    $stmt = $pdo->prepare("SELECT id, nombre, cuenta_id FROM promotores WHERE id = ?");

    $stmt->execute([$promotorId]);

    $promotor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promotor) {

        return [null, null];

    }



    // Prioridad 1: Relación directa en la tabla promotores

    if (!empty($promotor['cuenta_id'])) {

        $stmt = $pdo->prepare("SELECT id, saldo_actual FROM cuentas WHERE id = ? AND tipo_cuenta = 5 LIMIT 1");

        $stmt->execute([$promotor['cuenta_id']]);

        $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cuenta) return [$promotor, $cuenta];

    }



    // Prioridad 2: Relación por entidad_tipo

    $stmt = $pdo->prepare("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 5 AND entidad_tipo = 'promotor' AND entidad_id = ? LIMIT 1");

    $stmt->execute([$promotorId]);

    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cuenta) return [$promotor, $cuenta];



    // Prioridad 3: Fallback por nombre

    $stmt = $pdo->prepare("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 5 AND nombre LIKE ? LIMIT 1");

    $stmt->execute(['Comisiones - ' . $promotor['nombre'] . '%']);

    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);



    return [$promotor, $cuenta ?: null];

}



try {

    $pdo = getPDO();

    $method = $_SERVER['REQUEST_METHOD'];



    // --- GET: OBTENER TOTALES O LISTADO ---

    if ($method === 'GET') {

        $promotor_id = filter_var($_GET['promotor_id'] ?? null, FILTER_VALIDATE_INT);



        if (!$promotor_id) {

            http_response_code(400);

            echo json_encode(['error' => 'ID de promotor requerido']);

            exit;

        }



        [$promotor, $cuentaPromotor] = obtenerPromotorYCuenta($pdo, $promotor_id);

        $cuentaPromotorId = $cuentaPromotor ? (int)$cuentaPromotor['id'] : null;



        // Construir filtros de fecha

        $filtroFecha = '';

        $params = [':pId' => $promotor_id, ':cId' => $cuentaPromotorId];

        

        if (isset($_GET['mes']) && isset($_GET['anio'])) {

            $mes = str_pad($_GET['mes'], 2, '0', STR_PAD_LEFT);

            $anio = $_GET['anio'];

            $filtroFecha = " AND DATE_FORMAT(fecha, '%Y-%m') = :periodo";

            $params[':periodo'] = "$anio-$mes";

        } elseif (isset($_GET['anio'])) {

            $filtroFecha = " AND YEAR(fecha) = :anio";

            $params[':anio'] = $_GET['anio'];

        } elseif (isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta'])) {

            $filtroFecha = " AND fecha BETWEEN :desde AND :hasta";

            $params[':desde'] = $_GET['fecha_desde'];

            $params[':hasta'] = $_GET['fecha_hasta'];

        }



        // ENDPOINT: GENERAR PDF

        if (isset($_GET['pdf'])) {

            generarPDF($pdo, $promotor, $promotor_id, $cuentaPromotorId, $filtroFecha, $params);

            exit;

        }



        // ENDPOINT: TOTALES PARA LAS CARDS

        if (isset($_GET['totales'])) {

            if (!$promotor) {

                echo json_encode(['total_comisiones' => 0, 'total_pagado' => 0, 'total_pendiente' => 0, 'saldo_cuenta' => 0]);

                exit;

            }



            // 1. Total Generado (Lo que ha ganado en comisiones)

            $stmt = $pdo->prepare("

                SELECT COALESCE(SUM(monto), 0)

                FROM movimientos

                WHERE tipo = 'comision' AND activo = 1

                  AND (promotor_id = :pId OR (:cId IS NOT NULL AND cuenta_destino_id = :cId))

                  $filtroFecha

            ");

            $stmt->execute($params);

            $total_comisiones = (float) $stmt->fetchColumn();



            // 2. Total Pagado (desde la tabla de pagos)

            $paramsPago = [':pId' => $promotor_id];

            $filtroFechaPago = str_replace('fecha', 'fecha_pago', $filtroFecha);

            if ($filtroFecha) {

                if (isset($params[':periodo'])) $paramsPago[':periodo'] = $params[':periodo'];

                if (isset($params[':anio'])) $paramsPago[':anio'] = $params[':anio'];

                if (isset($params[':desde'])) $paramsPago[':desde'] = $params[':desde'];

                if (isset($params[':hasta'])) $paramsPago[':hasta'] = $params[':hasta'];

            }

            

            $stmt = $pdo->prepare("

                SELECT COALESCE(SUM(monto_pagado), 0)

                FROM pagos_promotores

                WHERE promotor_id = :pId AND activo = 1

                $filtroFechaPago

            ");

            $stmt->execute($paramsPago);

            $total_pagado = (float) $stmt->fetchColumn();



            $total_pendiente = max(0, $total_comisiones - $total_pagado);



            echo json_encode([

                'total_comisiones' => $total_comisiones,

                'total_pagado'      => $total_pagado,

                'total_pendiente'   => $total_pendiente,

                'saldo_cuenta'      => $cuentaPromotor ? (float)$cuentaPromotor['saldo_actual'] : 0

            ]);

            exit;

        }



        // ENDPOINT: LISTADO DE MOVIMIENTOS (Comisiones generadas + Pagos realizados)

        // Unimos comisiones y pagos en una sola consulta

        $filtroFechaMovimientos = str_replace('fecha', 'fm.fecha', $filtroFecha);

        $filtroFechaPagos = str_replace('fecha', 'pcp.fecha_pago', $filtroFecha);

        

       $stmt = $pdo->prepare("
            (SELECT 
                fm.id, fm.grupo_id, s.nombre AS servicio, fm.fecha, 
                fm.monto AS comision, fm.descripcion, 'comision' AS tipo,
                fm.creado_en /* <-- AGREGADO */
            FROM movimientos fm
            LEFT JOIN servicios s ON fm.servicio_id = s.id
            WHERE fm.activo = 1 AND fm.tipo = 'comision'
              AND (fm.promotor_id = :pId OR fm.cuenta_destino_id = :cId)
              $filtroFechaMovimientos)
            
            UNION ALL
            
            (SELECT 
                pcp.id, NULL AS grupo_id, 'Pago de Comisiones' AS servicio, 
                pcp.fecha_pago AS fecha, pcp.monto_pagado AS comision, 
                pcp.descripcion, 'pago' AS tipo,
                pcp.creado_en /* <-- AGREGADO */
            FROM pagos_promotores pcp
            WHERE pcp.activo = 1 AND pcp.promotor_id = :pId
            $filtroFechaPagos)
            
            ORDER BY fecha DESC, id DESC
        ");

        $stmt->execute($params);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        exit;

    }



    // --- PUT: REGISTRAR PAGO (Similar a deudas_empresa) ---

    if ($method === 'PUT') {

        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        $promotor_id = (int) ($input['promotor_id'] ?? 0);

        $monto = (float) ($input['monto_a_pagar'] ?? 0);

        $cuenta_pago_id = (int) ($input['cuenta_pago_id'] ?? 0); // Caja/Banco de la empresa

        $fecha_pago = !empty($input['fecha_pago']) ? $input['fecha_pago'] : date('Y-m-d');



        if (!$promotor_id || $monto <= 0 || !$cuenta_pago_id) {

            http_response_code(400);

            echo json_encode(['error' => 'Datos incompletos para procesar el pago']);

            exit;

        }



        [$promotor, $cuentaPromotor] = obtenerPromotorYCuenta($pdo, $promotor_id);

        

        if (!$cuentaPromotor) {

            http_response_code(400);

            echo json_encode(['error' => 'El promotor no tiene una cuenta de comisiones válida']);

            exit;

        }



        $cuentaPromotorId = (int) $cuentaPromotor['id'];



        $pdo->beginTransaction();

        try {

            $grupo_id = time() . mt_rand(100, 999);



            // 1. REGISTRAR en movimientos: Salida de la Caja/Banco de la empresa

            // servicio_id = 15 (Pago de Comisión a Promotor)

            $stmt = $pdo->prepare("

                INSERT INTO movimientos (

                    grupo_id, fecha, servicio_id, tipo, moneda, monto,

                    cuenta_origen_id, cuenta_destino_id, promotor_id, activo, descripcion

                ) VALUES (?, ?, 15, 'salida', 'MXN', ?, ?, ?, ?, 1, ?)

            ");

            $stmt->execute([

                $grupo_id, 

                $fecha_pago, 

                $monto, 

                $cuenta_pago_id,

                $cuentaPromotorId,

                $promotor_id,

                'Pago de Comisiones a ' . $promotor['nombre']

            ]);

            $movimiento_id = $pdo->lastInsertId();



            // 2. REGISTRAR en pagos_promotores (historial de pagos)

            $stmt = $pdo->prepare("

                INSERT INTO pagos_promotores (

                    promotor_id, cuenta_pago_id, cuenta_promotor_id, 

                    monto_pagado, fecha_pago, movimiento_id, descripcion

                ) VALUES (?, ?, ?, ?, ?, ?, ?)

            ");

            $stmt->execute([

                $promotor_id,

                $cuenta_pago_id,

                $cuentaPromotorId,

                $monto,

                $fecha_pago,

                $movimiento_id,

                'Pago de comisiones pendientes'

            ]);



            // 3. ACTUALIZAR: Restar de la cuenta de origen (Caja/Banco empresa)

            $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?");

            $stmt->execute([$monto, $cuenta_pago_id]);



            // 4. ACTUALIZAR: Restar del saldo virtual de la cuenta del promotor

            $stmt = $pdo->prepare("UPDATE cuentas SET saldo_actual = saldo_actual - ? WHERE id = ?");

            $stmt->execute([$monto, $cuentaPromotorId]);

            

            // 5. REGISTRAR AUDITORÍA

            $pago_id = $pdo->lastInsertId();

            registrarAuditoria($pdo, [

                'accion' => 'INSERT',

                'modulo' => 'comisiones-promotores',

                'tabla' => 'pagos_promotores',

                'registro_id' => $pago_id,

                'descripcion' => "Registró pago de comisiones: $" . number_format($monto, 2) . " a promotor {$promotor['nombre']}",

                'datos_nuevos' => [

                    'id' => $pago_id,

                    'promotor_id' => $promotor_id,

                    'promotor_nombre' => $promotor['nombre'],

                    'cuenta_pago_id' => $cuenta_pago_id,

                    'cuenta_promotor_id' => $cuentaPromotorId,

                    'monto_pagado' => $monto,

                    'fecha_pago' => $fecha_pago,

                    'movimiento_id' => $movimiento_id

                ]

            ]);



            $pdo->commit();

            echo json_encode([

                'success' => true, 

                'message' => 'Pago registrado correctamente',

                'monto_pagado' => $monto,

                'cuenta_origen' => $cuenta_pago_id

            ]);

        } catch (Exception $e) {

            $pdo->rollBack();

            http_response_code(500);

            echo json_encode(['error' => 'Error al procesar el pago: ' . $e->getMessage()]);

        }

        exit;

    }



    http_response_code(405);

    echo json_encode(['error' => 'Método no permitido']);



} catch (Exception $e) {

    error_log("Error en api/comisiones-promotores.php: " . $e->getMessage());

    http_response_code(500);

    echo json_encode(['error' => 'Error interno del servidor']);

}
