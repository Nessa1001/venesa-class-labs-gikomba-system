<?php
$products = $products ?? [];
$totalProducts = $totalProducts ?? count($products);
$perPage = $perPage ?? 12;
$pageNumber = $pageNumber ?? 1;
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="mb-1">Shop Affordable Fashion</h1>
        <p class="text-muted mb-0">Search products and filter by category and price instantly.</p>
    </div>
    <a href="index.php?page=cart" class="btn btn-accent">Go to Cart</a>
</section>

<section class="filter-bar p-3 mb-4" id="shopFilters">
    <div class="row g-3">
        <div class="col-md-4"><input class="form-control" id="productSearchInput" placeholder="Search jeans, jacket, dress..." autocomplete="off"></div>
        <div class="col-md-3">
            <select class="form-select" id="categoryFilterSelect">
                <option value="all">All Categories</option>
                <option value="dresses">Dresses</option>
                <option value="jeans">Jeans</option>
                <option value="shirts">Shirts</option>
                <option value="trousers">Trousers</option>
                <option value="jackets">Jackets</option>
                <option value="shoes">Shoes</option>
                <option value="bags">Bags</option>
                <option value="tops">Tops</option>
                <option value="sweaters">Sweaters</option>
                <option value="accessories">Accessories</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="priceFilterSelect">
                <option value="all">All Prices</option>
                <option value="0-500">Up to KSh 500</option>
                <option value="501-1000">KSh 501 - KSh 1,000</option>
                <option value="1001-1500">KSh 1,001 - KSh 1,500</option>
                <option value="1501-999999">Above KSh 1,500</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-dark w-100" id="clearFiltersBtn" type="button">Clear</button></div>
    </div>
</section>

<?php if (!$products): ?>
    <div class="surface-card p-4 text-center">No products available at the moment.</div>
<?php endif; ?>

<div class="row g-3 mb-4" id="shopProductsGrid">
    <?php foreach ($products as $product): ?>
        <?php require __DIR__ . '/../components/product-card.php'; ?>
    <?php endforeach; ?>
</div>
<div id="shopNoResults" class="surface-card p-3 text-center d-none">No matching products found. Try another search or filter.</div>

<?php
$totalPages = max(1, (int) ceil(($totalProducts ?? count($products)) / max(1, $perPage ?? 12)));
$currentPage = max(1, (int) ($pageNumber ?? 1));
?>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="index.php?page=categories&p=<?= max(1, $currentPage - 1) ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>"><a class="page-link" href="index.php?page=categories&p=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="index.php?page=categories&p=<?= min($totalPages, $currentPage + 1) ?>">Next</a>
        </li>
    </ul>
</nav>
