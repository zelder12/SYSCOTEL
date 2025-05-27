<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar los datos
$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

// Verificar si el producto existe y tiene stock suficiente
$stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit;
}

$producto = $result->fetch_assoc();
$stock_disponible = $producto['stock'];

// Verificar la cantidad actual en el carrito
$cantidad_en_carrito = 0;
if (isset($_SESSION['id'])) {
    // Si el usuario está logueado, verificar en la base de datos
    $stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
    $stmt->bind_param("ii", $_SESSION['id'], $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $cantidad_en_carrito = $result->fetch_assoc()['cantidad'];
    }
} else {
    // Si no está logueado, verificar en la sesión
    $cantidad_en_carrito = isset($_SESSION['carrito'][$producto_id]) ? $_SESSION['carrito'][$producto_id] : 0;
}

// Verificar si hay suficiente stock considerando lo que ya está en el carrito
if ($stock_disponible < ($cantidad_en_carrito + $cantidad)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Stock insuficiente. Solo hay ' . $stock_disponible . ' unidades disponibles y ya tienes ' . $cantidad_en_carrito . ' en el carrito.'
    ]);
    exit;
}

// Si el usuario está logueado, guardar en la base de datos
if (isset($_SESSION['id'])) {
    // Verificar si el producto ya está en el carrito
    $stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
    $stmt->bind_param("ii", $_SESSION['id'], $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar cantidad
        $stmt = $conn->prepare("UPDATE carrito SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("iii", $cantidad, $_SESSION['id'], $producto_id);
    } else {
        // Insertar nuevo registro
        $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $_SESSION['id'], $producto_id, $cantidad);
    }
    
    if ($stmt->execute()) {
        // Obtener el total de items en el carrito
        $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'] ?? 0;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Producto agregado al carrito',
            'total_items' => $total
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar al carrito']);
    }
} else {
    // Si no está logueado, guardar en la sesión
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }
    
    if (isset($_SESSION['carrito'][$producto_id])) {
        $_SESSION['carrito'][$producto_id] += $cantidad;
    } else {
        $_SESSION['carrito'][$producto_id] = $cantidad;
    }
    
    $total = array_sum($_SESSION['carrito']);
    echo json_encode([
        'status' => 'success',
        'message' => 'Producto agregado al carrito',
        'total_items' => $total
    ]);
}

$stmt->close();
$conn->close();
?>





