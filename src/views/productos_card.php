<?php
// =============================================================================
// RENDERIZADO DE TARJETAS DE PRODUCTO (SOLO VISTA/HTML — SIN CONSULTAS A LA DB)
// =============================================================================
// Esta función arma el HTML de una tarjeta de producto a partir de un array
// asociativo con los datos ya obtenidos. NO se conecta a la base de datos:
// espera recibir el array $p ya resuelto (por eso puede usarse en la home,
// en el catálogo, etc. sin importar de dónde vino el dato).
//
// El array $p viene de productos_controller.php -> mapearProducto(), con las
// claves: id_producto, nombre, descripcion, imagenes (array de URLs), stock,
// precio, id_categoria, categoria_nombre.
// Opcionalmente puede traer 'precio_oferta' si en el futuro se agrega ese
// campo en Strapi (por ahora nunca viene, así que $tieneOferta da false).

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

    // Imagen real si existe, o un placeholder si el producto todavía no tiene una cargada.
    // Las URLs de 'imagenes' ya vienen absolutas (apuntan al servidor de Strapi),
    // así que NO hay que anteponerles BASE_URL/assets/img/ como antes.
    $primeraImagen = $p['imagenes'][0] ?? null;
    if ($primeraImagen) {
        $imagenHtml = '<img src="' . htmlspecialchars($primeraImagen) . '" '
        . 'class="w-100 rounded-3 mb-3" style="height:160px;object-fit:cover;" alt="' . htmlspecialchars($p['nombre']) . '">';
        } elseif ($logoMarca = obtenerLogoMarca($p)) {
            $imagenHtml = '<div class="mvb-brand-placeholder"><img src="' . htmlspecialchars($logoMarca) . '" alt="Marca del producto"><span>Foto del producto pendiente</span></div>';
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
                        <button type="button"
        class="btn btn-premium-red"
        <?= $disabledAttr ?>
        onclick="event.stopPropagation(); addToCart(<?= (int) $p['id_producto'] ?>, 1, <?= htmlspecialchars(json_encode($p['nombre'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)">
    Agregar al carrito
</button>

            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
    }
