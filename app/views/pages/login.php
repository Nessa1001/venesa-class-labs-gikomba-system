<?php
$isAdminLogin = ($pageTitle ?? '') === 'Admin Login';
?>
<div class="auth-box surface-card p-4">
    <h1 class="mb-3"><?= $isAdminLogin ? 'Admin Login' : 'Login' ?></h1>
    <p class="text-muted"><?= $isAdminLogin ? 'Access admin dashboard with administrator credentials.' : 'Sign in to continue shopping.' ?></p>
    <form method="post" action="index.php?page=<?= $isAdminLogin ? 'admin-login' : 'login' ?>" id="loginForm" data-validate="<?= $isAdminLogin ? 'admin-login' : 'login' ?>">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label"><?= $isAdminLogin ? 'Email or Username' : 'Email' ?></label><input type="text" class="form-control" name="email" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
        <button class="btn btn-accent w-100" type="submit"><?= $isAdminLogin ? 'Admin Sign In' : 'Sign In' ?></button>
    </form>
    <?php if (!$isAdminLogin): ?>
        <p class="mt-3 mb-0">No account? <a href="index.php?page=register">Create one</a></p>
    <?php endif; ?>
</div>
