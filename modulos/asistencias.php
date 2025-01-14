<?php
include('conexion.php');
$fecha = date('2025-01-14'); // Se obtiene la fecha actual
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleModales.css">
</head>

<body>
    <div class="contenido-principal">
        <div class="superiorSolo">
            <h2>Control de Asistencias de Alumnos</h2>
        </div>
        <div class="busqueda">
            <div class="busqueda-items">
                <div class="busqueda-item">
                    <label for="nivel">Nivel Educativo</label>
                    <select name="nivel" id="nivel" class="busqueda-field" required>
                        <option value="">Seleccione</option>
                        <?php
                        $consultaNiveles = "SELECT cv, ni FROM niveles";
                        $resultadoNiveles = $conn->query($consultaNiveles);
                        if ($resultadoNiveles && $resultadoNiveles->num_rows > 0) {
                            while ($fila = $resultadoNiveles->fetch_assoc()) {
                                $cv = htmlspecialchars($fila['cv'], ENT_QUOTES, 'UTF-8');
                                $nivel = htmlspecialchars($fila['ni'], ENT_QUOTES, 'UTF-8');
                                echo "<option value='$cv'>$nivel</option>";
                            }
                        } else {
                            echo "<option value=''>No hay niveles disponibles</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="busqueda-item">
                    <label for="carrera">Carrera</label>
                    <select name="carrera" id="carrera" class="busqueda-field" required>
                        <option value="">Seleccione</option>
                    </select>
                </div>
                <div class="busqueda-item">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="busqueda-field" value="<?php echo $fecha ?>">
                </div>
            </div>
        </div>
        <div class="tabla-responsive">
            <table class="tabla tabla-strip tabla-hover tabla-bordeada">
                <thead class="tabla-oscura">
                    <tr>
                        <th>Número de Control</th>
                        <th>Nombre del Alumno</th>
                        <th>Asistencia</th>
                    </tr>
                </thead>
                <tbody id="tablaAsistencias">
                </tbody>
            </table>
        </div>
        <br>
        <div class="registrar-asistencia">
            <button id="registrarBtn" class="boton-accion">Registrar Asistencia</button>
        </div>
    </div>

    <script src="modales/asistencias/script.js"></script>
</body>

</html>