<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
// Validar los datos de entrada
$cv = isset($_POST['cv']) ? trim($_POST['cv']) : null;
$usuario = isset($_POST['us']) ? trim($_POST['us']) : null;
if (!$cv || !$usuario) {
    echo json_encode(['exito' => false, 'mensaje_final' => 'Usuario no proporcionado o inválido.']);
    exit;
}
$conn->begin_transaction();
try {
    // Eliminar el usuario
    $query = "DELETE FROM usuarios WHERE cv = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param("s", $cv);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se encontró el usuario con el folio especificado.");
    }
    $stmt->close();
    // Registrar en la bitácora
    if (!isset($_SESSION['clave'])) {
        throw new Exception("Sesión no válida. El usuario no está autenticado.");
    }
    $clave = $_SESSION['clave'];
    $accion = sprintf(
        "Eliminó los datos del usuario %s",
        htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8')
    );
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
    if (!$stmt_bitacora) {
        throw new Exception("Error al preparar la bitácora: " . $conn->error);
    }

    $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
    $stmt_bitacora->execute();
    $stmt_bitacora->close();

    // Confirmar la transacción
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje_final' => 'El usuario se eliminó correctamente.']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
} finally {
    $conn->close();
}
