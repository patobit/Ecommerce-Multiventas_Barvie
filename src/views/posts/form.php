<?php
include(__DIR__ . '/../_layouts/layout.php');
$post = $post ?? null;
?>

<header>
  <h1><?= $post ? 'Editar Post' : 'Crear Post' ?></h1>
  <p>Completa el formulario</p>
</header>

<form method="POST" action="<?= $post ? '/src/controllers/posts/update.php' : '/src/controllers/posts/store.php' ?>">
  <?php if ($post): ?>
    <input
      type="hidden"
      name="id"
      value="<?= $post['id'] ?>">
  <?php endif; ?>

  <div class="mb-3">
    <input
      class="form-control"
      type="text"
      name="title"
      value="<?= $post['title'] ?? '' ?>"
      placeholder="Título">
  </div>

  <div class="mb-3">
    <textarea
      class="form-control"
      name="content"
      placeholder="Contenido"><?= $post['content'] ?? '' ?></textarea>
  </div>

  <button class="btn btn-primary" type="submit">
    Guardar
  </button>

</form>