<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no logueado']);
    exit;
}

// Obtener el carrito del localStorage
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Limpiar el carrito actual del usuario
    $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();

    // Insertar los nuevos productos
    $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
    foreach ($input as $producto_id => $cantidad) {
        $stmt->bind_param("iii", $_SESSION['id'], $producto_id, $cantidad);
        $stmt->execute();
    }

    // Actualizar la sesión
    $_SESSION['carrito'] = $input;

    // Confirmar transacción
    $conn->commit();

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?> 