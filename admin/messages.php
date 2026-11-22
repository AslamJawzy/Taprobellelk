<?php
require __DIR__ . '/_layout.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $id = (int)($_POST['id'] ?? 0); $a = $_POST['action'] ?? '';
    if ($a === 'read')   $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    if ($a === 'unread') $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
    if ($a === 'delete') { $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]); flash('success', 'Message deleted.'); }
    if ($a === 'allread') { $pdo->exec('UPDATE messages SET is_read = 1'); flash('success', 'All messages marked as read.'); }
    redirect('admin/messages.php');
}
$rows = $pdo->query('SELECT * FROM messages ORDER BY is_read ASC, id DESC LIMIT 200')->fetchAll();
admin_head('Messages', 'messages');
?>
<div class="panel">
    <div class="filters"><span class="muted">Messages sent from the website Contact page.</span>
        <form method="post" style="margin-left:auto"><?= csrf_field() ?><input type="hidden" name="action" value="allread"><button class="btn sm ghost">Mark all as read</button></form></div>
    <?php if (!$rows): ?><p class="muted">No messages yet.</p><?php endif; ?>
    <?php foreach ($rows as $m): ?>
    <div class="msg <?= $m['is_read'] ? '' : 'unread' ?>">
        <h3><?= e($m['name']) ?> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt;</h3>
        <div class="when"><?= e(date('d M Y H:i', strtotime($m['created_at']))) ?></div>
        <p><?= e($m['message']) ?></p>
        <div class="actions">
            <a class="btn sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: your message to Taprobellelk') ?>">Reply by email</a>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input type="hidden" name="action" value="<?= $m['is_read'] ? 'unread' : 'read' ?>"><button class="btn sm ghost"><?= $m['is_read'] ? 'Mark unread' : 'Mark read' ?></button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn sm danger" onclick="return confirm('Delete this message?')">Delete</button></form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php admin_foot();
