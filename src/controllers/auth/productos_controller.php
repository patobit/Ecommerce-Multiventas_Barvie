<?php
// =============================================================================
// CONSULTAS A LA BASE DE DATOS + RENDERIZADO DE TARJETAS DE PRODUCTO
// =============================================================================
// Todas las funciones reciben $pdo (la conexión) como parámetro, así queda
// explícito de dónde viene cada dato y es más fácil de testear.

/**
 * Últimos productos cargados (para la sección "Nuevos Ingresos").
 */
function obtenerProductosNuevos(PDO $pdo, int $limite = 4): array
{
    $sql = "SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON c.id_categoria = p.id_categoria
            ORDER BY p.id_producto DESC
            LIMIT :limite";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * El producto con más unidades vendidas, sumando detalle_compra.
 * Devuelve un array vacío si todavía no hay compras cargadas (no rompe la página).
 */
function obtenerProductoMasVendido(PDO $pdo): array
{
    $sql = "SELECT p.*, c.nombre AS categoria_nombre, SUM(dc.cantidad) AS total_vendido
            FROM detalle_compra dc
            JOIN productos p ON p.id_producto = dc.id_producto
            JOIN categorias c ON c.id_categoria = p.id_categoria
            GROUP BY p.id_producto
            ORDER BY total_vendido DESC
            LIMIT 1";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Productos con precio de oferta activo (para "Ofertas de la Semana").
 * Requiere la columna opcional productos.precio_oferta (ver ALTER TABLE sugerido).
 * Si la columna no existe todavía, devuelve [] en vez de romper la página.
 */
function obtenerProductosOferta(PDO $pdo, int $limite = 4): array
{
    try {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE p.precio_oferta IS NOT NULL AND p.precio_oferta < p.precio
                ORDER BY p.id_producto DESC
                LIMIT :limite";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Categorías principales (sin padre) o subcategorías de una categoría dada.
 * Pasá $idPadre = null para traer las categorías de nivel superior.
 */
function obtenerCategorias(PDO $pdo, ?int $idPadre = null): array
{
    if ($idPadre === null) {
        $stmt = $pdo->query("SELECT * FROM categorias WHERE id_categoria_padre IS NULL ORDER BY nombre");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_categoria_padre = :padre ORDER BY nombre");
        $stmt->execute([':padre' => $idPadre]);
    }
    return $stmt->fetchAll();
}

/**
 * Trae una categoría puntual por su id (para saber su nombre y su padre).
 */
function obtenerCategoriaPorId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_categoria = :id");
    $stmt->execute([':id' => $id]);
    $cat = $stmt->fetch();
    return $cat ?: null;
}

/**
 * Cuenta cuántos productos hay cargados directamente en una categoría.
 */
function contarProductosEnCategoria(PDO $pdo, int $idCategoria): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = :id");
    $stmt->execute([':id' => $idCategoria]);
    return (int) $stmt->fetchColumn();
}

/**
 * Todos los productos que pertenecen a una categoría puntual.
 */
function obtenerProductosPorCategoria(PDO $pdo, int $idCategoria): array
{
    $sql = "SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.id_categoria = :id
            ORDER BY p.nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idCategoria]);
    return $stmt->fetchAll();
}

/**
 * Trae un producto puntual por su id (para la vista de detalle).
 */
function obtenerProductoPorId(PDO $pdo, int $id): ?array
{
    $sql = "SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.id_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();
    return $producto ?: null;
}

/**
 * Búsqueda de productos por nombre o descripción (usada por catalogo.php con $_GET['buscar']).
 */
function buscarProductos(PDO $pdo, string $termino): array
{
    $like = '%' . $termino . '%';
    $sql = "SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.nombre LIKE :termino OR p.descripcion LIKE :termino
            ORDER BY p.nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':termino' => $like]);
    return $stmt->fetchAll();
}
