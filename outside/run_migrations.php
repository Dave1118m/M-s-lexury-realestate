<?php
// Aggregator to include all migration scripts from the project root.
$files = [
    '../migration.php',
    '../migration_blog_category.php',
    '../migration_enterprise.php',
    '../migration_ethiopian_data.php',
    '../migration_new_features.php',
    '../migration_property_offers.php',
    '../migration_rich_media.php',
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
