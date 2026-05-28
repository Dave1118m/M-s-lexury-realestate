<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Ensure user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($method === 'GET' && $action === 'fetch') {
    $agent_id = $_GET['agent_id'] ?? 0;
    if (!$agent_id) {
        echo json_encode(['success' => false, 'error' => 'Missing agent_id']);
        exit;
    }
    // Use session identifier to isolate conversation per user
    $session_id = $_SESSION['chat_session_id'] ?? (bin2hex(random_bytes(16)));
    $_SESSION['chat_session_id'] = $session_id;

    $stmt = db()->prepare("SELECT cm.*, u.name as sender_name FROM chat_messages cm LEFT JOIN users u ON cm.sender_id = u.id WHERE cm.session_id = ? AND cm.agent_id = ? ORDER BY cm.created_at ASC");
    $stmt->execute([$session_id, $agent_id]);
    echo json_encode(['success' => true, 'messages' => $stmt->fetchAll()]);
} elseif ($method === 'POST' && $action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $agent_id = $data['agent_id'] ?? 0;
    $message = trim($data['message'] ?? '');
    if (!$agent_id || !$message) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }
    // Ensure the same session identifier is used
    $session_id = $_SESSION['chat_session_id'] ?? (bin2hex(random_bytes(16)));
    $_SESSION['chat_session_id'] = $session_id;

    $stmt = db()->prepare("INSERT INTO chat_messages (session_id, sender_id, client_user_id, agent_id, message, is_agent) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$session_id, $user_id, $user_id, $agent_id, $message]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
