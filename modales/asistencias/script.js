// Obtener elementos del DOM
var nivelSelect = document.getElementById("nivel"); // Select para seleccionar el nivel académico
var carreraSelect = document.getElementById("carrera"); // Select para seleccionar la carrera
var fechaInput = document.getElementById("fecha"); // Input para seleccionar la fecha
var tablaAsistencias = document.getElementById("tablaAsistencias"); // Tabla para mostrar los alumnos y sus asistencias
var registrarBtn = document.getElementById("registrarBtn"); // Botón para registrar asistencias
var asistenciasSeleccionadas = {}; // Objeto para almacenar las asistencias seleccionadas por alumno
// Evento: Cambio en el select de nivel
nivelSelect.addEventListener("change", function () {
    const nivelSeleccionado = this.value; // Obtener el nivel seleccionado
    carreraSelect.innerHTML = "<option value=''>Seleccione</option>"; // Limpiar opciones de carrera
    tablaAsistencias.innerHTML = ""; // Limpiar la tabla al cambiar el nivel
    if (nivelSeleccionado === "1") { // Si es nivel Bachillerato
        carreraSelect.disabled = true; // Deshabilitar el select de carreras
        const urlAlumnos = `modales/asistencias/consultarAlumnos.php?nivel=${nivelSeleccionado}`; // URL para cargar alumnos
        cargarAlumnos(urlAlumnos); // Cargar alumnos directamente
    } else { // Otros niveles
        carreraSelect.disabled = false; // Habilitar el select de carreras
        if (nivelSeleccionado) {
            cargarCarreras(nivelSeleccionado); // Cargar las carreras correspondientes al nivel
        }
    }
});
// Evento: Cambio en el select de carrera
carreraSelect.addEventListener("change", () => {
    const carreraSeleccionada = carreraSelect.value; // Obtener la carrera seleccionada
    if (carreraSeleccionada) {
        const urlAlumnos = `modales/asistencias/consultarAlumnos.php?carrera=${carreraSeleccionada}`; // URL para cargar alumnos de esa carrera
        cargarAlumnos(urlAlumnos); // Cargar alumnos
    } else {
        tablaAsistencias.innerHTML = ""; // Limpiar la tabla si no se selecciona carrera
    }
});
// Función: Cargar carreras disponibles para un nivel
function cargarCarreras(nivelSeleccionado) {
    fetch(`modales/pagos/consultarCarreras.php?nivel=${nivelSeleccionado}`) // Llamada al servidor para obtener las carreras
        .then(response => response.json())
        .then(carreras => {
            carreraSelect.innerHTML = "<option value=''>Seleccione</option>"; // Limpiar y agregar opción predeterminada
            carreras.forEach(carrera => {
                let option = document.createElement("option"); // Crear opción para cada carrera
                option.value = carrera.cv; // Código de la carrera
                option.textContent = carrera.ca; // Nombre de la carrera
                carreraSelect.appendChild(option); // Agregar opción al select
            });
        })
        .catch(error => {
            console.error("Error al cargar carreras:", error); // Manejar errores
        });
}
// Evento: Cambio en el input de fecha
fechaInput.addEventListener("change", () => {
    const fechaSeleccionada = fechaInput.value; // Obtener la fecha seleccionada
    const nivelSeleccionado = nivelSelect.value; // Obtener el nivel seleccionado
    const carreraSeleccionada = carreraSelect.value; // Obtener la carrera seleccionada
    let urlAlumnos = "";
    if (nivelSeleccionado === "1") { // Si es nivel Bachillerato
        urlAlumnos = `modales/asistencias/consultarAlumnos.php?nivel=${nivelSeleccionado}`;
    } else if (carreraSeleccionada) { // Si hay una carrera seleccionada
        urlAlumnos = `modales/asistencias/consultarAlumnos.php?carrera=${carreraSeleccionada}`;
    }
    if (urlAlumnos) {
        cargarAlumnos(urlAlumnos); // Recargar alumnos con la nueva fecha
    }
});
// Función: Cargar alumnos en la tabla con opciones de asistencia
function cargarAlumnos(url) {
    const fechaSelec = fechaInput.value; // Obtener la fecha seleccionada
    url += `&fecha=${fechaSelec}`; // Agregar la fecha como parámetro
    fetch(url)
        .then(response => response.json())
        .then(alumnos => {
            // Crear filas de la tabla con los datos de los alumnos
            tablaAsistencias.innerHTML = alumnos.map(alumno => `
                <tr>
                    <td>${alumno.nC}</td> <!-- Número de control -->
                    <td>${alumno.alumno}</td> <!-- Nombre del alumno -->
                    <td>
                        <select name="estado" id="estado_${alumno.nC}" class="busqueda-field"> <!-- Select de estado -->
                            <option value="">Seleccione</option>
                            <option value="Si" ${alumno.estado === 'Si' ? 'selected' : ''}>Si</option>
                            <option value="No" ${alumno.estado === 'No' ? 'selected' : ''}>No</option>
                            <option value="Ju" ${alumno.estado === 'Ju' ? 'selected' : ''}>Ju</option>
                        </select>
                    </td>
                </tr>
            `).join("");
            // Agregar eventos de cambio a cada select para almacenar el estado seleccionado
            alumnos.forEach(alumno => {
                const selectEstado = document.getElementById(`estado_${alumno.nC}`);
                selectEstado.addEventListener("change", function () {
                    asistenciasSeleccionadas[alumno.nC] = selectEstado.value; // Guardar estado seleccionado
                });
            });
        })
        .catch(error => {
            console.error("Error al cargar alumnos:", error); // Manejar errores
            tablaAsistencias.innerHTML = `<tr><td colspan="5">Error al cargar los datos</td></tr>`; // Mostrar error en la tabla
        });
}
// Función: Registrar todas las asistencias seleccionadas
function registrarAsistencia() {
    const fecha = fechaInput.value.trim(); // Obtener la fecha seleccionada
    if (!fecha) {
        alert("La fecha es obligatoria. Verifica el campo de fecha."); // Validar que la fecha no esté vacía
        return;
    }
    const filas = document.querySelectorAll("#tablaAsistencias tr"); // Obtener las filas de la tabla
    const asistenciasSeleccionadas = { fecha: fecha }; // Incluir la fecha en los datos
    for (const fila of filas) {
        const nC = fila.querySelector("td:first-child")?.textContent.trim(); // Obtener número de control
        const selectEstado = fila.querySelector("select[name='estado']"); // Obtener select de estado
        if (nC && selectEstado) {
            const estadoSeleccionado = selectEstado.value;
            if (!estadoSeleccionado) { // Validar que se haya seleccionado un estado
                alert("Debe seleccionar la asistencia para todos los alumnos");
                return;
            }
            asistenciasSeleccionadas[`asistencias[${nC}]`] = estadoSeleccionado; // Agregar estado seleccionado
        }
    }
    // Enviar datos al servidor
    fetch('modales/asistencias/agregar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(asistenciasSeleccionadas)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Asistencias registradas correctamente');
                setTimeout(() => location.reload(), 1000); // Recargar la página tras un tiempo
            } else {
                alert(data.error || 'Error al registrar las asistencias'); // Mostrar error del servidor
            }
        })
        .catch(error => {
            console.error('Error al registrar asistencia:', error); // Manejar errores de la petición
        });
}
// Evento: Click en el botón de registrar asistencia
registrarBtn.addEventListener("click", registrarAsistencia);