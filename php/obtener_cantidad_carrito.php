<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Inicializar la cantidad en 0
$cantidad = 0;

if (isset($_SESSION['id'])) {
    // Usuario logueado - obtener de la base de datos
    $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $cantidad = intval($row['total']) ?? 0;
    }
} else {
    // Usuario no logueado - obtener de la sesión
    if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
        $cantidad = array_sum($_SESSION['carrito']);
    }
}

// Enviar la respuesta JSON
echo json_encode([
    'status' => 'success',
    'cantidad' => $cantidad
]);

// Cerrar la conexión a la base de datos
if (isset($conn)) {
    $conn->close();
}
?> 
