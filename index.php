<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// SEO Meta
$PAGE_TITLE = "Simple, Reliable, Verified | Find Escorts in Your City";
$META_DESC = "The premium adult directory platform. Find verified escorts in Mumbai, Delhi, Bangalore, and across India. Simple, reliable, and secure.";

renderHeader($PAGE_TITLE, 'home');
?>

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
                    <img src="images/hero_model.png" alt="Premium Escort Directory">
                </div>
            </div>
        </section>

        <!-- Featured Locations -->
        <section class="section-padding">
            <div class="container-wide">
                <h2 class="section-title">Featured Locations</h2>
                
                <div class="location-grid">
                    <a href="/escorts/lahore" class="location-card">
                        <img src="images/city_mumbai.png" alt="Lahore">
                        <div class="overlay">
                            <h3>Lahore</h3>
                        </div>
                    </a>
                    <a href="/escorts/delhi" class="location-card">
                        <img src="images/city_delhi.png" alt="Delhi">
                        <div class="overlay">
                            <h3>Delhi</h3>
                        </div>
                    </a>
                    <a href="/escorts/bangalore" class="location-card">
                        <img src="images/city_bangalore.png" alt="Bangalore">
                        <div class="overlay">
                            <h3>Bangalore</h3>
                        </div>
                    </a>
                    <a href="/escorts/hyderabad" class="location-card">
                        <img src="images/city_mumbai.png" alt="Hyderabad" style="filter: hue-rotate(45deg);">
                        <div class="overlay">
                            <h3>Hyderabad</h3>
                        </div>
                    </a>
                    <a href="/escorts/chennai" class="location-card">
                        <img src="images/city_delhi.png" alt="Chennai" style="filter: hue-rotate(-45deg);">
                        <div class="overlay">
                            <h3>Chennai</h3>
                        </div>
                    </a>
                    <a href="/escorts/pune" class="location-card">
                        <img src="images/city_bangalore.png" alt="Pune" style="filter: brightness(0.8);">
                        <div class="overlay">
                            <h3>Pune</h3>
                        </div>
                    </a>
                </div>
                
                <a href="cities.php" class="see-more">See more cities <i class="fas fa-chevron-right" style="font-size: 12px; margin-left: 4px;"></i></a>
            </div>
        </section>

        <!-- Top Searches -->
        <section class="searches-bg">
            <div class="container-wide">
                <h2 class="section-title">Top Searches in India</h2>
                
                <div class="search-tags">
                    <a href="products.php?q=Shemale+escort+in+India" class="tag-btn">Shemale escort in India</a>
                    <a href="products.php?q=Erotic+massage+in+India" class="tag-btn">Erotic massage in India</a>
                    <a href="products.php?q=Bhabhi+sex+girl+in+India" class="tag-btn">Bhabhi sex girl in India</a>
                    <a href="products.php?q=Cash+payment+call+girl+in+India" class="tag-btn">Cash payment call girl in India</a>
                    <a href="products.php?q=Tamil+lady+sex+in+India" class="tag-btn">Tamil lady sex in India</a>
                    <a href="products.php?q=Whatsapp+sex+chat+in+India" class="tag-btn">Whatsapp sex chat in India</a>
                </div>
            </div>
        </section>
    </main>

<?php
renderFooter();
?>