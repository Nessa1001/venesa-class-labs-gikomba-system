<?php $userAddresses = $userAddresses ?? []; ?>
<section class="mb-3">
    <h1>Addresses</h1>
    <p class="text-muted">Manage your delivery addresses.</p>
</section>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="surface-card p-4">
            <h5 class="mb-3">Add New Address</h5>
            <form method="post" action="index.php?page=addresses&action=add" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-12"><label class="form-label">County</label><input class="form-control" name="county" required></div>
                <div class="col-12"><label class="form-label">Town</label><input class="form-control" name="town" required></div>
                <div class="col-12"><label class="form-label">Street</label><input class="form-control" name="street" required></div>
                <div class="col-12"><label class="form-label">House Number</label><input class="form-control" name="house_number" required></div>
                <div class="col-12 form-check ms-1">
                    <input class="form-check-input" type="checkbox" value="1" name="is_default" id="isDefaultAddress">
                    <label class="form-check-label" for="isDefaultAddress">Set as default</label>
                </div>
                <div class="col-12"><button class="btn btn-accent w-100" type="submit">Save Address</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="surface-card p-4">
            <h5 class="mb-3">Saved Addresses</h5>
            <?php if (!$userAddresses): ?>
                <p class="text-muted mb-0">No saved addresses yet.</p>
            <?php else: ?>
                <?php foreach ($userAddresses as $address): ?>
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <p class="mb-1"><strong><?= e((string) $address['county']) ?></strong>, <?= e((string) $address['town']) ?></p>
                                <p class="mb-1"><?= e((string) $address['street']) ?></p>
                                <p class="mb-0">House No: <?= e((string) $address['house_number']) ?></p>
                            </div>
                            <?php if ((int) $address['is_default'] === 1): ?>
                                <span class="badge text-bg-success">Default</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <?php if ((int) $address['is_default'] !== 1): ?>
                                <form method="post" action="index.php?page=addresses&action=default">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="address_id" value="<?= (int) $address['id'] ?>">
                                    <button class="btn btn-outline-dark btn-sm" type="submit">Set Default</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="index.php?page=addresses&action=delete">
                                <?= csrf_field() ?>
                                <input type="hidden" name="address_id" value="<?= (int) $address['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
