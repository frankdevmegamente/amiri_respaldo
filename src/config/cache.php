<?php
/**
 * Sistema de caché simple en sesión
 * Para datos que no cambian frecuentemente como servicios, tipos de gasto, etc.
 */

class SimpleCache {
    
    /**
     * Tiempo de vida por defecto del caché (1 hora)
     */
    private static $defaultTTL = 3600;
    
    /**
     * Obtiene un valor del caché
     * 
     * @param string $key Clave del caché
     * @return mixed|null Valor del caché o null si no existe o expiró
     */
    public static function get($key) {
        if (!isset($_SESSION['cache'][$key])) {
            return null;
        }
        
        $cached = $_SESSION['cache'][$key];
        
        // Verificar expiración
        if (time() > $cached['expires']) {
            unset($_SESSION['cache'][$key]);
            return null;
        }
        
        return $cached['data'];
    }
    
    /**
     * Almacena un valor en el caché
     * 
     * @param string $key Clave del caché
     * @param mixed $data Datos a cachear
     * @param int|null $ttl Tiempo de vida en segundos (null = usar default)
     */
    public static function set($key, $data, $ttl = null) {
        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = [];
        }
        
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $_SESSION['cache'][$key] = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created_at' => time()
        ];
    }
    
    /**
     * Invalida (elimina) una entrada específica del caché
     * 
     * @param string $key Clave del caché a eliminar
     */
    public static function invalidate($key) {
        if (isset($_SESSION['cache'][$key])) {
            unset($_SESSION['cache'][$key]);
        }
    }
    
    /**
     * Invalida múltiples claves que coincidan con un patrón
     * 
     * @param string $pattern Patrón a buscar (ej: 'servicios*')
     */
    public static function invalidatePattern($pattern) {
        if (!isset($_SESSION['cache'])) {
            return;
        }
        
        $pattern = str_replace('*', '.*', $pattern);
        
        foreach (array_keys($_SESSION['cache']) as $key) {
            if (preg_match('/^' . $pattern . '$/', $key)) {
                unset($_SESSION['cache'][$key]);
            }
        }
    }
    
    /**
     * Limpia todo el caché
     */
    public static function invalidateAll() {
        if (isset($_SESSION['cache'])) {
            unset($_SESSION['cache']);
        }
    }
    
    /**
     * Obtiene o calcula un valor (cache-aside pattern)
     * 
     * @param string $key Clave del caché
     * @param callable $callback Función que retorna el valor si no está en caché
     * @param int|null $ttl Tiempo de vida en segundos
     * @return mixed Valor del caché o calculado
     * 
     * Ejemplo:
     * $servicios = SimpleCache::remember('servicios_activos', function() use ($pdo) {
     *     $stmt = $pdo->query("SELECT * FROM servicios WHERE activo = 1");
     *     return $stmt->fetchAll(PDO::FETCH_ASSOC);
     * }, 3600);
     */
    public static function remember($key, callable $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        // No está en caché, calcular
        $value = $callback();
        
        // Guardar en caché
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Verifica si una clave existe en el caché y no ha expirado
     * 
     * @param string $key Clave a verificar
     * @return bool True si existe y no ha expirado
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Obtiene información sobre el caché (para debugging)
     * 
     * @return array Información del caché
     */
    public static function getInfo() {
        if (!isset($_SESSION['cache'])) {
            return [
                'total_keys' => 0,
                'total_size' => 0,
                'keys' => []
            ];
        }
        
        $info = [
            'total_keys' => count($_SESSION['cache']),
            'total_size' => strlen(serialize($_SESSION['cache'])),
            'keys' => []
        ];
        
        foreach ($_SESSION['cache'] as $key => $data) {
            $ttl = $data['expires'] - time();
            $age = time() - $data['created_at'];
            
            $info['keys'][$key] = [
                'expires_in' => $ttl > 0 ? $ttl : 0,
                'age' => $age,
                'size' => strlen(serialize($data['data']))
            ];
        }
        
        return $info;
    }
    
    /**
     * Limpia entradas expiradas del caché
     */
    public static function cleanup() {
        if (!isset($_SESSION['cache'])) {
            return;
        }
        
        $currentTime = time();
        
        foreach ($_SESSION['cache'] as $key => $data) {
            if ($currentTime > $data['expires']) {
                unset($_SESSION['cache'][$key]);
            }
        }
    }
}
