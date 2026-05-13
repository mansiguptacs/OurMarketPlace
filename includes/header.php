<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/product_image.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'OurMarketplace'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo baseUrl('/assets/css/style.css'); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo baseUrl('/index.php'); ?>">
            <i class="fas fa-store"></i> OurMarketplace
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/companies/index.php'); ?>">Companies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/products/index.php'); ?>">All Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/rankings/marketplace_top5.php'); ?>">Top Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/products/search.php'); ?>">Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/compare/index.php'); ?>">
                        Compare<?php if (compareCount() > 0): ?> <span class="badge bg-secondary"><?php echo compareCount(); ?></span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo baseUrl('/team.php'); ?>">Team</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo baseUrl('/tracking/history.php'); ?>">
                            <i class="fas fa-history"></i> My Visits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo baseUrl('/wishlist/index.php'); ?>">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars(getCurrentFullName()); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo baseUrl('/dashboard.php'); ?>">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('/compare/index.php'); ?>">Compare Products</a></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('/wishlist/index.php'); ?>">My Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('/auth/logout.php'); ?>">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo baseUrl('/auth/login.php'); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ms-2" href="<?php echo baseUrl('/auth/register.php'); ?>">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
