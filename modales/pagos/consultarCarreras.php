<?php
include '../../conexion.php';
// Verifica que el nivel esté presente
if (isset($_GET['nivel'])) {
    $nivelSeleccionado = $_GET['nivel'];

    // Consulta para obtener las carreras basadas en el nivel seleccionado
    $consultaCarreras = "SELECT cv, ca FROM carreras WHERE cN = ?"; // 'cN' es la clave foránea en carreras que hace referencia a 'niveles'

    // Prepara y ejecuta la consulta
    if ($stmt = $conn->prepare($consultaCarreras)) {
        $stmt->bind_param("s", $nivelSeleccionado); // 's' es para cadena (string)
        $stmt->execute();
        $resultadoCarreras = $stmt->get_result();

        $carreras = [];
        while ($fila = $resultadoCarreras->fetch_assoc()) {
            $carreras[] = [
                'cv' => $fila['cv'],
                'ca' => $fila['ca']
            ];
        }

        // Retorna las carreras en formato JSON
        echo json_encode($carreras);

        // Cierra la sentencia preparada
        $stmt->close();
    }
}
