</main>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="<?= img('logo.png') ?>" alt="<?= e(SITE_NAME) ?>">
                <p>Natural Ceylon sapphires, rubies and custom-made jewellery, crafted in Sri Lanka and shipped to you.</p>
            </div>
            <div>
                <h3>Shop</h3>
                <ul>
                    <li><a href="<?= url('gemstones.php') ?>">All gemstones</a></li>
                    <li><a href="<?= url('gemstones.php?cat=Blue+Sapphire') ?>">Blue sapphires</a></li>
                    <li><a href="<?= url('custom_made.php') ?>">Custom-made jewellery</a></li>
                    <li><a href="<?= url('index.php#jewellery') ?>">Finished jewellery</a></li>
                    <li><a href="<?= url('cart.php') ?>">Your cart</a></li>
                </ul>
            </div>
            <div>
                <h3>Help</h3>
                <ul>
                    <?php foreach (footer_pages() as $pg): ?><li><a href="<?= url('page.php?slug=' . urlencode($pg['slug'])) ?>"><?= e($pg['title']) ?></a></li><?php endforeach; ?>
                    <li><a href="<?= url('contact.php') ?>">Contact us</a></li>
                    <li><a href="<?= url('orders.php') ?>">My orders</a></li>
                </ul>
            </div>
            <div>
                <h3>Follow us</h3>
                <div class="socials">
                    <a href="https://www.facebook.com/share/1VHsuyfLVG/" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                    <a href="https://www.instagram.com/taprobellelk?srtk=MW5pMXo0M3JkcnpudQ" aria-label="Instagram"><i class="fa fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa fa-youtube-play"></i></a>
                    <a href="https://wa.me/message/I5QAY72XIBOAF1" aria-label="Whatsapp"><i class="fa fa-whatsapp"></i></a>
                </div>
                <h3 style="font-size:15px">Get our app</h3>
                <div class="app-logo"><img src="<?= img('play-store.png') ?>" alt="Google Play"><img src="<?= img('app-store.png') ?>" alt="App Store"></div>
            </div>
        </div>
        <div class="footer-bottom">&copy; <?= date('Y') ?> Taprobellelk &mdash; Sri Lanka. All rights reserved.</div>
    </div>
</footer>
<button class="to-top" aria-label="Back to top" type="button">&#8593;</button>
<script src="<?= url('assets/theme.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
