<?php
include('../_layouts/auth.layout.php');
?>

<div class="text-center mb-4">
  <img src="/assets/img/php-logo.png" alt="Logo" width="60" class="mb-2">
  <h1 class="h4 mb-0">PDISC</h1>
  <p class="text-muted small">Creá cuenta</p>
</div>

<form action="/src/controllers/auth/register.php" method="POST">
  <div class="mb-3">
    <label for="email" class="form-label">E-mail</label>
    <input type="email" class="form-control" id="email" name="email" required autofocus>
  </div>

  <div class="mb-3">
    <label for="name" class="form-label">Usuario</label>
    <input type="text" class="form-control" id="name" name="name" required autofocus>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Contraseña</label>
    <input type="password" class="form-control" id="password" name="password" required>
  </div>

  <div class="mb-3">
    <label for="repeatPassword" class="form-label">Repetí tu contraseña</label>
    <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" required>
  </div>

  <button type="submit" class="btn btn-primary w-100">Ingresar</button>
</form>

<br />

<div class="text-center">
  <p class="text-muted small">¿Ya tenés una cuenta? <a href="/src/views/auth/login.php">¡Inicia sesión!</a></p>
</div>