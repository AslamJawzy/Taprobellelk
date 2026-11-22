<?php
/** Installation check - open http://localhost/<folder>/check.php  (localhost only). Delete before hosting online. */
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) { http_response_code(403); die('check.php only works on localhost.'); }
require __DIR__ . '/config.php';

$rows = [];
function t(string $name, bool $ok, string $detail = '', string $fix = '') { global $rows; $rows[] = [$name, $ok, $detail, $fix]; }

t('PHP version 8.0 or newer', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION, 'Install a current XAMPP (PHP 8.x).');
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'json', 'curl'] as $ext)
    t("PHP extension: $ext", extension_loaded($ext), '', "Open C:\\xampp\\php\\php.ini, remove the ';' before extension=$ext, restart Apache.");

$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
t('Site URL detected correctly', strcasecmp(BASE_URL, $uri) === 0, "BASE_URL = " . BASE_URL . "   |   you opened: $uri", 'Open config.php and set BASE_URL by hand, e.g. define(\'BASE_URL\', \'/Taprobellelk\');');
t('Project is not nested in an extra folder', is_file(__DIR__ . '/includes/bootstrap.php') && is_file(__DIR__ . '/assets/site.css'), __DIR__, 'Move the files so that index.php sits directly inside the folder you open in the browser.');

$pdo = null;
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    t('MySQL server reachable', true, DB_HOST . ' as ' . DB_USER);
    $has = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'")->fetch();
    t('Database "' . DB_NAME . '" exists', (bool)$has, '', 'phpMyAdmin > Import > database/taprobellelk.sql');
    if ($has) $pdo->exec('USE `' . DB_NAME . '`');
} catch (Throwable $e) {
    t('MySQL server reachable', false, $e->getMessage(), 'Start MySQL in the XAMPP Control Panel. If you set a MySQL password, put it in config.php (DB_PASS).');
}

if ($pdo && !empty($has)) {
    foreach (['users', 'products', 'metal_rates', 'jewellery_types', 'orders', 'order_items', 'payments', 'messages', 'pages'] as $tb) {
        $ok = (bool)$pdo->query("SHOW TABLES LIKE '$tb'")->fetch();
        t("Table: $tb", $ok, '', $tb === 'messages' || $tb === 'pages' ? 'Run database/upgrade_v4.sql in phpMyAdmin (SQL tab).' : 'Import database/taprobellelk.sql');
    }
    try {
        $c = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'")->fetch();
        t('users.is_admin column', (bool)$c, '', 'Run database/upgrade_v4.sql in phpMyAdmin.');
        $adm = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_admin = 1')->fetchColumn();
        t('An admin account exists', $adm > 0, "$adm admin(s)", 'Run database/upgrade_v4.sql (creates admin / Admin@12345).');
        $np = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        t('Products in database', $np > 0, "$np products", 'Import database/taprobellelk.sql or add products in Admin.');
        // every product image must exist
        $missing = [];
        foreach ($pdo->query('SELECT name, image FROM products') as $p) if (!is_file(__DIR__ . '/Images/' . $p['image'])) $missing[] = $p['image'];
        t('All product images exist', !$missing, $missing ? 'Missing: ' . implode(', ', array_slice($missing, 0, 5)) : '', 'Copy your Images folder into the project folder (same level as index.php).');
    } catch (Throwable $e) { t('Database content check', false, $e->getMessage()); }
}

foreach (['logo.png', 'logo-mark.png'] as $f) t("Image: Images/$f", is_file(__DIR__ . "/Images/$f"), '', 'Make sure the Images folder is inside the project folder.');
t('Images folder writable (admin uploads)', is_dir(__DIR__ . '/Images') && is_writable(__DIR__ . '/Images'), '', 'Right-click Images > Properties > uncheck Read-only (only needed for uploading product photos).');
t('Payment mode', true, PAYMENT_MODE === 'paypal' ? (PAYPAL_CLIENT_ID && PAYPAL_SECRET ? 'PayPal sandbox (keys set)' : 'PayPal selected but KEYS ARE EMPTY') : 'Demo gateway (no keys needed)');
if (PAYMENT_MODE === 'paypal') t('PayPal keys filled in', PAYPAL_CLIENT_ID !== '' && PAYPAL_SECRET !== '', '', 'Paste your sandbox Client ID and Secret in config.php.');
t('Sessions work', session_start() && session_id() !== '', '', 'Check session.save_path in php.ini.');

$bad = count(array_filter($rows, fn($r) => !$r[1]));
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Taprobellelk install check</title>
<style>body{font-family:Segoe UI,Arial,sans-serif;background:#f3f4fb;margin:0;padding:30px;color:#1b1f3a}.box{max-width:900px;margin:auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
h1{margin-top:0}.sum{padding:12px 16px;border-radius:10px;margin:14px 0 22px;font-weight:600}.good{background:#e8f6ed;color:#1b6b34}.warn{background:#fdeaea;color:#a12a2a}
table{width:100%;border-collapse:collapse}td{padding:9px 8px;border-bottom:1px solid #eee;vertical-align:top;font-size:14px}.ok{color:#2e9e55;font-weight:700}.no{color:#d6405a;font-weight:700}small{color:#666}.fix{color:#a12a2a;font-size:13px}</style></head><body><div class="box">
<h1>Taprobellelk install check</h1>
<div class="sum <?= $bad ? 'warn' : 'good' ?>"><?= $bad ? "$bad problem(s) found - fix the red items below, then refresh this page." : 'Everything looks good. You can open the site.' ?></div>
<table><?php foreach ($rows as [$n, $ok, $d, $fx]): ?>
<tr><td style="width:34px" class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? '&#10004;' : '&#10008;' ?></td><td><b><?= htmlspecialchars($n) ?></b><?php if ($d): ?><br><small><?= htmlspecialchars($d) ?></small><?php endif; ?>
<?php if (!$ok && $fx): ?><br><span class="fix">How to fix: <?= htmlspecialchars($fx) ?></span><?php endif; ?></td></tr><?php endforeach; ?></table>
<p><small>PHP <?= PHP_VERSION ?> &middot; <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '') ?> &middot; Delete this file before putting the site online.</small></p>
</div></body></html>
