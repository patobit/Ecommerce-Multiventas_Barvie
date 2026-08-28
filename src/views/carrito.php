<?php
require_once __DIR__ . '/../config/rutas.php';
require_once __DIR__ . '/_layouts/header.php';
?>

<main class="container my-5">

    <!-- ENCABEZADO -->
    <div class="mb-4">
        <h1 class="fw-bold text-white text-uppercase tracking-wide">
            Mi Carrito
        </h1>

        <p class="text-secondary">
            Revisá los productos que agregaste antes de finalizar tu compra.
        </p>
    </div>

    <div class="row g-4">

        <!-- ==========================================
             PRODUCTOS DEL CARRITO
             ========================================== -->
        <div class="col-12 col-lg-8">

            <div class="card card-premium p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h5 fw-bold text-white mb-0">
                        Productos
                    </h2>

                    <span class="text-secondary small" id="cartItemCount">
                        0 productos
                    </span>
                </div>

                <!-- Acá JavaScript va a colocar los productos -->
                <div id="cartItems">

                    <div class="text-center py-5">
                        <p class="text-secondary mb-0">
                            Tu carrito está vacío.
                        </p>
                    </div>

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

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">
                        Subtotal
                    </span>

                    <span class="text-white" id="cartSubtotal">
                        $0,00
                    </span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">
                        Envío
                    </span>

                    <span class="text-white" id="cartShipping">
                        $0,00
                    </span>
                </div>

                <hr class="border-secondary border-opacity-25">

                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold text-white">
                        Total
                    </span>

                    <span class="fw-bold text-danger fs-5" id="cartTotal">
                        $0,00
                    </span>
                </div>

                <button type="button"
                        class="btn btn-premium-red w-100 fw-semibold">
                    Finalizar compra
                </button>

                <a href="<?= BASE_URL ?>/src/views/catalogo.php"
                   class="btn btn-premium-outline w-100 mt-2">
                    Seguir comprando
                </a>

            </div>

        </div>

    </div>

</main>

<?php
require_once __DIR__ . '/_layouts/footer.php';
?>