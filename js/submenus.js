// Selecciona todos los elementos del menú principal que tienen submenú
const dropdowns = document.querySelectorAll('.dropdown > a');
dropdowns.forEach(dropdown => {
    dropdown.addEventListener('click', function (event) {
        event.preventDefault(); // Evita que se siga el enlace
        // Alterna la clase 'active' en el elemento padre (li)
        const parent = this.parentElement;
        parent.classList.toggle('active');
        // Cierra otros submenús si es necesario
        dropdowns.forEach(otherDropdown => {
            if (otherDropdown !== this) {
                otherDropdown.parentElement.classList.remove('active');
            }
        });
    });
});