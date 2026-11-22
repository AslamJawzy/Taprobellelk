<?php
$pageTitle = 'Natural Ceylon Sapphires & Custom Jewellery';
require __DIR__ . '/includes/header.php';
$blue    = products_where("kind='gem' AND category='Blue Sapphire'");
$others  = products_where("kind='gem' AND category<>'Blue Sapphire'");
$gold    = products_where("kind='jewellery' AND category='Gold Jewellery'");
$silver  = products_where("kind='jewellery' AND category='Silver Jewellery'");
$nGems   = count($blue) + count($others);
$nJewels = count($gold) + count($silver);
$nTypes  = (int)db()->query("SELECT COUNT(DISTINCT category) FROM products WHERE kind='gem' AND is_active=1")->fetchColumn();
?>
<section class="hero">
    <div class="sparkles"></div>
    <div class="container">
        <div class="row">
            <div class="col-2 hero-text">
                <span class="eyebrow">Ceylon Sapphires &middot; Sri Lanka</span>
                <h1>Globally trusted for <span class="grad">Ceylon Sapphires</span> loved around the world.</h1>
                <p>We specialise in sourcing only the finest natural sapphires in Sri Lanka: blue, pink, yellow, padparadscha and other rare stones with true beauty, rarity and lasting value.</p>
                <div class="hero-cta">
                    <a href="<?= url('gemstones.php') ?>" class="btn btn-gold">Explore gemstones &#8594;</a>
                    <a href="<?= url('custom_made.php') ?>" class="btn btn-ghost">Design your jewellery</a>
                </div>
                <div class="hero-stats">
                    <div><b data-count="<?= $nGems ?>" data-suffix="+">0</b><span>Natural gemstones</span></div>
                    <div><b data-count="<?= $nTypes ?>">0</b><span>Gem varieties</span></div>
                    <div><b data-count="<?= $nJewels ?>" data-suffix="+">0</b><span>Finished jewels</span></div>
                </div>
            </div>
            <div class="col-2 hero-visual">
                <div class="orb"></div>
                <span class="gem g1"></span><span class="gem g2"></span><span class="gem g3"></span>
                <img class="tilt" src="<?= img('ruby gold.jpg') ?>" alt="Ruby and gold jewellery">
            </div>
        </div>
    </div>
</section>

<div class="marquee"><div>
    <?php for ($k = 0; $k < 2; $k++) foreach (['Blue Sapphire', 'Ruby', 'Padparadscha', 'Yellow Sapphire', 'Pink Sapphire', 'Green Sapphire', 'Purple Sapphire', 'Custom-made Jewellery'] as $w) echo '<span>' . e($w) . '</span>'; ?>
</div></div>

<div class="container">
    <div class="cat-grid">
        <a class="cat-tile" href="<?= url('gemstones.php') ?>"><img src="<?= img('category-01.jpg') ?>" alt="Hand-selected gemstones" loading="lazy"><div><small>Browse</small><h3>Hand-Selected Gems</h3></div></a>
        <a class="cat-tile" href="<?= url('custom_made.php') ?>"><img src="<?= img('category-02.jpg') ?>" alt="Expert craftsmanship" loading="lazy"><div><small>Made for you</small><h3>Expert Craftsmanship</h3></div></a>
        <a class="cat-tile" href="#jewellery"><img src="<?= img('category-03.jpg') ?>" alt="Finished jewellery" loading="lazy"><div><small>Ready to wear</small><h3>Finished Jewellery</h3></div></a>
    </div>

    <h2 class="title">Sapphires</h2>
    <p class="sub-title">Blue Sapphires</p>
    <div class="prod-row"><?php foreach ($blue as $p) echo product_card($p); ?></div>

    <h2 class="title">Latest Gemstones</h2>
    <p class="sub-title">Rubies, padparadscha &amp; fancy sapphires</p>
    <div class="prod-row"><?php foreach ($others as $p) echo product_card($p); ?></div>
</div>

<section class="offer">
    <div class="container">
        <div class="row">
            <div class="col-2 offer-art"><img class="offer-img" src="<?= img('Emareld Silver ring.png') ?>" alt="Emerald silver ring" loading="lazy"></div>
            <div class="col-2">
                <p style="color:var(--gold)">Exclusively available on Taprobellelk</p>
                <h2>Emerald Silver Ring</h2>
                <small>Make every moment memorable. Our Emerald Silver Ring brings together the beauty of a rich green emerald and the shine of premium silver. A perfect gift for her, or a treat for yourself.</small>
                <hr>
                <a href="<?= url('custom_made.php') ?>" class="btn btn-gold">Design yours &#8594;</a>
            </div>
        </div>
    </div>
</section>

<div class="container" id="jewellery">
    <h2 class="title">Jewellery</h2>
    <p class="sub-title">Ready-made pieces</p>
    <h3 class="group-title">Gold jewellery</h3>
    <div class="prod-row"><?php foreach ($gold as $p) echo product_card($p); ?></div>
    <h3 class="group-title">Silver jewellery</h3>
    <div class="prod-row"><?php foreach ($silver as $p) echo product_card($p); ?></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
