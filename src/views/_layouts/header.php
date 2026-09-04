<?php
// El header necesita su propia conexión porque se incluye desde páginas a
// distinta profundidad (raíz, src/views/, etc.) — require_once evita que se
// duplique si el archivo que lo incluyó ya la había cargado antes.
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/auth/productos_controller.php';

$categoriasNavbar = obtenerCategorias($pdo, null); // categorías principales

//esto es para el numero que esta en el boton del carrito, para que no se borre
require_once __DIR__ . '/../../controllers/auth/carrito.php';

$id_usuario = 2; // temporal, igual que en el resto del carrito

$resultadoCarrito = obtenerProductosDelCarrito($pdo, $id_usuario);
$cantidadCarrito = 0;
foreach ($resultadoCarrito['productos'] as $producto) {
    $cantidadCarrito += (int) $producto['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiventas Barvie - Accesorios y Repuestos Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Header / Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-premium py-3">
            <div class="container">
                <a class="navbar-brand fw-bold text-uppercase tracking-wider" href="<?= BASE_URL ?>/index.php">
                    <span class="text-white">Multiventas</span><span class="text-danger"> Barvie</span>
                </a>
                <div class="flex-grow-1 mx-lg-4 my-2 my-lg-0 order-3 order-lg-0 position-relative" id="searchWrapper">
                    <form class="d-flex" role="search" id="searchForm">
                        <div class="input-group">
                            <input type="search" class="form-control form-control-premium border-end-0" id="searchInput" placeholder="Buscar productos, marcas y más..." aria-label="Buscar" autocomplete="off">
                            <button class="btn btn-premium-red px-3" type="submit" id="searchBtn" aria-label="Buscar">
                                🔍
                            </button>
                        </div>
                    </form>
                    <div id="searchSuggestions" class="d-none"></div>
                </div>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navOpcion2">
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
                        <li class="nav-item"><a class="nav-link text-secondary" href="#">Ofertas</a></li>
                        <li class="nav-item"><a class="nav-link text-secondary" href="#">Envíos</a></li>
                    </ul>
                    <div class="ms-lg-4 d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>/src/views/carrito.php"
                           class="btn btn-premium-red btn-sm px-3 py-2 fw-semibold"
                           id="cartBtn">
                           Mi Carrito (<?= $cantidadCarrito ?>)
                        </a>
                        <button
                            class="btn btn-outline-light btn-sm px-3 py-2 fw-semibold border-secondary border-opacity-50"
                            data-bs-toggle="modal" data-bs-target="#authModal">
                            Ingresar
                        </button>
                    </div>
                </div>
            </div>
        </nav>
           <?php if (basename($_SERVER['PHP_SELF']) !== 'carrito.php'): ?>                              
        <!-- Hero Section -->
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-12 col-md-6 text-center text-md-start">
                    <h1 class="display-5 fw-bold text-white lh-sm mb-3">EL DETALLE QUE TU VEHÍCULO MERECE</h1>
                    <p class="text-secondary my-3 lead fs-6">Accedé a componentes de alta performance, estética
                        detallada e iluminación de vanguardia. Diseñado para conductores exigentes.</p>
                    <div class="d-grid gap-3 d-md-flex justify-content-md-start pt-3">
                        <a href="#productos" class="btn btn-premium-red px-4 py-2 fw-semibold">Explorar Galería</a>
                        <a href="#" class="btn btn-premium-outline px-4 py-2 fw-semibold">Asesoramiento VIP</a>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div id="heroCarousel" class="carousel slide shadow-lg rounded-3 overflow-hidden" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="carousel-item-custom d-flex flex-column align-items-center justify-content-center text-center p-5">
                                    <span class="badge bg-danger text-white fw-bold px-3 py-1 mb-2">NUEVO INGRESO</span>
                                    <h3 class="fw-bold text-white mb-1 text-uppercase tracking-wide">Kits de Distribución</h3>
                                    <p class="small text-secondary mb-3">Originales importados con certificación y garantía directa.</p>
                                    <a href="#productos" class="btn btn-sm btn-premium-outline px-4">Ver Modelos</a>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="carousel-item-custom d-flex flex-column align-items-center justify-content-center text-center p-5">
                                    <span class="badge bg-white text-dark fw-bold px-3 py-1 mb-2">OFERTA DEL DÍA</span>
                                    <h3 class="fw-bold text-white mb-1 text-uppercase tracking-wide">Detailing Premium</h3>
                                    <p class="small text-secondary mb-3">Combos de limpieza con ceras de carnauba y microfibras importadas.</p>
                                    <a href="#productos" class="btn btn-sm btn-premium-red px-4">Comprar Ahora</a>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="carousel-item-custom d-flex flex-column align-items-center justify-content-center text-center p-5">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2">NOVEDAD</span>
                                    <h3 class="fw-bold text-white mb-1 text-uppercase tracking-wide">Luz LED Cree C6</h3>
                                    <p class="small text-secondary mb-3">Potencia extrema de 6000K para una conducción nocturna segura.</p>
                                    <a href="#productos" class="btn btn-sm btn-premium-outline px-4">Ver Compatibilidad</a>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
         <?php endif; ?>
    </header>
