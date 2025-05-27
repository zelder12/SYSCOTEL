<?php
// generarFactura.php

ob_start();
session_start();
include 'php/conexion.php';
require_once('librerias/TCPDF-main/tcpdf.php');

// Validaciones de sesión y parámetros
if (!isset($_SESSION['nombre'])) header('Location: php/login.php');
if (!isset($_GET['pedido_id'])) header('Location: index.php');

$pedido_id = intval($_GET['pedido_id']);
if ($pedido_id <= 0) die("ID de pedido inválido.");

define('BASE_DIR', __DIR__);

// Manejo de errores
function handleError($msg) {
    ob_end_clean();
    echo "<div style='text-align:center;margin-top:50px'>";
    echo "<h2>Error</h2><p>{$msg}</p>";
    echo "<a href='index.php'>Volver</a></div>";
    exit;
}

// Obtener información del pedido
$stmt = $conn->prepare("SELECT p.*, pd.producto_id, pd.nombre_producto, pd.cantidad, pd.precio 
                       FROM pedidos p 
                       JOIN pedido_detalles pd ON p.id = pd.pedido_id 
                       WHERE p.id = ?");

if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("i", $pedido_id);

if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$result = $stmt->get_result();
$detalles = [];
$pedido = null;

while ($row = $result->fetch_assoc()) {
    if (!$pedido) {
        $pedido = [
            'id' => $row['id'],
            'total' => $row['total'],
            'metodo_pago' => $row['metodo_pago'],
            'compra_online' => $row['compra_online'],
            'cliente_nombre' => $row['cliente_nombre'],
            'cliente_direccion' => $row['cliente_direccion'],
            'cliente_telefono' => $row['cliente_telefono'],
            'cliente_email' => $row['cliente_email'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
    
    $detalles[] = [
        'producto_id' => $row['producto_id'],
        'nombre' => $row['nombre_producto'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio'],
        'subtotal' => $row['cantidad'] * $row['precio']
    ];
}

if (!$pedido) {
    die("No se encontró el pedido especificado");
}

// Datos de cliente
$cli_nombre    = $pedido['cliente_nombre']   ?: $_SESSION['nombre'];
$cli_direccion = $pedido['cliente_direccion'];
// Si la dirección está vacía y es compra online, mostrar un mensaje más apropiado
if (empty($cli_direccion)) {
    $cli_direccion = $pedido['compra_online'] ? 'Recoger en tienda' : 'No disponible';
}
$cli_telefono  = $pedido['cliente_telefono'] ?: '';
$cli_email     = $pedido['cliente_email'] ?: ($_SESSION['email'] ?? '');
if ($uid > 0) {
  $s3 = $conn->prepare("SELECT email,telefono FROM login WHERE id=?");
    $s3->bind_param("i", $uid);
  $s3->execute();
  $u = $s3->get_result()->fetch_assoc();
    $cli_email    = empty($cli_email) ? ($u['email'] ?? '') : $cli_email;
    $cli_telefono = empty($cli_telefono) ? ($u['telefono'] ?? '') : $cli_telefono;
}

// Cálculo de impuestos
$subtotal = $pedido['total'] / 1.16;
$iva      = $pedido['total'] - $subtotal;

try {
    ob_end_clean();
    $pdf = new TCPDF('P','mm','A4',true,'UTF-8',false);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->SetMargins(15,15,15);
    $pdf->SetAutoPageBreak(true,15);
    $pdf->AddPage();

    // Marca de agua
    $logo = BASE_DIR.'/img/logo_FACTURA.png';
    if (file_exists($logo)) {
        $x = ($pdf->getPageWidth()-100)/2;
        $y = ($pdf->getPageHeight()-100)/2;
        $pdf->Image($logo,$x,$y,100,100,'','', '',false,8,'',false,false,0,'CM');
    }

    // CSS mejorado
    $css = '<style>
        .header { text-align:center; margin-bottom:30px; }
        .logo { max-height:70px; margin-bottom:10px; }
        .title { font-size:20pt; font-weight:bold; color:#333; margin:10px 0; }
        .subtitle { font-size:14pt; color:#555; margin-bottom:25px; }
        .section { border:1px solid #ddd; border-radius:8px; padding:15px; margin-bottom:20px; background:#fff; }
        .section-title { font-size:13pt; font-weight:bold; color:#333; border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:12px; }
        .company-info, .client-info { font-size:10pt; line-height:1.6; color:#555; }
        .client-info div { margin-bottom:6px; }
        .client-info strong { display:inline-block; width:90px; color:#333; }
        .product-table { width:100%; border-collapse:collapse; font-size:9.5pt; table-layout:fixed; }
        .product-table th, .product-table td {
            border:1px solid #ccc;
            padding:10px;
            vertical-align:top;
            word-wrap:break-word;
            white-space:normal;
        }
        .product-table th { background:#f0f0f0; text-align:center; border-bottom:2px solid #ccc; }
        .product-table tbody tr:nth-child(even) { background:#f9f9f9; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .legal-text { font-size:7.5pt; color:#777; text-align:center; margin-top:30px; border-top:1px solid #eee; padding-top:10px; }
        
        .total-row td {
            font-weight:bold;
            background:#eee;
            border-top:2px solid #ccc;
        }
        .total-row td:first-child { border-left: 1px solid #ccc; }
        .total-row td:last-child { border-right: 1px solid #ccc; }
        .total-row:last-child td { border-bottom: 1px solid #ccc; }
    </style>';

    // Construir HTML
    $logo_header = BASE_DIR.'/img/syscotel.png';
    $html = $css . '<div class="header">';
    if (file_exists($logo_header)) {
        $html .= "<img src='{$logo_header}' class='logo'><br>";
    }
    $html .= '<div class="title">SYSCOTEL</div>
              <div class="subtitle">Documento Tributario Electrónico</div>
              </div>';

    // Emisor y DTE
    $dte_num = 'DTE-01-S003P009-' . str_pad($pedido_id,12,'0',STR_PAD_LEFT);
    $fecha   = date('d/m/Y H:i', strtotime($pedido['fecha_creacion']));
    $html .= '<div class="section">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="60%" valign="top">
                      <div class="section-title">Emisor</div>
                      <div class="company-info">
                        SYSCOTEL, S.A. de C.V.<br>
                        CC Metrocentro San Miguel, Local 105<br>
                        NIT: 0511-060616-101-6<br>
                        NRC: 251311-3<br>
                        Tel: 2527-8000
                      </div>
                    </td>
                    <td width="40%" valign="top" align="right">
                      <div class="section-title">DTE No:</div>
                      <div style="font-size:12pt;font-weight:bold;">' . $dte_num . '</div>
                      <div>Fecha: ' . $fecha . '</div>
                    </td>
                  </tr>
                </table>
              </div>';

    // Receptor similar a Emisor
    $html .= '<div class="section">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="100%" valign="top">
                      <div class="section-title">Receptor</div>
                      <div class="client-info">
                        <div><strong>Nombre:</strong> '.esc($cli_nombre).'</div>
                        <div><strong>Dirección:</strong> '.esc($cli_direccion).'</div>
                        <div><strong>Teléfono:</strong> '.esc($cli_telefono).'</div>
                        <div><strong>Email:</strong> '.esc($cli_email).'</div>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>';

    // Detalle de Artículos
    $html .= '<div class="section">
                <div class="section-title">Detalle de Artículos</div>
                <table class="product-table">
                  <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 22%;">
                    <col style="width: 40%;">
                    <col style="width: 14%;">
                    <col style="width: 14%;">
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="text-center">Cant</th>
                      <th class="text-center">Código</th>
                      <th class="text-left">Descripción</th>
                      <th class="text-right">P. Unitario</th>
                      <th class="text-right">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>';
    foreach ($detalles as $detalle) {
        $line_total = $detalle['cantidad'] * $detalle['precio'];
        $html .= '<tr>
                    <td class="text-center">' . $detalle['cantidad'] . '</td>
                    <td class="text-center">' . substr(str_shuffle('0123456789'),0,8) . '</td>
                    <td class="text-left">' . esc($detalle['nombre']) . '</td>
                    <td class="text-right">$' . number_format($detalle['precio'],2) . '</td>
                    <td class="text-right">$' . number_format($line_total,2) . '</td>
                  </tr>';
    }

    // Agregar costo de envío si corresponde
    if ($pedido['compra_online'] && $pedido['metodo_pago'] !== 'efectivo') {
        $costo_envio = 5.00;
        $html .= '<tr>
                    <td></td>
                    <td></td>
                    <td class="text-left">Costo de envío</td>
                    <td></td>
                    <td class="text-right">$' . number_format($costo_envio,2) . '</td>
                  </tr>';
    }

    // Agregar totales dentro del tbody, con 5 celdas para alinear
    $html .= '<tr class="total-row">
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right" style="font-weight:bold;">Subtotal:</td>
                <td class="text-right" style="font-weight:bold;">$'.number_format($subtotal,2).'</td>
              </tr>';
    $html .= '<tr class="total-row">
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right" style="font-weight:bold;">IVA (16%):</td>
                <td class="text-right" style="font-weight:bold;">$'.number_format($iva,2).'</td>
              </tr>';
    $html .= '<tr class="total-row">
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right" style="font-weight:bold;">Total:</td>
                <td class="text-right" style="font-weight:bold;">$'.number_format($pedido['total'],2).'</td>
              </tr>';

    $html .= '</tbody>
              </table>
              </div>';

    // Texto legal
    $html .= '<div class="legal-text">
                Esta factura digital cumple con el Art.29 del Código Tributario de El Salvador.<br>
                Documento generado automáticamente el '.date('d/m/Y H:i:s').'
              </div>';

    // Generar PDF
    $pdf->writeHTML($html,true,false,true,false,'');
    $mode = (isset($_GET['descargar']) && $_GET['descargar']==='true') ? 'D':'I';
    $pdf->Output("factura_{$pedido_id}.pdf",$mode);

} catch(Exception $e) {
    $cli_email = $_SESSION['email'] ?? '';
    handleError("Error al generar PDF: ".$e->getMessage());
}

// Escapar texto
function esc($s){ return htmlspecialchars(stripslashes($s),ENT_QUOTES,'UTF-8'); }

$stmt->close();
$conn->close();
?>




