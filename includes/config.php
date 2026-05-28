<?php
/**
 * Hawassa Luxury Real Estate - Configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'bhs_clone');
define('DB_USER', 'root');
define('DB_PASS', 'mysql');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'http://localhost/realstate');
define('SITE_NAME', 'Hawassa Real Estate');
define('ADMIN_EMAIL', 'admin@bhsusa.com');

// SMTP Configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'mihrete99@gmail.com'); // TODO: User must configure this
define('SMTP_PASS', 'xiahywbzivacocar'); // TODO: User must configure this

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('HAWASSA_SESSID');
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include dynamic images helper
require_once __DIR__ . '/images.php';