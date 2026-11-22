<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$p = ['kind' => 'gem', 'category' => '', 'name' => '', 'carat' => '', 'price' => '', 'image' => '', 'description' => '', 'stock' => 1, 'jewellery_type' => '', 'metal_code' => '', 'is_active' => 1];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?'); $st->execute([$id]);
    $p = $st->fetch() ?: null;
    if (!$p) { flash('error', 'Product not found.'); redirect('admin/products.php'); }
}
$types = jewellery_types(); $metals = metal_rates();
$cats = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $f = [
        'kind' => $_POST['kind'] ?? 'gem', 'category' => trim($_POST['category'] ?? ''), 'name' => trim($_POST['name'] ?? ''),
        'carat' => trim($_POST['carat'] ?? ''), 'price' => trim($_POST['price'] ?? ''), 'image' => $_POST['image'] ?? '',
        'description' => trim($_POST['description'] ?? ''), 'stock' => (int)($_POST['stock'] ?? 0),
        'jewellery_type' => $_POST['jewellery_type'] ?? '', 'metal_code' => $_POST['metal_code'] ?? '', 'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    // optional image upload -> saved into /Images
    if (!empty($_FILES['upload']['name']) && $_FILES['upload']['error'] !== UPLOAD_ERR_NO_FILE) {
        $u = $_FILES['upload'];
        $mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = $u['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($u['tmp_name']) : '';
        if ($u['error'] !== UPLOAD_ERR_OK)             $err = 'Image upload failed (error ' . (int)$u['error'] . ').';
        elseif ($u['size'] > 5 * 1024 * 1024)          $err = 'Image must be 5 MB or smaller.';
        elseif (!isset($mimes[$mime]))                 $err = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        else {
            $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($f['name'] ?: 'product')), '-') ?: 'product';
            $fname = $slug . '-' . time() . '.' . $mimes[$mime];
            if (move_uploaded_file($u['tmp_name'], IMAGES_DIR . '/' . $fname)) $f['image'] = $fname;
            else $err = 'Could not save the image. Check that the Images folder is writable.';
        }
    }

    if (!$err) {
        if (!in_array($f['kind'], ['gem', 'jewellery'], true))        $err = 'Invalid type.';
        elseif ($f['name'] === '' || mb_strlen($f['name']) > 150)     $err = 'Name is required (max 150 characters).';
        elseif ($f['category'] === '' || mb_strlen($f['category']) > 60) $err = 'Category is required (max 60 characters).';
        elseif (!is_numeric($f['price']) || $f['price'] < 0 || $f['price'] > 10000000) $err = 'Enter a valid price.';
        elseif ($f['carat'] !== '' && (!is_numeric($f['carat']) || $f['carat'] < 0 || $f['carat'] > 999)) $err = 'Enter a valid carat weight.';
        elseif ($f['stock'] < 0 || $f['stock'] > 10000)               $err = 'Stock must be between 0 and 10000.';
        elseif (!in_array($f['image'], image_files(), true))          $err = 'Please choose or upload an image.';
        elseif ($f['kind'] === 'jewellery' && (!isset($types[$f['jewellery_type']]) || !isset($metals[$f['metal_code']]))) $err = 'Jewellery needs a jewellery type and a metal.';
    }

    if (!$err) {
        if ($f['kind'] === 'gem') { $f['jewellery_type'] = null; $f['metal_code'] = null; }
        $gallery = json_encode(gallery_for($f['image']));
        $carat = $f['carat'] === '' ? null : (float)$f['carat'];
        $args = [$f['kind'], $f['category'], $f['name'], $carat, round((float)$f['price'], 2), $f['image'], $gallery, $f['description'], $f['stock'], $f['jewellery_type'], $f['metal_code'], $f['is_active']];
        if ($id) {
            $pdo->prepare('UPDATE products SET kind=?,category=?,name=?,carat=?,price=?,image=?,gallery=?,description=?,stock=?,jewellery_type=?,metal_code=?,is_active=? WHERE id=?')->execute([...$args, $id]);
            flash('success', 'Product updated.');
        } else {
            $pdo->prepare('INSERT INTO products (kind,category,name,carat,price,image,gallery,description,stock,jewellery_type,metal_code,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')->execute($args);
            flash('success', 'Product added.');
        }
        redirect('admin/products.php');
    }
    $p = array_merge($p, $f);
}
$imgs = image_files();
admin_head($id ? 'Edit product' : 'Add product', 'products');
?>
<div class="panel" style="max-width:900px">
    <?php if ($err): ?><div class="flash flash-error" style="margin-bottom:12px"><?= e($err) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-grid">
            <div><label>Type</label><select name="kind" id="kind"><option value="gem" <?= $p['kind'] === 'gem' ? 'selected' : '' ?>>Gemstone</option><option value="jewellery" <?= $p['kind'] === 'jewellery' ? 'selected' : '' ?>>Finished jewellery</option></select></div>
            <div><label>Category</label><input list="cats" name="category" value="<?= e($p['category']) ?>" required><datalist id="cats"><?php foreach ($cats as $c) echo '<option value="' . e($c) . '">'; ?></datalist></div>
            <div><label>Name</label><input type="text" name="name" value="<?= e($p['name']) ?>" required maxlength="150"></div>
            <div><label>Price (USD) <span id="priceHint" class="muted"></span></label><input type="number" step="0.01" min="0" name="price" value="<?= e($p['price']) ?>" required></div>
            <div><label>Carat weight</label><input type="number" step="0.01" min="0" name="carat" value="<?= e($p['carat']) ?>"></div>
            <div><label>Stock</label><input type="number" min="0" name="stock" value="<?= (int)$p['stock'] ?>"></div>
        </div>
        <div class="form-grid" id="jewel" style="display:none">
            <div><label>Jewellery type</label><select name="jewellery_type"><option value="">-</option><?php foreach ($types as $c => $t) echo '<option value="' . e($c) . '" ' . ($p['jewellery_type'] === $c ? 'selected' : '') . '>' . e($t['label']) . '</option>'; ?></select></div>
            <div><label>Metal</label><select name="metal_code"><option value="">-</option><?php foreach ($metals as $c => $m) echo '<option value="' . e($c) . '" ' . ($p['metal_code'] === $c ? 'selected' : '') . '>' . e($m['label']) . '</option>'; ?></select></div>
        </div>
        <label>Description</label><textarea name="description" rows="3"><?= e($p['description']) ?></textarea>

        <div class="form-grid">
            <div><label>Main image (from your Images folder)</label>
                <select name="image" id="imgSel"><option value="">- choose -</option>
                <?php foreach ($imgs as $f) echo '<option value="' . e($f) . '" ' . ($p['image'] === $f ? 'selected' : '') . '>' . e($f) . '</option>'; ?></select></div>
            <div><label>...or upload a new image (JPG/PNG/WEBP, max 5 MB)</label><input type="file" name="upload" accept="image/jpeg,image/png,image/webp,image/gif"></div>
        </div>
        <p style="margin-top:12px"><img id="prev" class="preview" src="<?= $p['image'] ? e(img($p['image'])) : '' ?>" style="<?= $p['image'] ? '' : 'display:none' ?>" alt="">
        <br><small class="muted">Extra photos named like "<i>name 1.jpg</i>", "<i>name (2).jpg</i>" are picked up automatically as the product gallery.</small></p>

        <label style="display:flex;gap:8px;align-items:center;margin-top:16px"><input type="checkbox" name="is_active" style="width:auto" <?= $p['is_active'] ? 'checked' : '' ?>> Visible in shop</label>
        <p style="margin-top:20px"><button class="btn" type="submit">Save product</button> <a class="btn ghost" href="<?= url('admin/products.php') ?>">Cancel</a></p>
    </form>
</div>
<script>
    var kind = document.getElementById('kind'), jewel = document.getElementById('jewel'), hint = document.getElementById('priceHint');
    function sync() { var j = kind.value === 'jewellery'; jewel.style.display = j ? 'grid' : 'none'; hint.textContent = j ? '(fixed price of the finished piece)' : '(gem price - metal & labour are added by the estimator)'; }
    kind.onchange = sync; sync();
    document.getElementById('imgSel').onchange = function () { var i = document.getElementById('prev'); if (this.value) { i.src = "<?= url('Images/') ?>" + encodeURIComponent(this.value); i.style.display = 'block'; } else i.style.display = 'none'; };
</script>
<?php admin_foot();
