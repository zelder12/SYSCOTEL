<?php
session_start();
require '../php/conexion.php';

if (!isset($_SESSION['nombre'])) {
    header("Location: ../php/login.php");
    exit();
}
if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    echo "No tienes permiso para acceder.";
    exit();
}

include("connection.php");
$con = connection();

// Función para eliminar un usuario
function eliminarUsuario($id) {
    global $con;
    
    $sql = "DELETE FROM login WHERE id='$id' AND admin=0";
    $query = mysqli_query($con, $sql);
    
    if ($query) {
        return ['status' => 'success', 'message' => 'Usuario eliminado correctamente'];
    } else {
        return ['status' => 'error', 'message' => 'Error al eliminar el usuario'];
    }
}

// Función para actualizar un usuario
function actualizarUsuario($id, $nombre, $email, $password = null) {
    global $con;
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'El formato del email no es válido'];
    }
    
    // Validar nombre
    if (empty($nombre) || strlen($nombre) < 3) {
        return ['status' => 'error', 'message' => 'El nombre debe tener al menos 3 caracteres'];
    }
    
    try {
        // Preparar la consulta base
        $sql = "UPDATE login SET nombre=?, email=?";
        $params = [$nombre, $email];
        $types = "ss";
        
        // Agregar password si se proporciona
        if ($password && !empty($password)) {
            if (strlen($password) < 6) {
                return ['status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password=?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        $sql .= " WHERE id=? AND admin=0";
        $params[] = $id;
        $types .= "i";
        
        // Preparar y ejecutar la consulta
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        }
        
        if (mysqli_stmt_affected_rows($stmt) === 0) {
            return ['status' => 'error', 'message' => 'No se encontró el usuario o no se realizaron cambios'];
        }
        
        mysqli_stmt_close($stmt);
        return ['status' => 'success', 'message' => 'Usuario actualizado correctamente'];
        
    } catch (Exception $e) {
        error_log("Error en actualizarUsuario: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Error al actualizar el usuario: ' . $e->getMessage()];
    }
}

// Determinar la operación a realizar
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'eliminar':
        if (isset($_REQUEST['id'])) {
            $response = eliminarUsuario($_REQUEST['id']);
            
            if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode($response);
            } else {
                if ($response['status'] === 'success') {
                    header("Location: index.php?success=deleted");
                } else {
                    header("Location: index.php?error=delete_failed");
                }
            }
        } else {
            if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
            } else {
                header("Location: index.php?error=no_id");
            }
        }
        break;
        
    case 'actualizar':
        if (isset($_REQUEST['id'])) {
            $id = filter_var($_REQUEST['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                $response = ['status' => 'error', 'message' => 'ID de usuario inválido'];
            } else {
                $nombre = trim($_REQUEST['nombre']);
                $email = trim($_REQUEST['email']);
                $password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : null;
                
                $response = actualizarUsuario($id, $nombre, $email, $password);
            }
            
            if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } else {
                if ($response['status'] === 'success') {
                    header("Location: index.php?success=updated");
                } else {
                    header("Location: index.php?error=" . urlencode($response['message']));
                }
                exit();
            }
        } else {
            $response = ['status' => 'error', 'message' => 'ID no proporcionado'];
            if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode($response);
            } else {
                header("Location: index.php?error=no_id");
            }
            exit();
        }
        break;
        
    default:
        header("Location: index.php");
        exit();
}
?>