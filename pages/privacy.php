<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Privacy Policy | Elocanto', 'privacy');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">Privacy Policy</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); font-size: 1.1rem;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Privacy Policy</span>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide" style="max-width: 900px; margin: 0 auto;">
            <div class="content-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 32px; padding: 60px; backdrop-filter: blur(10px);">
                <div class="legal-content" style="color: var(--text-muted); line-height: 1.8; font-size: 1.05rem;">
                    <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 25px;">Privacy Policy Overview</h2>
                    <p style="margin-bottom: 20px;">Welcome to Elocanto. Your privacy is very important to us. This Privacy Policy explains how we collect, use, and protect your information when you use our website. By using Elocanto, you agree to the practices described in this policy.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Information We Collect</h3>
                    <p style="margin-bottom: 20px;">When you use Elocanto, we may collect different types of information to provide and improve our services. This includes information you directly provide, such as your name, email address, phone number, and details you include in your ads.</p>
                    <p style="margin-bottom: 20px;">We also collect basic technical data automatically, such as IP address, browser type, device information, and pages visited.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">How We Use Your Information</h3>
                    <p style="margin-bottom: 20px;">We use collected information to operate, maintain, and improve the Elocanto platform. This includes enabling ad posting, account management, user communication, customer support, and fraud prevention.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Public Content</h3>
                    <p style="margin-bottom: 20px;">Elocanto is a user-generated content platform. Any information you publish in ads, including images and descriptions, may be visible to other users. We strongly recommend avoiding the sharing of sensitive personal or financial details in public listings.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Cookies and Tracking</h3>
                    <p style="margin-bottom: 20px;">We use cookies and similar technologies to improve user experience, analyze traffic, and enhance platform functionality. You may disable cookies through your browser settings, but some features of the platform may not work properly.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Data Sharing</h3>
                    <p style="margin-bottom: 20px;">We do not sell or rent your personal data to third parties. We may share limited information with trusted service providers who help us operate the platform, or if required by law.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Data Security</h3>
                    <p style="margin-bottom: 20px;">We take reasonable security measures to protect your personal data from unauthorized access, misuse, or loss. However, no online system is completely secure.</p>

                    <div style="margin-top: 50px; padding: 40px; background: rgba(212, 175, 55, 0.05); border-radius: 20px; border-left: 4px solid var(--accent-gold);">
                        <h4 style="color: var(--white); margin-bottom: 15px;">Questions About Your Privacy?</h4>
                        <p style="margin-bottom: 10px;">If you have any questions or requests regarding this Privacy Policy, please contact us:</p>
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
