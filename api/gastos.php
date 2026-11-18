<?php

require_once __DIR__ . '/../src/config/api-middleware.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';



initApi();

requireAuth(['admin', 'usuario']);

if (($_SESSION['usuario_rol'] ?? '') === 'usuario' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    jsonError('Solo lectura', 403);
}

// Liberar lock de sesión para permitir requests concurrentes (resumen + chart + tabla)
// Si no se libera, los 3 $.getJSON paralelos de gastos.php se bloquean entre sí
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$pdo = getPDO();

$pdo->exec("SET NAMES utf8mb4");



try {



    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $periodo = $_GET['periodo'] ?? null;
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $mes = $_GET['mes'] ?? null;

        if ($periodo) {
            switch ($periodo) {
                case 'mes':
                    $fechaInicio = date('Y-m-01');
                    $fechaFin = date('Y-m-t');
                    break;
                case 'mes_pasado':
                    $fechaInicio = date('Y-m-01', strtotime('-1 month'));
                    $fechaFin = date('Y-m-t', strtotime('-1 month'));
                    break;
                case 'anio':
                    $fechaInicio = date('Y-01-01');
                    $fechaFin = date('Y-12-31');
                    break;
                case 'rango':
                    break;
            }
        } elseif ($mes) {
            $fechaInicio = $mes . '-01';
            $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        }

        function buildFechaWhere($tableAlias, &$fi, &$ff) {
            if ($fi && $ff) {
                return " AND {$tableAlias}.fecha BETWEEN ? AND ?";
            }
            return '';
        }
        function addFechaParams(&$params, $fi, $ff) {
            if ($fi && $ff) {
                $params[] = $fi;
                $params[] = $ff;
            }
        }

        // ─── Resumen ─────────────────────────────────────
        if (isset($_GET['resumen'])) {
            $where = 'g.activo = 1';
            $params = [];
            $where .= buildFechaWhere('g', $fechaInicio, $fechaFin);
            addFechaParams($params, $fechaInicio, $fechaFin);
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
                $w2 .= buildFechaWhere('g', $fechaInicio, $fechaFin);
                addFechaParams($p2, $fechaInicio, $fechaFin);
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
            $where .= buildFechaWhere('g', $fechaInicio, $fechaFin);
            addFechaParams($params, $fechaInicio, $fechaFin);

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
            $where .= buildFechaWhere('g', $fechaInicio, $fechaFin);
            addFechaParams($params, $fechaInicio, $fechaFin);
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

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Soporta ?id=190 y body id=190 (parse_str o JSON)
        $raw = file_get_contents('php://input');
        $data = [];
        if ($raw) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = $json;
            } else {
                parse_str($raw, $data);
            }
        }
        $id = $_GET['id'] ?? $data['id'] ?? null;
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        // Obtener gasto y validar
        $stmt = $pdo->prepare("SELECT * FROM gastos WHERE id = ?");
        $stmt->execute([$id]);
        $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$gasto) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto no encontrado']);
            exit;
        }
        if ((int)$gasto['activo'] === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El gasto ya está anulado']);
            exit;
        }

        // Validar mes no cerrado (como en POST)
        validarMesNoCerrado($pdo, $gasto['fecha']);

        // Buscar grupo_id asociado (si existe)
        $stmt = $pdo->prepare("SELECT grupo_id FROM movimientos WHERE gasto_id = ? AND servicio_id = 12 AND estado = 'activo' LIMIT 1");
        $stmt->execute([$id]);
        $grupo_id = $stmt->fetchColumn();

        // Si tiene movimientos, usar servicio central (reversión contable completa)
        if ($grupo_id) {
            require_once __DIR__ . '/../src/services/MovimientoCorreccionService.php';
            $svc = new MovimientoCorreccionService($pdo);
            // Necesitamos reabrir sesión brevemente para obtener usuario_id si se cerró
            // $_SESSION sigue en memoria tras session_write_close(), así que usamos el valor ya cargado
            $usuario_id = $_SESSION['usuario_id'] ?? 1;
            $motivo = $data['motivo'] ?? "Anulación gasto #$id - " . ($gasto['concepto'] ?: 'sin concepto');
            $res = $svc->anularPorGrupo((int)$grupo_id, $motivo, (int)$usuario_id);
            if (!$res['success']) {
                http_response_code(500);
                echo json_encode(['error' => $res['error'] ?? 'Error al anular']);
                exit;
            }
            // El servicio ya hizo UPDATE gastos activo=0, revertir saldos y auditoría
            echo json_encode(['success' => true, 'message' => 'Gasto anulado correctamente', 'grupo_anulado' => $grupo_id, 'reverso' => $res['grupo_reverso'] ?? null]);
            exit;
        }

        // Fallback sin movimientos (gasto huérfano): soft-delete simple
        $pdo->prepare("UPDATE gastos SET activo = 0 WHERE id = ?")->execute([$id]);
        registrarAuditoria($pdo, [
            'accion' => 'DELETE',
            'modulo' => 'gastos',
            'tabla' => 'gastos',
            'registro_id' => $id,
            'descripcion' => "Anuló gasto #$id: $" . number_format($gasto['monto'], 2) . " - " . ($gasto['concepto'] ?: 'sin concepto'),
            'datos_anteriores' => $gasto
        ]);
        echo json_encode(['success' => true, 'message' => 'Gasto anulado (sin movimientos)']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);


} catch (Exception $e) {

    http_response_code(500);

    echo json_encode(['error' => $e->getMessage()]);

}

