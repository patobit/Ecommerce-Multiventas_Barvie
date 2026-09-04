<?php
require_once __DIR__ . '/../../config/rutas.php';     // BASE_URL
require_once __DIR__ . '/../../config/bootstrap.php'; // sesión + $pdo

if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiventas Barvie - Ingresá o creá tu cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="auth-page">

        <div class="auth-topbar">
            <a href="<?= BASE_URL ?>/index.php" class="auth-back-link">
                ← Volver al inicio
            </a>
        </div>

        <div class="auth-center">
            <div class="auth-shell">

                <!-- Panel izquierdo decorativo -->
                <div class="auth-side">
                    <div class="auth-side-content text-white">
                        
                        <!-- Bloque Superior -->
                        <div>
                            <span class="badge badge-premium-red mb-3 px-3 py-2 fs-6">
                                Club de Ofertas
                            </span>
                            
                            <h2 class="text-white fw-bold mb-3 fs-2">
                                ¡Comprá rápido, seguro y a tu medida!
                            </h2>
                            
                            <p class="text-light mb-4 fs-6 opacity-90">
                                Para poder realizar compras en <strong class="text-danger fw-bold">Multiventas Barvie</strong> es necesario contar con una cuenta activa.
                            </p>
                        </div>

                        <!-- Bloque Medio (Lista de beneficios) -->
                        <div class="d-flex flex-column gap-3 my-auto">
                            
                            <div class="d-flex align-items-start gap-3">
                                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 28px; height: 28px;">✓</span>
                                <div>
                                    <strong class="d-block text-white fs-6">Registro Obligatorio</strong>
                                    <span class="text-light small">Es un requisito indispensable para procesar tus pedidos, emitir facturas y coordinar los envíos a todo el país.</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 28px; height: 28px;">★</span>
                                <div>
                                    <strong class="d-block text-white fs-6">Descuentos Exclusivos</strong>
                                    <span class="text-light small">Accedé a precios preferenciales en artículos de detailing, herramientas y accesorios para tu vehículo.</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 28px; height: 28px;">🚗</span>
                                <div>
                                    <strong class="d-block text-white fs-6">¿Por qué completar los datos opcionales?</strong>
                                    <span class="text-light small">Al indicarnos la marca, modelo y año de tu auto, nuestro sistema filtra automáticamente los productos 100% compatibles con tu modelo.</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <span class="bg-danger text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 28px; height: 28px;">⚡</span>
                                <div>
                                    <strong class="d-block text-white fs-6">Atención Prioritaria</strong>
                                    <span class="text-light small">Recibí soporte técnico directo vía WhatsApp para resolver dudas sobre repuestos o instalación.</span>
                                </div>
                            </div>

                        </div>

                        <!-- Bloque Inferior -->
                        <div class="p-3 rounded bg-dark border border-secondary text-center mt-3">
                            <span class="text-white small fw-semibold d-block">
                                🔒 Tus datos están protegidos y solo se utilizan para personalizar tu experiencia de compra.
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Panel del formulario -->
                <div class="auth-form-panel">
                    <div class="text-center text-md-start mb-4">
                        <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none fw-bold text-uppercase tracking-wider fs-5">
                            <span class="text-white">Multiventas</span><span class="text-danger"> Barvie</span>
                        </a>
                    </div>
