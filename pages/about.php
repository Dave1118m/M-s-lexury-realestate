<?php
require_once '../includes/header.php';
?>

<!-- Hero Section with Stunning Background Image -->
<section class="about-hero" style="position: relative; min-height: 85vh; display: flex; align-items: center; justify-content: center; overflow: hidden;">
    <!-- Background Image -->
    <div style="position: absolute; inset: 0; z-index: 0;">
        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2400&q=85"
             alt="Luxury modern villa with infinity pool"
             style="width: 100%; height: 100%; object-fit: cover; animation: heroZoom 30s ease-in-out infinite alternate;">
    </div>
    <!-- Gradient Overlay -->
    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.55) 50%, rgba(0,0,0,0.85) 100%); z-index: 1;"></div>
    <!-- Content -->
    <div class="container" style="position: relative; z-index: 2; text-align: center; color: #fff; max-width: 850px; padding: 2rem;">
        <div style="display: inline-block; border: 1px solid rgba(201,169,110,0.5); padding: 0.4rem 1.5rem; border-radius: 30px; margin-bottom: 2rem; backdrop-filter: blur(4px); background: rgba(201,169,110,0.08);">
            <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; color: #C9A96E; font-weight: 500;">Est. 1873 — Addis Ababa</span>
        </div>
        <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2.5rem, 6vw, 4.5rem); color: #fff; margin-bottom: 1.5rem; line-height: 1.15; text-shadow: 0 4px 20px rgba(0,0,0,0.4);">
            Defining Luxury<br>Real Estate <span style="color: #C9A96E;">Excellence</span>
        </h1>
        <p style="font-size: 1.2rem; line-height: 1.8; opacity: 0.85; max-width: 650px; margin: 0 auto 2.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.5);">
            For over 15 years, Hawassa Real Estate has set the standard for luxury real estate services. Our legacy is built on integrity, discretion, and an unwavering commitment to extraordinary results.
        </p>
        <!-- Stats Row -->
        <div style="display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: #C9A96E;">150+</div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.7; margin-top: 0.3rem;">Years of Legacy</div>
            </div>
            <div style="text-align: center;">
                <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: #C9A96E;">$25B+</div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.7; margin-top: 0.3rem;">Lifetime Sales</div>
            </div>
            <div style="text-align: center;">
                <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: #C9A96E;">2,500+</div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.7; margin-top: 0.3rem;">Properties Sold</div>
            </div>
        </div>
    </div>
    <!-- Scroll Indicator -->
    <div style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); z-index: 2; text-align: center; animation: bounce 2s infinite;">
        <div style="width: 28px; height: 45px; border: 2px solid rgba(255,255,255,0.4); border-radius: 14px; position: relative; margin: 0 auto 0.5rem;">
            <div style="width: 4px; height: 10px; background: #C9A96E; border-radius: 2px; position: absolute; top: 6px; left: 50%; transform: translateX(-50%); animation: scrollDot 2s infinite;"></div>
        </div>
        <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.5);">Scroll</span>
    </div>
</section>

<style>
@keyframes heroZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.08); }
}
@keyframes bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(8px); }
}
@keyframes scrollDot {
    0% { opacity: 1; top: 6px; }
    100% { opacity: 0; top: 24px; }
}
</style>

<!-- Our Heritage Section -->
<section style="padding: 6rem 0;">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 4rem;">
            <div style="flex: 1; min-width: 300px;" class="fade-up">
                <div style="display: inline-block; border: 1px solid rgba(201,169,110,0.4); padding: 0.3rem 1.2rem; border-radius: 20px; margin-bottom: 1.5rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: #C9A96E; font-weight: 500;">Our Story</span>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--color-black);">Our Heritage</h2>
                <div class="gold-line" style="margin-bottom: 2rem;"></div>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700); margin-bottom: 1.5rem;">
                    Founded on the principles of integrity, discretion, and excellence, our brokerage has been at the forefront of the luxury real estate market for over two decades. We understand that buying or selling a premium property is more than a transaction—it is a significant milestone that requires the utmost care and expertise.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700);">
                    Our global network and intimate local knowledge allow us to seamlessly connect discerning buyers with extraordinary properties. From historic penthouses in Bole to sprawling estates in the Hawassa, we offer an exclusive portfolio curated for those who demand the very best.
                </p>
            </div>
            <div style="flex: 1; min-width: 300px;" class="fade-up">
                <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=800&q=80" alt="Luxury Estate" style="width: 100%; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.12);">
            </div>
        </div>
    </div>
</section>

<!-- Expertise Section -->
<section style="padding: 6rem 0; background: var(--color-gray-50);">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap-reverse; align-items: center; gap: 4rem;">
            <div style="flex: 1; min-width: 300px;" class="fade-up">
                <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80" alt="Luxurious Interior" style="width: 100%; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.12);">
            </div>
            <div style="flex: 1; min-width: 300px;" class="fade-up">
                <div style="display: inline-block; border: 1px solid rgba(201,169,110,0.4); padding: 0.3rem 1.2rem; border-radius: 20px; margin-bottom: 1.5rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: #C9A96E; font-weight: 500;">Why Choose Us</span>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--color-black);">Unrivaled Expertise</h2>
                <div class="gold-line" style="margin-bottom: 2rem;"></div>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700); margin-bottom: 1.5rem;">
                    Our agents are not just real estate professionals; they are trusted advisors. Hand-selected for their exceptional track records and deep understanding of the high-end market, our team provides tailored strategies to meet your unique goals.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-gray-700);">
                    We utilize cutting-edge marketing technology, breathtaking architectural photography, and private, off-market networks to ensure maximum visibility for our listings and exclusive access for our buyers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Our Exclusive Representation Portfolio -->
<section style="padding: 6rem 0; background: var(--color-black); color: #fff;">
    <div class="container">
        <div style="text-align: center; max-width: 800px; margin: 0 auto 4rem; padding: 0 1rem;">
            <div style="display: inline-block; border: 1px solid rgba(201,169,110,0.4); padding: 0.3rem 1.2rem; border-radius: 20px; margin-bottom: 1.5rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: #C9A96E; font-weight: 500;">Marketing Excellence</span>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 3rem; margin-bottom: 1.5rem; color: #fff; line-height: 1.2;">Selling Your Luxury Property</h2>
            <div class="gold-line" style="margin: 0 auto 2rem;"></div>
            <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-gray-400);">
                We showcase our listings using state-of-the-art cinematic video, bespoke high-fidelity photography, and immersive 3D virtual walkthroughs, ensuring your property is presented in its most exceptional light to qualified global buyers.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <!-- Gallery Item 1 -->
            <div style="position: relative; overflow: hidden; border-radius: 8px; aspect-ratio: 4/3; cursor: pointer;" class="portfolio-item-card">
                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80" alt="Metropolitan Penthouse" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.85) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem; transition: background 0.4s ease;">
                    <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #C9A96E; margin-bottom: 0.5rem;">Metropolitan</span>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; margin: 0; font-weight: 500;">Bespoke Penthouses</h3>
                </div>
            </div>

            <!-- Gallery Item 2 -->
            <div style="position: relative; overflow: hidden; border-radius: 8px; aspect-ratio: 4/3; cursor: pointer;" class="portfolio-item-card">
                <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80" alt="Coastal Estate" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.85) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem; transition: background 0.4s ease;">
                    <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #C9A96E; margin-bottom: 0.5rem;">Coastal</span>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; margin: 0; font-weight: 500;">Oceanfront Estates</h3>
                </div>
            </div>

            <!-- Gallery Item 3 -->
            <div style="position: relative; overflow: hidden; border-radius: 8px; aspect-ratio: 4/3; cursor: pointer;" class="portfolio-item-card">
                <img src="https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=800&q=80" alt="Modernist Retreat" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.85) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem; transition: background 0.4s ease;">
                    <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #C9A96E; margin-bottom: 0.5rem;">Contemporary</span>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; margin: 0; font-weight: 500;">Modernist Retreats</h3>
                </div>
            </div>

            <!-- Gallery Item 4 -->
            <div style="position: relative; overflow: hidden; border-radius: 8px; aspect-ratio: 4/3; cursor: pointer;" class="portfolio-item-card">
                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" alt="Historic Residence" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.85) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem; transition: background 0.4s ease;">
                    <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #C9A96E; margin-bottom: 0.5rem;">Heritage</span>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; margin: 0; font-weight: 500;">Historic Residences</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.portfolio-item-card:hover img {
    transform: scale(1.08);
}
.portfolio-item-card:hover div {
    background: linear-gradient(180deg, rgba(0,0,0,0.2) 20%, rgba(0,0,0,0.9) 100%) !important;
}
</style>

<!-- CTA Section -->
<section style="padding: 6rem 0; text-align: center;">
    <div class="container fade-up">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--color-black);">Experience the Extraordinary</h2>
        <p style="font-size: 1.2rem; color: var(--color-gray-500); margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            Connect with one of our specialized agents today to begin your journey toward finding your perfect home.
        </p>
        <a href="agents.php" class="btn btn-gold" style="font-size: 1.1rem; padding: 1rem 3rem;">Meet Our Team</a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
