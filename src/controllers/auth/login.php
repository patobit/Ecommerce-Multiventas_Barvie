<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';

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

// Paso clave #3: Buscar usuario por Email y verificar contraseña -
try {
  // Buscamos el usuario por email y traemos id, name, last_name, email y el hash de password
$stmt = $pdo->prepare('SELECT id_usuario, nombre, apellido, email, password FROM usuarios WHERE email = :email');
  $stmt->execute(['email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Verificamos si existe el usuario y comprobamos el hash de la contraseña ingresada
  if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error_login'] = 'Email o contraseña incorrectos.';
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
  }

  // Paso clave #4: Cargar datos del usuario en la Sesión --------
$_SESSION['usuario'] = [
    'id'       => $user['id_usuario'],
    'name'     => $user['nombre'],
    'apellido' => $user['apellido'],
    'email'    => $user['email'],
];
  header('Location: ' . BASE_URL . '/src/views/index.php');
  exit;

} catch (PDOException $e) {
  $_SESSION['error_login'] = 'Error al iniciar sesión. Intentá de nuevo.';
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}
