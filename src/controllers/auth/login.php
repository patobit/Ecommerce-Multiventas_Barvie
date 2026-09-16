<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $stmt = $pdo->prepare('SELECT id_usuario, nombre, apellido, email, clave FROM usuarios WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificamos si existe el usuario y comprobamos el hash de la contraseña
    if (!$user || !password_verify($password, $user['clave'])) {
        $_SESSION['error_login'] = 'Email o contraseña incorrectos.';
        header('Location: ' . BASE_URL . '/src/views/auth/login.php');
        exit;
    }

    // Paso clave #4: Cargar datos del usuario en la Sesión --------
    $_SESSION['usuario_id'] = $user['id_usuario'];
    $_SESSION['nombre']     = $user['nombre'];
    $_SESSION['usuario']    = [
        'id_usuario' => $user['id_usuario'],
        'nombre'     => $user['nombre'],
        'apellido'   => $user['apellido'],
        'email'      => $user['email'],
    ];

    // Redirigir al inicio logueado
    header('Location: ' . BASE_URL . '/index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error_login'] = 'Error al iniciar sesión. Intentá de nuevo.';
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}