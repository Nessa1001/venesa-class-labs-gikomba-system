<section class="mb-4">
    <h1 class="mb-1">Customer Dashboard</h1>
    <p class="text-muted mb-0">Welcome back<?= isset($authUser['name']) ? ', ' . e($authUser['name']) : '' ?>.</p>
</section>

<div class="row g-3">
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=orders"><h5>My Orders</h5><p class="mb-0 text-muted">View purchase history.</p></a></div>
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=tracking"><h5>Track Orders</h5><p class="mb-0 text-muted">Check delivery progress.</p></a></div>
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=wishlist"><h5>Wishlist</h5><p class="mb-0 text-muted">Saved products.</p></a></div>
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=addresses"><h5>Addresses</h5><p class="mb-0 text-muted">Manage delivery locations.</p></a></div>
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=profile"><h5>Profile</h5><p class="mb-0 text-muted">Edit account details.</p></a></div>
    <div class="col-md-4"><a class="surface-card p-4 d-block text-decoration-none text-dark" href="index.php?page=logout"><h5>Logout</h5><p class="mb-0 text-muted">Sign out securely.</p></a></div>
</div>
