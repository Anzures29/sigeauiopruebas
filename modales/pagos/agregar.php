<?php
session_start();
include('../../conexion.php');
// Función para generar respuestas JSON
function responder($exito, $mensaje)
{
    header('Content-Type: application/json');
    echo json_encode(['exito' => $exito, 'mensaje_final' => $mensaje]);
    exit();
}
// Función para insertar el pago
function insertarPago($conn, $fo, $fe, $nC, $cT, $de, $ca, $im, $tot, $cF)
{
    $stmtPagos = $conn->prepare("INSERT INTO pagos (fo, fe, nC, cT, de, ca, im, tot, cF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmtPagos === false) {
        throw new Exception("Error al preparar la inserción del pago: " . $conn->error);
    }
    $stmtPagos->bind_param("sssssssss", $fo, $fe, $nC, $cT, $de, $ca, $im, $tot, $cF);
    if (!$stmtPagos->execute()) {
        throw new Exception("Error al insertar el pago: " . $stmtPagos->error);
    }
    $stmtPagos->close();
}
$fechaActual = new DateTime('2025-03-06'); // Fecha simulada
// Verificar si los datos requeridos existen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fo = trim($_POST['fo']);
    // $fe = trim($_POST['fe']);
    $fe = $fechaActual->format('Y-m-d');
    $nC = trim($_POST['nC']);
    $cT = (int) $_POST['tipoPago'];
    $de = trim($_POST['de']);
    $ca = (int) $_POST['ca'];
    $im = (float) $_POST['im'];
    $tot = (float) $_POST['tot'];
    $cF = trim($_POST['formaPago']);
    $conn->begin_transaction(); // Se inicia la transacción
    try {
        // Obtener nombre del alumno
        $stmtAlumno = $conn->prepare("SELECT CONCAT(nom, ' ', aP, ' ', aM) AS alumno FROM alumnos WHERE nC = ?");
        if ($stmtAlumno === false) {
            throw new Exception("Error al preparar la consulta del nombre del alumno: " . $conn->error);
        }
        $stmtAlumno->bind_param("s", $nC);
        $stmtAlumno->execute();
        $alumno = $stmtAlumno->get_result()->fetch_assoc()['alumno'] ?? 'Desconocido';
        $stmtAlumno->close();
        // Pagos de inscripcion, reinscripcion y colegiatura
        $pagosConfig = [
            1 => ['costo' => 'coIns', 'estatus' => 'ins', 'descripcion' => 'inscripción'],
            2 => ['costo' => 'coRei', 'estatus' => 'rein', 'descripcion' => 'reinscripción'],
            3 => ['costo' => 'coColOrig', 'estatus' => 'cole', 'descripcion' => 'colegiatura']
        ];
        if (!isset($pagosConfig[$cT])) {
            insertarPago($conn, $fo, $fe, $nC, $cT, $de, $ca, $im, $tot, $cF);
            $conn->commit();
            responder(true, "El pago $fo del alumno $alumno fue registrado correctamente.");
        }
        // Datos específicos para inscripción, reinscripción y colegiatura
        $costo = $pagosConfig[$cT]['costo'];
        $estatus = $pagosConfig[$cT]['estatus'];
        $descripcion = $pagosConfig[$cT]['descripcion'];
        if ($cT == 1 || $cT == 2) { // Inscripción o reinscripción
            if ($cT == 2) { // Validar periodos de reinscripción
                $mes = $fechaActual->format('m');
                $mesesPermitidos = ['04', '08', '12'];
                if (!in_array($mes, $mesesPermitidos)) {
                    responder(false, "No puede reinscribir fuera del periodo permitido");
                }
            }
            // Obtener costo y estatus del tipo de pago en una sola consulta
            $stmt = $conn->prepare("SELECT $estatus AS estatus FROM inscripciones WHERE nC = ?");
            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta: " . $conn->error);
            }
            $stmt->bind_param("s", $nC);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $estatusActual = $row['estatus'] ?? 'Pendiente';
            $saldoRaw = $row['estatus'] ?? '0';
            $saldo = floatval(str_replace(['$', ','], '', $saldoRaw)); // Convertir a número
            // Validar estatus de liquidación
            if ($estatusActual === 'Pagado') {
                responder(false, "La $descripcion del alumno $alumno ya está liquidada.");
            }
            // Validar monto
            if ($im > $saldo) {
                responder(false, "El importe ingresado es mayor al adeudo total. Faltan $$saldo.00 pesos.");
            }
            // Insertar el pago
            insertarPago($conn, $fo, $fe, $nC, $cT, $de, $ca, $im, $tot, $cF);
        }
        if ($cT == 3) { // Si el tipo de pago es colegiatura
            // Obtener el estatus y saldo en una sola consulta
            $stmt = $conn->prepare("SELECT cole AS estatus, cole AS saldoRaw FROM inscripciones WHERE nC = ?");
            $stmt->bind_param("s", $nC);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $estatus = $result['estatus'] ?? 'No'; // Estatus de la colegiatura
            $saldoRaw = $result['saldoRaw'] ?? '0'; // Saldo en bruto (con formato)
            $saldo = floatval(str_replace(['$', ','], '', $saldoRaw)); // Formatear a número
            // Validar estatus de pago
            if ($estatus === 'Pagado') {
                responder(false, "La colegiatura del alumno $alumno ya está liquidada.");
            }
            // Validar importe
            if ($im > $saldo) {
                responder(false, "El importe ingresado es mayor al adeudo total. Faltan $$saldo.00 pesos.");
            }
            // Insertar el pago
            insertarPago($conn, $fo, $fe, $nC, $cT, $de, $ca, $im, $tot, $cF);
        }
        // Registrar en bitácora
        $clave = $_SESSION['clave'];
        $accion = "Registró el pago $fo del alumno $alumno ($descripcion)";
        $fecha = date("Y-m-d");
        $hora = date("H:i:s");
        $stmtBitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        if ($stmtBitacora === false) {
            throw new Exception("Error al preparar la consulta de registro en bitácora: " . $conn->error);
        }
        $stmtBitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmtBitacora->execute();
        $stmtBitacora->close();
        // Confirmar transacción
        $conn->commit();
        responder(true, "El pago $fo del alumno $alumno, fue registrado correctamente.");
    } catch (Exception $e) {
        $conn->rollback();
        responder(false, "Error: " . $e->getMessage());
    } finally {
        $conn->close();
    }
}
