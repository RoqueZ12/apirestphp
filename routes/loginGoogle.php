<?php
// 🔒 Evita mostrar errores en HTML
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Lista de orígenes permitidos
$allowed_origins = [
    "https://miniecommerce-dun.vercel.app"
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

// Obtener y validar el JSON del cuerpo
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Si no se encontró idToken en el body, intenta leerlo del header Authorization
if (!is_array($data) || empty($data['idToken'])) {
    $headers = getallheaders();
    if (!empty($headers['Authorization']) && str_starts_with($headers['Authorization'], 'Bearer ')) {
        $idToken = trim(str_replace('Bearer', '', $headers['Authorization']));
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Solicitud inválida o token no enviado"]);
        exit;
    }
} else {
    $idToken = $data['idToken'];
}


// Ejecutar autenticación
$authController = new AuthController($pdo, $auth, $config['jwt_secret']);
$result = $authController->loginWithGoogle($idToken);

// Validar token JWT generado
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
    $_SESSION['user_id'] = $payload['uid'] ?? null;
}

// Responder con JSON
header('Content-Type: application/json');
echo json_encode($result);
