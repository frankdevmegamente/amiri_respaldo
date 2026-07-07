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
        $mes = $_GET['mes'] ?? null;

        // ─── Resumen ─────────────────────────────────────
        if (isset($_GET['resumen'])) {
            $where = 'g.activo = 1';
            $params = [];
            if ($mes) {
                $where .= ' AND g.fecha LIKE ?';
                $params[] = $mes . '-%';
            }
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) AS total_gastos,
                    COALESCE(SUM(g.monto), 0) AS total_monto,
                    COALESCE(AVG(g.monto), 0) AS promedio_monto,
                    COALESCE(MAX(g.monto), 0) AS max_monto
                FROM gastos g
                WHERE $where
            ");
            $stmt->execute($params);
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resumen['max_monto'] > 0) {
                $w2 = 'g.activo = 1';
                $p2 = [];
                if ($mes) {
                    $w2 .= ' AND g.fecha LIKE ?';
                    $p2[] = $mes . '-%';
                }
                $stmt2 = $pdo->prepare("
                    SELECT g.concepto, tg.nombre AS tipo
                    FROM gastos g
                    JOIN categorias_gasto tg ON g.categoria_id = tg.id
                    WHERE $w2
                    ORDER BY g.monto DESC
                    LIMIT 1
                ");
                $stmt2->execute($p2);
                $top = $stmt2->fetch(PDO::FETCH_ASSOC);
                $resumen['max_concepto'] = $top ? ($top['concepto'] ?: $top['tipo']) : '—';
            } else {
                $resumen['max_concepto'] = '—';
            }

            $resumen['total_gastos'] = (int)$resumen['total_gastos'];
            $resumen['total_monto'] = (float)$resumen['total_monto'];
            $resumen['promedio_monto'] = (float)$resumen['promedio_monto'];
            $resumen['max_monto'] = (float)$resumen['max_monto'];

            echo json_encode($resumen);
            exit;
        }

        // ─── Charts ──────────────────────────────────────
        if (isset($_GET['chart'])) {
            $where = 'g.activo = 1';
            $params = [];
            if ($mes) {
                $where .= ' AND g.fecha LIKE ?';
                $params[] = $mes . '-%';
            }

            $stmt = $pdo->prepare("
                SELECT tg.nombre AS tipo, COALESCE(g.clasificacion, 'sin_definir') AS clasificacion, COALESCE(SUM(g.monto), 0) AS total
                FROM gastos g
                JOIN categorias_gasto tg ON g.categoria_id = tg.id
                WHERE $where
                GROUP BY tg.nombre, g.clasificacion
                ORDER BY total DESC
            ");
            $stmt->execute($params);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $consolidado = [];
            $order = [];
            foreach ($datos as $d) {
                $tipo = $d['tipo'];
                if (!isset($consolidado[$tipo])) {
                    $consolidado[$tipo] = ['tipo' => $tipo, 'total' => 0, 'clasificacion' => $d['clasificacion']];
                    $order[] = $tipo;
                }
                $consolidado[$tipo]['total'] += (float)$d['total'];
                if ($consolidado[$tipo]['clasificacion'] !== $d['clasificacion']) {
                    $consolidado[$tipo]['clasificacion'] = 'mixto';
                }
            }

            $resultado = [];
            foreach ($order as $t) {
                $resultado[] = $consolidado[$t];
            }

            echo json_encode($resultado);
            exit;
        }

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
            $where = 'g.activo = 1';
            $params = [];
            if ($mes) {
                $where .= ' AND g.fecha LIKE ?';
                $params[] = $mes . '-%';
            }
            $stmt = $pdo->prepare("
                SELECT g.*, c.nombre cuenta_nombre, tg.nombre tipo_gasto_nombre
                FROM gastos g
                JOIN cuentas c ON g.cuenta_id = c.id
                JOIN categorias_gasto tg ON g.categoria_id = tg.id
                WHERE $where
                ORDER BY g.fecha DESC, g.id DESC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        }
        exit;
    }



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Si no hay fecha, usar la fecha actual

        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        validarMesNoCerrado($pdo, $fecha);

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

        $saldoNuevo = round($saldoAnterior - $monto, 2);



        // Actualizar saldo de la cuenta seleccionada
        $pdo->exec("SET @hyperion_saldo_auth = 1");
        $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")
            ->execute([$saldoNuevo, $_POST['cuenta_id']]);
        
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



        // Descontar también de la cuenta de Capital (Utilidades AMIRI)
        $stmtCapital = $pdo->query("SELECT id, saldo_actual FROM cuentas WHERE tipo_cuenta = 3 AND activo = 1 LIMIT 1");
        $capital = $stmtCapital->fetch(PDO::FETCH_ASSOC);
        $capital_id = $capital ? (int)$capital['id'] : null;

        if ($capital_id && (int)$_POST['cuenta_id'] !== $capital_id) {
            $saldoCapitalAnterior = (float)$capital['saldo_actual'];
            $saldoCapitalNuevo = round($saldoCapitalAnterior - $monto, 2);

            $stmtMovCapital = $pdo->prepare("
                INSERT INTO movimientos
                (grupo_id, fecha, tipo, monto, moneda, descripcion, cuenta_origen_id, cuenta_destino_id, usuario_id, gasto_id, servicio_id, activo)
                VALUES (?, ?, 'salida', ?, 'MXN', ?, ?, NULL, ?, ?, 12, 1)
            ");
            $descCapital = "Gasto (desde Comisiones AMIRI): {$datos_gasto['tipo_gasto_nombre']} - " . ($_POST['concepto'] ?? 'Sin concepto');
            $stmtMovCapital->execute([
                $grupo_id,
                $fecha,
                $monto,
                $descCapital,
                $capital_id,
                $_SESSION['usuario_id'],
                $id
            ]);
            $movCapitalId = $pdo->lastInsertId();

            $pdo->exec("SET @hyperion_saldo_auth = 1");
            $pdo->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?")
                ->execute([$saldoCapitalNuevo, $capital_id]);

            $pdo->prepare("
                INSERT INTO historial_saldos
                (cuenta_id, movimiento_id, tipo, monto, saldo_anterior, saldo_nuevo)
                VALUES (?, ?, 'salida', ?, ?, ?)
            ")->execute([
                $capital_id,
                $movCapitalId,
                $monto,
                $saldoCapitalAnterior,
                $saldoCapitalNuevo
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

