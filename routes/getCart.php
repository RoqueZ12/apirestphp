<?php

header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once '../database/db.php';
require '../config/firebase.php';
require_once '../controllers/CartController.php';

// Obtener el token del header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado (sin token)']);
    exit;
}
$idToken = substr($authHeader, 7);

try {
    $verifiedIdToken = $auth->verifyIdToken($idToken);
    $userId = $verifiedIdToken->claims()->get('sub');
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// Obtener los productos del carrito
$cart = new CartController($pdo, $userId);
$items = $cart->getCartItems();

echo json_encode(['cart' => $items]);
