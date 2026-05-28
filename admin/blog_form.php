<?php
require_once '../includes/auth.php';
if (!is_logged_in() || (!is_admin() && !is_agent())) redirect('../pages/login.php');

$is_agent = is_agent();
$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;
$post = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    
    if ($post && $is_agent && $post['author_id'] != $user_id) {
        die("Unauthorized to edit this post.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Post - Hawassa Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-main { flex: 1; padding: 2rem; background: var(--color-gray-50); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 4px; }
        .admin-form { background: white; padding: 2rem; border-radius: 8px; max-width: 900px; }
        
        /* Ensure TinyMCE is large enough */
        .tox-tinymce { min-height: 500px !important; }
    </style>
    <!-- TinyMCE for Rich Text Editing (Graphs, Images, Icons, Paragraphs) -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#richContent',
        plugins: 'image link media table lists code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image media | code',
        menubar: false
      });
    </script>
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
            <h2><?php echo $id ? 'Edit' : 'Add'; ?> Insight / Blog Post</h2>
            <div class="admin-form">
                <form action="../api/blog.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group" style="display: flex; gap: 1rem;">
                        <div style="flex: 1;">
                            <label>Category</label>
                            <select name="category">
                                <?php 
                                $cats = ['Market Report', 'Neighborhood Guide', 'Architecture & Design', 'Company News', 'General'];
                                foreach ($cats as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($post['category'] ?? '') == $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Status</label>
                            <select name="status">
                                <option value="published" <?php echo ($post['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($post['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Excerpt (Short Description for the card)</label>
                        <input type="text" name="excerpt" value="<?php echo htmlspecialchars($post['excerpt'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Cover Image (Upload from PC or provide URL)</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if(!empty($post['image'])): ?>
                            <div style="margin-top: 1rem;">
                                <img src="<?php echo strpos($post['image'], 'http') === 0 ? $post['image'] : '../' . $post['image']; ?>" style="max-width: 200px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                        <small style="color: gray;">(Optional: Provide an Unsplash image URL instead below)</small>
                        <input type="text" name="image_url" placeholder="https://images.unsplash.com/..." value="<?php echo (strpos($post['image'] ?? '', 'http') === 0) ? htmlspecialchars($post['image']) : ''; ?>" style="margin-top: 0.5rem;">
                    </div>

                    <div class="form-group">
                        <label>Rich Content (Add Paragraphs, Icons, Graphs, Images)</label>
                        <textarea id="richContent" name="content"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-gold">Save Post</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
