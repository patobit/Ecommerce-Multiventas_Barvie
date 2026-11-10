<?php
// El header necesita poder arrancar sesión porque se incluye desde páginas a
// distinta profundidad (raíz, src/views/, etc.) y algunas de esas páginas
// (como catalogo.php) no la inician antes de llegar acá.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../controllers/auth/productos_controller.php';
require_once __DIR__ . '/../../controllers/auth/carrito.php';

$categoriasNavbar = obtenerCategorias(null); // categorías principales

// Contador del botón "Mi Carrito". Antes usaba un id_usuario=2 "temporal";
// ahora que el login es real, si no hay nadie logueado el contador queda en 0
// (no tiene sentido pedirle el carrito a Strapi sin un usuario autenticado).
$cantidadCarrito = 0;
if (!empty($_SESSION['usuario']['id']) && !empty($_SESSION['usuario']['jwt'])) {
    try {
    $resultadoCarrito = obtenerProductosDelCarrito($_SESSION['usuario']['jwt'], (int) $_SESSION['usuario']['id']);
    foreach ($resultadoCarrito['productos'] as $producto) {
        $cantidadCarrito += (int) $producto['cantidad'];
    }
    } catch (RuntimeException $e) { error_log($e->getMessage()); }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVB | Accesorios, herramientas y cuidado del auto</title>
    <meta name="description" content="Accesorios, limpieza, iluminación y herramientas para tu vehículo. Mirá fotos, precios y stock en MVB Multiventas.">
    <link rel="preconnect" href="https://res.cloudinary.com">
    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/style.css') ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/storefront.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/storefront.css') ?>" rel="stylesheet">
</head>

<body class="<?= !empty($isHomePage) ? 'mvb-home' : (!empty($isCatalogPage) ? 'mvb-catalog-page' : (!empty($isOrdersPage) ? 'mvb-orders-page' : '')) ?>">

    <a class="mvb-skip-link" href="#contenido">Saltar al contenido</a>
    <!-- Header / Navbar -->
    <header class="mvb-site-header">
        <div class="mvb-header-ribbon"><span>ACCESORIOS · LIMPIEZA · HERRAMIENTAS</span><a href="<?= BASE_URL ?>/index.php#como-empezar">Contactanos ↗</a></div>
        <nav class="navbar navbar-expand-xl navbar-dark navbar-premium py-3">
            <div class="container">
                <!-- Marca actualizada a MVB -->
                <a class="navbar-brand fw-bold text-uppercase tracking-wider" href="<?= BASE_URL ?>/index.php">
                    <img class="mvb-nav-logo" src="<?= BASE_URL ?>/assets/img/mvb-logo-aprobado.png" alt="MVB Multiventas" width="190" height="95">
                </a>
                <div class="flex-grow-1 mx-lg-4 my-2 my-lg-0 order-3 order-lg-0 position-relative" id="searchWrapper">
                    <label class="mvb-global-search-label" for="searchInput">Buscar en toda la tienda</label>
                    <form class="d-flex" role="search" id="<?= !empty($isHomePage) || !empty($isCatalogPage) || !empty($isAuthPage) ? 'catalogSearchForm' : 'searchForm' ?>" method="GET" action="<?= BASE_URL ?>/src/views/catalogo.php">
                        <div class="input-group">
                            <input type="search" class="form-control form-control-premium border-end-0" id="searchInput" name="buscar" value="<?= !empty($isCatalogPage) ? htmlspecialchars($busqueda ?? '') : '' ?>" placeholder="Buscar productos, marcas y más..." aria-label="Buscar en toda la tienda" autocomplete="off">
                            <button class="btn btn-premium-red px-3" type="submit" id="searchBtn" aria-label="Buscar">
                                <span aria-hidden="true">⌕</span>
                            </button>
                        </div>
                    </form>
                    <div id="searchSuggestions" class="d-none"></div>
                </div>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navOpcion2" aria-controls="navOpcion2" aria-expanded="false" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navOpcion2">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2 align-items-lg-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white fw-semibold dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categorías
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="categoryDropdown" id="categoryList">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/src/views/catalogo.php">Todo el Catálogo</a></li>
                                <?php foreach ($categoriasNavbar as $cat): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/src/views/catalogo.php?categoria=<?= (int) $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link text-white fw-semibold" href="<?= BASE_URL ?>/src/views/catalogo.php">Catálogo</a></li>
                        <li class="nav-item"><a class="nav-link text-secondary" href="<?= BASE_URL ?>/index.php#productos">Destacados</a></li>
                        <li class="nav-item"><a class="nav-link text-secondary" href="<?= BASE_URL ?>/index.php#como-empezar">Conocé MVB</a></li>
                    </ul>
                    <div class="ms-lg-4 d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>/src/views/carrito.php"
                           class="btn btn-premium-red btn-sm px-3 py-2 fw-semibold"
                           id="cartBtn">
                            Mi Carrito (<?= $cantidadCarrito ?>)
                        </a>

                        <!-- Detección de Usuario / Sesión -->
                        <?php if (isset($_SESSION['usuario_id']) || isset($_SESSION['nombre'])): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-light btn-sm px-3 py-2 fw-semibold dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="mvb-account-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['nombre'] ?? 'M', 0, 1))) ?></span><span><?= htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Mi Cuenta') ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userMenu">
                                    <li class="mvb-menu-caption">TU CUENTA MVB</li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/src/views/user/pedidos.php">Mis pedidos ↗</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/src/views/user/profile.php">Mi perfil ↗</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/src/views/carrito.php">Mi carrito ↗</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/src/controllers/auth/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/src/views/auth/login.php"
                               class="btn btn-outline-light btn-sm px-3 py-2 fw-semibold border-secondary border-opacity-50">
                                Ingresar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

    </header>
