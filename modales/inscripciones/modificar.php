<?php
session_start();
include('../../conexion.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    // Datos recibidos del formulario
    $fol = isset($_POST['folMod']) ? trim($_POST['folMod']) : null;
    $cC = isset($_POST['carreraMod']) ? trim($_POST['carreraMod']) : null;
    $feIni = isset($_POST['feIMod']) ? trim($_POST['feIMod']) : null;
    $feFin = isset($_POST['feFMod']) ? trim($_POST['feFMod']) : null;
    $fePa = isset($_POST['fePaMod']) ? trim($_POST['fePaMod']) : null;
    $nC = isset($_POST['nCAct']) ? trim($_POST['nCAct']) : null;
    $alumno = isset($_POST['alumnoMod']) ? trim($_POST['alumnoMod']) : null;
    $coAct = isset($_POST['cosAct']) ? trim($_POST['cosAct']) : null;
    $co = isset($_POST['costoMod']) ? trim($_POST['costoMod']) : null;
    $colAct = isset($_POST['colAct']) ? trim($_POST['colAct']) : null;
    $col = isset($_POST['coleMod']) ? trim($_POST['coleMod']) : null;
    $reAct = isset($_POST['reiAct']) ? trim($_POST['reiAct']) : null;
    $re = isset($_POST['reinMod']) ? trim($_POST['reinMod']) : null;
    // Validar datos mínimos
    if (!$fol || !$feIni) {
        echo json_encode(['exito' => false, 'mensaje_final' => 'Faltan datos obligatorios.']);
        exit;
    }
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        // Manejo de la imagen (si se sube)
        $ruta_destino = null;
        if (isset($_FILES['ftMod']) && $_FILES['ftMod']['error'] === UPLOAD_ERR_OK) {
            $imagen_nombre = basename($_FILES['ftMod']['name']);
            $imagen_temporal = $_FILES['ftMod']['tmp_name'];
            $directorio_destino = 'img/';
            $fileType = mime_content_type($imagen_temporal);
            $allowedTypes = ['image/jpeg', 'image/png'];
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('El archivo debe ser una imagen JPEG o PNG.');
            }
            if ($_FILES['ftMod']['size'] > 5 * 1024 * 1024) {
                throw new Exception('El tamaño de la imagen no debe exceder los 5 MB.');
            }
            if (!file_exists($directorio_destino) && !mkdir($directorio_destino, 0777, true)) {
                throw new Exception('Error al crear el directorio de destino.');
            }
            $ruta_destino = $directorio_destino . $imagen_nombre;
            if (!move_uploaded_file($imagen_temporal, $ruta_destino)) {
                throw new Exception('Error al mover el archivo de imagen.');
            }
        }
        // Verificar si se modificó el costo de inscripción, colegiatura o reinscripción
        if ($co != $coAct || $col != $colAct || $re != $reAct) {
            $tiposPago = [
                ['actual' => $co, 'previo' => $coAct, 'tp' => 1, 'mensaje' => "Está intentando modificar los costos de la inscripción, por favor elimine los pagos que el alumno haya realizado."],
                ['actual' => $re, 'previo' => $reAct, 'tp' => 2, 'mensaje' => "Está intentando modificar los costos de la inscripción, por favor elimine los pagos que el alumno haya realizado."],
                ['actual' => $col, 'previo' => $colAct, 'tp' => 3, 'mensaje' => "Está intentando modificar los costos de la inscripción, por favor elimine los pagos que el alumno haya realizado."]
            ];
            foreach ($tiposPago as $tipo) {
                if ($tipo['actual'] != $tipo['previo']) {
                    $stmtPagos = $conn->prepare("SELECT im FROM pagos WHERE nC = ? AND cT = ?");
                    if ($stmtPagos === false) {
                        throw new Exception("Error al preparar la consulta de los pagos del alumno: " . $conn->error);
                    }
                    $stmtPagos->bind_param("ss", $nC, $tipo['tp']);
                    $stmtPagos->execute();
                    $result = $stmtPagos->get_result();
                    $numRows = $result->num_rows;
                    if ($numRows >= 1) {
                        throw new Exception($tipo['mensaje']);
                    }
                    $stmtPagos->close();
                }
            }
        }
        // Actualizar inscripción
        $query = "UPDATE inscripciones SET feIni = ?, feFin = ?, fePa = ?, coIns = ?, coRei = ?, coCol = ?, coColOrig = ?";
        $params = [$feIni, $feFin, $fePa, $co, $re, $col, $col];
        $types = "sssssss";
        if ($ruta_destino) {
            $query .= ", ft = ?";
            $params[] = $ruta_destino;
            $types .= "s";
        }
        if ($cC) {
            $query .= ", cC = ?";
            $params[] = $cC;
            $types .= "s";
        }
        $query .= " WHERE fol = ?";
        $params[] = $fol;
        $types .= "s";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
        // Registrar en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Modificó la inscripción del alumno: " . $alumno;
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje_final' => 'Inscripción modificada correctamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
