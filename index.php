<?php

// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejo del preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/database/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $uri);
$params = explode('/', trim($uri, '/'));

if (isset($params[0]) && $params[0] === 'productos') {
    require_once __DIR__ . '/routes/product.php';
    handleProductRoutes($pdo, $params);
} else {
    require_once __DIR__ . '/api/response.php';
    ResponseHelper::error("Ruta no encontrada", 404);
}
