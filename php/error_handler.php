<?php
// Basado en el ejemplo de set_error_handler de la documentación
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $errstr = htmlspecialchars($errstr);
    $error_log = date('Y-m-d H:i:s') . " - Error [$errno] $errstr en $errfile línea $errline\n";
    
    // Guardar en archivo de log
    error_log($error_log, 3, "../logs/error.log");
    
    // Respuesta según el tipo de error
    switch ($errno) {
        case E_USER_ERROR:
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Error crítico: ' . $errstr]);
            } else {
                echo "<div class='error'>Error crítico: $errstr</div>";
            }
            exit(1);
            
        case E_USER_WARNING:
        case E_USER_NOTICE:
        default:
            // Registrar pero no mostrar al usuario en producción
            if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
                echo "<div class='warning'>Advertencia: $errstr</div>";
            }
            break;
    }
    
    return true;
}

// Establecer el manejador de errores personalizado
set_error_handler("customErrorHandler");
?>