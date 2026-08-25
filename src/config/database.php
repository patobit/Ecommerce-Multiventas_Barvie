<?php
// CONEXIÓN A LA BASE DE DATOS (PDO) — credenciales leídas desde el .env


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

$DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
$DB_NAME = $_ENV['DB_NAME'] ?? 'multiventas';
$DB_USER = $_ENV['DB_USER'] ?? 'root';
$DB_PASS = $_ENV['DB_PASSWORD'] ?? '';

if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
            $DB_USER,
            $DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        // En desarrollo mostramos el error para poder debuggear.
        // Antes de entregar el proyecto, esto debería loguearse en vez de mostrarse.
        die('<div style="font-family:sans-serif;padding:2rem;color:#b00">
                <h2>Error de conexión a la base de datos</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Revisá los valores en tu archivo <code>.env</code> (raíz del proyecto) y que el servicio MySQL de Laragon esté encendido.</p>
             </div>');
    }
}
