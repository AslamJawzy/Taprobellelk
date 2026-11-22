<?php
require __DIR__ . '/_layout.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $a = $_POST['action'] ?? '';
    $num = fn($v, $max) => (is_numeric($v) && $v >= 0 && $v <= $max) ? round((float)$v, 2) : null;
    if ($a === 'metals') {
        $ok = true;
        foreach ((array)($_POST['metal'] ?? []) as $code => $m) {
            $r = $num($m['rate'] ?? '', 100000); $label = trim($m['label'] ?? '');
            if ($r === null || $label === '') { $ok = false; continue; }
            $pdo->prepare('UPDATE metal_rates SET label = ?, rate_per_gram = ? WHERE code = ?')->execute([$label, $r, $code]);
        }
        flash($ok ? 'success' : 'error', $ok ? 'Metal rates saved. Estimates update immediately.' : 'Some rows were invalid and were skipped.');
    } elseif ($a === 'newmetal') {
        $code = strtolower(trim($_POST['code'] ?? '')); $label = trim($_POST['label'] ?? ''); $r = $num($_POST['rate'] ?? '', 100000);
        if (!preg_match('/^[a-z0-9]{1,20}$/', $code) || $label === '' || $r === null) flash('error', 'Enter a short code (letters/numbers), a label and a valid rate.');
        elseif (isset(metal_rates()[$code])) flash('error', 'That metal code already exists.');
        else { $pdo->prepare('INSERT INTO metal_rates (code,label,rate_per_gram,sort_order) VALUES (?,?,?,?)')->execute([$code, $label, $r, count(metal_rates()) + 1]); flash('success', 'Metal added.'); }
    } elseif ($a === 'types') {
        foreach ((array)($_POST['type'] ?? []) as $code => $t) {
            $g = $num($t['grams'] ?? '', 1000); $m = $num($t['making'] ?? '', 500); $s = $num($t['setting'] ?? '', 1000000);
            if ($g === null || $m === null || $s === null) { flash('error', 'Invalid value for ' . $code . '.'); continue; }
            $pdo->prepare('UPDATE jewellery_types SET base_grams=?, making_pct=?, setting_fee=? WHERE code=?')->execute([$g, $m, $s, $code]);
        }
        flash('success', 'Jewellery settings saved.');
    }
    redirect('admin/rates.php');
}
$metals = $pdo->query('SELECT * FROM metal_rates ORDER BY sort_order')->fetchAll();
$types  = $pdo->query('SELECT * FROM jewellery_types ORDER BY sort_order')->fetchAll();
admin_head('Pricing rates', 'rates');
?>
<p class="muted" style="margin-bottom:18px">These values drive the customer-facing <b>Dynamic Price Estimator</b>: <i>gem price + (grams × metal rate) + making charge % + setting fee</i>. Orders already placed keep the price they were bought at.</p>
<div class="panel" style="max-width:720px"><h2>Metal rates (USD per gram)</h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="metals">
    <table><tr><th>Code</th><th>Label</th><th>Rate / gram</th></tr>
    <?php foreach ($metals as $m): ?><tr><td><code><?= e($m['code']) ?></code></td>
        <td><input class="t" name="metal[<?= e($m['code']) ?>][label]" value="<?= e($m['label']) ?>"></td>
        <td><input class="n" type="number" step="0.01" min="0" name="metal[<?= e($m['code']) ?>][rate]" value="<?= e($m['rate_per_gram']) ?>"></td></tr><?php endforeach; ?></table>
    <p style="margin-top:14px"><button class="btn">Save metal rates</button></p></form>
    <hr style="margin:20px 0;border:none;border-top:1px solid var(--line)">
    <form method="post" class="filters"><?= csrf_field() ?><input type="hidden" name="action" value="newmetal">
        <input name="code" placeholder="code (e.g. 9k)" style="min-width:110px"><input name="label" placeholder="Label (e.g. 9k Gold)"><input name="rate" type="number" step="0.01" placeholder="Rate/g"><button class="btn ghost">+ Add metal</button></form>
</div>
<div class="panel" style="max-width:720px"><h2>Jewellery types</h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="types">
    <table><tr><th>Type</th><th>Base grams</th><th>Making %</th><th>Setting fee ($)</th></tr>
    <?php foreach ($types as $t): ?><tr><td><b><?= e($t['label']) ?></b></td>
        <td><input class="n" type="number" step="0.01" min="0" name="type[<?= e($t['code']) ?>][grams]" value="<?= e($t['base_grams']) ?>"></td>
        <td><input class="n" type="number" step="0.01" min="0" name="type[<?= e($t['code']) ?>][making]" value="<?= e($t['making_pct']) ?>"></td>
        <td><input class="n" type="number" step="0.01" min="0" name="type[<?= e($t['code']) ?>][setting]" value="<?= e($t['setting_fee']) ?>"></td></tr><?php endforeach; ?></table>
    <p style="margin-top:14px"><button class="btn">Save jewellery settings</button></p></form>
</div>
<?php admin_foot();
