<?php
// Desactivar la visualización de errores en la salida
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar buffer de salida
ob_start();

session_start();
require __DIR__ . '/../vendor/autoload.php';
require 'conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Limpiar cualquier búfer de salida existente
ob_clean();

// Configurar encabezados para JSON
header('Content-Type: application/json');

// Verificar si el usuario está autenticado y es admin
if (!isset($_SESSION['nombre']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

// Verificar si se subió un archivo
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No se subió ningún archivo o hubo un error']);
    exit;
}

// Definir la ruta de la imagen placeholder original
$placeholder_original_path = '../img/placeholder.jpg';
// Asegurarse de que el directorio base de imágenes exista
$base_img_dir = '../img/';
if (!is_dir($base_img_dir)) {
    if (!mkdir($base_img_dir, 0777, true)) {
        error_log("Error al crear directorio base de imágenes: $base_img_dir");
        // Continuar script, pero la importación de imágenes fallará o usará rutas incorrectas
    }
}

// Verificar/Crear la imagen placeholder original si no existe y el directorio base es escribible
if (is_dir($base_img_dir) && is_writable($base_img_dir)) {
    if (!file_exists($placeholder_original_path)) {
        // Crear una imagen placeholder básica
        $width = 600;
        $height = 400;
        // Verificar si las funciones GD están disponibles
        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor($width, $height);

            // Colores
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 100, 100, 100);
            $border_color = imagecolorallocate($image, 200, 200, 200);

            // Rellenar fondo
            imagefill($image, 0, 0, $bg_color);

            // Dibujar borde
            imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

            // Texto
            $text = "Imagen no disponible";
            $font = 5; // Fuente incorporada

            // Centrar texto
            $text_width = imagefontwidth($font) * strlen($text);
            $text_height = imagefontheight($font);
            $text_x = ($width - $text_width) / 2;
            $text_y = ($height - $text_height) / 2;

            // Escribir texto
            imagestring($image, $font, $text_x, $text_y, $text, $text_color);

            // Guardar imagen
            if (imagejpeg($image, $placeholder_original_path, 90)) {
                error_log("Imagen placeholder original creada en: $placeholder_original_path");
            } else {
                 error_log("Error al guardar la imagen placeholder original en: $placeholder_original_path");
            }
            imagedestroy($image);
        }
    }
}

// Función para asegurar que el placeholder esté disponible
function asegurarPlaceholder() {
    $placeholder_original = '../img/placeholder.jpg';
    
    // Crear una imagen placeholder básica si no existe
    if (!file_exists($placeholder_original)) {
        $width = 600;
        $height = 400;
        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor($width, $height);
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 100, 100, 100);
            $border_color = imagecolorallocate($image, 200, 200, 200);

            imagefill($image, 0, 0, $bg_color);
            imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

            $text = "Imagen no disponible";
            $font = 5;
            $text_width = imagefontwidth($font) * strlen($text);
            $text_height = imagefontheight($font);
            $text_x = ($width - $text_width) / 2;
            $text_y = ($height - $text_height) / 2;

            imagestring($image, $font, $text_x, $text_y, $text, $text_color);
            imagejpeg($image, $placeholder_original, 90);
            imagedestroy($image);
        }
    }
}

// Función para generar un nombre único para el placeholder
function generarNombrePlaceholder($nombre_producto) {
    // Limpiar el nombre del producto para usarlo en el nombre del archivo
    $nombre_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nombre_producto));
    $nombre_limpio = substr($nombre_limpio, 0, 30); // Limitar longitud
    return 'placeholder_' . $nombre_limpio . '_' . uniqid() . '.jpg';
}

// Función para descargar y guardar la imagen
function descargarImagen($url, $apartado, $seccion, $nombre_producto) {
    try {
        // Verificar que la URL sea válida
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            error_log("URL de imagen inválida: $url");
            return generarNombrePlaceholder($nombre_producto);
        }

        // Intentar descargar la imagen
        $imagen = @file_get_contents($url);
        if ($imagen === false) {
            error_log("No se pudo descargar la imagen de: $url");
            return generarNombrePlaceholder($nombre_producto);
        }

        // Verificar que el contenido sea una imagen válida
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($imagen);

        if (strpos($mime_type, 'image/') !== 0) {
            error_log("El contenido descargado no es una imagen válida (MIME: $mime_type)");
            return generarNombrePlaceholder($nombre_producto);
        }

        // Contenido válido, intentar guardar
        $extension = '';
        switch ($mime_type) {
            case 'image/jpeg': $extension = 'jpg'; break;
            case 'image/png': $extension = 'png'; break;
            case 'image/gif': $extension = 'gif'; break;
            case 'image/webp': $extension = 'webp'; break;
            default: $extension = 'jpg'; break;
        }

        $nombre_archivo = 'producto_' . time() . '_' . uniqid() . '.' . $extension;
        
        // Determinar la ruta relativa según el apartado y sección
        $ruta_relativa = "";
        switch ($apartado) {
            case 'Gaming':
                switch ($seccion) {
                    case 'Perifericos': $ruta_relativa = "perifericos/"; break;
                    case 'Consolas': $ruta_relativa = "consolas/"; break;
                    case 'Equipos': $ruta_relativa = "equipos/"; break;
                    default: $ruta_relativa = "gaming/"; break;
                }
                break;
            case 'Moviles':
                switch ($seccion) {
                    case 'Audifonos': $ruta_relativa = "audifonos/"; break;
                    case 'Celulares': $ruta_relativa = "celulares/"; break;
                    case 'Gadgets': $ruta_relativa = "gadgets/"; break;
                    default: $ruta_relativa = "moviles/"; break;
                }
                break;
            case 'Varios':
                switch ($seccion) {
                    case 'Seguridad': $ruta_relativa = "seguridad/"; break;
                    case 'Unidades': $ruta_relativa = "unidades/"; break;
                    case 'Varios': $ruta_relativa = "varios/"; break;
                    default: $ruta_relativa = "varios/"; break;
                }
                break;
            default:
                $ruta_relativa = "otros/";
                break;
        }
        
        $ruta_completa = "../img/" . $ruta_relativa . $nombre_archivo;

        // Intentar guardar la imagen
        if (!file_put_contents($ruta_completa, $imagen)) {
            error_log("No se pudo guardar la imagen en: " . $ruta_completa);
            return generarNombrePlaceholder($nombre_producto);
        }

        return $nombre_archivo;
    } catch (Exception $e) {
        error_log("Error al procesar imagen: " . $e->getMessage());
        return generarNombrePlaceholder($nombre_producto);
    }
}

// Llamar a la función al inicio del script
asegurarPlaceholder();

try {
    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($_FILES['excelFile']['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    // Usar toArray con preserveNullAndEmptyStrings para leer celdas vacías como strings vacíos
    $rows = $worksheet->toArray(null, true, true, true);

    // Definir las secciones válidas para cada apartado
    $secciones_validas = [
        'Gaming' => ['Perifericos', 'Consolas', 'Equipos'],
        'Moviles' => ['Audifonos', 'Celulares', 'Gadgets'],
        'Varios' => ['Seguridad', 'Unidades', 'Varios']
    ];

    // Mapear los encabezados a índices de columna
    $headers = $rows[1]; // Primera fila son los encabezados
    $column_map = [];
    $required_columns = ['nombre', 'precio', 'descripcion', 'apartado', 'seccion'];

    // Inicializar el mapeo de columnas
    foreach ($required_columns as $col) {
        $column_map[$col] = false;
    }
    $column_map['stock'] = false;
    $column_map['es_popular'] = false;
    $column_map['imagen_url'] = false;

    // Convertir encabezados a minúsculas y quitar espacios para comparación
    foreach ($headers as $col => $header) {
        $normalized_header = strtolower(trim(preg_replace('/\s*\([^)]*\)/', '', $header)));

        if ($normalized_header === 'nombre') {
            $column_map['nombre'] = $col;
        } else if ($normalized_header === 'precio') {
            $column_map['precio'] = $col;
        } else if ($normalized_header === 'stock') {
            $column_map['stock'] = $col;
        } else if ($normalized_header === 'descripcion' || $normalized_header === 'descripción') {
            $column_map['descripcion'] = $col;
        } else if ($normalized_header === 'apartado') {
            $column_map['apartado'] = $col;
        } else if ($normalized_header === 'seccion' || $normalized_header === 'sección') {
            $column_map['seccion'] = $col;
        } else if ($normalized_header === 'es popular') {
            $column_map['es_popular'] = $col;
        } else if ($normalized_header === 'url imagen') {
            $column_map['imagen_url'] = $col;
        }
    }

    // Verificar que todas las columnas requeridas existan
    foreach ($required_columns as $col) {
        if ($column_map[$col] === false) {
            throw new Exception("Columna requerida '$col' no encontrada en el archivo.");
        }
    }

    // Variables para seguimiento
    $productos_importados = 0;
    $errores = 0;
    $errores_detalle = [];
    $productos_existentes_omitidos = 0; // Contador para productos omitidos

    // Preparar consulta para verificar existencia
    $stmt_check = $conn->prepare("SELECT id FROM productos WHERE nombre = ? AND apartado = ? AND seccion = ?");
    if (!$stmt_check) {
        throw new Exception("Error al preparar la consulta de verificación: " . $conn->error);
    }


    // Procesar cada fila
    foreach ($rows as $rowIndex => $row) {
        // Saltar la primera fila (encabezados) y filas completamente vacías
        if ($rowIndex <= 1 || implode('', $row) === '') continue;

        // Usar las referencias de columna del mapeo para obtener los datos
        $nombre = trim($row[$column_map['nombre']]);
        $precio_str = trim($row[$column_map['precio']]);
        $stock_str = $column_map['stock'] !== false ? trim($row[$column_map['stock']]) : '';
        $descripcion = trim($row[$column_map['descripcion']]);
        $apartado = trim($row[$column_map['apartado']]);
        $seccion = trim($row[$column_map['seccion']]);
        $es_popular_str = $column_map['es_popular'] !== false ? trim($row[$column_map['es_popular']]) : '';
        $imagen_url = $column_map['imagen_url'] !== false ? trim($row[$column_map['imagen_url']]) : '';

        // Validar campos obligatorios mínimos antes de verificar existencia
        if (empty($nombre) || empty($apartado) || empty($seccion)) {
             $errores++;
             $errores_detalle[] = "Fila $rowIndex: Datos básicos incompletos (nombre, apartado o sección). Fila omitida.";
             continue; // Omitir fila si los datos básicos son incompletos
        }

        // Validar que la imagen sea obligatoria
        if (empty($imagen_url)) {
            $errores++;
            $errores_detalle[] = "Fila $rowIndex: La imagen es obligatoria para el producto '$nombre'. Fila omitida.";
            continue; // Omitir fila si no tiene imagen
        }

        // --- VERIFICAR SI EL PRODUCTO YA EXISTE ---
        $stmt_check->bind_param("sss", $nombre, $apartado, $seccion);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // El producto ya existe, omitir esta fila
            $productos_existentes_omitidos++;
            $errores_detalle[] = "Fila $rowIndex: Producto '$nombre' en Apartado '$apartado' y Sección '$seccion' ya existe. Omitido.";
            continue; // Pasar a la siguiente fila
        }
        // --- FIN VERIFICACIÓN ---


        // Continuar con validaciones detalladas e importación si el producto no existe
        // Validar y convertir precio (obligatorio)
        $precio = filter_var($precio_str, FILTER_VALIDATE_FLOAT);
        if ($precio === false || $precio <= 0) {
            $errores++;
            $errores_detalle[] = "Fila $rowIndex: El precio debe ser un número mayor que 0. Producto no importado.";
            continue; // Omitir fila si el precio no es válido
        }

        // Validar y convertir stock (opcional, por defecto 0)
        $stock = filter_var($stock_str, FILTER_VALIDATE_INT);
        if ($stock === false) {
            $stock = 0; // Valor por defecto si no es válido
        }

        // Validar descripción (obligatorio)
        if (empty($descripcion)) {
            $errores++;
            $errores_detalle[] = "Fila $rowIndex: La descripción es obligatoria. Producto no importado.";
            continue; // Omitir fila si la descripción está vacía
        }

        // Validar apartado (obligatorio) - Ya se hizo una validación básica antes de la verificación de existencia
        // Solo verificamos que esté en las secciones válidas
        if (!isset($secciones_validas[$apartado]) || !in_array($seccion, $secciones_validas[$apartado])) {
             $errores++;
             $errores_detalle[] = "Fila $rowIndex: La sección '$seccion' no es válida para el apartado '$apartado'. Producto no importado.";
             continue; // Omitir fila si apartado/sección no es válida
        }


        // Validar y convertir es_popular (opcional, por defecto 0)
        $es_popular = filter_var($es_popular_str, FILTER_VALIDATE_INT);
        if ($es_popular === false || ($es_popular !== 0 && $es_popular !== 1)) {
            $es_popular = 0; // Valor por defecto si no es válido
        }

        // --- PROCESAR IMAGEN ---
        $imagen_a_usar_db = descargarImagen($imagen_url, $apartado, $seccion, $nombre);
        if (strpos($imagen_a_usar_db, 'placeholder_') === 0) {
            $errores++;
            $errores_detalle[] = "Fila $rowIndex: No se pudo procesar la imagen de $imagen_url para el producto '$nombre'. Fila omitida.";
            continue; // Omitir fila si no se pudo procesar la imagen
        }
        // --- FIN PROCESAR IMAGEN ---


        // --- INSERTAR EL PRODUCTO ---
        try {
            // Preparar la consulta SQL con todos los campos
            $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, stock, descripcion, apartado, seccion, es_popular, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Verificar que la preparación fue exitosa
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta INSERT: " . $conn->error);
            }

            // Vincular parámetros, usando $imagen_a_usar_db (que ya es la ruta correcta)
            $bind_result = $stmt->bind_param("sdisssss", $nombre, $precio, $stock, $descripcion, $apartado, $seccion, $es_popular, $imagen_a_usar_db);

            // Verificar que la vinculación fue exitosa
            if (!$bind_result) {
                throw new Exception("Error en la vinculación de parámetros INSERT: " . $stmt->error);
            }

            // Ejecutar la consulta
            $execute_result = $stmt->execute();

            // Verificar que la ejecución fue exitosa
            if (!$execute_result) {
                // Si hay un error de duplicidad (aunque ya verificamos, es una capa extra)
                if ($conn->errno == 1062) { // Código de error para llave duplicada en MySQL
                     $productos_existentes_omitidos++;
                     $errores_detalle[] = "Fila $rowIndex: Error INSERT: Producto '$nombre' en Apartado '$apartado' y Sección '$seccion' ya existe (error de llave duplicada). Omitido.";
                     // Si se produjo un error de duplicidad al insertar, eliminar la imagen descargada si aplica
                     if ($imagen_a_usar_db !== generarNombrePlaceholder($nombre)) {
                         unlink($imagen_a_usar_db);
                         error_log("Limpiada imagen: $imagen_a_usar_db debido a error de inserción duplicada.");
                     }

                } else {
                    // Otro error de inserción
                    $errores++;
                    $errores_detalle[] = "Fila $rowIndex: Error en la ejecución de la consulta INSERT: " . $stmt->error;
                     // Si hubo un error al insertar, asegurarse de que la imagen descargada se limpie si aplica
                    if ($imagen_a_usar_db !== generarNombrePlaceholder($nombre) && file_exists($imagen_a_usar_db)) {
                        unlink($imagen_a_usar_db);
                        error_log("Limpiada imagen: $imagen_a_usar_db debido a error de inserción.");
                    }
                }
            } else {
                 // Inserción exitosa
                 $productos_importados++;
                 error_log("Producto insertado: '$nombre' con imagen '$imagen_a_usar_db'");
            }

            // Cerrar el statement INSERT si se abrió
            if ($stmt) {
               $stmt->close();
            }

        } catch (Exception $e) {
            // Error general en el bloque try/catch de inserción
            $errores++;
            $errores_detalle[] = "Fila $rowIndex: Error general al insertar producto: " . $e->getMessage();
             // Si hubo un error al insertar, asegurarse de que la imagen descargada se limpie si aplica
            if ($imagen_a_usar_db !== generarNombrePlaceholder($nombre) && file_exists($imagen_a_usar_db)) {
                unlink($imagen_a_usar_db);
                error_log("Limpiada imagen: $imagen_a_usar_db debido a excepción en inserción.");
            }
        }
    }
    // Cerrar el statement de verificación al final del bucle
     if ($stmt_check) {
        $stmt_check->close();
     }


    // Limpiar cualquier salida anterior
    ob_clean();

    // Determinar el estado final
    $final_status = 'success';
    if ($errores > 0) {
        $final_status = 'warning'; // Hubo errores de procesamiento o validación
    }
    if ($productos_importados === 0 && $productos_existentes_omitidos === 0 && count($errores_detalle) === count($rows) - 1) {
        // Si no se importó ni omitió nada, y el número de errores de detalle es igual al número de filas de datos (menos encabezado),
        // probablemente hubo un error grave por fila (ej: datos básicos incompletos en todas)
        $final_status = 'error';
        if (empty($errores_detalle) && $errores > 0) { // Si no hay detalles pero hay contador de errores
            $errores_detalle[] = "Error desconocido durante el procesamiento de las filas.";
        }
    } else if ($productos_importados > 0 || $productos_existentes_omitidos > 0) {
        // Si al menos se importó algo o se omitió algo, no es un error total de procesamiento
        if ($errores > 0) {
            $final_status = 'warning';
        } else {
            $final_status = 'success';
        }
    } else if (count($rows) <= 1) { // Solo la fila de encabezado
        $final_status = 'warning';
        $errores_detalle[] = "El archivo Excel parece estar vacío o solo contiene encabezados.";
    }

    // Construir mensaje de resumen más detallado
    $mensaje_resumen = "Importación completada:\n";
    if ($productos_importados > 0) {
        $mensaje_resumen .= "✅ Productos agregados: $productos_importados\n";
    }
    if ($productos_existentes_omitidos > 0) {
        $mensaje_resumen .= "⚠️ Productos omitidos (ya existentes): $productos_existentes_omitidos\n";
    }
    if ($errores > 0) {
        $mensaje_resumen .= "❌ Errores encontrados: $errores\n";
        $mensaje_resumen .= "   - Productos sin imagen o con imágenes inválidas\n";
        $mensaje_resumen .= "   - Productos con datos incompletos\n";
    }

    echo json_encode([
        'status' => $final_status,
        'importados' => $productos_importados,
        'omitidos_existentes' => $productos_existentes_omitidos,
        'errores' => $errores,
        'errores_detalle' => $errores_detalle,
        'mensaje_resumen' => $mensaje_resumen
    ]);

} catch (Exception $e) {
    // Limpiar cualquier salida anterior
    ob_clean();

    echo json_encode([
        'status' => 'error',
        'message' => 'Error fatal al procesar el archivo: ' . $e->getMessage(),
        'importados' => 0,
        'omitidos_existentes' => 0,
        'errores' => 1,
        'errores_detalle' => ['Error fatal general: ' . $e->getMessage()],
        'mensaje_resumen' => "❌ Error fatal: " . $e->getMessage()
    ]);
}

// Cerrar la conexión
$conn->close();

// Finalizar y enviar la salida
ob_end_flush();
?>
