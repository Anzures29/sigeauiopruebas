<?php
require_once('../../tcpdf/tcpdf.php');
require_once('../../conexion.php'); // Llamando a la conexión para BD
date_default_timezone_set('Ciudad de México');
ob_end_clean(); // Limpiar la memoria
// Capturar parámetros
$mes = $_GET['mes'] ?? date('Y-m'); // Mes seleccionado
$buscar = $_GET['buscar'] ?? ''; // Texto del buscador
$fechaInicio = $mes . '-01';
$fechaFin = date("Y-m-t", strtotime($fechaInicio));
// Construcción del query con filtro opcional para buscar
$query = "SELECT pagos.fo, pagos.fe, 
                 CONCAT(alumnos.nom, ' ', alumnos.aP, ' ', alumnos.aM) AS alumno, 
                 tipopago.tipo, pagos.de, pagos.ca, pagos.im, pagos.tot, formapago.forma
          FROM pagos
          INNER JOIN alumnos ON pagos.nC = alumnos.nC
          INNER JOIN tipopago ON pagos.cT = tipopago.cv
          INNER JOIN formapago ON pagos.cF = formapago.cv
          WHERE (pagos.fe BETWEEN ? AND ?)";
if (!empty($buscar)) {
    if ($buscar === "inscripcion") {
        // Filtrar registros de inscripción
        $query .= " AND tipopago.tipo = 'inscripción'";
    } elseif ($buscar === "reinscripcion") {
        // Filtrar registros de reinscripción
        $query .= " AND tipopago.tipo = 'reinscripción'";
    } else {
        // Búsqueda general
        $query .= " AND (pagos.fo LIKE ? OR 
                         alumnos.nom LIKE ? OR 
                         tipopago.tipo LIKE ? OR 
                         formapago.forma LIKE ? OR 
                         pagos.de LIKE ?)";
    }
}
$query .= " ORDER BY pagos.fo ASC";
$stmt = $conn->prepare($query);
// Asignar parámetros dinámicamente dependiendo si "buscar" está vacío
if (empty($buscar)) {
    $stmt->bind_param('ss', $fechaInicio, $fechaFin);
} elseif ($buscar === "inscripcion" || $buscar === "reinscripcion") {
    // Solo se necesitan las fechas para búsqueda exacta de inscripción o reinscripción
    $stmt->bind_param('ss', $fechaInicio, $fechaFin);
} else {
    // Búsqueda general
    $paramBuscar = '%' . $buscar . '%';
    $stmt->bind_param('sssssss', $fechaInicio, $fechaFin, $paramBuscar, $paramBuscar, $paramBuscar, $paramBuscar, $paramBuscar);
}
$stmt->execute();
$res = $stmt->get_result();
class MYPDF extends TCPDF
{
    private $mes; // Variable para almacenar el mes
    public function __construct($orientation, $unit, $size, $unicode, $encoding, $diskcache, $mes)
    {
        parent::__construct($orientation, $unit, $size, $unicode, $encoding, $diskcache);
        $this->mes = $mes; // Asignar el valor del mes al atributo
    }
    public function Header()
    {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        $img_file = $_SERVER['DOCUMENT_ROOT'] . '\xampp\htdocs\SIGEAUIO\img\logo.png';
        if (!file_exists($img_file)) {
            die("Error: La imagen no existe en " . $img_file);
        }
        $this->SetFont('helvetica', 'B', 20);
        $this->SetY(8);
        $this->SetX(30);
        $this->Cell(0, 0, "REPORTE DE PAGOS DEL MES: " . strtoupper($this->mes), 0, 0, 'C'); // Incluye el mes en el título
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 0, 0)));
        $this->RoundedRect(($this->getPageWidth() - 275) / 2, 8, 275, 40, 3.5, '1111', 'D');
        $this->Image($img_file, 12, 8, 40, 40, '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }
}
// Crear el PDF con la variable $mes
$pdf = new MYPDF('L', 'mm', 'Letter', true, 'UTF-8', false, $mes);
// Información del PDF
$pdf->SetTitle('Reporte de Pagos');
// Agregar la primera página
$pdf->AddPage();
// Cabecera de la tabla
$pdf->SetTextColor(255, 255, 255); // Color de texto
$pdf->SetFillColor(187, 143, 22); // Color de cabecera
$pdf->SetFont('helvetica', 'B', 10); // Tipo de letra
$pdf->SetXY(10, 50); // Posición X y Y
$pdf->Cell(20, 6, 'Folio', 1, 0, 'C', 1); // Columnas a partir de aquí
$pdf->Cell(25, 6, 'Fecha', 1, 0, 'C', 1);
$pdf->Cell(62, 6, 'Alumno', 1, 0, 'C', 1);
$pdf->Cell(25, 6, 'Tipo Pago', 1, 0, 'C', 1);
$pdf->Cell(60, 6, 'Descripción', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Cantidad', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Importe', 1, 0, 'C', 1);
$pdf->Cell(20, 6, 'Total', 1, 0, 'C', 1);
$pdf->Cell(25, 6, 'Forma Pago', 1, 1, 'C', 1);
// Datos de la tabla
$pdf->SetTextColor(0, 0, 0); // Color de Texto
$pdf->SetFont('helvetica', 'B', 8); // Tipo de letra
// Calcular el total de la columna "Total"
$totalGeneral = 0;
while ($dataRow = $res->fetch_assoc()) {
    $pdf->SetX(10);
    $pdf->Cell(20, 6, $dataRow['fo'], 1, 0, 'C');
    $pdf->Cell(25, 6, date('d-m-Y', strtotime($dataRow['fe'])), 1, 0, 'C');
    $pdf->Cell(62, 6, $dataRow['alumno'], 1, 0, 'C');
    $pdf->Cell(25, 6, $dataRow['tipo'], 1, 0, 'C');
    $pdf->Cell(60, 6, $dataRow['de'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['ca'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['im'], 1, 0, 'C');
    $pdf->Cell(20, 6, $dataRow['tot'], 1, 0, 'C');
    $pdf->Cell(25, 6, $dataRow['forma'], 1, 1, 'C');
    $totalGeneral += $dataRow['tot']; // Sumar al total general
}
// Agregar etiqueta "Total" y mostrar el cálculo
$pdf->SetX(10);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(232, 6, 'Total', 1, 0, 'R', 1); // Etiqueta "Total"
$pdf->Cell(20, 6, number_format($totalGeneral, 2), 1, 0, 'C', 1); // Total calculado
$pdf->Cell(25, 6, '', 1, 1, 'C', 1); // Celda vacía para "Forma Pago"
// Salida del PDF
$pdf->Output('Resumen_Pedido_' . date('d_m_y') . '.pdf', 'I');
