<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
// Validar la entrada
$cv = $_POST['cv'] ?? null;
$ca = $_POST['ca'] ?? null;
// Validar datos mínimos
if (empty(trim($cv))) {
    echo json_encode(['exito' => false, 'mensaje_final' => "No se recibió la clave de la carrera"]);
    exit;
}
// Iniciar transacción
$conn->begin_transaction();
try {
    // Comprobar si hay alumnos inscritos en esa carrera
    $query = $conn->prepare("SELECT fol FROM inscripciones WHERE cC = ?");
    $query->bind_param("s", $cv);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $query->close();
        echo json_encode(['exito' => true, 'mensaje_final' => "No puede eliminar la carrera $ca, porque hay alumnos inscritos"]);
        exit;
    }
    $query->close();
    // Eliminar oferta
    $stmt = $conn->prepare("DELETE FROM carreras WHERE cv = ?");
    $stmt->bind_param("s", $cv);
    $stmt->execute();
    $stmt->close();
    // Registrar en la bitácora
    $clave = $_SESSION['clave'];
    $accion = "Eliminó la carrera: " . $ca;
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
    $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();
    // Confirmar transacción
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje_final' => 'La oferta se eliminó correctamente.']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
} finally {
    $conn->close();
}
