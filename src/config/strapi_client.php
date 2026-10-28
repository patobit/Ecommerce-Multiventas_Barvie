<?php
// =============================================================================
// CLIENTE HTTP COMPARTIDO PARA HABLAR CON LA API REST DE STRAPI
// =============================================================================
// Lo usan productos_controller.php, login.php, register.php, carrito.php,
// carrito_controller.php y checkout_controller.php. Centralizarlo acá evita
// tener 6 copias del mismo curl_init() repartidas por el proyecto.

require_once __DIR__ . '/database.php';

if (!defined('STRAPI_URL')) {
    define('STRAPI_URL', rtrim(getenv('STRAPI_URL') ?: 'http://localhost:1337', '/')); // Sin barra al final
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
//UNA SOLA FUNCION PARA TODAS LAS LLAMADAS A STRAPI
{

    //ARMA UNA URL COM PLETA PARA CODIFICAR LOS PARAMETROS DE FORMA SEGURA 
    $url = rtrim(STRAPI_URL, '/') . '/api/' . ltrim($endpoint, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    //PIDE LA RESPUESTA JSON. SI HAY TOKEN, LO MADA EN EL HEADER PARA RUTAS QUE NECESUTAN ESTAR LOGUEADAS
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $options = [
        CURLOPT_RETURNTRANSFER => true,//DEVUELVE LA RESPUESTA COMO TEXTO
        CURLOPT_TIMEOUT        => 10,// SI STRAPI NO RESPONDE EN 10 SEGUNDO, CORTA
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),// DEFINE UN METODO(GET, POST, ETC)
    ];

    //SI HAY DATOS PARA ENVIAR LOS CONVIERTE EN JSON CON json_encode
    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($body);
        $headers[] = 'Content-Type: application/json';
    }

    //EJECUTA LA PETICION, GUARDA LA RESPUESTA, EL CODIGO HTTP Y EL ERROR DE CONEXION, Y CIERRA LA CONEXION
    $options[CURLOPT_HTTPHEADER] = $headers;

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    //SI NO SE PUDO CONECTAR, LO REGISTRA EN EL LOG Y DEVUELVE UN ERROR 
    if ($curlError !== '') {
        error_log("strapiRequest: error de conexión a Strapi ($method $endpoint): $curlError");
        return ['ok' => false, 'status' => 0, 'data' => null, 'error' => 'No se pudo conectar con Strapi. ¿Está corriendo el servidor?'];
    }

    // CONVIERTE EL JSON EN ARREGLO PHP. SI EL HTTP NO ES 2XX(200 A 299), LO TRATA COMO ERROR Y DEVUELVE EL MENSAJE DE STRAPI
    $decoded = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        $mensajeError = $decoded['error']['message'] ?? "Error HTTP $httpCode";
        error_log("strapiRequest: Strapi devolvió HTTP $httpCode para $method $endpoint ");
        return ['ok' => false, 'status' => $httpCode, 'data' => $decoded, 'error' => $mensajeError];
    }

    // SI LA RESPUESTA NO ES JSON VALIDO, ERROR. SI TODO ESTA BIEN, DEVUELVE OK-->TRUE CON LOS DATOS
    if ($httpCode !== 204 && !is_array($decoded)) {
        return ['ok' => false, 'status' => $httpCode, 'data' => null, 'error' => 'Strapi devolvió una respuesta inválida.'];
    }
    return ['ok' => true, 'status' => $httpCode, 'data' => is_array($decoded) ? $decoded : null, 'error' => null];
}
