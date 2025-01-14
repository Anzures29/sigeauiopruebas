<?php
// Detecta si se está trabajando en un entorno local
$is_local = ($_SERVER['SERVER_NAME'] === 'localhost');
// Verifica si la sesión no está ya activa
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de las cookies de sesión
    $cookieParams = [
        'lifetime' => 0, // Caduca cuando se cierra el navegador
        'path' => '/',
        'domain' => $is_local ? '' : 'tudominio.com',
        'secure' => !$is_local,
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    session_set_cookie_params($cookieParams); // Se configuran los parámetros de la cookie
    session_start(); // Se inicia la sesión
}
?>