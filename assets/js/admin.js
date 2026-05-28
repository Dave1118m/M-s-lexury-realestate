/**
 * Hawassa Luxury Real Estate - Admin Panel JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    // Confirm delete actions
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                return;
            }
            
            const id = this.getAttribute('data-id');
            if (id) {
                let endpoint = '';
                if (window.location.pathname.includes('properties.php')) endpoint = '../api/properties.php';
                else if (window.location.pathname.includes('users.php')) endpoint = '../api/users.php';
                
                if (endpoint) {
                    try {
                        const res = await fetch(`${endpoint}?id=${id}`, { method: 'DELETE' });
                        const data = await res.json();
                        if (data.success) {
                            this.closest('tr').remove();
                        } else {
                            alert(data.error || 'Failed to delete item.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Network error while deleting.');
                    }
                }
            }
        });
    });

    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Image preview for file inputs
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function () {
            const preview = document.querySelector(this.getAttribute('data-preview'));
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Slug auto-generation from title
    const titleInput = document.querySelector('#title');
    const slugInput = document.querySelector('#slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.getAttribute('data-manual')) {
                slugInput.value = slugify(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            this.setAttribute('data-manual', 'true');
        });
    }

    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w-]+/g, '')
            .replace(/--+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    // Data table search
    const tableSearch = document.querySelector('#tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Dashboard charts moved to dashboard.php directly to avoid caching and script execution issues
});