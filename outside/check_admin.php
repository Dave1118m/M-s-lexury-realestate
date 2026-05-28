<?php
require_once 'includes/database.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = db()->prepare("UPDATE users SET password = ? WHERE email = 'mihrete99@gmail.com'");
$stmt->execute([$hash]);
echo "Password reset to admin123";