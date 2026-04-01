<?php

/**

 * Middleware para validar dispositivo de confianza

 * Permite saltarse 2FA si el dispositivo está dentro de 24 horas

 */



function checkTrustedDevice($pdo, $usuarioId) {

    // Verificar si hay cookie de dispositivo de confianza

    $deviceToken = $_COOKIE['trusted_device'] ?? null;

    

    if (!$deviceToken) {

        return ['trusted' => false];

    }



    try {

        require_once __DIR__ . '/../services/TwoFactorService.php';

        $twoFAService = new TwoFactorService($pdo);



        // Validar que el dispositivo es válido y no ha expirado

        $isValid = $twoFAService->validateTrustedDevice($usuarioId, $deviceToken);

        

        if ($isValid) {

            return [

                'trusted' => true,

                'device_token' => $deviceToken,

                'message' => 'Dispositivo de confianza válido'

            ];

        }



        // Dispositivo expirado, eliminar cookie

        setcookie('trusted_device', '', time() - 3600, '/');

        return ['trusted' => false];



    } catch (Exception $e) {

        error_log("Error verificando dispositivo de confianza: " . $e->getMessage());

        return ['trusted' => false];

    }

}



/**

 * Middleware para aplicar en login.php después de verificar credenciales

 * Si el usuario tiene 2FA y un dispositivo de confianza válido, salta la verificación

 */

function processTrustedDeviceLogin($pdo, $usuario, $rateLimitKey) {

    // Si el usuario no tiene 2FA, login normal

    if (!$usuario['two_fa_enabled']) {

        return ['skip_2fa' => false];

    }



    // Verificar si hay dispositivo de confianza

    $trustedCheck = checkTrustedDevice($pdo, $usuario['id']);



    if ($trustedCheck['trusted']) {

        // Dispositivo confiable - permitir login sin 2FA

        return [

            'skip_2fa' => true,

            'device_trusted' => true,

            'message' => 'Dispositivo de confianza, 2FA saltado'

        ];

    }



    // Sin dispositivo de confianza válido - requiere 2FA

    return ['skip_2fa' => false];

}

