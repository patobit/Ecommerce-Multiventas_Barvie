<?php
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/auth/productos_controller.php';

$idProducto = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = $idProducto > 0 ? obtenerProductoPorId($idProducto) : null;
$isCatalogPage = true;

require_once __DIR__ . '/_layouts/header.php';
?>
    <main class="container my-5 mvb-product-detail">

        <?php if (!$producto): ?>

            <!-- ================================================================
                 PRODUCTO NO ENCONTRADO
                 ================================================================ -->
            <div class="p-5 text-center bg-dark border border-secondary border-opacity-25 rounded-3">
                <p class="text-secondary mb-3">No encontramos el producto que buscás.</p>
                <a href="<?= BASE_URL ?>/src/views/catalogo.php" class="btn btn-premium-red">Volver al catálogo</a>
            </div>

        <?php else: ?>

            <!-- ================================================================
                 VISTA DE DETALLE
                 ================================================================ -->
            <?php
                $precio = number_format((float) $producto['precio'], 2, ',', '.');
                // NOTA: 'precio_oferta' todavía no existe como campo en Strapi,
                // así que $tieneOferta siempre da false por ahora (ver aviso en
                // productos_controller.php -> obtenerProductosOferta()).
                $tieneOferta = !empty($producto['precio_oferta']) && (float) $producto['precio_oferta'] < (float) $producto['precio'];

                if ((int) $producto['stock'] <= 0) {
                    $stockHtml = '<span class="text-danger fw-semibold">● Sin Stock</span>';
                } elseif ((int) $producto['stock'] <= 5) {
                    $stockHtml = '<span class="text-warning fw-semibold">● Últimas ' . (int) $producto['stock'] . ' unidades</span>';
                } else {
                    $stockHtml = '<span class="text-success fw-semibold">● Stock disponible (' . (int) $producto['stock'] . ')</span>';
                }

                // Strapi puede devolver varias imágenes por producto (campo "Imagen"
                // es "multiple"), ya como URLs absolutas. Antes era un solo nombre
                // de archivo local en /assets/img/.
                $imagenes = $producto['imagenes'] ?? [];
                $esLogoMarca = empty($imagenes) && ($logoMarca = obtenerLogoMarca($producto));
                if ($esLogoMarca) $imagenes = [$logoMarca];
            ?>

            <a href="<?= BASE_URL ?>/src/views/catalogo.php?categoria=<?= (int) $producto['id_categoria'] ?>" class="text-secondary text-decoration-none small d-inline-block mb-4">← Volver a <?= htmlspecialchars($producto['categoria_nombre']) ?></a>

            <div class="row g-5">

                <!-- Carrusel de fotos -->
                <div class="col-12 col-lg-6">
                    <div id="productoCarousel" class="mvb-product-gallery rounded-3 overflow-hidden">
                        <div class="mvb-gallery-stage">
                            <?php if (!empty($imagenes)): ?>
                                <?php foreach ($imagenes as $i => $url): ?>
                                    <div class="mvb-gallery-slide" <?= $i === 0 ? '' : 'hidden' ?>>
                                        <img src="<?= htmlspecialchars($url) ?>"
                                             class="d-block w-100 product-detail-photo"
                                             alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="mvb-gallery-slide">
                                    <div class="d-flex align-items-center justify-content-center" style="height:420px;background-color:#0b0c0e;">
                                        <span class="text-secondary">Sin imagen todavía</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (count($imagenes) > 1): ?>
                            <button class="mvb-gallery-prev" type="button" aria-label="Foto anterior" data-gallery-step="-1">
                                ←
                            </button>
                            <button class="mvb-gallery-next" type="button" aria-label="Foto siguiente" data-gallery-step="1">
                                →
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($esLogoMarca): ?><p class="text-secondary small mt-2">Logo de la marca · Foto del producto pendiente.</p><?php endif; ?>
                    <?php if (count($imagenes) > 1): ?>
                    <div class="mvb-gallery-thumbs" aria-label="Elegir foto del producto">
                        <?php foreach ($imagenes as $i => $url): ?>
                        <button type="button" data-gallery-index="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"><img src="<?= htmlspecialchars($url) ?>" alt="" loading="lazy"></button>
                        <?php endforeach; ?>
                    </div><p class="text-secondary small mt-2" id="galleryPosition">Foto 1 de <?= count($imagenes) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Info del producto -->
                <div class="col-12 col-lg-6">
                    <span class="badge badge-premium-red mb-3"><?= htmlspecialchars($producto['categoria_nombre']) ?></span>
                    <h1 class="fw-bold text-white mb-3"><?= htmlspecialchars($producto['nombre']) ?></h1>

                    <div class="mb-3"><?= $stockHtml ?></div>

                    <div class="mb-4">
                        <?php if ($tieneOferta): ?>
                            <span class="me-2 text-secondary text-decoration-line-through fs-5">$<?= $precio ?></span>
                            <span class="display-6 fw-bold text-danger">$<?= number_format((float) $producto['precio_oferta'], 2, ',', '.') ?></span>
                        <?php else: ?>
                            <span class="display-6 fw-bold text-white">$<?= $precio ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="text-secondary mb-4"><?= nl2br(htmlspecialchars($producto['descripcion'] ?? 'Sin descripción disponible.')) ?></p>

                    <label for="qtyInput" class="form-label">Cantidad</label>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <div class="input-group" style="max-width: 140px;">
                            <button class="btn btn-premium-outline" type="button" id="qtyMinus">−</button>
                            <input type="number" id="qtyInput" aria-label="Cantidad de unidades" class="form-control form-control-premium text-center" value="1" min="1" max="<?= max(1, (int) $producto['stock']) ?>" <?= (int) $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                            <button class="btn btn-premium-outline" type="button" id="qtyPlus">+</button>
                        </div>
                        <button type="button" class="btn btn-premium-red product-add-button flex-grow-1 py-3"
                                onclick="addToCart(<?= (int) $producto['id_producto'] ?>, document.getElementById('qtyInput').value, <?= htmlspecialchars(json_encode($producto['nombre'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)"
                                <?= (int) $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?= (int) $producto['stock'] <= 0 ? 'Sin stock' : 'Añadir al carrito' ?>
                        </button>
                    </div>
                    <div class="product-detail-help"><strong>¿Tenés dudas sobre este producto?</strong><p>Consultanos antes de comprar. Respondemos consultas en todo momento.</p><a href="https://wa.me/5491162982496?text=<?= rawurlencode('Hola MVB, quiero consultar por ' . $producto['nombre']) ?>" target="_blank" rel="noopener noreferrer">Consultar por WhatsApp ↗</a></div>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <script >
        // Botones +/- de cantidad (respetando el stock disponible)
        document.addEventListener('DOMContentLoaded', () => {
            const slides = Array.from(document.querySelectorAll('.mvb-gallery-slide'));
            const thumbnails = Array.from(document.querySelectorAll('[data-gallery-index]'));
            let selectedPhoto = 0;
            const showPhoto = index => {
                if (!slides.length) return;
                selectedPhoto = (index + slides.length) % slides.length;
                slides.forEach((slide, i) => { slide.hidden = i !== selectedPhoto; });
                thumbnails.forEach((button, i) => button.setAttribute('aria-pressed', String(i === selectedPhoto)));
                const position = document.getElementById('galleryPosition');
                if (position) position.textContent = `Foto ${selectedPhoto + 1} de ${slides.length}`;
            };
            document.querySelectorAll('[data-gallery-step]').forEach(button => button.addEventListener('click', () => showPhoto(selectedPhoto + Number(button.dataset.galleryStep))));
            thumbnails.forEach(button => button.addEventListener('click', () => showPhoto(Number(button.dataset.galleryIndex))));
            const qtyInput = document.getElementById('qtyInput');
            const qtyMinus = document.getElementById('qtyMinus');
            const qtyPlus = document.getElementById('qtyPlus');
            if (qtyInput && qtyMinus && qtyPlus) {
                const normalizeQuantity = () => {
                    qtyInput.value = Math.max(1, Math.min(parseInt(qtyInput.max, 10), parseInt(qtyInput.value, 10) || 1));
                };
                qtyInput.addEventListener('change', normalizeQuantity);
                qtyMinus.disabled = qtyPlus.disabled = qtyInput.disabled;
                qtyMinus.addEventListener('click', () => {
                    qtyInput.value = Math.max(1, parseInt(qtyInput.value || '1', 10) - 1);
                });
                qtyPlus.addEventListener('click', () => {
                    const max = parseInt(qtyInput.max || '999', 10);
                    qtyInput.value = Math.min(max, parseInt(qtyInput.value || '1', 10) + 1);
                });
            }
        });
    </script>

<?php
require_once __DIR__ . '/_layouts/footer.php';
?>
