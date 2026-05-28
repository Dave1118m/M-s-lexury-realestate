<?php
require_once '../includes/header.php';

$stmt = db()->query("
    SELECT a.*, p.title as property_title, p.slug as property_slug, p.image_main 
    FROM announcements a 
    LEFT JOIN properties p ON a.property_id = p.id 
    WHERE a.status = 'active' 
    ORDER BY a.created_at DESC
");
$announcements = $stmt->fetchAll();
?>

<!-- Hero Section with Unsplash Image -->
<section style="position: relative; min-height: 50vh; display: flex; align-items: center; overflow: hidden;">
    <div style="position: absolute; inset: 0; z-index: 0;">
        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=2400&q=85"
             alt="Luxury real estate special offers"
             style="width: 100%; height: 100%; object-fit: cover; animation: heroZoom 25s ease-in-out infinite alternate;">
    </div>
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.75) 100%); z-index: 1;"></div>
    <div class="container" style="position: relative; z-index: 2; padding: 5rem 0; color: #fff; text-align: center;">
        <div style="display: inline-block; border: 1px solid rgba(201,169,110,0.5); padding: 0.4rem 1.5rem; border-radius: 30px; margin-bottom: 1.5rem; backdrop-filter: blur(4px); background: rgba(201,169,110,0.08);">
            <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; color: #C9A96E; font-weight: 500;">Limited Time Only</span>
        </div>
        <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2.5rem, 5vw, 4rem); color: #fff; margin-bottom: 1rem; text-shadow: 0 4px 20px rgba(0,0,0,0.4);">
            Special <span style="color: #C9A96E;">Offers</span>
        </h1>
        <p style="font-size: 1.15rem; line-height: 1.7; opacity: 0.85; max-width: 600px; margin: 0 auto;">
            Discover exclusive discounts, seasonal promotions, and unique incentives on our finest luxury properties.
        </p>
    </div>
</section>

<style>
@keyframes heroZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.08); }
}
</style>

<section style="padding: 4rem 0; min-height: 50vh; background: var(--color-gray-50);">
    <div class="container">
        <?php if (empty($announcements)): ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <h3 style="color: var(--color-gray-500);">No active announcements at the moment.</h3>
                <p>Please check back later for new exclusive offers.</p>
            </div>
        <?php else: ?>
            <div class="grid-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 2rem;">
                <?php foreach ($announcements as $ann): ?>
                    <div class="announcement-card fade-up" style="background: white; border: 1px solid var(--color-gray-200); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); position: relative; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        
                        <?php if ($ann['property_id']): ?>
                            <div style="height: 220px; width: 100%; overflow: hidden;">
                                <?php 
                                // Determine image source - handle both full URLs and relative paths
                                $img_src = '';
                                if (!empty($ann['image_main'])) {
                                    if (strpos($ann['image_main'], 'http') === 0) {
                                        $img_src = $ann['image_main'];
                                    } else {
                                        $img_src = '../' . $ann['image_main'];
                                    }
                                } else {
                                    $img_src = get_dynamic_property_image($ann['property_id']);
                                }
                                ?>
                                <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($ann['property_title'] ?? 'Property'); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
                            </div>
                        <?php endif; ?>

                        <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column;">
                            <?php if ($ann['discount_percentage'] > 0): ?>
                                <div style="position: absolute; top: 1rem; right: -2rem; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 0.3rem 3rem; transform: rotate(45deg); font-weight: bold; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(231,76,60,0.4); z-index: 10;">
                                    <?php echo $ann['discount_percentage']; ?>% OFF
                                </div>
                            <?php endif; ?>
                            
                            <div style="color: var(--color-gold); margin-bottom: 1rem; font-size: 0.9rem;">
                                <?php echo date('F j, Y', strtotime($ann['created_at'])); ?>
                            </div>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; margin-bottom: 1rem; color: var(--color-black); line-height: 1.3;">
                                <?php echo htmlspecialchars($ann['title']); ?>
                            </h3>
                            <p style="color: var(--color-gray-700); line-height: 1.7; flex: 1; font-size: 0.95rem;">
                                <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                            </p>
                            
                            <div style="margin-top: 2rem;">
                                <?php if ($ann['property_slug']): ?>
                                    <a href="property.php?slug=<?php echo $ann['property_slug']; ?>" class="btn btn-gold" style="width: 100%; text-align: center; display: inline-block; border-radius: 8px;">View Property Details</a>
                                <?php else: ?>
                                    <a href="search.php" class="btn btn-outline" style="border-color: var(--color-gold); color: var(--color-gold); width: 100%; text-align: center; display: inline-block; border-radius: 8px;">Browse Properties</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
