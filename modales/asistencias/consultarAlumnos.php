<?php
include '../../conexion.php'; // Incluir el archivo de conexión a la base de datos
header('Content-Type: application/json'); // Especificar que el contenido devuelto será en formato JSON
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null; // Verificar si se envió el parámetro 'fecha'
// Verificar si el parámetro 'nivel' está presente y si corresponde al valor "1" (nivel Bachillerato)
if (isset($_GET['nivel']) && $_GET['nivel'] === "1") {
    // Nivel 1 representa Bachillerato
    $cN = 1;
    // Consulta SQL para obtener la lista de alumnos inscritos en el nivel especificado
    // y su estado de asistencia para la fecha proporcionada (si existe)
    $alumnosQuery = "SELECT inscripciones.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
                    IFNULL(asistencias.estado, '') AS estado
                    FROM inscripciones
                    INNER JOIN alumnos ON inscripciones.nC = alumnos.nC
                    LEFT JOIN asistencias ON asistencias.nC = inscripciones.nC AND asistencias.fecha = ?
                    WHERE inscripciones.cN = ?";
    // Preparar la consulta SQL para su ejecución
    if ($stmt = $conn->prepare($alumnosQuery)) {
        $stmt->bind_param("ss", $fecha, $cN); // Asociar los valores de los parámetros a la consulta (fecha y nivel)
        $stmt->execute(); // Ejecutar la consulta
        $resultado = $stmt->get_result(); // Obtener los resultados de la consulta
        $alumnosList = [];
        // Recorrer los resultados y construir un arreglo con los datos de los alumnos
        while ($fila = $resultado->fetch_assoc()) {
            $alumnosList[] = [
                'nC' => $fila['nC'],           // Número de control del alumno
                'alumno' => $fila['alumno'],   // Nombre completo del alumno
                'estado' => $fila['estado']    // Estado de asistencia (si, no, justificado o vacío)
            ];
        }
        echo json_encode($alumnosList); // Devolver la lista de alumnos en formato JSON
        $stmt->close(); // Cerrar la consulta preparada
    }
}
// Verificar si se proporcionó el parámetro 'carrera' y si no está vacío
elseif (isset($_GET['carrera']) && !empty($_GET['carrera'])) {
    $cC = $_GET['carrera']; // Obtener el código de la carrera desde los parámetros GET
    // Consulta SQL para obtener los alumnos inscritos en la carrera especificada
    // y su estado de asistencia para la fecha proporcionada (si existe)
    $alumnosQuery = "SELECT inscripciones.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
                    IFNULL(asistencias.estado, '') AS estado
                    FROM inscripciones
                    INNER JOIN alumnos ON inscripciones.nC = alumnos.nC
                    LEFT JOIN asistencias ON asistencias.nC = inscripciones.nC AND asistencias.fecha = ?
                    WHERE inscripciones.cC = ?";
    // Preparar la consulta SQL para su ejecución
    if ($stmt = $conn->prepare($alumnosQuery)) {
        $stmt->bind_param("ss", $fecha, $cC); // Asociar los valores de los parámetros a la consulta (fecha y carrera)
        $stmt->execute(); // Ejecutar la consulta
        // Obtener los resultados de la consulta
        $resultado = $stmt->get_result();
        $alumnosList = [];
        // Recorrer los resultados y construir un arreglo con los datos de los alumnos
        while ($fila = $resultado->fetch_assoc()) {
            $alumnosList[] = [
                'nC' => $fila['nC'],           // Número de control del alumno
                'alumno' => $fila['alumno'],   // Nombre completo del alumno
                'estado' => $fila['estado']    // Estado de asistencia (si, no, justificado o vacío)
            ];
        }
        echo json_encode($alumnosList); // Devolver la lista de alumnos en formato JSON
        $stmt->close(); // Cerrar la consulta preparada
    }
}
// Si no se proporcionaron los parámetros 'nivel' o 'carrera', o están vacíos
else {
    // Enviar un mensaje de error en formato JSON
    echo json_encode(["error" => "Parámetro 'carrera' o 'nivel' no proporcionado o vacío"]);
}
