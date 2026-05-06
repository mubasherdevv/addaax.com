<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// SEO Meta
$PAGE_TITLE = "Simple, Reliable, Verified | Find Escorts in Your City";
$META_DESC = "The premium adult directory platform. Find verified escorts in Mumbai, Delhi, Bangalore, and across India. Simple, reliable, and secure.";

renderHeader($PAGE_TITLE, 'home');
?>

<!-- WebSite Search Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "url": "https://addaax.com/",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://addaax.com/products.php?search={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container-wide hero-grid">
                <div class="hero-content">
                    <h1>Simple, Reliable, Verified</h1>
                    <p class="subtext">Find <span>escorts</span> in your city</p>
                    
                    <form action="products.php" method="GET" class="search-container desktop-only">
                        <div class="search-input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="city" placeholder="Search by city...">
                        </div>
                        <button type="submit" class="search-btn">SEARCH</button>
                    </form>

                    <!-- Mobile Hero Search -->
                    <form action="products.php" method="GET" class="mobile-hero-search">
                        <input type="text" name="city" placeholder="Search by city...">
                        <button type="submit">SEARCH</button>
                    </form>
                </div>
                
                <div class="hero-image-wrap">
                    <img src="images/hero_model.png" alt="Premium Escort Directory" width="600" height="400" fetchpriority="high">
                </div>
            </div>
        </section>

        <!-- Featured Locations -->
        <section class="section-padding">
            <div class="container-wide">
                <h2 class="section-title">Featured Locations</h2>
                
                <div class="location-grid">
                    <a href="/escorts/lahore" class="location-card">
                        <img src="images/cities/lahore.webp" alt="Lahore" width="400" height="250" loading="lazy">
                        <div class="overlay">
                            <h3>Lahore</h3>
                        </div>
                    </a>
                    <a href="/escorts/karachi" class="location-card">
                        <img src="images/cities/karachi.webp" alt="Karachi" width="400" height="250" loading="lazy">
                        <div class="overlay">
                            <h3>Karachi</h3>
                        </div>
                    </a>
                    <a href="/escorts/multan" class="location-card">
                        <img src="images/cities/multan.webp" alt="Multan" width="400" height="250" loading="lazy">
                        <div class="overlay">
                            <h3>Multan</h3>
                        </div>
                    </a>
                    <a href="/escorts/islamabad" class="location-card">
                        <img src="images/cities/islamabad.webp" alt="Islamabad" width="400" height="250" loading="lazy">
                        <div class="overlay">   
                            <h3>Islamabad</h3>
                        </div>
                    </a>
                    <a href="/escorts/rawalpindi" class="location-card">
                        <img src="images/cities/rawalpindi.webp" alt="Rawalpindi" width="400" height="250" loading="lazy">
                        <div class="overlay">
                            <h3>Rawalpindi</h3>
                        </div>
                    </a>
                    <a href="/escorts/murree" class="location-card">
                        <img src="images/cities/murree.webp" alt="Murree" width="400" height="250" loading="lazy">
                        <div class="overlay">
                            <h3>Murree</h3>
                        </div>
                    </a>
                </div>
                
                <a href="cities.php" class="see-more">See more cities <i class="fas fa-chevron-right" style="font-size: 12px; margin-left: 4px;"></i></a>
            </div>
        </section>

       
    </main>

<?php
renderFooter();
?>