<?php
// Verificar requisitos del sistema
$requisitos = [
    'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'Extensión mysqli' => extension_loaded('mysqli'),
    'Extensión mbstring' => extension_loaded('mbstring'),
    'Extensión intl' => extension_loaded('intl'),
];

$faltantes = array_filter($requisitos, function($v) { return !$v; });

if (!empty($faltantes)) {
    echo "El sistema requiere las siguientes extensiones que no están disponibles:<br>";
    foreach (array_keys($faltantes) as $req) {
        echo "- $req<br>";
    }
    echo "<br>Por favor, contacte al administrador del sistema.";
    exit;
}

// Si la extensión intl no está disponible, cargar una implementación alternativa
if (!extension_loaded('intl')) {
    include 'lib/punycode.php'; // Implementación alternativa de Punycode
}
?>