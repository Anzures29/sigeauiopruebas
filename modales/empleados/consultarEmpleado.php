<?php
include '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');

// Validar si el parámetro 'cv' está presente y no está vacío
if (!isset($_POST['cv']) || empty(trim($_POST['cv']))) {
    echo json_encode(['exito' => false, 'mensaje' => 'Clave proporcionada o inválida.']);
    exit;
}

$cv = trim($_POST['cv']);

$query = "SELECT 
        empleados.cv, empleados.nom, empleados.aP, empleados.aM, empleados.te, empleados.em, 
        empleados.ca, municipios.mu, colonias.co, roles.rol, empleados.su
    FROM empleados
    INNER JOIN colonias ON empleados.cCo = colonias.cv
    INNER JOIN municipios ON colonias.cM = municipios.cv
    INNER JOIN roles ON empleados.cR = roles.cv
    WHERE empleados.cv = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al preparar la consulta.']);
    exit;
}

$stmt->bind_param("s", $cv);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el registro
if ($row = $result->fetch_assoc()) {
    echo json_encode(['exito' => true, 'empleado' => $row]);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró el empleado.']);
}

// Cerrar la conexión y liberar recursos
$stmt->close();
$conn->close();
