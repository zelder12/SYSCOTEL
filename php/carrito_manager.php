<?php
session_start();
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// Función para calcular el total del carrito
function calcularTotal() {
    $total = 0;
    if (!empty($_SESSION['carrito'])) {
        include 'conexion.php';
        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            $sql = "SELECT precio FROM productos WHERE id = $id";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $producto = $result->fetch_assoc();
                $total += $producto['precio'] * $cantidad;
            }
        }
    }
    return $total;
}

// Asegurarse de que el carrito existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Determinar la operación a realizar
$operacion = $_REQUEST['operacion'] ?? '';

switch ($operacion) {
    case 'eliminar':
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            
            if (isset($_SESSION['carrito'][$id])) {
                unset($_SESSION['carrito'][$id]);
            }
            
            $total = calcularTotal();
            
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Producto eliminado del carrito',
                'carrito' => $_SESSION['carrito'],
                'total' => $total
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de producto no proporcionado',
                'carrito' => $_SESSION['carrito'],
                'total' => calcularTotal()
            ]);
            exit();
        }
        break;
        
    case 'actualizar':
        if (isset($_REQUEST['producto_id']) && isset($_REQUEST['cambio'])) {
            $producto_id = intval($_REQUEST['producto_id']);
            $cambio = intval($_REQUEST['cambio']);
            
            if (isset($_SESSION['carrito'][$producto_id])) {
                $nueva_cantidad = $_SESSION['carrito'][$producto_id] + $cambio;
                
                if ($nueva_cantidad <= 0) {
                    unset($_SESSION['carrito'][$producto_id]);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Producto eliminado del carrito',
                        'carrito' => $_SESSION['carrito'],
                        'total' => calcularTotal()
                    ]);
                } else {
                    $_SESSION['carrito'][$producto_id] = $nueva_cantidad;
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Cantidad actualizada',
                        'carrito' => $_SESSION['carrito'],
                        'total' => calcularTotal()
                    ]);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El producto no existe en el carrito',
                    'carrito' => $_SESSION['carrito'],
                    'total' => calcularTotal()
                ]);
            }
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetros incompletos',
                'carrito' => $_SESSION['carrito'],
                'total' => calcularTotal()
            ]);
            exit();
        }
        break;
        
    case 'vaciar':
        if (isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
            echo json_encode([
                'status' => 'success',
                'message' => 'Carrito vaciado correctamente',
                'carrito' => $_SESSION['carrito'],
                'total' => 0
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No hay carrito para vaciar'
            ]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Operación no válida',
            'carrito' => $_SESSION['carrito'],
            'total' => calcularTotal()
        ]);
        exit();
}
// Cerrar la conexión a la base de datos
if (isset($conn)) {
    $conn->close();
}
?>

