<?php

namespace App\models;

class AddressModel extends BaseModel
{
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function create(int $userId, array $payload): int
    {
        if (!empty($payload['is_default'])) {
            $this->clearDefault($userId);
        }

        $stmt = $this->db->prepare('INSERT INTO addresses (user_id, county, town, street, house_number, is_default, created_at, updated_at) VALUES (:user_id, :county, :town, :street, :house_number, :is_default, NOW(), NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':county' => $payload['county'],
            ':town' => $payload['town'],
            ':street' => $payload['street'],
            ':house_number' => $payload['house_number'],
            ':is_default' => !empty($payload['is_default']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function setDefault(int $userId, int $addressId): void
    {
        $this->clearDefault($userId);

        $stmt = $this->db->prepare('UPDATE addresses SET is_default = 1, updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id' => $addressId,
            ':user_id' => $userId,
        ]);
    }

    public function delete(int $userId, int $addressId): void
    {
        $stmt = $this->db->prepare('DELETE FROM addresses WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id' => $addressId,
            ':user_id' => $userId,
        ]);
    }

    private function clearDefault(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE addresses SET is_default = 0, updated_at = NOW() WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }
}
