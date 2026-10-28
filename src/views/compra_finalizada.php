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

// Solo procesa la compra cuando se viene del botón (POST). Recargar la página no la repite.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $res = finalizarCompra($_SESSION['usuario']['jwt'], (int) $_SESSION['usuario']['id']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $res = ['success' => false, 'message' => 'No se pudo finalizar la compra.'];
    }
    $_SESSION['flash_compra'] = $res;
    header('Location: ' . BASE_URL . '/src/views/compra_finalizada.php');
    exit;
}

$res = $_SESSION['flash_compra'] ?? null;
unset($_SESSION['flash_compra']);

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
            <p class="text-secondary mb-4">Gracias por tu compra en MultiVentas Barvie.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-premium-red px-4">Volver al inicio</a>
        <?php else: ?>
            <div class="display-3 mb-3">⚠️</div>
            <h1 class="h2 fw-bold text-white mb-3">No se pudo finalizar la compra</h1>
            <p class="text-secondary mb-4"><?= htmlspecialchars($res['message']) ?></p>
            <a href="<?= BASE_URL ?>/src/views/carrito.php" class="btn btn-premium-red px-4">Volver al carrito</a>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/_layouts/footer.php'; ?>