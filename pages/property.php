<?php
require_once '../includes/header.php';

$slug = $_GET['slug'] ?? '';
$property = get_property($slug);

if (!$property) {
    echo '<section style="padding: 6rem 0; text-align: center;"><h2>Property Not Found</h2><p><a href="search.php">Browse all properties</a></p></section>';
    require_once '../includes/footer.php';
    exit;
}

$is_saved = false;
if (is_logged_in()) {
    $is_saved = is_property_saved($_SESSION['user_id'], $property['id']);
}
?>

<section class="property-detail">
    <div class="container">
        <div class="breadcrumb" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--color-gray-200); margin-bottom: 2rem;">
            <div>
                <a href="<?php echo SITE_URL; ?>">Home</a> &raquo; 
                <a href="search.php?category=<?php echo $property['category_name']; ?>"><?php echo $property['category_name']; ?></a> &raquo; 
                <span><?php echo $property['title']; ?></span>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                    <?php if (!is_agent()): ?>
                        <?php if (is_logged_in()): ?>
                            <button id="savePropertyBtn" class="btn btn-outline" style="padding: 0.25rem 1rem; border-color: <?php echo $is_saved ? '#dc3545' : 'var(--color-gray-400)'; ?>; color: <?php echo $is_saved ? '#dc3545' : 'var(--color-gray-600)'; ?>;">
                                <?php echo $is_saved ? '❤️ Saved' : '🤍 Save Property'; ?>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline" style="padding: 0.25rem 1rem; border-color: var(--color-gray-400); color: var(--color-gray-600);">🤍 Save Property</a>
                        <?php endif; ?>
                    <?php endif; ?>
                <a href="javascript:history.back()" class="btn btn-outline" style="padding: 0.25rem 1rem; border-color: var(--color-gold); color: var(--color-gold);">← Back</a>
            </div>
        </div>

        <!-- Gallery -->
        <div class="property-gallery">
            <div class="gallery-main">
                <img src="<?php echo $property['image_main'] ?: get_dynamic_property_image($property['id'], 0); ?>" alt="<?php echo $property['title']; ?>">
            </div>
            <div><img src="<?php echo get_dynamic_property_image($property['id'], 1); ?>" alt="Gallery 1"></div>
            <div><img src="<?php echo get_dynamic_property_image($property['id'], 2); ?>" alt="Gallery 2"></div>
            <div><img src="<?php echo get_dynamic_property_image($property['id'], 3); ?>" alt="Gallery 3"></div>
            <div><img src="<?php echo get_dynamic_property_image($property['id'], 4); ?>" alt="Gallery 4"></div>
        </div>

        <div class="grid-2">
            <div>
                <!-- Header -->
                <div class="property-detail-header">
                    <div>
                        <h1><?php echo $property['title']; ?></h1>
                        <p style="color: var(--color-gray-500); font-size: 1.1rem;">
                            <?php echo $property['address']; ?>, <?php echo $property['city']; ?>, <?php echo $property['state']; ?> <?php echo $property['zip']; ?>
                        </p>
                    </div>
                    <div class="price"><?php echo format_price($property['price']); ?></div>
                </div>

                <!-- Meta -->
                <div class="property-detail-meta">
                    <div class="meta-item">
                        <div class="meta-value"><?php echo $property['bedrooms']; ?></div>
                        <div class="meta-label">Bedrooms</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?php echo $property['bathrooms']; ?></div>
                        <div class="meta-label">Bathrooms</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?php echo number_format($property['area_sqft']); ?></div>
                        <div class="meta-label">Sq Ft</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?php echo ucfirst($property['property_type']); ?></div>
                        <div class="meta-label">Type</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-value"><?php echo ucfirst($property['type']); ?></div>
                        <div class="meta-label">Transaction</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="property-description">
                    <h3>About This Property</h3>
                    <div class="gold-line"></div>
                    <p><?php echo nl2br($property['description']); ?></p>
                </div>

                <!-- Features -->
                <?php if (!empty($property['features'])): ?>
                    <h3>Features & Amenities</h3>
                    <div class="gold-line"></div>
                    <div class="property-features">
                        <?php foreach ($property['features'] as $feature): ?>
                            <div class="feature-item">✓ <?php echo $feature; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Rich Media -->
                <?php if (!empty($property['video_url']) || !empty($property['virtual_tour_url']) || !empty($property['floor_plan_image'])): ?>
                    <h3>Media & Virtual Tours</h3>
                    <div class="gold-line"></div>
                    
                    <?php if (!empty($property['video_url'])): ?>
                        <?php 
                        // Convert standard youtube link to embed link
                        $vid_url = $property['video_url'];
                        if (strpos($vid_url, 'youtube.com/watch?v=') !== false) {
                            $vid_url = str_replace('watch?v=', 'embed/', $vid_url);
                        }
                        ?>
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 0.5rem;">Property Video</h4>
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                                <iframe src="<?php echo htmlspecialchars($vid_url); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($property['virtual_tour_url'])): ?>
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 0.5rem;">3D Virtual Tour</h4>
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                                <iframe src="<?php echo htmlspecialchars($property['virtual_tour_url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($property['floor_plan_image'])): ?>
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 0.5rem;">Floor Plan</h4>
                            <?php if (str_ends_with(strtolower($property['floor_plan_image']), '.pdf')): ?>
                                <a href="../<?php echo htmlspecialchars($property['floor_plan_image']); ?>" target="_blank" class="btn btn-outline" style="display: inline-block;">📄 View Floor Plan (PDF)</a>
                            <?php else: ?>
                                <a href="../<?php echo htmlspecialchars($property['floor_plan_image']); ?>" target="_blank">
                                    <img src="../<?php echo htmlspecialchars($property['floor_plan_image']); ?>" alt="Floor Plan" style="max-width: 100%; border-radius: 8px; border: 1px solid var(--color-gray-200);">
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Map -->
                <h3>Location</h3>
                <div class="gold-line"></div>
                <div id="propertyMap" style="height: 400px; width: 100%; border-radius: 8px; z-index: 1;"></div>
            </div>

            <!-- Sidebar: Agent Contact & Inquiry -->
            <div>
                <div class="agent-contact-card">
                    <img src="../assets/images/placeholder.jpg" alt="Agent">
                    <h3><?php echo $property['agent_name'] ?? 'Hawassa Agent'; ?></h3>
                    <p style="color: var(--color-gold);">Licensed Real Estate Agent</p>
                    <p style="margin: 1rem 0;"><?php echo $property['agent_phone'] ?? '+251 911 234 567'; ?></p>

                    <div style="margin-top: 1rem;">
                        <?php if (!is_agent()): ?>
                            <a href="chat.php?agent_id=<?php echo $property['agent_id']; ?>&property_id=<?php echo $property['id']; ?>" class="btn btn-gold" style="width: 100%; display: inline-block; text-align: center; margin-bottom: 0.5rem;">💬 Chat with Agent</a>
                            <?php if ($property['status'] === 'active'): ?>
                                <?php if (is_logged_in()): ?>
                                    <a href="payment_demo.php?property_id=<?php echo $property['id']; ?>" class="btn btn-outline" style="width: 100%; display: inline-block; text-align: center; border-color: var(--color-gold); color: var(--color-gold);">💳 Reserve Property</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline" style="width: 100%; display: inline-block; text-align: center; border-color: var(--color-gold); color: var(--color-gold);">💳 Sign In to Reserve</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="background: rgba(220, 53, 69, 0.08); color: #dc3545; padding: 0.85rem; border-radius: 6px; text-align: center; font-weight: 600; font-size: 0.9rem; border: 1px solid rgba(220, 53, 69, 0.2); display: flex; align-items: center; justify-content: center; gap: 0.5rem; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 0.5rem;">
                                    <span>🔒 Under Offer / Reserved</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="background: rgba(52, 152, 219, 0.1); color: #1f618d; padding: 1rem; border-radius: 8px; text-align: center;">
                                You are viewing this listing as an agent. Manage it through your <a href="agent_dashboard.php" style="color: #1f618d; font-weight: 700; text-decoration: underline;">Agent Portal</a> or <a href="../admin/dashboard.php" style="color: #1f618d; font-weight: 700; text-decoration: underline;">Advanced Portal</a>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


            </div>
        </div>

        <!-- Explore Other Properties -->
        <?php 
        $other_properties = get_other_properties($property['id'], 3);
        if (!empty($other_properties)): 
        ?>
        <div style="margin-top: 5rem;">
            <h2 class="section-title" style="font-size: 2rem;">Explore Other Properties</h2>
            <div class="gold-line" style="margin: 0 auto 2rem;"></div>
            <div class="property-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php foreach ($other_properties as $other_prop): ?>
                    <div class="property-card fade-up">
                        <div class="property-card-image">
                            <a href="property.php?slug=<?php echo $other_prop['slug']; ?>">
                                <img src="<?php echo $other_prop['image_main'] ?: get_dynamic_property_image($other_prop['id']); ?>" alt="<?php echo $other_prop['title']; ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="property-card-body">
                            <div class="property-price"><?php echo format_price($other_prop['price']); ?></div>
                            <h3><a href="property.php?slug=<?php echo $other_prop['slug']; ?>"><?php echo $other_prop['title']; ?></a></h3>
                            <p class="property-location"><?php echo $other_prop['city']; ?>, <?php echo $other_prop['state']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inquiry Form (if exists)
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your inquiry! An agent will contact you shortly.');
            this.reset();
        });
    }

    // Save Property
    const saveBtn = document.getElementById('savePropertyBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            try {
                const res = await fetch('../api/save_property.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ property_id: <?php echo $property['id']; ?> })
                });
                const data = await res.json();
                if (data.success) {
                    if (data.status === 'saved') {
                        saveBtn.innerHTML = '❤️ Saved';
                        saveBtn.style.color = '#dc3545';
                        saveBtn.style.borderColor = '#dc3545';
                    } else {
                        saveBtn.innerHTML = '🤍 Save Property';
                        saveBtn.style.color = 'var(--color-gray-600)';
                        saveBtn.style.borderColor = 'var(--color-gray-400)';
                    }
                } else {
                    alert(data.error);
                }
            } catch (e) {
                console.error(e);
            }
        });
    }



    // Leaflet Map Initialization
    const lat = <?php echo !empty($property['latitude']) ? json_encode($property['latitude']) : '40.7128'; ?>;
    const lng = <?php echo !empty($property['longitude']) ? json_encode($property['longitude']) : '-74.0060'; ?>;
    const address = <?php echo json_encode($property['address']); ?>;

    const map = L.map('propertyMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup(`<b>${address}</b>`)
        .openPopup();
});
</script>

<?php require_once '../includes/footer.php'; ?>