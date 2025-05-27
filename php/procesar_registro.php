<?php
// Asegurar que PHP maneje correctamente UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrar.php?error=invalid_method");
    exit();
}

$nombre                = trim($_POST['nombre'] ?? '');
$email_input           = $_POST['email'] ?? '';
$contrasena            = $_POST['contrasena'] ?? '';
$confirmar_contrasena  = $_POST['confirmar_contrasena'] ?? '';

// Función para generar sugerencias de nombres de usuario
function generarSugerencias($nombre, $conn) {
    $sugerencias = [];
    $base = $nombre;
    
    // Función auxiliar para verificar si un nombre está disponible
    $estaDisponible = function($nombre) use ($conn) {
        $stmt = $conn->prepare("SELECT id FROM login WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 0;
    };
    
    // 1. Intentar con el nombre base + año actual
    $sugerencia = $base . date('Y');
    if ($estaDisponible($sugerencia)) {
        $sugerencias[] = $sugerencia;
    }
    
    // 2. Intentar con el nombre base + número aleatorio entre 100-999
    $sugerencia = $base . rand(100, 999);
    if ($estaDisponible($sugerencia)) {
        $sugerencias[] = $sugerencia;
    }
    
    // 3. Intentar con el nombre base + sufijos comunes
    $sufijos = ['_user', '_pro', '_sv', '_es', '_sv2024'];
    foreach ($sufijos as $sufijo) {
        if (count($sugerencias) < 3) {
            $sugerencia = $base . $sufijo;
            if ($estaDisponible($sugerencia)) {
                $sugerencias[] = $sugerencia;
            }
        }
    }
    
    // 4. Si aún no tenemos suficientes sugerencias, intentar con el nombre base + letras
    if (count($sugerencias) < 3) {
        $letras = range('a', 'z');
        shuffle($letras);
        foreach ($letras as $letra) {
            if (count($sugerencias) < 3) {
                $sugerencia = $base . $letra;
                if ($estaDisponible($sugerencia)) {
                    $sugerencias[] = $sugerencia;
                }
            }
        }
    }
    
    // 5. Si aún no tenemos suficientes sugerencias, intentar con el nombre base + números secuenciales
    if (count($sugerencias) < 3) {
        $i = 1;
        while (count($sugerencias) < 3 && $i <= 100) {
            $sugerencia = $base . $i;
            if ($estaDisponible($sugerencia)) {
                $sugerencias[] = $sugerencia;
            }
            $i++;
        }
    }
    
    // Asegurarnos de que no haya duplicados
    $sugerencias = array_unique($sugerencias);
    
    // Si aún no tenemos suficientes sugerencias, intentar con el nombre base + palabras comunes
    if (count($sugerencias) < 3) {
        $palabras = ['user', 'sv', 'es', 'pro', 'dev', '2024'];
        foreach ($palabras as $palabra) {
            if (count($sugerencias) < 3) {
                $sugerencia = $base . $palabra;
                if ($estaDisponible($sugerencia)) {
                    $sugerencias[] = $sugerencia;
                }
            }
        }
    }
    
    return array_slice($sugerencias, 0, 3); // Asegurarnos de devolver máximo 3 sugerencias
}

$errores = [];

// Validación de nombre más inclusiva para diferentes alfabetos
if (empty($nombre) || mb_strlen($nombre, 'UTF-8') < 4 || mb_strlen($nombre, 'UTF-8') > 30) {
    $errores[] = 'nombre';
}

// Validación mejorada para correos electrónicos internacionalizados
function validarEmailInternacional($email) {
    // Primero verificamos el formato básico
    if (empty($email) || strpos($email, '@') === false) {
        return false;
    }
    
    // Dividir en parte local y dominio
    list($localPart, $domain) = explode('@', $email, 2);
    
    // Verificar longitudes
    if (mb_strlen($localPart, 'UTF-8') > 64 || mb_strlen($domain, 'UTF-8') > 255) {
        return false;
    }
    
    // Si el dominio es ASCII, usar filter_var
    if (preg_match('/^[a-zA-Z0-9.-]+$/', $domain)) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Para dominios IDN, convertir a Punycode para validación
    if (function_exists('idn_to_ascii')) {
        $punycodeDomain = idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
        if ($punycodeDomain !== false) {
            $asciiEmail = $localPart . '@' . $punycodeDomain;
            return filter_var($asciiEmail, FILTER_VALIDATE_EMAIL) !== false;
        }
    }
    
    // Si no podemos convertir a punycode, hacemos una validación básica
    return (bool) preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email);
}

// Usar la nueva función de validación
if (!validarEmailInternacional($email_input)) {
    $errores[] = 'email';
    $email = null;
} else {
    $email = $email_input;
}

if (strlen($contrasena) < 8 || $contrasena !== $confirmar_contrasena) {
    $errores[] = 'password';
}

if (!empty($errores)) {
    $params = [
        'error' => implode(',', $errores),
        'nombre' => $nombre,
        'email' => $email_input,
        'contrasena' => $contrasena,
        'confirmar_contrasena' => $confirmar_contrasena
    ];
    header("Location: registrar.php?" . http_build_query($params));
    exit();
}

// Después de la validación del email y antes de verificar el nombre de usuario
// Verificar si el email ya existe en la base de datos
$email_normalizado = normalizarEmail($email_input);

$stmt = $conn->prepare("SELECT id FROM login WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email_normalizado);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $errores[] = 'email_exists';
    $params = [
        'error' => implode(',', $errores),
        'nombre' => $nombre,
        'email' => $email_input,
        'contrasena' => $contrasena,
        'confirmar_contrasena' => $confirmar_contrasena
    ];
    header("Location: registrar.php?" . http_build_query($params));
    exit();
}

$hashed_password = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);

// Función para normalizar correos electrónicos internacionales
function normalizarEmail($email) {
    if (empty($email) || strpos($email, '@') === false) {
        return $email;
    }
    
    list($localPart, $domain) = explode('@', $email, 2);
    
    // Convertir dominio a punycode si es necesario
    if (function_exists('idn_to_ascii') && !preg_match('/^[a-zA-Z0-9.-]+$/', $domain)) {
        $punycodeDomain = idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
        if ($punycodeDomain !== false) {
            return $localPart . '@' . $punycodeDomain;
        }
    }
    
    return $email;
}

// Normalizar el email antes de guardarlo
$email_normalizado = normalizarEmail($email);

$stmt = $conn->prepare("INSERT INTO login (nombre, email, password, admin) VALUES (?, ?, ?, 0)");
$stmt->bind_param("sss", $nombre, $email_normalizado, $hashed_password);

if ($stmt->execute()) {
    session_regenerate_id(true);
    $_SESSION['nombre']   = $nombre;
    $_SESSION['es_admin'] = false;
    header("Location: ../index.php");
} else {
    header("Location: registrar.php?error=db");
}
exit();
