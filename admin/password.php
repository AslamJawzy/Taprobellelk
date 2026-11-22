<?php
require __DIR__ . '/_layout.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?'); $st->execute([$admin['id']]);
    $hash = $st->fetchColumn();
    $new = $_POST['new'] ?? '';
    if (!password_verify($_POST['current'] ?? '', $hash)) $err = 'Current password is incorrect.';
    elseif (strlen($new) < 8)                              $err = 'New password must be at least 8 characters.';
    elseif ($new !== ($_POST['confirm'] ?? ''))            $err = 'New passwords do not match.';
    else {
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        session_regenerate_id(true);
        flash('success', 'Password changed.');
        redirect('admin/index.php');
    }
}
admin_head('Change password', 'password');
?>
<div class="panel" style="max-width:460px">
    <?php if ($err): ?><div class="flash flash-error" style="margin-bottom:10px"><?= e($err) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?>
        <label>Current password</label><input type="password" name="current" required>
        <label>New password (min 8 characters)</label><input type="password" name="new" required>
        <label>Confirm new password</label><input type="password" name="confirm" required>
        <p style="margin-top:18px"><button class="btn">Update password</button></p>
    </form>
</div>
<?php admin_foot();
