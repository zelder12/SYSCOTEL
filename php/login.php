<?php
session_start();
// Asegurar que PHP maneje correctamente UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if (isset($_SESSION['nombre'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión – Syscotel</title>
  <link rel="icon" href="ruta/a/tu/favicon.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://use.fontawesome.com/releases/v6.4.0/css/all.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary-color: #0d6efd;
      --primary-hover: #0b5ed7;
    }

    body {
      background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('../img/image.jpg') center/cover fixed;
      min-height: 100vh;
    }

    .auth-card {
      max-width: 400px;
      background: rgba(20, 20, 20, 0.95);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 1rem;
      backdrop-filter: blur(10px);
      transition: transform 0.3s ease;
    }

    .auth-card:hover {
      transform: translateY(-5px);
    }

    .input-group-text {
      background: var(--primary-color);
      border: none;
      min-width: 45px;
      justify-content: center;
    }

    .form-control {
      background: rgba(255,255,255,0.05);
      border: none;
      color: #fff !important;
    }

    .form-control:focus {
      background: rgba(255,255,255,0.1);
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }

    .btn-primary {
      background: var(--primary-color);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.05em;
    }

    .btn-primary:hover {
      background: var(--primary-hover);
    }

    .auth-links a {
      color: rgba(255,255,255,0.6);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .auth-links a:hover {
      color: var(--primary-color);
    }

    .logo {
      width: 80px;
      height: 80px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }

    .logo img {
      max-width: 100%;
      max-height: 100%;
      border-radius: 20px;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
  <div class="auth-card p-4 w-100">
    <div class="text-center mb-4">
      <div class="logo">
        <img src="../img/syscotel.png" alt="Syscotel Logo">
      </div>
      <h2 class="fw-bold">Bienvenido a Syscotel</h2>
      <p class="text-muted">Inicia sesión para continuar</p>
    </div>

    <form action="validacion.php" method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="nombre" class="form-control form-control-lg" 
                 placeholder="Usuario" required>
          <div class="invalid-feedback">Ingresa tu usuario</div>
        </div>
      </div>

      <div class="mb-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" class="form-control form-control-lg" 
                 placeholder="Contraseña" required>
          <div class="invalid-feedback">Ingresa tu contraseña</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg mb-4">Acceder al sistema</button>

      <div class="d-flex flex-column gap-2">
        <a href="registrar.php" class="btn btn-outline-light w-100 btn-lg">
          <i class="fas fa-user-plus me-2"></i> Crear nueva cuenta
        </a>
        <a href="../index.php" class="btn btn-outline-secondary w-100 btn-lg">
          <i class="fas fa-sign-in-alt me-2"></i> Continuar sin cuenta
        </a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()

    <?php
    if(isset($_GET['error'])) {
      $error = $_GET['error'];
      $mensaje = '';
      $titulo = 'Error';
      
      switch($error) {
        case '1':
          $mensaje = 'Por favor, verifica tu usuario y contraseña';
          $titulo = 'Error de acceso';
          break;
        case '2':
          $mensaje = 'No encontramos ninguna cuenta con ese nombre de usuario';
          break;
        case '3':
          $mensaje = 'La contraseña ingresada no es correcta';
          break;
        default:
          $mensaje = 'Ha ocurrido un error desconocido';
      }

      echo "
      Swal.fire({
        icon: 'error',
        title: '$titulo',
        text: '$mensaje',
        confirmButtonColor: '#0d6efd',
        background: 'rgba(20, 20, 20, 0.95)',
        color: '#fff'
      })
      ";
    }
    ?>
  </script>
</body>
</html>


