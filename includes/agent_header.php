<?php
ob_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
// Refresh role from DB in case admin changed it after login
if (isset($_SESSION['user_id'])) {
    $stmt = db()->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row && $row['role'] !== $_SESSION['user_role']) {
        $_SESSION['user_role'] = $row['role'];
    }
}
$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_setting('site_name'); ?> | Luxury Real Estate</title>
    <meta name="description" content="<?php echo get_setting('site_description'); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/luxury.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Leaflet for Interactive Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <span><i class="icon-phone"></i> <?php echo get_setting('contact_phone'); ?></span>
            <span><i class="icon-mail"></i> <?php echo get_setting('contact_email'); ?></span>
            <?php if (is_logged_in()): ?>
                <span class="user-greeting">Welcome, <?php echo $_SESSION['user_name']; ?> | <a href="<?php echo SITE_URL; ?>/pages/logout.php">Logout</a></span>
            <?php else: ?>
                <span><a href="<?php echo SITE_URL; ?>/pages/login.php">Sign In</a> | <a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a></span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                    <svg width="45" height="45" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="box-shadow: 0 4px 10px rgba(212, 175, 55, 0.2); border-radius: 12px;">
                        <rect width="100" height="100" rx="12" fill="var(--color-black)" />
                        <path d="M 15 15 L 85 15 L 85 85 L 15 85 Z" fill="none" stroke="var(--color-gold)" stroke-width="2" />
                        <path d="M 22 22 L 78 22 L 78 78 L 22 78 Z" fill="none" stroke="var(--color-gold)" stroke-width="1" opacity="0.5" />
                        <text x="50" y="60" font-family="'Playfair Display', serif" font-size="22" fill="var(--color-gold)" font-weight="bold" text-anchor="middle">HW</text>
                    </svg>
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span style="font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--color-black); letter-spacing: 0.5px; line-height: 1.1;">HAWASSA</span>
                        <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 500; color: var(--color-gold); letter-spacing: 4px; text-transform: uppercase;">Real Estate</span>
                    </div>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="agent_dashboard.php">Dashboard</a></li>
                    <li><a href="agent_dashboard.php#listings">My Listings</a></li>
                    <li><a href="agent_dashboard.php#messages">Messages</a></li>
                    <li><a href="../admin/dashboard.php">Advanced Portal</a></li>
                </ul>
            </nav>
            <div class="header-toggles" style="display: flex; align-items: center; gap: 1rem;">
                <button id="theme-toggle" aria-label="Toggle Dark Mode" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-gray-700);">
                    <span class="light-icon">🌙</span>
                    <span class="dark-icon" style="display: none;">☀️</span>
                </button>
                <button class="mobile-menu-toggle" aria-label="Menu" style="margin: 0;">☰</button>
            </div>
        </div>
    </header>
    <main>
