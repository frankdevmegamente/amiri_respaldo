<?php
/**
 * Middleware para APIs
 * Centraliza autenticación, autorización y respuestas JSON
 */

require_once __DIR__ . '/session.php';

/**
 * Requiere que el usuario esté autenticado con uno de los roles especificados
 * 
 * @param array $rolesPermitidos Roles que tienen acceso (ej: ['admin', 'usuario'])
 * @return bool true si tiene acceso
 */
function requireAuth($rolesPermitidos = ['admin']) {
    // Verificar timeout de sesión
    if (!checkSessionTimeout()) {
        jsonError('Sesión expirada', 401);
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        jsonError('No autenticado', 401);
    }
    
    if (!in_array($_SESSION['usuario_rol'], $rolesPermitidos)) {
        jsonError('Acceso denegado', 403);
    }
    
    return true;
}

/**
 * Requiere rol de administrador
 * 
 * @return bool true si es admin
 */
function requireAdmin() {
    return requireAuth(['admin']);
}

/**
 * Envía una respuesta JSON exitosa
 * 
 * @param mixed $data Datos a enviar
 * @param int $code Código HTTP (default: 200)
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Envía una respuesta JSON de error
 * 
 * @param string $message Mensaje de error
 * @param int $code Código HTTP (default: 400)
 * @param mixed $details Detalles adicionales opcionales
 */
function jsonError($message, $code = 400, $details = null) {
    $response = ['error' => $message];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    jsonResponse($response, $code);
}

/**
 * Obtiene y decodifica el input JSON del request
 * 
 * @return array Datos decodificados
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonError('JSON inválido: ' . json_last_error_msg(), 400);
    }
    
    return $data ?? [];
}

/**
 * Obtiene datos del request (JSON o POST)
 * 
 * @return array Datos del request
 */
function getRequestData() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        return getJsonInput();
    }
    
    return $_POST;
}

/**
 * Valida que existan los campos requeridos en los datos
 * 
 * @param array $data Datos a validar
 * @param array $campos Campos requeridos
 * @throws Exception Si falta algún campo
 */
function validateRequired($data, $campos) {
    $faltantes = [];
    
    foreach ($campos as $campo) {
        if (!isset($data[$campo]) || trim($data[$campo]) === '') {
            $faltantes[] = $campo;
        }
    }
    
    if (!empty($faltantes)) {
        jsonError('Campos requeridos faltantes: ' . implode(', ', $faltantes), 400);
    }
}

/**
 * Sanitiza un string para prevenir XSS
 * 
 * @param string $string String a sanitizar
 * @return string String sanitizado
 */
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Inicia el output buffering y configura headers para API
 */
function initApi() {
    // Habilitar reporte de errores en desarrollo
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
    
    // Headers CORS si es necesario (ajustar según necesidad)
    // header('Access-Control-Allow-Origin: *');
    // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    // header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    header('Content-Type: application/json; charset=utf-8');
}
