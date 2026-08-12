<?php

namespace App\models;

class OrderModel extends BaseModel
{
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function listAll(): array
    {
        $stmt = $this->db->query('SELECT o.*, u.first_name, u.last_name FROM orders o INNER JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC');

        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM orders');
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM orders WHERE status = :status');
        $stmt->execute([':status' => $status]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function totalSales(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) AS total_sales FROM orders WHERE status IN ('confirmed', 'processing', 'ready_for_delivery', 'completed')");
        $row = $stmt->fetch();

        return (float) ($row['total_sales'] ?? 0);
    }

    public function recentOrders(int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT o.*, u.first_name, u.last_name FROM orders o INNER JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function popularProducts(int $limit = 5): array
    {
        $sql = 'SELECT oi.product_id, oi.product_name, SUM(oi.quantity) AS total_quantity, SUM(oi.total_price) AS total_sales
                FROM order_items oi
                INNER JOIN orders o ON o.id = oi.order_id
                GROUP BY oi.product_id, oi.product_name
                ORDER BY total_quantity DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function generateOrderNumber(): string
    {
        $year = date('Y');
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM orders WHERE YEAR(created_at) = :year');
        $stmt->execute([':year' => $year]);
        $count = (int) ($stmt->fetch()['total'] ?? 0) + 1;

        return sprintf('ORD-%s-%06d', $year, $count);
    }

    public function createOrder(array $payload, array $items, float $subtotal, float $shipping, float $vat, float $total): int
    {
        $this->db->beginTransaction();

        try {
            $orderNumber = $this->generateOrderNumber();

            $orderStmt = $this->db->prepare('INSERT INTO orders (order_number, user_id, customer_name, phone, email, county, town, street, house_number, subtotal, shipping_fee, vat_amount, total_amount, payment_method, status, estimated_delivery_date, created_at, updated_at) VALUES (:order_number, :user_id, :customer_name, :phone, :email, :county, :town, :street, :house_number, :subtotal, :shipping_fee, :vat_amount, :total_amount, :payment_method, :status, :estimated_delivery_date, NOW(), NOW())');

            $orderStmt->execute([
                ':order_number' => $orderNumber,
                ':user_id' => $payload['user_id'],
                ':customer_name' => $payload['customer_name'],
                ':phone' => $payload['phone'],
                ':email' => $payload['email'],
                ':county' => $payload['county'],
                ':town' => $payload['town'],
                ':street' => $payload['street'],
                ':house_number' => $payload['house_number'],
                ':subtotal' => $subtotal,
                ':shipping_fee' => $shipping,
                ':vat_amount' => $vat,
                ':total_amount' => $total,
                ':payment_method' => $payload['payment_method'],
                ':status' => 'pending',
                ':estimated_delivery_date' => date('Y-m-d', strtotime('+3 days')),
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price, created_at, updated_at) VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :total_price, NOW(), NOW())');

            $productModel = new ProductModel();

            foreach ($items as $item) {
                $unitPrice = (float) ($item['discount_price'] ?: $item['price']);
                $quantity = (int) $item['quantity'];
                $lineTotal = $unitPrice * $quantity;

                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['name'],
                    ':unit_price' => $unitPrice,
                    ':quantity' => $quantity,
                    ':total_price' => $lineTotal,
                ]);

                $productModel->reduceStock((int) $item['product_id'], $quantity);
            }

            $deliveryStmt = $this->db->prepare('INSERT INTO deliveries (order_id, stage, expected_date, created_at, updated_at) VALUES (:order_id, :stage, :expected_date, NOW(), NOW())');
            $deliveryStmt->execute([
                ':order_id' => $orderId,
                ':stage' => 'pending',
                ':expected_date' => date('Y-m-d', strtotime('+3 days')),
            ]);

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemsStmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id DESC');
        $itemsStmt->execute([':order_id' => $orderId]);
        $order['items'] = $itemsStmt->fetchAll();

        return $order;
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
        $stmt->execute([':order_number' => $orderNumber]);
        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $orderId,
            ':status' => $status,
        ]);

        $deliveryStmt = $this->db->prepare('UPDATE deliveries SET stage = :stage, updated_at = NOW() WHERE order_id = :order_id');
        $deliveryStmt->execute([
            ':stage' => $status,
            ':order_id' => $orderId,
        ]);
    }
}
