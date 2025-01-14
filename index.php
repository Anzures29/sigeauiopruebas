<?php
include 'config.php'; // Configuraciones de seguridad para las cookies
// Si el usuario ya está logueado, se destruye la sesión y se redirige al login
if (isset($_SESSION['usuario'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
include 'conexion.php'; // Conexión a la base de datos
$_SESSION['token'] = bin2hex(random_bytes(32)); // Genera un token único
// Verifica si se ha enviado el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $us = $_POST["us"];
    $co = $_POST["co"];
    // Consulta para verificar usuario
    $stmt = $conn->prepare("SELECT usuarios.cv, usuarios.us, usuarios.co, usuarios.co AS password, roles.rol AS rol 
                            FROM usuarios 
                            INNER JOIN roles ON usuarios.cR = roles.cv 
                            WHERE usuarios.us = ?");
    $stmt->bind_param("s", $us);
    $stmt->execute();
    $result = $stmt->get_result();
    // Verifica si se encontró el usuario
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Compara la contraseña ingresada con la almacenada (encriptada)
        if (password_verify($co, $row['password'])) {
            // Contraseña correcta
            $_SESSION['usuario'] = $row['us'];
            $_SESSION['rol'] = $row['rol'];
            $_SESSION['clave'] = $row['cv'];
            // Ingresar acción a la bitácora
            $clave = $_SESSION['clave'];
            $accion = "Inicio de Sesión";
            $fecha = date("Y-m-d");
            // $fecha = date("2025-03-01");
            $hora = date("H:i:s");
            // Prepara la consulta de inserción en la bitácora
            $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
            if ($stmt_bitacora) {
                $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
                if (!$stmt_bitacora->execute()) {
                    echo "Error al insertar en la bitácora: " . $stmt_bitacora->error;
                }
                // Cierra la sentencia de bitácora
                $stmt_bitacora->close();
            } else {
                echo "Error en la preparación de la consulta de bitácora: " . $conn->error;
            }
            // Redirige al usuario al index.php
            header("Location: principal.php");
            exit();
        } else {
            echo "Nombre de usuario o contraseña incorrectos.";
            // Aquí podrías registrar el intento fallido, si lo deseas
        }
    } else {
        echo "Nombre de usuario no encontrado.";
    }
    // Cierra la consulta de verificación
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleLogin.css">
    <link rel="icon" href="/xampp/htdocs/favicon.ico" type="image/x-icon">
    <title>Inicio de Sesión</title>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-in">
            <form action="index.php" method="POST">
                <h1>Inicio de Sesión</h1>
                <br>
                <br>
                <label for="username">Usuario</label>
                <input type="text" name="us" placeholder="Ingrese su usuario" required>
                <label for="password">Contraseña</label>
                <input type="password" name="co" placeholder="Ingrese su contraseña" required>
                <br>
                <br>
                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>
        <div class="img-container">
            <div class="img">
                <img src="img/logo.png">
            </div>
        </div>
    </div>
</body>

</html>