<?php
require_once('../../tcpdf/tcpdf.php');
require_once('../../conexion.php'); // Llamando a la conexión para BD
date_default_timezone_set('Ciudad de México');
ob_end_clean(); // Limpiar la memoria y prevenir problemas con cabeceras
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("Error: Los datos no se recibieron en modo GET.");
}
// Validar y procesar los datos recibidos del formulario
$fo = $_GET['fo']; // Folio de pago
$fe = $_GET['fe']; // Fecha de pago
$cN = $_GET['nivel']; // Clave del nivel
$cC = isset($_GET['carrera']) ? trim($_GET['carrera']) : null; // Clave de la carrera
$nC = $_GET['nC']; // Número de control
$cT = $_GET['tipoPago']; // Clave tipo de pago
$de = $_GET['de']; // Descripción
$ca = $_GET['ca']; // Cantidad
$im = $_GET['im']; // Importe
$tot = $_GET['tot']; // Total
$cF = $_GET['formaPago']; // Clave forma de pago
if (empty($fo) || empty($fe) || empty($nC) || $cT === null || empty($de) || $ca === null || $im === null || $tot === null || empty($cF)) {
    die("Error: Datos incompletos enviados al servidor.");
}
try {
    // Obtener el nivel educativo del alumno
    $stmtNivel = $conn->prepare("SELECT ni FROM niveles WHERE cv = ?");
    $stmtNivel->bind_param('s', $cN);
    $stmtNivel->execute();
    $resultNivel = $stmtNivel->get_result();
    $nivel = $resultNivel->fetch_assoc()['ni'] ?? null;
    if (!$nivel) {
        die("Error: No se encontró información del alumno en la base de datos.");
    }
    $stmtNivel->close();
    // Obtener la carrera del alumno
    if ($cC !== null) {
        $stmtCarrera = $conn->prepare("SELECT ca FROM carreras WHERE cv = ?");
        $stmtCarrera->bind_param('s', $cC);
        $stmtCarrera->execute();
        $resultCarrera = $stmtCarrera->get_result();
        $carrera = $resultCarrera->fetch_assoc()['ca'] ?? null;
        if (!$carrera) {
            die("Error: No se encontró información del alumno en la base de datos.");
        }
        $stmtCarrera->close();
    } else {
        $carrera = "";
    }
    // Obtener nombre del alumno
    $stmtAlumno = $conn->prepare("SELECT CONCAT(nom, ' ', aP, ' ', aM) AS alumno FROM alumnos WHERE nC = ?");
    $stmtAlumno->bind_param('s', $nC);
    $stmtAlumno->execute();
    $resultAlumno = $stmtAlumno->get_result();
    $alumno = $resultAlumno->fetch_assoc()['alumno'] ?? null;
    if (!$alumno) {
        die("Error: No se encontró información del alumno en la base de datos.");
    }
    $stmtAlumno->close();
    // Obtener el tipo de pago
    $stmtTipo = $conn->prepare("SELECT tipo FROM tipopago WHERE cv = ?");
    $stmtTipo->bind_param('i', $cT);
    $stmtTipo->execute();
    $resultTipo = $stmtTipo->get_result();
    $tiPa = $resultTipo->fetch_assoc()['tipo'] ?? null;
    if (!$tiPa) {
        die("Error: No se encontró el tipo de pago en la base de datos.");
    }
    $stmtTipo->close();
    // Obtener la forma de pago
    $stmtForma = $conn->prepare("SELECT forma FROM formapago WHERE cv = ?");
    $stmtForma->bind_param('s', $cF);
    $stmtForma->execute();
    $resultForma = $stmtForma->get_result();
    $foPa = $resultForma->fetch_assoc()['forma'] ?? null;
    if (!$foPa) {
        die("Error: No se encontró la forma de pago en la base de datos.");
    }
    $stmtForma->close();
} catch (Exception $e) {
    die("Error inesperado al validar datos: " . $e->getMessage());
}
try {
    class MYPDF extends TCPDF
    {
        public function Header()
        {
            // Imagen, título y datos generales del pago
            $bMargin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);
            $img_file = $_SERVER['DOCUMENT_ROOT'] . '\xampp\htdocs\SIGEAUIO\img\logo.png';
            if (!file_exists($img_file)) {
                die("Error: La imagen no existe en " . $img_file);
            }
            $this->SetFont('helvetica', 'B', 20);
            $this->SetY(8); // Ajusta la posición vertical a justo arriba de la imagen
            $this->SetX(30);
            $this->Cell(0, 10, 'UNIVERSIDAD INTERCONTINENTAL OLIMPO S. C.', 0, 1, 'C');
            // Colocar los textos en tres líneas, lado a lado con posiciones absolutas
            $this->SetFont('helvetica', '', 8);
            $this->SetY(18); // Ajusta la posición vertical para los textos
            $this->SetX(60); // Ajusta la posición horizontal para que no quede detrás de la imagen
            $this->Cell(0, 3, 'C. 5 de febrero, Col. Centro, 86726', 0, 1, 'L');
            $this->SetX(60);
            $this->Cell(0, 3, 'Villa Benito Juárez, Macuspana, Tabasco.', 0, 1, 'L');
            $this->SetX(60);
            $this->Cell(0, 3, 'Tel. Cel. 936 - 121 - 4379', 0, 1, 'L');
            $this->SetY(18); // Ajusta la posición vertical para los textos a la derecha
            $this->SetX(130); // Ajusta la posición horizontal para los textos a la derecha
            $this->Cell(0, 3, 'RFC: UIO2312052N7', 0, 1, '');
            $this->SetX(130);
            $this->Cell(0, 3, 'Correo: finanzas@uniolimpo.com', 0, 1, '');
            $this->SetX(130);
            $this->Cell(0, 3, 'www.uniolimpo.com', 0, 1, '');
            // Dibujar el rectángulo con esquinas redondeadas
            $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 0, 0)));
            $this->RoundedRect(10, 8, 190, 55, 3.5, '1111', 'D');
            // Posicionar la imagen dentro del rectángulo
            $this->Image($img_file, 11, 15, 48, 48, '', '', '', false, 300, '', false, false, 0);
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
        }
    }
    $pdf = new MYPDF('P', 'mm', 'Letter', true, 'UTF-8', false, "");
    $pdf->SetMargins(10, 35, 10);
    $pdf->SetHeaderMargin(20);
    $pdf->setPrintFooter(false);
    $pdf->setPrintHeader(true);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $pdf->SetTitle('FICHA DE PAGO');
    $pdf->AddPage();

    // Primer recibo
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 10);
    function addLabeledCellFol($pdf, $label, $value, $x, $y)
    {
        $pdf->SetXY($x, $y);
        $labelWidth = $pdf->GetStringWidth($label) + 2;
        $valueWidth = $pdf->GetStringWidth($value) + 2;
        $pdf->Cell($labelWidth, 6, $label, 0, 0, 'L');
        $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), $valueWidth, 6, 1, '1111');
        $pdf->Cell($valueWidth, 6, $value, 0, 1, 'C');
    }
    function addLabeledCell($pdf, $label, $value, $x, $y, $maxWidth)
    {
        $pdf->SetXY($x, $y);
        $labelWidth = $pdf->GetStringWidth($label) + 2;
        $valueWidth = $maxWidth - $labelWidth - 2;
        $pdf->Cell($labelWidth, 6, $label, 0, 0, 'L');
        $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), $valueWidth, 6, 1, '1111');
        $pdf->Cell($valueWidth, 6, $value, 0, 1, 'C');
    }
    $maxWidth = 190 - 38 - 10;
    addLabeledCellFol($pdf, 'Folio de Pago:', $fo, 60, 36);
    $pdf->SetXY(138, 36);
    $pdf->Cell(27, 6, 'Fecha de Pago:', 0, 0, 'L');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111');
    $pdf->Cell(10, 6, date('d', strtotime($fe)), 0, 0, 'C');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111');
    $pdf->Cell(10, 6, date('m', strtotime($fe)), 0, 0, 'C');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 15, 6, 1, '1111');
    $pdf->Cell(15, 6, date('Y', strtotime($fe)), 0, 1, 'C');
    addLabeledCell($pdf, 'Nivel:    ', $nivel, 60, 42, $maxWidth);
    addLabeledCell($pdf, 'Carrera:', $carrera, 60, 48, $maxWidth);
    addLabeledCell($pdf, 'Alumno:', $alumno, 60, 54, $maxWidth);
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(232, 232, 232);
    $column_widths = [20, 25, 60, 20, 20, 25];
    $total_width = array_sum($column_widths);
    $scaling_factor = 190 / $total_width;
    $adjusted_widths = array_map(function ($width) use ($scaling_factor) {
        return $width * $scaling_factor;
    }, $column_widths);
    $pdf->Cell($adjusted_widths[0], 6, 'Cantidad', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[1], 6, 'Tipo Pago', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[2], 6, 'Descripción', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[3], 6, 'Importe', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[4], 6, 'Total', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[5], 6, 'Forma Pago', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($adjusted_widths[0], 6, $ca, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[1], 6, $tiPa, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[2], 6, $de, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[3], 6, $im, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[4], 6, $tot, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[5], 6, $foPa, 1, 1, 'C');
    $pdf->Ln(2);
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetX(8);
    $pdf->SetY(80);
    $pdf->Cell(0, 0, 'Pago realizado a nombre de Universidad Intercontinental Olimpo S. C.', 0, 0, 'L');
    $pdf->SetX(8);
    $pdf->SetY(83);
    $pdf->Cell(0, 0, 'Cualquier duda o comentario comunicarse al correo y al teléfono antes mencionado', 0, 0, 'L');
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetXY(144, 80);
    $pdf->Cell(26, 6, 'Total a Pagar $', 0, 0, 'L');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 30, 6, 1, '1111');
    $pdf->Cell(30, 6, $tot, 0, 1, 'C');

    // SEGUNDO RECIBO A MITAD DE LA HOJA (140)
    // Se declara la ruta de la imagen
    $img_file = $_SERVER['DOCUMENT_ROOT'] . '\xampp\htdocs\SIGEAUIO\img\logo.png';
    if (!file_exists($img_file)) {
        die("Error: La imagen no existe en " . $img_file);
    }

    $pdf->SetY(140); // Mitad de la pagina
    // Añadir cuadro
    $pdf->RoundedRect(10, 140, 190, 54, 3.5, '1111', 'D');
    // Añadir título
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetXY(30, 140);
    $pdf->Cell(0, 10, 'UNIVERSIDAD INTERCONTINENTAL OLIMPO S. C.', 0, 1, 'C');
    // Añadir imagen
    $pdf->Image($img_file, 11, 147, 48, 48, '', '', '', false, 300, '', false, false, 0);
    // Añadir información de contacto
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetY(150); // Ajusta la posición vertical para los textos
    $pdf->SetX(60); // Ajusta la posición horizontal para que no quede detrás de la imagen
    $pdf->Cell(0, 3, 'C. 5 de febrero, Col. Centro, 86726', 0, 1, 'L');
    $pdf->SetX(60);
    $pdf->Cell(0, 3, 'Villa Benito Juárez, Macuspana, Tabasco.', 0, 1, 'L');
    $pdf->SetX(60);
    $pdf->Cell(0, 3, 'Tel. Cel. 936 - 121 - 4379', 0, 1, 'L');
    $pdf->SetY(150); // Ajusta la posición vertical para los textos a la derecha
    $pdf->SetX(130); // Ajusta la posición horizontal para los textos a la derecha
    $pdf->Cell(0, 3, 'RFC: UIO2312052N7', 0, 1, '');
    $pdf->SetX(130);
    $pdf->Cell(0, 3, 'Correo: finanzas@uniolimpo.com', 0, 1, '');
    $pdf->SetX(130);
    $pdf->Cell(0, 3, 'www.uniolimpo.com', 0, 1, '');
    // Añadir información del alumno
    $pdf->SetFont('helvetica', '', 10);
    addLabeledCellFol($pdf, 'Folio de Pago:', $fo, 60, 167);
    $pdf->SetXY(138, 167);
    $pdf->Cell(27, 6, 'Fecha de Pago:', 0, 0, 'L');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111');
    $pdf->Cell(10, 6, date('d', strtotime($fe)), 0, 0, 'C');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 10, 6, 1, '1111');
    $pdf->Cell(10, 6, date('m', strtotime($fe)), 0, 0, 'C');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 15, 6, 1, '1111');
    $pdf->Cell(15, 6, date('Y', strtotime($fe)), 0, 1, 'C');
    addLabeledCell($pdf, 'Nivel:    ', $nivel, 60, 173, $maxWidth);
    addLabeledCell($pdf, 'Carrera:', $carrera, 60, 179, $maxWidth);
    addLabeledCell($pdf, 'Alumno:', $alumno, 60, 185, $maxWidth);
    $pdf->Ln(5);
    // Añadir tabla de información del pago
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Cell($adjusted_widths[0], 6, 'Cantidad', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[1], 6, 'Tipo Pago', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[2], 6, 'Descripción', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[3], 6, 'Importe', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[4], 6, 'Total', 1, 0, 'C', 1);
    $pdf->Cell($adjusted_widths[5], 6, 'Forma Pago', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($adjusted_widths[0], 6, $ca, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[1], 6, $tiPa, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[2], 6, $de, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[3], 6, $im, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[4], 6, $tot, 1, 0, 'C');
    $pdf->Cell($adjusted_widths[5], 6, $foPa, 1, 1, 'C');
    $pdf->Ln(2);
    // Añadir información final, debajo de la tabla
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetX(8);
    $pdf->SetY(211); // Ajusta la posición vertical para bajar las etiquetas un poquito más
    $pdf->Cell(0, 0, 'Pago realizado a nombre de Universidad Intercontinental Olimpo S. C.', 0, 0, 'L');
    $pdf->SetX(8);
    $pdf->SetY(214); // Ajusta la posición vertical para bajar las etiquetas un poquito más
    $pdf->Cell(0, 0, 'Cualquier duda o comentario comunicarse al correo y al teléfono antes mencionado', 0, 0, 'L');
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetXY(144, 211);
    $pdf->Cell(26, 6, 'Total a Pagar $', 0, 0, 'L');
    $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), 30, 6, 1, '1111');
    $pdf->Cell(30, 6, $tot, 0, 1, 'C');


    // Salida del PDF
    $pdf->Output('Resumen_Pago_' . date('d_m_y') . '.pdf', 'I');
} catch (Exception $e) {
    die("Error inesperado al generar el PDF: " . $e->getMessage());
}
