<?php
require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $query = '
        SELECT
            p.*,
            u.id AS user_id,
            u.name AS user_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = :id
    ';

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'id' => $_GET['id'],
    ]);

    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    require_once __DIR__ . '/../../views/posts/show.php';
} catch (PDOException $e) {
    exit;
}