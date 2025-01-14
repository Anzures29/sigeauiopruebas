<?php
include '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');
// Si el folio no está presente o está vacío
if (!isset($_POST['fol']) || empty(trim($_POST['fol']))) {
    echo json_encode(['exito' => false, 'mensaje' => 'Folio no proporcionado o inválido.']);
    exit;
}
$folio = $_POST['fol']; // Asigna el folio recibido
// Prepara la consulta
$query =    "SELECT inscripciones.fol, inscripciones.ft, inscripciones.fe, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno, 
                niveles.ni, COALESCE(carreras.ca, '') AS carrera, inscripciones.feIni, inscripciones.feFin, inscripciones.fePa, inscripciones.nC, inscripciones.pe, 
                inscripciones.cN, inscripciones.coIns, inscripciones.coColOrig, inscripciones.coRei
            FROM inscripciones
            INNER JOIN alumnos ON inscripciones.nC = alumnos.nC
            INNER JOIN niveles ON inscripciones.cN = niveles.cv
            LEFT JOIN carreras ON inscripciones.cC = carreras.cv
            WHERE inscripciones.fol = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error en la preparación de la consulta.']);
    exit;
}
$stmt->bind_param("s", $folio);
$stmt->execute();
$result = $stmt->get_result();
// Verifica si se encontró el registro
if ($row = $result->fetch_assoc()) {
    echo json_encode(['exito' => true, 'inscripcion' => $row]);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró la inscripción.']);
}
// Cierra la conexión y libera recursos
$stmt->close();
$conn->close();
