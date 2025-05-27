<?php
session_start();
include 'conexion.php';

// Validaciones de sesión
if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT imagen, apartado, seccion FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($producto = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($producto);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Producto no encontrado']);
    }
    
    $stmt->close();
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID no proporcionado']);
}

$conn->close();
?> 