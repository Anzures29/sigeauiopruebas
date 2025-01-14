<?php
session_start();
include('../../conexion.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    // Datos recibidos del formulario
    $nC = isset($_POST['nC']) ? trim($_POST['nC']) : null;
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : null;
    $pr = isset($_POST['pr']) ? trim($_POST['pr']) : null;
    // Validar datos mínimos
    if (!$nC || !$pr) {
        echo json_encode(['exito' => false, 'mensaje_final' => 'Faltan datos obligatorios.']);
        exit;
    }
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        // Actualizar promedio en la tabla egresados
        $query = "UPDATE egresados SET pr = ? WHERE nC = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $pr, $nC);
        $stmt->execute();
        $stmt->close();
        // Registrar acción en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Promedió al alumno: " . $nom;
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje_final' => 'Alumno promediado correctamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
