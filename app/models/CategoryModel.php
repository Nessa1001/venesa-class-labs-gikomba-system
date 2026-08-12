<?php

namespace App\models;

class CategoryModel extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name ASC');

        return $stmt->fetchAll();
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (:name, :slug, :description, NOW(), NOW())');
        $stmt->execute([
            ':name' => $payload['name'],
            ':slug' => $payload['slug'],
            ':description' => $payload['description'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':name' => $payload['name'],
            ':slug' => $payload['slug'],
            ':description' => $payload['description'],
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
