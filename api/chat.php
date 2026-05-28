<?php
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Basic Session ID for guests
if (!isset($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['chat_session_id'];
$user_role = $_SESSION['user_role'] ?? '';
$is_admin = $user_role === 'admin';
$is_agent_user = $user_role === 'agent';
$logged_user_id = $_SESSION['user_id'] ?? null;

if ($method === 'GET' && $action === 'fetch') {
    $agent_id = $_GET['agent_id'] ?? 0;

    // Allow session id to be provided by admin or agent UI via `session_id` or `session` param.
    $client_session = $_GET['session_id'] ?? $_GET['session'] ?? $session_id;

    $stmt = db()->prepare("SELECT cm.*, u.name as sender_name FROM chat_messages cm LEFT JOIN users u ON cm.sender_id = u.id WHERE cm.session_id = ? AND cm.agent_id = ? ORDER BY cm.created_at ASC");
    $stmt->execute([$client_session, $agent_id]);

    echo json_encode(['success' => true, 'messages' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST' && $action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $agent_id = $data['agent_id'] ?? 0;
    $message = trim($data['message'] ?? '');
    $target_session = $data['session_id'] ?? $data['session'] ?? $session_id;
    $is_agent_flag = ($is_admin || $is_agent_user) ? 1 : 0;

    if ($message && $agent_id && $target_session) {
        $stmt = db()->prepare("INSERT INTO chat_messages (session_id, sender_id, agent_id, message, is_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$target_session, $logged_user_id, $agent_id, $message, $is_agent_flag]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
}
?>
