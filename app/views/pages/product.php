<?php
$productReviews = $productReviews ?? [];
$authUser = $authUser ?? null;
?>
<?php if (!$selectedProduct): ?>
    <div class="alert alert-warning">Product not found.</div>
<?php else: ?>
    <?php
    $images = array_filter([
        $selectedProduct['image_primary'] ?? null,
        $selectedProduct['image_secondary'] ?? null,
        $selectedProduct['image_tertiary'] ?? null,
    ]);
    if (!$images) {
        $images = ['images.jpg'];
    }
    $price = (float) ($selectedProduct['price'] ?? 0);
    $discountPrice = isset($selectedProduct['discount_price']) && $selectedProduct['discount_price'] !== null ? (float) $selectedProduct['discount_price'] : null;
    $sizes = explode(',', (string) ($selectedProduct['sizes'] ?? 'One Size'));
    $colors = explode(',', (string) ($selectedProduct['colors'] ?? 'Mixed'));
    ?>
    <div class="row g-4">
        <div class="col-lg-6">
            <img src="<?= e($images[0]) ?>" class="img-fluid rounded-4 mb-3" alt="<?= e($selectedProduct['name']) ?>">
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($images as $image): ?>
                    <img src="<?= e($image) ?>" alt="thumb" style="width:84px;height:84px;object-fit:cover;border-radius:10px;">
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <h1><?= e($selectedProduct['name']) ?></h1>
            <div class="product-rating mb-2">
                <span class="stars"><?= str_repeat('★', (int) round((float) ($selectedProduct['rating'] ?? 4.0))) ?></span>
                <span class="text-muted"><?= (int) ($selectedProduct['review_count'] ?? 0) ?> reviews</span>
            </div>
            <div class="product-price mb-3">
                <strong class="fs-4"><?= format_money($discountPrice ?: $price) ?></strong>
                <?php if ($discountPrice): ?><span class="text-muted text-decoration-line-through"><?= format_money($price) ?></span><?php endif; ?>
            </div>
            <p class="text-muted"><?= e($selectedProduct['description'] ?? 'Quality second-hand product.') ?></p>
            <p class="mb-1"><strong>Category:</strong> <?= e((string) ($selectedProduct['category_name'] ?? 'General')) ?></p>
            <p class="mb-1"><strong>Condition:</strong> <?= e((string) ($selectedProduct['item_condition'] ?? 'Good')) ?></p>
            <p><strong>Stock:</strong> <?= (int) ($selectedProduct['stock'] ?? 0) > 0 ? 'Available' : 'Out of stock' ?></p>

            <form method="post" action="index.php?page=cart&action=add" class="surface-card p-3">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $selectedProduct['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Size</label>
                    <select class="form-select" name="size">
                        <?php foreach ($sizes as $size): ?><option><?= e(trim($size)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Colour</label>
                    <select class="form-select" name="color">
                        <?php foreach ($colors as $color): ?><option><?= e(trim($color)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" min="1" name="quantity" value="1" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-accent">Add to Cart</button>
            </form>
            <form method="post" action="index.php?page=wishlist&action=add" class="mt-2">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $selectedProduct['id'] ?>">
                <button class="btn btn-outline-dark" type="submit">Wishlist</button>
            </form>
        </div>
    </div>

    <section class="mt-4 surface-card p-4">
        <h3 class="mb-3">Product Reviews</h3>
        <?php if ($productReviews): ?>
            <?php foreach ($productReviews as $review): ?>
                <div class="border-bottom pb-2 mb-2">
                    <p class="mb-1">
                        <strong><?= e((string) $review['first_name']) ?> <?= e((string) $review['last_name']) ?></strong>
                        <span class="stars ms-2"><?= str_repeat('★', (int) $review['rating']) ?></span>
                    </p>
                    <p class="mb-0"><?= e((string) $review['review_text']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No reviews yet for this product.</p>
        <?php endif; ?>

        <?php if ($authUser): ?>
            <form method="post" action="index.php?page=product&action=review" class="mt-3">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $selectedProduct['id'] ?>">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Review</label>
                        <input name="review_text" class="form-control" placeholder="Share your experience" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-dark" type="submit">Submit Review</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p class="mb-0 mt-2">Please <a href="index.php?page=login">login</a> to leave a review.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>
