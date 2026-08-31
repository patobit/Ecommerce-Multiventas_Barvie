
<?php

// Forzar la salida como JSON
/* header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/../../config/database.php';
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);
    exit;
} */

// CONFIGURACIÓN TEMPORAL
// Más adelante esto va a salir de la sesión del usuario.



// ============================================================
// OBTENER PRODUCTOS DEL CARRITO
// ============================================================

function obtenerProductosDelCarrito(PDO $pdo, int $id_usuario): array
{
    try {

        // 1. BUSCAR EL CARRITO ACTIVO DEL USUARIO
        $sql = "SELECT id_carrito
                FROM carritos
                WHERE id_usuario = ?
                  AND estado = 'Activo'
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario]);

        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. SI EL USUARIO NO TIENE CARRITO ACTIVO
        if (!$carrito) {
            return [
                'success' => true,
                'productos' => [],
                'total' => 0
            ];
        }

        $id_carrito = $carrito['id_carrito'];

        // 3. OBTENER LOS PRODUCTOS DEL CARRITO
        $sql = "SELECT
                    dc.id_detalle_carrito,
                    dc.id_producto,
                    dc.cantidad,
                    p.nombre,
                    p.descripcion,
                    p.imagen,
                    p.precio,
                    p.stock
                FROM detalle_carrito dc
                INNER JOIN productos p
                    ON dc.id_producto = p.id_producto
                WHERE dc.id_carrito = ?
                ORDER BY dc.id_detalle_carrito ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_carrito]);

        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. CALCULAR SUBTOTALES Y TOTAL
        $total = 0;

        foreach ($productos as &$producto) {
            $precio = (float) $producto['precio'];
            $cantidad = (int) $producto['cantidad'];

            $subtotal = $precio * $cantidad;
            $producto['subtotal'] = $subtotal;

            $total += $subtotal;
        }
        unset($producto);

        return [
            'success' => true,
            'id_carrito' => $id_carrito,
            'productos' => $productos,
            'total' => $total
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error SQL: ' . $e->getMessage(),
            'productos' => [],
            'total' => 0
        ];
    }
}
