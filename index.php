<?php

// Configura aquí el origen permitido:
$allowedOrigin = "https://miniecommerce-dun.vercel.app";

header("Access-Control-Allow-Origin: $allowedOrigin");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Aquí continúa tu lógica
require_once __DIR__ . '/database/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$params = explode('/', trim($uri, '/'));

if (isset($params[0])) {
    if ($params[0] === 'productos') {
        require_once __DIR__ . '/routes/product.php';
        handleProductRoutes($pdo, $params);
    } elseif ($params[0] === 'loginGoogle') {
        require_once __DIR__ . '/routes/loginGoogle.php';
        // Llama a la función de loginGoogle aquí
    } else {
        require_once __DIR__ . '/api/response.php';
        ResponseHelper::error("Ruta no encontrada", 404);
    }
} else {
    ResponseHelper::error("Ruta no encontrada", 404);
}
