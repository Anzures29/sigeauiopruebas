<?php
session_start();
include('../../conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $aP = trim($_POST['aP']);
    $aM = trim($_POST['aM']);
    $te = trim($_POST['te']);
    $em = trim($_POST['em']);
    $cCo = trim($_POST['colonia']);
    $ca = trim($_POST['ca']);
    $cR = trim($_POST['rol']);
    $su = trim($_POST['su']);
    $respuesta = ['exito' => true, 'mensaje_final' => 'Empleado registrado correctamente'];
    try {
        // Validar correo electrónico
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Correo electrónico no válido: $em");
        }
        // Iniciar transacción
        $conn->begin_transaction();
        // Insertar empleado
        $stmtEmpleados = $conn->prepare("INSERT INTO empleados (nom, aP, aM, te, em, ca, cCo, cR, su) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtEmpleados->bind_param("ssssssssd", $nom, $aP, $aM, $te, $em, $ca, $cCo, $cR, $su);
        if ($stmtEmpleados->execute()) {
            $cE = $conn->insert_id;
            // Generar usuario y contraseña
            // Obtener la parte del correo antes del "@" para crear el usuario
            $us = strstr($em, '@', true);  // Esto obtiene todo lo que está antes del "@"
            $co = bin2hex(random_bytes(4));
            $coEncrip = password_hash($co, PASSWORD_BCRYPT);
            // Insertar usuario
            $stmtUsuarios = $conn->prepare("INSERT INTO usuarios (us, co, cR, cE) VALUES (?, ?, ?, ?)");
            $stmtUsuarios->bind_param("ssii", $us, $coEncrip, $cR, $cE);
            $stmtUsuarios->execute();
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
                $mail->Subject = 'Credenciales de acceso al sistema';
                $mail->Body = "
                    <h1>Bienvenido al sistema</h1>
                    <p>Hola <b>$nom $aP</b>,</p>
                    <p>Estas son tus credenciales para acceder al sistema:</p>
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
            // Ingresar acción a la bitácora
            $clave = $_SESSION['clave'];
            $accion = "Registró al empleado " . $nom . " " . $aP . " " . $aM;
            $fecha = date("Y-m-d");
            $hora = date("H:i:s");
            $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
            $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
            $stmt_bitacora->execute();
            $stmt_bitacora->close();
        } else {
            throw new Exception("Error al insertar en la tabla empleados: " . $stmtEmpleados->error);
        }
        $stmtEmpleados->close();
        // Confirmar transacción final
        $conn->commit();
        error_log("Transacción confirmada exitosamente.");
    } catch (Exception $e) {
        $conn->rollback();
        $respuesta['exito'] = false;
        $respuesta['mensaje_final'] = "Favor de revisar los datos ingresados.";
        error_log("Error en la inserción: " . $e->getMessage());
    }
    // Enviar respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
    $conn->close();
    exit();
}
