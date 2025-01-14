<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
// Validar los datos de entrada
$nC = isset($_POST['nC']) ? trim($_POST['nC']) : null;
$alumno = isset($_POST['alumno']) ? trim($_POST['alumno']) : null;
if (!$nC || !$alumno) {
    echo json_encode(['exito' => false, 'mensaje_final' => $nC . $alumno]);
    exit;
}
$conn->begin_transaction();
try {
    // Eliminar los datos de la escuela de procedencia
    $queryEscuelas = "DELETE FROM escuelas WHERE nC = ?";
    $stmtEscuelas = $conn->prepare($queryEscuelas);
    $stmtEscuelas->bind_param("s", $nC);
    $stmtEscuelas->execute();
    $stmtEscuelas->close();
    // Eliminar los datos del tutor
    $queryTutores = "DELETE FROM tutores WHERE nC = ?";
    $stmtTutores = $conn->prepare($queryTutores);
    $stmtTutores->bind_param("s", $nC);
    $stmtTutores->execute();
    $stmtTutores->close();
    // Eliminar los pagos del alumno
    $query = "DELETE FROM pagos WHERE nC = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nC);
    $stmt->execute();
    $stmt->close();
    // Eliminar los datos del alumno
    $query = "DELETE FROM alumnos WHERE nC = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nC);
    $stmt->execute();
    $stmt->close();
    // Registrar en la bitácora
    $clave = $_SESSION['clave'];
    $accion = sprintf(
        "Eliminó los datos del alumno %s",
        htmlspecialchars($alumno, ENT_QUOTES, 'UTF-8')
    );
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
    $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();
    // Confirmar la transacción
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje_final' => 'El alumno se eliminó correctamente.']);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
} finally {
    $conn->close();
}
