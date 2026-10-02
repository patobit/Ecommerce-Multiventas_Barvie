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
 
// Configuración compartida para consultar la API de Strapi.
require_once __DIR__ . '/database.php';

// Mantiene las claves de sesión utilizadas por las vistas y los controladores.
function guardarSesionUsuario(array $user, string $jwt): void
{
    session_regenerate_id(true);
    $nombre = $user['Nombre'] ?? $user['username'] ?? '';
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['nombre'] = $nombre;
    $_SESSION['usuario'] = [
        'id' => $user['id'], 'id_usuario' => $user['id'],
        'name' => $nombre, 'nombre' => $nombre,
        'apellido' => $user['Apellido'] ?? '', 'email' => $user['email'],
        'jwt' => $jwt,
    ];
}
 
