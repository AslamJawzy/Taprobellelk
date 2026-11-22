<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/paypal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'POST only'], 405);
if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) json_out(['ok' => false, 'error' => 'Session expired. Refresh the page.'], 419);
$user = current_user();
if (!$user) json_out(['ok' => false, 'error' => 'Please log in.'], 401);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$order = get_order_for_user((int)($in['order_id'] ?? 0), (int)$user['id']);
if (!$order) json_out(['ok' => false, 'error' => 'Order not found.'], 404);
if (in_array($order['status'], ['paid', 'shipped'], true)) json_out(['ok' => true, 'redirect' => url('order_success.php?order=' . $order['id'])]);

// the PayPal order being captured must be the one WE created for this order
$ppId = (string)($in['paypal_order_id'] ?? '');
if ($ppId === '' || !hash_equals((string)$order['paypal_order_id'], $ppId)) json_out(['ok' => false, 'error' => 'Payment reference mismatch.'], 400);

try {
    $r = paypal_capture_order($ppId);
    $cap = $r['data']['purchase_units'][0]['payments']['captures'][0] ?? null;

    $valid = $r['code'] < 300
          && ($r['data']['status'] ?? '') === 'COMPLETED'
          && $cap && ($cap['status'] ?? '') === 'COMPLETED'
          && ($cap['amount']['currency_code'] ?? '') === $order['currency']
          && ($cap['amount']['value'] ?? '') === amount_str((float)$order['total']);

    if (!$valid) {
        record_failed_payment((int)$order['id'], 'paypal', $ppId, (float)$order['total'], $r['raw']);
        json_out(['ok' => false, 'error' => 'PayPal did not complete the payment. You have not been charged. Please try again.'], 402);
    }
    if (!mark_order_paid((int)$order['id'], 'paypal', $cap['id'], (float)$order['total'], $r['raw'])) {
        json_out(['ok' => false, 'error' => 'Payment received but order update failed. Contact support with order ' . $order['order_no'] . '.'], 500);
    }
    cart_clear();
    json_out(['ok' => true, 'redirect' => url('order_success.php?order=' . $order['id'])]);
} catch (Throwable $e) {
    error_log('paypal_capture: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Could not confirm payment with PayPal.'], 502);
}
