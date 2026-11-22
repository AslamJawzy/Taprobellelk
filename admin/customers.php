<?php
require __DIR__ . '/_layout.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $uid = (int)($_POST['id'] ?? 0);
    if ($uid === (int)$admin['id']) flash('error', 'You cannot change your own admin access.');
    else { $pdo->prepare('UPDATE users SET is_admin = 1 - is_admin WHERE id = ?')->execute([$uid]); flash('success', 'Access updated.'); }
    redirect('admin/customers.php');
}
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) n,
        (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=u.id AND status IN ('paid','shipped')) spent
        FROM users u WHERE (u.username LIKE ? OR u.email LIKE ?) ORDER BY u.id DESC");
$st->execute(["%$q%", "%$q%"]); $rows = $st->fetchAll();
admin_head('Customers', 'customers');
?>
<div class="panel">
    <form class="filters" method="get"><input type="text" name="q" placeholder="Search username or email" value="<?= e($q) ?>"><button class="btn">Search</button></form>
    <table><tr><th>User</th><th>Email</th><th>Joined</th><th>Orders</th><th>Spent</th><th>Role</th><th></th></tr>
    <?php foreach ($rows as $u): ?>
    <tr><td><b><?= e($u['username']) ?></b></td><td><?= e($u['email']) ?></td><td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
        <td><?= (int)$u['n'] ?></td><td><?= money($u['spent']) ?></td>
        <td><span class="badge <?= $u['is_admin'] ? 'admin' : '' ?>"><?= $u['is_admin'] ? 'Admin' : 'Customer' ?></span></td>
        <td><?php if ((int)$u['id'] !== (int)$admin['id']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn sm ghost" onclick="return confirm('<?= $u['is_admin'] ? 'Remove admin access?' : 'Give this user FULL admin access?' ?>')"><?= $u['is_admin'] ? 'Remove admin' : 'Make admin' ?></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?></table>
</div>
<?php admin_foot();
