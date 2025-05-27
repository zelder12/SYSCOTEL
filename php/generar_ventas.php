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

try {
    class MYPDF extends TCPDF {
        public function Header() {
            $this->SetFont('helvetica','B',16);
            $this->Cell(0,10,'REPORTE DE VENTAS',0,1,'C');
            $this->SetFont('helvetica','',10);
            $this->Cell(0,8,'Fecha: '.date('d/m/Y H:i:s'),0,1,'C');
            $this->Ln(2);
        }
    }

    // Configuración del PDF
    $pdf = new MYPDF('L','mm','A4',true,'UTF-8',false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Syscotel');
    $pdf->SetTitle('Reporte de Ventas');
    $pdf->SetMargins(10,30,10);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(true,15);
    $pdf->setCellHeightRatio(1.1);
    $pdf->AddPage();

    // Anchos y etiquetas para la tabla de pedidos
    $anchos_pedidos = [25,35,45,45,70];
    $etiquetas_pedidos = ['ID','Fecha','Método de Pago','Cliente','Contacto'];

    // Encabezado de pedidos
    $pdf->SetFont('helvetica','B',11);
    $pdf->SetFillColor(52,73,94);
    $pdf->SetTextColor(255);
    $pdf->Cell(0,8,'INFORMACIÓN DE CLIENTES',0,1,'L',1);
    $pdf->Ln(1);

    // Encabezado de la tabla de pedidos
    $pdf->SetFont('helvetica','B',10);
    foreach ($etiquetas_pedidos as $i => $txt) {
        $ln = ($i === count($etiquetas_pedidos)-1) ? 1 : 0;
        $pdf->Cell($anchos_pedidos[$i], 8, $txt, 1, $ln, 'C', 1);
    }

    // Datos de pedidos
    $pdf->SetFont('helvetica','',9);
    $pdf->SetTextColor(0);
    $fill = false;

    $query_pedidos = "SELECT p.id, p.fecha, p.metodo_pago, p.cliente_nombre, 
                     CONCAT(p.cliente_telefono, ' / ', p.cliente_email) as contacto
                     FROM pedidos p 
                     ORDER BY p.fecha DESC";
    
    $res_pedidos = $conn->query($query_pedidos);
    if (!$res_pedidos) throw new Exception($conn->error);

    while ($pedido = $res_pedidos->fetch_assoc()) {
        $bg = $fill ? [245,245,245] : [255,255,255];
        $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);

        $pdf->Cell($anchos_pedidos[0], 12, $pedido['id'], 1, 0, 'C', true);
        $pdf->Cell($anchos_pedidos[1], 12, date('d/m/Y', strtotime($pedido['fecha'])), 1, 0, 'C', true);
        $pdf->Cell($anchos_pedidos[2], 12, $pedido['metodo_pago'], 1, 0, 'C', true);
        $pdf->Cell($anchos_pedidos[3], 12, $pedido['cliente_nombre'], 1, 0, 'L', true);
        $pdf->Cell($anchos_pedidos[4], 12, $pedido['contacto'], 1, 1, 'L', true);

        $fill = !$fill;
    }

    $pdf->Ln(5);

    // Anchos y etiquetas para la tabla de detalles
    $anchos_detalles = [20, 30, 40, 60, 20, 20, 20, 20];
    $etiquetas_detalles = ['ID Pedido', 'Fecha/Hora', 'ID Cliente', 'Producto', 'Cantidad', 'Precio Unit.', 'Subtotal', 'Total'];

    // Encabezado de detalles
    $pdf->SetFont('helvetica','B',11);
    $pdf->SetFillColor(52,73,94);
    $pdf->SetTextColor(255);
    $pdf->Cell(0,8,'DETALLES DE VENTAS',0,1,'L',1);
    $pdf->Ln(1);

    // Encabezado de la tabla de detalles
    $pdf->SetFont('helvetica','B',10);
    foreach ($etiquetas_detalles as $i => $txt) {
        $ln = ($i === count($etiquetas_detalles)-1) ? 1 : 0;
        $pdf->Cell($anchos_detalles[$i], 8, $txt, 1, $ln, 'C', 1);
    }

    // Datos de detalles
    $pdf->SetFont('helvetica','',9);
    $pdf->SetTextColor(0);
    $fill = false;

    // Modificamos la consulta para incluir información del cliente y la fecha/hora del pedido
    $query_detalles = "SELECT
        pd.pedido_id,
        ped.fecha as fecha_pedido,
        ped.cliente_nombre,
        p.nombre as producto_nombre,
        p.seccion,
        p.apartado,
        pd.cantidad,
        pd.precio as precio_venta,
        (pd.cantidad * pd.precio) as subtotal
        FROM pedido_detalles pd
        LEFT JOIN productos p ON pd.producto_id = p.id
        LEFT JOIN pedidos ped ON pd.pedido_id = ped.id
        ORDER BY pd.pedido_id, pd.id";
    
    $res_detalles = $conn->query($query_detalles);
    if (!$res_detalles) {
        throw new Exception("Error en la consulta de detalles: " . $conn->error);
    }

    $total_general = 0;
    $pedido_actual = null;

    // Verificamos si hay resultados
    if ($res_detalles->num_rows > 0) {
        while ($detalle = $res_detalles->fetch_assoc()) {
            if ($pedido_actual !== null && $pedido_actual != $detalle['pedido_id']) {
                $pdf->Ln(2);
            }
            $pedido_actual = $detalle['pedido_id'];

            $bg = $fill ? [245,245,245] : [255,255,255];
            $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);

            // ID Pedido
            $pdf->Cell($anchos_detalles[0], 8, $detalle['pedido_id'], 1, 0, 'C', true);

            // Fecha y Hora del Pedido
            $pdf->Cell($anchos_detalles[1], 8, date('d/m/Y H:i', strtotime($detalle['fecha_pedido'])), 1, 0, 'C', true);

            // ID Cliente (usamos el nombre en lugar del ID por ser más informativo)
            $pdf->Cell($anchos_detalles[2], 8, $detalle['cliente_nombre'], 1, 0, 'L', true);

            // Nombre del producto con categoría y apartado
            $nombre_completo = $detalle['producto_nombre'] ?
                $detalle['producto_nombre'] . "\n" .
                "(" . $detalle['seccion'] . " - " . $detalle['apartado'] . ")" :
                "Producto no encontrado";
            // Ajustar altura de celda si el nombre es largo
            $cell_height = $pdf->getStringHeight($anchos_detalles[3] - $pdf->getCellPaddings()['L'] - $pdf->getCellPaddings()['R'], $nombre_completo);
            $pdf->MultiCell($anchos_detalles[3], max(8, $cell_height), $nombre_completo, 1, 'L', true, 0, '', '', true, 0, true);

            // Cantidad
            $pdf->Cell($anchos_detalles[4], max(8, $cell_height), $detalle['cantidad'], 1, 0, 'C', true);

            // Precio unitario
            $pdf->Cell($anchos_detalles[5], max(8, $cell_height), '$'.number_format($detalle['precio_venta'], 2), 1, 0, 'R', true);

            // Subtotal
            $pdf->Cell($anchos_detalles[6], max(8, $cell_height), '$'.number_format($detalle['subtotal'], 2), 1, 0, 'R', true);

            // Total (mismo que subtotal para cada detalle, el total general es la suma)
            $pdf->Cell($anchos_detalles[7], max(8, $cell_height), '$'.number_format($detalle['subtotal'], 2), 1, 1, 'R', true);

            $total_general += $detalle['subtotal'];
            $fill = !$fill;
        }
    } else {
        // Si no hay detalles, mostramos un mensaje
        $pdf->Cell(0, 8, 'No hay detalles de ventas disponibles', 0, 1, 'C');
    }

    // Mostrar total general
    $pdf->Ln(2);
    $pdf->SetFont('helvetica','B',11);
    $pdf->Cell(0, 8, 'Total General: $' . number_format($total_general, 2), 0, 1, 'R');

    if (ob_get_length()) ob_end_clean();
    $pdf->Output('reporte_ventas.pdf','I');

} catch (Exception $e) {
    handleError("Error al generar PDF: " . $e->getMessage());
} 