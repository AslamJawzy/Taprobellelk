<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/paypal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'POST only'], 405);
if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) json_out(['ok' => false, 'error' => 'Session expired. Refresh the page.'], 419);
$user = current_user();
if (!$user) json_out(['ok' => false, 'error' => 'Please log in.'], 401);
if (PAYMENT_MODE !== 'paypal' || !paypal_configured()) json_out(['ok' => false, 'error' => 'PayPal not configured.'], 500);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$order = get_order_for_user((int)($in['order_id'] ?? 0), (int)$user['id']);
if (!$order || $order['status'] !== 'pending') json_out(['ok' => false, 'error' => 'Order not payable.'], 400);

try {
    // amount comes from OUR database, never from the browser
    $ppId = paypal_create_order($order);
    db()->prepare('UPDATE orders SET paypal_order_id = ? WHERE id = ?')->execute([$ppId, $order['id']]);
    json_out(['ok' => true, 'paypal_order_id' => $ppId]);
} catch (Throwable $e) {
    error_log('paypal_create: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => $e->getMessage()], 502);
}
