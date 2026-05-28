<?php
require_once '../includes/auth.php';
if (!is_logged_in() || !is_admin()) redirect('../pages/login.php');

$id = $_GET['id'] ?? 0;
$announcement = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();
}

$properties = db()->query("SELECT id, title FROM properties ORDER BY title")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Offer - Hawassa Admin</title>
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
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 4px; }
        .admin-form { background: white; padding: 2rem; border-radius: 8px; max-width: 600px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>Hawassa Admin</h3>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="properties.php">🏠 Properties</a>
            <a href="agents.php">👥 Agents</a>
            <a href="testimonials.php">⭐ Testimonials</a>
            <a href="announcements.php" style="color: var(--color-gold);">📢 Offers</a>
            <a href="chat.php">💬 Chats</a>
        </aside>
        <main class="admin-main">
            <h2><?php echo $id ? 'Edit' : 'Add'; ?> Offer</h2>
            <div class="admin-form">
                <form action="../api/announcements.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($announcement['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Content / Description</label>
                        <textarea name="content" required rows="5"><?php echo htmlspecialchars($announcement['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Discount Percentage (e.g. 15 for 15% OFF)</label>
                        <input type="number" name="discount_percentage" min="0" max="100" value="<?php echo htmlspecialchars($announcement['discount_percentage'] ?? '0'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Link to Specific Property (Optional)</label>
                        <select name="property_id">
                            <option value="">-- None (General Offer) --</option>
                            <?php foreach ($properties as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($announcement['property_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($announcement['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($announcement['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-gold">Save Offer</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
