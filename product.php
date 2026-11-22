<?php
require_once __DIR__ . '/includes/bootstrap.php';
$p = get_product((int)($_GET['id'] ?? 0));
if (!$p) { require __DIR__ . '/404.php'; exit; }

$pageTitle = $p['name'];
$gallery = json_decode($p['gallery'] ?: '[]', true) ?: [$p['image']];
$isGem   = $p['kind'] === 'gem';
$types   = jewellery_types();
$metals  = metal_rates();
$custom  = $isGem && !empty($_GET['custom']);
$inStock = (int)$p['stock'] > 0;
$needsSize = !$isGem && $p['jewellery_type'] === 'ring';
require __DIR__ . '/includes/header.php';
echo page_banner($custom ? 'Design your jewellery' : ($isGem ? 'Gemstone' : 'Jewellery'), ['Gemstones' => url('gemstones.php'), $p['category'] => url('gemstones.php' . ($isGem ? '?cat=' . urlencode($p['category']) : ''))]);
?>
<div class="container single-product">
    <div class="row">
        <div class="col-2">
            <div class="zoom-box"><img src="<?= e(img($gallery[0])) ?>" width="100%" id="ProductImg" alt="<?= e($p['name']) ?>"></div>
            <div class="small-img-row">
                <?php foreach (array_slice($gallery, 0, 4) as $g): ?>
                <div class="small-img-col"><img src="<?= e(img($g)) ?>" width="100%" class="small-img"></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-2">
            <p class="p-cat"><?= e($p['category']) ?></p>
            <h1><?= e($p['name']) ?></h1>
            <div class="price-big"><?= $isGem ? 'Gemstone price: ' : '' ?><?= money($p['price']) ?></div>

            <form method="post" action="<?= url('cart_action.php') ?>" id="addForm" class="buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                <?php if ($isGem): ?>
                <select name="jtype" id="jtype">
                    <option value="">Loose gemstone (no jewellery)</option>
                    <?php foreach ($types as $code => $t): ?>
                    <option value="<?= e($code) ?>" <?= ($custom && $code === 'ring') ? 'selected' : '' ?>><?= e($t['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <?php if ($isGem || $needsSize): ?>
                <select name="size" id="size" style="<?= $isGem ? 'display:none' : '' ?>">
                    <option value="">Select Ring Size</option>
                    <?php for ($i = 1; $i <= 33; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                </select>
                <?php endif; ?>

                <?php if ($isGem): ?>
                <select name="metal" id="metal" style="display:none">
                    <option value="">Select Metal Type &amp; Purity</option>
                    <?php foreach ($metals as $code => $m): ?>
                    <option value="<?= e($code) ?>"><?= e($m['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <div class="estimator" id="estimator">
                    <h3><?= $isGem ? 'Instant Price Estimate' : 'Price' ?></h3>
                    <table id="estRows"></table>
                    <div class="note" id="estNote"><?= $isGem ? 'Estimate updates automatically. Metal rates are reviewed regularly.' : '' ?></div>
                </div>

                <?php if ($inStock): ?>
                    <?php if ($p['stock'] > 1): ?>
                    <input type="number" name="qty" class="qty-input" value="1" min="1" max="<?= min(10, (int)$p['stock']) ?>">
                    <?php else: ?><input type="hidden" name="qty" value="1"><p class="stock-low">Unique piece - only 1 available.</p><?php endif; ?>
                    <button type="submit" class="btn" id="addBtn">Add to cart</button>
                <?php else: ?>
                    <p class="stock-low"><b>Sold out</b></p>
                <?php endif; ?>
            </form>

            <div class="trust">
                <span><i class="fa fa-diamond"></i>Natural Ceylon sapphire</span>
                <span><i class="fa fa-lock"></i>Secure checkout</span>
                <span><i class="fa fa-gift"></i><?= $isGem ? 'Made to order' : 'Handcrafted' ?></span>
            </div>
            <h3>Product details</h3>
            <div class="spec">
            <?php if ($isGem): ?>
                <p>Type – <?= e($p['category']) ?></p>
                <?php if ($p['carat']): ?><p>Weight – <?= e($p['carat']) ?> Carats</p><?php endif; ?>
                <p>Identification – Natural Corundum</p>
            <?php else: ?>
                <p>Metal – <?= e($metals[$p['metal_code']]['label'] ?? $p['metal_code']) ?></p>
                <p>Type – <?= e(ucfirst($p['jewellery_type'])) ?></p>
            <?php endif; ?>
            <p><?= e($p['description']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row-head"><h2>Related Products</h2><a class="btn btn-outline" href="<?= url('gemstones.php') ?>">View more</a></div>
    <div class="prod-row">
        <?php
        $rel = products_where('kind = ? AND id <> ? AND stock > 0', [$p['kind'], $p['id']], 'RAND() LIMIT 4');
        foreach ($rel as $r) echo product_card($r);
        ?>
    </div>
</div>

<?php
ob_start(); ?>
<script>
    // live price estimator
    var PID = <?= (int)$p['id'] ?>, IS_GEM = <?= $isGem ? 'true' : 'false' ?>, NEEDS_SIZE = <?= $needsSize ? 'true' : 'false' ?>;
    var estimator = document.getElementById("estimator"), rows = document.getElementById("estRows");
    var jtype = document.getElementById("jtype"), size = document.getElementById("size"), metal = document.getElementById("metal");
    var RING_TYPES = ["ring"], timer = null;

    function syncFields() {
        if (!IS_GEM) return;
        var t = jtype.value;
        size.style.display  = (t === "ring") ? "block" : "none";
        metal.style.display = (t !== "") ? "block" : "none";
        if (t !== "ring") size.value = "";
        if (t === "") metal.value = "";
    }
    function money(n) { return "$" + Number(n).toLocaleString("en-US", {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function refresh() {
        syncFields();
        var q = new URLSearchParams({id: PID, jtype: jtype ? jtype.value : "", size: size ? size.value : "", metal: metal ? metal.value : ""});
        estimator.classList.add("loading");
        fetch("<?= url('api/estimate.php') ?>?" + q.toString())
            .then(function (r) { return r.json(); })
            .then(function (d) {
                estimator.classList.remove("loading");
                rows.innerHTML = "";
                if (!d.ok) { rows.innerHTML = '<tr><td style="color:#a12a2a">' + d.error + '</td></tr>'; return; }
                d.rows.forEach(function (r) { rows.innerHTML += "<tr><td>" + r.label + "</td><td style='text-align:right'>" + money(r.amount) + "</td></tr>"; });
                if (d.rows.length > 1) rows.innerHTML += "<tr class='total'><td>Estimated total</td><td style='text-align:right'>" + d.total_fmt + "</td></tr>";
            })
            .catch(function () { estimator.classList.remove("loading"); rows.innerHTML = "<tr><td>Could not load estimate.</td></tr>"; });
    }
    [jtype, size, metal].forEach(function (el) { if (el) el.addEventListener("change", refresh); });
    refresh();
</script>
<?php $extraScripts = ob_get_clean();
require __DIR__ . '/includes/footer.php'; ?>
