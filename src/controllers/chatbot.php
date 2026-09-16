<?php
// API del chatbot: independiente de MySQL para que el chat pueda seguir funcionando.
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responderChat(array $datos, int $estado = 200): void
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    responderChat(['error' => 'Método no permitido.'], 405);
}
$_SESSION['chat_id'] = $_SESSION['chat_id'] ?? bin2hex(random_bytes(24));
$_SESSION['chat_token'] = $_SESSION['chat_token'] ?? bin2hex(random_bytes(32));
$chatId = $_SESSION['chat_id'];

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../../storage/chat.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec("CREATE TABLE IF NOT EXISTS mensajes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id TEXT NOT NULL,
        remitente TEXT NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec('CREATE INDEX IF NOT EXISTS mensajes_chat ON mensajes(chat_id, id)');

    if ($metodo === 'GET') {
        // Mostrar los últimos 100 mensajes; el resto permanece guardado.
        $consulta = $db->prepare('SELECT remitente, mensaje, fecha FROM (SELECT id, remitente, mensaje, fecha FROM mensajes WHERE chat_id = ? ORDER BY id DESC LIMIT 100) ORDER BY id ASC');
        $consulta->execute([$chatId]);
        responderChat(['mensajes' => $consulta->fetchAll(PDO::FETCH_ASSOC), 'token' => $_SESSION['chat_token']]);
    }

    if (!is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['chat_token'], $_POST['token'])) {
        responderChat(['error' => 'Recargá la página para volver a conectar el chat.'], 403);
    }
    $mensaje = $_POST['mensaje'] ?? '';
    if (!is_string($mensaje) || !mb_check_encoding($mensaje, 'UTF-8') || trim($mensaje) === '' || mb_strlen($mensaje) > 1000) {
        responderChat(['error' => 'Escribí un mensaje de entre 1 y 1000 caracteres.'], 422);
    }
    $mensaje = trim($mensaje);
    $texto = strtr(mb_strtolower($mensaje, 'UTF-8'), ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', '¿'=>'', '?'=>'']);
    $respuesta = 'Todavía no tengo una respuesta para esa consulta. Puedo orientarte sobre catálogo, carrito, registro e historial. Probá con alguno de esos temas.';
    // Reglas del apunte, ampliadas con temas de la tienda. No se usa una API de IA.
    $reglas = [
        '/\b(historial|conversacion|privacidad)\b/u' => 'Los mensajes se guardan con fecha y hora. Mientras conserves esta sesión, podés volver a ver los últimos 100 mensajes. No compartas contraseñas ni datos de pago.',
        '/\b(stock|disponibilidad|precio|precios)\b/u' => 'Consultá el catálogo y la ficha del producto para ver la información publicada. Este chat no consulta precios ni stock en tiempo real.',
        '/\b(carrito|comprar|compra)\b/u' => 'Buscá un producto en el catálogo, abrí su ficha y usá la opción para agregarlo al carrito. Desde el carrito podés revisar tu selección antes de continuar.',
        '/\b(catalogo|productos|repuestos|ofertas)\b/u' => 'Podés explorar el catálogo desde el menú de la tienda. En el inicio también tenés nuevos ingresos, ofertas y filtros por precio.',
        '/\b(registro|registrar|registrarme|cuenta|sesion|login)\b/u' => 'Usá la opción de iniciar sesión de la tienda. Si todavía no tenés cuenta, elegí Registrarse y completá el formulario.',
        '/\b(envio|envios|pago|pagos|devolucion)\b/u' => 'No tengo información confirmada sobre envíos, medios de pago o devoluciones. Consultá esas condiciones con la tienda antes de comprar.',
        '/\b(como estas)\b/u' => '¡Muy bien! Estoy listo para ayudarte a recorrer Multiventas Barvie.',
        '/\b(hola|buenas|buenos dias)\b/u' => '¡Hola! Soy el asistente de Multiventas Barvie. Puedo orientarte sobre catálogo, carrito y registro. ¿Qué necesitás?',
        '/\b(gracias)\b/u' => '¡De nada! Si necesitás ayuda con otra parte de la tienda, escribime.',
        '/\b(chau|adios|hasta luego)\b/u' => '¡Hasta luego! Gracias por visitar Multiventas Barvie.',
    ];
    foreach ($reglas as $patron => $contenido) {
        if (preg_match($patron, $texto)) {
            $respuesta = $contenido;
            break;
        }
    }
    // Guardar ambos mensajes juntos evita conversaciones incompletas.
    $db->beginTransaction();
    $guardar = $db->prepare('INSERT INTO mensajes (chat_id, remitente, mensaje) VALUES (?, ?, ?)');
    $guardar->execute([$chatId, 'usuario', $mensaje]);
    $guardar->execute([$chatId, 'bot', $respuesta]);
    $db->commit();
    responderChat(['respuesta' => $respuesta]);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Chatbot: ' . $error->getMessage());
    responderChat(['error' => 'No pudimos acceder al historial. Intentá nuevamente en unos momentos.'], 503);
}
