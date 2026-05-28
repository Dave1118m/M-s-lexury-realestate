<?php
require_once '../includes/auth.php';
if (!is_logged_in() || (!is_admin() && !is_agent())) redirect('../pages/login.php');

$is_agent = is_agent();
$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;
$property = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    
    if ($property && $is_agent && $property['agent_id'] != $user_id) {
        die("Unauthorized to edit this property.");
    }
}

$categories = db()->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$agents = db()->query("SELECT id, name FROM users WHERE role = 'agent' ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Property - Hawassa Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/luxury.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: var(--color-black); color: var(--color-white); padding: 2rem 1rem; }
        .admin-sidebar h3 { color: var(--color-gold); margin-bottom: 2rem; }
        .admin-sidebar a { display: block; color: var(--color-gray-300); padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-sidebar a:hover { color: var(--color-gold); }
        .admin-main { flex: 1; padding: 2rem; background: var(--color-gray-50); }
        
        .admin-form { background: white; padding: 2rem; border-radius: 8px; box-shadow: var(--shadow-md); max-width: 900px; }
        .form-row { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 4px; font-family: inherit; font-size: 0.95rem;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        
        .image-preview-container { margin-top: 1rem; }
        .image-preview-container img { max-width: 100%; max-height: 250px; border-radius: 4px; border: 1px solid var(--color-gray-200); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3><?php echo $is_agent ? 'Agent Portal' : 'Hawassa Admin'; ?></h3>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="properties.php" style="color: var(--color-gold);">🏠 Properties</a>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><?php echo $id ? 'Edit' : 'Add New'; ?> Property</h2>
                <a href="properties.php" class="btn btn-outline" style="color: var(--color-gray-700); border-color: var(--color-gray-300);">Back to List</a>
            </div>

            <div class="admin-form">
                <form id="propertyForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label>Title</label>
                            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($property['title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" name="price" required value="<?php echo htmlspecialchars($property['price'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select name="type">
                                <option value="sale" <?php echo ($property['type'] ?? '') === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                <option value="rent" <?php echo ($property['type'] ?? '') === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Property Type</label>
                            <select name="property_type">
                                <?php foreach(['Single Family', 'Condo', 'Co-op', 'Townhouse', 'Land'] as $pt): ?>
                                    <option value="<?php echo $pt; ?>" <?php echo ($property['property_type'] ?? '') === $pt ? 'selected' : ''; ?>><?php echo $pt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?php echo ($property['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($property['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="sold" <?php echo ($property['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Bedrooms</label>
                            <input type="number" name="bedrooms" value="<?php echo htmlspecialchars($property['bedrooms'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Bathrooms</label>
                            <input type="number" step="0.5" name="bathrooms" value="<?php echo htmlspecialchars($property['bathrooms'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Area (Sq Ft)</label>
                            <input type="number" name="area_sqft" value="<?php echo htmlspecialchars($property['area_sqft'] ?? '0'); ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Description</label>
                        <textarea name="description" required><?php echo htmlspecialchars($property['description'] ?? ''); ?></textarea>
                    </div>

                    <h3 style="margin: 2rem 0 1rem; font-size: 1.2rem;">Location & Assignment</h3>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label>Street Address</label>
                            <input type="text" name="address" required value="<?php echo htmlspecialchars($property['address'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" required value="<?php echo htmlspecialchars($property['city'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" required value="<?php echo htmlspecialchars($property['state'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Zip Code</label>
                            <input type="text" name="zip" required value="<?php echo htmlspecialchars($property['zip'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="text" name="latitude" value="<?php echo htmlspecialchars($property['latitude'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="text" name="longitude" value="<?php echo htmlspecialchars($property['longitude'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Category (Region)</label>
                            <select name="category_id">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($property['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Assign Agent</label>
                            <?php if ($is_agent): ?>
                                <input type="hidden" name="agent_id" value="<?php echo $user_id; ?>">
                                <input type="text" disabled value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" style="background: var(--color-gray-100);">
                            <?php else: ?>
                                <select name="agent_id">
                                    <?php foreach($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>" <?php echo ($property['agent_id'] ?? '') == $agent['id'] ? 'selected' : ''; ?>><?php echo $agent['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; padding-top: 2rem;">
                            <input type="checkbox" name="featured" value="1" style="width: auto;" <?php echo !empty($property['featured']) ? 'checked' : ''; ?>>
                            <label style="margin: 0;">Featured Property</label>
                        </div>
                    </div>

                    <h3 style="margin: 2rem 0 1rem; font-size: 1.2rem;">Rich Media</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>YouTube Video URL</label>
                            <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..." value="<?php echo htmlspecialchars($property['video_url'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Virtual Tour URL (Matterport, etc.)</label>
                            <input type="url" name="virtual_tour_url" placeholder="https://my.matterport.com/show/?m=..." value="<?php echo htmlspecialchars($property['virtual_tour_url'] ?? ''); ?>">
                        </div>
                    </div>

                    <h3 style="margin: 2rem 0 1rem; font-size: 1.2rem;">Media</h3>
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label>Main Property Image</label>
                            <input type="file" name="image_main" accept="image/*" data-preview="#mainImagePreview">
                            <div class="image-preview-container">
                                <img id="mainImagePreview" src="<?php echo !empty($property['image_main']) ? '../' . $property['image_main'] : ''; ?>" style="display: <?php echo !empty($property['image_main']) ? 'block' : 'none'; ?>;">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label>Floor Plan Image</label>
                            <input type="file" name="floor_plan_image" accept="image/*,.pdf" data-preview="#floorPlanPreview">
                            <div class="image-preview-container">
                                <?php if (!empty($property['floor_plan_image'])): ?>
                                    <?php if (str_ends_with(strtolower($property['floor_plan_image']), '.pdf')): ?>
                                        <a href="../<?php echo $property['floor_plan_image']; ?>" target="_blank" style="display: inline-block; padding: 0.5rem 1rem; background: var(--color-gray-200); border-radius: 4px;">📄 View Current PDF</a>
                                    <?php else: ?>
                                        <img id="floorPlanPreview" src="../<?php echo $property['floor_plan_image']; ?>" style="display: block; max-height: 150px;">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <img id="floorPlanPreview" style="display: none; max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold" id="submitBtn">Save Property</button>
                    <div id="formMsg" style="margin-top: 1rem; font-weight: 500;"></div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        document.getElementById('propertyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('formMsg');
            
            btn.textContent = 'Saving...';
            btn.disabled = true;
            msg.textContent = '';
            
            try {
                const formData = new FormData(this);
                // Ensure unchecked checkboxes aren't just omitted
                if (!formData.has('featured')) formData.append('featured', 0);
                
                const response = await fetch('../api/properties.php', {
                    method: 'POST', // Always POST for file uploads
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    msg.style.color = 'green';
                    msg.textContent = 'Property saved successfully!';
                    setTimeout(() => window.location.href = 'properties.php', 1500);
                } else {
                    msg.style.color = 'red';
                    msg.textContent = result.error || 'An error occurred while saving.';
                    btn.textContent = 'Save Property';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                msg.style.color = 'red';
                msg.textContent = 'Network error occurred.';
                btn.textContent = 'Save Property';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
