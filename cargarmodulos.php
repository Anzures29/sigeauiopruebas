<?php
// Función para verificar permisos de acceso a los módulos
function tieneAcceso($modulo) {
    $modulos_permitidos = [
        'ofertas',
        'inscripciones',
        'reinscripciones',
        'alumnos',
        'documentacion',
        'asistencias',
        'tutores',
        'escuelas',
        'pagos'
    ];
    return in_array($modulo, $modulos_permitidos) ||
           (in_array($modulo, ['reportePagos', 'bitacora', 'empleados', 'usuarios']));
}
// Verificar si se recibió el módulo solicitado y si el usuario tiene acceso a él
if (isset($_GET['modulo'])) {
    $modulo = $_GET['modulo'];
    include("modulos/{$modulo}.php");
    // Incluir el script de inicialización específico del módulo
    echo "<script>if (typeof inicializarModulo_{$modulo} === 'function') { inicializarModulo_{$modulo}(); }</script>";
} else {
    echo "<h2>No tienes acceso a este módulo.</h2>";
}
?>