<?php
session_start();
include 'conexion.php';

// Asegurar que PHP maneje correctamente UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php?error=1");
    exit();
}

// Usar real_escape_string para evitar inyección SQL, pero mantener caracteres UTF-8
$nombre = $conn->real_escape_string(trim($_POST['nombre'] ?? ''));
$password = $_POST['password'] ?? '';

if ($nombre === '' || $password === '') {
    header("Location: login.php?error=1");
    exit();
}

// Preparar consulta con parámetros para evitar problemas con caracteres especiales
$stmt = $conn->prepare("SELECT id, password, admin FROM login WHERE nombre = ? LIMIT 1");
if (!$stmt) {
    header("Location: login.php?error=1");
    exit();
}
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?error=2");
    exit();
}

$user = $result->fetch_assoc();
$stored = $user['password'];

if (password_verify($password, $stored)) {
    // Contraseña correcta con hash moderno
    // Continuar con el inicio de sesión
} elseif ($stored === $password) {
    // Contraseña almacenada en texto plano (legacy)
    // Actualizar a hash moderno
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $upd = $conn->prepare("UPDATE login SET password = ? WHERE id = ?");
    $upd->bind_param("si", $newHash, $user['id']);
    $upd->execute();
    $upd->close();
} else {
    // Contraseña incorrecta
    header("Location: login.php?error=3");
    exit();
}

session_regenerate_id(true);
$_SESSION['id'] = $user['id'];
$_SESSION['nombre'] = $nombre;
$_SESSION['es_admin'] = ($user['admin'] == 1);

// Cargar el carrito del usuario desde la base de datos
$stmt = $conn->prepare("SELECT producto_id, cantidad FROM carrito WHERE usuario_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['carrito'] = array();
while ($row = $result->fetch_assoc()) {
    $_SESSION['carrito'][$row['producto_id']] = $row['cantidad'];
}

if ($_SESSION['es_admin']) {
    header("Location: ../admin.php");
} else {
    header("Location: ../index.php");
}
exit();
?>

