<?php
session_start();
include('../../conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos recibidos del formulario
    $cv = isset($_POST['cvUsu']) ? trim($_POST['cvUsu']) : null;
    $nom = isset($_POST['empleado']) ? trim($_POST['empleado']) : null;
    $em = isset($_POST['em']) ? trim($_POST['em']) : null;
    $us = isset($_POST['usMod']) ? trim($_POST['usMod']) : null;
    $co = isset($_POST['coMod']) ? trim($_POST['coMod']) : null;
    $enviarCredenciales = isset($_POST['enviarCredenciales']) && $_POST['enviarCredenciales'] === '1';
    // Validar datos mínimos
    if (!$cv || !$nom || !$em || !$us || !$co) {
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
        $coEncrip = password_hash($co, PASSWORD_BCRYPT);
        $queryUsuarios = "UPDATE usuarios SET us = ?, co = ? WHERE cv = ?";
        $stmtUsuarios = $conn->prepare($queryUsuarios);
        if (!$stmtUsuarios) {
            throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
        }
        $stmtUsuarios->bind_param("ssi", $us, $coEncrip, $cv);
        $stmtUsuarios->execute();
        if ($stmtUsuarios->affected_rows === 0) {
            throw new Exception("No se pudo actualizar el usuario en la tabla de usuarios.");
        }
        $stmtUsuarios->close();
        // Enviar las credenciales si se solicita
        if ($enviarCredenciales) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'eanzures29@gmail.com';
                $mail->Password = 'axji aoae dsuw rrmo';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('eanzures29@gmail.com', 'UNIVERSIDAD INTERCONTINENTAL OLIMPO');
                $mail->addAddress($em, "$nom");
                $mail->isHTML(true);
                $mail->Subject = 'Modificacion de Credenciales de Acceso al Sistema SIGEAUIO';
                $mail->Body = "
                    <h1>Bienvenido al sistema</h1>
                    <p>Hola <b>$nom</b>,</p>
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
        // Registrar en la bitácora
        $clave = $_SESSION['clave'];
        $accion = "Modificó el usuario del empleado: " . $nom;
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje_final' => 'Usuario modificado correctamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje_final' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
