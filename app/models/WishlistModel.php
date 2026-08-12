<?php

namespace App\models;

class WishlistModel extends BaseModel
{
    public function items(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT w.id AS wishlist_id, p.* FROM wishlists w INNER JOIN products p ON p.id = w.product_id WHERE w.user_id = :user_id ORDER BY w.created_at DESC');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function count(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM wishlists WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function add(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare('SELECT id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id LIMIT 1');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);

        if ($stmt->fetch()) {
            return;
        }

        $insert = $this->db->prepare('INSERT INTO wishlists (user_id, product_id, created_at, updated_at) VALUES (:user_id, :product_id, NOW(), NOW())');
        $insert->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);
    }

    public function remove(int $wishlistId, int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM wishlists WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id' => $wishlistId,
            ':user_id' => $userId,
        ]);
    }
}
