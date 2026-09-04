<?php
require_once __DIR__ . '/../_layouts/auth.layout.php';
$error = $_GET['error'] ?? null;
?>

<div class="text-center mb-4">
    <h1 class="h4 fw-bold text-white mb-1">Iniciar Sesión</h1>
    <p class="text-secondary small mb-0">Ingresá con tu cuenta para seguir comprando</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger py-2 small" role="alert">
        Correo o contraseña incorrectos. Probá de nuevo.
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/src/controllers/auth/login.php" method="POST" novalidate>
    <div class="mb-3">
        <label for="email" class="form-label text-secondary small text-uppercase fw-semibold">Correo Electrónico</label>
        <input type="email" class="form-control form-control-premium" id="email" name="email" placeholder="nombre@correo.com" required autofocus>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label text-secondary small text-uppercase fw-semibold">Contraseña</label>
        <input type="password" class="form-control form-control-premium" id="password" name="password" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-premium-red w-100 py-2 fw-bold text-uppercase mt-2">Entrar</button>
</form>

<div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
    <p class="text-secondary small mb-2">¿Todavía no tenés una cuenta?</p>
    <a href="<?= BASE_URL ?>/src/views/auth/register.php" class="btn btn-premium-outline w-100 fw-semibold">
        Crear cuenta nueva
    </a>
</div>

<?php require_once __DIR__ . '/../_layouts/auth.footer.php'; ?>