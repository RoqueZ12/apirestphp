<?php

$allowed_origins = ["https://miniecommerce-dun.vercel.app"];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Vary: Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, Accept");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../database/db.php';
require_once '../config/firebase.php';
require_once '../controllers/AuthController.php';

$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['idToken'] ?? null;

$authController = new AuthController($pdo, $auth);
$result = $authController->loginWithGoogle($idToken);

if (isset($result['success']) && $result['success'] && isset($result['token'])) {
    session_start();
    $payload = json_decode(base64_decode(explode('.', $result['token'])[1]), true);
    $_SESSION['user_id'] = $payload['uid'];
}

echo json_encode($result);
