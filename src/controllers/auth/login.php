<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';

// Validar que el acceso sea exclusivamente por método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/src/views/auth/register.php');
    exit;
}

// --- 1. Campos Obligatorios ---
$datos = [
    'email'         => trim($_POST['email'] ?? ''),
    'nombre'        => trim($_POST['nombre'] ?? ''),
    'apellido'      => trim($_POST['apellido'] ?? ''),
    'clave'         => $_POST['clave'] ?? '',
    'repetir_clave' => $_POST['repetir_clave'] ?? '',
];

// --- 2. Campos Opcionales ---
$telefono          = trim($_POST['telefono'] ?? '') ?: null;
$provincia         = trim($_POST['provincia'] ?? '') ?: null;
$ciudad            = trim($_POST['ciudad'] ?? '') ?: null;
$direccion         = trim($_POST['direccion'] ?? '') ?: null;
$autoMarca         = trim($_POST['auto_marca'] ?? '') ?: null;
$autoModelo        = trim($_POST['auto_modelo'] ?? '') ?: null;
$autoAnio          = !empty($_POST['auto_anio']) ? (int) $_POST['auto_anio'] : null;
$frecuenciaCompra  = in_array($_POST['frecuencia_compra'] ?? '', ['ocasional', 'mensual', 'frecuente'], true) ? $_POST['frecuencia_compra'] : null;
$aceptaDescuentos  = isset($_POST['acepta_descuentos']) ? 1 : 0;
$aceptaPromociones = isset($_POST['acepta_promociones']) ? 1 : 0;

// --- 3. Validaciones de campos obligatorios ---
if ($datos['email'] === '' || $datos['nombre'] === '' || $datos['apellido'] === '' || $datos['clave'] === '') {
    header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=1');
    exit;
}
if ($datos['clave'] !== $datos['repetir_clave']) {
    header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=password');
    exit;
}
if (strlen($datos['clave']) < 8) {
    header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=1');
    exit;
}

try {
    // --- 4. Verificar si el email ya existe ---
    $verificacion = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
    $verificacion->execute(['email' => $datos['email']]);
    if ($verificacion->fetch()) {
        header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=email');
        exit;
    }

    // --- 5. Hashear la contraseña con BCRYPT ---
    $claveHasheada = password_hash($datos['clave'], PASSWORD_DEFAULT);

    // --- 6. Guardar en la Base de Datos ---
    $sentencia = $pdo->prepare('
        INSERT INTO usuarios
            (nombre, apellido, email, clave, telefono, provincia, ciudad, direccion, auto_marca, auto_modelo, auto_anio,
             frecuencia_compra, acepta_descuentos, acepta_promociones)
        VALUES
            (:nombre, :apellido, :email, :clave, :telefono, :provincia, :ciudad, :direccion, :auto_marca, :auto_modelo, :auto_anio,
             :frecuencia_compra, :acepta_descuentos, :acepta_promociones)
    ');

    $sentencia->execute([
        'nombre'             => $datos['nombre'],
        'apellido'           => $datos['apellido'],
        'email'              => $datos['email'],
        'clave'              => $claveHasheada,
        'telefono'           => $telefono,
        'provincia'          => $provincia,
        'ciudad'             => $ciudad,
        'direccion'          => $direccion,
        'auto_marca'         => $autoMarca,
        'auto_modelo'        => $autoModelo,
        'auto_anio'          => $autoAnio,
        'frecuencia_compra'  => $frecuenciaCompra,
        'acepta_descuentos'  => $aceptaDescuentos,
        'acepta_promociones' => $aceptaPromociones,
    ]);

    // --- 7. Cargar la Sesión del Usuario ---
    $_SESSION['usuario'] = [
        'id'       => $pdo->lastInsertId(),
        'nombre'   => $datos['nombre'],
        'apellido' => $datos['apellido'],
        'email'    => $datos['email'],
    ];

    // --- 8. Redirección Exitosa ---
    header('Location: ' . BASE_URL . '/src/views/index.php');
    exit;

} catch (PDOException $e) {
    // Te muestra en pantalla el motivo exacto por el cual falla la base de datos
    die("Error en la Base de Datos: " . $e->getMessage());
}
