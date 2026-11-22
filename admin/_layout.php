<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/orders.php';
$admin = require_admin();
define('IMAGES_DIR', dirname(__DIR__) . '/Images');

function admin_head(string $title, string $active): void {
    global $admin;
    $nav = ['index' => ['Dashboard', 'index.php', 'fa-tachometer'], 'orders' => ['Orders', 'orders.php', 'fa-shopping-bag'],
            'products' => ['Products', 'products.php', 'fa-diamond'], 'customers' => ['Customers', 'customers.php', 'fa-users'],
            'messages' => ['Messages', 'messages.php', 'fa-envelope'], 'pages' => ['Info pages', 'pages.php', 'fa-file-text-o'],
            'rates' => ['Pricing rates', 'rates.php', 'fa-line-chart'], 'password' => ['Change password', 'password.php', 'fa-key']];
    try { $unread = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(); } catch (Throwable $e) { $unread = 0; }
    ?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> - Admin - <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= url('assets/admin.css') ?>">
</head><body>
<div class="layout">
<aside class="side" id="side">
    <div class="brand"><i></i> Taprobellelk</div>
    <a href="<?= url('admin/product_edit.php') ?>" class="addnew"><i class="fa fa-plus-circle" style="width:18px"></i> Add product</a>
    <?php foreach ($nav as $k => [$label, $href, $icon]): ?>
        <a href="<?= url('admin/' . $href) ?>" class="<?= $k === $active ? 'on' : '' ?>"><i class="fa <?= $icon ?>" style="width:18px"></i> <?= e($label) ?><?php if ($k === 'messages' && $unread): ?> <span class="pill"><?= $unread ?></span><?php endif; ?></a>
    <?php endforeach; ?>
    <div class="sep"></div>
    <a href="<?= url('index.php') ?>" target="_blank"><i class="fa fa-external-link" style="width:18px"></i> View website</a>
    <a href="<?= url('logout.php') ?>"><i class="fa fa-sign-out" style="width:18px"></i> Logout</a>
</aside>
<div class="main">
    <div class="top">
        <div style="display:flex;gap:12px;align-items:center"><button class="burger" onclick="document.getElementById('side').classList.toggle('open')">&#9776;</button><h1><?= e($title) ?></h1></div>
        <div class="who">Signed in as <b><?= e($admin['username']) ?></b></div>
    </div>
    <div class="toasts"><?php foreach (flashes() as [$t, $m]): ?><div class="flash flash-<?= e($t) ?>" onclick="this.remove()"><?= e($m) ?></div><?php endforeach; ?></div>
<?php }

function admin_foot(): void { ?>
</div></div>
<script>setTimeout(function(){document.querySelectorAll('.toasts .flash').forEach(function(f){f.style.transition='.5s';f.style.opacity=0;setTimeout(function(){f.remove()},500)})},4500);</script>
</body></html>
<?php }

/** Same grouping logic used when the catalogue was built: "ring.jpg", "ring 1.jpg", "ring (2).jpg" are one set. */
function image_stem(string $f): string {
    $s = pathinfo($f, PATHINFO_FILENAME);
    $s = preg_replace('/\s*\(\d+\)$/', '', $s);
    $s = preg_replace('/\s+\d+$/', '', $s);
    return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
}
function image_files(): array {
    $out = [];
    foreach (@scandir(IMAGES_DIR) ?: [] as $f) if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $f)) $out[] = $f;
    natcasesort($out);
    return array_values($out);
}
function gallery_for(string $main): array {
    $st = image_stem($main); $g = [$main];
    foreach (image_files() as $f) if ($f !== $main && image_stem($f) === $st) $g[] = $f;
    return $g;
}
function pager(int $total, int $per, int $page, string $base): string {
    $pages = (int)ceil($total / $per); if ($pages < 2) return '';
    $h = '<div class="pager">';
    for ($i = 1; $i <= $pages; $i++) $h .= $i === $page ? "<b>$i</b>" : '<a href="' . e($base . (str_contains($base, '?') ? '&' : '?') . 'page=' . $i) . '">' . $i . '</a>';
    return $h . '</div>';
}
