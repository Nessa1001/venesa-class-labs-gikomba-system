<?php
$product = $product ?? [];
$price = (float) ($product['price'] ?? 0);
$discountPrice = isset($product['discount_price']) && $product['discount_price'] !== null ? (float) $product['discount_price'] : null;
$effectivePrice = $discountPrice ?: $price;
$badge = $product['badge'] ?? 'Trending';
$rating = (float) ($product['rating'] ?? 4.5);
$reviewCount = (int) ($product['review_count'] ?? 0);
$condition = trim((string) ($product['item_condition'] ?? 'Good'));
$sizes = trim((string) ($product['sizes'] ?? 'One Size'));
$category = trim((string) ($product['category_name'] ?? 'General'));
?>
<div class="col-sm-6 col-lg-4 col-xl-3">
    <article class="product-card h-100"
        data-name="<?= e(strtolower((string) ($product['name'] ?? ''))) ?>"
        data-category="<?= e(strtolower($category)) ?>"
        data-price="<?= (float) $effectivePrice ?>"
        data-size="<?= e(strtolower($sizes)) ?>"
        data-condition="<?= e(strtolower($condition)) ?>">
        <div class="product-badge badge-<?= strtolower(e($badge)) ?>"><?= e($badge) ?></div>
        <a href="index.php?page=product&id=<?= (int) $product['id'] ?>" class="product-image-wrap">
            <img src="<?= e($product['image_primary'] ?? 'images.jpg') ?>" class="product-image" loading="lazy" alt="<?= e($product['name'] ?? 'Product') ?>">
        </a>
        <div class="product-body">
            <small class="product-category"><?= e($category) ?></small>
            <h3 class="product-title"><a href="index.php?page=product&id=<?= (int) $product['id'] ?>"><?= e($product['name'] ?? 'Item') ?></a></h3>
            <p class="mb-1 text-muted small">Condition: <?= e($condition) ?></p>
            <p class="mb-1 text-muted small">Size: <?= e($sizes) ?></p>
            <div class="product-rating">
                <span class="stars"><?= str_repeat('★', (int) round($rating)) ?></span>
                <span class="text-muted">(<?= $reviewCount ?>)</span>
            </div>
            <div class="product-price">
                <strong><?= format_money($effectivePrice) ?></strong>
                <?php if ($discountPrice): ?>
                    <span class="text-muted text-decoration-line-through"><?= format_money($price) ?></span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="index.php?page=product&id=<?= (int) $product['id'] ?>" class="btn btn-outline-dark btn-sm flex-fill">View Details</a>
                <form method="post" action="index.php?page=cart&action=add" class="flex-fill add-to-cart-form" data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= e((string) ($product['name'] ?? 'Product')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="btn btn-accent btn-sm w-100" type="submit">Add to Cart</button>
                </form>
            </div>
        </div>
    </article>
</div>
