<?php
require_once __DIR__ . '/includes/database.php';
$stmt = db()->prepare("SELECT name, email, phone FROM users WHERE role = 'user'");
$stmt->execute();
$users = $stmt->fetchAll();
foreach ($users as $u) {
    echo $u['name'] . ' - ' . $u['email'] . ' - ' . $u['phone'] . PHP_EOL;
}
?>
