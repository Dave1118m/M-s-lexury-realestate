<?php
require_once __DIR__.'/includes/database.php';
$stmt = db()->prepare('SELECT id, name, email, phone, avatar FROM users WHERE role = ?');
$stmt->execute(['agent']);
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($agents as $a) {
    echo $a['id'].' - '.$a['name'].' - '.$a['email'].' - '.$a['phone']."\n";
}
?>
