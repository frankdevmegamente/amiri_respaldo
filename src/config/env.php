<?php

// Simple .env loader: reads key=value lines and populates getenv()/$_ENV/$_SERVER

date_default_timezone_set('America/Mexico_City');



function load_env_file($path)

{

    if (!file_exists($path)) {

        return;

    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line === '' || $line[0] === '#') {

            continue;

        }

        if (strpos($line, '=') === false) {

            continue;

        }

        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);

        $value = trim($value);

        $value = trim($value, "\"'\n\r");

        putenv("$name=$value");

        $_ENV[$name] = $value;

        $_SERVER[$name] = $value;

    }

}



$projectRoot = dirname(__DIR__, 2); // src/config -> project root

$envPath = $projectRoot . '/.env';

load_env_file($envPath);

