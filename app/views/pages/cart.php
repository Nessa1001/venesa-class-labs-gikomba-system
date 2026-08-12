<?php
$cartItems = $cartItems ?? [];
$totals = $totals ?? ['subtotal' => 0, 'shipping' => 0, 'vat' => 0, 'total' => 0];
?>
<section class="d-flex justify-content-between flex-wrap gap-2 align-items-center mb-3">
    <h1 class="mb-0">Shopping Cart</h1>
    <a href="index.php?page=categories" class="btn btn-outline-dark">Continue Shopping</a>
</section>

<?php if (!$cartItems): ?>
    <div class="surface-card p-4 text-center">
        <p class="mb-3">Your cart is empty.</p>
        <a href="index.php?page=categories" class="btn btn-accent">Browse Products</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <?php foreach ($cartItems as $item): ?>
                <?php $linePrice = (float) (($item['discount_price'] ?? null) ?: $item['price']); ?>
                <div class="surface-card p-3 mb-3 d-flex gap-3 align-items-center cart-item-row" data-unit-price="<?= $linePrice ?>">
                    <img src="<?= e($item['image_primary']) ?>" alt="<?= e($item['name']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:10px;">
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= e($item['name']) ?></h5>
                        <p class="mb-1 text-muted">Unit Price: <?= format_money($linePrice) ?></p>
                        <p class="mb-1 text-muted">Subtotal: <strong class="item-subtotal"><?= format_money($linePrice * (int) $item['quantity']) ?></strong></p>
                        <p class="mb-0"><strong>Stock:</strong> <?= (int) $item['stock'] ?> left</p>
                    </div>
                    <form method="post" action="index.php?page=cart&action=update" class="d-flex gap-2 align-items-center cart-update-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-outline-dark btn-sm qty-btn" type="button" data-action="minus">-</button>
                        <input type="number" min="1" name="quantity" value="<?= (int) $item['quantity'] ?>" class="form-control cart-qty-input" style="width:90px;">
                        <button class="btn btn-outline-dark btn-sm qty-btn" type="button" data-action="plus">+</button>
                        <button class="btn btn-outline-dark btn-sm" type="submit">Update</button>
                    </form>
                    <form method="post" action="index.php?page=cart&action=remove" class="remove-item-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-4">
            <div class="surface-card p-4 checkout-summary">
                <h4>Order Summary</h4>
                <div class="line"><span>Subtotal</span><strong id="cartSubtotal" data-value="<?= (float) $totals['subtotal'] ?>"><?= format_money($totals['subtotal']) ?></strong></div>
                <div class="line"><span>Shipping</span><strong id="cartShipping" data-value="<?= (float) $totals['shipping'] ?>"><?= format_money($totals['shipping']) ?></strong></div>
                <div class="line"><span>VAT</span><strong id="cartVat" data-rate="0.16" data-value="<?= (float) $totals['vat'] ?>"><?= format_money($totals['vat']) ?></strong></div>
                <hr>
                <div class="line"><span>Total</span><strong id="cartTotal" data-value="<?= (float) $totals['total'] ?>"><?= format_money($totals['total']) ?></strong></div>
                <p class="text-muted mt-3 mb-3">Estimated delivery: <?= estimated_delivery_date(3) ?></p>
                <a href="index.php?page=checkout" class="btn btn-accent w-100">Proceed to Checkout</a>
            </div>
        </div>
    </div>
<?php endif; ?>
