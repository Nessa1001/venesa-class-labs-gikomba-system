<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

use App\models\CartModel;
use App\models\CategoryModel;
use App\models\AddressModel;
use App\models\OrderModel;
use App\models\FeedbackModel;
use App\models\ProductModel;
use App\models\ReviewModel;
use App\models\UserModel;
use App\models\WishlistModel;
use App\services\NotificationService;

$page = trim((string) ($_GET['page'] ?? 'home'));
$action = trim((string) ($_GET['action'] ?? ''));

function render_page(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/app/views/layouts/header.php';
    require __DIR__ . '/app/views/pages/' . $view . '.php';
    require __DIR__ . '/app/views/layouts/footer.php';
}

function fallback_products(): array
{
    return [
        ['id' => 1, 'name' => 'Ladies Denim Jacket', 'slug' => 'ladies-denim-jacket', 'price' => 1000, 'discount_price' => 800, 'image_primary' => 'dress.jpg', 'category_name' => 'Jackets', 'description' => 'Clean second-hand denim jacket from Gikomba.', 'badge' => 'Sale', 'rating' => 4.3, 'review_count' => 18, 'stock' => 8, 'sizes' => 'S,M,L', 'colors' => 'Blue,Navy', 'item_condition' => 'Excellent'],
        ['id' => 2, 'name' => "Men\'s Jeans", 'slug' => 'mens-jeans', 'price' => 750, 'discount_price' => 700, 'image_primary' => 'trouser.jpg', 'category_name' => 'Jeans', 'description' => 'Affordable straight-fit jeans in good condition.', 'badge' => 'Trending', 'rating' => 4.6, 'review_count' => 37, 'stock' => 12, 'sizes' => '30,32,34,36', 'colors' => 'Blue,Black', 'item_condition' => 'Good'],
        ['id' => 3, 'name' => 'Floral Dress', 'slug' => 'floral-dress', 'price' => 750, 'discount_price' => 600, 'image_primary' => 'skirt.jpg', 'category_name' => 'Dresses', 'description' => 'Light floral dress, perfect for casual events.', 'badge' => 'New', 'rating' => 4.4, 'review_count' => 20, 'stock' => 5, 'sizes' => 'S,M,L', 'colors' => 'Pink,White', 'item_condition' => 'Very Good'],
        ['id' => 4, 'name' => 'Casual Shirt', 'slug' => 'casual-shirt', 'price' => 500, 'discount_price' => null, 'image_primary' => 'shorts.jpg', 'category_name' => 'Shirts', 'description' => 'Soft cotton shirt with neat stitching.', 'badge' => 'Trending', 'rating' => 4.1, 'review_count' => 10, 'stock' => 9, 'sizes' => 'M,L,XL', 'colors' => 'White,Checked', 'item_condition' => 'Good'],
        ['id' => 5, 'name' => 'Unisex Hoodie', 'slug' => 'unisex-hoodie', 'price' => 1200, 'discount_price' => 1000, 'image_primary' => 'clothes.jpg', 'category_name' => 'Sweaters', 'description' => 'Warm hoodie for cool evenings and campus wear.', 'badge' => 'Sale', 'rating' => 4.5, 'review_count' => 28, 'stock' => 6, 'sizes' => 'M,L,XL', 'colors' => 'Grey,Black', 'item_condition' => 'Excellent'],
    ];
}

function cart_totals(array $items): array
{
    $subtotal = 0.0;

    foreach ($items as $item) {
        $price = isset($item['discount_price']) && $item['discount_price'] !== null ? (float) $item['discount_price'] : (float) $item['price'];
        $subtotal += $price * (int) $item['quantity'];
    }

    $shipping = (float) config('shipping_fee', 0);
    $vat = $subtotal * (float) config('vat_rate', 0);
    $total = $subtotal + $shipping + $vat;

    return compact('subtotal', 'shipping', 'vat', 'total');
}

function make_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
    $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';

    return trim($slug, '-');
}

$productModel = null;
$cartModel = null;
$categoryModel = null;
$addressModel = null;
$userModel = null;
$orderModel = null;
$reviewModel = null;
$wishlistModel = null;
$feedbackModel = null;
$dbAvailable = true;

try {
    $productModel = new ProductModel();
    $cartModel = new CartModel();
    $categoryModel = new CategoryModel();
    $addressModel = new AddressModel();
    $userModel = new UserModel();
    $orderModel = new OrderModel();
    $reviewModel = new ReviewModel();
    $wishlistModel = new WishlistModel();
    $feedbackModel = new FeedbackModel();
} catch (Throwable $throwable) {
    $dbAvailable = false;
    flash_message('warning', 'Database is not connected yet. Run database/schema.sql then update app/config/database.php.');
}

if ($page === 'logout') {
    unset($_SESSION['auth_user']);
    flash_message('success', 'You have logged out successfully.');
    redirect('index.php?page=home');
}

if ($page === 'subscribe' && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=home');
    }

    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        flash_message('error', 'Please enter a valid email address.');
    } else {
        flash_message('success', 'Newsletter subscription successful.');
    }
    redirect('index.php?page=home');
}

if ($page === 'contact' && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=contact');
    }

    if (!$dbAvailable || !$feedbackModel) {
        flash_message('error', 'Database unavailable. Feedback not saved.');
        redirect('index.php?page=contact');
    }

    $payload = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'rating' => (int) ($_POST['rating'] ?? 0),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    if ($payload['name'] === '' || $payload['message'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL) || $payload['rating'] < 1 || $payload['rating'] > 5) {
        flash_message('error', 'Please complete all feedback fields correctly.');
        redirect('index.php?page=contact');
    }

    $feedbackModel->create($payload);
    flash_message('success', 'Thank you. Your feedback has been submitted successfully.');
    redirect('index.php?page=contact');
}

if ($page === 'register' && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=register');
    }

    $payload = [
        'first_name' => trim((string) ($_POST['first_name'] ?? '')),
        'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'password' => (string) ($_POST['password'] ?? ''),
        'confirm_password' => (string) ($_POST['confirm_password'] ?? ''),
    ];

    flash_old($payload);

    if (!$dbAvailable || !$userModel) {
        flash_message('error', 'Database unavailable. Cannot register now.');
        redirect('index.php?page=register');
    }

    if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        flash_message('error', 'Invalid email format.');
        redirect('index.php?page=register');
    }

    if ($payload['password'] !== $payload['confirm_password'] || strlen($payload['password']) < 8) {
        flash_message('error', 'Password must be at least 8 characters and match confirmation.');
        redirect('index.php?page=register');
    }

    if ($userModel->findByEmail($payload['email'])) {
        flash_message('error', 'An account with this email already exists.');
        redirect('index.php?page=register');
    }

    $id = $userModel->create($payload);
    $_SESSION['auth_user'] = [
        'id' => $id,
        'name' => $payload['first_name'] . ' ' . $payload['last_name'],
        'email' => $payload['email'],
        'role' => 'customer',
    ];

    clear_old();
    flash_message('success', 'Account created successfully. Welcome!');
    redirect('index.php?page=dashboard');
}

if (($page === 'login' || $page === 'admin-login') && is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=' . ($page === 'admin-login' ? 'admin-login' : 'login'));
    }

    if (!$dbAvailable || !$userModel) {
        flash_message('error', 'Database unavailable. Cannot login now.');
        redirect('index.php?page=' . ($page === 'admin-login' ? 'admin-login' : 'login'));
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = $userModel->findByEmailOrUsername($email);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash_message('error', $page === 'admin-login' ? 'Invalid admin credentials.' : 'Invalid login credentials.');
        redirect('index.php?page=' . ($page === 'admin-login' ? 'admin-login' : 'login'));
    }

    if ($page === 'admin-login' && (string) ($user['role'] ?? '') !== 'admin') {
        flash_message('error', 'Invalid admin credentials.');
        redirect('index.php?page=admin-login');
    }

    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'name' => trim($user['first_name'] . ' ' . $user['last_name']),
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    flash_message('success', 'Welcome back, ' . $user['first_name'] . '.');
    redirect('index.php?page=' . ($page === 'admin-login' ? 'admin' : 'dashboard'));
}

if ($page === 'cart' && in_array($action, ['add', 'remove', 'update'], true) && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=cart');
    }

    if (!$dbAvailable || !$cartModel) {
        flash_message('error', 'Database unavailable. Cart action failed.');
        redirect('index.php?page=cart');
    }

    $cart = $cartModel->getOrCreateCart(auth_user_id());

    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            flash_message('error', 'Invalid product selected.');
            redirect('index.php?page=categories');
        }

        if ($dbAvailable && $productModel && !$productModel->findById($productId)) {
            flash_message('error', 'Product not found.');
            redirect('index.php?page=categories');
        }

        $cartModel->addItem((int) $cart['id'], $productId, $quantity);
        flash_message('success', 'Item added to cart.');
    }

    if ($action === 'remove') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $cartModel->removeItem($itemId);
        flash_message('success', 'Item removed from cart.');
    }

    if ($action === 'update') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $cartModel->updateQuantity($itemId, $quantity);
        flash_message('success', 'Cart updated.');
    }

    redirect('index.php?page=cart');
}

if ($page === 'wishlist' && in_array($action, ['add', 'remove'], true) && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=wishlist');
    }

    if (!$dbAvailable || !$wishlistModel) {
        flash_message('error', 'Database unavailable. Wishlist action failed.');
        redirect('index.php?page=wishlist');
    }

    if ($action === 'add') {
        $wishlistModel->add(auth_user_id(), (int) ($_POST['product_id'] ?? 0));
        flash_message('success', 'Added to wishlist.');
    }

    if ($action === 'remove') {
        $wishlistModel->remove((int) ($_POST['wishlist_id'] ?? 0), auth_user_id());
        flash_message('success', 'Removed from wishlist.');
    }

    redirect('index.php?page=wishlist');
}

if ($page === 'addresses' && in_array($action, ['add', 'delete', 'default'], true) && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=addresses');
    }

    if (!$dbAvailable || !$addressModel) {
        flash_message('error', 'Database unavailable. Address action failed.');
        redirect('index.php?page=addresses');
    }

    if ($action === 'add') {
        $payload = [
            'county' => trim((string) ($_POST['county'] ?? '')),
            'town' => trim((string) ($_POST['town'] ?? '')),
            'street' => trim((string) ($_POST['street'] ?? '')),
            'house_number' => trim((string) ($_POST['house_number'] ?? '')),
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        if ($payload['county'] === '' || $payload['town'] === '' || $payload['street'] === '' || $payload['house_number'] === '') {
            flash_message('error', 'All address fields are required.');
            redirect('index.php?page=addresses');
        }

        $addressModel->create(auth_user_id(), $payload);
        flash_message('success', 'Address added successfully.');
    }

    if ($action === 'delete') {
        $addressModel->delete(auth_user_id(), (int) ($_POST['address_id'] ?? 0));
        flash_message('success', 'Address removed.');
    }

    if ($action === 'default') {
        $addressModel->setDefault(auth_user_id(), (int) ($_POST['address_id'] ?? 0));
        flash_message('success', 'Default address updated.');
    }

    redirect('index.php?page=addresses');
}

if ($page === 'product' && $action === 'review' && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=categories');
    }

    if (!$dbAvailable || !$reviewModel) {
        flash_message('error', 'Database unavailable. Review action failed.');
        redirect('index.php?page=categories');
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
    $reviewText = trim((string) ($_POST['review_text'] ?? ''));

    if ($productId <= 0 || $reviewText === '') {
        flash_message('error', 'Please provide a review message and rating.');
        redirect('index.php?page=product&id=' . $productId);
    }

    $reviewModel->create(auth_user_id(), $productId, $rating, $reviewText);
    flash_message('success', 'Review submitted successfully.');
    redirect('index.php?page=product&id=' . $productId);
}

if ($page === 'admin' && in_array($action, ['add-product', 'update-product', 'delete-product', 'add-category', 'update-category', 'delete-category', 'update-order-status', 'toggle-customer', 'update-customer', 'delete-feedback'], true) && is_post()) {
    require_admin();
    $adminReturnSection = trim((string) ($_GET['section'] ?? $_POST['section'] ?? 'dashboard'));
    $adminReturnUrl = 'index.php?page=admin&section=' . urlencode($adminReturnSection);

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect($adminReturnUrl);
    }

    if (!$dbAvailable) {
        flash_message('error', 'Database unavailable. Admin action failed.');
        redirect($adminReturnUrl);
    }

    try {

    if (($action === 'add-product' || $action === 'update-product') && $productModel) {
        $id = (int) ($_POST['product_id'] ?? 0);
        $payload = [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => make_slug((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => (float) ($_POST['price'] ?? 0),
            'discount_price' => $_POST['discount_price'] === '' ? null : (float) ($_POST['discount_price'] ?? 0),
            'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
            'item_condition' => trim((string) ($_POST['item_condition'] ?? 'Good')),
            'sizes' => trim((string) ($_POST['sizes'] ?? '')),
            'colors' => trim((string) ($_POST['colors'] ?? '')),
            'badge' => trim((string) ($_POST['badge'] ?? 'Trending')),
            'image_primary' => trim((string) ($_POST['image_primary'] ?? 'images.jpg')),
            'image_secondary' => trim((string) ($_POST['image_secondary'] ?? '')),
            'image_tertiary' => trim((string) ($_POST['image_tertiary'] ?? '')),
        ];

        if ($payload['category_id'] <= 0 || $payload['name'] === '' || $payload['price'] <= 0) {
            flash_message('error', 'Category, name, and valid price are required.');
            redirect($adminReturnUrl);
        }

        if ($action === 'add-product') {
            $productModel->create($payload);
            flash_message('success', 'Product added successfully.');
        } else {
            $productModel->update($id, $payload);
            flash_message('success', 'Product updated successfully.');
        }
    }

    if ($action === 'delete-product' && $productModel) {
        $productModel->delete((int) ($_POST['product_id'] ?? 0));
        flash_message('success', 'Product deleted successfully.');
    }

    if (($action === 'add-category' || $action === 'update-category') && $categoryModel) {
        $id = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $payload = [
            'name' => $name,
            'slug' => make_slug($name),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];

        if ($payload['name'] === '') {
            flash_message('error', 'Category name is required.');
            redirect($adminReturnUrl);
        }

        if ($action === 'add-category') {
            $categoryModel->create($payload);
            flash_message('success', 'Category added successfully.');
        } else {
            $categoryModel->update($id, $payload);
            flash_message('success', 'Category updated successfully.');
        }
    }

    if ($action === 'delete-category' && $categoryModel) {
        $categoryModel->delete((int) ($_POST['category_id'] ?? 0));
        flash_message('success', 'Category deleted successfully.');
    }

    if ($action === 'update-order-status' && $orderModel) {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? 'pending'));
        $allowed = ['pending', 'confirmed', 'processing', 'ready_for_delivery', 'completed', 'cancelled'];

        if (!in_array($status, $allowed, true)) {
            flash_message('error', 'Invalid order status.');
            redirect($adminReturnUrl);
        }

        $orderModel->updateStatus($orderId, $status);
        flash_message('success', 'Order status updated.');
    }

    if ($action === 'toggle-customer' && $userModel) {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $isActive = (int) ($_POST['is_active'] ?? 1) === 1;
        $userModel->setActiveStatus($customerId, !$isActive);
        flash_message('success', 'Customer status updated.');
    }

    if ($action === 'update-customer' && $userModel) {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $isActive = (int) ($_POST['is_active'] ?? 1) === 1;

        if ($customerId <= 0 || $firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^(\+254|0)\d{9}$/', $phone)) {
            flash_message('error', 'Please provide valid customer details.');
            redirect('index.php?page=admin&section=customers&customer_id=' . $customerId);
        }

        $userModel->updateByAdmin($customerId, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive,
        ]);
        flash_message('success', 'Customer details updated successfully.');
    }

    if ($action === 'delete-feedback' && $feedbackModel) {
        $feedbackId = (int) ($_POST['feedback_id'] ?? 0);
        if ($feedbackId > 0) {
            $feedbackModel->delete($feedbackId);
            flash_message('success', 'Feedback removed successfully.');
        }
    }

    } catch (Throwable $throwable) {
        flash_message('error', 'Admin action failed: ' . $throwable->getMessage());
    }

    redirect($adminReturnUrl);
}

if ($page === 'checkout' && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=checkout');
    }

    $_SESSION['checkout'] = [
        'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'county' => trim((string) ($_POST['county'] ?? '')),
        'town' => trim((string) ($_POST['town'] ?? '')),
        'street' => trim((string) ($_POST['street'] ?? '')),
        'house_number' => trim((string) ($_POST['house_number'] ?? '')),
    ];

    redirect('index.php?page=payment');
}

if ($page === 'payment' && $action === 'confirm' && is_post()) {
    require_auth();

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        flash_message('error', 'Invalid CSRF token.');
        redirect('index.php?page=payment');
    }

    if (!$dbAvailable || !$cartModel || !$orderModel) {
        flash_message('error', 'Database unavailable. Payment flow paused.');
        redirect('index.php?page=payment');
    }

    $method = trim((string) ($_POST['payment_method'] ?? 'mpesa'));
    $checkout = $_SESSION['checkout'] ?? [];

    $requiredFields = ['customer_name', 'phone', 'email', 'county', 'town', 'street', 'house_number'];
    foreach ($requiredFields as $field) {
        if (empty($checkout[$field])) {
            flash_message('error', 'Please complete your checkout details first.');
            redirect('index.php?page=checkout');
        }
    }

    if ($method !== 'mpesa' && $method !== 'cod') {
        flash_message('error', 'Invalid payment method selected.');
        redirect('index.php?page=payment');
    }

    if ($method === 'mpesa') {
        $phone = trim((string) ($_POST['mpesa_phone'] ?? ''));
        if (!preg_match('/^(\+254|0)\d{9}$/', $phone)) {
            flash_message('error', 'Invalid M-Pesa phone number.');
            redirect('index.php?page=payment');
        }
    }

    $cart = $cartModel->getOrCreateCart(auth_user_id());
    $items = $cartModel->items((int) $cart['id']);

    if (!$items) {
        flash_message('error', 'Your cart is empty.');
        redirect('index.php?page=cart');
    }

    $totals = cart_totals($items);

    $orderId = $orderModel->createOrder([
        'user_id' => auth_user_id(),
        'customer_name' => $checkout['customer_name'],
        'phone' => $checkout['phone'],
        'email' => $checkout['email'],
        'county' => $checkout['county'],
        'town' => $checkout['town'],
        'street' => $checkout['street'],
        'house_number' => $checkout['house_number'],
        'payment_method' => $method,
    ], $items, $totals['subtotal'], $totals['shipping'], $totals['vat'], $totals['total']);

    $order = $orderModel->findById($orderId);

    if ($order) {
        $cartModel->clear((int) $cart['id']);

        $notificationService = new NotificationService();
        $notificationService->sendOrderEmail($order, $order['items']);
        $notificationService->sendOrderSms($order['phone'], $order['order_number']);

        $_SESSION['latest_order_id'] = $orderId;
        unset($_SESSION['checkout']);

        flash_message('success', 'Payment successful. Your order has been placed.');
        redirect('index.php?page=order-success');
    }

    flash_message('error', 'Payment completed but order retrieval failed.');
    redirect('index.php?page=home');
}

$products = fallback_products();
$totalProducts = count($products);

if ($dbAvailable && $productModel) {
    try {
        $pageNumber = max(1, (int) ($_GET['p'] ?? 1));
        $perPage = 12;
        $offset = ($pageNumber - 1) * $perPage;

        $products = $productModel->all($perPage, $offset);
        $totalProducts = $productModel->countAll();
    } catch (Throwable $throwable) {
        $products = fallback_products();
        $totalProducts = count($products);
    }
}

$cartItems = [];
$wishlistItems = [];
$cartCount = 0;
$wishlistCount = 0;

if (is_authenticated() && $dbAvailable && $cartModel && $wishlistModel) {
    try {
        $cart = $cartModel->getOrCreateCart(auth_user_id());
        $cartItems = $cartModel->items((int) $cart['id']);
        $wishlistItems = $wishlistModel->items(auth_user_id());
        $cartCount = count($cartItems);
        $wishlistCount = $wishlistModel->count(auth_user_id());
    } catch (Throwable $throwable) {
        $cartItems = [];
    }
}

$selectedProduct = null;
$productReviews = [];
if ($page === 'product') {
    $productId = max(1, (int) ($_GET['id'] ?? 1));

    if ($dbAvailable && $productModel) {
        $selectedProduct = $productModel->findById($productId);
    }

    if (!$selectedProduct) {
        foreach (fallback_products() as $fallback) {
            if ((int) $fallback['id'] === $productId) {
                $selectedProduct = $fallback;
                break;
            }
        }
    }

    if ($selectedProduct && $dbAvailable && $reviewModel) {
        $productReviews = $reviewModel->listByProduct((int) $selectedProduct['id']);
    }
}

$totals = cart_totals($cartItems);
$currentOrder = null;
$userOrders = [];
$userAddresses = [];
$categories = [];
$adminProducts = [];
$adminCategories = [];
$adminOrders = [];
$adminCustomers = [];
$feedbackEntries = [];
$adminRecentOrders = [];
$adminRecentCustomers = [];
$adminPopularProducts = [];
$selectedAdminCustomer = null;
$selectedAdminCustomerOrders = [];
$selectedAdminOrder = null;
$adminSearch = trim((string) ($_GET['q'] ?? ''));
$adminSection = trim((string) ($_GET['section'] ?? 'dashboard'));
$adminStats = [
    'total_customers' => 0,
    'total_products' => 0,
    'total_orders' => 0,
    'pending_orders' => 0,
    'completed_orders' => 0,
    'total_sales' => 0,
    'customers' => 0,
    'orders' => 0,
    'revenue' => 0,
    'low_stock_items' => 0,
    'feedback_total' => 0,
];

if ($dbAvailable && $categoryModel) {
    $categories = $categoryModel->all();
}

if ($dbAvailable && $feedbackModel) {
    $feedbackEntries = $feedbackModel->all(12);
}

if (is_authenticated() && $dbAvailable && $orderModel) {
    $userOrders = $orderModel->listByUser(auth_user_id());
}

if (is_authenticated() && $dbAvailable && $addressModel) {
    $userAddresses = $addressModel->listByUser(auth_user_id());
}

if ($page === 'order-success' && $dbAvailable && $orderModel) {
    $latestOrderId = (int) ($_SESSION['latest_order_id'] ?? 0);
    if ($latestOrderId > 0) {
        $currentOrder = $orderModel->findById($latestOrderId);
    }
}

if ($page === 'tracking' && $dbAvailable && $orderModel && isset($_GET['order_number'])) {
    $currentOrder = $orderModel->findByOrderNumber(trim((string) $_GET['order_number']));
}

if ($page === 'orders' && !empty($userOrders)) {
    $currentOrder = $userOrders[0];
}

if ($page === 'admin' && $dbAvailable && $productModel && $categoryModel && $orderModel && $userModel) {
    $adminProducts = $productModel->allForAdmin();
    $adminCategories = $categoryModel->all();
    $adminOrders = $orderModel->listAll();
    $adminCustomers = $userModel->listCustomers($adminSearch);

    $adminStats['total_orders'] = $orderModel->countAll();
    $adminStats['total_customers'] = $userModel->countCustomers();
    $adminStats['total_products'] = $productModel->countAll();
    $adminStats['pending_orders'] = $orderModel->countByStatus('pending');
    $adminStats['completed_orders'] = $orderModel->countByStatus('completed');
    $adminStats['total_sales'] = $orderModel->totalSales();
    $adminStats['feedback_total'] = $feedbackModel ? $feedbackModel->countAll() : 0;
    $adminStats['orders'] = $adminStats['total_orders'];
    $adminStats['customers'] = $adminStats['total_customers'];
    $adminStats['revenue'] = $adminStats['total_sales'];

    $adminRecentOrders = $orderModel->recentOrders(5);
    $adminRecentCustomers = $userModel->recentCustomers(5);
    $adminPopularProducts = $orderModel->popularProducts(5);

    $revenue = (float) $adminStats['total_sales'];
    $lowStock = 0;
    foreach ($adminProducts as $adminProduct) {
        if ((int) ($adminProduct['stock'] ?? 0) <= 5) {
            $lowStock++;
        }
    }

    $adminStats['revenue'] = $revenue;
    $adminStats['low_stock_items'] = $lowStock;

    $customerId = (int) ($_GET['customer_id'] ?? 0);
    if ($customerId > 0) {
        $selectedAdminCustomer = $userModel->findById($customerId);
        if ($selectedAdminCustomer && (string) ($selectedAdminCustomer['role'] ?? '') === 'customer') {
            $selectedAdminCustomerOrders = $orderModel->listByUser($customerId);
        } else {
            $selectedAdminCustomer = null;
        }
    }

    $orderId = (int) ($_GET['order_id'] ?? 0);
    if ($orderId > 0) {
        $selectedAdminOrder = $orderModel->findById($orderId);
    }
}

$viewData = [
    'products' => $products,
    'totalProducts' => $totalProducts,
    'pageNumber' => max(1, (int) ($_GET['p'] ?? 1)),
    'perPage' => 12,
    'selectedProduct' => $selectedProduct,
    'productReviews' => $productReviews,
    'cartItems' => $cartItems,
    'wishlistItems' => $wishlistItems,
    'categories' => $categories,
    'userOrders' => $userOrders,
    'userAddresses' => $userAddresses,
    'adminProducts' => $adminProducts,
    'adminCategories' => $adminCategories,
    'adminOrders' => $adminOrders,
    'adminCustomers' => $adminCustomers,
    'adminRecentOrders' => $adminRecentOrders,
    'adminRecentCustomers' => $adminRecentCustomers,
    'adminPopularProducts' => $adminPopularProducts,
    'selectedAdminCustomer' => $selectedAdminCustomer,
    'selectedAdminCustomerOrders' => $selectedAdminCustomerOrders,
    'selectedAdminOrder' => $selectedAdminOrder,
    'adminSection' => $adminSection,
    'adminSearch' => $adminSearch,
    'feedbackEntries' => $feedbackEntries,
    'adminStats' => $adminStats,
    'totals' => $totals,
    'cartCount' => $cartCount,
    'wishlistCount' => $wishlistCount,
    'currentOrder' => $currentOrder,
    'authUser' => auth_user(),
];

$routes = [
    'home' => ['view' => 'home', 'title' => 'Home', 'active' => 'home'],
    'shop' => ['view' => 'categories', 'title' => 'Shop', 'active' => 'shop'],
    'categories' => ['view' => 'categories', 'title' => 'Categories', 'active' => 'categories'],
    'product' => ['view' => 'product', 'title' => 'Product Details', 'active' => 'categories'],
    'cart' => ['view' => 'cart', 'title' => 'Shopping Cart', 'active' => 'categories'],
    'checkout' => ['view' => 'checkout', 'title' => 'Checkout', 'active' => 'categories'],
    'payment' => ['view' => 'payment', 'title' => 'Payment', 'active' => 'categories'],
    'order-success' => ['view' => 'order-success', 'title' => 'Order Confirmed', 'active' => 'categories'],
    'tracking' => ['view' => 'tracking', 'title' => 'Track Order', 'active' => 'categories'],
    'login' => ['view' => 'login', 'title' => 'Login', 'active' => 'login'],
    'admin-login' => ['view' => 'login', 'title' => 'Admin Login', 'active' => 'login'],
    'register' => ['view' => 'register', 'title' => 'Register', 'active' => 'login'],
    'dashboard' => ['view' => 'dashboard', 'title' => 'Dashboard', 'active' => 'dashboard'],
    'orders' => ['view' => 'orders', 'title' => 'My Orders', 'active' => 'dashboard'],
    'wishlist' => ['view' => 'wishlist', 'title' => 'Wishlist', 'active' => 'dashboard'],
    'addresses' => ['view' => 'addresses', 'title' => 'Addresses', 'active' => 'dashboard'],
    'profile' => ['view' => 'profile', 'title' => 'Profile', 'active' => 'dashboard'],
    'admin' => ['view' => 'admin', 'title' => 'Admin Dashboard', 'active' => 'dashboard'],
    'about' => ['view' => 'about', 'title' => 'About Us', 'active' => 'about'],
    'contact' => ['view' => 'contact', 'title' => 'Contact Us', 'active' => 'contact'],
];

if ($page === 'dashboard' || $page === 'orders' || $page === 'wishlist' || $page === 'addresses' || $page === 'profile' || $page === 'checkout' || $page === 'payment' || $page === 'cart') {
    if (!is_authenticated()) {
        flash_message('warning', 'Please login to continue.');
        redirect('index.php?page=login');
    }
}

if ($page === 'admin') {
    if (!is_admin()) {
        flash_message('error', 'Admin role is required for this page.');
        redirect('index.php?page=admin-login');
    }
}

$route = $routes[$page] ?? $routes['home'];
$viewData['pageTitle'] = $route['title'];
$viewData['activePage'] = $route['active'];

render_page($route['view'], $viewData);
