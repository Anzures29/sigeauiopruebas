var currentPage = 1;
var registros = 10;
var response;
$(document).ready(function () {
    loadData();
    // Manejo del filtro de cantidad de registros
    $("#registros").on("change", function () {
        registros = this.value;
        currentPage = 1;
        loadData();
    });
    // Búsqueda en tiempo real
    $("#buscar").on("input", function () {
        currentPage = 1;
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
    const tablaAlumnos = $("#tablaAlumnos");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/alumnos/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaAlumnos.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (alumnos) {
                    let actionButtons = '';
                    if (userRole === 'Rector') {
                        actionButtons = `
                            <button class="boton-accion" onclick="abrirModalModificar('${alumnos.nC}')">Modificar</button>
                            <button class="boton-accion" onclick="eliminarRegistro('${alumnos.nC}', '${alumnos.alumno}')">Eliminar</button>
                        `;
                    }
                    tablaAlumnos.append(`
                        <tr>
                            <td>${alumnos.nC}</td>
                            <td>${alumnos.ma}</td>
                            <td>${alumnos.alumno}</td>
                            <td>${alumnos.mu}</td>
                            <td>${alumnos.fN}</td>
                            <td>${alumnos.cu}</td>
                            <td>${alumnos.te}</td>
                            <td>${alumnos.em}</td>
                            <td>
                                <button class="boton-accion" onclick="abrirModalVisualizar('${alumnos.nC}')">Visualizar</a></button>
                                ${actionButtons}
                            </td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaAlumnos.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaAlumnos.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["visualizar", "modificar", "eliminar"];
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
        const modales = ["visualizar", "modificar", "eliminar"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
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
// Llamadas a la función genérica para abrir los modales específicos
function abrirModalVisualizar(nC) {
    abrirModal("visualizar");
    visualizarRegistro(nC);
}
function abrirModalModificar(nC) {
    abrirModal("modificar");
    visualizarRegistro(nC);
}
// Llamadas a la función genérica para cerrar los modales específicos
function cerrarModalVisualizar() {
    cerrarModal("visualizar");
}
function cerrarModalModificar() {
    cerrarModal("modificar");
}
// Función genérica para cargar las colonias
function cargarColonias(municipio, colonia) {
    var municipio = document.getElementById(municipio).value;
    var colonia = document.getElementById(colonia);
    colonia.innerHTML = "<option value=''>Seleccione</option>";
    if (municipio !== "") {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "modales/empleados/consultarColonias.php?municipio=" + encodeURIComponent(municipio), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var colonias = JSON.parse(xhr.responseText);
                    colonias.forEach(function (col) {
                        var option = document.createElement("option");
                        option.value = col.cv;
                        option.textContent = col.co;
                        colonia.appendChild(option);
                    });
                } catch (e) {
                    console.error("Error al analizar JSON:", e);
                }
            }
        };
        xhr.send();
    }
}
// Función para cargar las colonias del municipio seleccionado Modificar
document.getElementById("municipioAlumnoMod").addEventListener("change", function () {
    cargarColonias("municipioAlumnoMod", "coloniaAlumnoMod");
});
document.getElementById("municipioTutorMod").addEventListener("change", function () {
    cargarColonias("municipioTutorMod", "coloniaTutorMod");
});
// Función para calcular la edad del alumno con su fecha de nacimiento
document.getElementById("fechaNacimientoMod").addEventListener("change", function () {
    const fechaNacimiento = document.getElementById("fechaNacimientoMod").value;
    if (fechaNacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        document.getElementById("edMod").value = edad;
    }
});
// Función para actualizar la ESCUELA de la cct seleccionada
function updateEscuela() {
    const cctSelect = document.getElementById('cctMod');
    const escuelaSelect = document.getElementById('escuelaMod');
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
    const escuelaSelect = document.getElementById('escuelaMod');
    const cctSelect = document.getElementById('cctMod');
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
// Función para consultar los datos del alumno y cargarlos en los modales correspondientes
async function visualizarRegistro(nC) {
    const lugarN = document.getElementById('lugarNacimientoMod');
    const municipio = document.getElementById('municipioAlumnoMod');
    const colonia = document.getElementById('coloniaAlumnoMod');
    const cct = document.getElementById('cctMod');
    const escuela = document.getElementById('escuelaMod');
    const municipioTutor = document.getElementById('municipioTutorMod');
    const coloniaTutor = document.getElementById('coloniaTutorMod');
    try {
        const response = await fetch('modales/alumnos/consultarAlumno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `nC=${encodeURIComponent(nC)}`
        });
        const data = await response.json();
        if (data.exito) {
            const alumno = data.alumno;
            cargarDatosModalVisualizar(alumno);
            cargarDatosModalModificar(alumno, lugarN, municipio, colonia, cct, escuela, municipioTutor, coloniaTutor);
        } else {
            alert(data.mensaje || 'No se pudieron cargar los datos del Alumno.');
        }
    } catch (error_1) {
        console.error('Error al obtener los datos del Alumno:', error_1);
    }
}
// Función para cargar los datos en los campos de los modales
function cargarDatosModalVisualizar(alumno) {
    // Campos de Alumno
    document.getElementById(`aPVis`).value = alumno.aP || '';
    document.getElementById(`aMVis`).value = alumno.aM || '';
    document.getElementById(`nomVis`).value = alumno.nom || '';
    document.getElementById(`maVis`).value = alumno.ma || '';
    document.getElementById(`lugarNacimientoVis`).value = alumno.lugarN || '';
    document.getElementById(`fechaNacimientoVis`).value = alumno.fN || '';
    document.getElementById(`edVis`).value = alumno.ed || '';
    document.getElementById(`cuVis`).value = alumno.cu || '';
    document.getElementById(`seVis`).value = alumno.se || '';
    document.getElementById(`tsVis`).value = alumno.ts || '';
    document.getElementById(`afVis`).value = alumno.af || '';
    document.getElementById(`municipioAlumnoVis`).value = alumno.muAlumno || '';
    document.getElementById(`coloniaAlumnoVis`).value = alumno.coAlumno || '';
    document.getElementById(`caVis`).value = alumno.caAlumno || '';
    document.getElementById(`teVis`).value = alumno.teAlumno || '';
    document.getElementById(`emVis`).value = alumno.em || '';
    // Campos de Escuela
    document.getElementById(`cctVis`).value = alumno.cct || '';
    document.getElementById(`escuelaVis`).value = alumno.es || '';
    document.getElementById(`geVis`).value = alumno.ge || '';
    document.getElementById(`prVis`).value = alumno.pr || '';
    // Campos de Tutor
    document.getElementById(`curpTutorVis`).value = alumno.cuTutor || '';
    document.getElementById(`nomTutorVis`).value = alumno.nomTutor || '';
    document.getElementById(`paVis`).value = alumno.pa || '';
    document.getElementById(`teTutorVis`).value = alumno.teTutor || '';
    document.getElementById(`municipioTutorVis`).value = alumno.muTutor || '';
    document.getElementById(`coloniaTutorVis`).value = alumno.coTutor || '';
    document.getElementById(`calleTutorVis`).value = alumno.caTutor || '';
}
function cargarDatosModalModificar(alumno, lugarN = null, municipio = null, colonia = null, cct = null, escuela = null, municipioTutor = null, coloniaTutor = null) {
    // Campos de Alumno
    document.getElementById(`nC`).value = alumno.nC || '';
    document.getElementById(`aPMod`).value = alumno.aP || '';
    document.getElementById(`aMMod`).value = alumno.aM || '';
    document.getElementById(`nomMod`).value = alumno.nom || '';
    document.getElementById(`maMod`).value = alumno.ma || '';
    if (lugarN) seleccionarOpcionPorTexto(lugarN, alumno.lugarN);
    document.getElementById(`fechaNacimientoMod`).value = alumno.fN || '';
    document.getElementById(`edMod`).value = alumno.ed || '';
    document.getElementById(`cuMod`).value = alumno.cu || '';
    document.getElementById(`seMod`).value = alumno.se || '';
    document.getElementById(`tsMod`).value = alumno.ts || '';
    document.getElementById(`afMod`).value = alumno.af || '';
    if (municipio) seleccionarOpcionPorTexto(municipio, alumno.muAlumno);
    colonia.innerHTML = `<option value=''>${alumno.coAlumno}</option>`;
    document.getElementById(`caMod`).value = alumno.caAlumno || '';
    document.getElementById(`teMod`).value = alumno.teAlumno || '';
    document.getElementById(`emMod`).value = alumno.em || '';
    // Campos de Escuela
    if (cct) seleccionarOpcionPorTexto(cct, alumno.cct);
    if (escuela) seleccionarOpcionPorTexto(escuela, alumno.es);
    document.getElementById(`geMod`).value = alumno.ge || '';
    document.getElementById(`prMod`).value = alumno.pr || '';
    // Campos de Tutor
    document.getElementById(`curpTutorMod`).value = alumno.cuTutor || '';
    document.getElementById(`nomTutorMod`).value = alumno.nomTutor || '';
    document.getElementById(`paMod`).value = alumno.pa || '';
    document.getElementById(`teTutorMod`).value = alumno.teTutor || '';
    if (municipioTutor) seleccionarOpcionPorTexto(municipioTutor, alumno.muTutor);
    coloniaTutor.innerHTML = `<option value=''>${alumno.coTutor}</option>`;
    document.getElementById(`calleTutorMod`).value = alumno.caTutor || '';
}
// Función genérica para cargar correctamente los datos de un select y el que se obtiene de una consulta
function seleccionarOpcionPorTexto(selectElement, texto) {
    Array.from(selectElement.options).forEach(option => {
        // Validar que ambos valores sean cadenas antes de comparar
        if (typeof option.textContent === 'string' && typeof texto === 'string' &&
            option.textContent.trim() === texto.trim()) {
            option.selected = true;
        }
    });
}
// Función para mandar los datos del formulario modificar
document.getElementById('btnModificar').addEventListener('click', function () {
    const form = document.getElementById('formModificar');
    if (form.checkValidity()) {
        const formData = new FormData(form);
        fetch('modales/alumnos/modificar.php', {
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
                    alert(data.mensaje_final);
                    cerrarModalModificar();
                    loadData();
                    // setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.mensaje_final || 'Favor de verificar los datos ingresados');
                }
            })
            .catch(error => {
                console.error("Error al enviar datos:", error);
                fetch('modales/alumnos/modificar.php', {
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
// Función para eliminar los datos del alumno
async function eliminarRegistro(nC, alumno) {
    if (!confirm(`¿Está seguro de que desea eliminar los datos del alumno ${nC} ${alumno}?`)) {
        return;
    }
    try {
        const response = await fetch('modales/alumnos/eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ nC, alumno }),
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final);
            loadData();
            // setTimeout(() => location.reload(), 1000); // Recargar página tras éxito
        } else {
            alert(data.mensaje_final);
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un problema al procesar la solicitud. Intente nuevamente.');
    }
}