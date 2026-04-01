<?php

/**

 * Servicio de Autenticación de Dos Factores (2FA) con TOTP

 * Utiliza Google Authenticator estándar (RFC 6238)

 */



use OTPHP\TOTP;

use OTPHP\Factory;



class TwoFactorService {

    private $pdo;

    private $encryptionKey;

    

    // Configuración

    private const TOTP_PERIOD = 30;           // Códigos cada 30 segundos (RFC 6238)

    private const TOTP_DIGITS = 6;            // 6 dígitos estándar

    private const TOTP_ALGORITHM = 'SHA1';    // Algoritmo estándar

    private const DEVICE_TRUST_HOURS = 24;    // Recordar dispositivo por 24 horas

    private const MAX_2FA_ATTEMPTS = 5;       // Máximo intentos fallidos

    private const BLOCK_DURATION_MINUTES = 15; // Bloquear por 15 minutos

    private const BACKUP_CODES_COUNT = 10;    // Cantidad de códigos de respaldo

    

    public function __construct($pdo) {

        $this->pdo = $pdo;

        // Usar una clave derivada del environment o APP_KEY

        $this->encryptionKey = getenv('APP_KEY') ?: 'default-insecure-key-change-in-production';

    }



    /**

     * Generar un nuevo secret TOTP para el usuario

     * Retorna array con secret (base32) y QR provisorio

     */

    public function generateNewSecret($email) {

        try {

            // Generar secret aleatorio de 20 bytes (160 bits)

            $secretBytes = random_bytes(20);

            $secret = $this->base32Encode($secretBytes);



            // Crear instancia TOTP usando createFromSecret

            $totp = \OTPHP\TOTP::createFromSecret($secret);

            

            if (!$totp || !is_object($totp)) {

                throw new Exception("No se pudo crear instancia TOTP: " . gettype($totp));

            }

            

            // Configurar metadatos

            if (method_exists($totp, 'setLabel')) {

                $totp->setLabel('Amiri App (' . $email . ')');

            }

            if (method_exists($totp, 'setIssuer')) {

                $totp->setIssuer('Amiri App');

            }



            return [

                'secret' => $totp->getSecret(),

                'provisioning_uri' => $totp->getProvisioningUri()

            ];

        } catch (Exception $e) {

            throw new Exception("Error generando secret TOTP: " . $e->getMessage());

        }

    }



    /**

     * Codificar bytes a base32

     * RFC 4648 implementation

     */

    private function base32Encode($data) {

        $base32chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

        $datalen = strlen($data);

        $result = "";

        $bitstring = "";



        for ($i = 0; $i < $datalen; $i++) {

            $bitstring .= str_pad(base_convert(ord($data[$i]), 10, 2), 8, '0', STR_PAD_LEFT);

        }



        $bitstring = str_pad($bitstring, ceil(strlen($bitstring) / 5) * 5, '0', STR_PAD_RIGHT);



        for ($i = 0; $i < strlen($bitstring); $i += 5) {

            $result .= $base32chars[base_convert(substr($bitstring, $i, 5), 2, 10)];

        }



        return $result;

    }



    /**

     * Encriptar secret antes de guardar en BD

     */

    private function encryptSecret($secret) {

        $iv = openssl_random_pseudo_bytes(16);

        $encrypted = openssl_encrypt(

            $secret,

            'AES-256-CBC',

            hash('sha256', $this->encryptionKey, true),

            0,

            $iv

        );

        return base64_encode($iv . $encrypted);

    }



    /**

     * Desencriptar secret desde BD

     */

    private function decryptSecret($encrypted) {

        $data = base64_decode($encrypted);

        $iv = substr($data, 0, 16);

        $encrypted_text = substr($data, 16);

        

        return openssl_decrypt(

            $encrypted_text,

            'AES-256-CBC',

            hash('sha256', $this->encryptionKey, true),

            0,

            $iv

        );

    }



    /**

     * Guardar configuración 2FA en usuario

     */

    public function enableTwoFactor($usuarioId, $secret, $backupCodes = null) {

        try {

            // Si no proporciona códigos, generar automaticamente

            if (!$backupCodes) {

                $backupCodes = $this->generateBackupCodes();

            }



            $encryptedSecret = $this->encryptSecret($secret);



            $stmt = $this->pdo->prepare("

                UPDATE usuarios 

                SET 

                    two_fa_enabled = 1,

                    two_fa_secret = ?,

                    two_fa_backup_codes = ?,

                    two_fa_created_at = NOW()

                WHERE id = ?

            ");



            $stmt->execute([

                $encryptedSecret,

                json_encode($backupCodes),

                $usuarioId

            ]);



            return [

                'success' => true,

                'backup_codes' => $backupCodes,

                'message' => '2FA habilitado correctamente'

            ];

        } catch (Exception $e) {

            throw new Exception("Error habilitando 2FA: " . $e->getMessage());

        }

    }



    /**

     * Deshabilitar 2FA del usuario

     */

    public function disableTwoFactor($usuarioId) {

        try {

            // Eliminar tokens de dispositivos de confianza también

            $stmt = $this->pdo->prepare("DELETE FROM trusted_devices WHERE usuario_id = ?");

            $stmt->execute([$usuarioId]);



            // Deshabilitar 2FA

            $stmt = $this->pdo->prepare("

                UPDATE usuarios 

                SET 

                    two_fa_enabled = 0,

                    two_fa_secret = NULL,

                    two_fa_backup_codes = NULL,

                    two_fa_created_at = NULL

                WHERE id = ?

            ");



            return $stmt->execute([$usuarioId]);

        } catch (Exception $e) {

            throw new Exception("Error deshabilitando 2FA: " . $e->getMessage());

        }

    }



    /**

     * Validar código TOTP (6 dígitos)

     * Permite ±1 ventana de tiempo para sincronización de reloj

     */

    public function verifyCode($usuarioId, $code) {

        try {

            // Obtener usuario con secret

            $stmt = $this->pdo->prepare("

                SELECT two_fa_secret, two_fa_enabled 

                FROM usuarios 

                WHERE id = ? AND two_fa_enabled = 1

            ");

            $stmt->execute([$usuarioId]);

            $user = $stmt->fetch();



            if (!$user) {

                return ['valid' => false, 'message' => '2FA no está habilitado'];

            }



            // Desencriptar secret

            $secret = $this->decryptSecret($user['two_fa_secret']);



            // Crear instancia TOTP con el secret guardado

            $totp = TOTP::create($secret);



            // Verificar código actual y ventanas adyacentes (±1)

            $isValid = $totp->verify($code, null, 1);



            if ($isValid) {

                // Limpiar intentos fallidos

                $this->clearFailedAttempts($usuarioId, $_SERVER['REMOTE_ADDR'] ?? '');

                return ['valid' => true];

            }



            // Verificar si es un código de respaldo

            $backupResult = $this->verifyBackupCode($usuarioId, $code);

            if ($backupResult['valid']) {

                $this->clearFailedAttempts($usuarioId, $_SERVER['REMOTE_ADDR'] ?? '');

                return $backupResult;

            }



            // Registrar intento fallido

            $this->recordFailedAttempt($usuarioId, $_SERVER['REMOTE_ADDR'] ?? '');



            return [

                'valid' => false,

                'message' => 'Código TOTP o respaldo inválido'

            ];

        } catch (Exception $e) {

            return [

                'valid' => false,

                'message' => 'Error validando código: ' . $e->getMessage()

            ];

        }

    }



    /**

     * Generar códigos de respaldo (backup codes)

     * 10 códigos de 8 caracteres alfanuméricos

     */

    public function generateBackupCodes($count = self::BACKUP_CODES_COUNT) {

        $codes = [];

        for ($i = 0; $i < $count; $i++) {

            // Generar código de 8 caracteres

            $code = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));

            $codes[] = [

                'code' => $code,

                'used' => false,

                'used_at' => null

            ];

        }

        return $codes;

    }



    /**

     * Validar código de respaldo

     */

    private function verifyBackupCode($usuarioId, $code) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT two_fa_backup_codes 

                FROM usuarios 

                WHERE id = ?

            ");

            $stmt->execute([$usuarioId]);

            $user = $stmt->fetch();



            if (!$user) {

                return ['valid' => false];

            }



            $codes = json_decode($user['two_fa_backup_codes'], true);

            if (!is_array($codes)) {

                return ['valid' => false];

            }



            // Buscar el código

            $codeUpper = strtoupper(str_replace(['-', ' '], '', $code));

            foreach ($codes as &$backupCode) {

                if ($backupCode['code'] === $codeUpper && !$backupCode['used']) {

                    // Marcar como usado

                    $backupCode['used'] = true;

                    $backupCode['used_at'] = date('Y-m-d H:i:s');



                    // Guardar cambios

                    $stmt = $this->pdo->prepare("

                        UPDATE usuarios 

                        SET two_fa_backup_codes = ? 

                        WHERE id = ?

                    ");

                    $stmt->execute([json_encode($codes), $usuarioId]);



                    return [

                        'valid' => true,

                        'message' => 'Código de respaldo validado',

                        'is_backup' => true

                    ];

                }

            }



            return ['valid' => false];

        } catch (Exception $e) {

            return ['valid' => false, 'message' => $e->getMessage()];

        }

    }



    /**

     * Crear token de dispositivo de confianza (24 horas)

     */

    public function createTrustedDevice($usuarioId, $deviceName = null) {

        try {

            // Generar token único

            $deviceToken = bin2hex(random_bytes(32));



            // Obtener info del dispositivo

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';



            if (!$deviceName) {

                $deviceName = $this->parseDeviceName($userAgent);

            }



            // Calcular expiración (24 horas desde ahora)

            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 3600));



            $stmt = $this->pdo->prepare("

                INSERT INTO trusted_devices 

                (usuario_id, device_token, device_name, ip_address, user_agent, expires_at)

                VALUES (?, ?, ?, ?, ?, ?)

            ");



            $stmt->execute([

                $usuarioId,

                $deviceToken,

                $deviceName,

                $ip,

                $userAgent,

                $expiresAt

            ]);



            return [

                'success' => true,

                'device_token' => $deviceToken,

                'expires_at' => $expiresAt

            ];

        } catch (Exception $e) {

            throw new Exception("Error creando dispositivo de confianza: " . $e->getMessage());

        }

    }



    /**

     * Validar si un dispositivo está dentro del período de confianza

     */

    public function validateTrustedDevice($usuarioId, $deviceToken) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT id, expires_at 

                FROM trusted_devices 

                WHERE usuario_id = ? 

                AND device_token = ? 

                AND expires_at > NOW()

            ");



            $stmt->execute([$usuarioId, $deviceToken]);

            $device = $stmt->fetch();



            if ($device) {

                // Actualizar last_used_at

                $updateStmt = $this->pdo->prepare("

                    UPDATE trusted_devices 

                    SET last_used_at = NOW() 

                    WHERE id = ?

                ");

                $updateStmt->execute([$device['id']]);



                return true;

            }



            return false;

        } catch (Exception $e) {

            return false;

        }

    }



    /**

     * Eliminar dispositivo de confianza

     */

    public function revokeTrustedDevice($usuarioId, $deviceId) {

        try {

            $stmt = $this->pdo->prepare("

                DELETE FROM trusted_devices 

                WHERE id = ? AND usuario_id = ?

            ");



            return $stmt->execute([$deviceId, $usuarioId]);

        } catch (Exception $e) {

            throw new Exception("Error revocando dispositivo: " . $e->getMessage());

        }

    }



    /**

     * Obtener dispositivos de confianza activos del usuario

     */

    public function getTrustedDevices($usuarioId) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT id, device_name, ip_address, trusted_at, expires_at, last_used_at

                FROM trusted_devices 

                WHERE usuario_id = ? AND expires_at > NOW()

                ORDER BY last_used_at DESC

            ");



            $stmt->execute([$usuarioId]);

            return $stmt->fetchAll();

        } catch (Exception $e) {

            return [];

        }

    }



    /**

     * Limpiar dispositivos expirados

     */

    public function cleanupExpiredDevices() {

        try {

            $stmt = $this->pdo->prepare("

                DELETE FROM trusted_devices 

                WHERE expires_at <= NOW()

            ");



            return $stmt->execute();

        } catch (Exception $e) {

            return false;

        }

    }



    /**

     * Registrar intento fallido de 2FA

     */

    private function recordFailedAttempt($usuarioId, $ipAddress) {

        try {

            $stmt = $this->pdo->prepare("

                INSERT INTO two_fa_attempts 

                (usuario_id, ip_address, failed_attempts, first_attempt_at, last_attempt_at)

                VALUES (?, ?, 1, NOW(), NOW())

                ON DUPLICATE KEY UPDATE

                    failed_attempts = failed_attempts + 1,

                    last_attempt_at = NOW(),

                    blocked_until = CASE 

                        WHEN failed_attempts >= ? THEN DATE_ADD(NOW(), INTERVAL ? MINUTE)

                        ELSE blocked_until

                    END

            ");



            $stmt->execute([

                $usuarioId,

                $ipAddress,

                self::MAX_2FA_ATTEMPTS,

                self::BLOCK_DURATION_MINUTES

            ]);



            return true;

        } catch (Exception $e) {

            error_log("Error registrando intento fallido: " . $e->getMessage());

            return false;

        }

    }



    /**

     * Limpiar intentos fallidos después de éxito

     */

    private function clearFailedAttempts($usuarioId, $ipAddress) {

        try {

            $stmt = $this->pdo->prepare("

                DELETE FROM two_fa_attempts 

                WHERE usuario_id = ? AND ip_address = ?

            ");



            return $stmt->execute([$usuarioId, $ipAddress]);

        } catch (Exception $e) {

            return false;

        }

    }



    /**

     * Verificar si el usuario está bloqueado por intentos fallidos

     */

    public function isBlocked($usuarioId, $ipAddress) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT blocked_until, failed_attempts

                FROM two_fa_attempts 

                WHERE usuario_id = ? AND ip_address = ?

                AND blocked_until > NOW()

            ");



            $stmt->execute([$usuarioId, $ipAddress]);

            $result = $stmt->fetch();



            if ($result) {

                $secondsLeft = strtotime($result['blocked_until']) - time();

                return [

                    'blocked' => true,

                    'seconds_left' => max(0, $secondsLeft),

                    'attempts' => $result['failed_attempts']

                ];

            }



            return ['blocked' => false];

        } catch (Exception $e) {

            return ['blocked' => false];

        }

    }



    /**

     * Parsear nombre del dispositivo desde User Agent

     */

    private function parseDeviceName($userAgent) {

        // Detectar navegador y SO básicamente

        if (strpos($userAgent, 'Chrome') !== false) {

            $browser = 'Chrome';

        } elseif (strpos($userAgent, 'Firefox') !== false) {

            $browser = 'Firefox';

        } elseif (strpos($userAgent, 'Safari') !== false) {

            $browser = 'Safari';

        } elseif (strpos($userAgent, 'Edge') !== false) {

            $browser = 'Edge';

        } else {

            $browser = 'Unknown';

        }



        if (strpos($userAgent, 'Windows') !== false) {

            $os = 'Windows';

        } elseif (strpos($userAgent, 'Macintosh') !== false) {

            $os = 'macOS';

        } elseif (strpos($userAgent, 'Linux') !== false) {

            $os = 'Linux';

        } elseif (strpos($userAgent, 'iPhone') !== false) {

            $os = 'iOS';

        } elseif (strpos($userAgent, 'Android') !== false) {

            $os = 'Android';

        } else {

            $os = 'Unknown';

        }



        return "{$browser} on {$os}";

    }



    /**

     * Obtener estado de 2FA del usuario

     */

    public function getUserTwoFAStatus($usuarioId) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT 

                    two_fa_enabled,

                    two_fa_created_at,

                    (SELECT COUNT(*) FROM trusted_devices WHERE usuario_id = ? AND expires_at > NOW()) as active_devices,

                    (SELECT COUNT(*) FROM two_fa_backup_codes WHERE JSON_EXTRACT(two_fa_backup_codes, '$.*.used') = false) as backup_codes_available

                FROM usuarios 

                WHERE id = ?

            ");



            $stmt->execute([$usuarioId, $usuarioId]);

            return $stmt->fetch();

        } catch (Exception $e) {

            return null;

        }

    }



    /**

     * Limpiar intentos fallidos viejos (> 24 horas)

     */

    public function cleanupOldAttempts() {

        try {

            $stmt = $this->pdo->prepare("

                DELETE FROM two_fa_attempts 

                WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)

            ");



            return $stmt->execute();

        } catch (Exception $e) {

            return false;

        }

    }

}

