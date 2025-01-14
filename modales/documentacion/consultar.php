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
$query = "SELECT inscripciones.nC, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno,
            niveles.ni, COALESCE(carreras.ca, '') AS carrera, inscripciones.cN
            FROM inscripciones
            INNER JOIN alumnos ON inscripciones.nC = alumnos.nC
            INNER JOIN niveles ON inscripciones.cN = niveles.cv
            LEFT JOIN carreras ON inscripciones.cC = carreras.cv
            WHERE inscripciones.nC LIKE ?
                OR alumnos.nom LIKE ?
                OR niveles.ni LIKE ?
                OR carreras.ca LIKE ?
            ORDER BY alumnos.nC ASC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ssssii', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $registros, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Recopilar datos de la consulta principal
$inscripciones = $result->fetch_all(MYSQLI_ASSOC);

// Contar el total de registros que coinciden con el término de búsqueda (sin paginación)
$countQuery = "SELECT COUNT(*) as total
                FROM inscripciones
                INNER JOIN alumnos ON inscripciones.nC = alumnos.nC
                INNER JOIN niveles ON inscripciones.cN = niveles.cv
                LEFT JOIN carreras ON inscripciones.cC = carreras.cv
                WHERE alumnos.nC LIKE ?
                    OR alumnos.nom LIKE ?
                    OR niveles.ni LIKE ?
                    OR carreras.ca LIKE ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRegistros = $countResult->fetch_assoc()['total'];

// Calcular el total de páginas
$totalPaginas = ceil($totalRegistros / $registros);

// Respuesta JSON con los datos y la información de paginación
echo json_encode([
    'data' => $inscripciones,
    'totalRecords' => $totalRegistros, // Total de registros coincidentes
    'totalPages' => $totalPaginas,
    'currentPage' => $pagina
]);

// Cierre
$stmt->close();
$countStmt->close();
$conn->close();
