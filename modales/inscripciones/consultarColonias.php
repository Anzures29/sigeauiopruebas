<?php
include '../../conexion.php';
// Verifica que el municipio esté presente
if (isset($_GET['municipio'])) {
    $municipio = $_GET['municipio'];
    // Consulta para obtener las colonias basadas en el municipio seleccionado
    $consultaColonias = "SELECT cv, co FROM colonias WHERE cM = ?";
    if ($stmt = $conn->prepare($consultaColonias)) {
        $stmt->bind_param("s", $municipio);
        $stmt->execute();
        $resColonias = $stmt->get_result();
        $colonias = [];
        while ($fila = $resColonias->fetch_assoc()) {
            $colonias[] = [
                'cv' => $fila['cv'],
                'co' => $fila['co']
            ];
        }
        echo json_encode($colonias); // Envía el JSON con clave y colonia
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error en la consulta"]);
    }
} else {
    echo json_encode(["error" => "Municipio no proporcionado"]);
}
