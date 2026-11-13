<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/src/config/rutas.php';
require_once __DIR__ . '/src/controllers/auth/productos_controller.php';
require_once __DIR__ . '/src/views/productos_card.php';
$allProducts = obtenerTodosLosProductos();
$available = array_values(array_filter($allProducts, fn($p) => !empty($p['imagenes']) && (int)$p['stock'] > 0));
$groups = [];
foreach ($available as $p) $groups[$p['categoria_nombre']][] = $p;
$selection = [];
for ($round = 0; $round < 3 && count($selection) < 12; $round++) {
    foreach ($groups as $items) {
        if (isset($items[$round])) $selection[] = $items[$round];
        if (count($selection) === 12) break;
    }
}
$hero = [];
foreach (['Manopla de limpieza de microfibra', 'Atornillador inalámbrico Goodyear', 'Lavamotores'] as $term) {
    foreach ($available as $p) if (mb_stripos($p['nombre'], $term) !== false) { $hero[] = $p; break; }
}
if (count($hero) < 3) $hero = array_slice($available, 0, 3);
$categoriasHome = obtenerArbolCategorias();
$categoriasPrincipales = [];
foreach ($categoriasHome as $cat) {
    if ($cat['id_categoria_padre']) continue;
    $ids = idsDescendientes($categoriasHome, (int)$cat['id_categoria']);
    foreach ($available as $p) if (in_array((int)$p['id_categoria'], $ids, true)) { $cat['foto'] = $p['imagenes'][0]; $categoriasPrincipales[] = $cat; break; }
}
$isHomePage = true;
require __DIR__ . '/src/views/_layouts/header.php';
?>
<main id="contenido" class="mvb-storefront">
    <section class="mvb-showcase" aria-labelledby="home-title">
        <div class="mvb-wide mvb-showcase-grid">
            <div class="mvb-showcase-copy">
                <p class="mvb-eyebrow">MVB MULTIVENTAS · PARA TU VEHÍCULO</p>
                <h1 id="home-title">El detalle<br>hace la <span>diferencia.</span></h1>
                <p>Limpieza, accesorios y herramientas para cuidar tu auto y hacerlo más tuyo. Elegí con fotos claras, precios y stock en cada producto.</p>
                <a class="btn btn-premium-red" href="<?= BASE_URL ?>/src/views/catalogo.php">Explorar el catálogo <span aria-hidden="true">↗</span></a>
                <a class="mvb-showcase-contact" href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">¿Necesitás ayuda? Hablemos por WhatsApp ↗</a>
            </div>
            <div class="mvb-hero-products" aria-label="Una selección de nuestros productos">
                <?php foreach ($hero as $i => $p): ?>
                <a class="mvb-hero-product <?= $i === 0 ? 'mvb-hero-product-main' : '' ?>" href="<?= BASE_URL ?>/src/views/detalles_producto.php?id=<?= (int)$p['id_producto'] ?>">
                    <img src="<?= htmlspecialchars(imagenCatalogo($p['imagenes'][0], $i === 0 ? 800 : 480)) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" width="640" height="640" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                    <span><?= htmlspecialchars($p['categoria_nombre']) ?><strong><?= htmlspecialchars($p['nombre']) ?> ↗</strong></span>
                </a>
                <?php endforeach; ?>
                <?php if (!$hero): ?><div class="mvb-loading-notice"><strong>Estamos preparando el catálogo.</strong><p>El servicio está tardando en responder. Volvé a intentar en unos instantes.</p><a href="<?= BASE_URL ?>/index.php">Volver a intentar ↻</a></div><?php endif; ?>
            </div>
        </div>
    </section>
    <div class="mvb-service-strip mvb-wide"><span>FOTOS PARA ELEGIR MEJOR</span><span>PRECIOS Y STOCK DISPONIBLES</span><a href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">ASESORAMIENTO POR WHATSAPP ↗</a></div>

    <?php if ($groups): ?>
    <section class="mvb-wide mvb-home-section" aria-labelledby="category-title">
        <div class="mvb-section-heading"><div><p class="mvb-eyebrow">ENCONTRÁ LO QUE NECESITÁS</p><h2 id="category-title">Cada detalle, en su lugar.</h2></div><a class="mvb-text-link" href="<?= BASE_URL ?>/src/views/catalogo.php">Todas las categorías ↗</a></div>
        <nav class="mvb-category-tiles" aria-label="Comprar por categoría">
            <?php foreach ($categoriasPrincipales as $cat): ?>
            <a href="<?= BASE_URL ?>/src/views/catalogo.php?categoria=<?= (int)$cat['id_categoria'] ?>"><img src="<?= htmlspecialchars(imagenCatalogo($cat['foto'],360)) ?>" alt="" width="180" height="180" loading="lazy"><span><?= htmlspecialchars($cat['nombre']) ?></span><span aria-hidden="true">↗</span></a>
            <?php endforeach; ?>
        </nav>
    </section>
    <?php endif; ?>

    <section class="mvb-wide mvb-home-section" id="productos" aria-labelledby="selection-title">
        <div class="mvb-section-heading"><div><p class="mvb-eyebrow">UNA SELECCIÓN PARA VOS</p><h2 id="selection-title">Equipá tu próximo cuidado.</h2></div><div class="mvb-rail-controls"><button type="button" data-product-scroll="-1" aria-label="Ver productos anteriores">←</button><button type="button" data-product-scroll="1" aria-label="Ver más productos">→</button><a class="mvb-text-link" href="<?= BASE_URL ?>/src/views/catalogo.php?ver=todos">Ver todos ↗</a></div></div>
        <?php if ($selection): ?><div class="mvb-product-rail" id="homeProductRail" tabindex="0" role="region" aria-label="Productos destacados, carrusel desplazable"><?php foreach ($selection as $p) echo renderProductCard($p); ?></div>
        <?php else: ?><div class="mvb-loading-notice"><p>No pudimos cargar los productos en este momento.</p><a href="<?= BASE_URL ?>/index.php">Volver a intentar ↻</a></div><?php endif; ?>
    </section>

    <section class="mvb-wide mvb-home-section" aria-labelledby="advice-title"><div class="mvb-advice-banner"><div><p class="mvb-eyebrow">ELEGÍ CON ASESORAMIENTO</p><h2 id="advice-title">Tu auto. Tu estilo.<br>Nosotros te damos una mano.</h2><p>Si tenés dudas sobre un producto, sus medidas o su compatibilidad, consultanos antes de comprar.</p></div><a class="btn btn-premium-red" href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">Consultar por WhatsApp ↗</a></div></section>
</main>
<?php require __DIR__ . '/src/views/_layouts/footer.php'; ?>
