(function () {
    var minInactivo = 15; // Minutos de inactividad
    var timeInactividad = minInactivo * 60 * 1000; // Tiempo de inactividad en milisegundos
    var minAlerta = 2; // Minutos para la alerta de inactividad
    var alerInactividad = timeInactividad - (minAlerta * 60 * 1000); // Tiempo para la alerta en milisegundos
    // Funciones de manejo de inactividad
    function mostrarAlerta() {
        alert("Tu sesión está a punto de expirar. Por favor, guarda tu trabajo.");
    }
    function redirigirLogin() {
        window.location.href = "login.php";
    }
    // Se reinician los temporizadores en caso de actividad del usuario
    function reiniciarTemporizadores() {
        clearTimeout(alertaTimeout);
        clearTimeout(redirigirTimeout);
        alertaTimeout = setTimeout(mostrarAlerta, alerInactividad); // Tiempo para la alerta
        redirigirTimeout = setTimeout(redirigirLogin, timeInactividad); // Tiempo para redirigir
    }
    // Se inician los temporizadores
    var alertaTimeout = setTimeout(mostrarAlerta, alerInactividad);
    var redirigirTimeout = setTimeout(redirigirLogin, timeInactividad);
    // Se escuchan eventos de interacción del usuario
    window.onload = reiniciarTemporizadores;
    document.onmousemove = reiniciarTemporizadores;
    document.onkeypress = reiniciarTemporizadores;
    document.onclick = reiniciarTemporizadores;
    document.onscroll = reiniciarTemporizadores;
})();