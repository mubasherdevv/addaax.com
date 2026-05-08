<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('About Us | Elocanto', 'about');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">About Elocanto</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); font-size: 1.1rem;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>About Us</span>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide" style="max-width: 900px; margin: 0 auto;">
            <div class="content-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 32px; padding: 60px; backdrop-filter: blur(10px);">
                <div class="legal-content" style="color: var(--text-muted); line-height: 1.8; font-size: 1.1rem;">
                    <h2 style="color: var(--white); font-size: 2.2rem; margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Our Vision: Connecting People Through Digital Classifieds</h2>
                    <p style="margin-bottom: 25px;">
                        <strong>Elocanto</strong> is a modern online classified ads platform designed to connect people across Pakistan through simple, fast, and reliable listings. Our goal is to make buying, selling, and offering services easier for everyone by providing a digital space where real people can interact directly.
                    </p>
                    <p style="margin-bottom: 25px;">
                        We believe that opportunities should be accessible to everyone, whether you are selling a product, searching for a job, renting a property, or offering a service. Elocanto is built to bring these opportunities together in one place.
                    </p>

                    <h3 style="color: var(--accent-gold); font-size: 1.8rem; margin-top: 50px; margin-bottom: 20px;">Platform Overview: What Elocanto Offers</h3>
                    <p style="margin-bottom: 25px;">
                        Elocanto allows users to post and explore a wide range of classified ads across multiple categories. From jobs and real estate to vehicles, services, and personal items, our platform is designed to support everyday needs and business opportunities.
                    </p>
                    <p style="margin-bottom: 25px;">
                        We provide a simple and user-friendly system where users can create listings, reach potential buyers, and communicate directly without unnecessary complexity.
                    </p>

                    <h3 style="color: var(--accent-gold); font-size: 1.8rem; margin-top: 50px; margin-bottom: 20px;">How Elocanto Works: A User-Driven Marketplace</h3>
                    <p style="margin-bottom: 25px;">
                        Elocanto is a user-generated platform, which means all ads are created and managed by individual users. We do not sell products or services directly. Instead, we provide the platform where users can connect, communicate, and complete transactions independently.
                    </p>

                    <h3 style="color: var(--accent-gold); font-size: 1.8rem; margin-top: 50px; margin-bottom: 20px;">Our Commitment to Platform Quality and Safety</h3>
                    <p style="margin-bottom: 25px;">
                        Our priority is to create a safe, easy, and reliable experience for all users. We continuously work to improve platform performance, reduce fake listings, and maintain a clean and trustworthy environment.
                    </p>

                    <div style="margin-top: 60px; padding: 40px; background: rgba(212, 175, 55, 0.05); border-radius: 20px; border-left: 4px solid var(--accent-gold);">
                        <h4 style="color: var(--white); margin-bottom: 15px;">Contact Us and User Support</h4>
                        <p style="margin-bottom: 10px;">We are always open to feedback, suggestions, and support requests.</p>
                        <p><strong>Email:</strong> support@elocanto.pk</p>
                        <p><strong>WhatsApp:</strong> +447490809237</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>
