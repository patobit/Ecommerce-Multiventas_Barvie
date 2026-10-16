<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/src/config/rutas.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/controllers/auth/productos_controller.php';
require_once __DIR__ . '/src/views/productos_card.php';
$seleccion = array_slice(array_values(array_filter(obtenerTodosLosProductos(), fn($p) => !empty($p['imagenes']) && (int)$p['stock'] > 0)), 0, 12);
$isHomePage = true;
require_once __DIR__ . '/src/views/_layouts/header.php';
?>
<main class="mvb-storefront">
    <section class="mvb-showcase" aria-labelledby="home-title">
        <div class="mvb-wide mvb-showcase-grid">
            <div class="mvb-showcase-copy">
                <p class="mvb-eyebrow">MULTIVENTAS BARVIE</p>
                <h1 id="home-title">Más cuidado.<br>Más equipado.<br><span>Más tuyo.</span></h1>
                <p>Todo para tu auto: limpieza, accesorios, iluminación y herramientas. Encontrá el próximo detalle con fotos, precios y stock disponibles.</p>
                <a class="btn btn-premium-red" href="<?= BASE_URL ?>/src/views/catalogo.php">Ver todos los productos ↗</a>
                <a class="mvb-showcase-contact" href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">Te ayudamos a elegir por WhatsApp</a>
            </div>
            <div class="mvb-hero-products" aria-label="Productos de Multiventas Barvie">
                <a class="mvb-hero-product mvb-hero-product-main" href="<?= BASE_URL ?>/src/views/catalogo.php?buscar=ARMOR"><img src="<?= BASE_URL ?>/assets/img/limpieza-productos/arm17744/arm17744_01.png" alt="Shampoo Armor All Ultra Shine Wash and Wax" fetchpriority="high"><span>Limpieza y cuidado <strong>Que se note el detalle ↗</strong></span></a>
                <a class="mvb-hero-product" href="<?= BASE_URL ?>/src/views/catalogo.php?buscar=DESTORNILLADOR"><img src="<?= BASE_URL ?>/assets/img/herramientas-y-elevacion/ll-013/ll-013_01.png" alt="Juego de destornilladores"><span>Herramientas <strong>Equipate para más ↗</strong></span></a>
                <a class="mvb-hero-product" href="<?= BASE_URL ?>/src/views/catalogo.php?buscar=REVIGAL"><img src="<?= BASE_URL ?>/assets/img/limpieza-productos/re551/re551_01.png" alt="Limpiador de motores Revigal"><span>Cuidado del motor <strong>Ponelo a punto ↗</strong></span></a>
            </div>
        </div>
    </section>
    <div class="mvb-service-strip mvb-wide"><span>ACCESORIOS PARA TU VEHÍCULO</span><span>PRECIOS Y STOCK EN CADA FICHA</span><a href="https://wa.me/5491162982496" target="_blank" rel="noopener noreferrer">ASESORAMIENTO POR WHATSAPP ↗</a></div>

    <section class="mvb-wide mvb-home-section" id="productos" aria-labelledby="selection-title">
        <div class="mvb-section-heading"><div><p class="mvb-eyebrow">ENCONTRÁ TU PRÓXIMO DETALLE</p><h2 id="selection-title">Dale una vuelta a tu auto.</h2></div><div class="mvb-rail-controls"><button type="button" data-product-scroll="-1" aria-label="Ver productos anteriores">←</button><button type="button" data-product-scroll="1" aria-label="Ver más productos">→</button><a href="<?= BASE_URL ?>/src/views/catalogo.php">Ver todo ↗</a></div></div>
        <?php if ($seleccion): ?>
        <div class="mvb-product-rail" id="homeProductRail" tabindex="0" role="region" aria-label="Carrusel de productos, desplazable horizontalmente">
            <?php foreach ($seleccion as $p): ?><?= renderProductCard($p) ?><?php endforeach; ?>
        </div>
        <?php else: ?><p>Explorá el catálogo para consultar nuestra selección.</p><?php endif; ?>
    </section>

    <section class="mvb-wide mvb-home-section" aria-labelledby="category-title">
        <div class="mvb-section-heading"><h2 id="category-title">Explorá</h2></div>
        <nav class="mvb-page-shortcuts" aria-label="Accesos principales">
            <a href="<?= BASE_URL ?>/src/views/catalogo.php"><strong>Catálogo</strong></a>
            <a href="<?= BASE_URL ?>/src/views/carrito.php"><strong>Carrito</strong></a>
            <a href="<?= BASE_URL ?>/src/views/<?= !empty($_SESSION['usuario']) ? 'user/profile.php' : 'auth/login.php' ?>"><strong>Cuenta</strong></a>
            <a href="#como-empezar"><strong>Contacto</strong></a>
        </nav>
    </section>

    <section class="mvb-wide mvb-home-section" aria-labelledby="brands-title">
        <div class="mvb-section-heading"><h2 id="brands-title">Marcas</h2></div>
        <div class="mvb-brands-strip">
            <?php foreach (['revigal' => 'Revigal', 'iael' => 'IAEL', 'goodyear' => 'Goodyear', 'barbie' => 'Barbie', 'armorall' => 'Armor All', 'california-scents' => 'California Scents'] as $archivo => $marca): ?>
            <a href="<?= BASE_URL ?>/src/views/catalogo.php?buscar=<?= rawurlencode($marca) ?>" aria-label="Ver productos <?= htmlspecialchars($marca) ?>"><img src="<?= BASE_URL ?>/assets/img/marca-<?= $archivo ?>.png" alt="<?= htmlspecialchars($marca) ?>" loading="lazy"></a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rail = document.getElementById('homeProductRail');
    document.querySelectorAll('[data-product-scroll]').forEach(button => {
        button.addEventListener('click', () => {
            if (rail) rail.scrollBy({left: Number(button.dataset.productScroll) * rail.clientWidth * .85, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
        });
    });
});
</script>
<?php require_once __DIR__ . '/src/views/_layouts/footer.php'; ?>
