<?php
session_start();
require 'php/conexion.php';

if (!isset($_SESSION['nombre'])) {
    header("Location: php/login.php");
    exit();
}

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    echo "No tienes permiso para acceder.";
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: php/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SYSCOTEL</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/user.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/syscotel.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-...hash..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .featured-sections {
            display: flex;
            gap: 90px;
            background-color: rgba(128, 128, 128, 0.1);
            padding: 10px;
            justify-content: center;
        }

        .section {
            width: calc(25% - 20px);
            margin-bottom: 5px;
            text-align: center;
            background-color: rgba(210, 210, 210, 0.226);
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease;
            gap: 1px;
        }

        .section:hover {
            transform: scale(1.1);
        }

        .section a {
            text-decoration: none;
            color: black;
        }

        .section-image {
            width: 100%;
            max-height: 250px;
            border-radius: 10px;
            margin-bottom: 10px;

        }

        .section-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        h2 {
            font-size: 18px;
            font-weight: bold;
        }

        .btn-generar-reporte {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-generar-reporte:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }

        .btn-generar-reporte i {
            font-size: 20px;
        }

        .footer {
            margin-top: 100px;
        }
    </style>

</head>

<body>
    <header>
        <nav>
            <div class="wrapper">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/syscotel.png" alt="Syscotel Logo">syscotel</a>
                </div>
                <ul class="nav-links">
                    <label for="close-btn" class="btn close-btn"><i class="fas fa-times"></i></label>
                    </li>
                    <div class="search-cart">
                        <a href="#" onclick="toggleUserOptions()">
                            <i class="fas fa-user" style="color: black;"></i>
                        </a>
                    </div>
                    <div class="user-options" id="userOptions">
                        <?php if (isset($_SESSION['nombre'])): ?>
                            <a href="#" onclick="logout()">Cerrar Sesión</a>
                        <?php endif; ?>
                    </div>

                    <script>
                        function toggleUserOptions() {
                            var userOptions = document.getElementById("userOptions");
                            if (userOptions.style.display === "block") {
                                userOptions.style.display = "none";
                            } else {
                                userOptions.style.display = "block";
                            }
                        }

                        function logout() {
                            window.location.href = "php/logout.php";
                        }
                    </script>
    </header>

    <main class="main-content">

        <section class="container specials" data-aos="fade-up">
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <?php

            $nombre = $_SESSION['nombre'];

            $mensaje_bienvenida = "¡Bienvenido, $nombre!";
            echo "<h1 class='heading-1'>$mensaje_bienvenida</h1>";
            ?>

            <h1 class="heading-1">VISTA ADMIN</h1>
            <br>
            <br>
            <div class="featured-sections">
                <div class="section">
                    <a href="php/agregar.php">
                        <div class="section-image">
                            <img src="img/PRODUCTOS.png" alt="Productos">
                        </div>
                        <h2>ADMINISTRAR PRODUCTOS</h2>
                    </a>
                </div>

                <div class="section">
                    <a href="modificar/index.php">
                        <div class="section-image">
                            <img src="img/usuario.png" alt="Usuarios">
                        </div>
                        <h2>ADMINISTRAR USUARIOS</h2>
                    </a>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="php/generar_ventas.php" class="btn-generar-reporte">
                    <i class="fas fa-file-pdf"></i>
                    Generar Reporte de Ventas
                </a>
            </div>
        </section>


    </main>

    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-section about">
                <h3>SYSCOTEL</h3>
                <p>Bienvenido a Syscotel, tu destino definitivo para todo lo relacionado con la electrónica y la
                    tecnología de
                    vanguardia. Situada en una ubicación privilegiada en el centro de la ciudad, Syscotel se destaca
                    como un oasis
                    de innovación en un mundo digital en constante evolución.</p>
            </div>
            <div class="footer-section contact">
                <h3>Contáctanos</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> (+503) 7850-8218</li>
                    <li><i class="fas fa-envelope"></i> SYSCOTEL@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Calle Principal, Ciudad, Principal</li>
                </ul>
            </div>
            <div class="footer-section social">
                <h3>Síguenos</h3>
                <div class="flex justify-center space-x-5">
                    <a href="https://www.facebook.com/syscotel.sanmiguel.1" target="_blank" rel="noopener noreferrer">
                        <img src="https://img.icons8.com/fluent/30/000000/facebook-new.png" alt="Facebook" />
                    </a>
                    <a href="https://www.instagram.com/syscotel_sm/?hl=es-la" target="_blank" rel="noopener noreferrer">
                        <img src="https://img.icons8.com/fluent/30/000000/instagram-new.png" alt="Instagram" />
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bar">
            <p>&copy; 2024 SYSCOTEL. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>


</html>
