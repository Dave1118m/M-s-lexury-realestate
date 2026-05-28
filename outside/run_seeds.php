<?php
// Aggregator to include all seed scripts from the project root.
$files = [
    '../seed_agents.php',
    '../seed_announcements.php',
    '../seed_blog.php',
    '../seed_blog_extended.php',
    '../seed_special_offers.php',
    '../seed_testimonials.php',
    '../seed_users.php',
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (file_exists($path)) {
        require_once $path;
        echo "Included: $f" . PHP_EOL;
    } else {
        echo "Missing file: $f" . PHP_EOL;
    }
}
