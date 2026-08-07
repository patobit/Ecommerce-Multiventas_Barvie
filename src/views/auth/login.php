<?php
include('../_layouts/auth.layout.php');
?>

<div class="text-center mb-4">
  <img src="/assets/img/php-logo.png" alt="Logo" width="60" class="mb-2">
  <h1 class="h4 mb-0">PDISC</h1>
  <p class="text-muted small">Ingresá con tu cuenta</p>
</div>

<form action="/src/controllers/auth/login.php" method="POST">
  <div class="mb-3">
    <label for="email" class="form-label">E-mail</label>
    <input type="email" class="form-control" id="email" name="email" required autofocus>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Contraseña</label>
    <input type="password" class="form-control" id="password" name="password" required>
  </div>

  <button type="submit" class="btn btn-primary w-100">Ingresar</button>
</form>

<br />

<div class="text-center">
  <p class="text-muted small">¿No tienes una cuenta? <a href="/src/views/auth/register.php">¡Registrate ahora!</a></p>
</div>