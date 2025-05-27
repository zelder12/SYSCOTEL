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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM login WHERE id=$id AND admin=0";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: index.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Usuario – Syscotel</title>
  <link rel="shortcut icon" href="../img/syscotel.png" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --brand: #2c3e50;
      --brand-dark: #1a252f;
      --danger: #dc3545;
      --success: #28a745;
      --bg-light: #f8f9fa;
      --bg-white: #fff;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --input-bg: #fff;
      --input-border: #dee2e6;
      --card-shadow: 0 2px 15px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }

    * { 
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .navbar {
      background: linear-gradient(135deg, #2c3e50, #1a252f);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .navbar h1 {
      color: var(--bg-white);
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .card {
      background: var(--bg-white);
      border-radius: 10px;
      box-shadow: var(--card-shadow);
      padding: 2rem;
      margin-bottom: 2rem;
    }

    h2 {
      color: var(--brand);
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      font-weight: 600;
      border-bottom: 2px solid var(--brand);
      padding-bottom: 0.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--text-dark);
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--input-border);
      border-radius: 8px;
      background: var(--input-bg);
      color: var(--text-dark);
      transition: var(--transition);
    }

    .form-group input:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      font-weight: 500;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: var(--transition);
      text-decoration: none;
      gap: 0.5rem;
    }

    .btn-primary {
      background: var(--brand);
      color: white;
    }

    .btn-primary:hover {
      background: var(--brand-dark);
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: var(--danger);
      color: white;
    }

    .btn-secondary:hover {
      background: #c82333;
      transform: translateY(-2px);
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      .form-actions {
        flex-direction: column;
      }

      .form-actions .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <h1>Modificar Usuario</h1>
    <button class="btn btn-primary" onclick="location.href='index.php'" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 500; transition: all 0.3s ease; background: transparent; border: 2px solid #fff;">
      <i class="fas fa-arrow-left"></i>
      <span>Regresar</span>
    </button>
  </nav>

  <main class="container">
    <section class="card">
      <h2><i class="fas fa-user-edit"></i> Editar Información del Usuario</h2>
      <form action="usuarios_manager.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="accion" value="actualizar">
        
        <div class="form-group">
          <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
          <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required>
        </div>

        <div class="form-group">
          <label for="email"><i class="fas fa-envelope"></i> Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Guardar Cambios
          </button>
          <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
          </a>
        </div>
      </form>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.querySelector('form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      formData.append('ajax', '1');
      
      fetch('usuarios_manager.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: data.message,
            confirmButtonColor: 'var(--brand)'
          }).then(() => {
            window.location.href = 'index.php';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonColor: 'var(--brand)'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ocurrió un error al procesar la solicitud',
          confirmButtonColor: 'var(--brand)'
        });
      });
    });
  </script>
</body>
</html>


