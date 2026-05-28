<?php
/**
 * Hawassa Luxury Real Estate - Database Setup Script
 * Run this file once to create all required tables and insert demo data.
 */
session_name('HAWASSA_SESSID');
session_start();

// ---------------------------------------------------------------------
// 1. Database credentials (adjust to your environment)
// ---------------------------------------------------------------------
$host = 'localhost';
$db   = 'bhs_clone';
$user = 'root';
$pass = 'mysql';
$charset = 'utf8mb4';

try {
    // Connect without database first to create it if needed
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // -----------------------------------------------------------------
    // 2. Create tables
    // -----------------------------------------------------------------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('user','admin') DEFAULT 'user',
            `phone` VARCHAR(20) DEFAULT NULL,
            `avatar` VARCHAR(255) DEFAULT NULL,
            `verification_code` VARCHAR(10) DEFAULT NULL,
            `verification_expires` DATETIME DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT,
            `image` VARCHAR(255) DEFAULT NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `properties` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `description` TEXT,
            `price` DECIMAL(15,2) NOT NULL,
            `type` ENUM('sale','rent') DEFAULT 'sale',
            `property_type` VARCHAR(50) DEFAULT 'Residential',
            `bedrooms` INT DEFAULT 0,
            `bathrooms` INT DEFAULT 0,
            `area_sqft` INT DEFAULT 0,
            `address` VARCHAR(255),
            `city` VARCHAR(100),
            `state` VARCHAR(50),
            `zip` VARCHAR(20),
            `latitude` DECIMAL(10,7),
            `longitude` DECIMAL(10,7),
            `category_id` INT,
            `agent_id` INT,
            `featured` TINYINT(1) DEFAULT 0,
            `status` ENUM('active','pending','sold') DEFAULT 'active',
            `image_main` VARCHAR(255),
            `image_gallery` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `property_features` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `property_id` INT NOT NULL,
            `feature_name` VARCHAR(100) NOT NULL,
            FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_posts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `content` TEXT,
            `excerpt` TEXT,
            `author_id` INT,
            `image` VARCHAR(255),
            `status` ENUM('published','draft') DEFAULT 'draft',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `inquiries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `property_id` INT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `phone` VARCHAR(20),
            `message` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `testimonials` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `position` VARCHAR(100),
            `content` TEXT,
            `rating` TINYINT DEFAULT 5,
            `image` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(50) NOT NULL UNIQUE,
            `setting_value` TEXT
        ) ENGINE=InnoDB
    ");

    // -----------------------------------------------------------------
    // 3. Insert demo data
    // -----------------------------------------------------------------
    // Admin user (password: admin123)
    $pdo->prepare("INSERT IGNORE INTO `users` (`name`,`email`,`password`,`role`) VALUES (?,?,?,?)")
        ->execute(['Admin Hawassa', 'admin@bhsusa.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

    // Demo agent
    $pdo->prepare("INSERT IGNORE INTO `users` (`name`,`email`,`password`,`role`,`phone`) VALUES (?,?,?,?,?)")
        ->execute(['Sarah Johnson', 'sarah@bhsusa.com', password_hash('agent123', PASSWORD_DEFAULT), 'user', '+1 (212) 555-0198']);

    // Categories
    $cats = [
        ['Addis Ababa', 'new-york-city', 'Luxury properties in Bole, Brooklyn, and beyond'],
        ['Hawassa', 'hamptons', 'Exclusive beachfront estates and summer retreats'],
        ['Hudson Valley', 'hudson-valley', 'Historic homes and countryside estates'],
        ['Bishoftu', 'connecticut', 'Prestigious Fairfield County properties'],
        ['Bahir Dar', 'miami', 'Waterfront condos and luxury villas'],
        ['Langano', 'palm-beach', 'Oceanfront mansions and exclusive communities'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `categories` (`name`,`slug`,`description`) VALUES (?,?,?)");
    foreach ($cats as $c) $stmt->execute($c);

    // Properties
    $props = [
        ['Luxury Penthouse with Central Park Views', 'luxury-penthouse-central-park', 'Stunning 4-bedroom penthouse...', 12500000, 'sale', 'Condo', 4, 4.5, 4200, '157 W 57th St', 'Addis Ababa', 'NY', '10019', 40.7650, -73.9790, 1, 2, 1, 'active'],
        ['Hawassa Beachfront Estate', 'hamptons-beachfront-estate', 'Magnificent oceanfront property...', 18500000, 'sale', 'Single Family', 7, 6, 7500, '101 Ocean Rd', 'Southampton', 'NY', '11968', 40.8840, -72.3900, 2, 2, 1, 'active'],
        ['Tribeca Loft with Skyline Views', 'tribeca-loft-skyline', 'Industrial-chic 3-bedroom loft...', 4950000, 'sale', 'Condo', 3, 2, 2800, '60 Hudson St', 'Addis Ababa', 'NY', '10013', 40.7170, -74.0080, 1, 2, 1, 'active'],
        ['Greenwich Village Townhouse', 'greenwich-village-townhouse', 'Historic 5-story townhouse...', 8900000, 'sale', 'Townhouse', 5, 4, 5200, '12 W 11th St', 'Addis Ababa', 'NY', '10011', 40.7340, -73.9960, 1, 2, 0, 'active'],
        ['Bahir Dar Waterfront Villa', 'miami-waterfront-villa', 'Contemporary villa on Biscayne Bay...', 7200000, 'sale', 'Single Family', 6, 5.5, 6200, '456 Ocean Blvd', 'Bahir Dar', 'FL', '33139', 25.7617, -80.1918, 5, 2, 1, 'active'],
        ['Upper East Side Classic Six', 'upper-east-side-classic-six', 'Elegant pre-war cooperative...', 3750000, 'sale', 'Co-op', 3, 2.5, 2400, '920 Park Ave', 'Addis Ababa', 'NY', '10028', 40.7770, -73.9590, 1, 2, 0, 'active'],
        ['Langano Oceanfront Mansion', 'palm-beach-mansion', 'Mediterranean-style estate...', 22500000, 'sale', 'Single Family', 8, 9, 11000, '1 Ocean Blvd', 'Langano', 'FL', '33480', 26.7056, -80.0364, 6, 2, 1, 'active'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `properties` (`title`,`slug`,`description`,`price`,`type`,`property_type`,`bedrooms`,`bathrooms`,`area_sqft`,`address`,`city`,`state`,`zip`,`latitude`,`longitude`,`category_id`,`agent_id`,`featured`,`status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($props as $p) $stmt->execute($p);

    // Blog posts
    $posts = [
        ['Market Report Q1 2026: Bole Luxury Real Estate', 'market-report-q1-2026', 'The Bole luxury market...', 'An in-depth analysis of Q1 2026 trends...', 1, 'draft'],
        ['Top 5 Neighborhoods for Investment in NYC', 'top-5-neighborhoods-investment-nyc', 'Discover the neighborhoods poised for growth...', 'Where to invest in 2026...', 1, 'draft'],
        ['Hawassa Summer Rental Guide 2026', 'hamptons-summer-rental-2026', 'Everything you need to know...', 'Your complete guide to summer rentals...', 1, 'draft'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `blog_posts` (`title`,`slug`,`content`,`excerpt`,`author_id`,`status`) VALUES (?,?,?,?,?,?)");
    foreach ($posts as $bp) $stmt->execute($bp);

    // Testimonials
    $testimonials = [
        ['Michael R.', 'CEO, TechVision Inc.', 'Hawassa made selling our Churchill Road penthouse seamless. Their market knowledge and negotiation skills are unmatched.', 5],
        ['Jennifer L.', 'Private Investor', 'I have worked with Hawassa for over a decade. They consistently find the best properties before they hit the market.', 5],
        ['David & Sarah K.', 'Homeowners', 'The team at Hawassa guided us through every step of buying our first home in Bishoftu. Truly white-glove service.', 5],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `testimonials` (`name`,`position`,`content`,`rating`) VALUES (?,?,?,?)");
    foreach ($testimonials as $t) $stmt->execute($t);

    // Site settings
    $settings = [
        ['site_name', 'Hawassa Real Estate'],
        ['site_description', 'Luxury Real Estate in NYC, Hawassa, Hudson Valley, Bishoftu, New Jersey, Langano and Bahir Dar'],
        ['contact_email', 'info@bhsusa.com'],
        ['contact_phone', '+1 (212) 555-0100'],
        ['footer_text', '© 2026 Hawassa Real Estate. All rights reserved.'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES (?,?)");
    foreach ($settings as $s) $stmt->execute($s);

    $_SESSION['install_success'] = true;
    header('Location: ../index.php?installed=1');
    exit;

} catch (PDOException $e) {
    die("<h2>Installation Error</h2><p>" . htmlspecialchars($e->getMessage()) . "</p><p>Please check your database credentials and try again.</p>");
}