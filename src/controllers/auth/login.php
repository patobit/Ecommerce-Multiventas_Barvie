<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/strapi_client.php';

// Paso clave #1: Validar tipo de solicitud (Solo POST) ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}

// Paso clave #2: Tomar SOLO email y contraseña -----------------
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  $_SESSION['error_login'] = 'Completá email y contraseña.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}

// Paso clave #3: Autenticar contra Strapi (Users & Permissions) -
// Strapi acepta 'identifier' como email O username; acá siempre mandamos
// el email. Antes esto buscaba en una tabla local `usuarios` con PDO.
$resultado = strapiRequest('POST', 'auth/local', [], [
    'identifier' => $email,
    'password'   => $password,
]);

if (!$resultado['ok']) {
  // Strapi devuelve el mismo error genérico tanto si el email no existe
  // como si la contraseña está mal, así que no hace falta distinguir acá.
  $_SESSION['error_login'] = 'Email o contraseña incorrectos.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}

$jwt  = $resultado['data']['jwt'] ?? null;
$user = $resultado['data']['user'] ?? null;

if (!$jwt || !$user) {
  $_SESSION['error_login'] = 'Error al iniciar sesión. Intentá de nuevo.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}

// Paso clave #4: Cargar datos del usuario en la sesión ----------
// OJO: el content-type "user" de Strapi no tiene campos separados de
// nombre/apellido (solo 'username'). Guardamos 'username' como nombre para
// mostrar, ya que no hay otra fuente después del login (a diferencia del
// registro, donde sí los pedimos por formulario).
$_SESSION['usuario'] = [
    'id'       => $user['id'],
    'name'     => $user['username'],
    'apellido' => '',
    'email'    => $user['email'],
    'jwt'      => $jwt,
];

header('Location: ' . BASE_URL . '/src/views/index.php');
exit;