<?php
/**
 * Hawassa Luxury Real Estate - Home Page
 */
require_once 'includes/header.php';

$featured_properties = get_properties(6, true);
$categories = get_categories();
$agents = get_agents(5);
$blog_posts = get_blog_posts(3);
$testimonials = get_testimonials();
?>

<!-- Hero Section (Luxury Image Slider Background) -->
<section class="hero" id="hero-slider">
    <!-- Image Slider Background -->
    <div class="hero-slider" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 0;">
        <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1920&q=80');"></div>
        <!-- Overlay -->
        <div class="hero-slider-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.45); z-index: 5;"></div>
    </div>
    
    <div class="hero-content" style="position: relative; z-index: 10;">
        <h1>Find the Home of<br>Your Dreams</h1>
        <p>Discover exceptional properties in the world's most sought-after locations</p>
        <form class="hero-search" action="pages/search.php" method="GET">
            <input type="text" name="q" placeholder="Search by location, address, or keyword...">
            <select name="type">
                <option value="">All Types</option>
                <option value="sale">For Sale</option>
                <option value="rent">For Rent</option>
            </select>
            <button type="submit" class="btn btn-gold">Search</button>
        </form>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div>
                <div class="stat-number counter" data-target="150">0</div>
                <div class="stat-label">Years of Excellence</div>
            </div>
            <div>
                <div class="stat-number counter" data-target="2500">0</div>
                <div class="stat-label">Expert Agents</div>
            </div>
            <div>
                <div class="stat-number counter" data-target="45000">0</div>
                <div class="stat-label">Properties Sold</div>
            </div>
            <div>
                <div class="stat-number counter" data-target="15">0</div>
                <div class="stat-label">Billion in Sales</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="featured-properties">
    <div class="container">
        <h2 class="section-title">Featured Properties</h2>
        <p class="section-subtitle">An exclusive selection of our most exceptional listings</p>

        <div class="property-grid">
            <?php foreach ($featured_properties as $property): ?>
                <div class="property-card fade-up">
                    <div class="property-card-image">
                        <a href="pages/property.php?slug=<?php echo $property['slug']; ?>">
                            <img src="<?php echo $property['image_main'] ?: get_dynamic_property_image($property['id']); ?>" alt="<?php echo $property['title']; ?>" loading="lazy">
                        </a>
                        <span class="property-badge">Featured</span>
                    </div>
                    <div class="property-card-body">
                        <div class="property-price"><?php echo format_price($property['price']); ?></div>
                        <h3><a href="pages/property.php?slug=<?php echo $property['slug']; ?>"><?php echo $property['title']; ?></a></h3>
                        <p class="property-location"><?php echo $property['city']; ?>, <?php echo $property['state']; ?></p>
                        <div class="property-meta">
                            <span>🛏 <?php echo $property['bedrooms']; ?> Beds</span>
                            <span>🛁 <?php echo $property['bathrooms']; ?> Baths</span>
                            <span>📐 <?php echo number_format($property['area_sqft']); ?> sqft</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="pages/search.php" class="btn btn-dark">View All Properties</a>
        </div>
    </div>
</section>

<!-- Categories / Neighborhoods -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Featured Neighborhoods</h2>
        <p class="section-subtitle">Explore luxury real estate in the most prestigious locations</p>

        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a href="pages/search.php?category=<?php echo $category['slug']; ?>" class="category-card img-hover-zoom">
                    <img src="<?php echo $category['image'] ?: get_dynamic_category_image($category['id']); ?>" alt="<?php echo $category['name']; ?>" loading="lazy">
                    <div class="category-overlay">
                        <h3><?php echo $category['name']; ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About / Sell with Us -->
<section class="cta-section parallax-section" style="background-image: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)), url('assets/images/cta-bg.jpg');">
    <div class="container">
        <h2>Sell with Us</h2>
        <p>For over 15 years, Hawassa Real Estate is the preeminent force in bespoke,<br>personalized real estate service and strategy.</p>
        <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;">Hawassa agents have the highest average sales price achieved per agent in Ethiopia's luxury real estate market.</p>
        <a href="pages/contact.php" class="btn btn-gold">Contact an Agent</a>
    </div>
</section>

<!-- Agents -->
<section class="agents-section">
    <div class="container">
        <h2 class="section-title">Our Exceptional Agents</h2>
        <p class="section-subtitle">Work with the industry's most accomplished professionals</p>

        <div class="agent-grid">
            <?php foreach ($agents as $agent): ?>
                <div class="agent-card fade-up">
                    <img src="<?php echo $agent['avatar'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo $agent['name']; ?>" class="agent-avatar">
                    <h3><?php echo $agent['name']; ?></h3>
                    <p class="agent-title">Licensed Real Estate Agent</p>
                    <p class="agent-contact"><?php echo $agent['phone']; ?></p>
                    <a href="pages/agents.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline" style="color: var(--color-gold); border-color: var(--color-gold); margin-top: 1rem;">View Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Blog / Insights -->
<section class="blog-section">
    <div class="container">
        <h2 class="section-title">Market Insights</h2>
        <p class="section-subtitle">Stay informed with the latest real estate news and trends</p>

        <div class="blog-grid">
            <?php foreach ($blog_posts as $post): ?>
                <article class="blog-card fade-up">
                    <a href="pages/blog.php?slug=<?php echo $post['slug']; ?>">
                        <img src="<?php echo $post['image'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo $post['title']; ?>" loading="lazy">
                    </a>
                    <div class="blog-card-body">
                        <p class="blog-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>
                        <h3><a href="pages/blog.php?slug=<?php echo $post['slug']; ?>"><?php echo $post['title']; ?></a></h3>
                        <p class="blog-excerpt"><?php echo substr($post['excerpt'], 0, 120); ?>...</p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="pages/blog.php" class="btn btn-dark">Read More Insights</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title">What Our Clients Say</h2>
        <div class="testimonial-grid">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card fade-up" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <img src="<?php echo $testimonial['image'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo $testimonial['name']; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; border: 2px solid var(--color-gold);">
                    <div class="testimonial-text">"<?php echo $testimonial['content']; ?>"</div>
                    <div class="testimonial-author"><?php echo $testimonial['name']; ?></div>
                    <div class="testimonial-position"><?php echo $testimonial['position']; ?></div>
                    <div class="testimonial-stars" style="color: var(--color-gold); margin-top: 0.5rem;">
                        <?php echo str_repeat('★', $testimonial['rating']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hero Styles -->
<style>
.hero { position: relative; overflow: hidden; height: 100vh; display: flex; align-items: center; justify-content: center; background: #000; }
.hero-content { position: relative; z-index: 10; text-align: center; color: white; width: 100%; max-width: 800px; padding: 0 2rem; }

/* Luxury Ken Burns Background Slider */
.hero-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0;
    z-index: 1;
    transition: opacity 2s ease-in-out;
}

.hero-slide.active {
    opacity: 1;
    z-index: 2;
    animation: kenBurnsSlider 10s ease-out forwards;
}

@keyframes kenBurnsSlider {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.08);
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>