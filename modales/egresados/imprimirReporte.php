<?php
// Incluir la biblioteca TCPDF
require_once('../../tcpdf/tcpdf.php');
include_once('../../conexion.php');
// Capturar el filtro de búsqueda desde GET
$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
// Crear la consulta SQL base
$sql = "SELECT e.fol, e.nC, CONCAT(e.nom, ' ', e.aP, ' ', e.aM) AS Alumno, n.ni, COALESCE(c.ca, '') AS carrera, 
        DATE_FORMAT(e.feIng, '%d-%m-%Y') AS feIng,
        DATE_FORMAT(e.feEgr, '%d-%m-%Y') AS feEgr, 
            COALESCE(e.pr, '') AS promedio
        FROM egresados e 
        INNER JOIN niveles n ON e.cN = n.cv 
        LEFT JOIN carreras c ON e.cC = c.cv 
        WHERE 1=1";
// Si hay texto en el campo de búsqueda, ajustar la consulta
if (!empty($buscar)) {
    $buscar = $conn->real_escape_string($buscar);
    $sql .= " AND (
        e.fol LIKE '%$buscar%' OR
        e.nC LIKE '%$buscar%' OR
        n.ni LIKE '%$buscar%' OR
        COALESCE(c.ca, ' ') LIKE '%$buscar%' OR
        e.feIng LIKE '%$buscar%' OR
        e.feEgr LIKE '%$buscar%'
    )";
}
$sql .= " ORDER BY e.fol ASC";
// Ejecutar la consulta
$resultado = $conn->query($sql);
class MYPDF extends TCPDF
{
    public function Header()
    {
        // Imagen, título y datos generales del pago
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        // Ruta del logo (utilizando DOCUMENT_ROOT y evitando la barra invertida al final)
        $img_file = $_SERVER['DOCUMENT_ROOT'] . '/SIGEAUIO/img/logo.png';
        // Verificación de existencia de la imagen
        if (!file_exists($img_file)) {
            die("Error: La imagen no existe en " . $img_file);
        }
        // Posicionar la imagen dentro del rectángulo
        $this->Image($img_file, 6, 0, 70, 70, '', '', '', false, 300, '', false, false, 0);
        // Restaurar la configuración de AutoPageBreak
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }
}
// Crear una nueva instancia de TCPDF
$pdf = new TCPDF();
// Configurar el documento PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Alumnos Egresados');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(10, 30, 10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);
// Agregar una página
$pdf->AddPage();
// Colocar logo en el encabezado
$logoPath = $_SERVER['DOCUMENT_ROOT'] . '/xampp/htdocs/SIGEAUIO/img/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 0, 0, 40, 40, '', '', '', false, 300, '', false, false, 0);
}
$pdf->Ln(5);
// Título del reporte
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 0, 'Reporte de Alumnos Egresados', 0, 1, 'C');
// Espacio
$pdf->Ln(5);
// Crear la tabla
$html = '<style>
            table { 
                border-collapse: collapse; 
                width: 200mm; /* Ancho ajustado para que termine a 5px del borde derecho */
            }
            th { 
                background-color: rgb(187, 143, 22); 
                color: white; 
                font-size: 8px; 
                padding: 8px; 
                text-align: center; /* Centra el texto en los encabezados */
            }
            td { 
                font-size: 8px; 
                padding: 0px; 
                text-align: left; /* Centra el texto en las celdas */
            }
            tr:nth-child(even) { 
                background-color: #f2f2f2; 
            }
        </style>';
// Ajustar la posición en el PDF
$pdf->SetX(5); // Establece un margen izquierdo de 5 píxeles
$html .= '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Folio Inscripción</th>
                    <th>Número de Control</th>
                    <th>Nivel</th>
                    <th>Carrera</th>
                    <th>Fecha de Ingreso</th>
                    <th>Fecha de Egreso</th>
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>';
// Agregar las filas de la tabla
while ($fila = $resultado->fetch_assoc()) {
    $html .= '<tr>
                <td>' . $fila['fol'] . '</td>
                <td>' . $fila['nC'] . '</td>
                <td>' . $fila['ni'] . '</td>
                <td>' . $fila['carrera'] . '</td>
                <td>' . $fila['feIng'] . '</td>
                <td>' . $fila['feEgr'] . '</td>
                <td>' . $fila['promedio'] . '</td>
              </tr>';
}
$html .= '</tbody></table>';
// Escribir el contenido HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');
// Salida del PDF
$pdf->Output('reporte_alumnos_inscritos.pdf', 'I');
