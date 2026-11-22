<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/orders.php';
$user  = require_login();
$order = get_order_for_user((int)($_GET['order'] ?? 0), (int)$user['id']);
if (!$order) redirect('orders.php');
if ($order['status'] === 'pending') redirect('pay.php?order=' . $order['id']);
$items = get_order_items((int)$order['id']);
$pageTitle = 'Order confirmed';
require __DIR__ . '/includes/header.php';
?>
<canvas id="confetti"></canvas>
<?= page_banner('Order confirmed') ?>
<div class="container"><?= steps(5) ?>
  <div class="success-box">
    <div class="tick"><svg viewBox="0 0 100 100" width="110" height="110"><circle cx="50" cy="50" r="46" fill="none" stroke="#2e9e55" stroke-width="6"/><path d="M28 52 L45 68 L73 36" fill="none" stroke="#2e9e55" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
    <h2>Thank you, <?= e($user['username']) ?>!</h2>
    <p>Your payment was successful. Order number: <b><?= e($order['order_no']) ?></b></p><br>
    <table>
        <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['name']) ?> × <?= (int)$it['qty'] ?><br><small class="cart-opts"><?= e(describe_opts(json_decode($it['options'] ?: '[]', true) ?: [])) ?></small></td><td><?= money($it['unit_price'] * $it['qty']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td>Tax</td><td><?= money($order['tax']) ?></td></tr>
        <tr><td><b>Total paid</b></td><td><b><?= money($order['total']) ?></b></td></tr>
    </table>
    <p><br>We will ship to: <?= e($order['full_name']) ?>, <?= e($order['address']) ?>, <?= e($order['city']) ?>, <?= e($order['country']) ?></p>
    <a href="<?= url('orders.php') ?>" class="btn">My Orders</a>
    <a href="<?= url('gemstones.php') ?>" class="btn btn-outline">Keep Shopping</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
