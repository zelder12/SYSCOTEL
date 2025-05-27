<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nosotros - SYSCOTEL</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/styles2.css">
  <link rel="stylesheet" href="css/user.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="shortcut icon" href="img/syscotel.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    integrity="sha512-...hash..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .main-content {
      padding: 120px 20px 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .about-section {
      background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(30, 136, 229, 0.1);
      overflow: hidden;
      margin-bottom: 40px;
    }

    .about-header {
      color: #000000;
      padding: 40px;
      text-align: center;
      background: none;
    }

    .about-header h1 {
      font-size: 2.5em;
      margin: 0;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #000000;
    }

    .about-content {
      display: grid;
      grid-template-columns: 1fr;
      gap: 40px;
      padding: 40px;
    }

    @media (max-width: 768px) {
      .about-content {
        grid-template-columns: 1fr;
      }
    }

    .about-image {
      position: relative;
      overflow: hidden;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      height: 500px;
    }

    .about-image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
      background-color: #f8f9fa;
      padding: 10px;
    }

    .about-image:hover img {
      transform: none;
    }

    .about-text {
      display: flex;
      flex-direction: column;
      gap: 30px;
      max-width: 800px;
      margin: 0 auto;
    }

    .about-card {
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }

    .about-card:hover {
      transform: translateY(-5px);
    }

    .about-card h2 {
      color: #1e88e5;
      font-size: 1.8em;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .about-card h2 i {
      font-size: 1.4em;
    }

    .about-card p {
      color: #666;
      line-height: 1.8;
      font-size: 1.5em;
      margin: 0;
    }

    .stats-section {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      padding: 40px;
      background: #e3f2fd;
    }

    @media (max-width: 768px) {
      .stats-section {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .stat-card {
      text-align: center;
      padding: 20px;
    }

    .stat-number {
      font-size: 3em;
      font-weight: 700;
      color: #1e88e5;
      margin-bottom: 10px;
    }

    .stat-label {
      color: #666;
      font-size: 1.3em;
    }

    .team-section {
      padding: 40px;
      text-align: center;
    }

    .team-section h2 {
      color: #333;
      font-size: 2.5em;
      margin-bottom: 30px;
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      margin-top: 30px;
    }

    @media (max-width: 768px) {
      .team-grid {
        grid-template-columns: 1fr;
      }
    }

    .team-member {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .team-member:hover {
      transform: translateY(-5px);
    }

    .team-member img {
      width: 100%;
      height: 250px;
      object-fit: contain;
      padding: 10px;
      background-color: #f8f9fa;
    }

    .team-info {
      padding: 20px;
    }

    .team-info h3 {
      color: #333;
      margin: 0 0 10px;
      font-size: 1.6em;
    }

    .team-info p {
      color: #666;
      margin: 0;
      font-size: 1.3em;
    }

    .social-links {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 15px;
    }

    .social-links a {
      color: #1e88e5;
      font-size: 1.2em;
      transition: color 0.3s ease;
    }

    .social-links a:hover {
      color: #1565c0;
    }

    .btn-add-cart {
      --background: #1e88e5;
      --text: #fff;
    }

    .btn-add-cart:hover {
      background-color: #1565c0;
    }

    .btn-quantity-modal {
      background-color: #1e88e5;
    }

    .btn-quantity-modal:hover {
      background-color: #1565c0;
    }

    /* Estilos del carrito y contador */
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

    /* Estilos del usuario */
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
      background: #ff4444;
      color: white;
    }

    .btn-logout:hover {
      background: #cc0000;
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
  </style>
</head>

<body>
  <?php include 'header.php'; ?>

  <main class="main-content">
    <section class="about-section" data-aos="fade-up">
      <div class="about-header">
        <h1>Sobre SYSCOTEL</h1>
      </div>
      <div class="about-content">
        <div class="about-text">
          <div class="about-card" data-aos="fade-up">
            <h2><i class="fas fa-eye"></i> Nuestra Visión</h2>
            <p>Ser el referente líder en el mercado de tecnología y electrónica, reconocidos por nuestra innovación, calidad y servicio excepcional. Aspiramos a transformar la experiencia tecnológica de nuestros clientes, ofreciendo soluciones integrales que impulsen su crecimiento y desarrollo.</p>
          </div>
          <div class="about-card" data-aos="fade-up" data-aos-delay="100">
            <h2><i class="fas fa-bullseye"></i> Nuestra Misión</h2>
            <p>Proporcionar soluciones tecnológicas integrales y personalizadas, adaptadas a las necesidades específicas de cada cliente. Nos comprometemos a ofrecer productos de la más alta calidad, respaldados por un servicio excepcional y un soporte técnico especializado.</p>
          </div>
          <div class="about-card" data-aos="fade-up" data-aos-delay="200">
            <h2><i class="fas fa-star"></i> Nuestros Valores</h2>
            <p>Innovación constante, excelencia en el servicio, integridad en nuestras operaciones, compromiso con la calidad y pasión por la tecnología. Estos valores guían cada decisión y acción en SYSCOTEL, asegurando una experiencia excepcional para nuestros clientes.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="stats-section" data-aos="fade-up">
      <div class="stat-card" data-aos="zoom-in">
        <div class="stat-number">10+</div>
        <div class="stat-label">Años de Experiencia</div>
      </div>
      <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
        <div class="stat-number">5000+</div>
        <div class="stat-label">Clientes Satisfechos</div>
      </div>
      <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
        <div class="stat-number">1000+</div>
        <div class="stat-label">Productos Disponibles</div>
      </div>
      <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
        <div class="stat-number">24/7</div>
        <div class="stat-label">Soporte Técnico</div>
      </div>
    </section>

    <section class="team-section" data-aos="fade-up">
      <h2>Nuestro Equipo</h2>
      <div class="team-grid">
        <div class="team-member" data-aos="fade-up">
          <img src="img/a.jpg" alt="CEO">
          <div class="team-info">
            <h3>Papi Dani</h3>
            <p>CEO & Fundador</p>
            <div class="social-links">
              <a href="#"><i class="fab fa-linkedin"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-facebook"></i></a>
            </div>
          </div>
        </div>
        <div class="team-member" data-aos="fade-up" data-aos-delay="100">
          <img src="https://pm1.aminoapps.com/5765/a32bd33249e602f18a08e4b7686f9a499f0ebeeb_hq.jpg" alt="CTO">
          <div class="team-info">
            <h3>Jane Doe</h3>
            <p>CTO & Innovación</p>
            <div class="social-links">
              <a href="#"><i class="fab fa-linkedin"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-facebook"></i></a>
            </div>
          </div>
        </div>
        <div class="team-member" data-aos="fade-up" data-aos-delay="200">
          <img src="https://pm1.aminoapps.com/5765/a32bd33249e602f18a08e4b7686f9a499f0ebeeb_hq.jpg" alt="COO">
          <div class="team-info">
            <h3>Jhon Doe</h3>
            <p>COO & Operaciones</p>
            <div class="social-links">
              <a href="#"><i class="fab fa-linkedin"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-facebook"></i></a>
            </div>
          </div>
        </div>
      </div>
    </section>
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

  <a href="index.php" class="floating-help" title="Volver al inicio">
    <i class="fas fa-home"></i>
  </a>

  <script src="js/main.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="js/cart.js"></script>
  <script>
    // Make sure AOS is initialized properly
    document.addEventListener('DOMContentLoaded', function() {
      AOS.init({
        duration: 1000,
        once: true
      });
      
      // Force content visibility
      document.querySelector('.main-content').style.display = 'block';
    });
  </script>
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
            
            document.getElementById('quickViewTitle').innerText = title;
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


