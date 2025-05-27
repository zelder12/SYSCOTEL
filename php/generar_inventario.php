<?php
ob_start();
session_start();
include 'conexion.php';
require_once('../librerias/TCPDF-main/tcpdf.php');

// Validaciones de sesión
if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header('Location: ../index.php');
    exit();
}
if (!$conn) {
    die("Error en conexión: " . mysqli_connect_error());
}

// Manejo de errores
function handleError($msg) {
    if (ob_get_length()) ob_end_clean();
    echo "<div style='text-align:center;margin-top:50px'><h2>Error</h2><p>{$msg}</p><a href='../index.php'>Volver</a></div>";
    exit;
}

// Limpia saltos y espacios
function cleanText($s) {
    $t = htmlspecialchars(stripslashes($s), ENT_QUOTES, 'UTF-8');
    return preg_replace('/\s+/', ' ', $t);
}

try {
    class MYPDF extends TCPDF {
        public function Header() {
            $this->SetFont('helvetica','B',16);
            $this->Cell(0,10,'INVENTARIO DE PRODUCTOS',0,1,'C');
            $this->SetFont('helvetica','',10);
            $this->Cell(0,8,'Fecha: '.date('d/m/Y H:i:s'),0,1,'C');
            $this->Ln(2);
        }
    }

    // Configuración del PDF
    $pdf = new MYPDF('L','mm','A4',true,'UTF-8',false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Syscotel');
    $pdf->SetTitle('Inventario de Productos');
    $pdf->SetMargins(15,40,15);
    $pdf->SetHeaderMargin(15);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(true,20);
    $pdf->setCellHeightRatio(1.2);
    $pdf->AddPage();

    // Anchos y etiquetas
    $anchos    = [15,50,70,25,20,40,30,30];
    $etiquetas = ['ID','Código de Barras','Nombre','Precio','Stock','Categoría','Apartado','Estado'];

    // Encabezado
    $pdf->SetFont('helvetica','B',11);
    $pdf->SetFillColor(52,73,94);
    $pdf->SetTextColor(255);
    foreach ($etiquetas as $i => $txt) {
        $ln = ($i === count($etiquetas)-1) ? 1 : 0;
        $pdf->Cell($anchos[$i], 10, $txt, 1, $ln, 'C', 1);
    }

    // Datos
    $pdf->SetFont('helvetica','',10);
    $pdf->SetTextColor(0);
    $fill = false;
    $res = $conn->query("SELECT * FROM productos ORDER BY apartado,seccion,nombre");
    if (!$res) throw new Exception($conn->error);

    while ($p = $res->fetch_assoc()) {
        // color de fondo alternado
        $bg = $fill ? [245,245,245] : [255,255,255];
        $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);

        // 1) ID
        $pdf->Cell($anchos[0], 12, $p['id'], 1, 0, 'C', true);

        // 2) Código de barras
        $x0 = $pdf->GetX();
        $y0 = $pdf->GetY();
        $pdf->Cell($anchos[1], 12, '', 1, 0, 'C', true);
        $pdf->write1DBarcode(
            str_pad($p['id'], 8, '0', STR_PAD_LEFT),
            'C128',
            $x0 + 2, $y0 + 2,
            46, 8,
            0.4,
            ['font'=>'helvetica','fontsize'=>7],
            'N'
        );
        // forzamos el cursor justo al final de la celda de barras
        $pdf->SetXY($x0 + $anchos[1], $y0);

        // 3) Nombre
        $pdf->Cell($anchos[2], 12, cleanText($p['nombre']), 1, 0, 'L', true);

        // 4) Precio
        $pdf->Cell($anchos[3], 12, '$'.number_format($p['precio'],2), 1, 0, 'R', true);

        // 5) Stock
        $pdf->Cell($anchos[4], 12, $p['stock'], 1, 0, 'C', true);

        // 6) Categoría
        $pdf->Cell($anchos[5], 12, cleanText($p['seccion']), 1, 0, 'L', true);

        // 7) Apartado
        $pdf->Cell($anchos[6], 12, cleanText($p['apartado']), 1, 0, 'L', true);

        // 8) Estado (última columna, ln=1 para saltar fila)
        $color = $p['stock'] > 0 ? [40,167,69] : [220,53,69];
        $pdf->SetTextColor($color[0], $color[1], $color[2]);
        $pdf->Cell($anchos[7], 12, $p['stock'] > 0 ? 'Disponible' : 'Sin Stock', 1, 1, 'C', true);
        $pdf->SetTextColor(0);

        $fill = !$fill;
    }

    if (ob_get_length()) ob_end_clean();
    $pdf->Output('inventario.pdf','I');

} catch (Exception $e) {
    handleError("Error al generar PDF: " . $e->getMessage());
}
