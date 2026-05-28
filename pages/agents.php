<?php
require_once '../includes/header.php';
$agents = get_agents(12);
?>

<section style="padding: 4rem 0;">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a> &raquo; <span>Our Agents</span>
        </div>

        <h2 class="section-title">Our Exceptional Agents</h2>
        <p class="section-subtitle">Meet the dedicated professionals who make Hawassa the leader in luxury real estate</p>

        <div class="agent-grid">
            <?php foreach ($agents as $agent): ?>
                <div class="agent-card fade-up">
                    <img src="<?php echo $agent['avatar'] ?: '../assets/images/placeholder.jpg'; ?>" alt="<?php echo $agent['name']; ?>" class="agent-avatar">
                    <h3><?php echo $agent['name']; ?></h3>
                    <p class="agent-title">Licensed Real Estate Agent</p>
                    <p class="agent-contact">📧 <?php echo $agent['email']; ?></p>
                    <p class="agent-contact">📞 <?php echo $agent['phone'] ?: '+251 911 234 567'; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>