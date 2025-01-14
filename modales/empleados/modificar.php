<?php
session_start();
include('../../conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar datos requeridos
    $cv = isset($_POST['cvMod']) ? trim($_POST['cvMod']) : null;
    $nom = isset($_POST['nomMod']) ? trim($_POST['nomMod']) : null;
    $aP = isset($_POST['aPMod']) ? trim($_POST['aPMod']) : null;
    $aM = isset($_POST['aMMod']) ? trim($_POST['aMMod']) : null;
    $te = isset($_POST['teMod']) ? trim($_POST['teMod']) : null;
    $em = isset($_POST['emMod']) ? trim($_POST['emMod']) : null;
    $correoActual = isset($_POST['correoActual']) ? trim($_POST['correoActual']) : null;
    $ca = isset($_POST['caMod']) ? trim($_POST['caMod']) : null;
    $cCo = isset($_POST['coloniaMod']) ? trim($_POST['coloniaMod']) : null;
    $cR = isset($_POST['rolMod']) ? trim($_POST['rolMod']) : null;
    $su = isset($_POST['suMod']) ? trim($_POST['suMod']) : null;
    $actualizarUsuario = isset($_POST['actualizarUsuario']) && $_POST['actualizarUsuario'] === '1';
    // Validar datos mínimos
    if (!$nom || !$aP || !$aM || !$te || !$em || !$ca || !$cR || !$su || !$correoActual) {
        echo json_encode(['exito' => false, 'mensaje_final' => 'Faltan datos obligatorios.']);
        exit;
    }
    // Validar correo electrónico
    if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Correo electrónico no válido: $em");
    }
    // Iniciar transacción
    $conn->begin_transaction();
    try {
        $mensaje_final = "Datos del Empleado Modificados Correctamente.";
        // Verificar si el correo ha cambiado y el usuario desea actualizar
        if ($correoActual !== $em && $actualizarUsuario) {
            $us = strstr($em, '@', true);
            $co = bin2hex(random_bytes(4));
            $coEncrip = password_hash($co, PASSWORD_BCRYPT);
            $queryUsuarios = "UPDATE usuarios SET us = ?, co = ?, cR = ? WHERE cE = ?";
            $stmtUsuarios = $conn->prepare($queryUsuarios);
            if (!$stmtUsuarios) {
                throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
            }
            $stmtUsuarios->bind_param("ssii", $us, $coEncrip, $cR, $cv);
            $stmtUsuarios->execute();
            if ($stmtUsuarios->affected_rows === 0) {
                throw new Exception("No se pudo actualizar el usuario en la tabla de usuarios.");
            }
            $stmtUsuarios->close();
            // Configuración de PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP de Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'eanzures29@gmail.com';  // Coloca tu dirección de Gmail
                $mail->Password = 'axji aoae dsuw rrmo';  // Coloca la contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                // Configuración del correo
                $mail->setFrom('eanzures29@gmail.com', 'UNIVERSIDAD INTERCONTINENTAL OLIMPO');
                $mail->addAddress($em, "$nom $aP");  // Dirección del destinatario
                $mail->isHTML(true);
                $mail->Subject = 'Modificacion de Credenciales de Acceso al Sistema SIGEAUIO';
                $mail->Body = "
                <h1>Bienvenido al sistema</h1>
                <p>Hola <b>$nom $aP</b>,</p>
                <p>Estas son tus nuevas credenciales para acceder al sistema:</p>
                <ul>
                    <li><b>Usuario:</b> $us</li>
                    <li><b>Contraseña:</b> $co</li>
                </ul>";
                $mail->AltBody = "Usuario: $us\nContraseña: $co\n";
                $mail->send();
                $respuesta['mensaje_final'] = 'Empleado registrado y credenciales enviadas correctamente.';
            } catch (Exception $e) {
                $respuesta['mensaje_final'] = 'Empleado registrado, pero no se pudo enviar el correo de credenciales.';
                error_log("Error de PHPMailer: " . $mail->ErrorInfo);
            }
        }
        // Actualizar los datos del alumno
        $query = "UPDATE empleados SET nom = ?, aP = ?, aM = ?, te = ?, em = ?, ca = ?, cR = ?, su = ? ";
        $types = "ssssssss";
        $params = [$nom, $aP, $aM, $te, $em, $ca, $cR, $su];
        // Agregar cCo si está presente
        if ($cCo) {
            $query .= ", cCo = ?";
            $types .= "s";
            $params[] = $cCo;
        }
        $query .= " WHERE cv = ?"; // Condición
        $types .= "s";
        $params[] = $cv;
        $stmtEmpleado = $conn->prepare($query); // Preparar la consulta
        $stmtEmpleado->bind_param($types, ...$params); // Vincular los parámetros dinámicamente
        $stmtEmpleado->execute(); // Ejecutar la consulta
        $stmtEmpleado->close(); // Cerrar la consulta preparada
        // Registrar acción en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Modificó los datos del empleado $nom";
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
