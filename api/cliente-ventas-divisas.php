<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAdmin();

$pdo = getPDO();



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $cliente_id = (int)($_GET['cliente_id'] ?? 0);

    if (!$cliente_id) {

        http_response_code(400);

        echo json_encode(['error' => 'cliente_id requerido']);

        exit;

    }



    // movimientos columns: tipo, moneda, paridad (not tipo_movimiento / tipo_moneda / tipo_cambio)

    // The historial movement (step 5 of venta): tipo='entrada', moneda='USD'/'USDT',

    //   descripcion LIKE 'Registro venta:%', monto = cantidad divisas, paridad = TC venta

    // The MXN entrada movement (step 1): tipo='entrada', moneda='MXN', same grupo_id

    //   consumos_inventario.movimiento_id links to that MXN entrada movement

    $stmt = $pdo->prepare("

        SELECT
            m.creado_en,

            m.fecha,

            m.moneda,

            m.monto                                                         AS cantidad,

            m.paridad                                                        AS tc_venta,

            ROUND(m.monto * m.paridad, 2)                                   AS total_mxn,

            (

                SELECT ROUND(

                    SUM(ci.paridad_compra * ci.cantidad_consumida) / NULLIF(SUM(ci.cantidad_consumida), 0)

                , 4)

                FROM consumos_inventario ci

                JOIN movimientos m2 ON ci.movimiento_id = m2.id

                WHERE m2.grupo_id = m.grupo_id

                  AND m2.moneda = 'MXN'

                  AND m2.activo = 1

            )                                                               AS tc_costo_fifo,

            (

                SELECT ROUND(SUM(ci2.ganancia), 2)

                FROM consumos_inventario ci2

                JOIN movimientos m3 ON ci2.movimiento_id = m3.id

                WHERE m3.grupo_id = m.grupo_id

                  AND m3.moneda = 'MXN'

                  AND m3.activo = 1

            )                                                               AS ganancia_amiri

        FROM movimientos m

        WHERE m.cliente_id = ?

          AND m.servicio_id IN (2, 3)

          AND m.moneda IN ('USD', 'USDT')

          AND m.descripcion LIKE 'Registro venta:%'

          AND m.activo = 1

        ORDER BY m.fecha DESC, m.id DESC

    ");

    $stmt->execute([$cliente_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    exit;

}



http_response_code(405);

echo json_encode(['error' => 'Método no permitido']);

