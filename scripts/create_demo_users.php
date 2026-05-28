<?php
// create_demo_users.php – create demo users for testing
require_once __DIR__ . '/../includes/core.php'; // provides db() function

function create_user($name, $email, $password, $role) {
    // Check if user already exists
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "User $email already exists.\n";
        return;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $role]);
    echo "Created $role: $email / $password\n";
}

create_user('Admin Demo', 'admin@demo.com', 'admin123', 'admin');
create_user('Agent Demo', 'agent@demo.com', 'agent123', 'agent');
create_user('User Demo', 'user@demo.com', 'user123', 'user');
?>
