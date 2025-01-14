<?php
include '../../conexion.php';
header('Content-Type: application/json');
if (isset($_GET['nivel']) && $_GET['nivel'] === "1") {
    $cN = 1;
    $alumnosQuery = "SELECT inscripciones.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno FROM inscripciones
                    INNER JOIN alumnos WHERE inscripciones.nC = alumnos.nC AND inscripciones.cN = ?";
    if ($stmt = $conn->prepare($alumnosQuery)) {
        $stmt->bind_param("s", $cN);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $alumnosList = [];
        while ($fila = $resultado->fetch_assoc()) {
            $alumnosList[] = [
                'nC' => $fila['nC'],
                'alumno' => $fila['alumno']
            ];
        }
        echo json_encode($alumnosList);
        $stmt->close();
    }
} elseif (isset($_GET['carrera']) && !empty($_GET['carrera'])) {
    $cC = $_GET['carrera'];
    $alumnosQuery = "SELECT inscripciones.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno FROM inscripciones
                    INNER JOIN alumnos WHERE inscripciones.nC = alumnos.nC AND inscripciones.cC = ?";
    if ($stmt = $conn->prepare($alumnosQuery)) {
        $stmt->bind_param("s", $cC);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $alumnosList = [];
        while ($fila = $resultado->fetch_assoc()) {
            $alumnosList[] = [
                'nC' => $fila['nC'],
                'alumno' => $fila['alumno']
            ];
        }
        echo json_encode($alumnosList);
        $stmt->close();
    }
} else {
    echo json_encode(["error" => "Parámetro 'carrera' o 'nivel' no proporcionado o vacío"]);
}
