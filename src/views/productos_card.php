<?php
function renderProductCard(array $p): string
{
    $id = (int) $p['id_producto'];
    $name = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
    $url = BASE_URL . '/src/views/detalles_producto.php?id=' . $id;
    $photo = $p['imagenes'][0] ?? null;
    $stock = max(0, (int) $p['stock']);
    $hasOffer = !empty($p['precio_oferta']) && (float)$p['precio_oferta'] < (float)$p['precio'];
    $price = number_format((float)($hasOffer ? $p['precio_oferta'] : $p['precio']), 2, ',', '.');
    ob_start(); ?>
    <div class="mvb-product-item" data-category="<?= (int)$p['id_categoria'] ?>">
        <article class="card-premium mvb-product-card">
            <a class="mvb-card-photo" href="<?= htmlspecialchars($url) ?>" tabindex="-1" aria-hidden="true">
                <?php if ($photo): ?><img src="<?= htmlspecialchars(imagenCatalogo($photo)) ?>" srcset="<?= htmlspecialchars(imagenCatalogo($photo, 360)) ?> 360w, <?= htmlspecialchars(imagenCatalogo($photo, 640)) ?> 640w" sizes="(max-width: 600px) 46vw, (max-width: 991px) 42vw, 300px" width="640" height="640" loading="lazy" decoding="async" alt="<?= $name ?>">
                <?php else: ?><span class="mvb-photo-pending">Foto pendiente</span><?php endif; ?>
            </a>
            <div class="mvb-card-body">
                <p class="mvb-card-category"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></p>
                <h3><a href="<?= htmlspecialchars($url) ?>"><?= $name ?></a></h3>
                <p class="mvb-card-stock <?= $stock ? 'is-available' : '' ?>"><?= $stock ? 'En stock' : 'Sin stock' ?></p>
                <div class="mvb-card-bottom">
                    <?php if ($hasOffer): ?><del>$<?= number_format((float)$p['precio'], 2, ',', '.') ?></del><?php endif; ?>
                    <p class="mvb-card-price">$<?= $price ?></p>
                    <button type="button" class="btn btn-premium-red" <?= $stock ? '' : 'disabled' ?> aria-label="Agregar <?= $name ?> al carrito" onclick="addToCart(<?= $id ?>, 1, <?= htmlspecialchars(json_encode($p['nombre'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)"><?= $stock ? 'Agregar al carrito' : 'Sin stock' ?></button>
                </div>
            </div>
        </article>
    </div>
    <?php return ob_get_clean();
}
