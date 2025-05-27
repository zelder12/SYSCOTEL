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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['status' => 'error', 'message' => 'Acción no válida'];
    
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $imagen = $_POST['imagen'];
        $apartado = $_POST['apartado'];
        $seccion = $_POST['seccion'];
        
        // Determinar la ruta de la imagen
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
        
        $ruta_imagen = $base_dir . $ruta_relativa . $imagen;
        
        // Eliminar el producto de la base de datos
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Si se eliminó el producto, intentar eliminar la imagen
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            $response['status'] = 'success';
            $response['message'] = 'Producto eliminado correctamente';
        } else {
            $response['message'] = 'Error al eliminar el producto: ' . $stmt->error;
        }
        $stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$conn->close();
?>


