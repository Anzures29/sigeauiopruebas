<?php
session_start();
include('conexion.php');
// Consulta para los municipios
$conMunicipios = "SELECT cv, mu FROM municipios";
$resMunicipios = $conn->query($conMunicipios);
$municipios = [];
if ($resMunicipios->num_rows > 0) {
    while ($fila = $resMunicipios->fetch_assoc()) {
        $municipios[] = $fila;
    }
}
// Consulta para obtener CCT, escuela y calle
$conCCT = "SELECT cct, es, ca FROM cctescuelas ORDER BY es ASC";
$resCCT = $conn->query($conCCT);
$ccts = [];
if ($resCCT->num_rows > 0) {
    while ($fila = $resCCT->fetch_assoc()) {
        $ccts[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleModales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        var userRole = "<?php echo $_SESSION['rol']; ?>"; // Rol del usuario
    </script>
</head>

<body>
    <div class="">
        <div class="contenido-principal">
            <!-- Encabezado -->
            <div class="superiorSolo">
                <div>
                    <h2>Información de Alumnos</h2>
                </div>
            </div>
            <!-- Filtros -->
            <div class="filtros">
                <!-- Filtro para seleccionar la cantidad de registros a mostrar -->
                <div class="filtro-registros">
                    <label for="registros">Mostrar</label>
                    <select id="registros" class="selector-filtro">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label>registros</label>
                </div>
                <!-- Buscador de registros -->
                <input type="text" id="buscar" placeholder="Buscar..." class="input-busqueda">
            </div>
            <!-- Tabla de datos -->
            <div class="tabla-responsive">
                <table class="tabla tabla-strip tabla-hover tabla-bordeada">
                    <thead class="tabla-oscura">
                        <tr>
                            <th>Número de Control</th>
                            <th>Matrícula</th>
                            <th>Nombre</th>
                            <th>Lugar de Nacimiento</th>
                            <th>Fecha de Nacimiento</th>
                            <th>CURP</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tablaAlumnos">
                        <!-- Aquí se cargan los datos -->
                    </tbody>
                </table>
            </div>
            <!-- Botones de paginación -->
            <div class="paginacion">
                <div id="cantidad-registros" style="margin-top: 10px;"></div>
                <div>
                    <button id="anterior" class="boton-accion">Anterior</button>
                    <span id="pageInfo"></span>
                    <button id="siguiente" class="boton-accion">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Visualizar Alumno -->
    <div id="visualizar" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalVisualizar()">&times;</span>
            <h1 class="modal-title">INFORMACIÓN GENERAL</h1>
            <div id="documentosContainer" class="form-group">
                <!-- DATOS PERSONALES -->
                <fieldset>
                    <legend class="modal-title">Datos Personales</legend>
                    <div class="input-group">
                        <div class="input-item">
                            <label>APELLIDO PATERNO</label>
                            <input type="text" id="aPVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>APELLIDO MATERNO</label>
                            <input type="text" id="aMVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>NOMBRE(S)</label>
                            <input type="text" id="nomVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>MATRÍCULA</label>
                            <input type="text" id="maVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>LUGAR DE NACIMIENTO</label>
                            <input type="text" id="lugarNacimientoVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="text" id="fechaNacimientoVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>EDAD</label>
                            <input type="text" id="edVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>CURP</label>
                            <input type="text" id="cuVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>SEXO</label>
                            <input type="text" id="seVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>TIPO DE SANGRE</label>
                            <input type="text" id="tsVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>ALERGÍAS O ENFERMEDADES</label>
                            <input type="text" id="afVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>MUNICIPIO</label>
                            <input type="text" id="municipioAlumnoVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>COLONIA</label>
                            <input type="text" id="coloniaAlumnoVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>CALLE</label>
                            <input type="text" id="caVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>NÚMERO DE CELULAR</label>
                            <input type="text" id="teVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>CORREO</label>
                            <input type="text" id="emVis" class="input-field" readonly>
                        </div>
                    </div>
                </fieldset>
                <!-- DATOS DE LA ESCUELA DE PROCEDENCIA -->
                <fieldset>
                    <legend class="modal-title">Datos de la Escuela de Procedencia</legend>
                    <div>
                        <label>CCT</label>
                        <input type="text" id="cctVis" class="input-field" readonly>
                    </div>
                    <div>
                        <label>ESCUELA</label>
                        <input type="text" id="escuelaVis" class="input-field" readonly>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>GENERACIÓN</label>
                            <input type="text" id="geVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>PROMEDIO</label>
                            <input type="text" id="prVis" class="input-field" readonly>
                        </div>
                    </div>
                </fieldset>
                <!-- DATOS DEL TUTOR -->
                <fieldset>
                    <legend class="modal-title">Contacto de Emergencia/Tutor</legend>
                    <div>
                        <label>CURP</label>
                        <input type="text" id="curpTutorVis" class="input-field" readonly>
                    </div>
                    <div>
                        <label>NOMBRE COMPLETO</label>
                        <input type="text" id="nomTutorVis" class="input-field" readonly>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>PARENTESCO</label>
                            <input type="text" id="paVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>NÚMERO DE CELULAR</label>
                            <input type="text" id="teTutorVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>MUNICIPIO</label>
                            <input type="text" id="municipioTutorVis" class="input-field" readonly>
                        </div>
                        <div class="input-item">
                            <label>COLONIA</label>
                            <input type="text" id="coloniaTutorVis" class="input-field" readonly>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-item">
                            <label>CALLE</label>
                            <input type="text" id="calleTutorVis" class="input-field" readonly>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <!-- Modal Modificar Alumno -->
    <div id="modificar" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalModificar()">&times;</span>
            <h1 class="modal-title">MODIFICAR LA INFORMACIÓN DEL ALUMNO</h1>
            <form id="formModificar" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <!-- DATOS PERSONALES -->
                    <fieldset>
                        <legend class="modal-title">Datos Personales</legend>
                        <div class="input-group">
                            <div class="input-item">
                                <input type="hidden" name="nC" id="nC">
                                <label>APELLIDO PATERNO</label>
                                <input type="text" name="aPMod" id="aPMod" class="input-field" placeholder="Ingrese el apellido paterno" onkeyup="mayus(this);" required>
                            </div>
                            <div class="input-item">
                                <label>APELLIDO MATERNO</label>
                                <input type="text" name="aMMod" id="aMMod" class="input-field" placeholder="Ingrese el apellido materno" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NOMBRE(S)</label>
                                <input type="text" name="nomMod" id="nomMod" class="input-field" placeholder="Ingrese el nombre" onkeyup="mayus(this);" required>
                            </div>
                            <div class="input-item">
                                <label>MATRÍCULA</label>
                                <input type="text" name="maMod" id="maMod" class="input-field" placeholder="Ingrese la matrícula" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>LUGAR DE NACIMIENTO</label>
                                <select name="lugarNacimientoMod" id="lugarNacimientoMod" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?php echo $municipio['cv']; ?>">
                                            <?php echo $municipio['mu']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>FECHA DE NACIMIENTO</label>
                                <input type="date" name="fechaNacimientoMod" id="fechaNacimientoMod" class="input-field" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>EDAD</label>
                                <input type="number" name="edMod" id="edMod" class="input-field" placeholder="Ingrese la edad" readonly>
                            </div>
                            <div class="input-item">
                                <label>CURP</label>
                                <input type="text" name="cuMod" id="cuMod" class="input-field" maxlength="18" pattern="[A-Z0-9]{18}" placeholder="Ingrese la curp" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>SEXO</label>
                                <select name="seMod" id="seMod" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>TIPO DE SANGRE</label>
                                <select name="tsMod" id="tsMod" class="input-field">
                                    <option value="">Seleccione</option>
                                    <?php
                                    $tiposSangre = array("A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-");
                                    foreach ($tiposSangre as $tipo) {
                                        echo "<option value='$tipo'>$tipo</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ALERGÍAS O ENFERMEDADES</label>
                                <input type="text" name="afMod" id="afMod" class="input-field" placeholder="Ingrese las alergías o enfermedades" onkeyup="mayus(this);">
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estadoAlumno" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipioAlumnoMod" id="municipioAlumnoMod" class="input-field">
                                    <option value="">Seleccione</option>
                                    <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?php echo $municipio['cv']; ?>">
                                            <?php echo $municipio['mu']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>COLONIA</label>
                                <!-- <input type="text" id="coloniaAlumnoMod"> -->
                                <select name="coloniaAlumnoMod" id="coloniaAlumnoMod" class="input-field">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="caMod" id="caMod" class="input-field" placeholder="Ingrese la calle" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NÚMERO DE CELULAR</label>
                                <input type="tel" name="teMod" id="teMod" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular" required>
                            </div>
                            <div class="input-item">
                                <label>CORREO</label>
                                <input type="email" name="emMod" id="emMod" class="input-field" placeholder="Ingrese el correo" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                    </fieldset>
                    <!-- DATOS DE LA ESCUELA DE PROCEDENCIA -->
                    <fieldset>
                        <legend class="modal-title">Datos de la Escuela de Procedencia</legend>
                        <div>
                            <label>CCT</label>
                            <select name="cctMod" id="cctMod" class="input-field" onchange="updateEscuela()">
                                <option value="">Seleccione</option>
                                <?php foreach ($ccts as $cct): ?>
                                    <option value="<?php echo $cct['cct']; ?>" data-escuela="<?php echo htmlspecialchars($cct['es']); ?>">
                                        <?php echo $cct['cct']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>ESCUELA</label>
                            <select name="escuelaMod" id="escuelaMod" class="input-field" onchange="updateCCT()">
                                <option value="">Seleccione</option>
                                <?php foreach ($ccts as $cct): ?>
                                    <option value="<?php echo htmlspecialchars($cct['es']); ?>" data-cct="<?php echo $cct['cct']; ?>">
                                        <?php echo htmlspecialchars($cct['es']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>GENERACIÓN</label>
                                <input type="text" name="geMod" id="geMod" class="input-field" pattern="^\d{4}-\d{4}$" placeholder="Ingrese con el formato YYYY-YYYY">
                            </div>
                            <div class="input-item">
                                <label>PROMEDIO</label>
                                <input type="number" name="prMod" id="prMod" class="input-field" min="0" max="10" step="0.01" placeholder="Ingrese con el formato 0.00">
                            </div>
                        </div>
                    </fieldset>
                    <!-- DATOS DEL TUTOR -->
                    <fieldset>
                        <legend class="modal-title">Contacto de Emergencia/Tutor</legend>
                        <div>
                            <label>CURP</label>
                            <input type="text" name="curpTutorMod" id="curpTutorMod" class="input-field" maxlength="18" pattern="[A-Z0-9]{18}" placeholder="Ingrese la CURP" onkeyup="mayus(this);">
                        </div>
                        <div>
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" name="nomTutorMod" id="nomTutorMod" class="input-field" placeholder="Ingrese el nombre completo" onkeyup="mayus(this);">
                        </div>
                        <div>
                            <label>PARENTESCO</label>
                            <select name="paMod" id="paMod" class="input-field">
                                <option value="">Seleccione</option>
                                <?php
                                $cargos = array("Hijo", "Sobrino", "Hermano", "Hermana", "Nieto", "Primo", "Padrastro", "Madrastra", "Padre", "Madre", "Tío", "Tía", "Abuelo", "Abuela", "Tutor Legal", "Cuñado", "Cuñada");
                                foreach ($cargos as $cargo) {
                                    echo "<option value='$cargo'>$cargo</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>NÚMERO DE CELULAR</label>
                            <input type="tel" name="teTutorMod" id="teTutorMod" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular">
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estadoTutor" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipioTutorMod" id="municipioTutorMod" class="input-field">
                                    <option value="">Seleccione</option>
                                    <?php foreach ($municipios as $municipio): ?>
                                        <option value="<?php echo $municipio['cv']; ?>">
                                            <?php echo $municipio['mu']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>COLONIA</label>
                                <select name="coloniaTutorMod" id="coloniaTutorMod" class="input-field">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="calleTutorMod" id="calleTutorMod" class="input-field" placeholder="Ingrese la calle" onkeyup="mayus(this);">
                            </div>
                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnModificar">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para manejar el modal y la tabla con AJAX -->
    <script src="modales/alumnos/script.js"></script>
    <script src="modulos/script.js"></script>

</body>

</html>