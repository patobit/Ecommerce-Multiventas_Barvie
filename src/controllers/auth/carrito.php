<?php
// =============================================================================
// LÓGICA DEL CARRITO (API REST de Strapi)
// =============================================================================
// Los IDs de las vistas siguen siendo numéricos; las escrituras REST usan documentId.

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
        'populate'   => ['detalle_carritos' => ['populate' => ['producto' => ['populate' => ['categoria', 'Imagen']]]]],
        'pagination' => ['limit' => 1],
    ], null, $jwt);

    if (!$resultado['ok']) {
        throw new RuntimeException('No se pudo consultar el carrito. Intentá de nuevo.');
    }
    $items = $resultado['data']['data'] ?? [];
    return empty($items) ? null : $items[0];
}

/**
 * Crea un carrito nuevo en estado 'Activo' para el usuario. Devuelve el id
 * del carrito recién creado, o null si falló.
 */
function crearCarritoActivo(string $jwt, int $idUsuario): ?string
{
    $resultado = strapiRequest('POST', 'carritos', [], [
        'data' => [
            'Estado'                  => 'Activo',
            'users_permissions_user'  => $idUsuario,
        ],
    ], $jwt);

    return $resultado['ok'] ? ($resultado['data']['data']['documentId'] ?? null) : null;
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

        $precio = (float) ($producto['Precio'] ?? 0);
        $oferta = (float) ($producto['Precio_oferta'] ?? 0);
        if ($oferta > 0 && $oferta < $precio) $precio = $oferta;
        $cantidad  = (int) ($detalle['Cantidad'] ?? 0);
        $subtotal  = $precio * $cantidad;
        $total    += $subtotal;

        $productos[] = [
            'id_detalle_carrito' => $detalle['id'],
            'document_id' => $detalle['documentId'],
            'producto_document_id' => $producto['documentId'],
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
        'id_carrito' => $carrito['documentId'],
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
    $producto = obtenerProductoPorId($idProducto);
    if (!$producto || $cantidad <= 0 || $cantidad > $producto['stock']) {
        return ['success' => false, 'message' => 'Cantidad o producto inválido; revisá el stock.'];
    }
    $carrito = obtenerCarritoActivoRaw($jwt, $idUsuario);
    $idCarrito = $carrito['documentId'] ?? crearCarritoActivo($jwt, $idUsuario);

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
        if ($nuevaCantidad > $producto['stock']) return ['success' => false, 'message' => 'Stock insuficiente.'];
        $resultado = strapiRequest('PUT', 'detalle-carritos/' . $detalleExistente['documentId'], [], [
            'data' => ['Cantidad' => $nuevaCantidad],
        ], $jwt);
    } else {
        $resultado = strapiRequest('POST', 'detalle-carritos', [], [
            'data' => [
                'Cantidad' => $cantidad,
                'carrito'  => $idCarrito,
                'producto' => $producto['document_id'],
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
    $detalle = obtenerDetallePropio($jwt, $idUsuario, $idDetalleCarrito);
    if (!$detalle) {
        return ['success' => false, 'message' => 'El producto no pertenece a tu carrito.'];
    }

    if ($cantidad <= 0 || $cantidad > (int) ($detalle['producto']['Stock'] ?? 0)) {
        return ['success' => false, 'message' => 'Cantidad inválida o stock insuficiente.'];
    }
    $resultado = strapiRequest('PUT', 'detalle-carritos/' . $detalle['documentId'], [], [
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
    $detalle = obtenerDetallePropio($jwt, $idUsuario, $idDetalleCarrito);
    if (!$detalle) {
        return ['success' => false, 'message' => 'El producto no pertenece a tu carrito.'];
    }

    $resultado = strapiRequest('DELETE', 'detalle-carritos/' . $detalle['documentId'], [], null, $jwt);

    if (!$resultado['ok']) {
        return ['success' => false, 'message' => 'Error al eliminar: ' . ($resultado['error'] ?? '')];
    }

    return ['success' => true, 'message' => 'Producto eliminado del carrito.'];
}

/**
 * Verifica que un detalle_carrito exista, esté en un carrito 'Activo' y ese
 * carrito sea del usuario dado.
 */
function obtenerDetallePropio(string $jwt, int $idUsuario, int $idDetalleCarrito): ?array
{
    $carrito = obtenerCarritoActivoRaw($jwt, $idUsuario);
    foreach ($carrito['detalle_carritos'] ?? [] as $detalle) {
        if ((int) $detalle['id'] === $idDetalleCarrito) return $detalle;
    }
    return null;
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
    $idCompra = $compraResultado['data']['data']['documentId'];

    // 2. Copiar cada línea del carrito a detalle-compras
    foreach ($productos as $producto) {
        $linea = strapiRequest('POST', 'detalle-compras', [], [
            'data' => [
                'Cantidad'        => $producto['cantidad'],
                'Precio_unitario' => $producto['precio'],
                'compra'          => $idCompra,
                'producto'        => $producto['producto_document_id'],
            ],
        ], $jwt);
        if (!$linea['ok']) {
            return ['success' => false, 'message' => 'No se pudo completar la compra. Contactá a la tienda antes de reintentar.'];
        }
    }

    // Cerrar el carrito conservando sus líneas como historial.
    $cierre = strapiRequest('PUT', 'carritos/' . $idCarrito, [], [
        'data' => ['Estado' => 'Convertido', 'compra' => $idCompra],
    ], $jwt);

    if (!$cierre['ok']) return ['success' => false, 'message' => 'No se pudo cerrar la compra. Contactá a la tienda.'];
    return ['success' => true, 'message' => '¡Compra realizada con éxito!', 'id_compra' => $idCompra];
}