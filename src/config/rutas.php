<?php
// RUTA BASE DEL PROYECTO
// Esta constante se antepone a los links de navegación y a los assets
// (CSS, JS) para que funcionen sin importar desde qué carpeta se sirva
// el archivo PHP (raíz, src/views/, etc.).
// Ajustá el valor de abajo según cuál sea tu caso.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Multiventas_Barvie');
}