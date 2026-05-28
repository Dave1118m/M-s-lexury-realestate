<?php
require_once '../includes/header.php';
$category_slug = $_GET['category'] ?? '';
$type = $_GET['type'] ?? '';
$q = $_GET['q'] ?? '';
?>
<meta name="site-url" content="<?php echo SITE_URL; ?>">
<meta name="is-logged-in" content="<?php echo is_logged_in() ? '1' : '0'; ?>">

<section class="search-page">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Home</a> &raquo; <span>Search Properties</span>
        </div>

        <h2 class="section-title" style="text-align: left;">Find Your Perfect Property</h2>

        <!-- Search Filters -->
        <form id="propertySearchForm" class="search-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="q" placeholder="Location, address..." value="<?php echo sanitize($q); ?>">
                </div>
                <div class="filter-group">
                    <label>Property Type</label>
                    <select name="property_type">
                        <option value="">All Types</option>
                        <option value="Single Family">Single Family</option>
                        <option value="Condo">Condo</option>
                        <option value="Co-op">Co-op</option>
                        <option value="Townhouse">Townhouse</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Transaction</label>
                    <select name="type">
                        <option value="">All</option>
                        <option value="sale" <?php echo $type === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo $type === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Locations</option>
                        <?php foreach (get_categories() as $cat): ?>
                            <option value="<?php echo $cat['slug']; ?>" <?php echo $category_slug === $cat['slug'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label>Min Price</label>
                    <input type="number" name="min_price" placeholder="$ Min">
                </div>
                <div class="filter-group">
                    <label>Max Price</label>
                    <input type="number" name="max_price" placeholder="$ Max">
                </div>
                <div class="filter-group">
                    <label>Bedrooms</label>
                    <select name="bedrooms">
                        <option value="">Any</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Bathrooms</label>
                    <select name="bathrooms">
                        <option value="">Any</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Results -->
        <div class="search-results-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 id="resultCount">Loading properties...</h3>
            <div style="display: flex; gap: 1rem;">
                <button id="saveSearchBtn" class="btn btn-outline" style="padding: 0.5rem 1rem;">🔔 Save Search</button>
                <select id="sortBy" onchange="document.getElementById('propertySearchForm').dispatchEvent(new Event('submit'))" style="padding: 0.5rem; border: 1px solid var(--color-gray-300); border-radius: 4px;">
                    <option value="newest">Newest First</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div id="searchSpinner" class="spinner" style="display: none;"></div>

        <div class="property-grid" id="searchResults">
            <p class="text-center">Loading properties...</p>
        </div>

        <!-- Pagination placeholder -->
        <div class="pagination" id="pagination"></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveSearchBtn = document.getElementById('saveSearchBtn');
    if (saveSearchBtn) {
        saveSearchBtn.addEventListener('click', async function() {
            const formData = new FormData(document.getElementById('propertySearchForm'));
            const criteria = Object.fromEntries(formData.entries());
            
            try {
                const res = await fetch('../api/save_search.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        search_name: 'Search: ' + (criteria.q || criteria.category || 'All Properties'),
                        criteria: criteria 
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Search saved successfully! You can view it in your dashboard.');
                    saveSearchBtn.innerHTML = '✅ Saved';
                    saveSearchBtn.disabled = true;
                } else {
                    alert(data.error);
                }
            } catch (e) {
                console.error(e);
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>