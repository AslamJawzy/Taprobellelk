<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/orders.php';
$user = require_login();
$cart = cart_totals();
if (!$cart['lines']) { flash('error', 'Your cart is empty.'); redirect('cart.php'); }

$f = ['full_name' => '', 'phone' => '', 'address' => '', 'city' => '', 'postal_code' => '', 'country' => 'Sri Lanka'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));
    if (in_array('', $f, true))                          $error = 'Please fill in all shipping details.';
    elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $f['phone'])) $error = 'Enter a valid phone number.';
    else {
        try {
            $oid = create_order_from_cart((int)$user['id'], $f);
            redirect('pay.php?order=' . $oid);
        } catch (RuntimeException $e) { $error = $e->getMessage(); }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
echo page_banner('Checkout', ['Cart' => url('cart.php')]);
?>
<div class="container"><?= steps(2) ?>
  <div class="checkout-grid">
    <div>
        <h2>Shipping details</h2>
        <div class="form-card">
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <label>Full name</label><input type="text" name="full_name" value="<?= e($f['full_name']) ?>" required>
            <label>Phone</label><input type="text" name="phone" value="<?= e($f['phone']) ?>" required>
            <label>Address</label><input type="text" name="address" value="<?= e($f['address']) ?>" required>
            <label>City</label><input type="text" name="city" value="<?= e($f['city']) ?>" required>
            <label>Postal code</label><input type="text" name="postal_code" value="<?= e($f['postal_code']) ?>" required>
            <label>Country</label><input type="text" name="country" value="<?= e($f['country']) ?>" required>
            <button type="submit" class="btn btn-block">Continue to payment &#8594;</button>
        </form>
        </div>
    </div>
    <div>
        <h2>Order summary</h2>
        <div class="summary-box">
            <table>
            <?php foreach ($cart['lines'] as $l): ?>
                <tr><td><?= e($l['product']['name']) ?> × <?= (int)$l['qty'] ?><br><small class="cart-opts"><?= e($l['desc']) ?></small></td><td><?= money($l['line']) ?></td></tr>
            <?php endforeach; ?>
                <tr><td>Subtotal</td><td><?= money($cart['subtotal']) ?></td></tr>
                <tr><td>Tax</td><td><?= money($cart['tax']) ?></td></tr>
                <tr><td><b>Total (<?= e(CURRENCY) ?>)</b></td><td><b><?= money($cart['total']) ?></b></td></tr>
            </table>
        </div>
        <p><a href="<?= url('cart.php') ?>">&#8592; Edit cart</a></p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
