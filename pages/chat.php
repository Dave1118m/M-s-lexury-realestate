<?php
require_once '../includes/header.php';

$agent_id = $_GET['agent_id'] ?? 0;
$property_id = $_GET['property_id'] ?? 0;

if (!$agent_id) {
    echo "<div class='container' style='padding: 4rem 0;'><h3>Error: No agent specified.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

$stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'agent'");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

if (!$agent) {
    echo "<div class='container' style='padding: 4rem 0;'><h3>Error: Agent not found.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

// Basic Session ID for guests
if (!isset($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['chat_session_id'];

?>

<style>
.chat-container { max-width: 800px; margin: 4rem auto; background: white; border-radius: 12px; box-shadow: var(--shadow-md); overflow: hidden; display: flex; flex-direction: column; height: 600px; }
.chat-header { background: var(--color-black); color: var(--color-gold); padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
.chat-header img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold); }
.chat-messages { flex: 1; padding: 1.5rem; overflow-y: auto; background: var(--color-gray-50); display: flex; flex-direction: column; gap: 1rem; }
.chat-message { max-width: 70%; padding: 1rem; border-radius: 8px; line-height: 1.4; }
.chat-message.client { align-self: flex-end; background: var(--color-gold); color: white; border-bottom-right-radius: 0; }
.chat-message.agent { align-self: flex-start; background: white; border: 1px solid var(--color-gray-200); border-bottom-left-radius: 0; box-shadow: var(--shadow-sm); }
.chat-input { padding: 1rem; background: white; border-top: 1px solid var(--color-gray-200); display: flex; gap: 1rem; }
.chat-input input { flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--color-gray-300); border-radius: 24px; outline: none; }
.chat-input input:focus { border-color: var(--color-gold); }
.chat-input button { background: var(--color-gold); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 24px; cursor: pointer; font-weight: 600; }
</style>

<div class="chat-container">
    <div class="chat-header">
        <img src="<?php echo $agent['avatar'] ?: '../assets/images/placeholder.jpg'; ?>" alt="Agent">
        <div>
            <h3 style="margin: 0; font-family: 'Playfair Display', serif;"><?php echo htmlspecialchars($agent['name']); ?></h3>
            <span style="font-size: 0.8rem; color: var(--color-gray-300);">Typically replies in a few minutes</span>
        </div>
    </div>
    
    <div class="chat-messages" id="chatBox">
        <!-- Messages will be loaded here via AJAX -->
        <div class="chat-message agent">
            Hello! I am <?php echo htmlspecialchars($agent['name']); ?>. How can I help you with your luxury real estate needs today?
        </div>
    </div>
    
    <form class="chat-input" id="chatForm">
        <input type="text" id="msgInput" placeholder="Type your message here..." required autocomplete="off">
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

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function fetchMessages() {
        try {
            const res = await fetch(`../api/chat.php?action=fetch&agent_id=${agentId}`);
            const data = await res.json();

            if (data.success) {
                chatBox.innerHTML = '';
                if (!data.messages.length) {
                    chatBox.innerHTML = `<div class="chat-message agent"><div class="msg-sender"><?php echo htmlspecialchars($agent['name']); ?></div><div>Hello! I am <?php echo htmlspecialchars($agent['name']); ?>. How can I help you with your luxury real estate needs today?</div></div>`;
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
        } catch (e) { console.error(e); }
    }

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const text = msgInput.value.trim();
        if (!text) return;
        
        msgInput.value = '';
        
        // Optimistic UI update
        const div = document.createElement('div');
        div.className = 'chat-message client';
        div.textContent = text;
        chatBox.appendChild(div);
        scrollToBottom();
        
        try {
            await fetch('../api/chat.php?action=send', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ agent_id: agentId, message: text })
            });
            fetchMessages(); // refresh to get official state
        } catch (e) { console.error(e); }
    });

    // Poll every 3 seconds
    setInterval(fetchMessages, 3000);
    fetchMessages();
});
</script>

<?php require_once '../includes/footer.php'; ?>
