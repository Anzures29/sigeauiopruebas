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

// Consultas SQL para obtener los datos
$query = "SELECT empleados.cv, empleados.nom,
        CONCAT(empleados.aP, ' ', empleados.aM) AS apellidos, empleados.te, empleados.em, empleados.ca, colonias.co, roles.rol, empleados.su
        FROM empleados
        INNER JOIN colonias ON empleados.cCo = colonias.cv
        INNER JOIN roles ON empleados.cR = roles.cv
        WHERE empleados.nom LIKE ?
            OR empleados.aP LIKE ?
            OR empleados.te LIKE ?
            OR empleados.em LIKE ?
            OR empleados.ca LIKE ?
            OR colonias.co LIKE ?
            OR roles.rol LIKE ?
            OR empleados.su LIKE ?
        ORDER BY empleados.cv ASC
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
$stmt->execute();
$result = $stmt->get_result();

// Recopilar datos de la consulta principal
$empleados = $result->fetch_all(MYSQLI_ASSOC);

// Contar el total de registros que coinciden con el término de búsqueda (sin paginación)
$countQuery = "SELECT COUNT(*) as total
            FROM empleados
            INNER JOIN colonias ON empleados.cCo = colonias.cv
            INNER JOIN roles ON empleados.cR = roles.cv
            WHERE empleados.nom LIKE ?
                OR empleados.aP LIKE ?
                OR empleados.te LIKE ?
                OR empleados.em LIKE ?
                OR empleados.ca LIKE ?
                OR colonias.co LIKE ?
                OR roles.rol LIKE ?
                OR empleados.su LIKE ?";
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
    'data' => $empleados,
    'totalRecords' => $totalRegistros, // Total de registros coincidentes
    'totalPages' => $totalPaginas,
    'currentPage' => $pagina
]);

// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
