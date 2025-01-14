<?php
include '../../conexion.php';

// Verifica que el municipio esté presente
if (isset($_GET['municipio'])) {
    $municipio = $_GET['municipio'];
    // Consulta para obtener las colonias basadas en el municipio seleccionado
    $consultaColonias = "SELECT cv, co FROM colonias WHERE cM = ?";
    // Prepara y ejecuta la consulta
    if ($stmt = $conn->prepare($consultaColonias)) {
        $stmt->bind_param("s", $municipio); // 's' es para cadena (string)
        $stmt->execute();
        $resColonias = $stmt->get_result();

        $colonias = [];
        while ($fila = $resColonias->fetch_assoc()) {
            $colonias[] = [
                'cv' => $fila['cv'],
                'co' => $fila['co']
            ];
        }
        // Retorna las colonias en formato JSON
        echo json_encode($colonias);
        // Cierra la sentencia preparada
        $stmt->close();
    }
}
?>