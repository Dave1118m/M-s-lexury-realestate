<?php
require_once '../includes/auth.php';
if (!is_logged_in() || !is_admin()) redirect('../pages/login.php');

$announcements = db()->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Offers - Hawassa Admin</title>
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
            <h3>Hawassa Admin</h3>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="properties.php">🏠 Properties</a>
            <a href="agents.php">👥 Agents</a>
            <a href="testimonials.php">⭐ Testimonials</a>
            <a href="announcements.php" style="color: var(--color-gold);">📢 Offers</a>
            <a href="chat.php">💬 Chats</a>
        </aside>
        <main class="admin-main">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Manage Offers & Announcements</h2>
                <a href="announcement_form.php" class="btn btn-gold btn-sm">+ Add Offer</a>
            </div>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Discount</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $a): ?>
                        <tr>
                            <td><?php echo $a['id']; ?></td>
                            <td><?php echo $a['title']; ?></td>
                            <td><?php echo $a['discount_percentage']; ?>%</td>
                            <td><?php echo $a['status']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($a['created_at'])); ?></td>
                            <td>
                                <a href="announcement_form.php?id=<?php echo $a['id']; ?>" class="btn btn-dark btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
