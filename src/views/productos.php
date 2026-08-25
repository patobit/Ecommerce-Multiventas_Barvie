<?php
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/productos_controller.php';

$idProducto = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = $idProducto > 0 ? obtenerProductoPorId($pdo, $idProducto) : null;

require_once __DIR__ . '/_layouts/header.php';
?>
    <main class="container my-5">

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
                $tieneOferta = !empty($producto['precio_oferta']) && (float) $producto['precio_oferta'] < (float) $producto['precio'];

                if ((int) $producto['stock'] <= 0) {
                    $stockHtml = '<span class="text-danger fw-semibold">● Sin Stock</span>';
                } elseif ((int) $producto['stock'] <= 5) {
                    $stockHtml = '<span class="text-warning fw-semibold">● Últimas ' . (int) $producto['stock'] . ' unidades</span>';
                } else {
                    $stockHtml = '<span class="text-success fw-semibold">● Stock disponible (' . (int) $producto['stock'] . ')</span>';
                }
            ?>

            <a href="<?= BASE_URL ?>/src/views/catalogo.php?categoria=<?= (int) $producto['id_categoria'] ?>" class="text-secondary text-decoration-none small d-inline-block mb-4">← Volver a <?= htmlspecialchars($producto['categoria_nombre']) ?></a>

            <div class="row g-5">

                <!-- Carrusel de fotos -->
                <div class="col-12 col-lg-6">
                    <div id="productoCarousel" class="carousel slide bg-dark border border-secondary border-opacity-25 rounded-3 overflow-hidden" data-bs-ride="false">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($producto['imagen']) ?>"
                                         class="d-block w-100" style="height:420px;object-fit:cover;"
                                         alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center" style="height:420px;background-color:#0b0c0e;">
                                        <span class="text-secondary">Sin imagen todavía</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- TODO: cuando haya varias fotos por producto, agregar acá más <div class="carousel-item"> -->
                        </div>
                        <!-- Controles ocultos por ahora (solo hay 1 foto); al agregar más fotos, descomentar: -->
                        <!--
                        <button class="carousel-control-prev" type="button" data-bs-target="#productoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        -->
                    </div>
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

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="input-group" style="max-width: 140px;">
                            <button class="btn btn-premium-outline" type="button" id="qtyMinus">−</button>
                            <input type="number" id="qtyInput" class="form-control form-control-premium text-center" value="1" min="1" max="<?= (int) $producto['stock'] ?>">
                            <button class="btn btn-premium-outline" type="button" id="qtyPlus">+</button>
                        </div>
                        <button class="btn btn-premium-red flex-grow-1 py-2"
                                onclick="addToCart(<?= (int) $producto['id_producto'] ?>, document.getElementById('qtyInput').value, '<?= addslashes($producto['nombre']) ?>')"
                                <?= (int) $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?= (int) $producto['stock'] <= 0 ? 'Sin stock' : 'Añadir al carrito' ?>
                        </button>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <script>
        // Botones +/- de cantidad (respetando el stock disponible)
        document.addEventListener('DOMContentLoaded', () => {
            const qtyInput = document.getElementById('qtyInput');
            const qtyMinus = document.getElementById('qtyMinus');
            const qtyPlus = document.getElementById('qtyPlus');
            if (qtyInput && qtyMinus && qtyPlus) {
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