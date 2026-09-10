<?php
require_once __DIR__ . '/../../config/bootstrap.php';

try {
    if (!isset($_POST['title'], $_POST['content'])) {
        throw new Exception('Faltan datos del formulario');
    }

    $stmt = $pdo->prepare('INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)');
    $stmt->execute([
        'title' => $_POST['title'],
        'content' => $_POST['content'],
        'user_id' => $_SESSION['user']['id'],
    ]);

    header('Location: /src/controllers/posts/index.php');
    exit;
} catch (PDOException $e) {
    exit('Error al crear el post');
}