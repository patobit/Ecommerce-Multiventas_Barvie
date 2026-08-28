<?php
// Forzar la salida como JSON
header('Content-Type: application/json; charset=utf-8');

// CONEXIÓN A LA BASE DE DATOS
try {
    require_once __DIR__ . '/../../config/database.php';
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);
    exit;
}

// CONFIGURACIÓN (TEMPORAL)
$id_usuario = 2;

// Obtener la lista de productos
if($_SERVER['REQUEST_METHOD'] === 'GET'){

}
/* if ($_SERVER['REQUEST_METHOD'] === 'POST') {
} */