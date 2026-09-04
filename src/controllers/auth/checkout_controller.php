<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/carrito.php'; // trae obtenerProductosDelCarrito()
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la base de datos.']);
    exit;
}

// CONFIGURACIÓN (TEMPORAL, igual que en carrito_controller.php)
$id_usuario = 2;

// TODO: cuando el login de tu compañero esté listo, reemplazar la línea de arriba por:
// if (!isset($_SESSION['usuario'])) {
//     echo json_encode(['success' => false, 'message' => 'Tenés que iniciar sesión para comprar.']);
//     exit;
// }
// $id_usuario = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // 1. OBTENER LOS PRODUCTOS DEL CARRITO ACTIVO
        $resultado = obtenerProductosDelCarrito($pdo, $id_usuario);
        $productos = $resultado['productos'] ?? [];

        if (empty($productos)) {
            echo json_encode(['success' => false, 'message' => 'Tu carrito está vacío.']);
            exit;
        }

        $id_carrito = $resultado['id_carrito'];
        $total = $resultado['total'];

        // Empezamos una transacción: si algo falla en el medio, se deshace todo
        $pdo->beginTransaction();

        // 2. CREAR LA COMPRA
        $sql = "INSERT INTO compras (id_usuario, total) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario, $total]);

        $id_compra = $pdo->lastInsertId();

        // 3. COPIAR CADA PRODUCTO DEL CARRITO A detalle_compra
        $sql = "INSERT INTO detalle_compra (id_compra, id_producto, cantidad, precio_unitario)
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($productos as $producto) {
            $stmt->execute([
                $id_compra,
                $producto['id_producto'],
                $producto['cantidad'],
                $producto['precio']
            ]);
        }

        // 4. CERRAR EL CARRITO (queda vinculado a la compra y deja de estar "Activo")
        $sql = "UPDATE carritos SET estado = 'Finalizado', id_compra = ? WHERE id_carrito = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_compra, $id_carrito]);

        // Si llegamos hasta acá sin errores, confirmamos todo
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => '¡Compra realizada con éxito!',
            'id_compra' => $id_compra
        ]);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack(); // deshacemos todo si algo falló
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        exit;
    }
}