<?php
$pageTitle = 'Custom-made Jewellery';
require __DIR__ . '/includes/header.php';
$gems = products_where("kind='gem' AND stock > 0");
echo page_banner('Custom-made Jewellery', [], 'Pick a gemstone, choose the setting, and see your price instantly.');
?>
<div class="container page-body">
    <div class="steps" style="max-width:520px"><div class="now">Pick a gem</div><div>Customise</div><div>Add to cart</div></div>
    <br>
    <div class="prod-row">
        <?php foreach ($gems as $p): $l = url('product.php?id=' . (int)$p['id'] . '&custom=1'); ?>
        <div><div class="p-card">
            <a class="p-img" href="<?= e($l) ?>"><span class="p-tag"><?= $p['carat'] ? e(rtrim(rtrim(number_format((float)$p['carat'], 2), '0'), '.')) . ' ct' : 'Natural' ?></span>
                <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><span class="p-view">Customise &#8594;</span></a>
            <div class="p-body"><span class="p-cat"><?= e($p['category']) ?></span><h4><a href="<?= e($l) ?>"><?= e($p['name']) ?></a></h4><p class="p-price">from <?= money($p['price']) ?></p></div>
        </div></div>
        <?php endforeach; ?>
    </div>
    <?php if (!$gems) echo '<p>No gemstones are available right now. Please check back soon.</p>'; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
