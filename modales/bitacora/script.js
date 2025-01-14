var currentPage = 1;
var registros = 10;
var response;
var mes = $("#mes").val();
$(document).ready(function () {
    // Cargar la tabla al iniciar
    loadData();
    // Manejo del filtro de cantidad de registros
    $("#registros").on("change", function () {
        registros = this.value;
        currentPage = 1; // Reiniciar a la primera página
        loadData();
    });
    // Redirige al cambiar el mes seleccionado
    $("#mes").on("change", function() {
        mes = this.value;
        currentPage = 1;
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
// Redirige al cambiar el mes seleccionado
$("#mes").on("change", function() {
    mes = this.value;
    currentPage = 1; // Reiniciar la página actual
    loadData();
});
// Función para cargar los datos con AJAX
function loadData() {
    const tablaBitacora = $("#tablaBitacora");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    const mes = $("#mes").val(); // Obtener el mes seleccionado
    // Solicitud AJAX
    $.ajax({
        url: 'modales/bitacora/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar,
            mes: mes // Añadir parámetro de mes
        },
        success: function (data) {
            response = data;
            tablaBitacora.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, currentPage * registros)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (bitacora) {
                    tablaBitacora.append(`
                        <tr>
                            <td>${bitacora.us}</td>
                            <td>${bitacora.nombre}</td>
                            <td>${bitacora.ac}</td>
                            <td>${bitacora.fechahora}</td>
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaBitacora.html("<tr><td colspan='6'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud: " + textStatus, errorThrown);
            tablaBitacora.html("<tr><td colspan='6'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}