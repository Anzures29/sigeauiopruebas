var currentPage = 1;
var registros = 10;
var response;
$(document).ready(function () {
    // Cargar la tabla al iniciar
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
    const ofertaTable = $("#tablaOferta");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/ofertas/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            ofertaTable.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (oferta) {
                    ofertaTable.append(`
                        <tr>
                            <td>${oferta.cv}</td>
                            <td>${oferta.ni}</td>
                            <td>${oferta.ca}</td>
                            <td>
                                <button class="boton-accion" onclick="abrirModalModificar('${oferta.cv}', '${oferta.ni}', '${oferta.ca}')">Modificar</button>
                                <button class="boton-accion" onclick="eliminar('${oferta.cv}', '${oferta.ni}', '${oferta.ca}')">Eliminar</button>
                            </td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                ofertaTable.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            ofertaTable.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
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
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["modalRegistrarOferta", "modificarOferta", "eliminarOferta"];
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
        const modales = ["modalRegistrarOferta", "modificarOferta", "eliminarOferta"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
// Función para cargar los datos de la inscripción en el modal modificar
async function cargarDatosOferta(cv, ni, ca) {
    const nivel = document.getElementById('niMod');

    if (nivel) {
        document.getElementById('cvA').value = cv || '';
        document.getElementById('cvMod').value = cv || '';
        seleccionarOpcionPorTexto(nivel, ni);
        document.getElementById('caMod').value = ca || '';
    } else {
        console.error('Elemento con id "niMod" no encontrado');
    }
}

// Función genérica para cargar correctamente los datos de un select y el que se obtiene de una consulta
function seleccionarOpcionPorTexto(selectElement, texto) {
    if (!selectElement) {
        console.error('Elemento select no encontrado');
        return;
    }

    Array.from(selectElement.options).forEach(option => {
        // Validar que ambos valores sean cadenas antes de comparar
        if (typeof option.textContent === 'string' && typeof texto === 'string' &&
            option.textContent.trim() === texto.trim()) {
            option.selected = true;
        }
    });
}

// Llamadas a la función genérica para abrir los modales específicos
function abrirModalRegistrar() {
    abrirModal("modalRegistrarOferta");
}
function abrirModalModificar(cv, ni, ca) {
    abrirModal("modificarOferta");
    cargarDatosOferta(cv, ni, ca);
}
// Llamadas a la función genérica para cerrar los modales específicos
function cerrarModalRegistrar() {
    cerrarModal("modalRegistrarOferta");
}
function cerrarModalModificar() {
    cerrarModal("modificarOferta");
}
// Función para el envío de los datos del formulario
document.getElementById('btnRegistrarOferta').addEventListener('click', function () {
    const form = document.getElementById('formRegistrarOferta');
    if (form.checkValidity()) {
        const formData = new FormData(form);
        fetch('modales/ofertas/agregar.php', {
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
                    data = JSON.parse(text);  // Intentar parsear la respuesta como JSON
                } catch (error) {
                    console.error("Error al analizar JSON:", error, "Respuesta completa:", text);
                    alert("Hubo un problema al procesar la respuesta del servidor.");
                    return;  // Detener el flujo si la respuesta no es JSON válido
                }
                if (data.exito) {
                    alert(data.mensaje_final); // Se muestra el mensaje al usuario
                    setTimeout(function () { // Se recarga la página
                        location.reload();
                    }, 50);
                } else {
                    alert(data.mensaje_final); // Se muestra el mensaje al usuario
                }
            })
            .catch(error => {
                console.error("Error al enviar datos:", error);
                fetch('modales/ofertas/agregar.php', {
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
    const form = document.getElementById('formModificarOferta');
    // Validar el formulario antes de enviarlo
    if (form.checkValidity()) {
        const formData = new FormData(form);
        // Enviar los datos al servidor
        fetch('modales/ofertas/modificar.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json(); // Procesar respuesta JSON
            })
            .then(data => {
                if (data.exito) {
                    alert(data.mensaje_final);
                    cerrarModalModificar();
                    loadData();
                    // setTimeout(() => location.reload(), 1000); // Recargar página después de un segundo
                } else {
                    alert(data.mensaje_final);
                }
            })
            .catch(error => {
                console.error("Error al enviar datos:", error);
                fetch('modales/ofertas/modificar.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(text => {
                        console.error("Contenido de la respuesta del servidor:", text);
                    });
                alert("Error al enviar los datos, por favor intente de nuevo.");
            });
    } else {
        // Si el formulario no es válido, mostrar los errores
        form.reportValidity();
    }
});
// Función para enviar los datos a eliminar
async function eliminar(cv, ni, ca) {
    if (!confirm(`¿Está seguro que desea eliminar la oferta "${ni} en ${ca}"?`)) {
        return;
    }
    try {
        const response = await fetch('modales/ofertas/eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ cv, ca }).toString()
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final);
            loadData();
        } else {
            throw new Error(data.mensaje_final || 'Error desconocido en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert(`Hubo un problema al enviar los datos: ${error.message}`);
    }
}