<?php
// Script para la verificación de los datos en la consola
// // Función para el envío de los datos del formulario
// document.getElementById('btnRegistrarEmpleado').addEventListener('click', function () {
//     const form = document.getElementById('formRegistrarEmpleado');
//     if (form.checkValidity()) {
//         const formData = new FormData(form);
//         // Mostrar los datos que se están enviando
//         console.log("Datos del formulario a enviar:", formData);
//         fetch('modales/empleados/agregar.php', {
//             method: 'POST',
//             body: formData
//         })
//             .then(response => {
//                 if (!response.ok) {
//                     throw new Error("Error en la red: " + response.status + " " + response.statusText);
//                 }
//                 return response.text();
//             })
//             .then(text => {
//                 let data;
//                 try {
//                     data = JSON.parse(text);  // Intentar parsear la respuesta como JSON
//                 } catch (error) {
//                     console.error("Error al analizar JSON:", error, "Respuesta completa:", text);
//                     alert("Hubo un problema al procesar la respuesta del servidor.");
//                     return;  // Detener el flujo si la respuesta no es JSON válido
//                 }
//                 // Mostrar en consola los datos recibidos
//                 console.log("Datos recibidos del servidor:", data.datos); // Muestra los datos que el servidor ha recibido
//                 if (data.exito) {
//                     console.log("Éxito:", data.mensaje_final);  // Mostrar mensaje de éxito
//                     alert(data.mensaje_final);  // Mostrar el mensaje de éxito al usuario
//                 } else {
//                     console.log("Errores encontrados:");
//                     // Si hay errores, mostrar los mensajes en consola
//                     data.mensajes && data.mensajes.forEach(mensaje => console.log(mensaje));
//                     alert("Hubo errores en el registro. Verifica los mensajes en la consola.");
//                 }
//             })
//             .catch(error => {
//                 console.error("Error de red o al obtener JSON:", error);
//                 alert("Ocurrió un error al enviar el formulario. Verifica la conexión de red o revisa la consola.");
//             });
//     } else {
//         form.reportValidity();
//     }
// });
// Incluye todos los datos en un arreglo para la verificación
// Respuesta inicial con los datos recibidos
$datosRecibidos = [
    'nom' => $nom,
    'aP' => $aP,
    'aM' => $aM,
    'te' => $te,
    'em' => $em,
    'municipio' => $municipio,
    'colonia' => $colonia,
    'ca' => $ca,
    'rol' => $rol,
    'su' => $su
];
$respuesta = ['exito' => true, 'mensaje_final' => 'Empleado registrado correctamente', 'datos' => $datosRecibidos];
// VALIDACIÓN DE DATOS RECIBIDOS
$datosRecibidos = [
    'fo' => $fo,
    'nC' => $nC,
    'alumno' => $alumno,
    'tpa' => $tpa,
    'cT' => $cT,
    'de' => $de,
    'ca' => $ca,
    'ima' => $ima,
    'im' => $im,
    'tot' => $tot,
    'cF' => $cF
];
echo json_encode(["exito" => true, "mensaje_final" => $datosRecibidos]);
exit();
$datosRecibidos = [
    'fo' => $fo,
    'nC' => $nC,
    'alumno' => $alumno,
    'tpa' => $tpa,
    'cT' => $cT,
    'de' => $de,
    'ca' => $ca,
    'ima' => $ima,
    'im' => $im,
    'tot' => $tot,
    'cF' => $cF
];
responder(false, $datosRecibidos);
exit();
// Muestra y detiene el script para depuración
echo "Datos personales del alumno:<br>";
echo "Número de Control: " . htmlspecialchars($nC) . "<br>\n";
echo "Matrícula: " . htmlspecialchars($ma) . "<br>\n";
echo "Apellido Paterno: " . htmlspecialchars($aP) . "<br>\n";
echo "Apellido Materno: " . htmlspecialchars($aM) . "<br>\n";
echo "Nombre: " . htmlspecialchars($nom) . "<br>\n";
echo "Lugar de Nacimiento: " . htmlspecialchars($na) . "<br>\n";
echo "Fecha de Nacimiento: " . htmlspecialchars($fN) . "<br>\n";
echo "Edad: " . htmlspecialchars($ed) . "<br>\n";
echo "CURP: " . htmlspecialchars($cu) . "<br>\n";
echo "Sexo: " . htmlspecialchars($se) . "<br>\n";
echo "Municipio: " . htmlspecialchars($cM) . "<br>\n";
echo "Colonia: " . htmlspecialchars($cCo) . "<br>\n";
echo "Calle: " . htmlspecialchars($ca) . "<br>\n";
echo "Tipo de Sangre: " . htmlspecialchars($ts) . "<br>\n";
echo "Alergias: " . htmlspecialchars($af) . "<br>\n";
echo "Teléfono: " . htmlspecialchars($te) . "<br>\n";
echo "Email: " . htmlspecialchars($em) . "<br>\n";
echo "<br>Datos de la Escuela de Procedencia:<br>\n";
echo "CCT: " . htmlspecialchars($cct) . "<br>\n";
echo "Grado Escolar: " . htmlspecialchars($ge) . "<br>\n";
echo "Promedio: " . htmlspecialchars($pr) . "<br>\n";
echo "<br>Datos del Tutor:<br>\n";
echo "CURP Tutor: " . htmlspecialchars($curpTutor) . "<br>\n";
echo "Nombre Tutor: " . htmlspecialchars($nomTutor) . "<br>\n";
echo "Parentesco: " . htmlspecialchars($pa) . "<br>\n";
echo "Teléfono Tutor: " . htmlspecialchars($teTutor) . "<br>\n";
echo "Municipio Tutor: " . htmlspecialchars($cMT) . "<br>\n";
echo "Colonia Tutor: " . htmlspecialchars($cCoT) . "<br>\n";
echo "Calle Tutor: " . htmlspecialchars($calleTutor) . "<br>\n";
echo "<br>Datos de la Inscripción:<br>\n";
echo "Folio: " . htmlspecialchars($fol) . "<br>\n";
echo "Fecha: " . htmlspecialchars($fe) . "<br>\n";
echo "Nivel: " . htmlspecialchars($cN) . "<br>\n";
echo "Carrera: " . htmlspecialchars($cC) . "<br>\n";
echo "Fecha Inscripción: " . htmlspecialchars($feI) . "<br>\n";
echo "Periodo: " . htmlspecialchars($cP) . "<br>\n";
// Detener el script para depuración
die();
// Función para cargar los datos en los campos de los modales
// function cargarDatosModal(alumno, sufijo, lugarN = null, municipio = null, colonia = null, cct = null, escuela = null, municipioTutor = null, coloniaTutor = null) {
//     // Campos de Alumno
//     document.getElementById(`aP${sufijo}`).value = alumno.aP || '';
//     document.getElementById(`aM${sufijo}`).value = alumno.aM || '';
//     document.getElementById(`nom${sufijo}`).value = alumno.nom || '';
//     document.getElementById(`lugarNacimiento${sufijo}`).value = alumno.lugarN || '';
//     document.getElementById(`fechaNacimiento${sufijo}`).value = alumno.fN || '';
//     document.getElementById(`ed${sufijo}`).value = alumno.ed || '';
//     document.getElementById(`cu${sufijo}`).value = alumno.cu || '';
//     document.getElementById(`se${sufijo}`).value = alumno.se || '';
//     document.getElementById(`ts${sufijo}`).value = alumno.ts || '';
//     document.getElementById(`af${sufijo}`).value = alumno.af || '';
//     document.getElementById(`municipioAlumno${sufijo}`).value = alumno.muAlumno || '';
//     //document.getElementById(`coloniaAlumno${sufijo}`).value = alumno.coAlumno || '';
//     document.getElementById(`ca${sufijo}`).value = alumno.caAlumno || '';
//     document.getElementById(`te${sufijo}`).value = alumno.teAlumno || '';
//     document.getElementById(`em${sufijo}`).value = alumno.em || '';
//     // Campos de Escuela
//     document.getElementById(`cct${sufijo}`).value = alumno.cct || '';
//     document.getElementById(`escuela${sufijo}`).value = alumno.es || '';
//     document.getElementById(`ge${sufijo}`).value = alumno.ge || '';
//     document.getElementById(`pr${sufijo}`).value = alumno.pr || '';
//     // Campos de Tutor
//     document.getElementById(`curpTutor${sufijo}`).value = alumno.cuTutor || '';
//     document.getElementById(`nomTutor${sufijo}`).value = alumno.nomTutor || '';
//     document.getElementById(`pa${sufijo}`).value = alumno.pa || '';
//     document.getElementById(`teTutor${sufijo}`).value = alumno.teTutor || '';
//     document.getElementById(`municipioTutor${sufijo}`).value = alumno.muTutor || '';
//     document.getElementById(`coloniaTutor${sufijo}`).value = alumno.coTutor || '';
//     document.getElementById(`calleTutor${sufijo}`).value = alumno.caTutor || '';
//     // Si estamos modificando, cargar los select
//     if (lugarN) seleccionarOpcionPorTexto(lugarN, alumno.lugarN);
//     if (municipio) seleccionarOpcionPorTexto(municipio, alumno.muAlumno);
//     //if (colonia) seleccionarOpcionPorTexto(colonia, alumno.coAlumno);
//     if (cct) seleccionarOpcionPorTexto(cct, alumno.cct);
//     if (escuela) seleccionarOpcionPorTexto(escuela, alumno.es);
//     if (municipioTutor) seleccionarOpcionPorTexto(municipioTutor, alumno.muTutor);
//     if (coloniaTutor) seleccionarOpcionPorTexto(coloniaTutor, alumno.coTutor);
// }
// // Función genérica para cargar correctamente los datos de un select y el que se obtiene de una consulta
// function seleccionarOpcionPorTexto(selectElement, texto) {
//     Array.from(selectElement.options).forEach(option => {
//         option.selected = option.textContent.trim() === texto.trim();
//     });
// }

// function cargarDatosModalModificar(alumno) {
//     // Campos de Alumno
//     document.getElementById(`aPMod`).value = alumno.aP || '';
//     document.getElementById(`aMMod`).value = alumno.aM || '';
//     document.getElementById(`nomMod`).value = alumno.nom || '';
//     document.getElementById(`lugarNacimientoMod`).value = alumno.lugarN || '';
//     document.getElementById(`fechaNacimientoMod`).value = alumno.fN || '';
//     document.getElementById(`edMod`).value = alumno.ed || '';
//     document.getElementById(`cuMod`).value = alumno.cu || '';
//     document.getElementById(`seMod`).value = alumno.se || '';
//     document.getElementById(`tsMod`).value = alumno.ts || '';
//     document.getElementById(`afMod`).value = alumno.af || '';
//     document.getElementById(`municipioAlumnoMod`).value = alumno.muAlumno || '';
//     document.getElementById(`coloniaAlumnoMod`).value = alumno.coAlumno || '';
//     document.getElementById(`caMod`).value = alumno.caAlumno || '';
//     document.getElementById(`teMod`).value = alumno.teAlumno || '';
//     document.getElementById(`emMod`).value = alumno.em || '';
//     // Campos de Escuela
//     document.getElementById(`cctMod`).value = alumno.cct || '';
//     document.getElementById(`escuelaMod`).value = alumno.es || '';
//     document.getElementById(`geMod`).value = alumno.ge || '';
//     document.getElementById(`prMod`).value = alumno.pr || '';
//     // Campos de Tutor
//     document.getElementById(`curpTutorMod`).value = alumno.cuTutor || '';
//     document.getElementById(`nomTutorMod`).value = alumno.nomTutor || '';
//     document.getElementById(`paMod`).value = alumno.pa || '';
//     document.getElementById(`teTutorMod`).value = alumno.teTutor || '';
//     document.getElementById(`municipioTutorMod`).value = alumno.muTutor || '';
//     document.getElementById(`coloniaTutorMod`).value = alumno.coTutor || '';
//     document.getElementById(`calleTutorMod`).value = alumno.caTutor || '';
// }

// Lógica para el manejo de las colegiaturas y las reinscripciones
include('conexion.php'); // Conexión a la base de datos
try {
    // Obtener la fecha actual
    $fecha = new DateTime('2025-02-01'); // Fecha simulada
    $fechaActual = $fecha->format('Y-m-d');
    $anioActual = $fecha->format('Y');
    $mesActual = $fecha->format('m');
    $conn->begin_transaction(); // Iniciar transacción
    // 1. Resetear estatus de colegiatura al inicio de un nuevo mes
    if ($fecha->format('d') === '01') {
        // Obtener los pagos realizados el día 1 del mes actual
        $queryPagos = "SELECT nC, SUM(tot) AS totalAbonado 
                       FROM pagos 
                       WHERE fe = ? AND cT = 3 
                       GROUP BY nC";
        $stmtPagos = $conn->prepare($queryPagos);
        $stmtPagos->bind_param('s', $fechaActual);
        $stmtPagos->execute();
        $resultPagos = $stmtPagos->get_result();
        $pagosDiaActual = [];
        while ($row = $resultPagos->fetch_assoc()) {
            $pagosDiaActual[$row['nC']] = $row['totalAbonado'];
        }
        $stmtPagos->close();
        // Resetear estatus, pero validar si ya se ha liquidado con pagos del día 1
        $queryColegiatura = "SELECT nC, coCol FROM inscripciones";
        $resultColegiatura = $conn->query($queryColegiatura);
        while ($row = $resultColegiatura->fetch_assoc()) {
            $nC = $row['nC'];
            $costoColegiatura = $row['coCol'];
            $totalAbonado = isset($pagosDiaActual[$nC]) ? $pagosDiaActual[$nC] : 0;
            // Actualizar estatus a 'No' solo si no está liquidado
            $nuevoEstatus = ($totalAbonado >= $costoColegiatura) ? 'Sí' : 'No';
            $stmtUpdate = $conn->prepare("UPDATE inscripciones SET cole = ? WHERE nC = ?");
            $stmtUpdate->bind_param('ss', $nuevoEstatus, $nC);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
        echo ($totalAbonado);
    }
    // 2. Resetear estatus de reinscripción al inicio de un nuevo periodo
    $queryPeriodos = "SELECT nC, feIni FROM inscripciones";
    $result = $conn->query($queryPeriodos);
    while ($row = $result->fetch_assoc()) {
        $nC = $row['nC'];
        $fechaInicio = new DateTime($row['feIni']);
        // Calcular el número de meses desde el inicio del periodo
        $diferenciaMeses = $fechaInicio->diff($fecha)->m + ($fechaInicio->diff($fecha)->y * 12);
        // Si han transcurrido múltiplos de 4 meses (un nuevo periodo)
        if ($diferenciaMeses > 0 && $diferenciaMeses % 4 === 0) {
            $stmtReinscripcion = $conn->prepare("UPDATE inscripciones SET rein = 'No' WHERE nC = ?");
            $stmtReinscripcion->bind_param('s', $nC);
            $stmtReinscripcion->execute();
            $stmtReinscripcion->close();
        }
    }
    // Confirmar transacción
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}

// Lógica 2
include('conexion.php'); // Conexión a la base de datos
// Sentencia SQL manual para el reset de la bandera: UPDATE configuracion SET valor = 'false' WHERE nombre = 'reset_colegiatura';
try {
    // Obtener la fecha actual
    $fechaActual = new DateTime('2025-05-01'); // Fecha simulada
    $conn->begin_transaction(); // Iniciar transacción
    // Obtener el estado de la bandera `reset_colegiatura`
    $queryConfig = "SELECT valor FROM configuracion WHERE nombre = 'reset_colegiatura'";
    $resultConfig = $conn->query($queryConfig);
    $resetRealizado = false;
    if ($resultConfig && $rowConfig = $resultConfig->fetch_assoc()) {
        $resetRealizado = ($rowConfig['valor'] === 'true');
    }
    // 1. Resetear estatus de colegiatura al inicio de un nuevo mes si no se ha realizado
    if ($fechaActual->format('d') === '01' && !$resetRealizado) {
        $conn->query("UPDATE inscripciones SET cole = 'No'");
        // Marcar el reset como realizado
        $stmtUpdateConfig = $conn->prepare("UPDATE configuracion SET valor = 'true' WHERE nombre = 'reset_colegiatura'");
        $stmtUpdateConfig->execute();
        $stmtUpdateConfig->close();
    }
    // 2. Resetear la variable al inicio de un nuevo mes
    if ($fechaActual->format('d') !== '01') {
        $stmtResetConfig = $conn->prepare("UPDATE configuracion SET valor = 'false' WHERE nombre = 'reset_colegiatura'");
        $stmtResetConfig->execute();
        $stmtResetConfig->close();
    }
    // 3. Resetear estatus de reinscripción al inicio de un nuevo periodo
    $queryPeriodos = "SELECT nC, feIni FROM inscripciones";
    $result = $conn->query($queryPeriodos);
    while ($row = $result->fetch_assoc()) {
        $nC = $row['nC'];
        $fechaInicio = new DateTime($row['feIni']);
        // Calcular el número de meses desde el inicio del periodo
        $diferenciaMeses = $fechaInicio->diff($fecha)->m + ($fechaInicio->diff($fecha)->y * 12);
        // Si han transcurrido múltiplos de 4 meses (un nuevo periodo)
        if ($diferenciaMeses > 0 && $diferenciaMeses % 4 === 0) {
            $stmtReinscripcion = $conn->prepare("UPDATE inscripciones SET rein = 'No' WHERE nC = ?");
            $stmtReinscripcion->bind_param('s', $nC);
            $stmtReinscripcion->execute();
            $stmtReinscripcion->close();
        }
    }
    // Confirmar transacción
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
