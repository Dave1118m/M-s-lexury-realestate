<?php
require_once '../includes/auth.php';
if (!is_logged_in() || !is_admin()) redirect('../pages/login.php');

// Get all unique chat sessions across all agents
$stmt = db()->query("
    SELECT c.session_id, c.agent_id, MAX(c.created_at) as last_msg_time,
    a.name as agent_name,
    (SELECT message FROM chat_messages WHERE session_id = c.session_id ORDER BY created_at DESC LIMIT 1) as last_message,
    (SELECT u.name FROM chat_messages cm LEFT JOIN users u ON cm.sender_id = u.id WHERE cm.session_id = c.session_id AND cm.is_agent = 0 LIMIT 1) as client_name
    FROM chat_messages c
    LEFT JOIN users a ON c.agent_id = a.id
    GROUP BY c.session_id, c.agent_id
    ORDER BY last_msg_time DESC
");
$sessions = $stmt->fetchAll();

$active_session = $_GET['session'] ?? ($sessions[0]['session_id'] ?? null);
$active_agent = $_GET['agent_id'] ?? ($sessions[0]['agent_id'] ?? null);

$messages = [];
if ($active_session && $active_agent) {
    $stmt_msgs = db()->prepare("
        SELECT cm.*, u.name as sender_name 
        FROM chat_messages cm 
        LEFT JOIN users u ON cm.sender_id = u.id 
        WHERE session_id = ? AND agent_id = ? 
        ORDER BY created_at ASC
    ");
    $stmt_msgs->execute([$active_session, $active_agent]);
    $messages = $stmt_msgs->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Oversight - Hawassa Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; height: 100vh; overflow: hidden; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; flex-shrink: 0; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-sidebar a:hover { color: var(--color-gold); }
        
        .chat-dashboard { flex: 1; display: flex; background: white; }
        .session-list { width: 350px; border-right: 1px solid var(--color-gray-200); background: var(--color-gray-50); overflow-y: auto; }
        .session-item { padding: 1rem; border-bottom: 1px solid var(--color-gray-200); cursor: pointer; text-decoration: none; display: block; color: inherit; }
        .session-item:hover, .session-item.active { background: white; border-left: 4px solid var(--color-gold); }
        
        .chat-window { flex: 1; display: flex; flex-direction: column; background: white; }
        .chat-header { padding: 1rem 2rem; border-bottom: 1px solid var(--color-gray-200); font-weight: bold; font-size: 1.2rem; display: flex; justify-content: space-between;}
        .chat-messages { flex: 1; padding: 2rem; overflow-y: auto; background: var(--color-gray-50); display: flex; flex-direction: column; gap: 1rem; }
        
        .chat-message { max-width: 70%; padding: 1rem; border-radius: 8px; line-height: 1.4; }
        .chat-message.client { align-self: flex-start; background: white; border: 1px solid var(--color-gray-200); border-bottom-left-radius: 0; box-shadow: var(--shadow-sm); }
        .chat-message.agent { align-self: flex-end; background: var(--color-gold); color: white; border-bottom-right-radius: 0; }
        
        .msg-sender { font-size: 0.8rem; font-weight: bold; margin-bottom: 0.25rem; color: var(--color-gray-500); }
        .agent .msg-sender { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>Hawassa Admin</h3>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="properties.php">🏠 Properties</a>
            <a href="agents.php">👥 Agents</a>
            <a href="users.php">👤 Users</a>
            <a href="testimonials.php">⭐ Testimonials</a>
            <a href="announcements.php">📢 Offers</a>
            <a href="chats.php" style="color: var(--color-gold);">👁️ Chat Oversight</a>
            <a href="logout.php" style="color: #e74c3c;">🚪 Logout</a>
        </aside>
        
        <div class="chat-dashboard">
            <div class="session-list">
                <div style="padding: 1rem; font-weight: bold; border-bottom: 1px solid var(--color-gray-200); background: #eee;">All Platform Conversations</div>
                <?php foreach ($sessions as $s): ?>
                    <a href="chats.php?session=<?php echo $s['session_id']; ?>&agent_id=<?php echo $s['agent_id']; ?>" class="session-item <?php echo $s['session_id'] === $active_session ? 'active' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <strong style="font-size: 0.9rem;"><?php echo $s['client_name'] ?: 'Guest'; ?></strong>
                            <span style="font-size: 0.8rem; color: var(--color-gray-500);">w/ <?php echo htmlspecialchars($s['agent_name']); ?></span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--color-gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($s['last_message']); ?>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($sessions)): ?>
                    <div style="padding: 1rem; color: var(--color-gray-500); font-size: 0.9rem;">No chats found.</div>
                <?php endif; ?>
            </div>
            
            <div class="chat-window">
                <?php if ($active_session): ?>
                    <div class="chat-header">
                        <span>Transcript: Client <?php echo substr($active_session, 0, 6); ?></span>
                        <span style="font-size: 0.9rem; font-weight: normal; color: #e74c3c;">READ-ONLY OVERSIGHT</span>
                    </div>
                    <div class="chat-messages" id="chatBox">
                        <?php foreach ($messages as $msg): ?>
                            <div class="chat-message <?php echo $msg['is_agent'] ? 'agent' : 'client'; ?>">
                                <div class="msg-sender"><?php echo htmlspecialchars($msg['sender_name'] ?: ($msg['is_agent'] ? 'Agent' : 'Guest')); ?></div>
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <div style="font-size: 0.7rem; margin-top: 0.5rem; opacity: 0.7; text-align: right;">
                                    <?php echo date('M d, H:i', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                            <div style="text-align: center; color: var(--color-gray-400);">No messages in this session.</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: var(--color-gray-500);">
                        Select a conversation to view the transcript
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Auto scroll to bottom
        const chatBox = document.getElementById('chatBox');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>
