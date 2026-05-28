<?php
require_once '../includes/auth.php';
if (!is_logged_in() || (!is_admin() && !is_agent())) redirect('../pages/login.php');

$agent_id = $_SESSION['user_id'];

// Get unique chat sessions for this admin/agent
$stmt = db()->prepare("
    SELECT c.session_id, MAX(c.created_at) as last_msg_time, 
    (SELECT message FROM chat_messages WHERE session_id = c.session_id ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_messages c
    WHERE c.agent_id = ?
    GROUP BY c.session_id
    ORDER BY last_msg_time DESC
");
$stmt->execute([$agent_id]);
$sessions = $stmt->fetchAll();

$active_session = $_GET['session'] ?? ($sessions[0]['session_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Dashboard - Hawassa Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; height: 100vh; overflow: hidden; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; flex-shrink: 0; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .chat-dashboard { flex: 1; display: flex; background: white; }
        .session-list { width: 300px; border-right: 1px solid var(--color-gray-200); background: var(--color-gray-50); overflow-y: auto; }
        .session-item { padding: 1rem; border-bottom: 1px solid var(--color-gray-200); cursor: pointer; text-decoration: none; display: block; color: inherit; }
        .session-item:hover, .session-item.active { background: white; border-left: 4px solid var(--color-gold); }
        
        .chat-window { flex: 1; display: flex; flex-direction: column; background: white; }
        .chat-header { padding: 1rem 2rem; border-bottom: 1px solid var(--color-gray-200); font-weight: bold; font-size: 1.2rem; }
        .chat-messages { flex: 1; padding: 2rem; overflow-y: auto; background: var(--color-gray-50); display: flex; flex-direction: column; gap: 1rem; }
        
        .chat-message { max-width: 70%; padding: 0.8rem 1.2rem; border-radius: 18px; font-size: 0.95rem; line-height: 1.5; }
        .chat-message.client { align-self: flex-start; background: #ffffff; color: #333; border: 1px solid var(--color-gray-200); border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .chat-message.agent { align-self: flex-end; background: var(--color-gold); color: white; border-bottom-right-radius: 4px; box-shadow: 0 2px 5px rgba(184, 134, 11, 0.2); }
        
        .chat-input { padding: 1rem; background: #fff8e1; border-top: 1px solid var(--color-gold); display: flex; gap: 1rem; justify-content: space-between; }
.chat-input input { flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--color-gold); border-radius: 24px; outline: none; background: white; color: #333; }
.chat-input button { background: var(--color-gold); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 24px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>Hawassa Chat</h3>
            <?php if (is_admin()): ?>
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="properties.php">🏠 Properties</a>
                <a href="agents.php">👥 Agents</a>
                <a href="testimonials.php">⭐ Testimonials</a>
                <a href="announcements.php">📢 Offers</a>
                <a href="chat.php" style="color: var(--color-gold);">💬 Chats</a>
                <a href="chats.php">👁️ Chat Oversight</a>
            <?php else: ?>
                <a href="../pages/agent_dashboard.php">← Back to Dashboard</a>
                <a href="chat.php" style="color: var(--color-gold);">💬 My Chats</a>
            <?php endif; ?>
        </aside>
        
        <div class="chat-dashboard">
            <div class="session-list">
                <div style="padding: 1rem; font-weight: bold; border-bottom: 1px solid var(--color-gray-200);">Active Conversations</div>
                <?php foreach ($sessions as $s): ?>
                    <a href="chat.php?session=<?php echo $s['session_id']; ?>" class="session-item <?php echo $s['session_id'] === $active_session ? 'active' : ''; ?>">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Client <?php echo substr($s['session_id'], 0, 6); ?></div>
                        <div style="font-size: 0.85rem; color: var(--color-gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($s['last_message']); ?>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($sessions)): ?>
                    <div style="padding: 1rem; color: var(--color-gray-500); font-size: 0.9rem;">No active chats.</div>
                <?php endif; ?>
            </div>
            
            <div class="chat-window">
                <?php if ($active_session): ?>
                    <div class="chat-header">
                        Conversation with Client <?php echo substr($active_session, 0, 6); ?>
                    </div>
                    <div class="chat-messages" id="chatBox">
                        <!-- Loaded via AJAX -->
                    </div>
                    <form class="chat-input" id="chatForm">
                        <input type="text" id="msgInput" placeholder="Type your reply..." required autocomplete="off">
                        <button type="submit">Send Reply</button>
                    </form>
                <?php else: ?>
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: var(--color-gray-500);">
                        Select a conversation to start chatting
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($active_session): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');
        const msgInput = document.getElementById('msgInput');
        const sessionId = "<?php echo $active_session; ?>";
        const agentId = <?php echo $agent_id; ?>;
        let lastMessageCount = 0;

        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function fetchMessages() {
            try {
                const res = await fetch(`../api/chat.php?action=fetch&agent_id=${agentId}&session_id=${sessionId}`);
                const data = await res.json();
                
                if (data.success && data.messages.length > lastMessageCount) {
                    chatBox.innerHTML = '';
                    
                    data.messages.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = 'chat-message ' + (msg.is_agent == 1 ? 'agent' : 'client');
                        div.textContent = msg.message;
                        chatBox.appendChild(div);
                    });
                    
                    scrollToBottom();
                    lastMessageCount = data.messages.length;
                }
            } catch (e) { console.error(e); }
        }

        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = msgInput.value.trim();
            if (!text) return;
            
            msgInput.value = '';
            
            const div = document.createElement('div');
            div.className = 'chat-message agent';
            div.textContent = text;
            chatBox.appendChild(div);
            scrollToBottom();
            
            try {
                await fetch('../api/chat.php?action=send', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ agent_id: agentId, session_id: sessionId, message: text })
                });
                fetchMessages();
            } catch (e) { console.error(e); }
        });

        setInterval(fetchMessages, 3000);
        fetchMessages();
    });
    </script>
    <?php endif; ?>
</body>
</html>
