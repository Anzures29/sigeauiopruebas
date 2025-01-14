<?php
include '../../conexion.php';
header('Content-Type: application/json');
$numeroControl = $_GET['nC'] ?? '';
$query = "SELECT ru, cD, noDo FROM documentacion WHERE nC = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $numeroControl);
$stmt->execute();
$result = $stmt->get_result();
$documentos = [];
while ($row = $result->fetch_assoc()) {
    $documentos[] = [
        'ruta' => 'modales/documentacion/' . $row['ru'],
        'claveDocumento' => $row['cD'],
        'nombre' => $row['noDo']
    ];
}
echo json_encode(['documentos' => $documentos]);
?>