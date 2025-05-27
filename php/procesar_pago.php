<?php
// Redirigir errores a un archivo de log
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/payment_errors.log');
ini_set('error_reporting', E_ALL);

// Incluir error handler
include 'error_handler.php';

// Desactivar la salida de errores de PHP
ini_set('display_errors', 0);
error_reporting(0);

// Configuración de errores para desarrollo
function handleError($message, $details = null) {
    $response = [
        'status' => 'error',
        'message' => $message,
        'debug_info' => $details
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Función para manejar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error fatal: ' . $error['message']
        ]);
    }
});

// Iniciar sesión y conectar a la base de datos
session_start();
include 'conexion.php';

// Debug: Log the incoming data
error_log('Payment data received: ' . file_get_contents('php://input'));

if ($conn->connect_error) {
    handleError('Error de conexión a la base de datos: ' . $conn->connect_error);
}

if (!isset($_SESSION['nombre'])) {
    handleError('Usuario no autenticado');
}

if (empty($_SESSION['carrito'])) {
    handleError('No hay productos en el carrito');
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    handleError('Datos inválidos: ' . json_last_error_msg());
}

try {
    // Validar datos requeridos
    $campos_requeridos = ['total', 'metodo_pago', 'cliente_nombre', 'cliente_telefono', 'cliente_email'];
    foreach ($campos_requeridos as $campo) {
        if (!isset($data[$campo]) || $data[$campo] === '') {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    // Asegurarse de que compra_local esté definido (true o false)
    $compra_local = isset($data['compra_local']) ? (bool)$data['compra_local'] : false;

    // Validar que el total sea un número positivo
    $total = floatval($data['total']);
    if ($total <= 0) {
        throw new Exception("El total debe ser mayor a 0");
    }

    // Agregar costo de envío si es compra online
    if (!$compra_local && $data['metodo_pago'] !== 'efectivo') {
        // Costo fijo de envío de $5.00
        $costo_envio = 5.00;
        $total += $costo_envio;
    }

    // Validar método de pago
    $metodo_pago = $conn->real_escape_string($data['metodo_pago']);
    if (!in_array($metodo_pago, ['efectivo', 'tarjeta', 'paypal'])) {
        throw new Exception("Método de pago no válido");
    }

    // Validar datos del cliente
    $cliente_nombre = trim($conn->real_escape_string($data['cliente_nombre']));
    if (empty($cliente_nombre)) {
        throw new Exception("El nombre del cliente es requerido");
    }

    $compra_local = isset($data['compra_local']) ? ($data['compra_local'] ? 1 : 0) : 0;
    $cliente_direccion = isset($data['cliente_direccion']) ? trim($conn->real_escape_string($data['cliente_direccion'])) : '';

    // Si es compra online y no es efectivo, la dirección es obligatoria
    if (!$compra_local && $metodo_pago !== 'efectivo' && empty($cliente_direccion)) {
        throw new Exception("La dirección es requerida para envíos a domicilio");
    }

    $cliente_telefono = isset($data['cliente_telefono']) ? trim($conn->real_escape_string($data['cliente_telefono'])) : '';
    $cliente_email = isset($data['cliente_email']) ? trim($conn->real_escape_string($data['cliente_email'])) : (isset($_SESSION['email']) ? $_SESSION['email'] : '');

    // Validar email si está presente
    if (!empty($cliente_email) && !filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El correo electrónico no es válido");
    }

    // Validar teléfono si está presente
    if (!empty($cliente_telefono) && !preg_match('/^[267]\d{7}$/', $cliente_telefono)) {
        throw new Exception("El número de teléfono no es válido para El Salvador");
    }

    // Validar dirección si es compra online
    if (!$compra_local && $metodo_pago !== 'efectivo' && empty($cliente_direccion)) {
        throw new Exception("La dirección es requerida para compras online");
    }

    // Obtener el ID del usuario de la sesión
    $usuario_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar el pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id, total, metodo_pago, compra_online, cliente_nombre, cliente_direccion, cliente_telefono, cliente_email, fecha_creacion, fecha) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $compra_online = !$compra_local;
        $stmt->bind_param("dsissss", 
            $total,
            $metodo_pago,
            $compra_online,
            $cliente_nombre,
            $cliente_direccion,
            $cliente_telefono,
            $cliente_email
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el pedido: " . $stmt->error);
        }

        $pedido_id = $conn->insert_id;

        // Insertar los detalles del pedido
        $stmt = $conn->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta de detalles: " . $conn->error);
        }

        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            // Obtener información del producto
            $stmt_producto = $conn->prepare("SELECT nombre, precio FROM productos WHERE id = ?");
            if (!$stmt_producto) {
                throw new Exception("Error al preparar consulta de producto: " . $conn->error);
            }
            
            $stmt_producto->bind_param("i", $producto_id);
            if (!$stmt_producto->execute()) {
                throw new Exception("Error al obtener información del producto: " . $stmt_producto->error);
            }
            
            $result = $stmt_producto->get_result();
            $producto = $result->fetch_assoc();
            
            if (!$producto) {
                throw new Exception("Producto no encontrado: ID " . $producto_id);
            }

            // Insertar detalle del pedido
            $stmt->bind_param("iisid", 
                $pedido_id,
                $producto_id,
                $producto['nombre'],
                $cantidad,
                $producto['precio']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al insertar detalle del pedido: " . $stmt->error);
            }

            // Actualizar el stock del producto
            $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            if (!$stmt_stock) {
                throw new Exception("Error al preparar la actualización de stock: " . $conn->error);
            }
            
            $stmt_stock->bind_param("ii", $cantidad, $producto_id);
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al actualizar el stock: " . $stmt_stock->error);
            }
            $stmt_stock->close();
        }

        // Limpiar el carrito de la sesión
        $_SESSION['carrito'] = array();
        
        // Si el usuario está logueado, limpiar también el carrito de la base de datos
        if (isset($_SESSION['id'])) {
            $stmt_limpiar = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            if ($stmt_limpiar) {
                $stmt_limpiar->bind_param("i", $_SESSION['id']);
                $stmt_limpiar->execute();
                $stmt_limpiar->close();
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'message' => 'Pago procesado correctamente',
            'pedido_id' => $pedido_id
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    handleError('Ha ocurrido un error al procesar el pago: ' . $e->getMessage());
}

if (isset($conn)) {
    $conn->close();
}
?>



