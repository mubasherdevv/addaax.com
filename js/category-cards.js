document.addEventListener('DOMContentLoaded', function() {
    // Get all category cards
    const categoryCards = document.querySelectorAll('.category-card-link');
    
    // Add click event listener to each card
    categoryCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // If the click is on the explore button, let it handle the click
            if (e.target.closest('.explore-btn')) {
                return;
            }
            
            // Otherwise, navigate to the category page
            window.location.href = this.href;
        });
    });
}); 