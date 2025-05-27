<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (isset($_SESSION['id'])) {
    // Usuario logueado - obtener de la base de datos
    $stmt = $conn->prepare("
        SELECT c.producto_id, c.cantidad, p.* 
        FROM carrito c 
        JOIN productos p ON c.producto_id = p.id 
        WHERE c.usuario_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $carrito = array();
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $ruta_img = 'img/' . strtolower($row['seccion']) . '/' . $row['imagen'];

        $nombre = $row['nombre'];
        if (strlen($nombre) > 25) {
            $nombre = substr($nombre, 0, 25) . '...';
        }
        
        $carrito[] = array(
            'id' => $row['producto_id'],
            'nombre' => $nombre,
            'precio' => $row['precio'],
            'cantidad' => $row['cantidad'],
            'imagen' => $ruta_img,
            'subtotal' => $row['precio'] * $row['cantidad']
        );
        $total += $row['precio'] * $row['cantidad'];
    }
    
    echo json_encode([
        'status' => 'success',
        'carrito' => $carrito,
        'total' => $total
    ]);
} else {
    // Usuario no logueado - obtener de la sesión
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        echo json_encode([
            'status' => 'success',
            'carrito' => array(),
            'total' => 0
        ]);
    exit;
}

    $productos_ids = array_keys($_SESSION['carrito']);
    $placeholders = str_repeat('?,', count($productos_ids) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT * FROM productos 
        WHERE id IN ($placeholders)
    ");
    $stmt->bind_param(str_repeat('i', count($productos_ids)), ...$productos_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $carrito = array();
$total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $cantidad = $_SESSION['carrito'][$row['id']];
        
        $ruta_img = 'img/' . strtolower($row['seccion']) . '/' . $row['imagen'];

        $nombre = $row['nombre'];
        if (strlen($nombre) > 25) {
            $nombre = substr($nombre, 0, 25) . '...';
        }
        
        $carrito[] = array(
            'id' => $row['id'],
            'nombre' => $nombre,
            'precio' => $row['precio'],
            'cantidad' => $cantidad,
            'imagen' => $ruta_img,
            'subtotal' => $row['precio'] * $cantidad
        );
        $total += $row['precio'] * $cantidad;
}

echo json_encode([
    'status' => 'success',
        'carrito' => $carrito,
    'total' => $total
]);
}

$conn->close();
?>
