<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/_layouts/header.php';
?>
<main class="container my-5">
    <div class="card card-premium p-5 text-center mx-auto" style="max-width:600px;">
        <div class="display-3 mb-3">✅</div>
        <h1 class="h2 fw-bold text-white mb-3">¡Compra finalizada!</h1>
        <p class="text-secondary mb-4">Gracias por tu compra en MultiVentas Barvie.</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-premium-red px-4">Volver al inicio</a>
    </div>
</main>
<?php require_once __DIR__ . '/_layouts/footer.php'; ?>