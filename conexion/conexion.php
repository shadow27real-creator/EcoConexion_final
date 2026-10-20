<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$usuario = 'root';
$password = '';
$baseDatos = 'ecoconexion';

try {
    $mysqli = new mysqli($host, $usuario, $password, $baseDatos);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Error crítico de conexión a la base de datos EcoConexión.');
}