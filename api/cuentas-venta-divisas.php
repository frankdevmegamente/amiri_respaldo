<?php

/**

 * Endpoint dedicado para flujo-divisas.php

 * Carga cuentas MXN bancarias (tipo 1) y caja (tipo 2) donde se recibe el pago

 * al vender USD/USDT. Completamente aislado de api/cuentas.php para no afectar flujo.php.

 */

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';



initApi();

requireAdmin();

$pdo = getPDO();



try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

        http_response_code(405);

        echo json_encode(['error' => 'Método no permitido']);

        exit;

    }



    $stmt = $pdo->prepare("

        SELECT id, nombre, tipo_cuenta, tipo_moneda, saldo_actual

        FROM cuentas

        WHERE tipo_cuenta IN (1, 2)

          AND tipo_moneda = 'MXN'

          AND activo = 1

        ORDER BY tipo_cuenta, nombre

    ");

    $stmt->execute();

    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);



    echo json_encode($cuentas);



} catch (Exception $e) {

    http_response_code(500);

    echo json_encode(['error' => $e->getMessage()]);

}

