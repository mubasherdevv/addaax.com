// Categories Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Animate category items on scroll
    function animateOnScroll() {
        const categoryCards = document.querySelectorAll('.category-card');
        const categoryItems = document.querySelectorAll('.subcategory-item');
        const allElements = [...categoryCards, ...categoryItems];
        
        allElements.forEach(element => {
            if (isElementInViewport(element) && !element.classList.contains('animated')) {
                element.classList.add('animated');
                element.style.animation = 'fadeInUp 0.6s ease forwards';
            }
        });
    }
    
    // Check if element is in viewport
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom >= 0
        );
    }
    
    // Add scroll listener
    window.addEventListener('scroll', animateOnScroll);
    
    // Initial check on page load
    animateOnScroll();
    
    // Add CSS for animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .category-card, .subcategory-item {
            opacity: 0;
        }
        
        .animated {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
    
    // Handle category link clicks to set URL parameters
    const categoryLinks = document.querySelectorAll('a[href^="products.php?category="]');
    
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Store the category in sessionStorage for the products page
            const href = this.getAttribute('href');
            const params = new URLSearchParams(href.split('?')[1]);
            
            if (params.has('category')) {
                const category = params.get('category');
                sessionStorage.setItem('selectedCategory', category);
                
                if (params.has('subcategory')) {
                    const subcategory = params.get('subcategory');
                    sessionStorage.setItem('selectedSubcategory', subcategory);
                } else {
                    sessionStorage.removeItem('selectedSubcategory');
                }
            }
        });
    });
}); 