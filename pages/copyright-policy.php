<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Copyright Policy | Elocanto', 'copyright');
?>

<style>
    :root {
        --page-red: #dc2626;
        --page-black: #111827;
    }
    
    .legal-page-wrapper {
        padding-top: 90px; /* Offset for fixed header */
        background: #ffffff;
        min-height: 100vh;
    }
    
    .legal-banner {
        background: #f8fafc;
        padding: 80px 0;
        text-align: center;
        border-bottom: 1px solid #eee;
    }
    
    .legal-banner h1 {
        color: var(--page-red);
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 900;
        margin-bottom: 20px;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    
    .legal-banner .breadcrumbs {
        color: var(--page-black) !important;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .legal-banner .breadcrumbs a {
        color: var(--page-black) !important;
        text-decoration: none;
    }
    
    .legal-banner .breadcrumbs span {
        color: var(--page-red);
        opacity: 1;
    }
    
    .legal-content-section {
        padding: 80px 0;
    }
    
    .legal-card {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 24px;
        color: var(--page-black);
        line-height: 1.8;
    }
    
    .legal-card h2 {
        color: var(--page-red);
        font-size: 1.8rem;
        margin-top: 50px;
        margin-bottom: 25px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        border-left: 5px solid var(--page-red);
        padding-left: 20px;
    }
    
    .legal-card p {
        margin-bottom: 25px;
        font-size: 1.1rem;
        color: #374151;
    }

    @media (max-width: 768px) {
        .legal-banner {
            padding: 60px 0;
        }
        .legal-content-section {
            padding: 40px 0;
        }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>Copyright Policy</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">Copyright Policy</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="legal-card">
                <h2>Respect for Intellectual Property Rights</h2>
                <p>At Elocanto, we respect the intellectual property rights of others and expect all users of our platform to do the same. We are committed to maintaining a safe and lawful environment where original content is protected and unauthorized use of copyrighted material is strictly prohibited.</p>
                <p>All content published on Elocanto, including text, images, logos, design elements, and layout, is either owned by Elocanto or used with proper authorization. Any unauthorized copying, reproduction, or distribution of this content is not allowed.</p>

                <h2>User Responsibility for Uploaded Content</h2>
                <p>Elocanto is a user-generated content platform, which means all listings, including images, descriptions, and other materials, are created and uploaded by users.</p>
                <p>By posting content on Elocanto, you confirm that you own the rights to that content or have proper permission to use it. You also confirm that your content does not infringe on any third-party copyright, trademark, or intellectual property rights. Users are fully responsible for ensuring that their content is legal and authorized.</p>

                <h2>Reporting Copyright Infringement</h2>
                <p>If you believe that any content on Elocanto violates your copyright or intellectual property rights, you can report it to us immediately.</p>
                <p>When submitting a complaint, please include clear details such as the link to the content, a description of the copyrighted material, and proof of ownership if available. This helps us review your request quickly and accurately. We take all valid copyright complaints seriously and investigate them as soon as they are received.</p>

                <h2>Enforcement Actions and Content Removal</h2>
                <p>We are committed to protecting intellectual property rights on our platform. If any content is found to be infringing or violating copyright laws, we may take immediate action. This may include removing the content, restricting access, or suspending the user account responsible for repeated violations.</p>

                <h2>Platform Liability Disclaimer</h2>
                <p>Elocanto acts as a platform that allows users to post and view classified ads. We do not pre-verify all content uploaded by users. While we make efforts to address copyright complaints promptly, we cannot guarantee that all content on the platform is free from infringement. Users acknowledge that they use the platform at their own risk regarding content ownership and accuracy.</p>

                <h2>Contact for Copyright Concerns</h2>
                <p>If you need to report copyright infringement or have any related concerns, you can contact us directly:</p>
                <p><strong>Email:</strong> support@elocanto.pk</p>
                <p><strong>WhatsApp:</strong> +447490809237</p>
            </div>
        </div>
    </section>

    <section class="timeline-section">
        <div class="container-wide">
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 style="color: var(--page-red); font-size: 2.5rem; font-weight: 900; margin-bottom: 15px;">Our Journey</h2>
                <p style="color: #6b7280; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Building a trusted marketplace takes time and commitment. Here is how we've grown.</p>
            </div>

            <div class="timeline-container">
                <div class="timeline-line"></div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Foundation</span>
                            <h3>The Vision Begins</h3>
                            <p>Elocanto was founded with a simple goal: to make classified ads simple, accessible, and reliable for everyone across Pakistan.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Growth</span>
                            <h3>Expanding Community</h3>
                            <p>We successfully connected thousands of buyers and sellers, creating a vibrant digital ecosystem for trade and services.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Safety</span>
                            <h3>Trust & Security</h3>
                            <p>Implemented advanced anti-scam policies and reporting tools to ensure a safe environment for all our users.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Future</span>
                            <h3>Constant Innovation</h3>
                            <p>We continue to evolve, adding new features and improving performance to remain Pakistan's favorite classifieds platform.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
