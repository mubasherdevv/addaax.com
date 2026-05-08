<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Copyright Policy | Addaax', 'copyright');
?>

<style>
    :root {
        --page-red: #dc2626;
        --page-black: #111827;
    }
    
    .legal-page-wrapper {
        padding-top: 90px;
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
    
    .timeline-wrapper {
        position: relative;
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 0;
    }

    .timeline-wrapper::before {
        content: '';
        position: absolute;
        left: 31px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f1f5f9;
    }

    .timeline-item {
        position: relative;
        padding-left: 80px;
        margin-bottom: 50px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        width: 24px;
        height: 24px;
        background: white;
        border: 4px solid var(--page-red);
        border-radius: 50%;
        z-index: 2;
    }

    .timeline-content {
        background: #ffffff;
        padding: 30px;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateX(10px);
        border-color: #fee2e2;
        box-shadow: 0 15px 40px rgba(220,38,38,0.05);
    }

    .timeline-content h2 {
        color: var(--page-red);
        font-size: 1.5rem;
        margin-bottom: 15px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .timeline-content p {
        margin-bottom: 15px;
        font-size: 1.1rem;
        color: #374151;
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .timeline-wrapper::before { left: 16px; }
        .timeline-item { padding-left: 45px; }
        .timeline-item::before { left: 5px; width: 20px; height: 20px; }
        .timeline-content { padding: 20px; }
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
            <div class="timeline-wrapper">
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Respect for Intellectual Property Rights</h2>
                        <p>At Addaax, we respect the intellectual property rights of others and expect all users of our platform to do the same. We are committed to maintaining a safe and lawful environment where original content is protected and unauthorized use of copyrighted material is strictly prohibited.</p>
                        <p>All content published on Addaax, including text, images, logos, design elements, and layout, is either owned by Addaax or used with proper authorization. Any unauthorized copying, reproduction, or distribution of this content is not allowed.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>User Responsibility for Uploaded Content</h2>
                        <p>Addaax is a user-generated content platform, which means all listings, including images, descriptions, and other materials, are created and uploaded by users.</p>
                        <p>By posting content on Addaax, you confirm that you own the rights to that content or have proper permission to use it. You also confirm that your content does not infringe on any third-party copyright, trademark, or intellectual property rights. Users are fully responsible for ensuring that their content is legal and authorized.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Reporting Copyright Infringement</h2>
                        <p>If you believe that any content on Addaax violates your copyright or intellectual property rights, you can report it to us immediately.</p>
                        <p>When submitting a complaint, please include clear details such as the link to the content, a description of the copyrighted material, and proof of ownership if available. This helps us review your request quickly and accurately. We take all valid copyright complaints seriously and investigate them as soon as they are received.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Enforcement Actions and Content Removal</h2>
                        <p>We are committed to protecting intellectual property rights on our platform. If any content is found to be infringing or violating copyright laws, we may take immediate action. This may include removing the content, restricting access, or suspending the user account responsible for repeated violations.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Platform Liability Disclaimer</h2>
                        <p>Addaax acts as a platform that allows users to post and view classified ads. We do not pre-verify all content uploaded by users. While we make efforts to address copyright complaints promptly, we cannot guarantee that all content on the platform is free from infringement. Users acknowledge that they use the platform at their own risk regarding content ownership and accuracy.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Contact for Copyright Concerns</h2>
                        <p>If you need to report copyright infringement or have any related concerns, you can contact us directly:</p>
                        <p><strong>Email:</strong> contactadmin@addaax.com</p>
                        <p><strong>WhatsApp:</strong> +447490809237</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
