<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!isset($_SESSION['nombre'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debe iniciar sesión para modificar el carrito']);
    exit;
}

if (isset($_POST['producto_id']) && isset($_POST['cambio'])) {
    $producto_id = intval($_POST['producto_id']);
    $cambio = intval($_POST['cambio']);
    
    // Verificar el stock disponible
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
    
    // Obtener la cantidad actual en el carrito
    $cantidad_actual = 0;
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $_SESSION['id'], $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $cantidad_actual = $result->fetch_assoc()['cantidad'];
        }
    } else {
        $cantidad_actual = isset($_SESSION['carrito'][$producto_id]) ? $_SESSION['carrito'][$producto_id] : 0;
    }
    
    $nueva_cantidad = $cantidad_actual + $cambio;
    
    // Validar la nueva cantidad contra el stock disponible
    if ($nueva_cantidad > $stock_disponible) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Stock insuficiente. Solo hay ' . $stock_disponible . ' unidades disponibles.'
        ]);
        exit;
    }
    
    if ($nueva_cantidad <= 0) {
        // Eliminar del carrito
        if (isset($_SESSION['id'])) {
            $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->bind_param("ii", $_SESSION['id'], $producto_id);
            $stmt->execute();
        }
        unset($_SESSION['carrito'][$producto_id]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Producto eliminado del carrito',
            'carrito' => $_SESSION['carrito']
        ]);
    } else {
        // Actualizar cantidad
        if (isset($_SESSION['id'])) {
            $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
            $stmt->bind_param("iii", $nueva_cantidad, $_SESSION['id'], $producto_id);
            $stmt->execute();
        }
        $_SESSION['carrito'][$producto_id] = $nueva_cantidad;
        echo json_encode([
            'status' => 'success',
            'message' => 'Cantidad actualizada',
            'carrito' => $_SESSION['carrito']
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos']);
}

$conn->close();
?>