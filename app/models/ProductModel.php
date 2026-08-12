<?php

namespace App\models;

class ProductModel extends BaseModel
{
    public function allForAdmin(): array
    {
        $stmt = $this->db->query('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC');

        return $stmt->fetchAll();
    }

    public function all(int $limit = 24, int $offset = 0): array
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM products');
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        return $product ?: null;
    }

    public function search(string $query, int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, price, discount_price, image_primary FROM products WHERE name LIKE :query OR description LIKE :query ORDER BY name ASC LIMIT :limit');
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function reduceStock(int $productId, int $quantity): void
    {
        $stmt = $this->db->prepare('UPDATE products SET stock = GREATEST(stock - :qty, 0), updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':qty' => $quantity,
            ':id' => $productId,
        ]);
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare('INSERT INTO products (category_id, name, slug, description, price, discount_price, stock, item_condition, sizes, colors, badge, image_primary, image_secondary, image_tertiary, created_at, updated_at) VALUES (:category_id, :name, :slug, :description, :price, :discount_price, :stock, :item_condition, :sizes, :colors, :badge, :image_primary, :image_secondary, :image_tertiary, NOW(), NOW())');
        $stmt->execute([
            ':category_id' => $payload['category_id'],
            ':name' => $payload['name'],
            ':slug' => $payload['slug'],
            ':description' => $payload['description'],
            ':price' => $payload['price'],
            ':discount_price' => $payload['discount_price'],
            ':stock' => $payload['stock'],
            ':item_condition' => $payload['item_condition'] ?? 'Good',
            ':sizes' => $payload['sizes'],
            ':colors' => $payload['colors'],
            ':badge' => $payload['badge'],
            ':image_primary' => $payload['image_primary'],
            ':image_secondary' => $payload['image_secondary'],
            ':image_tertiary' => $payload['image_tertiary'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $stmt = $this->db->prepare('UPDATE products SET category_id = :category_id, name = :name, slug = :slug, description = :description, price = :price, discount_price = :discount_price, stock = :stock, item_condition = :item_condition, sizes = :sizes, colors = :colors, badge = :badge, image_primary = :image_primary, image_secondary = :image_secondary, image_tertiary = :image_tertiary, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':category_id' => $payload['category_id'],
            ':name' => $payload['name'],
            ':slug' => $payload['slug'],
            ':description' => $payload['description'],
            ':price' => $payload['price'],
            ':discount_price' => $payload['discount_price'],
            ':stock' => $payload['stock'],
            ':item_condition' => $payload['item_condition'] ?? 'Good',
            ':sizes' => $payload['sizes'],
            ':colors' => $payload['colors'],
            ':badge' => $payload['badge'],
            ':image_primary' => $payload['image_primary'],
            ':image_secondary' => $payload['image_secondary'],
            ':image_tertiary' => $payload['image_tertiary'],
        ]);
    }

    public function delete(int $id): void
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute([':id' => $id]);
        } catch (\Throwable $throwable) {
            // If the product is already linked to orders, keep history and deactivate it.
            $stmt = $this->db->prepare('UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = :id');
            $stmt->execute([':id' => $id]);
        }
    }
}
