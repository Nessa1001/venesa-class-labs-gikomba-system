<?php if (!$currentOrder): ?>
    <div class="alert alert-warning">Order not found. <a href="index.php?page=home">Return home</a>.</div>
<?php else: ?>
    <div class="surface-card p-4 text-center">
        <h1 class="mb-3">Order Placed Successfully</h1>
        <p class="mb-1">Order Number: <strong><?= e($currentOrder['order_number']) ?></strong></p>
        <p class="mb-1">Payment Method: <strong><?= strtoupper(e($currentOrder['payment_method'])) ?></strong></p>
        <p class="mb-1">Estimated Delivery: <strong><?= date('d M Y', strtotime($currentOrder['estimated_delivery_date'])) ?></strong></p>
        <p class="text-muted mb-4">A confirmation email and SMS have been queued.</p>
        <a href="index.php?page=tracking&order_number=<?= urlencode($currentOrder['order_number']) ?>" class="btn btn-accent">Track This Order</a>
    </div>
<?php endif; ?>
