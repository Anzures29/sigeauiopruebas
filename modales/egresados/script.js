var currentPage = 1;
var registros = 10;
var response;
$(document).ready(function () {
    loadData();
    // Evento click para el botón "imprimir"
    $("#imprimir").on("click", function () {
        var buscar = $("#buscar").val().trim(); // Obtén el texto del buscador
        // Generar la URL con el filtro de búsqueda
        var url = 'modales/egresados/imprimirReporte.php?buscar=' + encodeURIComponent(buscar);
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
// Función para cargar los datos con AJAX <button class="boton-accion">Modificar</button>
function loadData() {
    const tablaEgresados = $("#tablaEgresados");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/egresados/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaEgresados.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (egresados) {
                    tablaEgresados.append(`
                        <tr>
                            <td>${egresados.fol}</td>
                            <td>${egresados.nC}</td>
                            <td><img src="${egresados.ft}" alt="Foto del alumno" style="max-width: 100px; border-radius: 5px; vertical-align: middle;"></td>
                            <td>${egresados.Alumno}</td>
                            <td>${egresados.ni}</td>
                            <td>${egresados.carrera}</td>
                            <td>${egresados.feIng}</td>
                            <td>${egresados.feEgr}</td>
                            <td>${egresados.promedio}</td>
                            <td>
                                <button class="boton-accion" onclick="abrirModal('${egresados.nC}')">Promediar</button>
                            </td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaEgresados.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud AJAX:");
            console.error("Estado:", textStatus);
            console.error("Error:", errorThrown);

            // Verifica si hay contenido en la respuesta del servidor
            if (jqXHR.responseText) {
                console.error("Respuesta del servidor (formato recibido):", jqXHR.responseText);
            } else {
                console.error("No se recibió contenido del servidor.");
            }

            // Muestra un mensaje de error en la tabla
            tablaEgresados.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos. Verifique la consola para más detalles.</td></tr>");
        }

    });
}
// <button class="boton-accion" onclick="eliminar('${egresados.fol}', '${egresados.alumno}', '${egresados.nC}')">Eliminar</button>
// Función genérica para abrir cualquier modal
function abrir(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "flex";
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    void modalContent.offsetWidth;
    modalContent.classList.add("show");
}
// Función genérica para cerrar cualquier modal
function cerrar(modalId) {
    const modal = document.getElementById(modalId);
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    setTimeout(function () {
        modal.style.display = "none";
    }, 400);
}
// Función para cargar los datos de la inscripción en el modal modificar
async function cargarDatos(nC) {
    try {
        const response = await fetch('modales/egresados/consultarAlumno.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `nC=${encodeURIComponent(nC)}`
        });
        const data = await response.json();
        if (data.exito) {
            const egresados = data.egresados;
            // Cargar datos en el modal
            if (egresados.ft) {
                const fotoCargadaMod = document.getElementById('ft');
                fotoCargadaMod.src = egresados.ft;
                fotoCargadaMod.style.display = 'block';
            }
            document.getElementById('nC').value = egresados.nC || '';
            document.getElementById('pr').value = egresados.pr || '';
            document.getElementById('nom').value = egresados.Alumno || '';
        } else {
            alert(data.mensaje || 'No se pudieron cargar los datos de los alumnos egresados.');
        }
    } catch (error_1) {
        return console.error('Error al obtener los datos:', error_1);
    }
}
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["promediar"];
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
        const modales = ["promediar"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
// Llamadas a la función genérica para abrir los modales específicos
function abrirModal(nC) {
    abrir("promediar");
    cargarDatos(nC);
}
// // Llamadas a la función genérica para cerrar los modales específicos
function cerrarModal() {
    cerrar("promediar");
}
// Función para guardar el promedio del alumno
document.getElementById('btnPromediar').addEventListener('click', function () {
    const form = document.getElementById('formPromediar');
    if (form.checkValidity()) {
        const formData = new FormData(form);
        fetch('modales/egresados/promediar.php', {
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
                    cerrarModal();
                    loadData();
                    // setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.mensaje_final);
                }
            })
            .catch(error => {
                console.error('Error al enviar datos:', error);
                alert('Hubo un problema al enviar los datos, intente nuevamente.');
            });
    } else {
        form.reportValidity();
    }
});