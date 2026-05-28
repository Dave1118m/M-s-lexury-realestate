<?php
/**
 * Hawassa Luxury Real Estate - Agent CRM Dashboard (Agent Layout)
 */
require_once '../includes/agent_header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if (is_admin()) {
    redirect('../admin/dashboard.php');
}

if (!is_agent()) {
    redirect('dashboard.php');
}

$agent_id = $_SESSION['user_id'];



// Fetch properties managed by this agent
$stmt_props = db()->prepare("SELECT * FROM properties WHERE agent_id = ? ORDER BY created_at DESC");
$stmt_props->execute([$agent_id]);
$agent_properties = $stmt_props->fetchAll();



// Fetch unique users who have chatted with this agent
$stmt_chats = db()->prepare("
    SELECT DISTINCT session_id, sender_id, u.name as user_name
    FROM chat_messages cm
    LEFT JOIN users u ON cm.sender_id = u.id
    WHERE agent_id = ?
");
$stmt_chats->execute([$agent_id]);
$chat_sessions = $stmt_chats->fetchAll();

?>

<section class="dashboard-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); padding: 4rem 0 2rem; color: #fff;">
    <div class="container">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 0.5rem;">Agent Portal</h1>
        <p style="color: #ecf0f1; font-size: 1.1rem; letter-spacing: 0.05em; text-transform: uppercase;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>
</section>

<section class="dashboard-content" style="padding: 4rem 0; background-color: #f9f9f9; min-height: 50vh;">
    <div class="container">
        <div class="grid-2 dashboard-layout" style="gap: 2rem; align-items: start;">
            
            <!-- Sidebar Navigation -->
            <div class="dashboard-card dashboard-sidebar-card">
                <ul class="dashboard-nav">
                    <li><a href="#listings">🏠 My Listings</a></li>
                    <li><a href="#messages">💬 Messages</a></li>
                    <li class="dashboard-sidebar-action">
                        <a href="../admin/dashboard.php" class="btn btn-gold dashboard-full-width">Go to Advanced Portal</a>
                        <small>Manage your listings and write blog posts</small>
                    </li>
                    <li><a href="../admin/blog_form.php" class="btn btn-gold btn-sm dashboard-full-width">+ Add Blog Post</a></li>
                </ul>
            </div>

            <!-- Content Area -->
            <div>
                <!-- My Listings -->
                <div id="listings" class="dashboard-card">
                    <h3>My Listings</h3>
                    <?php if (empty($agent_properties)): ?>
                        <p class="dashboard-note">You have no properties assigned to you.</p>
                    <?php else: ?>
                        <div class="listings-grid">
                            <?php foreach ($agent_properties as $prop): ?>
                                <div class="listing-item">
                                    <img src="<?php echo $prop['image_main'] ?: get_dynamic_property_image($prop['id']); ?>" alt="<?php echo $prop['title']; ?>">
                                    <div>
                                        <h4><a href="property.php?slug=<?php echo $prop['slug']; ?>"><?php echo $prop['title']; ?></a></h4>
                                        <div class="listing-price"><?php echo format_price($prop['price']); ?></div>
                                        <div class="listing-status">Status: <?php echo ucfirst($prop['status']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages -->
                <div id="messages" class="dashboard-card">
                    <h3>Active Conversations</h3>
                    <?php if (empty($chat_sessions)): ?>
                        <p class="dashboard-note">No active conversations.</p>
                    <?php else: ?>
                        <div class="chat-list">
                            <?php foreach ($chat_sessions as $session): ?>
                                <div class="chat-item">
                                    <div>
                                        <strong><?php echo $session['user_name'] ? htmlspecialchars($session['user_name']) : 'Guest User'; ?></strong>
                                        <span>Session ID: <?php echo substr($session['session_id'], 0, 8); ?>...</span>
                                    </div>
                                    <a href="../admin/chat.php?session=<?php echo urlencode($session['session_id']); ?>" class="btn btn-outline">View Chat</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<?php
require_once '../includes/agent_footer.php'; ?>
