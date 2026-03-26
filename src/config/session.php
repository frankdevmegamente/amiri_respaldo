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



// Tiempo de vida de la sesión (1 hora = 3600 segundos)
// Tiempo de vida de la sesión (2 hora = 7200 segundos)

ini_set('session.gc_maxlifetime', 7200);

ini_set('session.cookie_lifetime', 7200);



// Nombre de la sesión personalizado (más difícil de identificar el framework)

session_name('AMIRI_SESSION');



// Iniciar sesión si no está iniciada

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}



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

    $timeout = 7200; // 2 horas en segundos

    

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {

        // Sesión expirada por inactividad

        session_unset();

        session_destroy();

        return false;

    }

    

    $_SESSION['LAST_ACTIVITY'] = time();

    return true;

}

