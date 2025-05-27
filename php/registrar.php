<?php
session_start();
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
  <title>Registro – Syscotel</title>
  <link rel="icon" href="../img/syscotel.png" type="image/png">
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
      max-width: 450px;
      background: rgba(20, 20, 20, 0.95);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 1rem;
      backdrop-filter: blur(10px);
    }

    .input-group-text {
      background: var(--primary-color);
      border: none;
      min-width: 45px;
    }

    .form-control {
      background: rgba(255,255,255,0.05);
      border: none;
      color: #fff !important;
    }

    .logo {
      width: 80px;
      height: 80px;
      border-radius: 20px;
      overflow: hidden;
    }

    .alert {
      background-color: rgba(220, 53, 69, 0.2);
      border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .sugerencias-container {
      background: rgba(255,255,255,0.05);
      border-radius: 0.5rem;
      padding: 0.75rem;
      border: 1px solid rgba(255,255,255,0.1);
    }

    .sugerencia-btn {
      transition: all 0.2s ease;
      font-size: 0.875rem;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      background: rgba(13, 110, 253, 0.1);
      border: 1px solid rgba(13, 110, 253, 0.3);
    }

    .sugerencia-btn:hover {
      background: rgba(13, 110, 253, 0.2);
      transform: translateY(-1px);
    }

    .sugerencia-btn i {
      font-size: 0.75rem;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
  <div class="auth-card p-4 w-100">
    <div class="text-center mb-4">
      <div class="logo d-flex align-items-center justify-content-center m-auto">
        <img src="../img/syscotel.png" alt="Syscotel Logo" class="img-fluid">
      </div>
      <h2 class="fw-bold mt-3">Crear nueva cuenta</h2>
      <p class="text-muted">Regístrate para comenzar</p>
    </div>

    <form action="procesar_registro.php" method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="nombre" class="form-control form-control-lg" 
                 placeholder="Nombre de usuario" required
                 value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>">
          <div class="invalid-feedback">Usuario requerido (mínimo 4 caracteres)</div>
        </div>
        <?php if (isset($_GET['sugerencias'])): ?>
        <div class="mt-2 sugerencias-container">
          <small class="text-muted d-block mb-1">Sugerencias disponibles:</small>
          <div class="d-flex gap-2 flex-wrap">
            <?php 
            $sugerencias = explode(',', $_GET['sugerencias']);
            foreach ($sugerencias as $index => $sugerencia) {
              echo '<button type="button" class="btn btn-outline-primary btn-sm sugerencia-btn" data-sugerencia="' . htmlspecialchars($sugerencia) . '">
                      <i class="fas fa-magic me-1"></i>' . htmlspecialchars($sugerencia) . '
                    </button>';
            }
            ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      
      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          <input type="email" name="email" class="form-control form-control-lg" 
                 placeholder="Correo electrónico" required
                 value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
          <div class="invalid-feedback">Correo electrónico válido requerido (se aceptan correos internacionales)</div>
        </div>
      </div>
      
      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="contrasena" class="form-control form-control-lg" 
                 placeholder="Contraseña" required minlength="8"
                 value="<?php echo isset($_GET['contrasena']) ? htmlspecialchars($_GET['contrasena']) : ''; ?>">
          <div class="invalid-feedback">Mínimo 8 caracteres requeridos</div>
        </div>
      </div>
      
      <div class="mb-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="confirmar_contrasena" class="form-control form-control-lg" 
                 placeholder="Confirmar contraseña" required
                 value="<?php echo isset($_GET['confirmar_contrasena']) ? htmlspecialchars($_GET['confirmar_contrasena']) : ''; ?>">
          <div class="invalid-feedback">Las contraseñas deben coincidir</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">Registrarse ahora</button>
      
      <div class="text-center">
        <a href="login.php" class="text-decoration-none text-white-50">¿Ya tienes cuenta? Inicia sesión</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Agregar funcionalidad para las sugerencias
    document.querySelectorAll('.sugerencia-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelector('input[name="nombre"]').value = this.dataset.sugerencia;
        // Opcional: dar feedback visual
        this.classList.add('btn-primary');
        this.classList.remove('btn-outline-primary');
        setTimeout(() => {
          this.classList.remove('btn-primary');
          this.classList.add('btn-outline-primary');
        }, 200);
      });
    });

    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      
      const validatePassword = () => {
        const password = document.querySelector('input[name="contrasena"]')
        const confirm = document.querySelector('input[name="confirmar_contrasena"]')
        
        if (password.value !== confirm.value) {
          confirm.setCustomValidity('Las contraseñas no coinciden')
        } else {
          confirm.setCustomValidity('')
        }
      }
      
      // Actualizar validación de nombre de usuario
      const validateUsername = () => {
        const username = document.querySelector('input[name="nombre"]')
        if (username.value.length < 4) {
          username.setCustomValidity('El nombre debe tener al menos 4 caracteres')
        } else if (username.value.length > 30) {
          username.setCustomValidity('El nombre debe tener máximo 30 caracteres')
        } else {
          username.setCustomValidity('')
        }
      }
      
      // Función para validar correos electrónicos internacionalizados
      const validateEmail = () => {
        const emailInput = document.querySelector('input[name="email"]')
        const email = emailInput.value.trim()
        
        // Validación básica
        if (email === '') {
          emailInput.setCustomValidity('El correo electrónico es requerido')
          return
        }
        
        // Verificar formato básico
        if (email.indexOf('@') === -1) {
          emailInput.setCustomValidity('El correo electrónico debe contener @')
          return
        }
        
        // Dividir en parte local y dominio
        const [localPart, domain] = email.split('@')
        
        // Verificar longitudes
        if (localPart.length > 64 || domain.length > 255) {
          emailInput.setCustomValidity('El correo electrónico es demasiado largo')
          return
        }
        
        // Validación básica de formato
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          emailInput.setCustomValidity('Formato de correo electrónico inválido')
          return
        }
        
        // Si pasa todas las validaciones
        emailInput.setCustomValidity('')
      }
      
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          validateUsername()
          validatePassword()
          validateEmail() // Agregar validación de email
          
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
        
        form.querySelector('input[name="nombre"]')
           .addEventListener('input', validateUsername)
        
        form.querySelector('input[name="confirmar_contrasena"]')
           .addEventListener('input', validatePassword)
        
        form.querySelector('input[name="email"]')
           .addEventListener('input', validateEmail)
      })
    })()

    <?php
    if(isset($_GET['error'])) {
      $error = $_GET['error'];
      $titulo = 'Error';
      $mensaje = '';
      
      if ($error === 'exists') {
        $titulo = 'Usuario existente';
        $mensaje = 'El nombre de usuario ya está registrado. Por favor, elija uno de los nombres sugeridos.';
      } else if ($error === 'db') {
        $titulo = 'Error en la base de datos';
        $mensaje = 'Ocurrió un error al registrar el usuario. Inténtelo de nuevo.';
      } else if ($error === 'invalid_method') {
        $titulo = 'Método inválido';
        $mensaje = 'Método de solicitud inválido.';
      } else {
        $errores = explode(',', $error);
        $titulo = 'Error de validación';
        
        if (in_array('nombre', $errores)) {
          $mensaje .= '• El nombre de usuario debe tener entre 4 y 30 caracteres.<br>';
        }
        
        if (in_array('email', $errores)) {
          $mensaje .= '• El formato del correo electrónico no es válido. Se aceptan correos internacionales.<br>';
        }
        
        if (in_array('password', $errores)) {
          $mensaje .= '• La contraseña debe tener al menos 8 caracteres y ambas contraseñas deben coincidir.';
        }
        
        if (in_array('email_exists', $errores)) {
          $mensaje .= '• El correo electrónico ya está registrado. Por favor, utilice otro correo.<br>';
        }
      }

      echo "
      Swal.fire({
        icon: 'error',
        title: '$titulo',
        html: '$mensaje',
        confirmButtonColor: '#0d6efd',
        background: 'rgba(20, 20, 20, 0.95)',
        color: '#fff'
      });
      ";
    }
    ?>
  </script>
</body>
</html>







