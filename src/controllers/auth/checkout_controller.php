<?php
// GUIA DE ESTUDIO MVB
// CONFIRMACION: solo acepta POST con usuario autenticado y token CSRF. Delega la compra a Strapi; el navegador no decide el precio final.

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/carrito.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Usá POST.']);
    exit;
}
if (empty($_SESSION['usuario']['jwt'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Iniciá sesión para comprar.']);
    exit;
}
$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos) || !is_string($datos['csrf'] ?? null) ||
    !hash_equals($_SESSION['checkout_csrf'] ?? '', $datos['csrf']) || empty($_SESSION['checkout_csrf'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Recargá la página antes de confirmar.']);
    exit;
}
unset($datos['csrf']);
echo json_encode(finalizarCompra($_SESSION['usuario']['jwt'], (int)$_SESSION['usuario']['id'], $datos));
