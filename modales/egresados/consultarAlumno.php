<?php
include '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');

// Si el número de control no está presente o está vacío
if (!isset($_POST['nC']) || empty(trim($_POST['nC']))) {
    echo json_encode(['exito' => false, 'mensaje' => 'Número de control no proporcionado o inválido.']);
    exit;
}
$nC = $_POST['nC']; // Asigna el número de control recibido

// Prepara la consulta
$query = "SELECT e.ft, e.nC, CONCAT(e.pr, ' ') AS Promedio, CONCAT(e.nom, ' ', e.aP, ' ', e.aM) AS Alumno FROM egresados e WHERE e.nC = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error en la preparación de la consulta.']);
    exit;
}
$stmt->bind_param("s", $nC);
$stmt->execute();
$result = $stmt->get_result();

// Verifica si se encontró el registro
if ($row = $result->fetch_assoc()) {
    echo json_encode(['exito' => true, 'egresados' => $row]);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró el alumno.']);
}

// Cierra la conexión y libera recursos
$stmt->close();
$conn->close();
