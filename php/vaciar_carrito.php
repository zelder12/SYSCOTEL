<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no permitido']);
    exit;
}

try {
    // Si el usuario está logueado, vaciar el carrito en la base de datos
    if (isset($_SESSION['id'])) {
        $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param("i", $_SESSION['id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al vaciar el carrito en la base de datos");
        }
        $stmt->close();
    }
    
    // Vaciar el carrito de la sesión
    $_SESSION['carrito'] = array();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Carrito vaciado correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 