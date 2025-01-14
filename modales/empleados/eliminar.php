<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar los datos de entrada
    $cv = isset($_POST['cv']) ? trim($_POST['cv']) : null;
    $empleado = isset($_POST['nom']) ? trim($_POST['nom']) : null;
    if (!$cv || !$empleado) {
        echo json_encode(['exito' => false, 'mensaje_final' => 'Empleado no proporcionado o inválido.']);
        exit;
    }
    $conn->begin_transaction();
    try {
        // Eliminar registros de bitácora del usuario del empleado
        $query = "DELETE FROM bitacora WHERE cU = (SELECT cv FROM usuarios WHERE cE = ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $cv);
        $stmt->execute();
        $stmt->close();
        // Eliminar el usuario del empleado
        $query = "DELETE FROM usuarios WHERE cE = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $cv);
        $stmt->execute();
        $stmt->close();
        // Eliminar el empleado
        $query = "DELETE FROM empleados WHERE cv = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $cv);
        $stmt->execute();
        $stmt->close();
        // Registrar en la bitácora
        $clave = $_SESSION['clave'];
        $accion = sprintf(
            "Eliminó los datos del empleado %s",
            htmlspecialchars($empleado, ENT_QUOTES, 'UTF-8')
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
        echo json_encode(['exito' => true, 'mensaje_final' => 'El empleado se eliminó correctamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
