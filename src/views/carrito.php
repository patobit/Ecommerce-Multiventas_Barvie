<?php
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/_layouts/header.php';
require_once __DIR__ . '/../controllers/auth/carrito.php';

$id_usuario = 2; // temporal

$resultado = obtenerProductosDelCarrito($pdo, $id_usuario);

$productos = $resultado['productos'] ?? [];
$total = (float) ($resultado['total'] ?? 0);

// Cantidad total de unidades
$cantidadProductos = 0;

foreach ($productos as $producto) {
    $cantidadProductos += (int) $producto['cantidad'];
}
?>
<main class="container my-5">

    <!-- TÍTULO DEL CARRITO -->
    <div class="mb-4">
        <h1 class="fw-bold text-white text-uppercase tracking-wide">
             Mi Carrito
        </h1>

        <p class="text-secondary">
            Revisá los productos que agregaste antes de finalizar tu compra.
        </p>
    </div>


    <!-- CONTENIDO DEL CARRITO -->
    <div class="row g-4">

        <!-- ==========================================
             LISTA DE PRODUCTOS
             ========================================== -->
        <div class="col-12 col-lg-8">

            <div class="card card-premium p-4">

                <!-- ENCABEZADO -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h2 class="h5 fw-bold text-white mb-0">
                        Productos
                    </h2>

                    <span class="text-secondary small" id="cartItemCount">
                        <?= count($productos) ?> productos
                    </span>

                </div>


                <!-- PRODUCTOS -->
                <div id="cartItems">

                    <?php if (empty($productos)): ?>

                        <div class="text-center py-5">
                            <div class="fs-1 mb-3">🛒</div>

                            <p class="text-secondary mb-0">
                                Tu carrito está vacío.
                            </p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($productos as $producto): ?>

    
        <div class="cart-item" data-id="<?= (int) $producto['id_detalle_carrito'] ?>"> 

        <!-- IMAGEN DEL PRODUCTO -->
        <div class="cart-product-image">
            <?php if (!empty($producto['imagen'])): ?>
                <img
                    src="<?= htmlspecialchars($producto['imagen']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                >
            <?php else: ?>
                <span>🛒</span>
            <?php endif; ?>
        </div>


        <!-- INFORMACIÓN DEL PRODUCTO -->
        <div class="cart-product-info">

            <h3 class="cart-product-name">
                <?= htmlspecialchars($producto['nombre']) ?>
            </h3>

            <p class="cart-product-description">
                <?= htmlspecialchars($producto['descripcion']) ?>
            </p>

            <p class="cart-product-price">
                $<?= number_format($producto['precio'], 2, ',', '.') ?>
            </p>

        </div>


        <!-- CANTIDAD -->
        <div class="cart-product-quantity">

            <span class="quantity-label">
                Cantidad
            </span>

            <div class="quantity-box">
                <button type="button" class="btn-menos">−</button>

                <span>
                    <?= (int)$producto['cantidad'] ?>
                </span>

                <button type="button" class="btn-mas">+</button>
            </div>

        </div>


        <!-- SUBTOTAL -->
        <div class="cart-product-subtotal">

            <span class="subtotal-label">
                Subtotal
            </span>

            <strong>
                $<?= number_format($producto['subtotal'], 2, ',', '.') ?>
            </strong>

            <button type="button" class="btn-eliminar btn btn-sm btn-outline-danger mt-2">🗑️ Quitar</button>

        </div>

    </div>

<?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- ==========================================
             RESUMEN DE COMPRA
             ========================================== -->
        <div class="col-12 col-lg-4">

            <div class="card card-premium p-4">

                <h2 class="h5 fw-bold text-white mb-4">
                    Resumen de compra
                </h2>


                <!-- SUBTOTAL -->
                <div class="d-flex justify-content-between mb-3">

                    <span class="text-secondary">
                        Subtotal
                    </span>

                    <span class="text-white" id="cartSubtotal">
                        $<?= number_format($resultado['total'], 2, ',', '.') ?>
                    </span>

                </div>


                <!-- ENVÍO -->
                <div class="d-flex justify-content-between mb-3">

                    <span class="text-secondary">
                        Envío
                    </span>

                    <span class="text-white" id="cartShipping">
                        $0,00
                    </span>

                </div>


                <hr class="border-secondary border-opacity-25">


                <!-- TOTAL -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <span class="fw-bold text-white">
                        Total
                    </span>

                    <span class="fw-bold text-danger fs-5" id="cartTotal">
                        $<?= number_format($resultado['total'], 2, ',', '.') ?>
                    </span>

                </div>


                <!-- FINALIZAR COMPRA -->
                <button
                     type="button"
                    class="btn btn-premium-red w-100 fw-semibold"
                    id="btnFinalizarCompra"
                    >
                      Finalizar compra
                </button>


                <!-- SEGUIR COMPRANDO -->
                <a
                    href="<?= BASE_URL ?>/src/views/catalogo.php"
                    class="btn btn-premium-outline w-100 mt-2"
                >
                    Seguir comprando
                </a>

            </div>

        </div>

    </div>

</main>


<script type="module">
import { actualizarCantidad, eliminarDelCarrito, finalizarCompra } from "../../assets/js/carrito.js"

document.getElementById('cartItems').addEventListener('click', async (e) => {
    const item = e.target.closest('.cart-item');
    if (!item) return;

    const idDetalle = parseInt(item.dataset.id, 10);

    if (e.target.closest('.btn-eliminar')) {
        if (!confirm('¿Eliminar este producto del carrito?')) return;
        const resultado = await eliminarDelCarrito(idDetalle);
        resultado.success ? location.reload() : alert(resultado.message);
        return;
    }

    if (e.target.closest('.btn-menos') || e.target.closest('.btn-mas')) {
        const spanCantidad = item.querySelector('.quantity-box span');
        let nuevaCantidad = parseInt(spanCantidad.textContent, 10);
        nuevaCantidad += e.target.closest('.btn-mas') ? 1 : -1;

        if (nuevaCantidad <= 0) {
            if (!confirm('¿Eliminar este producto del carrito?')) return;
            const resultado = await eliminarDelCarrito(idDetalle);
            resultado.success ? location.reload() : alert(resultado.message);
            return;
        }

        const resultado = await actualizarCantidad(idDetalle, nuevaCantidad);
        resultado.success ? location.reload() : alert(resultado.message);
    }
});

document.getElementById('btnFinalizarCompra').addEventListener('click', async () => {
    const resultado = await finalizarCompra();

    if (resultado.success) {
        alert(resultado.message);
        location.reload();
    } else {
        alert(resultado.message);
    }
});

</script>



<?php
require_once __DIR__ . '/_layouts/footer.php';
?>
