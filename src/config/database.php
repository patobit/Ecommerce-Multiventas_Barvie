<?php
$env = parse_ini_file(__DIR__ . '/../../.env');

# Valores de la conexión, los saca del archivo '.env'
$host    = $env['DB_HOST'];
$db      = $env['DB_NAME'];
$user    = $env['DB_USER'];
$pass    = $env['DB_PASS'];
$charset = $env['DB_CHARSET'] ?? 'utf8mb4';

# Data Source Name (DSN) especifica el driver (mysql), host, database, y el charset
# No es más que un string que se usa al crear la conexión (linea 21)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("La conexión a la base de datos falló: " . $e->getMessage());
}
?>