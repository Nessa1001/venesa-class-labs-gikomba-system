<?php
$checkout = $_SESSION['checkout'] ?? [];
$userEmail = $authUser['email'] ?? '';
$userName = $authUser['name'] ?? '';
$totals = $totals ?? ['subtotal' => 0, 'shipping' => 0, 'vat' => 0, 'total' => 0];
?>
<section class="mb-3">
    <h1>Checkout</h1>
    <p class="text-muted">Enter your shipping details and continue to payment.</p>
</section>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="post" action="index.php?page=checkout" class="surface-card p-4" id="checkoutForm" data-validate="checkout">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="customer_name" value="<?= e($checkout['customer_name'] ?? $userName) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Phone Number</label><input class="form-control" name="phone" value="<?= e($checkout['phone'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($checkout['email'] ?? $userEmail) ?>" required></div>
                <div class="col-md-6"><label class="form-label">County</label><input class="form-control" name="county" value="<?= e($checkout['county'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Town</label><input class="form-control" name="town" value="<?= e($checkout['town'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Street</label><input class="form-control" name="street" value="<?= e($checkout['street'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Delivery Location</label><input class="form-control" name="house_number" value="<?= e($checkout['house_number'] ?? '') ?>" required></div>
            </div>
            <button class="btn btn-accent mt-3" type="submit">Continue to Payment</button>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="surface-card p-4 totals">
            <h5>Order Summary</h5>
            <div class="line"><span>Subtotal</span><strong><?= format_money($totals['subtotal']) ?></strong></div>
            <div class="line"><span>Shipping</span><strong><?= format_money($totals['shipping']) ?></strong></div>
            <div class="line"><span>VAT</span><strong><?= format_money($totals['vat']) ?></strong></div>
            <hr>
            <div class="line"><span>Total</span><strong><?= format_money($totals['total']) ?></strong></div>
        </div>
    </div>
</div>
