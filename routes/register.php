<?php
require_once '../database/db.php';
require_once '../controllers/AuthController.php';

header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $auth = new AuthController($pdo, null);
    $response = $auth->registerWithForm($name, $email, $password);

    echo json_encode($response);
}
