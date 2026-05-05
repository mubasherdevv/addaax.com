// Main JavaScript functionality for wholesale e-commerce website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu functionality
    initMobileMenu();
    
    // Initialize product filtering
    initFilters();
    
    // Initialize price range slider
    initPriceRangeSlider();
    
    // Initialize category accordion
    initCategoryAccordion();
    
    // Initialize product quantity selectors
    initQuantitySelectors();
    
    // Initialize animations on scroll
    initScrollAnimations();
    
    // Initialize smooth scrolling for anchor links
    initSmoothScroll();
    
    // Initialize statistics counter animation
    // Only animate if statistics section exists on the page
    if (document.querySelector('.statistics')) {
        animateStatistics();
    }
    
    // Initialize add to cart animation
    initAddToCart();
    
    // Add animation styles
    addAnimationStyles();
});

// Mobile menu functionality
function initMobileMenu() {
    const header = document.querySelector('header');
    if (!header) return; // Exit if header doesn't exist

    const headerContainer = header.querySelector('.container');
    if (!headerContainer) return; // Exit if header container doesn't exist

    // Create mobile menu button if it doesn't exist
    if (!document.querySelector('.mobile-menu-btn')) {
        const mobileMenuBtn = document.createElement('button');
        mobileMenuBtn.classList.add('mobile-menu-btn');
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        headerContainer.appendChild(mobileMenuBtn);
        
        // Toggle menu on button click
        mobileMenuBtn.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            if (!nav) return; // Exit if nav doesn't exist
            
            this.classList.toggle('active');
            nav.classList.toggle('active');
            
            // Change icon and toggle body scroll
            if (this.classList.contains('active')) {
                this.innerHTML = '<i class="fas fa-times"></i>';
                document.body.style.overflow = 'hidden';
            } else {
                this.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            }
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        const nav = document.querySelector('nav');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        // Only proceed if both nav and mobileMenuBtn exist
        if (nav && mobileMenuBtn) {
            // If nav is active AND click is outside of nav AND not on the menu button
            if (nav.classList.contains('active') && 
                !nav.contains(e.target) && 
                e.target !== mobileMenuBtn && 
                !mobileMenuBtn.contains(e.target)) {
                
                nav.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            }
        }
    });
}

// Category accordion functionality
function initCategoryAccordion() {
    const parentCategories = document.querySelectorAll('.parent-category');
    
    if (parentCategories) {
        parentCategories.forEach(category => {
            category.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Toggle the active class
                this.classList.toggle('active');
                
                // Get the subcategories element
                const subcategories = this.nextElementSibling;
                
                // Toggle the subcategories display with animation
                if (subcategories.style.maxHeight) {
                    subcategories.style.maxHeight = null;
                    this.querySelector('i').style.transform = 'rotate(0deg)';
                } else {
                    subcategories.style.maxHeight = subcategories.scrollHeight + "px";
                    this.querySelector('i').style.transform = 'rotate(180deg)';
                }
            });
        });
    }
}

// Filter functionality
function initFilters() {
    const applyFilterBtn = document.querySelector('.btn-apply-filter');
    const clearFilterBtn = document.querySelector('.btn-clear-filter');
    
    if (applyFilterBtn && clearFilterBtn) {
        // Apply filter
        applyFilterBtn.addEventListener('click', function() {
            applyFilters();
        });
        
        // Clear all filters
        clearFilterBtn.addEventListener('click', function() {
            clearFilters();
        });
    }
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-options');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortProducts(this.value);
        });
    }
}

// Apply selected filters (demo functionality)
function applyFilters() {
    // Show loading indicator
    showLoadingIndicator();
    
    // Get selected price range
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    
    // Get selected brands
    const selectedBrands = [];
    document.querySelectorAll('.filter-group:nth-of-type(2) input:checked').forEach(input => {
        selectedBrands.push(input.parentElement.textContent.trim());
    });
    
    // Get selected availability options
    const selectedAvailability = [];
    document.querySelectorAll('.filter-group:nth-of-type(3) input:checked').forEach(input => {
        selectedAvailability.push(input.parentElement.textContent.trim());
    });
    
    // Get selected bulk discount options
    const selectedBulkDiscounts = [];
    document.querySelectorAll('.filter-group:nth-of-type(4) input:checked').forEach(input => {
        selectedBulkDiscounts.push(input.parentElement.textContent.trim());
    });
    
    // Log filter selections (would be sent to server in real application)
    console.log(`Price range: $${minPrice} - $${maxPrice}`);
    console.log('Selected brands:', selectedBrands);
    console.log('Selected availability:', selectedAvailability);
    console.log('Selected bulk discounts:', selectedBulkDiscounts);
    
    // Simulate server request with delay
    setTimeout(() => {
        hideLoadingIndicator();
        
        // Apply subtle animation to product cards
        const products = document.querySelectorAll('.products-grid .product-card');
        products.forEach((product, index) => {
            product.style.opacity = '0';
            product.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }, 50 * index); // Staggered animation
        });
        
        // Show filter confirmation
        showNotification('Filters have been applied!');
    }, 800);
}

// Clear all filters
function clearFilters() {
    // Reset price range
    if (document.getElementById('priceRange')) {
        document.getElementById('priceRange').value = 500;
        document.getElementById('minPrice').value = 0;
        document.getElementById('maxPrice').value = 500;
    }
    
    // Uncheck all checkboxes
    document.querySelectorAll('.checkbox-list input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Show confirmation
    showNotification('All filters have been cleared!');
}

// Sort products (demo functionality)
function sortProducts(sortBy) {
    // Show loading indicator
    showLoadingIndicator();
    
    console.log(`Sorting by: ${sortBy}`);
    
    // Simulate server request with delay
    setTimeout(() => {
        hideLoadingIndicator();
        
        // Apply subtle animation to product cards
        const products = document.querySelectorAll('.products-grid .product-card');
        products.forEach((product, index) => {
            product.style.opacity = '0';
            product.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }, 50 * index); // Staggered animation
        });
        
        // Show notification
        showNotification(`Products sorted by ${sortBy}!`);
    }, 800);
}

// Initialize price range slider
function initPriceRangeSlider() {
    const rangeSlider = document.getElementById('priceRange');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    
    if (rangeSlider && minPriceInput && maxPriceInput) {
        // Update max price input when slider changes
        rangeSlider.addEventListener('input', function() {
            maxPriceInput.value = this.value;
        });
        
        // Update slider when max price input changes
        maxPriceInput.addEventListener('change', function() {
            rangeSlider.value = this.value;
        });
        
        // Update min/max values when inputs change
        minPriceInput.addEventListener('change', function() {
            if (parseInt(this.value) > parseInt(maxPriceInput.value)) {
                this.value = maxPriceInput.value;
            }
        });
        
        maxPriceInput.addEventListener('change', function() {
            if (parseInt(this.value) < parseInt(minPriceInput.value)) {
                this.value = minPriceInput.value;
            }
        });
    }
}

// Initialize quantity selectors for product pages
function initQuantitySelectors() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    if (quantityInputs) {
        quantityInputs.forEach(input => {
            const decreaseBtn = input.querySelector('.decrease');
            const increaseBtn = input.querySelector('.increase');
            const quantityValue = input.querySelector('input');
            
            if (decreaseBtn && increaseBtn && quantityValue) {
                decreaseBtn.addEventListener('click', function() {
                    let value = parseInt(quantityValue.value);
                    if (value > 1) {
                        value--;
                        quantityValue.value = value;
                        // Trigger animation
                        this.classList.add('clicked');
                        setTimeout(() => {
                            this.classList.remove('clicked');
                        }, 300);
                    }
                });
                
                increaseBtn.addEventListener('click', function() {
                    let value = parseInt(quantityValue.value);
                    value++;
                    quantityValue.value = value;
                    // Trigger animation
                    this.classList.add('clicked');
                    setTimeout(() => {
                        this.classList.remove('clicked');
                    }, 300);
                });
            }
        });
    }
}

// Loading indicator functionality
function showLoadingIndicator() {
    // Create loading indicator if it doesn't exist
    if (!document.querySelector('.loading-indicator')) {
        const loadingIndicator = document.createElement('div');
        loadingIndicator.classList.add('loading-indicator');
        loadingIndicator.innerHTML = `
            <div class="spinner"></div>
            <p>Loading...</p>
        `;
        document.body.appendChild(loadingIndicator);
    }
    
    // Show the loading indicator
    const indicator = document.querySelector('.loading-indicator');
    indicator.classList.add('active');
}

function hideLoadingIndicator() {
    const indicator = document.querySelector('.loading-indicator');
    if (indicator) {
        indicator.classList.remove('active');
    }
}

// Utility function to show notifications
function showNotification(message) {
    // Create notification element if it doesn't exist
    if (!document.querySelector('.notification')) {
        const notification = document.createElement('div');
        notification.classList.add('notification');
        document.body.appendChild(notification);
    }
    
    const notificationEl = document.querySelector('.notification');
    notificationEl.textContent = message;
    notificationEl.classList.add('active');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notificationEl.classList.remove('active');
    }, 3000);
}

// Add to cart functionality (demo)
function initAddToCart() {
    const addToCartButtons = document.querySelectorAll('.btn-add-cart');
    if (addToCartButtons && addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get product info
                const productCard = this.closest('.product-card');
                if (!productCard) return;
                
                const productName = productCard.querySelector('h3')?.textContent || 'Product';
                const productPrice = productCard.querySelector('.product-price')?.textContent || '0';
                
                // Create flying animation element
                const productImage = productCard.querySelector('img');
                if (productImage) {
                    // Get image position
                    const imgRect = productImage.getBoundingClientRect();
                    
                    // Get cart position
                    const cart = document.querySelector('.btn-primary.btn-icon');
                    if (!cart) return;
                    
                    const cartRect = cart.getBoundingClientRect();
                    
                    // Create flying element
                    const flyingImg = document.createElement('div');
                    flyingImg.classList.add('flying-image');
                    flyingImg.style.backgroundImage = `url(${productImage.src})`;
                    flyingImg.style.width = '50px';
                    flyingImg.style.height = '50px';
                    flyingImg.style.backgroundSize = 'cover';
                    flyingImg.style.backgroundPosition = 'center';
                    flyingImg.style.position = 'fixed';
                    flyingImg.style.borderRadius = '50%';
                    flyingImg.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                    flyingImg.style.zIndex = '9999';
                    flyingImg.style.transition = 'all 0.8s cubic-bezier(0.18, 0.89, 0.32, 1.28)';
                    
                    // Set initial position (at the product image)
                    flyingImg.style.top = `${imgRect.top}px`;
                    flyingImg.style.left = `${imgRect.left}px`;
                    
                    // Add to DOM
                    document.body.appendChild(flyingImg);
                    
                    // Apply button animation
                    this.classList.add('adding');
                    
                    // Trigger flying animation
                    setTimeout(() => {
                        flyingImg.style.top = `${cartRect.top + 10}px`;
                        flyingImg.style.left = `${cartRect.left + 10}px`;
                        flyingImg.style.width = '20px';
                        flyingImg.style.height = '20px';
                        flyingImg.style.opacity = '0';
                    }, 50);
                    
                    // Clean up after animation completes
                    setTimeout(() => {
                        this.classList.remove('adding');
                        document.body.removeChild(flyingImg);
                        
                        // Show confirmation
                        showNotification(`${productName} added to cart!`);
                        
                        // Update cart count (demo)
                        const cartCount = document.querySelector('.btn-cart, .btn-primary.btn-icon');
                        if (cartCount) {
                            let currentCount = parseInt(cartCount.textContent.match(/\d+/)[0] || '0');
                            cartCount.innerHTML = `<i class="fas fa-shopping-cart"></i> <span>Cart (${currentCount + 1})</span>`;
                            
                            // Add cart bounce animation
                            cartCount.classList.add('bounce');
                            setTimeout(() => {
                                cartCount.classList.remove('bounce');
                            }, 1000);
                        }
                    }, 800);
                }
            });
        });
    }
}

// Add flying image animation styles
function addAnimationStyles() {
    const style = document.createElement('style');
    style.innerHTML += `
        @keyframes scaleUp {
            0% { transform: scale(0.5); opacity: 0; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .flying-image {
            animation: scaleUp 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
}

// Initialize testimonial slider if exists
const testimonialSlider = document.querySelector('.testimonials-slider');
if (testimonialSlider && testimonialSlider.children.length > 1) {
    let currentSlide = 0;
    const testimonials = testimonialSlider.querySelectorAll('.testimonial');
    
    // Hide all slides except the first one
    for (let i = 1; i < testimonials.length; i++) {
        testimonials[i].style.display = 'none';
    }
    
    // Create navigation dots
    const dotsContainer = document.createElement('div');
    dotsContainer.classList.add('slider-dots');
    
    for (let i = 0; i < testimonials.length; i++) {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        if (i === 0) dot.classList.add('active');
        
        dot.addEventListener('click', function() {
            goToSlide(i);
        });
        
        dotsContainer.appendChild(dot);
    }
    
    testimonialSlider.after(dotsContainer);
    
    function goToSlide(n) {
        // Don't do anything if we're already on this slide
        if (currentSlide === n) return;
        
        // Fade out current slide
        testimonials[currentSlide].classList.add('fade-out');
        
        setTimeout(() => {
            testimonials[currentSlide].style.display = 'none';
            testimonials[currentSlide].classList.remove('fade-out');
            document.querySelectorAll('.slider-dots .dot')[currentSlide].classList.remove('active');
            
            currentSlide = (n + testimonials.length) % testimonials.length;
            
            // Fade in new slide
            testimonials[currentSlide].style.display = 'block';
            testimonials[currentSlide].classList.add('fade-in');
            document.querySelectorAll('.slider-dots .dot')[currentSlide].classList.add('active');
            
            setTimeout(() => {
                testimonials[currentSlide].classList.remove('fade-in');
            }, 500);
        }, 500);
    }
    
    // Initialize auto-sliding
    let sliderInterval = setInterval(() => {
        goToSlide(currentSlide + 1);
    }, 6000);
    
    // Pause auto-sliding when hovering over testimonials
    testimonialSlider.addEventListener('mouseenter', () => {
        clearInterval(sliderInterval);
    });
    
    testimonialSlider.addEventListener('mouseleave', () => {
        sliderInterval = setInterval(() => {
            goToSlide(currentSlide + 1);
        }, 6000);
    });
}

// Initialize animations on scroll
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.section-title, .hero-content, .category-card, .product-card, .testimonial, .cta-content');
    
    // Define IntersectionObserver
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                // Unobserve after animation is triggered
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        threshold: 0.1,
        rootMargin: '-50px'
    });
    
    // Observe each element
    animatedElements.forEach(element => {
        observer.observe(element);
    });
    
    // Add necessary CSS for animations
    const style = document.createElement('style');
    style.innerHTML = `
        .section-title, .hero-content, .category-card, .product-card, .testimonial, .cta-content {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .category-card, .product-card {
            transition-delay: calc(var(--animation-order, 0) * 100ms);
        }
        
        .animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .fade-out {
            animation: fadeOut 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .bounce {
            animation: bounce 0.5s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .adding {
            animation: adding 0.5s ease;
        }
        
        @keyframes adding {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        
        .clicked {
            animation: clicked 0.3s ease;
        }
        
        @keyframes clicked {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .loading-indicator {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .loading-indicator.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid rgba(79, 70, 229, 0.2);
            border-top-color: var(--primary-color);
            animation: spin 1s infinite linear;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // Set animation order for grid items
    document.querySelectorAll('.category-card, .product-card').forEach((item, index) => {
        item.style.setProperty('--animation-order', index);
    });
}

// Smooth scrolling for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            e.preventDefault();
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Close mobile menu if open
                const nav = document.querySelector('nav');
                const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
                
                if (nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    mobileMenuBtn.classList.remove('active');
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    document.body.style.overflow = '';
                }
                
                // Scroll to element
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Function to animate statistics numbers
function animateStatistics() {
    const statElements = document.querySelectorAll('.stat-card h3');
    
    statElements.forEach(element => {
        // Get the target number (without '+' or any other characters)
        const targetNumber = parseInt(element.innerText.replace(/,|\+|[a-zA-Z]/g, ''));
        const duration = 2000; // Animation duration in milliseconds
        const startTime = Date.now();
        let currentNumber = 0;
        
        // Store original text to preserve any suffix (like '+')
        const originalText = element.innerText;
        const suffix = originalText.includes('+') ? '+' : '';
        
        // Start the animation
        const timer = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smoother animation
            const easeOutQuad = progress * (2 - progress);
            
            // Calculate current number based on progress
            currentNumber = Math.floor(targetNumber * easeOutQuad);
            
            // Add thousand separators
            const formattedNumber = currentNumber.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            
            // Update the text with the current number and preserve suffix
            element.innerText = formattedNumber + suffix;
            
            // Stop the animation when complete
            if (progress === 1) {
                clearInterval(timer);
            }
        }, 16); // ~60fps
    });
}

// Add a scroll listener to animate statistics when they come into view
window.addEventListener('scroll', function() {
    const statisticsSection = document.querySelector('.statistics');
    if (statisticsSection) {
        const rect = statisticsSection.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
        
        if (isVisible) {
            // Only animate once
            if (!statisticsSection.classList.contains('animated')) {
                statisticsSection.classList.add('animated');
                animateStatistics();
            }
        }
    }
}); 