<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Terms & Conditions | Elocanto', 'terms');
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
            <div class="legal-card">
                <h2>Acceptance and Agreement to Terms</h2>
                <p>Welcome to Elocanto. By accessing or using our website, you agree to comply with and be bound by these Terms and Conditions. These terms are designed to ensure a safe, fair, and reliable environment for all users of the platform.</p>
                <p>If you do not agree with any part of these terms, you should discontinue using our services immediately.</p>

                <h2>Platform Overview and Nature of Service</h2>
                <p>Elocanto is an online classified ads platform that enables users to post listings, explore ads, and connect with others for buying, selling, or offering services.</p>
                <p>We do not own, control, or directly sell any of the products or services listed on the platform. All content is generated and managed by individual users. Our role is limited to providing a digital space where users can interact.</p>

                <h2>User Obligations and Responsibilities</h2>
                <p>By using Elocanto, you agree to act responsibly, honestly, and in accordance with applicable laws.</p>
                <p>You are solely responsible for the content you post, including text, images, and any other information. All listings must be accurate, genuine, and not misleading. Any misuse of the platform that may harm other users or the integrity of the platform is strictly prohibited.</p>

                <h2>Listing Standards and Content Compliance</h2>
                <p>All ads posted on Elocanto must meet basic quality and legal standards. Users must ensure that their listings are clear, truthful, and relevant.</p>
                <p>Posting duplicate ads, misleading information, or unauthorized content is not allowed. Any content that violates laws or platform guidelines may be removed without prior notice.</p>

                <h2>Restricted and Prohibited Activities</h2>
                <p>Users are strictly prohibited from engaging in activities that compromise the safety or functionality of the platform.</p>
                <p>This includes fraudulent behavior, scam attempts, spam posting, fake listings, system abuse, or any attempt to manipulate visibility or engagement. Violations may result in immediate action, including account suspension or permanent removal.</p>

                <h2>User-to-User Transactions and Deal Responsibility</h2>
                <p>All transactions conducted through Elocanto take place directly between users. We do not act as a party to any agreement, payment, or delivery process. Therefore, we do not guarantee the quality, safety, legality, or authenticity of any listing.</p>
                <p>Users are advised to exercise caution and verify all details before completing any transaction.</p>

                <h2>Payment Disclaimer and Financial Risk</h2>
                <p>Elocanto does not process or facilitate payments between users. All financial interactions are carried out at the user's own discretion and risk. We strongly recommend avoiding advance payments unless you fully trust the other party.</p>
                <p>We are not liable for any financial loss, fraud, or disputes arising from user transactions.</p>

                <h2>Reporting System and Complaint Resolution</h2>
                <p>We provide built-in tools that allow users to report listings, users, or any suspicious activity.</p>
                <p>If you encounter fraud, scam, misleading content, or copyright violations, you can submit a complaint through our platform. Our team reviews all reports carefully and aims to take appropriate action within 24 hours.</p>

                <h2>Intellectual Property Rights and Content Ownership</h2>
                <p>Users must ensure that they have full rights to use and upload any content on the platform. Uploading copyrighted material without proper authorization is strictly prohibited. Any reported violation may result in content removal and further action against the account.</p>

                <h2>Limitation of Liability and Platform Disclaimer</h2>
                <p>Elocanto is provided on an "as-is" basis without any guarantees or warranties. We are not responsible for any direct or indirect loss, damage, or disputes that may occur between users. This includes financial loss, misrepresentation, or issues related to listings or services.</p>

                <h2>Account Control and Enforcement Rights</h2>
                <p>We reserve the right to monitor, restrict, suspend, or permanently remove user accounts that violate our terms or engage in harmful activity. We may also limit access to certain features if required to maintain platform safety and integrity.</p>

                <h2>Modifications and Updates to Terms</h2>
                <p>Elocanto reserves the right to update or modify these Terms and Conditions at any time without prior notice. Users are encouraged to review this page regularly. Continued use of the platform after changes means you accept the updated terms.</p>

                <h2>Contact Information and User Support</h2>
                <p>If you have any questions, concerns, or need assistance regarding these Terms and Conditions, you can contact us through our official website. We are committed to providing support and maintaining a safe experience for all users.</p>
            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
