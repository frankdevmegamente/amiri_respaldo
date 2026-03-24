<?php
/**
 * Rate Limiter simple basado en archivos
 * Previene brute force attacks limitando intentos por IP/email
 */

class RateLimiter {
    
    /**
     * Máximo de intentos permitidos
     */
    private static $maxAttempts = 5;
    
    /**
     * Tiempo de bloqueo en minutos
     */
    private static $decayMinutes = 15;
    
    /**
     * Directorio para almacenar los límites
     */
    private static $storePath = __DIR__ . '/../../uploads/rate-limits/';
    
    /**
     * Verifica si se han superado los intentos permitidos
     * 
     * @param string $key Clave única (ej: 'login:192.168.1.1:user@example.com')
     * @return bool True si se superó el límite
     */
    public static function tooManyAttempts($key) {
        self::ensureStorageExists();
        
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Si ya expiró, eliminar archivo
        if (time() > $data['expires']) {
            @unlink($file);
            return false;
        }
        
        return $data['attempts'] >= self::$maxAttempts;
    }
    
    /**
     * Incrementa el contador de intentos
     * 
     * @param string $key Clave única
     */
    public static function hit($key) {
        self::ensureStorageExists();
        
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            // Primer intento
            $data = [
                'attempts' => 1,
                'expires' => time() + (self::$decayMinutes * 60),
                'first_attempt' => time()
            ];
        } else {
            $data = json_decode(file_get_contents($file), true);
            
            // Si expiró, reiniciar contador
            if (time() > $data['expires']) {
                $data = [
                    'attempts' => 1,
                    'expires' => time() + (self::$decayMinutes * 60),
                    'first_attempt' => time()
                ];
            } else {
                // Incrementar intentos
                $data['attempts']++;
            }
        }
        
        file_put_contents($file, json_encode($data));
    }
    
    /**
     * Limpia el límite para una clave (después de login exitoso)
     * 
     * @param string $key Clave única
     */
    public static function clear($key) {
        $file = self::getFilePath($key);
        
        if (file_exists($file)) {
            @unlink($file);
        }
    }
    
    /**
     * Obtiene el tiempo restante de bloqueo en segundos
     * 
     * @param string $key Clave única
     * @return int Segundos restantes, 0 si no está bloqueado
     */
    public static function availableIn($key) {
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        $remaining = $data['expires'] - time();
        
        return max(0, $remaining);
    }
    
    /**
     * Obtiene el número de intentos realizados
     * 
     * @param string $key Clave única
     * @return int Número de intentos
     */
    public static function attempts($key) {
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Si expiró, retornar 0
        if (time() > $data['expires']) {
            @unlink($file);
            return 0;
        }
        
        return $data['attempts'];
    }
    
    /**
     * Obtiene el número de intentos restantes
     * 
     * @param string $key Clave única
     * @return int Intentos restantes
     */
    public static function retriesLeft($key) {
        return max(0, self::$maxAttempts - self::attempts($key));
    }
    
    /**
     * Limpia todos los archivos expirados (mantenimiento)
     */
    public static function cleanup() {
        self::ensureStorageExists();
        
        $files = glob(self::$storePath . '*.json');
        $currentTime = time();
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if ($data && $currentTime > $data['expires']) {
                @unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtiene la ruta del archivo para una clave
     * 
     * @param string $key Clave
     * @return string Ruta completa del archivo
     */
    private static function getFilePath($key) {
        return self::$storePath . md5($key) . '.json';
    }
    
    /**
     * Asegura que el directorio de almacenamiento existe
     */
    private static function ensureStorageExists() {
        if (!file_exists(self::$storePath)) {
            mkdir(self::$storePath, 0755, true);
            
            // Crear .htaccess para proteger el directorio
            $htaccess = self::$storePath . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Require all denied\n");
            }
        }
    }
    
    /**
     * Configura límites personalizados
     * 
     * @param int $maxAttempts Máximo de intentos
     * @param int $decayMinutes Minutos de bloqueo
     */
    public static function configure($maxAttempts, $decayMinutes) {
        self::$maxAttempts = $maxAttempts;
        self::$decayMinutes = $decayMinutes;
    }
}
