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
// Función para cargar los datos
function loadData() {
    const tablaInscripciones = $("#tablaInscripciones");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/documentacion/consultar.php',
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
                    const user = userRole;
                    tablaInscripciones.append(`
                        <tr>
                            <td>${inscripciones.nC}</td>
                            <td>${inscripciones.alumno}</td>
                            <td>${inscripciones.ni}</td>
                            <td>${inscripciones.carrera}</td>
                            <td>
                            <button onclick="abrirModal('${inscripciones.cN}', '${inscripciones.nC}', '${user}')" class="boton-accion">Documentos</button>
                            </td>
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
// Función para abrir el modal
async function abrirModal(nivelEducativo, numeroControl, user) {
    document.getElementById("modalDocumentacion").style.display = "flex";
    var modalContent = document.querySelector("#modalDocumentacion .modal-content");
    modalContent.classList.remove("show");
    void modalContent.offsetWidth;
    modalContent.classList.add("show");
    const documentosRequeridosPorNivel = {
        "1": ["Acta Nacimiento", "CURP", "Certificado Secundaria", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"],
        "2": ["Acta Nacimiento", "CURP", "Cert. Bachillerato", "Certificado Validacion", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"],
        "3": ["Acta Nacimiento", "CURP", "Certificado Licenciatura", "Cédula Licenciatura", "Título Licenciatura", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"],
        "4": ["Acta Nacimiento", "CURP", "Certificado Maestría", "Cédula Maestría", "Título Maestría", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"]
    };
    const documentosContainer = document.getElementById("documentosContainer");
    documentosContainer.innerHTML = "";
    try {
        const response = await fetch(`modales/documentacion/consultarArchivo.php?nC=${numeroControl}`);
        const { documentos: documentosGuardados = [] } = await response.json();
        const documentosRequeridos = documentosRequeridosPorNivel[nivelEducativo] || [];
        documentosRequeridos.forEach((documento, index) => {
            // Div en el que se colocarán los elementos
            const div = document.createElement("div");
            div.className = "form-group";
            // Etiquetas con los nombes de los documentos requeridos
            const label = document.createElement("label");
            label.textContent = documento;
            // Selector del documento
            const input = document.createElement("input");
            input.type = "file";
            input.name = `documento_${index}`;
            input.setAttribute("data-documento", documento);
            input.accept = "application/pdf";
            // Icono PDF para abrir el archivo
            const icon = document.createElement("a");
            icon.target = "_blank";
            icon.className = "fas fa-file-pdf"; // Icono de PDF
            icon.style.color = "gray"; // Color inicial del icono (gris)
            icon.style.marginLeft = "30px";
            icon.style.cursor = "pointer";
            icon.style.display = "inline";
            // Ícono Eliminar para eliminar el archivo
            const deleteIcon = document.createElement("a");
            deleteIcon.className = "fas fa-trash-alt"; // Icono de eliminar
            deleteIcon.style.color = "red";
            deleteIcon.style.marginLeft = "10px";
            deleteIcon.style.cursor = "pointer";
            deleteIcon.style.display = "none"; // Oculto inicialmente
            // Evento para manejar la subida de archivo
            input.addEventListener("change", async () => {
                const archivo = input.files[0];
                if (!archivo || archivo.type !== "application/pdf") {
                    alert("Por favor, selecciona un archivo PDF válido.");
                    input.value = "";
                    icon.style.display = "none";
                    return;
                }
                const formData = new FormData();
                formData.append("archivo", archivo);
                formData.append("documento", documento);
                formData.append("cN", nivelEducativo);
                formData.append("nC", numeroControl);
                try {
                    const response = await fetch("modales/documentacion/guardarArchivo.php", {
                        method: "POST",
                        body: formData,
                    });
                    const result = await response.json();
                    if (result.success) {
                        icon.href = URL.createObjectURL(archivo);
                        icon.style.color = "red";
                        icon.style.display = "inline";
                        deleteIcon.style.display = "inline";
                        alert("Archivo guardado correctamente");
                    } else {
                        alert("Error al subir el archivo: " + (result.error || "Error desconocido"));
                    }
                } catch (error) {
                    console.error("Error al intentar subir el archivo:", error);
                    alert("Hubo un problema al subir el archivo.");
                }
            });
            // Estado inicial del icono del documento
            const documentoGuardado = documentosGuardados.find(doc => doc.ruta.includes(documento));
            if (documentoGuardado) {
                icon.href = documentoGuardado.ruta;
                icon.style.display = "inline";
                icon.style.color = "red";
                deleteIcon.style.display = "inline";
            }
            // Evento para eliminar el archivo al hacer clic en el icono de eliminar
            deleteIcon.addEventListener("click", async function () {
                if (confirm("¿Estás seguro de que deseas eliminar este archivo?")) {
                    const result = await eliminarArchivo(numeroControl, documento, nivelEducativo);
                    if (result.success) {
                        alert("Archivo eliminado correctamente");
                        icon.style.color = "gray";
                        icon.removeAttribute("href");
                        deleteIcon.style.display = "none";
                    } else {
                        alert("Error al eliminar el archivo: " + (result.error || "Error desconocido"));
                    }
                }
            });
            div.appendChild(label);
            div.appendChild(input);
            div.appendChild(icon);
            if (user === 'Rector') {
                div.appendChild(deleteIcon);
            }
            documentosContainer.appendChild(div);
        });
    } catch (error) {
        console.error("Error al cargar documentos:", error);
    }
}
// Función para eliminar el archivo
async function eliminarArchivo(numeroControl, documento, nivelEducativo) {
    try {
        const response = await fetch("modales/documentacion/eliminarArchivo.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ numeroControl, documento, nivelEducativo })
        });
        const result = await response.json();
        return result;
    } catch (error) {
        console.error("Error al intentar eliminar el archivo:", error);
        return { success: false, error: "Hubo un problema al intentar eliminar el archivo." };
    }
}
// Función para cerrar el modal
function cerrarModal() {
    var modal = document.getElementById("modalDocumentacion");
    var modalContent = modal.querySelector(".modal-content");

    modalContent.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 300);
}
// Cerrar el modal al hacer clic fuera del contenido
window.onclick = function (event) {
    const modal = document.getElementById("modalDocumentacion");
    if (event.target == modal) {
        cerrarModal();
    }
}
document.body.addEventListener("keydown", function (event) {
    if (event.code === 'Escape') {
        cerrarModal();
    }
});