<?php
session_start();
header('Content-Type: application/json');
include('../../conexion.php');
function generarPeriodo($feI)
{
    // Obtener el mes y el año de la fecha de inicio de clases
    $fechaInicio = new DateTime($feI);
    $mesInicio = intval($fechaInicio->format('m'));
    if ($mesInicio !== 1 && $mesInicio !== 5 && $mesInicio !== 9) {
        $respuesta['exito'] = false;
        $respuesta['mensaje_final'] = "Favor de revisar la fecha de inicio de clases";
        echo json_encode($respuesta);
        exit();
    }
    $anioInicio = $fechaInicio->format('y'); // Dos últimos dígitos del año
    // Crear un array con los nombres de los meses abreviados
    $meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
    // Calcular el mes final del periodo (cuatro meses después del mes de inicio)
    $mesFinal = ($mesInicio + 3);
    $anioFinal = $mesInicio + 3 > 12 ? $anioInicio + 1 : $anioInicio;
    // Obtener el nombre del mes inicial y final
    $nombreMesInicio = $meses[$mesInicio - 1];
    $nombreMesFinal = $meses[$mesFinal - 1];
    // Formatear el periodo en el formato solicitado
    return "{$nombreMesInicio}{$anioInicio}-{$nombreMesFinal}{$anioFinal}";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos personales del alumno
    $nC = trim($_POST['nC']);
    $ma = trim($_POST['ma']);
    $aP = trim($_POST['aP']);
    $aM = trim($_POST['aM']);
    $nom = trim($_POST['nom']);
    $na = trim($_POST['lugarNacimiento']);
    $fN = trim($_POST['fechaNacimiento']);
    $ed = (int) $_POST['ed'];
    $cu = trim($_POST['cu']);
    $se = trim($_POST['se']);
    $ts = trim($_POST['ts']);
    $af = trim($_POST['af']);
    $cCo = trim($_POST['coloniaAlumno']);
    $ca = trim($_POST['ca']);
    $te = trim($_POST['te']);
    $em = trim($_POST['em']);
    // Datos de la Escuela de Procedencia
    $cct = isset($_POST['cct']) ? trim($_POST['cct']) : null;
    $ge = isset($_POST['ge']) ? trim($_POST['ge']) : null;
    $pr = isset($_POST['pr']) ? trim($_POST['pr']) : null;
    // Datos del Tutor
    $curpTutor = isset($_POST['curpTutor']) ? trim($_POST['curpTutor']) : null;
    $nomTutor = isset($_POST['nomTutor']) ? trim($_POST['nomTutor']) : null;
    $pa = isset($_POST['pa']) ? trim($_POST['pa']) : null;
    $teTutor = isset($_POST['teTutor']) ? trim($_POST['teTutor']) : null;
    $cCoT = isset($_POST['coloniaTutor']) ? trim($_POST['coloniaTutor']) : null;
    $calleTutor = isset($_POST['calleTutor']) ? trim($_POST['calleTutor']) : null;
    // Datos de la inscripción
    $fol = trim($_POST['fol']);
    $ban = "false";
    $fe = trim($_POST['fe']);
    $cN = trim($_POST['nivel']);
    $cC = isset($_POST['carrera']) ? trim($_POST['carrera']) : null;
    $feI = trim($_POST['feI']);
    $feF = trim($_POST['feF']);
    $fePa = trim($_POST['fePa']);
    $coIns = (float) $_POST['costo'];
    $coCol = (float) $_POST['cole'];
    $coRei = (float) $_POST['rein'];
    $ins = "";
    $cole = "";
    $rein = "";
    $pe = generarPeriodo($feI); // Periodo de inscripción

    // Validación final antes de las inserciones
    if (!$cCo || !$cN) {
        $respuesta['exito'] = false;
        $respuesta['mensaje_final'] = "Favor de revisar los datos ingresados.";
        echo json_encode($respuesta);
        $conn->rollback();
        $conn->close();
        exit();
    }
    $respuesta = [
        'exito' => true,
        'mensaje_final' => ''
    ];
    $conn->begin_transaction(); // Se inicia la transacción
    // Try Catch para realizar todas las inserciones de inscripción
    try {
        if (!empty($ts)) {
            $ts = "";
        }
        if (!empty($af)) {
            $af = "";
        }
        // Inserción en la tabla `alumnos`
        $stmtAlumnos = $conn->prepare("INSERT INTO alumnos (nC, ma, aP, aM, nom, cM, fN, ed, cu, se, cCo, ca, ts, af, te, em)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtAlumnos->bind_param("ssssssssssssssss", $nC, $ma, $aP, $aM, $nom, $na, $fN, $ed, $cu, $se, $cCo, $ca, $ts, $af, $te, $em);
        $stmtAlumnos->execute();
        $stmtAlumnos->close();
        // Insertar en la tabla `escuelas` si al menos uno de los datos está presente
        if ($nC) {
            // Verificar si al menos uno de los campos tiene datos
            if (!empty($cct) || !empty($ge) || !empty($pr)) {
                // Preparar la sentencia SQL con los cuatro campos
                $stmtEscuelas = $conn->prepare("INSERT INTO escuelas (cct, nC, ge, pr) VALUES (?, ?, ?, ?)");
                // Usar valores predeterminados para los campos vacíos
                $cct = $cct ?: null;
                $ge = $ge ?: null;
                $pr = $pr ?: null;
                $stmtEscuelas->bind_param("ssss", $cct, $nC, $ge, $pr);
            } else {
                // Solo insertar el campo `nC`
                $stmtEscuelas = $conn->prepare("INSERT INTO escuelas (nC) VALUES (?)");
                $stmtEscuelas->bind_param("s", $nC);
            }
            $stmtEscuelas->execute();
            $stmtEscuelas->close();
        }

        // Insertar en la tabla `tutores` si al menos uno de los datos está presente
        if ($nC) {
            // Verificar si al menos uno de los campos tiene datos
            if (!empty($curpTutor) || !empty($nomTutor) || !empty($pa) || !empty($teTutor) || !empty($cCoT) || !empty($calleTutor)) {
                // Preparar la sentencia SQL con todos los campos
                $stmtTutores = $conn->prepare("INSERT INTO tutores (cu, nom, nC, pa, te, cCo, ca) VALUES (?, ?, ?, ?, ?, ?, ?)");
                // Usar valores predeterminados para los campos vacíos
                $curpTutor = $curpTutor ?: null;
                $nomTutor = $nomTutor ?: null;
                $pa = $pa ?: null;
                $teTutor = $teTutor ?: null;
                $cCoT = $cCoT ?: null;
                $calleTutor = $calleTutor ?: null;
                $stmtTutores->bind_param("sssssss", $curpTutor, $nomTutor, $nC, $pa, $teTutor, $cCoT, $calleTutor);
            } else {
                // Solo insertar el campo `nC`
                $stmtTutores = $conn->prepare("INSERT INTO tutores (nC) VALUES (?)");
                $stmtTutores->bind_param("s", $nC);
            }
            $stmtTutores->execute();
            $stmtTutores->close();
        }

        // Verificar y procesar la carga de archivo
        if (isset($_FILES['ft']) && $_FILES['ft']['error'] === UPLOAD_ERR_OK) {
            $imagen_nombre = basename($_FILES['ft']['name']);
            $imagen_temporal = $_FILES['ft']['tmp_name'];
            $directorio_destino = 'img/';
            $fileType = mime_content_type($imagen_temporal);
            $allowedTypes = ['image/jpeg', 'image/png'];
            // Validar tipo de archivo
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Error: El archivo debe ser una imagen JPEG o PNG.");
            }
            // Validar tamaño de archivo
            $maxFileSize = 2 * 1024 * 1024; // 2 MB
            if ($_FILES['ft']['size'] > $maxFileSize) {
                throw new Exception("Error: El tamaño de la imagen no debe exceder los 2 MB.");
            }
            // Crear directorio si no existe
            if (!file_exists($directorio_destino) && !mkdir($directorio_destino, 0777, true)) {
                throw new Exception("Error al crear el directorio de destino.");
            }
            // Mover archivo
            $ruta_destino = $directorio_destino . $imagen_nombre;
            if (file_exists($ruta_destino)) {
                unlink($ruta_destino); // Eliminar el archivo existente si ya está presente
            }
            if (!move_uploaded_file($imagen_temporal, $ruta_destino)) {
                throw new Exception("Error al mover el archivo a la ubicación de destino.");
            }
            // Insertar en la tabla `inscripciones` con o sin la clave de la carrera
            if ($cC !== null) {
                // Insertar con la clave de carrera
                $stmtInscripciones = $conn->prepare("INSERT INTO inscripciones (fol, bandera, ft, fe, nC, cN, cC, feIni, feFin, fePa, pe, peAct, coIns, coCol, coColOrig, coRei, ins, cole, rein)
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInscripciones->bind_param("sssssssssssssssssss", $fol, $ban, $ruta_destino, $fe, $nC, $cN, $cC, $feI, $feF, $fePa, $pe, $pe, $coIns, $coCol, $coCol, $coRei, $ins, $cole, $rein);
            } else {
                // Insertar sin la clave de carrera
                $stmtInscripciones = $conn->prepare("INSERT INTO inscripciones (fol, bandera, ft, fe, nC, cN, feIni, feFin, fePa, pe, peAct, coIns, coCol, coColOrig, coRei, ins, cole, rein)
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInscripciones->bind_param("ssssssssssssssssss", $fol, $ban, $ruta_destino, $fe, $nC, $cN, $feI, $feF, $fePa, $pe, $pe, $coIns, $coCol, $coCol, $coRei, $ins, $cole, $rein);
            }
            $stmtInscripciones->execute();
            $stmtInscripciones->close();
        }
        // Ingresar acción a la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Inscribió al alumno " . $nom . " " . $aP . " " . $aM;
        $fecha = date('Y-m-d');
        $hora = date("H:i:s");
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Si todas las inserciones fueron exitosas
        $respuesta['exito'] = true;
        $respuesta['mensaje_final'] = "Alumno inscrito correctamente.";
        $conn->commit();
    } catch (Exception $e) {
        // Revertir la transacción si ocurre un error
        $conn->rollback();
        $respuesta['exito'] = false;
        $respuesta['mensaje_final'] = "Favor de verificar los datos ingresados.";
    }
    echo json_encode($respuesta);
    $conn->close();
    exit();
}
