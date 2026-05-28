/**
 * Hawassa Luxury Real Estate - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    // Theme Toggle Logic
    const themeToggle = document.getElementById('theme-toggle');
    const lightIcon = themeToggle ? themeToggle.querySelector('.light-icon') : null;
    const darkIcon = themeToggle ? themeToggle.querySelector('.dark-icon') : null;
    
    function updateThemeIcons(theme) {
        if (!lightIcon || !darkIcon) return;
        if (theme === 'dark') {
            lightIcon.style.display = 'none';
            darkIcon.style.display = 'block';
        } else {
            lightIcon.style.display = 'block';
            darkIcon.style.display = 'none';
        }
    }

    if (themeToggle) {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        updateThemeIcons(currentTheme);

        themeToggle.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons(newTheme);
        });
    }


    // Mobile Menu Toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            mainNav.classList.toggle('active');
            const isOpen = mainNav.classList.contains('active');
            menuToggle.setAttribute('aria-expanded', isOpen);
            menuToggle.textContent = isOpen ? '✕' : '☰';
        });
    }

    // Scroll Animation (Intersection Observer)
    const fadeElements = document.querySelectorAll('.fade-up');

    if (fadeElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(el => observer.observe(el));
    }

    // Smooth Scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Header Scroll Effect
    const header = document.querySelector('.main-header');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
        } else {
            header.style.boxShadow = 'none';
        }

        lastScroll = currentScroll;
    });

    // Number Counter Animation
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;

                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.floor(current).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                };

                updateCounter();
                counterObserver.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));

    // Newsletter Form Submission
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('Thank you for subscribing to the Hawassa newsletter!');
                this.reset();
            }
        });
    }

    // Image Gallery Lightbox (simple)
    const galleryImages = document.querySelectorAll('.property-gallery img');
    if (galleryImages.length > 0) {
        galleryImages.forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function () {
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed; inset: 0; background: rgba(0,0,0,0.9);
                    z-index: 9999; display: flex; align-items: center; justify-content: center;
                    cursor: pointer;
                `;
                const clone = this.cloneNode(true);
                clone.style.cssText = 'max-width: 90vw; max-height: 90vh; object-fit: contain;';
                overlay.appendChild(clone);
                overlay.addEventListener('click', () => overlay.remove());
                document.body.appendChild(overlay);
            });
        });
    }

    // Hero Background Image Slider (Ken Burns Slideshow)
    const heroSlides = document.querySelectorAll('.hero-slide');
    if (heroSlides.length > 0) {
        let currentSlideIndex = 0;
        const slideInterval = 7000; // Change slide every 7 seconds (transition is 2s, visibility is 5s)

        function nextSlide() {
            // Remove active class from the current slide
            heroSlides[currentSlideIndex].classList.remove('active');
            
            // Move to the next slide index
            currentSlideIndex = (currentSlideIndex + 1) % heroSlides.length;
            
            // Add active class to the new slide
            heroSlides[currentSlideIndex].classList.add('active');
        }

        // Cycle background images automatically
        setInterval(nextSlide, slideInterval);
    }

    // Price Formatting
    function formatPrice(price) {
        if (price >= 1000000) {
            return '$' + (price / 1000000).toFixed(1) + 'M';
        } else if (price >= 1000) {
            return '$' + (price / 1000).toFixed(1) + 'K';
        }
        return '$' + price.toLocaleString();
    }

    // Expose to global scope
    window.formatPrice = formatPrice;
});