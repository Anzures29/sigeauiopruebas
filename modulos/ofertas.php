<?php
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
                <button class="boton-accion" onclick="abrirModalRegistrar()">Registrar Nueva Oferta</button>
                <div>
                    <h2>Ofertas Educativas</h2>
                </div>
                <div></div>
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
                            <th>Clave De La Oferta</th>
                            <th>Nivel Educativo</th>
                            <th>Carrera</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tablaOferta">
                        <!-- Aquí se cargan los datos -->
                    </tbody>
                </table>
            </div>
            <!-- Botones de paginación -->
            <div class="paginacion">
                <div id="cantidad-registros" style="margin-top: 10px;">

                </div>
                <div>
                    <button id="anterior" class="boton-accion">Anterior</button>
                    <span id="pageInfo"></span>
                    <button id="siguiente" class="boton-accion">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Oferta -->
    <div id="modalRegistrarOferta" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalRegistrar()">&times;</span>
            <h1 class="modal-title">OFERTA ACADÉMICA</h1>
            <form id="formRegistrarOferta" enctype="multipart/form-data">
                <div id="" class="form-group">
                    <!-- OFERTA ACADÉMICA -->
                    <fieldset>
                        <div class="input-group">
                            <div class="input-item">
                                <label>NIVEL EDUCATIVO</label>
                                <select name="ni" id="ni" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <option value="2">Licenciatura</option>
                                    <option value="3">Maestría</option>
                                    <option value="4">Doctorado</option>
                                </select>
                            </div>
                            <div class="input-item">
                                <label>CARRERA</label>
                                <input type="text" name="ca" class="input-field" placeholder="Ingrese la carrera" onkeyup="mayus(this);" required>
                            </div>
                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnRegistrarOferta">Registrar Oferta</button>
                    </div>
                </div>
            </form>
            <div id="mensaje"></div>
        </div>
    </div>

    <!-- Modal Modificar -->
    <div id="modificarOferta" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModalModificar()">&times;</span>
            <h1 class="modal-title">MODIFICAR OFERTA ACADÉMICA</h1>
            <form id="formModificarOferta" enctype="multipart/form-data">
                <div id="" class="form-group">
                    <!-- OFERTA ACADÉMICA -->
                    <fieldset>
                        <div class="input-group">
                            <div class="input-item">
                                <input type="hidden" name="cvA" id="cvA">
                                <label>CLAVE</label>
                                <input type="text" name="cvMod" id="cvMod" class="input-field" title="No se puede modificar" onkeyup="mayus(this);">
                            </div>
                            <div class="input-item">
                                <label>NIVEL EDUCATIVO</label>
                                <select name="niMod" id="niMod" class="input-field" required>
                                    <option value="">Seleccione</option>
                                    <option value="2">Licenciatura</option>
                                    <option value="3">Maestría</option>
                                    <option value="4">Doctorado</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label>CARRERA</label>
                            <input type="text" name="caMod" id="caMod" class="input-field" placeholder="Ingrese el nombre de la carrera" onkeyup="mayus(this);" required>
                        </div>
                    </fieldset>
                    <div class="button-group">
                        <button type="button" class="boton-accion" id="btnModificar">Modificar Oferta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Script para manejar el modal y la tabla con AJAX -->
    <script src="modales/ofertas/script.js"></script>
    <script src="modulos/script.js"></script>

</body>

</html>