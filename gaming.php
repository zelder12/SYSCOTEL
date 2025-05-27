<?php
session_start();
include 'php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gaming - SYSCOTEL</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/styles2.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/custom.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/syscotel.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-...hash..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .no-products-message {
            text-align: center;
            width: 100%;
            font-size: 24px;
            font-weight: bold;
            color: #555;
            padding: 30px;
        }
        
        .cart-icon {
            position: relative;
            display: inline-block;
            text-decoration: none;
        }

        .cart-icon i {
            font-size: 28px;
            color: #333;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .cart-icon:hover i {
            transform: scale(1.2);
        }

        .cart-count {
            position: absolute;
            top: 3px;
            right: -8px;
            background-color: #1e88e5;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(30, 136, 229, 0.2);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .cart-icon:hover .cart-count {
            transform: scale(1.1);
        }

        .cart-animation {
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            will-change: transform;
            animation: cartFly 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes cartFly {
            0% {
                transform: scale(1) translate(0, 0);
                opacity: 1;
            }
            50% {
                transform: scale(0.8) translate(var(--target-x), var(--target-y));
                opacity: 0.8;
            }
            100% {
                transform: scale(0.4) translate(var(--target-x), var(--target-y));
                opacity: 0;
            }
        }

        .cart-animation img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Estilos mejorados para el botón de agregar al carrito */
        .btn-add-cart {
            --background: #4CAF50;
            --text: #fff;
            position: relative;
            border: none;
            background: none;
            padding: 10px 28px;
            border-radius: 10px;
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
            overflow: hidden;
            cursor: pointer;
            text-align: center;
            min-width: 144px;
            height: 45px;
            color: var(--text);
            background: var(--background);
            transition: .3s ease-in-out;
            font-family: Arial, sans-serif;
        }

        .btn-add-cart:hover {
            background-color: #45a049;
        }

        .btn-add-cart:active {
            transform: scale(.95);
        }

        .btn-add-cart span {
            position: absolute;
            z-index: 3;
            left: 50%;
            top: 50%;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            transform: translate(-50%, -50%);
            transition: .3s ease-in-out;
            white-space: nowrap;
            opacity: 0;
        }

        .btn-add-cart span.add-to-cart {
            opacity: 1;
        }

        .btn-add-cart span.added {
            opacity: 0;
        }

        .btn-add-cart.loading span.add-to-cart {
            animation: txt1 1.5s ease-in-out forwards;
        }

        .btn-add-cart.loading span.added {
            animation: txt2 1.5s ease-in-out forwards;
        }

        .btn-add-cart .cart {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: -10%;
            font-size: 18px;
            transform: translate(-50%, -50%);
        }

        .btn-add-cart .box {
            position: absolute;
            z-index: 3;
            top: -20%;
            left: 52%;
            font-size: 16px;
            transform: translate(-50%, -50%);
        }

        .btn-add-cart.loading .cart {
            animation: cart 1.5s ease-in-out forwards;
        }

        .btn-add-cart.loading .box {
            animation: box 1.5s ease-in-out forwards;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            font-family: Arial, sans-serif;
            text-align: left;
            border-radius: 8px;
        }

        .quickView-bg {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 6px;
        }

        .quickView-details h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .modal-quantity-controls {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .modal-quantity-controls label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-quantity-modal {
            background-color: #6187ca;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            margin: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-quantity-modal:hover {
            background-color: #446eaa;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }

        .image-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .image-control-btn {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
        }

        .image-control-btn:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        #quickViewImageContainer {
            position: relative;
        }

        @keyframes cart {
            0% {
                left: -10%;
            }
            40%, 60% {
                left: 50%;
            }
            100% {
                left: 110%;
            }
        }

        @keyframes box {
            0%, 40% {
                top: -20%;
            }
            60% {
                top: 40%;
                left: 52%;
            }
            100% {
                top: 40%;
                left: 112%;
            }
        }

        @keyframes txt1 {
            0% {
                opacity: 1;
            }
            20%, 100% {
                opacity: 0;
            }
        }

        @keyframes txt2 {
            0%, 80% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .me-2 {
            margin-right: 8px;
        }

        /* Estilos para el botón flotante de ayuda */
        .floating-help {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: #1e88e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(30, 136, 229, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            text-decoration: none;
        }

        .floating-help:hover {
            transform: scale(1.1);
            background-color: #1565c0;
        }

        .floating-help i {
            color: white;
            font-size: 24px;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .floating-help {
            animation: float 3s ease-in-out infinite;
        }

        .user-options {
            position: absolute;
            right: 20px;
            top: 50px;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
            min-width: 200px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            transform-origin: top right;
            animation: menuAppear 0.3s ease-out;
        }

        @keyframes menuAppear {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .btn-login, .btn-register, .btn-logout {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            margin: 6px 0;
            text-align: left;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .btn-login {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-register {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.2);
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
        }

        .btn-logout {
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.2);
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #cc0000, #b30000);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 68, 68, 0.3);
        }

        .btn-login:active, .btn-register:active, .btn-logout:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn-login i, .btn-register i, .btn-logout i {
            font-size: 16px;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .btn-login:hover i, .btn-register:hover i, .btn-logout:hover i {
            transform: scale(1.1);
        }

        .me-2 {
            margin-right: 12px;
        }

        .search-cart a[onclick="toggleUserOptions()"] {
            position: relative;
            transition: transform 0.3s ease;
        }

        .search-cart a[onclick="toggleUserOptions()"]:hover {
            transform: scale(1.1);
        }

        .search-cart a[onclick="toggleUserOptions()"] i {
            font-size: 28px;
            transition: all 0.3s ease;
        }

        .search-cart a[onclick="toggleUserOptions()"]:hover i {
            color: #1e88e5;
        }

        /* Efecto de brillo al pasar el mouse */
        .btn-login::after, .btn-register::after, .btn-logout::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.1) 50%,
                rgba(255,255,255,0) 100%
            );
            transform: rotate(45deg);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .btn-login:hover::after, .btn-register:hover::after, .btn-logout:hover::after {
            opacity: 1;
            animation: shine 1.5s ease-in-out infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <br>
        <h1 data-aos="fade-up" class="heading-1">Gaming y Tecnología</h1>
        <h1 data-aos="fade-up" class="heading-2" id="perifericos">Periféricos Gaming</h1>
        <hr class="separator" data-aos="fade-up">
        <br>
        <br>

        <?php
        echo "<div class='container-products' data-aos='fade-up'>";
        $seccion = 'Perifericos';

        $sql = "SELECT * FROM productos WHERE seccion = 'Perifericos'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stock = isset($row['stock']) ? (int)$row['stock'] : 0;
                $nombre_escapado = addslashes($row['nombre']);
                $descripcion_escapada = addslashes($row['descripcion']);
                $click = $stock > 0 ? "onclick='showQuickView(\"{$nombre_escapado}\", \"{$descripcion_escapada}\", \"{$row['precio']}\", \"{$row['imagen']}\", \"{$row['seccion']}\", \"{$row['id']}\")'" : '';
                echo "<div class='card-product' $click>";
                echo "<div class='container-img'>";
                echo "<img class='dynamic-image' src='img/perifericos/" . $row['imagen'] . "' alt='" . $row['nombre'] . "' />";
                if ($stock == 0) {
                    echo "<img src='img/out_of_stock.png' alt='Sin stock' style='position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;background-color:rgba(255,255,255,0.7);z-index:10;'>";
                }
                echo "</div>";
                echo "<div class='content-card-product'>";
                $nombre = $row['nombre'];
                if (strlen($nombre) > 40) {
                    $nombre = substr($nombre, 0, 40) . '...';
                }
                echo "<h3>" . $nombre . "</h3>";
                echo "<p class='price'>$" . $row['precio'] . "</p>";
                echo "<p style='color:#888;font-size:0.95em;'>Stock: $stock</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-products-message'>No se encontraron productos en la sección $seccion</p>";
        }

        echo "</div>";
        ?>

        <h1 data-aos="fade-up" class="heading-2" id="consolas">Consolas</h1>
        <hr class="separator" data-aos="fade-up">
        <br>
        <br>

        <?php
        echo "<div class='container-products' data-aos='fade-up'>";
        $seccion = 'Consolas';

        // Eliminar el filtro "AND stock > 0" para mostrar todos los productos
        $sql = "SELECT * FROM productos WHERE seccion = 'Consolas'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stock = isset($row['stock']) ? (int)$row['stock'] : 0;
                $nombre_escapado = addslashes($row['nombre']);
                $descripcion_escapada = addslashes($row['descripcion']);
                $click = $stock > 0 ? "onclick='showQuickView(\"{$nombre_escapado}\", \"{$descripcion_escapada}\", \"{$row['precio']}\", \"{$row['imagen']}\", \"{$row['seccion']}\", \"{$row['id']}\")'" : '';
                echo "<div class='card-product' $click>";
                echo "<div class='container-img'>";
                echo "<img class='dynamic-image' src='img/consolas/" . $row['imagen'] . "' alt='" . $row['nombre'] . "' />";
                if ($stock == 0) {
                    echo "<img src='img/out_of_stock.png' alt='Sin stock' style='position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;background-color:rgba(255,255,255,0.7);z-index:10;'>";
                }
                echo "</div>";
                echo "<div class='content-card-product'>";
                $nombre = $row['nombre'];
                if (strlen($nombre) > 40) {
                    $nombre = substr($nombre, 0, 40) . '...';
                }
                echo "<h3>" . $nombre . "</h3>";
                echo "<p class='price'>$" . $row['precio'] . "</p>";
                echo "<p style='color:#888;font-size:0.95em;'>Stock: $stock</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-products-message'>No se encontraron productos en la sección $seccion</p>";
        }

        echo "</div>";
        ?>

        <h1 data-aos="fade-up" class="heading-2" id="equipos">Equipos Gaming</h1>
        <hr class="separator" data-aos="fade-up">
        <br>
        <br>

        <?php
        echo "<div class='container-products' data-aos='fade-up'>";
        $seccion = 'Equipos';

        // Eliminar el filtro "AND stock > 0" para mostrar todos los productos
        $sql = "SELECT * FROM productos WHERE seccion = 'Equipos'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stock = isset($row['stock']) ? (int)$row['stock'] : 0;
                $nombre_escapado = addslashes($row['nombre']);
                $descripcion_escapada = addslashes($row['descripcion']);
                $click = $stock > 0 ? "onclick='showQuickView(\"{$nombre_escapado}\", \"{$descripcion_escapada}\", \"{$row['precio']}\", \"{$row['imagen']}\", \"{$row['seccion']}\", \"{$row['id']}\")'" : '';
                echo "<div class='card-product' $click>";
                echo "<div class='container-img'>";
                echo "<img class='dynamic-image' src='img/equipos/" . $row['imagen'] . "' alt='" . $row['nombre'] . "' />";
                if ($stock == 0) {
                    echo "<img src='img/out_of_stock.png' alt='Sin stock' style='position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;background-color:rgba(255,255,255,0.7);z-index:10;'>";
                }
                echo "</div>";
                echo "<div class='content-card-product'>";
                $nombre = $row['nombre'];
                if (strlen($nombre) > 40) {
                    $nombre = substr($nombre, 0, 40) . '...';
                }
                echo "<h3>" . $nombre . "</h3>";
                echo "<p class='price'>$" . $row['precio'] . "</p>";
                echo "<p style='color:#888;font-size:0.95em;'>Stock: $stock</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-products-message'>No se encontraron productos en la sección $seccion</p>";
        }

        echo "</div>";
        ?>

        <div id="quickView" class="modal" onclick="closeQuickView()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="quickView-bg">
                    <div id="quickViewImageContainer" class="container-img">
                        <div id="quickViewImage"></div>
                        <div class="image-controls">
                            <button id="prevImage" class="image-control-btn"><i class="fas fa-chevron-left"></i></button>
                            <button id="nextImage" class="image-control-btn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="quickView-details">
                        <h2 id="quickViewTitle"></h2>
                        <p id="quickViewDescription"></p>
                        <p id="quickViewPrice"></p>
                        <input type="hidden" id="quickViewProductId" value="">
                        <div class="modal-quantity-controls">
                            <label for="modalQuantity">Cantidad:</label>
                            <div class="quantity-selector">
                                <button class="btn-quantity-modal" onclick="decrementModalQuantity()">-</button>
                                <input type="number" id="modalQuantity" class="quantity-input" value="1" min="1" max="99">
                                <button class="btn-quantity-modal" onclick="incrementModalQuantity()">+</button>
                            </div>
                        </div>
                        <div class="btn-container">
                            <button onclick="quickViewAddToCart()" class="btn-add-cart">
                                <span class="add-to-cart">Agregar al carrito</span>
                                <span class="added">¡Agregado!</span>
                                <i class="fas fa-shopping-cart cart"></i>
                                <i class="fas fa-box box"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-section about">
                <h3>syscotel</h3>
                <p>Bienvenido a syscotel, tu destino definitivo para todo lo relacionado con la electrónica y la tecnología de vanguardia. syscotel se destaca como un oasis de innovación en un mundo digital en constante evolución.</p>
            </div>
            <div class="footer-section contact">
                <h3>Contáctanos</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> (+503) 7850-8218</li>
                    <li><i class="fas fa-envelope"></i> syscotel@gmail.com</li>
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
            <p>&copy; 2025 syscotel. Todos los derechos reservados.</p>
        </div>
    </footer>

    <a href="about_us.php" class="floating-help" title="Acerca de SYSCOTEL">
        <i class="fas fa-question"></i>
    </a>

    <script src="js/main.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script src="js/cart.js"></script>
    <script src="js/section-scroll.js"></script>
    <script>
        // Mantener la posición del scroll al refrescar
        if (window.history.scrollRestoration) {
            window.history.scrollRestoration = 'manual';
        }

        // Guardar la posición del scroll antes de refrescar
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        // Restaurar la posición del scroll después de cargar
        window.addEventListener('load', function() {
            const scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, scrollPosition);
                sessionStorage.removeItem('scrollPosition');
            }
        });

        let currentImages = [];
        let currentImageIndex = 0;
        let isAnimating = false;

        // Agregar evento a todos los botones de agregar al carrito
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.btn-add-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    <?php if (isset($_SESSION['nombre'])): ?>
                    if (!button.classList.contains('loading')) {
                        button.classList.add('loading');
                        setTimeout(() => button.classList.remove('loading'), 3700);
                    }
                    <?php endif; ?>
                });
                });
        });

        function incrementModalQuantity() {
            const input = document.getElementById('modalQuantity');
            const currentValue = parseInt(input.value);
            if (currentValue < 99) {
                input.value = currentValue + 1;
            }
        }

        function decrementModalQuantity() {
            const input = document.getElementById('modalQuantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        function showQuickView(title, description, price, image, seccion, id) {
            document.getElementById('modalQuantity').value = 1;
            
            fetch('php/obtener_imagenes.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        currentImages = data.imagenes;
                        currentImageIndex = 0;
                        
                        var imagePath = 'img/';
                        switch (seccion) {
                            case 'Perifericos': imagePath += 'perifericos/'; break;
                            case 'Consolas': imagePath += 'consolas/'; break;
                            case 'Equipos': imagePath += 'equipos/'; break;
                            case 'Audifonos': imagePath += 'audifonos/'; break;
                            case 'Celulares': imagePath += 'celulares/'; break;
                            case 'Gadgets': imagePath += 'gadgets/'; break;
                            case 'Seguridad': imagePath += 'seguridad/'; break;
                            case 'Unidades': imagePath += 'unidades/'; break;
                            case 'Varios': imagePath += 'varios/'; break;
                            default: imagePath += seccion.toLowerCase() + '/'; break;
                        }
                        
                        document.getElementById('quickViewTitle').innerText = title.length > 40 ? title.substring(0, 40) + '...' : title;
                        document.getElementById('quickViewDescription').innerText = description;
                        document.getElementById('quickViewPrice').innerText = 'Precio: $' + price;
                        document.getElementById('quickViewProductId').value = id;
                        
                        updateModalImage(imagePath);
                        
                        document.getElementById('prevImage').onclick = function(e) {
                            e.stopPropagation();
                            currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                            updateModalImage(imagePath);
                        };
                        
                        document.getElementById('nextImage').onclick = function(e) {
                            e.stopPropagation();
                            currentImageIndex = (currentImageIndex + 1) % currentImages.length;
                            updateModalImage(imagePath);
                        };
                        
                        const controls = document.querySelector('.image-controls');
                        if (currentImages.length <= 1) {
                            controls.style.display = 'none';
                        } else {
                            controls.style.display = 'flex';
                        }
                        
                        document.getElementById('quickView').style.display = 'block';
                    } else {
                        console.error('Error al obtener imágenes:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function updateModalImage(basePath) {
            if (currentImages.length > 0) {
                var quickViewImage = document.getElementById('quickViewImage');
                quickViewImage.innerHTML = "<img class='dynamic-image' src='" + basePath + currentImages[currentImageIndex] + "' alt='Imagen del producto' />";
            }
        }

        function closeQuickView() {
            document.getElementById('quickView').style.display = 'none';
        }

        function quickViewAddToCart() {
            <?php if (!isset($_SESSION['nombre'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 28px;">Inicia sesión</span>',
                    html: '<div style="font-size: 20px;">Debes iniciar sesión para agregar productos al carrito</div>',
                    confirmButtonColor: '#007bff',
                    confirmButtonText: '<span style="font-size: 18px;">Iniciar sesión</span>',
                    showCancelButton: true,
                    cancelButtonText: '<span style="font-size: 18px;">Cancelar</span>',
                    customClass: {
                        popup: 'swal-large-text'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'php/login.php';
                    }
                });
                return;
            <?php endif; ?>

            if (isAnimating) return;

            const id = document.getElementById('quickViewProductId').value;
            const quantity = parseInt(document.getElementById('modalQuantity').value);
            const modal = document.getElementById('quickView');
            const addToCartButton = modal.querySelector('.btn-add-cart');
            
            if (isNaN(quantity) || quantity < 1) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'error',
                    title: 'La cantidad debe ser un número mayor a 0'
                });
                return;
            }

            // Bloquear el botón y la animación inmediatamente
            isAnimating = true;
            addToCartButton.classList.add('loading');
            addToCartButton.style.pointerEvents = 'none';

            // Verificar si el modal está visible
            const isModalVisible = modal.style.display === 'block';
            let animationTimeout;
            let closeTimeout;

            // Función para detener la animación y restaurar el botón
            const stopAnimation = () => {
                isAnimating = false;
                addToCartButton.classList.remove('loading');
                addToCartButton.style.pointerEvents = 'auto';
                if (animationTimeout) clearTimeout(animationTimeout);
                if (closeTimeout) clearTimeout(closeTimeout);
            };

            // Agregar evento para detectar cuando el modal se cierra
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'style') {
                        const display = modal.style.display;
                        if (display === 'none') {
                            stopAnimation();
                            observer.disconnect();
                        }
                    }
                });
            });

            observer.observe(modal, { attributes: true });

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/agregar_carrito.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            // Incrementar el contador del carrito usando la nueva función
                            incrementCartCount(quantity, isModalVisible);

                            // Mostrar notificación toast
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            Toast.fire({
                                icon: 'success',
                                title: 'Se han agregado ' + quantity + ' unidad(es) al carrito'
                            });

                            // Si el modal está visible, esperar a que termine la animación antes de cerrarlo
                            if (isModalVisible) {
                                animationTimeout = setTimeout(() => {
                                    stopAnimation();
                                    closeTimeout = setTimeout(() => {
                                        closeQuickView();
                                    }, 500);
                                }, 1500);
                            } else {
                                stopAnimation();
                            }
                        } else {
                            // Si hay error, mostrar el mensaje y restaurar el estado
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'No se pudo agregar el producto'
                            });

                            stopAnimation();
                        }
                    } catch (e) {
                        console.error('Error al parsear la respuesta:', e);
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        
                        Toast.fire({
                            icon: 'error',
                            title: 'Error al procesar la respuesta del servidor'
                        });

                        stopAnimation();
                    }
                }
            };
            
            xhr.onerror = function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'error',
                    title: 'Error de conexión con el servidor'
                });

                stopAnimation();
            };
            
            xhr.send('producto_id=' + id + '&cantidad=' + quantity);
        }
    </script>
</body>

</html>




