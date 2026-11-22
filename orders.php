<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/orders.php';
$user = require_login();
$st = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();
$pageTitle = 'My Orders';
require __DIR__ . '/includes/header.php';
?>
<?= page_banner('My Orders') ?>
<div class="container page-body">
    <?php if (!$orders): ?>
        <div class="empty-cart"><div class="egem"></div><h2>No orders yet</h2><a class="btn" href="<?= url('gemstones.php') ?>">Start shopping</a></div>
    <?php else: ?>
    <div class="table-wrap"><table class="orders-table">
        <tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach ($orders as $o): $its = get_order_items((int)$o['id']); ?>
        <tr>
            <td><?= e($o['order_no']) ?></td>
            <td><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
            <td><?php foreach ($its as $it) echo e($it['name']) . ' × ' . (int)$it['qty'] . '<br>'; ?></td>
            <td><?= money($o['total']) ?></td>
            <td><span class="badge <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
            <td><?= $o['status'] === 'pending' ? '<a href="' . url('pay.php?order=' . (int)$o['id']) . '">Pay now</a>' : '<a href="' . url('order_success.php?order=' . (int)$o['id']) . '">View</a>' ?></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
