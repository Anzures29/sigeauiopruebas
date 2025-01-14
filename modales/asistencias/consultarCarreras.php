<?php
include '../../conexion.php';
header('Content-Type: application/json');

if (isset($_GET['nivel']) && !empty($_GET['nivel'])) {
    $nivelSeleccionado = $_GET['nivel'];

    // Consulta para obtener las carreras relacionadas con el nivel seleccionado
    $consultaCarreras = "SELECT cv, ca FROM carreras WHERE cN = ?";

    if ($stmt = $conn->prepare($consultaCarreras)) {
        $stmt->bind_param("s", $nivelSeleccionado);
        $stmt->execute();
        $resultadoCarreras = $stmt->get_result();

        if ($resultadoCarreras->num_rows > 0) {
            $carreras = [];
            while ($fila = $resultadoCarreras->fetch_assoc()) {
                $carreras[] = [
                    'cv' => $fila['cv'],
                    'nombre' => $fila['ca'], // Cambia 'ca' a 'nombre' para consistencia
                ];
            }
            echo json_encode($carreras);
        } else {
            echo json_encode(["error" => "No se encontraron carreras para el nivel seleccionado."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error al preparar la consulta: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Parámetro 'nivel' no proporcionado o vacío."]);
}
