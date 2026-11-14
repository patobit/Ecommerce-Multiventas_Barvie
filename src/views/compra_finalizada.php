<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../controllers/auth/carrito.php';

if (empty($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['jwt'])) {
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}

// 1. Detectar si el usuario viene de regreso desde Mercado Pago con un pago aprobado
if (isset($_GET['status']) && $_GET['status'] === 'approved') {
    try {
        // El pago fue exitoso, ahora SÍ vaciamos el carrito y registramos la compra en Strapi
$res = finalizarCompra(
    $_SESSION['usuario']['jwt'],
    (int) $_SESSION['usuario']['id'],
    ['payment_id' => $_GET['payment_id'] ?? $_GET['collection_id'] ?? '']
);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $res = ['success' => false, 'message' => 'No se pudo registrar la compra en el sistema.'];
    }
    
    $_SESSION['flash_compra'] = $res;
    
    // Redirigimos a esta misma página pero limpiando la URL de los parámetros de Mercado Pago
    header('Location: ' . BASE_URL . '/src/views/compra_finalizada.php');
    exit;
}

// 2. Comprobar si hay una alerta guardada en la sesión
$res = $_SESSION['flash_compra'] ?? null;
unset($_SESSION['flash_compra']);

// Si entra a la página directo (sin venir de MP y sin sesión flash), lo sacamos al inicio
if ($res === null) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

require_once __DIR__ . '/_layouts/header.php';
?>
<main class="container my-5">
    <div class="card card-premium p-5 text-center mx-auto" style="max-width:600px;">
        <?php if ($res['success']): ?>
            <div class="display-3 mb-3">✅</div>
            <h1 class="h2 fw-bold text-white mb-3">¡Compra finalizada!</h1>
            <p class="text-secondary mb-4">El pago se aprobó correctamente. Gracias por tu compra en MultiVentas Barvie.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-premium-red px-4">Volver al inicio</a>
        <?php else: ?>
            <div class="display-3 mb-3">⚠️</div>
            <h1 class="h2 fw-bold text-white mb-3">Hubo un problema</h1>
            <p class="text-secondary mb-4"><?= htmlspecialchars($res['message']) ?></p>
            <a href="<?= BASE_URL ?>/src/views/carrito.php" class="btn btn-premium-red px-4">Volver al carrito</a>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/_layouts/footer.php'; ?>