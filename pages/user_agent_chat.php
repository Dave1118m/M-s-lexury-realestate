<?php
require_once '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$agent_id = $_GET['agent_id'] ?? 0;
if (!$agent_id) {
    echo "<div class='container' style='padding: 4rem 0;'><h3>Error: No agent specified.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

// Fetch agent details for UI
$stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'agent'");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
if (!$agent) {
    echo "<div class='container' style='padding: 4rem 0;'><h3>Error: Agent not found.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}
?>

<style>
    .chat-container {
        max-width: 800px;
        margin: 4rem auto;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 600px;
    }
    .chat-header {
        background: var(--color-black);
        color: var(--color-gold);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .chat-messages {
        flex: 1;
        padding: 1.5rem;
        overflow-y: auto;
        background: var(--color-gray-50);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .chat-message { max-width: 70%; padding: 0.9rem 1.1rem; border-radius: 12px; line-height: 1.4; }
    .chat-message.client { align-self: flex-end; background: var(--color-gold); color: white; border-bottom-right-radius: 4px; }
    .chat-message.agent { align-self: flex-start; background: white; border: 1px solid var(--color-gray-200); color: #333; }
    .msg-sender { font-size: 0.78rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--color-gray-600); }
    .chat-message.agent .msg-sender { color: var(--color-gray-500); }
    .msg-time { font-size: 0.75rem; opacity: 0.7; margin-top: 0.35rem; text-align: right; }
</style>

<div class="chat-container">
    <div class="chat-header">
        <img src="<?php echo $agent['avatar'] ?: '../assets/images/placeholder.jpg'; ?>" alt="Agent" width="50" height="50" style="border-radius:50%;border:2px solid var(--color-gold);object-fit:cover;">
        <div>
            <h3 style="margin:0; font-family: var(--font-display);"><?php echo htmlspecialchars($agent['name']); ?></h3>
            <span style="font-size:0.8rem;color:var(--color-gray-300);">Your personal luxury concierge</span>
        </div>
    </div>
    <div class="chat-messages" id="chatBox">
        <div class="chat-message agent">
            <div class="msg-sender"><?php echo htmlspecialchars($agent['name']); ?></div>
            <div>Hello! I'm <?php echo htmlspecialchars($agent['name']); ?>. How may I assist you with our premium properties today?</div>
        </div>
    </div>
    <form class="chat-input" id="chatForm">
        <input type="text" id="msgInput" placeholder="Type your message..." required autocomplete="off">
        <button type="submit">Send</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');
        const msgInput = document.getElementById('msgInput');
        const agentId = <?php echo $agent_id; ?>;
        let lastMessageCount = 0;
        function scrollToBottom(){ chatBox.scrollTop = chatBox.scrollHeight; }
        async function fetchMessages(){
            try {
                const res = await fetch(`../api/user_agent_chat.php?action=fetch&agent_id=${agentId}`);
                const data = await res.json();
                if (data.success) {
                    // Re-render messages (keeps ordering and shows both sides clearly)
                    chatBox.innerHTML = '';
                    if (!data.messages.length) {
                        chatBox.innerHTML = `<div class="chat-message agent"><div class="msg-sender"><?php echo htmlspecialchars($agent['name']); ?></div><div>Hello! I'm <?php echo htmlspecialchars($agent['name']); ?>. How may I assist you with our premium properties today?</div></div>`;
                    } else {
                        data.messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.className = 'chat-message ' + (msg.is_agent == 1 ? 'agent' : 'client');

                            const sender = document.createElement('div');
                            sender.className = 'msg-sender';
                            sender.textContent = msg.is_agent == 1 ? (msg.sender_name || 'Agent') : 'You';

                            const text = document.createElement('div');
                            text.textContent = msg.message;

                            const time = document.createElement('div');
                            time.className = 'msg-time';
                            time.textContent = msg.created_at ? new Date(msg.created_at).toLocaleString() : '';

                            div.appendChild(sender);
                            div.appendChild(text);
                            div.appendChild(time);
                            chatBox.appendChild(div);
                        });
                    }
                    scrollToBottom();
                    lastMessageCount = data.messages.length;
                }
            } catch(e){ console.error(e); }
        }
        chatForm.addEventListener('submit', async function(e){
            e.preventDefault();
            const text = msgInput.value.trim();
            if(!text) return;
            msgInput.value = '';
            const div = document.createElement('div');
            div.className = 'chat-message client';
            div.textContent = text;
            chatBox.appendChild(div);
            scrollToBottom();
            try {
                await fetch('../api/user_agent_chat.php?action=send', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({agent_id: agentId, message: text})
                });
                fetchMessages();
            } catch(e){ console.error(e); }
        });
        setInterval(fetchMessages, 3000);
        fetchMessages();
    });
</script>

<?php require_once '../includes/footer.php'; ?>
