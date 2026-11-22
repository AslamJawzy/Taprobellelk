-- Upgrade for an EXISTING Taprobellelk database (keeps your users, orders and products).
-- Safe to run more than once. Made for XAMPP's MariaDB.
-- phpMyAdmin: click the `taprobellelk` database > SQL tab > paste this file > Go.
USE taprobellelk;

ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash;

-- Default administrator (only created if the username 'admin' does not exist yet)
--   username: admin     password: Admin@12345    -> CHANGE IT after first login
INSERT IGNORE INTO users (username,email,password_hash,is_admin) VALUES
('admin','admin@taprobellelk.lk','$2y$10$xSaZUX53U0GU.4bZQrkYc.FOqPKwQQXXXorRF6gMQ0dH/vhbig4Ym',1);
UPDATE users SET is_admin = 1 WHERE username = 'admin' AND email = 'admin@taprobellelk.lk';

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pages (
  slug VARCHAR(60) PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO pages (slug,title,content,sort_order) VALUES
('faq','Frequently Asked Questions','## Are your gemstones natural?
Yes. Taprobellelk specialises in natural gemstones, including Blue Sapphires, Rubies, Padparadscha, Yellow, Pink, Green and Purple Sapphires, sourced from trusted suppliers in Sri Lanka.

## Can I have a gemstone made into jewellery?
Yes. Open any gemstone, choose a jewellery type (ring, earrings, necklace or bracelet), the metal and purity, and your ring size if needed. The price estimate updates instantly as you choose.

## How is the custom-made price calculated?
The estimate is the gemstone price plus the metal cost (weight x current rate for the chosen purity), a making charge and a gem-setting fee. The total is shown before you add anything to your cart.

## Why can I only buy one of some gemstones?
Natural gemstones are unique, so many stones are available as a single piece. Once sold, the stone is marked as sold out.

## How do I pay?
At checkout you can pay securely online. We never store your card details.

## How can I track my order?
Log in and open My Orders to see the status of every order: pending, paid or shipped.

## How do I contact you?
Use the Contact page and send us a message. We will reply by email.',1),
('shipping-returns','Shipping & Returns','## Processing
Orders are processed once your payment has been confirmed. Custom-made jewellery is made to your specification, so it may take longer than ready-made items.

## Shipping
We ship from Sri Lanka. Shipping details and any delivery charges are confirmed with you after you place your order. Please make sure your address and phone number are correct at checkout.

## Returns
Ready-made items in their original condition can be returned after you contact us. Custom-made pieces are made specially for you, so they cannot normally be returned unless they arrive faulty or not as described.

## Damaged or incorrect items
If something arrives damaged or is not what you ordered, contact us as soon as possible with your order number and we will make it right.

## Questions
Send us a message from the Contact page and we will help.',2),
('privacy-policy','Privacy Policy','## What we collect
When you register or place an order we collect your name, email address, phone number and delivery address. We also keep a record of your orders and payments.

## How we use it
We use your information to process orders, deliver your items, provide order updates and reply to your messages. We do not sell your personal information.

## Payments
Payments are handled by our payment provider. Your card details are entered with the provider and are never stored on our website.

## Passwords and security
Passwords are stored in an encrypted (hashed) form. Please keep your password private.

## Your choices
You can contact us at any time to ask what information we hold about you or to ask us to correct or delete it.

## Changes
We may update this policy from time to time. The latest version is always on this page.',3),
('terms','Terms & Conditions','## Using this website
By using Taprobellelk you agree to these terms. Please read them before placing an order.

## Products and pricing
All prices are shown in US dollars. Natural gemstones vary in colour and appearance, and photographs are a guide. Custom-made jewellery prices are estimates based on the gemstone, the metal and the options you choose, and are confirmed at checkout.

## Orders and payment
An order is accepted once payment has been confirmed. We may cancel an order if an item is no longer available, in which case you will be refunded in full.

## Custom-made items
Custom-made jewellery is made to your chosen specification. Please check your ring size and options carefully before ordering.

## Accounts
You are responsible for keeping your account details secure and for activity under your account.

## Contact
Questions about these terms? Use the Contact page.',4);
