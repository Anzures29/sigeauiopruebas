<?php
session_start();
include('conexion.php');
$fecha = date('Y-m-d'); // Se obtiene la fecha actual
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
// Generar folio de inscripción
function generarFolioInscripcion($conn)
{
    $queryFolio = "SELECT fol FROM inscripciones ORDER BY fol DESC LIMIT 1"; // Consulta el último folio
    $result = $conn->query($queryFolio);
    if ($result && $row = $result->fetch_assoc()) {
        $ultimoFolio = intval(substr($row['fol'], 6)) + 1; // Extrae la numeración del último folio registrado y la incrementa
    } else {
        $ultimoFolio = 1; // Comienza desde 0001 si no hay registros previos
    }
    $folio = 23 . 'IUIO' . str_pad($ultimoFolio, 5, '0', STR_PAD_LEFT); // Formatear el número con ceros a la izquierda y añadir el prefijo
    return $folio;
}
// Generar número de control
function generarNumeroControl($conn)
{
    $anioActual = date("y"); // Últimos dos dígitos del año
    $queryNumControl = "SELECT nC FROM alumnos WHERE nC LIKE '{$anioActual}UIO%' ORDER BY nC DESC LIMIT 1"; // Consulta el último número de control del año actual
    $result = $conn->query($queryNumControl);
    if ($result && $row = $result->fetch_assoc()) {
        $ultimoNumControl = intval(substr($row['nC'], 6)) + 1; // Extrae la numeración del último número de control registrado y la incrementa
    } else {
        $ultimoNumControl = 1; // Comienza desde 0001 si no hay registros previos
    }
    $numeroControl = $anioActual . 'UIO' . str_pad($ultimoNumControl, 4, '0', STR_PAD_LEFT); // Formatear el número con ceros a la izquierda y añadir el prefijo del año
    return $numeroControl;
}
$folioInscripcion = generarFolioInscripcion($conn); // Se guarda el folio en una variable
$numeroControl = generarNumeroControl($conn); // Se guarda el numero de control en una variable
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleModales.css">
    <script>
        var userRole = "<?php echo $_SESSION['rol']; ?>"; // Rol del usuario
    </script>
</head>

<body>
    <div class="">
        <div class="contenido-principal">
            <!-- Encabezado -->
            <div class="superior">
                <button class="boton-accion" onclick="abrirModalInscribir()">Inscribir Alumno</button>
                <div>
                    <h2>Alumnos Inscritos</h2>
                </div>
                <div>
                    <?php if ($_SESSION['rol'] === 'Rector'): ?>
                        <button id="imprimir" class="boton-accion">PDF</button>
                    <?php endif; ?>
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
                            <th>Folio</th>
                            <th>Foto</th>
                            <th>Fecha</th>
                            <th>Alumno</th>
                            <th>Nivel</th>
                            <th>Carrera</th>
                            <th>Fecha de Inicio</th>
                            <th>Fecha de Término</th>
                            <th>Fecha de Pago</th>
                            <th>Periodo de Inicio</th>
                            <th>Periodo Actual</th>
                            <th>Inscripción</th>
                            <th>Colegiatura</th>
                            <th>Reinscripción</th>
                            <?php if ($_SESSION['rol'] == 'Rector'): ?>
                                <th></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tablaInscripciones">
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
    <!-- Modal Inscribir Alumno -->
    <div id="inscribirAlumno" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalInscribir()">&times;</span>
            <h1 class="modal-title">FICHA DE INSCRIPCIÓN</h1>
            <form id="formInscribir" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <!-- Datos de la Inscripción -->
                    <fieldset>
                        <legend class="modal-title">Datos de la Inscripción</legend>
                        <div>
                            <label>FOTO</label>
                            <input type="file" name="ft" id="ft" class="input-field" accept="image/png, image/jpeg" onchange="cargarImagenInscribir(event)" required>
                            <img id="fotoCargada" src="#" alt="Vista previa de la foto" style="display: none; max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 8px;">
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>FOLIO DE INSCRIPCIÓN</label>
                                <input type="text" name="fol" id="fol" class="input-field" value="<?php echo $folioInscripcion ?>" readonly>
                            </div>
                            <div class="input-item">
                                <label>FECHA</label>
                                <input type="text" name="fe" class="input-field" value="<?php echo $fecha ?>" readonly>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NÚMERO DE CONTROL</label>
                                <input type="text" name="nC" id="nC" class="input-field" value="<?php echo $numeroControl ?>" readonly>
                            </div>
                            <div class="input-item">
                                <label>MATRÍCULA</label>
                                <input type="text" name="ma" class="input-field" placeholder="Ingrese la matrícula del alumno" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div>
                            <label>NIVEL EDUCATIVO</label>
                            <select name="nivel" id="nivel" class="input-field" required>
                                <option value="">Seleccione</option>
                                <?php
                                // Consulta para obtener los niveles educativos
                                $consultaNiveles = "SELECT cv, ni FROM niveles";
                                $resultadoNiveles = $conn->query($consultaNiveles);
                                if ($resultadoNiveles && $resultadoNiveles->num_rows > 0) {
                                    while ($fila = $resultadoNiveles->fetch_assoc()) {
                                        $cv = htmlspecialchars($fila['cv'], ENT_QUOTES, 'UTF-8');
                                        $nivel = htmlspecialchars($fila['ni'], ENT_QUOTES, 'UTF-8');
                                        echo "<option value='$cv'>$nivel</option>"; // Clave como valor, nombre como texto
                                    }
                                } else {
                                    echo "<option value=''>No hay niveles disponibles</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>CARRERA</label>
                            <select name="carrera" id="carrera" class="input-field" required>
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>HORARIO DE CLASES</label>
                                <input type="text" name="ho" class="input-field" placeholder="Ingrese el horario del alumno" oninput="formatearHorario(event)" required>
                            </div>
                            <div class="input-item">
                                <label>DÍA DE CLASES</label>
                                <input type="text" name="di" class="input-field" placeholder="Ingrese día de clases del alumno" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>FECHA INICIO DE CLASES</label>
                                <input type="date" name="feI" id="feI" class="input-field" required>
                                <span id="errorFecha" style="color: red; display: none;">Favor de revisar la fecha de inicio de clases</span>
                            </div>
                            <div class="input-item">
                                <label>FECHA TÉRMINO DE CLASES</label>
                                <input type="date" name="feF" id="feF" class="input-field" required>
                            </div>
                            <div class="input-item">
                                <label>FECHA PAGO DE COLEGIATURA</label>
                                <input type="date" name="fePa" id="fePa" class="input-field" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>INSCRIPCIÓN</label>
                                <input type="number" name="costo" class="input-field" placeholder="Inscripción" required>
                            </div>
                            <div class="input-item">
                                <label>COLEGIATURA</label>
                                <input type="number" name="cole" class="input-field" placeholder="Colegiatura" required>
                            </div>
                            <div class="input-item">
                                <label>REINSCRIPCION</label>
                                <input type="number" name="rein" class="input-field" placeholder="Reinscripción" required>
                            </div>
                        </div>
                    </fieldset>
                    <!-- DATOS PERSONALES -->
                    <fieldset>
                        <legend class="modal-title">Datos Personales</legend>
                        <div class="input-group">
                            <div class="input-item">
                                <label>APELLIDO PATERNO</label>
                                <input type="text" name="aP" class="input-field" placeholder="Ingrese el apellido paterno" onkeyup="mayus(this);" required>
                            </div>
                            <div class="input-item">
                                <label>APELLIDO MATERNO</label>
                                <input type="text" name="aM" class="input-field" placeholder="Ingrese el apellido materno" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div>
                            <label>NOMBRE(S)</label>
                            <input type="text" name="nom" class="input-field" placeholder="Ingrese el nombre" onkeyup="mayus(this);" required>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>LUGAR DE NACIMIENTO</label>
                                <select name="lugarNacimiento" class="input-field" required>
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
                                <input type="date" name="fechaNacimiento" id="fechaNacimiento" class="input-field" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>EDAD</label>
                                <input type="number" name="ed" id="ed" class="input-field" placeholder="Ingrese la edad" readonly>
                            </div>
                            <div class="input-item">
                                <label>CURP</label>
                                <input type="text" name="cu" class="input-field" maxlength="18" pattern="[A-Z0-9]{18}" placeholder="Ingrese la curp" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>SEXO</label>
                                <select name="se" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>TIPO DE SANGRE</label>
                                <select name="ts" class="input-field">
                                    <option value="">Seleccione</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ALERGÍAS O ENFERMEDADES</label>
                                <input type="text" name="af" class="input-field" placeholder="Ingrese las alergías o enfermedades" onkeyup="mayus(this);">
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estadoAlumno" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipioAlumno" id="municipioAlumno" class="input-field" required>
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
                                <select name="coloniaAlumno" id="coloniaAlumno" class="input-field" required>
                                    <option>Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="ca" class="input-field" placeholder="Ingrese la calle" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NÚMERO DE CELULAR</label>
                                <input type="tel" name="te" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular" required>
                            </div>
                            <div class="input-item">
                                <label>CORREO</label>
                                <input type="email" name="em" class="input-field" placeholder="Ingrese el correo" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                    </fieldset>
                    <!-- DATOS DE LA ESCUELA DE PROCEDENCIA -->
                    <fieldset>
                        <legend class="modal-title">Datos de la Escuela de Procedencia</legend>
                        <div>
                            <label>CCT</label>
                            <select name="cct" id="cct" class="input-field" onchange="updateEscuela()">
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
                            <select name="escuela" id="escuela" class="input-field" onchange="updateCCT()">
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
                                <input type="text" name="ge" class="input-field" pattern="^\d{4}-\d{4}$" placeholder="Ingrese con el formato YYYY-YYYY">
                            </div>
                            <div class="input-item">
                                <label>PROMEDIO</label>
                                <input type="number" name="pr" class="input-field" min="0" max="10" step="0.01" placeholder="Ingrese con el formato 0.00">
                            </div>
                        </div>
                    </fieldset>
                    <!-- DATOS DEL TUTOR -->
                    <fieldset>
                        <legend class="modal-title">Contacto de Emergencia/Tutor</legend>
                        <div>
                            <label>CURP</label>
                            <input type="text" name="curpTutor" class="input-field" maxlength="18" pattern="[A-Z0-9]{18}" placeholder="Ingrese la CURP" onkeyup="mayus(this);">
                        </div>
                        <div>
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" name="nomTutor" class="input-field" placeholder="Ingrese el nombre completo" onkeyup="mayus(this);">
                        </div>
                        <div>
                            <label>PARENTESCO</label>
                            <select name="pa" class="input-field">
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
                            <input type="tel" name="teTutor" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular">
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estadoTutor" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipioTutor" id="municipioTutor" class="input-field">
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
                                <select name="coloniaTutor" id="coloniaTutor" class="input-field">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="calleTutor" class="input-field" placeholder="Ingrese la calle" onkeyup="mayus(this);">
                            </div>
                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnInscribir">Inscribir Alumno</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Modificar Inscripción (Solo datos de la inscripción) -->
    <div id="modificarInscripcion" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalModificar()">&times;</span>
            <h1 class="modal-title">MODIFICAR DATOS DE LA INSCRIPCION</h1>
            <form id="formModificarInscripcion" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <fieldset>
                        <legend class="modal-title">Modificar Inscripción</legend>
                        <div>
                            <label>FOTO</label>
                            <input type="file" name="ftMod" id="ftMod" class="input-field" accept="image/png, image/jpeg" onchange="cargarImagenModificar(event)">
                            <img id="fotoCargadaMod" src="" alt="Vista previa de la foto" style="display: none; max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 8px;">
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>FOLIO DE INSCRIPCIÓN</label>
                                <input type="text" name="folMod" id="folMod" class="input-field" title="No se puede modificar" readonly>
                            </div>
                            <div class="input-item">
                                <label>FECHA DE INSCRIPCIÓN</label>
                                <input type="text" name="feMod" id="feMod" class="input-field" title="No se puede modificar" readonly>
                            </div>
                        </div>
                        <div>
                            <input type="hidden" name="nCAct" id="nCAct">
                            <label>ALUMNO</label>
                            <input type="text" name="alumnoMod" id="alumnoMod" class="input-field" title="Los datos del alumno se modifican en el módulo información general" readonly>
                        </div>
                        <div>
                            <label>NIVEL EDUCATIVO</label>
                            <input type="text" name="nivelMod" id="nivelMod" class="input-field" title="No se puede modificar" readonly>
                        </div>
                        <div>
                            <label>CARRERA</label>
                            <select name="carreraMod" id="carreraMod" class="input-field">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>FECHA INICIO DE CLASES</label>
                                <input type="date" name="feIMod" id="feIMod" class="input-field" required>
                                <span id="errorFechaMod" style="color: red; display: none;">Favor de revisar la fecha de inicio de clases</span>
                            </div>
                            <div class="input-item">
                                <label>FECHA TÉRMINO DE CLASES</label>
                                <input type="date" name="feFMod" id="feFMod" class="input-field" required>
                            </div>
                            <div class="input-item">
                                <label>FECHA PAGO DE COLEGIATURA</label>
                                <input type="date" name="fePaMod" id="fePaMod" class="input-field" required>
                            </div>
                        </div>
                        <div>
                            <label>PERIODO</label>
                            <input type="text" name="peMod" id="peMod" class="input-field" title="No se puede modificar" readonly>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <input type="hidden" name="cosAct" id="cosAct">
                                <label>COSTO</label>
                                <input type="text" name="costoMod" id="costoMod" class="input-field" placeholder="Costo inscripción" required>
                            </div>
                            <div class="input-item">
                                <input type="hidden" name="colAct" id="colAct">
                                <label>COLEGIATURA</label>
                                <input type="text" name="coleMod" id="coleMod" class="input-field" placeholder="Mensualidad" required>
                            </div>
                            <div class="input-item">
                                <input type="hidden" name="reiAct" id="reiAct">
                                <label>REINSCRIPCIÓN</label>
                                <input type="text" name="reinMod" id="reinMod" class="input-field" placeholder="Costo reinscripción" required>
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
    <script src="modales/inscripciones/script.js"></script>
    <script src="modulos/script.js"></script>
</body>

</html>