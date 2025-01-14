<?php
include '../../conexion.php';
header('Content-Type: application/json');

// Recibir parámetros del usuario y definir valores predeterminados
$registros = intval($_GET['limit'] ?? 10);
$pagina = intval($_GET['page'] ?? 1);
$buscar = $_GET['buscar'] ?? '';
$mes = $_GET['mes'] ?? date('Y-m'); // Mes seleccionado

// Calcular el offset para la paginación
$offset = ($pagina - 1) * $registros;

// Preparar el término de búsqueda
$searchTerm = "%$buscar%";

// Definir el rango de fechas del mes seleccionado
$fechaInicio = $mes . '-01';
$fechaFin = date("Y-m-t", strtotime($fechaInicio));

// Consulta SQL para obtener los datos paginados con filtros
$query =    "SELECT bitacora.cv, usuarios.us, 
                CONCAT(
                    COALESCE(empleados.nom, ''), ' ', 
                    COALESCE(empleados.aP, ''), ' ', 
                    COALESCE(empleados.aM, '')
                ) AS nombre, 
                bitacora.ac, CONCAT(bitacora.fe, ' ', bitacora.ho) AS fechahora
            FROM bitacora
            INNER JOIN usuarios ON bitacora.cU = usuarios.cv
            LEFT JOIN empleados ON usuarios.cE = empleados.cv
            WHERE (bitacora.fe BETWEEN ? AND ?)
            AND (bitacora.cv LIKE ?
                OR usuarios.us LIKE ?
                OR empleados.nom LIKE ?
                OR bitacora.ac LIKE ?
                OR bitacora.fe LIKE ?)
            ORDER BY bitacora.cv ASC
            LIMIT ? OFFSET ?";

// Preparar la consulta SQL
$stmt = $conn->prepare($query);

// Vincular parámetros de búsqueda y paginación
$stmt->bind_param('ssssssiii', $fechaInicio, $fechaFin, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $registros, $offset);

$stmt->execute();
$result = $stmt->get_result();

// Recopilar datos de la consulta principal
$bitacora = $result->fetch_all(MYSQLI_ASSOC);

// Consulta para contar el total de registros que coinciden con el término de búsqueda
$countQuery = "SELECT COUNT(*) as total
                FROM bitacora
                INNER JOIN usuarios ON bitacora.cU = usuarios.cv
                LEFT JOIN empleados ON usuarios.cE = empleados.cv
                WHERE (bitacora.fe BETWEEN ? AND ?)
                AND (bitacora.cv LIKE ? OR usuarios.us LIKE ? OR empleados.nom LIKE ? OR bitacora.ac LIKE ? OR bitacora.fe LIKE ?)";

$countStmt = $conn->prepare($countQuery);

// Vincular parámetros de búsqueda para contar los registros
$countStmt->bind_param('sssssss', $fechaInicio, $fechaFin, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRegistros = $countResult->fetch_assoc()['total'];

// Calcular el total de páginas
$totalPaginas = ceil($totalRegistros / $registros);

// Respuesta JSON con los datos y la información de paginación
echo json_encode([
    'data' => $bitacora,
    'totalRecords' => $totalRegistros,
    'totalPages' => $totalPaginas,
    'currentPage' => $pagina
]);

// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
