<?php
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return '$' . number_format((float)$n, 2); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function img(string $file): string { return url('Images/' . rawurlencode($file)); }

function redirect(string $path): void {
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

// ---- CSRF ----
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(?string $t = null): bool {
    $t = $t ?? ($_POST['csrf'] ?? '');
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}
function csrf_or_die(): void {
    if (!csrf_ok()) { http_response_code(419); die('Security token expired. Go back, refresh the page and try again.'); }
}

// ---- Flash messages ----
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

// ---- Auth ----
function current_user(): ?array {
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT id, username, email, is_admin FROM users WHERE id = ?');
            $st->execute([$_SESSION['uid']]);
            $u = $st->fetch() ?: null;
        }
    }
    return $u;
}
function require_login(): array {
    $u = current_user();
    if (!$u) {
        flash('error', 'Please log in or register to continue.');
        redirect('account.php?next=' . urlencode(basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')));
    }
    return $u;
}
function require_admin(): array {
    $u = current_user();
    if (!$u) { flash('error', 'Please log in with your admin account.'); redirect('account.php'); }
    if (empty($u['is_admin'])) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;margin:60px">403 - Administrators only. <a href="' . e(url('index.php')) . '">Back to shop</a></h2>');
    }
    return $u;
}
function safe_next(?string $next): string {
    // only allow local page names like "checkout.php" or "pay.php?order=5"
    return ($next && preg_match('/^[A-Za-z0-9_\-]+\.php(\?[A-Za-z0-9_=&\-]*)?$/', $next)) ? $next : 'index.php';
}

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ---- UI helpers ----
function product_card(array $p): string {
    $link = url('product.php?id=' . (int)$p['id']);
    if ($p['kind'] === 'gem') $tag = $p['carat'] ? rtrim(rtrim(number_format((float)$p['carat'], 2), '0'), '.') . ' ct' : 'Natural';
    else $tag = $p['metal_code'] === 'silver' ? 'Silver' : strtoupper($p['metal_code']) . ' Gold';
    $out = '<div><div class="p-card">'
         . '<a class="p-img" href="' . e($link) . '"><span class="p-tag">' . e($tag) . '</span>'
         . '<img src="' . e(img($p['image'])) . '" alt="' . e($p['name']) . '" loading="lazy">'
         . '<span class="p-view">View details &#8594;</span></a>'
         . '<div class="p-body"><span class="p-cat">' . e($p['category']) . '</span>'
         . '<h4><a href="' . e($link) . '">' . e($p['name']) . '</a></h4>'
         . '<p class="p-price">' . money($p['price']) . '</p></div>';
    if ((int)$p['stock'] < 1) $out .= '<div class="p-sold">Sold out</div>';
    return $out . '</div></div>';
}
function steps(int $now): string {
    $labels = ['Cart', 'Checkout', 'Payment', 'Done'];
    $h = '<div class="steps">';
    foreach ($labels as $i => $l) $h .= '<div class="' . ($i + 1 < $now ? 'done' : ($i + 1 === $now ? 'now' : '')) . '">' . $l . '</div>';
    return $h . '</div>';
}
function products_where(string $where, array $params = [], string $order = 'id'): array {
    $st = db()->prepare("SELECT * FROM products WHERE is_active = 1 AND $where ORDER BY $order");
    $st->execute($params);
    return $st->fetchAll();
}

// ---- Layout helpers ----
function page_banner(string $title, array $crumbs = [], string $sub = ''): string {
    $h = '<section class="page-banner"><div class="container"><div class="crumbs"><a href="' . e(url('index.php')) . '">Home</a>';
    foreach ($crumbs as $label => $href) $h .= '<span>/</span>' . ($href ? '<a href="' . e($href) . '">' . e($label) . '</a>' : e($label));
    $h .= '<span>/</span>' . e($title) . '</div><h1>' . e($title) . '</h1>';
    if ($sub !== '') $h .= '<p>' . e($sub) . '</p>';
    return $h . '</div></section>';
}
/** Plain-text pages: "## Heading" lines and blank-line separated paragraphs. Everything is escaped (no raw HTML). */
function render_content(string $text): string {
    $out = '';
    foreach (preg_split('/\R{2,}/', trim($text)) as $block) {
        $block = trim($block);
        if ($block === '') continue;
        if (strncmp($block, '## ', 3) === 0) {
            $parts = preg_split('/\R/', $block, 2);
            $out .= '<h2>' . e(substr($parts[0], 3)) . '</h2>';
            if (!empty($parts[1])) $out .= '<p>' . nl2br(e(trim($parts[1]))) . '</p>';
        } else $out .= '<p>' . nl2br(e($block)) . '</p>';
    }
    return $out;
}
function footer_pages(): array {
    try {
        return db()->query('SELECT slug, title FROM pages WHERE is_published = 1 ORDER BY sort_order, title')->fetchAll();
    } catch (Throwable $e) {   // pages table not created yet (old database) - fall back to nothing
        return [];
    }
}
