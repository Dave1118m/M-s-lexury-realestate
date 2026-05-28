<?php
require_once '../includes/auth.php';
if (!is_logged_in() || (!is_admin() && !is_agent())) redirect('../pages/login.php');

$is_agent = is_agent();
$user_id = $_SESSION['user_id'];

if ($is_agent) {
    $property_count = db()->query("SELECT COUNT(*) FROM properties WHERE agent_id = $user_id")->fetchColumn();
    // Inquiries for properties owned by this agent
    $inquiry_count = db()->query("SELECT COUNT(*) FROM inquiries i JOIN properties p ON i.property_id = p.id WHERE p.agent_id = $user_id")->fetchColumn();
    $blog_count = db()->query("SELECT COUNT(*) FROM blog_posts WHERE author_id = $user_id")->fetchColumn();
    // Agents shouldn't see total user count, just set to 0 or hide
    $user_count = 0; 
} else {
    $property_count = db()->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $user_count = db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $inquiry_count = db()->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
    $blog_count = db()->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_agent ? 'Agent Portal' : 'Admin Dashboard'; ?> - Hawassa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; font-size: 1.2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-sidebar a:hover { color: var(--color-gold); }
        .admin-main { flex: 1; padding: 2rem; background: var(--color-gray-50); }
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow-md); }
        .stat-card .number { font-family: var(--font-display); font-size: 2.5rem; color: var(--color-gold); }
        .stat-card .label { color: var(--color-gray-500); margin-top: 0.5rem; }
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
            <a href="blog.php">📝 Blog</a>
            <?php if (!$is_agent): ?>
                <a href="users.php">👤 Users</a>
            <?php endif; ?>
            <a href="logout.php" style="color: #e74c3c;">🚪 Logout</a>
            <a href="../pages/agent_dashboard.php" style="color: #3498db; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">⬅️ Back to Frontend</a>
        </aside>
        <main class="admin-main">
            <h2><?php echo $is_agent ? 'Agent Portal' : 'Dashboard'; ?></h2>
            <p style="color: var(--color-gray-500); margin-bottom: 2rem;">Welcome, <?php echo $_SESSION['user_name']; ?>!</p>
            <div class="stats-cards">
                <div class="stat-card"><div class="number"><?php echo (int)$property_count; ?></div><div class="label">Total Properties</div></div>
                <?php if (!$is_agent): ?>
                    <div class="stat-card"><div class="number"><?php echo (int)$user_count; ?></div><div class="label">Total Users</div></div>
                <?php endif; ?>
                <div class="stat-card"><div class="number"><?php echo (int)$inquiry_count; ?></div><div class="label">Inquiries</div></div>
                <div class="stat-card"><div class="number"><?php echo (int)$blog_count; ?></div><div class="label">Blog Posts</div></div>
            </div>
            
            <!-- Chart Container -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: var(--shadow-md); max-width: 500px; margin: 0 auto; position: relative; height: 400px; display: flex; flex-direction: column;">
                <h3 style="text-align: center; margin-bottom: 1rem; color: var(--color-black);">System Overview</h3>
                <div style="flex: 1; position: relative; width: 100%;">
                    <canvas id="analyticsPieChart"></canvas>
                </div>
            </div>
            
            <!-- Pass PHP data to JS -->
            <script>
                window.dashboardData = {
                    properties: <?php echo (int)$property_count; ?>,
                    users: <?php echo (int)$user_count; ?>,
                    inquiries: <?php echo (int)$inquiry_count; ?>,
                    blogs: <?php echo (int)$blog_count; ?>
                };
            </script>
        </main>
    </div>
    <!-- Include Chart.js for premium animations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/admin.js?v=<?php echo time(); ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const pieCanvas = document.querySelector('#analyticsPieChart');
        if (pieCanvas && typeof Chart !== 'undefined') {
            const ctx = pieCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Properties', 'Users', 'Inquiries', 'Blog Posts'],
                    datasets: [{
                        data: [
                            window.dashboardData.properties,
                            window.dashboardData.users,
                            window.dashboardData.inquiries,
                            window.dashboardData.blogs
                        ],
                        backgroundColor: ['#c8a96e', '#1a1a1a', '#666666', '#d1c4a1'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: "'Inter', sans-serif", size: 13 },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>