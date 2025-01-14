<?php
require_once('../../tcpdf/tcpdf.php');
require_once('../../conexion.php'); // Llamando a la conexión para BD
date_default_timezone_set('Ciudad de México');
ob_end_clean(); // Limpiar la memoria

class MYPDF extends TCPDF {
    public function Header() {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        $img_file = dirname(__FILE__) . '../../img/logo.png';
        $this->Image($img_file, 85, 8, 20, 25, '', '', '', false, 30, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }
}

// Iniciando un nuevo PDF con orientación horizontal
$pdf = new MYPDF('L', 'mm', 'Letter', true, 'UTF-8', false, "");

// Establecer márgenes del PDF
$pdf->SetMargins(10, 35, 10);
$pdf->SetHeaderMargin(20);
$pdf->setPrintFooter(false);
$pdf->setPrintHeader(true);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

// Información del PDF
$pdf->SetTitle('Reporte de Pagos');

// Agregar la primera página
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(250, 20);
$pdf->SetXY(250, 25);
$pdf->SetXY(250, 30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(15, 20);
$pdf->SetTextColor(204, 0, 0);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 25);
$pdf->Ln(35);
$pdf->Cell(40, 26, '', 0, 0, 'C');
$pdf->SetTextColor(34, 68, 136);
$pdf->SetFont('helvetica', 'B', 15);
$pdf->Cell(250, 6, 'REPORTE DE PAGOS', 0, 0, 'C');
$pdf->Ln(10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(232, 232, 232);
$pdf->SetFont('helvetica', 'B', 10);

// Cabecera de la tabla
$pdf->Cell(20, 6, 'Folio', 1, 0, 'C', 1);
$pdf->Cell(25, 6, 'Fecha', 1, 0, 'C', 1);
$pdf->Cell(40, 6, 'Alumno', 1, 0, 'C', 1);
$pdf->Cell(25, 6, 'Tipo Pago', 1, 0, 'C', 1);
$pdf->Cell(60, 6, 'Descripción', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Cantidad', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Importe', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Total', 1, 0, 'C', 1);
$pdf->Cell(25, 6, 'Forma Pago', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 8);

// SQL para consultas Empleados
$mes = $_GET['mes'] ?? date('Y-m'); // Mes seleccionado
// Definir el rango de fechas del mes seleccionado
$fechaInicio = $mes . '-01';
$fechaFin = date("Y-m-t", strtotime($fechaInicio));

// Consultas SQL para obtener los datos
$query = "SELECT pagos.fo, pagos.fe, CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno, tipopago.tipo, pagos.de,
            pagos.ca, pagos.im, pagos.tot, formapago.forma
            FROM pagos
            INNER JOIN alumnos ON pagos.nC = alumnos.nC
            INNER JOIN tipopago ON pagos.cT = tipopago.cv
            INNER JOIN formapago ON pagos.cF = formapago.cv
            WHERE (pagos.fe BETWEEN ? AND ?)
            ORDER BY pagos.fo ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$res = $stmt->get_result();

// Generar el contenido del PDF
while ($dataRow = $res->fetch_assoc()) {
    $pdf->Cell(20, 6, $dataRow['fo'], 1, 0, 'C');
    $pdf->Cell(25, 6, date('d-m-Y', strtotime($dataRow['fe'])), 1, 0, 'C');
    $pdf->Cell(40, 6, $dataRow['alumno'], 1, 0, 'C');
    $pdf->Cell(25, 6, $dataRow['tipo'], 1, 0, 'C');
    $pdf->Cell(60, 6, $dataRow['de'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['ca'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['im'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['tot'], 1, 0, 'C');
    $pdf->Cell(25, 6, $dataRow['forma'], 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('Resumen_Pedido_' . date('d_m_y') . '.pdf', 'I');
?>