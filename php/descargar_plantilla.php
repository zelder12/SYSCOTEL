<?php
require __DIR__ . '/../vendor/autoload.php';
require 'conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\NamedRange;

// Limpiar cualquier búfer de salida existente
ob_clean();

// Crear una nueva hoja de cálculo principal
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos');

// Definir los apartados y sus secciones
$apartados = ['Gaming', 'Moviles', 'Varios'];
$secciones = [
    'Gaming' => ['Perifericos', 'Consolas', 'Equipos'],
    'Moviles' => ['Audifonos', 'Celulares', 'Gadgets'],
    'Varios' => ['Seguridad', 'Unidades', 'Varios']
];

// Crear una hoja para las listas de validación (oculta)
$listSheet = $spreadsheet->createSheet();
$listSheet->setTitle('_ListasValidacion');

// Llenar la hoja de listas y definir rangos nombrados
$colIndex = 0;
foreach ($secciones as $apartado => $seccionList) {
    // Convertir índice de columna numérico a letra (A, B, C...)
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

    // Escribir el nombre del apartado como encabezado en la hoja de listas
    $listSheet->setCellValue($colLetter . '1', $apartado);

    // Escribir las secciones debajo del encabezado
    $row = 2;
    foreach ($seccionList as $seccion) {
        $listSheet->setCellValue($colLetter . $row, $seccion);
        $row++;
    }

    // Definir un rango nombrado para esta lista de secciones
    // El nombre del rango es el nombre del apartado (ej: Gaming)
    // El rango se refiere a las celdas que contienen las secciones para ese apartado
    $rangeAddress = '$' . $colLetter . '$2:$' . $colLetter . '$' . ($row - 1);
    
    // Asegurarse de que el nombre del rango no tenga espacios u caracteres especiales si es posible
    // y que el nombre del rango coincida exactamente con el valor que estará en la celda de Apartado

    // Eliminar NamedRange existente si ya existe (útil para depuración si corres el script varias veces)
    // $spreadsheet->removeNamedRange($apartado);

    $spreadsheet->addNamedRange(
        new NamedRange(
            $apartado, // Nombre del rango (debe coincidir con el valor del Apartado)
            $listSheet, // Hoja donde se encuentra el rango
            '_ListasValidacion!' . $rangeAddress // Referencia al rango en formato Hoja!Rango
        )
    );
    $colIndex++;
}

// Ocultar la hoja de listas
$listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

// Establecer los encabezados en la hoja principal
$headers = [
    'A1' => 'Nombre (Obligatorio)',
    'B1' => 'Precio (Obligatorio, mayor que 0)',
    'C1' => 'Stock (Opcional, 0 por defecto)',
    'D1' => 'Descripción (Obligatorio)',
    'E1' => 'Apartado (Obligatorio)',
    'F1' => 'Sección (Obligatorio)',
    'G1' => 'Es Popular (1=Si, 0=No)',
    'H1' => 'URL Imagen (Opcional)'
];

// Aplicar los encabezados
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Establecer el ancho de las columnas
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(10);
$sheet->getColumnDimension('D')->setWidth(50);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(50);

// Estilo para los encabezados
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2C3E50']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(30);

// Estilo para las celdas de datos
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];

// Aplicar estilo a las primeras 101 filas de datos + fila de ejemplo = 102 filas (filas 2 a 103)
$sheet->getStyle('A2:H103')->applyFromArray($dataStyle);

// Agregar validación de datos para Apartado
$apartadoValidation = new DataValidation();
$apartadoValidation->setType(DataValidation::TYPE_LIST);
$apartadoValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
$apartadoValidation->setAllowBlank(false);
$apartadoValidation->setShowInputMessage(true);
$apartadoValidation->setShowErrorMessage(true);
$apartadoValidation->setShowDropDown(true);
// Usar la lista directamente en la fórmula para el Apartado
$apartadoValidation->setFormula1('"' . implode(',', $apartados) . '"');

// Aplicar la validación a las primeras 102 filas de datos (filas 2 a 103)
for ($row = 2; $row <= 103; $row++) {
    $sheet->getCell("E$row")->setDataValidation(clone $apartadoValidation);
}

// Agregar validación de datos para Sección usando INDIRECT
$seccionValidation = new DataValidation();
$seccionValidation->setType(DataValidation::TYPE_LIST);
$seccionValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
$seccionValidation->setAllowBlank(false);
$seccionValidation->setShowInputMessage(true);
$seccionValidation->setShowErrorMessage(true);
$seccionValidation->setShowDropDown(true);

// La fórmula INDIRECT(E[fila]) hace referencia al rango nombrado que coincide con el valor de la celda E[fila]
// Por ejemplo, si E2 es "Gaming", INDIRECT(E2) se refiere al rango nombrado "Gaming" en la hoja _ListasValidacion

// Aplicar la validación a las primeras 102 filas de datos (filas 2 a 103)
for ($row = 2; $row <= 103; $row++) {
    $currentRowValidation = clone $seccionValidation;
    // Ajustar la referencia de celda en la fórmula para cada fila
    $currentRowValidation->setFormula1('=INDIRECT(E'.$row.')');
    $sheet->getCell("F$row")->setDataValidation($currentRowValidation);
}

// Agregar validación para Es Popular
$popularValidation = new DataValidation();
$popularValidation->setType(DataValidation::TYPE_LIST);
$popularValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
$popularValidation->setAllowBlank(false);
$popularValidation->setShowInputMessage(true);
$popularValidation->setShowErrorMessage(true);
$popularValidation->setShowDropDown(true);
$popularValidation->setFormula1('"1,0"');

// Aplicar la validación a las primeras 102 filas de datos (filas 2 a 103)
for ($row = 2; $row <= 103; $row++) {
    $sheet->getCell("G$row")->setDataValidation(clone $popularValidation);
}

// Agregar datos de ejemplo (Fila 2)
$exampleData = [
    ['Producto Ejemplo', '99.99', '10', 'Descripción del producto', 'Gaming', 'Perifericos', '1', 'https://ejemplo.com/imagen.jpg']
];

$row = 2;
foreach ($exampleData as $data) {
    $sheet->fromArray($data, null, "A$row");
    $row++;
}

// Aplicar formato numérico a las columnas de precio y stock
$sheet->getStyle('B2:B103')->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('C2:C103')->getNumberFormat()->setFormatCode('0');

// Agregar instrucciones en una hoja separada
$instructionSheet = $spreadsheet->createSheet();
$instructionSheet->setTitle('Instrucciones');

$instructions = [
    ['Columna', 'Descripción', 'Requisitos'],
    ['Nombre', 'Nombre del producto', 'Obligatorio'],
    ['Precio', 'Precio del producto', 'Obligatorio, debe ser mayor a 0'],
    ['Stock', 'Cantidad disponible', 'Opcional, 0 por defecto'],
    ['Descripción', 'Descripción detallada del producto', 'Obligatorio'],
    ['Apartado', 'Categoría principal', 'Obligatorio, debe ser: Gaming, Moviles o Varios'],
    ['Sección', 'Subcategoría', 'Obligatorio, depende del apartado seleccionado'],
    ['Es Popular', 'Indica si el producto es popular', 'Opcional, 1 = Si, 0 = No (0 por defecto)'],
    ['URL Imagen', 'URL de la imagen del producto', 'Opcional, debe ser una URL válida']
];

$instructionSheet->fromArray($instructions, null, 'A1');

// Estilo para las instrucciones
$instructionSheet->getStyle('A1:C' . (count($instructions) + 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$instructionSheet->getStyle('A1:C1')->applyFromArray($headerStyle);

// Ajustar el ancho de las columnas de instrucciones
$instructionSheet->getColumnDimension('A')->setWidth(15);
$instructionSheet->getColumnDimension('B')->setWidth(40);
$instructionSheet->getColumnDimension('C')->setWidth(30);

// Configurar el archivo para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="plantilla_productos.xlsx"');
header('Cache-Control: max-age=0');

// Crear el archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Asegurarse de que no haya salida adicional
flush();
exit(); 
