<?php

if (!function_exists('getFechaInicializacion')) {

    function getFechaInicializacion($pdo) {
        $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'fecha_inicializacion' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['valor'] : null;
    }

}

if (!function_exists('setFechaInicializacion')) {

    function setFechaInicializacion($pdo, $fecha) {
        $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES ('fecha_inicializacion', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $stmt->execute([$fecha]);
    }

}
