<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/../controllers/auth/carrito.php';

// Antes había un id_usuario=2 "temporal". Ahora se exige login real.
if (empty($_SESSION['usuario']['id']) || empty($_SESSION['usuario']['jwt'])) {
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}

$idUsuario = (int) $_SESSION['usuario']['id'];
$jwt       = $_SESSION['usuario']['jwt'];

require_once __DIR__ . '/_layouts/header.php';

try {
    $resultado = obtenerProductosDelCarrito($jwt, $idUsuario);
} catch (RuntimeException $e) {
    echo '<div class="container alert alert-warning">' . htmlspecialchars($e->getMessage()) . '</div>';
    require_once __DIR__ . '/_layouts/footer.php';
    exit;
}

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
            <?php $imagenProducto = $producto['imagenes'][0] ?? null; ?>
            <?php if ($imagenProducto): ?>
                <img
                    src="<?= htmlspecialchars($imagenProducto) ?>"
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

                <!-- eliminar -->
            <button type="button" class="btn-eliminar btn btn-sm btn-outline-danger mt-2" aria-label="Eliminar producto" title="Eliminar">🗑️</button>
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
                        $<?= number_format($total, 2, ',', '.') ?>
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
                        $<?= number_format($total, 2, ',', '.') ?>
                    </span>

                </div>


                <!-- FINALIZAR COMPRA -->
                <?php if (!empty($productos)): ?>
                 <a href="<?= BASE_URL ?>/src/views/compra_finalizada.php"
                    class="btn btn-premium-red w-100 fw-semibold">
                     Finalizar compra
                 </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-premium-red w-100 fw-semibold" disabled>
                         Finalizar compra
                        </button>
                    <?php endif; ?>


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


<!-- Cartel de confirmación para eliminar -->
<div id="modalEliminar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:2000;align-items:center;justify-content:center;">
    <div class="card card-premium p-4 text-center" style="max-width:360px;width:90%;">
        <h2 class="h5 fw-bold text-white mb-2">¿Desea eliminar el producto?</h2>
        <p class="text-secondary small mb-4">Se va a quitar de tu carrito.</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-premium-outline w-50" id="btnEliminarNo">No</button>
            <button type="button" class="btn btn-premium-red w-50" id="btnEliminarSi">Sí</button>
        </div>
    </div>
</div>

<script type="module">
import { actualizarCantidad, eliminarDelCarrito } from "../../assets/js/carrito.js";

const modal = document.getElementById('modalEliminar');
let idPendiente = null;

function pedirConfirmacion(idDetalle) {
    idPendiente = idDetalle;
    modal.style.display = 'flex';
}
function cerrarModal() {
    idPendiente = null;
    modal.style.display = 'none';
}

document.getElementById('btnEliminarNo').addEventListener('click', cerrarModal);
modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });

document.getElementById('btnEliminarSi').addEventListener('click', async () => {
    if (idPendiente === null) return;
    const resultado = await eliminarDelCarrito(idPendiente);
    if (resultado.success) {
        location.reload(); // recarga: lista, total y "Mi Carrito (n)" quedan actualizados
    } else {
        cerrarModal();
        alert(resultado.message);
    }
});

document.getElementById('cartItems').addEventListener('click', async (e) => {
    const item = e.target.closest('.cart-item');
    if (!item) return;

    const idDetalle = parseInt(item.dataset.id, 10);

    if (e.target.closest('.btn-eliminar')) {
        pedirConfirmacion(idDetalle);
        return;
    }

    if (e.target.closest('.btn-menos') || e.target.closest('.btn-mas')) {
        const spanCantidad = item.querySelector('.quantity-box span');
        let nuevaCantidad = parseInt(spanCantidad.textContent, 10);
        nuevaCantidad += e.target.closest('.btn-mas') ? 1 : -1;

        if (nuevaCantidad <= 0) {
            pedirConfirmacion(idDetalle);
            return;
        }

        const resultado = await actualizarCantidad(idDetalle, nuevaCantidad);
        resultado.success ? location.reload() : alert(resultado.message);
    }
});
</script>



<?php
require_once __DIR__ . '/_layouts/footer.php';
?>