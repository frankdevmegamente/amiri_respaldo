<?php

// Cargar variables de entorno

require_once __DIR__ . '/dotenv.php';

require_once __DIR__ . '/env.php';

require_once __DIR__ . '/helpers.php';



function getPDO()
{
    static $pdo = null;

    if ($pdo === null) {
        $host = DotEnv::get('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        $db = DotEnv::get('DB_NAME', getenv('DB_NAME') ?: 'guiaspar_amiri_db');
        $user = DotEnv::get('DB_USER', getenv('DB_USER') ?: 'root');
        $pass = DotEnv::get('DB_PASS', getenv('DB_PASS') ?: 'TuContraseñaSegura');
        $charset = DotEnv::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => true,
            ]);
        } catch (PDOException $e) {
            if (DotEnv::get('APP_DEBUG', false)) {
                error_log("Error de conexión DB: " . $e->getMessage());
            }
            throw new Exception('Error de conexión a la base de datos');
        }
    }

    return $pdo;
}

