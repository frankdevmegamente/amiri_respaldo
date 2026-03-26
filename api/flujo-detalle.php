<?php

// api/flujo-detalle.php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAdmin();

$pdo = getPDO();



$movimiento_id = intval($_GET['id'] ?? 0);



if (!$movimiento_id) {

    jsonError('ID de movimiento no proporcionado', 400);

}



try {

    // Primero obtener el grupo_id del movimiento solicitado

    $sqlGrupo = "SELECT grupo_id FROM movimientos WHERE id = ?";

    $stmtGrupo = $pdo->prepare($sqlGrupo);

    $stmtGrupo->execute([$movimiento_id]);

    $grupoData = $stmtGrupo->fetch(PDO::FETCH_ASSOC);

    

    if (!$grupoData) {

        http_response_code(404);

        echo json_encode(['error' => 'Movimiento no encontrado']);

        exit;

    }

    

    $grupo_id = $grupoData['grupo_id'];

    

    // Ahora obtener TODOS los movimientos del mismo grupo

    $sql = "

        SELECT 

            fm.*,

            s.nombre as servicio_nombre,

            co.nombre as cuenta_origen_nombre,
            co.banco as cuenta_origen_banco,

            cd.nombre as cuenta_destino_nombre,
            cd.banco as cuenta_destino_banco,

            cl.nombre as cliente_nombre,

            cl.porcentaje_amiri,

            cl.porcentaje_promotor,

            pr.nombre as promotor_nombre,

            prov.nombre as proveedor_nombre

        FROM movimientos fm

        LEFT JOIN servicios s ON fm.servicio_id = s.id

        LEFT JOIN cuentas co ON fm.cuenta_origen_id = co.id

        LEFT JOIN cuentas cd ON fm.cuenta_destino_id = cd.id

        LEFT JOIN clientes cl ON fm.cliente_id = cl.id

        LEFT JOIN promotores pr ON cl.promotor_id = pr.id

        LEFT JOIN proveedores prov ON fm.proveedor_id = prov.id

        WHERE fm.grupo_id = ?

        ORDER BY 

            CASE fm.tipo

                WHEN 'salida' THEN 1

                WHEN 'entrada' THEN 2

                WHEN 'comision' THEN 3

                WHEN 'traspaso' THEN 4

                ELSE 5

            END,

            fm.id

    ";



    $stmt = $pdo->prepare($sql);

    $stmt->execute([$grupo_id]);

    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);



    if (empty($movimientos)) {

        http_response_code(404);

        echo json_encode(['error' => 'No se encontraron movimientos']);

        exit;

    }



    // Crear respuesta con datos generales y detalle de movimientos

    $response = [

        'grupo_id' => $grupo_id,

        'fecha' => $movimientos[0]['fecha'],

        'servicio' => $movimientos[0]['servicio_nombre'],

        'servicio_nombre' => $movimientos[0]['servicio_nombre'],

        'estado' => $movimientos[0]['estado'] ?? 'activo',

        'cliente_nombre' => $movimientos[0]['cliente_nombre'] ?? null,

        'proveedor_nombre' => $movimientos[0]['proveedor_nombre'] ?? null,

        'promotor_nombre' => $movimientos[0]['promotor_nombre'] ?? null,

        'total_movimientos' => count($movimientos),

        'movimientos' => $movimientos

    ];



    // Si es venta o compra de divisa, obtener inventario relacionado

    if (in_array($movimientos[0]['servicio_nombre'], ['Venta de USDT', 'Compra de USDT', 'Venta de Dolares', 'Compra de Dolares'])) {

        $invSql = "

            SELECT * FROM inventario_monedas

            WHERE movimiento_id IN (SELECT id FROM movimientos WHERE grupo_id = ?)

        ";

        $invStmt = $pdo->prepare($invSql);

        $invStmt->execute([$grupo_id]);

        $response['inventario'] = $invStmt->fetchAll(PDO::FETCH_ASSOC);

        

        // Para ventas, obtener desglose de ganancias FIFO

        if (in_array($movimientos[0]['servicio_nombre'], ['Venta de USDT', 'Venta de Dolares'])) {

            $consumosSql = "

                SELECT 

                    ci.*,

                    im.fecha as fecha_compra,

                    im.cantidad_original as lote_cantidad_original,

                    im.cantidad_restante as lote_cantidad_restante

                FROM consumos_inventario ci

                LEFT JOIN inventario_monedas im ON ci.inventario_id = im.id

                WHERE ci.movimiento_id IN (

                    SELECT id FROM movimientos

                    WHERE grupo_id = ? AND (tipo = 'salida' OR (tipo = 'entrada' AND moneda = 'MXN'))

                )

                ORDER BY im.fecha ASC

            ";

            $consumosStmt = $pdo->prepare($consumosSql);

            $consumosStmt->execute([$grupo_id]);

            $consumos = $consumosStmt->fetchAll(PDO::FETCH_ASSOC);

            

            $response['consumos_fifo'] = $consumos;

            

            // Calcular totales de ganancia

            $totalGanancia = 0;

            $totalCantidadVendida = 0;

            foreach ($consumos as $consumo) {

                $totalGanancia += floatval($consumo['ganancia']);

                $totalCantidadVendida += floatval($consumo['cantidad_consumida']);

            }

            $response['ganancia_total'] = $totalGanancia;

            $response['cantidad_vendida'] = $totalCantidadVendida;

        }

    }



    echo json_encode($response);



} catch (Exception $e) {

    http_response_code(500);

    echo json_encode(['error' => $e->getMessage()]);

}

?>

