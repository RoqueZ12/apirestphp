<?php

// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../api/response.php';

function handleProductRoutes($pdo, $params)
{
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents("php://input"), true) ?? [];

    switch ($method) {
        case 'GET':
            if (isset($params[1]) && is_numeric($params[1])) {
                $id = (int)$params[1];
                ProductController::getOne($pdo, $id);
            } else {
                ProductController::getAll($pdo);
            }

            break;

        case 'POST':
            ProductController::create($pdo, $input);
            break;

        case 'PUT':
            if (isset($params[1])) {
                $input['id'] = $params[1];
                ProductController::update($pdo, $input);
            } else {
                ResponseHelper::error("ID requerido para actualizar", 422);
            }
            break;

        case 'DELETE':
            if (isset($params[1])) {
                ProductController::delete($pdo, $params[1]);
            } else {
                ResponseHelper::error("ID requerido para eliminar", 422);
            }
            break;

        default:
            ResponseHelper::error("Método no permitido", 405);
    }
}
