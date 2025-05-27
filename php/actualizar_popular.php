<?php
include("conexion.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $es_popular = $_POST['es_popular'];

    $stmt = $conn->prepare("UPDATE productos SET es_popular = ? WHERE id = ?");
    $stmt->bind_param("ii", $es_popular, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}

$conn->close();
?> 