<?php
include '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');
if (!isset($_POST['nC']) || empty(trim($_POST['nC']))) {
    echo json_encode(['exito' => false, 'mensaje' => 'Número de Control no proporcionado o inválido.']);
    exit;
}
$nC = trim($_POST['nC']);
$query = "SELECT 
            alumnos.nC, alumnos.ma, alumnos.aP, alumnos.aM, alumnos.nom, municipios.mu AS lugarN, alumnos.fN, alumnos.ed, alumnos.cu, alumnos.se, 
            alumnos.ts, alumnos.af, munAlumno.mu AS muAlumno, colAlumno.co AS coAlumno, alumnos.cCo AS cvCaA, alumnos.ca AS caAlumno, alumnos.te AS teAlumno, 
            alumnos.em, 
            escuelas.cct, cctescuelas.es, escuelas.ge, escuelas.pr, 
            tutores.cu AS cuTutor, tutores.nom AS nomTutor, tutores.pa, tutores.te AS teTutor, COALESCE(munTutor.mu, '') AS muTutor, COALESCE(colTutor.co, '') AS coTutor,
            tutores.cCo AS cvCaT, tutores.ca AS caTutor
        FROM alumnos
        INNER JOIN municipios ON alumnos.cM = municipios.cv
        INNER JOIN colonias AS colAlumno ON alumnos.cCo = colAlumno.cv
        INNER JOIN municipios AS munAlumno ON colAlumno.cM = munAlumno.cv
        LEFT JOIN escuelas ON alumnos.nC = escuelas.nC
        LEFT JOIN cctescuelas ON escuelas.cct = cctescuelas.cct
        LEFT JOIN tutores ON alumnos.nC = tutores.nC
        LEFT JOIN colonias AS colTutor ON tutores.cCo = colTutor.cv
        LEFT JOIN municipios AS munTutor ON colTutor.cM = munTutor.cv
        WHERE alumnos.nC = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al preparar la consulta.']);
    exit;
}
$stmt->bind_param("s", $nC);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode(['exito' => true, 'alumno' => $row]);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se encontró el alumno.']);
}
$stmt->close();
$conn->close();
