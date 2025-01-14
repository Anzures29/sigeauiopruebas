<?php
include('../../conexion.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $documento = $_POST['documento'];
    $cN = $_POST['cN'];
    $nC = $_POST['nC'];
    $uploadDir = "documentos/";
    // Nombre original del archivo subido por el usuario
    $nombreOriginalArchivo = $_FILES['archivo']['name'];
    // Crear directorio de carga si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileTmpPath = $_FILES['archivo']['tmp_name'];
    $fileName = $nC . "_" . $documento . ".pdf";
    $filePath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        $fe = date('Y-m-d H:i:s');
        // Primero verifica si ya existe un registro
        $sqlCheck = "SELECT * FROM documentacion WHERE nC = ? AND cD = (SELECT cv FROM documentos WHERE do = ? AND cN = ?)";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ssi", $nC, $documento, $cN);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            // Si existe, actualiza la ruta y la fecha
            $sqlUpdate = "UPDATE documentacion SET ru = ?, noDo = ?, fe = ? WHERE nC = ? AND cD = (SELECT cv FROM documentos WHERE do = ? AND cN = ?)";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssssi", $filePath, $nombreOriginalArchivo, $fe, $nC, $documento, $cN);
            $success = $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            // Si no existe, inserta un nuevo registro
            $sqlInsert = "INSERT INTO documentacion (cD, nC, ru, noDo, fe)
                          SELECT cv, ?, ?, ? , ? 
                          FROM documentos 
                          WHERE do = ? AND cN = ?";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("sssssi", $nC, $filePath, $nombreOriginalArchivo, $fe, $documento, $cN);
            $success = $stmtInsert->execute();
            $stmtInsert->close();
        }
        $stmtCheck->close();
        if ($success) {
            // Aquí es donde se agrega el nombre del documento a la respuesta JSON
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => "Error en la base de datos"]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => "Error moviendo el archivo"]);
    }
} else {
    echo json_encode(['success' => false, 'error' => "Archivo no recibido"]);
}
$conn->close();
?>