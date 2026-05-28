<?php

/**

 * Configuración segura de sesiones

 * Usar este archivo ANTES de cualquier session_start()

 */



// Configuración de seguridad de cookies de sesión

ini_set('session.cookie_httponly', 1);      // No accesible desde JavaScript

ini_set('session.cookie_samesite', 'Strict'); // Protección CSRF

ini_set('session.use_only_cookies', 1);      // Solo usar cookies, no URL

ini_set('session.use_strict_mode', 1);       // Rechazar IDs de sesión no inicializados



// En producción, descomentar para forzar HTTPS:

// ini_set('session.cookie_secure', 1);      // Solo transmitir por HTTPS



// Tiempo de vida de la sesión (3 horas 5 min = 11100 segundos)
// 3h para el modal de inactividad + 5 min de buffer para que el usuario responda

ini_set('session.gc_maxlifetime', 11100);

ini_set('session.cookie_lifetime', 11100);



// Nombre de la sesión personalizado (más difícil de identificar el framework)

session_name('AMIRI_SESSION');



// Iniciar sesión si no está iniciada

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

// Verificar expiración por inactividad en cada petición
checkSessionTimeout();



/**

 * Regenerar ID de sesión en eventos críticos

 * Llamar esta función después de login exitoso

 */

function regenerateSessionId() {

    if (session_status() === PHP_SESSION_ACTIVE) {

        session_regenerate_id(true);

    }

}



/**

 * Verificar si la sesión ha expirado por inactividad

 */

function checkSessionTimeout() {

    $timeout = 11100; // 3 horas 5 min en segundos (3h para modal + 5min buffer)

    

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {

        // Sesión expirada por inactividad

        session_unset();

        session_destroy();

        return false;

    }

    

    $_SESSION['LAST_ACTIVITY'] = time();

    return true;

}

