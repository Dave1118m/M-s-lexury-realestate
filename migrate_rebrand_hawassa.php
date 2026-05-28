<?php
/**
 * Hawassa Real Estate - Rebrand & Localize to Ethiopian
 * Updates all database records: site settings, user names, phone numbers
 */
require_once 'includes/database.php';
require_once 'includes/config.php';

$db = db();

// =============================
// 1. Update Site Settings
// =============================
$settings_updates = [
    'site_name' => 'Hawassa Real Estate',
    'site_description' => 'Luxury Real Estate in Hawassa, Addis Ababa, Bahir Dar, Dire Dawa, and across Ethiopia',
    'contact_email' => 'info@hawassarealestate.et',
    'contact_phone' => '+251 91 234 5678',
    'footer_text' => '© 2026 Hawassa Real Estate. All rights reserved.',
];

foreach ($settings_updates as $key => $value) {
    $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$value, $key]);
}
echo "✅ Site settings updated to Hawassa/Ethiopian.\n";

// =============================
// 2. Update Admin User
// =============================
$db->exec("UPDATE users SET name = 'Admin Hawassa' WHERE name = 'Admin Hawassa'");
echo "✅ Admin user renamed.\n";

// =============================
// 3. Update Agent Names & Phones to Ethiopian
// =============================
$ethiopian_agents = [
    ['Jane Doe' => ['Tigist Bekele', '+251 91 123 4501', 'tigist@hawassarealestate.et']],
    ['John Smith' => ['Abebe Tadesse', '+251 91 123 4502', 'abebe@hawassarealestate.et']],
    ['Sarah Johnson' => ['Meron Alemu', '+251 91 123 4503', 'meron@hawassarealestate.et']],
    ['Michael Brown' => ['Dawit Haile', '+251 91 123 4504', 'dawit@hawassarealestate.et']],
    ['Emily Davis' => ['Hanna Gebre', '+251 91 123 4505', 'hanna@hawassarealestate.et']],
];

$update_agent = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE name = ?");
foreach ($ethiopian_agents as $mapping) {
    foreach ($mapping as $old_name => $new_data) {
        $update_agent->execute([$new_data[0], $new_data[1], $new_data[2], $old_name]);
    }
}
echo "✅ Agent names and phones updated to Ethiopian.\n";

// =============================
// 4. Update Testimonials to Ethiopian Names
// =============================
$testimonial_updates = [
    ['David Chen' => ['Yonas Tesfaye', 'CEO, EthioTech Solutions']],
    ['Elena Rodriguez' => ['Selamawit Desta', 'International Investor']],
    ['Robert Williams' => ['Solomon Mengistu', 'Philanthropist']],
    ['Michael R.' => ['Yonas T.', 'CEO, EthioTech Solutions']],
    ['Jennifer L.' => ['Selamawit D.', 'Private Investor']],
    ['David & Sarah K.' => ['Solomon & Meron K.', 'Homeowners']],
];

$update_test = $db->prepare("UPDATE testimonials SET name = ?, position = ? WHERE name = ?");
foreach ($testimonial_updates as $mapping) {
    foreach ($mapping as $old => $new) {
        $update_test->execute([$new[0], $new[1], $old]);
    }
}

// Update testimonial content to replace Hawassa/Hawassa Real Estate
$db->exec("UPDATE testimonials SET content = REPLACE(content, 'Hawassa Real Estate', 'Hawassa Real Estate')");
$db->exec("UPDATE testimonials SET content = REPLACE(content, 'Hawassa', 'Hawassa')");
echo "✅ Testimonials updated to Ethiopian names.\n";

// =============================
// 5. Update Blog Posts - Replace Hawassa/Brown Harris in content
// =============================
$db->exec("UPDATE blog_posts SET content = REPLACE(content, 'Hawassa Real Estate', 'Hawassa Real Estate')");
$db->exec("UPDATE blog_posts SET content = REPLACE(content, 'Hawassa', 'Hawassa')");
$db->exec("UPDATE blog_posts SET title = REPLACE(title, 'Hawassa', 'Hawassa')");
$db->exec("UPDATE blog_posts SET excerpt = REPLACE(excerpt, 'Hawassa', 'Hawassa')");

// Replace person names in blog content
$db->exec("UPDATE blog_posts SET content = REPLACE(content, 'Sarah Jenkins', 'Bethlehem Asfaw')");
$db->exec("UPDATE blog_posts SET content = REPLACE(content, 'Victoria Chen', 'Kidist Girma')");
$db->exec("UPDATE blog_posts SET content = REPLACE(content, 'Kelly Wearstler', 'Aster Kebede')");
echo "✅ Blog posts rebranded to Hawassa with Ethiopian names.\n";

// =============================
// 6. Update Announcements - Replace Hawassa
// =============================
$db->exec("UPDATE announcements SET content = REPLACE(content, 'Hawassa', 'Hawassa')");
$db->exec("UPDATE announcements SET title = REPLACE(title, 'Hawassa', 'Hawassa')");
echo "✅ Announcements rebranded.\n";

echo "\n🎉 All data successfully rebranded to Hawassa Real Estate with Ethiopian locale!\n";
?>
