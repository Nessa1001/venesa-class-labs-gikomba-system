<?php

declare(strict_types=1);

$pdo = require __DIR__ . '/db_connect.php';

$stmt = $pdo->query('SELECT p.id, p.name, p.price, p.discount_price, p.sizes, p.item_condition, p.image_primary, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();

function money(float $amount): string
{
    return 'KSh ' . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Retrieval Demo | Gikomba</title>
    <link rel="stylesheet" href="public/assets/css/app.css">
    <style>
        .demo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .demo-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        .demo-card img { width: 100%; height: 180px; object-fit: cover; }
        .demo-body { padding: 12px; }
    </style>
</head>
<body>
    <main class="container py-4">
        <h1>Products Retrieved from MySQL</h1>
        <p>This page demonstrates: MySQL connection -> SELECT query -> loop through rows -> display as cards.</p>

        <?php if (!$products): ?>
            <div class="surface-card p-3">No products available at the moment.</div>
        <?php else: ?>
            <div class="demo-grid">
                <?php foreach ($products as $product): ?>
                    <?php $price = $product['discount_price'] !== null ? (float) $product['discount_price'] : (float) $product['price']; ?>
                    <article class="demo-card">
                        <img src="<?= htmlspecialchars((string) $product['image_primary'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="demo-body">
                            <h3><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><strong>Category:</strong> <?= htmlspecialchars((string) $product['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Size:</strong> <?= htmlspecialchars((string) ($product['sizes'] ?: 'One Size'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Condition:</strong> <?= htmlspecialchars((string) ($product['item_condition'] ?: 'Good'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Price:</strong> <?= money($price) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
