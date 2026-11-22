<?php
// Live price estimate for the product page (read-only, no state change).
require_once __DIR__ . '/../includes/bootstrap.php';

$p = get_product((int)($_GET['id'] ?? 0));
if (!$p) json_out(['ok' => false, 'error' => 'Product not found.'], 404);

$est = estimate_price($p, [
    'jtype' => $_GET['jtype'] ?? '',
    'size'  => (int)($_GET['size'] ?? 0),
    'metal' => $_GET['metal'] ?? '',
]);

$rows = [];
foreach ($est['breakdown'] as $label => $amt) $rows[] = ['label' => $label, 'amount' => $amt];
json_out(['ok' => $est['ok'], 'error' => $est['error'], 'rows' => $rows, 'total' => $est['total'], 'total_fmt' => money($est['total'])]);
