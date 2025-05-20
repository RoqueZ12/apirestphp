<?php
require_once '../database/db.php';
require_once '../controllers/AuthController.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];

$auth = new AuthController($db, null); // No se necesita Firebase aquí
$response = $auth->loginWithForm($email, $password);

echo json_encode($response);
