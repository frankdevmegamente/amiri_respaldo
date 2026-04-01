<?php

require_once __DIR__ . '/src/config/session.php';



// Registrar auditoría antes de destruir la sesión

if (isset($_SESSION['usuario_id'])) {

    require_once __DIR__ . '/src/config/db.php';

    require_once __DIR__ . '/src/config/auditoria.php';

    

    try {

        $pdo = getPDO();

        registrarLogout($pdo);

    } catch (Exception $e) {

        error_log("Error al registrar logout: " . $e->getMessage());

    }

}



session_destroy();

header('Location: login.php');

exit;

