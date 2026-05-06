<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// Set 404 Header
http_response_code(404);

$PAGE_TITLE = "404 Page Not Found | ADDAAX";
renderHeader($PAGE_TITLE, '404');
?>

<main class="error-page" style="padding: 150px 20px; text-align: center; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="container-wide">
        <div class="error-content" style="max-width: 600px; margin: 0 auto;">
            <h1 style="font-size: clamp(80px, 15vw, 150px); font-weight: 900; margin-bottom: 0; line-height: 1; background: linear-gradient(to bottom, #C9A84C, #25153F); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">404</h1>
            <h2 style="font-size: 32px; color: var(--white); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px;">Lost in the Clouds?</h2>
            <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 40px; line-height: 1.6;">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="/index.php" class="btn btn-primary" style="padding: 15px 40px; border-radius: 50px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Back to Home</a>
                <a href="/products.php" class="btn btn-secondary" style="padding: 15px 40px; border-radius: 50px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--white);">Explore Ads</a>
            </div>
        </div>
    </div>
</main>

<style>
.error-page {
    background: radial-gradient(circle at center, rgba(37, 21, 63, 0.2) 0%, transparent 70%);
}
</style>

<?php
renderFooter();
?>
