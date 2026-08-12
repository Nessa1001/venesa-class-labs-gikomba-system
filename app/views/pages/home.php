<?php $products = $products ?? []; ?>
<section class="hero-banner mb-4">
    <p class="hero-subtitle mb-2">Affordable Second-Hand Fashion</p>
    <h1 class="hero-title mb-3">Find quality clothes at affordable prices from Gikomba.</h1>
    <p class="mb-4">Shop dresses, jeans, shirts, jackets, shoes, and bags with student-friendly prices from KSh 300.</p>
    <div class="d-flex gap-2 flex-wrap">
        <a href="index.php?page=shop" class="btn btn-accent">Shop Now</a>
        <a href="index.php?page=categories" class="btn btn-outline-light">Browse Categories</a>
    </div>
</section>

<section class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Featured Products</h2>
        <a href="index.php?page=shop" class="btn btn-sm btn-outline-dark">View All</a>
    </div>
    <div class="row g-3">
        <?php foreach (array_slice($products, 0, 8) as $product): ?>
            <?php require __DIR__ . '/../components/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="surface-card p-4">
    <div class="row g-4 text-center">
        <div class="col-md-3"><h3>500+</h3><p class="mb-0">Affordable Listings</p></div>
        <div class="col-md-3"><h3>10</h3><p class="mb-0">Main Clothing Categories</p></div>
        <div class="col-md-3"><h3>KSh 300+</h3><p class="mb-0">Student-Friendly Prices</p></div>
        <div class="col-md-3"><h3>Daily</h3><p class="mb-0">New Stock Updates</p></div>
    </div>
</section>
