<?php
// Datos de conexión
$servername = "localhost";
$username = "root";
$password = "";
$database = "sigeauio2";
// Se crea la conexión
$conn = new mysqli($servername, $username, $password, $database);
// Se verifica la conexión
if ($conn->connect_error) {
    error_log("Error de conexión: " . $conn->connect_error); // Registrar el error
    die("Error al conectar a la base de datos.");
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Mexico_City');
