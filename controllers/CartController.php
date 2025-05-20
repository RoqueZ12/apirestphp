<?php

use Google\Cloud\Core\Exception\GoogleException;

class CartController
{
    private $pdo;
    private $userId;
    private $firestore;

    public function __construct($pdo, $userId, $firestore = null)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->firestore = $firestore; // Opcional
    }

    public function getOrCreateCartId()
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $cartId = $stmt->fetchColumn();

        if (!$cartId) {
            try {
                $stmt = $this->pdo->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
                $stmt->execute([$this->userId]);
                $cartId = $this->pdo->lastInsertId();
            } catch (PDOException $e) {
                error_log('Error al insertar carrito: ' . $e->getMessage());
                return null;
            }
        }

        return $cartId;
    }

    public function addItem($productId, $quantity)
    {
        $cartId = $this->getOrCreateCartId();

        // Verificar si el producto ya está en el carrito
        $stmt = $this->pdo->prepare("SELECT id FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $itemId = $stmt->fetchColumn();

        if ($itemId) {
            // Si ya existe, incrementar la cantidad
            $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $itemId]);
        } else {
            // Si no existe, insertar el nuevo producto
            $stmt = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$cartId, $productId, $quantity]);
        }

        // Sincronizar con Firestore
        $this->syncCartToFirestore();
    }

    public function getCartItems()
    {
        $cartId = $this->getOrCreateCartId();
        $stmt = $this->pdo->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cart = [];

        foreach ($items as $item) {
            $product = $this->getProductByIdFromLocalApi($item['product_id']);
            if (is_array($product) && isset($product[0])) {
                foreach ($product as $p) {
                    if (
                        (isset($p['id']) && $p['id'] == $item['product_id']) ||
                        (isset($p['product_id']) && $p['product_id'] == $item['product_id'])
                    ) {
                        $product = $p;
                        break;
                    }
                }
            }
            if (!$product) continue;

            $cart[] = [
                'id' => $item['product_id'],
                'nombre' => $product['nombre'] ?? 'Sin nombre',
                'precio' => $product['precio'] ?? 0,
                'image' => $product['image'] ?? '',
                'quantity' => $item['quantity'],
            ];
        }

        return $cart;
    }

    public function clearCart()
    {
        $cartId = $this->getOrCreateCartId();
        $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);

        // Sincroniza Firestore al vaciar
        $this->syncCartToFirestore();
    }

    private function getProductByIdFromLocalApi($productId)
    {
        $apiUrl = "http://localhost/PruebaTecnica/backendphp/routes/product.php?id=" . urlencode($productId);
        $response = @file_get_contents($apiUrl);
        if (!$response) {
            error_log("No se pudo obtener el producto con ID $productId desde la API.");
            return null;
        }
        $product = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error al decodificar JSON para producto ID $productId: " . json_last_error_msg());
            return null;
        }
        return $product;
    }

    // 🔁 Sincroniza con Firestore si está disponible
    private function syncCartToFirestore()
    {
        if (!$this->firestore) return;

        try {
            // Obtener los elementos del carrito
            $cartItems = $this->getCartItems();

            // Sincroniza el carrito completo en Firestore
            $cartDocRef = $this->firestore->collection('carritos')->document($this->userId);
            $cartDocRef->set([
                'cart' => $cartItems,
                'updatedAt' => date('c'),
            ]);
        } catch (GoogleException $e) {
            error_log('Error al sincronizar con Firestore: ' . $e->getMessage());
        }
    }
}
