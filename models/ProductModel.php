<?php
class Product
{
    private $pdo;
    private $table = 'productos';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM $this->table");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM $this->table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $cantidad, $precio, $imagen)
    {
        $stmt = $this->pdo->prepare("INSERT INTO $this->table (nombre, cantidad, precio, image) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $cantidad, $precio, $imagen])) {
            return $this->pdo->lastInsertId(); // Devuelve el ID insertado
        }
        return false;
    }

    public function update($id, $nombre, $cantidad, $precio, $imagen)
    {
        $stmt = $this->pdo->prepare("UPDATE $this->table SET nombre=?, cantidad=?, precio=?, image=? WHERE id=?");
        return $stmt->execute([$nombre, $cantidad, $precio, $imagen, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
