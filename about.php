<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

renderHeader('About Us | Adaax Premium', 'about');
?>

<main>
    <!-- About Us Banner -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3rem; font-weight: 900;">About Us</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); margin-top: 10px;">
                <a href="index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>About Us</span>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="section-padding">
        <div class="container-wide">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
                <div>
                    <h2 style="font-size: 2.5rem; margin-bottom: 20px; color: var(--white);">Our Story</h2>
                    <p style="color: var(--text-muted); line-height: 1.8; font-size: 1.1rem; margin-bottom: 20px;">
                        Founded with a vision to revolutionize the classifieds industry, ADAAX has grown into Pakistan's most trusted premium marketplace. Our journey began with a simple goal: to create a safe, reliable, and verified platform for high-end services.
                    </p>
                    <p style="color: var(--text-muted); line-height: 1.8; font-size: 1.1rem;">
                        Today, we are proud to serve a community of thousands, maintaining the highest standards of verification and security. Our commitment to quality continues to drive every decision we make.
                    </p>
                </div>
                <div style="border-radius: 24px; overflow: hidden; border: 1px solid var(--glass-border);">
                    <img src="images/hero_model.png" alt="Our Story" style="width: 100%; height: auto; filter: grayscale(0.2);">
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>