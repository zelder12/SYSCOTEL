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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $seccion = $_POST['seccion'] ?? '';
    $apartado = $_POST['apartado'] ?? '';

    if (empty($nombre) || empty($seccion) || empty($apartado)) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }

    // Verificar si existe un producto con el mismo nombre en la misma sección y apartado
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE nombre = ? AND seccion = ? AND apartado = ?");
    $stmt->bind_param("sss", $nombre, $seccion, $apartado);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['existe' => $row['total'] > 0]);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Método no permitido']);
}

$conn->close();
?> 