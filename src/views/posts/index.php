<?php
include(__DIR__ . '/../_layouts/layout.php');
?>

<header class="row">
  <div class="col">
    <h1>Listado de posts</h1>
    <p>Aquí puedes ver todos los posts publicados</p>
  </div>
  <div class="col d-flex justify-content-end align-items-center">
    <a class="btn btn-primary" href="/src/controllers/posts/form.php">Crear Post</a>
  </div>
</header>

<main>
  <?php if (!empty($posts)): ?>
    <?php foreach ($posts as $post): ?>
      <article class="card mb-3">
        <div class="card-body">
          <h2 class="card-title"><a href="/src/controllers/posts/show.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a></h2>
          <p class="card-text"><?= $post['content'] ?></p>
          
          <small class="text-muted"><?= $post['created_at'] ?></small>
          
          <p class="card-text"><strong>By:</strong> <?= $post['user_name'] ?></p>
          
          <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
            <a class="btn btn-secondary me-3" href="/src/controllers/posts/form.php?id=<?= $post['id'] ?>">Editar</a>
            <a class="btn btn-danger" href="/src/controllers/posts/delete.php?id=<?= $post['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este post?');">Eliminar</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No hay posts todavía.</p>
  <?php endif; ?>
</main>