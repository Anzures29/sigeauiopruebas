<?php
include '../../conexion.php';
header('Content-Type: application/json');
// Recibir parámetros del usuario y definir valores predeterminados
$registros = intval($_GET['limit'] ?? 10);
$pagina = intval($_GET['page'] ?? 1);
$buscar = $_GET['buscar'] ?? '';
// Calcular el offset para la paginación
$offset = ($pagina - 1) * $registros;
// Preparar el término de búsqueda
$searchTerm = "%$buscar%";
// Consulta SQL para obtener los datos paginados
$query = "SELECT alumnos.nC, alumnos.ma, CONCAT(alumnos.aP, ' ', alumnos.aM, ' ', alumnos.nom) AS alumno, municipios.mu, alumnos.fN, alumnos.ed,
                alumnos.cu, alumnos.se, alumnos.ca, colonias.co, alumnos.ts, alumnos.af, alumnos.te, alumnos.em
            FROM alumnos
            INNER JOIN municipios ON alumnos.cM = municipios.cv
            INNER JOIN colonias ON alumnos.cCo = colonias.cv
            WHERE alumnos.nC LIKE ?
                OR alumnos.ma LIKE ?
                OR alumnos.nom LIKE ?
                OR municipios.mu LIKE ?
                OR alumnos.fN LIKE ?
                OR alumnos.cu LIKE ?
                OR alumnos.te LIKE ?
                OR alumnos.em LIKE ?
            ORDER BY alumnos.nC ASC
                LIMIT ? OFFSET ?";
// Preparar la consulta SQL
$stmt = $conn->prepare($query);
// Vincular parámetros de búsqueda y paginación
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
$stmt->execute();
$result = $stmt->get_result();
// Recopilar datos de la consulta principal
$ofertas = $result->fetch_all(MYSQLI_ASSOC);
// Consulta para contar el total de registros que coinciden con el término de búsqueda
$countQuery = "SELECT COUNT(*) as total
                FROM alumnos
                INNER JOIN municipios ON alumnos.cM = municipios.cv
                INNER JOIN colonias ON alumnos.cCo = colonias.cv
                WHERE alumnos.nC LIKE ?
                    OR alumnos.ma LIKE ?
                    OR alumnos.nom LIKE ?
                    OR municipios.mu LIKE ?
                    OR alumnos.fN LIKE ?
                    OR alumnos.cu LIKE ?
                    OR alumnos.te LIKE ?
                    OR alumnos.em LIKE ?";
$countStmt = $conn->prepare($countQuery);
// Vincular parámetros de búsqueda para contar los registros
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
    'totalRecords' => $totalRegistros,
    'totalPages' => $totalPaginas,
    'currentPage' => $pagina
]);
// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
