<?php
// =============================================================================
// LÓGICA DEL CARRITO (API REST de Strapi)
// =============================================================================
// SUPUESTOS DE NOMBRES DE CAMPO (a confirmar si algo falla):
// Siguiendo el patrón visto en categoria/producto/detalle-compra (atributos
// con mayúscula inicial: Nombre, Precio, Cantidad, Precio_unitario), se
// asume que:
//   - carrito         tiene el campo "Estado" (string: 'Activo' / 'Finalizado')
//   - compra          tiene los campos "Fecha" (datetime) y "Total" (decimal)
//   - detalle-carrito tiene el campo "Cantidad" (integer)
// Los nombres de las RELACIONES sí están confirmados (vienen del schema.json
// de "user" que ya compartiste): la relación de carrito/compra hacia el
// usuario se llama "users_permissions_user". Las relaciones "carrito",
// "producto" y "compra" dentro de detalle-carrito/detalle-compra siguen el
// mismo patrón lowercase que ya confirmamos en producto->categoria.
//
// Si alguna de estas rutas te tira 400 "Invalid key ...", mandame el
// schema.json de carrito/compra/detalle-carrito y te corrijo el nombre exacto.
//
// PERMISOS NECESARIOS EN STRAPI: a diferencia de Producto/Categoria (que son
// públicos), estas rutas requieren estar logueado. En el panel de Strapi:
// Settings > Users & Permissions > Roles > Authenticated, hay que habilitar
// find/findOne/create/update/delete para Carrito, DetalleCarrito, Compra y
// DetalleCompra.

require_once __DIR__ . '/../../config/strapi_client.php';
require_once __DIR__ . '/productos_controller.php'; // reutiliza mapearImagenes()

/**
 * Busca el carrito "Activo" del usuario, con sus detalle_carritos y los
 * productos de cada uno ya populados. Devuelve el item crudo de Strapi (no
 * el mapeado), o null si no tiene carrito activo.
 */
function obtenerCarritoActivoRaw(string $jwt, int $idUsuario): ?array
{
    $resultado = strapiRequest('GET', 'carritos', [
        'filters' => [
            'users_permissions_user' => ['id' => ['$eq' => $idUsuario]],
            'Estado'                 => ['$eq' => 'Activo'],
        ],
        'populate'   => ['detalle_carritos' => ['populate' => ['producto' => ['populate' => ['categoria']]]]],
        'pagination' => ['limit' => 1],
    ], null, $jwt);

    $items = $resultado['data']['data'] ?? [];
    return empty($items) ? null : $items[0];
}

/**
 * Crea un carrito nuevo en estado 'Activo' para el usuario. Devuelve el id
 * del carrito recién creado, o null si falló.
 */
function crearCarritoActivo(string $jwt, int $idUsuario): ?int
{
    $resultado = strapiRequest('POST', 'carritos', [], [
        'data' => [
            'Estado'                  => 'Activo',
            'users_permissions_user'  => $idUsuario,
        ],
    ], $jwt);

    return $resultado['ok'] ? ($resultado['data']['data']['id'] ?? null) : null;
}

/**
 * Devuelve los productos del carrito activo del usuario, con subtotales y
 * total, en el mismo formato que usaba la versión con PDO.
 */
function obtenerProductosDelCarrito(string $jwt, int $idUsuario): array
{
    $carrito = obtenerCarritoActivoRaw($jwt, $idUsuario);

    if (!$carrito) {
        return ['success' => true, 'id_carrito' => null, 'productos' => [], 'total' => 0];
    }

    $detalles  = $carrito['detalle_carritos'] ?? [];
    $productos = [];
    $total     = 0;

    foreach ($detalles as $detalle) {
        $producto = $detalle['producto'] ?? null;
        if (!$producto) {
            continue; // el producto fue borrado pero el detalle quedó huérfano
        }

        $precio    = (float) ($producto['Precio'] ?? 0);
        $cantidad  = (int) ($detalle['Cantidad'] ?? 0);
        $subtotal  = $precio * $cantidad;
        $total    += $subtotal;

        $productos[] = [
            'id_detalle_carrito' => $detalle['id'],
            'id_producto'        => $producto['id'],
            'nombre'             => $producto['Nombre'] ?? '',
            'descripcion'        => $producto['Descripcion'] ?? '',
            'imagenes'           => mapearImagenes($producto['Imagen'] ?? null),
            'precio'             => $precio,
            'stock'              => $producto['Stock'] ?? 0,
            'cantidad'           => $cantidad,
            'subtotal'           => $subtotal,
        ];
    }

    return [
        'success'    => true,
        'id_carrito' => $carrito['id'],
        'productos'  => $productos,
        'total'      => $total,
    ];
}

/**
 * Agrega un producto al carrito activo del usuario (crea el carrito si no
 * existe). Si el producto ya estaba en el carrito, suma la cantidad.
 */
function agregarProductoAlCarrito(string $jwt, int $idUsuario, int $idProducto, int $cantidad): array
{
    $carrito = obtenerCarritoActivoRaw($jwt, $idUsuario);
    $idCarrito = $carrito['id'] ?? crearCarritoActivo($jwt, $idUsuario);

    if (!$idCarrito) {
        return ['success' => false, 'message' => 'No se pudo crear el carrito.'];
    }

    // ¿El producto ya está en este carrito?
    $detalleExistente = null;
    foreach ($carrito['detalle_carritos'] ?? [] as $detalle) {
        if (($detalle['producto']['id'] ?? null) === $idProducto) {
            $detalleExistente = $detalle;
            break;
        }
    }

    if ($detalleExistente) {
        $nuevaCantidad = (int) ($detalleExistente['Cantidad'] ?? 0) + $cantidad;
        $resultado = strapiRequest('PUT', 'detalle-carritos/' . $detalleExistente['id'], [], [
            'data' => ['Cantidad' => $nuevaCantidad],
        ], $jwt);
    } else {
        $resultado = strapiRequest('POST', 'detalle-carritos', [], [
            'data' => [
                'Cantidad' => $cantidad,
                'carrito'  => $idCarrito,
                'producto' => $idProducto,
            ],
        ], $jwt);
    }

    if (!$resultado['ok']) {
        return ['success' => false, 'message' => 'Error al agregar el producto: ' . ($resultado['error'] ?? '')];
    }

    return ['success' => true, 'message' => 'Producto agregado correctamente.', 'id_carrito' => $idCarrito];
}

/**
 * Actualiza la cantidad de un ítem del carrito, verificando antes que ese
 * detalle realmente pertenezca al carrito activo del usuario (para que un
 * usuario no pueda editar el carrito de otro adivinando ids).
 */
function actualizarCantidadEnCarrito(string $jwt, int $idUsuario, int $idDetalleCarrito, int $cantidad): array
{
    if (!detalleCarritoPerteneceAlUsuario($jwt, $idUsuario, $idDetalleCarrito)) {
        return ['success' => false, 'message' => 'El producto no pertenece a tu carrito.'];
    }

    $resultado = strapiRequest('PUT', 'detalle-carritos/' . $idDetalleCarrito, [], [
        'data' => ['Cantidad' => $cantidad],
    ], $jwt);

    if (!$resultado['ok']) {
        return ['success' => false, 'message' => 'Error al actualizar: ' . ($resultado['error'] ?? '')];
    }

    return ['success' => true, 'message' => 'Cantidad actualizada correctamente.'];
}

/**
 * Elimina un ítem del carrito, con la misma verificación de pertenencia.
 */
function eliminarProductoDelCarrito(string $jwt, int $idUsuario, int $idDetalleCarrito): array
{
    if (!detalleCarritoPerteneceAlUsuario($jwt, $idUsuario, $idDetalleCarrito)) {
        return ['success' => false, 'message' => 'El producto no pertenece a tu carrito.'];
    }

    $resultado = strapiRequest('DELETE', 'detalle-carritos/' . $idDetalleCarrito, [], null, $jwt);

    if (!$resultado['ok']) {
        return ['success' => false, 'message' => 'Error al eliminar: ' . ($resultado['error'] ?? '')];
    }

    return ['success' => true, 'message' => 'Producto eliminado del carrito.'];
}

/**
 * Verifica que un detalle_carrito exista, esté en un carrito 'Activo' y ese
 * carrito sea del usuario dado.
 */
function detalleCarritoPerteneceAlUsuario(string $jwt, int $idUsuario, int $idDetalleCarrito): bool
{
    $resultado = strapiRequest('GET', 'detalle-carritos/' . $idDetalleCarrito, [
        'populate' => ['carrito' => ['populate' => ['users_permissions_user']]],
    ], null, $jwt);

    if (!$resultado['ok']) {
        return false;
    }

    $carrito = $resultado['data']['data']['carrito'] ?? null;
    if (!$carrito || ($carrito['Estado'] ?? null) !== 'Activo') {
        return false;
    }

    return ($carrito['users_permissions_user']['id'] ?? null) === $idUsuario;
}

/**
 * Convierte el carrito activo del usuario en una compra: crea la Compra,
 * copia cada línea a detalle-compras, y cierra el carrito.
 */
function finalizarCompra(string $jwt, int $idUsuario): array
{
    $resultado = obtenerProductosDelCarrito($jwt, $idUsuario);
    $productos = $resultado['productos'] ?? [];

    if (empty($productos)) {
        return ['success' => false, 'message' => 'Tu carrito está vacío.'];
    }

    $idCarrito = $resultado['id_carrito'];
    $total     = $resultado['total'];

    // 1. Crear la compra
    $compraResultado = strapiRequest('POST', 'compras', [], [
        'data' => [
            'Fecha'                   => date('c'),
            'Total'                   => $total,
            'users_permissions_user'  => $idUsuario,
        ],
    ], $jwt);

    if (!$compraResultado['ok']) {
        return ['success' => false, 'message' => 'Error al crear la compra: ' . ($compraResultado['error'] ?? '')];
    }
    $idCompra = $compraResultado['data']['data']['id'];

    // 2. Copiar cada línea del carrito a detalle-compras
    foreach ($productos as $producto) {
        strapiRequest('POST', 'detalle-compras', [], [
            'data' => [
                'Cantidad'        => $producto['cantidad'],
                'Precio_unitario' => $producto['precio'],
                'compra'          => $idCompra,
                'producto'        => $producto['id_producto'],
            ],
        ], $jwt);
    }

    // 3. Vaciar el carrito (borrar sus detalle_carritos) y cerrarlo
    foreach ($productos as $producto) {
        strapiRequest('DELETE', 'detalle-carritos/' . $producto['id_detalle_carrito'], [], null, $jwt);
    }

    strapiRequest('PUT', 'carritos/' . $idCarrito, [], [
        'data' => ['Estado' => 'Finalizado', 'compra' => $idCompra],
    ], $jwt);

    return ['success' => true, 'message' => '¡Compra realizada con éxito!', 'id_compra' => $idCompra];
}