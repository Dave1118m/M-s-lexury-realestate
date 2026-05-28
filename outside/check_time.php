<?php
require_once 'includes/database.php';
$stmt = db()->query("SELECT id, verification_code, verification_expires, NOW() as mysql_now FROM users WHERE email = 'mihrete99@gmail.com'");
print_r($stmt->fetchAll());
echo 'PHP Time: ' . date('Y-m-d H:i:s');