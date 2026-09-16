<?php
// src/controllers/UserController.php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;

    // 1. Datos Personales
    $nombre   = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);

    // Actualizar datos personales en la BD
    // $userModel->update($userId, $nombre, $apellido, $email, $telefono);

    // 2. Procesar Direcciones
    $direcciones = $_POST['direcciones'] ?? [];
    if (is_array($direcciones)) {
        // Opción: $direccionModel->deleteAllByUser($userId);
        foreach ($direcciones as $dir) {
            $provincia = trim($dir['provincia'] ?? '');
            $ciudad    = trim($dir['ciudad'] ?? '');
            $direccion = trim($dir['direccion'] ?? '');

            if (!empty($direccion)) {
                // $direccionModel->insert($userId, $provincia, $ciudad, $direccion);
            }
        }
    }

    // 3. Procesar Vehículos
    $vehiculos = $_POST['vehiculos'] ?? [];
    if (is_array($vehiculos)) {
        // Opción: $vehiculoModel->deleteAllByUser($userId);
        foreach ($vehiculos as $veh) {
            $marca  = trim($veh['marca'] ?? '');
            $modelo = trim($veh['modelo'] ?? '');
            $anio   = filter_var($veh['anio'] ?? 0, FILTER_VALIDATE_INT);

            if (!empty($marca) && !empty($modelo)) {
                // $vehiculoModel->insert($userId, $marca, $modelo, $anio);
            }
        }
    }

    // Redirección tras actualizar
    header('Location: ' . BASE_URL . '/index.php?action=profile&status=updated');
    exit;
}