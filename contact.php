<?php
require_once __DIR__ . '/includes/bootstrap.php';
$err = ''; $f = ['name' => '', 'email' => '', 'message' => ''];
$u = current_user();
if ($u) { $f['name'] = $u['username']; $f['email'] = $u['email']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_or_die();
    foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));
    if (!empty($_POST['website']))                               { redirect('contact.php'); }   // honeypot: bots fill this
    elseif ($f['name'] === '' || mb_strlen($f['name']) > 100)   $err = 'Please enter your name.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))     $err = 'Please enter a valid email address.';
    elseif (mb_strlen($f['message']) < 10 || mb_strlen($f['message']) > 3000) $err = 'Your message should be between 10 and 3000 characters.';
    elseif (($_SESSION['last_msg'] ?? 0) > time() - 20)          $err = 'Please wait a few seconds before sending another message.';
    else {
        try {
            db()->prepare('INSERT INTO messages (name,email,message) VALUES (?,?,?)')->execute([$f['name'], $f['email'], $f['message']]);
            $_SESSION['last_msg'] = time();
            flash('success', 'Thank you! Your message has been sent. We will reply by email.');
            redirect('contact.php');
        } catch (Throwable $e) {
            $err = 'Sorry, your message could not be saved. Please try again later.';
        }
    }
}
$pageTitle = 'Contact Us';
require __DIR__ . '/includes/header.php';
echo page_banner('Contact Us', [], 'Questions about a gemstone or a custom design? Send us a message.');
?>
<div class="container">
    <div class="contact-grid">
        <div class="form-card">
            <h2 style="margin-bottom:6px">Get in touch</h2>
            <p>We usually reply by email.</p>
            <?php if ($err): ?><br><div class="alert error"><?= e($err) ?></div><?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                <label for="cn">Your name</label><input id="cn" type="text" name="name" value="<?= e($f['name']) ?>" required maxlength="100">
                <label for="ce">Your email</label><input id="ce" type="email" name="email" value="<?= e($f['email']) ?>" required maxlength="120">
                <label for="cm">Your message</label><textarea id="cm" name="message" rows="6" required maxlength="3000"><?= e($f['message']) ?></textarea>
                <button class="btn" type="submit">Send message &#8594;</button>
            </form>
        </div>
        <div><img src="<?= img('right_img.png') ?>" alt="" loading="lazy"></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
