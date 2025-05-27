<?php
session_start();
header('Content-Type: application/json');

include("conexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Usar consulta directa en lugar de prepared statement para evitar el error
    $sql = "SELECT * FROM imagenes_producto WHERE producto_id = $id ORDER BY orden ASC";
    $result = $conn->query($sql);
    
    $imagenes = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagenes[] = $row['nombre_archivo'];
        }
    } else {
        $sql = "SELECT imagen FROM productos WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['imagen'])) {
                $imagenes[] = $row['imagen'];
            } else {
                $imagenes[] = 'placeholder.jpg';
            }
        } else {
            $imagenes[] = 'placeholder.jpg';
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'imagenes' => $imagenes
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID de producto no especificado'
    ]);
}

$conn->close();
?>
