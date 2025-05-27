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
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: ../php/login.php");
    exit();
}

include("connection.php");
$con = connection();

$sql = "SELECT * FROM login WHERE admin=0";
$query = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Users CRUD – Syscotel</title>
  <link rel="shortcut icon" href="../img/syscotel.png" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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

    .table-container {
      overflow-x: auto;
      margin-top: 1rem;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: var(--bg-white);
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--input-border);
    }

    th {
      background: var(--brand);
      color: white;
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.9rem;
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr:hover {
      background: rgba(30, 136, 229, 0.05);
    }

    .action-cell {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-start;
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

    .users-table--edit,
    .users-table--delete {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 8px;
      transition: var(--transition);
      color: white;
      text-decoration: none;
    }

    .users-table--edit {
      background: var(--brand);
    }

    .users-table--edit:hover {
      background: var(--brand-dark);
      transform: translateY(-2px);
    }

    .users-table--delete {
      background: var(--danger);
    }

    .users-table--delete:hover {
      background: #c82333;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      .action-cell {
        flex-direction: column;
      }

      .action-cell .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <h1>Panel de Administración</h1>
    <button class="btn btn-primary" onclick="location.href='../admin.php'" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 500; transition: all 0.3s ease; background: transparent; border: 2px solid #fff;">
      <i class="fas fa-arrow-left"></i>
      <span>Regresar</span>
    </button>
  </nav>

  <main class="container">
    <section class="card">
      <h2><i class="fas fa-users"></i> Listado de Usuarios</h2>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($query)): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td class="action-cell">
                  <a href="update.php?id=<?= $row['id'] ?>" class="users-table--edit" title="Editar usuario">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="#" class="users-table--delete" data-id="<?= $row['id'] ?>" title="Eliminar usuario">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script>
    document.querySelectorAll('.users-table--delete').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        const id = btn.dataset.id;
        Swal.fire({
          title: '¿Estás seguro?',
          text: '¡No podrás revertir esta acción!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: 'var(--brand)',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) {
            window.location.href = 'usuarios_manager.php?accion=eliminar&id=' + id;
          }
        });
      });
    });
  </script>

</body>
</html>




