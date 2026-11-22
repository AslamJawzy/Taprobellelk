<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = $pdo->prepare('SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id = ?'); $st->execute([$id]);
$o = $st->fetch();
if (!$o) { flash('error', 'Order not found.'); redirect('admin/orders.php'); }

// allowed manual transitions (payments change status only through the payment flow)
$allowed = ['pending' => ['cancelled'], 'paid' => ['shipped'], 'shipped' => ['paid'], 'failed' => ['cancelled'], 'cancelled' => []];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $new = $_POST['status'] ?? '';
    if (in_array($new, $allowed[$o['status']], true)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$new, $id]);
        flash('success', "Order {$o['order_no']} marked as $new.");
    } else flash('error', 'That status change is not allowed.');
    redirect('admin/order_view.php?id=' . $id);
}
$items = get_order_items($id);
$pays = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id'); $pays->execute([$id]); $pays = $pays->fetchAll();
admin_head('Order ' . $o['order_no'], 'orders');
?>
<p style="margin-bottom:16px"><a href="<?= url('admin/orders.php') ?>">&larr; Back to orders</a></p>
<div class="grid2">
  <div>
    <div class="panel"><h2>Items</h2>
        <table><tr><th></th><th>Item</th><th>Options</th><th>Qty</th><th class="right">Line total</th></tr>
        <?php foreach ($items as $it): ?>
        <tr><td><img class="thumb" src="<?= e(img($it['image'])) ?>" alt=""></td><td><b><?= e($it['name']) ?></b></td>
            <td><small><?= e(describe_opts(json_decode($it['options'] ?: '[]', true) ?: [])) ?></small>
            <?php foreach (json_decode($it['breakdown'] ?: '[]', true) ?: [] as $lbl => $amt) if (count(json_decode($it['breakdown'], true)) > 1) echo '<br><small class="muted">' . e($lbl) . ': ' . money($amt) . '</small>'; ?></td>
            <td><?= (int)$it['qty'] ?></td><td class="right"><?= money($it['unit_price'] * $it['qty']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td colspan="4" class="right">Subtotal</td><td class="right"><?= money($o['subtotal']) ?></td></tr>
        <tr><td colspan="4" class="right">Tax</td><td class="right"><?= money($o['tax']) ?></td></tr>
        <tr><td colspan="4" class="right"><b>Total (<?= e($o['currency']) ?>)</b></td><td class="right"><b><?= money($o['total']) ?></b></td></tr></table>
    </div>
    <div class="panel"><h2>Payment attempts</h2>
        <?php if (!$pays): ?><p class="muted">No payment attempts yet.</p><?php else: ?>
        <table><tr><th>Date</th><th>Provider</th><th>Reference</th><th>Amount</th><th>Status</th></tr>
        <?php foreach ($pays as $p): ?><tr><td><?= e(date('d M Y H:i', strtotime($p['created_at']))) ?></td><td><?= e(strtoupper($p['provider'])) ?></td><td><small><?= e($p['provider_ref']) ?></small></td><td><?= money($p['amount']) ?></td>
            <td><span class="badge <?= $p['status'] === 'completed' ? 'paid' : 'failed' ?>"><?= e($p['status']) ?></span></td></tr><?php endforeach; ?></table>
        <?php endif; ?>
    </div>
  </div>
  <div>
    <div class="panel"><h2>Status</h2>
        <p style="margin-bottom:14px"><span class="badge <?= e($o['status']) ?>" style="font-size:13px;padding:5px 16px"><?= e($o['status']) ?></span></p>
        <?php foreach ($allowed[$o['status']] as $next): ?>
        <form method="post" style="margin-bottom:8px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="status" value="<?= e($next) ?>">
            <button class="btn <?= $next === 'cancelled' ? 'danger' : 'ok' ?>" style="width:100%" onclick="return confirm('Mark this order as <?= e($next) ?>?')">Mark as <?= e($next) ?></button></form>
        <?php endforeach; if (!$allowed[$o['status']]) echo '<p class="muted">No further actions.</p>'; ?>
        <p class="muted" style="margin-top:10px"><small>Orders become <b>paid</b> automatically when the payment is confirmed.</small></p>
    </div>
    <div class="panel"><h2>Customer & shipping</h2>
        <dl class="kv"><dt>Account</dt><dd><?= e($o['username']) ?></dd><dt>Email</dt><dd><?= e($o['email']) ?></dd><dt>Name</dt><dd><?= e($o['full_name']) ?></dd>
        <dt>Phone</dt><dd><?= e($o['phone']) ?></dd><dt>Address</dt><dd><?= e($o['address']) ?><br><?= e($o['city']) ?> <?= e($o['postal_code']) ?><br><?= e($o['country']) ?></dd>
        <dt>Placed</dt><dd><?= e(date('d M Y H:i', strtotime($o['created_at']))) ?></dd><dt>Paid</dt><dd><?= $o['paid_at'] ? e(date('d M Y H:i', strtotime($o['paid_at']))) : '-' ?></dd></dl>
    </div>
  </div>
</div>
<?php admin_foot();
