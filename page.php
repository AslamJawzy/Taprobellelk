<?php
require_once __DIR__ . '/includes/bootstrap.php';
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'] ?? ''));
$pg = null;
try {
    $st = db()->prepare('SELECT * FROM pages WHERE slug = ? AND is_published = 1');
    $st->execute([$slug]);
    $pg = $st->fetch() ?: null;
} catch (Throwable $e) { /* pages table missing */ }
if (!$pg) { require __DIR__ . '/404.php'; exit; }
$pageTitle = $pg['title'];
require __DIR__ . '/includes/header.php';
echo page_banner($pg['title']);
?>
<div class="container page-body"><article class="prose"><?= render_content($pg['content']) ?></article></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
