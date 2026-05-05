// Product Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile filters
    setupMobileFilters();
    
    // Category tree toggle
    const parentCategories = document.querySelectorAll('.parent-category');
    
    parentCategories.forEach(category => {
        category.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle active class on parent category
            this.classList.toggle('active');
            
            // Find the next sibling (subcategories list) and toggle it
            const subcategories = this.parentNode.querySelector('.subcategories');
            if (subcategories) {
                subcategories.classList.toggle('active');
            }
        });
    });
    
    // Check for selected category from URL parameters or sessionStorage
    function checkForCategoryParameters() {
        // First check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        let category = urlParams.get('category');
        let subcategory = urlParams.get('subcategory');
        
        // If not in URL, check sessionStorage
        if (!category && sessionStorage.getItem('selectedCategory')) {
            category = sessionStorage.getItem('selectedCategory');
            sessionStorage.removeItem('selectedCategory');
            
            if (sessionStorage.getItem('selectedSubcategory')) {
                subcategory = sessionStorage.getItem('selectedSubcategory');
                sessionStorage.removeItem('selectedSubcategory');
            }
        }
        
        // If we have a category, apply filtering
        if (category) {
            // Update result count
            const resultCount = document.querySelector('.products-result-count p');
            if (resultCount) {
                resultCount.textContent = `Filtered by: ${category.replace('-', ' ')}${subcategory ? ' > ' + subcategory.replace('-', ' ') : ''}`;
            }
            
            // Expand the relevant category in the sidebar
            const categoryElement = document.querySelector(`.parent-category[href="#"][data-category="${category}"]`);
            if (categoryElement) {
                categoryElement.classList.add('active');
                const subcategoriesList = categoryElement.parentNode.querySelector('.subcategories');
                if (subcategoriesList) {
                    subcategoriesList.classList.add('active');
                }
            }
            
            // Add a clear filters button
            const productsControls = document.querySelector('.products-controls');
            if (productsControls) {
                const clearFilterBtn = document.createElement('a');
                clearFilterBtn.href = 'products.php';
                clearFilterBtn.className = 'clear-filter-btn';
                clearFilterBtn.innerHTML = '<i class="fas fa-times"></i> Clear Filters';
                productsControls.appendChild(clearFilterBtn);
                
                // Add a little CSS for the button
                const style = document.createElement('style');
                style.textContent = `
                    .clear-filter-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        background-color: var(--background-alt);
                        color: var(--text-muted);
                        padding: 5px 10px;
                        border-radius: var(--radius-md);
                        font-size: 0.9rem;
                        margin-top: 10px;
                        transition: all 0.3s ease;
                    }
                    .clear-filter-btn:hover {
                        background-color: var(--error-color);
                        color: white;
                    }
                `;
                document.head.appendChild(style);
            }
        }
    }
    
    // Run the check
    checkForCategoryParameters();
    
    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    
    if (priceRange && minPrice && maxPrice) {
        // Update input when slider changes
        priceRange.addEventListener('input', function() {
            maxPrice.value = this.value;
        });
        
        // Update slider when max price input changes
        maxPrice.addEventListener('change', function() {
            priceRange.value = this.value;
        });
        
        // Min price can't be more than max price
        minPrice.addEventListener('change', function() {
            if (parseInt(this.value) > parseInt(maxPrice.value)) {
                this.value = maxPrice.value;
            }
        });
    }
    
    // Filter buttons
    const applyFilterBtn = document.querySelector('.btn-apply-filter');
    const clearFilterBtn = document.querySelector('.btn-clear-filter');
    
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // In a real app, this would filter products based on selected criteria
            // For now, we'll just reload the page to simulate the action
            const filterAnimation = document.querySelector('.products-grid');
            if (filterAnimation) {
                filterAnimation.style.opacity = '0.5';
                setTimeout(() => {
                    filterAnimation.style.opacity = '1';
                }, 500);
            }
        });
    }
    
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            // Reset all filters
            const checkboxes = document.querySelectorAll('.checkbox-list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            if (priceRange && minPrice && maxPrice) {
                priceRange.value = 500;
                minPrice.value = 0;
                maxPrice.value = 500;
            }
        });
    }
    
    // Sort products functionality
    const sortSelect = document.getElementById('sort-options');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const productsGrid = document.querySelector('.products-grid');
            
            if (productsGrid) {
                // Add slight animation to indicate sorting is happening
                productsGrid.style.opacity = '0.5';
                
                setTimeout(() => {
                    // In a real application, this would sort the products based on the selected criteria
                    // For now, we'll just change the opacity back to normal
                    productsGrid.style.opacity = '1';
                }, 500);
            }
        });
    }
    
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.btn-add-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get product info
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = productCard.querySelector('.product-price').textContent;
            
            // Show a small notification that product was added (would be better with a proper toast)
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = `Added ${productName} to cart`;
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('active');
            }, 10);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('active');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
            
            // Update cart count (this is just for show)
            const cartCount = document.querySelector('.btn-cart');
            if (cartCount) {
                const currentText = cartCount.textContent;
                const currentCount = parseInt(currentText.match(/\d+/)[0]);
                cartCount.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${currentCount + 1})`;
            }
        });
    });
    
    // Pagination (for demo purposes)
    const paginationLinks = document.querySelectorAll('.pagination a');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            paginationLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            if (!this.classList.contains('next')) {
                this.classList.add('active');
            }
            
            // Scroll back to top
            window.scrollTo({
                top: document.querySelector('.products-main').offsetTop - 100,
                behavior: 'smooth'
            });
            
            // Add slight loading effect to product grid
            const productsGrid = document.querySelector('.products-grid');
            if (productsGrid) {
                productsGrid.style.opacity = '0.5';
                
                setTimeout(() => {
                    productsGrid.style.opacity = '1';
                }, 500);
            }
        });
    });

    // Mobile filter toggle
    const filterToggle = document.getElementById('filterToggle');
    const filterClose = document.getElementById('filterClose');
    const productFiltersSidebar = document.getElementById('productFiltersSidebar');

    if (filterToggle && filterClose && productFiltersSidebar) {
        filterToggle.addEventListener('click', function() {
            productFiltersSidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        filterClose.addEventListener('click', function() {
            productFiltersSidebar.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// Mobile filter toggle functionality
const setupMobileFilters = () => {
    const filterToggle = document.getElementById('filterToggle');
    const filterClose = document.getElementById('filterClose');
    const filterSidebar = document.getElementById('productFiltersSidebar');
    const body = document.body;

    if (filterToggle && filterClose && filterSidebar) {
        // Open filters when toggle is clicked
        filterToggle.addEventListener('click', function() {
            filterSidebar.classList.add('active');
            body.style.overflow = 'hidden'; // Prevent scrolling
            
            // Create overlay if it doesn't exist
            if (!document.querySelector('.filter-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'filter-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.5)';
                overlay.style.zIndex = '999';
                body.appendChild(overlay);
                
                // Close filters when overlay is clicked
                overlay.addEventListener('click', closeFilters);
            }
        });
        
        // Close filters when close button is clicked
        filterClose.addEventListener('click', closeFilters);
        
        // Function to close filters
        function closeFilters() {
            filterSidebar.classList.remove('active');
            body.style.overflow = ''; // Allow scrolling
            
            // Remove overlay
            const overlay = document.querySelector('.filter-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    }
}; 