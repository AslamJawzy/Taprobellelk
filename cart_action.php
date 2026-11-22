<?php
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('cart.php');
csrf_or_die();

$action = $_POST['action'] ?? '';
switch ($action) {
    case 'add':
        [$ok, $msg] = cart_add((int)($_POST['id'] ?? 0),
            ['jtype' => $_POST['jtype'] ?? '', 'size' => (int)($_POST['size'] ?? 0), 'metal' => $_POST['metal'] ?? ''],
            (int)($_POST['qty'] ?? 1));
        flash($ok ? 'success' : 'error', $msg);
        if ($ok) redirect('cart.php');
        redirect('product.php?id=' . (int)($_POST['id'] ?? 0));
    case 'update':
        cart_update((string)($_POST['key'] ?? ''), (int)($_POST['qty'] ?? 1));
        flash('info', 'Cart updated.');
        break;
    case 'remove':
        cart_remove((string)($_POST['key'] ?? ''));
        flash('info', 'Item removed.');
        break;
    case 'clear':
        cart_clear();
        break;
}
redirect('cart.php');
