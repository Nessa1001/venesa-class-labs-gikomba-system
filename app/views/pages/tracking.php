<?php
$stages = [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'processing' => 'Processing',
    'ready_for_delivery' => 'Ready for Delivery',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];
$currentStage = $currentOrder['status'] ?? 'pending';
$keys = array_keys($stages);
$currentIndex = array_search($currentStage, $keys, true);
if ($currentIndex === false) {
    $currentIndex = 0;
}
$progress = (int) ((($currentIndex + 1) / count($keys)) * 100);
?>
<section class="mb-3">
    <h1>Order Tracking</h1>
    <form class="row g-2" method="get" action="index.php">
        <input type="hidden" name="page" value="tracking">
        <div class="col-md-8"><input class="form-control" name="order_number" placeholder="Enter order number e.g. ORD-2026-000135" value="<?= e($_GET['order_number'] ?? '') ?>"></div>
        <div class="col-md-4"><button class="btn btn-outline-dark w-100" type="submit">Track</button></div>
    </form>
</section>

<?php if (!$currentOrder): ?>
    <div class="surface-card p-4">Enter an order number to track your package.</div>
<?php else: ?>
    <div class="surface-card p-4">
        <p class="mb-1"><strong>Order:</strong> <?= e($currentOrder['order_number']) ?></p>
        <p class="mb-3"><strong>Estimated Delivery:</strong> <?= date('d M Y', strtotime($currentOrder['estimated_delivery_date'])) ?></p>

        <div class="progress mb-3" role="progressbar" aria-label="Order Progress" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-success" style="width: <?= $progress ?>%;"><?= $progress ?>%</div>
        </div>

        <?php $i = 0; foreach ($stages as $key => $label): ?>
            <div class="tracking-stage <?= $i <= $currentIndex ? 'active' : '' ?>"><?= e($label) ?></div>
        <?php $i++; endforeach; ?>
    </div>
<?php endif; ?>
