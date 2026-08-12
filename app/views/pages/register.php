<div class="auth-box surface-card p-4">
    <h1 class="mb-3">Create Account</h1>
    <form method="post" action="index.php?page=register" id="registerForm" data-validate="register">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?= e(old('first_name', '')) ?>" data-label="First Name" required></div>
            <div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?= e(old('last_name', '')) ?>" data-label="Last Name" required></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e(old('phone', '')) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e(old('email', '')) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
            <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password" required></div>
        </div>
        <button class="btn btn-accent w-100 mt-3" type="submit">Register</button>
    </form>
    <p class="mt-3 mb-0">Already registered? <a href="index.php?page=login">Login</a></p>
</div>
<?php clear_old(); ?>
