<section class="mb-3">
    <h1>Profile</h1>
    <p class="text-muted">Update account details and password.</p>
</section>

<div class="surface-card p-4">
    <form class="row g-3" method="post" action="#">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" value="<?= e($authUser['name'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= e($authUser['email'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Current Password</label><input type="password" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">New Password</label><input type="password" class="form-control"></div>
        <div class="col-12"><button class="btn btn-accent" type="button">Save Changes</button></div>
    </form>
</div>
