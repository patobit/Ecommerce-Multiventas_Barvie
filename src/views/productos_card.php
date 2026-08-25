<?php
// =============================================================================
// RENDERIZADO DE TARJETAS DE PRODUCTO (SOLO VISTA/HTML — SIN CONSULTAS A LA DB)
// =============================================================================
// Esta función arma el HTML de una tarjeta de producto a partir de un array
// asociativo con los datos ya obtenidos. NO se conecta a la base de datos:
// espera recibir el array $p ya resuelto (por eso puede usarse en la home,
// en el catálogo, etc. sin importar de dónde vino el dato).
//
// El array $p debe tener las columnas de la tabla `productos`
// (id_producto, nombre, descripcion, imagen, stock, precio, id_categoria)
// más 'categoria_nombre' (viene de un JOIN con `categorias`).
// Opcionalmente puede traer 'precio_oferta' si esa columna existe.

function renderProductCard(array $p): string
{
    $precio = number_format((float) $p['precio'], 2, ',', '.');

    // Estado de stock
    if ((int) $p['stock'] <= 0) {
        $stockHtml = '<span class="text-danger small fw-semibold">● Sin Stock</span>';
    } elseif ((int) $p['stock'] <= 5) {
        $stockHtml = '<span class="text-warning small fw-semibold">● Últimas Unidades</span>';
    } else {
        $stockHtml = '<span class="text-success small fw-semibold">● Stock Disponible</span>';
    }

    // Precio (con o sin oferta activa)
    $tieneOferta = !empty($p['precio_oferta']) && (float) $p['precio_oferta'] < (float) $p['precio'];
    if ($tieneOferta) {
        $precioOferta = number_format((float) $p['precio_oferta'], 2, ',', '.');
        $precioHtml = '<span class="me-2 text-secondary text-decoration-line-through small">$' . $precio . '</span>'
            . '<span class="fs-5 fw-bold text-danger">$' . $precioOferta . '</span>';
    } else {
        $precioHtml = '<span class="fs-5 fw-bold text-white">$' . $precio . '</span>';
    }

    // Imagen real si existe, o un placeholder si el producto todavía no tiene una cargada
    if (!empty($p['imagen'])) {
        $imagenHtml = '<img src="' . BASE_URL . '/assets/img/' . htmlspecialchars($p['imagen']) . '" '
            . 'class="w-100 rounded-3 mb-3" style="height:160px;object-fit:cover;" alt="' . htmlspecialchars($p['nombre']) . '">';
    } else {
        $imagenHtml = '<div class="w-100 rounded-3 mb-3 d-flex align-items-center justify-content-center" '
            . 'style="height:160px;background-color:#0b0c0e;border:1px dashed #495057;">'
            . '<span class="text-secondary small">Sin imagen</span></div>';
    }

    $disabledAttr = (int) $p['stock'] <= 0 ? 'disabled' : '';

    ob_start();
    ?>
    <div class="col-12 col-md-6" data-category="<?= (int) $p['id_categoria'] ?>">
        <div class="card card-premium h-100 d-flex flex-column justify-content-between p-4" onclick="openProductDetail(<?= (int) $p['id_producto'] ?>)">
            <div>
                <?= $imagenHtml ?>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge badge-premium-red"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></span>
                    <?= $stockHtml ?>
                </div>
                <h4 class="h5 fw-bold text-white mb-2"><?= htmlspecialchars($p['nombre']) ?></h4>
                <p class="text-secondary small mb-3"><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>
            </div>
            <div class="border-top border-secondary border-opacity-10 pt-3 mt-3 d-flex justify-content-between align-items-center" onclick="event.stopPropagation();">
                <span><?= $precioHtml ?></span>
                <button class="btn btn-sm btn-premium-red" onclick="addToCart(<?= (int) $p['id_producto'] ?>, 1, '<?= addslashes($p['nombre']) ?>')" <?= $disabledAttr ?>>Añadir</button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}