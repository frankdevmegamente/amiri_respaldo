<?php
require_once __DIR__ . '/../src/config/api-middleware.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/auditoria.php';

initApi();
requireAdmin();
$pdo = getPDO();

try {

    /* =========================
       GET
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        // Obtener una cuenta específica por ID
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT *
                FROM cuentas
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            exit;
        }

        // Listar combinado por tipo de moneda y tipo de cuenta
        if (isset($_GET['tipo_moneda']) && isset($_GET['tipo_cuenta'])) {
            $tipoMoneda = $_GET['tipo_moneda'];
            $tipoCuenta = (int)$_GET['tipo_cuenta'];

            if ($tipoCuenta === 2) {
                // Si pide tipo 2 (Caja), incluir todas las variantes de caja: 2,4,5,6
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM cuentas
                    WHERE tipo_moneda = ? AND tipo_cuenta IN (2, 4, 5, 6) AND activo = 1
                    ORDER BY nombre
                ");
                $stmt->execute([$tipoMoneda]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM cuentas
                    WHERE tipo_moneda = ? AND tipo_cuenta = ? AND activo = 1
                    ORDER BY nombre
                ");
                $stmt->execute([$tipoMoneda, $tipoCuenta]);
            }

            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Listar por tipo de moneda
        if (isset($_GET['tipo_moneda'])) {
            $tipoMoneda = $_GET['tipo_moneda'];
            $esCajaEmpresa = isset($_GET['es_caja_empresa']) ? (int)$_GET['es_caja_empresa'] : null;
            $conSaldo = isset($_GET['con_saldo']) ? (int)$_GET['con_saldo'] : 0;
            
            // Construir condiciones
            $conditions = ["tipo_moneda = ?", "activo = 1"];
            $params = [$tipoMoneda];
            
            if ($esCajaEmpresa !== null) {
                $conditions[] = "es_caja_empresa = ?";
                $params[] = $esCajaEmpresa;
            }
            
            if ($conSaldo) {
                $conditions[] = "saldo_actual > 0";
            }
            
            $whereClause = implode(' AND ', $conditions);
            $stmt = $pdo->prepare("SELECT * FROM cuentas WHERE $whereClause ORDER BY nombre");
            $stmt->execute($params);
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Filtro para gastos: cuentas bancarias (tipo 1) y caja de efectivo (tipo 2)
        // Se excluye tipo 3 (Comisiones AMIRI) porque se descuenta automáticamente
        if (isset($_GET['solo_gastos'])) {
            $conSaldo = isset($_GET['con_saldo']) ? (int)$_GET['con_saldo'] : 0;
            $tipoMonedaFiltro = $_GET['tipo_moneda'] ?? null;
            $params = [];
            $esCajaEmpresaFiltro = isset($_GET['es_caja_empresa']) ? (int)$_GET['es_caja_empresa'] : null;
            $sql = "SELECT * FROM cuentas WHERE tipo_cuenta IN (1, 2) AND activo = 1";
            if ($tipoMonedaFiltro) {
                $sql .= " AND tipo_moneda = ?";
                $params[] = $tipoMonedaFiltro;
            }
            if ($esCajaEmpresaFiltro !== null) {
                $sql .= " AND es_caja_empresa = ?";
                $params[] = $esCajaEmpresaFiltro;
            }
            if ($conSaldo) {
                $sql .= " AND saldo_actual > 0";
            }
            $sql .= " ORDER BY tipo_cuenta, nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Listar por tipo de cuenta
        if (isset($_GET['tipo_cuenta'])) {
            $tipoCuenta = (int)$_GET['tipo_cuenta'];
            $conSaldo = isset($_GET['con_saldo']) ? (int)$_GET['con_saldo'] : 0;
            $saldoCondition = $conSaldo ? " AND saldo_actual > 0" : "";

            if ($tipoCuenta === 2) {
                // Si pide tipo 2 (Caja), incluir todas las variantes: 2,4,5,6
                $stmt = $pdo->query("
                    SELECT *
                    FROM cuentas
                    WHERE tipo_cuenta IN (2, 4, 5, 6) AND activo = 1" . $saldoCondition . "
                    ORDER BY nombre
                ");
            } else {
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM cuentas
                    WHERE tipo_cuenta = ? AND activo = 1" . $saldoCondition . "
                    ORDER BY nombre
                ");
                $stmt->execute([$tipoCuenta]);
            }

            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // Todas las cuentas activas (sin filtro)
        $stmt = $pdo->query("SELECT * FROM cuentas WHERE activo = 1 ORDER BY nombre");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /* =========================
       POST (crear / editar)
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $id          = $_POST['id'] ?? null;
        $nombre      = trim($_POST['nombre']);
        $tipoCuenta  = (int)$_POST['tipo_cuenta'];
        $tipoMoneda  = $_POST['tipo_moneda'];
        $activo      = (int)$_POST['activo'];
        $esCajaEmpresa = (int)($_POST['es_caja_empresa'] ?? 0);

        $numeroCuenta = $_POST['numero_cuenta'] ?? null;
        $banco        = $_POST['banco'] ?? null;

        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre obligatorio']);
            exit;
        }

        if ($tipoCuenta === 1 && (!$numeroCuenta || !$banco)) {
            http_response_code(400);
            echo json_encode(['error' => 'Banco y número obligatorios']);
            exit;
        }

        // Procesar documento si se subió
        $documentoPath = null;
        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $documento = $_FILES['documento'];
            
            // Validar extensión
            $extension = strtolower(pathinfo($documento['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($extension, $extensionesPermitidas)) {
                http_response_code(400);
                echo json_encode(['error' => 'Formato de archivo no permitido']);
                exit;
            }

            // Validar tamaño (máximo 5MB)
            if ($documento['size'] > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['error' => 'Archivo demasiado grande (máximo 5MB)']);
                exit;
            }

            // Crear directorio si no existe
            $uploadDir = __DIR__ . '/../uploads/cuentas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generar nombre único
            $nombreArchivo = 'cuenta_' . time() . '_' . uniqid() . '.' . $extension;
            $rutaArchivo = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($documento['tmp_name'], $rutaArchivo)) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al subir el archivo']);
                exit;
            }

            // Ruta relativa para guardar en BD
            $documentoPath = 'uploads/cuentas/' . $nombreArchivo;
        }

        if ($id) {
            // EDITAR
            if ($documentoPath) {
                // Obtener datos anteriores
                $datos_anteriores = obtenerDatosAnteriores($pdo, 'cuentas', $id);
                
                // Si hay nuevo documento, eliminar el anterior
                $stmt = $pdo->prepare("SELECT documento_path FROM cuentas WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && $row['documento_path']) {
                    $rutaAnterior = __DIR__ . '/../' . $row['documento_path'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                $stmt = $pdo->prepare("
                    UPDATE cuentas SET
                        nombre = ?,
                        numero_cuenta = ?,
                        banco = ?,
                        tipo_moneda = ?,
                        activo = ?,
                        es_caja_empresa = ?,
                        documento_path = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nombre,
                    $tipoCuenta === 1 ? $numeroCuenta : null,
                    $tipoCuenta === 1 ? $banco : null,
                    $tipoMoneda,
                    $activo,
                    $esCajaEmpresa,
                    $documentoPath,
                    $id
                ]);
                
                // Registrar auditoría
                registrarAuditoria($pdo, [
                    'accion' => 'UPDATE',
                    'modulo' => 'cuentas',
                    'tabla' => 'cuentas',
                    'registro_id' => $id,
                    'descripcion' => "Actualizó cuenta: $nombre (con nuevo documento)",
                    'datos_anteriores' => $datos_anteriores,
                    'datos_nuevos' => [
                        'nombre' => $nombre,
                        'numero_cuenta' => $tipoCuenta === 1 ? $numeroCuenta : null,
                        'banco' => $tipoCuenta === 1 ? $banco : null,
                        'tipo_moneda' => $tipoMoneda,
                        'activo' => $activo,
                        'es_caja_empresa' => $esCajaEmpresa,
                        'documento_actualizado' => true
                    ]
                ]);
            } else {
                // Obtener datos anteriores
                $datos_anteriores = obtenerDatosAnteriores($pdo, 'cuentas', $id);
                
                // Sin nuevo documento
                $stmt = $pdo->prepare("
                    UPDATE cuentas SET
                        nombre = ?,
                        numero_cuenta = ?,
                        banco = ?,
                        tipo_moneda = ?,
                        activo = ?,
                        es_caja_empresa = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nombre,
                    $tipoCuenta === 1 ? $numeroCuenta : null,
                    $tipoCuenta === 1 ? $banco : null,
                    $tipoMoneda,
                    $activo,
                    $esCajaEmpresa,
                    $id
                ]);
                
                // Registrar auditoría
                registrarAuditoria($pdo, [
                    'accion' => 'UPDATE',
                    'modulo' => 'cuentas',
                    'tabla' => 'cuentas',
                    'registro_id' => $id,
                    'descripcion' => "Actualizó cuenta: $nombre",
                    'datos_anteriores' => $datos_anteriores,
                    'datos_nuevos' => [
                        'nombre' => $nombre,
                        'numero_cuenta' => $tipoCuenta === 1 ? $numeroCuenta : null,
                        'banco' => $tipoCuenta === 1 ? $banco : null,
                        'tipo_moneda' => $tipoMoneda,
                        'activo' => $activo,
                        'es_caja_empresa' => $esCajaEmpresa
                    ]
                ]);
            }
        } else {
            // CREAR (saldo = 0 SIEMPRE)
            $stmt = $pdo->prepare("
                INSERT INTO cuentas
                (nombre, numero_cuenta, banco, tipo_cuenta, tipo_moneda, saldo_actual, activo, es_caja_empresa, documento_path)
                VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)
            ");
            $stmt->execute([
                $nombre,
                $tipoCuenta === 1 ? $numeroCuenta : null,
                $tipoCuenta === 1 ? $banco : null,
                $tipoCuenta,
                $tipoMoneda,
                $activo,
                $esCajaEmpresa,
                $documentoPath
            ]);
            
            $cuenta_id = $pdo->lastInsertId();
            
            // Registrar auditoría
            registrarAuditoria($pdo, [
                'accion' => 'INSERT',
                'modulo' => 'cuentas',
                'tabla' => 'cuentas',
                'registro_id' => $cuenta_id,
                'descripcion' => "Creó cuenta: $nombre ($tipoMoneda)",
                'datos_nuevos' => [
                    'id' => $cuenta_id,
                    'nombre' => $nombre,
                    'numero_cuenta' => $tipoCuenta === 1 ? $numeroCuenta : null,
                    'banco' => $tipoCuenta === 1 ? $banco : null,
                    'tipo_cuenta' => $tipoCuenta,
                    'tipo_moneda' => $tipoMoneda,
                    'activo' => $activo,
                    'es_caja_empresa' => $esCajaEmpresa
                ]
            ]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    /* =========================
       DELETE
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $data);
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido']);
            exit;
        }
        
        // Obtener datos antes de eliminar
        $datos_anteriores = obtenerDatosAnteriores($pdo, 'cuentas', $id);

        // Soft-delete: desactivar cuenta (NUNCA borrar físicamente)
        $stmt = $pdo->prepare("UPDATE cuentas SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($datos_anteriores) {
            registrarAuditoria($pdo, [
                'accion' => 'SOFT_DELETE',
                'modulo' => 'cuentas',
                'tabla' => 'cuentas',
                'registro_id' => $id,
                'descripcion' => "Desactivó cuenta: {$datos_anteriores['nombre']}",
                'datos_anteriores' => $datos_anteriores
            ]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}
