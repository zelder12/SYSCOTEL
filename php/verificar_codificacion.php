<?php
// Script para verificar y corregir la codificación de la base de datos
include 'conexion.php';

// Verificar la codificación actual de la base de datos
$result = $conn->query("SHOW VARIABLES LIKE 'character_set%'");
echo "<h2>Configuración actual de caracteres en MySQL:</h2>";
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
}
echo "</pre>";

// Verificar la codificación de las tablas
$result = $conn->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'syscotel'");
echo "<h2>Codificación de tablas:</h2>";
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    echo $row['TABLE_NAME'] . ": " . $row['TABLE_COLLATION'] . "\n";
}
echo "</pre>";

// Verificar la codificación de las columnas en la tabla login
$result = $conn->query("SHOW FULL COLUMNS FROM login");
echo "<h2>Codificación de columnas en tabla login:</h2>";
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ": " . $row['Collation'] . "\n";
}
echo "</pre>";

// Función para corregir la codificación si es necesario
function corregirCodificacion() {
    global $conn;
    
    // Convertir la base de datos a utf8mb4
    $conn->query("ALTER DATABASE syscotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Convertir la tabla login a utf8mb4
    $conn->query("ALTER TABLE login CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Asegurar que las columnas específicas estén en utf8mb4
    $conn->query("ALTER TABLE login MODIFY nombre VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->query("ALTER TABLE login MODIFY email VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "<h2>Codificación corregida</h2>";
    echo "<p>La base de datos ha sido convertida a utf8mb4_unicode_ci</p>";
}

// Descomenta la siguiente línea para corregir la codificación
// corregirCodificacion();
?>