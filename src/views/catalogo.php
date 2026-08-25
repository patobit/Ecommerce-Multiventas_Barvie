<?php
require_once __DIR__ . '/../config/rutas.php';                     // define BASE_URL
require_once __DIR__ . '/../config/database.php';                  // define $pdo
require_once __DIR__ . '/../controllers/productos_controller.php'; // consultas a la DB
require_once __DIR__ . '/productos_card.php';                       // renderProductCard()

// ---------------------------------------------------------------------------
// Parámetros de la URL (todo por GET: se puede compartir el link, usar
// "atrás/adelante" del navegador, y recargar sin perder el filtro)
// ---------------------------------------------------------------------------
$categoriaId = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$categoriaActual = null;
$subcategorias = [];
$productos = [];

if ($busqueda !== '') {
    // Si hay búsqueda activa, ignoramos la navegación por categorías
    // y mostramos directamente los resultados que matchean el texto.
    $productos = buscarProductos($pdo, $busqueda);
} elseif ($categoriaId !== null) {
    $categoriaActual = obtenerCategoriaPorId($pdo, $categoriaId);

    if ($categoriaActual) {
        $subcategorias = obtenerCategorias($pdo, $categoriaId);
        // Si la categoría no tiene subcategorías, es una categoría "hoja" -> mostramos sus productos
        if (empty($subcategorias)) {
            $productos = obtenerProductosPorCategoria($pdo, $categoriaId);
        }
    }
}

if ($busqueda === '' && $categoriaId === null) {
    $subcategorias = obtenerCategorias($pdo, null); // categorías principales
}

require_once __DIR__ . '/_layouts/header.php';
?>
    <!-- Catálogo -->
    <main class="container my-5">

        <!-- Encabezado + buscador propio del catálogo (GET) -->
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <?php if ($busqueda !== ''): ?>
                    <a href="<?= BASE_URL ?>/src/views/catalogo.php" class="text-secondary text-decoration-none small d-inline-block mb-2">← Volver al catálogo</a>
                    <h1 class="fw-bold text-white text-uppercase tracking-wide m-0">Resultados para "<?= htmlspecialchars($busqueda) ?>"</h1>
                <?php elseif ($categoriaActual): ?>
                    <a href="<?= BASE_URL ?>/src/views/catalogo.php<?= $categoriaActual['id_categoria_padre'] ? '?categoria=' . (int) $categoriaActual['id_categoria_padre'] : '' ?>" class="text-secondary text-decoration-none small d-inline-block mb-2">← Volver</a>
                    <h1 class="fw-bold text-white text-uppercase tracking-wide m-0"><?= htmlspecialchars($categoriaActual['nombre']) ?></h1>
                <?php else: ?>
                    <h1 class="fw-bold text-white text-uppercase tracking-wide m-0">Catálogo</h1>
                    <p class="text-secondary mb-0">Elegí una categoría para ver los productos disponibles.</p>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?= BASE_URL ?>/src/views/catalogo.php" class="d-flex gap-2">
                <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" class="form-control form-control-premium" placeholder="Buscar en el catálogo...">
                <button type="submit" class="btn btn-premium-red px-4">Buscar</button>
            </form>
        </div>

        <?php if (!empty($subcategorias)): ?>

            <!-- ============================================================
                 VISTA: GRILLA DE (SUB)CATEGORÍAS
                 ============================================================ -->
            <div class="row g-4">
                <?php foreach ($subcategorias as $cat): ?>
                    <?php $cantidad = contarProductosEnCategoria($pdo, $cat['id_categoria']); ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="<?= BASE_URL ?>/src/views/catalogo.php?categoria=<?= (int) $cat['id_categoria'] ?>" class="text-decoration-none">
                            <div class="card card-premium h-100 p-4 text-center">
                                <h4 class="h5 fw-bold text-white mb-2"><?= htmlspecialchars($cat['nombre']) ?></h4>
                                <?php if (!empty($cat['descripcion'])): ?>
                                    <p class="text-secondary small mb-3"><?= htmlspecialchars($cat['descripcion']) ?></p>
                                <?php endif; ?>
                                <?php if ($cantidad > 0): ?>
                                    <span class="badge badge-premium-red align-self-center">
                                        <?= $cantidad ?> producto<?= $cantidad === 1 ? '' : 's' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif (!empty($productos)): ?>

            <!-- ============================================================
                 VISTA: PRODUCTOS (de una categoría hoja, o de una búsqueda)
                 ============================================================ -->
            <div class="row g-4">
                <?php foreach ($productos as $p): ?>
                    <?= renderProductCard($p) ?>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <div class="p-5 text-center bg-dark border border-secondary border-opacity-25 rounded-3">
                <p class="text-secondary m-0">
                    <?= $busqueda !== '' ? 'No encontramos productos que coincidan con tu búsqueda.' : 'Todavía no hay productos cargados en esta categoría.' ?>
                </p>
            </div>

        <?php endif; ?>

    </main>

<?php
require_once __DIR__ . '/_layouts/footer.php';
?>