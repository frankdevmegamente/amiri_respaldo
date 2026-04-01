<?php

/**

 * Logger - Sistema de logging estructurado

 * Registra eventos con diferentes niveles de severidad

 * Rotación automática de logs

 */



class Logger {

    

    // Niveles de log

    const ERROR = 'ERROR';

    const WARNING = 'WARNING';

    const INFO = 'INFO';

    const DEBUG = 'DEBUG';

    

    // Directorio de logs

    private static $logDir = __DIR__ . '/../../logs';

    

    // Tamaño máximo de archivo antes de rotar (5MB)

    private static $maxFileSize = 5242880;

    

    // Número máximo de archivos rotados a mantener

    private static $maxFiles = 5;

    

    /**

     * Log de error (errores críticos que requieren atención inmediata)

     */

    public static function error($message, $context = []) {

        self::log(self::ERROR, $message, $context, 'error.log');

    }

    

    /**

     * Log de warning (situaciones anormales pero no críticas)

     */

    public static function warning($message, $context = []) {

        self::log(self::WARNING, $message, $context, 'warning.log');

    }

    

    /**

     * Log de info (eventos informativos importantes)

     */

    public static function info($message, $context = []) {

        self::log(self::INFO, $message, $context, 'info.log');

    }

    

    /**

     * Log de debug (información detallada para debugging)

     */

    public static function debug($message, $context = []) {

        // Solo loggear en desarrollo

        if (self::isDebugMode()) {

            self::log(self::DEBUG, $message, $context, 'debug.log');

        }

    }

    

    /**

     * Log de auditoría (para rastreo de acciones de usuario)

     */

    public static function audit($action, $user_id, $details = []) {

        $context = [

            'user_id' => $user_id,

            'action' => $action,

            'details' => $details,

            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',

            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'

        ];

        

        self::log(self::INFO, "AUDIT: $action", $context, 'audit.log');

    }

    

    /**

     * Log de queries lentas (para optimización)

     */

    public static function slowQuery($query, $duration, $params = []) {

        $context = [

            'query' => $query,

            'duration' => $duration,

            'params' => $params

        ];

        

        self::log(self::WARNING, "Slow query detected: {$duration}s", $context, 'slow_queries.log');

    }

    

    /**

     * Función principal de logging

     */

    private static function log($level, $message, $context, $filename) {

        // Crear directorio si no existe

        if (!is_dir(self::$logDir)) {

            mkdir(self::$logDir, 0755, true);

        }

        

        $filepath = self::$logDir . '/' . $filename;

        

        // Verificar rotación de logs

        self::rotateIfNeeded($filepath);

        

        // Construir línea de log

        $timestamp = date('Y-m-d H:i:s');

        $contextJson = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';

        $logLine = "[$timestamp] [$level] $message$contextJson" . PHP_EOL;

        

        // Escribir al archivo

        file_put_contents($filepath, $logLine, FILE_APPEND | LOCK_EX);

    }

    

    /**

     * Rotar logs si exceden el tamaño máximo

     */

    private static function rotateIfNeeded($filepath) {

        if (!file_exists($filepath)) {

            return;

        }

        

        if (filesize($filepath) < self::$maxFileSize) {

            return;

        }

        

        // Mover archivos rotados

        $pathinfo = pathinfo($filepath);

        $dir = $pathinfo['dirname'];

        $filename = $pathinfo['filename'];

        $ext = $pathinfo['extension'] ?? '';

        

        // Eliminar archivo más antiguo si existe

        $oldestFile = "$dir/$filename." . self::$maxFiles . ".$ext";

        if (file_exists($oldestFile)) {

            unlink($oldestFile);

        }

        

        // Mover archivos rotados

        for ($i = self::$maxFiles - 1; $i >= 1; $i--) {

            $oldFile = "$dir/$filename.$i.$ext";

            $newFile = "$dir/$filename." . ($i + 1) . ".$ext";

            if (file_exists($oldFile)) {

                rename($oldFile, $newFile);

            }

        }

        

        // Rotar archivo actual

        $rotatedFile = "$dir/$filename.1.$ext";

        rename($filepath, $rotatedFile);

    }

    

    /**

     * Verificar si está en modo debug

     */

    private static function isDebugMode() {

        // Verificar si existe variable de entorno

        if (getenv('APP_DEBUG') === 'true') {

            return true;

        }

        

        // Verificar si está en localhost

        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);

        

        return $isLocalhost;

    }

    

    /**

     * Limpiar logs antiguos (llamar desde cron o manualmente)

     */

    public static function cleanup($daysToKeep = 30) {

        if (!is_dir(self::$logDir)) {

            return;

        }

        

        $cutoffTime = time() - ($daysToKeep * 86400);

        $files = glob(self::$logDir . '/*.log*');

        

        foreach ($files as $file) {

            if (filemtime($file) < $cutoffTime) {

                unlink($file);

            }

        }

    }

    

    /**

     * Obtener últimas líneas de un log

     */

    public static function tail($filename = 'error.log', $lines = 100) {

        $filepath = self::$logDir . '/' . $filename;

        

        if (!file_exists($filepath)) {

            return [];

        }

        

        $file = new SplFileObject($filepath);

        $file->seek(PHP_INT_MAX);

        $totalLines = $file->key();

        

        $startLine = max(0, $totalLines - $lines);

        $result = [];

        

        $file->seek($startLine);

        while (!$file->eof()) {

            $line = trim($file->current());

            if ($line !== '') {

                $result[] = $line;

            }

            $file->next();

        }

        

        return $result;

    }

    

    /**

     * Obtener estadísticas de logs

     */

    public static function getStats() {

        if (!is_dir(self::$logDir)) {

            return [];

        }

        

        $stats = [];

        $files = glob(self::$logDir . '/*.log');

        

        foreach ($files as $file) {

            $filename = basename($file);

            $stats[$filename] = [

                'size' => filesize($file),

                'size_mb' => round(filesize($file) / 1048576, 2),

                'modified' => date('Y-m-d H:i:s', filemtime($file)),

                'lines' => self::countLines($file)

            ];

        }

        

        return $stats;

    }

    

    /**

     * Contar líneas en un archivo

     */

    private static function countLines($filepath) {

        $lineCount = 0;

        $file = fopen($filepath, 'r');

        

        while (!feof($file)) {

            fgets($file);

            $lineCount++;

        }

        

        fclose($file);

        return $lineCount;

    }

    

    /**

     * Log de excepción completa con stack trace

     */

    public static function exception(Exception $e, $context = []) {

        $context['exception'] = [

            'message' => $e->getMessage(),

            'code' => $e->getCode(),

            'file' => $e->getFile(),

            'line' => $e->getLine(),

            'trace' => $e->getTraceAsString()

        ];

        

        self::error("Exception: " . $e->getMessage(), $context);

    }

}

