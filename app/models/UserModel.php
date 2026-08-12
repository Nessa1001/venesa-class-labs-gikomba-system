<?php

namespace App\models;

class UserModel extends BaseModel
{
    public function listCustomers(?string $search = null): array
    {
        if ($search === null || trim($search) === '') {
            $stmt = $this->db->query("SELECT id, first_name, last_name, phone, email, role, is_active, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC");
            return $stmt->fetchAll();
        }

        $term = '%' . trim($search) . '%';
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, phone, email, role, is_active, created_at FROM users WHERE role = 'customer' AND (first_name LIKE :term OR last_name LIKE :term OR email LIKE :term OR phone LIKE :term) ORDER BY created_at DESC");
        $stmt->execute([':term' => $term]);

        return $stmt->fetchAll();
    }

    public function countCustomers(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function updateByAdmin(int $userId, array $payload): void
    {
        $stmt = $this->db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, is_active = :is_active, updated_at = NOW() WHERE id = :id AND role = :role');
        $stmt->execute([
            ':id' => $userId,
            ':first_name' => $payload['first_name'],
            ':last_name' => $payload['last_name'],
            ':email' => strtolower($payload['email']),
            ':phone' => $payload['phone'],
            ':is_active' => $payload['is_active'] ? 1 : 0,
            ':role' => 'customer',
        ]);
    }

    public function recentCustomers(int $limit = 5): array
    {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function setActiveStatus(int $userId, bool $isActive): void
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = :is_active, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':is_active' => $isActive ? 1 : 0,
            ':id' => $userId,
        ]);
    }

    public function create(array $payload): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (first_name, last_name, phone, email, password_hash, role, created_at, updated_at) VALUES (:first_name, :last_name, :phone, :email, :password_hash, :role, NOW(), NOW())');
        $stmt->execute([
            ':first_name' => $payload['first_name'],
            ':last_name' => $payload['last_name'],
            ':phone' => $payload['phone'],
            ':email' => strtolower($payload['email']),
            ':password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
            ':role' => $payload['role'] ?? 'customer',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower($email)]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByEmailOrUsername(string $login): ?array
    {
        $value = trim($login);
        $sql = 'SELECT * FROM users WHERE email = :email OR first_name = :username OR CONCAT(first_name, " ", last_name) = :full_name LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => strtolower($value),
            ':username' => $value,
            ':full_name' => $value,
        ]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}
