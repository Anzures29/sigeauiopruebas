<?php
// Incluir la biblioteca TCPDF
require_once('../../tcpdf/tcpdf.php');
include_once('../../conexion.php');
date_default_timezone_set('Ciudad de México');
ob_end_clean(); // Limpiar la memoria y prevenir problemas con cabeceras
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("Error: Los datos no se recibieron en modo GET.");
}
// Datos de la inscripción
$fo = trim($_GET['fol']); // Folio
$fe = trim($_GET['fe']); // Fecha
$cN = trim($_GET['nivel']); // Clave nivel
$cC = isset($_GET['carrera']) ? trim($_GET['carrera']) : null; // Clave carrera
$ho = trim($_GET['ho']); // Horario de clase
$di = trim($_GET['di']); // Día de clase
$feI = trim($_GET['feI']); // Fecha inicio de clases
$feF = trim($_GET['feF']); // Fecha término de clases
$coIns = (float) $_GET['costo']; // Costo de la inscripción
$coCol = (float) $_GET['cole']; // Costo de la colegiatura
$coRei = (float) $_GET['rein']; // Costo de la reinscripción
// Datos personales del alumno
$nC = trim($_GET['nC']);
$aP = trim($_GET['aP']); // Apellido paterno
$aM = trim($_GET['aM']); // Apellido materno
$nom = trim($_GET['nom']); // Nombre
$lN = trim($_GET['lugarNacimiento']);
$fN = trim($_GET['fechaNacimiento']);
$ed = (int) $_GET['ed']; // Edad
$cu = trim($_GET['cu']); // Curp 
$se = trim($_GET['se']); // Sexo
$dom = trim($_GET['ca']); // Calle
$cCo = trim($_GET['coloniaAlumno']); // Clave colonia
$ts = trim($_GET['ts']); // Tipo de sangre
$af = trim($_GET['af']); // Alerfías o enfermedades
$te = trim($_GET['te']); // Teléfono
$em = trim($_GET['em']); // Correo
// Datos de la Escuela de Procedencia
$cct = isset($_GET['cct']) ? trim($_GET['cct']) : null; // Clave centro de trabajo
$ge = isset($_GET['ge']) ? trim($_GET['ge']) : null; // Generación
$pr = isset($_GET['pr']) ? trim($_GET['pr']) : null; // Promedio
// Datos del Tutor
$cuT = isset($_GET['curpTutor']) ? trim($_GET['curpTutor']) : null; // Curp tutor
$nomT = isset($_GET['nomTutor']) ? trim($_GET['nomTutor']) : null; // Nombre tutor
$pa = isset($_GET['pa']) ? trim($_GET['pa']) : null; // Parentesco
$telT = isset($_GET['teTutor']) ? trim($_GET['teTutor']) : null; // Teléfono tutor
$domT = isset($_GET['calleTutor']) ? trim($_GET['calleTutor']) : null; // Calle tutor
$cCoT = isset($_GET['coloniaTutor']) ? trim($_GET['coloniaTutor']) : null; // Clave colonia tutor
// VARIABLES CON DATOS DE PRUEBA
$es = "TABASCO";
$esE = "TABASCO";

if (isset($_GET['ft']) && !empty($_GET['ft'])) {
    $imagen_nombre = basename($_GET['ft']); // Extraer el nombre del archivo enviado
    $directorio_destino = 'C:/xampp/htdocs/SIGEAUIO/modales/inscripciones/img/';
    $fotoAlumno = $directorio_destino . $imagen_nombre; // Construir la ruta completa
}
try {
    // Obtener la carrera del alumno
    if ($cC !== null) {
        $stmtCarrera = $conn->prepare("SELECT ca FROM carreras WHERE cv = ?");
        $stmtCarrera->bind_param('s', $cC);
        $stmtCarrera->execute();
        $resultCarrera = $stmtCarrera->get_result();
        $row = $resultCarrera->fetch_assoc();
        $ca = $row['ca'] ?? null; // Campo 'ca' de la tabla carreras
        if (!$ca) {
            die("Error: No se encontró la carrera");
        }
        $stmtCarrera->close();
    } else {
        $ca = "";
    }
    // Obtener municipio de nacimiento
    if ($lN !== null) {
        $stmtMunNaci = $conn->prepare("SELECT mu FROM municipios WHERE cv = ?");
        $stmtMunNaci->bind_param('s', $lN);
        $stmtMunNaci->execute();
        $resultMunNaci = $stmtMunNaci->get_result();
        $row = $resultMunNaci->fetch_assoc();
        $lN = $row['mu'] ?? null; // Campo 'mu' de la tabla municipios
        if (!$lN) {
            die("Error: No se encontró el municipio de nacimiento");
        }
        $stmtMunNaci->close();
    } else {
        $lN = "";
    }
    // Obtener la colonia y el municipio del alumno
    $stmt = $conn->prepare("SELECT co, cM FROM colonias WHERE cv = ?");
    $stmt->bind_param('s', $cCo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $col = $row['co']; // Colonia
        $cM = $row['cM']; // Clave municipio
        // Obtener el municipio
        $stmt->close(); // Cerrar el stmt antes de reutilizarlo
        $stmt = $conn->prepare("SELECT mu FROM municipios WHERE cv = ?");
        $stmt->bind_param('s', $cM);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $mu = $row['mu'] ?? null;
        if (!$mu) {
            die("Error: No se encontró el municipio del alumno");
        }
    } else {
        die("Error: No se encontró la colonia del alumno");
    }
    $stmt->close();
    // Obtener la escuela, calle y municipio
    if (!empty($cct)) {
        $stmt = $conn->prepare("SELECT es, ca, cM FROM cctescuelas WHERE cct = ?");
        $stmt->bind_param('s', $cct);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $esc = $row['es']; // Escuela
            $domE = $row['ca']; // Calle de la escuela
            $cME = $row['cM']; // Clave municipio de la escuela
            // Obtener el municipio
            $stmt->close(); // Cerrar el stmt antes de reutilizarlo
            $stmt = $conn->prepare("SELECT mu FROM municipios WHERE cv = ?");
            $stmt->bind_param('s', $cME);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $muE = $row['mu'] ?? null;
            if (!$muE) {
                die("Error: No se encontró el municipio de la escuela");
            }
        } else {
            die("Error: No se encontró la escuela");
        }
        $stmt->close();
    } else {
        $esc = "";
        $domE = "";
        $muE = "";
    }
    // Obtener la colonia y el municipio del tutor
    if (!empty($cCoT)) {
        $stmt = $conn->prepare("SELECT co, cM FROM colonias WHERE cv = ?");
        $stmt->bind_param('s', $cCoT);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $colT = $row['co'];
            $cMT = $row['cM'];
            // Obtener el municipio
            $stmt->close(); // Cerrar el stmt antes de reutilizarlo
            $stmt = $conn->prepare("SELECT mu FROM municipios WHERE cv = ?");
            $stmt->bind_param('s', $cMT);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $muT = $row['mu'] ?? null;
            if (!$muT) {
                die("Error: No se encontró el municipio del tutor");
            }
        } else {
            die("Error: No se encontró la colonia del tutor");
        }
        $stmt->close();
    } else {
        $colT = "";
        $muT = "";
    }
} catch (Exception $e) {
    die("Error inesperado al validar datos: " . $e->getMessage());
}
// Array de Documentos Requeridos por Nivel Educativo
$documentosRequeridosPorNivel = array(
    "1" => array("Acta Nacimiento", "CURP", "Certificado Secundaria", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"),
    "2" => array("Acta Nacimiento", "CURP", "Cert. Bachillerato", "Certificado Validacion", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"),
    "3" => array("Acta Nacimiento", "CURP", "Certificado Licenciatura", "Cédula Licenciatura", "Título Licenciatura", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil"),
    "4" => array("Acta Nacimiento", "CURP", "Certificado Maestría", "Cédula Maestría", "Título Maestría", "Certificado Médico con Tipo de Sangre", "Comprobante de Domicilio", "INE", "Fotografia Infantil")
);

// Crear un nuevo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// Establecer información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Universidad Intercontinental Olimpo');
$pdf->SetTitle('Ficha de Inscripción');
$pdf->SetSubject('Ficha de Inscripción');
$pdf->SetKeywords('TCPDF, PDF, inscripción, universidad');

// Añadir una página
$pdf->AddPage();


// Establecer el color de relleno (RGB: 220, 220, 220 para un gris claro)
$pdf->SetFillColor(220, 220, 220);
$pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');
// Imagen centrada como marca de agua
$pdf->SetAlpha(0.1);
$pdf->Image('/xampp/htdocs/SIGEAUIO/img/logo.png', ($pdf->getPageWidth() - 100) / 2, ($pdf->getPageHeight() - 100) / 2, 100, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, 0, false);
$pdf->SetAlpha(1);


// PARTE SUPERIOR
// Logos
$logoUniversidad = '/xampp/htdocs/SIGEAUIO/img/logo.png'; // Ruta logo olimpo
$logoSEP = '/xampp/htdocs/SIGEAUIO/img/sep.png'; // Ruta logo sep
$logoTabasco = '/xampp/htdocs/SIGEAUIO/img/tabasco.png'; // Ruta logo tabasco
$pdf->Image($logoUniversidad, 0, 0, 35, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Logo Olimpo
$pdf->Image($logoSEP, 88, 3, 60, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Logo SEP
$pdf->Image($logoTabasco, 151, 5, 55, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Logo TABASCO
// Etiqueta nombre de la universidad (a un lado del logo olimpo)
$pdf->SetTextColor(139, 69, 19); // Color
$pdf->SetFont('pdfacourier', 'B', 15); // Fuente
$pdf->SetXY(33, 6); // Posición
$pdf->Cell(60, 0, "UNIVERSIDAD", 0, 0, 'L'); // Línea de texto
$pdf->SetXY(33, 12);
$pdf->Cell(60, 0, "INTERCONTINENTAL", 0, 0, 'L');
$pdf->SetXY(33, 18);
$pdf->Cell(60, 0, "OLIMPO", 0, 0, 'L');
// Etiqueta "Líderes en Calidad de Excelencia"
$pdf->SetTextColor(139, 69, 19); // Color
$pdf->SetFont('greatvibes', '', 40); // Fuente
$pdf->SetXY(5, 23); // Posición
$pdf->Cell(0, 10, 'Líderes en Calidad de Excelencia', 0, 1, 'C'); // Línea de texto
// Etiqueta "Ficha de inscripción"
$pdf->SetTextColor(109, 141, 60); // Color
$pdf->SetFont('helvetica', 'B', 18); // Fuente
$pdf->SetXY(5, 33); // Posición
$pdf->Cell(0, 10, 'FICHA DE INSCRIPCIÓN', 0, 1, 'C'); // Línea de texto
// Etiqueta "Campus San Carlos"
$pdf->SetTextColor(139, 69, 19); // Color
$pdf->SetFont('helvetica', 'B', 15); // Fuente
$pdf->SetXY(5, 38); // Posición
$pdf->Cell(0, 10, 'CAMPUS "SAN CARLOS"', 0, 1, 'C'); // Línea de texto


// DATOS DE LA INSCRIPCIÓN
$pdf->SetTextColor(0, 0, 0); // Establece el color del texto a negro (RGB: 0, 0, 0)
$pdf->SetFont('helvetica', 'B', 10); // Establece la fuente a 'helvetica', en negrita, con tamaño 10
// Función para añadir una celda con etiqueta y valor (para 'Folio')
function addLabeledCellFol($pdf, $label, $value, $x, $y)
{
    $pdf->SetXY($x, $y); // Establece la posición X y Y del cursor
    $labelWidth = $pdf->GetStringWidth($label) + 2; // Calcula el ancho de la etiqueta con un pequeño margen
    $valueWidth = $pdf->GetStringWidth($value) + 2; // Calcula el ancho del valor con un pequeño margen
    $pdf->Cell($labelWidth, 6, $label, 0, 0, 'L'); // Añade una celda con la etiqueta, alineada a la izquierda
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), $valueWidth, 6, 1, '1111'); // Dibuja un rectángulo redondeado alrededor del valor
    $pdf->Cell($valueWidth, 6, $value, 0, 1, 'C'); // Añade una celda con el valor, centrado
}
// Foto del Alumno
$xFoto = 10; // Posición X
$yFoto = 35; // Posición Y
$anchoFoto = 28; // Ancho del cuadro en mm
$altoFoto = 38; // Alto del cuadro en mm
$pdf->RoundedRect($xFoto, $yFoto, $anchoFoto, $altoFoto, 1, '1111'); // Cuadrado
$pdf->Image($fotoAlumno, $xFoto, $yFoto, $anchoFoto, $altoFoto, '', '', '', false, 300, '', false, false, 0, false, false, false); // Se coloca la foto
// Etiqueta FOLIO
addLabeledCellFol($pdf, 'FOLIO:', $fo, 42, 46); // Posición X: 42, Y: 46
// Etiqueta FECHA
$pdf->SetXY(138, 46); // Posición X: 138, Y: 48
$pdf->Cell(27, 6, 'FECHA:', 0, 0, 'L');
// Añade celdas etiquetadas para el día, mes y año de la fecha de inscripción
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111'); // Rectángulo redondeado para el día
$pdf->Cell(10, 6, date('d', strtotime($fe)), 0, 0, 'C'); // Celda con el día de la fecha, centrado
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111'); // Rectángulo redondeado para el mes
$pdf->Cell(10, 6, date('m', strtotime($fe)), 0, 0, 'C'); // Celda con el mes de la fecha, centrado
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 15, 6, 1, '1111'); // Rectángulo redondeado para el año
$pdf->Cell(15, 6, date('Y', strtotime($fe)), 0, 1, 'C'); // Celda con el año de la fecha, centrado
// Función para marcar la celda con una "X" si corresponde al nivel seleccionado
function marcarCelda($pdf, $x, $y)
{
    $pdf->SetXY($x, $y);
    $pdf->Cell(5, 6, 'X', 0, 0, 'C');
}
// Etiqueta "BACHILLERATO" con su celda
$pdf->SetXY(42, 53); // Posición X: 42, Y: 53
$pdf->Cell(30, 6, 'BACHILLERATO', 0, 0, 'L'); // Texto
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 5, 6, 1, '1111'); // Celda
if ($cN == 1) {
    marcarCelda($pdf, $pdf->GetX(), $pdf->GetY());
}
// Etiqueta "LICENCIATURA" con su celda
$pdf->SetXY(80, 53); // Posición X: 80, Y: 53
$pdf->Cell(30, 6, 'LICENCIATURA', 0, 0, 'L'); // Texto
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 5, 6, 1, '1111'); // Celda
if ($cN == 2) {
    marcarCelda($pdf, $pdf->GetX(), $pdf->GetY());
}
// Etiqueta "MAESTRÍA" con su celda
$pdf->SetXY(120, 53); // Posición X: 120, Y: 53
$pdf->Cell(30, 6, 'MAESTRÍA', 0, 0, 'L'); // Texto
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 5, 6, 1, '1111'); // Celda
if ($cN == 3) {
    marcarCelda($pdf, $pdf->GetX(), $pdf->GetY());
}
// Etiqueta "DOCTORADO" con su celda
$pdf->SetXY(160, 53); // Posición X: 160, Y: 53
$pdf->Cell(30, 6, 'DOCTORADO', 0, 0, 'L'); // Texto
$pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 5, 6, 1, '1111'); // Celda
if ($cN == 4) {
    marcarCelda($pdf, $pdf->GetX(), $pdf->GetY());
}
// Etiqueta "CARRERA"
$pdf->SetXY(42, 60); // Posición X: 42, Y: 60
$pdf->Cell(30, 6, 'CARRERA:', 0, 0, 'L'); // Texto de la etiqueta
$pdf->Line($pdf->GetX() - 10, $pdf->GetY() + 5, $pdf->GetX() + 128, $pdf->GetY() + 5); // Línea debajo de la etiqueta
$pdf->SetXY(62, 60); // Ajuste de posición para el valor
$pdf->Cell(60, 6, $ca, 0, 0, 'L'); // Valor de la variable $ca
// Etiqueta "HORARIO"
$pdf->SetXY(42, 67); // Posición X: 42, Y: 67
$pdf->Cell(30, 6, ' HORARIO:', 0, 0, 'L'); // Texto de la etiqueta
$pdf->Line($pdf->GetX() - 10, $pdf->GetY() + 5, $pdf->GetX() + 21, $pdf->GetY() + 5); // Línea debajo de la etiqueta
$pdf->SetXY(62, 67); // Ajuste de posición para el valor
$pdf->Cell(60, 6, $ho, 0, 0, 'L'); // Valor de la variable $ho
// Etiqueta "DÍAS"
$pdf->SetXY(92, 67); // Posición X: 92, Y: 67
$pdf->Cell(30, 6, 'DÍA:', 0, 0, 'L'); // Texto de la etiqueta
$pdf->Line($pdf->GetX() - 19, $pdf->GetY() + 5, $pdf->GetX() + 21, $pdf->GetY() + 5); // Línea debajo de la etiqueta
$pdf->SetXY(103, 67); // Ajuste de posición para el valor
$pdf->Cell(60, 6, $di, 0, 0, 'L'); // Valor de la variable $di
// Etiqueta "FECHA DE INICIO"
$pdf->SetXY(142, 67); // Posición X: 142, Y: 67
$pdf->Cell(30, 6, 'FECHA DE INICIO:', 0, 0, 'L'); // Texto de la etiqueta
$pdf->Line($pdf->GetX() + 2, $pdf->GetY() + 5, $pdf->GetX() + 28, $pdf->GetY() + 5); // Línea debajo de la etiqueta
$pdf->SetXY(174, 67); // Ajuste de posición para el valor
$pdf->Cell(60, 6, $feI, 0, 0, 'L'); // Valor de la variable $feIni


// DATOS PERSONALES
// Etiqueta "DATOS PERSONALES DEL ALUMNO"
$pdf->SetTextColor(109, 141, 60); // Color
$pdf->SetFont('helvetica', 'B', 13); // Fuente
$pdf->SetXY(10, 70); // Posición
$pdf->Cell(0, 10, 'DATOS PERSONALES', 0, 0, 'C'); // Línea de texto
// Establece el color del texto a negro (RGB: 0, 0, 0)
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 8);

// Ancho total disponible para las celdas (210 mm ancho de página - 20 mm márgenes)
$anchoTotal = 210 - 20;
// Ancho de cada celda
$anchoCelda = $anchoTotal / 3;
// Etiqueta "APELLIDO PATERNO"
$pdf->SetXY(26, 76); // Posición X: 23, Y: 76
$pdf->Cell($anchoCelda, 6, 'APELLIDO PATERNO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, 81); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($aP, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda para el valor de $aP centrado
// Etiqueta "APELLIDO MATERNO"
$pdf->SetXY(26 + $anchoCelda, 76); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'APELLIDO MATERNO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, 81); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($aM, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda para el valor de $aM centrado
// Etiqueta "NOMBRE(S)"
$pdf->SetXY(33 + 2 * $anchoCelda, 76); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'NOMBRE(S)', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, 81); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($nom, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda para el valor de $nom centrado

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 5; // Ancho de cada celda
// Etiqueta "LUGAR DE NACIMIENTO"
$pdf->SetXY(11, 87); // Posición X: 11, Y: 87
$pdf->Cell($anchoCelda, 6, 'LUGAR DE NACIMIENTO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, 92); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($lN, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $lN
// Etiqueta "FECHA DE NACIMIENTO" (Día, Mes, Año)
$pdf->SetXY(12 + $anchoCelda, 87); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'FECHA DE NACIMIENTO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, 92); // Debajo de la etiqueta
$pdf->Cell($anchoCelda / 3, 6, date('d', strtotime($fN)), 1, 0, 'C'); // Celda centrada para el día
$pdf->Cell($anchoCelda / 3, 6, date('m', strtotime($fN)), 1, 0, 'C'); // Celda centrada para el mes
$pdf->Cell($anchoCelda / 3, 6, date('Y', strtotime($fN)), 1, 0, 'C'); // Celda centrada para el año
// Etiqueta "EDAD"
$pdf->SetXY(24 + 2 * $anchoCelda, 87); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'EDAD', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, 92); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($ed, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $ed
// Etiqueta "CURP"
$pdf->SetXY(24 + 3 * $anchoCelda, 87); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'CURP', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 3 * $anchoCelda, 92); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($cu, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $cu
// Etiqueta "SEXO"
$pdf->SetXY(24 + 4 * $anchoCelda, 87); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'SEXO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 4 * $anchoCelda, 92); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($se, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $se

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 5; // Ancho de cada celda
// Coordenada Y de la fila anterior más el espacio entre filas
$prevPosY = 98;
// Etiqueta "DOMICILIO"
$pdf->SetXY(20, $prevPosY); // Posición X: 20, Y: $prevPosY
$pdf->Cell($anchoCelda, 6, 'DOMICILIO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($dom, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $dom
// Etiqueta "COLONIA"
$pdf->SetXY(21 + $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'COLONIA', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($col, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $col
// Etiqueta "MUNICIPIO"
$pdf->SetXY(21 + 2 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'MUNICIPIO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($mu, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $mu
// Etiqueta "ESTADO"
$pdf->SetXY(22 + 3 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'ESTADO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 3 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($es, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $es
// Etiqueta "TIPO DE SANGRE"
$pdf->SetXY(16 + 4 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'TIPO DE SANGRE', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 4 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($ts, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $ts

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 3; // Ancho de cada celda
// Coordenada Y de la fila anterior más el espacio entre filas
$prevPosY = 109;
// Etiqueta "ALERGÍAS O ENFERMEDADES"
$pdf->SetXY(20, $prevPosY); // Posición X: 20, Y: $prevPosY
$pdf->Cell($anchoCelda, 6, 'ALERGÍAS O ENFERMEDADES', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($af, 0, $anchoCelda / 2, "..."), 1, 0, 'C'); // Celda centrada para el valor de $af
// Etiqueta "TEL. CELULAR"
$pdf->SetXY(30 + $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'TEL. CELULAR', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($te, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $tel
// Etiqueta "CORREO ELECTRÓNICO"
$pdf->SetXY(23 + 2 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'CORREO ELECTRÓNICO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($em, 0, $anchoCelda / 1.5, "..."), 1, 0, 'C'); // Celda centrada para el valor de $em


// DATOS DE LA ESCUELA DE PROCEDENCIA
// Etiqueta "DATOS PERSONALES DEL ALUMNO"
$pdf->SetTextColor(109, 141, 60); // Color
$pdf->SetFont('helvetica', 'B', 13); // Fuente
$pdf->SetXY(10, 118); // Posición
$pdf->Cell(0, 10, 'DATOS DE LA ESCUELA DE PROCEDENCIA', 0, 0, 'C'); // Línea de texto
// Establece el color del texto a negro (RGB: 0, 0, 0)
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 8);

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 3; // Ancho de cada celda
// Coordenada Y de la fila anterior más el espacio entre filas
$prevPosY = 124;
// Etiqueta "ESCUELA DE PROCEDENCIA"
$pdf->SetXY(20, $prevPosY); // Posición X: 20, Y: $prevPosY
$pdf->Cell($anchoCelda, 6, 'ESCUELA DE PROCEDENCIA', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($esc, 0, $anchoCelda / 2, "..."), 1, 0, 'C'); // Celda centrada para el valor de $esc
// Etiqueta "DIRECCIÓN"
$pdf->SetXY(33 + $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'DIRECCIÓN', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($domE, 0, $anchoCelda / 2, "..."), 1, 0, 'C'); // Celda centrada para el valor de $domE
// Etiqueta "CCT"
$pdf->SetXY(36 + 2 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'CCT', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($cct, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $cct

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 4; // Ancho de cada celda
// Coordenada Y de la fila anterior más el espacio entre filas
$prevPosY = 135;
// Etiqueta "GENERACIÓN"
$pdf->SetXY(10, $prevPosY); // Posición X: 10, Y: $prevPosY
$pdf->Cell($anchoCelda, 6, 'GENERACIÓN', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($ge, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $ge
// Etiqueta "PROMEDIO"
$pdf->SetXY(10 + $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'PROMEDIO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($pr, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $pr
// Etiqueta "MUNICIPIO"
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'MUNICIPIO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($muE, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $muE
// Etiqueta "ESTADO"
$pdf->SetXY(10 + 3 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'ESTADO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 3 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($esE, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $esE


// CONTACTO DE EMERGENCIA Y/O TUTOR
// Etiqueta "CONTACTO DE EMERGENCIA Y/O TUTOR"
$pdf->SetTextColor(109, 141, 60); // Color
$pdf->SetFont('helvetica', 'B', 13); // Fuente
$pdf->SetXY(10, 144); // Posición
$pdf->Cell(0, 10, 'CONTACTO DE EMERGENCIA Y/O TUTOR', 0, 0, 'C'); // Línea de texto
// Establece el color del texto a negro (RGB: 0, 0, 0)
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 8);

$anchoTotal = 210 - 20; // Ancho total disponible para las celdas
$anchoCelda = $anchoTotal / 5; // Ancho de cada celda
// Coordenada Y de la fila anterior más el espacio entre filas
$prevPosY = 150;
// Etiqueta "CURP"
$pdf->SetXY(20, $prevPosY); // Posición X: 20, Y: $prevPosY
$pdf->Cell($anchoCelda, 6, 'CURP', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($cuT, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $cu
// Etiqueta "NOMBRE COMPLETO"
$pdf->SetXY(21 + $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'NOMBRE COMPLETO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($nomT, 0, $anchoCelda / 2, "..."), 1, 0, 'C'); // Celda centrada para el valor de $nomT
// Etiqueta "PARENTESCO"
$pdf->SetXY(21 + 2 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'PARENTESCO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 2 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($pa, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $pa
// Etiqueta "CELULAR"
$pdf->SetXY(22 + 3 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'CELULAR', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 3 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($telT, 0, $anchoCelda, "..."), 1, 0, 'C'); // Celda centrada para el valor de $telT
// Etiqueta "DOMICILIO"
$pdf->SetXY(16 + 4 * $anchoCelda, $prevPosY); // Posición X ajustada para la siguiente celda
$pdf->Cell($anchoCelda, 6, 'DOMICILIO', 0, 0, 'L'); // Texto de la etiqueta
$pdf->SetXY(10 + 4 * $anchoCelda, $prevPosY + 5); // Debajo de la etiqueta
$pdf->Cell($anchoCelda, 6, mb_strimwidth($domT, 0, $anchoCelda / 2, "..."), 1, 0, 'C'); // Celda centrada para el valor de $domT


// LADO IZQUIERDO ->>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><<<
// Obtener los documentos para el nivel educativo seleccionado
$filas = $documentosRequeridosPorNivel[$cN];
// Tabla DOCUMENTOS QUE PRESENTA EL ASPIRANTE
$pdf->SetFillColor(109, 141, 60); // Color de fondo de la cabecera
$pdf->SetTextColor(255, 255, 255); // Color del texto de la cabecera
$pdf->SetFont('helvetica', 'B', 7); // Fuente
$pdf->SetXY(10, 165); // Posición
$anchoColumna = array(55, 15, 15, 15); // Ancho de cada columna
$anchoTotal = array_sum($anchoColumna); // Ancho total de la tabla
$pdf->Cell($anchoTotal, 4, 'DOCUMENTOS QUE PRESENTA EL ASPIRANTE', 1, 1, 'C', true);
// Encabezados de la tabla
$encabezados = array('DOCUMENTOS', 'ORIGINAL', 'COPIAS', 'PENDIENTE');
foreach ($encabezados as $index => $encabezado) {
    $pdf->Cell($anchoColumna[$index], 4, $encabezado, 1, 0, 'C', true);
}
$pdf->Ln();
// Configuración de color y fuente para el contenido de la tabla
$pdf->SetTextColor(0, 0, 0); // Color del texto de la tabla
$pdf->SetFont('helvetica', '', 8); // Fuente del texto de la tabla
// Imprimir las filas de la tabla
foreach ($filas as $fila) {
    $pdf->Cell($anchoColumna[0], 4, $fila, 1, 0, 'L');
    $pdf->Cell($anchoColumna[1], 4, '', 1, 0, 'C');
    $pdf->Cell($anchoColumna[2], 4, '', 1, 0, 'C');
    $pdf->Cell($anchoColumna[3], 4, '', 1, 0, 'C');
    $pdf->Ln();
}
// Etiqueta "NOMBRE Y FIRMA DEL ASESOR EDUCATIVO"
$pdf->SetFont('helvetica', '', 7); // Fuente
$pdf->SetXY(35, 230); // Posición de la línea
$pdf->Cell($anchoTotal / 2, 10, '___________________________________', 0, 0, 'C', false);
$pdf->SetXY(35, 236); // Posición de la etiqueta
$pdf->Cell($anchoTotal / 2, 4, 'NOMBRE Y FIRMA DEL ASESOR EDUCATIVO', 0, 0, 'C', false);
// Tabla BECA POR INSCRIPCIÓN
$pdf->SetFillColor(109, 141, 60); // Color de fondo de la cabecera
$pdf->SetTextColor(255, 255, 255); // Color del texto de la cabecera
$pdf->SetFont('helvetica', 'B', 8); // Fuente
$pdf->SetXY(10, 242); // Posición
$anchoColumna = array(50, 50); // Ancho de cada columna
$anchoTotal = array_sum($anchoColumna); // Ancho total de la tabla
$pdf->Cell($anchoTotal, 4, 'BECA POR INSCRIPCIÓN', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0); // Color del texto de la tabla
$pdf->SetFont('helvetica', '', 8); // Fuente del texto de la tabla
$filasCostos = array(
    array('INSCRIPCIÓN', $coIns),
    array('COLEGIATURA MENSUAL', $coCol),
    array('REINSCRIPCIÓN', $coRei)
);
foreach ($filasCostos as $fila) {
    $pdf->Cell($anchoColumna[0], 4, $fila[0], 1, 0, 'C');
    $pdf->Cell($anchoColumna[1], 4, '$' . number_format($fila[1], 2), 1, 0, 'C');
    $pdf->Ln();
}
// Etiqueta "BUENO POR"
$pdf->SetFont('helvetica', '', 7); // Fuente del texto
$pdf->SetXY(95, 245); // Posición de la etiqueta
$pdf->Cell($anchoTotal / 2, 4, 'BUENO POR:', 0, 0, 'C', false);


// LADO DERECHO ->>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// Cuadro COMPROMISO EN ENTREGA DE DOCUMENTOS
$pdf->SetFillColor(109, 141, 60); // Color de fondo del encabezado
$pdf->SetTextColor(255, 255, 255); // Color del texto del encabezado
$pdf->SetFont('helvetica', 'B', 8); // Fuente del encabecado
$pdf->SetXY(125, 165); // Posición
$anchoTotal = 75; // Ancho
$pdf->Cell($anchoTotal, 4, 'COMPROMISO EN ENTREGA DE DOCUMENTOS', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0); // Color del fondo del texto
$pdf->SetFont('helvetica', '', 7); // Fuente del text
$pdf->SetXY(125, 169); // Posición
$pdf->Rect(125, 169, $anchoTotal, 60); // Borde
$textoCompromiso = "ME COMPROMETO A ENTREGAR LOS DOCUMENTOS FALTANTES DE MI INSCRIPCIÓN ANTES DEL ________ DE _____________________ DEL AÑO 20____.

EN CASO DE OMISO, ESTOY CONSCIENTE DE QUE AUTOMÁTICAMENTE SE CANCELARÁ MI INSCRIPCIÓN Y NO PONDRÉ OBJECCIÓN ALGUNA, YA QUE LO ANTERIOR ES REQUISITO INDISPENSABLE PARA PRESENTAR MI ALTA ANTE LA SECRETARÍA DE EDUCACIÓN, POR LO TANTO, FIRMO DE CONFORMIDAD Y HAGO CONSTAR QUE TODO LO EXPUESTO AQUÍ ES VERDAD.";
$pdf->MultiCell($anchoTotal, 60, $textoCompromiso, 0, 'J', false); // Se imprime el texto
// Etiqueta NOMBRE Y FIRMA
$pdf->SetXY(132, 219); // Posicón de la línea
$pdf->Cell($anchoTotal / 2, 10, '___________________________________', 0, 0, 'C', false);
$pdf->SetXY(132, 225); // Posición de la etiqueta
$pdf->Cell($anchoTotal / 2, 4, 'NOMBRE Y FIRMA', 0, 0, 'C', false);
// Cuadro para la huella
$x = 179; // Posición X
$y = 205; // Posición Y
$w = 20; // Ancho del cuadro en mm
$h = 20; // Alto del cuadro en mm
$pdf->RoundedRect($x, $y, $w, $h, 1, '1111'); // Cuadro para la huella
// Etiqueta "HUELLA"
$pdf->SetXY(170, 225);
$pdf->Cell($anchoTotal / 2, 4, 'HUELLA', 0, 1, 'C', false);
// Etiqueta Compromiso
$pdf->SetXY(125, 230); // Posición
$textoCompromiso = "LO SUSCRITO SE COMPROMETE A CUMPLIR CON LAS NORMAS Y DISPOSICIONES DICTADAS POR LAS AUTORIDADES DE LA ESCUELA, ASÍ COMO APOYAR A LAS MISMAS PARA EL MÁXIMO APROVECHAMIENTO ESCOLAR";
$pdf->MultiCell($anchoTotal, 0, $textoCompromiso, 0, 'J', false);
// Imagen QR
$qr = '/xampp/htdocs/SIGEAUIO/img/qr.png'; // Ruta logo tabasco
$pdf->Image($qr, 170, 244, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Logo Olimpo


// Información de contacto en la parte inferior del PDF
$pdf->SetTextColor(139, 69, 19); // Color
$pdf->SetFont('pdfacourier', 'B', 10); // Fuente
// Icono y Etiqueta Ubicación
$pdf->Image('/xampp/htdocs/SIGEAUIO/img/ubicacion.png', 22, 260, 5, 5, 'PNG');
$pdf->SetXY(0, 260);
$pdf->Cell(0, 0, 'Calle 5 de Febrero, Col. Centro, 86726, V. Benito Juárez, Mac., Tab.', 0, 1, 'C');
// Icono y Etiqueta Correo Electrónico
$pdf->Image('/xampp/htdocs/SIGEAUIO/img/gmail.png', 45, 265, 5, 5, 'PNG');
$pdf->SetXY(0, 265);
$pdf->Cell(0, 5, 'contacto@uniolimpo.com            936-121-4379', 0, 1, 'C');
// Icono y Etiqueta Teléfono
$pdf->Image('/xampp/htdocs/SIGEAUIO/img/telefono.png', 111, 265, 5, 5, 'PNG');
// Icono y Etiqueta WhatsApp
$pdf->Image('/xampp/htdocs/SIGEAUIO/img/whatsapp.png', 117, 265, 5, 5, 'PNG');


// Salida del PDF
$pdf->Output('Ficha de Inscripción: ' . $fo . '.pdf', 'I');
