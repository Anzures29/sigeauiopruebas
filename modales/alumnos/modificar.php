<?php
session_start();
include('../../conexion.php');
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos personales del alumno
    $nC = trim($_POST['nC']);
    $ma = trim($_POST['maMod']);
    $aP = trim($_POST['aPMod']);
    $aM = trim($_POST['aMMod']);
    $nom = trim($_POST['nomMod']);
    $cM = isset($_POST['lugarNacimientoMod']) ? trim($_POST['lugarNacimientoMod']) : null;
    $fN = trim($_POST['fechaNacimientoMod']);
    $ed = (int) $_POST['edMod'];
    $cu = trim($_POST['cuMod']);
    $se = trim($_POST['seMod']);
    $ts = trim($_POST['tsMod']);
    $af = trim($_POST['afMod']);
    $cCo = isset($_POST['coloniaAlumnoMod']) ? trim($_POST['coloniaAlumnoMod']) : null;
    $ca = trim($_POST['caMod']);
    $te = trim($_POST['teMod']);
    $em = trim($_POST['emMod']);
    // Datos de la Escuela de Procedencia
    $cctMod = isset($_POST['cctMod']) ? trim($_POST['cctMod']) : null;
    $geMod = isset($_POST['geMod']) ? trim($_POST['geMod']) : null;
    $prMod = isset($_POST['prMod']) ? trim($_POST['prMod']) : null;
    // Datos del Tutor
    $curpTutorMod = isset($_POST['curpTutorMod']) ? trim($_POST['curpTutorMod']) : null;
    $nomTutorMod = isset($_POST['nomTutorMod']) ? trim($_POST['nomTutorMod']) : null;
    $paMod = isset($_POST['paMod']) ? trim($_POST['paMod']) : null;
    $teTutorMod = isset($_POST['teTutorMod']) ? trim($_POST['teTutorMod']) : null;
    $coloniaTutorMod = isset($_POST['coloniaTutorMod']) ? trim($_POST['coloniaTutorMod']) : null;
    $calleTutorMod = isset($_POST['calleTutorMod']) ? trim($_POST['calleTutorMod']) : null;
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        $mensaje_final = "Datos del Alumno Modificados Correctamente.";
        // Actualizar los datos del alumno
        $query = "UPDATE alumnos SET ma = ?, aP = ?, aM = ?, nom = ?, fN = ?, ed = ?, cu = ?, se = ?, ca = ?, te = ?, em = ?";
        $types = "sssssssssss";
        $params = [$ma, $aP, $aM, $nom, $fN, $ed, $cu, $se, $ca, $te, $em];
        // Agregar cM si está presente
        if ($cM) {
            $query .= ", cM = ?";
            $types .= "s";
            $params[] = $cM;
        }
        // Agregar cCo si está presente
        if ($cCo) {
            $query .= ", cCo = ?";
            $types .= "s";
            $params[] = $cCo;
        }
        // Agregar ts si está presente
        if ($ts) {
            $query .= ", ts = ?";
            $types .= "s";
            $params[] = $ts;
        }
        // Agregar af si está presente
        if ($af) {
            $query .= ", af = ?";
            $types .= "s";
            $params[] = $af;
        }
        // Condición
        $query .= " WHERE nC = ?";
        $types .= "s";
        $params[] = $nC;
        $stmtAlumnos = $conn->prepare($query); // Preparar la consulta
        $stmtAlumnos->bind_param($types, ...$params); // Vincular los parámetros dinámicamente
        $stmtAlumnos->execute(); // Ejecutar la consulta
        $stmtAlumnos->close(); // Cerrar la consulta preparada
        // Actualizar los datos de las escuelas si se reciben alguno de estos valores
        if ($cctMod || $geMod || $prMod) {
            // Si no hay valor en cctMod, asignar NULL
            $cctMod = $cctMod ?: null;
            $geMod = $geMod ?: "";
            $prMod = $prMod ?: "";
            $stmtEscuelas = $conn->prepare("UPDATE escuelas SET cct = ?, ge = ?, pr = ? WHERE nC = ?");
            $stmtEscuelas->bind_param("ssss", $cctMod, $geMod, $prMod, $nC);
            $stmtEscuelas->execute();
            $stmtEscuelas->close();
        }
        // Actualizar los datos de los tutores si se reciben alguno de estos valores
        if (!$curpTutorMod || !$nomTutorMod || !$paMod || !$teTutorMod || !$calleTutorMod || !$coloniaTutorMod) {
            // Usar el operador ternario para asignar valores vacíos si no están presentes
            $curpTutorMod = $curpTutorMod ?: "";
            $nomTutorMod = $nomTutorMod ?: "";
            $paMod = $paMod ?: "";
            $teTutorMod = $teTutorMod ?: "";
            $calleTutorMod = $calleTutorMod ?: "";
            // Consulta SQL
            $query = "UPDATE tutores SET cu = ?, nom = ?, pa = ?, te = ?, ca = ?";
            $types = "sssss";
            $params = [$curpTutorMod, $nomTutorMod, $paMod, $teTutorMod, $calleTutorMod];
            // Agregar coloniaTutorMod si está presente
            if ($coloniaTutorMod) {
                $query .= ", cCo = ?";
                $types .= "s";
                $params[] = $coloniaTutorMod;
            }
            // Condición
            $query .= " WHERE nC = ?";
            $types .= "s";
            $params[] = $nC;
            $stmtAlumnos = $conn->prepare($query); // Preparar la consulta
            $stmtAlumnos->bind_param($types, ...$params); // Vincular los parámetros dinámicamente
            $stmtAlumnos->execute(); // Ejecutar la consulta
            $stmtAlumnos->close(); // Cerrar la consulta preparada
        }
        // Registrar acción en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Modificó los datos del alumno $nom $aP $aM";
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $bitacoraQuery = "INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)";
        $stmt_bitacora = $conn->prepare($bitacoraQuery);
        if (!$stmt_bitacora) {
            throw new Exception("Error al preparar la consulta de bitácora: " . $conn->error);
        }
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje_final' => $mensaje_final]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
