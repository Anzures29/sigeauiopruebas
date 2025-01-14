<?php
include('conexion.php'); // Conexión a la base de datos
// Función para generar el nuevo periodo
function generarPeriodo($fechaActual)
{
    $mesInicio = (int)($fechaActual->format('m'));
    $anioInicio = $fechaActual->format('y'); // Dos últimos dígitos del año
    // Crear un array con los nombres de los meses abreviados
    $meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
    // Calcular el mes final del periodo (cuatro meses después del mes de inicio)
    $mesFinal = ($mesInicio + 3);
    $anioFinal = $mesInicio + 3 > 12 ? $anioInicio + 1 : $anioInicio;
    // Obtener el nombre del mes inicial y final
    $nombreMesInicio = $meses[$mesInicio - 1];
    $nombreMesFinal = $meses[$mesFinal - 1];
    // Formatear el periodo en el formato solicitado
    return "{$nombreMesInicio}{$anioInicio}-{$nombreMesFinal}{$anioFinal}";
}
$conn->begin_transaction(); // Iniciar transacción
try {
    // Obtener la fecha actual
    // $fechaActual = new DateTime(); // Fecha actual
    $fechaActual = new DateTime('2025-01-01'); // Fecha simulada para pruebas
    // Consultar inscripciones con bandera = 'false' y fecha no nula
    $queryFechas = "SELECT nC, feIni FROM inscripciones WHERE bandera = 'false' AND feIni IS NOT NULL"; // Consulta SQL
    $resultFechas = $conn->query($queryFechas);
    if ($resultFechas && $resultFechas->num_rows > 0) { // Si hay resultados
        while ($row = $resultFechas->fetch_assoc()) { // Se ejecuta mientras
            $nC = $row['nC'];
            $feIni = DateTime::createFromFormat('Y-m-d', $row['feIni']);
            if ($fechaActual->format('Y-m') === $feIni->format('Y-m')) { // Si la fecha actual es igual a la fecha de inicio de clases
                // Se actualiza la bandera del alumno a true
                $stmtBandera = $conn->prepare("UPDATE inscripciones SET bandera = 'true' WHERE nC = ?"); // Consulta SQL
                $stmtBandera->bind_param("s", $nC); // Parámetros
                $stmtBandera->execute(); // Se ejecuta
                $stmtBandera->close(); // Se cierra
            }
        }
    }
    // Cargar banderas de reset
    $queryConfig = "SELECT nombre, valor FROM configuracion WHERE nombre IN ('reset_colegiatura', 'reset_reinscripcion', 'reset_mesfepa')";
    $resultConfig = $conn->query($queryConfig);
    $config = [];
    while ($rowConfig = $resultConfig->fetch_assoc()) {
        $config[$rowConfig['nombre']] = $rowConfig['valor'] === 'true';
    }
    // Obtener el mes y año actuales
    $mesActual = (int)$fechaActual->format('m'); // Mes actual en formato numérico
    $anioActual = (int)$fechaActual->format('Y'); // Año actual en formato numérico
    if ($mesActual >= 1 && $mesActual <= 3) {
        // Si estamos en enero, febrero o marzo (periodo cubierto por pagos de diciembre)
        $inicioPeriodo = ($anioActual - 1) . "-12-01"; // Inicio del periodo en diciembre del año anterior
        $finPeriodo = "$anioActual-03-31"; // Fin del periodo en marzo del año actual
    } elseif ($mesActual >= 4 && $mesActual <= 7) {
        // Si estamos en abril a julio (periodo cubierto por pagos de abril)
        $inicioPeriodo = "$anioActual-04-01"; // Inicio en abril del año actual
        $finPeriodo = "$anioActual-07-31"; // Fin en julio del año actual
    } elseif ($mesActual >= 8 && $mesActual <= 11) {
        // Si estamos en agosto a noviembre (periodo cubierto por pagos de agosto)
        $inicioPeriodo = "$anioActual-08-01"; // Inicio en agosto del año actual
        $finPeriodo = "$anioActual-11-30"; // Fin en noviembre del año actual
    } elseif ($mesActual == 12) {
        // Si estamos en diciembre (mes de pago para el periodo Enero-Abril del siguiente año)
        $inicioPeriodo = "$anioActual-12-01"; // Inicio en diciembre del año actual
        $finPeriodo = ($anioActual + 1) . "-03-31"; // Fin en marzo del siguiente año
    }
    // Cargar los pagos relevantes para determinar el estatus de reinscripción
    $queryAlumnos = "SELECT i.*, n.pe AS cantPeriodos, 
                    IFNULL(SUM(CASE WHEN p.cT = 1 THEN p.im ELSE 0 END), 0) AS abonosInscripcion, 
                    IFNULL(SUM(CASE WHEN p.cT = 2 AND p.fe BETWEEN DATE_SUB('$inicioPeriodo', INTERVAL 1 MONTH) 
                        AND '$finPeriodo' THEN p.im ELSE 0 END), 0) AS abonosReinscripcion, 
                    IFNULL(SUM(CASE WHEN p.cT = 3 THEN p.im ELSE 0 END), 0) AS abonosColegiatura, 
                    IFNULL(SUM(CASE WHEN p.cT = 2 THEN p.im ELSE 0 END), 0) AS totalPagado 
                    FROM inscripciones i 
                    INNER JOIN niveles n ON i.cN = n.cv 
                    LEFT JOIN pagos p ON i.nC = p.nC 
                    WHERE i.bandera = 'true' 
                    GROUP BY i.nC, n.pe";
    $resultAlumnos = $conn->query($queryAlumnos);
    // LÓGICAS QUE SE DEBEN EJECUTAR SOLO PARA LOS ALUMNOS QUE YA INICIARON CLASES
    if ($resultAlumnos && $resultAlumnos->num_rows > 0) {
        while ($alumno = $resultAlumnos->fetch_assoc()) {
            // DATOS DE LA INSCRIPCIÓN QUE SE USARÁN
            $nC = $alumno['nC']; // Número de control del alumno
            $fePa = DateTime::createFromFormat('Y-m-d', $alumno['fePa']); // Fecha de pago de colegiaturas

            // Actualizar la fecha de pago de colegiatura al inicio de un nuevo mes
            if ($fechaActual->format('d') === '01' && !$config['reset_mesfepa']) { // Si el día actual es '1' y la bandera es 'false'
                // Obtener mes y año actuales
                $mesActual = $fechaActual->format('m');
                $anioActual = $fechaActual->format('Y');
                // Actualizar fechas de pago solo si no es el mes y año de inicio de clases
                $conn->query("UPDATE inscripciones SET fePa = DATE_FORMAT(DATE_ADD(fePa, INTERVAL 1 MONTH), '%Y-%m-05')
                            WHERE bandera = 'true' AND (MONTH(feIni) != $mesActual OR YEAR(feIni) != $anioActual)");
                // Se actualiza la bandera 'reset_mesfepa' a 'true' para evitar múltiples actualizaciones
                $conn->query("UPDATE configuracion SET valor = 'true' WHERE nombre = 'reset_mesfepa'");
                $config['reset_mesfepa'] = true;
                // Resetear la bandera 'reset_mesfepa' a 'false' al final del día
            } elseif ($fechaActual->format('d') !== '01' && $config['reset_mesfepa']) { // Si el día es diferente de '1' y la bandera es 'true'
                // Se actualiza la bandera 'reset_colegiatura' a 'false' para que esté lista en el siguiente mes
                $conn->query("UPDATE configuracion SET valor = 'false' WHERE nombre = 'reset_mesfepa'");
                $config['reset_mesfepa'] = false;
            }
            // Consultar la nueva fecha de pago de los alumnos para las siguientes lógicas
            $queryfePa = "SELECT fePa FROM inscripciones WHERE nC = '$nC'";
            $resultfePa = $conn->query($queryfePa);
            if ($resultfePa && $resultfePa->num_rows > 0) {
                $fecha = $resultfePa->fetch_assoc();
                $fePa = DateTime::createFromFormat('Y-m-d', $fecha['fePa']);
            }

            // DATOS NECESARIOS PARA LAS SIGUIENTES LÓGICAS
            // Inscripción y Reinscripción
            $coIns = $alumno['coIns']; // Costo de inscripcion
            $coRei = $alumno['coRei']; // Costo de Reinscripcion
            $abIns = $alumno['abonosInscripcion']; // Abonos de inscripcion
            $abRei = $alumno['abonosReinscripcion']; // Abonos de reinscripcion
            // Calcular el adeudo en inscripcion y colegiatura
            $saldoInscripcion = max(0, $coIns - $abIns); // Calcular saldo pendiente de inscripción
            $estatusInscripcion = $saldoInscripcion > 0 ? "$" . number_format($saldoInscripcion, 2) : "Pagado";
            $saldoReinscripcion = max(0, $coRei - $abRei); // Calcular saldo pendiente de reinscripción
            $estatusReinscripcion = $saldoReinscripcion > 0 ? "$" . number_format($saldoReinscripcion, 2) : "Pagado";

            // Colegiatura (Mensualidad)
            $coColOrig = $alumno['coColOrig']; // Costo original de colegiatura
            $coCol = $alumno['coCol']; // Costo de colegiatura
            $abCol = $alumno['abonosColegiatura']; // Abonos de colegiatura
            $incrementoCol = $alumno['incrementoCol'] ?? 0; // Incremento acumulado
            $feIni = DateTime::createFromFormat('Y-m-d', $alumno['feIni']); // Fecha de inicio de clases
            // Calcular adeudos de colegiatura
            $inicioIntervalo = 1;
            $intervaloMeses = (($fechaActual->format('Y') - $feIni->format('Y')) * 12) + ($fechaActual->format('m') - $feIni->format('m')) + $inicioIntervalo;
            $mesesAdeudados = max(0, $intervaloMeses); // Evitar valores negativos
            // Calcular adeudos acumulados correctamente
            // $adeudoBase = $mesesAdeudados * $coColOrig; // Adeudo base de colegiaturas
            // $adeudoConIncremento = $adeudoBase + $incrementoCol; // Suma de recargos acumulados
            // $adeudoTotal = $adeudoConIncremento - $abCol; // Total pendiente menos abonos realizados
            // $saldoColegiatura = max(0, $adeudoTotal);

            // Calcular adeudos acumulados correctamente
            $adeudoBase = $mesesAdeudados * $coColOrig; // Adeudo base de colegiaturas
            $adeudoConIncremento = $adeudoBase + $incrementoCol; // Suma de recargos acumulados
            $adeudoTotal = $adeudoConIncremento - $abCol; // Total pendiente menos abonos realizados
            $saldoColegiatura = max(0, $adeudoTotal);
            // Estatus de la colegiatura
            $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
            // Verificar si se aplica un nuevo incremento
            $ultimoIncremento = DateTime::createFromFormat('Y-m-d', $alumno['ultimoIncremento'] ?? '0000-00-00');
            $mesActual = $fechaActual->format('Y-m');
            $mesUltimoIncremento = $ultimoIncremento ? $ultimoIncremento->format('Y-m') : '0000-00';

            // Aplicar nuevo incremento solo si corresponde
            if ($mesActual > $mesUltimoIncremento && $fechaActual->format('Y-m-d') > $fePa->format('Y-m-d') && $saldoColegiatura > 0) {
                // // Calcular meses atrasados correctamente
                // $mesesAtrasados = max(0, $intervaloMeses - floor($abCol / $coColOrig));
                // $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10; // Incremento del 10% por mes atrasado

                // Calcular recargo acumulado (sin restar recargos previos)
                $nuevoRecargo = $coColOrig * 0.10; // 10% por el mes actual
                $incrementoCol += $nuevoRecargo; // Actualizar recargo acumulado

                // echo ("$nC nuevo incremento: $nuevoIncremento\n"); // Línea para depurar dato

                // // Actualizar el recargo acumulado solo para el mes actual
                // $incrementoCol = $nuevoIncremento;
                // $nuevoCostoColeg = $coColOrig + $incrementoCol;
                // $fechaIncremento = $fechaActual->format('Y-m-d');

                // Actualizar la base de datos con el nuevo recargo y costo
                $nuevoCostoColeg = $coColOrig + $incrementoCol;
                $fechaIncremento = $fechaActual->format('Y-m-d');

                // echo (" incremento: $incrementoCol\n"); // Línea para depurar dato

                // // Actualizar la base de datos con el nuevo recargo y costo
                // $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
                // $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
                // $stmtUpdateIncremento->execute();
                // $stmtUpdateIncremento->close();

                // Actualizar la base de datos
                $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
                $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
                $stmtUpdateIncremento->execute();
                $stmtUpdateIncremento->close();

                // Actualizar incremento en la base de datos si es necesario
                if ($saldoColegiatura > 0) {
                    $nuevoCostoColeg = $coColOrig + $incrementoCol;
                    $fechaIncremento = $fechaActual->format('Y-m-d');
                    // Actualizar el recargo acumulado solo para el mes actual si es necesario
                    $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
                    $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
                    $stmtUpdateIncremento->execute();
                    $stmtUpdateIncremento->close();
                }
            }
            if ($saldoColegiatura <= 0) {
                $stmtRestore = $conn->prepare("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = ?");
                $stmtRestore->bind_param('s', $nC);
                $stmtRestore->execute();
                $stmtRestore->close();
            }
            if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
                $stmtReset = $conn->prepare("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = ?");
                $stmtReset->bind_param('s', $nC);
                $stmtReset->execute();
                $stmtReset->close();
            }
            // Actualizar tabla principal con los saldos calculados
            $stmtUpdate = $conn->prepare("UPDATE inscripciones SET ins = ?, cole = ?, incrementoCol = ?, rein = ? WHERE nC = ? AND bandera = 'true'");
            $stmtUpdate->bind_param('sssss', $estatusInscripcion, $estatusColegiatura, $incrementoCol, $estatusReinscripcion, $nC);
            $stmtUpdate->execute();
            $stmtUpdate->close();


            // // Calcular adeudos acumulados
            // $adeudoTotal = (($mesesAdeudados * $coColOrig) + $incrementoCol) - $abCol;
            // $saldoColegiatura = max(0, $adeudoTotal);
            // $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
            // // Recuperar datos de incremento
            // $ultimoIncremento = DateTime::createFromFormat('Y-m-d', $alumno['ultimoIncremento'] ?? '0000-00-00');
            // $mesActual = $fechaActual->format('Y-m');
            // $mesUltimoIncremento = $ultimoIncremento ? $ultimoIncremento->format('Y-m') : '0000-00';
            // // Verificar si se debe aplicar un incremento
            // if ($mesActual > $mesUltimoIncremento && $fechaActual->format('Y-m-d') > $fePa->format('Y-m-d') && $saldoColegiatura > 0) {
            //     $mesesAtrasados = max(0, $intervaloMeses - floor($abCol / $coColOrig));
            //     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10;

            //     // Asegurar que no se pierdan incrementos previos
            //     $incrementoCol = max($incrementoCol, $nuevoIncremento);
            //     $nuevoCostoColeg = $coColOrig + $incrementoCol;
            //     $fechaIncremento = $fechaActual->format('Y-m-d');

            //     // Actualizar el costo de colegiatura y la fecha del último incremento
            //     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
            //     $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
            //     $stmtUpdateIncremento->execute();
            //     $stmtUpdateIncremento->close();
            // }
            // // Resetear incremento al inicio de un nuevo mes si no hay saldo pendiente
            // if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
            //     $stmtReset = $conn->prepare("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = ?");
            //     $stmtReset->bind_param('s', $nC);
            //     $stmtReset->execute();
            //     $stmtReset->close();
            // }
            // // Actualizar colegiatura al original si está pagado
            // if ($saldoColegiatura <= 0) {
            //     $stmtRestore = $conn->prepare("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = ?");
            //     $stmtRestore->bind_param('s', $nC);
            //     $stmtRestore->execute();
            //     $stmtRestore->close();
            // }



            // // Calcular adeudos de colegiatura
            // $inicioIntervalo = 1;
            // $intervaloMeses = (($fechaActual->format('Y') - $feIni->format('Y')) * 12) + ($fechaActual->format('m') - $feIni->format('m')) + $inicioIntervalo;
            // $mesesAdeudados = max(0, $intervaloMeses); // Evitar valores negativos
            // // Calcular adeudos acumulados
            // $adeudoTotal = (($mesesAdeudados * $coColOrig) + $incrementoCol) - $abCol;
            // // Saldo colegiatura
            // $saldoColegiatura = max(0, $adeudoTotal);
            // $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
            // // Recuperar datos de incremento
            // $ultimoIncremento = DateTime::createFromFormat('Y-m-d', $alumno['ultimoIncremento'] ?? '0000-00-00');
            // // Calcular si se requiere aplicar un nuevo incremento
            // $mesActual = $fechaActual->format('Y-m');
            // $mesUltimoIncremento = $ultimoIncremento ? $ultimoIncremento->format('Y-m') : '0000-00';
            // if ($mesActual > $mesUltimoIncremento && $fechaActual->format('Y-m-d') > $fePa->format('Y-m-d')) {
            //     // DATOS PARA DEPURAR
            //     echo ("\nFecha Actual: " . $fechaActual->format('Y-m-d'));
            //     echo ("\nFecha de Pago: " . $fePa->format('Y-m-d'));
            //     echo ("\nSaldo Colegiatura: " . $saldoColegiatura);
            //     echo ("\nAbonos Colegiatura: " . $abCol);
            //     $mesesAtrasados = max(0, $intervaloMeses - floor(($abCol - $incrementoCol) / $coColOrig));
            //     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10;
            //     $incrementoCol += $nuevoIncremento;
            //     $nuevoCostoColeg = $coColOrig + $incrementoCol;
            //     $fechaIncremento = $fechaActual->format('Y-m-d');
            //     // Actualizar el costo de colegiatura y la fecha del último incremento
            //     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
            //     $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
            //     $stmtUpdateIncremento->execute();
            //     $stmtUpdateIncremento->close();
            // }
            // // Resetear incremento al inicio de un nuevo mes si no hay saldo pendiente
            // if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
            //     $conn->query("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = '$nC'");
            // }
            // // Restaurar el costo al original si está pagado
            // if ($saldoColegiatura <= 0) {
            //     $conn->query("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = '$nC'");
            // }
            // // Actualizar
            // $stmtUpdate = $conn->prepare("UPDATE inscripciones SET ins = ?, cole = ?, incrementoCol = ?, rein = ? WHERE nC = ? AND bandera = 'true'"); // Actualizar la tabla con los saldos calculados
            // $stmtUpdate->bind_param('sssss', $estatusInscripcion, $estatusColegiatura, $incrementoCol, $estatusReinscripcion, $nC);
            // $stmtUpdate->execute();
            // $stmtUpdate->close();


            // // Incrementar el costo de colegiatura en un 10%, después de la fecha límite de pago
            // if ($fechaActual->format('Y-m-d') > $fePa->format('Y-m-d')) {
            //     $mesesAtrasados = max(0, $intervaloMeses - floor($abCol / $coColOrig));
            //     // Calcular el recargo acumulado actual (solo basado en los meses atrasados y el costo base)
            //     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10;
            //     // Asegurar que no se pierdan incrementos previos
            //     $incrementoCol = max($incrementoCol, $nuevoIncremento);
            //     // Actualizar el costo actual de la colegiatura sumando el incremento acumulado
            //     $nuevoCostoColeg = $coColOrig + $incrementoCol;
            //     // Actualizar en la base de datos
            //     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ? WHERE nC = ? AND cole != 'Pagado'");
            //     $stmtUpdateIncremento->bind_param('sss', $nuevoCostoColeg, $incrementoCol, $nC);
            //     $stmtUpdateIncremento->execute();
            //     $stmtUpdateIncremento->close();
            // }
            // // Restaurar el costo de la colegiatura si aún no se excede la fecha límite de pago
            // if ($fechaActual->format('Y-m-d') <= $fePa->format('Y-m-d') && $saldoColegiatura <= 0) {
            //     // Se actualiza el costo de colegiatura al original
            //     $conn->query("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = '$nC'");
            // }
            // // Resetear incremento al inicio de un nuevo mes si no hay saldo pendiente
            // if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
            //     $conn->query("UPDATE inscripciones SET incrementoCol = 0 WHERE nC = '$nC'");
            // }
            // // Incrementar el costo de colegiatura
            // if ($fechaActual->format('Y-m-d') > $fePa->format('Y-m-d')) {
            //     $mesesAtrasados = max(0, $intervaloMeses - floor(($abCol - $incrementoCol) / $coColOrig));
            //     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10;
            //     $incrementoCol += $nuevoIncremento;
            //     $nuevoCostoColeg = $coColOrig + $incrementoCol;
            //     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ? WHERE nC = ?");
            //     $stmtUpdateIncremento->bind_param('sss', $nuevoCostoColeg, $incrementoCol, $nC);
            //     $stmtUpdateIncremento->execute();
            //     $stmtUpdateIncremento->close();
            // }
            // // Resetear el incremento al inicio de un nuevo mes si no hay saldo
            // if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
            //     $conn->query("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = '$nC'");
            // }


            // Actualizar periodo si es inicio de uno nuevo
            if (($fechaActual->format('m') - 1) % 4 === 0) {
                $nuevoPeriodo = generarPeriodo($fechaActual);
                $conn->query("UPDATE inscripciones SET peAct = '$nuevoPeriodo' WHERE nC = '$nC'");
            }
            // Verificar si el alumno ya terminó sus clases
            $feFin = DateTime::createFromFormat('Y-m-d', $alumno['feFin']); // Fecha de fin de clases
            if ($fechaActual->format('Y-m-d') > $feFin->format('Y-m-d')) { // Si fecha actual es mayor a fecha de fin de clases
                // Se actualiza su bandera a false
                $conn->query("UPDATE inscripciones SET bandera = 'false' WHERE nC = '$nC'");
                // Se agregan los siguientes datos a la tabla egresados
                // Consultar apellido paterno y materno, y nombre del alumno, en la tabla alumnos
                $queryNom = "SELECT inscripciones.fol, inscripciones.ft, inscripciones.feIni, inscripciones.feFin, inscripciones.cN, 
                            inscripciones.cC, alumnos.aP, alumnos.aM, alumnos.nom 
                FROM inscripciones 
                INNER JOIN alumnos ON alumnos.nC = inscripciones.nC 
                WHERE inscripciones.nC = '$nC'
                LIMIT 1";  // Asegúrate de limitar a un solo resultado
                $resultNom = $conn->query($queryNom);
                if ($resultNom && $resultNom->num_rows > 0) {
                    $nombres = $resultNom->fetch_assoc();
                    $fol = $nombres['fol']; // Ruta de la foto
                    $ft = $nombres['ft']; // Ruta de la foto
                    $aP = $nombres['aP']; // Apellido paterno
                    $aM = $nombres['aM']; // Apellido naterno
                    $nom = $nombres['nom']; // Nombre
                    $cN = $nombres['cN']; // Clave nivel educativo
                    $cC = $nombres['cC']; // Clave carrera
                    $feIng = $nombres['feIni']; // Fecha de ingreso
                    $feEgr = $nombres['feFin']; // Fecha de egreso
                }
                // Verificar si ya existe en la tabla egresados
                $checkEgresado = $conn->prepare("SELECT nC FROM egresados WHERE nC = ?");
                $checkEgresado->bind_param("s", $nC);
                $checkEgresado->execute();
                $checkEgresado->store_result();
                if ($checkEgresado->num_rows == 0) {
                    // Insertar los datos en la tabla egresados
                    if ($cC !== null) {
                        // Insertar con la clave de carrera
                        $stmtEgresados = $conn->prepare("INSERT INTO egresados (fol, nC, ft, aP, aM, nom, cN, cC, feIng, feEgr)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmtEgresados->bind_param("ssssssssss", $fol, $nC, $ft, $aP, $aM, $nom, $cN, $cC, $feIng, $feEgr);
                    } else {
                        // Insertar sin la clave de la carrera
                        $stmtEgresados = $conn->prepare("INSERT INTO egresados (fol, nC, ft, aP, aM, nom, cN, feIng, feEgr)
                                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmtEgresados->bind_param("sssssssss", $fol, $nC, $ft, $aP, $aM, $nom, $cN, $feIng, $feEgr);
                    }
                    $stmtEgresados->execute();
                    $stmtEgresados->close();
                }
                $checkEgresado->close();
            }
        }
    }

    // LÓGICAS QUE NECESITAN EJECUTARSE SIEMPRE, SIN EXCEPCIÓN ALGUNA
    // 1. Eliminar los registros de la bitácora del mes anterior, cada primero del mes actual
    if ($fechaActual->format('d') === '01') { // Si el dia de la fecha actual es 1
        // Obtener el estado de la bandera reset_bitacora
        $queryConfig = "SELECT valor FROM configuracion WHERE nombre = 'reset_bitacora'";
        $resultConfig = $conn->query($queryConfig);
        $resetRealizado = false;
        if ($resultConfig && $rowConfig = $resultConfig->fetch_assoc()) {
            $resetRealizado = ($rowConfig['valor'] === 'true');
        }
        // Si no se ha realizado el reset este mes
        if (!$resetRealizado) {
            // Calcular rango de fechas del mes anterior
            $mesAnterior = (clone $fechaActual)->modify('-1 month');
            $inicioMesAnterior = $mesAnterior->format('Y-m-01');
            $finMesAnterior = $mesAnterior->format('Y-m-t');
            // Eliminar registros del mes anterior
            $stmtDelete = $conn->prepare("DELETE FROM bitacora WHERE fe BETWEEN ? AND ?");
            if ($stmtDelete === false) {
                throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
            }
            $stmtDelete->bind_param('ss', $inicioMesAnterior, $finMesAnterior);
            $stmtDelete->execute();
            $stmtDelete->close();
            // Actualizar la bandera para evitar múltiples resets en el mismo mes
            $stmtUpdateConfig = $conn->prepare("UPDATE configuracion SET valor = 'true' WHERE nombre = 'reset_bitacora'");
            if ($stmtUpdateConfig === false) {
                throw new Exception("Error al actualizar la bandera: " . $conn->error);
            }
            $stmtUpdateConfig->execute();
            $stmtUpdateConfig->close();
        }
    } // Restaurar la bandera reset_bitacora al final del día 1, para que esté lista para el próximo mes
    if ($fechaActual->format('d') !== '01') {
        $stmtResetConfig = $conn->prepare("UPDATE configuracion SET valor = 'false' WHERE nombre = 'reset_bitacora'");
        if ($stmtResetConfig === false) {
            throw new Exception("Error al restaurar la bandera: " . $conn->error);
        }
        $stmtResetConfig->execute();
        $stmtResetConfig->close();
    }
    // Confirmar transacción
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback(); // Revertir transacción en caso de error
    echo "Error: " . $e->getMessage();
} finally {
    // Solo cierra la conexión cuando todas las operaciones estén completas.
    if ($conn) {
        $conn->close();
    }
}
// // Calcular el incremento acumulado
// $mesesAtrasados = $intervaloMeses - floor($abCol / $coColOrig); // Calcula los meses reales atrasados
// $nuevoIncremento = ($mesesAtrasados * $coColOrig * 0.10); // Incremento total acumulado basado en los meses atrasados
// $incrementoCol = max($incrementoCol, $nuevoIncremento); // Asegura que no se pierdan incrementos previos

// // Actualizar en la base de datos
// $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ? WHERE nC = ? AND cole != 'Pagado'");
// $nuevoCostoColeg = $coColOrig + $incrementoCol;
// $stmtUpdateIncremento->bind_param('sss', $nuevoCostoColeg, $incrementoCol, $nC);
// $stmtUpdateIncremento->execute();
// $stmtUpdateIncremento->close();
// Calcular los meses reales atrasados basados solo en la colegiatura base

// $coIns = $alumno['coIns']; // Costo de inscripcion
// $coRei = $alumno['coRei']; // Costo de Reinscripcion
// $coColOrig = $alumno['coColOrig']; // Costo original de colegiatura
// $coCol = $alumno['coCol']; // Costo de colegiatura
// $abIns = $alumno['abonosInscripcion']; // Abonos de inscripcion
// $abRei = $alumno['abonosReinscripcion']; // Abonos de reinscripcion
// $abCol = $alumno['abonosColegiatura']; // Abonos de colegiatura
// $incrementoCol = $alumno['incrementoCol'] ?? 0; // Incremento acumulado
// $feIni = DateTime::createFromFormat('Y-m-d', $alumno['feIni']); // Fecha de inicio de clases
// // Calcular el adeudo en inscripcion, colegiatura y reinscripcion, y mostrarlos en el estatus del alumno
// $saldoInscripcion = max(0, $coIns - $abIns); // Calcular saldo pendiente de inscripción
// $estatusInscripcion = $saldoInscripcion > 0 ? "$" . number_format($saldoInscripcion, 2) : "Pagado";
// $saldoReinscripcion = max(0, $coRei - $abRei); // Calcular saldo pendiente de reinscripción
// $estatusReinscripcion = $saldoReinscripcion > 0 ? "$" . number_format($saldoReinscripcion, 2) : "Pagado";
// // Calcular adeudos de colegiatura
// $inicioIntervalo = 1;
// $intervaloMeses = (($fechaActual->format('Y') - $feIni->format('Y')) * 12) + ($fechaActual->format('m') - $feIni->format('m')) + $inicioIntervalo;
// $mesesAdeudados = max(0, $intervaloMeses); // Evitar valores negativos
// // Calcular adeudo acumulado
// // adeudo total es igual a los meses adeudados por el costo original colegiatura + el incremento del 10% que haya habido en meses
// // anteriores menos el abono de colegiatura
// $adeudoTotal = (($mesesAdeudados * $coColOrig) + $incrementoCol) - $abCol;
// // Saldo de colegiatura
// $saldoColegiatura = max(0, $adeudoTotal);
// $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
// // Incrementar el costo de colegiatura en un 10%, después de la fecha límite de pago
// if ($fechaActual->format('Y-m-d') > $fePa->format('Y-m-d')) {
//     // Calcular el incremento acumulado
//     $mesesAtrasados = $intervaloMeses - floor($abCol / $coColOrig); // Calcula los meses reales atrasados
//     $nuevoIncremento = ($mesesAtrasados * $coColOrig * 0.10); // Incremento total acumulado basado en los meses atrasados
//     $incrementoCol = max($incrementoCol, $nuevoIncremento); // Asegura que no se pierdan incrementos previos

//     // Actualizar en la base de datos
//     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ? WHERE nC = ? AND cole != 'Pagado'");
//     $nuevoCostoColeg = $coColOrig + $incrementoCol;
//     $stmtUpdateIncremento->bind_param('sss', $nuevoCostoColeg, $incrementoCol, $nC);
//     $stmtUpdateIncremento->execute();
//     $stmtUpdateIncremento->close();
// }

// // Restaurar el costo de la colegiatura si aún no se excede la fecha límite de pago
// if ($fechaActual->format('Y-m-d') <= $fePa->format('Y-m-d') && $saldoColegiatura <= 0) {
//     // Se actualiza el costo de colegiatura al original
//     $conn->query("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = '$nC'");
// }

// // Resetear incremento al inicio de un nuevo mes si no hay saldo pendiente
// if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
//     $conn->query("UPDATE inscripciones SET incrementoCol = 0 WHERE nC = '$nC'");
// }

// // Actualizar
// $stmtUpdate = $conn->prepare("UPDATE inscripciones SET ins = ?, cole = ?, incrementoCol = ?, rein = ? WHERE nC = ? AND bandera = 'true'"); // Actualizar la tabla con los saldos calculados
// $stmtUpdate->bind_param('sssss', $estatusInscripcion, $estatusColegiatura, $incrementoCol, $estatusReinscripcion, $nC);
// $stmtUpdate->execute();
// $stmtUpdate->close();

// // DATOS NECESARIOS PARA LAS SIGUIENTES LÓGICAS
// $coIns = $alumno['coIns']; // Costo de inscripcion
// $coRei = $alumno['coRei']; // Costo de Reinscripcion
// $coColOrig = $alumno['coColOrig']; // Costo original de colegiatura
// $coCol = $alumno['coCol']; // Costo de colegiatura
// $abIns = $alumno['abonosInscripcion']; // Abonos de inscripcion
// $abRei = $alumno['abonosReinscripcion']; // Abonos de reinscripcion
// $abCol = $alumno['abonosColegiatura']; // Abonos de colegiatura
// $incrementoCol = $alumno['incrementoCol'] ?? 0; // Incremento acumulado
// $feIni = DateTime::createFromFormat('Y-m-d', $alumno['feIni']); // Fecha de inicio de clases
// // Validar feIni antes de continuar
// if (!$feIni) {
//     echo "Error: Fecha de inicio de clases no válida.";
//     exit;
// }
// // Calcular el adeudo en inscripcion, colegiatura y reinscripcion
// $saldoInscripcion = max(0, $coIns - $abIns); // Calcular saldo pendiente de inscripción
// $estatusInscripcion = $saldoInscripcion > 0 ? "$" . number_format($saldoInscripcion, 2) : "Pagado";
// $saldoReinscripcion = max(0, $coRei - $abRei); // Calcular saldo pendiente de reinscripción
// $estatusReinscripcion = $saldoReinscripcion > 0 ? "$" . number_format($saldoReinscripcion, 2) : "Pagado";
// // Calcular adeudos de colegiatura
// $inicioIntervalo = 1;
// $intervaloMeses = (($fechaActual->format('Y') - $feIni->format('Y')) * 12) + ($fechaActual->format('m') - $feIni->format('m')) + $inicioIntervalo;
// $mesesAdeudados = max(0, $intervaloMeses); // Evitar valores negativos
// // Calcular adeudos acumulados correctamente
// $adeudoBase = $mesesAdeudados * $coColOrig; // Adeudo base de colegiaturas
// $adeudoConIncremento = $adeudoBase + $incrementoCol; // Suma de recargos acumulados
// $adeudoTotal = $adeudoConIncremento - $abCol; // Total pendiente menos abonos realizados
// $saldoColegiatura = max(0, $adeudoTotal);
// // Estatus de la colegiatura
// $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
// // Recuperar datos del último incremento
// $ultimoIncremento = DateTime::createFromFormat('Y-m-d', $alumno['ultimoIncremento'] ?? '0000-00-00');
// $mesActual = $fechaActual->format('Y-m');
// $mesUltimoIncremento = $ultimoIncremento ? $ultimoIncremento->format('Y-m') : '0000-00';
// // Aplicar nuevo incremento solo si corresponde
// if ($mesActual > $mesUltimoIncremento && $fechaActual->format('Y-m-d') > $fePa->format('Y-m-d') && $saldoColegiatura > 0) {
//     // Calcular meses atrasados correctamente
//     $mesesAtrasados = max(0, $intervaloMeses - floor($abCol / $coColOrig));
//     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10; // Incremento del 10% por mes atrasado
//     echo ("$nC nuevo incremento: $nuevoIncremento\n"); // Línea para depurar dato
//     // Actualizar el recargo acumulado solo para el mes actual
//     $incrementoCol = $nuevoIncremento;
//     $nuevoCostoColeg = $coColOrig + $incrementoCol;
//     $fechaIncremento = $fechaActual->format('Y-m-d');
//     echo ("$nC incremento: $incrementoCol\n"); // Línea para depurar dato
//     // Actualizar la base de datos con el nuevo recargo y costo
//     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
//     $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
//     $stmtUpdateIncremento->execute();
//     $stmtUpdateIncremento->close();
//     // Actualizar incremento en la base de datos si es necesario
//     if ($saldoColegiatura > 0) {
//         $nuevoCostoColeg = $coColOrig + $incrementoCol;
//         $fechaIncremento = $fechaActual->format('Y-m-d');
//         // Actualizar el recargo acumulado solo para el mes actual si es necesario
//         $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
//         $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
//         $stmtUpdateIncremento->execute();
//         $stmtUpdateIncremento->close();
//     }
// }
// if ($saldoColegiatura <= 0) {
//     $stmtRestore = $conn->prepare("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = ?");
//     $stmtRestore->bind_param('s', $nC);
//     $stmtRestore->execute();
//     $stmtRestore->close();
// }
// if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
//     $stmtReset = $conn->prepare("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = ?");
//     $stmtReset->bind_param('s', $nC);
//     $stmtReset->execute();
//     $stmtReset->close();
// }
// // Actualizar tabla principal con los saldos calculados
// $stmtUpdate = $conn->prepare("UPDATE inscripciones SET ins = ?, cole = ?, incrementoCol = ?, rein = ? WHERE nC = ? AND bandera = 'true'");
// $stmtUpdate->bind_param('sssss', $estatusInscripcion, $estatusColegiatura, $incrementoCol, $estatusReinscripcion, $nC);
// $stmtUpdate->execute();
// $stmtUpdate->close();


// // DATOS NECESARIOS PARA LAS SIGUIENTES LÓGICAS
// $coIns = $alumno['coIns']; // Costo de inscripcion
// $coRei = $alumno['coRei']; // Costo de Reinscripcion
// $coColOrig = $alumno['coColOrig']; // Costo original de colegiatura
// $coCol = $alumno['coCol']; // Costo de colegiatura
// $abIns = $alumno['abonosInscripcion']; // Abonos de inscripcion
// $abRei = $alumno['abonosReinscripcion']; // Abonos de reinscripcion
// $abCol = $alumno['abonosColegiatura']; // Abonos de colegiatura
// $incrementoCol = $alumno['incrementoCol'] ?? 0; // Incremento acumulado
// $feIni = DateTime::createFromFormat('Y-m-d', $alumno['feIni']); // Fecha de inicio de clases
// // Calcular el adeudo en inscripcion, colegiatura y reinscripcion, y mostrarlos en el estatus del alumno
// $saldoInscripcion = max(0, $coIns - $abIns); // Calcular saldo pendiente de inscripción
// $estatusInscripcion = $saldoInscripcion > 0 ? "$" . number_format($saldoInscripcion, 2) : "Pagado";
// $saldoReinscripcion = max(0, $coRei - $abRei); // Calcular saldo pendiente de reinscripción
// $estatusReinscripcion = $saldoReinscripcion > 0 ? "$" . number_format($saldoReinscripcion, 2) : "Pagado";
// // Validar feIni antes de continuar
// if (!$feIni) {
//     echo "Error: Fecha de inicio de clases no válida.";
//     exit;
// }
// // Calcular adeudos de colegiatura
// $inicioIntervalo = 1;
// $intervaloMeses = (($fechaActual->format('Y') - $feIni->format('Y')) * 12) + ($fechaActual->format('m') - $feIni->format('m')) + $inicioIntervalo;
// $mesesAdeudados = max(0, $intervaloMeses); // Evitar valores negativos
// // Calcular adeudos acumulados correctamente
// $adeudoBase = $mesesAdeudados * $coColOrig; // Adeudo base de colegiaturas
// $adeudoConIncremento = $adeudoBase + $incrementoCol; // Suma de recargos acumulados
// $adeudoTotal = $adeudoConIncremento - $abCol; // Total pendiente menos abonos realizados
// // Calcular saldo de colegiatura
// $saldoColegiatura = max(0, $adeudoTotal);
// $estatusColegiatura = $saldoColegiatura > 0 ? "$" . number_format($saldoColegiatura, 2) : "Pagado";
// // Recuperar datos del último incremento
// $ultimoIncremento = DateTime::createFromFormat('Y-m-d', $alumno['ultimoIncremento'] ?? '0000-00-00');
// $mesActual = $fechaActual->format('Y-m');
// $mesUltimoIncremento = $ultimoIncremento ? $ultimoIncremento->format('Y-m') : '0000-00';
// // Aplicar nuevo incremento solo si corresponde
// if ($mesActual > $mesUltimoIncremento && $fechaActual->format('Y-m-d') > $fePa->format('Y-m-d') && $saldoColegiatura > 0) {
//     // Calcular meses atrasados correctamente
//     $mesesAtrasados = max(0, $intervaloMeses - floor($abCol / $coColOrig));
//     $nuevoIncremento = $mesesAtrasados * $coColOrig * 0.10; // Incremento del 10% por mes atrasado
//     echo ("$nC nuevo incremento: $nuevoIncremento\n"); // Línea para depurar dato
//     // Actualizar el recargo acumulado solo para el mes actual
//     $incrementoCol = $nuevoIncremento;
//     $nuevoCostoColeg = $coColOrig + $incrementoCol;
//     $fechaIncremento = $fechaActual->format('Y-m-d');
//     echo ("$nC incremento: $incrementoCol\n"); // Línea para depurar dato
//     // Actualizar la base de datos con el nuevo recargo y costo
//     $stmtUpdateIncremento = $conn->prepare("UPDATE inscripciones SET coCol = ?, incrementoCol = ?, ultimoIncremento = ? WHERE nC = ?");
//     $stmtUpdateIncremento->bind_param('ssss', $nuevoCostoColeg, $incrementoCol, $fechaIncremento, $nC);
//     $stmtUpdateIncremento->execute();
//     $stmtUpdateIncremento->close();
// }
// if ($saldoColegiatura <= 0) {
//     $stmtRestore = $conn->prepare("UPDATE inscripciones SET coCol = coColOrig, incrementoCol = 0 WHERE nC = ?");
//     $stmtRestore->bind_param('s', $nC);
//     $stmtRestore->execute();
//     $stmtRestore->close();
// }
// if ($fechaActual->format('d') === '01' && $saldoColegiatura <= 0) {
//     $stmtReset = $conn->prepare("UPDATE inscripciones SET incrementoCol = 0, coCol = coColOrig WHERE nC = ?");
//     $stmtReset->bind_param('s', $nC);
//     $stmtReset->execute();
//     $stmtReset->close();
// }
// // Actualizar tabla principal con los saldos calculados
// $stmtUpdate = $conn->prepare("UPDATE inscripciones SET ins = ?, cole = ?, incrementoCol = ?, rein = ? WHERE nC = ? AND bandera = 'true'");
// $stmtUpdate->bind_param('sssss', $estatusInscripcion, $estatusColegiatura, $incrementoCol, $estatusReinscripcion, $nC);
// $stmtUpdate->execute();
// $stmtUpdate->close();