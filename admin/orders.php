<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$status = $_GET['status'] ?? ''; $q = trim($_GET['q'] ?? ''); $page = max(1, (int)($_GET['page'] ?? 1)); $per = 15;
$where = ['1=1']; $par = [];
if (in_array($status, ['pending', 'paid', 'shipped', 'failed', 'cancelled'], true)) { $where[] = 'o.status = ?'; $par[] = $status; }
if ($q !== '') { $where[] = '(o.order_no LIKE ? OR o.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)'; array_push($par, "%$q%", "%$q%", "%$q%", "%$q%"); }
$w = implode(' AND ', $where);
$st = $pdo->prepare("SELECT COUNT(*) FROM orders o JOIN users u ON u.id=o.user_id WHERE $w"); $st->execute($par); $total = (int)$st->fetchColumn();
$st = $pdo->prepare("SELECT o.*, u.username, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) items FROM orders o JOIN users u ON u.id=o.user_id WHERE $w ORDER BY o.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($par); $rows = $st->fetchAll();
admin_head('Orders', 'orders');
?>
<div class="panel">
    <form class="filters" method="get">
        <input type="text" name="q" placeholder="Order no, customer, email" value="<?= e($q) ?>">
        <select name="status"><option value="">All statuses</option>
            <?php foreach (['pending', 'paid', 'shipped', 'failed', 'cancelled'] as $s) echo '<option value="' . $s . '" ' . ($status === $s ? 'selected' : '') . '>' . ucfirst($s) . '</option>'; ?></select>
        <button class="btn" type="submit">Filter</button>
    </form>
    <table>
        <tr><th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach ($rows as $o): ?>
        <tr><td><b><?= e($o['order_no']) ?></b></td>
            <td><?= e($o['full_name']) ?><br><small class="muted">@<?= e($o['username']) ?></small></td>
            <td><?= e(date('d M Y H:i', strtotime($o['created_at']))) ?></td><td><?= (int)$o['items'] ?></td>
            <td><b><?= money($o['total']) ?></b></td>
            <td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><a class="btn sm" href="<?= url('admin/order_view.php?id=' . (int)$o['id']) ?>">View</a></td></tr>
        <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="muted">No orders found.</td></tr>'; ?>
    </table>
    <?= pager($total, $per, $page, url('admin/orders.php') . '?' . http_build_query(array_filter(['q' => $q, 'status' => $status]))) ?>
</div>
<?php admin_foot();
