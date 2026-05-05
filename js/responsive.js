// Immediate execution to force mobile menu visibility
(function() {
    // Create and inject critical styles
    const criticalStyles = `
                        .mobile-menu-toggle {
            position: absolute !important;
            display: flex !important;
            top: 1.5rem !important;
            right: 1rem !important;
            z-index: 9999 !important;
            opacity: 1 !important;
            visibility: visible !important;
            background-color: rgba(255,255,255,0.9) !important;
            border-radius: 4px !important;
            padding: 8px !important;
            border: 1px solid #ccc !important;
        }
        .mobile-menu-toggle i,
        .mobile-menu-toggle .menu-text {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            color: #333 !important;
        }
    `;
    
    const styleTag = document.createElement('style');
    styleTag.innerHTML = criticalStyles;
    document.head.appendChild(styleTag);
    
    // Force apply styles to any existing toggle buttons
    setTimeout(() => {
        const toggleButton = document.querySelector('.mobile-menu-toggle');
        if (toggleButton) {
            toggleButton.style.cssText = "position: absolute !important; display: flex !important; top: 1.5rem !important; right: 1rem !important; z-index: 9999 !important; opacity: 1 !important; visibility: visible !important; background-color: rgba(255,255,255,0.9) !important; border-radius: 4px !important; padding: 8px !important; border: 1px solid #ccc !important;";
            
            const icon = toggleButton.querySelector('i');
            if (icon) icon.style.cssText = "display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #333 !important;";
            
            const text = toggleButton.querySelector('.menu-text');
            if (text) text.style.cssText = "display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #333 !important;";
        }
    }, 0);
    
    // Fix for automatic scrolling issue
    let isUserScrolling = false;
    let scrollTimeout;
    
    // Function to mark when user is actively scrolling
    function handleUserScroll() {
        isUserScrolling = true;
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isUserScrolling = false;
        }, 200);
    }
    
    // Add event listeners for touch and mouse scroll events
    window.addEventListener('wheel', handleUserScroll, { passive: true });
    window.addEventListener('touchmove', handleUserScroll, { passive: true });
    
    // Block automatic scroll events that aren't user-initiated
    window.addEventListener('scroll', function(e) {
        if (!isUserScrolling) {
            // This is an automatic scroll, try to prevent it
            window.scrollTo(0, window.scrollY);
        }
    }, { passive: false });
})();

// Responsive JavaScript Functions
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the dashboard page
    const isDashboard = window.location.pathname.includes('dashboard.php');
    
    if (isDashboard) {
        setupDashboardSidebar();
    }
    
    makeTablesResponsive();
    setupStickyHeader();
    initResponsiveFeatures();
});

function setupDashboardSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (!sidebar || !sidebarToggle || !sidebarOverlay) return;
    
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Setup event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
}

function makeTablesResponsive() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
}

function setupStickyHeader() {
    const header = document.querySelector('header');
    if (!header) return;
    
    let lastScrollTop = 0;
    let scrollTimer;
    
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add scrolled class when scrolled
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide header when scrolling down, show when scrolling up
        if (currentScroll > lastScrollTop && currentScroll > 100) {
            header.classList.add('header-hidden');
        } else {
            header.classList.remove('header-hidden');
        }
        
        lastScrollTop = currentScroll;
        
        // Show header after scroll stops
        scrollTimer = setTimeout(() => header.classList.remove('header-hidden'), 1000);
    });
}

function initResponsiveFeatures() {
    // Handle responsive images and sliders
    const sliders = document.querySelectorAll('.testimonials-slider, .products-slider');
    
    function updateSliders() {
        sliders.forEach(slider => {
            slider.classList.toggle('scrollable', window.innerWidth < 768);
        });
    }
    
    // Initial update
    updateSliders();
    
    // Update on resize
    window.addEventListener('resize', debounce(updateSliders, 250));
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 