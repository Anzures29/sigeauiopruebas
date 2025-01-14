<?php
require '../../PhpSpreadsheet/vendor/autoload.php'; // Incluye la biblioteca PhpSpreadsheet.
require_once('../../conexion.php'); // Incluye el archivo de conexión a la base de datos.
date_default_timezone_set('Ciudad de México'); // Configura la zona horaria a Ciudad de México.
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Importa la clase principal para trabajar con hojas de cálculo.
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Importa la clase para escribir archivos en formato XLSX.
use PhpOffice\PhpSpreadsheet\Style\Color; // Importa la clase para manejar colores en estilos.
use PhpOffice\PhpSpreadsheet\Style\Fill; // Importa la clase para manejar rellenos en estilos.
use PhpOffice\PhpSpreadsheet\Style\Border; // Importa la clase para manejar bordes en estilos.
ob_clean(); // Limpia el búfer de salida para evitar conflictos al descargar el archivo Excel.
// Obtiene el mes seleccionado y el texto del buscador desde la URL.
$mes = $_GET['mes'] ?? date('Y-m'); // Mes seleccionado
$buscar = $_GET['buscar'] ?? ''; // Texto del buscador
$fechaInicio = $mes . '-01'; // Primer día del mes
$fechaFin = date("Y-m-t", strtotime($fechaInicio)); // Último día del mes
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
// Crea una nueva hoja de cálculo.
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(); // Obtiene la hoja activa.
$sheet->setTitle('Reporte de Pagos'); // Establece el título de la hoja.
// Configura el título del reporte.
$sheet->mergeCells('B1:J1'); // Fusiona las celdas B1 a J1.
$sheet->setCellValue('B2', "Reporte de Pagos del mes $mes"); // Establece el texto del título.
$sheet->mergeCells('B2:J3'); // Fusiona las celdas B2 a J3.
$sheet->getStyle('B2:J3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Borde exterior grueso
$sheet->getStyle('B2')->getFont()->setBold(true)->setSize(20); // Aplica estilo de fuente al título.
$sheet->getRowDimension('2')->setRowHeight(15); // Ajusta la altura de la fila 2.
$sheet->getRowDimension('3')->setRowHeight(15); // Ajusta la altura de la fila 3.
$sheet->getStyle('B2')->getAlignment()->setHorizontal('center'); // Centra horizontalmente el título.
$sheet->getStyle('B2')->getAlignment()->setVertical('center'); // Centra verticalmente el título.
// Define las cabeceras de la tabla.
$cabeceras = ['Folio', 'Fecha', 'Alumno', 'Tipo Pago', 'Descripción', 'Cantidad', 'Importe', 'Total', 'Forma Pago'];
$colIndex = 'B'; // Inicia en la columna B.
foreach ($cabeceras as $cabecera) {
    $sheet->setCellValue($colIndex . '4', $cabecera); // Establece el valor de cada cabecera.
    $colIndex++;
}
// Aplica estilos a la cabecera.
$sheet->getStyle('B4:J4')->applyFromArray([
    'font' => [
        'bold' => true, // Negrita.
        'color' => ['argb' => Color::COLOR_WHITE], // Color de texto blanco.
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID, // Relleno sólido.
        'startColor' => ['argb' => 'FFBB8F16'], // Color de fondo.
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Texto centrado.
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN, // Borde delgado.
            'color' => ['argb' => 'FF000000'], // Color del borde.
        ],
    ],
]);
// Agregar filtros automáticos a las columnas
$sheet->setAutoFilter('B4:J4'); // Aplica filtros a las columnas B a J en la fila 4
// Agrega los datos a la tabla.
$fila = 5; // Comienza en la fila 5, debajo de la cabecera.
while ($dataRow = $res->fetch_assoc()) {
    $sheet->setCellValue('B' . $fila, $dataRow['fo']); // Folio.
    $sheet->setCellValue('C' . $fila, date('d-m-Y', strtotime($dataRow['fe']))); // Fecha.
    $sheet->setCellValue('D' . $fila, $dataRow['alumno']); // Nombre del alumno.
    $sheet->setCellValue('E' . $fila, $dataRow['tipo']); // Tipo de pago.
    $sheet->setCellValue('F' . $fila, $dataRow['de']); // Descripción.
    $sheet->setCellValue('G' . $fila, $dataRow['ca']); // Cantidad.
    $sheet->setCellValue('H' . $fila, $dataRow['im']); // Importe.
    $sheet->setCellValue('I' . $fila, $dataRow['tot']); // Total.
    $sheet->setCellValue('J' . $fila, $dataRow['forma']); // Forma de pago.
    $fila++;
}
// Borde sencillo para las columnas
foreach (range('B', 'J') as $columna) {
    $sheet->getStyle($columna . '4:' . $columna . ($fila - 1))->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle($columna . '4:' . $columna . ($fila - 1))->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
}
// Borde exterior grueso para la cabecera
$sheet->getStyle('B4:J4')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
// Borde exterior grueso para toda la tabla
$sheet->getStyle('B4:J' . ($fila - 1))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
// Ajusta el ancho de las columnas automáticamente.
foreach (range('B', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Centrar las columnas específicas por defecto
$centrarColumnas = ['B', 'C', 'E', 'G', 'J']; // Folio, Fecha, Tipo de Pago, Cantidad, Forma de Pago
foreach ($centrarColumnas as $columna) {
    $sheet->getStyle($columna . '5:' . $columna . ($fila - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}
// Aplicar formato de contabilidad a las columnas Importe y Total
$formatoContableColumnas = ['H', 'I']; // Importe, Total
foreach ($formatoContableColumnas as $columna) {
    $sheet->getStyle($columna . '5:' . $columna . $fila)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
}
// Colocar "Total" debajo de la columna Importe (columna H)
$sheet->setCellValue('H' . $fila, 'Total'); // Coloca "Total" en la siguiente fila de Importe
$sheet->getStyle('H' . $fila)->getFont()->setBold(true); // Aplica negritas
$sheet->getStyle('H' . $fila)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Aplicar borde grueso
// Colocar el total de la columna Total (columna I)
if ($fila > 5) {
    // Si hay registros, agrega la fórmula para sumar los totales.
    $sheet->setCellValue('I' . $fila, '=SUM(I5:I' . ($fila - 1) . ')'); // Suma los valores en la columna Total
    $sheet->getStyle('I' . $fila)->getFont()->setBold(true); // Aplica negritas
    $sheet->getStyle('I' . $fila)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Aplicar borde grueso
} else {
    // Si no hay registros, escribe "Sin datos" o deja la celda vacía.
    $sheet->setCellValue('H5', 'Sin datos'); // Coloca "Sin datos" en la celda de la primera fila de registros.
    $sheet->mergeCells('H5:I5'); // Fusiona las celdas de "Importe" y "Total" para el mensaje.
    $sheet->getStyle('H5')->getFont()->setBold(true); // Aplica negritas.
    $sheet->getStyle('H5:I5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Aplicar borde grueso al mensaje.
}
// Configura el archivo para descarga.
$writer = new Xlsx($spreadsheet); // Crea un escritor de archivos XLSX.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // Encabezado para tipo de contenido.
header('Content-Disposition: attachment;filename="reporte_pagos.xlsx"'); // Nombre del archivo para descarga.
header('Cache-Control: max-age=0'); // Desactiva el caché.
$writer->save('php://output'); // Guarda el archivo en la salida estándar.
exit(); // Finaliza el script.