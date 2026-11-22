<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$one = fn(string $sql) => $pdo->query($sql)->fetchColumn();

$revenue   = (float)$one("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('paid','shipped')");
$orders    = (int)$one("SELECT COUNT(*) FROM orders");
$pending   = (int)$one("SELECT COUNT(*) FROM orders WHERE status='pending'");
$toShip    = (int)$one("SELECT COUNT(*) FROM orders WHERE status='paid'");
$customers = (int)$one("SELECT COUNT(*) FROM users WHERE is_admin=0");
$products  = (int)$one("SELECT COUNT(*) FROM products WHERE is_active=1");
try { $unread = (int)$one("SELECT COUNT(*) FROM messages WHERE is_read=0"); } catch (Throwable $e) { $unread = 0; }
$lowStock  = (int)$one("SELECT COUNT(*) FROM products WHERE is_active=1 AND stock <= IF(kind='gem',0,2)");

// revenue per day, last 7 days
$rows = $pdo->query("SELECT DATE(paid_at) d, SUM(total) s FROM orders WHERE status IN ('paid','shipped') AND paid_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(paid_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$days = []; for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $days[$d] = (float)($rows[$d] ?? 0); }
$max = max(1, max($days));

$recent = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 8")->fetchAll();
$top = $pdo->query("SELECT oi.name, SUM(oi.qty) q, SUM(oi.qty*oi.unit_price) s FROM order_items oi JOIN orders o ON o.id=oi.order_id
                    WHERE o.status IN ('paid','shipped') GROUP BY oi.product_id, oi.name ORDER BY s DESC LIMIT 5")->fetchAll();

admin_head('Dashboard', 'index');
?>
<div class="quick">
    <a class="btn ok" href="<?= url('admin/product_edit.php') ?>"><i class="fa fa-plus"></i> Add product</a>
    <a class="btn" href="<?= url('admin/orders.php?status=paid') ?>"><i class="fa fa-truck"></i> Orders to ship (<?= $toShip ?>)</a>
    <a class="btn" href="<?= url('admin/messages.php') ?>"><i class="fa fa-envelope"></i> Messages (<?= $unread ?> new)</a>
    <a class="btn ghost" href="<?= url('admin/pages.php') ?>"><i class="fa fa-file-text-o"></i> Edit info pages</a>
</div>
<div class="cards">
    <div class="card"><small>Revenue (paid)</small><b><?= money($revenue) ?></b></div>
    <div class="card"><small>Total orders</small><b><?= $orders ?></b></div>
    <div class="card"><small>Awaiting shipment</small><b><?= $toShip ?></b></div>
    <div class="card"><small>Pending payment</small><b><?= $pending ?></b></div>
    <div class="card"><small>Customers</small><b><?= $customers ?></b></div>
    <div class="card"><small>Active products</small><b><?= $products ?></b></div>
    <div class="card <?= $lowStock ? 'warn' : '' ?>"><small>Sold out / low stock</small><b><?= $lowStock ?></b></div>
</div>

<div class="grid2">
    <div class="panel">
        <h2>Revenue - last 7 days</h2>
        <div class="bars">
            <?php $k = 0; foreach ($days as $d => $v): ?>
            <div class="bar"><em><?= $v > 0 ? '$' . number_format($v, 0) : '' ?></em>
                <span style="height:<?= round($v / $max * 100) ?>%;animation-delay:<?= $k++ * 90 ?>ms" title="<?= money($v) ?>"></span>
                <?= e(date('D', strtotime($d))) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <h2>Top sellers</h2>
        <?php if (!$top): ?><p class="muted">No paid orders yet.</p><?php else: ?>
        <table><?php foreach ($top as $t): ?><tr><td><?= e($t['name']) ?></td><td class="right"><b><?= money($t['s']) ?></b><br><small class="muted"><?= (int)$t['q'] ?> sold</small></td></tr><?php endforeach; ?></table>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <h2>Recent orders <a class="btn sm ghost" style="float:right" href="<?= url('admin/orders.php') ?>">View all</a></h2>
    <?php if (!$recent): ?><p class="muted">No orders yet.</p><?php else: ?>
    <table>
        <tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach ($recent as $o): ?>
        <tr><td><b><?= e($o['order_no']) ?></b></td><td><?= e($o['username']) ?></td><td><?= e(date('d M Y H:i', strtotime($o['created_at']))) ?></td>
            <td><?= money($o['total']) ?></td><td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><a href="<?= url('admin/order_view.php?id=' . (int)$o['id']) ?>">Open</a></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
<?php admin_foot();
