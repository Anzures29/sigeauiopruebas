<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
// Función para generar respuestas JSON
function responder($exito, $mensaje)
{
    header('Content-Type: application/json');
    echo json_encode(['exito' => $exito, 'mensaje_final' => $mensaje]);
    exit();
}
// Verificar si los datos requeridos existen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar la entrada
    $fo = trim($_POST['fo'] ?? '');
    $cT = trim($_POST['cT'] ?? '');
    $nC = trim($_POST['nC'] ?? '');
    $alumno = trim($_POST['alumno'] ?? '');
    if (empty($fo) || empty($cT) || empty($nC) || empty($alumno)) {
        responder(false, "Datos incompletos");
    }
    try {
        $conn->begin_transaction(); // Iniciar transacción
        // Eliminar el pago
        $stmt_delete = $conn->prepare("DELETE FROM pagos WHERE fo = ?");
        if ($stmt_delete === false) {
            throw new Exception("Error al preparar la eliminación del pago: " . $conn->error);
        }
        $stmt_delete->bind_param("s", $fo);
        $stmt_delete->execute();
        if ($stmt_delete->affected_rows === 0) {
            throw new Exception("No se pudo eliminar el pago.");
        }
        $stmt_delete->close();
        // // Actualizar estado en la tabla de inscripciones según el tipo de pago eliminado
        // $update_queries = [
        //     1 => "UPDATE inscripciones SET ins = 'No' WHERE nC = ?",
        //     2 => "UPDATE inscripciones SET rein = 'No' WHERE nC = ?",
        //     3 => "UPDATE inscripciones SET cole = 'No' WHERE nC = ?"
        // ];
        // if (isset($update_queries[$cT])) {
        //     $stmt_update = $conn->prepare($update_queries[$cT]);
        //     if ($stmt_update === false) {
        //         throw new Exception("Error al preparar la actualización del estatus: " . $conn->error);
        //     }
        //     $stmt_update->bind_param("s", $nC);
        //     $stmt_update->execute();
        //     if ($stmt_update->affected_rows === 0) {
        //         throw new Exception("No se pudo actualizar el estatus.");
        //     }
        //     $stmt_update->close();
        // }
        // Registrar en la bitácora
        if (!isset($_SESSION['clave'])) {
            throw new Exception("Sesión no válida. El usuario no está autenticado.");
        }
        $clave = $_SESSION['clave'];
        $accion = "Eliminó el pago $fo del alumno $alumno";
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        if ($stmt_bitacora === false) {
            throw new Exception("Error al preparar la bitácora: " . $conn->error);
        }
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        responder(true, "El pago $fo del alumno $alumno se eliminó correctamente."); // Respuesta
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        responder(false, $e->getMessage()); // Respuesta
    } finally {
        $conn->close();
    }
} else {
    responder(false, "Método de solicitud no permitido."); // Respuesta
}
