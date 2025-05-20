<?php

// Lista de orígenes permitidos
$allowed_origins = [
    "https://miniecommerce-dun.vercel.app" // sin barra final
];

$origin = rtrim($_SERVER['HTTP_ORIGIN'] ?? '', '/');

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, Accept");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    exit(0);
}


// Requiere archivos necesarios
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../config/firebase.php';
$config = require_once __DIR__ . '/../config/credenciales.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Obtener el ID token del cuerpo de la petición
$data = json_decode(file_get_contents("php://input"), true);
$idToken = $data['idToken'] ?? null;

// Ejecutar autenticación
$authController = new AuthController($pdo, $auth, $config['jwt_secret']);
$result = $authController->loginWithGoogle($idToken);

// ⚠️ Verifica si el token fue generado exitosamente y está bien formado
if (
    is_array($result) &&
    isset($result['success'], $result['token']) &&
    $result['success'] === true &&
    count(explode('.', $result['token'])) === 3
) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $payload = json_decode(base64_decode(explode('.', $result['token'])[1]), true);
    $_SESSION['user_id'] = $payload['uid'];
}

// Responder con JSON
header('Content-Type: application/json');
echo json_encode($result);
