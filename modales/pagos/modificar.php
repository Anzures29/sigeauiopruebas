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
// Función para actualizar los datos del pago
function actualizarPago($conn, $tpActual, $de, $ca, $imNuevo, $tot, $cF, $fo)
{
    // Se actualizan los datos del pago
    $stmt = $conn->prepare("UPDATE pagos SET cT = ?, de = ?, ca = ?, im = ?, tot = ?, cF = ? WHERE fo = ?");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta de actualización de pago: " . $conn->error);
    }
    $stmt->bind_param("sssssss", $tpActual, $de, $ca, $imNuevo, $tot, $cF, $fo);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se realizaron cambios");
    }
    $stmt->close();
}
// Verificar si los datos requeridos existen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y validar datos
    $fo = trim($_POST['foMod'] ?? '');
    $fe = trim($_POST['feMod']);
    $nC = trim($_POST['nCa'] ?? '');
    $alumno = trim($_POST['alumno'] ?? '');
    $tpActual = isset($_POST['tpa']) ? (int)$_POST['tpa'] : 0;
    $de = trim($_POST['deMod'] ?? '');
    $ca = trim($_POST['caMod'] ?? '');
    $imActual = isset($_POST['imA']) ? (float)$_POST['imA'] : 0.0;
    $imNuevo = isset($_POST['imMod']) ? (float)$_POST['imMod'] : 0.0;
    $tot = trim($_POST['totMod'] ?? '');
    $cF = trim($_POST['formaPagoMod'] ?? '');
    $respuesta = ['exito' => true, 'mensajes' => []];
    $conn->begin_transaction(); // Iniciar transacción
    try {
        // Configuración de tipos de pago
        $pagosConfig = [
            1 => ['costo' => 'coIns', 'estatus' => 'ins', 'descripcion' => 'inscripción'],
            2 => ['costo' => 'coRei', 'estatus' => 'rein', 'descripcion' => 'reinscripción'],
            3 => ['costo' => 'coCol', 'estatus' => 'cole', 'descripcion' => 'colegiatura']
        ];
        $tipoPagoValido = isset($pagosConfig[$tpActual]);
        if ($imNuevo !== $imActual) { // Si el importe nuevo es diferente al actual
            if ($imNuevo < $imActual) { // Si el importe nuevo es menor que el actual
                $conn->begin_transaction();
                try {
                    // Se llama a la función actualizar
                    actualizarPago($conn, $tpActual, $de, $ca, $imNuevo, $tot, $cF, $fo);
                    // Si el pago es 1, 2 o 3, se actualiza el estatus del pago
                    if ($tipoPagoValido) {
                        $estatus = $pagosConfig[$tpActual]['estatus']; // Columna del estatus
                        $descripcion = $pagosConfig[$tpActual]['descripcion']; // Descripción del tipo de pago
                        $stmt2 = $conn->prepare("UPDATE inscripciones SET $estatus = 'No' WHERE nC = ?");
                        if ($stmt2 === false) {
                            throw new Exception("Error al actualizar el estatus de la $descripcion: " . $conn->error);
                        }
                        $stmt2->bind_param("s", $nC);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                    // Registrar la acción en la bitácora
                    $clave = $_SESSION['clave'];
                    $accion = "Modificó el pago $fo del alumno $alumno";
                    $fecha = date('Y-m-d');
                    $hora = date('H:i:s');
                    $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
                    $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
                    $stmt_bitacora->execute();
                    $stmt_bitacora->close();
                    // Se confirman los cambios
                    $conn->commit();
                    responder(true, "El pago $fo del alumno $alumno se modificó correctamente."); // Respuesta
                } catch (Exception $e) {
                    $conn->rollback();
                    responder(false, "Error: " . $e->getMessage());
                }
            } elseif ($imNuevo > $imActual) { // si el importe nuevo es mayor al actual
                responder(false, "No puede aumentar el importe de un pago, ingrese otro pago por favor"); // Respuesta
            }
        } else { // Si no se cumple ninguna de las condiciones anteriores
            // Se llama a la función actualizar
            actualizarPago($conn, $tpActual, $de, $ca, $imNuevo, $tot, $cF, $fo);
        }
        // Registrar la acción en la bitácora
        $accion = "Modificó el pago $fo del alumno $alumno";
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (cU, ac, fe, ho) VALUES (?, ?, ?, ?)");
        $stmt_bitacora->bind_param("ssss", $clave, $accion, $fecha, $hora);
        $stmt_bitacora->execute();
        $stmt_bitacora->close();
        // Confirmar transacción
        $conn->commit();
        responder(true, "El pago $fo del alumno $alumno se modificó correctamente.");
    } catch (Exception $e) {
        $conn->rollback();
        responder(false, $e->getMessage());
    } finally {
        $conn->close();
    }
}
