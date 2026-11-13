<?php
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../controllers/auth/productos_controller.php';
require_once __DIR__ . '/productos_card.php';
$categoriaId = isset($_GET['categoria']) && ctype_digit((string)$_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busqueda = trim((string)($_GET['buscar'] ?? ''));
$todosProductos = obtenerTodosLosProductos();
$todasCategorias = obtenerArbolCategorias();
$categoriaId = idCategoriaVigente($todasCategorias, $categoriaId);
$index = array_column($todasCategorias, null, 'id_categoria');
$categoriaActual = $categoriaId ? ($index[$categoriaId] ?? null) : null;
$invalida = isset($_GET['categoria']) && (!$categoriaId || !$categoriaActual);
if ($invalida && !catalogoNoDisponible()) http_response_code(404);
$ruta = rutaCategoria($todasCategorias, $categoriaId);
$conteos = []; $representantes = [];
foreach ($todasCategorias as $cat) {
    $id = (int)$cat['id_categoria']; $ids = idsDescendientes($todasCategorias, $id);
    $coinciden = array_values(array_filter($todosProductos, fn($p) => in_array((int)$p['id_categoria'], $ids, true)));
    $conteos[$id] = count($coinciden);
    foreach ($coinciden as $p) if (!empty($p['imagenes'])) { $representantes[$id] = $p; break; }
}
$hijas = array_values(array_filter($todasCategorias, fn($c) => (int)($c['id_categoria_padre'] ?? 0) === (int)($categoriaId ?? 0) && ($conteos[$c['id_categoria']] ?? 0) > 0));
$mostrarProductos = $busqueda !== '' || ($_GET['ver'] ?? '') === 'todos' || ($categoriaActual && !$hijas);
$ids = $categoriaId ? idsDescendientes($todasCategorias, $categoriaId) : array_keys($index);
$productos = array_values(array_filter($todosProductos, function($p) use ($categoriaId, $ids, $busqueda) {
    if ($categoriaId && !in_array((int)$p['id_categoria'], $ids, true)) return false;
    $texto = $p['nombre'] . ' ' . $p['descripcion'] . ' ' . $p['marca'];
    $termino = strtolower($busqueda) === 'revigal' ? 'revi' : $busqueda;
    return $termino === '' || mb_stripos($texto, $termino) !== false;
}));
$orden = in_array($_GET['orden'] ?? '', ['precio-asc','precio-desc'], true) ? $_GET['orden'] : 'nombre';
if ($orden !== 'nombre') usort($productos, fn($a,$b) => $orden === 'precio-asc' ? $a['precio'] <=> $b['precio'] : $b['precio'] <=> $a['precio']);
$enlace = function(?int $id, bool $todos = false): string {
    $q = []; if ($id) $q['categoria'] = $id; if ($todos) $q['ver'] = 'todos';
    return BASE_URL . '/src/views/catalogo.php' . ($q ? '?' . http_build_query($q) : '');
};
$padreNavegacion = $hijas ? ($categoriaId ?? 0) : (int)($categoriaActual['id_categoria_padre'] ?? 0);
$opciones = array_values(array_filter($todasCategorias, fn($c) => (int)($c['id_categoria_padre'] ?? 0) === $padreNavegacion && ($conteos[$c['id_categoria']] ?? 0) > 0));
$isCatalogPage = true;
require __DIR__ . '/_layouts/header.php';
?>
<main id="contenido" class="container my-5 mvb-catalog">
    <nav class="catalog-breadcrumb" aria-label="Ubicación"><a href="<?= BASE_URL ?>/index.php">Inicio</a><span> / </span><a href="<?= $enlace(null) ?>">Todo el catálogo</a><?php foreach ($ruta as $c): ?><span> / </span><a href="<?= htmlspecialchars($enlace((int)$c['id_categoria'])) ?>" <?= (int)$c['id_categoria'] === $categoriaId ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($c['nombre']) ?></a><?php endforeach; ?></nav>
    <div class="catalog-layout">
        <aside class="catalog-sidebar" aria-label="Navegar categorías">
            <h2><?= $padreNavegacion ? htmlspecialchars($index[$padreNavegacion]['nombre']) : 'Categorías' ?></h2>
            <?php if ($padreNavegacion): ?><a href="<?= htmlspecialchars($enlace($index[$padreNavegacion]['id_categoria_padre'] ? (int)$index[$padreNavegacion]['id_categoria_padre'] : null)) ?>">← Volver a categorías</a><?php endif; ?>
            <a href="<?= $enlace(null) ?>">Todo el catálogo</a>
            <ul class="catalog-category-tree"><?php foreach ($opciones as $c): ?><li><a href="<?= htmlspecialchars($enlace((int)$c['id_categoria'])) ?>" <?= (int)$c['id_categoria'] === $categoriaId ? 'aria-current="page"' : '' ?>><?= htmlspecialchars($c['nombre']) ?> <span><?= $conteos[$c['id_categoria']] ?></span></a></li><?php endforeach; ?></ul>
            <div class="catalog-help"><strong>¿Necesitás una mano?</strong><p>Consultanos por el producto que buscás.</p><a href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">Consultar por WhatsApp ↗</a></div>
        </aside>
        <section class="catalog-results" aria-labelledby="catalog-title">
            <p class="mvb-eyebrow"><?= $categoriaActual ? 'EXPLORÁ ESTA CATEGORÍA' : 'MVB MULTIVENTAS' ?></p>
            <h1 id="catalog-title"><?= $busqueda !== '' ? 'Resultados para “' . htmlspecialchars($busqueda) . '”' : htmlspecialchars($categoriaActual['nombre'] ?? 'Todo el catálogo') ?></h1>
            <p class="text-secondary"><?= $mostrarProductos ? 'Fotos, precio y stock de cada producto.' : 'Elegí una categoría para seguir explorando.' ?></p>
            <form method="GET" class="d-flex gap-2 catalog-search mb-4">
                <?php if ($categoriaId): ?><input type="hidden" name="categoria" value="<?= $categoriaId ?>"><?php endif; ?>
                <label for="catalog-search" class="visually-hidden">Buscar en <?= htmlspecialchars($categoriaActual['nombre'] ?? 'todo el catálogo') ?></label>
                <input id="catalog-search" type="search" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" class="form-control form-control-premium" placeholder="Buscar en <?= htmlspecialchars($categoriaActual['nombre'] ?? 'todo el catálogo') ?>">
                <button class="btn btn-premium-red" type="submit">Buscar</button>
            </form>
            <?php if (catalogoNoDisponible()): ?>
                <div class="mvb-loading-notice" role="status"><strong>El catálogo está tardando en responder.</strong><p>No pudimos comprobar los productos. Volvé a intentar en unos instantes.</p><a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES) ?>">Volver a intentar ↻</a></div>
            <?php elseif ($invalida): ?>
                <div class="mvb-loading-notice"><p>No encontramos esa categoría.</p><a href="<?= $enlace(null) ?>">Volver a todo el catálogo</a></div>
            <?php else: ?>
                <?php if ($hijas && $busqueda === ''): ?>
                <nav class="mvb-category-browser" aria-label="<?= $categoriaActual ? 'Subcategorías de ' . htmlspecialchars($categoriaActual['nombre']) : 'Categorías principales' ?>">
                    <?php foreach ($hijas as $c): $p = $representantes[$c['id_categoria']] ?? null; ?>
                    <a href="<?= htmlspecialchars($enlace((int)$c['id_categoria'])) ?>"><?php if ($p): ?><img src="<?= htmlspecialchars(imagenCatalogo($p['imagenes'][0],480)) ?>" alt="" width="480" height="480" loading="lazy"><?php endif; ?><div><h2><?= htmlspecialchars($c['nombre']) ?></h2><span><?= $conteos[$c['id_categoria']] ?> <?= $conteos[$c['id_categoria']] === 1 ? 'producto' : 'productos' ?> <b aria-hidden="true">↗</b></span></div></a>
                    <?php endforeach; ?>
                </nav>
                <?php if (!$mostrarProductos): ?><a class="btn btn-premium-outline my-4" href="<?= htmlspecialchars($enlace($categoriaId,true)) ?>">Ver todos los productos<?= $categoriaActual ? ' de ' . htmlspecialchars($categoriaActual['nombre']) : '' ?> (<?= count($productos) ?>)</a><?php endif; ?>
                <?php endif; ?>
                <?php if ($mostrarProductos): ?>
                <div class="mvb-results-toolbar"><p class="catalog-result-count"><?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?></p><form method="GET" class="mvb-sort-form"><input type="hidden" name="ver" value="todos"><?php if ($categoriaId): ?><input type="hidden" name="categoria" value="<?= $categoriaId ?>"><?php endif; ?><?php if ($busqueda !== ''): ?><input type="hidden" name="buscar" value="<?= htmlspecialchars($busqueda) ?>"><?php endif; ?><label for="product-sort">Ordenar por</label><select id="product-sort" name="orden"><?php foreach (['nombre'=>'Nombre','precio-asc'=>'Menor precio','precio-desc'=>'Mayor precio'] as $key=>$label): ?><option value="<?= $key ?>" <?= $orden === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><button type="submit">Aplicar</button></form></div>
                <?php if ($productos): ?><div class="catalog-product-grid"><?php foreach ($productos as $p) echo renderProductCard($p); ?></div><?php else: ?><div class="mvb-loading-notice"><p>No encontramos productos con esos filtros.</p><a href="<?= htmlspecialchars($enlace($categoriaId)) ?>">Limpiar búsqueda</a></div><?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php require __DIR__ . '/_layouts/footer.php'; ?>
