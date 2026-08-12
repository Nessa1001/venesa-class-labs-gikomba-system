<?php
$adminStats = $adminStats ?? [];
$adminProducts = $adminProducts ?? [];
$adminCategories = $adminCategories ?? [];
$adminOrders = $adminOrders ?? [];
$adminCustomers = $adminCustomers ?? [];
$feedbackEntries = $feedbackEntries ?? [];
$adminRecentOrders = $adminRecentOrders ?? [];
$adminRecentCustomers = $adminRecentCustomers ?? [];
$adminPopularProducts = $adminPopularProducts ?? [];
$selectedAdminCustomer = $selectedAdminCustomer ?? null;
$selectedAdminCustomerOrders = $selectedAdminCustomerOrders ?? [];
$selectedAdminOrder = $selectedAdminOrder ?? null;
$adminSection = $adminSection ?? 'dashboard';
$adminSearch = $adminSearch ?? '';
$statusOptions = ['pending', 'confirmed', 'processing', 'ready_for_delivery', 'completed', 'cancelled'];

$editProductId = (int) ($_GET['edit_product_id'] ?? 0);
$editProduct = null;
foreach ($adminProducts as $product) {
    if ((int) $product['id'] === $editProductId) {
        $editProduct = $product;
        break;
    }
}
?>

<section class="admin-shell">
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="surface-card p-3 admin-sidebar">
                <h4 class="mb-3">Gikomba Admin</h4>
                <nav class="d-grid gap-2">
                    <a class="btn btn-sm <?= $adminSection === 'dashboard' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=dashboard">Dashboard</a>
                    <a class="btn btn-sm <?= $adminSection === 'customers' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=customers">Customers</a>
                    <a class="btn btn-sm <?= $adminSection === 'products' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=products">Products</a>
                    <a class="btn btn-sm <?= $adminSection === 'categories' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=categories">Categories</a>
                    <a class="btn btn-sm <?= $adminSection === 'orders' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=orders">Orders</a>
                    <a class="btn btn-sm <?= $adminSection === 'feedback' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=feedback">Feedback</a>
                    <a class="btn btn-sm <?= $adminSection === 'reports' ? 'btn-accent' : 'btn-outline-dark' ?>" href="index.php?page=admin&section=reports">Reports</a>
                    <a class="btn btn-sm btn-outline-danger" href="index.php?page=logout">Logout</a>
                </nav>
            </div>
        </aside>

        <div class="col-lg-9">
            <section class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="mb-1">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Manage customers, products, categories, orders, and feedback.</p>
                </div>
                <a class="btn btn-outline-dark" href="database/schema.sql">Download Schema</a>
            </section>

            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="surface-card p-3"><h6>Total Customers</h6><h4><?= (int) ($adminStats['total_customers'] ?? 0) ?></h4></div></div>
                <div class="col-md-4"><div class="surface-card p-3"><h6>Total Products</h6><h4><?= (int) ($adminStats['total_products'] ?? 0) ?></h4></div></div>
                <div class="col-md-4"><div class="surface-card p-3"><h6>Total Orders</h6><h4><?= (int) ($adminStats['total_orders'] ?? 0) ?></h4></div></div>
                <div class="col-md-4"><div class="surface-card p-3"><h6>Pending Orders</h6><h4><?= (int) ($adminStats['pending_orders'] ?? 0) ?></h4></div></div>
                <div class="col-md-4"><div class="surface-card p-3"><h6>Completed Orders</h6><h4><?= (int) ($adminStats['completed_orders'] ?? 0) ?></h4></div></div>
                <div class="col-md-4"><div class="surface-card p-3"><h6>Total Sales</h6><h4><?= format_money((float) ($adminStats['total_sales'] ?? 0)) ?></h4></div></div>
            </div>

            <?php if ($adminSection === 'dashboard'): ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="surface-card p-4 h-100">
                            <h5>Recent Orders</h5>
                            <ul class="list-unstyled mb-0">
                                <?php if (!$adminRecentOrders): ?>
                                    <li class="text-muted">No recent orders.</li>
                                <?php else: ?>
                                    <?php foreach ($adminRecentOrders as $order): ?>
                                        <li class="py-2 border-bottom">#<?= e((string) $order['order_number']) ?> | <?= e((string) $order['first_name']) ?> | <?= format_money((float) $order['total_amount']) ?> | <?= e(str_replace('_', ' ', (string) $order['status'])) ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="surface-card p-4 h-100">
                            <h5>Recent Customers</h5>
                            <ul class="list-unstyled mb-0">
                                <?php if (!$adminRecentCustomers): ?>
                                    <li class="text-muted">No recent customers.</li>
                                <?php else: ?>
                                    <?php foreach ($adminRecentCustomers as $customer): ?>
                                        <li class="py-2 border-bottom"><?= e((string) $customer['first_name']) ?> <?= e((string) $customer['last_name']) ?> <span class="text-muted">(<?= e((string) $customer['email']) ?>)</span></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($adminSection === 'customers'): ?>
                <div class="surface-card p-4 mb-3">
                    <h5>Customer Management</h5>
                    <form method="get" action="index.php" class="row g-2 mb-3" id="adminCustomerSearchForm">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="section" value="customers">
                        <div class="col-md-10"><input class="form-control" name="q" value="<?= e((string) $adminSearch) ?>" placeholder="Search by name, email or phone"></div>
                        <div class="col-md-2"><button class="btn btn-outline-dark w-100" type="submit">Search</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Registration Date</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if (!$adminCustomers): ?>
                                    <tr><td colspan="7" class="text-center text-muted">No customers found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($adminCustomers as $customer): ?>
                                        <tr>
                                            <td><?= (int) $customer['id'] ?></td>
                                            <td><?= e((string) $customer['first_name']) ?> <?= e((string) $customer['last_name']) ?></td>
                                            <td><?= e((string) $customer['email']) ?></td>
                                            <td><?= e((string) $customer['phone']) ?></td>
                                            <td><?= date('d M Y', strtotime((string) $customer['created_at'])) ?></td>
                                            <td><?= (int) $customer['is_active'] === 1 ? 'Active' : 'Disabled' ?></td>
                                            <td>
                                                <a class="btn btn-outline-dark btn-sm" href="index.php?page=admin&section=customers&customer_id=<?= (int) $customer['id'] ?>">View/Edit</a>
                                                <form method="post" action="index.php?page=admin&action=toggle-customer&section=customers" class="d-inline admin-danger-form" data-confirm="Are you sure you want to remove this customer?">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>">
                                                    <input type="hidden" name="is_active" value="<?= (int) $customer['is_active'] ?>">
                                                    <button class="btn btn-outline-danger btn-sm" type="submit"><?= (int) $customer['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($selectedAdminCustomer): ?>
                    <div class="surface-card p-4 mb-3">
                        <h5>Customer Details</h5>
                        <form method="post" action="index.php?page=admin&action=update-customer&section=customers" class="row g-3" data-validate="admin-customer-edit">
                            <?= csrf_field() ?>
                            <input type="hidden" name="customer_id" value="<?= (int) $selectedAdminCustomer['id'] ?>">
                            <div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?= e((string) $selectedAdminCustomer['first_name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?= e((string) $selectedAdminCustomer['last_name']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e((string) $selectedAdminCustomer['email']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e((string) $selectedAdminCustomer['phone']) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1" <?= (int) $selectedAdminCustomer['is_active'] === 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= (int) $selectedAdminCustomer['is_active'] === 0 ? 'selected' : '' ?>>Disabled</option></select></div>
                            <div class="col-md-6"><label class="form-label">Registration Date</label><input class="form-control" value="<?= date('d M Y', strtotime((string) $selectedAdminCustomer['created_at'])) ?>" disabled></div>
                            <div class="col-12"><button class="btn btn-accent" type="submit">Update Customer</button></div>
                        </form>
                    </div>

                    <div class="surface-card p-4">
                        <h5>Order History</h5>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Order #</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
                                <tbody>
                                    <?php if (!$selectedAdminCustomerOrders): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No orders for this customer.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($selectedAdminCustomerOrders as $order): ?>
                                            <tr>
                                                <td><?= e((string) $order['order_number']) ?></td>
                                                <td><?= date('d M Y', strtotime((string) $order['created_at'])) ?></td>
                                                <td><?= e(str_replace('_', ' ', (string) $order['status'])) ?></td>
                                                <td><?= format_money((float) $order['total_amount']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($adminSection === 'products'): ?>
                <div class="surface-card p-4 mb-3">
                    <h5><?= $editProduct ? 'Edit Product' : 'Add Product' ?></h5>
                    <form method="post" action="index.php?page=admin&action=<?= $editProduct ? 'update-product' : 'add-product' ?>&section=products" class="row g-2" data-validate="admin-product-form">
                        <?= csrf_field() ?>
                        <?php if ($editProduct): ?><input type="hidden" name="product_id" value="<?= (int) $editProduct['id'] ?>"><?php endif; ?>
                        <div class="col-md-6"><label class="form-label">Product Name</label><input class="form-control" name="name" value="<?= e((string) ($editProduct['name'] ?? '')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category_id" required><?php foreach ($adminCategories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= isset($editProduct['category_id']) && (int) $editProduct['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e((string) $category['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Price</label><input class="form-control" type="number" step="0.01" name="price" value="<?= e((string) ($editProduct['price'] ?? '')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Discount Price</label><input class="form-control" type="number" step="0.01" name="discount_price" value="<?= e((string) ($editProduct['discount_price'] ?? '')) ?>"></div>
                        <div class="col-md-6"><label class="form-label">Stock</label><input class="form-control" type="number" name="stock" value="<?= e((string) ($editProduct['stock'] ?? '0')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Condition</label><input class="form-control" name="item_condition" value="<?= e((string) ($editProduct['item_condition'] ?? 'Good')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Sizes</label><input class="form-control" name="sizes" value="<?= e((string) ($editProduct['sizes'] ?? '')) ?>" placeholder="S,M,L"></div>
                        <div class="col-md-6"><label class="form-label">Colors</label><input class="form-control" name="colors" value="<?= e((string) ($editProduct['colors'] ?? '')) ?>" placeholder="Black,Blue"></div>
                        <div class="col-md-6"><label class="form-label">Badge</label><select class="form-select" name="badge"><option <?= ($editProduct['badge'] ?? '') === 'Trending' ? 'selected' : '' ?>>Trending</option><option <?= ($editProduct['badge'] ?? '') === 'New' ? 'selected' : '' ?>>New</option><option <?= ($editProduct['badge'] ?? '') === 'Sale' ? 'selected' : '' ?>>Sale</option></select></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"><?= e((string) ($editProduct['description'] ?? '')) ?></textarea></div>
                        <div class="col-md-4"><label class="form-label">Primary Image</label><input class="form-control" name="image_primary" value="<?= e((string) ($editProduct['image_primary'] ?? 'images.jpg')) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Secondary Image</label><input class="form-control" name="image_secondary" value="<?= e((string) ($editProduct['image_secondary'] ?? '')) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Tertiary Image</label><input class="form-control" name="image_tertiary" value="<?= e((string) ($editProduct['image_tertiary'] ?? '')) ?>"></div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-accent" type="submit"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
                            <?php if ($editProduct): ?><a class="btn btn-outline-dark" href="index.php?page=admin&section=products">Cancel Edit</a><?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="surface-card p-4">
                    <h5>Product Management</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Size</th><th>Condition</th><th>Availability</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($adminProducts as $product): ?>
                                    <tr>
                                        <td><?= (int) $product['id'] ?></td>
                                        <td><img src="<?= e((string) $product['image_primary']) ?>" alt="<?= e((string) $product['name']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;"></td>
                                        <td><?= e((string) $product['name']) ?></td>
                                        <td><?= e((string) $product['category_name']) ?></td>
                                        <td><?= format_money((float) $product['price']) ?></td>
                                        <td><?= e((string) ($product['sizes'] ?: 'One Size')) ?></td>
                                        <td><?= e((string) ($product['item_condition'] ?? 'Good')) ?></td>
                                        <td>
                                            <?php if ((int) ($product['is_active'] ?? 1) === 0): ?>
                                                Inactive
                                            <?php else: ?>
                                                <?= (int) $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-outline-dark btn-sm" href="index.php?page=admin&section=products&edit_product_id=<?= (int) $product['id'] ?>">Edit</a>
                                            <form method="post" action="index.php?page=admin&action=delete-product&section=products" class="d-inline admin-danger-form" data-confirm="Are you sure you want to delete this product?">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($adminSection === 'categories'): ?>
                <div class="surface-card p-4 mb-3">
                    <h5>Add Category</h5>
                    <form method="post" action="index.php?page=admin&action=add-category&section=categories" class="row g-2" data-validate="admin-category-form">
                        <?= csrf_field() ?>
                        <div class="col-md-6"><label class="form-label">Category Name</label><input class="form-control" name="name" required></div>
                        <div class="col-md-6"><label class="form-label">Description</label><input class="form-control" name="description"></div>
                        <div class="col-12"><button class="btn btn-accent" type="submit">Add Category</button></div>
                    </form>
                </div>
                <div class="surface-card p-4">
                    <h5>Category Management</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Name</th><th>Description</th><th>Update</th><th>Delete</th></tr></thead>
                            <tbody>
                                <?php foreach ($adminCategories as $category): ?>
                                    <tr>
                                        <td><?= e((string) $category['name']) ?></td>
                                        <td><?= e((string) $category['description']) ?></td>
                                        <td>
                                            <form method="post" action="index.php?page=admin&action=update-category&section=categories" class="d-flex gap-2">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                                                <input class="form-control form-control-sm" name="name" value="<?= e((string) $category['name']) ?>" required>
                                                <input class="form-control form-control-sm" name="description" value="<?= e((string) $category['description']) ?>">
                                                <button class="btn btn-outline-dark btn-sm" type="submit">Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" action="index.php?page=admin&action=delete-category&section=categories" class="admin-danger-form" data-confirm="Delete this category?">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($adminSection === 'orders'): ?>
                <div class="surface-card p-4 mb-3">
                    <h5>Order Management</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Order ID</th><th>Customer</th><th>Order Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($adminOrders as $order): ?>
                                    <tr>
                                        <td><?= e((string) $order['order_number']) ?></td>
                                        <td><?= e((string) $order['first_name']) ?> <?= e((string) $order['last_name']) ?></td>
                                        <td><?= date('d M Y', strtotime((string) $order['created_at'])) ?></td>
                                        <td><?= format_money((float) $order['total_amount']) ?></td>
                                        <td><?= strtoupper(e((string) $order['payment_method'])) ?></td>
                                        <td>
                                            <form method="post" action="index.php?page=admin&action=update-order-status&section=orders" class="d-flex gap-2 align-items-center">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                                <select class="form-select form-select-sm" name="status">
                                                    <?php foreach ($statusOptions as $status): ?>
                                                        <option value="<?= e($status) ?>" <?= (string) $order['status'] === $status ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $status)) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-outline-dark btn-sm" type="submit">Save</button>
                                            </form>
                                        </td>
                                        <td><a class="btn btn-outline-dark btn-sm" href="index.php?page=admin&section=orders&order_id=<?= (int) $order['id'] ?>">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($selectedAdminOrder): ?>
                    <div class="surface-card p-4">
                        <h5>Order Details: <?= e((string) $selectedAdminOrder['order_number']) ?></h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><p class="mb-1"><strong>Name:</strong> <?= e((string) $selectedAdminOrder['customer_name']) ?></p><p class="mb-1"><strong>Email:</strong> <?= e((string) $selectedAdminOrder['email']) ?></p><p class="mb-1"><strong>Phone:</strong> <?= e((string) $selectedAdminOrder['phone']) ?></p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Location:</strong> <?= e((string) $selectedAdminOrder['county']) ?>, <?= e((string) $selectedAdminOrder['town']) ?>, <?= e((string) $selectedAdminOrder['street']) ?>, <?= e((string) $selectedAdminOrder['house_number']) ?></p><p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime((string) $selectedAdminOrder['created_at'])) ?></p></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                                <tbody>
                                    <?php foreach (($selectedAdminOrder['items'] ?? []) as $item): ?>
                                        <tr>
                                            <td><?= e((string) $item['product_name']) ?></td>
                                            <td><?= format_money((float) $item['unit_price']) ?></td>
                                            <td><?= (int) $item['quantity'] ?></td>
                                            <td><?= format_money((float) $item['total_price']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="mb-1"><strong>Subtotal:</strong> <?= format_money((float) $selectedAdminOrder['subtotal']) ?></p>
                        <p class="mb-1"><strong>Delivery:</strong> <?= format_money((float) $selectedAdminOrder['shipping_fee']) ?></p>
                        <p class="mb-0"><strong>Total:</strong> <?= format_money((float) $selectedAdminOrder['total_amount']) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($adminSection === 'feedback'): ?>
                <div class="surface-card p-4">
                    <h5>Feedback Management</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Customer</th><th>Rating</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if (!$feedbackEntries): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No feedback records.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($feedbackEntries as $entry): ?>
                                        <tr>
                                            <td><?= e((string) $entry['name']) ?><br><small class="text-muted"><?= e((string) $entry['email']) ?></small></td>
                                            <td><?= str_repeat('★', (int) $entry['rating']) ?></td>
                                            <td><?= e((string) $entry['message']) ?></td>
                                            <td><?= date('d M Y', strtotime((string) $entry['created_at'])) ?></td>
                                            <td>
                                                <form method="post" action="index.php?page=admin&action=delete-feedback&section=feedback" class="admin-danger-form" data-confirm="Remove this feedback message?">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="feedback_id" value="<?= (int) $entry['id'] ?>">
                                                    <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($adminSection === 'reports'): ?>
                <div class="surface-card p-4 mb-3">
                    <h5>Reports Overview</h5>
                    <div class="row g-3">
                        <div class="col-md-4"><div class="p-3 border rounded">Total Customers: <strong><?= (int) ($adminStats['total_customers'] ?? 0) ?></strong></div></div>
                        <div class="col-md-4"><div class="p-3 border rounded">Total Products: <strong><?= (int) ($adminStats['total_products'] ?? 0) ?></strong></div></div>
                        <div class="col-md-4"><div class="p-3 border rounded">Total Orders: <strong><?= (int) ($adminStats['total_orders'] ?? 0) ?></strong></div></div>
                        <div class="col-md-4"><div class="p-3 border rounded">Completed Orders: <strong><?= (int) ($adminStats['completed_orders'] ?? 0) ?></strong></div></div>
                        <div class="col-md-4"><div class="p-3 border rounded">Pending Orders: <strong><?= (int) ($adminStats['pending_orders'] ?? 0) ?></strong></div></div>
                        <div class="col-md-4"><div class="p-3 border rounded">Total Sales: <strong><?= format_money((float) ($adminStats['total_sales'] ?? 0)) ?></strong></div></div>
                    </div>
                </div>

                <div class="surface-card p-4 mb-3">
                    <h5>Most Popular Products</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Product</th><th>Units Sold</th><th>Sales</th></tr></thead>
                            <tbody>
                                <?php if (!$adminPopularProducts): ?>
                                    <tr><td colspan="3" class="text-center text-muted">No product sales records yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($adminPopularProducts as $product): ?>
                                        <tr>
                                            <td><?= e((string) $product['product_name']) ?></td>
                                            <td><?= (int) $product['total_quantity'] ?></td>
                                            <td><?= format_money((float) $product['total_sales']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="surface-card p-4 h-100">
                            <h6>Recent Orders</h6>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($adminRecentOrders as $order): ?>
                                    <li class="py-2 border-bottom"><?= e((string) $order['order_number']) ?> - <?= format_money((float) $order['total_amount']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="surface-card p-4 h-100">
                            <h6>Recent Customers</h6>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($adminRecentCustomers as $customer): ?>
                                    <li class="py-2 border-bottom"><?= e((string) $customer['first_name']) ?> <?= e((string) $customer['last_name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
