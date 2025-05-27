<?php
?>
<header>
    <nav>
        <div class="wrapper">
            <div class="logo">
                <a href="index.php">
                    <img src="img/syscotel.png" alt="Syscotel Logo">syscotel</a>
            </div>
            <ul class="nav-links">
                <label for="close-btn" class="btn close-btn"><i class="fas fa-times"></i></label>
                <li><a href="index.php">Inicio</a></li>
                <li>
                    <a href="#" class="desktop-item">Secciones</a>
                    <input type="checkbox" id="showDrop">
                    <label for="showDrop" class="mobile-item">Secciones</label>
                    <ul class="drop-menu">
                        <li><a href="gaming.php">Gaming</a></li>
                        <li><a href="varios.php">Varios</a></li>
                        <li><a href="moviles.php">Moviles</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="desktop-item">Apartados</a>
                    <input type="checkbox" id="showMega">
                    <div class="mega-box">
                        <div class="content">
                            <div class="row">
                                <img src="https://cdn.computerhoy.com/sites/navi.axelspringer.es/public/media/image/2021/10/persona-jugando-pc-gaming-teclado-raton-rgb-2494995.jpg"
                                    alt="">
                            </div>
                            <div class="row">
                                <header>Gaming</header>
                                <ul class="mega-links">
                                    <li><a href="gaming.php#perifericos">Perifericos</a></li>
                                    <li><a href="gaming.php#consolas">Consolas</a></li>
                                    <li><a href="gaming.php#equipos">Equipos gamer</a></li>
                                </ul>
                            </div>
                            <div class="row">
                                <header>Varios</header>
                                <ul class="mega-links">
                                    <li><a href="varios.php#seguridad">Seguridad</a></li>
                                    <li><a href="varios.php#unidades">Unidades de Red</a></li>
                                    <li><a href="varios.php#varios">Varios</a></li>
                                </ul>
                            </div>
                            <div class="row">
                                <header>Móviles</header>
                                <ul class="mega-links">
                                    <li><a href="moviles.php#audifonos">Audífonos</a></li>
                                    <li><a href="moviles.php#celulares">Celulares</a></li>
                                    <li><a href="moviles.php#gadgets">Gadgets</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <div class="search-cart">
                    <a href="#" onclick="toggleUserOptions()">
                        <i class="fas fa-user" style="color: black;"></i>
                    </a>
                </div>
                <div class="user-options" id="userOptions">
                    <?php if (isset($_SESSION['nombre'])): ?>
                        <a href="#" onclick="logout()" class="btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="php/login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </a>
                        <a href="php/registrar.php" class="btn-register">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </a>
                    <?php endif; ?>
                </div>
                <div class="search-cart">
                    <a href="carrito.php" class="cart-icon" <?php if(basename($_SERVER['PHP_SELF']) == 'carrito.php') echo 'style="display: none;"'; ?>>
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </ul>
        </div>
    </nav>
    <script src="js/cart-counter.js"></script>
    <script src="js/cart.js"></script>
    <script>
        var lastScrollTop = 0;

        window.addEventListener("scroll", function () {
            var currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            if (currentScroll > lastScrollTop && currentScroll > 70) {
                document.querySelector("nav").style.top = "-70px";
            } else {
                document.querySelector("nav").style.top = "0";
            }
            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
        }, false);

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

        document.addEventListener('click', function(event) {
            var userOptions = document.getElementById("userOptions");
            var userIcon = document.querySelector('.search-cart a[onclick="toggleUserOptions()"]');
            
            if (userOptions.style.display === "block" && 
                !userOptions.contains(event.target) && 
                !userIcon.contains(event.target)) {
                userOptions.style.display = "none";
            }
        });
    </script>
</header>

<style>
    .search-cart {
        display: flex;
        align-items: center;
        gap: 20px;
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

    .cart-icon:hover .cart-count {
        transform: scale(1.1);
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
</style> 