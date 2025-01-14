<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos recibidos del formulario
    $cvA = isset($_POST['cvA']) ? trim($_POST['cvA']) : null; // Clave carrera actual
    $cv = isset($_POST['cvMod']) ? trim($_POST['cvMod']) : null; // Clave carrera nueva
    $ca = isset($_POST['caMod']) ? trim($_POST['caMod']) : null; // Carrera nueva
    $ni = (int)$_POST['niMod'] ? (int)$_POST['niMod'] : null; // Nivel nuevo (clave)
    // Validar datos mínimos
    if (!$cvA && !$cv && !$ca && !$ni) {
        echo json_encode(['exito' => false, 'mensaje_final' => "CvA: $cvA, cv: $cv, ca: $ca, ni: $ni"]);
        exit;
    }
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        // Verificar si hay alumnos inscritos en la carrera
        $query = $conn->prepare("SELECT fol FROM inscripciones WHERE cC = ?");
        $query->bind_param("s", $cvA);
        $query->execute();
        $result = $query->get_result();
        if ($result->num_rows === 0) { // Si no se encontró ningun alumno inscrito
            $query->close();
            // Actualizar los datos de la carrera
            $queryOferta = $conn->prepare("UPDATE carreras SET cv = ?, ca = ?, cN = ? WHERE cv = ?");
            $queryOferta->bind_param("ssss", $cv, $ca, $ni, $cvA);
            $queryOferta->execute();
            if ($queryOferta->affected_rows === 0) {
                $queryOferta->close();
                echo json_encode(['exito' => true, 'mensaje_final' => "No se realizaron cambios"]);
                exit;
            }
            $queryOferta->close();
        } elseif ($result->num_rows > 0) { // Si se encontraron alumnos inscritos
            $query->close();
            echo json_encode(['exito' => true, 'mensaje_final' => "No puede modificar los datos de la carrera porque hay alumnos inscritos"]);
            exit;
        }
        // Obtener nombre del nivel
        $queryNivel = $conn->prepare("SELECT ni FROM niveles WHERE cv = ?");
        $queryNivel->bind_param("s", $ni);
        $queryNivel->execute();
        $ni = $queryNivel->get_result()->fetch_assoc()['ni'];
        $queryNivel->close();
        // Registrar en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Modificó la oferta: $ni $ca";
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje_final' => 'Oferta modificada correctamente']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
