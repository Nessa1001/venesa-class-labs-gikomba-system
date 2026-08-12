<?php

namespace App\models;

class FeedbackModel extends BaseModel
{
    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM feedback');
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare('INSERT INTO feedback (name, email, rating, message, created_at, updated_at) VALUES (:name, :email, :rating, :message, NOW(), NOW())');
        $stmt->execute([
            ':name' => $payload['name'],
            ':email' => strtolower($payload['email']),
            ':rating' => $payload['rating'],
            ':message' => $payload['message'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(int $limit = 20): array
    {
        $stmt = $this->db->prepare('SELECT * FROM feedback ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM feedback WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
