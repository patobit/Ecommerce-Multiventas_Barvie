<?php
require_once __DIR__ . '/../../config/bootstrap.php'; # Acá linkea las configuraciones de bootstrap.php
if (isset($_SESSION['user'])) {
  header('Location: /src/views/index.php');
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
  <title>Autenticate - PDI</title>
</head>
<body class="bg-body-secondary">
 
  <div class="d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
      <div class="card-body p-4">
  