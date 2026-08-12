<?php $wishlistItems = $wishlistItems ?? []; ?>
<section class="mb-3">
    <h1>Wishlist</h1>
    <p class="text-muted">Products saved for later.</p>
</section>

<?php if (!$wishlistItems): ?>
    <div class="surface-card p-4">Your wishlist is empty.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($wishlistItems as $product): ?>
            <?php require __DIR__ . '/../components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
