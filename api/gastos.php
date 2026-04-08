<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';



initApi();

requireAdmin();

$pdo = getPDO();

$pdo->exec("SET NAMES utf8mb4");



try {



    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT g.*, c.nombre cuenta_nombre, tg.nombre tipo_gasto_nombre
                FROM gastos g
                JOIN cuentas c ON g.cuenta_id = c.id
                JOIN categorias_gasto tg ON g.categoria_id = tg.id
                WHERE g.id = ? AND g.activo = 1
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = $pdo->query("
                SELECT g.*, c.nombre cuenta_nombre, tg.nombre tipo_gasto_nombre
                FROM gastos g
                JOIN cuentas c ON g.cuenta_id = c.id
                JOIN categorias_gasto tg ON g.categoria_id = tg.id
                WHERE g.activo = 1
                ORDER BY g.fecha DESC, g.id DESC
            ");
            echo json_encode($stmt->fetchAll());
        }
        exit;
    }



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Si no hay fecha, usar la fecha actual

        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        

        $stmt = $pdo->prepare("
            INSERT INTO gastos (fecha, cuenta_id, categoria_id, clasificacion, monto, concepto)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $fecha,
            $_POST['cuenta_id'],
            $_POST['categoria_id'],
            $_POST['clasificacion'], // Aquí recibimos si es fijo o variable
            $_POST['monto'],
            $_POST['concepto'] ?? null
        ]);



        $id = $pdo->lastInsertId();

        

        // Obtener información para auditoría

        $stmtGasto = $pdo->prepare("

            SELECT g.*, c.nombre cuenta_nombre, tg.nombre tipo_gasto_nombre

            FROM gastos g

            JOIN cuentas c ON g.cuenta_id = c.id

            JOIN categorias_gasto tg ON g.categoria_id = tg.id

            WHERE g.id = ?

        ");

        $stmtGasto->execute([$id]);

        $datos_gasto = $stmtGasto->fetch(PDO::FETCH_ASSOC);



        // Obtener saldo anterior

        $stmt = $pdo->prepare("SELECT saldo_actual FROM cuentas WHERE id = ?");

        $stmt->execute([$_POST['cuenta_id']]);

        $saldoAnterior = $stmt->fetch(PDO::FETCH_ASSOC)['saldo_actual'];

        

        $monto = (float)$_POST['monto'];

        $saldoNuevo = $saldoAnterior - $monto;



        // Actualizar saldo de la cuenta seleccionada

        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")

            ->execute([$saldoNuevo, $_POST['cuenta_id']]);



        // TAMBIÉN descontar automáticamente de Comisiones AMIRI (tipo_cuenta = 3)

        $stmtAmiri = $pdo->prepare("SELECT id, nombre, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");

        $stmtAmiri->execute();

        $cuentaAmiri = $stmtAmiri->fetch(PDO::FETCH_ASSOC);

        

        if ($cuentaAmiri) {

            $saldoAnteriorAmiri = $cuentaAmiri['saldo_actual'];

            $saldoNuevoAmiri = $saldoAnteriorAmiri - $monto;

            

            // Actualizar saldo de Comisiones AMIRI

            $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")

                ->execute([$saldoNuevoAmiri, $cuentaAmiri['id']]);

        }



        // Generar ID de grupo único para este gasto

        $grupo_id = round(microtime(true) * 1000);

        

        // Registrar movimiento en movimientos con gasto_id

        // servicio_id = 12 (Gasto)

        $movimiento_id = $pdo->prepare("

            INSERT INTO movimientos 

            (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, gasto_id, servicio_id, activo)

            VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, ?, 12, 1)

        ");

        $descripcion = "Gasto: {$datos_gasto['tipo_gasto_nombre']} - " . ($_POST['concepto'] ?? 'Sin concepto');

        $movimiento_id->execute([

            $grupo_id,

            $fecha,

            $monto,

            $descripcion,

            $_POST['cuenta_id'],

            $_SESSION['usuario_id'],

            $id

        ]);

        $flujo_movimiento_id = $pdo->lastInsertId();



        $pdo->prepare("

            INSERT INTO historial_saldos 

            (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)

            VALUES (?, ?, 'salida', ?, ?, ?)

        ")->execute([

            $_POST['cuenta_id'],

            $flujo_movimiento_id,

            $monto,

            $saldoAnterior,

            $saldoNuevo

        ]);

        

        // Registrar movimiento también para Comisiones AMIRI

        if ($cuentaAmiri) {

            // Crear segundo movimiento en movimientos para Comisiones AMIRI

            $movimiento_id_amiri = $pdo->prepare("

                INSERT INTO movimientos 

                (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, gasto_id, servicio_id, activo)

                VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, ?, 12, 1)

            ");

            $descripcion_amiri = "Gasto (desde Comisiones AMIRI): {$datos_gasto['tipo_gasto_nombre']} - " . ($_POST['concepto'] ?? 'Sin concepto');

            $movimiento_id_amiri->execute([

                $grupo_id,

                $fecha,

                $monto,

                $descripcion_amiri,

                $cuentaAmiri['id'],

                $_SESSION['usuario_id'],

                $id

            ]);

            $flujo_movimiento_id_amiri = $pdo->lastInsertId();

            

            // Registrar en historial_saldos para Comisiones AMIRI

            $pdo->prepare("

                INSERT INTO historial_saldos 

                (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)

                VALUES (?, ?, 'salida', ?, ?, ?)

            ")->execute([

                $cuentaAmiri['id'],

                $flujo_movimiento_id_amiri,

                $monto,

                $saldoAnteriorAmiri,

                $saldoNuevoAmiri

            ]);

        }

        

        // Registrar auditoría

        registrarAuditoria($pdo, [

            'accion' => 'INSERT',

            'modulo' => 'gastos',

            'tabla' => 'gastos',

            'registro_id' => $id,

            'descripcion' => "Registró gasto: $" . number_format($monto, 2) . " - {$datos_gasto['tipo_gasto_nombre']}",

            'datos_nuevos' => $datos_gasto

        ]);



        echo json_encode(['success' => true]);

        exit;

    }



} catch (Exception $e) {

    http_response_code(500);

    echo json_encode(['error' => $e->getMessage()]);

}

