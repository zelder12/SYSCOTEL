<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no permitido']);
    exit;
}

// Verificar que se recibió el ID del producto
if (!isset($_POST['producto_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado']);
    exit;
}

$producto_id = intval($_POST['producto_id']);

try {
    // Si el usuario está logueado, eliminar de la base de datos
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $_SESSION['id'], $producto_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar el producto del carrito");
        }
        $stmt->close();
    }
    
    // Eliminar de la sesión
    if (isset($_SESSION['carrito'][$producto_id])) {
        unset($_SESSION['carrito'][$producto_id]);
    }
    
    // Calcular el nuevo total
    $total = 0;
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("
            SELECT SUM(c.cantidad * p.precio) as total 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ?
        ");
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'] ?? 0;
    } else {
        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            $stmt = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $total += $row['precio'] * $cantidad;
            }
        }
    }
    
    // Verificar si el carrito está vacío
    $carrito_vacio = empty($_SESSION['carrito']);
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $carrito_vacio = $row['count'] == 0;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Producto eliminado correctamente',
        'total' => $total,
        'carrito_vacio' => $carrito_vacio
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 