<?php
include 'conexion.php';


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SHOW TABLES LIKE 'pedidos'";
$result = $conn->query($sql);
if ($conn->query($sql) === TRUE) {
    echo "Tabla pedidos creada correctamente<br>";
} else {
    echo "Error al crear la tabla pedidos: " . $conn->error . "<br>";
} {
    echo "La tabla pedidos ya existe<br>";

  
    $sql = "SHOW COLUMNS FROM pedidos LIKE 'compra_online'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {

        $sql = "ALTER TABLE pedidos ADD COLUMN compra_online TINYINT(1) NOT NULL DEFAULT 0 AFTER metodo_pago";
        if ($conn->query($sql) === TRUE) {
            echo "Columna compra_online agregada correctamente<br>";
        } else {
            echo "Error al agregar la columna compra_online: " . $conn->error . "<br>";
        }
    }


    $sql = "SHOW COLUMNS FROM pedidos LIKE 'cliente_nombre'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {

        $sql = "ALTER TABLE pedidos ADD COLUMN cliente_nombre VARCHAR(255) NOT NULL AFTER productos";
        if ($conn->query($sql) === TRUE) {
            echo "Columna cliente_nombre agregada correctamente<br>";
        } else {
            echo "Error al agregar la columna cliente_nombre: " . $conn->error . "<br>";
        }
    }

    $sql = "SHOW COLUMNS FROM pedidos LIKE 'cliente_direccion'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {

        $sql = "ALTER TABLE pedidos ADD COLUMN cliente_direccion TEXT AFTER cliente_nombre";
        if ($conn->query($sql) === TRUE) {
            echo "Columna cliente_direccion agregada correctamente<br>";
        } else {
            echo "Error al agregar la columna cliente_direccion: " . $conn->error . "<br>";
        }
    }
}


$sql = "DESCRIBE pedidos";
$result = $conn->query($sql);

echo "<h3>Estructura de la tabla pedidos:</h3>";
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["Field"] . "</td>";
    echo "<td>" . $row["Type"] . "</td>";
    echo "<td>" . $row["Null"] . "</td>";
    echo "<td>" . $row["Key"] . "</td>";
    echo "<td>" . $row["Default"] . "</td>";
    echo "</tr>";
}
echo "</table>";


$sql = "SHOW TABLES LIKE 'pedido_detalles'";
$result = $conn->query($sql);


if ($conn->query($sql) === TRUE) {
    echo "Tabla pedido_detalles creada correctamente<br>";
} else {
    echo "Error al crear la tabla pedido_detalles: " . $conn->error . "<br>";
} {
    echo "La tabla pedido_detalles ya existe<br>";
}


$sql = "SHOW COLUMNS FROM pedidos LIKE 'cliente_telefono'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE pedidos ADD COLUMN cliente_telefono VARCHAR(50) DEFAULT NULL AFTER cliente_direccion";
    if ($conn->query($sql) === TRUE) {
        echo "Columna cliente_telefono agregada correctamente<br>";
    } else {
        echo "Error al agregar la columna cliente_telefono: " . $conn->error . "<br>";
    }
}

$sql = "SHOW COLUMNS FROM pedidos LIKE 'cliente_email'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE pedidos ADD COLUMN cliente_email VARCHAR(100) DEFAULT NULL AFTER cliente_telefono";
    if ($conn->query($sql) === TRUE) {
        echo "Columna cliente_email agregada correctamente<br>";
    } else {
        echo "Error al agregar la columna cliente_email: " . $conn->error . "<br>";
    }
}

// Verificar si la columna es_popular existe en la tabla productos
$sql = "SHOW COLUMNS FROM productos LIKE 'es_popular'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE productos ADD COLUMN es_popular TINYINT(1) NOT NULL DEFAULT 0";
    if ($conn->query($sql) === TRUE) {
        echo "Columna es_popular agregada correctamente<br>";
    } else {
        echo "Error al agregar la columna es_popular: " . $conn->error . "<br>";
    }
}

$query_detalles = "SELECT 
    pd.pedido_id,
    p.nombre as producto_nombre,
    p.seccion,
    p.apartado,
    p.descripcion,
    pd.cantidad,
    pd.precio as precio_venta,
    (pd.cantidad * pd.precio) as subtotal
    FROM pedido_detalles pd 
    LEFT JOIN productos p ON pd.producto_id = p.id 
    ORDER BY pd.pedido_id, pd.id";

// Verificar datos en pedido_detalles
echo "<h3>Datos en pedido_detalles:</h3>";
$result_detalles = $conn->query("SELECT * FROM pedido_detalles");
if ($result_detalles) {
    if ($result_detalles->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Pedido ID</th><th>Producto ID</th><th>Cantidad</th><th>Precio</th></tr>";
        while ($row = $result_detalles->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["pedido_id"] . "</td>";
            echo "<td>" . $row["producto_id"] . "</td>";
            echo "<td>" . $row["cantidad"] . "</td>";
            echo "<td>" . $row["precio"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay registros en pedido_detalles<br>";
    }
} else {
    echo "Error al consultar pedido_detalles: " . $conn->error . "<br>";
}

// Verificar datos en productos
echo "<h3>Datos en productos:</h3>";
$result_productos = $conn->query("SELECT * FROM productos");
if ($result_productos) {
    if ($result_productos->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Nombre</th><th>Sección</th><th>Apartado</th></tr>";
        while ($row = $result_productos->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["nombre"] . "</td>";
            echo "<td>" . $row["seccion"] . "</td>";
            echo "<td>" . $row["apartado"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay registros en productos<br>";
    }
} else {
    echo "Error al consultar productos: " . $conn->error . "<br>";
}

// Verificar la consulta de detalles
echo "<h3>Resultado de la consulta de detalles:</h3>";
$result_query = $conn->query($query_detalles);
if ($result_query) {
    if ($result_query->num_rows > 0) {
        echo "<table border='1'><tr><th>Pedido ID</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>";
        while ($row = $result_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["pedido_id"] . "</td>";
            echo "<td>" . $row["producto_nombre"] . "</td>";
            echo "<td>" . $row["cantidad"] . "</td>";
            echo "<td>" . $row["precio_venta"] . "</td>";
            echo "<td>" . $row["subtotal"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay resultados en la consulta de detalles<br>";
    }
} else {
    echo "Error en la consulta de detalles: " . $conn->error . "<br>";
}

$conn->close();
?>
