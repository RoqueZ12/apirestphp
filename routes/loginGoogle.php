<?php

// Lista de orígenes permitidos
$allowed_origins = [
    "https://miniecommerce-dun.vercel.app"
];

// Normaliza el origen recibido quitando la barra final si existe
$origin = rtrim($_SERVER['HTTP_ORIGIN'] ?? '', '/');

// Compara contra los orígenes permitidos
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, Accept");
    header("Access-Control-Allow-Credentials: true");
}

// Manejo de preflight (pre-solicitudes OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Requiere archivos necesarios
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../config/firebase.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Obtener el ID token del cuerpo de la petición
$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['idToken'] ?? null;

// Ejecutar autenticación
$authController = new AuthController($pdo, $auth);
$result = $authController->loginWithGoogle($idToken);

// Si autenticación exitosa, inicia sesión
if (isset($result['success']) && $result['success'] && isset($result['token'])) {
    session_start();
    $payload = json_decode(base64_decode(explode('.', $result['token'])[1]), true);
    $_SESSION['user_id'] = $payload['uid'];
}

// Responder con el resultado en JSON
header('Content-Type: application/json');
echo json_encode($result);
