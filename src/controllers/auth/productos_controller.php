<?php
// =============================================================================
// CONSULTAS A STRAPI (API REST) + MAPEO A ARRAYS "ESTILO PDO"
// =============================================================================
// Usa el cliente HTTP compartido (strapi_client.php) para no repetir curl.
// Cada función devuelve arrays con las mismas claves que antes devolvía el
// SELECT con PDO (id_producto, nombre, categoria_nombre, etc.) para que el
// resto del proyecto no tenga que reescribirse.

require_once __DIR__ . '/../../config/strapi_client.php';

// -----------------------------------------------------------------------
// MAPEO: de la forma "Strapi" a la forma "antigua estilo PDO"
// -----------------------------------------------------------------------

function mapearProducto(array $item): array
{
    $categoria = $item['categoria'] ?? null;

    return [
        'id_producto'      => $item['id'] ?? null,
        'document_id'      => $item['documentId'] ?? null,
        'nombre'           => $item['Nombre'] ?? '',
        'descripcion'      => $item['Descripcion'] ?? '',
        'stock'            => $item['Stock'] ?? 0,
        'precio'           => $item['Precio'] ?? 0,
        'precio_oferta'    => $item['Precio_oferta'] ?? null,
        'imagenes'         => mapearImagenes($item['Imagen'] ?? null),
        'id_categoria'     => $categoria['id'] ?? null,
        'categoria_nombre' => $categoria['Nombre'] ?? null,
    ];
}

function mapearImagenes(mixed $imagenData): array
{
    if (!$imagenData) {
        return [];
    }
    $items = $imagenData['data'] ?? $imagenData;
    if (!is_array($items)) {
        return [];
    }

    $urls = [];
    foreach ($items as $img) {
        $attrs = $img['attributes'] ?? $img;
        if (!empty($attrs['url'])) {
            // Si la URL ya viene absoluta (ej: un proveedor externo tipo
            // Cloudinary/S3), se usa tal cual. Si es relativa (guardado
            // local, ej: "/uploads/foto_123.png"), se le antepone la URL
            // de Strapi.
            $esAbsoluta = str_starts_with($attrs['url'], 'http://') || str_starts_with($attrs['url'], 'https://');
            $urls[] = $esAbsoluta ? $attrs['url'] : rtrim(STRAPI_URL, '/') . $attrs['url'];
        }
    }
    return $urls;
}

function mapearCategoria(array $item): array
{
    $padre = $item['categoria'] ?? null;

    return [
        'id_categoria'       => $item['id'] ?? null,
        'document_id'        => $item['documentId'] ?? null,
        'nombre'             => $item['Nombre'] ?? '',
        'descripcion'        => $item['Descripcion'] ?? '',
        'id_categoria_padre' => $padre['id'] ?? null,
    ];
}

// -----------------------------------------------------------------------
// FUNCIONES PÚBLICAS (misma firma que el controller original, sin $pdo)
// -----------------------------------------------------------------------

function obtenerProductosNuevos(int $limite = 4): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'sort'       => 'id:desc',
        'populate'   => ['categoria', 'Imagen'],
        'pagination' => ['limit' => $limite],
    ]);

    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearProducto', $items);
}

/**
 * SUPUESTO A CONFIRMAR: content-type 'detalle-compra' (endpoint 'detalle-compras')
 * con campo 'Cantidad' — confirmado por el schema.json que pasaste. La
 * agregación (SUM por producto) se hace acá en PHP porque la API REST de
 * Strapi no hace GROUP BY. Si el catálogo de compras crece mucho, conviene
 * un endpoint custom en Strapi que agregue en la base de datos.
 */
function obtenerProductoMasVendido(): array
{
    $resultado = strapiRequest('GET', 'detalle-compras', [
        'populate'   => ['producto' => ['populate' => ['categoria', 'Imagen']]],
        'pagination' => ['limit' => 1000],
    ]);

    $items = $resultado['data']['data'] ?? [];
    if (empty($items)) {
        return [];
    }

    $totales = [];
    foreach ($items as $detalle) {
        $producto = $detalle['producto'] ?? null;
        if (!$producto) {
            continue;
        }
        $cantidad = $detalle['Cantidad'] ?? 0;
        $idProd   = $producto['id'];

        if (!isset($totales[$idProd])) {
            $totales[$idProd] = ['producto' => $producto, 'total' => 0];
        }
        $totales[$idProd]['total'] += (int) $cantidad;
    }

    if (empty($totales)) {
        return [];
    }

    usort($totales, fn($a, $b) => $b['total'] <=> $a['total']);
    $ganador = $totales[0];

    $productoMapeado = mapearProducto($ganador['producto']);
    $productoMapeado['total_vendido'] = $ganador['total'];

    // Envuelto en array porque index.php espera una LISTA para hacer foreach().
    return [$productoMapeado];
}

function obtenerProductosOferta(int $limite = 4): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters' => ['Precio_oferta' => ['$gt' => 0]],
        'populate' => ['categoria', 'Imagen'],
        'sort' => 'id:desc',
        'pagination' => ['limit' => max(1, $limite)],
    ]);
    return array_values(array_filter(array_map('mapearProducto', $resultado['data']['data'] ?? []),
        fn($p) => (float) $p['precio_oferta'] < (float) $p['precio']));
}

function obtenerCategorias(?int $idPadre = null): array
{
    $filtro = $idPadre === null
        ? ['categoria' => ['id' => ['$null' => true]]]
        : ['categoria' => ['id' => ['$eq' => $idPadre]]];

    $resultado = strapiRequest('GET', 'categorias', [
        'filters' => $filtro,
        'sort'    => 'Nombre:asc',
    ]);

    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearCategoria', $items);
}

function obtenerCategoriaPorId(int $id): ?array
{
    $resultado = strapiRequest('GET', 'categorias', [
        'filters'    => ['id' => ['$eq' => $id]],
        'populate'   => ['categoria'],
        'pagination' => ['limit' => 1],
    ]);

    $items = $resultado['data']['data'] ?? [];
    return empty($items) ? null : mapearCategoria($items[0]);
}

function contarProductosEnCategoria(int $idCategoria): int
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters'    => ['categoria' => ['id' => ['$eq' => $idCategoria]]],
        'pagination' => ['limit' => 1],
    ]);

    return $resultado['data']['meta']['pagination']['total'] ?? 0;
}

function obtenerProductosPorCategoria(int $idCategoria): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters'    => ['categoria' => ['id' => ['$eq' => $idCategoria]]],
        'populate'   => ['categoria', 'Imagen'],
        'sort'       => 'Nombre:asc',
        'pagination' => ['start' => 0, 'limit' => 100],
    ]);

    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearProducto', $items);
}

function obtenerProductoPorId(int $id): ?array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters'    => ['id' => ['$eq' => $id]],
        'populate'   => ['categoria', 'Imagen'],
        'pagination' => ['limit' => 1],
    ]);

    $items = $resultado['data']['data'] ?? [];
    return empty($items) ? null : mapearProducto($items[0]);
}

function buscarProductos(string $termino): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters' => [
            '$or' => [
                ['Nombre'      => ['$containsi' => $termino]],
                ['Descripcion' => ['$containsi' => $termino]],
            ],
        ],
        'populate'   => ['categoria', 'Imagen'],
        'sort'       => 'Nombre:asc',
        'pagination' => ['start' => 0, 'limit' => 100],
    ]);

    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearProducto', $items);
}