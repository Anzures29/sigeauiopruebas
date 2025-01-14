var currentPage = 1;
var registros = 10;
var response;
$(document).ready(function () {
    loadData();
    // Manejo del filtro de cantidad de registros
    $("#registros").on("change", function () {
        registros = this.value;
        currentPage = 1; // Reiniciar a la primera página
        loadData();
    });
    // Búsqueda en tiempo real
    $("#buscar").on("input", function () {
        currentPage = 1; // Reiniciar a la primera página al hacer búsqueda
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
    const tablaEmpleados = $("#tablaEmpleados");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/empleados/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaEmpleados.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (empleados) {
                    tablaEmpleados.append(`
                        <tr>
                            <td>${empleados.nom}</td>
                            <td>${empleados.apellidos}</td>
                            <td>${empleados.te}</td>
                            <td>${empleados.em}</td>
                            <td>${empleados.ca}</td>
                            <td>${empleados.co}</td>
                            <td>${empleados.rol}</td>
                            <td>${empleados.su}</td>
                            <td>
                                <button class="boton-accion" onclick="abrirModalModificar('${empleados.cv}')">Modificar</button>
                                <button class="boton-accion" onclick="eliminarEmpleado('${empleados.cv}', '${empleados.nom}')">Eliminar</button>
                            </td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaEmpleados.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaEmpleados.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}// Función para cargar los datos de la inscripción en el modal modificar
async function cargarDatosModificar(cv) {
    const municipioMod = document.getElementById('municipioMod');
    const coloniaMod = document.getElementById('coloniaMod');
    const rolMod = document.getElementById('rolMod');
    try {
        const response = await fetch('modales/empleados/consultarEmpleado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `cv=${encodeURIComponent(cv)}`
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) {
            const empleado = data.empleado;
            // Llenar los campos de texto
            document.getElementById('cvMod').value = empleado.cv || '';
            document.getElementById('nomMod').value = empleado.nom || '';
            document.getElementById('aPMod').value = empleado.aP || '';
            document.getElementById('aMMod').value = empleado.aM || '';
            document.getElementById('teMod').value = empleado.te || '';
            document.getElementById('emMod').value = empleado.em || '';
            document.getElementById('caMod').value = empleado.ca || '';
            document.getElementById('suMod').value = empleado.su || '';
            document.getElementById('correoActual').value = empleado.em || '';
            // Seleccionar opciones en los dropdowns
            seleccionarOpcionPorTexto(municipioMod, empleado.mu);
            coloniaMod.innerHTML = `<option value=''>${empleado.co}</option>`;
            // seleccionarOpcionPorTexto(coloniaMod, empleado.co);
            seleccionarOpcionPorTexto(rolMod, empleado.rol);
        } else {
            alert(data.mensaje || 'No se pudieron cargar los datos del empleado.');
        }
    } catch (error) {
        console.error('Error al obtener los datos:', error);
        alert('Hubo un problema al cargar los datos. Intente nuevamente.');
    }
}

// Función auxiliar para seleccionar una opción por su texto
function seleccionarOpcionPorTexto(selectElement, texto) {
    Array.from(selectElement.options).forEach(option => {
        option.selected = option.textContent.trim() === texto.trim();
    });
}

// Función para eliminar un empleado
async function eliminarEmpleado(cv, nom) {
    if (!confirm(`Se eliminarán todos los datos del empleado ${nom}. ¿Está seguro de que desea continuar?`)) {
        return;
    }
    try {
        const response = await fetch('modales/empleados/eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ cv, nom }).toString()
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final);
            loadData();
            // setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.mensaje_final);
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un problema al enviar los datos, intente nuevamente.');
    }
}
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["registrar", "modificar"];
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
        const modales = ["registrar", "modificar"];
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
function abrirModalRegistrar() {
    abrirModal("registrar");
}
function abrirModalModificar(cv) {
    abrirModal("modificar");
    cargarDatosModificar(cv);
}
// Llamadas a la función genérica para cerrar los modales específicos
function cerrarModalRegistrar() {
    cerrarModal("registrar");
}
function cerrarModalModificar() {
    cerrarModal("modificar");
}

// Función para cargar las colonias del municipio seleccionado Registrar
document.getElementById("municipio").addEventListener("change", function () {
    cargarColonias("municipio", "colonia");
});
// Función para cargar las colonias del municipio seleccionado Modificar
document.getElementById("municipioMod").addEventListener("change", function () {
    cargarColonias("municipioMod", "coloniaMod");
});
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
// Función para el envío de los datos del formulario
document.getElementById('btnRegistrarEmpleado').addEventListener('click', function () {
    const form = document.getElementById('formRegistrarEmpleado');
    if (form.checkValidity()) {
        const formData = new FormData(form);
        fetch('modales/empleados/agregar.php', {
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
                    data = JSON.parse(text);
                } catch (error) {
                    console.error("Error al analizar JSON:", error, "Respuesta completa:", text);
                    alert("Hubo un problema al procesar la respuesta del servidor.");
                    return;
                }
                if (data.exito) {
                    alert(data.mensaje_final);
                    cerrarModalRegistrar();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert("Favor de verificar los datos ingresados");
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
// Función para el envío de los datos a modificar
document.getElementById('btnModificar').addEventListener('click', async () => {
    const form = document.getElementById('formModificar');
    const formData = new FormData(form);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const correoActual = document.getElementById('correoActual').value; // El correo actual
    const correoNuevo = formData.get('emMod'); // El nuevo correo
    let actualizarUsuario = true;
    if (correoActual !== correoNuevo) {
        const confirmacion = confirm(
            "El correo ha cambiado. ¿Desea actualizar también los datos del usuario?"
        );
        if (!confirmacion) {
            actualizarUsuario = false;
        }
    }
    formData.append('actualizarUsuario', actualizarUsuario ? '1' : '0');
    try {
        const response = await fetch('modales/empleados/modificar.php', {
            method: 'POST',
            body: formData,
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor.');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final, 'success');
            cerrarModalModificar();
            loadData();
            // setTimeout(() => location.reload(), 1000);
        } else {
            console.error('Respuesta del servidor:', data);
            alert(data.mensaje_final || 'Error al modificar los datos.', 'error');
        }

    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un problema al enviar los datos. Intente nuevamente.', 'error');
    }
});