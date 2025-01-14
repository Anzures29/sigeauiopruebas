<?php
include('../../conexion.php');
header('Content-Type: application/json');

// Activa la depuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Recibe los datos JSON y decodifica
$data = json_decode(file_get_contents("php://input"), true);

// Verifica que los datos estén presentes
if (isset($data['numeroControl']) && isset($data['documento']) && isset($data['nivelEducativo'])) {
    $nC = $data['numeroControl'];
    $documento = $data['documento'];
    $cN= $data['nivelEducativo'];

    // Consulta para eliminar el documento en la base de datos
    $sqlDelete = "DELETE FROM documentacion WHERE nC = ? AND cD = (SELECT cv FROM documentos WHERE do = ? AND cN = ?)";
    $stmt = $conn->prepare($sqlDelete);

    // Verifica que la preparación de la consulta fue exitosa
    if ($stmt === false) {
        echo json_encode([
            "success" => false,
            "error" => "Error al preparar la consulta: " . $conn->error
        ]);
        exit;
    }

    // Vincula los parámetros y ejecuta la consulta
    $stmt->bind_param("sss", $nC, $documento, $cN);
    if ($stmt->execute()) {
        // Consulta exitosa
        echo json_encode(["success" => true]);
    } else {
        // Error en la ejecución de la consulta
        echo json_encode([
            "success" => false,
            "error" => "Error al ejecutar la consulta: " . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    // Retorna un error si faltan datos
    echo json_encode([
        "success" => false,
        "error" => "Datos incompletos para eliminar el archivo."
    ]);
}

$conn->close();
?>