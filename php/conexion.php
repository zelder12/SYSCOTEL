<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "rober"; // Actualizado con la contraseña correcta
$dbname = "syscotel";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Registrar el error en el log
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    
    // Si estamos en una solicitud AJAX, devolver un error JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }
    
    // De lo contrario, mostrar un mensaje de error amigable
    die("Error de conexión: Por favor, contacte al administrador del sistema.");
}

// Establecer el conjunto de caracteres
$conn->set_charset("utf8");
?>








