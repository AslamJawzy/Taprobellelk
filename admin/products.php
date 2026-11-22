<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle' && $id) {
        $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('success', 'Product visibility updated.');
    } elseif (($_POST['action'] ?? '') === 'stock' && $id) {
        $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([max(0, min(10000, (int)($_POST['stock'] ?? 0))), $id]);
        flash('success', 'Stock updated.');
    }
    redirect('admin/products.php?' . http_build_query(array_filter(['q' => $_POST['q'] ?? '', 'kind' => $_POST['kind'] ?? '', 'page' => $_POST['page'] ?? ''])));
}

$q = trim($_GET['q'] ?? ''); $kind = $_GET['kind'] ?? ''; $page = max(1, (int)($_GET['page'] ?? 1)); $per = 12;
$where = ['1=1']; $par = [];
if ($q !== '')  { $where[] = '(name LIKE ? OR category LIKE ?)'; $par[] = "%$q%"; $par[] = "%$q%"; }
if (in_array($kind, ['gem', 'jewellery'], true)) { $where[] = 'kind = ?'; $par[] = $kind; }
$w = implode(' AND ', $where);
$st = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $w"); $st->execute($par); $total = (int)$st->fetchColumn();
$st = $pdo->prepare("SELECT * FROM products WHERE $w ORDER BY id DESC LIMIT $per OFFSET " . (($page - 1) * $per)); $st->execute($par);
$rows = $st->fetchAll();

admin_head('Products', 'products');
?>
<div class="panel">
    <form class="filters" method="get">
        <input type="text" name="q" placeholder="Search name or category" value="<?= e($q) ?>">
        <select name="kind"><option value="">All types</option><option value="gem" <?= $kind === 'gem' ? 'selected' : '' ?>>Gemstones</option><option value="jewellery" <?= $kind === 'jewellery' ? 'selected' : '' ?>>Jewellery</option></select>
        <button class="btn" type="submit">Filter</button>
        <a class="btn ok" style="margin-left:auto" href="<?= url('admin/product_edit.php') ?>">+ Add product</a>
    </form>
    <table>
        <tr><th></th><th>Name</th><th>Type</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($rows as $p): ?>
        <tr>
            <td><img class="thumb" src="<?= e(img($p['image'])) ?>" alt=""></td>
            <td><b><?= e($p['name']) ?></b><br><small class="muted"><?= e($p['category']) ?><?= $p['carat'] ? ' · ' . e($p['carat']) . ' ct' : '' ?></small></td>
            <td><?= e(ucfirst($p['kind'])) ?></td>
            <td><?= money($p['price']) ?></td>
            <td>
                <form method="post" class="actions"><?= csrf_field() ?><input type="hidden" name="action" value="stock"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input class="n" style="width:70px;padding:5px 8px" type="number" min="0" name="stock" value="<?= (int)$p['stock'] ?>"><button class="btn sm ghost">Save</button></form>
            </td>
            <td><span class="badge <?= $p['is_active'] ? 'active' : 'archived' ?>"><?= $p['is_active'] ? 'Visible' : 'Hidden' ?></span></td>
            <td class="actions">
                <a class="btn sm" href="<?= url('admin/product_edit.php?id=' . (int)$p['id']) ?>">Edit</a>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="q" value="<?= e($q) ?>"><input type="hidden" name="kind" value="<?= e($kind) ?>"><input type="hidden" name="page" value="<?= $page ?>">
                    <button class="btn sm <?= $p['is_active'] ? 'danger' : 'ok' ?>" onclick="return confirm('<?= $p['is_active'] ? 'Hide this product from the shop?' : 'Show this product in the shop?' ?>')"><?= $p['is_active'] ? 'Hide' : 'Show' ?></button></form>
            </td>
        </tr>
        <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="muted">No products found.</td></tr>'; ?>
    </table>
    <?= pager($total, $per, $page, url('admin/products.php') . '?' . http_build_query(array_filter(['q' => $q, 'kind' => $kind]))) ?>
    <p class="muted" style="margin-top:12px"><small>Products that have been ordered can't be deleted (order history needs them), so use <b>Hide</b> to remove them from the shop.</small></p>
</div>
<?php admin_foot();
