<?php

require_once __DIR__ . '/../../config/bootstrap.php';

$post = null;

if (isset($_GET['id'])) {
  $stmt = $pdo->prepare('
        SELECT *
        FROM posts
        WHERE id = :id
    ');

  $stmt->execute([
    'id' => $_GET['id']
  ]);

  $post = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../views/posts/form.php';
