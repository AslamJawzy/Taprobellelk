<?php
$pageTitle = 'Gemstones';
require __DIR__ . '/includes/header.php';

$cats = db()->query("SELECT DISTINCT category FROM products WHERE is_active=1 AND kind='gem' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$cat  = $_GET['cat'] ?? '';
$sort = $_GET['sort'] ?? '';
$orders = ['price_asc' => 'price ASC', 'price_desc' => 'price DESC', 'carat' => 'carat DESC', '' => 'id'];
$order  = $orders[$sort] ?? 'id';
if ($cat !== '' && in_array($cat, $cats, true)) $gems = products_where("kind='gem' AND category = ?", [$cat], $order);
else { $cat = ''; $gems = products_where("kind='gem'", [], $order); }

echo page_banner('Gemstones', [], 'Every stone is unique. Filter by variety, sort by price or carat, then make it yours.');
?>
<div class="container page-body">
    <div class="filter-bar">
        <div class="chips">
            <a href="<?= url('gemstones.php' . ($sort ? '?sort=' . urlencode($sort) : '')) ?>" class="<?= $cat === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($cats as $c): ?>
            <a href="<?= url('gemstones.php?cat=' . urlencode($c) . ($sort ? '&sort=' . urlencode($sort) : '')) ?>" class="<?= $cat === $c ? 'active' : '' ?>"><?= e($c) ?></a>
            <?php endforeach; ?>
        </div>
        <form method="get" id="sortForm">
            <?php if ($cat !== ''): ?><input type="hidden" name="cat" value="<?= e($cat) ?>"><?php endif; ?>
            <select name="sort" onchange="this.form.submit()" aria-label="Sort">
                <option value="">Default sorting</option>
                <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: low to high</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                <option value="carat"      <?= $sort === 'carat'      ? 'selected' : '' ?>>Carat weight</option>
            </select>
        </form>
    </div>
    <div class="prod-row"><?php foreach ($gems as $p) echo product_card($p); ?></div>
    <?php if (!$gems) echo '<p>No gemstones found.</p>'; ?>

    <h2 class="title" id="jewellery">Jewellery</h2>
    <p class="sub-title">Ready-made pieces</p>
    <div class="prod-row"><?php foreach (products_where("kind='jewellery'") as $p) echo product_card($p); ?></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
