<?php
require_once '../includes/auth.php';
if (!is_logged_in() || !is_admin()) redirect('../pages/login.php');

$id = $_GET['id'] ?? 0;
$testimonial = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonial - Hawassa Admin</title>
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
            <a href="testimonials.php" style="color: var(--color-gold);">⭐ Testimonials</a>
            <a href="announcements.php">📢 Offers</a>
            <a href="chat.php">💬 Chats</a>
        </aside>
        <main class="admin-main">
            <h2><?php echo $id ? 'Edit' : 'Add'; ?> Testimonial</h2>
            <div class="admin-form">
                <form id="tForm" action="../api/testimonials.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($testimonial['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Position / Title</label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($testimonial['position'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Review Content</label>
                        <textarea name="content" required rows="4"><?php echo htmlspecialchars($testimonial['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Rating (1-5)</label>
                        <input type="number" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($testimonial['rating'] ?? '5'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Client Image (Upload from PC)</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if(!empty($testimonial['image'])): ?>
                            <div style="margin-top: 1rem;">
                                <img src="<?php echo strpos($testimonial['image'], 'http') === 0 ? $testimonial['image'] : '../' . $testimonial['image']; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-gold">Save Testimonial</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
