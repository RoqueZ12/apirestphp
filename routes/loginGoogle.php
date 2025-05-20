<?php

require_once '../database/db.php'; // define $db
require_once '../config/firebase.php'; // define $auth
require_once '../controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app/");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    exit(0);
}

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app/");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$idToken = isset($data['idToken']) ? $data['idToken'] : null;


$authController = new AuthController($pdo, $auth);

$result = $authController->loginWithGoogle($idToken);

if (isset($result['success']) && $result['success'] && isset($result['token'])) {
    session_start(); // iniciamos session

    $payload = json_decode(base64_decode(explode('.', $result['token'])[1]), true);
    $_SESSION['user_id'] = $payload['uid'];
}


echo json_encode($result);
