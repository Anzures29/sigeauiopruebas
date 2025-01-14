var currentPage = 1;
var registros = 10;
var response;
$(document).ready(function () {
    loadData();
    // Evento click para el botón "imprimir"
    $("#imprimir").on("click", function () {
        var buscar = $("#buscar").val().trim(); // Obtén el texto del buscador
        // Generar la URL con el filtro de búsqueda
        var url = 'modales/inscripciones/imprimirReporte.php?buscar=' + encodeURIComponent(buscar);
        window.open(url, '_blank'); // Abre el reporte en una nueva pestaña
    });
    // Filtro de cantidad de registros
    $("#registros").on("change", function () {
        registros = this.value;
        currentPage = 1; // Reinicia a la primera página
        loadData();
    });
    // Filtro de búsqueda en tiempo real
    $("#buscar").on("input", function () {
        currentPage = 1; // Reiniciaa la primera página
        loadData();
    });
    // Botones de paginación
    $("#anterior").on("click", function () {
        if (currentPage > 1) {
            currentPage--;
            loadData();
        }
    });
    $("#siguiente").on("click", function () {
        if (currentPage < response.totalPages) {
            currentPage++;
            loadData();
        }
    });
});
// Función para cargar los datos con AJAX
function loadData() {
    const tablaInscripciones = $("#tablaInscripciones");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/inscripciones/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaInscripciones.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (inscripciones) {
                    let actionButtons = '';
                    let actionColumn = ''; // Define an empty string for the action column
                    if (userRole === 'Rector') {
                        actionButtons = `
                                <button class="boton-accion" onclick="abrirModalModificar('${inscripciones.fol}')">Modificar</button>
                                <button class="boton-accion" onclick="eliminar('${inscripciones.fol}', '${inscripciones.alumno}', '${inscripciones.nC}')">Baja</button>
                        `;
                        actionColumn = `<td>${actionButtons}</td>`; // Populate actionColumn only if the user is rector
                    }
                    tablaInscripciones.append(`
                        <tr>
                            <td>${inscripciones.fol}</td>
                            <td><img src="${inscripciones.ft}" alt="Foto del alumno" style="max-width: 100px; border-radius: 5px; vertical-align: middle;"></td>
                            <td>${inscripciones.fe}</td>
                            <td>${inscripciones.alumno}</td>
                            <td>${inscripciones.ni}</td>
                            <td>${inscripciones.carrera}</td>
                            <td>${inscripciones.feIni}</td>
                            <td>${inscripciones.feFin}</td>
                            <td>${inscripciones.fePa}</td>
                            <td>${inscripciones.pe}</td>
                            <td>${inscripciones.peAct}</td>
                            <td>${inscripciones.ins}</td>
                            <td>${inscripciones.cole}</td>
                            <td>${inscripciones.rein}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaInscripciones.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaInscripciones.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}
// Función genérica para abrir cualquier modal
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "flex";
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    void modalContent.offsetWidth;
    modalContent.classList.add("show");
}
// Función genérica para cerrar cualquier modal
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    setTimeout(function () {
        modal.style.display = "none";
    }, 400);
}
// Función genérica para cargar cualquier imagen
function cargarImagen(event, imgElementId) {
    const fotoCargada = document.getElementById(imgElementId);
    fotoCargada.src = URL.createObjectURL(event.target.files[0]);
    fotoCargada.style.display = 'block';
}
// Función genérica para cargar las colonias
function cargarColonias(municipio, colonia) {
    var municipio = document.getElementById(municipio).value;
    var coloniaSelect = document.getElementById(colonia);
    coloniaSelect.innerHTML = "<option value=''>Seleccione</option>";
    if (municipio !== "") {
        fetch(`modales/inscripciones/consultarColonias.php?municipio=${encodeURIComponent(municipio)}`)
            .then(response => response.json())
            .then(colonias => {
                colonias.forEach(colonia => {
                    let option = document.createElement("option");
                    option.value = colonia.cv;
                    option.textContent = colonia.co;
                    coloniaSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Error al cargar colonias:", error));
    }
}
// Función para formatear el horario automáticamente mientras el usuario escribe
function formatearHorario(event) {
    const input = event.target; // Obtener el elemento input que dispara el evento
    const value = input.value.replace(/\D/g, ''); // Eliminar todo excepto dígitos del valor ingresado
    let formattedValue = ''; // Inicializar la variable para el valor formateado
    // Formatear el valor según la longitud de los dígitos ingresados
    if (value.length <= 2) {
        formattedValue = value; // Si tiene 2 o menos dígitos, mantener el valor tal cual
    } else if (value.length <= 4) {
        formattedValue = value.slice(0, 2) + ':' + value.slice(2); // Si tiene entre 3 y 4 dígitos, agregar ":" entre los primeros 2 y los siguientes dígitos
    } else if (value.length <= 8) {
        formattedValue = value.slice(0, 2) + ':' + value.slice(2, 4) + ' a ' + value.slice(4, 6) + ':' + value.slice(6); // Si tiene entre 5 y 8 dígitos, agregar " a " y ":" para formar el rango de tiempo
    } else {
        formattedValue = value.slice(0, 2) + ':' + value.slice(2, 4) + ' a ' + value.slice(4, 6) + ':' + value.slice(6, 8); // Si tiene más de 8 dígitos, mantener el formato de rango completo
    }
    input.value = formattedValue; // Actualizar el valor del input con el valor formateado
}
// Función para validar que el horario esté en el formato correcto cuando el usuario sale del campo de entrada
function validarHorario(event) {
    const input = event.target; // Obtener el elemento input que dispara el evento
    const value = input.value; // Obtener el valor actual del input
    const regex = /^\d{2}:\d{2} a \d{2}:\d{2}$/; // Expresión regular para validar el formato HH:MM a HH:MM

    if (!regex.test(value)) {
        input.setCustomValidity('Formato de horario inválido. El formato correcto es HH:MM a HH:MM.'); // Establecer un mensaje de error si el valor no cumple con el formato
    } else {
        input.setCustomValidity(''); // Limpiar el mensaje de error si el valor cumple con el formato
    }
}
// Función para validar la fecha de inicio de clases
document.addEventListener('DOMContentLoaded', function () {
    const fechaInput = document.getElementById('feI');
    const mensajeFecha = document.getElementById('mensajeFecha');
    fechaInput.addEventListener('change', function () {
        const fechaIngresada = new Date(fechaInput.value);
        const fechaActual = new Date();
        // Restablecer la hora para comparación precisa (solo fecha)
        fechaIngresada.setHours(0, 0, 0, 0);
        fechaActual.setHours(0, 0, 0, 0);
        // Comparar las fechas (si la fecha ingresada es menor que la actual)
        const esFechaPasada = fechaIngresada.getTime() < fechaActual.getTime();
        // Obtener el mes de la fecha ingresada (indexado desde 0)
        const mes = fechaIngresada.getMonth();
        // Mostrar el mensaje de advertencia si la fecha es pasada o el mes no está permitido
        if (esFechaPasada) {
            mensajeFecha.style.display = 'inline'; // Mostrar el mensaje de advertencia
        } else {
            mensajeFecha.style.display = 'none'; // Ocultar el mensaje de advertencia si la fecha es correcta
        }
    });
});
// Función para calcular la edad del alumno con su fecha de nacimiento
document.getElementById("fechaNacimiento").addEventListener("change", function () {
    const fechaNacimiento = document.getElementById("fechaNacimiento").value;
    if (fechaNacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        document.getElementById("ed").value = edad;
    }
});
// Función para generar la fecha de pago de colegiatura en base a la fecha de inicio de clases del alumno
document.getElementById("feI").addEventListener("change", function () {
    let fechaInicio = new Date(this.value);
    let year = fechaInicio.getFullYear();
    let month = ("0" + (fechaInicio.getMonth() + 1)).slice(-2); // Ajuste para el mes en base 0
    let fechaPago = `${year}-${month}-05`;
    document.getElementById("fePa").value = fechaPago;
});
// Función para generar la fecha de pago de colegiatura en base a la fecha de inicio de clases del alumno
document.getElementById("feIMod").addEventListener("change", function () {
    let fechaInicio = new Date(this.value);
    let year = fechaInicio.getFullYear();
    let month = ("0" + (fechaInicio.getMonth() + 1)).slice(-2); // Ajuste para el mes en base 0
    let fechaPago = `${year}-${month}-05`;
    document.getElementById("fePaMod").value = fechaPago;
});
// Función para cargar las carreras en el select correspondiente según el nivel educativo seleccionado
document.getElementById("nivel").addEventListener("change", function () {
    var nivelSeleccionado = this.value;
    var carreraSelect = document.getElementById("carrera");
    // Restablecer el contenido del select de carrera
    carreraSelect.innerHTML = "<option value=''>Seleccione</option>";
    // Si el nivel seleccionado es Bachillerato, deshabilitar el select de carrera
    if (nivelSeleccionado === "1") {  // Comparación estricta
        carreraSelect.disabled = true;
    } else {
        carreraSelect.disabled = false;
        if (nivelSeleccionado !== "") {
            fetch(`modales/inscripciones/consultarCarreras.php?nivel=${encodeURIComponent(nivelSeleccionado)}`)
                .then(response => response.json())
                .then(carreras => {
                    carreras.forEach(carrera => {
                        let option = document.createElement("option");
                        option.value = carrera.cv; // Asigna la clave como valor
                        option.textContent = carrera.ca; // Muestra solo el nombre
                        carreraSelect.appendChild(option);
                    });
                })
                .catch(error => console.error("Error al cargar carreras:", error));
        }
    }
});
// Función para actualizar la ESCUELA de la cct seleccionada
function updateEscuela() {
    const cctSelect = document.getElementById('cct');
    const escuelaSelect = document.getElementById('escuela');
    const selectedCCT = cctSelect.options[cctSelect.selectedIndex];
    const escuela = selectedCCT.getAttribute('data-escuela');
    for (let option of escuelaSelect.options) {
        if (option.value === escuela) {
            option.selected = true;
            return;
        }
    }
    escuelaSelect.value = "";
}
// Función para actualizar la CCT de la escuela seleccionada
function updateCCT() {
    const escuelaSelect = document.getElementById('escuela');
    const cctSelect = document.getElementById('cct');
    const selectedEscuela = escuelaSelect.options[escuelaSelect.selectedIndex];
    const cct = selectedEscuela.getAttribute('data-cct');
    for (let option of cctSelect.options) {
        if (option.value === cct) {
            option.selected = true;
            return;
        }
    }
    cctSelect.value = "";
}
// Función para cargar los datos de la inscripción en el modal modificar
async function cargarDatosInscripcion(folio) {
    const carreraMod = document.getElementById('carreraMod');
    try {
        const response = await fetch('modales/inscripciones/consultarInscripcion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `fol=${encodeURIComponent(folio)}`
        });
        const data = await response.json();
        if (data.exito) {
            const inscripcion = data.inscripcion;
            // Cargar datos en el modal
            if (inscripcion.ft) {
                const fotoCargadaMod = document.getElementById('fotoCargadaMod');
                fotoCargadaMod.src = inscripcion.ft;
                fotoCargadaMod.style.display = 'block';
            }
            document.getElementById('folMod').value = inscripcion.fol || '';
            document.getElementById('feMod').value = inscripcion.fe || '';
            document.getElementById('nCAct').value = inscripcion.nC || '';
            document.getElementById('alumnoMod').value = inscripcion.alumno || '';
            document.getElementById('nivelMod').value = inscripcion.ni || '';
            carreraMod.innerHTML = `<option value=''>${inscripcion.carrera}</option>`;
            document.getElementById('feIMod').value = inscripcion.feIni || '';
            document.getElementById('feFMod').value = inscripcion.feFin || '';
            document.getElementById('fePaMod').value = inscripcion.fePa || '';
            document.getElementById('peMod').value = inscripcion.pe || '';
            document.getElementById('cosAct').value = inscripcion.coIns || '';
            document.getElementById('costoMod').value = inscripcion.coIns || '';
            document.getElementById('colAct').value = inscripcion.coColOrig || '';
            document.getElementById('coleMod').value = inscripcion.coColOrig || '';
            document.getElementById('reiAct').value = inscripcion.coRei || '';
            document.getElementById('reinMod').value = inscripcion.coRei || '';
            if (inscripcion.cN === 1) { // Si el nivel seleccionado es Bachillerato, deshabilitar el select de carrera
                carreraMod.disabled = true;
            } else {
                carreraMod.disabled = false;
                if (inscripcion.cN !== "") {
                    fetch(`modales/inscripciones/consultarCarreras.php?nivel=${encodeURIComponent(inscripcion.cN)}`)
                        .then(response_1 => response_1.json())
                        .then(carreras => {
                            carreras.forEach(carrera => {
                                let option = document.createElement("option");
                                option.value = carrera.cv;
                                option.textContent = carrera.ca;
                                carreraMod.appendChild(option);
                            });
                        })
                        .catch(error => console.error("Error al cargar carreras:", error));
                }
            }
        } else {
            alert(data.mensaje || 'No se pudieron cargar los datos de la inscripción.');
        }
    } catch (error_1) {
        return console.error('Error al obtener los datos:', error_1);
    }
}
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["inscribirAlumno", "modificarInscripcion", "eliminarInscripcion"];
    modales.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            cerrarModal(modalId);
        }
    });
};
// Función para cerrar el modal con la tecla Esc
document.body.addEventListener("keydown", function (event) {
    if (event.code === 'Escape') {
        const modales = ["inscribirAlumno", "modificarInscripcion", "eliminarInscripcion"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
// Llamadas a la función genérica para abrir los modales específicos
function abrirModalInscribir() {
    abrirModal("inscribirAlumno");
}
function abrirModalModificar(folio) {
    abrirModal("modificarInscripcion");
    cargarDatosInscripcion(folio);
}
function abrirModalEliminar(folio) {
    abrirModal("eliminarInscripcion");
    cargarDatosEliminar(folio);
}
// Llamadas a la función genérica para cerrar los modales específicos
function cerrarModalInscribir() {
    cerrarModal("inscribirAlumno");
}
function cerrarModalModificar() {
    cerrarModal("modificarInscripcion");
}
function cerrarModalEliminar() {
    cerrarModal("eliminarInscripcion");
}
// Llamadas a las funciones cargar imagen
function cargarImagenInscribir(event) {
    cargarImagen(event, 'fotoCargada');
}
function cargarImagenModificar(event) {
    cargarImagen(event, 'fotoCargadaMod');
}
// Asignación de eventos de cambio para cada select de municipio
document.getElementById("municipioAlumno").addEventListener("change", function () {
    cargarColonias("municipioAlumno", "coloniaAlumno");
});
document.getElementById("municipioTutor").addEventListener("change", function () {
    cargarColonias("municipioTutor", "coloniaTutor");
});
// Función para mandar los datos del formulario
document.getElementById('btnInscribir').addEventListener('click', function () {
    const form = document.getElementById('formInscribir');
    const carreraSelect = document.getElementById('carrera');
    const fechaInicio = document.getElementById('feI');
    const errorFecha = document.getElementById('errorFecha');
    const fechaInicioValor = new Date(fechaInicio.value);
    const fechaActual = new Date();
    if (fechaInicioValor.getFullYear() < fechaActual.getFullYear()) {
        errorFecha.style.display = 'block';
        fechaInicio.focus();
        return;
    } else {
        errorFecha.style.display = 'none';
    }
    if (form.checkValidity()) {
        const formData = new FormData(form);
        if (carreraSelect.disabled) {
            formData.delete('carrera');
        }
        fetch('modales/inscripciones/agregar.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return response.json();
            })
            .then(data => {
                // console.log("Datos recibidos del servidor:", data.datos_recibidos); // Línea para depuración en caso de requerirlo
                if (data.exito) {
                    alert(data.mensaje_final);
                    cerrarModalInscribir();
                    // Se formatean los datos del formulario para generar la papeleta de inscripción
                    const queryString = new URLSearchParams();
                    formData.forEach((value, key) => {
                        if (key === 'ft' && value instanceof File) {
                            queryString.append(key, value.name); // Agregar solo el nombre del archivo
                        } else {
                            queryString.append(key, value);
                        }
                    });
                    // Redirigir a la ventana del PDF
                    window.open('', '_blank').location.href = 'modales/inscripciones/reporteInscripcion.php?' + queryString.toString();
                    setTimeout(function () { // Se recarga la página
                        location.reload();
                    }, 50);
                } else {
                    alert(data.mensaje_final);
                }
            })
            .catch(error => {
                console.error("Error al enviar datos:", error);
                fetch('modales/inscripciones/agregar.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(text => {
                        console.error("Contenido de la respuesta del servidor:", text);
                    });
                alert("Error al enviar los datos, por favor intente de nuevo.");
            });
    } else {
        form.reportValidity();
    }
});
// Función para mandar los datos del formulario modificar
document.getElementById('btnModificar').addEventListener('click', function () {
    const form = document.getElementById('formModificarInscripcion');
    const carreraSelect = document.getElementById('carreraMod');
    const fechaInicio = document.getElementById('feIMod');
    const errorFecha = document.getElementById('errorFechaMod');
    const fechaInicioValor = new Date(fechaInicio.value);
    const fechaActual = new Date();
    if (fechaInicioValor.getFullYear() < fechaActual.getFullYear()) {
        errorFecha.textContent = 'La fecha debe ser del año actual o posterior.';
        errorFecha.style.display = 'block';
        fechaInicio.focus();
        return;
    } else {
        errorFecha.style.display = 'none';
    }
    if (form.checkValidity()) {
        const formData = new FormData(form);
        if (carreraSelect.disabled) {
            formData.delete('carreraMod');
        }
        fetch('modales/inscripciones/modificar.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.exito) {
                    alert(data.mensaje_final || 'Inscripción modificada correctamente');
                    setTimeout(function () { // Se recarga la página
                        location.reload();
                    }, 50);
                } else {
                    alert(data.mensaje_final || 'Favor de verificar los datos ingresados');
                }
            })
            .catch(error => {
                console.error("Error al enviar datos:", error);
                fetch('modales/inscripciones/modificar.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(text => {
                        console.error("Contenido de la respuesta del servidor:", text);
                    });
                alert("Error al enviar los datos, por favor intente de nuevo.");
            });
    } else {
        form.reportValidity();
    }
});
// Función para dar de baja
async function eliminar(fo, alumno, nC) {
    if (!confirm(`¿Está seguro de que desea dar de baja al alumno ${alumno}, con folio ${fo}?`)) {
        return;
    }
    try {
        const response = await fetch('modales/inscripciones/eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ fo, alumno }).toString()
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final);
            if (!confirm(`¿Desea eliminar toda la información del alumno?`)) {
                setTimeout(() => location.reload(), 1000);
                return;
            }
            try {
                const eliminarResponse = await fetch('modales/inscripciones/eliminarAlumno.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ nC, alumno }).toString(),
                });
                if (!eliminarResponse.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                const eliminarData = await eliminarResponse.json();
                if (eliminarData.exito) {
                    alert(eliminarData.mensaje_final);
                    setTimeout(function () { // Se recarga la página
                        location.reload();
                    }, 50);
                } else {
                    alert(eliminarData.mensaje_final);
                }
            } catch (error) {
                console.error('Error al enviar datos:', error);
                alert('Hubo un problema al procesar la solicitud. Intente nuevamente.');
            }
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un problema al enviar los datos, intente nuevamente.');
    }
}