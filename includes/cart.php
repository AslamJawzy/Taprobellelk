<?php
/** Session-based cart. The browser never sends prices - they are recomputed from the DB every time. */

function cart_key(int $pid, array $opts): string { ksort($opts); return md5($pid . json_encode($opts)); }

function cart_add(int $pid, array $optsIn, int $qty): array {
    $p = get_product($pid);
    if (!$p) return [false, 'Product not found.'];
    $est = estimate_price($p, $optsIn);
    if (!$est['ok']) return [false, $est['error']];
    $qty = max(1, min(10, $qty));
    $key = cart_key($pid, $est['opts']);
    $have = $_SESSION['cart'][$key]['qty'] ?? 0;
    $new  = min($have + $qty, max(0, (int)$p['stock']));
    if ($new < 1) return [false, 'Sorry, this item is out of stock.'];
    $_SESSION['cart'][$key] = ['pid' => $pid, 'opts' => $est['opts'], 'qty' => $new];
    return [true, $new < $have + $qty ? 'Added to cart (limited stock - quantity adjusted).' : 'Added to cart.'];
}
function cart_update(string $key, int $qty): void {
    if (!isset($_SESSION['cart'][$key])) return;
    if ($qty < 1) { unset($_SESSION['cart'][$key]); return; }
    $p = get_product($_SESSION['cart'][$key]['pid']);
    $_SESSION['cart'][$key]['qty'] = $p ? min($qty, 10, max(1, (int)$p['stock'])) : 1;
}
function cart_remove(string $key): void { unset($_SESSION['cart'][$key]); }
function cart_clear(): void { unset($_SESSION['cart']); }
function cart_count(): int {
    $n = 0; foreach ($_SESSION['cart'] ?? [] as $i) $n += (int)$i['qty']; return $n;
}

/** @return array ['lines'=>[...], 'subtotal','tax','total','problems'=>[...]] */
function cart_totals(): array {
    $lines = []; $subtotal = 0.0; $problems = [];
    foreach (($_SESSION['cart'] ?? []) as $key => $item) {
        $p = get_product((int)$item['pid']);
        if (!$p) { unset($_SESSION['cart'][$key]); continue; }
        $est = estimate_price($p, ['jtype' => $item['opts']['type'] === 'loose' ? '' : ($p['kind'] === 'gem' ? $item['opts']['type'] : ''),
                                   'metal' => $item['opts']['metal'] ?? '', 'size' => $item['opts']['size'] ?? 0]);
        if (!$est['ok']) { unset($_SESSION['cart'][$key]); continue; }
        $qty = (int)$item['qty'];
        if ($qty > (int)$p['stock']) { $problems[] = $p['name'] . ' - only ' . (int)$p['stock'] . ' available.'; $qty = max(0, (int)$p['stock']); }
        $line = round($est['total'] * $qty, 2);
        $subtotal += $line;
        $lines[] = ['key' => $key, 'product' => $p, 'qty' => $qty, 'opts' => $est['opts'], 'desc' => describe_opts($est['opts']),
                    'unit' => $est['total'], 'line' => $line, 'breakdown' => $est['breakdown']];
    }
    $subtotal = round($subtotal, 2);
    $tax = round($subtotal * TAX_RATE, 2);
    return ['lines' => $lines, 'subtotal' => $subtotal, 'tax' => $tax, 'total' => round($subtotal + $tax, 2), 'problems' => $problems];
}
