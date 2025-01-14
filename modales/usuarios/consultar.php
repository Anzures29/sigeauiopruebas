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
$query = "SELECT usuarios.cv, usuarios.us, usuarios.cR, usuarios.cE, roles.rol, empleados.em,
            CONCAT(empleados.nom, ' ', empleados.aP, ' ', empleados.aM) AS empleado
         FROM usuarios
         INNER JOIN roles ON usuarios.cR = roles.cv
         LEFT JOIN empleados ON usuarios.cE = empleados.cv
            WHERE usuarios.us LIKE ?
            OR roles.rol LIKE ?
            OR empleados.nom LIKE ?
            ORDER BY usuarios.cv ASC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $registros, $offset);
$stmt->execute();
$result = $stmt->get_result();
// Recopilar datos de la consulta principal
$empleados = $result->fetch_all(MYSQLI_ASSOC);
// Contar el total de registros que coinciden con el término de búsqueda (sin paginación)
$countQuery = "SELECT COUNT(*) as total 
               FROM usuarios
               INNER JOIN roles ON usuarios.cR = roles.cv
               LEFT JOIN empleados ON usuarios.cE = empleados.cv
               WHERE usuarios.us LIKE ?
                  OR roles.rol LIKE ?
                  OR empleados.nom LIKE ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
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
