<?php
// =============================================================================
// CLIENTE HTTP COMPARTIDO PARA HABLAR CON LA API REST DE STRAPI
// =============================================================================
// Lo usan productos_controller.php, login.php, register.php, carrito.php,
// carrito_controller.php y checkout_controller.php. Centralizarlo acá evita
// tener 6 copias del mismo curl_init() repartidas por el proyecto.

if (!defined('STRAPI_URL')) {
    define('STRAPI_URL', 'http://localhost:1337'); // Sin barra al final
}

/**
 * Hace una request HTTP a /api/{endpoint} de Strapi.
 *
 * @param string      $method   'GET' | 'POST' | 'PUT' | 'DELETE'
 * @param string      $endpoint p.ej. 'productos', 'auth/local', 'carritos/5'
 * @param array       $query    query params (filters, populate, pagination, sort...)
 * @param array|null  $body     cuerpo a mandar como JSON (para auth/local, o { data: {...} })
 * @param string|null $token    JWT del usuario logueado, si la ruta lo necesita
 *
 * @return array{ok:bool, status:int, data:?array, error:?string}
 *         'data' es el JSON ya decodificado que devolvió Strapi (con su propio
 *         'data'/'meta' adentro, o 'jwt'/'user' en el caso de auth/local).
 */
function strapiRequest(string $method, string $endpoint, array $query = [], ?array $body = null, ?string $token = null): array
{
    $url = rtrim(STRAPI_URL, '/') . '/api/' . ltrim($endpoint, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
    ];

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($body);
        $headers[] = 'Content-Type: application/json';
    }
    $options[CURLOPT_HTTPHEADER] = $headers;

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError !== '') {
        error_log("strapiRequest: error de conexión a Strapi ($method $endpoint): $curlError");
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'No se pudo conectar con Strapi. ¿Está corriendo el servidor?'];
    }

    $decoded = json_decode($response, true);

    if ($httpCode >= 400) {
        $mensajeError = $decoded['error']['message'] ?? "Error HTTP $httpCode";
        error_log("strapiRequest: Strapi devolvió HTTP $httpCode para $method $endpoint -> $response");
        return ['ok' => false, 'status' => $httpCode, 'data' => $decoded, 'error' => $mensajeError];
    }

    return ['ok' => true, 'status' => $httpCode, 'data' => is_array($decoded) ? $decoded : null, 'error' => null];
}