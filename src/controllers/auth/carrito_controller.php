<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/carrito_controller.php'; // en tu proyecto: carrito.php (ver nota de nombres al final)

// Antes había un id_usuario=2 "temporal". Ahora que el login existe de
// verdad, usamos el usuario logueado en la sesión.
if (empty($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['jwt'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Tenés que iniciar sesión para usar el carrito.']);
    exit;
}

$idUsuario = (int) $_SESSION['usuario']['id'];
$jwt       = $_SESSION['usuario']['jwt'];

// AGREGAR PRODUCTO AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $id_producto = isset($inputData['id_producto']) ? (int) $inputData['id_producto'] : 0;
    $cantidad    = isset($inputData['cantidad'])    ? (int) $inputData['cantidad']    : 1;

    if ($id_producto <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos del producto inválidos.']);
        exit;
    }

    echo json_encode(agregarProductoAlCarrito($jwt, $idUsuario, $id_producto, $cantidad));
    exit;
}

// ACTUALIZAR CANTIDAD DE UN PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];

    $id_detalle_carrito = isset($inputData['id_detalle_carrito']) ? (int) $inputData['id_detalle_carrito'] : 0;
    $cantidad = isset($inputData['cantidad']) ? (int) $inputData['cantidad'] : 0;

    if ($id_detalle_carrito <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }

    echo json_encode(actualizarCantidadEnCarrito($jwt, $idUsuario, $id_detalle_carrito, $cantidad));
    exit;
}

// ELIMINAR PRODUCTO DEL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];

    $id_detalle_carrito = isset($inputData['id_detalle_carrito']) ? (int) $inputData['id_detalle_carrito'] : 0;

    if ($id_detalle_carrito <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto inválido.']);
        exit;
    }

    echo json_encode(eliminarProductoDelCarrito($jwt, $idUsuario, $id_detalle_carrito));
    exit;
}

// OBTENER TODOS LOS PRODUCTOS DEL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(obtenerProductosDelCarrito($jwt, $idUsuario));
    exit;
}