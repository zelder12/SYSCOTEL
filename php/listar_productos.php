<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión
include("conexion.php");

// Configurar encabezados para JSON
header('Content-Type: application/json');

// Manejar errores de manera controlada
function handleError($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

try {
    // Consulta para obtener todos los productos
    $sql = "SELECT * FROM productos ORDER BY nombre ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $productos = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determinar la ruta de la imagen según el apartado y sección
        $imagen_path = determinarRutaImagen($row['apartado'], $row['seccion'], $row['imagen']);
        
        // Agregar producto al array
        $productos[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'precio' => $row['precio'],
            'descripcion' => $row['descripcion'],
            'imagen' => $imagen_path,
            'seccion' => $row['seccion'],
            'apartado' => $row['apartado'],
            'stock' => $row['stock'],
            'es_popular' => $row['es_popular']
        ];
    }
    
    // Devolver los productos como JSON
    echo json_encode($productos);
    
} catch (Exception $e) {
    handleError($e->getMessage());
}

// Función para determinar la ruta de la imagen
function determinarRutaImagen($apartado, $seccion, $imagen_nombre) {
    if (empty($imagen_nombre)) {
        return "../img/placeholder.jpg"; // Imagen por defecto
    }
    
    $base_url = "../img/";
    
    // Si es placeholder.jpg, usar la ruta base
    if ($imagen_nombre === 'placeholder.jpg') {
        return $base_url . $imagen_nombre;
    }
    
    $ruta_relativa = "";
    
    switch ($apartado) {
        case 'Gaming':
            switch ($seccion) {
                case 'Perifericos': $ruta_relativa = "perifericos/"; break;
                case 'Consolas': $ruta_relativa = "consolas/"; break;
                case 'Equipos': $ruta_relativa = "equipos/"; break;
                default: $ruta_relativa = "gaming/"; break;
            }
            break;
        case 'Moviles':
            switch ($seccion) {
                case 'Audifonos': $ruta_relativa = "audifonos/"; break;
                case 'Celulares': $ruta_relativa = "celulares/"; break;
                case 'Gadgets': $ruta_relativa = "gadgets/"; break;
                default: $ruta_relativa = "moviles/"; break;
            }
            break;
        case 'Varios':
            switch ($seccion) {
                case 'Seguridad': $ruta_relativa = "seguridad/"; break;
                case 'Unidades': $ruta_relativa = "unidades/"; break;
                case 'Varios': $ruta_relativa = "varios/"; break;
                default: $ruta_relativa = "varios/"; break;
            }
            break;
        default:
            $ruta_relativa = "otros/";
            break;
    }
    
    return $base_url . $ruta_relativa . $imagen_nombre;
}

// Cerrar la conexión
$conn->close();
?>


