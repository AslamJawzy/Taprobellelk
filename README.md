# Taprobellelk - complete PHP + MySQL website with admin panel

## Quick start (XAMPP, Windows) - 4 steps
1. **Replace your old project.** Delete everything inside `C:\xampp\htdocs\Taprobellelk\` (old `.html` files, old `Style.css`),
   then extract this zip so that `index.php`, `Images`, `admin`, `includes` ... sit **directly inside** that folder
   (not in a second `Taprobellelk` folder inside it).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin > **Import** > choose `database/taprobellelk.sql` > **Go**.
   *(Already have the database from an earlier version and want to keep your users/orders? Don't import. Instead open the
   `taprobellelk` database > **SQL** tab, paste the contents of `database/upgrade_v4.sql` > Go.)*
4. Open **http://localhost/Taprobellelk/check.php**. It tests PHP, MySQL, tables, images, admin account and payment settings
   and tells you exactly how to fix anything red. When all green, open http://localhost/Taprobellelk/

The site URL is detected automatically, so the folder can have any name.

## Logins
| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `Admin@12345` |

Log in at *Login / Register*. Admins are taken to the admin dashboard. **Change the password at once:** Admin > Change password.
Customers register themselves on the same page.

## Admin panel (http://localhost/Taprobellelk/admin/)
* **Dashboard** - revenue, orders, orders to ship, customers, low stock, 7-day chart, top sellers, quick actions
* **Add product** (gold button, top of the sidebar) - gemstone or finished jewellery, price, stock, carat, description,
  choose an image from the `Images` folder **or upload a new photo**; extra photos named `name 1.jpg`, `name (2).jpg` form the gallery automatically
* **Products** - search, edit, change stock, hide/show
* **Orders** - search/filter, full detail (items, price breakdown, shipping, payment attempts), mark shipped / cancel
* **Customers** - order count, total spent, promote/demote admins
* **Messages** - inbox for the website Contact form (reply by email, mark read, delete)
* **Info pages** - edit the FAQ, Shipping & Returns, Privacy Policy, Terms; add your own pages. Published pages appear in the footer.
  **The starter text is a template - read it and edit it to match your real policies.**
* **Pricing rates** - metal prices and jewellery settings used by the live price estimator
* **Change password**

## Website pages
Home, Gemstones (filter + sort), Product (hover-zoom gallery + live price estimate), Custom-made, Cart, Checkout, Payment,
Order confirmation, My Orders, Login/Register, About, Contact, FAQ / Shipping / Privacy / Terms, 404 page.
Old links such as `Gemstones.html` or `Cart.html` automatically redirect to the new pages (`.htaccess`).

## Payment
`config.php` > `PAYMENT_MODE`
* `'demo'` (default): built-in simulated card gateway. Test card `4242 4242 4242 4242`, any future expiry, any CVV. `4000 0000 0000 0002` = declined.
* `'paypal'`: **PayPal Sandbox (free)**. Create an app at https://developer.paypal.com > Apps & Credentials > Sandbox, paste
  `PAYPAL_CLIENT_ID` and `PAYPAL_SECRET` in `config.php`, and pay with the sandbox *Personal* test account (Testing Tools > Sandbox Accounts).
  *cURL SSL error on XAMPP?* Download https://curl.se/ca/cacert.pem to `C:\xampp\php\extras\ssl\`, set `curl.cainfo` in `php.ini`, restart Apache.

## Price estimator
`total = gem price + (grams x metal rate) + making charge % + setting fee`; grams scale with ring size.
Rates are editable in Admin > Pricing rates. Prices are always calculated on the server.

## Security built in
Prepared statements, escaped output, CSRF tokens on every form, hashed passwords, admin-only area, validated image uploads,
server-side price/stock checks, PayPal capture verified (status, amount, currency, order id), contact-form spam trap and rate limit.

## Folder map
```
index.php gemstones.php product.php custom_made.php about.php contact.php page.php 404.php
cart.php checkout.php pay.php order_success.php orders.php account.php logout.php
admin/            admin panel          api/        price estimate + PayPal endpoints
includes/         header, footer, helpers, pricing, cart, orders, paypal
assets/site.css   the ONE stylesheet   assets/theme.js   animations     assets/admin.css
database/         taprobellelk.sql (fresh install)   upgrade_v4.sql (existing database)
Images/           all your photos + logo-mark.png    check.php   install check (localhost only)
```
Delete `check.php` before putting the site online. Change the `admin` password, `PAYMENT_MODE` keys and `DB_PASS` for a real deployment.
