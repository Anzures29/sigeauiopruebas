<?php
include('../../conexion.php'); // Incluir el archivo de conexión a la base de datos
// Verificar que la solicitud se realizó mediante el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos enviados en la solicitud POST
    $asistencias = $_POST['asistencias']; // Array de asistencias (nC => estado)
    $fecha = $_POST['fecha']; // Fecha de la asistencia enviada desde el formulario
    // Verificar que se haya proporcionado la fecha
    if (empty($fecha)) {
        echo json_encode(['success' => false, 'error' => 'La fecha es requerida.']);
        exit;
    }
    // Iterar sobre el arreglo de asistencias
    foreach ($asistencias as $nC => $estado) {
        // Verificar si ya existe un registro de asistencia para el alumno en la fecha especificada
        $queryCheck = "SELECT COUNT(*) AS count FROM asistencias WHERE nC = ? AND fecha = ?";
        $stmtCheck = $conn->prepare($queryCheck);
        $stmtCheck->bind_param("ss", $nC, $fecha); // Asignar los valores a los parámetros
        $stmtCheck->execute(); // Ejecutar la consulta
        $resultCheck = $stmtCheck->get_result(); // Obtener el resultado de la consulta
        if ($resultCheck) {
            $row = $resultCheck->fetch_assoc();
            if ($row['count'] > 0) {
                // Si ya existe un registro, actualizar el estado de la asistencia
                $queryUpdate = "UPDATE asistencias SET estado = ? WHERE nC = ? AND fecha = ?";
                $stmtUpdate = $conn->prepare($queryUpdate);
                $stmtUpdate->bind_param("sss", $estado, $nC, $fecha); // Asignar los valores a los parámetros
                if (!$stmtUpdate->execute()) {
                    // Enviar un mensaje de error si la actualización falla
                    echo json_encode(['success' => false, 'error' => 'Error al actualizar la asistencia.']);
                    exit;
                }
            } else {
                // Si no existe un registro, insertar uno nuevo
                $queryInsert = "INSERT INTO asistencias (nC, estado, fecha) VALUES (?, ?, ?)";
                $stmtInsert = $conn->prepare($queryInsert);
                $stmtInsert->bind_param("sss", $nC, $estado, $fecha); // Asignar los valores a los parámetros
                if (!$stmtInsert->execute()) {
                    // Enviar un mensaje de error si la inserción falla
                    echo json_encode(['success' => false, 'error' => 'Error al registrar la asistencia.']);
                    exit;
                }
            }
        } else {
            // Enviar un mensaje de error si la verificación de existencia falla
            echo json_encode(['success' => false, 'error' => 'Error al verificar la existencia del registro.']);
            exit;
        }
    }
    // Si todas las operaciones se realizan correctamente, enviar una respuesta de éxito
    echo json_encode(['success' => true]);
}
