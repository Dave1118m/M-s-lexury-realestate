<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function ensure_user_role($role, $namePrefix) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM users WHERE role = ? LIMIT 1");
    $stmt->execute([$role]);
    $u = $stmt->fetch();
    if ($u) return $u;

    $email = strtolower($namePrefix) . '@example.test';
    $password = password_hash('password', PASSWORD_DEFAULT);
    $insert = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert->execute([ $namePrefix, $email, $password, $role ]);
    $id = $db->lastInsertId();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

try {
    echo "=== Chat E2E Simulation ===\n";
    $agent = ensure_user_role('agent', 'E2EAgent');
    $user = ensure_user_role('user', 'E2EUser');
    echo "Agent: {$agent['id']} - {$agent['name']}\n";
    echo "User: {$user['id']} - {$user['name']}\n";

    // New session id
    $session_id = bin2hex(random_bytes(8));
    $_SESSION['chat_session_id'] = $session_id;
    echo "Session ID: $session_id\n";

    // Simulate user sending message
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = 'user';

    $user_msg = 'Hello agent, I am interested in a listing. (E2E test)';
    $db = db();
    $colCheck = $db->query("SHOW COLUMNS FROM chat_messages LIKE 'client_user_id'");
    $has_client_col = $colCheck && $colCheck->rowCount() > 0;
    if ($has_client_col) {
        $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender_id, client_user_id, agent_id, message, is_agent) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$session_id, $user['id'], $user['id'], $agent['id'], $user_msg]);
    } else {
        // older schema: no client_user_id column
        $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender_id, agent_id, message, is_agent) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$session_id, $user['id'], $agent['id'], $user_msg]);
    }
    echo "User sent: $user_msg\n";

    // Agent fetches messages
    $_SESSION['user_id'] = $agent['id'];
    $_SESSION['user_role'] = 'agent';

    $db = db();
    $stmtFetch = $db->prepare("SELECT cm.*, u.name as sender_name FROM chat_messages cm LEFT JOIN users u ON cm.sender_id = u.id WHERE cm.session_id = ? AND cm.agent_id = ? ORDER BY cm.created_at ASC");
    $stmtFetch->execute([$session_id, $agent['id']]);
    $msgs = $stmtFetch->fetchAll();

    echo "\nAgent fetching messages... (should see user message)\n";
    foreach ($msgs as $m) {
        $role = $m['is_agent'] ? 'AGENT' : 'USER';
        $sender = $m['sender_name'] ?: ($m['is_agent'] ? 'Agent' : 'Guest');
        echo "[{$m['created_at']}] {$role} ({$sender}): {$m['message']}\n";
    }

    // Agent replies
    $agent_reply = 'Thanks for your interest — happy to help. When would you like a viewing? (E2E reply)';
    $stmtReply = db()->prepare("INSERT INTO chat_messages (session_id, sender_id, agent_id, message, is_agent) VALUES (?, ?, ?, ?, 1)");
    $stmtReply->execute([$session_id, $agent['id'], $agent['id'], $agent_reply]);
    echo "\nAgent replied: $agent_reply\n";

    // User fetches messages
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = 'user';
    $stmtFetch->execute([$session_id, $agent['id']]);
    $msgs2 = $stmtFetch->fetchAll();

    echo "\nUser fetching messages... (should see agent reply)\n";
    foreach ($msgs2 as $m) {
        $role = $m['is_agent'] ? 'AGENT' : 'USER';
        $sender = $m['sender_name'] ?: ($m['is_agent'] ? 'Agent' : 'You');
        echo "[{$m['created_at']}] {$role} ({$sender}): {$m['message']}\n";
    }

    echo "\n=== End E2E Simulation ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
