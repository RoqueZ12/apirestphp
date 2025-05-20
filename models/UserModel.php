<?php
class UserModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByUID($uid)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE uid = ?");
        $stmt->execute([$uid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($user)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (uid, name, email, password_hash, provider)
            VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $user['uid'],
            $user['name'],
            $user['email'],
            $user['password_hash'],
            $user['provider']
        ]);
    }
}
