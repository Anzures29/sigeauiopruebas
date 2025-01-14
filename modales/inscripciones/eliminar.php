<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
// Validar la entrada
$fo = $_POST['fo'] ?? null;
$alumno = $_POST['alumno'] ?? null;
// Validar datos mínimos
if (empty(trim($fo)) || empty(trim($alumno))) {
    echo json_encode(['exito' => false, 'mensaje_final' => 'Folio o alumno no proporcionado.']);
    exit;
}
try {
    // Iniciar transacción
    $conn->begin_transaction();
    // Dar de baja
    $query = "DELETE FROM inscripciones WHERE fol = ?";
    $params = [$fo];
    $types = "s";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    // Registrar en la bitácora
    $clave = $_SESSION['clave'];
    $accion = "Dió de baja al alumno: " . $alumno;
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
    $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();

    // Confirmar transacción
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje_final' => 'El alumno ha sido dado de baja correctamente.']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
} finally {
    $conn->close();
}
