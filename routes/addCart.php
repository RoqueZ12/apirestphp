<?php

header("Access-Control-Allow-Origin: http://localhost:5173");
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

// Obtener datos JSON
$data = json_decode(file_get_contents("php://input"), true);
$productId = $data['product_id'] ?? null;
$quantity = $data['quantity'] ?? 1;

if (!$productId) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID del producto']);
    exit;
}

// Ejecutar controlador
$cart = new CartController($pdo, $userId);
$cart->addItem($productId, $quantity);

echo json_encode(['success' => true]);
