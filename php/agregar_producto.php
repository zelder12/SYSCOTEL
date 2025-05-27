<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("conexion.php");

    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $seccion = $_POST['seccion'];
    $apartado = $_POST['apartado']; 
    $imagen_nombre = $_FILES['imagen']['name'];
    $imagen_tmp = $_FILES['imagen']['tmp_name'];

    $base_dir = "../img/";
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
    
    $ruta = $base_dir . $ruta_relativa . $imagen_nombre;

    $response = [];

    if (move_uploaded_file($imagen_tmp, $ruta)) {
        $es_popular = isset($_POST['es_popular']) ? 1 : 0;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, descripcion, imagen, seccion, apartado, es_popular, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssssii", $nombre, $precio, $descripcion, $imagen_nombre, $seccion, $apartado, $es_popular, $stock);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Producto agregado correctamente';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error al agregar el producto: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error al cargar la imagen: ' . error_get_last()['message'];
    }

    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
