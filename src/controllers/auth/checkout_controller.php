<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/carrito_funciones.php'; // en tu proyecto: carrito.php

if (empty($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['jwt'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Tenés que iniciar sesión para comprar.']);
    exit;
}

$idUsuario = (int) $_SESSION['usuario']['id'];
$jwt       = $_SESSION['usuario']['jwt'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(finalizarCompra($jwt, $idUsuario));
    exit;
}