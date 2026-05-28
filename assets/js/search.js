/**
 * Hawassa Luxury Real Estate - Property Search & Filter (AJAX)
 */
document.addEventListener('DOMContentLoaded', function () {

    const searchForm = document.getElementById('propertySearchForm');
    const resultsContainer = document.getElementById('searchResults');
    const resultCount = document.getElementById('resultCount');
    const loadingSpinner = document.getElementById('searchSpinner');

    if (!searchForm || !resultsContainer) return;

    // Get initial results on page load
    fetchProperties();

    // Handle form submission
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchProperties();
    });

    // Live search on input change (debounced)
    let debounceTimer;
    const filterInputs = searchForm.querySelectorAll('input, select');
    filterInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchProperties, 400);
        });
        input.addEventListener('change', fetchProperties);
    });

    async function fetchProperties() {
        // Show loading
        if (loadingSpinner) loadingSpinner.style.display = 'block';
        resultsContainer.style.opacity = '0.5';

        // Collect form data
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);

        // Get URL params too (for shared links)
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams) {
            if (!params.has(key)) {
                params.append(key, value);
            }
        }

        try {
            const siteUrl = document.querySelector('meta[name="site-url"]')?.content || '';
            const response = await fetch(siteUrl + '/api/search.php?' + params.toString());
            if (!response.ok) throw new Error('Network error');
            const data = await response.json();

            // Update results
            renderProperties(data.properties);
            if (resultCount) {
                resultCount.textContent = data.total + ' propert' + (data.total !== 1 ? 'ies' : 'y') + ' found';
            }
        } catch (error) {
            console.error('Search error:', error);
            resultsContainer.innerHTML = '<p class="text-center">Error loading properties. Please try again.</p>';
        } finally {
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            resultsContainer.style.opacity = '1';
        }
    }

    function renderProperties(properties) {
        if (!properties || properties.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center" style="padding: 4rem 0;">
                    <h3>No Properties Found</h3>
                    <p style="color: var(--color-gray-500);">Try adjusting your search criteria.</p>
                </div>`;
            return;
        }

        const siteUrl = document.querySelector('meta[name="site-url"]')?.content || '';
        const isLoggedIn = document.querySelector('meta[name="is-logged-in"]')?.content === '1';

        resultsContainer.innerHTML = properties.map(prop => `
            <div class="property-card fade-up" style="position: relative;">
                <div class="property-card-image">
                    <a href="${siteUrl}/pages/property.php?slug=${prop.slug}">
                        <img src="${prop.image_main || 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1000&q=80'}" alt="${prop.title}" loading="lazy">
                    </a>
                    ${prop.featured ? '<span class="property-badge">Featured</span>' : ''}
                </div>
                ${isLoggedIn ? `<button class="save-prop-btn" data-id="${prop.id}" style="position:absolute; top:10px; right:10px; background:white; border:none; border-radius:50%; width:35px; height:35px; font-size:1.2rem; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.2); z-index:10;">🤍</button>` : ''}
                <div class="property-card-body">
                    <div class="property-price">${formatPrice(prop.price)}</div>
                    <h3><a href="${siteUrl}/pages/property.php?slug=${prop.slug}">${prop.title}</a></h3>
                    <p class="property-location">${prop.city}, ${prop.state}</p>
                    <div class="property-meta">
                        <span>🛏 ${prop.bedrooms} Beds</span>
                        <span>🛁 ${prop.bathrooms} Baths</span>
                        <span>📐 ${prop.area_sqft?.toLocaleString()} sqft</span>
                    </div>
                </div>
            </div>
        `).join('');

        // Attach listeners for save buttons
        if (isLoggedIn) {
            document.querySelectorAll('.save-prop-btn').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const propId = this.getAttribute('data-id');
                    try {
                        const res = await fetch('../api/save_property.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ property_id: propId })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (data.status === 'saved') {
                                this.innerHTML = '❤️';
                            } else {
                                this.innerHTML = '🤍';
                            }
                        }
                    } catch(err) { console.error(err); }
                });
            });
        }

        // Re-trigger animations
        document.querySelectorAll('.fade-up').forEach(el => {
            el.classList.remove('visible');
            setTimeout(() => el.classList.add('visible'), 50);
        });
    }
});