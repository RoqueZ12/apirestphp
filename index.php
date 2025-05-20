<?php

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
// Quitar solo la parte antes de 'productos' si tu app está en un subdirectorio.
// Por ejemplo, si está en raíz no necesitas str_replace.
$params = explode('/', trim($uri, '/'));

error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Parsed URI: " . $uri);
error_log("Params: " . print_r($params, true));

if (isset($params[0]) && $params[0] === 'productos') {
    require_once __DIR__ . '/routes/product.php';
    handleProductRoutes($pdo, $params);
} else {
    require_once __DIR__ . '/api/response.php';
    ResponseHelper::error("Ruta no encontrada", 404);
}
