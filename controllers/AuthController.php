<?php

require_once __DIR__ . '/../models/UserModel.php';
$config = require_once __DIR__ . '/../config/credenciales.php';


use Firebase\JWT\JWT;

class AuthController
{
    private $db;
    private $userModel;
    private $authFirebase;
    private $jwtSecret;

    public function __construct($pdo, $firebaseAuth, $jwtSecret)
    {
        if (!is_string($jwtSecret) || trim($jwtSecret) === '') {
            throw new InvalidArgumentException("JWT secret inválido: debe ser un string no vacío.");
        }
        if (!$this->userModel->create(...)) {
            return ['error' => 'Error al crear usuario'];
        }

        $this->db = $pdo;
        $this->userModel = new UserModel($pdo);
        $this->authFirebase = $firebaseAuth;
        $this->jwtSecret = $jwtSecret;
    }


    public function registerWithForm($name, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $uid = uniqid(true); // UID único para usuarios locales
        $stmt = $this->db->prepare("INSERT INTO users (uid, name, email, password_hash, provider) VALUES (?, ?, ?, ?, 'local')");
        if ($stmt->execute([$uid, $name, $email, $hashedPassword])) {
            return ['success' => true, 'uid' => $uid];
        }
        return ['error' => 'Error al registrar el usuario'];
    }

    public function loginWithGoogle($idToken)
    {
        try {
            $verified = $this->authFirebase->verifyIdToken($idToken);

            // Extraer claims de forma segura
            $claims = method_exists($verified, 'claims') ? $verified->claims()->all() : [];
            $uid = $claims['sub'] ?? null;
            $email = $claims['email'] ?? null;
            $name = $claims['name'] ?? null;

            if (!$uid || !$email) {
                return ['error' => 'No se pudo extraer información del usuario'];
            }

            $user = $this->userModel->findByUID($uid);

            if (!$user) {
                $this->userModel->create([
                    'uid' => $uid,
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => null,
                    'provider' => 'google'
                ]);
                $user = $this->userModel->findByUID($uid); // 🔥 Esta línea es crucial
            }
            $jwt = $this->generateJWT($uid);
            // return ['success' => true, 'uid' => $uid];
            return [
                'success' => true,
                'token' => $jwt,
                'nombre' => $user['name'],
                'email' => $user['email']
            ];
        } catch (\Throwable $e) {
            return ['error' => 'Token inválido: ' . $e->getMessage()];
        }
    }

    public function loginWithForm($email, $password)
    {
        $user = $this->userModel->findByEmail($email);
        if ($user && $user['provider'] === 'local' && password_verify($password, $user['password_hash'])) {
            return $this->generateJWT($user['uid']);
        }
        return ['error' => 'Credenciales inválidas'];
    }

    private function generateJWT($uid)
    {
        $payload = [
            'uid' => $uid,
            'exp' => time() + 3600
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
