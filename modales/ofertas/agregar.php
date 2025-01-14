<?php
session_start(); // Se inicia la sesión
include('../../conexion.php'); // Archivo de conexión
header('Content-Type: application/json');
// Función parla generar la clave de la carrera
function generarClave($claveNivel, $carrera)
{
    // Dividir la carrera en palabras
    $palabras = explode(' ', trim($carrera));
    // Generar la clave dependiendo de la cantidad de palabras en la carrera
    if (count($palabras) == 1) {
        // Carrera de una sola palabra: primer letra del nivel + 3 primeras letras de la carrera
        $claveCarrera = substr($palabras[0], 0, 3);
    } else {
        // Carrera de varias palabras: primer letra del nivel + primer letra de cada palabra de la carrera
        $claveCarrera = '';
        foreach ($palabras as $palabra) {
            $claveCarrera .= substr($palabra, 0, 1);
        }
    }
    // Concatenar nivel y carrera para formar la clave
    $clave = strtoupper($claveNivel . $claveCarrera);
    return $clave;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ni = trim($_POST['ni']); // Clave del nivel 2: Licenciatura, 3: Maestría y 4: Doctorado
    $ca = trim($_POST['ca']); // Carrera
    $respuesta = ['exito' => true, 'mensajes' => []];
    // Mapear la clave del nivel a la primera letra del nivel
    $niveles = [
        '2' => 'L', // Licenciatura
        '3' => 'M', // Maestría
        '4' => 'D'  // Doctorado
    ];
    if (!isset($niveles[$ni])) {
        echo json_encode(['exito' => false, 'mensaje_final' => "Nivel educativo no válido"]);
        exit; // Se finaliza el script
    }
    $claveNivel = $niveles[$ni];
    $cv = generarClave($claveNivel, $ca);
    // Validar si los datos que se reciben están vacíos
    if (empty($cv) || empty($ni) || empty($ca)) {
        echo json_encode(['exito' => false, 'mensaje_final' => "Faltan datos requeridos: cv = $cv, ca = $ca, ni = $ni"]);
        exit; // Se finaliza el script
    }
    $conn->begin_transaction(); // Se inicia la transacción
    try {
        // Ingresar la oferta (carrera)
        $stmtCarrera = $conn->prepare("INSERT INTO carreras (cv, ca, cN) VALUES (?, ?, ?)");
        $stmtCarrera->bind_param("sss", $cv, $ca, $ni);
        $stmtCarrera->execute();
        $stmtCarrera->close();
        // Ingresar acción a la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Registró la oferta: $ni en $ca";
        $fecha = date("Y-m-d");
        $hora = date("H:i:s");
        $stmtBita = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmtBita->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmtBita->execute();
        $stmtBita->close();
        $conn->commit(); // Se finaliza la transacción
        $respuesta['mensaje_final'] = "Oferta registrada correctamente";
    } catch (Exception $e) {
        $conn->rollback(); // Se revierten las consultas SQL para evitar inconsistencia con la información en la base de datos
        $respuesta['exito'] = false;
        $respuesta['mensaje_final'] = "Se produjo un error en la inserción: " . $e->getMessage();
    }
    echo json_encode($respuesta); // Se envía la respuesta del servidor
    $conn->close(); // Se cierra la conexión
    exit(); // Se finaliza el script
}
