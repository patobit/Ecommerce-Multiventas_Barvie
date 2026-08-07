<?php
require_once __DIR__ . '/../../config/bootstrap.php'; # Acá linkea las configuraciones de bootstrap.php

# Si no estoy logueado, me saca
if (!isset($_SESSION['user'])) {
  header('Location: /src/views/auth/login.php');
  exit;
}

function logout() {
  session_destroy();
  header('Location: /src/views/auth/login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href=<?= '/assets/css/bootstrap.min.css' ?> >
  <script src=<?= '/assets/js/bootstrap.min.js' ?>></script>
  <title>App</title>
</head>
<body>
  <nav class="navbar bg-body-secondary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <img src="/assets/img/php-logo.png" alt="Logo" width="45" class="d-inline-block align-text-top">
        PDISC
      </a>
      <a href="/src/controllers/auth/logout.php" class="btn text-danger">Logout</a>
    </div>
  </nav>

  <main class="container mt-3">
    <!-- Acá se cargan los sitios, pueden modificar lo que gusten -->