// Script.js - Additional script functionality for wholesale e-commerce website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize trust badges animations if they exist
    initTrustBadgeAnimations();
    
    // Other page-specific initializations can go here
    
    console.log('script.js loaded successfully');
});

// Function to add subtle animations to trust badges
function initTrustBadgeAnimations() {
    const trustBadges = document.querySelectorAll('.trust-badge');
    
    if (trustBadges.length > 0) {
        // Add a slight delay to each badge for a staggered effect
        trustBadges.forEach((badge, index) => {
            setTimeout(() => {
                badge.classList.add('fade-in');
            }, index * 150);
        });
    }
}

// Add a scroll listener to animate elements when they come into view
window.addEventListener('scroll', function() {
    const animatableElements = document.querySelectorAll('.trust-badge, .security-certifications img');
    
    animatableElements.forEach(element => {
        const elementPosition = element.getBoundingClientRect();
        
        // If element is in viewport
        if (elementPosition.top < window.innerHeight && elementPosition.bottom >= 0) {
            element.classList.add('visible');
        }
    });
}); 