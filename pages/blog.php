<?php
require_once '../includes/header.php';

// Category-specific hero images and descriptions
$category_info = [
    'Market Report' => [
        'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1920&q=80',
        'description' => 'In-depth quarterly analyses of luxury real estate trends, pricing shifts, and investment opportunities across our key markets.',
        'icon' => '📊'
    ],
    'Neighborhood Guide' => [
        'image' => 'https://images.unsplash.com/photo-1449844908441-8829872d2607?auto=format&fit=crop&w=1920&q=80',
        'description' => 'Explore the finest neighborhoods — from exclusive enclaves to vibrant cultural districts — through the eyes of our local experts.',
        'icon' => '🏘️'
    ],
    'Architecture & Design' => [
        'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1920&q=80',
        'description' => 'Discover the latest trends in luxury architecture, interior design, smart home technology, and sustainable building practices.',
        'icon' => '🏛️'
    ],
    'Company News' => [
        'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1920&q=80',
        'description' => 'Stay informed with the latest announcements, milestones, team additions, and strategic developments from Hawassa Real Estate.',
        'icon' => '📰'
    ]
];

// Check for single post view
$slug = $_GET['slug'] ?? '';
if ($slug) {
    $stmt = db()->prepare("SELECT bp.*, u.name AS author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.slug = ? AND bp.status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if ($post): ?>
        <section class="blog-single">
            <div class="container">
                <div class="breadcrumb" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="<?php echo SITE_URL; ?>">Home</a> &raquo;
                        <a href="blog.php">Insights</a> &raquo;
                        <span><?php echo $post['title']; ?></span>
                    </div>
                    <a href="javascript:history.back()" class="btn btn-outline" style="padding: 0.25rem 1rem; border-color: var(--color-gold); color: var(--color-gold);">← Back</a>
                </div>
                <h1><?php echo $post['title']; ?></h1>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
                    <span style="background: var(--color-gold); color: white; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: bold;"><?php echo htmlspecialchars($post['category'] ?? 'General'); ?></span>
                    <span class="blog-meta" style="margin: 0;">By <?php echo $post['author_name']; ?> | <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                </div>
                <?php if ($post['image']): ?>
                    <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; border-radius: 8px; margin-bottom: 2rem; box-shadow: var(--shadow-md);">
                <?php endif; ?>
                <div class="blog-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700);">
                    <?php echo $post['content']; // Output raw HTML since we seeded rich HTML ?>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section style="padding: 6rem 0; text-align: center;"><h2>Post Not Found</h2></section>
    <?php endif;
} else {
    $category = $_GET['category'] ?? null;
    $posts = get_blog_posts(12, $category);
    
    // Get hero info for this category
    $hero_image = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80';
    $hero_desc = 'Expert analysis, market trends, neighborhood guides, and design inspiration from the Hawassa team.';
    $hero_icon = '💎';
    $hero_title = 'Market Insights';
    
    if ($category && isset($category_info[$category])) {
        $hero_image = $category_info[$category]['image'];
        $hero_desc = $category_info[$category]['description'];
        $hero_icon = $category_info[$category]['icon'];
        $hero_title = htmlspecialchars($category);
    }
    ?>

    <!-- Category Hero Section with Image -->
    <section style="position: relative; padding: 0; min-height: 45vh; display: flex; align-items: center; overflow: hidden;">
        <!-- Background Image -->
        <div style="position: absolute; inset: 0; z-index: 0;">
            <img src="<?php echo $hero_image; ?>" alt="<?php echo $hero_title; ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <!-- Overlay -->
        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.7) 100%); z-index: 1;"></div>
        <!-- Content -->
        <div class="container" style="position: relative; z-index: 2; padding: 5rem 0; color: #fff;">
            <div style="max-width: 700px;">
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                    <a href="<?php echo SITE_URL; ?>" style="color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem;">Home</a>
                    <span style="color: rgba(255,255,255,0.3);">›</span>
                    <a href="blog.php" style="color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem;">Insights</a>
                    <?php if ($category): ?>
                        <span style="color: rgba(255,255,255,0.3);">›</span>
                        <span style="color: #C9A96E; font-size: 0.85rem;"><?php echo htmlspecialchars($category); ?></span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 2.5rem; margin-bottom: 0.75rem;"><?php echo $hero_icon; ?></div>
                <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 5vw, 3.5rem); color: #fff; margin-bottom: 1rem; line-height: 1.15;"><?php echo $hero_title; ?></h1>
                <p style="font-size: 1.1rem; line-height: 1.7; color: rgba(255,255,255,0.8); max-width: 550px;"><?php echo $hero_desc; ?></p>
                
                <!-- Category Filter Pills -->
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-top: 2rem;">
                    <a href="blog.php" style="padding: 0.4rem 1.2rem; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; text-decoration: none; font-weight: 500; <?php echo !$category ? 'background: #C9A96E; color: #0D0D0D;' : 'background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.15);'; ?>">All</a>
                    <?php foreach (['Market Report', 'Neighborhood Guide', 'Architecture & Design', 'Company News'] as $cat): ?>
                        <a href="blog.php?category=<?php echo urlencode($cat); ?>" style="padding: 0.4rem 1.2rem; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; text-decoration: none; font-weight: 500; white-space: nowrap; <?php echo ($category === $cat) ? 'background: #C9A96E; color: #0D0D0D;' : 'background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.15);'; ?>"><?php echo $cat; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Grid -->
    <section style="padding: 4rem 0; background: var(--color-gray-50);">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <p style="color: var(--color-gray-500); font-size: 0.95rem;"><?php echo count($posts); ?> article<?php echo count($posts) !== 1 ? 's' : ''; ?> found</p>
            </div>
            <?php if (empty($posts)): ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <h3 style="color: var(--color-gray-500); margin-bottom: 1rem;">No articles found</h3>
                    <p style="color: var(--color-gray-400);">Check back soon for new insights from our team.</p>
                    <a href="blog.php" class="btn btn-gold" style="margin-top: 1.5rem;">View All Insights</a>
                </div>
            <?php else: ?>
                <div class="blog-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-card fade-up" style="position: relative; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <a href="?slug=<?php echo $post['slug']; ?>" style="display: block; overflow: hidden; height: 240px;">
                                <img src="<?php echo $post['image'] ?: '../assets/images/placeholder.jpg'; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
                            </a>
                            <div style="position: absolute; top: 1rem; left: 1rem; background: var(--color-gold); color: white; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 2;">
                                <?php echo htmlspecialchars($post['category'] ?? 'General'); ?>
                            </div>
                            <div class="blog-card-body" style="padding: 1.5rem;">
                                <p class="blog-date" style="color: var(--color-gold); font-size: 0.85rem; margin-bottom: 0.5rem;"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>
                                <h3 style="font-size: 1.15rem; line-height: 1.4; margin-bottom: 0.75rem;"><a href="?slug=<?php echo $post['slug']; ?>" style="color: var(--color-black); text-decoration: none;"><?php echo $post['title']; ?></a></h3>
                                <p class="blog-excerpt" style="color: var(--color-gray-500); font-size: 0.9rem; line-height: 1.6;"><?php echo substr($post['excerpt'], 0, 150); ?>...</p>
                                <a href="?slug=<?php echo $post['slug']; ?>" style="display: inline-block; margin-top: 1rem; color: #C9A96E; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; text-decoration: none;">Read Article →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php }

require_once '../includes/footer.php';