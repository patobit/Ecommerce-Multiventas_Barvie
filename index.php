<?php
require_once __DIR__ . '/src/config/rutas.php';                     // define BASE_URL
require_once __DIR__ . '/src/config/database.php';                  // define $pdo
require_once __DIR__ . '/src/controllers/productos_controller.php'; // consultas a la DB
require_once __DIR__ . '/src/views/productos_card.php';              // renderProductCard()

$secciones = [
    ['titulo' => '🆕 Nuevos Ingresos',      'productos' => obtenerProductosNuevos($pdo, 4)],
    ['titulo' => '🔥 Ofertas de la Semana', 'productos' => obtenerProductosOferta($pdo, 4)],
    ['titulo' => '⭐ Producto Más Vendido', 'productos' => obtenerProductoMasVendido($pdo)],
];

require_once __DIR__ . '/src/views/_layouts/header.php';
?>
    <!-- Main Content -->
    <main class="container my-5" id="productos">
        <div class="row g-4">

            <!-- Grilla de Productos -->
            <div class="col-12">

                <!-- Barra de Orden y Filtro por Precio -->
                <div class="p-3 p-md-4 mb-4 bg-dark border border-secondary border-opacity-25 rounded-3 shadow-sm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="sortSelect" class="form-label text-secondary small text-uppercase fw-semibold mb-1">Ordenar por</label>
                            <select id="sortSelect" class="form-select form-control-premium">
                                <option value="relevancia">Relevancia</option>
                                <option value="asc">Precio: Menor a Mayor</option>
                                <option value="desc">Precio: Mayor a Menor</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="minPrice" class="form-label text-secondary small text-uppercase fw-semibold mb-1">Desde $</label>
                            <input type="number" id="minPrice" class="form-control form-control-premium" placeholder="0" min="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="maxPrice" class="form-label text-secondary small text-uppercase fw-semibold mb-1">Hasta $</label>
                            <input type="number" id="maxPrice" class="form-control form-control-premium" placeholder="Sin límite" min="0">
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button id="applyPriceFilter" class="btn btn-premium-red flex-grow-1" type="button">Aplicar</button>
                            <button id="clearPriceFilter" class="btn btn-premium-outline" type="button" title="Limpiar filtro">✕</button>
                        </div>
                    </div>
                </div>

                <?php foreach ($secciones as $seccion): ?>
                    <?php if (empty($seccion['productos'])) continue; ?>
                    <!-- Sección: <?= htmlspecialchars($seccion['titulo']) ?> -->
                    <div class="mb-5 product-section">
                        <div class="d-flex align-items-center mb-4">
                            <h3 class="fw-bold text-white text-uppercase m-0 tracking-wide"><?= htmlspecialchars($seccion['titulo']) ?></h3>
                            <div class="flex-grow-1 ms-3 border-bottom border-danger border-2 opacity-50"></div>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($seccion['productos'] as $p): ?>
                                <?= renderProductCard($p) ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </main>

<?php
require_once __DIR__ . '/src/views/_layouts/footer.php';
?>
