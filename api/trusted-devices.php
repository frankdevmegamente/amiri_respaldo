<?php

/**

 * API: Gestión de dispositivos de confianza

 */



require_once __DIR__ . '/../src/config/session.php';

require_once __DIR__ . '/../src/config/db.php';

require_once __DIR__ . '/../src/config/logger.php';

require_once __DIR__ . '/../src/services/TwoFactorService.php';



header('Content-Type: application/json');



// Validar autenticación

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode(['error' => 'No autenticado']);

    exit;

}



$usuarioId = $_SESSION['usuario_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? null;



try {

    $pdo = getPDO();

    $twoFAService = new TwoFactorService($pdo);



    switch ($action) {

        case 'list':

            // Listar dispositivos de confianza activos

            $devices = $twoFAService->getTrustedDevices($usuarioId);

            echo json_encode([

                'success' => true,

                'devices' => $devices,

                'count' => count($devices)

            ]);

            break;



        case 'revoke':

            // Revocar un dispositivo específico

            $deviceId = $_POST['device_id'] ?? null;



            if (!$deviceId) {

                http_response_code(400);

                echo json_encode(['error' => 'Device ID requerido']);

                exit;

            }



            if ($twoFAService->revokeTrustedDevice($usuarioId, $deviceId)) {

                Logger::audit('trusted_device_revoked', $usuarioId, [

                    'device_id' => $deviceId,

                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'

                ]);



                echo json_encode([

                    'success' => true,

                    'message' => 'Dispositivo revocado'

                ]);

            } else {

                http_response_code(400);

                echo json_encode(['error' => 'No se pudo revocar el dispositivo']);

            }

            break;



        case 'revoke_all':

            // Revocar todos los dispositivos

            $stmt = $pdo->prepare("DELETE FROM trusted_devices WHERE usuario_id = ?");



            if ($stmt->execute([$usuarioId])) {

                Logger::audit('all_trusted_devices_revoked', $usuarioId, [

                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'

                ]);



                echo json_encode([

                    'success' => true,

                    'message' => 'Todos los dispositivos han sido revocados'

                ]);

            } else {

                http_response_code(400);

                echo json_encode(['error' => 'No se pudieron revocar los dispositivos']);

            }

            break;



        case 'cleanup':

            // Limpiar dispositivos expirados (admin)

            if ($_SESSION['usuario_rol'] !== 'admin') {

                http_response_code(403);

                echo json_encode(['error' => 'No autorizado']);

                exit;

            }



            $twoFAService->cleanupExpiredDevices();

            $twoFAService->cleanupOldAttempts();



            echo json_encode([

                'success' => true,

                'message' => 'Limpieza completada'

            ]);

            break;



        case 'status':

            // Obtener estado de 2FA del usuario

            $stmt = $pdo->prepare("

                SELECT 

                    two_fa_enabled,

                    two_fa_created_at,

                    (SELECT COUNT(*) FROM trusted_devices WHERE usuario_id = ? AND expires_at > NOW()) as active_devices

                FROM usuarios 

                WHERE id = ?

            ");

            $stmt->execute([$usuarioId, $usuarioId]);

            $status = $stmt->fetch(PDO::FETCH_ASSOC);



            echo json_encode([

                'success' => true,

                'status' => $status

            ]);

            break;



        default:

            http_response_code(400);

            echo json_encode(['error' => 'Acción no válida']);

    }

} catch (Exception $e) {

    error_log("Error en trusted-devices API: " . $e->getMessage());

    http_response_code(500);

    echo json_encode(['error' => 'Error interno']);

}

