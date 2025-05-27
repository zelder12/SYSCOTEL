<?php
// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configure error logging
ini_set('log_errors', 1);
ini_set('error_log', '../logs/db_update.log');

include 'conexion.php';

// Verificar si existe la columna estado y eliminarla
$check_column = $conn->query("SHOW COLUMNS FROM pedidos LIKE 'estado'");
if ($check_column->num_rows > 0) {
    $conn->query("ALTER TABLE pedidos DROP COLUMN estado");
    echo "Columna 'estado' eliminada de la tabla pedidos\n";
}

// Verificar si existe la tabla pedido_detalles
$check_table = $conn->query("SHOW TABLES LIKE 'pedido_detalles'");
if ($check_table->num_rows == 0) {
    // Crear la tabla pedido_detalles
    $sql = "CREATE TABLE pedido_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        producto_id INT NOT NULL,
        nombre_producto VARCHAR(255) NOT NULL,
        cantidad INT NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    )";
    
    if ($conn->query($sql)) {
        echo "Tabla pedido_detalles creada correctamente\n";
    } else {
        echo "Error al crear la tabla pedido_detalles: " . $conn->error . "\n";
    }
}

// Verificar y agregar columnas necesarias a la tabla pedidos
$required_columns = [
    'compra_online' => "ADD COLUMN compra_online BOOLEAN DEFAULT FALSE",
    'cliente_nombre' => "ADD COLUMN cliente_nombre VARCHAR(255)",
    'cliente_direccion' => "ADD COLUMN cliente_direccion TEXT",
    'cliente_telefono' => "ADD COLUMN cliente_telefono VARCHAR(20)",
    'cliente_email' => "ADD COLUMN cliente_email VARCHAR(255)",
    'fecha_creacion' => "ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP"
];

foreach ($required_columns as $column => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM pedidos LIKE '$column'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE pedidos $sql")) {
            echo "Columna '$column' agregada correctamente\n";
        } else {
            echo "Error al agregar la columna '$column': " . $conn->error . "\n";
        }
    } else {
        echo "La columna '$column' ya existe\n";
    }
}

// Verificar si existe la columna productos y eliminarla si existe
$check_column = $conn->query("SHOW COLUMNS FROM pedidos LIKE 'productos'");
if ($check_column->num_rows > 0) {
    $conn->query("ALTER TABLE pedidos DROP COLUMN productos");
    echo "Columna 'productos' eliminada de la tabla pedidos\n";
}

$conn->close();
echo "Actualización completada\n";
?>