<?php
// Usage: $pageTitle = '...'; require 'includes/header.php';   (pages draw their own hero / banner)
require_once __DIR__ . '/bootstrap.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
    <meta name="description" content="Taprobellelk - natural Ceylon sapphires, rubies and custom-made jewellery from Sri Lanka.">
    <link rel="icon" type="image/png" href="<?= url('favicon.png') ?>">
    <script>document.documentElement.classList.add('js')</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= url('assets/site.css') ?>">
</head>
<body>
<div class="preloader"><img src="<?= img('logo-mark.png') ?>" alt=""></div>
<div class="scroll-progress"></div>

<header class="site-header">
    <div class="topbar"><span>&#9670; Natural Ceylon sapphires</span><span>&#9670; Custom-made jewellery</span><span>&#9670; Secure checkout</span></div>
    <div class="site-nav" id="siteNav">
        <div class="container nav-inner">
            <a class="brand" href="<?= url('index.php') ?>" aria-label="<?= e(SITE_NAME) ?> home">
                <img class="brand-mark" src="<?= img('logo-mark.png') ?>" alt="">
                <span class="brand-text">TAPROBELLE<small>Ceylon Gems &amp; Jewellery</small></span>
            </a>
            <nav id="mainNav">
                <ul id="MenuItems">
                    <li><a href="<?= url('index.php') ?>">Home</a></li>
                    <li><a href="<?= url('gemstones.php') ?>">Gemstones</a></li>
                    <li><a href="<?= url('custom_made.php') ?>">Custom-made</a></li>
                    <li><a href="<?= url('about.php') ?>">About</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact</a></li>
                </ul>
            </nav>
            <div class="nav-actions">
                <?php if ($user): ?>
                <div class="user-menu" id="userMenu">
                    <button class="user-btn" type="button" aria-haspopup="true"><span class="avatar"><?= e(mb_substr($user['username'], 0, 1)) ?></span><?= e($user['username']) ?> <i class="fa fa-angle-down"></i></button>
                    <div class="user-dd">
                        <?php if ($user['is_admin']): ?><a href="<?= url('admin/index.php') ?>"><i class="fa fa-tachometer"></i> Admin dashboard</a><?php endif; ?>
                        <a href="<?= url('orders.php') ?>"><i class="fa fa-shopping-bag"></i> My orders</a>
                        <a href="<?= url('cart.php') ?>"><i class="fa fa-shopping-cart"></i> My cart</a>
                        <hr>
                        <a href="<?= url('logout.php') ?>"><i class="fa fa-sign-out"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <a class="login-link" href="<?= url('account.php') ?>"><i class="fa fa-user-o"></i> <span>Login / Register</span></a>
                <?php endif; ?>
                <a href="<?= url('cart.php') ?>" class="icon-btn cart-link" title="Cart" aria-label="Cart">
                    <i class="fa fa-shopping-bag"></i>
                    <?php if (cart_count() > 0): ?><span class="cart-badge"><?= cart_count() ?></span><?php endif; ?>
                </a>
                <button class="icon-btn menu-toggle" id="menuToggle" aria-label="Menu" type="button"><i class="fa fa-bars"></i></button>
            </div>
        </div>
    </div>
</header>

<div class="toasts">
<?php foreach (flashes() as [$t, $m]): ?>
    <div class="flash flash-<?= e($t) ?>"><?= e($m) ?></div>
<?php endforeach; ?>
</div>
<main>
