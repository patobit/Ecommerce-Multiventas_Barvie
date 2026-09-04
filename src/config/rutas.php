<?php
// RUTA BASE DEL PROYECTO (DETECCIÓN AUTOMÁTICA Y DINÁMICA)
if (!defined('BASE_URL')) {
    // $_SERVER['SCRIPT_NAME'] es una variable del sistema PHP, déjala TAL CUAL.
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    // Busca dónde empieza la carpeta '/src/' en la URL
    $pos = strpos($scriptName, '/src/');

    if ($pos !== false) {
        // Si está ejecutando desde adentro de 'src' (ej: /src/views/catalogo.php),
        // recorta la URL justo antes de '/src'
        $baseUrl = substr($scriptName, 0, $pos);
    } else {
        // Si está en la raíz (ej: /index.php), toma el directorio padre
        $baseUrl = rtrim(dirname($scriptName), '/\\');
    }

    define('BASE_URL', $baseUrl);
}
