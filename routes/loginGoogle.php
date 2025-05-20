<?php

// Habilitar CORS dinámicamente según el origen
$allowed_origins = [
    "http://localhost:5173",
    "https://miniecommerce-dun.vercel.app"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

// Preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../database/db.php'; // define $pdo
require_once '../config/firebase.php'; // define $auth
require_once '../controllers/AuthController.php';

$data = json_decode(file_get_contents("php://input"), true);
$idToken = isset($data['idToken']) ? $data['idToken'] : null;

$authController = new AuthController($pdo, $auth);

$result = $authController->loginWithGoogle($idToken);

if (isset($result['success']) && $result['success'] && isset($result['token'])) {
    session_start(); // iniciamos sesión

    $payload = json_decode(base64_decode(explode('.', $result['token'])[1]), true);
    $_SESSION['user_id'] = $payload['uid'];
}

echo json_encode($result);
