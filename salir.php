<?php
include 'conexion.php';
include 'config.php'; // Configuración de seguridad para las cookies
// Validar que el usuario está autenticado antes de proceder
session_start();
if (!isset($_SESSION['clave'])) {
    header("Location: login.php");
    exit();
}
// Registrar la acción en la bitácora
$clave = $_SESSION['clave'];
$accion = "Cerró sesión";
$fecha = date("Y-m-d");
// $fecha = date("2025-03-01");
$hora = date("H:i:s");
try {
    $stmt = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    $stmt->bind_param("ssss", $clave, $accion, $fecha, $hora);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al registrar en la bitácora: " . $e->getMessage());
}
// Destruir la sesión y eliminar cookies de sesión
session_unset();
session_destroy();
// Eliminar las cookies de sesión de forma segura
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '', // Vaciar el valor de la cookie
        time() - 42000, // Expirar la cookie
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
// Redirigir al usuario al login
header("Location: login.php");
exit();
?>