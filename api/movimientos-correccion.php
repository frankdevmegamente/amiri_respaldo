<?php

/**

 * API: Gestión de correcciones de movimientos

 * 

 * Endpoints:

 * - GET ?action=historial&id={movimiento_id} - Obtener historial de correcciones

 * - POST action=anular - Anular un movimiento

 * - POST action=corregir - Corregir un movimiento

 */



require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/auditoria.php';

require_once __DIR__ . '/../src/config/helpers.php';

require_once __DIR__ . '/../src/services/MovimientoCorreccionService.php';



header('Content-Type: application/json');



// Validar autenticación

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode(['error' => 'No autenticado']);

    exit;

}



// Solo admins pueden hacer correcciones

if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {

    http_response_code(403);

    echo json_encode(['error' => 'Solo administradores pueden hacer correcciones']);

    exit;

}



$usuario_id = $_SESSION['usuario_id'];



try {

    $pdo = getPDO();

    $service = new MovimientoCorreccionService($pdo);

    

    $method = $_SERVER['REQUEST_METHOD'];

    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    

    // =========================================================================

    // GET: Consultas

    // =========================================================================

    if ($method === 'GET') {

        switch ($action) {

            case 'historial':

                // Obtener historial de correcciones de un movimiento

                $movimiento_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                $historial = $service->obtenerHistorialCorrecciones($movimiento_id);

                echo json_encode([

                    'success' => true,

                    'historial' => $historial

                ]);

                break;

                

            case 'detalle':

                // Obtener detalle de un movimiento

                $movimiento_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                $movimiento = $service->obtenerMovimiento($movimiento_id);

                

                if (!$movimiento) {

                    http_response_code(404);

                    echo json_encode(['error' => 'Movimiento no encontrado']);

                    exit;

                }

                

                echo json_encode([

                    'success' => true,

                    'movimiento' => $movimiento

                ]);

                break;

                

            case 'ultimo':

                // Buscar el último grupo activo (por el ID más alto de cualquier movimiento del grupo)

                // Excluir grupos que ya tienen al menos un movimiento anulado/revertido

                $stmt = $pdo->prepare("

                    SELECT m.grupo_id, MAX(m.id) AS max_id

                    FROM movimientos m

                    WHERE m.estado = 'activo' AND m.activo = 1

                      AND (m.es_reverso = 0 OR m.es_reverso IS NULL)

                    GROUP BY m.grupo_id

                    ORDER BY max_id DESC

                    LIMIT 1

                ");

                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);



                if (!$row) {

                    echo json_encode(['success' => true, 'movimiento' => null]);

                    break;

                }



                // Obtener el movimiento "representativo" del grupo:

                // el primero (id ASC) que no sea historial (descripcion no empieza con 'Registro venta:')

                $stmtRep = $pdo->prepare("

                    SELECT m.*, s.nombre AS servicio_nombre,

                           cl.nombre AS cliente_nombre, pr.nombre AS proveedor_nombre,

                           cu_orig.nombre AS cuenta_origen_nombre,

                           cu_dest.nombre AS cuenta_destino_nombre

                    FROM movimientos m

                    LEFT JOIN servicios s ON m.servicio_id = s.id

                    LEFT JOIN clientes cl ON m.cliente_id = cl.id

                    LEFT JOIN proveedores pr ON m.proveedor_id = pr.id

                    LEFT JOIN cuentas cu_orig ON m.cuenta_origen_id = cu_orig.id

                    LEFT JOIN cuentas cu_dest ON m.cuenta_destino_id = cu_dest.id

                    WHERE m.grupo_id = ? AND m.estado = 'activo' AND m.activo = 1

                      AND (m.es_reverso = 0 OR m.es_reverso IS NULL)

                      AND (m.descripcion NOT LIKE 'Registro venta:%' OR m.descripcion IS NULL)

                    ORDER BY m.id ASC

                    LIMIT 1

                ");

                $stmtRep->execute([$row['grupo_id']]);

                $ultimo = $stmtRep->fetch(PDO::FETCH_ASSOC);



                if (!$ultimo) {

                    echo json_encode(['success' => true, 'movimiento' => null]);

                    break;

                }



                $stmtGrupo = $pdo->prepare("

                    SELECT m.*, s.nombre AS servicio_nombre,

                           cu_orig.nombre AS cuenta_origen_nombre,

                           cu_dest.nombre AS cuenta_destino_nombre

                    FROM movimientos m

                    LEFT JOIN servicios s ON m.servicio_id = s.id

                    LEFT JOIN cuentas cu_orig ON m.cuenta_origen_id = cu_orig.id

                    LEFT JOIN cuentas cu_dest ON m.cuenta_destino_id = cu_dest.id

                    WHERE m.grupo_id = ? AND m.estado = 'activo' AND m.activo = 1

                    ORDER BY m.id ASC

                ");

                $stmtGrupo->execute([$ultimo['grupo_id']]);

                $grupo = $stmtGrupo->fetchAll(PDO::FETCH_ASSOC);

                // Pre-calcular efectos del reverso
                $cuentasMap = [];
                $deudasAfectadas = [];

                foreach ($grupo as $mov) {
                    $montoAbs = abs((float)$mov['monto']);

                    // Efecto en cuenta origen
                    if (!empty($mov['cuenta_origen_id'])) {
                        $tipoRev = ($mov['tipo'] === 'salida' || $mov['tipo'] === 'traspaso') ? 'entrada' : 'salida';
                        $cid = $mov['cuenta_origen_id'];
                        if (!isset($cuentasMap[$cid])) {
                            $sC = $pdo->prepare("SELECT nombre, saldo_actual FROM cuentas WHERE id = ?");
                            $sC->execute([$cid]);
                            $c = $sC->fetch(PDO::FETCH_ASSOC);
                            $cuentasMap[$cid] = ['nombre' => $c['nombre'], 'saldo' => (float)$c['saldo_actual'], 'ajuste' => 0];
                        }
                        $cuentasMap[$cid]['ajuste'] += $tipoRev === 'entrada' ? $montoAbs : -$montoAbs;
                    }

                    // Efecto en cuenta destino (adeudo proveedor s5/s6 a cuenta
                    // tipo 6 se revierte con 'salida', ver MovimientoCorreccionService)
                    if (!empty($mov['cuenta_destino_id'])) {
                        $esAdeudoProv = (($mov['tipo'] ?? '') === 'salida' && in_array((int)($mov['servicio_id'] ?? 0), [5, 6], true));
                        if ($esAdeudoProv) {
                            $sT = $pdo->prepare("SELECT tipo_cuenta FROM cuentas WHERE id = ?");
                            $sT->execute([$mov['cuenta_destino_id']]);
                            $esAdeudoProv = ((int)$sT->fetchColumn() === 6);
                        }
                        $tipoRev = ($esAdeudoProv || ($mov['tipo'] === 'entrada' || $mov['tipo'] === 'traspaso' || $mov['tipo'] === 'comision')) ? 'salida' : 'entrada';
                        $cid = $mov['cuenta_destino_id'];
                        if (!isset($cuentasMap[$cid])) {
                            $sC = $pdo->prepare("SELECT nombre, saldo_actual FROM cuentas WHERE id = ?");
                            $sC->execute([$cid]);
                            $c = $sC->fetch(PDO::FETCH_ASSOC);
                            $cuentasMap[$cid] = ['nombre' => $c['nombre'], 'saldo' => (float)$c['saldo_actual'], 'ajuste' => 0];
                        }
                        $cuentasMap[$cid]['ajuste'] += $tipoRev === 'entrada' ? $montoAbs : -$montoAbs;
                    }

                    // Cleanup específico por servicio
                    $servId = (int)$mov['servicio_id'];
                    if ($servId === 13 && !empty($mov['referencia_id'])) {
                        $sD = $pdo->prepare("SELECT id, monto_pagado, estado FROM deudas_clientes WHERE id = ? AND activo = 1");
                        $sD->execute([$mov['referencia_id']]);
                        $deuda = $sD->fetch(PDO::FETCH_ASSOC);
                        if ($deuda) {
                            $nuevoPagado = max(0, (float)$deuda['monto_pagado'] - $montoAbs);
                            $nuevoEstado = $nuevoPagado <= 0 ? 'pendiente' : 'parcial';
                            $deudasAfectadas[] = [
                                'entidad' => 'deudas_clientes',
                                'id' => (int)$deuda['id'],
                                'monto_pagado_anterior' => (float)$deuda['monto_pagado'],
                                'monto_pagado_nuevo' => $nuevoPagado,
                                'estado_anterior' => $deuda['estado'],
                                'estado_nuevo' => $nuevoEstado
                            ];
                        }
                    } elseif ($servId === 1 && !empty($mov['referencia_id'])) {
                        $sD = $pdo->prepare("SELECT id, monto_pagado, estado FROM deudas_clientes WHERE id = ? AND activo = 1");
                        $sD->execute([$mov['referencia_id']]);
                        $deuda = $sD->fetch(PDO::FETCH_ASSOC);
                        if ($deuda) {
                            $deudasAfectadas[] = [
                                'entidad' => 'deudas_clientes',
                                'id' => (int)$deuda['id'],
                                'monto_pagado_anterior' => (float)$deuda['monto_pagado'],
                                'monto_pagado_nuevo' => (float)$deuda['monto_pagado'],
                                'estado_anterior' => $deuda['estado'],
                                'estado_nuevo' => $deuda['estado'],
                                'accion' => 'Se restaurará a activo'
                            ];
                        }
                    }
                }

                // Construir array de efectos
                $efectos = [];
                foreach ($cuentasMap as $cid => $c) {
                    $nuevoSaldo = $c['saldo'] + $c['ajuste'];
                    $efectos[] = [
                        'cuenta_id' => $cid,
                        'cuenta_nombre' => $c['nombre'],
                        'saldo_anterior' => $c['saldo'],
                        'saldo_nuevo' => $nuevoSaldo,
                        'diferencia' => $c['ajuste'],
                        'moneda' => $ultimo['moneda'] ?? 'MXN'
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'movimiento' => $ultimo,
                    'grupo' => $grupo,
                    'efectos' => $efectos,
                    'deudas_afectadas' => $deudasAfectadas,
                    'total_movimientos_reverso' => count($grupo)
                ]);

                break;



            default:

                http_response_code(400);

                echo json_encode(['error' => 'Acción no especificada']);

        }

    }



    // =========================================================================

    // POST: Modificaciones

    // =========================================================================

    elseif ($method === 'POST') {

        switch ($action) {

            case 'anular':
                if (!isset($_POST['grupo_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No se especificó el grupo a anular']);
                    exit;
                }
                
                $grupo_id = $_POST['grupo_id'];
                $motivo = $_POST['motivo'] ?? 'Anulación manual';

                try {
                    global $pdo;
                    if (!isset($pdo)) {
                        $pdo = getPDO();
                    }

                    // Validación centralizada (mes cerrado + candados préstamos)
                    validarGrupoNoCerrado($pdo, $grupo_id);

                    // --- [EJECUCIÓN CENTRALIZADA] ---
                    // El Service ahora hará todo el trabajo matemático una sola vez.
                    $resultado = $service->anularPorGrupo($grupo_id, $motivo, $usuario_id);
                    
                    if (isset($resultado['success']) && $resultado['success']) {
                        // Si el grupo anulado contenía un movimiento de inicialización (servicio_id=19),
                        // limpiar los cierres_mensuales que creó la inicialización
                        $stmtCheckInit = $pdo->prepare("SELECT COUNT(*) FROM movimientos WHERE grupo_id = ? AND servicio_id = 19");
                        $stmtCheckInit->execute([$grupo_id]);
                        if ($stmtCheckInit->fetchColumn() > 0) {
                            $pdo->exec("DELETE FROM cierres_mensuales");
                        }
                        echo json_encode($resultado);
                    } else {
                        http_response_code(400);
                        echo json_encode($resultado);
                    }

                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                break;

                

            case 'corregir':

                // Corregir un movimiento

                $movimiento_id = filter_var($_POST['movimiento_id'] ?? null, FILTER_VALIDATE_INT);

                $motivo = trim($_POST['motivo'] ?? '');

                

                if (!$movimiento_id) {

                    http_response_code(400);

                    echo json_encode(['error' => 'ID de movimiento requerido']);

                    exit;

                }

                

                if (empty($motivo)) {

                    http_response_code(400);

                    echo json_encode(['error' => 'Motivo de corrección requerido']);

                    exit;

                }

                // Validar que el movimiento original no pertenezca a un mes cerrado

                $stmtOrig = $pdo->prepare("SELECT fecha FROM movimientos WHERE id = ? AND activo = 1");

                $stmtOrig->execute([$movimiento_id]);

                $movOriginal = $stmtOrig->fetch(PDO::FETCH_ASSOC);

                if ($movOriginal) {

                    validarMesNoCerrado($pdo, explode(' ', $movOriginal['fecha'])[0]);

                }

                // Construir array de datos corregidos

                $datos_corregidos = [];

                

                // Solo incluir campos que fueron enviados/modificados

                if (isset($_POST['monto'])) {

                    $datos_corregidos['monto'] = floatval($_POST['monto']);

                }

                if (isset($_POST['fecha'])) {

                    validarMesNoCerrado($pdo, $_POST['fecha']);

                    $datos_corregidos['fecha'] = $_POST['fecha'];

                }

                if (isset($_POST['cliente_id'])) {

                    $datos_corregidos['cliente_id'] = $_POST['cliente_id'] ?: null;

                }

                if (isset($_POST['promotor_id'])) {

                    $datos_corregidos['promotor_id'] = $_POST['promotor_id'] ?: null;

                }

                if (isset($_POST['proveedor_id'])) {

                    $datos_corregidos['proveedor_id'] = $_POST['proveedor_id'] ?: null;

                }

                if (isset($_POST['cuenta_origen_id'])) {

                    $datos_corregidos['cuenta_origen_id'] = $_POST['cuenta_origen_id'] ?: null;

                }

                if (isset($_POST['cuenta_destino_id'])) {

                    $datos_corregidos['cuenta_destino_id'] = $_POST['cuenta_destino_id'] ?: null;

                }

                if (isset($_POST['paridad'])) {

                    $datos_corregidos['paridad'] = floatval($_POST['paridad']);

                }

                if (isset($_POST['descripcion'])) {

                    $datos_corregidos['descripcion'] = $_POST['descripcion'];

                }

                

                if (empty($datos_corregidos)) {

                    http_response_code(400);

                    echo json_encode(['error' => 'No se especificaron datos a corregir']);

                    exit;

                }

                

                $resultado = $service->corregirMovimiento($movimiento_id, $datos_corregidos, $motivo, $usuario_id);

                

                if ($resultado['success']) {

                    echo json_encode($resultado);

                } else {

                    http_response_code(400);

                    echo json_encode($resultado);

                }

                break;

                

            default:

                http_response_code(400);

                echo json_encode(['error' => 'Acción no especificada']);

        }

    }

    

    else {

        http_response_code(405);

        echo json_encode(['error' => 'Método no permitido']);

    }

    

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([

        'error' => 'Error interno del servidor',

        'message' => $e->getMessage()

    ]);

}

