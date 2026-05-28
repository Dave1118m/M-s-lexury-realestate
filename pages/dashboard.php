<?php
/**
 * Hawassa Luxury Real Estate - User Dashboard
 */
require_once '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if (is_admin()) {
    redirect('../admin/dashboard.php');
}

if (is_agent()) {
    redirect('agent_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$saved_properties = get_saved_properties($user_id);
$saved_searches = get_saved_searches($user_id);

$my_reservations = [];
try {
    $stmt = db()->prepare("
        SELECT p.*, pay.amount as amount_paid, pay.created_at as payment_date, pay.transaction_reference, pay.payment_method
        FROM properties p
        JOIN payments pay ON p.id = pay.property_id
        WHERE pay.user_id = ?
        ORDER BY pay.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $my_reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    // payments table may not exist yet, fallback gracefully
}
?>

<section class="dashboard-header" style="background: linear-gradient(135deg, var(--color-dark) 0%, #1a1a1a 100%); padding: 4rem 0 2rem; color: #fff;">
    <div class="container">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 0.5rem;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <p style="color: var(--color-gold); font-size: 1.1rem; letter-spacing: 0.05em; text-transform: uppercase;">Hawassa Luxury Member</p>
    </div>
</section>

<section class="dashboard-content" style="padding: 4rem 0; background-color: #f9f9f9; min-height: 50vh;">
    <div class="container">
        <div class="grid-2 dashboard-layout" style="gap: 2rem; align-items: start;">
            
            <!-- Left Sidebar -->
            <div class="dashboard-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem;">Account Details</h3>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--color-gray-500); font-size: 0.85rem; text-transform: uppercase;">Name</strong>
                    <div style="font-size: 1.1rem; font-weight: 500; margin-top: 0.25rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--color-gray-500); font-size: 0.85rem; text-transform: uppercase;">Email</strong>
                    <div style="font-size: 1.1rem; font-weight: 500; margin-top: 0.25rem;"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="logout.php" class="btn btn-outline" style="border-color: #dc3545; color: #dc3545; width: 100%; text-align: center;">Log Out</a>
                </div>
            </div>

            <!-- Right Content Area -->
            <div>
                <!-- Reserved Properties -->
                <?php if (!empty($my_reservations)): ?>
                <div class="dashboard-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 2rem; border-left: 4px solid var(--color-gold);">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔒 My Reserved Properties</span>
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <?php foreach ($my_reservations as $res): ?>
                            <div style="display: flex; gap: 1.5rem; border: 1px solid rgba(200, 169, 110, 0.25); padding: 1.25rem; border-radius: 6px; position: relative; background: rgba(200, 169, 110, 0.02); flex-wrap: wrap;">
                                <img src="<?php echo $res['image_main'] ?: get_dynamic_property_image($res['id']); ?>" alt="<?php echo $res['title']; ?>" style="width: 120px; height: 90px; object-fit: cover; border-radius: 4px;">
                                <div style="flex: 1; min-width: 200px;">
                                    <h4 style="margin: 0 0 0.5rem; font-size: 1.1rem;"><a href="property.php?slug=<?php echo $res['slug']; ?>" style="color: var(--color-dark); text-decoration: none;"><?php echo htmlspecialchars($res['title']); ?></a></h4>
                                    <p style="color: var(--color-gray-500); font-size: 0.85rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($res['address'] . ', ' . $res['city']); ?></p>
                                    
                                    <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; flex-wrap: wrap;">
                                        <span>Hold Deposit: <strong style="color: var(--color-gold);"><?php echo format_price($res['amount_paid']); ?></strong></span>
                                        <span>Method: <strong><?php echo htmlspecialchars($res['payment_method']); ?></strong></span>
                                        <span>Reference: <code style="background: var(--color-gray-100); padding: 0.15rem 0.35rem; border-radius: 3px; font-size: 0.8rem;"><?php echo htmlspecialchars($res['transaction_reference']); ?></code></span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end;">
                                    <span style="background: rgba(40, 167, 69, 0.1); color: #28a745; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; border: 1px solid rgba(40, 167, 69, 0.2); letter-spacing: 0.05em;">Reserved</span>
                                    <span style="font-size: 0.8rem; color: var(--color-gray-500); margin-top: 0.5rem;"><?php echo date('M d, Y', strtotime($res['payment_date'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Saved Properties -->
                <div class="dashboard-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem;">❤️ My Saved Properties</h3>
                    <?php if (empty($saved_properties)): ?>
                        <p style="color: var(--color-gray-500);">You haven't saved any properties yet.</p>
                        <a href="search.php" class="btn btn-outline" style="margin-top: 1rem; display: inline-block;">Browse Properties</a>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <?php foreach ($saved_properties as $prop): ?>
                                <div style="display: flex; gap: 1rem; align-items: center; border: 1px solid var(--color-gray-200); padding: 1rem; border-radius: 4px;">
                                    <img src="<?php echo $prop['image_main'] ?: get_dynamic_property_image($prop['id']); ?>" alt="<?php echo $prop['title']; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                                    <div>
                                        <h4 style="margin: 0 0 0.25rem; font-size: 1rem;"><a href="property.php?slug=<?php echo $prop['slug']; ?>" style="color: var(--color-dark); text-decoration: none;"><?php echo $prop['title']; ?></a></h4>
                                        <div style="color: var(--color-gold); font-weight: 600; font-size: 0.9rem;"><?php echo format_price($prop['price']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Saved Searches -->
                <div class="dashboard-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-gray-200); padding-bottom: 0.5rem;">🔔 My Saved Searches</h3>
                    <?php if (empty($saved_searches)): ?>
                        <p style="color: var(--color-gray-500);">You have no saved searches.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($saved_searches as $search): 
                                $criteria = json_decode($search['criteria'], true);
                                $query_string = http_build_query($criteria);
                            ?>
                                <div style="border: 1px solid var(--color-gray-200); padding: 1rem; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="display: block;"><?php echo htmlspecialchars($search['search_name']); ?></strong>
                                        <span style="color: var(--color-gray-500); font-size: 0.85rem;">Created <?php echo date('M d, Y', strtotime($search['created_at'])); ?></span>
                                    </div>
                                    <a href="search.php?<?php echo $query_string; ?>" class="btn btn-outline" style="padding: 0.25rem 1rem; font-size: 0.9rem;">View Results</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
