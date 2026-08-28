<?php
// Forzar la salida como JSON
header('Content-Type: application/json; charset=utf-8');

// CONEXIÓN A LA BASE DE DATOS
try {
    require_once __DIR__ . '/../../config/database.php';
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);
    exit;
}

// CONFIGURACIÓN (TEMPORAL)
$id_usuario = 2;

// AGREGAR PRODUCTO AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detectar si los datos vienen vía FormData ($_POST) o JSON en el body
    $inputData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $id_producto = isset($inputData['id_producto']) ? (int) $inputData['id_producto'] : 0;
    $cantidad    = isset($inputData['cantidad'])    ? (int) $inputData['cantidad']    : 1;

    // Validación de entrada
    if ($id_producto <= 0 || $cantidad <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos del producto inválidos.'
        ]);
        exit;
    }

    try {
        // 1. BUSCAR EL CARRITO ACTIVO DEL USUARIO
        $sql = "SELECT id_carrito
                FROM carritos
                WHERE id_usuario = ?
                  AND estado = 'Activo'
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        $carrito = $stmt->fetch();

        // 2. CREAR CARRITO SI NO EXISTE
        if (!$carrito) {
            $sql = "INSERT INTO carritos (id_usuario, estado) VALUES (?, 'Activo')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_usuario]);

            $id_carrito = $pdo->lastInsertId();
        } else {
            $id_carrito = $carrito['id_carrito'];
        }

        // 3. COMPROBAR SI EL PRODUCTO YA ESTÁ EN EL CARRITO
        $sql = "SELECT id_detalle_carrito, cantidad
                FROM detalle_carrito
                WHERE id_carrito = ?
                  AND id_producto = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_carrito, $id_producto]);
        $detalle = $stmt->fetch();
        // 4. ACTUALIZAR O INSERTAR PRODUCTO
        if ($detalle) {
            $nuevaCantidad = $detalle['cantidad'] + $cantidad;

            $sql = "UPDATE detalle_carrito
                    SET cantidad = ?
                    WHERE id_detalle_carrito = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nuevaCantidad, $detalle['id_detalle_carrito']]);
        } else {
            $sql = "INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad)
                    VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_carrito, $id_producto, $cantidad]);
        }

        // 5. RESPUESTA EXITOSA
        echo json_encode([
            'success' => true,
            'message' => 'Producto agregado correctamente.',
            'id_carrito' => $id_carrito
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
        'success' => false,
        'message' => 'Error SQL: ' . $e->getMessage() // Muestra el fallo exacto
    ]);
    exit;
    }
}