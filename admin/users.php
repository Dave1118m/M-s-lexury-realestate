<?php
require_once '../includes/auth.php';
if (!is_logged_in() || !is_admin()) redirect('../pages/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $uid = (int)$_POST['user_id'];
    
    if ($action === 'promote_agent') {
        db()->prepare("UPDATE users SET role = 'agent' WHERE id = ?")->execute([$uid]);
    } elseif ($action === 'demote_user') {
        db()->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$uid]);
    }
    redirect('users.php');
}

$users = db()->query("SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Hawassa Admin</title>
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
            <a href="blog.php">📝 Blog</a>
            <a href="users.php">👤 Users</a>
            <a href="logout.php" style="color: #e74c3c;">🚪 Logout</a>
        </aside>
        <main class="admin-main">
            <h2 style="margin-bottom: 2rem;">Manage Users</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo $u['name']; ?></td>
                            <td><?php echo $u['email']; ?></td>
                            <td><?php echo ucfirst($u['role']); ?></td>
                            <td><?php echo $u['phone'] ?: '—'; ?></td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <?php if ($u['role'] === 'user'): ?>
                                                <input type="hidden" name="action" value="promote_agent">
                                                <button type="submit" class="btn btn-sm" style="background: var(--color-gold); color: white; border: none;">Make Agent</button>
                                            <?php elseif ($u['role'] === 'agent'): ?>
                                                <input type="hidden" name="action" value="demote_user">
                                                <button type="submit" class="btn btn-sm" style="background: var(--color-gray-400); color: white; border: none;">Remove Agent</button>
                                            <?php endif; ?>
                                        </form>
                                        <a href="#" class="btn btn-sm btn-delete" style="background: #e74c3c; color: white;">Delete</a>
                                    <?php endif; ?>
                                </div>
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