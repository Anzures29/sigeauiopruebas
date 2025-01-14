<?php
include 'config.php'; // Configuraciones de seguridad para las cookies
include_once('estatus.php');
// Se verifica si la sesión y el rol están activos
if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
    // Si no están, se redirige al usuario al login
    header('Location: login.php');
    exit;
}
// Se regenera el ID de sesión si es la primera carga de la página
session_regenerate_id(true); // Se previene el secuestro de sesión
$rol = $_SESSION['rol']; // Se guarda el rol en una variable
// Se define el tiempo de inactividad
$minInactivo = 15; // Minutos de inactividad
$timeInactividad = $minInactivo * 60; // Minutos de inactividad en segundos
// Si la sesión ha expirado
if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > $timeInactividad)) {
    session_destroy(); // Destruye la sesión
    header("Location: login.php");
    exit();
}
$_SESSION['ultima_actividad'] = time(); // Establecer el tiempo actual como último tiempo activo
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador SIGEAUIO</title>
    <link rel="stylesheet" href="css/styleIndex.css">
    <link rel="icon" href="/xampp/htdocs/favicon.ico" type="image/x-icon">
</head>

<body>
    <div id="menu" class="menu">
        <ion-icon name="menu-outline"></ion-icon>
        <ion-icon name="close-outline"></ion-icon>
    </div>
    <div class="barra-lateral" id="barraLateral">
        <div class="img">
            <img id="ocultar" src="img/logo.png">
            <br>
        </div>
        <nav class="navegacion">
            <ul>
                <li>
                    <a id="inbox" href="index.php?modulo=ofertas">
                        <ion-icon name="school-outline"></ion-icon>
                        <span>Oferta Académica</span>
                    </a>
                </li>
                <?php
                $menusComunes = [
                    'Académico' => [
                        ['inscripciones', 'clipboard-outline', 'Inscripciones'],
                        ['documentacion', 'documents-outline', 'Documentación'],
                        ['asistencias', 'calendar-outline', 'Asistencias'],
                        ['egresados', 'school-outline', 'Egresados']
                    ],
                    'Alumnos' => [
                        ['alumnos', 'person-outline', 'Información General']
                    ]
                ];
                $menus = [
                    'Rector' => array_merge($menusComunes, [
                        'Finanzas' => [
                            ['pagos', 'cash-outline', 'Pagos'],
                            ['reportePagos', 'bar-chart-outline', 'Reportes']
                        ],
                        'Personal' => [
                            ['empleados', 'person-add-outline', 'Empleados'],
                            ['usuarios', 'person-circle-outline', 'Usuarios']
                        ],
                        'Bitácora' => [
                            ['bitacora', 'book-outline', 'Bitácora']
                        ]
                    ]),
                    'Recepcionista' => array_merge($menusComunes, [
                        'Pagos' => [
                            ['pagos', 'cash-outline', 'Pagos']
                        ]
                    ])
                ];
                // Se generan los menús mediante esta función
                function generarMenu($secciones)
                {
                    foreach ($secciones as $titulo => $items) {
                        echo '<li class="dropdown">';
                        echo '<a href="#"><ion-icon name="clipboard-outline"></ion-icon><span>' . $titulo . '</span></a>';
                        echo '<ul class="submenu">';
                        foreach ($items as $item) {
                            echo '<li><a href="index.php?modulo=' . $item[0] . '">';
                            echo '<ion-icon name="' . $item[1] . '"></ion-icon>';
                            echo '<span>' . $item[2] . '</span>';
                            echo '</a></li>';
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                }
                // Se renderiza el menú dependiendo del rol
                function renderizarMenu($rol, $menus)
                {
                    if (isset($menus[$rol])) {
                        generarMenu($menus[$rol]);
                    }
                }
                renderizarMenu($rol, $menus);
                ?>
                <li>
                    <a href="" onclick="window.location.href='salir.php';"><!--Se redirecciona al usuario a salir.php-->
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="contenedor" id="contenedorModulo"> <!--En este div se incluirán los módulos que seleccione el usuario--></div>
    <!--Scripts para el uso de los iconos-->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        if (typeof jQuery === 'undefined') {
            document.write('<script src="js/jquery.js"><\/script>');
        }
    </script>
    <!--Scripts para el comportamiento de la página-->
    <script src="js/barra.js"></script>
    <script src="js/submenus.js"></script>
    <script src="js/cargarmodulos.js"></script>
    <script src="js/inactividad.js"></script>

</body>

</html>