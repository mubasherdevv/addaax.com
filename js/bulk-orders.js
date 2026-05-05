document.addEventListener('DOMContentLoaded', function() {
    // Initialize product entries
    initProductEntries();
    
    // Initialize FAQ functionality
    initFAQAccordion();
    
    // Initialize form validation
    initFormValidation();
});

/**
 * Initialize product entries functionality
 */
function initProductEntries() {
    const productEntryContainer = document.querySelector('.product-entry-container');
    const addProductButton = document.querySelector('.btn-add-product');
    
    if (!productEntryContainer || !addProductButton) return;
    
    // Add event listener to add product button
    addProductButton.addEventListener('click', function() {
        addProductEntry(productEntryContainer);
    });
    
    // Add first product entry if container is empty
    if (productEntryContainer.querySelectorAll('.product-entry').length === 0) {
        addProductEntry(productEntryContainer);
    }
    
    // Add event delegation for removing product entries
    productEntryContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-product') || e.target.closest('.btn-remove-product')) {
            const entry = e.target.closest('.product-entry');
            if (entry) {
                // Don't remove if it's the last one
                const entries = productEntryContainer.querySelectorAll('.product-entry');
                if (entries.length > 1) {
                    entry.remove();
                } else {
                    // Clear fields instead of removing
                    clearProductEntryFields(entry);
                    showNotification('Cannot remove the last product entry', 'error');
                }
            }
        }
    });
    
    // Add event delegation for product search
    productEntryContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('product-search')) {
            const entry = e.target.closest('.product-entry');
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length > 2) {
                // Simulate product search (would be replaced with actual API call)
                simulateProductSearch(searchTerm, entry);
            }
        }
    });
}

/**
 * Add a new product entry to the container
 */
function addProductEntry(container) {
    const newEntry = document.createElement('div');
    newEntry.className = 'product-entry';
    newEntry.innerHTML = `
        <div class="form-row">
            <div class="form-group">
                <label for="product-search-${Date.now()}">Product Name or SKU</label>
                <input type="text" id="product-search-${Date.now()}" class="product-search" placeholder="Start typing to search products...">
                <div class="search-results"></div>
            </div>
            <div class="form-group">
                <label for="product-quantity-${Date.now()}">Quantity</label>
                <input type="number" id="product-quantity-${Date.now()}" class="product-quantity" min="1" value="1">
            </div>
        </div>
        <div class="product-details" style="display: none;">
            <div class="form-row">
                <div class="form-group">
                    <label for="product-price-${Date.now()}">Unit Price ($)</label>
                    <input type="text" id="product-price-${Date.now()}" class="product-price" readonly>
                </div>
                <div class="form-group">
                    <label for="product-discount-${Date.now()}">Volume Discount (%)</label>
                    <input type="text" id="product-discount-${Date.now()}" class="product-discount" readonly>
                </div>
                <div class="form-group">
                    <label for="product-total-${Date.now()}">Estimated Total ($)</label>
                    <input type="text" id="product-total-${Date.now()}" class="product-total" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="product-notes-${Date.now()}">Special Requirements</label>
                <textarea id="product-notes-${Date.now()}" class="product-notes" rows="2" placeholder="Any special requirements for this product..."></textarea>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline btn-remove-product">
            <i class="fas fa-trash-alt"></i> Remove Product
        </button>
    `;
    
    container.insertBefore(newEntry, document.querySelector('.btn-add-product'));
}

/**
 * Clear all fields in a product entry
 */
function clearProductEntryFields(entry) {
    entry.querySelector('.product-search').value = '';
    entry.querySelector('.product-quantity').value = '1';
    
    // Hide product details
    const productDetails = entry.querySelector('.product-details');
    if (productDetails) {
        productDetails.style.display = 'none';
    }
    
    // Clear other fields
    const priceInput = entry.querySelector('.product-price');
    if (priceInput) priceInput.value = '';
    
    const discountInput = entry.querySelector('.product-discount');
    if (discountInput) discountInput.value = '';
    
    const totalInput = entry.querySelector('.product-total');
    if (totalInput) totalInput.value = '';
    
    const notesInput = entry.querySelector('.product-notes');
    if (notesInput) notesInput.value = '';
}

/**
 * Simulate product search (would be replaced with actual API call)
 */
function simulateProductSearch(searchTerm, entry) {
    // Mock products for search
    const mockProducts = [
        { id: 1, name: 'Laptop Pro X1', sku: 'LPX1-2023', price: 899.99, category: 'Electronics' },
        { id: 2, name: 'Laptop Pro X2', sku: 'LPX2-2023', price: 1199.99, category: 'Electronics' },
        { id: 3, name: 'Desktop Pro 4K', sku: 'DP4K-2023', price: 1499.99, category: 'Electronics' },
        { id: 4, name: 'Smart TV 55"', sku: 'STV55-2023', price: 699.99, category: 'Electronics' },
        { id: 5, name: 'Office Chair Ergonomic', sku: 'OCE-2023', price: 199.99, category: 'Office' },
        { id: 6, name: 'Office Desk L-Shape', sku: 'ODL-2023', price: 299.99, category: 'Office' },
        { id: 7, name: 'T-Shirt Bundle Pack (50)', sku: 'TSBP50-2023', price: 399.99, category: 'Apparel' },
        { id: 8, name: 'Jeans Premium (Box of 20)', sku: 'JPB20-2023', price: 599.99, category: 'Apparel' },
    ];
    
    // Filter products based on search term
    const filteredProducts = mockProducts.filter(product => {
        return product.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
               product.sku.toLowerCase().includes(searchTerm.toLowerCase());
    }).slice(0, 5); // Limit to 5 results
    
    // Display search results
    const searchResults = entry.querySelector('.search-results');
    searchResults.innerHTML = '';
    
    if (filteredProducts.length > 0) {
        searchResults.style.display = 'block';
        filteredProducts.forEach(product => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.innerHTML = `
                <div class="product-name">${product.name}</div>
                <div class="product-sku">SKU: ${product.sku}</div>
            `;
            resultItem.addEventListener('click', function() {
                selectProduct(product, entry);
                searchResults.style.display = 'none';
            });
            searchResults.appendChild(resultItem);
        });
    } else {
        searchResults.innerHTML = '<div class="no-results">No products found</div>';
        searchResults.style.display = 'block';
    }
}

/**
 * Select a product and populate the entry fields
 */
function selectProduct(product, entry) {
    // Populate search field with product name
    entry.querySelector('.product-search').value = product.name;
    
    // Show product details
    const productDetails = entry.querySelector('.product-details');
    if (productDetails) {
        productDetails.style.display = 'block';
    }
    
    // Populate price field
    const priceInput = entry.querySelector('.product-price');
    if (priceInput) priceInput.value = product.price.toFixed(2);
    
    // Get quantity
    const quantityInput = entry.querySelector('.product-quantity');
    const quantity = parseInt(quantityInput.value, 10) || 1;
    
    // Calculate volume discount based on quantity
    const discount = calculateVolumeDiscount(quantity);
    const discountInput = entry.querySelector('.product-discount');
    if (discountInput) discountInput.value = discount.toFixed(2);
    
    // Calculate total
    updateProductTotal(entry);
    
    // Add event listener to quantity for recalculating
    quantityInput.addEventListener('input', function() {
        const newQuantity = parseInt(this.value, 10) || 1;
        const newDiscount = calculateVolumeDiscount(newQuantity);
        
        const discInput = entry.querySelector('.product-discount');
        if (discInput) discInput.value = newDiscount.toFixed(2);
        
        updateProductTotal(entry);
    });
}

/**
 * Calculate volume discount based on quantity
 */
function calculateVolumeDiscount(quantity) {
    if (quantity >= 1000) {
        return 15.0;
    } else if (quantity >= 500) {
        return 12.5;
    } else if (quantity >= 250) {
        return 10.0;
    } else if (quantity >= 100) {
        return 7.5;
    } else if (quantity >= 50) {
        return 5.0;
    } else if (quantity >= 25) {
        return 2.5;
    } else {
        return 0.0;
    }
}

/**
 * Update product total based on price, quantity, and discount
 */
function updateProductTotal(entry) {
    const priceInput = entry.querySelector('.product-price');
    const quantityInput = entry.querySelector('.product-quantity');
    const discountInput = entry.querySelector('.product-discount');
    const totalInput = entry.querySelector('.product-total');
    
    if (!priceInput || !quantityInput || !discountInput || !totalInput) return;
    
    const price = parseFloat(priceInput.value) || 0;
    const quantity = parseInt(quantityInput.value, 10) || 1;
    const discount = parseFloat(discountInput.value) || 0;
    
    const discountAmount = (price * quantity) * (discount / 100);
    const total = (price * quantity) - discountAmount;
    
    totalInput.value = total.toFixed(2);
}

/**
 * Initialize FAQ accordion functionality
 */
function initFAQAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Toggle active class
            const isActive = item.classList.contains('active');
            
            // Close all FAQ items
            faqItems.forEach(faq => {
                faq.classList.remove('active');
            });
            
            // If it wasn't active, make it active
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const bulkOrderForm = document.getElementById('bulk-order-form');
    
    if (!bulkOrderForm) return;
    
    bulkOrderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form fields
        let isValid = true;
        const requiredFields = bulkOrderForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
                
                // Show error message if it doesn't exist
                let errorMessage = field.parentElement.querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'This field is required';
                    field.parentElement.appendChild(errorMessage);
                }
            } else {
                field.classList.remove('error');
                
                // Remove error message if it exists
                const errorMessage = field.parentElement.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
            }
        });
        
        // Validate email format
        const emailInput = bulkOrderForm.querySelector('input[type="email"]');
        if (emailInput && emailInput.value.trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value.trim())) {
                isValid = false;
                emailInput.classList.add('error');
                
                // Show error message if it doesn't exist
                let errorMessage = emailInput.parentElement.querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'Please enter a valid email address';
                    emailInput.parentElement.appendChild(errorMessage);
                } else {
                    errorMessage.textContent = 'Please enter a valid email address';
                }
            }
        }
        
        if (isValid) {
            // Collect form data
            const formData = collectFormData(bulkOrderForm);
            
            // Submit form (would be replaced with actual AJAX call)
            simulateFormSubmission(formData);
        } else {
            // Scroll to first error
            const firstError = bulkOrderForm.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Reset validation on input
    bulkOrderForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('error')) {
            e.target.classList.remove('error');
            
            // Remove error message if it exists
            const errorMessage = e.target.parentElement.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }
    });
}

/**
 * Collect form data from the form
 */
function collectFormData(form) {
    const formData = {
        company: form.querySelector('[name="company"]').value,
        contactName: form.querySelector('[name="contact-name"]').value,
        email: form.querySelector('[name="email"]').value,
        phone: form.querySelector('[name="phone"]').value,
        address: form.querySelector('[name="address"]').value,
        city: form.querySelector('[name="city"]').value,
        state: form.querySelector('[name="state"]').value,
        zip: form.querySelector('[name="zip"]').value,
        country: form.querySelector('[name="country"]').value,
        preferredDelivery: form.querySelector('[name="preferred-delivery"]').value,
        additionalRequirements: form.querySelector('[name="additional-requirements"]').value,
        products: []
    };
    
    // Collect products
    const productEntries = form.querySelectorAll('.product-entry');
    productEntries.forEach(entry => {
        const productName = entry.querySelector('.product-search').value;
        if (productName.trim()) {
            formData.products.push({
                name: productName,
                quantity: entry.querySelector('.product-quantity').value,
                price: entry.querySelector('.product-price').value,
                discount: entry.querySelector('.product-discount').value,
                total: entry.querySelector('.product-total').value,
                notes: entry.querySelector('.product-notes').value
            });
        }
    });
    
    return formData;
}

/**
 * Simulate form submission (would be replaced with actual AJAX call)
 */
function simulateFormSubmission(formData) {
    // Show loading state
    const submitButton = document.querySelector('.btn-submit-quote');
    const originalText = submitButton.textContent;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    // Simulate server delay
    setTimeout(function() {
        // Reset button
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        // Show success notification
        showNotification('Your bulk order request has been submitted successfully! Our team will contact you soon with a personalized quote.', 'success');
        
        // Reset form
        document.getElementById('bulk-order-form').reset();
        
        // Reset product entries
        const productEntryContainer = document.querySelector('.product-entry-container');
        if (productEntryContainer) {
            productEntryContainer.innerHTML = '';
            initProductEntries();
        }
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 2000);
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.querySelector('.notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification';
        document.body.appendChild(notification);
    }
    
    // Set notification content and type
    notification.textContent = message;
    notification.className = `notification ${type}`;
    
    // Show notification
    notification.classList.add('show');
    
    // Hide notification after delay
    setTimeout(function() {
        notification.classList.remove('show');
    }, 5000);
} 