-- Taprobellelk database (MySQL / MariaDB - XAMPP)
-- phpMyAdmin: Import > choose this file > Go
CREATE DATABASE IF NOT EXISTS taprobellelk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taprobellelk;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS payments, order_items, orders, products, metal_rates, jewellery_types, users, messages, pages;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kind ENUM('gem','jewellery') NOT NULL,
  category VARCHAR(60) NOT NULL,
  name VARCHAR(150) NOT NULL,
  carat DECIMAL(5,2) NULL,
  price DECIMAL(10,2) NOT NULL,            -- gem price (gem) or finished piece price (jewellery)
  image VARCHAR(200) NOT NULL,
  gallery TEXT NULL,                       -- JSON array of file names in /Images
  description TEXT NULL,
  stock INT NOT NULL DEFAULT 1,
  jewellery_type VARCHAR(20) NULL,
  metal_code VARCHAR(20) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (kind), INDEX (category)
) ENGINE=InnoDB;

-- Rates used by the Dynamic Price Estimator (edit in phpMyAdmin any time)
CREATE TABLE metal_rates (
  code VARCHAR(20) PRIMARY KEY,
  label VARCHAR(60) NOT NULL,
  rate_per_gram DECIMAL(10,2) NOT NULL,    -- USD per gram
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE jewellery_types (
  code VARCHAR(20) PRIMARY KEY,
  label VARCHAR(60) NOT NULL,
  base_grams DECIMAL(6,2) NOT NULL,        -- typical metal weight
  making_pct DECIMAL(5,2) NOT NULL,        -- making charge, % of metal cost
  setting_fee DECIMAL(10,2) NOT NULL,      -- fixed gem-setting fee (USD)
  has_size TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_no VARCHAR(20) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  tax DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status ENUM('pending','paid','failed','cancelled','shipped') NOT NULL DEFAULT 'pending',
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(80) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  country VARCHAR(80) NOT NULL,
  paypal_order_id VARCHAR(40) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  image VARCHAR(200) NOT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  options TEXT NULL,
  breakdown TEXT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  provider VARCHAR(20) NOT NULL,           -- paypal | demo
  provider_ref VARCHAR(80) NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL,
  status VARCHAR(20) NOT NULL,
  raw_response TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default administrator (CHANGE THE PASSWORD after first login: Admin > Change password)
--   username: admin     password: Admin@12345
INSERT INTO users (username,email,password_hash,is_admin) VALUES
('admin','admin@taprobellelk.lk','$2y$10$xSaZUX53U0GU.4bZQrkYc.FOqPKwQQXXXorRF6gMQ0dH/vhbig4Ym',1);

INSERT INTO metal_rates (code,label,rate_per_gram,sort_order) VALUES
('14k','14k Gold',64.00,1),
('18k','18k Gold',83.00,2),
('22k','22k Gold',101.00,3),
('silver','Pure Silver',1.30,4);

INSERT INTO jewellery_types (code,label,base_grams,making_pct,setting_fee,has_size,sort_order) VALUES
('ring','Ring',4.00,18.00,40.00,1,1),
('earrings','Earrings (pair)',3.50,20.00,60.00,0,2),
('necklace','Necklace',12.00,22.00,80.00,0,3),
('bracelet','Bracelet',14.00,20.00,70.00,0,4);

INSERT INTO products (kind,category,name,carat,price,image,gallery,description,stock,jewellery_type,metal_code) VALUES
('gem','Blue Sapphire','Blue Sapphire 1.62ct',1.62,700.00,'blue 1.62ct (1).jpeg','["blue 1.62ct (1).jpeg", "blue 1.62ct (2).jpeg", "blue 1.62ct (3).jpeg", "blue 1.62ct (4).jpeg"]','Natural Blue Sapphire - 1.62 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Blue Sapphire 1.48ct',1.48,560.00,'blue 1.48ct (1).jpeg','["blue 1.48ct (1).jpeg", "blue 1.48ct (2).jpeg", "blue 1.48ct (3).jpeg", "blue 1.48ct (4).jpeg"]','Natural Blue Sapphire - 1.48 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Blue Sapphire 1.20ct',1.2,450.00,'blue 1.20ct (1).jpeg','["blue 1.20ct (1).jpeg", "blue 1.20ct (2).jpeg", "blue 1.20ct (3).jpeg", "blue 1.20ct (4).jpeg"]','Natural Blue Sapphire - 1.2 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Royal Blue Sapphire 1.70ct',1.7,740.00,'blue 1.70ct (1).jpeg','["blue 1.70ct (1).jpeg", "blue 1.70ct (2).jpeg", "blue 1.70ct (3).jpeg", "blue 1.70ct (4).jpeg"]','Natural Blue Sapphire - 1.7 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Royal Blue Sapphire 1.64ct',1.64,400.00,'1.64ct vivid_Blue.jpg','["1.64ct vivid_Blue.jpg"]','Natural Blue Sapphire - 1.64 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Blue Sapphire 2.00ct',2.0,320.00,'2ct_blue.jpg','["2ct_blue.jpg"]','Natural Blue Sapphire - 2.0 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Blue Sapphire 3.14ct',3.14,1850.00,'3.14ct_blue.webp','["3.14ct_blue.webp"]','Natural Blue Sapphire - 3.14 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Blue Sapphire','Royal Blue Sapphire 2.85ct',2.85,1500.00,'2.85ct_blue.jpg','["2.85ct_blue.jpg"]','Natural Blue Sapphire - 2.85 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Ruby','Ruby 2.57ct',2.57,3666.00,'Ruby 2.57ct.webp','["Ruby 2.57ct.webp"]','Natural Ruby - 2.57 carats.',1,NULL,NULL),
('gem','Ruby','Ruby 3.15ct',3.15,3800.00,'Ruby 3.15ct.jpg','["Ruby 3.15ct.jpg"]','Natural Ruby - 3.15 carats.',1,NULL,NULL),
('gem','Padparadscha','Padparadscha 2.91ct',2.91,1100.00,'Padparadscha 2.91ct.webp','["Padparadscha 2.91ct.webp"]','Natural Padparadscha - 2.91 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Padparadscha','Padparadscha 4.22ct',4.22,5400.00,'Sunset Padparadscha 4.22ct.jpg','["Sunset Padparadscha 4.22ct.jpg"]','Natural Padparadscha - 4.22 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Yellow Sapphire','Yellow Sapphire 1.77ct',1.77,250.00,'Yellow Sapphire 1.77ct.jpg','["Yellow Sapphire 1.77ct.jpg"]','Natural Yellow Sapphire - 1.77 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Yellow Sapphire','Golden Yellow Sapphire 1.27ct',1.27,366.00,'Golden Yellow Sapphire Rectangular Cushion  1.27ct.webp','["Golden Yellow Sapphire Rectangular Cushion  1.27ct.webp"]','Natural Yellow Sapphire - 1.27 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Green Sapphire','Green Sapphire 1.45ct',1.45,310.00,'Green Sapphire 1.45ct.webp','["Green Sapphire 1.45ct.webp"]','Natural Green Sapphire - 1.45 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Green Sapphire','Green Sapphire 1.34ct',1.34,280.00,'Green_Sapphire _1.34ct.jpg','["Green_Sapphire _1.34ct.jpg"]','Natural Green Sapphire - 1.34 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Purple Sapphire','Purple Sapphire 1.50ct',1.5,230.00,'Purple Sapphire 1.50ct.avif','["Purple Sapphire 1.50ct.avif"]','Natural Purple Sapphire - 1.5 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Purple Sapphire','Purple Sapphire 1.57ct',1.57,268.00,'natural Purple sapphire 1.57ct.webp','["natural Purple sapphire 1.57ct.webp"]','Natural Purple Sapphire - 1.57 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Pink Sapphire','Hot Pink Sapphire 1.71ct',1.71,480.00,'Hot Pink Sappire 1.71ct.jpg','["Hot Pink Sappire 1.71ct.jpg"]','Natural Pink Sapphire - 1.71 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('gem','Pink Sapphire','Pink Sapphire 1.65ct',1.65,280.00,'Pink Sapphire 1.65ct.webp','["Pink Sapphire 1.65ct.webp"]','Natural Pink Sapphire - 1.65 carats. Natural corundum from Sri Lanka.',1,NULL,NULL),
('jewellery','Gold Jewellery','18k gold blue sapphire ring',NULL,1700.00,'18k R blue ring.png','["18k R blue ring.png", "18k R blue ring 2.png", "18k R blue ring 3.png", "18k R blue ring 4.png"]','Handcrafted 18k gold blue sapphire ring. Natural Ceylon sapphire set in 18k gold.',10,'ring','18k'),
('jewellery','Gold Jewellery','18k gold pink sapphire earring',NULL,1550.00,'18k gold pink earring.png','["18k gold pink earring.png", "18k gold pink earring 2.png", "18k gold pink earring 3.png", "18k gold pink earring 4.png"]','Handcrafted 18k gold pink sapphire earring. Natural Ceylon sapphire set in 18k gold.',10,'earrings','18k'),
('jewellery','Gold Jewellery','22k gold pink sapphire necklace',NULL,4000.00,'22k gold pink sapphire necklace .jpeg','["22k gold pink sapphire necklace .jpeg", "22k gold pink sapphire necklace  2.jpeg", "22k gold pink sapphire necklace 3.jpeg", "22k gold pink sapphire necklace 4.jpeg"]','Handcrafted 22k gold pink sapphire necklace. Natural Ceylon sapphire set in 22k gold.',10,'necklace','22k'),
('jewellery','Gold Jewellery','18k gold yellow sapphire ring',NULL,2500.00,'Yellow sapphire 18k gold ring .jpeg','["Yellow sapphire 18k gold ring .jpeg", "Yellow sapphire 18k gold ring 1.jpeg", "Yellow sapphire 18k gold ring 2.jpeg", "Yellow sapphire 18k gold ring 3.jpeg"]','Handcrafted 18k gold yellow sapphire ring. Natural Ceylon sapphire set in 18k gold.',10,'ring','18k'),
('jewellery','Silver Jewellery','Royal blue silver ring',NULL,1780.00,'Royal blue Silver Ring .jpeg','["Royal blue Silver Ring .jpeg", "Royal blue Silver Ring  3.jpeg", "Royal blue Silver Ring 1.jpeg", "Royal blue Silver Ring 2.jpeg"]','Handcrafted Royal blue silver ring. Natural Ceylon sapphire set in sterling silver.',10,'ring','silver'),
('jewellery','Silver Jewellery','Blue sapphire silver bracelet',NULL,1560.00,'Silver bracelet.jpeg','["Silver bracelet.jpeg", "Silver bracelet 1.jpeg", "Silver bracelet 2.jpeg", "Silver bracelet 3.jpeg"]','Handcrafted Blue sapphire silver bracelet. Natural Ceylon sapphire set in sterling silver.',10,'bracelet','silver'),
('jewellery','Silver Jewellery','Pink sapphire silver ring',NULL,1450.00,'Silver pink sapphire ring .jpeg','["Silver pink sapphire ring .jpeg", "Silver pink sapphire ring 1.jpeg", "Silver pink sapphire ring 2.jpeg", "Silver pink sapphire ring 3.jpeg"]','Handcrafted Pink sapphire silver ring. Natural Ceylon sapphire set in sterling silver.',10,'ring','silver'),
('jewellery','Silver Jewellery','2ct Yellow sapphire silver ring',2.0,1600.00,'2ct yellow sapphire Silver ring.jpeg','["2ct yellow sapphire Silver ring.jpeg", "2ct yellow sapphire Silver ring 1.jpeg", "2ct yellow sapphire Silver ring 2.jpeg", "2ct yellow sapphire Silver ring 3.jpeg"]','Handcrafted 2ct Yellow sapphire silver ring. Natural Ceylon sapphire set in sterling silver.',10,'ring','silver');

-- ---- contact messages + editable info pages ----
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
