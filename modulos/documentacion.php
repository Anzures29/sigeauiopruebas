<?php session_start();?>
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
                    <h2>Documentación de Alumnos</h2>
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
                            <th>Alumno</th>
                            <th>Nivel Educativo</th>
                            <th>Carrera</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tablaInscripciones">
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

    <!-- Modal -->
    <div id="modalDocumentacion" class="modal">
        <div class="modal-content show">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h1 class="modal-title">Documentos</h1>
            
            <form id="uploadForm" enctype="multipart/form-data">
                <div id="documentosContainer" class="form-group">
                    <!-- Aquí se agregarán los inputs de archivos dinámicamente -->
                </div>
            </form>
        </div>
    </div>

    <!-- Script para manejar el modal y la tabla con AJAX -->
    <script src="modales/documentacion/script.js"></script>
</body>

</html>