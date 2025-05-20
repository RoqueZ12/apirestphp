<?php

require __DIR__ . '/../vendor/autoload.php';

// CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app/");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    exit(0);
}

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Origin: https://miniecommerce-dun.vercel.app/");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Factory;

// Instancia global de $auth para otros archivos
$firebaseJson = getenv('FIREBASE_CREDENTIALS_JSON');
$tempPath = '/tmp/firebase_credentials.json';
file_put_contents($tempPath, $firebaseJson);

$auth = (new Factory())
    ->withServiceAccount($tempPath)
    ->createAuth();

// Función para verificar el token (opcional, si la necesitas)
function verifyIdToken($idTokenString)
{
    global $auth;
    try {
        $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        // Iniciar sesión y guardar el user_id

        return $verifiedIdToken;
    } catch (InvalidToken $e) {
        return null;
    } catch (\InvalidArgumentException $e) {
        return null;
    }
}
