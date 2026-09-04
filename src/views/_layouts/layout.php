<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
<script src="<?= BASE_URL ?>/assets/js/bootstrap.min.js"></script>
...
<img src="<?= BASE_URL ?>/assets/img/php-logo.png" alt="Logo" width="45" class="d-inline-block align-text-top">
...
<a href="<?= BASE_URL ?>/src/controllers/auth/logout.php" class="btn text-danger">Logout</a>
<?php
function logout() {
  session_destroy();
  header('Location: ' . BASE_URL . '/src/views/auth/login.php');
  exit;
}
?>
