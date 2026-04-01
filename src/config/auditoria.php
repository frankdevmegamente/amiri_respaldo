<?php

/**

 * Sistema de Auditoría y Trazabilidad

 * Registra todas las acciones importantes del sistema

 */



/**

 * Registra una acción en la tabla de auditoría

 * 

 * @param PDO $pdo Conexión a la base de datos

 * @param array $datos Datos de la acción a registrar

 * @return bool True si se registró correctamente

 */

function registrarAuditoria($pdo, $datos) {

    try {

        // Validar datos requeridos

        if (!isset($datos['accion']) || !isset($datos['modulo'])) {

            error_log("Auditoría: faltan datos requeridos (accion, modulo)");

            return false;

        }



        // Obtener usuario_id de la sesión

        $usuario_id = $_SESSION['usuario_id'] ?? null;

        

        if (!$usuario_id) {

            error_log("Auditoría: no hay usuario_id en sesión");

            return false;

        }



        // Capturar información del contexto

        $ip_address = obtenerIPReal();

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';



        // Preparar datos para JSON (si existen)

        $datos_anteriores = null;

        if (isset($datos['datos_anteriores']) && is_array($datos['datos_anteriores'])) {

            $datos_anteriores = json_encode($datos['datos_anteriores'], JSON_UNESCAPED_UNICODE);

        }



        $datos_nuevos = null;

        if (isset($datos['datos_nuevos']) && is_array($datos['datos_nuevos'])) {

            $datos_nuevos = json_encode($datos['datos_nuevos'], JSON_UNESCAPED_UNICODE);

        }



        // Insertar en la tabla auditoria

        $sql = "INSERT INTO auditoria (

            usuario_id,

            accion,

            modulo,

            tabla,

            registro_id,

            datos_anteriores,

            datos_nuevos,

            descripcion,

            ip_address,

            user_agent

        ) VALUES (

            :usuario_id,

            :accion,

            :modulo,

            :tabla,

            :registro_id,

            :datos_anteriores,

            :datos_nuevos,

            :descripcion,

            :ip_address,

            :user_agent

        )";



        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ':usuario_id' => $usuario_id,

            ':accion' => $datos['accion'],

            ':modulo' => $datos['modulo'],

            ':tabla' => $datos['tabla'] ?? null,

            ':registro_id' => $datos['registro_id'] ?? null,

            ':datos_anteriores' => $datos_anteriores,

            ':datos_nuevos' => $datos_nuevos,

            ':descripcion' => $datos['descripcion'] ?? null,

            ':ip_address' => $ip_address,

            ':user_agent' => $user_agent

        ]);



        return true;



    } catch (PDOException $e) {

        error_log("Error al registrar auditoría: " . $e->getMessage());

        return false;

    }

}



/**

 * Obtiene la IP real del usuario (considerando proxies)

 * 

 * @return string IP del usuario

 */

function obtenerIPReal() {

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

        return $_SERVER['HTTP_CLIENT_IP'];

    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        // Puede contener múltiples IPs, tomar la primera

        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

        return trim($ips[0]);

    } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {

        return $_SERVER['HTTP_X_FORWARDED'];

    } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {

        return $_SERVER['HTTP_FORWARDED_FOR'];

    } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {

        return $_SERVER['HTTP_FORWARDED'];

    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {

        return $_SERVER['REMOTE_ADDR'];

    }

    

    return 'DESCONOCIDA';

}



/**

 * Registra un login exitoso

 * 

 * @param PDO $pdo Conexión a la base de datos

 * @param int $usuario_id ID del usuario

 * @param string $nombre_usuario Nombre del usuario

 * @return bool

 */

function registrarLogin($pdo, $usuario_id, $nombre_usuario) {

    // Guardar temporalmente en variable global para usar en registrarAuditoria

    $_SESSION['usuario_id'] = $usuario_id;

    

    return registrarAuditoria($pdo, [

        'accion' => 'LOGIN',

        'modulo' => 'auth',

        'tabla' => 'usuarios',

        'registro_id' => $usuario_id,

        'descripcion' => "Usuario '$nombre_usuario' inició sesión"

    ]);

}



/**

 * Registra un logout

 * 

 * @param PDO $pdo Conexión a la base de datos

 * @return bool

 */

function registrarLogout($pdo) {

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $nombre_usuario = $_SESSION['nombre'] ?? 'Desconocido';

    

    if (!$usuario_id) {

        return false;

    }

    

    return registrarAuditoria($pdo, [

        'accion' => 'LOGOUT',

        'modulo' => 'auth',

        'tabla' => 'usuarios',

        'registro_id' => $usuario_id,

        'descripcion' => "Usuario '$nombre_usuario' cerró sesión"

    ]);

}



/**

 * Obtiene los datos actuales de un registro antes de modificarlo

 * Útil para el campo 'datos_anteriores'

 * 

 * @param PDO $pdo Conexión a la base de datos

 * @param string $tabla Nombre de la tabla

 * @param int $id ID del registro

 * @return array|null Datos del registro o null si no existe

 */

function obtenerDatosAnteriores($pdo, $tabla, $id) {

    try {

        $sql = "SELECT * FROM $tabla WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {

        error_log("Error al obtener datos anteriores: " . $e->getMessage());

        return null;

    }

}

