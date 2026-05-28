<?php
require_once '../includes/auth.php';
if (!is_logged_in() || (!is_admin() && !is_agent())) redirect('../pages/login.php');

$is_agent = is_agent();
$user_id = $_SESSION['user_id'];

if ($is_agent) {
    $posts = db()->query("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.author_id = $user_id ORDER BY bp.created_at DESC")->fetchAll();
} else {
    $posts = db()->query("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id ORDER BY bp.created_at DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Hawassa Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-sidebar a:hover { color: var(--color-gold); }
        .admin-main { flex: 1; padding: 2rem; background: var(--color-gray-50); }
        table { width: 100%; background: white; border-radius: 8px; box-shadow: var(--shadow-md); border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-gray-200); }
        th { background: var(--color-gray-100); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3><?php echo $is_agent ? 'Agent Portal' : 'Hawassa Admin'; ?></h3>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="properties.php">🏠 Properties</a>
            <?php if (!$is_agent): ?>
                <a href="agents.php">👥 Agents</a>
            <?php endif; ?>
            <a href="blog.php" style="color: var(--color-gold);">📝 Insights/Blog</a>
            <?php if (!$is_agent): ?>
                <a href="testimonials.php">⭐ Testimonials</a>
                <a href="announcements.php">📢 Offers</a>
                <a href="chat.php">💬 Chats</a>
                <a href="users.php">👤 Users</a>
            <?php endif; ?>
            <?php if ($is_agent): ?>
                <a href="../admin/chat.php">💬 Chats</a>
            <?php endif; ?>
            <a href="logout.php" style="color: #e74c3c;">🚪 Logout</a>
            <a href="../pages/agent_dashboard.php" style="color: #3498db; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">⬅️ Back to Frontend</a>
        </aside>
        <main class="admin-main">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Manage Blog Posts</h2>
                <a href="blog_form.php" class="btn btn-gold btn-sm">+ New Post</a>
            </div>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $bp): ?>
                        <tr>
                            <td><?php echo $bp['id']; ?></td>
                            <td><?php echo $bp['title']; ?></td>
                            <td><?php echo $bp['author_name']; ?></td>
                            <td><?php echo $bp['status']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($bp['created_at'])); ?></td>
                            <td>
                                <a href="blog_form.php?id=<?php echo $bp['id']; ?>" class="btn btn-dark btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>