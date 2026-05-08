<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Terms & Conditions | ADDAAX', 'terms');
?>

<style>
    :root {
        --page-accent: #C9A84C;
        --page-bg: #ffffff;
        --text-gold: #C9A84C;
        --banner-bg: #000000;
    }
    
    .legal-page-wrapper {
        background: var(--page-bg);
        min-height: 100vh;
    }
    
    .legal-banner {
        background: var(--banner-bg);
        padding: 120px 0 80px;
        text-align: center;
        border-bottom: 2px solid var(--page-accent);
        position: relative;
    }
    
    .legal-banner h1 {
        background: linear-gradient(to right, #F5E9C8, var(--page-accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 900;
        margin-bottom: 20px;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    
    .legal-banner .breadcrumbs {
        color: rgba(255,255,255,0.6) !important;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .legal-banner .breadcrumbs a {
        color: white !important;
        text-decoration: none;
    }
    
    .legal-banner .breadcrumbs span.current {
        color: var(--page-accent);
    }
    
    .legal-content-section {
        background: #ffffff;
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
        width: 3px;
        background: #000000;
        opacity: 0.15;
    }

    .timeline-item {
        position: relative;
        padding-left: 80px;
        margin-bottom: 60px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        width: 24px;
        height: 24px;
        background: #ffffff;
        border: 4px solid var(--page-accent);
        border-radius: 50%;
        z-index: 2;
        box-shadow: 0 0 10px rgba(201, 168, 76, 0.2);
    }

    .timeline-content {
        background: #ffffff;
        padding: 40px;
        border-radius: 30px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s ease;
    }

    .timeline-content:hover {
        transform: translateY(-5px);
        border-color: var(--page-accent);
        box-shadow: 0 20px 40px rgba(201, 168, 76, 0.1);
    }

    .timeline-content h2 {
        color: var(--page-accent);
        font-size: 1.5rem;
        margin-bottom: 20px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .timeline-content p {
        margin-bottom: 15px;
        font-size: 1.15rem;
        color: #C9A84C;
        line-height: 1.8;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .timeline-wrapper::before { left: 16px; }
        .timeline-item { padding-left: 45px; }
        .timeline-item::before { left: 5px; width: 20px; height: 20px; }
        .timeline-content { padding: 25px; }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>Terms & Conditions</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">Terms & Conditions</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="timeline-wrapper">
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Acceptance and Agreement to Terms</h2>
                        <p>Welcome to ADDAAX. By accessing or using our website, you agree to comply with and be bound by these Terms and Conditions. These terms are designed to ensure a safe, fair, and reliable environment for all users of the platform.</p>
                        <p>If you do not agree with any part of these terms, you should discontinue using our services immediately.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Platform Overview and Nature of Service</h2>
                        <p>ADDAAX is an online classified ads platform that enables users to post listings, explore ads, and connect with others for buying, selling, or offering services.</p>
                        <p>We do not own, control, or directly sell any of the products or services listed on the platform. All content is generated and managed by individual users. Our role is limited to providing a digital space where users can interact.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>User Obligations and Responsibilities</h2>
                        <p>By using ADDAAX, you agree to act responsibly, honestly, and in accordance with applicable laws.</p>
                        <p>You are solely responsible for the content you post, including text, images, and any other information. All listings must be accurate, genuine, and not misleading. Any misuse of the platform that may harm other users or the integrity of the platform is strictly prohibited.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Listing Standards and Content Compliance</h2>
                        <p>All ads posted on ADDAAX must meet basic quality and legal standards. Users must ensure that their listings are clear, truthful, and relevant.</p>
                        <p>Posting duplicate ads, misleading information, or unauthorized content is not allowed. Any content that violates laws or platform guidelines may be removed without prior notice.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Restricted and Prohibited Activities</h2>
                        <p>Users are strictly prohibited from engaging in activities that compromise the safety or functionality of the platform.</p>
                        <p>This includes fraudulent behavior, scam attempts, spam posting, fake listings, system abuse, or any attempt to manipulate visibility or engagement. Violations may result in immediate action, including account suspension or permanent removal.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>User-to-User Transactions and Deal Responsibility</h2>
                        <p>All transactions conducted through ADDAAX take place directly between users. We do not act as a party to any agreement, payment, or delivery process. Therefore, we do not guarantee the quality, safety, legality, or authenticity of any listing.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Payment Disclaimer and Financial Risk</h2>
                        <p>ADDAAX does not process or facilitate payments between users. All financial interactions are carried out at the user's own discretion and risk. We strongly recommend avoiding advance payments unless you fully trust the other party.</p>
                        <p>We are not liable for any financial loss, fraud, or disputes arising from user transactions.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Reporting System and Complaint Resolution</h2>
                        <p>We provide built-in tools that allow users to report listings, users, or any suspicious activity.</p>
                        <p>If you encounter fraud, scam, misleading content, or copyright violations, you can submit a complaint through our platform. Our team reviews all reports carefully and aims to take appropriate action within 24 hours.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Intellectual Property Rights and Content Ownership</h2>
                        <p>Users must ensure that they have full rights to use and upload any content on the platform. Uploading copyrighted material without proper authorization is strictly prohibited. Any reported violation may result in content removal and further action against the account.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Limitation of Liability and Platform Disclaimer</h2>
                        <p>ADDAAX is provided on an "as-is" basis without any guarantees or warranties. We are not responsible for any direct or indirect loss, damage, or disputes that may occur between users. This includes financial loss, misrepresentation, or issues related to listings or services.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Account Control and Enforcement Rights</h2>
                        <p>We reserve the right to monitor, restrict, suspend, or permanently remove user accounts that violate our terms or engage in harmful activity. We may also limit access to certain features if required to maintain platform safety and integrity.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Modifications and Updates to Terms</h2>
                        <p>ADDAAX reserves the right to update or modify these Terms and Conditions at any time without prior notice. Users are encouraged to review this page regularly. Continued use of the platform after changes means you accept the updated terms.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Contact Information and User Support</h2>
                        <p>If you have any questions, concerns, or need assistance regarding these Terms and Conditions, you can contact us through our official website. We are committed to providing support and maintaining a safe experience for all users.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
