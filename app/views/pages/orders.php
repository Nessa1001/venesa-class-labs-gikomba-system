<?php $userOrders = $userOrders ?? []; ?>
<section class="mb-3">
    <h1>My Orders</h1>
    <p class="text-muted">Recent purchases and payment status.</p>
</section>

<div class="surface-card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($userOrders): ?>
                    <?php foreach ($userOrders as $order): ?>
                        <tr>
                            <td><?= e($order['order_number']) ?></td>
                            <td><?= date('d M Y', strtotime((string) $order['created_at'])) ?></td>
                            <td><span class="badge text-bg-success"><?= e(str_replace('_', ' ', (string) $order['status'])) ?></span></td>
                            <td><?= format_money((float) $order['total_amount']) ?></td>
                            <td><a href="index.php?page=tracking&order_number=<?= urlencode((string) $order['order_number']) ?>" class="btn btn-sm btn-outline-dark">Track</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No orders yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
