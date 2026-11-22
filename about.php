<?php
$pageTitle = 'About Us';
require __DIR__ . '/includes/header.php';
echo page_banner('About Us', [], 'Sri Lanka\'s gemstone heritage, brought to you online.');
?>
<div class="container">
    <div class="about-grid">
        <div>
            <h2 style="margin-bottom:16px">Welcome to Taprobellelk</h2>
            <p>Your trusted destination for premium natural gemstones and custom-made jewellery in Sri Lanka. We offer a carefully selected collection of high-quality gemstones, including Blue Sapphires, Rubies, Padparadscha Sapphires, Yellow Sapphires and other precious stones sourced from trusted suppliers.</p>
            <p>Our mission is to help customers create unique jewellery that reflects their personal style. On our website you can browse gemstones, customise jewellery by choosing different design options, and place orders conveniently from anywhere.</p>
            <p>We combine Sri Lanka's rich gemstone heritage with modern technology to provide a secure, reliable and personalised online shopping experience.</p>
            <a class="btn" href="<?= url('gemstones.php') ?>">Browse gemstones &#8594;</a>
        </div>
        <div><img src="<?= img('gold ring.jpg') ?>" alt="Gold ring" loading="lazy"></div>
    </div>

    <h2 class="title">Why Taprobellelk</h2>
    <p class="sub-title">What we promise</p>
    <div class="features">
        <div class="feature"><i class="fa fa-diamond"></i><h3>Genuine gemstones</h3><p>Natural stones from trusted suppliers across Sri Lanka.</p></div>
        <div class="feature"><i class="fa fa-paint-brush"></i><h3>Exceptional craftsmanship</h3><p>Jewellery made to your chosen design, metal and size.</p></div>
        <div class="feature"><i class="fa fa-calculator"></i><h3>Transparent pricing</h3><p>An instant estimate showing gem, metal, making and setting costs.</p></div>
        <div class="feature"><i class="fa fa-lock"></i><h3>Secure shopping</h3><p>Safe online payment and a clear record of every order.</p></div>
    </div>

    <div class="cta-band reveal">
        <h2>Ready to create something unique?</h2>
        <p>Choose a gemstone and design your own piece in minutes.</p>
        <a class="btn btn-gold" href="<?= url('custom_made.php') ?>">Start designing &#8594;</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
