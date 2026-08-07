<?php
require_once __DIR__ . '/../../config/bootstrap.php';
 
// Vaciamos los datos de la sesión
$_SESSION = [];
 
// Borramos la cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
 
// Destruimos la sesión en el servidor
session_destroy();
 
header('Location: /src/views/auth/login.php');
exit;