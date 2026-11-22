<?php
require __DIR__ . '/_layout.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $slug = (string)($_POST['slug'] ?? '');
    if (($_POST['action'] ?? '') === 'toggle') { $pdo->prepare('UPDATE pages SET is_published = 1 - is_published WHERE slug = ?')->execute([$slug]); flash('success', 'Page visibility updated.'); }
    if (($_POST['action'] ?? '') === 'delete') { $pdo->prepare('DELETE FROM pages WHERE slug = ?')->execute([$slug]); flash('success', 'Page deleted.'); }
    redirect('admin/pages.php');
}
$rows = $pdo->query('SELECT * FROM pages ORDER BY sort_order, title')->fetchAll();
admin_head('Info pages', 'pages');
?>
<div class="panel">
    <div class="filters"><span class="muted">FAQ, shipping, privacy and terms. Published pages appear in the website footer.</span>
        <a class="btn ok" style="margin-left:auto" href="<?= url('admin/page_edit.php') ?>">+ Add page</a></div>
    <table><tr><th>Title</th><th>Address</th><th>Updated</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($rows as $p): ?>
    <tr><td><b><?= e($p['title']) ?></b></td><td><a target="_blank" href="<?= url('page.php?slug=' . urlencode($p['slug'])) ?>">page.php?slug=<?= e($p['slug']) ?></a></td>
        <td><?= e(date('d M Y', strtotime($p['updated_at']))) ?></td>
        <td><span class="badge <?= $p['is_published'] ? 'active' : 'archived' ?>"><?= $p['is_published'] ? 'Published' : 'Hidden' ?></span></td>
        <td class="actions"><a class="btn sm" href="<?= url('admin/page_edit.php?slug=' . urlencode($p['slug'])) ?>">Edit</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="slug" value="<?= e($p['slug']) ?>"><input type="hidden" name="action" value="toggle"><button class="btn sm ghost"><?= $p['is_published'] ? 'Hide' : 'Publish' ?></button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="slug" value="<?= e($p['slug']) ?>"><input type="hidden" name="action" value="delete"><button class="btn sm danger" onclick="return confirm('Delete this page permanently?')">Delete</button></form></td></tr>
    <?php endforeach; if (!$rows) echo '<tr><td colspan="5" class="muted">No pages yet.</td></tr>'; ?></table>
</div>
<?php admin_foot();
