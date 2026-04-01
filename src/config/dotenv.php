<?php

/**

 * Cargador de variables de entorno

 * Parsea archivo .env y las carga en $_ENV y getenv()

 */



class DotEnv {

    private $path;

    

    public function __construct($path) {

        if (!file_exists($path)) {

            throw new Exception("Archivo .env no encontrado en: $path");

        }

        $this->path = $path;

    }

    

    public function load() {

        if (!is_readable($this->path)) {

            throw new Exception("No se puede leer el archivo .env");

        }

        

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        

        foreach ($lines as $line) {

            // Ignorar comentarios

            if (strpos(trim($line), '#') === 0) {

                continue;

            }

            

            // Parsear línea

            if (strpos($line, '=') !== false) {

                list($name, $value) = explode('=', $line, 2);

                

                $name = trim($name);

                $value = trim($value);

                

                // Remover comillas si existen

                $value = trim($value, '"\'');

                

                // Establecer variable de entorno

                if (!array_key_exists($name, $_ENV)) {

                    $_ENV[$name] = $value;

                    putenv("$name=$value");

                }

            }

        }

    }

    

    /**

     * Obtener variable de entorno con valor por defecto

     */

    public static function get($key, $default = null) {

        $value = getenv($key);

        

        if ($value === false) {

            $value = $_ENV[$key] ?? $default;

        }

        

        // Convertir valores booleanos

        if (is_string($value)) {

            $lower = strtolower($value);

            if ($lower === 'true') return true;

            if ($lower === 'false') return false;

            if ($lower === 'null') return null;

        }

        

        return $value;

    }

}



// Cargar .env si existe

$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {

    $dotenv = new DotEnv($envPath);

    $dotenv->load();

}

