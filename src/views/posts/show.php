<?php
include(__DIR__ . '/../_layouts/layout.php');
?>

<header class="row mb-4">
  <div class="col">
    <h1>Detalle del post</h1>
  </div>
  <div class="col d-flex justify-content-end align-items-center">
    <a class="btn btn-primary" href="/src/controllers/posts/index.php">Volver al listado</a>
  </div>
</header>

<main>
  <?php if (!empty($post)): ?>
    <article class="card border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <h2 class="card-title"><?= $post['title'] ?></h2>
          </div>
          <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
            <div class="col d-flex justify-content-end align-items-center">
              <a class="btn btn-secondary me-3" href="/src/views/posts/update.php?id=<?= $post['id'] ?>">Editar</a>
              <a class="btn btn-danger" href="/src/controllers/posts/delete.php?id=<?= $post['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este post?');">Eliminar</a>
            </div>
          <?php endif; ?>
        </div>

        
        <p class="card-text"><?= nl2br($post['content']) ?></p>
        <p class="card-text"><strong>Autor:</strong> <?= $post['user_name'] ?></p>
        <p class="text-muted">Publicado el <?= $post['created_at'] ?></p>
      </div>

      <section>
        <h3>Comentarios</h3>
        <p>Aún no hay comentarios (Ni los habrá)</p>
      </section>
    </article>
  <?php else: ?>
    <p>No se encontró el post solicitado.</p>
  <?php endif; ?>
</main>