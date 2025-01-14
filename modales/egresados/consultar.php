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
// Consultas SQL para obtener los datos, excluyendo los registros con cC NULL
$query =    "SELECT e.fol, e.nC, e.ft, CONCAT(e.nom, ' ', e.aP, ' ', e.aM) AS Alumno, n.ni, COALESCE(c.ca, '') AS carrera, 
                DATE_FORMAT(e.feIng, '%d-%m-%Y') AS feIng,
                DATE_FORMAT(e.feEgr, '%d-%m-%Y') AS feEgr, 
                COALESCE(e.pr, '') AS promedio
            FROM egresados e 
            INNER JOIN niveles n ON e.cN = n.cv 
            LEFT JOIN carreras c ON e.cC = c.cv 
            WHERE e.fol LIKE ?
                OR e.nC LIKE ?
                OR e.ft LIKE ?
                OR e.nom LIKE ?
                OR n.ni LIKE ?
                OR c.ca LIKE ?
                OR e.feIng LIKE ?
                OR e.feEgr LIKE ?
                OR e.pr LIKE ?
            ORDER BY e.nC ASC
            LIMIT ? OFFSET ?";
// Preparar la consulta
$stmt = $conn->prepare($query);
$stmt->bind_param(
    'sssssssssii',
    $searchTerm,
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
$egresados = $result->fetch_all(MYSQLI_ASSOC);
// Contar el total de registros que coinciden con el término de búsqueda (sin paginación)
$countQuery =   "SELECT COUNT(*) as total 
                FROM egresados e 
                INNER JOIN niveles n ON e.cN = n.cv 
                LEFT JOIN carreras c ON e.cC = c.cv 
                WHERE e.fol LIKE ?
                    OR e.nC LIKE ?
                    OR e.ft LIKE ?
                    OR e.nom LIKE ?
                    OR n.ni LIKE ?
                    OR c.ca LIKE ?
                    OR e.feIng LIKE ?
                    OR e.feEgr LIKE ?
                    OR e.pr LIKE ?
                ORDER BY e.nC ASC";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param(
    'sssssssss',
    $searchTerm,
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
    'data' => $egresados,
    'totalRecords' => $totalRegistros, // Total de registros coincidentes
    'totalPages' => $totalPaginas,
    'currentPage' => $pagina
]);
// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
