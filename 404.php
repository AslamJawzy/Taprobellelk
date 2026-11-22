<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(404);
$pageTitle = 'Page not found';
require __DIR__ . '/includes/header.php';
?>
<div class="container notfound">
    <h1>404</h1>
    <h2 style="margin:10px 0">This page could not be found</h2>
    <p>The page may have moved or the link may be wrong.</p>
    <a href="<?= url('index.php') ?>" class="btn">Back to home</a>
    <a href="<?= url('gemstones.php') ?>" class="btn btn-outline">Browse gemstones</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
