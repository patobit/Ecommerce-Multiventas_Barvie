<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/strapi_client.php';//TRAE LOS ARCHIVOS DE CONFIGURACION

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) { //ES UNA DOBLE SEGURIDAD
    session_start();
}

// Paso clave #1: Validar tipo de solicitud (Solo POST) ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    //SI ALGUIEN ENTRA AL CONTROLLER USANDO GET, LO MANDARIA DEVUELTA AL FORMULARIO
    exit;
}

// Paso clave #2: Tomar SOLO email y contraseña -----------------
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['error_login'] = 'Completá email y contraseña.';
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;// SI FALTA ALGUN DATO TE MANDA ESE MENSAJE 
}

// Paso clave #3: Autenticar contra Strapi (Users & Permissions) -
// Strapi acepta 'identifier' como email O username; acá siempre mandamos
// el email. Antes esto buscaba en una tabla local `usuarios` con PDO.
$resultado = strapiRequest('POST', 'auth/local', [], [
    'identifier' => $email,
    'password'   => $password,// LLAMA A STRAPI DESDE EL SERVIDOR Y ESTE GUARDA LOS DATOS Y LOS VERIFICA
]);

if (!$resultado['ok']) {
  // Strapi devuelve el mismo error genérico tanto si el email no existe
  // como si la contraseña está mal, así que no hace falta distinguir acá.
  $_SESSION['error_login'] = 'Email o contraseña incorrectos.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  // SI STRAPI DICE no, SE MUESTRA UN ERROR GENERICO
  //EVITA ENUMERACION DE MUCHOS USUARIOS 
  exit;
}

$jwt  = $resultado['data']['jwt'] ?? null;
$user = $resultado['data']['user'] ?? null;// SI FALTA UNO DE ESTOS VA A DARA ERROR Y VOLVER AL LOGIN

if (!$jwt || !$user) {
  $_SESSION['error_login'] = 'Error al iniciar sesión. Intentá de nuevo.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}

guardarSesionUsuario($user, $jwt);

header('Location: ' . BASE_URL . '/index.php');// GUARDA AL USUARIO EN LA SESION Y LO MANDA AL INICIO
exit;
