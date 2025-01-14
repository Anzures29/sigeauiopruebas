// Función para cargar un módulo en el contenedor principal de la página
function cargarModulo(modulo) {
    // Realiza una solicitud AJAX para cargar el contenido del módulo
    $.ajax({
        url: 'cargarmodulos.php?_=' + new Date().getTime(), // URL para el servidor
        type: 'GET', // Método HTTP utilizado para la solicitud
        data: { modulo: modulo }, // Parámetro que identifica el módulo a cargar
        success: function (data) {
            // Si la solicitud es exitosa, se inserta el contenido del módulo en el contenedor
            $('#contenedorModulo').html(data);
        },
        error: function () {
            // En caso de error, se muestra un mensaje en el contenedor
            $('#contenedorModulo').html("<h2>Error al cargar el módulo: " + modulo + "</h2>");
        }
    });
}
// Se ejecuta cuando el DOM está completamente cargado
$(document).ready(function () {
    // Carga el módulo inicial al cargar la página
    cargarModulo('inscripciones');
});
// Evento para manejar clics en los enlaces del menú de navegación
$(document).on('click', '.navegacion a', function (e) {
    var modulo = $(this).attr('href').split('=')[1]; // Extrae el nombre del módulo del enlace (asumiendo que está en la URL)
    if ($(this).closest('li').hasClass('dropdown')) {
        // Si el enlace pertenece a un menú desplegable, se previene la acción predeterminada
        e.preventDefault();
    } else {
        // Si no es un menú desplegable, se previene la acción predeterminada y se carga el módulo
        e.preventDefault();
        cargarModulo(modulo);
    }
});
