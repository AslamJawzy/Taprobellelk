<?php
/** Order creation + payment recording. */

function new_order_no(): string { return 'TB' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); }

/** Creates a pending order from the current cart. Returns order id or throws RuntimeException with a user message. */
function create_order_from_cart(int $userId, array $ship): int {
    $cart = cart_totals();
    if (!$cart['lines']) throw new RuntimeException('Your cart is empty.');
    if ($cart['problems']) throw new RuntimeException(implode(' ', $cart['problems']));
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO orders (order_no,user_id,subtotal,tax,total,currency,status,full_name,phone,address,city,postal_code,country)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([new_order_no(), $userId, $cart['subtotal'], $cart['tax'], $cart['total'], CURRENCY, 'pending',
                       $ship['full_name'], $ship['phone'], $ship['address'], $ship['city'], $ship['postal_code'], $ship['country']]);
        $oid = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO order_items (order_id,product_id,name,image,qty,unit_price,options,breakdown) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($cart['lines'] as $l) {
            $ins->execute([$oid, $l['product']['id'], $l['product']['name'], $l['product']['image'], $l['qty'], $l['unit'],
                           json_encode($l['opts']), json_encode($l['breakdown'])]);
        }
        $pdo->commit();
        return $oid;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw new RuntimeException('Could not create your order. Please try again.');
    }
}

function get_order_for_user(int $orderId, int $userId): ?array {
    $st = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $st->execute([$orderId, $userId]);
    return $st->fetch() ?: null;
}
function get_order_items(int $orderId): array {
    $st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $st->execute([$orderId]);
    return $st->fetchAll();
}

/** Marks the order paid exactly once (safe to call twice), records the payment, reduces stock. */
function mark_order_paid(int $orderId, string $provider, string $ref, float $amount, string $raw = ''): bool {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
        $st->execute([$orderId]);
        $o = $st->fetch();
        if (!$o) { $pdo->rollBack(); return false; }
        if ($o['status'] === 'paid' || $o['status'] === 'shipped') { $pdo->commit(); return true; }

        $pdo->prepare('INSERT INTO payments (order_id,provider,provider_ref,amount,currency,status,raw_response) VALUES (?,?,?,?,?,?,?)')
            ->execute([$orderId, $provider, $ref, $amount, $o['currency'], 'completed', $raw]);
        $pdo->prepare("UPDATE orders SET status='paid', paid_at=NOW() WHERE id = ?")->execute([$orderId]);

        $upd = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
        foreach (get_order_items($orderId) as $it) $upd->execute([$it['qty'], $it['product_id']]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('mark_order_paid failed: ' . $e->getMessage());
        return false;
    }
}

function record_failed_payment(int $orderId, string $provider, string $ref, float $amount, string $raw = ''): void {
    db()->prepare('INSERT INTO payments (order_id,provider,provider_ref,amount,currency,status,raw_response) VALUES (?,?,?,?,?,?,?)')
        ->execute([$orderId, $provider, $ref, $amount, CURRENCY, 'failed', $raw]);
}

function amount_str(float $n): string { return number_format($n, 2, '.', ''); }
