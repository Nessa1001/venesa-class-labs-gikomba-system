<?php

namespace App\models;

class ReviewModel extends BaseModel
{
    public function listByProduct(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT r.*, u.first_name, u.last_name FROM reviews r INNER JOIN users u ON u.id = r.user_id WHERE r.product_id = :product_id ORDER BY r.created_at DESC');
        $stmt->execute([':product_id' => $productId]);

        return $stmt->fetchAll();
    }

    public function create(int $userId, int $productId, int $rating, string $reviewText): int
    {
        $stmt = $this->db->prepare('INSERT INTO reviews (user_id, product_id, rating, review_text, created_at, updated_at) VALUES (:user_id, :product_id, :rating, :review_text, NOW(), NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':rating' => $rating,
            ':review_text' => $reviewText,
        ]);

        $this->refreshProductStats($productId);

        return (int) $this->db->lastInsertId();
    }

    private function refreshProductStats(int $productId): void
    {
        $statsStmt = $this->db->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE product_id = :product_id');
        $statsStmt->execute([':product_id' => $productId]);
        $stats = $statsStmt->fetch();

        $updateStmt = $this->db->prepare('UPDATE products SET rating = :rating, review_count = :review_count, updated_at = NOW() WHERE id = :id');
        $updateStmt->execute([
            ':rating' => round((float) ($stats['avg_rating'] ?? 0), 2),
            ':review_count' => (int) ($stats['total'] ?? 0),
            ':id' => $productId,
        ]);
    }
}
