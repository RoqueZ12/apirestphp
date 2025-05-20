<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../database/db.php';
class ProductController
{
    public static function getAll($pdo)
    {
        $product = new Product($pdo); // Instancia el modelo de producto
        $data = $product->getAll();
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function getOne($pdo, $id)
    {
        $product = new Product($pdo);
        $data = $product->getOne($id);
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["mensaje" => "Producto no encontrado"]);
        }
    }

    public static function create($pdo, $input)
    {
        if (!isset($input['nombre'], $input['cantidad'], $input['precio'])) {
            http_response_code(422);
            echo json_encode(["mensaje" => "Faltan datos requeridos"]);
            return;
        }

        $product = new Product($pdo);
        $id = $product->create($input['nombre'], $input['cantidad'], $input['precio'], $input['image'] ?? null);

        if ($id) {
            http_response_code(201);
            echo json_encode([
                "id" => (int)$id,
                "nombre" => $input['nombre'],
                "cantidad" => (int)$input['cantidad'],
                "precio" => number_format((float)$input['precio'], 2, '.', ''),
                "imagen" => $input['image'] ?? null
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al crear producto"]);
        }
    }

    public static function update($pdo, $input)
    {
        if (!isset($input['id'], $input['nombre'], $input['cantidad'], $input['precio'], $input['image'])) {
            http_response_code(422);
            echo json_encode(["mensaje" => "Faltan datos para actualizar"]);
            return;
        }

        $product = new Product($pdo);
        if ($product->update($input['id'], $input['nombre'], $input['cantidad'], $input['precio'], $input['image'])) {
            echo json_encode([
                "id" => (int)$input['id'],
                "nombre" => $input['nombre'],
                "cantidad" => (int)$input['cantidad'],
                "precio" => number_format((float)$input['precio'], 2, '.', ''),
                "imagen" => $input['image'] ?? null
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al actualizar"]);
        }
    }

    public static function delete($pdo, $id)
    {
        $product = new Product($pdo);
        if ($product->delete($id)) {
            echo json_encode(["mensaje" => "Producto eliminado"]);
        } else {
            http_response_code(500);
            echo json_encode(["mensaje" => "Error al eliminar"]);
        }
    }
}
