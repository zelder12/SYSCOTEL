<?php
// Asegurar que PHP maneje correctamente UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

include 'conexion.php';

function buscarUsuario($nombre) {
    global $conn;
    
    // Preparar consulta con parámetros para manejar caracteres especiales
    $stmt = $conn->prepare("SELECT id, nombre, email, admin FROM login WHERE nombre = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    return $usuario;
}

// Ejemplo de uso:
// $usuario = buscarUsuario("サスケ");
// if ($usuario) {
//     echo "Usuario encontrado: " . $usuario['nombre'];
// } else {
//     echo "Usuario no encontrado";
// }
?>