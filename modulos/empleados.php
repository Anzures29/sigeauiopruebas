<?php
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
// Consulta para los roles
$conRoles = "SELECT cv, rol FROM roles";
$resRoles = $conn->query($conRoles);
$roles = [];
if ($resRoles->num_rows > 0) {
    while ($fila = $resRoles->fetch_assoc()) {
        $roles[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleModales.css">
</head>

<body>
    <div class="">
        <div class="contenido-principal">
            <!-- Encabezado -->
            <div class="superior">
                <button class="boton-accion" onclick="abrirModalRegistrar()">Dar de Alta</button>
                <div>
                    <h2>Empleados de la Universidad</h2>
                </div>
                <div>
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
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Teléfono</th>
                            <th>Correo Electrónico</th>
                            <th>Calle</th>
                            <th>Colonia</th>
                            <th>Puesto</th>
                            <th>Sueldo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tablaEmpleados">
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

    <!-- Modal Registrar Pagos -->
    <div id="registrar" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalRegistrar()">&times;</span>
            <h1 class="modal-title">REGISTRO DE EMPLEADO</h1>
            <form id="formRegistrarEmpleado" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <!-- DATOS DEL EMPLEADO -->
                    <fieldset>
                        <legend class="modal-title">Datos del Empleado</legend>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NOMBRE</label>
                                <input type="text" name="nom" class="input-field" placeholder="Ingrese el nombre del empleado" onkeyup="mayus(this);" required>
                            </div>
                        </div>
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
                        <div class="input-group">
                            <div class="input-item">
                                <label>NÚMERO DE CELULAR</label>
                                <input type="tel" name="te" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular" required>
                            </div>
                            <div class="input-item">
                                <label>CORREO</label>
                                <input type="email" name="em" class="input-field" placeholder="Ingrese el correo electrónico" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estado" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipio" id="municipio" class="input-field" required>
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
                                <select name="colonia" id="colonia" class="input-field" required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="ca" class="input-field" placeholder="Ingrese el domicilio" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>PUESTO</label>
                                <select name="rol" id="rol" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['cv']; ?>">
                                            <?php echo $rol['rol']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>SUELDO</label>
                                <input type="number" name="su" class="input-field" step="0.01" placeholder="Ingrese con el formato 0.00" required>

                                <!-- <input type="text" name="su" class="input-field" pattern="^\d+(\.\d{1,2})?$" placeholder="Ingrese con el formato 0.00" required> -->
                            </div>

                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnRegistrarEmpleado">Registrar Empleado</button>
                    </div>
                </div>
            </form>
            <div id="mensaje"></div>
        </div>
    </div>

    <!-- Modal Modificar Empleado -->
    <div id="modificar" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalModificar()">&times;</span>
            <h1 class="modal-title">MODIFICAR DATOS DEL EMPLEADO</h1>
            <form id="formModificar" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <!-- DATOS DEL EMPLEADO -->
                    <fieldset>
                        <legend class="modal-title">Datos del Empleado</legend>
                        <input type="hidden" id="cvMod" name="cvMod">
                        <div class="input-group">
                            <div class="input-item">
                                <label>NOMBRE</label>
                                <input type="text" name="nomMod" id="nomMod" class="input-field" placeholder="Ingrese el nombre del empleado" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
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
                                <label>NÚMERO DE CELULAR</label>
                                <input type="tel" name="teMod" id="teMod" class="input-field" pattern="^[0-9]{10}$" maxlength="10" placeholder="Ingrese el número de celular" required>
                            </div>
                            <div class="input-item">
                                <input type="hidden" id="correoActual" name="correoActual">
                                <label>CORREO</label>
                                <input type="email" name="emMod" id="emMod" class="input-field" onkeyup="mayus(this);" placeholder="Ingrese el correo electrónico" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>ESTADO</label>
                                <input type="text" name="estado" class="input-field" value="Tabasco" readonly>
                            </div>
                            <div class="input-item">
                                <label>MUNICIPIO</label>
                                <select name="municipioMod" id="municipioMod" class="input-field" required>
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
                                <select name="coloniaMod" id="coloniaMod" class="input-field">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CALLE</label>
                                <input type="text" name="caMod" id="caMod" class="input-field" onkeyup="mayus(this);" placeholder="Ingrese el domicilio" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>PUESTO</label>
                                <select name="rolMod" id="rolMod" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['cv']; ?>">
                                            <?php echo $rol['rol']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>SUELDO</label>
                                <input type="number" name="suMod" id="suMod" class="input-field" step="0.01" placeholder="Ingrese con el formato 0.00" required>
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
    <script src="modales/empleados/script.js"></script>
    <script src="modulos/script.js"></script>

</body>

</html>