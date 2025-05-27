<?php
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (strlen($_POST['nombre']) >= 1 && strlen($_POST['email']) >= 1 && strlen($_POST['password']) >= 1) {
        $name = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if ($conn) { 
            $consulta = $conn->prepare("INSERT INTO login (nombre, password, email) VALUES (?, ?, ?)");
            if ($consulta) { 
                $consulta->bind_param("sss", $name, $password, $email);
                if ($consulta->execute()) {
                    $response = array('success' => true, 'message' => '¡Felicidades, te has inscrito correctamente!');
                    echo json_encode($response);
                    exit; 
                } else {
                    $response = array('success' => false, 'message' => '¡Ups, ha ocurrido un error al insertar en la base de datos!');
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = array('success' => false, 'message' => 'Error: ' . $conn->error);
                echo json_encode($response);
                exit;
            }
        } else {
            $response = array('success' => false, 'message' => '¡Error en base de datos!');
            echo json_encode($response);
            exit;
        }
    } else {
        $response = array('success' => false, 'message' => '¡Por favor, complete todos los campos!');
        echo json_encode($response);
        exit;
    }
}
?>
