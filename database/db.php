<?php
// $db = new mysqli("localhost", "root", "", "prueba_tecnica");
// if ($db->connect_errno) {
//     die("Error de conexión a la base de datos");
// }
// $pdo = new PDO("mysql:host=localhost;dbname=prueba_tecnica;charset=utf8mb4", "root", "");
// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Incluir archivo de configuración


// Cargar configuración desde archivo
$config = require_once __DIR__ . '/../config/credenciales.php';

$dbConfig = $config['db'];

// Crear conexión PDO
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};port={$dbConfig['port']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos: ' . $e->getMessage()]);
    exit;
}
