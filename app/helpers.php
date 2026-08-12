<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/config/config.php';
    }

    return $config[$key] ?? $default;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token) && isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function flash_message(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

function get_flash_messages(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $messages;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function format_money(float $amount): string
{
    return config('currency', 'KES') . ' ' . number_format($amount, 2);
}

function estimated_delivery_date(int $daysFromNow = 3): string
{
    return date('d M Y', strtotime('+' . $daysFromNow . ' days'));
}

function auth_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

function auth_user_id(): int
{
    $user = auth_user();

    return (int) ($user['id'] ?? 0);
}

function is_authenticated(): bool
{
    return auth_user_id() > 0;
}

function is_admin(): bool
{
    $user = auth_user();

    return (string) ($user['role'] ?? '') === 'admin';
}

function require_auth(): void
{
    if (!is_authenticated()) {
        flash_message('warning', 'Please login to continue.');
        redirect('index.php?page=login');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        flash_message('error', 'Admin access required.');
        redirect('index.php?page=home');
    }
}
