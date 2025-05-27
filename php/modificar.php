<?php
// Agregar al principio del archivo
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en la salida
ob_start(); // Iniciar buffer de salida para evitar salidas accidentales

session_start();

if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header('Location: ../index.php');
    exit();
}

include 'conexion.php';

// Función para registrar errores en un archivo de log
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, '../logs/error.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar cualquier salida anterior
    ob_clean();
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $seccion = $_POST['seccion'] ?? '';
    $apartado = $_POST['apartado'] ?? '';
    $es_popular = isset($_POST['es_popular']) ? 1 : 0;

    // Obtener la imagen actual
    $stmt = $conn->prepare("SELECT imagen, apartado, seccion FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $imagen_actual = $row['imagen'];
    $apartado_actual = $row['apartado'];
    $seccion_actual = $row['seccion'];
    $stmt->close();
    
    // Variables para las rutas
    $base_dir = "../img/";
    $ruta_relativa_nueva = "";
    $ruta_relativa_anterior = "";
    
    // Determinar la ruta anterior
    switch ($apartado_actual) {
        case 'Gaming':
            switch ($seccion_actual) {
                case 'Perifericos': $ruta_relativa_anterior = "perifericos/"; break;
                case 'Consolas': $ruta_relativa_anterior = "consolas/"; break;
                case 'Equipos': $ruta_relativa_anterior = "equipos/"; break;
                default: $ruta_relativa_anterior = "gaming/"; break;
            }
            break;
        case 'Moviles':
            switch ($seccion_actual) {
                case 'Audifonos': $ruta_relativa_anterior = "audifonos/"; break;
                case 'Celulares': $ruta_relativa_anterior = "celulares/"; break;
                case 'Gadgets': $ruta_relativa_anterior = "gadgets/"; break;
                default: $ruta_relativa_anterior = "moviles/"; break;
            }
            break;
        case 'Varios':
            switch ($seccion_actual) {
                case 'Seguridad': $ruta_relativa_anterior = "seguridad/"; break;
                case 'Unidades': $ruta_relativa_anterior = "unidades/"; break;
                case 'Varios': $ruta_relativa_anterior = "varios/"; break;
                default: $ruta_relativa_anterior = "varios/"; break;
            }
            break;
        default:
            $ruta_relativa_anterior = "otros/";
            break;
    }
    
    // Determinar la nueva ruta
    switch ($apartado) {
        case 'Gaming':
            switch ($seccion) {
                case 'Perifericos': $ruta_relativa_nueva = "perifericos/"; break;
                case 'Consolas': $ruta_relativa_nueva = "consolas/"; break;
                case 'Equipos': $ruta_relativa_nueva = "equipos/"; break;
                default: $ruta_relativa_nueva = "gaming/"; break;
            }
            break;
        case 'Moviles':
            switch ($seccion) {
                case 'Audifonos': $ruta_relativa_nueva = "audifonos/"; break;
                case 'Celulares': $ruta_relativa_nueva = "celulares/"; break;
                case 'Gadgets': $ruta_relativa_nueva = "gadgets/"; break;
                default: $ruta_relativa_nueva = "moviles/"; break;
            }
            break;
        case 'Varios':
            switch ($seccion) {
                case 'Seguridad': $ruta_relativa_nueva = "seguridad/"; break;
                case 'Unidades': $ruta_relativa_nueva = "unidades/"; break;
                case 'Varios': $ruta_relativa_nueva = "varios/"; break;
                default: $ruta_relativa_nueva = "varios/"; break;
            }
            break;
        default:
            $ruta_relativa_nueva = "otros/";
            break;
    }

    // Manejar la nueva imagen si se subió una
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $nombre_archivo = $imagen['name'];
        $tipo_archivo = $imagen['type'];
        $tamano_archivo = $imagen['size'];
        $tmp_archivo = $imagen['tmp_name'];

        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.']);
            exit();
        }

        // Validar tamaño (máximo 5MB)
        if ($tamano_archivo > 5 * 1024 * 1024) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'La imagen es demasiado grande. El tamaño máximo permitido es 5MB.']);
            exit();
        }

        // Generar nombre único para la imagen
        $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        $nuevo_nombre = uniqid() . '.' . $extension;
        
        // Asegurarse de que el directorio de destino exista
        if (!is_dir($base_dir . $ruta_relativa_nueva)) {
            mkdir($base_dir . $ruta_relativa_nueva, 0777, true);
        }
        
        $ruta_destino = $base_dir . $ruta_relativa_nueva . $nuevo_nombre;

        if (move_uploaded_file($tmp_archivo, $ruta_destino)) {
            // Eliminar la imagen anterior si existe y no es placeholder
            if ($imagen_actual && $imagen_actual !== 'placeholder.jpg') {
                $ruta_imagen_anterior = $base_dir . $ruta_relativa_anterior . $imagen_actual;
                if (file_exists($ruta_imagen_anterior)) {
                    unlink($ruta_imagen_anterior);
                }
            }
            $imagen_actual = $nuevo_nombre;
        } else {
            logError("Error al subir la imagen: " . error_get_last()['message']);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
            exit();
        }
    } else if ($imagen_actual && $imagen_actual !== 'placeholder.jpg' && ($apartado_actual !== $apartado || $seccion_actual !== $seccion)) {
        // Si no se subió una nueva imagen pero se cambió la sección, mover la imagen existente
        $ruta_imagen_anterior = $base_dir . $ruta_relativa_anterior . $imagen_actual;
        $ruta_imagen_nueva = $base_dir . $ruta_relativa_nueva . $imagen_actual;
        
        // Asegurarse de que el directorio de destino exista
        if (!is_dir($base_dir . $ruta_relativa_nueva)) {
            mkdir($base_dir . $ruta_relativa_nueva, 0777, true);
        }
        
        if (file_exists($ruta_imagen_anterior)) {
            // Copiar la imagen a la nueva ubicación y luego eliminar la original
            if (copy($ruta_imagen_anterior, $ruta_imagen_nueva)) {
                unlink($ruta_imagen_anterior);
            } else {
                logError("Error al mover la imagen: " . error_get_last()['message']);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Error al mover la imagen a la nueva ubicación.']);
                exit();
            }
        } else {
            logError("Imagen anterior no encontrada: " . $ruta_imagen_anterior);
        }
    }

    // Actualizar el producto en la base de datos
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, seccion = ?, apartado = ?, es_popular = ?, imagen = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssisi", $nombre, $descripcion, $precio, $stock, $seccion, $apartado, $es_popular, $imagen_actual, $id);

    // Asegurarse de enviar headers antes de cualquier salida
    header('Content-Type: application/json');

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Producto actualizado correctamente',
            'imagen_actual' => $imagen_actual,
            'ruta_imagen' => $ruta_relativa_nueva . $imagen_actual
        ]);
    } else {
        logError("Error al actualizar el producto: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el producto: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD']=='GET' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $row = $conn->query("SELECT * FROM productos WHERE id=$id")->fetch_assoc();
  if (!$row) {
    header('Location: admin_productos.php');
    exit();
  }
} else {
  header('Location: admin_productos.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modificar Producto</title>
  <link rel="shortcut icon" href="../img/syscotel.png" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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

    h1 {
      color: var(--brand);
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      font-weight: 600;
      border-bottom: 2px solid var(--brand);
      padding-bottom: 0.5rem;
    }

    form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .form-group label {
      font-weight: 500;
      color: var(--text-dark);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid var(--input-border);
      border-radius: 8px;
      background: var(--input-bg);
      color: var(--text-dark);
      transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }

    .form-group textarea {
      resize: none;
      height: 100px;
      overflow-y: auto;
    }

    .image-preview {
      text-align: center;
      padding: 1rem;
      border: 2px dashed var(--input-border);
      border-radius: 10px;
      transition: var(--transition);
      background: var(--bg-light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 200px;
    }

    .image-preview img {
      max-width: 100%;
      max-height: 200px;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
      justify-content: flex-end;
      margin-top: 1rem;
      grid-column: 1 / -1;
    }

    .checkbox-container {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      background: transparent;
      border: none;
      margin: 0;
    }

    .checkbox-container input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: var(--brand);
      margin: 0;
    }

    .checkbox-container label {
      cursor: pointer;
      user-select: none;
      font-weight: 500;
      color: var(--text-dark);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      form {
        grid-template-columns: 1fr;
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
    <h1>Modificar Producto</h1>
    <button class="btn btn-primary" onclick="location.href='agregar.php'" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 500; transition: all 0.3s ease; background: transparent; border: 2px solid #fff;">
      <i class="fas fa-arrow-left"></i>
      <span>Regresar</span>
    </button>
  </nav>

  <main class="container">
    <section class="card">
      <form id="editForm" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="accion" value="actualizar">
        
        <div class="form-group">
          <label for="nombre"><i class="fas fa-tag"></i> Nombre del Producto</label>
          <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required>
        </div>

        <div class="form-group">
          <label for="precio"><i class="fas fa-dollar-sign"></i> Precio</label>
          <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $row['precio']; ?>" required>
        </div>

        <div class="form-group">
          <label for="stock"><i class="fas fa-boxes"></i> Stock</label>
          <input type="number" id="stock" name="stock" min="0" value="<?php echo $row['stock']; ?>" required>
        </div>

        <div class="form-group">
          <label for="apartado"><i class="fas fa-folder"></i> Apartado</label>
          <select name="apartado" id="apartado" onchange="actualizarSecciones()" required>
            <?php
              $aparts = ['Gaming','Moviles','Varios'];
              foreach($aparts as $a) {
                $sel = isset($row['apartado']) && $row['apartado']==$a ? ' selected' : '';
                echo "<option{$sel}>{$a}</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label for="seccion"><i class="fas fa-tags"></i> Sección</label>
          <select name="seccion" id="seccion" required>
            <?php echo "<option selected>{$row['seccion']}</option>"; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
          <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($row['descripcion']); ?></textarea>
        </div>

        <div class="form-group checkbox-container">
          <input type="checkbox" id="es_popular" name="es_popular" value="1" <?php echo ($row['es_popular'] ? 'checked' : ''); ?>>
          <label for="es_popular">
            <i class="fas fa-star"></i>
            Marcar como producto popular
          </label>
        </div>

        <div class="form-group">
          <label for="currentImagen"><i class="fas fa-image"></i> Imagen Actual</label>
          <div class="image-preview">
            <?php
            $base_dir = "../img/";
            $seccion = $row['seccion'];
            $imagen_nombre = $row['imagen'];
            
            // Si es placeholder.jpg, usar la ruta base
            if ($imagen_nombre === 'placeholder.jpg') {
                $ruta = $base_dir . $imagen_nombre;
            } else {
                if ($seccion == 'Perifericos') $ruta = $base_dir . "perifericos/" . $imagen_nombre;
                else if ($seccion == 'Gadgets') $ruta = $base_dir . "gadgets/" . $imagen_nombre;
                else if ($seccion == 'Celulares') $ruta = $base_dir . "celulares/" . $imagen_nombre;
                else if ($seccion == 'Audifonos') $ruta = $base_dir . "audifonos/" . $imagen_nombre;
                else if ($seccion == 'Seguridad') $ruta = $base_dir . "seguridad/" . $imagen_nombre;
                else if ($seccion == 'Unidades') $ruta = $base_dir . "unidades/" . $imagen_nombre;
                else if ($seccion == 'Varios') $ruta = $base_dir . "varios/" . $imagen_nombre;
                else if ($seccion == 'Consolas') $ruta = $base_dir . "consolas/" . $imagen_nombre;
                else if ($seccion == 'Equipos') $ruta = $base_dir . "equipos/" . $imagen_nombre;
                else $ruta = $base_dir . $imagen_nombre;
            }
            ?>
            <img id="currentImagen" src="<?php echo $ruta; ?>" alt="Imagen actual">
          </div>
        </div>

        <div class="form-group">
          <label for="newImagen"><i class="fas fa-upload"></i> Cambiar imagen</label>
          <input type="file" id="newImagen" name="imagen" accept="image/*">
          <div class="image-preview">
            <img id="previewNewImagen" src="#" alt="Vista previa" style="display:none;">
            <div class="preview-placeholder">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Vista previa de la nueva imagen</p>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Actualizar Producto
          </button>
          <a href="agregar.php" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
          </a>
        </div>
      </form>
    </section>
  </main>

<script>
  function actualizarSecciones() {
    const apartado = document.getElementById('apartado').value;
    const seccionSelect = document.getElementById('seccion');
    const currentSeccion = "<?php echo $row['seccion']; ?>";
    
    seccionSelect.innerHTML = '';
    
    if (apartado === 'Gaming') {
      const secciones = ['Perifericos', 'Consolas', 'Equipos'];
      secciones.forEach(seccion => {
        const option = document.createElement('option');
        option.textContent = seccion;
        if (seccion === currentSeccion) option.selected = true;
        seccionSelect.appendChild(option);
      });
    } else if (apartado === 'Moviles') {
      const secciones = ['Audifonos', 'Celulares', 'Gadgets'];
      secciones.forEach(seccion => {
        const option = document.createElement('option');
        option.textContent = seccion;
        if (seccion === currentSeccion) option.selected = true;
        seccionSelect.appendChild(option);
      });
    } else if (apartado === 'Varios') {
      const secciones = ['Seguridad', 'Unidades', 'Varios'];
      secciones.forEach(seccion => {
        const option = document.createElement('option');
        option.textContent = seccion;
        if (seccion === currentSeccion) option.selected = true;
        seccionSelect.appendChild(option);
      });
    }
  }
  
  window.onload = function() {
    actualizarSecciones();
  };

  document.getElementById('newImagen').addEventListener('change', function(){
    const [file] = this.files;
    if (file) {
      const prev = document.getElementById('previewNewImagen');
      prev.src = URL.createObjectURL(file);
      prev.style.display = 'block';
    }
  });

  document.getElementById('editForm').addEventListener('submit', function(e){
    e.preventDefault();
    const missing = [];
    ['nombre','precio','apartado','seccion','descripcion'].forEach(name=>{
      if (!this[name].value.trim()) missing.push(name.charAt(0).toUpperCase()+name.slice(1));
    });
    if (missing.length) {
      Swal.fire({
        icon:'error',
        title:'Campos incompletos',
        text:'Faltan: '+missing.join(','),
        confirmButtonColor:getComputedStyle(document.documentElement).getPropertyValue('--brand').trim()
      });
      return;
    }
    
    const formData = new FormData(this);
    
    fetch('modificar.php',{ method:'POST', body:formData })
      .then(response => {
        if (!response.ok) {
          throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
      })
      .then(data => {
        Swal.fire({
          icon: data.status === 'success' ? 'success' : 'error',
          title: data.status === 'success' ? '¡Actualizado!' : '¡Error!',
          text: data.message,
          confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--brand').trim(),
          timer: data.status === 'success' ? 1000 : undefined,
          showConfirmButton: data.status === 'success' ? false : true
        });

        if (data.status === 'success') {
          // Actualizar la imagen actual si se movió
          if (data.imagen_actual) {
            const currentImagen = document.getElementById('currentImagen');
            if (currentImagen) {
              currentImagen.src = data.imagen_actual;
            }
          }
          setTimeout(() => {
            window.location.href = 'agregar.php';
          }, 1000);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo actualizar el producto. Por favor, intente nuevamente.',
          confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--brand').trim()
        });
      });
  });
</script>
</body>
</html>



