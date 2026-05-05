/**
 * Cart Management System
 */
class ShoppingCart {
    constructor() {
        this.items = [];
        this.total = 0;
        this.count = 0;
        this.init();
    }

    init() {
        // Load cart from localStorage if available
        const savedCart = localStorage.getItem('shopping_cart');
        if (savedCart) {
            try {
                const cartData = JSON.parse(savedCart);
                this.items = cartData.items || [];
                this.recalculate();
            } catch (e) {
                console.error('Error loading cart data:', e);
                this.items = [];
                this.save();
            }
        }
        
        // Update UI elements
        this.updateCartUI();
        
        // Setup event listeners
        document.addEventListener('DOMContentLoaded', () => {
            this.setupEventListeners();
        });
    }

    setupEventListeners() {
        // Listen for cart update events
        document.addEventListener('cart:updated', () => {
            this.updateCartUI();
        });
    }

    // Add item to cart
    addItem(productId, name, price, quantity = 1, image = null) {
        // Check if item already in cart
        const existingItemIndex = this.items.findIndex(item => item.productId === productId);
        
        if (existingItemIndex !== -1) {
            // Update quantity of existing item
            this.items[existingItemIndex].quantity += quantity;
        } else {
            // Add new item
            this.items.push({
                productId,
                name,
                price,
                quantity,
                image
            });
        }
        
        this.recalculate();
        this.save();
        return true;
    }

    // Remove item from cart
    removeItem(productId) {
        this.items = this.items.filter(item => item.productId !== productId);
        this.recalculate();
        this.save();
    }

    // Update item quantity
    updateQuantity(productId, quantity) {
        const itemIndex = this.items.findIndex(item => item.productId === productId);
        if (itemIndex !== -1) {
            if (quantity <= 0) {
                // Remove item if quantity is 0 or negative
                this.removeItem(productId);
            } else {
                this.items[itemIndex].quantity = quantity;
                this.recalculate();
                this.save();
            }
        }
    }

    // Clear all items from cart
    clearCart() {
        this.items = [];
        this.recalculate();
        this.save();
    }

    // Recalculate totals
    recalculate() {
        this.count = this.items.reduce((sum, item) => sum + item.quantity, 0);
        this.total = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    // Save cart to localStorage
    save() {
        localStorage.setItem('shopping_cart', JSON.stringify({
            items: this.items
        }));
        
        // Dispatch event that cart was updated
        document.dispatchEvent(new CustomEvent('cart:updated'));
    }

    // Update cart UI elements throughout the site
    updateCartUI() {
        // Update cart count in header
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = this.count;
        });
        
        // Update cart icon to show items
        const cartButtons = document.querySelectorAll('.btn-primary.btn-icon');
        cartButtons.forEach(button => {
            const countSpan = button.querySelector('span');
            if (countSpan) {
                countSpan.textContent = `Cart (${this.count})`;
            }
        });
    }
}

// Cart API - functions to interact with the cart
const Cart = {
    _instance: new ShoppingCart(),
    
    // Add item to cart
    addItem: function(productId, name, price, quantity = 1, image = null) {
        return this._instance.addItem(productId, name, price, quantity, image);
    },
    
    // Remove item from cart
    removeItem: function(productId) {
        this._instance.removeItem(productId);
    },
    
    // Update item quantity
    updateQuantity: function(productId, quantity) {
        this._instance.updateQuantity(productId, quantity);
    },
    
    // Clear all items from cart
    clearCart: function() {
        this._instance.clearCart();
    },
    
    // Get cart items
    getItems: function() {
        return this._instance.items;
    },
    
    // Get cart total
    getTotal: function() {
        return this._instance.total;
    },
    
    // Get item count
    getCount: function() {
        return this._instance.count;
    },
    
    // Get cart subtotal
    getSubtotal: function() {
        return this._instance.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }
};

// Make Cart available globally
window.Cart = Cart;

// Cart Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize quantity controls
    initQuantityControls();
    
    // Initialize cart item removal
    initRemoveItems();
    
    // Initialize cart summary calculations
    updateCartSummary();
    
    // Initialize shipping method change handling
    initShippingMethod();
    
    // Initialize coupon code
    initCouponCode();
    
    // Initialize clear cart functionality
    initClearCart();
    
    // Initialize checkout functionality
    initCheckout();
});

// Quantity controls functionality
function initQuantityControls() {
    const quantityControls = document.querySelectorAll('.quantity-input');
    
    quantityControls.forEach(control => {
        const decreaseBtn = control.querySelector('.decrease');
        const increaseBtn = control.querySelector('.increase');
        const input = control.querySelector('input');
        const cartItem = control.closest('.cart-item');
        const minQty = parseInt(cartItem.querySelector('.min-qty').textContent.match(/\d+/)[0]) || 1;
        const priceElement = cartItem.querySelector('.product-price');
        const totalElement = cartItem.querySelector('.item-total');
        
        // Get the price value from the price element
        const price = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, ''));
        
        // Decrease quantity button
        decreaseBtn.addEventListener('click', function() {
            let qty = parseInt(input.value);
            if (qty > minQty) {
                qty--;
                input.value = qty;
                updateItemTotal(price, qty, totalElement);
                updateCartSummary();
            } else {
                // Provide visual feedback that min qty has been reached
                control.classList.add('min-reached');
                setTimeout(() => {
                    control.classList.remove('min-reached');
                }, 500);
            }
        });
        
        // Increase quantity button
        increaseBtn.addEventListener('click', function() {
            let qty = parseInt(input.value);
            qty++;
            input.value = qty;
            updateItemTotal(price, qty, totalElement);
            updateCartSummary();
        });
        
        // Manual input change
        input.addEventListener('change', function() {
            let qty = parseInt(this.value);
            
            if (isNaN(qty) || qty < minQty) {
                qty = minQty;
                this.value = minQty;
                
                // Visual feedback for invalid input
                control.classList.add('min-reached');
                setTimeout(() => {
                    control.classList.remove('min-reached');
                }, 500);
            }
            
            updateItemTotal(price, qty, totalElement);
            updateCartSummary();
        });
    });
    
    // Add CSS for visual feedback
    const style = document.createElement('style');
    style.textContent = `
        .quantity-input.min-reached {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            20%, 60% {
                transform: translateX(-5px);
            }
            40%, 80% {
                transform: translateX(5px);
            }
        }
    `;
    document.head.appendChild(style);
}

// Update item total price
function updateItemTotal(price, quantity, element) {
    const total = (price * quantity).toFixed(2);
    element.textContent = `$${total}`;
}

// Cart item removal functionality
function initRemoveItems() {
    const removeButtons = document.querySelectorAll('.remove-item');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            
            // Add removal animation
            cartItem.style.opacity = '0';
            cartItem.style.height = `${cartItem.offsetHeight}px`;
            
            setTimeout(() => {
                cartItem.style.height = '0';
                cartItem.style.padding = '0';
                cartItem.style.margin = '0';
                cartItem.style.overflow = 'hidden';
                
                setTimeout(() => {
                    cartItem.remove();
                    updateCartCount();
                    updateCartSummary();
                    
                    // If no items left, refresh page to show empty cart state
                    const cartItems = document.querySelectorAll('.cart-item');
                    if (cartItems.length === 0) {
                        const emptyCartMessage = document.createElement('div');
                        emptyCartMessage.className = 'empty-cart-message';
                        emptyCartMessage.innerHTML = `
                            <div class="empty-cart-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3>Your cart is empty</h3>
                            <p>Looks like you haven't added any products to your cart yet.</p>
                            <a href="products.php" class="btn btn-primary">Browse Products</a>
                        `;
                        
                        const cartItemsContainer = document.querySelector('.cart-items');
                        cartItemsContainer.innerHTML = '';
                        cartItemsContainer.appendChild(emptyCartMessage);
                        
                        // Hide the cart actions
                        const cartActions = document.querySelector('.cart-actions');
                        if (cartActions) {
                            cartActions.style.display = 'none';
                        }
                    }
                }, 300);
            }, 300);
        });
    });
    
    // Add CSS for empty cart
    const style = document.createElement('style');
    style.textContent = `
        .empty-cart-message {
            text-align: center;
            padding: var(--space-xl) 0;
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: var(--space-md);
        }
        
        .empty-cart-message h3 {
            margin-bottom: var(--space-sm);
        }
        
        .empty-cart-message p {
            color: var(--text-muted);
            margin-bottom: var(--space-lg);
        }
    `;
    document.head.appendChild(style);
}

// Update cart summary
function updateCartSummary() {
    // Get all cart items
    const cartItems = document.querySelectorAll('.cart-item');
    let subtotal = 0;
    let discount = 0;
    
    // Calculate subtotal and discount
    cartItems.forEach(item => {
        const priceElement = item.querySelector('.product-price');
        const originalPriceElement = item.querySelector('.original-price');
        const quantityElement = item.querySelector('.quantity-input input');
        
        if (priceElement && quantityElement) {
            const price = parseFloat(priceElement.textContent.replace(/[^0-9.]/g, ''));
            const quantity = parseInt(quantityElement.value);
            
            subtotal += price * quantity;
            
            // Calculate discount if original price exists
            if (originalPriceElement) {
                const originalPrice = parseFloat(originalPriceElement.textContent.replace(/[^0-9.]/g, ''));
                discount += (originalPrice - price) * quantity;
            }
        }
    });
    
    // Update summary display
    const subtotalElement = document.querySelector('.summary-row:nth-child(1) .summary-value');
    const discountElement = document.querySelector('.summary-row:nth-child(2) .summary-value');
    const taxElement = document.querySelector('.summary-row:nth-child(3) .summary-value');
    
    if (subtotalElement) {
        subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    }
    
    if (discountElement) {
        discountElement.textContent = `-$${discount.toFixed(2)}`;
    }
    
    // Calculate tax (example: 8% tax)
    const tax = subtotal * 0.08;
    
    if (taxElement) {
        taxElement.textContent = `$${tax.toFixed(2)}`;
    }
    
    // Get shipping cost
    const shippingSelect = document.getElementById('shipping-method');
    let shippingCost = 0;
    
    if (shippingSelect) {
        const selectedOption = shippingSelect.options[shippingSelect.selectedIndex];
        
        if (selectedOption.value === 'standard') {
            shippingCost = 15.99;
        } else if (selectedOption.value === 'express') {
            shippingCost = 29.99;
        } else if (selectedOption.value === 'free') {
            shippingCost = 0;
        }
    }
    
    // Calculate total
    const total = subtotal - discount + tax + shippingCost;
    
    // Update total element
    const totalElement = document.querySelector('.total-value');
    
    if (totalElement) {
        totalElement.textContent = `$${total.toFixed(2)}`;
    }
    
    // Update cart item count in title
    const cartItemsTitle = document.querySelector('.cart-items h2');
    
    if (cartItemsTitle) {
        cartItemsTitle.textContent = `Your Cart (${cartItems.length} items)`;
    }
}

// Initialize shipping method change
function initShippingMethod() {
    const shippingSelect = document.getElementById('shipping-method');
    
    if (shippingSelect) {
        shippingSelect.addEventListener('change', function() {
            updateCartSummary();
            
            // Show shipping method change notification
            showNotification('Shipping method updated!');
        });
    }
}

// Initialize coupon code functionality
function initCouponCode() {
    const couponButton = document.querySelector('.btn-apply');
    const couponInput = document.getElementById('coupon');
    
    if (couponButton && couponInput) {
        couponButton.addEventListener('click', function() {
            const couponCode = couponInput.value.trim();
            
            if (couponCode) {
                // In a real application, you would validate the coupon code with a server request
                // For now, we'll simulate a successful coupon application
                
                // Check if it's a valid coupon code (for demo purposes)
                if (couponCode.toLowerCase() === 'wholesale10') {
                    showNotification('Coupon applied: 10% off!');
                    
                    // Apply additional discount
                    const subtotalElement = document.querySelector('.summary-row:nth-child(1) .summary-value');
                    const subtotalValue = parseFloat(subtotalElement.textContent.replace(/[^0-9.]/g, ''));
                    const additionalDiscount = subtotalValue * 0.1;
                    
                    // Update discount display
                    const discountElement = document.querySelector('.summary-row:nth-child(2) .summary-value');
                    const currentDiscount = parseFloat(discountElement.textContent.replace(/[^0-9.-]/g, ''));
                    const newDiscount = currentDiscount + additionalDiscount;
                    
                    discountElement.textContent = `-$${newDiscount.toFixed(2)}`;
                    
                    // Add coupon applied indicator
                    const couponElement = document.createElement('div');
                    couponElement.className = 'coupon-applied';
                    couponElement.innerHTML = `
                        <div class="summary-row">
                            <span>Coupon (WHOLESALE10)</span>
                            <span class="summary-value discount">-$${additionalDiscount.toFixed(2)}</span>
                        </div>
                    `;
                    
                    const taxRow = document.querySelector('.summary-row:nth-child(3)');
                    taxRow.parentNode.insertBefore(couponElement, taxRow);
                    
                    // Disable coupon input and button
                    couponInput.disabled = true;
                    couponButton.disabled = true;
                    couponButton.textContent = 'Applied';
                    couponButton.classList.add('disabled');
                    
                    updateCartSummary();
                } else {
                    // Invalid coupon
                    showNotification('Invalid coupon code', 'error');
                    
                    couponInput.classList.add('error');
                    setTimeout(() => {
                        couponInput.classList.remove('error');
                    }, 1000);
                }
            } else {
                // Empty coupon code
                showNotification('Please enter a coupon code', 'warning');
            }
        });
    }
}

// Initialize clear cart functionality
function initClearCart() {
    const clearCartButton = document.getElementById('clearCart');
    
    if (clearCartButton) {
        clearCartButton.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear your cart?')) {
                const cartItems = document.querySelectorAll('.cart-item');
                
                // Animate all items removal
                cartItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(20px)';
                        
                        setTimeout(() => {
                            item.remove();
                            
                            // If last item, update UI
                            if (index === cartItems.length - 1) {
                                updateCartCount();
                                updateCartSummary();
                                
                                // Show empty cart message
                                const emptyCartMessage = document.createElement('div');
                                emptyCartMessage.className = 'empty-cart-message';
                                emptyCartMessage.innerHTML = `
                                    <div class="empty-cart-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h3>Your cart is empty</h3>
                                    <p>Looks like you haven't added any products to your cart yet.</p>
                                    <a href="products.php" class="btn btn-primary">Browse Products</a>
                                `;
                                
                                const cartItemsContainer = document.querySelector('.cart-items');
                                cartItemsContainer.innerHTML = '';
                                cartItemsContainer.appendChild(emptyCartMessage);
                                
                                // Hide the cart actions
                                const cartActions = document.querySelector('.cart-actions');
                                if (cartActions) {
                                    cartActions.style.display = 'none';
                                }
                            }
                        }, 300);
                    }, index * 100);
                });
                
                showNotification('Cart cleared successfully');
            }
        });
    }
}

// Initialize checkout functionality
function initCheckout() {
    const checkoutButton = document.querySelector('.btn-checkout');
    
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function(e) {
            // In a real application, you would validate cart items and redirect to checkout
            // For now, we'll just simulate the action
            
            e.preventDefault();
            
            checkoutButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            checkoutButton.disabled = true;
            
            setTimeout(() => {
                showNotification('Redirecting to checkout...', 'success');
                
                setTimeout(() => {
                    // Normally would redirect to checkout page
                    alert('This is a demo. In a real application, you would be redirected to the checkout page.');
                    
                    checkoutButton.innerHTML = '<i class="fas fa-credit-card"></i> Proceed to Checkout';
                    checkoutButton.disabled = false;
                }, 1500);
            }, 1000);
        });
    }
}

// Function to update cart count
function updateCartCount() {
    fetch('includes/cart_functions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_cart_count'
    })
    .then(response => response.text())
    .then(count => {
        document.querySelectorAll('.cart-count').forEach(element => {
            element.textContent = count;
        });
    })
    .catch(error => console.error('Error updating cart count:', error));
}

// Update cart count when page loads
document.addEventListener('DOMContentLoaded', updateCartCount);

// Update cart count every 30 seconds
setInterval(updateCartCount, 30000);

// Show notification
function showNotification(message, type = 'success') {
    // Check if notification container exists, create if not
    let notificationContainer = document.querySelector('.notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
        
        // Add CSS for notifications
        const style = document.createElement('style');
        style.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
            
            .notification {
                padding: 12px 20px;
                margin-bottom: 10px;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-md);
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 250px;
                transform: translateX(120%);
                transition: transform 0.3s ease;
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification.success {
                background-color: var(--success-color);
                color: white;
            }
            
            .notification.error {
                background-color: var(--error-color);
                color: white;
            }
            
            .notification.warning {
                background-color: #f0ad4e;
                color: white;
            }
            
            .notification-icon {
                font-size: 1.2rem;
            }
            
            .notification-message {
                flex: 1;
            }
            
            .notification-close {
                cursor: pointer;
                font-size: 0.8rem;
                opacity: 0.7;
                transition: opacity 0.2s ease;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Get icon based on type
    let icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    notification.innerHTML = `
        <div class="notification-icon"><i class="fas fa-${icon}"></i></div>
        <div class="notification-message">${message}</div>
        <div class="notification-close"><i class="fas fa-times"></i></div>
    `;
    
    notificationContainer.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Add close button functionality
    const closeButton = notification.querySelector('.notification-close');
    closeButton.addEventListener('click', function() {
        notification.classList.remove('show');
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
} 