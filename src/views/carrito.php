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

$_SESSION['checkout_csrf'] ??= bin2hex(random_bytes(32));
$idUsuario = (int) $_SESSION['usuario']['id'];
$jwt       = $_SESSION['usuario']['jwt'];

require_once __DIR__ . '/_layouts/header.php';

try {
    $resultado = obtenerProductosDelCarrito($jwt, $idUsuario);
} catch (RuntimeException $e) {
    echo '<div class="container alert alert-warning">No se pudo consultar el carrito. Intentá de nuevo.</div>';
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
                        Total de productos
                    </span>

                    <span class="fw-bold text-danger fs-5" id="cartTotal">
                        $<?= number_format($total, 2, ',', '.') ?>
                    </span>

                </div>


<form id="checkoutForm" class="checkout-form">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['checkout_csrf']) ?>">
<input type="hidden" name="carrito" value="<?= htmlspecialchars($resultado['id_carrito'] ?? '') ?>">
<h2 class="h5 text-white mb-3">Datos del pedido</h2>
<label for="pedido-entrega">Entrega</label><select id="pedido-entrega" name="entrega" class="form-select form-control-premium" required><option value="Retiro">Retiro en la tienda</option><option value="Envio">Envío a domicilio</option></select>
<label for="pedido-pago">Método de pago</label><select id="pedido-pago" name="pago" class="form-select form-control-premium" required><option value="A coordinar">A coordinar con la tienda</option><option value="Transferencia">Transferencia bancaria</option><option value="Efectivo al retirar">Efectivo al retirar</option></select>
<label for="pedido-nombre">Nombre de quien recibe o retira</label><input id="pedido-nombre" name="nombre" class="form-control form-control-premium" required maxlength="254" autocomplete="name" value="<?= htmlspecialchars(trim(($_SESSION['usuario']['nombre'] ?? '') . ' ' . ($_SESSION['usuario']['apellido'] ?? ''))) ?>">
<label for="pedido-telefono">Teléfono de contacto</label><input id="pedido-telefono" type="tel" name="telefono" class="form-control form-control-premium" required maxlength="40" autocomplete="tel" value="<?= htmlspecialchars($_SESSION['usuario']['telefono'] ?? '') ?>">
<div id="pedido-direccion" hidden>
<label for="pedido-calle">Calle y número</label><input id="pedido-calle" name="direccion" class="form-control form-control-premium" maxlength="254" autocomplete="street-address">
<label for="pedido-ciudad">Ciudad</label><input id="pedido-ciudad" name="ciudad" class="form-control form-control-premium" maxlength="254" autocomplete="address-level2">
<label for="pedido-provincia">Provincia</label><input id="pedido-provincia" name="provincia" class="form-control form-control-premium" maxlength="254" autocomplete="address-level1">
<label for="pedido-cp">Código postal</label><input id="pedido-cp" name="codigo_postal" class="form-control form-control-premium" maxlength="20" autocomplete="postal-code">
<p class="small text-secondary">El costo del envío se confirma con la tienda.</p></div>
<label for="pedido-notas">Indicaciones (opcional)</label><textarea id="pedido-notas" name="notas" class="form-control form-control-premium" maxlength="1000" rows="2"></textarea>
<p class="small text-secondary mt-3">Se guarda tu pedido; este formulario no cobra ni solicita datos de tarjeta.</p>
<button type="submit" class="btn btn-premium-red w-100 fw-semibold" id="btnFinalizarCompra" <?= empty($productos) ? 'disabled' : '' ?>>Confirmar pedido</button>
<p id="checkout-status" role="status" class="small mt-3 mb-0"></p></form>
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

const form = document.getElementById('checkoutForm');
const entrega = document.getElementById('pedido-entrega');
const direccion = document.getElementById('pedido-direccion');
entrega.addEventListener('change', () => {
    const envio = entrega.value === 'Envio';
    direccion.hidden = !envio;
    document.getElementById('cartShipping').textContent = envio ? 'A confirmar' : '$0,00';
    direccion.querySelectorAll('input').forEach(input => input.required = envio);
    const efectivo = document.querySelector('#pedido-pago option[value="Efectivo al retirar"]');
    efectivo.disabled = envio;
    if (envio && document.getElementById('pedido-pago').value === 'Efectivo al retirar') document.getElementById('pedido-pago').value = 'A coordinar';
});
form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const button = document.getElementById('btnFinalizarCompra');
    const status = document.getElementById('checkout-status');
    button.disabled = true;
    status.textContent = 'Guardando pedido…';
    const resultado = await finalizarCompra(Object.fromEntries(new FormData(form)));
    if (resultado.success) location.href = window.BASE_URL + '/src/views/user/pedidos.php';
    else {status.textContent = resultado.message; button.disabled = false;}
});

</script>



<?php
require_once __DIR__ . '/_layouts/footer.php';
?>