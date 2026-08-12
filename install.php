<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

use App\services\DatabaseSetupService;

$dbConfig = require __DIR__ . '/app/config/database.php';
$message = null;
$error = null;

if (is_post()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid CSRF token.';
    } else {
        $host = trim((string) ($_POST['host'] ?? $dbConfig['host']));
        $port = (int) ($_POST['port'] ?? $dbConfig['port']);
        $username = trim((string) ($_POST['username'] ?? $dbConfig['username']));
        $password = (string) ($_POST['password'] ?? $dbConfig['password']);

        try {
            $service = new DatabaseSetupService();
            $result = $service->runSchema($host, $port, $username, $password, __DIR__ . '/database/schema.sql');
            $message = 'Database installation complete. Executed statements: ' . (int) $result['executed_statements'];
        } catch (Throwable $throwable) {
            $error = 'Installation failed: ' . $throwable->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Gikomba Store Database Installer</h1>
                    <p class="text-muted">This will create the database, tables, and seed data from database/schema.sql.</p>
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= e($message) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">DB Host</label>
                            <input class="form-control" name="host" value="<?= e((string) $dbConfig['host']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DB Port</label>
                            <input class="form-control" name="port" value="<?= e((string) $dbConfig['port']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DB Username</label>
                            <input class="form-control" name="username" value="<?= e((string) $dbConfig['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DB Password</label>
                            <input type="password" class="form-control" name="password" value="<?= e((string) $dbConfig['password']) ?>">
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Install Database</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
