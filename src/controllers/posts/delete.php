<?php
require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    $stmt->execute([
        'id' => $_GET['id'],
    ]);

    header('Location: /src/controllers/posts/index.php');
    exit;
} catch (PDOException $e) {
    exit('Error al eliminar el post');
}