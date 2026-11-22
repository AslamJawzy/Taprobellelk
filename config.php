<?php
/**
 * Taprobellelk - configuration
 * Edit the values below for your XAMPP setup.
 */

// ---- Database (XAMPP defaults) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'taprobellelk');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP default is an empty password

// ---- Site ----
// URL of this folder, detected automatically (works whatever you name the folder inside htdocs).
// If it ever detects wrongly, replace the next line with e.g.  define('BASE_URL', '/Taprobellelk');
$__root = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: ''), '/') : '';
$__dir  = rtrim(str_replace('\\', '/', __DIR__), '/');
define('BASE_URL', ($__root !== '' && stripos($__dir, $__root) === 0) ? substr($__dir, strlen($__root)) : '/Taprobellelk');
define('SITE_NAME', 'Taprobellelk');
define('CURRENCY', 'USD');
define('TAX_RATE', 0.10);              // 10% (the original cart page used 10%)

// ---- Payment gateway ----
// 'demo'   = built-in simulated card gateway (works with no account, for testing/presentation)
// 'paypal' = PayPal Sandbox (free) - fill in the two keys below
define('PAYMENT_MODE', 'demo');

// Get these free at https://developer.paypal.com > Apps & Credentials > Sandbox > Create App
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');
define('PAYPAL_API_BASE', 'https://api-m.sandbox.paypal.com');  // sandbox (free). Live would be api-m.paypal.com

// On Windows/XAMPP, cURL sometimes fails with "SSL certificate problem".
// Best fix: download cacert.pem and set curl.cainfo in php.ini (see README).
// For LOCAL TESTING ONLY you may set this to false.
define('CURL_VERIFY_SSL', true);

date_default_timezone_set('Asia/Colombo');
