var currentPage = 1;
var registros = 10;
var mes = $("#mes").val();
var response;
$(document).ready(function () {
    loadData();
    // Evento click para el botón "PDF"
    $("#imprimirPdf").on("click", function () {
        var mes = $("#mes").val(); // Obtén el valor del mes seleccionado
        var buscar = $("#buscar").val().trim(); // Obtén el texto del buscador
        imprimirPdf(mes, buscar); // Pasa ambos valores a la función
    });
    // Evento click para el botón "EXCEL"
    $("#imprimirExcel").on("click", function () {
        var mes = $('#mes').val(); // Obtén el valor del mes seleccionado
        var buscar = $("#buscar").val().trim(); // Obtén el texto del buscador
        imprimirExcel(mes, buscar); // Pasa ambos valores a la función
    });

    // Manejo del filtro de cantidad de registros
    $("#registros").on("change", function () {
        registros = this.value;
        currentPage = 1; // Reiniciar a la primera página
        loadData();
    });
    // Redirige al cambiar el mes seleccionado
    $("#mes").on("change", function () {
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
// Función para generar el reporte
function imprimirPdf(mes, buscar) {
    var url = 'modales/reportePagos/reportePdf.php?mes=' + mes + '&buscar=' + encodeURIComponent(buscar); // Incluye "buscar" en la URL
    window.open(url, '_blank'); // Abre el PDF en una nueva ventana
}
// Función para generar el reporte
function imprimirExcel(mes, buscar) {
    var url = 'modales/reportePagos/reporteExcel.php?mes=' + mes + '&buscar=' + encodeURIComponent(buscar); // Incluye "buscar" en la URL
    window.open(url, '_blank'); // Abre el reporte en una nueva ventana
}

// Función para cargar los datos
function loadData() {
    const tablaPagos = $("#tablaPagos");
    const buscar = $("#buscar").val().trim();
    const pageInfo = $("#pageInfo");
    $.ajax({
        url: 'modales/reportePagos/consultar.php',
        method: 'GET',
        dataType: 'json',
        data: {
            page: currentPage,
            limit: registros,
            buscar: buscar,
            mes: mes
        },
        success: function (data) {
            response = data;
            tablaPagos.empty();
            const startRecord = (currentPage - 1) * registros + 1;
            const endRecord = startRecord + data.data.length - 1;
            const totalRecords = response.totalRecords;
            $("#cantidad-registros").text(`Mostrando registros del ${startRecord} al ${Math.min(totalRecords, endRecord)} de un total de ${totalRecords} registros`);
            if (response.data.length > 0) {
                response.data.forEach(function (pagos) {
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
                        </tr>
                    `);
                });
                pageInfo.text(`Página ${response.currentPage} de ${response.totalPages}`);
            } else {
                tablaPagos.html("<tr><td colspan='9'>No se encontraron resultados</td></tr>");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            tablaPagos.html("<tr><td colspan='9'>Ocurrió un error al cargar los datos.</td></tr>");
        }
    });
}