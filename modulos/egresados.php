<?php
session_start();
include('conexion.php');
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
                <div></div>
                <div>
                    <h2>Alumnos Egresados</h2>
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
                            <th>Folio de la inscripción</th>
                            <th>Número de Control</th>
                            <th>Foto</th>
                            <th>Alumno</th>
                            <th>Nivel</th>
                            <th>Carrera</th>
                            <th>Fecha de Ingreso</th>
                            <th>Fecha de Egreso</th>
                            <th>Promedio Final</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tablaEgresados">
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
    <!-- Modal Modificar Inscripción (Solo datos de la inscripción) -->
    <div id="promediar" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h1 class="modal-title">PROMEDIAR ALUMNO</h1>
            <form id="formPromediar" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <fieldset>
                        <legend class="modal-title">Promediar Alumno</legend>
                        <div>
                            <label>FOTO</label>
                            <img id="ft" src="" alt="Vista previa de la foto" style="display: none; max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 8px;">
                        </div>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NÚMERO CONTROL</label>
                                <input type="text" name="nC" id="nC" class="input-field" readonly>
                            </div>
                            <div class="input-item">
                                <label>ALUMNO</label>
                                <input type="text" name="nom" id="nom" class="input-field" readonly>
                            </div>
                            <div class="input-item">
                                <label>PROMEDIO</label>
                                <input type="number" name="pr" id="pr" class="input-field" min="0" max="10" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnPromediar">Promediar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Script para manejar el modal y la tabla con AJAX -->
    <script src="modales/egresados/script.js"></script>
    <script src="modulos/script.js"></script>

</body>

</html>