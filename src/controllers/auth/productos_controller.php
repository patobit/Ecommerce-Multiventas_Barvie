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

// ESTUDIO obtenerLogoMarca: Elige el logo conocido como respaldo cuando falta una foto; no modifica la marca guardada.
function obtenerLogoMarca(array $producto): ?string
{
    $nombre = strtolower(($producto['marca'] ?? '') . ' ' . ($producto['nombre'] ?? ''));
    if (strtolower(trim($producto['marca'] ?? '')) === 'revi') return BASE_URL . '/assets/img/marca-revigal.png';
    foreach (['revigal' => 'revigal', 'iael' => 'iael', 'goodyear' => 'goodyear', 'barbie' => 'barbie', 'armor all' => 'armorall', 'armorall' => 'armorall', 'california scents' => 'california-scents'] as $marca => $archivo) {
        if (str_contains($nombre, $marca)) {
            return BASE_URL . '/assets/img/marca-' . $archivo . '.png';
        }
    }
    return null;
}

// ESTUDIO mapearProducto: Traduce Nombre/Precio/Stock/Marca de Strapi a nombre/precio/stock/marca usados por las vistas.
function mapearProducto(array $item): array
{
    $categoria = $item['categoria'] ?? null;

    return [
        'id_producto'      => $item['id'] ?? null,
        'document_id'      => $item['documentId'] ?? null,
        'nombre'           => $item['Nombre'] ?? '',
        'marca'            => $item['Marca'] ?? '',
        'descripcion'      => $item['Descripcion'] ?? '',
        'stock'            => $item['Stock'] ?? 0,
        'precio'           => $item['Precio'] ?? 0,
        'precio_oferta'    => $item['Precio_oferta'] ?? null,
        'imagenes'         => mapearImagenes($item['Imagen'] ?? null),
        'id_categoria'     => $categoria['id'] ?? null,
        'categoria_nombre' => $categoria['Nombre'] ?? null,
    ];
}

// ESTUDIO mapearImagenes: Convierte los datos de Imagen en una lista de URLs que el navegador puede descargar.
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

// Optimize only delivery copies; original media and gallery order stay intact.
function imagenCatalogo(string $url, int $width = 640): string
{
    if (!preg_match('~^https://res\.cloudinary\.com/[^/]+/image/upload/~', $url)) return $url;
    return preg_replace('~/image/upload/~', '/image/upload/f_auto,q_auto,c_limit,w_' . max(160, min(1600, $width)) . '/', $url, 1);
}

function catalogoNoDisponible(): bool
{
    return !empty($GLOBALS['mvb_catalog_unavailable']);
}

// ESTUDIO mapearCategoria: Traduce una categoria de Strapi al formato que usan el catalogo y su arbol.
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

// ESTUDIO obtenerTodosLosProductos: Consulta paginas de productos hasta completar el catalogo; evita quedarse solo con la primera pagina.
function obtenerTodosLosProductos(): array
{
    $productos = [];
    $inicio = 0;
    do {
        $resultado = strapiRequest('GET', 'productos', [
            'populate' => ['categoria', 'Imagen'],
            'sort' => 'Nombre:asc',
            'pagination' => ['start' => $inicio, 'limit' => 100],
        ]);
        if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
        $items = $resultado['data']['data'] ?? [];
        foreach ($items as $item) {
            $productos[] = mapearProducto($item);
        }
        $inicio += count($items);
        $total = (int) ($resultado['data']['meta']['pagination']['total'] ?? $inicio);
    } while (!empty($items) && $inicio < $total);
    return $productos;
}

// ESTUDIO obtenerProductosNuevos: Pide productos ordenados por id descendente, limitados a la cantidad solicitada.
function obtenerProductosNuevos(int $limite = 4): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'sort'       => 'id:desc',
        'populate'   => ['categoria', 'Imagen'],
        'pagination' => ['limit' => $limite],
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
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
// ESTUDIO obtenerProductoMasVendido: Agrega cantidades de detalles de compras para calcular un producto destacado; necesita permiso de lectura.
function obtenerProductoMasVendido(): array
{
    $resultado = strapiRequest('GET', 'detalle-compras', [
        'populate'   => ['producto' => ['populate' => ['categoria', 'Imagen']]],
        'pagination' => ['limit' => 1000],
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
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

// ESTUDIO obtenerProductosOferta: Consulta ofertas segun los datos disponibles; revisar el cuerpo para conocer el filtro aplicado.
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

// ESTUDIO obtenerCategorias: Pide categorias del nivel indicado; null representa categorias principales.
function obtenerCategorias(?int $idPadre = null): array
{
    $filtro = $idPadre === null
        ? ['categoria' => ['id' => ['$null' => true]]]
        : ['categoria' => ['id' => ['$eq' => $idPadre]]];

    $resultado = strapiRequest('GET', 'categorias', [
        'filters' => $filtro,
        'sort'    => 'Nombre:asc',
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearCategoria', $items);
}

// ESTUDIO obtenerCategoriaPorId: Busca una categoria por su id numerico; devuelve null si no hay coincidencia.
function obtenerCategoriaPorId(int $id): ?array
{
    $resultado = strapiRequest('GET', 'categorias', [
        'filters'    => ['id' => ['$eq' => $id]],
        'populate'   => ['categoria'],
        'pagination' => ['limit' => 1],
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
    $items = $resultado['data']['data'] ?? [];
    return empty($items) ? null : mapearCategoria($items[0]);
}

// ESTUDIO contarProductosEnCategoria: Cuenta los productos obtenidos para una categoria.
function contarProductosEnCategoria(int $idCategoria): int
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters'    => ['categoria' => ['id' => ['$eq' => $idCategoria]]],
        'pagination' => ['limit' => 1],
    ]);

    return $resultado['data']['meta']['pagination']['total'] ?? 0;
}

// ESTUDIO obtenerProductosPorCategoria: Obtiene productos vinculados a la categoria solicitada.
function obtenerProductosPorCategoria(int $idCategoria): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters'    => ['categoria' => ['id' => ['$eq' => $idCategoria]]],
        'populate'   => ['categoria', 'Imagen'],
        'sort'       => 'Nombre:asc',
        'pagination' => ['start' => 0, 'limit' => 100],
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearProducto', $items);
}

// ESTUDIO obtenerProductoPorId: Busca por id numerico para abrir una ficha; el resultado incluye document_id para escrituras REST.
function obtenerProductoPorId(int $id): ?array
{
    $query = ['filters'=>['id'=>['$eq'=>$id]],'populate'=>['categoria','Imagen'],'pagination'=>['limit'=>1]];
    $resultado = strapiRequest('GET','productos',$query);
    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
    $items = $resultado['data']['data'] ?? [];
    if (!$items && $resultado['ok']) {
        $aliases = json_decode(file_get_contents(__DIR__ . '/../../config/productos-legacy-ids.json'),true) ?: [];
        if (isset($aliases[(string)$id])) {
            $query['filters'] = ['documentId'=>['$eq'=>$aliases[(string)$id]]];
            $resultado = strapiRequest('GET','productos',$query);
            if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
            $items = $resultado['data']['data'] ?? [];
        }
    }
    return $items ? mapearProducto($items[0]) : null;
}

// ESTUDIO buscarProductos: Consulta coincidencias del termino en productos; no confundir con el filtro combinado de catalogo.php.
function buscarProductos(string $termino): array
{
    $resultado = strapiRequest('GET', 'productos', [
        'filters' => [
            '$or' => [
                ['Nombre'      => ['$containsi' => $termino]],
                ['Descripcion' => ['$containsi' => $termino]],
                ['Marca' => ['$containsi' => strtolower(trim($termino)) === 'revigal' ? 'Revi' : $termino]],
            ],
        ],
        'populate'   => ['categoria', 'Imagen'],
        'sort'       => 'Nombre:asc',
        'pagination' => ['start' => 0, 'limit' => 100],
    ]);

    if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
    $items = $resultado['data']['data'] ?? [];
    return array_map('mapearProducto', $items);
}

// ESTUDIO obtenerArbolCategorias: Recupera categorias con su padre para construir la navegacion del catalogo.
function obtenerArbolCategorias(): array
{
    $categorias = [];
    $inicio = 0;
    do {
        $resultado = strapiRequest('GET', 'categorias', ['populate' => ['categoria'], 'sort' => 'Nombre:asc', 'pagination' => ['start' => $inicio, 'limit' => 100]]);
        if (!$resultado['ok']) $GLOBALS['mvb_catalog_unavailable'] = true;
        $items = $resultado['data']['data'] ?? [];
        foreach ($items as $item) $categorias[] = mapearCategoria($item);
        $inicio += count($items);
        $total = (int)($resultado['data']['meta']['pagination']['total'] ?? $inicio);
    } while ($items && $inicio < $total);
    return $categorias;
}

// Category navigation uses the published hierarchy, never label prefixes.
function idsDescendientes(array $categorias, int $id): array
{
    $ids = []; $pending = [$id];
    while ($pending) {
        $next = array_pop($pending);
        if (in_array($next, $ids, true)) continue;
        $ids[] = $next;
        foreach ($categorias as $c) if ((int)($c['id_categoria_padre'] ?? 0) === $next) $pending[] = (int)$c['id_categoria'];
    }
    return $ids;
}
function rutaCategoria(array $categorias, ?int $id): array
{
    $index = array_column($categorias, null, 'id_categoria'); $path = []; $seen = [];
    while ($id && isset($index[$id]) && !isset($seen[$id])) {
        $seen[$id] = true; array_unshift($path, $index[$id]); $id = (int)($index[$id]['id_categoria_padre'] ?? 0);
    }
    return $path;
}

function idCategoriaVigente(array $categorias, ?int $id): ?int
{
    if (!$id) return $id;
    foreach ($categorias as $c) if ((int)$c['id_categoria'] === $id) return $id;
    $aliases = json_decode(file_get_contents(__DIR__ . '/../../config/categorias-legacy-ids.json'), true) ?: [];
    foreach ($categorias as $c) if ($c['document_id'] === ($aliases[(string)$id] ?? null)) return (int)$c['id_categoria'];
    return $id;
}
