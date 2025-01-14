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
    const tablaUsuarios = $("#tablaUsuarios");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");

    $.ajax({
        url: 'modales/usuarios/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar
        },
        success: function (data) {
            response = data;
            tablaUsuarios.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (usuarios) {
                    tablaUsuarios.append(`
                        <tr>
                            <td>${usuarios.us}</td>
                            <td>${usuarios.rol}</td>
                            <td>${usuarios.empleado}</td>
                            <td>
                                <button class="boton-accion" onclick="abrirModal('${usuarios.cv}', '${usuarios.us}', '${usuarios.empleado}', '${usuarios.em}')">Modificar</button>
                                <button class="boton-accion" onclick="eliminarUsuario('${usuarios.cv}', '${usuarios.us}')">Eliminar</button>
                            </td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaUsuarios.html("<tr><td colspan='4'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaUsuarios.html("<tr><td colspan='4'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}
// Función para cerrar el modal haciendoclick fuera del modal
window.onclick = function (event) {
    const modales = ["modificar"];
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
        const modales = ["modificar"];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === "flex") {
                cerrarModal(modalId);
            }
        });
    }
});
// Función genérica para abrir cualquier modal
function abrirModal(cv, us, empleado, em) {
    const modal = document.getElementById('modificar');
    modal.style.display = "flex";
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    void modalContent.offsetWidth;
    modalContent.classList.add("show");

    document.getElementById('cvUsu').value = cv || '';
    document.getElementById('empleado').value = empleado || '';
    document.getElementById('em').value = em || '';
    document.getElementById('usMod').value = us || '';
}
// Función genérica para cerrar cualquier modal
function cerrarModal() {
    const modal = document.getElementById('modificar');
    const modalContent = modal.querySelector(".modal-content");
    modalContent.classList.remove("show");
    setTimeout(function () {
        modal.style.display = "none";
    }, 400);
}
// Función para eliminar un usuario
async function eliminarUsuario(cv, us) {
    if (!confirm(`¿Está seguro de que desea eliminar el usuario ${us}?`)) {
        return;
    }
    try {
        const response = await fetch('modales/usuarios/eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ cv, us }).toString()
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
// Función para el envío de los datos a modificar
document.getElementById('btnModificar').addEventListener('click', async () => {
    const form = document.getElementById('formModificar');
    const formData = new FormData(form);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    let enviarCredenciales = true;
    const confirmacion = confirm(
        "¿Desea enviar las nuevas credenciales al empleado?"
    );
    if (!confirmacion) {
        enviarCredenciales = false;
    }
    formData.append('enviarCredenciales', enviarCredenciales ? '1' : '0');

    try {
        const response = await fetch('modales/usuarios/modificar.php', {
            method: 'POST',
            body: formData,
        });
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor.');
        }
        const data = await response.json();
        if (data.exito) {
            alert(data.mensaje_final, 'success');
            cerrarModal();
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