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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

include("connection.php");
$con = connection();

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$nombre = $con->real_escape_string(trim($_POST['nombre'] ?? ''));
$email = $con->real_escape_string(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

$check_sql = "SELECT admin FROM login WHERE id='$id'";
$check_result = mysqli_query($con, $check_sql);

if ($check_result && $row = mysqli_fetch_assoc($check_result)) {
    if ($row['admin'] == 1) {
        $response = array('status' => 'error', 'message' => 'No se pueden editar cuentas de administrador');
        echo json_encode($response);
        exit();
    }
}

if (empty($nombre) || empty($email) || $id <= 0) {
    $response = array('status' => 'error', 'message' => 'Datos incompletos o inválidos');
    echo json_encode($response);
    exit();
}

$sql = "UPDATE login SET nombre='$nombre', email='$email'";

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $sql .= ", password='$hashed_password'";
}

$sql .= " WHERE id='$id' AND admin=0";

$query = mysqli_query($con, $sql);

if ($query) {
    $response = array('status' => 'success', 'message' => 'Usuario actualizado correctamente');
} else {
    $response = array('status' => 'error', 'message' => 'Error al actualizar: ' . mysqli_error($con));
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
