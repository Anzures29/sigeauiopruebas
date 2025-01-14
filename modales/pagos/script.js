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
    const tablaPagos = $("#tablaPagos");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/pagos/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaPagos.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (pagos) {
                    let actionButtons = '';
                    let actionColumn = ''; // Define an empty string for the action column
                    if (userRole === 'Rector') {
                        actionButtons = `
                                <button class="boton-accion" onclick="abrirModalModificar('${pagos.fo}')">Modificar</button>
                                <button class="boton-accion" onclick="eliminar('${pagos.fo}', '${pagos.cT}', '${pagos.nC}', '${pagos.alumno}')">Eliminar</button>
                        `;
                        actionColumn = `<td>${actionButtons}</td>`; // Populate actionColumn only if the user is rector
                    }
                    tablaPagos.append(`
                        <tr>
                            <td>${pagos.fo}</td>
                            <td>${pagos.fe}</td>
                            <td>${pagos.alumno}</td>
                            <td>${pagos.tipo}</td>
                            <td>${pagos.de}</td>
                            <td>${pagos.ca}</td>
                            <td>${pagos.im}</td>
                            <td>${pagos.ca * pagos.im}</td>
                            <td>${pagos.forma}</td>
                            ${actionColumn}
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaPagos.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaPagos.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
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
// Función genérica para la consulta de alumnos; ya sea por nivel Bachillerato o por carrera
function cargarOpciones(url, selectElement) {
    fetch(url)
        .then(response => response.json())
        .then(datos => {
            selectElement.innerHTML = "<option value=''>Seleccione</option>";
            datos.forEach(dato => {
                let option = document.createElement("option");
                option.value = dato.nC;
                option.textContent = dato.alumno;
                selectElement.appendChild(option);
            });
        })
        .catch(error => console.error("Error al cargar opciones:", error));
}
// Función para cargar las carreras según el nivel seleccionado
document.getElementById("nivel").addEventListener("change", function () {
    var nivelSeleccionado = this.value;
    var carreraSelect = document.getElementById("carrera");
    var alumnoSelect = document.getElementById("nC");
    carreraSelect.innerHTML = "<option value=''>Seleccione</option>";
    alumnoSelect.innerHTML = "<option value=''>Seleccione</option>";
    if (nivelSeleccionado === "1") {
        carreraSelect.disabled = true;
        cargarOpciones(`modales/pagos/consultarAlumnos.php?nivel=${nivelSeleccionado}`, alumnoSelect);
    } else {
        carreraSelect.disabled = false;
        if (nivelSeleccionado !== "") {
            fetch(`modales/pagos/consultarCarreras.php?nivel=${nivelSeleccionado}`)
                .then(response => response.json())
                .then(carreras => {
                    carreras.forEach(carrera => {
                        let option = document.createElement("option");
                        option.value = carrera.cv;
                        option.textContent = carrera.ca;
                        carreraSelect.appendChild(option);
                    });
                });
        }
    }
});
// Función para cargar los alumnos según la carrera seleccionada
document.getElementById("carrera").addEventListener("change", function () {
    let carreraSeleccionada = this.value;
    let alumnoSelect = document.getElementById("nC");
    if (carreraSeleccionada) {
        cargarOpciones(`modales/pagos/consultarAlumnos.php?carrera=${carreraSeleccionada}`, alumnoSelect);
    }
});
// Función para escuhcar el cambio de tipo de pago
document.getElementById("tipoPago").addEventListener("change", function () {
    let tpSelect = this.value;
    let ca = document.getElementById("ca");
    if (tpSelect == 1 || tpSelect == 2 || tpSelect == 3) {
        ca.value = 1;
        ca.readOnly = true;
    } else {
        ca.readOnly = false;
    }
});
// Función para cerrar el modal haciendo click fuera del modal
window.onclick = function (event) {
    const modales = ["registrar", "modificar", "eliminar"];
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
        const modales = ["registrar", "modificar", "eliminar"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
// Llamadas a la función genérica para abrir los modales específicos
function abrirModalRegistrar() {
    abrirModal("registrar");
}
function abrirModalModificar(fo) {
    abrirModal("modificar");
    cargarDatosModificar(fo);
}
// Llamadas a la función genérica para cerrar los modales específicos
function cerrarModalRegistrar() {
    cerrarModal("registrar");
}
function cerrarModalModificar() {
    cerrarModal("modificar");
}
// Función general para calcular el total del pago
function calcularTotalGenerico(cantidadId, importeId, totalId) {
    const cantidad = parseFloat(document.getElementById(cantidadId).value) || 0;
    const importe = parseFloat(document.getElementById(importeId).value) || 0;
    document.getElementById(totalId).value = (cantidad * importe).toFixed(2);
}
// Calcular el total en el modal de registro
document.getElementById("ca").addEventListener("input", () => calcularTotalGenerico("ca", "im", "tot"));
document.getElementById("im").addEventListener("input", () => calcularTotalGenerico("ca", "im", "tot"));
// Calcular el total en el modal de modificar
document.getElementById("caMod").addEventListener("input", () => calcularTotalGenerico("caMod", "imMod", "totMod"));
document.getElementById("imMod").addEventListener("input", () => calcularTotalGenerico("caMod", "imMod", "totMod"));
// Función para resetear el formulario
function resetForm() {
    const form = document.getElementById('formRegistrarPago');
    form.reset();
}
// Función para Registrar los datos del pago
document.getElementById('btnRegistrarPago').addEventListener('click', function () {
    const form = document.getElementById('formRegistrarPago'); // Formulario
    if (form.checkValidity()) { // Se verifica si el formulario es válido
        const formData = new FormData(form);
        // Primer fetch para registrar el pago
        fetch('modales/pagos/agregar.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la red: " + response.status + " " + response.statusText);
                }
                return response.text();
            })
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text); // Parsear respuesta como JSON
                } catch (error) {
                    console.error("Error al analizar JSON:", error, "Respuesta completa:", text);
                    alert("Hubo un problema al procesar la respuesta del servidor.");
                    return; // Detener flujo si no es JSON válido
                }
                if (data.exito) { // Si el pago se registró correctamente
                    alert(data.mensaje_final); // Mostrar mensaje al usuario
                    cerrarModalRegistrar(); // Se cierra el modal
                    // Se formatean los datos del formulario para generar el reporte pdf (recibo de pago)
                    const queryString = new URLSearchParams();
                    formData.forEach((value, key) => {
                        queryString.append(key, value);
                    });
                    // Redirigir a la ventana del pdf
                    window.open('', '_blank').location.href = 'modales/pagos/imprimirReporte.php?' + queryString.toString();
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else { // Si el pago no se registró correctamente
                    alert(data.mensaje_final); // Mostrar mensaje al usuario
                }
            })
            .catch(error => {
                console.error("Error de red o al obtener JSON:", error);
                alert("Ocurrió un error al enviar el formulario. Verifica la conexión de red o revisa la consola.");
            });
    } else {
        form.reportValidity();
    }
});
// Función para cargar los datos de la inscripción en el modal modificar
async function cargarDatosModificar(fo) {
    const formaPagoMod = document.getElementById('formaPagoMod');
    try {
        const response = await fetch('modales/pagos/consultarPago.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `fo=${encodeURIComponent(fo)}`
        });
        const data = await response.json();
        if (data.exito) {
            const pago = data.pago;
            const formaPagoActual = pago.forma;
            document.getElementById('cNMod').value = pago.cN || '';
            document.getElementById('cCMod').value = pago.cC || '';
            document.getElementById('foMod').value = pago.fo || '';
            document.getElementById('feMod').value = pago.fe || '';
            document.getElementById('nCA').value = pago.nC || '';
            document.getElementById('alumno').value = pago.alumno || '';
            document.getElementById('tpa').value = pago.cT || '';
            document.getElementById('tipoPagoMod').value = pago.tipo || '';
            document.getElementById('deMod').value = pago.de || '';
            document.getElementById('caMod').value = pago.ca || '';
            document.getElementById('imA').value = pago.im || '';
            document.getElementById('imMod').value = pago.im || '';
            document.getElementById('totMod').value = pago.ca * pago.im || '';
            Array.from(formaPagoMod.options).forEach(option => {
                if (option.textContent === formaPagoActual) {
                    option.selected = true;
                }
            });
        } else {
            alert(data.mensaje || 'No se pudieron cargar los datos de la inscripción.');
        }
    } catch (error_1) {
        return console.error('Error al obtener los datos:', error_1);
    }
}
// Función para Modifcar los datos del pago
document.getElementById('btnModificar').addEventListener('click', async () => {
    const form = document.getElementById('formModificar'); // Formulario
    if (form.checkValidity()) { // Se verifica si el formulario es válido
        const formData = new FormData(form);
        try {
            // Se realiza la solicitud fetch para el envío de los datos
            const response = await fetch('modales/pagos/modificar.php', { // Archivo modificar.php
                method: 'POST',
                body: formData,
            });
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            const data = await response.json();
            if (data.exito) { // Si el pago se registró correctamente
                alert(data.mensaje_final); // Mostrar mensaje al usuario
                cerrarModalModificar(); // Se llama a la función para cerrar el modal
                loadData(); // Llamar a la función para recargar los registros
                // Se formatean los datos del formulario para generar el reporte pdf (recibo de pago)
                const queryString = new URLSearchParams();
                formData.forEach((value, key) => {
                    queryString.append(key, value);
                });
                // Redirigir a la ventana del pdf
                window.open('', '_blank').location.href = 'modales/pagos/imprimirReporteModificar.php?' + queryString.toString();
            } else { // Si el pago no se modificó correctamente
                alert(data.mensaje_final); // Mostrar mensaje al usuario
            }
        } catch (error) {
            console.error('Error al enviar datos:', error);
            alert('Hubo un problema al enviar los datos, intente nuevamente.');
        }
    } else {
        form.reportValidity();
    }
});
// Función para Eliminar un pago
async function eliminar(fo, cT, nC, alumno) { // Se reciben los datos necesarios
    // Si la confirmación del usuario es falsa (cancelar) se retorna y se cierra la función
    if (!confirm(`¿Está seguro de que desea eliminar el pago ${fo} del alumno ${alumno}?`)) {
        return;
    }
    // Si es true (aceptar)
    try {
        // Se realiza la solicitud fetch para el envío de los datos
        const response = await fetch('modales/pagos/eliminar.php', { // Archivo eliminar.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ fo, cT, nC, alumno }).toString()
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) { // Si el pago se elimminó correctamente
            alert(data.mensaje_final); // Mostrar mensaje al usuario
            loadData(); // Llamar a la función que carga los registros
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un problema al enviar los datos, intente nuevamente.');
    }
}