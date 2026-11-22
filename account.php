<?php
require_once __DIR__ . '/includes/bootstrap.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
$errors = ['login' => '', 'register' => ''];
$active = 'login';

if (current_user() && $_SERVER['REQUEST_METHOD'] !== 'POST') redirect(current_user()['is_admin'] ? 'admin/index.php' : 'orders.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    $act = $_POST['action'] ?? '';
    $pdo = db();

    if ($act === 'login') {
        $login = trim($_POST['username'] ?? '');
        $st = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $st->execute([$login, $login]);
        $u = $st->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            flash('success', 'Welcome back, ' . $u['username'] . '!');
            redirect(((int)$u['is_admin'] && $next === 'index.php') ? 'admin/index.php' : $next);
        }
        $errors['login'] = 'Invalid username or password.';
    } elseif ($act === 'register') {
        $active = 'register';
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $pass     = $_POST['password'] ?? '';
        if (!preg_match('/^[A-Za-z0-9_.\-]{3,30}$/', $username))       $errors['register'] = 'Username: 3-30 letters, numbers, . _ -';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))            $errors['register'] = 'Enter a valid email address.';
        elseif (strlen($pass) < 8)                                     $errors['register'] = 'Password must be at least 8 characters.';
        else {
            $st = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
            $st->execute([$username, $email]);
            if ($st->fetch()) $errors['register'] = 'Username or email is already registered.';
            else {
                $pdo->prepare('INSERT INTO users (username,email,password_hash) VALUES (?,?,?)')
                    ->execute([$username, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                $_SESSION['uid'] = (int)$pdo->lastInsertId();
                flash('success', 'Account created. Welcome, ' . $username . '!');
                redirect($next);
            }
        }
    }
}

$pageTitle = 'Login / Register';
require __DIR__ . '/includes/header.php';
echo page_banner('My Account', [], 'Log in or create an account to checkout and track your orders.');
?>
<div class="container">
    <div class="auth-wrap">
        <div class="auth-art"><img src="<?= img('white sapphire gold ring.jpg') ?>" alt="White sapphire gold ring" loading="lazy"></div>
        <div class="auth-card">
            <div class="auth-tabs">
                <button type="button" data-form="LoginForm" class="<?= $active === 'login' ? 'on' : '' ?>">Login</button>
                <button type="button" data-form="RegForm" class="<?= $active === 'register' ? 'on' : '' ?>">Register</button>
            </div>

            <form id="LoginForm" class="<?= $active === 'login' ? 'on' : '' ?>" method="post" action="<?= url('account.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="next" value="<?= e($next) ?>">
                <h2>Welcome back</h2><p style="margin-bottom:16px">Log in with your username or email.</p>
                <input type="text" name="username" placeholder="Username or email" required autocomplete="username">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                <?php if ($errors['login']): ?><div class="err"><?= e($errors['login']) ?></div><?php endif; ?>
                <button type="submit" class="btn btn-block">Login</button>
            </form>

            <form id="RegForm" class="<?= $active === 'register' ? 'on' : '' ?>" method="post" action="<?= url('account.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="next" value="<?= e($next) ?>">
                <h2>Create account</h2><p style="margin-bottom:16px">It only takes a moment.</p>
                <input type="text" name="username" placeholder="Username" required value="<?= $active === 'register' ? e($_POST['username'] ?? '') : '' ?>" autocomplete="username">
                <input type="email" name="email" placeholder="Email" required value="<?= $active === 'register' ? e($_POST['email'] ?? '') : '' ?>" autocomplete="email">
                <input type="password" name="password" placeholder="Password (min 8 characters)" required autocomplete="new-password">
                <?php if ($errors['register']): ?><div class="err"><?= e($errors['register']) ?></div><?php endif; ?>
                <button type="submit" class="btn btn-block">Register</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
