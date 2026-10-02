<?php
// Configuración compartida de la tienda, conservando el nombre del repositorio.
// Carga .env; el acceso a productos y cuentas se realiza mediante Strapi.


/**
 * Carga simple de variables desde un archivo .env (formato CLAVE=valor).
 * No depende de Composer ni de ninguna librería externa.
 */
function cargarEnv(string $rutaArchivo): void
{
    if (!file_exists($rutaArchivo)) {
        return;
    }

    foreach (file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);

        // Ignorar líneas vacías y comentarios (#)
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }
        if (strpos($linea, '=') === false) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);
        $valor = trim($valor, "\"'"); // saca comillas si las tiene, ej: DB_PASSWORD="1234"

        if (!isset($_ENV[$clave])) {
            $_ENV[$clave] = $valor;
            putenv("{$clave}={$valor}");
        }
    }
}

// El .env vive en la raíz del proyecto. Este archivo está en src/config/,
// así que hay que subir dos niveles.
cargarEnv(__DIR__ . '/../../.env');

// strapi_client.php utiliza STRAPI_URL de este entorno para consultar la API.
