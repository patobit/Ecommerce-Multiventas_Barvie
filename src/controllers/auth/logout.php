<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';

// Limpiar todas las variables de la sesión
$_SESSION = [];

// Destruir la cookie de sesión en el navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión en el servidor
session_destroy();

// Redirigir al inicio o a la vista de login
header('Location: ' . BASE_URL . '/src/views/index.php');
exit;
