<?php
$flashMessages = get_flash_messages();
$activePage = $activePage ?? 'home';
$pageTitle = $pageTitle ?? config('app_name', 'Gikomba');
$cartCount = $cartCount ?? 0;
$wishlistCount = $wishlistCount ?? 0;
$authUser = $authUser ?? auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(config('app_name', 'Gikomba')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/app.css">
</head>
<body>
<header class="main-header sticky-top">
    <nav class="navbar navbar-expand-lg app-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand brand-mark" href="index.php?page=home">Gikomba</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-2">
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php?page=home">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'shop' ? 'active' : '' ?>" href="index.php?page=shop">Shop</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'categories' ? 'active' : '' ?>" href="index.php?page=categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>" href="index.php?page=about">About</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="index.php?page=contact">Contact</a></li>
                </ul>
                <form class="d-flex nav-search" role="search" autocomplete="off">
                    <input class="form-control" id="liveSearchInput" type="search" placeholder="Search products..." aria-label="Search">
                    <div id="liveSearchResults" class="live-search-results d-none"></div>
                </form>
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 nav-icons">
                    <a class="icon-link" href="index.php?page=wishlist" aria-label="Wishlist">
                        <i class="bi bi-heart"></i>
                        <span class="icon-badge" id="wishlistCount"><?= (int) $wishlistCount ?></span>
                    </a>
                    <a class="icon-link" href="index.php?page=cart" aria-label="Cart">
                        <i class="bi bi-cart3"></i>
                        <span class="icon-badge" id="cartCount"><?= (int) $cartCount ?></span>
                    </a>
                    <div class="dropdown">
                        <button class="btn profile-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> Account
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($authUser): ?>
                                <li><a class="dropdown-item" href="index.php?page=dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="index.php?page=orders">My Orders</a></li>
                                <li><a class="dropdown-item" href="index.php?page=tracking">Track Orders</a></li>
                                <li><a class="dropdown-item" href="index.php?page=addresses">Addresses</a></li>
                                <li><a class="dropdown-item" href="index.php?page=profile">Profile</a></li>
                                <?php if (($authUser['role'] ?? '') === 'admin'): ?>
                                    <li><a class="dropdown-item" href="index.php?page=admin">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?page=logout">Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="index.php?page=login">Login</a></li>
                                <li><a class="dropdown-item" href="index.php?page=admin-login">Admin Login</a></li>
                                <li><a class="dropdown-item" href="index.php?page=register">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
<main class="page-shell">
    <div class="container py-4">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= e($type === 'error' ? 'danger' : $type) ?> alert-dismissible fade show" role="alert">
                    <?= e($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
