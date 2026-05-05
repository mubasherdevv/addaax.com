/**
 * Product Detail Page JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(`${button.dataset.tab}-tab`).classList.add('active');
        });
    });
    
    // Thumbnail image gallery
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.getElementById('main-product-image');
    const mainImageContainer = document.querySelector('.product-main-image');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', () => {
            // Update main image with fade effect
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = `images/${thumbnail.dataset.image}`;
                mainImage.style.opacity = '1';
            }, 300);
            
            // Update active thumbnail
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        });
    });
    
    // Image zoom functionality
    if (mainImageContainer && mainImage) {
        // Add zoom container if not exists
        if (!document.querySelector('.zoom-container')) {
            const style = document.createElement('style');
            style.textContent = `
                .product-main-image {
                    position: relative;
                    overflow: hidden;
                }
                .zoom-container {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(255, 255, 255, 0.5);
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    z-index: 5;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .zoom-icon {
                    font-size: 2rem;
                    color: var(--primary-color, #4f46e5);
                    background-color: white;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                }
                .product-main-image:hover .zoom-container {
                    opacity: 1;
                }
                .product-main-image.zoomed img {
                    transform: scale(1.5);
                    cursor: zoom-out;
                }
                .product-main-image img {
                    transition: transform 0.3s ease;
                }
            `;
            document.head.appendChild(style);
            
            const zoomContainer = document.createElement('div');
            zoomContainer.className = 'zoom-container';
            zoomContainer.innerHTML = '<div class="zoom-icon"><i class="fas fa-search-plus"></i></div>';
            mainImageContainer.appendChild(zoomContainer);
        }
        
        mainImageContainer.addEventListener('click', function() {
            this.classList.toggle('zoomed');
            const icon = this.querySelector('.zoom-icon i');
            if (icon) {
                if (this.classList.contains('zoomed')) {
                    icon.className = 'fas fa-search-minus';
                } else {
                    icon.className = 'fas fa-search-plus';
                }
            }
        });
    }
    
    // Quantity input
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-quantity');
    const increaseBtn = document.getElementById('increase-quantity');
    
    decreaseBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    increaseBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        const maxValue = parseInt(quantityInput.getAttribute('max'));
        if (currentValue < maxValue) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    // Prevent manual input of invalid quantities
    quantityInput.addEventListener('change', () => {
        const currentValue = parseInt(quantityInput.value);
        const maxValue = parseInt(quantityInput.getAttribute('max'));
        
        if (isNaN(currentValue) || currentValue < 1) {
            quantityInput.value = 1;
        } else if (currentValue > maxValue) {
            quantityInput.value = maxValue;
        }
    });
    
    // Add to cart functionality
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            if (this.disabled) return;
            
            // Get product data from data attributes
            const productId = this.dataset.productId;
            const quantity = parseInt(quantityInput.value);
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            
            // Add to cart animation
            const cartIcon = document.querySelector('.user-actions .fa-shopping-cart');
            if (cartIcon) {
                cartIcon.classList.add('cart-added');
                setTimeout(() => {
                    cartIcon.classList.remove('cart-added');
                }, 1000);
            }
            
            // Show notification
            showNotification(`Added ${quantity} × ${productName} to cart!`, 'success');
            
            // Update cart count
            updateCartCount(quantity);
            
            // In a real application, you would send an AJAX request to add the product to the cart
            console.log('Added to cart:', {
                product_id: productId,
                product_name: productName,
                price: productPrice,
                quantity: quantity
            });
        });
    }
    
    // Smooth scroll to reviews
    const reviewsLink = document.querySelector('a[href="#reviews-tab"]');
    if (reviewsLink) {
        reviewsLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Find the reviews tab button and click it
            const reviewsTabBtn = document.querySelector('.tab-btn[data-tab="reviews"]');
            if (reviewsTabBtn) {
                reviewsTabBtn.click();
            }
            
            // Scroll to reviews section
            const reviewsTab = document.getElementById('reviews-tab');
            if (reviewsTab) {
                const offset = reviewsTab.getBoundingClientRect().top + window.pageYOffset - 100;
                window.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    // Wishlist functionality
    const wishlistBtn = document.querySelector('.btn-wishlist');
    
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            // Toggle between filled and outlined heart icon
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Added to wishlist!', 'success');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Removed from wishlist!', 'info');
            }
        });
    }
    
    /**
     * Shows a notification message
     * @param {string} message - The message to display
     * @param {string} type - The type of notification (success, error, info)
     */
    function showNotification(message, type = 'info') {
        // Check if notification container exists, if not create it
        let notificationContainer = document.querySelector('.notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.className = 'notification-container';
            document.body.appendChild(notificationContainer);
            
            // Add styles if not already present
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    .notification-container {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                    }
                    .notification {
                        background-color: white;
                        color: #333;
                        padding: 15px 20px;
                        margin-bottom: 10px;
                        border-radius: 4px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                        display: flex;
                        align-items: center;
                        min-width: 300px;
                        max-width: 400px;
                        transform: translateX(120%);
                        transition: transform 0.3s ease;
                        animation: slide-in 0.3s forwards, fade-out 0.3s 2.7s forwards;
                    }
                    .notification.success {
                        border-left: 4px solid #4CAF50;
                    }
                    .notification.error {
                        border-left: 4px solid #F44336;
                    }
                    .notification.info {
                        border-left: 4px solid #2196F3;
                    }
                    .notification i {
                        margin-right: 10px;
                        font-size: 20px;
                    }
                    .notification.success i {
                        color: #4CAF50;
                    }
                    .notification.error i {
                        color: #F44336;
                    }
                    .notification.info i {
                        color: #2196F3;
                    }
                    .cart-added {
                        animation: pulse 0.5s ease;
                    }
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.3); }
                        100% { transform: scale(1); }
                    }
                    @keyframes slide-in {
                        100% { transform: translateX(0); }
                    }
                    @keyframes fade-out {
                        100% { opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        // Set icon based on notification type
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        
        notification.innerHTML = `<i class="fas fa-${icon}"></i>${message}`;
        
        // Add to container
        notificationContainer.appendChild(notification);
        
        // Remove notification after it fades out
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    /**
     * Updates the cart count in the header
     * @param {number} addedItems - The number of items added to the cart
     */
    function updateCartCount(addedItems) {
        const cartCountElem = document.querySelector('.user-actions .fa-shopping-cart + span');
        if (cartCountElem) {
            const currentText = cartCountElem.textContent;
            const matches = currentText.match(/\((\d+)\)/);
            
            if (matches && matches[1]) {
                let count = parseInt(matches[1]) + addedItems;
                cartCountElem.textContent = `Cart (${count})`;
            }
        }
    }

    // Add sticky product actions for better mobile UX
    function setupStickyActions() {
        const productActions = document.querySelector('.product-actions');
        if (!productActions) return;

        // Create sticky container
        const stickyActions = document.createElement('div');
        stickyActions.className = 'sticky-actions';
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .sticky-actions {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                z-index: 100;
                display: none;
                align-items: center;
                justify-content: space-between;
                transform: translateY(100%);
                transition: transform 0.3s ease;
            }
            .sticky-actions.visible {
                transform: translateY(0);
                display: flex;
            }
            .sticky-actions .btn {
                margin: 0;
            }
            @media (max-width: 768px) {
                .product-detail-wrapper {
                    padding-bottom: 80px;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Clone product actions for sticky container
        stickyActions.innerHTML = productActions.innerHTML;
        document.body.appendChild(stickyActions);
        
        // Show/hide sticky actions based on scroll
        const productInfo = document.querySelector('.product-info');
        if (productInfo) {
            window.addEventListener('scroll', () => {
                const rect = productActions.getBoundingClientRect();
                // Check if original actions are out of viewport
                if (rect.bottom < 0) {
                    stickyActions.classList.add('visible');
                } else {
                    stickyActions.classList.remove('visible');
                }
            });
        }
        
        // Setup event listeners for buttons in sticky actions
        const stickyAddToCartBtn = stickyActions.querySelector('.btn-add-to-cart');
        if (stickyAddToCartBtn && addToCartBtn) {
            stickyAddToCartBtn.addEventListener('click', function() {
                // Trigger click on original button
                addToCartBtn.click();
            });
        }
    }
    
    // Only setup sticky actions on mobile
    if (window.innerWidth <= 768) {
        setupStickyActions();
    }
}); 