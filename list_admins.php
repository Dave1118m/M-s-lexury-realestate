<?php
require 'c:/Program Files/Amppsss/www/realstate/includes/database.php';
$stmt = db()->prepare('SELECT name,email,phone FROM users WHERE role=\'admin\'');
$stmt->execute();
foreach ($stmt->fetchAll() as $row) {
    echo $row['name'].' - '.$row['email'].' - '.$row['phone'].PHP_EOL;
}
?>
