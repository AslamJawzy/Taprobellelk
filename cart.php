<?php
$pageTitle = 'Your Cart';
require __DIR__ . '/includes/header.php';
$cart = cart_totals();
?>
<?= page_banner('Your Cart') ?>
<div class="container"><?= steps(1) ?>
<div class="cart-page">
<?php if (!$cart['lines']): ?>
    <div class="empty-cart">
        <div class="egem"></div>
        <h2>Your cart is empty</h2>
        <p>Browse our gemstones or design your own jewellery.</p>
        <a href="<?= url('gemstones.php') ?>" class="btn">Shop Gemstones</a>
        <a href="<?= url('custom_made.php') ?>" class="btn btn-outline">Custom-made</a>
    </div>
<?php else: ?>
    <?php foreach ($cart['problems'] as $pr): ?><div class="alert error"><?= e($pr) ?></div><?php endforeach; ?>
    <table>
        <tr><th>Product</th><th>Quantity</th><th>Subtotal</th></tr>
        <?php foreach ($cart['lines'] as $l): $p = $l['product']; ?>
        <tr>
            <td>
                <div class="cart-info">
                    <img src="<?= e(img($p['image'])) ?>" alt="">
                    <div>
                        <p><a href="<?= url('product.php?id=' . (int)$p['id']) ?>"><?= e($p['name']) ?></a></p>
                        <span class="cart-opts"><?= e($l['desc']) ?></span>
                        <small>Unit price: <?= money($l['unit']) ?></small><br>
                        <form method="post" action="<?= url('cart_action.php') ?>" style="display:inline">
                            <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="key" value="<?= e($l['key']) ?>">
                            <button class="link-btn" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            </td>
            <td>
                <form method="post" action="<?= url('cart_action.php') ?>" class="qty-form">
                    <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="key" value="<?= e($l['key']) ?>">
                    <input type="number" name="qty" value="<?= (int)$l['qty'] ?>" min="1" max="<?= max(1, min(10, (int)$p['stock'])) ?>">
                    <button type="submit">Update</button>
                </form>
            </td>
            <td><?= money($l['line']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="total-price">
        <table>
            <tr><td>Subtotal</td><td><?= money($cart['subtotal']) ?></td></tr>
            <tr><td>Tax (<?= rtrim(rtrim(number_format(TAX_RATE * 100, 2), '0'), '.') ?>%)</td><td><?= money($cart['tax']) ?></td></tr>
            <tr><td><b>Total</b></td><td><b><?= money($cart['total']) ?></b></td></tr>
        </table>
    </div>
    <div class="cart-actions">
        <a href="<?= url('gemstones.php') ?>" class="btn btn-outline">&#8592; Continue shopping</a>
        <a href="<?= url('checkout.php') ?>" class="btn">Proceed to Checkout &#8594;</a>
    </div>
<?php endif; ?>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
