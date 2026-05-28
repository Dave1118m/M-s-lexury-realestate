    </main>
    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><?php echo get_setting('site_name'); ?></h4>
                    <p><?php echo get_setting('site_description'); ?></p>
                    <p class="footer-contact"><?php echo get_setting('contact_phone'); ?><br><?php echo get_setting('contact_email'); ?></p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/pages/search.php">Search Properties</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/agents.php">Our Agents</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/blog.php">Market Insights</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/login.php">Agent Login</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Our Markets</h4>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <li><a href="<?php echo SITE_URL; ?>/pages/search.php?category=<?php echo $cat['slug']; ?>"><?php echo $cat['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Newsletter</h4>
                    <p>Get the latest listings and market insights delivered to your inbox.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-gold">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?php echo get_setting('footer_text'); ?></p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'search.php'): ?>
        <script src="<?php echo SITE_URL; ?>/assets/js/search.js?v=2"></script>
    <?php endif; ?>
</body>
</html>