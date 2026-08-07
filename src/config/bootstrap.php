<?php
/**
 * Bootstrap global del proyecto.
 *
 * Se incluye al principio de cada "entry point" (archivos a los que
 * se accede directamente por URL: index.php, controllers/*.php).
 * Las vistas que se incluyen con require/include (layout.php, login.php, etc.)
 * NO necesitan llamarlo: ya van a recibir la sesión abierta.
 */
 
// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');
 
// Iniciamos la sesión si todavía no existe una
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Conexión a la base de datos, disponible en todo el proyecto como $pdo
require_once __DIR__ . '/database.php';
 