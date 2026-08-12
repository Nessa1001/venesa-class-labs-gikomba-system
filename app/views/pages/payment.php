<?php $checkout = $_SESSION['checkout'] ?? []; ?>
<section class="mb-3">
    <h1>Payment</h1>
    <p class="text-muted">Choose a demo payment method and place your order.</p>
</section>

<?php if (!$checkout): ?>
    <div class="alert alert-warning">Checkout details missing. <a href="index.php?page=checkout">Go back to checkout</a>.</div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <form method="post" action="index.php?page=payment&action=confirm" class="surface-card p-4" id="paymentForm" data-validate="payment">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>
                </div>

                <div id="mpesaPanel" class="surface-card p-3 mb-3">
                    <h5>M-Pesa Checkout</h5>
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" name="mpesa_phone" placeholder="07XXXXXXXX or +2547XXXXXXXX">
                    <p class="text-muted mb-0 mt-2">Waiting for customer confirmation...</p>
                </div>

                <div id="cardPanel" class="surface-card p-3 mb-3 d-none">
                    <h5>Cash on Delivery</h5>
                    <p class="mb-0 text-muted">Pay when your order is delivered to your selected location.</p>
                </div>

                <button class="btn btn-accent" type="submit">Confirm Payment</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="surface-card p-4">
                <h5>Delivery</h5>
                <p class="mb-1"><strong>Estimated Delivery:</strong></p>
                <p><?= estimated_delivery_date(3) ?></p>
                <p class="mb-0 text-success"><strong>Payment Successful</strong> after confirmation.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
