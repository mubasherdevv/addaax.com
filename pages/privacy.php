<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Privacy Policy | Elocanto', 'privacy');
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
            <h1>Privacy Policy</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">Privacy Policy</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="timeline-wrapper">
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Overview and Commitment to Privacy</h2>
                        <p>Welcome to Elocanto. Your privacy is very important to us. This Privacy Policy explains how we collect, use, and protect your information when you use our website.</p>
                        <p>By using Elocanto, you agree to the practices described in this policy. If you do not agree, please stop using the platform.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Information We Collect from Users</h2>
                        <p>When you use Elocanto, we may collect different types of information to provide and improve our services.</p>
                        <p>This includes information you directly provide, such as your name, email address, phone number, and details you include in your ads when creating an account or posting listings.</p>
                        <p>We also collect basic technical data automatically, such as IP address, browser type, device information, and pages visited, to help us improve platform performance and security.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>How We Use Your Information</h2>
                        <p>We use collected information to operate, maintain, and improve the Elocanto platform. This includes enabling ad posting, account management, user communication, customer support, platform security, fraud prevention, and service improvement.</p>
                        <p>We may also use your information to send important updates related to platform usage and safety. We do not use your data for any illegal or unauthorized purpose.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Public Content and User Responsibility</h2>
                        <p>Elocanto is a user-generated content platform. Any information you publish in ads, including images and descriptions, may be visible to other users.</p>
                        <p>We strongly recommend avoiding the sharing of sensitive personal or financial details in public listings, as this information becomes accessible to others.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Cookies and Tracking Technologies</h2>
                        <p>We use cookies and similar technologies to improve user experience, analyze traffic, and enhance platform functionality. Cookies help us remember user preferences and understand how users interact with the website.</p>
                        <p>You may disable cookies through your browser settings, but some features of the platform may not work properly if cookies are turned off.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Data Sharing and Third-Party Services</h2>
                        <p>We do not sell or rent your personal data to third parties. However, we may share limited information with trusted service providers who help us operate the platform, such as hosting providers, analytics tools, or security partners.</p>
                        <p>These third parties are required to handle your data securely and only for service-related purposes. We may also disclose information if required by law or to protect the rights and safety of users or the platform.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Data Protection and Security Measures</h2>
                        <p>We take reasonable security measures to protect your personal data from unauthorized access, misuse, or loss. However, no online system is completely secure. Users are also responsible for keeping their account information safe and avoiding sharing sensitive data publicly.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>User Data Responsibility and Safe Usage</h2>
                        <p>Users are responsible for the information they choose to share on the platform. We strongly advise users not to post sensitive information such as passwords, bank details, or identification numbers in public areas of the website.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Third-Party Links and External Websites</h2>
                        <p>Elocanto may contain links to external websites or third-party services. We are not responsible for the privacy practices or content of these external sites. Users are encouraged to review their policies before sharing any personal information.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Children’s Privacy Protection</h2>
                        <p>Elocanto is not intended for use by individuals under the age of 18 without supervision. We do not knowingly collect personal data from minors. If such data is identified, it will be removed promptly.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Updates and Changes to This Privacy Policy</h2>
                        <p>We may update this Privacy Policy from time to time to reflect changes in services or legal requirements. Users are encouraged to review this page regularly. Continued use of the platform after updates means acceptance of the revised policy.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Contact Information and User Support</h2>
                        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or how your data is handled, you can contact us at any time. We are committed to maintaining transparency and providing support to all users of Elocanto.</p>
                        <p><strong>Email:</strong> support@elocanto.pk</p>
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
