<?php

namespace App\models;

class CartModel extends BaseModel
{
    public function getOrCreateCart(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id AND status = :status LIMIT 1');
        $stmt->execute([
            ':user_id' => $userId,
            ':status' => 'active',
        ]);

        $cart = $stmt->fetch();
        if ($cart) {
            return $cart;
        }

        $insert = $this->db->prepare('INSERT INTO cart (user_id, status, created_at, updated_at) VALUES (:user_id, :status, NOW(), NOW())');
        $insert->execute([
            ':user_id' => $userId,
            ':status' => 'active',
        ]);

        return [
            'id' => (int) $this->db->lastInsertId(),
            'user_id' => $userId,
            'status' => 'active',
        ];
    }

    public function items(int $cartId): array
    {
        $stmt = $this->db->prepare('SELECT ci.*, p.name, p.slug, p.image_primary, p.price, p.discount_price, p.stock FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = :cart_id ORDER BY ci.created_at DESC');
        $stmt->execute([':cart_id' => $cartId]);

        return $stmt->fetchAll();
    }

    public function addItem(int $cartId, int $productId, int $quantity = 1): void
    {
        $stmt = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
        $stmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $this->db->prepare('UPDATE cart_items SET quantity = quantity + :quantity, updated_at = NOW() WHERE id = :id');
            $update->execute([
                ':quantity' => $quantity,
                ':id' => $existing['id'],
            ]);
            return;
        }

        $insert = $this->db->prepare('INSERT INTO cart_items (cart_id, product_id, quantity, created_at, updated_at) VALUES (:cart_id, :product_id, :quantity, NOW(), NOW())');
        $insert->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
        ]);
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $stmt = $this->db->prepare('UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':quantity' => max(1, $quantity),
            ':id' => $itemId,
        ]);
    }

    public function removeItem(int $itemId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE id = :id');
        $stmt->execute([':id' => $itemId]);
    }

    public function clear(int $cartId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
        $stmt->execute([':cart_id' => $cartId]);
    }
}
