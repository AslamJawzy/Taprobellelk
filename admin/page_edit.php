<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$slugParam = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['slug'] ?? ''));
$p = ['slug' => '', 'title' => '', 'content' => '', 'is_published' => 1, 'sort_order' => 10];
$isNew = $slugParam === '';
if (!$isNew) {
    $st = $pdo->prepare('SELECT * FROM pages WHERE slug = ?'); $st->execute([$slugParam]);
    $p = $st->fetch();
    if (!$p) { flash('error', 'Page not found.'); redirect('admin/pages.php'); }
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $f = ['slug' => $isNew ? strtolower(trim($_POST['slug'] ?? '')) : $p['slug'], 'title' => trim($_POST['title'] ?? ''), 'content' => trim($_POST['content'] ?? ''),
          'is_published' => isset($_POST['is_published']) ? 1 : 0, 'sort_order' => (int)($_POST['sort_order'] ?? 10)];
    if ($isNew && !preg_match('/^[a-z0-9\-]{2,60}$/', $f['slug'])) $err = 'Address: 2-60 characters, lowercase letters, numbers and dashes only.';
    elseif ($f['title'] === '' || mb_strlen($f['title']) > 150)   $err = 'Title is required (max 150 characters).';
    elseif ($f['content'] === '')                                  $err = 'Content cannot be empty.';
    elseif ($isNew && $pdo->query('SELECT 1 FROM pages WHERE slug = ' . $pdo->quote($f['slug']))->fetch()) $err = 'A page with that address already exists.';
    if (!$err) {
        if ($isNew) $pdo->prepare('INSERT INTO pages (slug,title,content,is_published,sort_order) VALUES (?,?,?,?,?)')->execute([$f['slug'], $f['title'], $f['content'], $f['is_published'], $f['sort_order']]);
        else $pdo->prepare('UPDATE pages SET title=?, content=?, is_published=?, sort_order=? WHERE slug=?')->execute([$f['title'], $f['content'], $f['is_published'], $f['sort_order'], $f['slug']]);
        flash('success', 'Page saved.');
        redirect('admin/pages.php');
    }
    $p = array_merge($p, $f);
}
admin_head($isNew ? 'Add page' : 'Edit page', 'pages');
?>
<div class="panel" style="max-width:900px">
    <?php if ($err): ?><div class="flash flash-error" style="margin-bottom:12px"><?= e($err) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?>
        <div class="form-grid">
            <div><label>Title</label><input type="text" name="title" value="<?= e($p['title']) ?>" required maxlength="150"></div>
            <div><label>Address (page.php?slug=...)</label><input type="text" name="slug" value="<?= e($p['slug']) ?>" <?= $isNew ? 'required' : 'readonly style="background:#f3f4fb"' ?> placeholder="e.g. gift-guide"></div>
            <div><label>Footer order</label><input type="number" name="sort_order" value="<?= (int)$p['sort_order'] ?>"></div>
        </div>
        <label>Content</label>
        <textarea name="content" rows="18" required><?= e($p['content']) ?></textarea>
        <p class="muted" style="margin-top:6px"><small>Start a line with <b>## </b> to make a heading. Leave a blank line between paragraphs. HTML is not allowed (everything is shown as plain text).</small></p>
        <label style="display:flex;gap:8px;align-items:center;margin-top:14px"><input type="checkbox" name="is_published" style="width:auto" <?= $p['is_published'] ? 'checked' : '' ?>> Published (shown in footer)</label>
        <p style="margin-top:18px"><button class="btn">Save page</button> <a class="btn ghost" href="<?= url('admin/pages.php') ?>">Cancel</a></p>
    </form>
</div>
<?php admin_foot();
