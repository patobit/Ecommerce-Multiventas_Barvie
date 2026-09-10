<?php
require_once __DIR__ . '/../../config/bootstrap.php';

try {
    if (!isset($_POST['title'], $_POST['content'])) {
        throw new Exception('Faltan datos del formulario');
    }

    $stmt = $pdo->prepare('UPDATE posts SET title = :title, content = :content WHERE id = :id');
    $stmt->execute([
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'content' => $_POST['content'],
    ]);

    header('Location: /src/controllers/posts/index.php');
    exit;
} catch (PDOException $e) {
    exit('Error al actualizar el post');
}