<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/paypal.php';
$user  = require_login();
$order = get_order_for_user((int)($_GET['order'] ?? 0), (int)$user['id']);
if (!$order) { flash('error', 'Order not found.'); redirect('orders.php'); }
if (in_array($order['status'], ['paid', 'shipped'], true)) redirect('order_success.php?order=' . (int)$order['id']);
if ($order['status'] !== 'pending') { flash('error', 'This order can no longer be paid.'); redirect('orders.php'); }

$items = get_order_items((int)$order['id']);
$error = '';

// ---------- Demo gateway (simulated card payment; nothing is stored except the last 4 digits) ----------
function luhn_ok(string $n): bool {
    $sum = 0; $alt = false;
    for ($i = strlen($n) - 1; $i >= 0; $i--) { $d = (int)$n[$i]; if ($alt) { $d *= 2; if ($d > 9) $d -= 9; } $sum += $d; $alt = !$alt; }
    return $sum % 10 === 0;
}
if (PAYMENT_MODE === 'demo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $num  = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $name = trim($_POST['card_name'] ?? '');
    $exp  = trim($_POST['expiry'] ?? '');
    $cvv  = preg_replace('/\D/', '', $_POST['cvv'] ?? '');
    $okExp = false;
    if (preg_match('#^(0[1-9]|1[0-2])\s*/\s*(\d{2})$#', $exp, $m)) {
        $okExp = (int)('20' . $m[2] . $m[1]) >= (int)date('Ym');
    }
    if ($name === '' || strlen($num) < 13 || strlen($num) > 19 || !luhn_ok($num)) $error = 'Invalid card details.';
    elseif (!$okExp)                                  $error = 'Card expiry is invalid or in the past.';
    elseif (strlen($cvv) < 3 || strlen($cvv) > 4)    $error = 'Invalid security code.';
    elseif ($num === '4000000000000002') {            // classic "declined" test card
        record_failed_payment((int)$order['id'], 'demo', 'DEMO-DECLINED', (float)$order['total'], 'card ending ' . substr($num, -4));
        $error = 'Your card was declined (test card). Try 4242 4242 4242 4242.';
    } else {
        $ref = 'DEMO-' . strtoupper(bin2hex(random_bytes(5)));
        if (mark_order_paid((int)$order['id'], 'demo', $ref, (float)$order['total'], 'card ending ' . substr($num, -4))) {
            cart_clear();
            redirect('order_success.php?order=' . (int)$order['id']);
        }
        $error = 'Payment could not be recorded. Please try again.';
    }
}

$pageTitle = 'Payment';
require __DIR__ . '/includes/header.php';
echo page_banner('Payment', ['Cart' => url('cart.php')]);
?>
<div class="container"><?= steps(3) ?>
  <div class="checkout-grid">
    <div>
        <h2>Pay for order <?= e($order['order_no']) ?></h2>
        <div class="pay-box">
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <?php if (PAYMENT_MODE === 'paypal'): ?>
            <?php if (!paypal_configured()): ?>
                <div class="alert error">PayPal is not configured. Add your sandbox Client ID and Secret in config.php (see README).</div>
            <?php else: ?>
                <p>Pay securely with PayPal or a card via PayPal.</p><br>
                <div id="paypal-buttons"></div>
                <div id="pp-msg" class="alert error" style="display:none"></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="hint"><b>Demo gateway.</b> Use test card <b>4242 4242 4242 4242</b>, any future expiry (e.g. 12/30) and any 3-digit CVV. Use <b>4000 0000 0000 0002</b> to simulate a declined card.</div>
            <div class="cc" id="cc">
                <span class="brand-tag">Taprobellelk Pay</span><div class="chip"></div>
                <div class="num" id="ccNum">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;</div>
                <div class="meta"><span id="ccName">Your name</span><span id="ccExp">MM/YY</span></div>
            </div>
            <form method="post" autocomplete="off">
                <?= csrf_field() ?>
                <label>Name on card</label><input type="text" name="card_name" id="inName" required>
                <label>Card number</label><input type="text" name="card_number" id="inNum" inputmode="numeric" placeholder="4242 4242 4242 4242" maxlength="23" required>
                <div class="row-inline">
                    <div style="flex:1"><label>Expiry (MM/YY)</label><input type="text" name="expiry" id="inExp" placeholder="12/30" maxlength="5" required></div>
                    <div style="flex:1"><label>CVV</label><input type="password" name="cvv" inputmode="numeric" maxlength="4" required></div>
                </div>
                <button type="submit" class="btn">Pay <?= money($order['total']) ?></button>
            </form>
        <?php endif; ?>
        </div>
    </div>
    <div>
        <h2>Order summary</h2>
        <div class="summary-box">
            <table>
            <?php foreach ($items as $it): ?>
                <tr><td><?= e($it['name']) ?> × <?= (int)$it['qty'] ?><br><small class="cart-opts"><?= e(describe_opts(json_decode($it['options'] ?: '[]', true) ?: [])) ?></small></td><td><?= money($it['unit_price'] * $it['qty']) ?></td></tr>
            <?php endforeach; ?>
                <tr><td>Subtotal</td><td><?= money($order['subtotal']) ?></td></tr>
                <tr><td>Tax</td><td><?= money($order['tax']) ?></td></tr>
                <tr><td><b>Total (<?= e($order['currency']) ?>)</b></td><td><b><?= money($order['total']) ?></b></td></tr>
            </table>
            <hr><p><small>Ship to: <?= e($order['full_name']) ?>, <?= e($order['address']) ?>, <?= e($order['city']) ?>, <?= e($order['country']) ?></small></p>
        </div>
    </div>
  </div>
</div>

<?php
ob_start();
if (PAYMENT_MODE === 'paypal' && paypal_configured()): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= e(PAYPAL_CLIENT_ID) ?>&currency=<?= e(CURRENCY) ?>&intent=capture"></script>
<script>
    var ORDER_ID = <?= (int)$order['id'] ?>, CSRF = <?= json_encode(csrf_token()) ?>;
    function showErr(m) { var b = document.getElementById("pp-msg"); b.textContent = m; b.style.display = "block"; }
    function post(url, body) {
        return fetch(url, {method: "POST", headers: {"Content-Type": "application/json", "X-CSRF-Token": CSRF}, body: JSON.stringify(body)})
            .then(function (r) { return r.json(); });
    }
    paypal.Buttons({
        createOrder: function () {
            return post("<?= url('api/paypal_create.php') ?>", {order_id: ORDER_ID}).then(function (d) {
                if (!d.ok) { showErr(d.error || "Could not start payment."); throw new Error(d.error); }
                return d.paypal_order_id;
            });
        },
        onApprove: function (data) {
            return post("<?= url('api/paypal_capture.php') ?>", {order_id: ORDER_ID, paypal_order_id: data.orderID}).then(function (d) {
                if (d.ok) window.location = d.redirect; else showErr(d.error || "Payment failed.");
            });
        },
        onCancel: function () { showErr("Payment cancelled. You can try again."); },
        onError: function () { showErr("Something went wrong with PayPal. Please try again."); }
    }).render("#paypal-buttons");
</script>
<?php endif;
if (PAYMENT_MODE === 'demo'): ?>
<script>
    (function () {
        var n = document.getElementById("inNum"), nm = document.getElementById("inName"), ex = document.getElementById("inExp");
        n.addEventListener("input", function () {
            var d = n.value.replace(/\D/g, "").slice(0, 19); n.value = d.replace(/(.{4})/g, "$1 ").trim();
            var pad = (d + "................").slice(0, 16).replace(/\./g, "\u2022"); document.getElementById("ccNum").textContent = pad.replace(/(.{4})/g, "$1 ").trim();
        });
        nm.addEventListener("input", function () { document.getElementById("ccName").textContent = nm.value || "Your name"; });
        ex.addEventListener("input", function () {
            var d = ex.value.replace(/\D/g, "").slice(0, 4); ex.value = d.length > 2 ? d.slice(0, 2) + "/" + d.slice(2) : d;
            document.getElementById("ccExp").textContent = ex.value || "MM/YY";
        });
    })();
</script>
<?php endif;
$extraScripts = ob_get_clean();
require __DIR__ . '/includes/footer.php'; ?>
