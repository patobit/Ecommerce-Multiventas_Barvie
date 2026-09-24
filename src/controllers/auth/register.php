<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/strapi_client.php';

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// --- 2. Campos Opcionales (estos SÍ existen como atributos en el content-type
//         "user" de Strapi, así que se guardan tal cual) ---
$telefono          = trim($_POST['telefono'] ?? '') ?: null;
$provincia         = trim($_POST['provincia'] ?? '') ?: null;
$ciudad            = trim($_POST['ciudad'] ?? '') ?: null;
$direccion         = trim($_POST['direccion'] ?? '') ?: null;
$autoMarca         = trim($_POST['auto_marca'] ?? '') ?: null;
$autoModelo        = trim($_POST['auto_modelo'] ?? '') ?: null;
$autoAnio          = !empty($_POST['auto_anio']) ? (int) $_POST['auto_anio'] : null;
$frecuenciaCompra  = $_POST['frecuencia_compra'] ?? '';
$aceptaDescuentos  = isset($_POST['acepta_descuentos']);
$aceptaPromociones = isset($_POST['acepta_promociones']);

// El enum en Strapi usa mayúscula inicial ("Ocasional", "Mensual",
// "Frecuente"), pero el formulario manda valores en minúscula. Se traduce acá.
$mapaFrecuencia = [
    'ocasional' => 'Ocasional',
    'mensual'   => 'Mensual',
    'frecuente' => 'Frecuente',
];
$frecuenciaCompraStrapi = $mapaFrecuencia[$frecuenciaCompra] ?? null;

// --- 3. Validaciones de campos obligatorios ---
if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || $datos['email'] === '' || $datos['nombre'] === '' || $datos['apellido'] === '' || $datos['clave'] === '') {
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

// Registrar los datos del formulario en Strapi.
$body = array_filter([
    'Nombre'             => $datos['nombre'],
    'Apellido'           => $datos['apellido'],
    'username'           => $datos['email'],
    'email'              => $datos['email'],
    'password'           => $datos['clave'],
    'Telefono'           => $telefono,
    'Provincia'          => $provincia,
    'Ciudad'             => $ciudad,
    'Direccion'          => $direccion,
    'Auto_marca'         => $autoMarca,
    'Auto_modelo'        => $autoModelo,
    'Auto_anio'          => $autoAnio,
    'Frecuencia_compra'  => $frecuenciaCompraStrapi,
    'Acepta_descuentos'  => $aceptaDescuentos,
    'Acepta_promociones' => $aceptaPromociones,
], fn($valor) => $valor !== null);

$resultado = strapiRequest('POST', 'auth/local/register', [], $body);

if (!$resultado['ok']) {
    $mensaje = $resultado['error'] ?? '';
    if (stripos($mensaje, 'email') !== false || stripos($mensaje, 'taken') !== false) {
        header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=email');
        exit;
    }
    header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=1');
    exit;
}

$jwt  = $resultado['data']['jwt'] ?? null;
$user = $resultado['data']['user'] ?? null;

if (!$jwt || !$user) {
    header('Location: ' . BASE_URL . '/src/views/auth/register.php?error=1');
    exit;
}

require_once __DIR__ . '/../../config/usuario_session.php';
guardarSesionUsuario($user, $jwt);

// --- 6. Redirección Exitosa ---
header('Location: ' . BASE_URL . '/index.php');
exit;
