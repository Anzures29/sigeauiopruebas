<?php
include '../../conexion.php';
header('Content-Type: application/json');
// Recibir parámetros del usuario y definir valores predeterminados
$registros = intval($_GET['limit'] ?? 10);
$pagina = intval($_GET['page'] ?? 1);
$buscar = $_GET['buscar'] ?? '';
// Calcular el offset para la paginación
$offset = ($pagina - 1) * $registros;
// Preparar la consulta SQL
try {
    if ($buscar === "inscripcion") {
        // Filtrar únicamente registros de inscripción
        $query = "SELECT pagos.fo, pagos.fe,
                    CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
                    tipopago.tipo, pagos.de, pagos.ca, pagos.im, pagos.cT, pagos.nC, formapago.forma
                    FROM pagos
                    INNER JOIN alumnos ON pagos.nC = alumnos.nC
                    INNER JOIN tipopago ON pagos.cT = tipopago.cv
                    INNER JOIN formapago ON pagos.cF = formapago.cv
                    WHERE tipopago.tipo = 'inscripción'
                    ORDER BY pagos.fo ASC
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $registros, $offset);
    } elseif ($buscar === "reinscripcion") {
        // Filtrar únicamente registros de reinscripción
        $query = "SELECT pagos.fo, pagos.fe,
                    CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
                    tipopago.tipo, pagos.de, pagos.ca, pagos.im, pagos.cT, pagos.nC, formapago.forma
                    FROM pagos
                    INNER JOIN alumnos ON pagos.nC = alumnos.nC
                    INNER JOIN tipopago ON pagos.cT = tipopago.cv
                    INNER JOIN formapago ON pagos.cF = formapago.cv
                    WHERE tipopago.tipo = 'reinscripción'
                    ORDER BY pagos.fo ASC
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $registros, $offset);
    } else {
        // Búsqueda general en todas las columnas
        $searchTerm = "%$buscar%";
        $query = "SELECT pagos.fo, pagos.fe,
                    CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
                    tipopago.tipo, pagos.de, pagos.ca, pagos.im, pagos.cT, pagos.nC, formapago.forma
                    FROM pagos
                    INNER JOIN alumnos ON pagos.nC = alumnos.nC
                    INNER JOIN tipopago ON pagos.cT = tipopago.cv
                    INNER JOIN formapago ON pagos.cF = formapago.cv
                    WHERE pagos.fo LIKE ? OR pagos.fe LIKE ? 
                        OR alumnos.nom LIKE ? 
                        OR tipopago.tipo LIKE ? 
                        OR pagos.de LIKE ? 
                        OR pagos.ca LIKE ? 
                        OR pagos.im LIKE ? 
                        OR formapago.forma LIKE ?
                    ORDER BY pagos.fo ASC
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'ssssssssii',
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $registros,
            $offset
        );
    }
    $stmt->execute();
    $result = $stmt->get_result();
    // Recopilar datos de la consulta principal
    $ofertas = $result->fetch_all(MYSQLI_ASSOC);
    // Contar el total de registros que coinciden con el término de búsqueda (sin paginación)
    $countQuery = "SELECT COUNT(*) as total 
                    FROM pagos
                    INNER JOIN alumnos ON pagos.nC = alumnos.nC
                    INNER JOIN tipopago ON pagos.cT = tipopago.cv
                    INNER JOIN formapago ON pagos.cF = formapago.cv
                    WHERE pagos.fo LIKE ? 
                        OR pagos.fe LIKE ? 
                        OR alumnos.nom LIKE ? 
                        OR tipopago.tipo LIKE ? 
                        OR pagos.de LIKE ? 
                        OR pagos.ca LIKE ? 
                        OR pagos.im LIKE ? 
                        OR formapago.forma LIKE ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param(
        'ssssssss',
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRegistros = $countResult->fetch_assoc()['total'];
    // Calcular el total de páginas
    $totalPaginas = ceil($totalRegistros / $registros);
    // Respuesta JSON con los datos y la información de paginación
    echo json_encode([
        'data' => $ofertas,
        'totalRecords' => $totalRegistros, // Total de registros coincidentes
        'totalPages' => $totalPaginas,
        'currentPage' => $pagina
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
