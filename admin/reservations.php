<?php
/**
 * Hawassa Luxury Real Estate - Reservations Management Page (Admin)
 * View and manage all reservations and verify validation system
 */
require_once '../includes/header.php';

// Check admin access
if (!is_admin()) {
    redirect('login.php');
}

$db = db();

// Get all active reservations
$stmt = $db->prepare("
    SELECT r.*, p.title, p.slug, p.price, p.status as property_status, 
           u.name as user_name, u.email as user_email
    FROM reservations r 
    JOIN properties p ON r.property_id = p.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status = 'active'
    ORDER BY r.created_at DESC
");
$stmt->execute();
$active_reservations = $stmt->fetchAll();

// Get all completed reservations
$stmt = $db->prepare("
    SELECT r.*, p.title, p.slug, p.price, 
           u.name as user_name, u.email as user_email
    FROM reservations r 
    JOIN properties p ON r.property_id = p.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status = 'completed'
    ORDER BY r.created_at DESC
    LIMIT 20
");
$stmt->execute();
$completed_reservations = $stmt->fetchAll();

// Get all cancelled reservations
$stmt = $db->prepare("
    SELECT r.*, p.title, p.slug, p.price, 
           u.name as user_name, u.email as user_email
    FROM reservations r 
    JOIN properties p ON r.property_id = p.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status = 'cancelled'
    ORDER BY r.created_at DESC
    LIMIT 20
");
$stmt->execute();
$cancelled_reservations = $stmt->fetchAll();

// Get property availability summary
$stmt = $db->query("
    SELECT 
        status,
        COUNT(*) as count
    FROM properties 
    GROUP BY status
");
$property_summary = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<section style="padding: 2rem; min-height: 100vh; background: var(--color-cream);">
    <div class="container">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 1rem;">📊 Reservations Management</h1>
        <p style="color: var(--color-gray-500); margin-bottom: 2rem;">View and manage all property reservations with validation verification.</p>

        <!-- Summary Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background: white; border-radius: 8px; padding: 1.5rem; border-left: 4px solid var(--color-gold);">
                <h3 style="color: var(--color-gold); font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Active Reservations</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--color-black);"><?php echo count($active_reservations); ?></p>
                <small style="color: var(--color-gray-500);">Properties with pending reservations</small>
            </div>

            <div style="background: white; border-radius: 8px; padding: 1.5rem; border-left: 4px solid #28a745;">
                <h3 style="color: #28a745; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Completed</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--color-black);"><?php echo count($completed_reservations); ?></p>
                <small style="color: var(--color-gray-500);">Successfully finalized purchases</small>
            </div>

            <div style="background: white; border-radius: 8px; padding: 1.5rem; border-left: 4px solid #dc3545;">
                <h3 style="color: #dc3545; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Cancelled</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--color-black);"><?php echo count($cancelled_reservations); ?></p>
                <small style="color: var(--color-gray-500);">Cancelled reservations</small>
            </div>

            <div style="background: white; border-radius: 8px; padding: 1.5rem; border-left: 4px solid #6c757d;">
                <h3 style="color: #6c757d; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Total Properties</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--color-black);"><?php echo array_sum($property_summary); ?></p>
                <small style="color: var(--color-gray-500);">All properties in system</small>
            </div>
        </div>

        <!-- Property Status Distribution -->
        <div style="background: white; border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1.5rem;">Property Inventory Status</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <?php 
                $status_colors = [
                    'active' => ['color' => '#28a745', 'label' => 'Available'],
                    'pending' => ['color' => '#ffc107', 'label' => 'Reserved'],
                    'sold' => ['color' => '#dc3545', 'label' => 'Sold']
                ];
                
                foreach ($status_colors as $status => $info):
                    $count = $property_summary[$status] ?? 0;
                ?>
                    <div style="background: rgba(<?php echo implode(',', sscanf($info['color'], '#%02x%02x%02x')); ?>, 0.1); border: 2px solid <?php echo $info['color']; ?>; border-radius: 6px; padding: 1rem; text-align: center;">
                        <p style="color: <?php echo $info['color']; ?>; font-weight: bold; font-size: 1.5rem;"><?php echo $count; ?></p>
                        <p style="color: <?php echo $info['color']; ?>; font-size: 0.9rem; text-transform: capitalize;"><?php echo $info['label']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Validation Constraints Reference -->
        <div style="background: white; border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1.5rem;">✓ Active Validation Constraints</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div style="border-left: 4px solid var(--color-gold); padding-left: 1rem;">
                    <h4 style="margin: 0 0 0.5rem; color: var(--color-gold);">Date Validation</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--color-gray-600); font-size: 0.9rem; line-height: 1.6;">
                        <li>Cannot reserve property for past date</li>
                        <li>Cannot reserve more than 1 year in advance</li>
                        <li>Must select valid future date</li>
                    </ul>
                </div>

                <div style="border-left: 4px solid #28a745; padding-left: 1rem;">
                    <h4 style="margin: 0 0 0.5rem; color: #28a745;">Property Status</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--color-gray-600); font-size: 0.9rem; line-height: 1.6;">
                        <li>Cannot reserve 'sold' properties</li>
                        <li>Cannot reserve 'pending' (already reserved) properties</li>
                        <li>Status changes: active → pending → sold</li>
                    </ul>
                </div>

                <div style="border-left: 4px solid #dc3545; padding-left: 1rem;">
                    <h4 style="margin: 0 0 0.5rem; color: #dc3545;">User Constraints</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--color-gray-600); font-size: 0.9rem; line-height: 1.6;">
                        <li>Cannot double-book same property</li>
                        <li>Only one active reservation per user-property</li>
                        <li>Unique database constraint enforced</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Active Reservations Table -->
        <div style="background: white; border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1.5rem;">🔒 Active Reservations (<?php echo count($active_reservations); ?>)</h2>
            
            <?php if ($active_reservations): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--color-cream); border-bottom: 2px solid var(--color-gray-300);">
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Property</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Client</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Reservation Date</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Hold Amount</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Reserved On</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_reservations as $res): ?>
                                <tr style="border-bottom: 1px solid var(--color-gray-200); hover: { background: var(--color-cream); }">
                                    <td style="padding: 1rem;">
                                        <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                        <small style="color: var(--color-gray-500);">ID: <?php echo $res['property_id']; ?></small>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo htmlspecialchars($res['user_name']); ?><br>
                                        <small style="color: var(--color-gray-500);"><?php echo htmlspecialchars($res['user_email']); ?></small>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <strong><?php echo date('M d, Y', strtotime($res['reservation_date'])); ?></strong>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo format_price($res['hold_amount']); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <small style="color: var(--color-gray-500);"><?php echo date('M d, Y', strtotime($res['created_at'])); ?></small>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span style="background: rgba(200, 169, 110, 0.2); color: var(--color-gold); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                            <?php echo ucfirst($res['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--color-gray-500); text-align: center; padding: 2rem;">No active reservations at this time.</p>
            <?php endif; ?>
        </div>

        <!-- Validation Test Scenarios -->
        <div style="background: white; border-radius: 8px; padding: 2rem;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1.5rem;">🧪 Validation Testing Scenarios</h2>
            
            <div style="background: #f8f9fa; border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.75rem; color: var(--color-black);">Scenario 1: Past Date Rejection</h4>
                <p style="margin: 0; color: var(--color-gray-600); font-size: 0.9rem;">User attempts to reserve property for today or earlier → <strong style="color: #dc3545;">Should fail</strong> with "cannot be in the past" error</p>
            </div>

            <div style="background: #f8f9fa; border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.75rem; color: var(--color-black);">Scenario 2: Double Reservation Prevention</h4>
                <p style="margin: 0; color: var(--color-gray-600); font-size: 0.9rem;">User A reserves Property X, then User B attempts same → <strong style="color: #dc3545;">Should fail</strong> with "already reserved" error</p>
            </div>

            <div style="background: #f8f9fa; border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem;">
                <h4 style="margin: 0 0 0.75rem; color: var(--color-black);">Scenario 3: Sold Property Protection</h4>
                <p style="margin: 0; color: var(--color-gray-600); font-size: 0.9rem;">Admin marks property status as 'sold', user attempts reservation → <strong style="color: #dc3545;">Should fail</strong> with "already sold" error</p>
            </div>

            <div style="background: #f8f9fa; border-radius: 6px; padding: 1.5rem;">
                <h4 style="margin: 0 0 0.75rem; color: var(--color-black);">Scenario 4: Valid Reservation Success</h4>
                <p style="margin: 0; color: var(--color-gray-600); font-size: 0.9rem;">User selects active property with future date, completes payment → <strong style="color: #28a745;">Should succeed</strong>, property status changes to 'pending', reservation recorded</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
