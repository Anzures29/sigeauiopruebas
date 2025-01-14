const ocultar = document.getElementById('ocultar'); // Logo
const barralateral = document.getElementById('barraLateral'); // Barra de navegación
const contenedor = document.getElementById('contenedorModulo'); // Contenedor de los módulos
const menu = document.getElementById('menu'); // Menú responsivo
// Manejar el evento de clic en el logo
ocultar.addEventListener('click', function () {
    barralateral.classList.toggle('ocultar'); // Se asigna la clase ocultar a la barra de navegación
    contenedor.style.marginLeft = '125px'; // El contenedor se reduce
    if (barralateral.classList.contains('ocultar')) {
    } else {
        contenedor.style.marginLeft = '250px'; // El contenedor se expande
    }
});
// MANEJO DEL EVENTO CLICK EN MENU
menu.addEventListener("click", () => {
    // Si la clase no está presente, la agrega; si está presente, la elimina
    barralateral.classList.toggle("max-barra-lateral");
    // Verifica si 'barralateral' tiene la clase 'max-barra-lateral'
    if (barralateral.classList.contains("max-barra-lateral")) {
        // Oculta el primer hijo del elemento 'menu' (generalmente el icono de menú cerrado)
        menu.children[0].style.display = "none"; // Menu
        // Muestra el segundo hijo del elemento 'menu' (generalmente el icono de menú abierto)
        menu.children[1].style.display = "block"; // Cerrar
        // Esto normalmente desplaza el contenido a la derecha para dar espacio a la barra lateral
        contenedor.style.marginLeft = '250px';
        if (barralateral.classList.contains('max-barra-layteral') || barralateral.classList.contains('ocultar')) {
            contenedor.style.marginLeft = '125px'; // El contenedor se reduce
        }
    } else {
        // Muestra el primer hijo del elemento 'menu' (generalmente el icono de menú cerrado)
        menu.children[1].style.display = "none";
        // Oculta el segundo hijo del elemento 'menu' (generalmente el icono de menú abierto)
        menu.children[0].style.display = "block";
        // Restablece el margen izquierdo del elemento 'contenedor' a '0px', 
        // lo que significa que no habrá desplazamiento cuando la barra lateral esté oculta
        contenedor.style.marginLeft = '0px';
    }
});