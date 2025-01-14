<?php
include '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');
// Si el folio no está presente o está vacío
if (!isset($_POST['fo']) || empty(trim($_POST['fo']))) {
    echo json_encode(['exito' => false, 'mensaje' => 'Folio no proporcionado o inválido.']);
    exit;
}
$fo = $_POST['fo']; // Asigna el folio recibido
// Prepara la consulta
$query =    "SELECT pagos.fo, pagos.fe, pagos.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno, tipopago.tipo, pagos.de, 
                pagos.ca, pagos.im, pagos.cT, formapago.forma, inscripciones.cN, inscripciones.cC 
            FROM pagos 
            INNER JOIN alumnos ON pagos.nC = alumnos.nC 
            INNER JOIN tipopago ON pagos.cT = tipopago.cv 
            INNER JOIN formapago ON pagos.cF = formapago.cv 
            INNER JOIN inscripciones ON pagos.nC = inscripciones.nC 
            WHERE pagos.fo LIKE ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error en la preparación de la consulta.']);
    exit;
}
$stmt->bind_param("s", $fo);
$stmt->execute();
$result = $stmt->get_result();
// Verifica si se encontró el registro
if ($row = $result->fetch_assoc()) {
    echo json_encode(['exito' => true, 'pago' => $row]);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró la inscripción.']);
}
// Cierra la conexión y libera recursos
$stmt->close();
$conn->close();
